<?php

// plik
$WywolanyPlik = 'brak_strony';

include('start.php');

if ( isset($_GET['kod']) && $_GET['kod'] != '' ) {
    $kodBledu = $_GET['kod'];
}
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// wyglad srodkowy
$srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) ); 

$srodek->dodaj('__NAGLOWEK_INFORMACJI', $GLOBALS['tlumacz']['BRAK_DANYCH_DO_WYSWIETLENIA']);

if ( isset($_GET['producent']) ) {
     $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUCENTA']);
     $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUCENTA']);
}

if ( isset($_GET['kategoria']) ) {
     $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_KATEGORII']);
     $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_KATEGORII']);
}

if ( isset($_GET['recenzja']) ) {
     $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_RECENZJI']);
     $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_RECENZJI']);
}

if ( isset($_GET['produkt']) ) {
     $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUKTU']);
     $nawigacja->dodaj($GLOBALS['tlumacz']['BLAD_NIE_ZNALEZIONO_PRODUKTU']);
}

if ( isset($kodBledu) ) {
     //
     if (isset($GLOBALS['tlumacz'][$kodBledu])) {
         //
         $srodek->dodaj('__KOMUNIKAT',$GLOBALS['tlumacz'][$kodBledu]);
         $nawigacja->dodaj($GLOBALS['tlumacz'][$kodBledu]);
         //
      } else {
         //
         Funkcje::PrzekierowanieURL('brak-strony.html'); 
         //
     }
}
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>