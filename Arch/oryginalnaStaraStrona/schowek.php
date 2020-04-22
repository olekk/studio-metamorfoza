<?php

// plik
$WywolanyPlik = 'schowek';

include('start.php');

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SCHOWEK') ), $GLOBALS['tlumacz'] );

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
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_SCHOWEK']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// stronicowanie
$srodek->dodaj('__STRONICOWANIE', '');
//
$IloscProduktow = $GLOBALS['schowekKlienta']->IloscProduktow;
//

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
             Funkcje::PrzekierowanieURL('schowek.html');
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

ob_start();

// listing wersji mobilnej
if ( $_SESSION['mobile'] == 'tak' ) {

    if (in_array( 'listing_schowek.mobilne.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_schowek.mobilne.php');
    }

  } else {

    if (in_array( 'listing_schowek.php', $Wyglad->PlikiListingiLokalne )) {
        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_schowek.php');
      } else {
        require('listingi/listing_schowek.php');
    }

}
    
$ListaProduktow = ob_get_contents();
ob_end_clean();        

// jezeli ceny tylko dla zalogowanych
if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {

    $srodek->dodaj('__WARTOSC_PRODUKTOW_SCHOWKA', '<span class="CenaDlaZalogowanych">' . $GLOBALS['tlumacz']['CENA_TYLKO_DLA_ZALOGOWANYCH'] . '</span>');

  } else {

    $WartoscSchowka = $GLOBALS['schowekKlienta']->WartoscProduktowSchowka();
    $srodek->dodaj('__WARTOSC_PRODUKTOW_SCHOWKA', $GLOBALS['waluty']->PokazCene($WartoscSchowka['brutto'], $WartoscSchowka['netto'], 0, $_SESSION['domyslnaWaluta']['id']));
    unset($WartoscSchowka);
    
}

$srodek->dodaj('__LISTA_PRODUKTOW', $ListaProduktow);   

unset($IloscProduktow, $ListaProduktow); 

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>