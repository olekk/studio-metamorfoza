<?php

// plik
$WywolanyPlik = 'listing';

include('start.php');

if (!isset($_GET['idkat']) && !isset($_GET['idproducent'])) {
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
}

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( '' );

// jezeli jest wywolana kategoria - szukanie danych kategorii
if (isset($_GET['idkat'])) {
    //
    $TabCPath = Kategorie::WyczyscPath($_GET['idkat']);
    $IdWyswietlanejKategorii = $TabCPath[ count($TabCPath) - 1 ];
    //
    // szukanie meta tagow do kategorii
    $zapytanie = "SELECT c.categories_id,
                         c.categories_status,
                         c.categories_image,
                         c.parent_id,
                         cd.categories_name,
                         cd.categories_description,
                         cd.categories_meta_title_tag,
                         cd.categories_meta_desc_tag,
                         cd.categories_meta_keywords_tag
                    FROM categories c, categories_description cd
                   WHERE c.categories_id = cd.categories_id AND
                         cd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' AND
                         c.categories_id = '" . $IdWyswietlanejKategorii . "'";
                         
    $sql = $GLOBALS['db']->open_query($zapytanie);                         
    //
    $info = $sql->fetch_assoc();
    $id = (int)$info['categories_status'];
    //
    // sprawdzenie linku SEO z linkiem w przegladarce
    //
    $SciezkaKategorii = $info['categories_id'];
    if ( $info['parent_id'] > 0 ) {
         $SciezkaKategorii = Kategorie::SciezkaKategoriiId($info['categories_id']);
    }
    //    
    Seo::link_Spr(Seo::link_SEO($info['categories_name'], $SciezkaKategorii, 'kategoria'));
    //
    unset($SciezkaKategorii);    
}
    
// jezeli jest wywolany producent - szukanie danych producenta
if (isset($_GET['idproducent'])) {
    //
    // szukanie meta tagow do producenta
    $zapytanie = "SELECT *
                    FROM manufacturers m, manufacturers_info mi
                   WHERE m.manufacturers_id = mi.manufacturers_id AND 
                         mi.languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' AND
                         m.manufacturers_id = '" . (int)$_GET['idproducent'] . "'";
                         
    $sql = $GLOBALS['db']->open_query($zapytanie); 
    //
    $info = $sql->fetch_assoc();
    $id = (int)$info['manufacturers_id'];
    //
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr(Seo::link_SEO($info['manufacturers_name'], $info['manufacturers_id'], 'producent'));
    //    
}
    
