<?php
chdir('../');
//
define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');
//
require_once('ustawienia/ustawienia_db.php');
//      
include 'klasy/Bazadanych.php';
$db = new Bazadanych();
include 'klasy/Sesje.php';
$session = new Sesje((int)DLUGOSC_SESJI);

if ( isset($_SESSION['js_stale']) && count($_SESSION['js_stale']) > 0 ) {
    foreach ( $_SESSION['js_stale'] as $Stala => $Wartosc ) {
        define($Stala, $Wartosc);
    }
  } else {
    //
    // awaryjne zapytanie jezeli jest wylaczone cookie w przegladarce
    $zapytanie = 'select code, value from settings where js_type = "tak"';
    $sql = $db->open_query($zapytanie);
    while ($info = $sql->fetch_assoc()) { 
        define($info['code'], $info['value']);
    }
    $db->close_query($sql);
    unset($zapytanie, $info);    
}

if ( !isset($_SESSION['domyslnyJezyk']['kod']) ) { $_SESSION['domyslnyJezyk']['kod'] = 'pl'; }
if ( !isset($_SESSION['domyslnyJezyk']['id']) ) { $_SESSION['domyslnyJezyk']['id'] = '1'; }
if ( !isset($_SESSION['mobile']) ) { $_SESSION['mobile'] = 'nie'; }
if ( !isset($_SESSION['rwd']) ) { $_SESSION['rwd'] = 'tak'; }
if ( !isset($_SESSION['mobile_urzadzenie']) ) { $_SESSION['mobile_urzadzenie'] = 'tak'; }
if ( !isset($_SESSION['js_stale']) ) { $_SESSION['js_stale'] = array(); }

$PlikCacheJs = 'cache/js/skrypty_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

// jezeli nie ma pliku cache lub cache jest wylaczone lub wersja mobilna szablonu
if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie' || $_SESSION['mobile'] == 'tak' ) {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('javascript/jquery.validate.jcs');
    
    if ( PRELOAD_OBRAZKOW == 'tak' && $_SESSION['mobile'] != 'tak' ) {
        $kod .= file_get_contents('javascript/img_loader.jcs');    
    }    
    
    $kod .= file_get_contents('javascript/skrypty.jcs');
    $kod .= file_get_contents('javascript/scrollTo.js');
    $kod .= file_get_contents('javascript/autouzupelnienie.jcs');
    
    if ( $_SESSION['mobile'] != 'tak' ) {
    
         $kod .= file_get_contents('javascript/animacje.jcs');         
         
    }
    
    // jezeli jest szablon rwd doczyta plik js z szablonu rwd
    if ( $_SESSION['rwd'] == 'tak' ) {
        //
        if ( file_exists( 'szablony/' . DOMYSLNY_SZABLON . '/funkcje_mobilne.js' ) ) {
             $kod .= file_get_contents('szablony/' . DOMYSLNY_SZABLON . '/funkcje_mobilne.js');
        }
        //
    }
    
    $kod .= file_get_contents('javascript/moduly.jcs');
    
    // okienko a'la lytebox
    $kod .= file_get_contents('programy/colorBox/colorbox-min.jcs');    
    
    if ( LISTING_POROWNYWARKA_PRODUKTOW == 'tak' && $_SESSION['mobile'] != 'tak' ) {
        // porownywarka produktow
        $kod .= file_get_contents('javascript/porownywarka.jcs');
    }
    
    // walidacja newslettera
    $kod .= file_get_contents('javascript/newsletter.jcs');
    
    if ( $_SESSION['mobile'] != 'tak' ) {
         // walidacja boxu ankiety
         $kod .= file_get_contents('javascript/ankiety.jcs');
    }
    
    // powiekszanie zdjecia po najechaniu kursorem - tylko dla PC - nie dziala na mobilach
    if ( ZDJECIE_LISTING_POWIEKSZENIE == 'tak' && $_SESSION['mobile'] != 'tak' && $_SESSION['mobile_urzadzenie'] == 'nie' ) {
        $kod .= file_get_contents('javascript/oknoZdjecia.jcs');
    }

    if ( ( ZAKLADKA_FACEBOOK_WLACZONA == 'tak' ||
         ZAKLADKA_GG_WLACZONA == 'tak' ||
         ZAKLADKA_NK_WLACZONA == 'tak' ||
         ZAKLADKA_YOUTUBE_WLACZONA == 'tak' ||
         ZAKLADKA_GOOGLE_WLACZONA == 'tak' ||
         ZAKLADKA_TWITTER_WLACZONA == 'tak' ||
         ZAKLADKA_ALLEGRO_WLACZONA == 'tak' ||
         ZAKLADKA_PIERWSZA_WLACZONA == 'tak' ||
         ZAKLADKA_DRUGA_WLACZONA == 'tak' ||
         ZAKLADKA_TRZECIA_WLACZONA == 'tak' ) && $_SESSION['mobile'] != 'tak' && (!isset($_SERVER['HTTPS'])) ) {
         //
         // wysuwane zakladki
         $kod .= file_get_contents('javascript/zakladki.jcs');
         //
         foreach ( $_SESSION['js_stale'] as $Stala => $Wartosc ) {
            $kod = str_replace( '{__' . $Stala . '}', trim(preg_replace('/\s+/', ' ', $Wartosc)), $kod );
         }
         // wielkosci obrazkow dla indywidualnych zakladek
         if ( ZAKLADKA_PIERWSZA_IKONA != '' && file_exists(KATALOG_ZDJEC . '/' . ZAKLADKA_PIERWSZA_IKONA) ) {
              //
              list($szerokosc, $wysokosc) = getimagesize(KATALOG_ZDJEC . '/' . ZAKLADKA_PIERWSZA_IKONA);
              $kod = str_replace( '{__ZAKLADKA_PIERWSZA_IKONA_SZEROKOSC}', $szerokosc, $kod );
              $kod = str_replace( '{__ZAKLADKA_PIERWSZA_IKONA_WYSOKOSC}', $wysokosc, $kod );
              unset($szerokosc, $wysokosc);
              //
         }
         if ( ZAKLADKA_DRUGA_IKONA != '' && file_exists(KATALOG_ZDJEC . '/' . ZAKLADKA_DRUGA_IKONA) ) {
              //
              list($szerokosc, $wysokosc) = getimagesize(KATALOG_ZDJEC . '/' . ZAKLADKA_DRUGA_IKONA);
              $kod = str_replace( '{__ZAKLADKA_DRUGA_IKONA_SZEROKOSC}', $szerokosc, $kod );
              $kod = str_replace( '{__ZAKLADKA_DRUGA_IKONA_WYSOKOSC}', $wysokosc, $kod );
              unset($szerokosc, $wysokosc);
              //
         }
         if ( ZAKLADKA_TRZECIA_IKONA != '' && file_exists(KATALOG_ZDJEC . '/' . ZAKLADKA_TRZECIA_IKONA) ) {
              //
              list($szerokosc, $wysokosc) = getimagesize(KATALOG_ZDJEC . '/' . ZAKLADKA_TRZECIA_IKONA);
              $kod = str_replace( '{__ZAKLADKA_TRZECIA_IKONA_SZEROKOSC}', $szerokosc, $kod );
              $kod = str_replace( '{__ZAKLADKA_TRZECIA_IKONA_WYSOKOSC}', $wysokosc, $kod );
              unset($szerokosc, $wysokosc);
              //
         }         
         //
         $kod = str_replace( '{__DOMYSLNY_JEZYK}', $_SESSION['domyslnyJezyk']['id'], $kod );
         $kod = str_replace( '{__WYSUWANE_ZAKLADKI_WYSWIETLANIE}', WYSUWANE_ZAKLADKI_WYSWIETLANIE, $kod );
         //
    }

    if ( INTEGRACJA_OPENRATE_WLACZONY == 'tak' ) {
        if (isset($_SESSION['stronaGlowna']) && $_SESSION['stronaGlowna'] == true) {
            $kod .= file_get_contents('javascript/openrate.jcs');
        }
    }

    // tlumaczenia
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('FORMULARZ','PRODUKT','WYGLAD','PRZYCISKI') );

    // konwersja danych jezykowych
    $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $kod, $matches);
    foreach ($matches[1] as $WartoscJezykowa) {
        $kod = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', nl2br($tlumacz[$WartoscJezykowa]), $kod);
    }
    
    unset($i18n, $tlumacz);

    $kod = JSMin::minify($kod);

    if ( CACHE_JS == 'tak' && $_SESSION['mobile'] != 'tak' ) {
        // zapis cache js do pliku
        $plikKlucz = fopen($PlikCacheJs,'a+');
        flock($plikKlucz,LOCK_EX);
        fseek($plikKlucz,0);
        ftruncate($plikKlucz,0);
        fwrite($plikKlucz, $kod);
        fclose($plikKlucz);    
    }
    
} else {

    // odczyt cache js z pliku
    $plikKlucz = fopen($PlikCacheJs,'r');
    flock($plikKlucz,LOCK_SH);
    
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ){
        header ("HTTP/1.0 304 Not Modified");
        exit;
    } 
          
    $kod = file_get_contents($PlikCacheJs);
    fclose($plikKlucz);          

}

