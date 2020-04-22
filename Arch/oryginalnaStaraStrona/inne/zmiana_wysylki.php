<?php
chdir('../');            
if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'WYSYLKI', 'PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

        $wysylki = new Wysylki( $_SESSION['krajDostawy']['kod'] );
        $tablicaWysylek = $wysylki->wysylki;

        unset($_SESSION['rodzajDostawy']);
        $_SESSION['rodzajDostawy'] = array('wysylka_id' => $tablicaWysylek[$_POST['data']]['id'],
                                           'wysylka_klasa' => $tablicaWysylek[$_POST['data']]['klasa'],
                                           'wysylka_koszt' => $tablicaWysylek[$_POST['data']]['wartosc'],
                                           'wysylka_nazwa' => $tablicaWysylek[$_POST['data']]['text'],
                                           'wysylka_vat_id' => $tablicaWysylek[$_POST['data']]['vat_id'],
                                           'wysylka_vat_stawka' => $tablicaWysylek[$_POST['data']]['vat_stawka'],
                                           'dostepne_platnosci' => $tablicaWysylek[$_POST['data']]['dostepne_platnosci'] );

        $platnosci = new Platnosci( $_POST['data'] );
        $tablicaPlatnosci = $platnosci->platnosci;

        if ( !array_key_exists($_SESSION['rodzajPlatnosci']['platnosc_id'], $tablicaPlatnosci) ) {
        
          $pierwsza_platnosc = array_slice($tablicaPlatnosci,0,1);
          unset($_SESSION['rodzajPlatnosci']);
          
          $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $pierwsza_platnosc['0']['id'],
                                               'platnosc_klasa' => $pierwsza_platnosc['0']['klasa'],
                                               'platnosc_koszt' => $pierwsza_platnosc['0']['wartosc'],
                                               'platnosc_nazwa' => $pierwsza_platnosc['0']['text'] );
                                              
        } else {
        
          if ( isset($tablicaPlatnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['id']) ) {
          
              $PlatnoscId = $tablicaPlatnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['id'];
              unset($_SESSION['rodzajPlatnosci']);
              
              $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $tablicaPlatnosci[$PlatnoscId]['id'],
                                                   'platnosc_klasa' => $tablicaPlatnosci[$PlatnoscId]['klasa'],
                                                   'platnosc_koszt' => $tablicaPlatnosci[$PlatnoscId]['wartosc'],
                                                   'platnosc_nazwa' => $tablicaPlatnosci[$PlatnoscId]['text'] );
              unset($PlatnoscId);
          }
          
        }

        // parametry do ustalenia podsumowania zamowienia
        $podsumowanie = new Podsumowanie();
        $podsumowanie_zamowienia = $podsumowanie->Generuj();

        $wynik = array();
        $bezplatna_dostawa = '';
        $bylKalkulator = false;

        if ( $tablicaWysylek[$_POST['data']]['wysylka_free'] > 0 ) {
          $bezplatna_dostawa = str_replace( '{KWOTA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($tablicaWysylek[$_POST['data']]['wysylka_free'], $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', $GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA'] );
        }

        $wynik['platnosci'] = Funkcje::ListaRadioKoszyk('rodzaj_platnosci', $tablicaPlatnosci, $_SESSION['rodzajPlatnosci']['platnosc_id'], '');
        
        $wynik['podsumowanie'] = $podsumowanie_zamowienia;

        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_santander', $tablicaPlatnosci) ) {
          $wynik['santander'] = '<a onclick="PoliczRateSantander('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_santander_white_koszyk.png" alt="" /></a>';
          $bylKalkulator = true;
        } else {
          $wynik['santander'] = '';
        }

        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_lukas', $tablicaPlatnosci) ) {
          $wynik['lukas'] = '<a onclick="PoliczRateLukas('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_lukas_produkt.png" alt="" /></a>';
          $bylKalkulator = true;
        } else {
          $wynik['lukas'] = '';
        }

        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_mbank', $tablicaPlatnosci) ) {
          $wynik['mbank'] = '<a onclick="PoliczRateMbank('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_mbank_koszyk.png" alt="" /></a>';
          $bylKalkulator = true;
        } else {
          $wynik['mbank'] = '';
        }

        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu', $tablicaPlatnosci) ) {
          $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_RATY_WLACZONE'";
          $sqlp = $GLOBALS['db']->open_query($zap);
          if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
            $infop = $sqlp->fetch_assoc();
            if ( $infop['wartosc'] == 'tak' ) {
              $wynik['payu'] = '<a onclick="PoliczRatePauYRaty('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_payu_koszyk.png" alt="" /></a>';
            }
          }
          $GLOBALS['db']->close_query($sqlp); 
          unset($zap, $infop);
          $bylKalkulator = true;
        } else {
          $wynik['payu'] = '';
        }

        if ( $_SESSION['rodzajPlatnosci']['platnosc_id'] == '0' || $_SESSION['rodzajDostawy']['wysylka_id'] == '0' ) {
          $wynik['przycisk_zamow'] = false;
        } else {
          $wynik['przycisk_zamow'] = true;
        }
        $wynik['wysylka_free'] = $bezplatna_dostawa;

        if ( $bylKalkulator == true ) {
            $wynik['raty'] = 'OK';
        } else {
            $wynik['raty'] = '';
        }

        unset($tablicaWysylek,$tablicaPlatnosci,$pierwsza_platnosc,$podsumowanie_zamowienia);

        echo json_encode($wynik);

    }
    
}

?>