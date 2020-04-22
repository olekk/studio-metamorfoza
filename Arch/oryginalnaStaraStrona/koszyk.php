<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'koszyk';

include('start.php');

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('WYSYLKI', 'PLATNOSCI', 'PRZYCISKI', 'KOSZYK','KUPONY_RABATOWE','PUNKTY','ZAMOWIENIE_REALIZACJA', 'PODSUMOWANIE_ZAMOWIENIA') ), $GLOBALS['tlumacz'] );

// produkty koszyka
$ProduktyKoszyka = array();

// dodatkowe parametry zamowienia
$DodatkoweInformacje = array();

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {

    // przelicza dodatkowo koszyk
    $GLOBALS['koszykKlienta']->PrzeliczKoszyk(); 

    //
    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();         
    //
    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        //
        $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ), 40, 40 );
        //        
        // elementy kupowania
        $Produkt->ProduktKupowanie();     
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
            $KomentarzProduktu = '<span class="Komentarz"><img id="img_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '" onclick="EdytujKomentarz(\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\')" src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/nawigacja/edytuj.png" alt="" title="' . $GLOBALS['tlumacz']['EDYTUJ_KOMENTARZ'] . '" />' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' <b id="komentarz_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '">' . $TablicaZawartosci['komentarz'] . '</b></span>';
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
        $ProduktyKoszyka[$TablicaZawartosci['id']] = array('id'        => $TablicaZawartosci['id'],
                                                           'zdjecie'   => $Produkt->fotoGlowne['zdjecie_link'],
                                                           'nazwa'     => $Produkt->info['link'] . $JakieCechy,
                                                           'komentarz' => $KomentarzProduktu,
                                                           'pola_txt'  => $PolaTekstowe,
                                                           'usun'      => '<span class="UsunKoszyk" onclick="UsunZKoszyka(\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\')"></span>',
                                                           'ilosc'     => (( $TablicaZawartosci['rodzaj_ceny'] == 'baza' ) ? '<input type="text" id="ilosc_' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '" value="' . $TablicaZawartosci['ilosc'] . '" size="4" onchange="SprIlosc(this,' . $Produkt->zakupy['minimalna_ilosc'] . ',' . $Produkt->info['jednostka_miary_typ'] . ')" /> <div class="Przelicz"><a onclick="return DoKoszyka(\'' . $Produkt->idUnikat . $TablicaZawartosci['id'] . '\',\'przelicz\',0)" href="/" class="przycisk">' . $GLOBALS['tlumacz']['PRZELICZ'] . '</a></div>' : $TablicaZawartosci['ilosc']),
                                                           'cena'      => $GLOBALS['waluty']->PokazCene($TablicaZawartosci['cena_brutto'], $TablicaZawartosci['cena_netto'], 0, $_SESSION['domyslnaWaluta']['id']),
                                                           'wartosc'   => $GLOBALS['waluty']->PokazCene($TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'], $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'], 0, $_SESSION['domyslnaWaluta']['id']));
        //
        unset($Produkt, $KomentarzProduktu, $PolaTekstowe);
        //
    }
    //
    // parametry do ustalenia dostepnych wysylek
    $wysylki = new Wysylki($_SESSION['krajDostawy']['kod']);
    $tablica_wysylek = $wysylki->wysylki;

    if ( isset($_SESSION['rodzajDostawy']) && !array_key_exists($_SESSION['rodzajDostawy']['wysylka_id'], $tablica_wysylek) ) {
    
      unset($_SESSION['rodzajDostawy']);
      
    }
    
    // select z panstwami
    $lista_rozwijana_panstw = Funkcje::RozwijaneMenu('kraj_dostawy',Klient::ListaPanstw('countries_iso_code_2'), $_SESSION['krajDostawy']['kod'], 'id="kraj_dostawy"');    
    
    if ( !isset($_SESSION['rodzajDostawy']) ) {
    
      $pierwsza_wysylka = array_slice($tablica_wysylek,0,1);
      $koszt_wysylki = $pierwsza_wysylka['0']['wartosc'];
      $prog_bezplatnej_wysylki = $pierwsza_wysylka['0']['wysylka_free'];
      $_SESSION['rodzajDostawy'] = array(
                                         'wysylka_id' => $pierwsza_wysylka['0']['id'],
                                         'wysylka_klasa' => $pierwsza_wysylka['0']['klasa'],
                                         'wysylka_koszt' => $pierwsza_wysylka['0']['wartosc'],
                                         'wysylka_nazwa' => $pierwsza_wysylka['0']['text'],
                                         'wysylka_vat_id' => $pierwsza_wysylka['0']['vat_id'],
                                         'wysylka_vat_stawka' => $pierwsza_wysylka['0']['vat_stawka'],                                          
                                         'dostepne_platnosci' => $pierwsza_wysylka['0']['dostepne_platnosci']);
                                         
    } else {
    
      $IdBiezace = $_SESSION['rodzajDostawy']['wysylka_id'];
      unset($_SESSION['rodzajDostawy']);
      $_SESSION['rodzajDostawy'] = array(
                                         'wysylka_id' => $tablica_wysylek[$IdBiezace]['id'],
                                         'wysylka_klasa' => $tablica_wysylek[$IdBiezace]['klasa'],
                                         'wysylka_koszt' => $tablica_wysylek[$IdBiezace]['wartosc'],
                                         'wysylka_nazwa' => $tablica_wysylek[$IdBiezace]['text'],
                                         'wysylka_vat_id' => $tablica_wysylek[$IdBiezace]['vat_id'],
                                         'wysylka_vat_stawka' => $tablica_wysylek[$IdBiezace]['vat_stawka'],                                          
                                         'dostepne_platnosci' => $tablica_wysylek[$IdBiezace]['dostepne_platnosci'] );

      $koszt_wysylki = $tablica_wysylek[$_SESSION['rodzajDostawy']['wysylka_id']]['wartosc'];
      $prog_bezplatnej_wysylki = $tablica_wysylek[$_SESSION['rodzajDostawy']['wysylka_id']]['wysylka_free'];
      
    }

    // radio z wysylkami
    $lista_radio_wysylek = '<div id="rodzaj_wysylki">'.Funkcje::ListaRadioKoszyk('rodzaj_wysylki', $tablica_wysylek, $_SESSION['rodzajDostawy']['wysylka_id'], '').'</div>';

    // parametry do ustalenia dostepnych platnosci
    $platnosci = new Platnosci($_SESSION['rodzajDostawy']['wysylka_id']);
    $tablica_platnosci = $platnosci->platnosci;

    if ( isset($_SESSION['rodzajPlatnosci']) && !array_key_exists($_SESSION['rodzajPlatnosci']['platnosc_id'], $tablica_platnosci) ) {
    
      unset($_SESSION['rodzajPlatnosci']);
      
    }
    
    if ( !isset($_SESSION['rodzajPlatnosci']) ) {
      $pierwsza_platnosc = array_slice($tablica_platnosci,0,1);
      $koszt_platnosci = $pierwsza_platnosc['0']['wartosc'];
      $_SESSION['rodzajPlatnosci'] = array(
                                         'platnosc_id' => $pierwsza_platnosc['0']['id'],
                                         'platnosc_klasa' => $pierwsza_platnosc['0']['klasa'],
                                         'platnosc_koszt' => $pierwsza_platnosc['0']['wartosc'],
                                         'platnosc_nazwa' => $pierwsza_platnosc['0']['text']);
                                         
    } else {
    
      $koszt_platnosci = $tablica_platnosci[$_SESSION['rodzajPlatnosci']['platnosc_id']]['wartosc'];
      
    }

    $calkowity_koszt = $koszt_wysylki + $koszt_platnosci;
    $calkowity_koszt_wysylki = $GLOBALS['waluty']->PokazCene($calkowity_koszt, 0, 0, $_SESSION['domyslnaWaluta']['id']);

    // radio z platnosciami
    $lista_radio_platnosci = '<div id="rodzaj_platnosci">'.Funkcje::ListaRadioKoszyk('rodzaj_platnosci', $tablica_platnosci, $_SESSION['rodzajPlatnosci']['platnosc_id'], '').'</div>';

    $ukryj_przycisk = '';
    if ( $_SESSION['rodzajPlatnosci']['platnosc_id'] == '0' || $_SESSION['rodzajDostawy']['wysylka_id'] == '0' ) {
      $ukryj_przycisk = 'style="display:none;"';
    }

    //Sprawdzenie czy jest wpisany kupon rabatowy i czy nadal spelnia warunki przyznania
    if ( isset($_SESSION['kuponRabatowy']) ) {
      $kupon = new Kupony($_SESSION['kuponRabatowy']['kupon_kod']);
      $tablica_kuponu = $kupon->kupon;
      if ( $_SESSION['kuponRabatowy'] != $tablica_kuponu ) {
          unset($_SESSION['kuponRabatowy']);
          $_SESSION['kuponRabatowy'] = $tablica_kuponu;
      }
      if ( $tablica_kuponu['kupon_status'] ) {
      } else {
        unset($_SESSION['kuponRabatowy']);
      }
    }

    // parametry do ustalenia podsumowania zamowienia
    $podsumowanie = new Podsumowanie();
    $podsumowanie_zamowienia = $podsumowanie->Generuj();

    // punkty klienta
    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' ) {
    
      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
        
        $punkty = new Punkty((int)$_SESSION['customer_id']);

        if ( $punkty->suma >= SYSTEM_PUNKTOW_MIN_ZAMOWIENIA && $GLOBALS['koszykKlienta']->KoszykWartoscProduktow() >= $GLOBALS['waluty']->PokazCeneBezSymbolu(SYSTEM_PUNKTOW_MIN_WARTOSC_ZAMOWIENIA,'',true) ) {
        
          $DodatkoweInformacje['WartoscPunktowKlienta'] = $punkty->suma;
          $DodatkoweInformacje['InfoPunktyKlienta'] = true;
          $DodatkoweInformacje['WartoscPunktowKlientaKwota'] = $punkty->wartosc;
          $DodatkoweInformacje['WartoscMaksymalnaPunktowKwota'] = $punkty->wartosc_maksymalna_kwota;

          $info_punkty = str_replace( '{WARTOSC_LACZNA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($punkty->wartosc, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', $GLOBALS['tlumacz']['INFO_PUNKTY'] );
          $info_punkty = str_replace( '{WARTOSC_MAKSYMALNA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($punkty->wartosc_maksymalna_kwota, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', $info_punkty );

          $wartosc_zamowienia_do_punktow = 0;
          foreach ( $_SESSION['podsumowanieZamowienia'] as $podsumowanie ) {
            if ( $podsumowanie['prefix'] == '1' ) {
              if ( $podsumowanie['klasa'] == 'ot_shipping' ) {
                $wartosc_zamowienia_do_punktow;
              } else {
                $wartosc_zamowienia_do_punktow += $podsumowanie['wartosc'];
              }
            } elseif ( $podsumowanie['prefix'] == '0' ) {
              $wartosc_zamowienia_do_punktow -= $podsumowanie['wartosc'];
            }
          }

          // wartosc punktow klienta
          $wartosc_punktow_do_wykorzystania = $punkty->wartosc;

          // jezeli wartosc punktow klienta jest wieksza niz wartosc zamawianych produktow
          if ( $wartosc_punktow_do_wykorzystania > $wartosc_zamowienia_do_punktow ) {
            $wartosc_punktow_do_wykorzystania = $wartosc_zamowienia_do_punktow;
          }

          // jezeli wartosc punktow klienta jest wieksza niz maks wartosc punktow do wykorzystania w jednym zamowieniu
          if ( $wartosc_punktow_do_wykorzystania > $punkty->wartosc_maksymalna_kwota ) {
            $wartosc_punktow_do_wykorzystania = $punkty->wartosc_maksymalna_kwota;
          }

          $info_punkty_do_wykorzystania = str_replace( '{KWOTA_PUNKTOW_W_ZAMOWIENIU}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($wartosc_punktow_do_wykorzystania, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', $GLOBALS['tlumacz']['INFO_PUNKTY_DO_WYKORZYSTANIA'] );

          // ilosc punktow klienta
          $ilosc_punktow_do_wykorzystania = $punkty->suma;

          // jezeli przeliczona ilosc punktow klienta jest wieksza niz wylicona z wartosci zamowienia
          if ( $ilosc_punktow_do_wykorzystania > ($wartosc_zamowienia_do_punktow/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU) {
            $ilosc_punktow_do_wykorzystania = ceil(($wartosc_zamowienia_do_punktow/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU);
          }

          // jezeli ilosc punktow klienta jest wieksza niz maks ilosc punktow do wykorzystania w jednym zamowieniu
          if ( $ilosc_punktow_do_wykorzystania > SYSTEM_PUNKTOW_MAX_ZAMOWIENIA ) {
            $ilosc_punktow_do_wykorzystania = SYSTEM_PUNKTOW_MAX_ZAMOWIENIA;
          }
            
          $info_punkty_do_wykorzystania = str_replace( '{ILOSC_PUNKTOW_W_ZAMOWIENIU}', '<b>'.$ilosc_punktow_do_wykorzystania.'</b>', $info_punkty_do_wykorzystania );

          $DodatkoweInformacje['WartoscPunktowZamowienia'] = $ilosc_punktow_do_wykorzystania;
          
        }
        
      }
      
    }
}

