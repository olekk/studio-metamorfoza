<?php

// plik
$WywolanyPlik = 'szukaj';

include('start.php');

$LinkDoPrzenoszenia = Seo::link_SEO('szukaj.php', '', 'inna');

// *****************************
// jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
if (isset($_POST['wyswietlanie']) || isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
    Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia . Funkcje::Zwroc_Get(array('s'), false, '/'));
}    
// *****************************   

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('WYSZUKIWANIE_ZAAWANSOWANE') ), $GLOBALS['tlumacz'] );

include('listing_gora.php');

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta); 

$WarunkiSzukania = '';
$WarunkiDoWyswietlenia = '';
$DodatkowePolaId = '';
$dodatkowePolaWyrazenia = array();

// dodatkowe pola do produktow w wyszukiwaniu
foreach ($_GET as $klucz => $wartosc) {
    if ( strpos($klucz, 'dodatkowe' ) !== false ) {
        $DodatkowePolaId .= substr($klucz, strrpos($klucz, '_')+1) . ',';
        $dodatkowePolaWyrazenia[substr($klucz, strrpos($klucz, '_')+1)] = $wartosc;
    }
}
if ( $DodatkowePolaId != '' ) {
    $DodatkowePolaId = substr($DodatkowePolaId, 0, -1);
}