unset($PlikCacheJs);

// zamiana tokenu bezpieczenstwa
$kod = str_replace( '{__DOMYSLNY_SZABLON}', DOMYSLNY_SZABLON, $kod );
$kod = str_replace( '{__UKRYWANIE_INPUTOW_ILOSCI}', PRODUKT_KUPOWANIE_ILOSC, $kod );
$kod = str_replace( '{__KOSZYK_ANIMACJA}', KOSZYK_ANIMACJA, $kod );
$kod = str_replace( '{__SZEROKOSC_TIP}', ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, $kod );
$kod = str_replace( '{__WYSOKOSC_TIP}', ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC, $kod );
$kod = str_replace( '{__ZDJECIE_POWIEKSZANIE}', ZDJECIE_LISTING_POWIEKSZENIE, $kod );
$kod = str_replace( '{__LISTING_LUPA}', LISTING_LUPA, $kod );
$kod = str_replace( '{__AKCJA_KOSZYKA}', PRODUKT_OKNO_POPUP, $kod ); 
$kod = str_replace( '{__AKCJA_SCHOWKA}', PRODUKT_OKNO_SCHOWEK_POPUP, $kod ); 
$kod = str_replace( '{__TOKEN_NEWSLETTER}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_ANKIETA}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_POROWNYWARKA}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_JEZYK}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_WALUTA}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_SCHOWEK_DODAJ}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_SCHOWEK_USUN}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KOSZYK_DODAJ}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KOSZYK_GRATIS}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KOSZYK_DODAJ_PRZELICZ}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KOSZYK_DODAJ_ILOSC}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_AUTOUZUPELNIENIE}', Sesje::Token(), $kod ); 
$kod = str_replace( '{__TOKEN_OBRAZEK}', Sesje::Token(), $kod ); 

echo $kod;

unset($kod, $db, $session);

?>