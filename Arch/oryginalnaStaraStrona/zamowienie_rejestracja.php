<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'zamowienie_rejestracja';

include('start.php');

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() == 0 || (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0) || !isset($_POST['email_nowy']) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

// czy wartosc zamowienia nie jest mniejsza niz koszyk
$MinimalneZamowienieGrupy = Klient::MinimalneZamowienie();
if ( $MinimalneZamowienieGrupy > 0 ) {

    $MinZamowienie = $GLOBALS['waluty']->PokazCeneBezSymbolu($MinimalneZamowienieGrupy,'',true);
    $WartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

    if ( $WartoscKoszyka['brutto'] < $MinZamowienie ) {
         //
         Funkcje::PrzekierowanieURL('koszyk.html'); 
         //
    }
    unset($MinZamowienie, $WartoscKoszyka);
    
}  
unset($MinimalneZamowienieGrupy);

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA','LOGOWANIE','REJESTRACJA','KLIENCI') ), $GLOBALS['tlumacz'] );

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
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_DANE_DO_WYSYLKI']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

$srodek->dodaj('__DODATKOWE_POLA_KLIENTOW', Klient::pokazDodatkowePolaKlientow('',$_SESSION['domyslnyJezyk']['id']));

// select z panstwami
$srodek->dodaj('__WYBOR_PANSTWA', Funkcje::RozwijaneMenu('panstwo',Klient::ListaPanstw(), $_SESSION['krajDostawy']['id'], 'id="selection" style="width:80%"'));

// select z panstwami do platnosci
$srodek->dodaj('__WYBOR_PANSTWA_FIRMA', Funkcje::RozwijaneMenu('panstwoFaktura',Klient::ListaPanstw(), $_SESSION['krajDostawy']['id'], 'id="selectionFirma" style="width:80%"'));

// select z wojwodztwami
$srodek->dodaj('__WYBOR_WOJEWODZTWA', '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', Klient::ListaWojewodztw($_SESSION['krajDostawy']['id']), '', ' style="width:80%"').'</span>');

// select z wojwodztwami do platnosci
$srodek->dodaj('__WYBOR_WOJEWODZTWA_FIRMA', '<span id="selectionresultFirma">'.Funkcje::RozwijaneMenu('wojewodztwoFirma', Klient::ListaWojewodztw($_SESSION['krajDostawy']['id']), '', ' style="width:80%"').'</span>');

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

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

include('koniec.php');
    
?>