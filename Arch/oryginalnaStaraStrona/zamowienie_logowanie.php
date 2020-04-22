<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'zamowienie_logowanie';

include('start.php');

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() == 0 || (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0) ) {

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

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZAMOWIENIE_LOGOWANIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

// select z panstwami
$srodek->dodaj('__WYBOR_PANSTWA', Funkcje::RozwijaneMenu('panstwo',Klient::ListaPanstw(), $_SESSION['krajDostawy']['id'], 'id="selection" style="width:80%"'));

// select z wojwodztwami
$srodek->dodaj('__WYBOR_WOJEWODZTWA', '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', Klient::ListaWojewodztw($_SESSION['krajDostawy']['id']), '', ' style="width:80%"').'</span>');

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>