$bylKalkulator = false;

// kalkulator ratalny Santander Consumer
$kalkulator_santander = '<div id="RataSantander"></div>';
if ( isset($tablica_platnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_santander', $tablica_platnosci) ) {
  $kalkulator_santander = '<div id="RataSantander"><a onclick="PoliczRateSantander('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_santander_white_koszyk.png" alt="" /></a></div>';
  $bylKalkulator = true;
}

// kalkulator ratalny Lukas
$kalkulator_lukas = '<div id="RataLukas"></div>';
if ( isset($tablica_platnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_lukas', $tablica_platnosci) ) {
  $kalkulator_lukas = '<div id="RataLukas"><a onclick="PoliczRateLukas('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_lukas_white.png" alt="" /></a></div>';
  $bylKalkulator = true;  
}

// kalkulator ratalny MBANK
$kalkulator_mbank = '<div id="RataMbank"></div>';
if ( isset($tablica_platnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_mbank', $tablica_platnosci) ) {
  $kalkulator_mbank = '<div id="RataMbank"><a onclick="PoliczRateMbank('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_mbank_koszyk.png" alt="" /></a></div>';
  $bylKalkulator = true;  
}

// kalkulator ratalny PayU Raty
$kalkulator_payuraty = '<div id="RataPayU"></div>';
if ( isset($_SESSION['podsumowanieZamowienia']) && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] >= 300 && $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] < 20000 ) {
    if ( isset($tablica_platnosci) && Funkcje::CzyJestWlaczonaPlatnosc('platnosc_payu', $tablica_platnosci) ) {
      $zap = "SELECT kod, wartosc FROM modules_payment_params WHERE kod ='PLATNOSC_PAYU_RATY_WLACZONE'";
      $sqlp = $GLOBALS['db']->open_query($zap);
      if ((int)$GLOBALS['db']->ile_rekordow($sqlp) > 0) {
        $infop = $sqlp->fetch_assoc();
        if ( $infop['wartosc'] == 'tak' ) {
          $kalkulator_payuraty = '<div id="RataPayU"><a onclick="PoliczRatePauYRaty('.$_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'].');" style="cursor: pointer;"><img src="' . KATALOG_ZDJEC . '/platnosci/oblicz_rate_payu_koszyk.png" alt="" /></a></div>';
          $bylKalkulator = true;
        }
      }
      $GLOBALS['db']->close_query($sqlp); 
      unset($zap, $infop);    
    }
}

