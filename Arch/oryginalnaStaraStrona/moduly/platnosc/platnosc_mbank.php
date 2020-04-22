<?php

if(!class_exists('platnosc_mbank')) {
  class platnosc_mbank {

    // class constructor
    function platnosc_mbank( $parametry ) {
      global $zamowienie, $Tlumaczenie, $numer_sklepu;

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

        $numer_sklepu    = $this->paramatery['parametry']['PLATNOSC_MBANK_NUMER_SKLEPU'];

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

        $tekst .= '<div class="WyrazamZgode"><input type="checkbox" value="1" name="regulamin_mbank" class="required" />Akceptuję regulamin.<div id="error-potwierdzenie"></div><br />Po potwierdzeniu zamówienia zostaniesz przekierowany do wniosku o mBank RATY,<br />gdzie będziesz mógł wnioskować o kredyt na wybrane produkty.</div>';

        return $tekst;
    }

    function podsumowanie() {

        $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);

        $tekst                          = '';
        $parameters                         = array();

        $koszt_wysylki                      = 0;
        $wartosc_produktow                  = 0;
        $ilosc_produktow                    = 0;
        $n                                  = 1;

        foreach ( $zamowienie->podsumowanie as $podsuma ) {
            if ( $podsuma['klasa'] == 'ot_shipping' || $podsuma['klasa'] == 'ot_payment' ) {
                $koszt_wysylki = $koszt_wysylki + $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_subtotal' ) {
                $wartosc_produktow = $podsuma['wartosc'];
            }
            unset($podsuma);
        }

        $parameters['rodzaj_platnosci']     = 'mbank';

        $parameters['sprzedawca']               = $this->paramatery['parametry']['PLATNOSC_MBANK_NUMER_SKLEPU'];
        $parameters['nr_zam_w_sklepie']         = $_SESSION['zamowienie_id'];
        $parameters['rodzaj_zamowienia']        = 'sklep';

        $parameters['wnioskowana_kwota']        = $wartosc_produktow;

        $parameters['transport']                = $koszt_wysylki;

        //$parameters['redir']                    = ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/platnosc_koniec.php?typ=mbank';

        foreach ( $zamowienie->produkty as $produkt ) {

            $parameters['product'.$n]               = $produkt['nazwa'];
            $parameters['price'.$n]                 = $produkt['cena_koncowa_brutto'];
            $parameters['qty'.$n]                   = $produkt['ilosc'];
            $n++;
        }

        $formularz = '';
        while (list($key, $value) = each($parameters)) {
            if ( $key != 'rodzaj_platnosci' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst .= '<form action="https://www.mbank.net.pl/redirect.php" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_WNIOSKU_RATALNEGO}:<br /><br />';
        $tekst .= $formularz;
        $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_KUPUJE_Z_MBANK}" />
                   </div>
                   </form>';

        return $tekst;
    }

  
  }
}
?>