<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'zamowienie_potwierdzenie';

include('start.php');

$Blad = '';

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() == 0 || (!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

// przekierowanie do koszyka jezeli nie ma zadnej ustawionej metody wyslki
if ( !isset($_SESSION['rodzajDostawy']['wysylka_id']) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

// jezeli kraj dostawy nie jest rowny zapisanemu w sesji - powraca do koszyka
if ( $_SESSION['krajDostawy']['id'] != $_SESSION['adresDostawy']['panstwo'] ) {
 
    Funkcje::PrzekierowanieSSL('zamowienie-zmien-dane.html'); 

}

// sprawdza czy jest dostepna wczesniej wybrana w koszyku forma wysylki
$wysylki = new Wysylki($_SESSION['krajDostawy']['kod']);

if ( isset($_SESSION['rodzajDostawy']) && !array_key_exists($_SESSION['rodzajDostawy']['wysylka_id'], $wysylki->wysylki) ) {

  unset($_SESSION['rodzajDostawy']);
  Funkcje::PrzekierowanieURL('koszyk.html'); 
  
}

// czy wartosc zamowienia nie jest mniejsza niz koszyk
$MinimalneZamowienieGrupy = Klient::MinimalneZamowienie();
if ( $MinimalneZamowienieGrupy > 0 ) {

    $MinZamowienie = $GLOBALS['waluty']->PokazCeneBezSymbolu($MinimalneZamowienieGrupy,'',true);
    $WartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

    if ( $WartoscKoszyka['brutto'] < $MinZamowienie ) {
         //
         Funkcje::PrzekierowanieURL('koszyk.html'); 
         //
    }
    unset($MinZamowienie, $WartoscKoszyka);
    
}  
unset($MinimalneZamowienieGrupy);

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK','ZAMOWIENIE_REALIZACJA', 'WYSYLKI', 'PLATNOSCI', 'PRZYCISKI', 'PODSUMOWANIE_ZAMOWIENIA', 'REJESTRACJA', 'PRODUKT') ), $GLOBALS['tlumacz'] );

// produkty koszyka
$ProduktyKoszyka = array();

//
// generuje tablice globalne z nazwami cech
Funkcje::TabliceCech();         
//
$MaksymalnyCzasWysylki = 0;
$MaksymalnyCzasWysylkiProdukt = true;

// sprawdzi czy w zamowieniu sa produkty w formie uslugi
$ProduktUsluga = false;
// sprawdzi czy w zamowieniu sa produkty elektroniczne
$ProduktOnline = false;
// sprawdzi czy w zamowieniu sa produkty niestandardowe, indywidualne
$ProduktNiestandardowy = false;

foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    //
    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ), 40, 40 );
    // elementy kupowania
    $Produkt->ProduktKupowanie(); 
    // czas wysylki
    $Produkt->ProduktCzasWysylki();
    // stan produktu
    if ( KARTA_PRODUKTU_STAN_PRODUKTU == 'tak' ) {
         $Produkt->ProduktStanProduktu();
    }  
    // gwarancja produktu
    if ( KARTA_PRODUKTU_GWARANCJA == 'tak' ) {
         $Produkt->ProduktGwarancja();
    }      
    //
    // jezeli jest kupowanie na wartosci ulamkowe to sformatuje liczbe
    if ( $Produkt->info['jednostka_miary_typ'] == '0' ) {
         $TablicaZawartosci['ilosc'] = number_format( $TablicaZawartosci['ilosc'] , 2, '.', '' );
    }
    //
    // czy produkt ma cechy
    $CechaPrd = Funkcje::CechyProduktuPoId( $TablicaZawartosci['id'] );
    $JakieCechy = '';
    if ( count($CechaPrd) > 0 ) {
        //
        for ($a = 0, $c = count($CechaPrd); $a < $c; $a++) {
            $JakieCechy .= '<span class="Cecha">' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></span>';
        }
        //
    }
    //
    // czy produkt ma komentarz
    $KomentarzProduktu = '';
    if ( $TablicaZawartosci['komentarz'] != '' ) {
        //
        $KomentarzProduktu = '<span class="Komentarz">' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' <b>' . $TablicaZawartosci['komentarz'] . '</b></span>';
        //
    }
    // czy sa pola tekstowe
    $PolaTekstowe = '';
    if ( $TablicaZawartosci['pola_txt'] != '' ) {
        //
        $TblPolTxt = Funkcje::serialCiag($TablicaZawartosci['pola_txt']);
        foreach ( $TblPolTxt as $WartoscTxt ) {
            //
            // jezeli pole to plik
            if ( $WartoscTxt['typ'] == 'plik' ) {
                $PolaTekstowe .= '<span class="Cecha">' . $WartoscTxt['nazwa'] . ': <a href="inne/wgranie.php?src=' . base64_encode(str_replace('.',';',$WartoscTxt['tekst'])) . '"><b>' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</b></a></span>';
              } else {
                $PolaTekstowe .= '<span class="Cecha">' . $WartoscTxt['nazwa'] . ': <b>' . $WartoscTxt['tekst'] . '</b></span>';
            }
        }
        unset($TblPolTxt);
        //
    }    
    //
    $ProduktyKoszyka[$TablicaZawartosci['id']] = array('id'            => $TablicaZawartosci['id'],
                                                       'zdjecie'       => $Produkt->fotoGlowne['zdjecie_link'],
                                                       'nazwa'         => $Produkt->info['link'] . $JakieCechy,
                                                       'link_opisu'    => '<a class="Informacja" href="' . $Produkt->info['adres_seo'] . '">' . $GLOBALS['tlumacz']['SZCZEGOLOWY_OPIS_PRODUKTU'] . '</a>',
                                                       'producent'     => (( !empty($Produkt->info['nazwa_producenta']) ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['PRODUCENT'] . ': <b>' . $Produkt->info['nazwa_producenta'] . '</b></span>' : ''),
                                                       'czas_wysylki'  => (( !empty($Produkt->czas_wysylki) ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ': <b>' . $Produkt->czas_wysylki . '</b></span>' : ''),
                                                       'stan_produktu' => (( !empty($Produkt->stan_produktu) ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['STAN_PRODUKTU'] . ': <b>' . $Produkt->stan_produktu . '</b></span>' : ''),
                                                       'gwarancja'     => (( !empty($Produkt->gwarancja) ) ? '<span class="Cecha">' . $GLOBALS['tlumacz']['GWARANCJA'] . ': <b>' . str_replace('<a ', '<a style="font-weight:bold" ', $Produkt->gwarancja) . '</b></span>' : ''),
                                                       'komentarz'     => $KomentarzProduktu,
                                                       'pola_txt'      => $PolaTekstowe,
                                                       'ilosc'         => $TablicaZawartosci['ilosc'],
                                                       'cena'          => $GLOBALS['waluty']->PokazCene($TablicaZawartosci['cena_brutto'], $TablicaZawartosci['cena_netto'], 0, $_SESSION['domyslnaWaluta']['id']),
                                                       'wartosc'       => $GLOBALS['waluty']->PokazCene($TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'], $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'], 0, $_SESSION['domyslnaWaluta']['id']));
    // maksymalny czas wysylki
    if ( (int)$Produkt->czas_wysylki_dni > $MaksymalnyCzasWysylki ) {
         $MaksymalnyCzasWysylki = (int)$Produkt->czas_wysylki_dni;
    }
    // sprawdza czy kazdy produkt ma czas wysylki
    if ( (int)$Produkt->czas_wysylki_dni == 0 ) {
         $MaksymalnyCzasWysylkiProdukt = false;
    }
    
    // sprawdzi czy w zamowieniu sa produkty w formie uslugi
    if ( $Produkt->info['typ_produktu'] == 'usluga' ) {
         $ProduktUsluga = true;
    }
    
    // sprawdzi czy w zamowieniu sa produkty elektroniczne
    if ( $Produkt->info['typ_produktu'] == 'online' ) {
         $ProduktOnline = true;
    }
    
    // sprawdzi czy w zamowieniu sa produkty niestandardowe, indywidualne
    if ( $Produkt->info['typ_produktu'] == 'indywidualny' ) {
         $ProduktNiestandardowy = true;
    }    
    
    //
    unset($Produkt, $KomentarzProduktu, $PolaTekstowe);
    //
}
//
// jezeli wszystkie produkty mialy czas wysylki
if ( $MaksymalnyCzasWysylkiProdukt == true ) {
     $MaksymalnyCzasWysylki = str_replace('{0}', $MaksymalnyCzasWysylki, $GLOBALS['tlumacz']['SZACOWANY_CZAS_WYSYLKI']);
}
//

// parametry do ustalenia podsumowania zamowienia
$podsumowanie = new Podsumowanie();
$PodsumowanieZamowienia = $podsumowanie->GenerujWPotwierdzeniu();

$CssDokumentSprzedazy = '';

if ( !isset($_SESSION['adresFaktury']['dokument']) ) {

    if ( KLIENT_DOMYSLNY_DOKUMENT == 'faktura' ) {
        $_SESSION['adresFaktury']['dokument'] = '1';
    } elseif ( KLIENT_DOMYSLNY_DOKUMENT == 'paragon' ) {
        $_SESSION['adresFaktury']['dokument'] = '0';
    }
    
}

// jezeli klient jest jako firma i ma byc faktura to ustawic domyslne fakture
if ( KLIENT_DOMYSLNY_DOKUMENT_FIRMA == 'tak' && $_SESSION['adresDostawy']['firma'] != '' ) {
    $_SESSION['adresFaktury']['dokument'] = '1';
}

// jezeli jest obsluga tylko firm to ustawi fakture jako dokument sprzedazy
if ( KLIENT_TYLKO_FIRMA == 'tylko firma' ) {
    $_SESSION['adresFaktury']['dokument'] = '1';
    //
    // jezeli jest tylko firma to nie potrzebny jest wybor dokumentu sprzedazy i pozostaje tylko faktura
    $CssDokumentSprzedazy = 'style="display:none"';
}

$DaneDoWysylki = '';

$DaneDoWysylki .= $_SESSION['adresDostawy']['imie'] . ' ' . $_SESSION['adresDostawy']['nazwisko'] . '<br />';

if ( $_SESSION['adresDostawy']['firma'] != '' ) {
    $DaneDoWysylki .= $_SESSION['adresDostawy']['firma'] . '<br />';
}

$DaneDoWysylki .= $_SESSION['adresDostawy']['ulica'] . '<br />';

$DaneDoWysylki .= $_SESSION['adresDostawy']['kod_pocztowy'] . ' ' . $_SESSION['adresDostawy']['miasto'] . '<br />';

if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    $DaneDoWysylki .= Klient::pokazNazweWojewodztwa($_SESSION['adresDostawy']['wojewodztwo']) . '<br />';
}