if ( isset($prog_bezplatnej_wysylki) && $prog_bezplatnej_wysylki > 0 ) {
  if ( $_SESSION['podsumowanieZamowienia']['ot_total']['wartosc'] > $prog_bezplatnej_wysylki ) {
      $bezplatna_dostawa = '';
  } else { 
      $bezplatna_dostawa = str_replace( '{KWOTA}', '<b>'.$GLOBALS['waluty']->WyswietlFormatCeny($prog_bezplatnej_wysylki, $_SESSION['domyslnaWaluta']['id'], true, false).'</b>', $GLOBALS['tlumacz']['INFO_BEZPLATNA_DOSTAWA'] );
      $DodatkoweInformacje['InfoWysylkaDarmo'] = true;
  }
} else {
  $bezplatna_dostawa = '';
}

$Zalogowany = 'nie';
if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
     $Zalogowany = 'tak';
}

//
// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $ProduktyKoszyka, $DodatkoweInformacje, $Zalogowany);
//
unset($ProduktyKoszyka);

$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
// meta tagi
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_KOSZYK']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$tpl->dodaj('__CSS_PLIK', ',listingi');

$ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

// wartosc koszyka
$srodek->dodaj('__WARTOSC_KOSZYKA', $GLOBALS['waluty']->PokazCene($ZawartoscKoszyka['brutto'], $ZawartoscKoszyka['netto'], 0, $_SESSION['domyslnaWaluta']['id']));

