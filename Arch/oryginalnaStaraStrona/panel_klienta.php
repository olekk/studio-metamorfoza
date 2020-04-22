<?php

// plik
$WywolanyPlik = 'panel_klienta';

include('start.php');

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'PUNKTY') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), (int)$_SESSION['customer_id']);
    
    $srodek->dodaj('__INFO_SUMOWANIE_RABATOW',  str_replace('{SKLADNIA}', ((RABAT_SUMOWANIE == 'tak') ? '' : '<b>nie</b>'), $GLOBALS['tlumacz']['INFO_SUMOWANIE_RABATOW']));
    
    $srodek->dodaj('__INFO_MAKSYMALNA_WARTOSC_RABATOW', $GLOBALS['tlumacz']['INFO_MAKSYMALNA_WARTOSC_RABATOW'] . ' <b>' . RABAT_MAKSYMALNA_WARTOSC . '%</b>');
    
    $srodek->dodaj('__INFO_PRODUKTY_PROMOCYJNE_RABATY',  str_replace('{SKLADNIA}', ((RABATY_PROMOCJE == 'tak') ? '' : '<b>nie</b>'), $GLOBALS['tlumacz']['INFO_PRODUKTY_PROMOCYJNE_RABATY']));
    
    $srodek->dodaj('__ILOSC_WEJSC_BANNERY', ((isset($_SESSION['pp_statystyka'])) ? $_SESSION['pp_statystyka'] : 0));
    
    $srodek->dodaj('__NAZWA_GRUPY_KLIENTA', ((isset($_SESSION['customers_groups_name'])) ? $_SESSION['customers_groups_name'] : ''));
    
    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik);

    include('koniec.php');

} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );
    
}
?>