// wyszukiwana fraza
$srodek->dodaj('__SZUKANA_FRAZA','');
if (isset($_GET['szukaj']) && trim($_GET['szukaj']) != '') {

    if ( !isset($_GET['fraza']) ) {
        $_GET['fraza'] = 'nie';
    }
    if ( !isset($_GET['opis']) ) {
        $_GET['opis'] = 'nie';
    }
    if ( !isset($_GET['nrkat']) ) {
        $_GET['nrkat'] = 'nie';
    }

    // ustawienie parametrow jezeli klikniety link w chmurze tagow
    if ( strpos($_SERVER['QUERY_STRING'], 'szukaj=') !== false ) {
        $_GET['fraza'] = 'tak';
        $_GET['opis'] = 'tak';
        $_GET['nrkat'] = 'tak';
    }

    //
    $_GET['szukaj'] = strip_tags( rawurldecode($filtr->process($_GET['szukaj'])) );    
    //
    
    // zamienia zmienne na poprawne znaki
    $_GET['szukaj'] = str_replace(array('[back]', '[proc]'), array('/', '%'), $_GET['szukaj']);
    
    // dodaje fraze do szablonu
    $srodek->dodaj('__SZUKANA_FRAZA', $_GET['szukaj']);
    
    // zabezpieczenie przez hackiem
    $_GET['szukaj'] = str_replace(array('_', '%'), array('\\_','\\%'), $_GET['szukaj']);
    
    // usuwanie '
    // sprawdza czy jest wylaczone magic_quotes_gpc
    if (!get_magic_quotes_gpc()) {
        $_GET['szukaj'] = str_replace("'", "\'", $_GET['szukaj']);
    }
    
    $PoprawnaDlugosc = false;
    $wyrazy = explode(' ', $_GET['szukaj']);
    if ( count($wyrazy) > 0 ) {
        for ($i = 0, $n = sizeof($wyrazy); $i < $n; $i++ ) {
            if ( strlen ($wyrazy[$i]) > 1 ) {
                $PoprawnaDlugosc = true;
            }
        }
    }

    if ( $_GET['fraza'] == 'nie' && $PoprawnaDlugosc == true ) {
        // wyszukiwanie wszystkich wyrazow w ciagu
        if ( count($wyrazy) > 0 ) {
            $WarunkiSzukania .= " AND ( (";
            for ($i = 0, $n = sizeof($wyrazy); $i < $n; $i++ ) {
                if ( strlen ($wyrazy[$i]) > 1 ) {
                    $WarunkiSzukania .= " pd.products_name like '%" . $wyrazy[$i] . "%' AND";
                }
            }
            $WarunkiSzukania = preg_replace('/\W\w+\s*(\W*)$/', '$1', $WarunkiSzukania);
            $WarunkiSzukania .= " ) ";
            $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_FRAZY'] . '</span> <b>' . $GLOBALS['tlumacz']['NIE'] . '</b></p>';
        }
    } else {
        // wyszukiwanie wpianej frazy
        $WarunkiSzukania .= " AND (pd.products_name like '%" . $_GET['szukaj'] . "%'";
        $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_FRAZY'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
    }


    //
    // jezeli jest dodatkowo szukanie po opisach
    if (isset($_GET['opis']) && $_GET['opis'] == 'tak') {
        
        if ( $_GET['fraza'] == 'nie' && $PoprawnaDlugosc == true  ) {
            //Wyszukiwanie wszystkich wyrazow w ciagu
            if ( count($wyrazy) > 0 ) {
                $WarunkiSzukania .= " OR ( ";
                for ($i = 0, $n = sizeof($wyrazy); $i < $n; $i++ ) {
                    if ( strlen ($wyrazy[$i]) > 1 ) {
                        $WarunkiSzukania .= " pd.products_description like '%" . $wyrazy[$i] . "%' AND";
                    }
                }
                $WarunkiSzukania = preg_replace('/\W\w+\s*(\W*)$/', '$1', $WarunkiSzukania);
                $WarunkiSzukania .= " ) ";
            }
        } else {
            //Wyszukiwanie wpianej frazy
            $WarunkiSzukania .= " OR pd.products_description like '%" . $_GET['szukaj'] . "%'";
        }
        
        $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_W_OPISACH'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
    }
    unset($wyrazy);

    // jezeli jest dodatkowo szukanie po numerach katalogowych
    if (isset($_GET['nrkat']) && $_GET['nrkat'] == 'tak') {
        $WarunkiSzukania .= " OR p.products_model like '%" . $_GET['szukaj'] . "%'";
        // nr katalogowe cech
        $WarunkiSzukania .= " OR ps.products_stock_model like '%" . $_GET['szukaj'] . "%' ";
        //
        $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_NR_KATALOGOWY'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
    }    
    // jezeli jest dodatkowo szukanie po kodzie producenta
    if (isset($_GET['kodprod']) && $_GET['kodprod'] == 'tak') {
        $WarunkiSzukania .= " OR p.products_man_code like '%" . $_GET['szukaj'] . "%'";
        //
        $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_KOD_PRODUCENTA'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
    }  
    // jezeli jest dodatkowo szukanie po kodzie ean
    if (isset($_GET['ean']) && $_GET['ean'] == 'tak') {
        $WarunkiSzukania .= " OR p.products_ean like '%" . $_GET['szukaj'] . "%'";
        //
        $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_W_EAN'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
    }       
    //

    // jezeli jest dodatkowo szukanie po dodatkowych polach
    if ( count($dodatkowePolaWyrazenia) > 0 ) {
        $wyrazy = explode(' ', $_GET['szukaj']);
        if ( count($wyrazy) > 0 ) {
            $WarunkiSzukania .= " OR ( ";
            for ($i = 0, $n = sizeof($wyrazy); $i < $n; $i++ ) {
                if ( strlen ($wyrazy[$i]) > 1 ) {
                    $WarunkiSzukania .= " p2pef.products_extra_fields_value like '%" . $wyrazy[$i] . "%' AND";
                }
            }
            $WarunkiSzukania = preg_replace('/\W\w+\s*(\W*)$/', '$1', $WarunkiSzukania);
        }
        $WarunkiSzukania .= " ) ";
        unset($wyrazy);

        $DodatkowePola = array();
        $zapytanie_pef = "SELECT products_extra_fields_id, products_extra_fields_name FROM products_extra_fields WHERE products_extra_fields_status ='1' AND products_extra_fields_id IN (".$DodatkowePolaId.") ORDER BY products_extra_fields_order";

        $sqlpef = $GLOBALS['db']->open_query($zapytanie_pef);

        while ($infopef = $sqlpef->fetch_assoc()) {
            $WarunkiDoWyswietlenia .= '<p><span>'.$infopef['products_extra_fields_name'].':</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
        }
    }


    $WarunkiSzukania .= ")";
    //
  } else {
    $WarunkiSzukania = " AND p.products_id = '0'";
}
//
// tylko promocje
if (isset($_GET['promocje']) && $_GET['promocje'] == 'tak') {
    $WarunkiSzukania .= " AND p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
    $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_PROMOCJE'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
}
// tylko nowosci
if (isset($_GET['nowosci']) && $_GET['nowosci'] == 'tak') {
    $WarunkiSzukania .= " AND p.new_status = '1'";
    $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_NOWOSCI'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
}
// zakres cenowy
//
if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
     $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
   } else {
     $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
}
// 
$CenaOd = '0';
if (isset($_GET['ceno']) && (float)$_GET['ceno'] > 0) {
    $WarunkiSzukania .= " AND " . $DodWarunekCen . " >= " . (float)$_GET['ceno'];
    $CenaOd = (float)$_GET['ceno'];
}
$CenaDo = '';
if (isset($_GET['cend']) && (float)$_GET['cend']) {
    $WarunkiSzukania .= " AND " . $DodWarunekCen . " <= " . (float)$_GET['cend'];
    $CenaDo = ' ' . $GLOBALS['tlumacz']['LISTING_ZAKRES_CEN_DO'] . ' ' . (float)$_GET['cend'] . ' ' . $_SESSION['domyslnaWaluta']['symbol'];
}
unset($DodWarunekCen);
//
if (isset($_GET['ceno']) || isset($_GET['cend'])) {
    $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_ZAKRES_CEN'] . '</span> <b>' . $CenaOd . ' ' . $_SESSION['domyslnaWaluta']['symbol'] . $CenaDo . '</b></p>';
}
unset($CenaOd, $CenaDo);
// producent
if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
    $WarunkiSzukania .= " AND p.manufacturers_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['producent'])) . ")";
    //
    $TablicaProducenta = Producenci::NazwaProducenta((int)$_GET['producent']);
    $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_PRODUCENT'] . '</span> <b>' . $TablicaProducenta['nazwa'] . '</b></p>';
    unset($TablicaProducenta);
}
// kategoria
if (isset($_GET['kategoria']) && (int)$_GET['kategoria'] > 0) {
    //
    $IdPodkategorii = (int)$_GET['kategoria'] . ',';
    //    
    // musi znalezc podkategorie dla danej kategorii
    if (isset($_GET['podkat']) && $_GET['podkat'] == 'tak') {
        //
        foreach(Kategorie::DrzewoKategorii((int)$_GET['kategoria']) as $IdKategorii => $Tablica) {
            $IdPodkategorii .= Kategorie::TablicaPodkategorie($Tablica);
        }               
    }    
    //
    $IdPodkategorii = substr($IdPodkategorii, 0, -1);        
    //     
    $WarunkiSzukania .= " AND c.categories_id in (" . $IdPodkategorii . ")";
    //
    $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_KATEGORIA'] . '</span> <b>' . Kategorie::NazwaKategoriiId((int)$_GET['kategoria']) . '</b></p>';
    //
    if (isset($_GET['podkat']) && $_GET['podkat'] == 'tak') {
       $WarunkiDoWyswietlenia .= '<p><span>' . $GLOBALS['tlumacz']['WYSZUKIWANIE_W_PODKATEGORIACH'] . '</span> <b>' . $GLOBALS['tlumacz']['TAK'] . '</b></p>';
    }
    unset($IdPodkategorii);
}

