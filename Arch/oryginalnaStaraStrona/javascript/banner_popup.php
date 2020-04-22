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

$PlikCacheJs = 'cache/js/banner_popup.jcs';

if (!file_exists($PlikCacheJs) || CACHE_JS == 'nie') {

    include 'klasy/jsMin.php';
    
    $kod = '';
    $kod .= file_get_contents('javascript/banner_popup.jcs'); 

    //$kod = JSMin::minify($kod);    
    
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

$kod = str_replace('{__BANNER_POPUP_AUTOCLOSE}', BANNER_POPUP_AUTOCLOSE, $kod);
$kod = str_replace('{__BANNER_POPUP_ILOSC_KLIKNIEC}', BANNER_POPUP_ILOSC_KLIKNIEC, $kod);
$kod = str_replace('{__BANNER_POPUP_EKRAN_SCIEMNIAJ}', BANNER_POPUP_EKRAN_SCIEMNIAJ, $kod);
$kod = str_replace('{__BANNER_POPUP_ZAMYKANIE}', BANNER_POPUP_ZAMYKANIE, $kod);
$kod = str_replace('{__BANNER_POPUP_WAZNOSC_COOKIE}', BANNER_POPUP_WAZNOSC_COOKIE, $kod);
$kod = str_replace('{__BANNER_POPUP_RODZAJ_OTWARCIA}', BANNER_POPUP_RODZAJ_OTWARCIA, $kod);
$kod = str_replace('{__BANNER_POPUP_RODZAJ_ZAMKNIECIA}', BANNER_POPUP_RODZAJ_ZAMKNIECIA, $kod);

echo $kod;

unset($kod, $db, $session);

?>
