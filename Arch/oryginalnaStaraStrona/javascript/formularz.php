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

$PlikCacheJs = 'cache/js/formularz_' . $_SESSION['domyslnyJezyk']['kod'] . '.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/Jezyki.php';
    include 'klasy/Translator.php';
    include 'klasy/jsMin.php';

    $kod = '';
    $kod .= file_get_contents('programy/zebraDatePicker/zebra_datepicker.js');
    
    $kod .= file_get_contents('javascript/formularz.jcs');

    // tlumaczenia dla strony glownej
    $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);
    $tlumacz = $i18n->tlumacz( array('FORMULARZ') );

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
    
echo $kod;

unset($kod, $db, $session);

?>    