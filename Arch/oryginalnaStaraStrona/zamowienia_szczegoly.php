<?php

  // plik
  $WywolanyPlik = 'zamowienia_szczegoly';

  include('start.php');

  if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_GET['id'])) {
  
    $blad = false;
    if ( isset($_GET['id']) && $_GET['id'] != '' ) {
    
        $zapytanie = "SELECT customers_id FROM orders WHERE orders_id = '".(int)$_GET['id']."' LIMIT 1";
        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
            $info = $sql->fetch_assoc();
            if ( (int)$info['customers_id'] == (int)$_SESSION['customer_id'] ) {
                $blad = false;
            } else {
                $blad = true;
            }
            unset($info);
        } else {
            $blad = true;
        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);

    } else {
        $blad = true;
    }

    if ( $blad ) {
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
    }

    unset($blad);  

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'PLATNOSCI', 'PRZYCISKI') ), $GLOBALS['tlumacz'] );

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_HISTORIA_ZAMOWIEN'],Seo::link_SEO('zamowienia_przegladaj.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['KLIENT_SZCZEGOLY_ZAMOWIENIA']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    $zamowienie = new Zamowienie((int)$_GET['id']);

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $zamowienie);

    $platnoscInfo = @unserialize($zamowienie->info['platnosc_info']);
    if ($platnoscInfo === false && $zamowienie->info['platnosc_info'] !== 'b:0;') {
    
        $srodek->dodaj('__PLATNOSC_INFO', $zamowienie->info['platnosc_info']);
        
    } else {
    
        require_once('moduly/platnosc/raporty/'.$platnoscInfo['rodzaj_platnosci'].'/formularz.php');
        $formularzPlatnosci = PowtorzPlatnosc($platnoscInfo, (int)$_GET['id']);

        $srodek->dodaj('__PLATNOSC_INFO', $formularzPlatnosci);
        
    }

    $srodek->dodaj('__NUMER_ZAMOWIENIA', (int)$_GET['id']);
    $srodek->dodaj('__METODA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);
    $srodek->dodaj('__WYSYLKA_MODUL', $zamowienie->info['wysylka_modul'] . ( $zamowienie->info['wysylka_info'] != '' ? ' ('.$zamowienie->info['wysylka_info'].')' : ''));
    $srodek->dodaj('__DATA_ZAMOWIENIA', date('d-m-Y H:i:s',strtotime($zamowienie->info['data_zamowienia'])));
    $srodek->dodaj('__STATUS_ZAMOWIENIA', Funkcje::pokazNazweStatusuZamowienia($zamowienie->info['status_zamowienia'],$_SESSION['domyslnyJezyk']['id']));
    $srodek->dodaj('__OPIEKUN_ZAMOWIENIA', Funkcje::PokazOpiekuna($zamowienie->info['opiekun']));
    
    // sprzedaz elektroniczna
    $srodek->dodaj('__LINK_POBRANIA_PLIKOW', $zamowienie->sprzedaz_online_link);

    foreach ($zamowienie->dostawa as $key => $value) {
        $srodek->dodaj('__DOSTAWA_'.strtoupper($key), $value);
    }
    
    foreach ($zamowienie->platnik as $key => $value) {
        $srodek->dodaj('__PLATNIK_'.strtoupper($key), $value);
    }

    $srodek->dodaj('__PDF_ZAMOWIENIE', '<a class="pdf" href="zamowienia-szczegoly-pdf-' . (int)$_GET['id'] . '.html"><img alt="' . $GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE'] . '" title="' . $GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE'] . '" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/pdf/zamowienie.png" /></a>');
    
    $srodek->dodaj('__PDF_FAKTURA', '');
    if ( FAKTURA_POBIERANIE == 'tak' ) { 
         $srodek->dodaj('__PDF_FAKTURA', '<a class="pdf" href="zamowienia-faktura-pdf-'.(int)$_GET['id'] . '.html"><img alt="' . $GLOBALS['tlumacz']['DRUKUJ_FAKTURE'] . '" title="' . $GLOBALS['tlumacz']['DRUKUJ_FAKTURE'] . '" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/pdf/faktura.png" /></a>');
    }

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik);

    include('koniec.php');

  } else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

  }
?>