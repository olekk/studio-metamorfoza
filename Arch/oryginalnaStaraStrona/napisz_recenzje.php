<?php

// plik
$WywolanyPlik = 'napisz_recenzje';

include('start.php');

//po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {

        if ( isset($_SESSION['weryfikacja']) && $_SESSION['weryfikacja'] == $filtr->process($_POST['weryfikacja']) ) {
            //
            $Autor = $filtr->process($_POST['autor']);
            $Opinia = $filtr->process($_POST['opinia']);
            $Ocena = (int)$_POST['ocena'];
            $IdProduktu = (int)$_POST['id_produkt'];
            
            // jezeli klient jest zalogowany
            $IdRecenzenta = 0;
            if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
                $IdRecenzenta = $_SESSION['customer_id'];
            }
            
            if (!empty($Autor) && !empty($Opinia) && $Ocena > 0 && $IdProduktu > 0) {
                //
                $pola = array(array('products_id', $IdProduktu),
                              array('customers_id', $IdRecenzenta),
                              array('customers_name', $Autor),
                              array('reviews_rating', $Ocena),
                              array('date_added', 'now()'),
                              array('approved','0'));
                //	
                $sql = $GLOBALS['db']->insert_query('reviews', $pola);
                $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();
                //
                unset($pola);        
                
                $pola = array(
                        array('reviews_id', $id_dodanej_pozycji),
                        array('languages_id', $_SESSION['domyslnyJezyk']['id']),
                        array('reviews_text', $Opinia));          
                $sql = $GLOBALS['db']->insert_query('reviews_description' , $pola);
                
                unset($pola, $Autor, $Opinia, $Ocena);  
                
                // dodawanie punktow za napisanie recenzji
                if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 && $IdRecenzenta > 0 ) {        
                    //
                    $pola = array(array('customers_id', $IdRecenzenta),
                                  array('reviews_id', $id_dodanej_pozycji),
                                  array('points', (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE),
                                  array('date_added', 'now()'),
                                  array('points_status', '1'),
                                  array('points_type','RV'));
                    //	
                    $sql = $GLOBALS['db']->insert_query('customers_points', $pola);            
                    //
                }
                
                unset($IdRecenzenta, $_SESSION['weryfikacja']);
                
                //
                Funkcje::PrzekierowanieURL('napisz-recenzje-sukces-rws-'. $id_dodanej_pozycji .'.html/produkt=' . $IdProduktu);        
            }
            
        } else {
        
            Funkcje::PrzekierowanieURL('napisz-recenzje-rw-'. (int)$_POST['id_produkt'] .'.html');   
            
        }
    
    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
        
    }    
    
}

$sql = $GLOBALS['db']->open_query( Produkty::SqlNapiszRecenzje( ((isset($_GET['produkt']) && (int)$_GET['produkt'] > 0) ? (int)$_GET['produkt'] : (int)$_GET['id']) ) );

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAPISZ_OPINIE_O_PRODUKCIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

    // sprawdzenie linku SEO z linkiem w przegladarce
    if ( !isset($_GET['sukces']) ) {
        //
        Seo::link_Spr('napisz-recenzje-rw-' . (int)$_GET['id'] . '.html');
        //
    }
    
    //
    $Zalogowany = 'nie';
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
         $Zalogowany = 'tak';
    }    
    //
    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $Zalogowany);
    //
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SYSTEM_PUNKTOW') ), $GLOBALS['tlumacz'] );
    //
    $info = $sql->fetch_assoc();

    $Produkt = new Produkt( $info['products_id'] );

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', $GLOBALS['tlumacz']['NAPISZ_OPINIE_O_PRODUKCIE'] . ' ' . $Produkt->info['nazwa']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Produkt->meta_tagi['slowa'])) ? $Meta['slowa'] : $Produkt->meta_tagi['slowa']));
    $tpl->dodaj('__META_OPIS', ((empty($Produkt->meta_tagi['opis'])) ? $Meta['opis'] : $Produkt->meta_tagi['opis']));
    unset($Meta); 
    
    $srodek->dodaj('__LINK', 'napisz-recenzje-rw-' . (int)$_GET['id'] . '.html');
    $srodek->dodaj('__ID_PRODUKTU', (int)$_GET['id']);
    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);
    $srodek->dodaj('__NAZWA_PRODUKTU', $Produkt->info['nazwa']);
    $srodek->dodaj('__ZDJECIE_PRODUKTU', $Produkt->fotoGlowne['zdjecie_link_ikony']);
    
    // jezeli klient jest zalogowany wstawi jego imie w pole autora
    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
        $srodek->dodaj('__IMIE_AUTORA', $_SESSION['customer_firstname']);
      } else {
        $srodek->dodaj('__IMIE_AUTORA', '');
    }
    
    // system punktow
    $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI','');
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 ) {
        $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI', str_replace('{ILOSC_PUNKTOW}', (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE, $GLOBALS['tlumacz']['PUNKTY_RECENZJE']));
    }
    //
    $GLOBALS['db']->close_query($sql); 
    unset($info);    
    //
    $srodek->dodaj('__TOKEN',Sesje::Token());
    //
  } else {
    //
    $GLOBALS['db']->close_query($sql); 
    unset($WywolanyPlik);    
    //
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
    //
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>