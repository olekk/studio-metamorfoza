<?php

// plik
$WywolanyPlik = 'artykul';

include('start.php');

$Artykul = Aktualnosci::AktualnoscId( (int)$_GET['idartykul'] );

if (!empty($Artykul)) { 
    //
    // sprawdzenie linku SEO z linkiem w przegladarce
    Seo::link_Spr($Artykul['seo']);
    //
    // aktualizacja informacji o wyswietlaniach artykulu
    $pola = array(array('newsdesk_article_viewed', $Artykul['wyswietlenia'] + 1));		
    $GLOBALS['db']->update_query('newsdesk_description' , $pola, " newsdesk_id = '".(int)$_GET['idartykul']."' AND language_id = '".$_SESSION['domyslnyJezyk']['id']."'");	
    unset($pola); 
    $Artykul['wyswietlenia'] = $Artykul['wyswietlenia'] + 1;
    //
    // poniewaz jest aktualizowany licznik wyswietlen musi usunac cache aktualnosci
    $GLOBALS['cache']->UsunCacheAktualnosci();

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    $tpl->dodaj('__META_TYTUL', ((empty($Artykul['meta_tytul'])) ? $Meta['tytul'] : $Artykul['meta_tytul']));
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($Artykul['meta_slowa'])) ? $Meta['slowa'] : $Artykul['meta_slowa']));
    $tpl->dodaj('__META_OPIS', ((empty($Artykul['meta_opis'])) ? $Meta['opis'] : $Artykul['meta_opis']));
    unset($Meta);

    // Breadcrumb dla kategorii i artykulu
    if (!empty($Artykul['nazwa_kategorii'])) {
        $nawigacja->dodaj($Artykul['nazwa_kategorii'], Seo::link_SEO($Artykul['nazwa_kategorii'], $Artykul['id_kategorii'], 'kategoria_aktualnosci'));
    }
    $nawigacja->dodaj($Artykul['tytul'], $Artykul['seo']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony( $Wyglad->TrescLokalna($WywolanyPlik) ); 
    //
    $srodek->dodaj('__NAGLOWEK_ARTYKULU',$Artykul['tytul']);
    //
    // czy pokazywac skrocony opis strony
    if ( AKTUALNOSCI_INFO_SKROCONY_OPIS == 'tak' ) {
        $srodek->dodaj('__TRESC_ARTYKULU_KROTKI',$Artykul['opis_krotki'] . '<br /><br />');
      } else {
        $srodek->dodaj('__TRESC_ARTYKULU_KROTKI','');
    }
    // czy pokazywac date dodania artykulu
    if ( AKTUALNOSCI_DATA == 'tak' ) {
        $srodek->dodaj('__TRESC_ARTYKULU_DATA_DODANIA', '<em class="DataDodania">' . $GLOBALS['tlumacz']['DATA_DODANIA_ARTYKULU'] . ' ' . $Artykul['data'] . '</em>');
      } else {
        $srodek->dodaj('__TRESC_ARTYKULU_DATA_DODANIA','');
    }    
    // czy pokazywac ilosc odslon
    if ( AKTUALNOSCI_ILOSC_ODSLON == 'tak' ) {
        $srodek->dodaj('__TRESC_ARTYKULU_ILOSC_ODSLON', '<em class="IloscOdslon">' . $GLOBALS['tlumacz']['ILOSC_WYSWIETLEN'] . ' ' . $Artykul['wyswietlenia'] . '</em>');
      } else {
        $srodek->dodaj('__TRESC_ARTYKULU_ILOSC_ODSLON','');
    }    
    //
    $srodek->dodaj('__TRESC_ARTYKULU',$Artykul['opis']);
    //
  } else {
    //
    unset($WywolanyPlik);
    //
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
    //
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik, $Artykul);

include('koniec.php');

?>