<?php

// plik
$WywolanyPlik = 'recenzje';

include('start.php');

$LinkDoPrzenoszenia = Seo::link_SEO('recenzje.php', '', 'inna');

// *****************************
// jezeli byla zmiana sortowania
if (isset($_POST['sortowanie']) && (int)$_POST['sortowanie'] > 0) {
    $_SESSION['sortowanie_recenzja'] = (int)$_POST['sortowanie'];
}
// jezeli jest zmiana ilosci recenzji na stronie
if (isset($_POST['ilosc_na_stronie']) && (int)$_POST['ilosc_na_stronie'] > 0) {
    $_SESSION['listing_produktow'] = (int)$_POST['ilosc_na_stronie'];
}
// *****************************


// *****************************
// jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
if (isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
    unset($WywolanyPlik);
    //
    Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia);
}    
// *****************************  

//
// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));
//

// style css
$tpl->dodaj('__CSS_PLIK', ',listingi');

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_RECENZJE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// klasa css dla ilosci recenzji na stronie
for ($k = 1; $k <= 3; $k++) {
    $srodek->dodaj('__CSS_PRODSTR_' . $k, '');
    $srodek->dodaj('__LISTA_ILOSC_PROD_' . $k, LISTING_PRODUKTOW_NA_STRONIE * $k);     
}
$srodek->dodaj('__CSS_PRODSTR_' . ( $_SESSION['listing_produktow'] / LISTING_PRODUKTOW_NA_STRONIE ), 'class="Tak"');

$TablicaSortowania = array( '1' => 'r.date_added desc, pd.products_name',
                            '2' => 'r.date_added asc, pd.products_name',
                            '3' => 'pd.products_name asc',
                            '4' => 'products_name desc' );

// klasa css dla aktualnego sortowania i dodawanie do zapytania sortowania
for ($k = 1; $k <= 6; $k++) {
    $srodek->dodaj('__CSS_SORT_' . $k, ''); 
}
if (isset($_SESSION['sortowanie_recenzja'])) {
    $Sortowanie = $TablicaSortowania[(int)$_SESSION['sortowanie_recenzja']];
    $srodek->dodaj('__CSS_SORT_' . $_SESSION['sortowanie_recenzja'], 'class="Tak"');
  } else {
    $Sortowanie = $TablicaSortowania[1];    
    $srodek->dodaj('__CSS_SORT_1', 'class="Tak"');
} 

$zapytanie = Produkty::SqlRecenzje($Sortowanie);

$sql = $GLOBALS['db']->open_query( $zapytanie );

// stronicowanie
$srodek->dodaj('__STRONICOWANIE', '');
//
$IloscRecenzji = (int)$GLOBALS['db']->ile_rekordow($sql);
if ($IloscRecenzji > 0) { 
    //
    $Strony = Stronicowanie::PokazStrony($sql, $LinkDoPrzenoszenia);
    $LinkiDoStron = $Strony[0];
    $LimitSql = $Strony[1];
    //
    $srodek->dodaj('__STRONICOWANIE', $LinkiDoStron);
    //
    // zabezpieczenie zeby nie mozna bylo wyswietlic wiecej niz ilosc na stronie x 3
    if ( $_SESSION['listing_produktow'] > LISTING_PRODUKTOW_NA_STRONIE * 3 ) {
         $_SESSION['listing_produktow'] = LISTING_PRODUKTOW_NA_STRONIE * 3;
    }
    //
    $zapytanie = $zapytanie . " LIMIT " . $LimitSql . "," . $_SESSION['listing_produktow'];
    $GLOBALS['db']->close_query($sql);
    //            
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    unset($Strony, $LinkiDoStron, $LimitSql);
}
//

ob_start();

if (in_array( 'listing_recenzje.php', $Wyglad->PlikiListingiLokalne )) {
    require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_recenzje.php');
  } else {
    require('listingi/listing_recenzje.php');
}

$ListaRecenzji = ob_get_contents();
ob_end_clean();        

$srodek->dodaj('__LISTA_RECENZJI', $ListaRecenzji);   

$tpl->dodaj('__LINK_CANONICAL', '<link rel="canonical" href="' . ADRES_URL_SKLEPU . '/' . $LinkDoPrzenoszenia . '" />');

unset($LinkDoPrzenoszenia, $IloscRecenzji, $ListaRecenzji); 

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>