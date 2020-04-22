<?php

// plik
$WywolanyPlik = 'rejestracja';

include('start.php');

if ((!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0)) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('REJESTRACJA','KLIENCI') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // css do kalendarza
    $tpl->dodaj('__CSS_PLIK', ',zebra_datepicker');
    // dla wersji mobilnej
    $tpl->dodaj('__CSS_KALENDARZ', ',zebra_datepicker');

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_REJESTRACJA']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));
      
    $srodek->dodaj('__DODATKOWE_POLA_KLIENTOW', Klient::pokazDodatkowePolaKlientow('',$_SESSION['domyslnyJezyk']['id']));

    // select z panstwami
    $srodek->dodaj('__WYBOR_PANSTWA', Funkcje::RozwijaneMenu('panstwo',Klient::ListaPanstw(), $_SESSION['krajDostawy']['id'], 'id="selection" style="width:80%"'));

    // select z wojwodztwami
    $srodek->dodaj('__WYBOR_WOJEWODZTWA', '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', Klient::ListaWojewodztw($_SESSION['krajDostawy']['id']), '', ' style="width:80%"').'</span>');

    // wybor osoba fizyczna / firma lub tylko firma - ukrywanie pola firma
    if ( KLIENT_TYLKO_FIRMA == 'dowolny' ) {
         //
         $srodek->dodaj('__CSS_FIRMA', 'style="display:none"');
         $srodek->dodaj('__CSS_FIZYCZNA', '');
         //
       } else {
         //
         $srodek->dodaj('__CSS_FIRMA', '');
         $srodek->dodaj('__CSS_FIZYCZNA', 'style="display:none"');
         //
    }
    
    // informacja o koniecznosci akceptacji konta przez administratora sklepu
    $srodek->dodaj('__INFORMACJA_O_AKTYWACJI_KONTA', '');
    if ( KLIENT_AKTYWACJA == 'nie' ) {
         //
         $srodek->dodaj('__INFORMACJA_O_AKTYWACJI_KONTA', '<br /><br />' . $GLOBALS['tlumacz']['REJESTRACJA_KONTO_NIEAKTYWNE_INFO']);
         //
    } 

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik);

    include('koniec.php');

} else {

    Funkcje::PrzekierowanieURL('/');

}
?>