$DaneDoWysylki .= Klient::pokazNazwePanstwa($_SESSION['adresDostawy']['panstwo']) . '<br />';

if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
    $DaneDoWysylki .= $GLOBALS['tlumacz']['TELEFON_SKROCONY'] . ' ' . $_SESSION['adresDostawy']['telefon'] . '<br />';
}

$DaneDoFaktury = '';

if ( $_SESSION['adresFaktury']['imie'] != '' && $_SESSION['adresFaktury']['nazwisko'] != '' ) {
    $DaneDoFaktury .= $_SESSION['adresFaktury']['imie'] . ' ' . $_SESSION['adresFaktury']['nazwisko'] . '<br />';
}

if ( $_SESSION['adresFaktury']['firma'] != '' ) {
    $DaneDoFaktury .= $_SESSION['adresFaktury']['firma'] . '<br />';
    $DaneDoFaktury .= $_SESSION['adresFaktury']['nip'] . '<br />';
}

$DaneDoFaktury .= $_SESSION['adresFaktury']['ulica'] . '<br />';

$DaneDoFaktury .= $_SESSION['adresFaktury']['kod_pocztowy'] . ' ' . $_SESSION['adresFaktury']['miasto'] . '<br />';

if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    $DaneDoFaktury .= Klient::pokazNazweWojewodztwa($_SESSION['adresFaktury']['wojewodztwo']) . '<br />';
}