if ( $id == 0 || (int)$GLOBALS['db']->ile_rekordow($sql) == 0 ) {
    //
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie, $info);

    if (isset($_GET['idkat'])) {
        Funkcje::PrzekierowanieURL('brak-kategorii.html'); 
    }
    if (isset($_GET['idproducent'])) {
        Funkcje::PrzekierowanieURL('brak-producenta.html'); 
    }    
    //
    
} else {

    // jezeli jest wywolana kategoria - szukanie danych kategorii
    if (isset($_GET['idkat'])) {
        //                        

        $LinkDoPrzenoszenia = Seo::link_SEO($info['categories_name'], implode('_', $TabCPath), 'kategoria');
        
        // *****************************
        // jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
        if (isset($_POST['wyswietlanie']) || isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
            $GLOBALS['db']->close_query($sql); 
            unset($info, $WywolanyPlik, $Meta, $IdWyswietlanejKategorii, $srodek, $zapytanie);
            //
            Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia . Funkcje::Zwroc_Get(array('s','idkat','idproducent'), false, '/'));
        }    
        // ***************************** 

        include('listing_gora.php');

        // meta tagi
        $tpl->dodaj('__META_TYTUL', ((empty($info['categories_meta_title_tag'])) ? $Meta['tytul'] : $info['categories_meta_title_tag']));
        $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($info['categories_meta_keywords_tag'])) ? $Meta['slowa'] : $info['categories_meta_keywords_tag']));
        $tpl->dodaj('__META_OPIS', ((empty($info['categories_meta_desc_tag'])) ? $Meta['opis'] : $info['categories_meta_desc_tag']));
        unset($Meta); 

        // Breadcrumb dla kategorii produktow
        if ( isset($_GET['idkat']) && $_GET['idkat'] != '' ) {
            //
            $tablica_kategorii = explode('_',$_GET['idkat']); 
            //
            for ( $i = 0, $n = count($tablica_kategorii); $i < $n; $i++ ) {
                if ( isset($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat']) ) {
                    //
                    $SciezkaKategorii = $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat'];
                    if ( $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Parent'] > 0 ) {
                         $SciezkaKategorii = Kategorie::SciezkaKategoriiId($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['IdKat']);
                    }
                    //
                    if ( $GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Widocznosc'] == '1' ) {
                         $nawigacja->dodaj($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Nazwa'], Seo::link_SEO($GLOBALS['tablicaKategorii'][$tablica_kategorii[$i]]['Nazwa'], $SciezkaKategorii, 'kategoria'));
                    }
                    //
                    unset($SciezkaKategorii);
                }
            }
            unset($tablica_kategorii);
            //
            $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
            //
        }
        
        //
        $srodek->dodaj('__NAGLOWEK_LISTINGU', $info['categories_name']);
        $srodek->dodaj('__OPIS_LISTINGU', ((strlen($info['categories_description']) > 10) ? $info['categories_description'] . '<br />&nbsp;' : ''));
        //
        $srodek->dodaj('__ZDJECIE_LISTINGU', '');
        if (strlen($info['categories_description']) > 10 && $info['categories_image'] != '') {
            $srodek->dodaj('__ZDJECIE_LISTINGU', Funkcje::pokazObrazek($info['categories_image'], $info['categories_name'], SZEROKOSC_OBRAZEK_MALY, WYSOKOSC_OBRAZEK_MALY, array(), 'class="ZdjecieListing"'));
        }
        //
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie, $info); 
        
        // podkategorie lista - do wyswietlenia podkategorii
        $PodkatLista = '';
        
        //    
        // podkategorie dla kategorii - wyswietli wszystkie produkty z kategorii razem z produktami z podkategorii
        $IdPodkategorii = $IdWyswietlanejKategorii . ',';
        //        
        $TablicaPodkategorii = array();
        foreach(Kategorie::DrzewoKategorii($IdWyswietlanejKategorii) as $IdKategorii => $Tablica) {
            //
            $IdPodkategorii .= Kategorie::TablicaPodkategorie($Tablica);
            //
            if ( $Tablica['Parent'] == $IdWyswietlanejKategorii ) {
                $TablicaPodkategorii[] = $Tablica;
            }
            //
        }
        
        //
        $SumaPodkategorii = 0;
        $LicznikPodkategorii = 0;
        //
        foreach ( $TablicaPodkategorii as $Tablica ) {
        
            // szerokosc podkategorii
            $SzerokoscPodkategorii = '';
            
            if ( $_SESSION['mobile'] == 'nie' ) {
                 $SzerokoscPodkategorii = ' style="width:' . (int)(100 / LISTING_PODKATEGORIE_KOLUMNY) . '%"';
            }
            if ( $_SESSION['rwd'] == 'tak' ) {
                 $SzerokoscPodkategorii = ' class="OknoRwd"';
            }
            
            $PodkatLista .= '<li' . $SzerokoscPodkategorii . '>';
            
            unset($SzerokoscPodkategorii);
            //
            $PodkatLista .= '<h2><a href="' . Seo::link_SEO($Tablica['Nazwa'], Kategorie::SciezkaKategoriiId( $Tablica['IdKat'] ), 'kategoria') . '">';
            //
            if (LISTING_PODKATEGORIE_ZDJECIA == 'tak') {
                $PodkatLista .= Funkcje::pokazObrazek($Tablica['Foto'], $Tablica['Nazwa'], SZEROKOSC_MINIATUREK_PODKATEGORII, WYSOKOSC_MINIATUREK_PODKATEGORII, array(), '', 'maly', true, false, false) . '<br />';
            }
            //
            $PodkatLista .= '<span>' . $Tablica['Nazwa'] . '</span>';
            //
            if (LISTING_ILOSC_PRODUKTOW == 'tak') {
                $PodkatLista .= '<em>('.$Tablica['WszystkichProduktow'] . ')</em>';
            }            
            //
            $PodkatLista .= '</a></h2></li>';
            
            $SumaPodkategorii++;
            $LicznikPodkategorii++;
            
            if ( $SumaPodkategorii == LISTING_PODKATEGORIE_KOLUMNY && $LicznikPodkategorii < count($TablicaPodkategorii) && $_SESSION['mobile'] == 'nie' && $_SESSION['rwd'] == 'nie' ) {
                 $PodkatLista .= '</ul><ul' . ((LISTING_PODKATEGORIE_ZDJECIA == 'tak') ? ' class="KategoriaZdjecie"' : ' class="KategoriaBezZdjecia"') . '>';
                 $SumaPodkategorii = 0;
            }
        }
        //
        unset($TablicaPodkategorii, $SumaPodkategorii, $LicznikPodkategorii);
        
        $srodek->dodaj('__PODKATEGORIE', '');
        
        if ( LISTING_PODKATEGORIE == 'tak' ) {
            
            // jezeli sa podkategorie
            if ( !empty($PodkatLista) ) {
                //
                // css dla rwd
                $cssRwd = '';
                if ( $_SESSION['rwd'] == 'tak' ) {
                     $cssRwd = ' OknaRwd Kol-' . LISTING_PODKATEGORIE_KOLUMNY;
                }
                //
                $PodkatLista = '<ul' . ((LISTING_PODKATEGORIE_ZDJECIA == 'tak') ? ' class="KategoriaZdjecie' . $cssRwd . '"' : ' class="KategoriaBezZdjecia' . $cssRwd . '"') . '>' . $PodkatLista . '</ul>';
                $srodek->dodaj('__PODKATEGORIE', '<strong>' . $GLOBALS['tlumacz']['LISTING_PODKATEGORIE'] . '</strong>' . $PodkatLista);
            }
        
        }
        
        unset($PodkatLista);
        
        $IdPodkategorii = substr($IdPodkategorii, 0, -1);
        //    
        $zapytanie = Produkty::SqlProduktyKategorii($IdPodkategorii, $WarunkiFiltrowania, $Sortowanie);                            
        //
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //

        // filtr nowosci
        if (POKAZUJ_FILTRY_NOWOSCI == 'tak') {
            $srodek->dodaj('__FILTRY_NOWOSCI', Filtry::FiltrNowosciSelect());
        } else {
            $srodek->dodaj('__FILTRY_NOWOSCI', '');
        }
        
        // filtr promocji
        if (POKAZUJ_FILTRY_PROMOCJE == 'tak') {
            $srodek->dodaj('__FILTRY_PROMOCJE', Filtry::FiltrPromocjeSelect());        
        } else {
            $srodek->dodaj('__FILTRY_PROMOCJE', '');
        }
        
        // filtry cech
        if (POKAZUJ_FILTRY_CECH == 'tak') {
            $srodek->dodaj('__FILTRY_PO_CECHACH', Filtry::FiltrSelect( Filtry::FiltrCech($IdPodkategorii, 'kategoria'), 'c' ));
        } else {
            $srodek->dodaj('__FILTRY_PO_CECHACH', '');
        }
        
        // filtry dodatkowych pol
        if (POKAZUJ_FILTRY_DODATKOWE_POLA == 'tak') {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', Filtry::FiltrSelect( Filtry::FiltrDodatkowePola($IdPodkategorii, 'kategoria'), 'p' ));        
        } else {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', ''); 
        }
        
        // filtr producenta
        if (POKAZUJ_FILTRY_PRODUCENCI == 'tak') {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', Filtry::FiltrProducentaSelect($IdPodkategorii) );
        } else {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', '');
        }
        
        unset($IdPodkategorii);

    }

    // jezeli jest wywolany producent - szukanie danych producenta
    if (isset($_GET['idproducent'])) {
        //                            
        
        $LinkDoPrzenoszenia = Seo::link_SEO($info['manufacturers_name'], (int)$_GET['idproducent'], 'producent');

        // *****************************
        // jezeli byla zmiana sposobu wyswietlania, sortowanie lub zmiana ilosci produktow na stronie - musi przeladowac strone
        if (isset($_POST['wyswietlanie']) || isset($_POST['sortowanie']) || isset($_POST['ilosc_na_stronie'])) {
            $GLOBALS['db']->close_query($sql); 
            unset($WywolanyPlik, $Meta, $IdWyswietlanejKategorii, $srodek, $zapytanie);
            //
            Funkcje::PrzekierowanieURL($LinkDoPrzenoszenia . Funkcje::Zwroc_Get(array('s','idkat','idproducent'), false, '/'));
        }    
        // *****************************  

        include('listing_gora.php');

        // meta tagi
        $tpl->dodaj('__META_TYTUL', ((empty($info['manufacturers_meta_title_tag'])) ? $Meta['tytul'] : $info['manufacturers_meta_title_tag']));
        $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($info['manufacturers_meta_keywords_tag'])) ? $Meta['slowa'] : $info['manufacturers_meta_keywords_tag']));
        $tpl->dodaj('__META_OPIS', ((empty($info['manufacturers_meta_desc_tag'])) ? $Meta['opis'] : $info['manufacturers_meta_desc_tag']));
        unset($Meta); 
        
        // Breadcrumb dla producenta
        $nawigacja->dodaj($info['manufacturers_name'], Seo::link_SEO($info['manufacturers_name'], (int)$_GET['idproducent'], 'producent'));
        $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

        //
        $srodek->dodaj('__NAGLOWEK_LISTINGU', $info['manufacturers_name']);
        $srodek->dodaj('__OPIS_LISTINGU', (strlen($info['manufacturers_description']) > 10 ? $info['manufacturers_description'] . '<br />&nbsp;' : ''));
        $srodek->dodaj('__ZDJECIE_LISTINGU', '');
        if (strlen($info['manufacturers_description']) > 10 && $info['manufacturers_image'] != '') {
            $srodek->dodaj('__ZDJECIE_LISTINGU', Funkcje::pokazObrazek($info['manufacturers_image'], $info['manufacturers_name'], SZEROKOSC_OBRAZEK_MALY, WYSOKOSC_OBRAZEK_MALY, array(), 'class="ZdjecieListing"', 'maly', true, false, false));
        }
        //
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie, $info); 
        //  
        $zapytanie = Produkty::SqlProduktyProducenta((int)$_GET['idproducent'], $WarunkiFiltrowania, $Sortowanie);                           
        //
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        
        $srodek->dodaj('__PODKATEGORIE', '');

        // filtr nowosci
        if (POKAZUJ_FILTRY_NOWOSCI == 'tak') {
            $srodek->dodaj('__FILTRY_NOWOSCI', Filtry::FiltrNowosciSelect());
        } else {
            $srodek->dodaj('__FILTRY_NOWOSCI', '');
        }    
        
        // filtr promocji
        if (POKAZUJ_FILTRY_PROMOCJE == 'tak') {
            $srodek->dodaj('__FILTRY_PROMOCJE', Filtry::FiltrPromocjeSelect());        
        } else {
            $srodek->dodaj('__FILTRY_PROMOCJE', '');
        }
        
        // filtry cech
        if (POKAZUJ_FILTRY_CECH == 'tak') {
            $srodek->dodaj('__FILTRY_PO_CECHACH', Filtry::FiltrSelect( Filtry::FiltrCech((int)$_GET['idproducent'], 'producent'), 'c' ));
        } else {
            $srodek->dodaj('__FILTRY_PO_CECHACH', '');
        }
        
        // filtry dodatkowych pol
        if (POKAZUJ_FILTRY_DODATKOWE_POLA == 'tak') {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', Filtry::FiltrSelect( Filtry::FiltrDodatkowePola((int)$_GET['idproducent'], 'producent'), 'p' ));    
        } else {
            $srodek->dodaj('__FILTRY_PO_DODATKOWYCH_POLACH', ''); 
        }
        
        // filtr kategorii
        if (POKAZUJ_FILTRY_KATEGORIE == 'tak') {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', Filtry::FiltrKategoriiSelect((int)$_GET['idproducent']));
        } else {
            $srodek->dodaj('__FILTRY_PRODUCENT_KATEGORIA', '');
        }

    }

    include('listing_dol.php');
    
}    

unset($id);

include('koniec.php');

?>