// waga produktow koszyka
$srodek->dodaj('__WAGA_KOSZYKA', number_format($ZawartoscKoszyka['waga'], 3, ',', ''));

unset($ZawartoscKoszyka);

// nastepna strona zamowienia
$isHTTPS = false;
if ( WLACZENIE_SSL == 'tak' ) {
    $isHTTPS = true;
}

if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 ) {
  $zamowienie_nastepny_krok = ( $isHTTPS ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/zamowienie-potwierdzenie.html';
} else {
  $zamowienie_nastepny_krok = ( $isHTTPS ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/zamowienie-logowanie.html';
}

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() > 0 ) {
  // modul wysylek i platnosci
  
  $srodek->dodaj('__WYBOR_PANSTWA', $lista_rozwijana_panstw);

  $srodek->dodaj('__WYBOR_WYSYLKI', $lista_radio_wysylek);

  $srodek->dodaj('__WYBOR_PLATNOSCI', $lista_radio_platnosci);

  $srodek->dodaj('__KOSZT_WYSYLKI', $calkowity_koszt_wysylki);

  $srodek->dodaj('__PODSUMOWANIE_ZAMOWIENIA', $podsumowanie_zamowienia);

  $srodek->dodaj('__PODSUMOWANIE_INFORMACJA', $GLOBALS['tlumacz']['INFO_WARTOSC_ZAMOWIENIA_PO_ZALOGOWANIU']);

  $srodek->dodaj('__DISPLAY_NONE', $ukryj_przycisk);

  $srodek->dodaj('__KALKULATOR_SANTANDER', $kalkulator_santander);
  $srodek->dodaj('__KALKULATOR_LUKAS', $kalkulator_lukas);
  $srodek->dodaj('__KALKULATOR_MBANK', $kalkulator_mbank);
  $srodek->dodaj('__KALKULATOR_PAYURATY', $kalkulator_payuraty);

  $srodek->dodaj('__KALKULATOR_CSS','');
  if ( $bylKalkulator == false ) {
       $srodek->dodaj('__KALKULATOR_CSS',' style="display:none"');
  }
  
  $srodek->dodaj('__CSS_PDF_KOSZYK','');
  if ( PDF_KOSZYK_POBRANIE_PDF == 'nie' ) {
       $srodek->dodaj('__CSS_PDF_KOSZYK',' style="display:none"');
  }  

  $srodek->dodaj('__BEZPLATNA_DOSTAWA', $bezplatna_dostawa);

  $srodek->dodaj('__ZAMOWIENIE_NASTEPNY_KROK', $zamowienie_nastepny_krok);

  if ( SYSTEM_PUNKTOW_STATUS == 'tak' && SYSTEM_PUNKTOW_STATUS_KUPOWANIA == 'tak' ) {
  
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
      if ( $punkty->suma >= SYSTEM_PUNKTOW_MIN_ZAMOWIENIA ) {
        if ( isset($DodatkoweInformacje['InfoPunktyKlienta']) && $DodatkoweInformacje['InfoPunktyKlienta'] ) {
          $srodek->dodaj('__INFO_PUNKTY', $info_punkty);
          $srodek->dodaj('__INFO_PUNKTY_DO_WYKORZYSTANIA', $info_punkty_do_wykorzystania);
        }
      }
      if ( isset($_SESSION['punktyKlienta']) ) {
        $info_punkty_wykorzystane = str_replace( '{ILOSC_PUNKTOW}', '<b>'.$_SESSION['punktyKlienta']['punkty_ilosc'].'</b>', $GLOBALS['tlumacz']['INFO_PUNKTY_WYKORZYSTANE'] );
        $srodek->dodaj('__INFO_PUNKTY_WYKORZYSTANE', $info_punkty_wykorzystane);
      }
    }
    
  }
  
  // produkty gratisowe
  $ListaProduktowGratisowych = '';
  //
  $Gratisy = Gratisy::TablicaGratisow( 'tak' );
  //
  if ( count($Gratisy) > 0 ) {
      ob_start();
      
      // listing wersji mobilnej
      if ( $_SESSION['mobile'] == 'tak' ) {    
      
          if (in_array( 'listing_gratisy.mobilne.php', $Wyglad->PlikiListingiLokalne )) {
              require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_gratisy.mobilne.php');
          }
          
        } else { 
        
          if (in_array( 'listing_gratisy.php', $Wyglad->PlikiListingiLokalne )) {
                require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_gratisy.php');
              } else {
                require('listingi/listing_gratisy.php');
          }
          
      }
      
      $ListaProduktowGratisowych = ob_get_contents();
      ob_end_clean();    
  }
  //
  $srodek->dodaj('__LISTING_PRODUKTY_GRATISOWE', $ListaProduktowGratisowych);  
  unset($ListaProduktowGratisowych, $Gratisy);
  
  //  
  
  // minimalne zamowienie dla grupy klientow
  $srodek->dodaj('__MINIMALNE_ZAMOWIENIE', '');

  $MinimalneZamowienieGrupy = Klient::MinimalneZamowienie();

  if ( $MinimalneZamowienieGrupy > 0 ) {

      $MinZamowienie = $GLOBALS['waluty']->PokazCeneBezSymbolu($MinimalneZamowienieGrupy,'',true);
      $WartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();

      if ( $WartoscKoszyka['brutto'] < $MinZamowienie ) {
           //
           $srodek->dodaj('__MINIMALNE_ZAMOWIENIE', '<strong>' .  $GLOBALS['tlumacz']['MINIMALNE_ZAMOWIENIE'] . ' <span>' . $GLOBALS['waluty']->WyswietlFormatCeny($MinZamowienie, $_SESSION['domyslnaWaluta']['id'], true, false) . '</span></strong>');
           $srodek->dodaj('__DISPLAY_NONE', 'style="display:none"');
           //
      }
      unset($MinZamowienie, $WartoscKoszyka);
      
  }   

  unset($MinimalneZamowienieGrupy); 

  // link uzywany w koszyku do przycisku kontynuuj zakupy
  $srodek->dodaj('__LINK_POPRZEDNIEJ_STRONY', $_SESSION['stat']['przed_koszykiem']);
  
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>