<?php

// plik
$WywolanyPlik = 'kategoria_artykulow';

include('start.php');

$KategoriaArtykulu = Aktualnosci::KategoriaAktualnoscId( (int)$_GET['idkatart'] );

if (!empty($KategoriaArtykulu)) {
    //
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr($KategoriaArtykulu['seo']);
    
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', ((empty($KategoriaArtykulu['meta_tytul'])) ? $Meta['tytul'] : $KategoriaArtykulu['meta_tytul']));
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($KategoriaArtykulu['meta_slowa'])) ? $Meta['slowa'] : $KategoriaArtykulu['meta_slowa']));
    $tpl->dodaj('__META_OPIS', ((empty($KategoriaArtykulu['meta_opis'])) ? $Meta['opis'] : $KategoriaArtykulu['meta_opis']));
    unset($Meta);

    // Breadcrumb dla kategorii artykulow
    $nawigacja->dodaj($KategoriaArtykulu['nazwa'], $KategoriaArtykulu['seo']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));    
    //
    $srodek->dodaj('__NAGLOWEK_KATEGORII_ARTYKULU', $KategoriaArtykulu['nazwa']);
    $srodek->dodaj('__OPIS_KATEGORII_ARTYKULU', $KategoriaArtykulu['opis']);
    
    $srodek->dodaj('__ZDJECIE_KATEGORII_ARTYKULU', '');
    if ( strlen($KategoriaArtykulu['opis']) > 10 && $KategoriaArtykulu['foto'] != '' ) {
         $srodek->dodaj('__ZDJECIE_KATEGORII_ARTYKULU', Funkcje::pokazObrazek($KategoriaArtykulu['foto'], $KategoriaArtykulu['nazwa'], SZEROKOSC_OBRAZEK_MALY, WYSOKOSC_OBRAZEK_MALY, array(), 'class="ZdjecieKategAktualnosci"'));   
    }
    //
    // wyszukiwanie artykulow
    $TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria( (int)$_GET['idkatart'] );
    
    $IloscArtykulow = count($TablicaArtykulow);
    
    ob_start();
    
    // listing wersji mobilnej
    if ( $_SESSION['mobile'] == 'tak' ) {       

        if (in_array( 'listing_artykuly_kategorii.mobilne.php', $Wyglad->PlikiListingiLokalne )) {
            require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_artykuly_kategorii.mobilne.php');
        }
        
      } else {
     
        if (in_array( 'listing_artykuly_kategorii.php', $Wyglad->PlikiListingiLokalne )) {
            require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_artykuly_kategorii.php');
          } else {
            require('listingi/listing_artykuly_kategorii.php');
        }     
      
    }

    $ListaArtykulow = ob_get_contents();
    ob_end_clean(); 

    //
    $srodek->dodaj('__ARTYKULY_KATEGORII', $ListaArtykulow);
    //
    unset($IloscArtykulow, $ListaArtykulow, $TablicaArtykulow);    
    //
  } else {
    //
    unset($WywolanyPlik);
    //
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
    //    
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik, $KategoriaArtykulu);

include('koniec.php');

?>