<?php

// plik
$WywolanyPlik = 'recenzja';

include('start.php');

$sql = $GLOBALS['db']->open_query( Produkty::SqlRecenzja((int)$_GET['id']) );

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['OPINIA_O_PRODUKCIE']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));
    //
    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('SYSTEM_PUNKTOW') ), $GLOBALS['tlumacz'] );
    //
    $info = $sql->fetch_assoc();

    $Produkt = new Produkt( $info['products_id'] );
    
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr(Seo::link_SEO($Produkt->info['nazwa_seo'], (int)$_GET['id'], 'recenzja'));
    
    $Produkt->ProduktRecenzje();

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', $GLOBALS['tlumacz']['OPINIA_O_PRODUKCIE'] . ' ' . $Produkt->info['nazwa'] . ' ' . $GLOBALS['tlumacz']['OPINIA_O_PRODUKCIE_NAPISANA_PRZEZ'] . ' ' . $Produkt->recenzje[(int)$_GET['id']]['recenzja_oceniajacy']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Produkt->meta_tagi['slowa'])) ? $Meta['slowa'] : $Produkt->meta_tagi['slowa']));
    $tpl->dodaj('__META_OPIS', ((empty($Produkt->meta_tagi['opis'])) ? $Meta['opis'] : $Produkt->meta_tagi['opis']));
    unset($Meta);    
    //
    $srodek->dodaj('__NAZWA_PRODUKTU', $Produkt->info['nazwa']);
    $srodek->dodaj('__TRESC_RECENZJI', $Produkt->recenzje[(int)$_GET['id']]['recenzja_tekst']);
    $srodek->dodaj('__ZDJECIE_PRODUKTU', $Produkt->fotoGlowne['zdjecie_link_ikony']);
    $srodek->dodaj('__AUTOR_RECENZJI', $Produkt->recenzje[(int)$_GET['id']]['recenzja_oceniajacy']);
    $srodek->dodaj('__DATA_DODANIA', $Produkt->recenzje[(int)$_GET['id']]['recenzja_data_dodania']);
    //
    // ocena produktu
    $srodek->dodaj('__OCENA_RECENZJI_TEKST', '<span itemprop="rating">'.$Produkt->recenzje[(int)$_GET['id']]['recenzja_ocena'].'</span>' . '/5');
    $srodek->dodaj('__OCENA_RECENZJI_GWIAZDKI', $Produkt->recenzje[(int)$_GET['id']]['recenzja_ocena_obrazek']);
    //
    // srednia ocena
    $srodek->dodaj('__SREDNIA_OCENA_RECENZJI_TEKST', $Produkt->recenzjeSrednia['srednia_ocena'] . '/5');
    $srodek->dodaj('__SREDNIA_OCENA_RECENZJI_GWIAZDKI', $Produkt->recenzjeSrednia['srednia_ocena_obrazek']);
    $srodek->dodaj('__ILOSC_WSZYSTKICH_RECENZJI', $Produkt->recenzjeSrednia['ilosc_glosow']);
    //
    $srodek->dodaj('__LINK_DO_PRODUKTU', $Produkt->info['adres_seo']);
    $srodek->dodaj('__LINK_DO_NAPISANIA_RECENZJI', 'napisz-recenzje-rw-' . $info['products_id'] . '.html');
    //
    // system punktow
    $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI','');
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE > 0 ) {
        $srodek->dodaj('__INFO_O_PUNKTACH_RECENZJI', str_replace('{ILOSC_PUNKTOW}', (int)SYSTEM_PUNKTOW_PUNKTY_RECENZJE, $GLOBALS['tlumacz']['PUNKTY_RECENZJE']));
    }
    //    
    $GLOBALS['db']->close_query($sql); 
    unset($Produkt, $info);
    //
    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
    //
  } else {
    //
    $GLOBALS['db']->close_query($sql); 
    //
    Funkcje::PrzekierowanieURL('brak-recenzji.html'); 
}

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>