//
$zapytanie = Produkty::SqlSzukajProdukty( $WarunkiSzukania, $Sortowanie, $DodatkowePolaId );                               
//
$sql = $GLOBALS['db']->open_query($zapytanie);

if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
    //
    // aktualizuje raport wyszukiwane frazy
    $zapytanieRaport = "SELECT search_id, search_key, freq FROM customers_searches where search_key = '" . $_GET['szukaj'] . "' AND language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";
    $sqlRaport = $GLOBALS['db']->open_query($zapytanieRaport);
    
    if ((int)$GLOBALS['db']->ile_rekordow($sqlRaport) > 0 ) {
        //
        $infoRaport = $sqlRaport->fetch_assoc();
        $pola = array(array('freq',$infoRaport['freq'] + 1));        
        //
        $GLOBALS['db']->update_query('customers_searches' , $pola, " search_id  = '" . $infoRaport['search_id'] . "'");	
        unset($pola);         
        //
    } else {
        //
        $pola = array(array('search_key',$_GET['szukaj']),
                      array('freq','1'),
                      array('language_id',$_SESSION['domyslnyJezyk']['id']));

        $db->insert_query('customers_searches', $pola);
        unset($pola);        
        //
    }
    $GLOBALS['db']->close_query($sqlRaport);
    unset($zapytanieRaport, $infoRaport);
    //
}
    
//
// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_WYNIKI_SZUKANIA']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$srodek->dodaj('__ILOSC_WYNIKOW_WYSZUKIWANIA',(int)$GLOBALS['db']->ile_rekordow($sql));
$srodek->dodaj('__INNE_WARUNKI_SZUKANIA',$WarunkiDoWyswietlenia);
unset($WarunkiDoWyswietlenia);
//

// usuwanie zbednych get - jezeli jest na nie to nie sa potrzebne w linku do dalszych stron w wynikach wyszukiwania
$niepotrzebneWartosci = array('fraza', 'opis', 'nrkat');
foreach ($_GET as $klucz => $wartosc) {
    if ( in_array($klucz, $niepotrzebneWartosci ) ) {
        //
        if ( $wartosc == 'nie' ) {
             unset($_GET[$klucz]);
        }
        //
    }
}
unset($niepotrzebneWartosci);

include('listing_dol.php');

include('koniec.php');

?>