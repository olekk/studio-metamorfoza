<?php

if(!class_exists('platnosc_pbn')) {
  class platnosc_pbn {

    // class constructor
    function platnosc_pbn( $parametry = array() ) {
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

        $parameters                     = array();

        $parameters['rodzaj_platnosci'] = 'pbn';

        $time = strtotime($zamowienie->info['data_zamowienia']);

        if (is_numeric($this->paramatery['parametry']['PLATNOSC_PBN_NUMER_TERMIN'])) {
            $pay_time = (int)$this->paramatery['parametry']['PLATNOSC_PBN_NUMER_TERMIN'] * 60;
        } else {
            $pay_time = 50000 * 60;
        }
        $pay_time                       = $time + $pay_time;

        $kwota                          = number_format(($zamowienie->info['wartosc_zamowienia_val'] / $zamowienie->info['waluta_kurs']), 2, ".", "");
        $accname                        = trim($this->paramatery['parametry']['PLATNOSC_PBN_NAZWA_SPRZEDAWCY']) . '^NM^' . trim($this->paramatery['parametry']['PLATNOSC_PBN_KOD_POCZTOWY']) . '^ZP^' . trim($this->paramatery['parametry']['PLATNOSC_PBN_MIASTO']) . '^CI^' . trim($this->paramatery['parametry']['PLATNOSC_PBN_ULICA']) . '^ST^' . trim($this->paramatery['parametry']['PLATNOSC_PBN_PANSTWO']) . '^CT^';

        $parameters['id_client']        = '<id_client>' . preg_replace("/[^A-Za-z0-9]/", "", $this->paramatery['parametry']['PLATNOSC_PBN_ID']) . '</id_client>';
        $parameters['id_trans']         = '<id_trans>' . sprintf("%010s", (int)$_SESSION['zamowienie_id']) . '</id_trans>';
        $parameters['date_valid']       = '<date_valid>' . date('d-m-Y H:i:s', $pay_time) . '</date_valid>';
        $parameters['amount']           = '<amount>' . $kwota . '</amount>';
        $parameters['currency']         = '<currency>PLN</currency>';
        $parameters['email']            = '<email>' . $zamowienie->klient['adres_email'] . '</email>';
        $parameters['account']          = '<account>' . preg_replace("/[^0-9]/", "", $this->paramatery['parametry']['PLATNOSC_PBN_NUMER_KONTA']) . '</account>';
        $parameters['accname']          = '<accname>' . $accname . '</accname>';
        $parameters['backpage']         = '<backpage>' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=pbn&status=OK&zamowienie_id=' . (int)$_SESSION['zamowienie_id'] . '</backpage>';
        $parameters['backpagereject']   = '<backpagereject>' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=pbn&status=FAIL&zamowienie_id=' . (int)$_SESSION['zamowienie_id'] . '</backpagereject>';
        $parameters['password']         = '<password>' . $this->paramatery['parametry']['PLATNOSC_PBN_HASLO'] . '</password>';

        
        $parameters['hash']             = '<hash>' . sha1($parameters['id_client'] . $parameters['id_trans'] . $parameters['date_valid'] . $parameters['amount'] . $parameters['currency'] .$parameters['email'] . $parameters['account'] . $parameters['accname'] .$parameters['backpage'] .  $parameters['backpagereject'] . $parameters['password']) . '</hash>';

        $formData = '';
        while (list($key, $value) = each($parameters)) {
            if ( $key != 'rodzaj_platnosci' && $key != 'password' ) {
                $formData .= $value;
            }
        }

        $formDataToSend = base64_encode($formData);

        $parametry                      = serialize($parameters);

        $tekst .= '<form action="https://'.( $this->paramatery['parametry']['PLATNOSC_PBN_SANDBOX'] == '1' ? 'pbn.paybynet.com.pl/PayByNetT/trans.do' : 'pbn.paybynet.com.pl/PayByNet/trans.do') .'" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
        $tekst .= '<input type="hidden" value="'.$formDataToSend.'" name="hashtrans">';
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