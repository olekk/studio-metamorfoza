<?php

if(!class_exists('platnosc_przelewy24')) {
  class platnosc_przelewy24 {

    // class constructor
    function platnosc_przelewy24( $parametry = array() ) {
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

        $parameters                         = array();

        $tekst                              = '';
        $kwota                              = number_format(($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']), 2, ".", "") * 100;

        $kluczCRC                           = session_id() . '-'. substr(md5(time()), 16) . '|' . $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_ID'] . '|' . $kwota . '|' . $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_CRC'];
        $kluczCRC                           = md5($kluczCRC);
 
        $parameters['rodzaj_platnosci']     = 'przelewy24';

        $parameters['p24_session_id']       = session_id() . '-'. substr(md5(time()), 16);
        $parameters['p24_id_sprzedawcy']    = $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_ID'];
        $parameters['p24_crc']              = $kluczCRC;
        $parameters['p24_kwota']            = $kwota;
        $parameters['p24_opis']             = 'Numer zamowienia: ' . (int)$_SESSION['zamowienie_id'];
        //$parameters['p24_opis']             = 'TEST_ERR103';
        $parameters['p24_language']         = $_SESSION['domyslnyJezyk']['kod'];
        $parameters['p24_klient']           = ( $zamowienie->platnik['nazwa'] != '' ? $zamowienie->platnik['nazwa'] : $zamowienie->klient['nazwa'] );
        $parameters['p24_adres']            = $zamowienie->platnik['ulica'];
        $parameters['p24_miasto']           = $zamowienie->platnik['miasto'];
        $parameters['p24_kod']              = $zamowienie->platnik['kod_pocztowy'];
        $parameters['p24_kraj']             = Funkcje::kodISOKrajuDostawy($_SESSION['adresFaktury']['panstwo']);
        $parameters['p24_email']            = $zamowienie->klient['adres_email'];
        $parameters['p24_return_url_ok']    = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=przelewy24&status=OK&zamowienie_id=' . (int)$_SESSION['zamowienie_id'];
        $parameters['p24_return_url_error'] = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=przelewy24&status=FAIL&zamowienie_id=' . (int)$_SESSION['zamowienie_id'];

        $parametry                          = serialize($parameters);

        $formularz = '';
        while (list($key, $value) = each($parameters)) {
            if ( $key != 'rodzaj_platnosci' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst .= '<form action="https://'.( $this->paramatery['parametry']['PLATNOSC_PRZELEWY24_SANDBOX'] == '1' ? 'sandbox.przelewy24.pl/index.php' : 'secure.przelewy24.pl/index.php') .'" method="post" name="payform" class="cmxform">
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