$DaneDoFaktury .= Klient::pokazNazwePanstwa($_SESSION['adresFaktury']['panstwo']);

// parametry do ustalenia dostepnych punktow odbioru
$WysylkaPotwierdzenieZamowienia = $wysylki->Potwierdzenie( $_SESSION['rodzajDostawy']['wysylka_id'], $_SESSION['rodzajDostawy']['wysylka_klasa'] );
$WysylkaPotwierdzenieZamowieniaInfo = '';
if ( isset($GLOBALS['tlumacz']['WYSYLKA_'.$_SESSION['rodzajDostawy']['wysylka_id'].'_INFORMACJA']) ) {
    $WysylkaPotwierdzenieZamowieniaInfo = $GLOBALS['tlumacz']['WYSYLKA_'.$_SESSION['rodzajDostawy']['wysylka_id'].'_INFORMACJA'];
    $_SESSION['rodzajDostawy']['informacja'] = $WysylkaPotwierdzenieZamowieniaInfo;
}

// parametry do ustalenia danych do wplaty
$platnosci = new Platnosci($_SESSION['rodzajDostawy']['wysylka_id']);
$PlatnoscPotwierdzenieZamowienia = $platnosci->Potwierdzenie( $_SESSION['rodzajPlatnosci']['platnosc_id'], $_SESSION['rodzajPlatnosci']['platnosc_klasa'] );

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// css do kalendarza
$tpl->dodaj('__CSS_PLIK', ',zebra_datepicker');
// dla wersji mobilnej
$tpl->dodaj('__CSS_KALENDARZ', ',zebra_datepicker');

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZAMOWIENIE_POTWIERDZENIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $ProduktyKoszyka);
//

