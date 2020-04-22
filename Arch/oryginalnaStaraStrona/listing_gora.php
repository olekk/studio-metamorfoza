<?php

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));  

// wspolne stale
$srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);

// style css
$tpl->dodaj('__CSS_PLIK', ',listingi');

//
$SposobWyswietlania = 1;

// klasa css dla aktywnej formy wyswietlania 
for ($k = 1; $k <= 3; $k++) {
    $srodek->dodaj('__CSS_WYGLAD_' . $k, ''); 
}

if ( $_SESSION['mobile'] != 'tak' ) {

    if (isset($_SESSION['wyswietlanie'])) {
        $srodek->dodaj('__CSS_WYGLAD_' . $_SESSION['wyswietlanie'], 'class="Tak"');
        $SposobWyswietlania = (int)$_SESSION['wyswietlanie'];
      } else {
        $srodek->dodaj('__CSS_WYGLAD_1', 'class="Tak"');
    }
    
}

$DomyslneDol = 'p.sort_order desc, pd.products_name';
$DomyslneGora = 'p.sort_order asc, pd.products_name';

// inne sortowanie dla nowosci
if ( NOWOSCI_SORTOWANIE == 'wg daty dodania' && $WywolanyPlik == 'nowosci' ) {
     //
     $DomyslneDol = 'p.products_date_added asc, pd.products_name';
     $DomyslneGora = 'p.products_date_added desc, pd.products_name';
     //
}

$TablicaSortowania = array( '1' => $DomyslneDol,
                            '2' => $DomyslneGora,
                            '3' => 'cena desc',
                            '4' => 'cena asc',
                            '5' => 'pd.products_name desc',
                            '6' => 'pd.products_name asc' );
                            
unset($DomyslneDol, $DomyslneGora);                           

// klasa css dla aktualnego sortowania i dodawanie do zapytania sortowania
for ($k = 1; $k <= 6; $k++) {
    $srodek->dodaj('__CSS_SORT_' . $k, ''); 
}
if (isset($_SESSION['sortowanie'])) {
    $Sortowanie = $TablicaSortowania[(int)$_SESSION['sortowanie']];
    $srodek->dodaj('__CSS_SORT_' . $_SESSION['sortowanie'], 'class="Tak"');
  } else {
    $Sortowanie = $TablicaSortowania[2];    
    $srodek->dodaj('__CSS_SORT_1', 'class="Tak"');
}  

// klasa css dla ilosci produktow na stronie
for ($k = 1; $k <= 3; $k++) {
    $srodek->dodaj('__CSS_PRODSTR_' . $k, '');
    $srodek->dodaj('__LISTA_ILOSC_PROD_' . $k, LISTING_PRODUKTOW_NA_STRONIE * $k);     
}
$srodek->dodaj('__CSS_PRODSTR_' . ( $_SESSION['listing_produktow'] / LISTING_PRODUKTOW_NA_STRONIE ), 'class="Tak"');

// *****************************
// opcje filtrowania do 
// zapytania sql
// *****************************

