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
if ( !isset($_SESSION['domyslnaWaluta']['symbol']) ) { $_SESSION['domyslnaWaluta']['symbol'] = 'zł'; }
if ( !isset($_SESSION['domyslnaWaluta']['separator']) ) { $_SESSION['domyslnaWaluta']['separator'] = ','; }
if ( !isset($_SESSION['domyslnaWaluta']['przelicznik']) ) { $_SESSION['domyslnaWaluta']['przelicznik'] = '1'; }

$PlikCacheJs = 'cache/js/produkt_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('javascript/produkt.jcs');
    $kod .= file_get_contents('javascript/swfobject.js');

    // tlumaczenia
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('WYGLAD','PRODUKT','SYSTEM_PUNKTOW') );
    
    // konwersja danych jezykowych
    $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $kod, $matches);
    foreach ($matches[1] as $WartoscJezykowa) {
        $kod = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', nl2br($tlumacz[$WartoscJezykowa]), $kod);
    }    
    
    unset($i18n, $tlumacz);

    // czy jest wlaczone skalowanie
    if (SKALOWANIE_POWIEKSZONE == 'tak') {
        $kod = str_replace('{SKALOWANIE}',", maxWidth:'90%', maxHeight:'90%'", $kod);
      } else {
        $kod = str_replace('{SKALOWANIE}','', $kod);
    }

    // jezeli jest wlaczona kontrola stanu magazynowego cech
    if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' ) {
        $kod = str_replace('{STAN_MAGAZYNOWY_CECH}','tak', $kod);
      } else {
        $kod = str_replace('{STAN_MAGAZYNOWY_CECH}','nie', $kod);
    }
    if ( MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'tak' ) {
        $kod = str_replace('{MAGAZYN_SPRZEDAJ_MIMO_BRAKU}','tak', $kod);
      } else {
        $kod = str_replace('{MAGAZYN_SPRZEDAJ_MIMO_BRAKU}','nie', $kod);
    }
    $kod = str_replace('{KARTA_PRODUKTU_CENA_KATALOGOWA_TYP}',KARTA_PRODUKTU_CENA_KATALOGOWA_TYP, $kod);
    $kod = str_replace('{KARTA_PRODUKTU_CENA_KATALOGOWA_TYP_ZAOKRAGLENIE}',KARTA_PRODUKTU_CENA_KATALOGOWA_TYP_ZAOKRAGLENIE, $kod);
    $kod = str_replace('{PRODUKT_KUPOWANIE_STATUS}',PRODUKT_KUPOWANIE_STATUS, $kod);

    // system punktow
    $kod = str_replace( '{WARTOSC_PUNKTOW}', (int)SYSTEM_PUNKTOW_WARTOSC, $kod );
    $kod = str_replace( '{WALUTA_PRZELICZNIK}', $_SESSION['domyslnaWaluta']['przelicznik'], $kod );
    
    $kod = str_replace( '{KATALOG_ZDJEC}', KATALOG_ZDJEC, $kod );

    unset($i18n, $tlumacz);

    $kod = JSMin::minify($kod);

    if ( CACHE_JS == 'tak' ) {
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

// zakladka z cookie
if (isset($_COOKIE['zakladka']) && $_COOKIE['zakladka'] != 'brak') {
    $kod = str_replace('{ZAKLADKA}', $_COOKIE['zakladka'], $kod);
  } else {
    $kod = str_replace('{ZAKLADKA}', '', $kod);
}

// podstawia znak waluty i separatora dziesietnego
$kod = str_replace('{SYMBOL}',$_SESSION['domyslnaWaluta']['symbol'], $kod);
$kod = str_replace('{SEPARATOR_DZIESIETNY}',$_SESSION['domyslnaWaluta']['separator'], $kod);
    
// zamiana tokenu bezpieczenstwa
$kod = str_replace( '{__DOMYSLNY_SZABLON}', DOMYSLNY_SZABLON, $kod );
$kod = str_replace( '{__TOKEN_PRODUKT}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_ZNIZKI}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_CECHA}', Sesje::Token(), $kod );

echo $kod;

unset($kod, $db, $session);

?> 