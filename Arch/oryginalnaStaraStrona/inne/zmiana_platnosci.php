<?php
chdir('../');

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

        $platnosci = new Platnosci( $_SESSION['rodzajDostawy']['wysylka_id'] );
        $tablicaPlatnosci = $platnosci->platnosci;

        unset($_SESSION['rodzajPlatnosci']);
        $_SESSION['rodzajPlatnosci'] = array(
                                             'platnosc_id' => $tablicaPlatnosci[$_POST['data']]['id'],
                                             'platnosc_klasa' => $tablicaPlatnosci[$_POST['data']]['klasa'],
                                             'platnosc_koszt' => $tablicaPlatnosci[$_POST['data']]['wartosc'],
                                             'platnosc_nazwa' => $tablicaPlatnosci[$_POST['data']]['text'] );

        // parametry do ustalenia podsumowania zamowienia
        $podsumowanie = new Podsumowanie();
        $podsumowanie_zamowienia = $podsumowanie->Generuj();

        $wynik = array();
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

        unset($tablicaPlatnosci,$podsumowanie_zamowienia);

        echo json_encode($wynik);

    }
    
}

?>