// dla wyszykiwania sa oddzielne warunki 
if (!isset($_GET['szukaj'])) {
    //
    // cechy produktu
    $WarunkiFiltrowania = '';
    //
    // okresli jaki jest max nr id cechy
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('CechyIlosc', CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $sqlIlosc = $GLOBALS['db']->open_query('select max(products_options_id) as nr_id from products_options');
        $infoIlosc = $sqlIlosc->fetch_assoc();
        //
        $GLOBALS['cache']->zapisz('CechyIlosc', $infoIlosc['nr_id'], CACHE_INNE);
        $IloscCech = $infoIlosc['nr_id'];
        //
        $GLOBALS['db']->close_query($sqlIlosc);
        unset($infoIlosc);  
        //     
      } else {
        //
        $IloscCech = $WynikCache;
        //
    }
    //
    unset($WynikCache);     
    //
    if ( (int)$IloscCech > 0 ) {
        //
        for ($p = 1; $p < $IloscCech + 1; $p++) {
            if (isset($_GET['c'.$p]) && Funkcje::czyNiePuste($_GET['c'.$p])) {  

              // jezeli jest magazyn cech to wyswietli tylko produkty ktore maja ta ceche w magazynie
              if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' ) {
              
                  $WarunkiFiltrowania .= " AND p.products_id in (SELECT distinct products_id FROM products_stock WHERE ";
                  
                  $WartosciFiltra = Filtry::WyczyscFiltr($_GET['c'.$p]);
                  foreach ( $WartosciFiltra as $WartFiltra ) {
                      //
                      $WarunkiFiltrowania .= " find_in_set('" . $p . "-" . (int)$WartFiltra . "', products_stock_attributes) or ";
                      //
                  }
                  unset($WartosciFiltra);

                  $WarunkiFiltrowania = substr($WarunkiFiltrowania, 0, -3) . " and products_stock_quantity > 0)";
              
                } else {
                
                  $WarunkiFiltrowania .= " AND p.products_id in (SELECT products_id FROM products_attributes WHERE options_id = '" . $p . "' AND options_values_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['c'.$p])) . ") )";
                
              }

              unset($Podziel);
            }  
        }
        //
    }
    unset($IloscCech);

    //
    // dodatkowe pola
    
    // okresli jaki jest max nr id dodatkowych pol
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePolaIlosc', CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $sqlIlosc = $GLOBALS['db']->open_query('select max(products_extra_fields_id) as nr_id from products_extra_fields');
        $infoIlosc = $sqlIlosc->fetch_assoc();
        //
        $GLOBALS['cache']->zapisz('DodatkowePolaIlosc', $infoIlosc['nr_id'], CACHE_INNE);
        $IloscDodPol = $infoIlosc['nr_id'];
        //
        $GLOBALS['db']->close_query($sqlIlosc);
        unset($infoIlosc);  
        //     
      } else {
        //
        $IloscDodPol = $WynikCache;
        //
    }
    //
    unset($WynikCache);     
    //    
    if ( (int)$IloscDodPol > 0 ) {
        //
        for ($p = 1; $p < $IloscDodPol + 1; $p++) {
            if (isset($_GET['p'.$p]) && Funkcje::czyNiePuste($_GET['p'.$p])) { 
              //
              // musi znalezc wartosc pola (value)
              // trzeba znalezc value
              $DoZapytania = '';
              //
              $pola_sql = $GLOBALS['db']->open_query("SELECT DISTINCT products_extra_fields_value FROM products_to_products_extra_fields WHERE products_id in (". implode(',', Filtry::WyczyscFiltr($_GET['p'.$p])) . ") and products_extra_fields_id = '" . $p . "'");                         
              while ($infoWartosc = $pola_sql->fetch_assoc()) {
                  $DoZapytania .= "'" . $infoWartosc['products_extra_fields_value'] . "',";
              }
              //
              $DoZapytania = substr($DoZapytania, 0, -1);
              //
              $WarunkiFiltrowania .= " AND p.products_id in (SELECT products_id FROM products_to_products_extra_fields WHERE products_extra_fields_id = '" . $p . "' AND products_extra_fields_value in (" . $DoZapytania . ") )";
              //
              $GLOBALS['db']->close_query($pola_sql); 
              unset($DoZapytania, $infoWartosc, $podziel, $pola_sql);
            }  
        }
        //
    }
    unset($IloscDodPol);
    //
    // tylko promocje
    if (isset($_GET['promocje']) && $_GET['promocje'] == 'tak') {
        $WarunkiFiltrowania .= " AND p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
    }
    // tylko nowosci
    if (isset($_GET['nowosci']) && $_GET['nowosci'] == 'tak') {
        $WarunkiFiltrowania .= " AND p.new_status = '1'";
    }
    // zakres cenowy
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }
    //    
    if (isset($_GET['ceno']) && (float)$_GET['ceno'] > 0) {

        $WarunkiFiltrowania .= " AND " . $DodWarunekCen . " >= " . (float)$_GET['ceno'];
        
    }
    if (isset($_GET['cend']) && (float)$_GET['cend'] > 0) {
        $WarunkiFiltrowania .= " AND " . $DodWarunekCen . " <= " . (float)$_GET['cend'];
    }
    //
    unset($DodWarunekCen);
    //
    // producent
    if (isset($_GET['producent'])) {
        $WarunkiFiltrowania .= " AND p.manufacturers_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['producent'])) . ")";
    }
    // kategoria
    if (isset($_GET['kategoria'])) {
        $WarunkiFiltrowania .= " AND c.categories_id in (" . implode(',', Filtry::WyczyscFiltr($_GET['kategoria'])) . ")";
    }
    //
}
// *****************************

// filtry wspolne
$srodek->dodaj('__CENA_OD_WARTOSC', ((isset($_GET['ceno']) && (float)$_GET['ceno'] > 0) ? (float)$_GET['ceno'] : ''));
$srodek->dodaj('__CENA_DO_WARTOSC', ((isset($_GET['cend']) && (float)$_GET['cend'] > 0) ? (float)$_GET['cend'] : ''));

// porownywanie produktow
$srodek->dodaj('__PRODUKTY_DO_POROWNANIA', '');
$srodek->dodaj('__CSS_POROWNANIE', 'style="display:none"');
$srodek->dodaj('__CSS_PRZYCISK_POROWNANIE', 'style="display:none"');
if ( count($_SESSION['produktyPorownania']) > 0 && LISTING_POROWNYWARKA_PRODUKTOW == 'tak' && $_SESSION['mobile'] == 'nie' ) {
    //
    $DoPorownaniaId = '';
    foreach ($_SESSION['produktyPorownania'] AS $Id) {
        $DoPorownaniaId .= $Id . ',';
    }
    $DoPorownaniaId = substr($DoPorownaniaId, 0, -1);
    //
    $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId);
    //
    $sqlNazwy = $GLOBALS['db']->open_query($zapNazwy);
    //
    $DoPorownaniaLinki = '';
    while ($infc = $sqlNazwy->fetch_assoc()) {
        //
        // ustala jaka ma byc tresc linku
        $linkSeo = ((!empty($infc['products_seo_url'])) ? $infc['products_seo_url'] : $infc['products_name']);
        //
        $DoPorownaniaLinki .= '<span onclick="Porownaj(' . $infc['products_id'] . ',\'wy\')"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a> <br />';
        //    
        unset($linkSeo);
        //
        // sprawdza czy produkt nie zostal wylaczony - jezeli tak usunie go z porownania
        if ( $infc['products_status'] == '0' ) {
             unset($_SESSION['produktyPorownania'][$infc['products_id']]);
             Funkcje::PrzekierowanieURL($_SERVER['REQUEST_URI']);
        }
        //
    }
    $GLOBALS['db']->close_query($sqlNazwy); 
    unset($zapNazwy, $DoPorownaniaId, $infc);      
    //
    $srodek->dodaj('__PRODUKTY_DO_POROWNANIA', $DoPorownaniaLinki);
    $srodek->dodaj('__CSS_POROWNANIE', '');
    //
    unset($DoPorownaniaLinki);
    //
    // jezeli jest wiecej niz 1 produkt do porownania to pokaze przycisk
    if (count($_SESSION['produktyPorownania']) > 1) {
        $srodek->dodaj('__CSS_PRZYCISK_POROWNANIE', 'style="display:block"');
    }
    //
}
?>