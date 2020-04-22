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

$PlikCacheJs = 'cache/js/koszyk_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('javascript/koszyk.jcs');

    // tlumaczenia
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('PRODUKT','FORMULARZ') );

    // konwersja danych jezykowych
    $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $kod, $matches);
    foreach ($matches[1] as $WartoscJezykowa) {
        $kod = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', nl2br($tlumacz[$WartoscJezykowa]), $kod);
    }

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

// zamiana tokenu bezpieczenstwa
$kod = str_replace( '{__DOMYSLNY_SZABLON}', DOMYSLNY_SZABLON, $kod );
$kod = str_replace( '{__AKCJA_KOSZYKA}', PRODUKT_OKNO_POPUP, $kod );
$kod = str_replace( '{__AKCJA_SCHOWKA}', PRODUKT_OKNO_SCHOWEK_POPUP, $kod ); 
$kod = str_replace( '{__TOKEN_KOSZYK}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KOSZYK_KOMENTARZ}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KOSZYK_USUN}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KOSZYK_USUN_PRZELICZ}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_KUPON_AKTYWUJ}', Sesje::Token(), $kod );
$kod = str_replace( '{__TOKEN_PUNKTY_AKTYWUJ}', Sesje::Token(), $kod );

$SystemyRatalne = AktywneSystemyRatalne();

// system ratalny Santander
if ( isset($SystemyRatalne['platnosc_santander']) && count($SystemyRatalne['platnosc_santander']) > 0 ) {
    $kod = str_replace( '{__SANTANDER_NUMER_SKLEPU}', $SystemyRatalne['platnosc_santander']['PLATNOSC_SANTANDER_NUMER_SKLEPU'], $kod );
    $kod = str_replace( '{__SANTANDER_WARIANT_SKLEPU}', $SystemyRatalne['platnosc_santander']['PLATNOSC_SANTANDER_WARIANT_SKLEPU'], $kod );
} else {
    $kod = str_replace( '{__SANTANDER_NUMER_SKLEPU}', '13010005', $kod );
    $kod = str_replace( '{__SANTANDER_WARIANT_SKLEPU}', '1', $kod );
}

// system ratalny LUKAS
if ( isset($SystemyRatalne['platnosc_lukas']) && count($SystemyRatalne['platnosc_lukas']) > 0 ) {
    $kod = str_replace( '{__LUKAS_NUMER_SKLEPU}', $SystemyRatalne['platnosc_lukas']['PLATNOSC_LUKAS_NUMER_SKLEPU'], $kod );
} else {
    $kod = str_replace( '{__LUKAS_NUMER_SKLEPU}', 'PSP1013102', $kod );
}
echo $kod;

// system ratalny MBANK
if ( isset($SystemyRatalne['platnosc_mbank']) && count($SystemyRatalne['platnosc_mbank']) > 0 ) {
    $kod = str_replace( '{__MBANK_NUMER_SKLEPU}', $SystemyRatalne['platnosc_mbank']['PLATNOSC_MBANK_NUMER_SKLEPU'], $kod );
} else {
    $kod = str_replace( '{__MBANK_NUMER_SKLEPU}', '', $kod );
}
echo $kod;

unset($kod, $db, $session, $SystemyRatalne);

// funkcja zwracajaca tablice aktywnych systemow ratalnych
function AktywneSystemyRatalne() {

    $SystemyRatalne = array();

    $zapSystemyRatalne = "
                             SELECT p.id, p.klasa, pp.kod, pp.wartosc FROM modules_payment p
                             LEFT JOIN modules_payment_params pp ON p.id = pp.modul_id WHERE p.status = '1' AND (p.klasa = 'platnosc_santander' OR p.klasa = 'platnosc_lukas' OR p.klasa = 'platnosc_mbank')";
    $sql = $GLOBALS['db']->open_query($zapSystemyRatalne);
    //
    while ($info = $sql->fetch_assoc()) {
        $SystemyRatalne[$info['klasa']][$info['kod']] = $info['wartosc'];      
    }
    //
    $GLOBALS['db']->close_query($sql);
    //        
    unset($zapSystemyRatalne, $info, $sql);    
    
    return $SystemyRatalne; 

}

?>  