$srodek->parametr('ProduktyUsluga', $ProduktUsluga);
$srodek->parametr('ProduktyOnline', $ProduktOnline);
$srodek->parametr('ProduktyNiestandardowe', $ProduktNiestandardowy);

unset($ProduktyKoszyka, $ProduktUsluga, $ProduktOnline, $ProduktNiestandardowy);

// maksymalny czas wysylki
$srodek->dodaj('__MAKSYMALNY_CZAS_WYSYLKI', '');
if ( $MaksymalnyCzasWysylkiProdukt == true ) {
     $srodek->dodaj('__MAKSYMALNY_CZAS_WYSYLKI', '<div class="Informacja">' . $MaksymalnyCzasWysylki . '</div>');
}
unset($MaksymalnyCzasWysylki, $MaksymalnyCzasWysylkiProdukt);

// wartosc koszyka
$ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
$srodek->dodaj('__WARTOSC_KOSZYKA', $GLOBALS['waluty']->PokazCene($ZawartoscKoszyka['brutto'], $ZawartoscKoszyka['netto'], 0, $_SESSION['domyslnaWaluta']['id']));
unset($ZawartoscKoszyka);

$TekstZgody = str_replace('{INFO_NAZWA_SKLEPU}',DANE_NAZWA_FIRMY_PELNA,$GLOBALS['tlumacz']['ZGODA_NA_PRZEKAZANIE_DANYCH']);

// dodatkowe elementy do podsumowania zamowienia
$srodek->dodaj('__PODSUMOWANIE_ZAMOWIENIA', $PodsumowanieZamowienia);
$srodek->dodaj('__DANE_DO_WYSYLKI', $DaneDoWysylki);
$srodek->dodaj('__DANE_DO_FAKTURY', $DaneDoFaktury);
$srodek->dodaj('__WYSYLKA_W_POTWIERDZENIU', $WysylkaPotwierdzenieZamowienia);
$srodek->dodaj('__WYSYLKA_W_POTWIERDZENIU_INFORMACJA', $WysylkaPotwierdzenieZamowieniaInfo);
$srodek->dodaj('__PLATNOSC_W_POTWIERDZENIU', $PlatnoscPotwierdzenieZamowienia);
$srodek->dodaj('__TEKST_ZGODY', $TekstZgody);
$srodek->dodaj('__CSS_DOKUMENT_SPRZEDAZY', $CssDokumentSprzedazy);

$DodatkowePolaZamowienia = Zamowienie::pokazDodatkowePolaZamowienia($_SESSION['domyslnyJezyk']['id']);
if ( $DodatkowePolaZamowienia != '' ) {
     $DodatkowePolaZamowienia = '<div class="PolaZamowienie">' . $DodatkowePolaZamowienia . '</div>';
}

$srodek->dodaj('__DODATKOWE_POLA_ZAMOWIENIA', $DodatkowePolaZamowienia);

unset($DodatkowePolaZamowienia);

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik, $PodsumowanieZamowienia, $DaneDoWysylki, $DaneDoFaktury, $WysylkaPotwierdzenieZamowienia, $WysylkaPotwierdzenieZamowieniaInfo, $PlatnoscPotwierdzenieZamowienia, $TekstZgody, $CssDokumentSprzedazy);

include('koniec.php');

?>