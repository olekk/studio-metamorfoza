<?php

if(!class_exists('platnosc_lukas')) {
  class platnosc_lukas {

    // class constructor
    function platnosc_lukas( $parametry ) {
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

        $numer_sklepu          = $this->paramatery['parametry']['PLATNOSC_LUKAS_NUMER_SKLEPU'];

        $this->kategorie       = $this->paramatery['parametry']['PLATNOSC_LUKAS_KATEGORIE'];

        $this->tekst_info      = $Tlumaczenie['PLATNOSC_'.$this->paramatery['id'].'_TEKST'];

        unset($Tlumaczenie);


    }

    function przetwarzanie() {

      // ustalenie wartosci zamowienia oraz czy w koszyku sa produkty z wykluczonych kategorii
      $wartosc_zamowienia = 0;
      foreach ( $_SESSION['koszyk'] as $rekord ) {
        //wartosc zamowienia
        $wartosc_zamowienia += $rekord['cena_brutto']*$rekord['ilosc'];

        //wykluczone kategorie
        $wykluczoneKategorie = explode(',',$this->kategorie);
        for ( $i=0, $x=sizeof($wykluczoneKategorie); $i<$x; $i++ ) {
            if ( $wykluczoneKategorie[$i] == $rekord['id_kategorii'] ) {
                 $this->wyswietl = false;
                 return;
            }
        }
        unset($wykluczoneKategorie);

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

        return $tekst;
    }

    static function GenerujKalkulator() {
      global $numer_sklepu, $wariant_sklepu;

      $wynik = '';
      $wynik .= "<script type=\"text/javascript\">";
      $wynik .= "function PoliczRateLukas(wartosc) { window.open('https://wniosek.eraty.pl/symulator/oblicz/numerSklepu/".$numer_sklepu."/wariantSklepu/".$wariant_sklepu."/typProduktu/0/wartoscTowarow/'+wartosc, 'Policz_rate', 'width=630,height=680,directories=no,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); }";
      $wynik .= "</script>";

      return $wynik;
    }

    function podsumowanie() {
        global $numer_sklepu;

        $zamowienie = new Zamowienie((int)$_SESSION['zamowienie_id']);

        $tekst                          = '';
        $parameters                     = array();
        $towary                         = array();

        $koszt_wysylki                      = 0;
        $wartosc_produktow                  = 0;
        $ilosc_produktow                    = 0;
        $n                                  = 1;
        $wartosc_kredytu                    = 0;
        $randomizer                         = date("YmdHis") . rand();

        foreach ( $zamowienie->podsumowanie as $podsuma ) {
            if ( $podsuma['klasa'] == 'ot_shipping' || $podsuma['klasa'] == 'ot_payment' ) {
                $koszt_wysylki = $koszt_wysylki + $podsuma['wartosc'];
            }
            if ( $podsuma['klasa'] == 'ot_subtotal' ) {
                $wartosc_produktow = $podsuma['wartosc'];
            }
            unset($podsuma);
        }
        $wartosc_kredytu                    = $wartosc_produktow + $koszt_wysylki;

        $parameters['rodzaj_platnosci']            = 'agricole';

        $parameters['numer_sklepu']                = $numer_sklepu;
        $parameters['nazwa_sklepu']                = INFO_NAZWA_SKLEPU;



        $parameters['PARAM_TYPE']                  = 'RAT';
        $parameters['PARAM_PROFILE']               = $numer_sklepu;
        $parameters['POST_ATTR']                   = '1';
        $parameters['cart.shopName']               = INFO_NAZWA_SKLEPU;
        $parameters['creditInfo.creditAmount']     = $wartosc_kredytu;
        $parameters['creditInfo.creditPeriod']     = '12';

        $parameters['email.address']               = $zamowienie->klient['adres_email'];
        $parameters['cart.orderNumber']            = $_SESSION['zamowienie_id'];
        $parameters['mailAddress.sameAsPermanent'] = 'tak';

        foreach ( $zamowienie->produkty as $produkt ) {
            if ( $n == 1 ) {
                $nazwa_produktu = trim($produkt['nazwa']);
                $cena_produktu  = $produkt['cena_koncowa_brutto'];
            }
            $parameters['cart.itemName'.$n]        = trim($produkt['nazwa']);
            $parameters['cart.itemQty'.$n]         = $produkt['ilosc'];
            $parameters['cart.itemPrice'.$n]       = $produkt['cena_koncowa_brutto'];
            $n++;
        }

        if ( $koszt_wysylki > 0 ) {
            $parameters['cart.itemName'.$n]        = 'Przesylka';
            $parameters['cart.itemQty'.$n]         = '1';
            $parameters['cart.itemPrice'.$n]       = $koszt_wysylki;
        }

        $parameters['pastCreditDataAgr.agreement'] = 'true';
        $parameters['marketingAgr.agreement']      = 'true';
        $parameters['emailAgr.agreement']          = 'true';
        $parameters['verificationAgr.agreement']   = 'true';
        $parameters['robinsonLBAgr.agreement']     = 'true';

        $parameters['PARAM_CREDIT_AMOUNT']         = $wartosc_kredytu;
        $parameters['PARAM_AUTH']                  = '2';
        $parameters['randomizer']                  = $randomizer;

        $hash = $numer_sklepu .'RAT' . '2' . $wartosc_kredytu . $nazwa_produktu . $cena_produktu . $randomizer . $this->paramatery['parametry']['PLATNOSC_LUKAS_HASLO'];

        $parameters['PARAM_HASH']                  = md5($hash);

        
        $formularz = '';
        while (list($key, $value) = each($parameters)) {
            if ( $key != 'rodzaj_platnosci' ) {
                $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
            }
        }

        $tekst .= '<form action="https://ewniosek.credit-agricole.pl/eWniosek/simulator.jsp" method="post" name="payform" id="payform" class="cmxform">';
        $tekst .= '<div style="text-align:center;padding:5px;padding-top:15px;">';
        $tekst .= $formularz;
        $tekst .= '<input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_KUPUJE_Z_LUKAS}" />
                   </div>
                   </form>';

        return $tekst;
    }

  }
}
?>