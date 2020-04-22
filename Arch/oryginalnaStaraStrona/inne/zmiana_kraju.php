<?php
chdir('../');            
if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'WYSYLKI', 'PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

        $wysylki = new Wysylki( $_POST['data'] );
        $tablicaWysylek = $wysylki->wysylki;

        unset($_SESSION['krajDostawy']);

        $zapytanie_kraj = "SELECT countries_id, countries_iso_code_2 FROM countries WHERE countries_iso_code_2 = '".$_POST['data']."'";
        $sql_kraj = $GLOBALS['db']->open_query($zapytanie_kraj);
        $wynik_kraj = $sql_kraj->fetch_assoc();
        
        $_SESSION['krajDostawy'] = array();
        $_SESSION['krajDostawy'] = array('id' => $wynik_kraj['countries_id'],
                                         'kod' => $wynik_kraj['countries_iso_code_2']);
                                         
        $GLOBALS['db']->close_query($sql_kraj);
        unset($zapytanie_kraj, $wynik_kraj); 

        $pierwsza_wysylka = array_slice($tablicaWysylek,0,1);

        unset($_SESSION['rodzajDostawy']);
        $_SESSION['rodzajDostawy'] = array('wysylka_id' => $pierwsza_wysylka['0']['id'],
                                           'wysylka_klasa' => $pierwsza_wysylka['0']['klasa'],
                                           'wysylka_koszt' => $pierwsza_wysylka['0']['wartosc'],
                                           'wysylka_nazwa' => $pierwsza_wysylka['0']['text'],
                                           'wysylka_vat_id' => $pierwsza_wysylka['0']['vat_id'],
                                           'wysylka_vat_stawka' => $pierwsza_wysylka['0']['vat_stawka'],                                           
                                           'dostepne_platnosci' => $pierwsza_wysylka['0']['dostepne_platnosci']);

        $platnosci = new Platnosci( $pierwsza_wysylka['0']['id'] );
        $tablicaPlatnosci = $platnosci->platnosci;


        $pierwsza_platnosc = array_slice($tablicaPlatnosci,0,1);
        unset($_SESSION['rodzajPlatnosci']);
        $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $pierwsza_platnosc['0']['id'],
                                             'platnosc_klasa' => $pierwsza_platnosc['0']['klasa'],
                                             'platnosc_koszt' => $pierwsza_platnosc['0']['wartosc'],
                                             'platnosc_nazwa' => $pierwsza_platnosc['0']['text']);

        // parametry do ustalenia podsumowania zamowienia
        $podsumowanie = new Podsumowanie();
        $podsumowanie_zamowienia = $podsumowanie->Generuj();

        $wynik = array();
        $bezplatna_dostawa = '';

        if ( $pierwsza_wysylka['0']['wysylka_free'] > 0 ) {
          $bezplatna_dostawa = str_replace( '{KWOTA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($pierwsza_wysylka['0']['wysylka_free'], $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', $GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA'] );
        }

        $wynik['wysylki'] = Funkcje::ListaRadioKoszyk('rodzaj_wysylki', $tablicaWysylek, $pierwsza_wysylka['0']['id'], ''); 
        $wynik['platnosci'] = Funkcje::ListaRadioKoszyk('rodzaj_platnosci', $tablicaPlatnosci, $pierwsza_platnosc['0']['id'], ''); 
        $wynik['podsumowanie'] = $podsumowanie_zamowienia;

        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_santander', $tablicaPlatnosci) ) {
          $wynik['santander'] = '<a onclick="PoliczRateSantander('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_santander_white_koszyk.png" alt="" /></a>';
        } else {
          $wynik['santander'] = '';
        }

        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_lukas', $tablicaPlatnosci) ) {
          $wynik['lukas'] = '<a onclick="PoliczRateLukas('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_lukas_produkt.png" alt="" /></a>';
        } else {
          $wynik['lukas'] = '';
        }

        if ( Funkcje::CzyJestWlaczonaPlatnosc('platnosc_mbank', $tablicaPlatnosci) ) {
          $wynik['mbank'] = '<a onclick="PoliczRateMbank('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_mbank_koszyk.png" alt="" /></a>';
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
        } else {
          $wynik['payu'] = '';
        }

        if ( $_SESSION['rodzajPlatnosci']['platnosc_id'] == '0' || $_SESSION['rodzajDostawy']['wysylka_id'] == '0' ) {
          $wynik['przycisk_zamow'] = false;
        } else {
          $wynik['przycisk_zamow'] = true;
        }
        $wynik['wysylka_free'] = $bezplatna_dostawa;

        unset($tablicaWysylek,$tablicaPlatnosci,$pierwsza_platnosc,$pierwsza_wysylka,$podsumowanie_zamowienia);

        echo json_encode($wynik);
        
    }
    
}

?>