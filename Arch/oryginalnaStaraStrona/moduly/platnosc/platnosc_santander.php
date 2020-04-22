<?php

if(!class_exists('platnosc_santander')) {
  class platnosc_santander {

    // class constructor
    function platnosc_santander( $parametry ) {
      global $zamowienie, $Tlumaczenie, $numer_sklepu, $wariant_sklepu;

        $Tlumaczenie          = $GLOBALS['tlumacz'];
        $this->paramatery     = $parametry;

        $this->klasa          = $this->paramatery['klasa'];
        $this->tytul          = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TYTUL'];
        $this->objasnienie    = ( isset($Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE']) ? $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_OBJASNIENIE'] : '' );
        $this->kolejnosc      = $this->paramatery['sortowanie'];
        $this->ikona          = '';
        $this->wyswietl       = false;
        $this->id             = $this->paramatery['id'];
        $this->wysylka_id     = $this->paramatery['wysylka_id'];

        $this->koszty         = $this->paramatery['parametry']['PLATNOSC_KOSZT'];
        $this->koszty_minimum = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_KOSZT_MINIMUM'],'',true);

        $this->wartosc_od      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'],'',true);
        $this->wartosc_do      = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'],'',true);

        $numer_sklepu    = $this->paramatery['parametry']['PLATNOSC_SANTANDER_NUMER_SKLEPU'];
        $wariant_sklepu  = $this->paramatery['parametry']['PLATNOSC_SANTANDER_WARIANT_SKLEPU'];

        $this->tekst_info      = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TEKST'];

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
          $koszt_platnosci = str_replace( 'x', $wartosc_zamowienia, $this->koszty);
          $koszt_platnosci = Funkcje::obliczWzor($koszt_platnosci);
          if ( $GLOBALS['waluty']->PokazCeneBezSymbolu($koszt_platnosci,'',true) < $this->koszty_minimum ) {
            $koszt_platnosci = $this->koszty_minimum;
          }
        } else {
          $koszt_platnosci = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->koszty,'',true);
        }

        $wynik = array('id' => $this->id,
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

        $tekst .= '<div class="WyrazamZgode"><input type="checkbox" value="1" name="regulamin_santander" class="raty required" />Zapoznałem się z <a style="cursor: pointer; text-decoration: underline;" onclick="SantanderRegulamin()">procedurą udzielenia kredytu konsumenckiego na zakup towarów i usług eRaty Santander Consumer Finanse</a><div id="error-potwierdzenie-raty"></div></div>';

        return $tekst;
    }

    static function GenerujKalkulator() {
      global $numer_sklepu, $wariant_sklepu;

      $wynik = '';
      $wynik .= "<script type=\"text/javascript\">";
      $wynik .= "function PoliczRateSantander(wartosc) { window.open('https://wniosek.eraty.pl/symulator/oblicz/numerSklepu/".$numer_sklepu."/wariantSklepu/".$wariant_sklepu."/typProduktu/0/wartoscTowarow/'+wartosc, 'Policz_rate', 'width=630,height=680,directories=no,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); }";
      $wynik .= "</script>";

      return $wynik;
    }

    function podsumowanie() {

        $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);

        $tekst                          = '';
        $parameters                         = array();

        $koszt_wysylki                      = 0;
        $wartosc_produktow                  = 0;
        $ilosc_produktow                    = 0;
        $n                                  = 1;

        $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->klient['ulica']);
        $adres_klienta_local  = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);

        foreach ( $zamowienie->podsumowanie as $podsuma ) {
            if ( $podsuma['klasa'] == 'ot_shipping' || $podsuma['klasa'] == 'ot_payment' ) {
                $koszt_wysylki = $koszt_wysylki + $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_subtotal' ) {
                $wartosc_produktow = $podsuma['wartosc'];
            }
            unset($podsuma);
        }

        $parameters['rodzaj_platnosci']     = 'santander';

        $parameters['numerSklepu']              = $this->paramatery['parametry']['PLATNOSC_SANTANDER_NUMER_SKLEPU'];
        $parameters['wariantSklepu']            = $this->paramatery['parametry']['PLATNOSC_SANTANDER_WARIANT_SKLEPU'];
        $parameters['typProduktu']              = '0';
        $parameters['nrZamowieniaSklep']        = $_SESSION['zamowienie_id'];

        foreach ( $zamowienie->produkty as $produkt ) {

            $parameters['idTowaru'.$n]              = $produkt['products_id'];
            $parameters['nazwaTowaru'.$n]           = $produkt['nazwa'];
            $parameters['wartoscTowaru'.$n]         = $produkt['cena_koncowa_brutto'];
            $parameters['liczbaSztukTowaru'.$n]     = $produkt['ilosc'];
            $parameters['jednostkaTowaru'.$n]       = $GLOBALS['jednostkiMiary'][$produkt['jm']]['nazwa'];
            $ilosc_produktow += $produkt['ilosc'];

            $n++;
        }

        if ( $koszt_wysylki > 0 ) {
            $parameters['idTowaru'.$n]              = 'kosztTransportu';
            $parameters['nazwaTowaru'.$n]           = 'Koszt przesyłki';
            $parameters['wartoscTowaru'.$n]         = $koszt_wysylki;
            $parameters['liczbaSztukTowaru'.$n]     = '1';
            $parameters['jednostkaTowaru'.$n]       = 'szt.';
            $ilosc_produktow += 1;
        }

        $parameters['wartoscTowarow']           = $wartosc_produktow + $koszt_wysylki;
        $parameters['liczbaSztukTowarow']       = $ilosc_produktow;

        $parameters['sposobDostarczeniaTowaru'] = $zamowienie->info['wysylka_modul'];

        $parameters['char']                     = 'UTF';

        $parameters['wniosekZapisany']          = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=santander&status=OK&zamowienie_id=';
        $parameters['wniosekAnulowany']         = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=santander&status=FAIL&zamowienie_id='.(int)$_SESSION['zamowienie_id'].'';


        $parameters['pesel']                    = '';

        $parameters['email']                = $zamowienie->klient['adres_email'];
        $parameters['telKontakt']           = $zamowienie->klient['telefon'];
        $parameters['imie']                 = $_SESSION['adresFaktury']['imie'];
        $parameters['nazwisko']             = $_SESSION['adresFaktury']['nazwisko'];

        $parameters['ulica']                = $adres_klienta['ulica'];
        $parameters['nrDomu']               = $adres_klienta_local['dom'];
        $parameters['nrMieszkania']         = $adres_klienta_local['mieszkanie'];
        $parameters['miasto']               = $zamowienie->platnik['miasto'];
        $parameters['kodPocz']              = $zamowienie->platnik['kod_pocztowy'];

        $parametry                          = serialize($parameters);

        $formularz = '';
        while (list($key, $value) = each($parameters)) {
            if ( $key != 'rodzaj_platnosci' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst .= '<form action="https://wniosek.eraty.pl/formularz/" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_WNIOSKU_RATALNEGO}:<br /><br />';
        $tekst .= $formularz;
        $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_KUPUJE_Z_SANTANDER}" />
                   </div>
                   </form>';

        return $tekst;
    }

  
  }
}
?>