<?php

if(!class_exists('platnosc_payeezy')) {
  class platnosc_payeezy {

    // class constructor
    function platnosc_payeezy( $parametry = array() ) {
      global $zamowienie, $Tlumaczenie;
      
        $Tlumaczenie          = $GLOBALS['tlumacz'];
        $this->paramatery     = $parametry;

        $this->klasa          = $this->paramatery['klasa'];
        $this->tytul          = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TYTUL'];
        $this->objasnienie    = ( isset($Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE']) ? $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE'] : '' );
        $this->kolejnosc      = $this->paramatery['sortowanie'];
        $this->klasa          = $this->paramatery['klasa'];
        $this->ikona          = '';
        $this->wyswietl       = false;
        $this->id             = $this->paramatery['id'];
        $this->wysylka_id     = $this->paramatery['wysylka_id'];

        $this->koszty         = $this->paramatery['parametry']['PLATNOSC_KOSZT'];
        $this->koszty_minimum = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_KOSZT_MINIMUM'],'',true);

        $this->wartosc_od      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'],'',true);
        $this->wartosc_do      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'],'',true);

        $this->tekst_info      = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TEKST'];

        $this->txtguzik        = $Tlumaczenie['PRZYCISK_POWROT_DO_SKLEPU'];
        
        unset($Tlumaczenie);



    }

    function przetwarzanie() {

      // ustalenie wartosci zamowienia
      $wartosc_zamowienia = 0;
      foreach ( $_SESSION['koszyk'] as $rekord ) {
        $wartosc_zamowienia += $rekord['cena_brutto']*$rekord['ilosc'];
      }
      $wartosc_zamowienia += $_SESSION['rodzajDostawy']['wysylka_koszt'];

      $wynik = array();

      // sprawdzenie czy dana platnosc jest dostepna dla wybranego rodzaju dostawy
      $tablica_wysylek = explode(';', $_SESSION['rodzajDostawy']['dostepne_platnosci']);

      if ( in_array( $this->id, $tablica_wysylek ) ) {

        // sprawdzenie czy wartosc zamowienia miesci sie w dopuszczalnym zakresie dla danej platnosci
        if ( Funkcje::czyWartoscJestwZakresie($wartosc_zamowienia, $this->wartosc_do, $this->wartosc_od) ) {
          $this->wyswietl = true;
        }

      }

      if ( $this->wyswietl ) {
        // jezeli koszt platnosci jest okreslony wzorem, to oblicza wartosc
        if ( !is_numeric($this->koszty) && $this->koszty != '' ) {
          $koszt_platnosci = str_replace( 'x', $wartosc_zamowienia/($_SESSION['domyslnaWaluta']['przelicznik']), $this->koszty);
          $koszt_platnosci = Funkcje::obliczWzor($koszt_platnosci);
          if ( $GLOBALS['waluty']->PokazCeneBezSymbolu($koszt_platnosci,'',true) < $this->koszty_minimum ) {
            $koszt_platnosci = $this->koszty_minimum;
          }
        } else {
          $koszt_platnosci = $this->koszty;
        }

        $wynik = array('id' => $this->id,
                       'klasa' => $this->klasa,
                       'text' => $this->tytul,
                       'wartosc' => $koszt_platnosci,
                       'objasnienie' => $this->objasnienie,
                       'klasa' => $this->klasa,
        );
      }

      return $wynik;
    }

    function potwierdzenie() {

        $tekst = '';

        $tekst .= '
                  <div id="PlatnoscText">'.$this->tekst_info.'</div>
                  <div><textarea name="platnosc_info" id="platnoscInfo" style="display:none;" >'.$this->tekst_info.'</textarea></div>';


        if ( isset($_SESSION['rodzajPlatnosci']['opis']) ) {
            unset($_SESSION['rodzajPlatnosci']['opis']);
        }
        $_SESSION['rodzajPlatnosci']['opis'] = $this->tekst_info;

        return $tekst;
    }

    function podsumowanie() {

        $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);

        $tekst                          = '';
        $waluta                         = 'PLN';
        $jezyk                          = strtoupper($_SESSION['domyslnyJezyk']['kod']);
        $session_id                     = session_id() . '-' . $_SESSION['zamowienie_id'] . '-'. substr(md5(time()), 16);
        $salt                           = $this->paramatery['parametry']['PLATNOSC_PAYEEZY_SHARED_KEY']; 

        $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
        $adres_klienta_local  = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

        $parameters                     = array();

        $parameters['rodzaj_platnosci'] = 'payeezy';

        $parameters['pos_id']           = $this->paramatery['parametry']['PLATNOSC_PAYEEZY_POS_ID'];
        $parameters['order_id']         = (int)$_SESSION['zamowienie_id'];
        $parameters['session_id']       = $session_id;
        $parameters['amount']           = number_format(($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']), 2, ".", "") * 100;
        $parameters['currency']         = $waluta;
        $parameters['test']             = ( $this->paramatery['parametry']['PLATNOSC_PAYEEZY_SANDBOX'] == '1' ? 'Y' : 'N' );
        $parameters['language']         = $_SESSION['domyslnyJezyk']['kod'];
        $parameters['client_ip']        = $_SERVER["REMOTE_ADDR"];
        $parameters['street']           = $adres_klienta['ulica'];
        $parameters['street_n1']        = $adres_klienta_local['dom'];
        $parameters['street_n2']        = $adres_klienta_local['mieszkanie'];
        $parameters['city']             = $zamowienie->dostawa['miasto'];
        $parameters['postcode']         = $zamowienie->dostawa['kod_pocztowy'];
        $parameters['country']          = Funkcje::kodISOKrajuDostawy($_SESSION['adresDostawy']['panstwo']);
        $parameters['email']            = $zamowienie->klient['adres_email'];
        $parameters['ba_firstname']     = $_SESSION['adresDostawy']['imie'];
        $parameters['ba_lastname']      = $_SESSION['adresDostawy']['nazwisko'];

        $controlData = '';
        while (list($key, $value) = each($parameters)) {
            if ( $key != 'rodzaj_platnosci' && $value != '' ) {
                $controlData .= $key .'=' . $value . '&';
            }
        }
        $controlData = substr($controlData, 0, -1);

    
        $saltTab = str_split($salt);
        $hexLenght = strlen($salt);
        
        $saltBin = '';

        for ( $x=1; $x <= $hexLenght/2; $x++ ) {
            $saltBin .= ( pack("H*", substr($salt,2 * $x -2,2)) );
        }

        $parameters['controlData']      = hash("sha256", $controlData.$saltBin);

        $parametry                      = serialize($parameters);

        reset($parameters);
        $formularz = '';
        while (list($key, $value) = each($parameters)) {
            if ( $key != 'rodzaj_platnosci' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst .= '<form action="https://vpos.polcard.com.pl/vpos/ecom/service.htm" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
        $tekst .= $formularz;
        $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br /><br />';
        if (isset($_SESSION['gosc']) && $_SESSION['gosc'] == '0') {
            $tekst .= '   {__TLUMACZ:ZAPLAC_W_HISTORII_ZAMOWIENIA}';
        }
        $tekst .= '</div>
                   </form>';


        $pola = array(
                array('payment_info',$parametry),
        );

        $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['zamowienie_id'] . "'");
        unset($pola);

        return $tekst;
    }

  }
}
?>