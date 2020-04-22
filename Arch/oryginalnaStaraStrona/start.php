<?php
// wczytanie ustawien inicjujacych system
require_once(dirname(__FILE__).'/ustawienia/init.php');

// jezeli sklep jest wylaczony
if ( INFO_WYLACZ_SKLEP == 'tak') {
     Wylaczenie::WylaczSklep();
}

// zmienna do okreslania ze nie jest aktualnie wyswietlana strona glowna
if (isset($WywolanyPlik) && $WywolanyPlik == 'strona_glowna') {
    $GLOBALS['stronaGlowna'] = true;
}

// Wczytanie skryptu js na stronie glownej do cash4free
if ( INTEGRACJA_OPENRATE_WLACZONY == 'tak' ) {
    if (isset($WywolanyPlik) && $WywolanyPlik == 'strona_glowna') {
        $_SESSION['stronaGlowna'] = true;
    } else {
        $_SESSION['stronaGlowna'] = false;
    }
}

// zmiana ustawienia jezeli jest pusty koszyk
if ( isset($_SESSION['koszyk']) && count($_SESSION['koszyk']) < 1 && $WywolanyPlik == 'koszyk' ) {
  unset($GLOBALS['kolumny']);
  $GLOBALS['kolumny'] = 'wszystkie';
}

// sprawdzi czy nie zmienil sie stan magazynowy produktu lub produkt nie jest wylaczony - musi wtedy zmienic wartosc koszyka
if ( isset($_SESSION['koszyk']) && count($_SESSION['koszyk']) > 0 && $WywolanyPlik != 'zamowienie_podsumowanie' ) {
    //
    $stanKoszyka = false;
    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        //
        $stanKoszyka = $GLOBALS['koszykKlienta']->SprawdzIloscProduktuMagazyn( $TablicaZawartosci['id'], true );
        //
    }
    if ( $stanKoszyka == true ) {
        //
        Funkcje::PrzekierowanieURL('koszyk.html');
        //
    }
    unset($stanKoszyka);
    //
}

// definiowanie szablonu
$tpl = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/strona_glowna.tp');

// pusta deklaracja
$tpl->dodaj('__LINK_CANONICAL', '');

// glowne definicje z sekcji head
$tpl->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);

$tpl->dodaj('__JEZYK_STRONY', $_SESSION['domyslnyJezyk']['kod']);

// strona glowna
$isHTTPS = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isHTTPS = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isHTTPS = true;
}
if ( $isHTTPS ) {
    $tpl->dodaj('__DOMENA', ADRES_URL_SKLEPU_SSL);
} else {
    $tpl->dodaj('__DOMENA', ADRES_URL_SKLEPU);
}


// plik javascript do wyswietlanego pliku
if (file_exists('javascript/'.$WywolanyPlik.'.jcs')) {
    $tpl->dodaj('__JS_PLIK', '<script type="text/javascript" src="javascript/' . $WywolanyPlik . '.php"></script>');
  } else {
    $tpl->dodaj('__JS_PLIK', '');
}

$kod_google_header  = "";

// kod weryfikacyjny Google dla webmasterow
if ( INTEGRACJA_GOOGLE_WERYFIKACJA != '' ) {
    $kod_google_header .= "<meta name=\"google-site-verification\" content=\"".INTEGRACJA_GOOGLE_WERYFIKACJA."\" />\n" . $kod_google_header;
}

$tpl->dodaj('__GOOGLE_WERYFIKACJA', $kod_google_header);

unset($kod_google_header);

// Modul Google Analytics
$kod_google_header  = "";
if ( INTEGRACJA_GOOGLE_WLACZONY == 'tak' && INTEGRACJA_GOOGLE_ID != '' ) {

    $ex = pathinfo($_SERVER['PHP_SELF']);
    if ( basename($_SERVER['PHP_SELF'],'.'.$ex['extension']) != 'zamowienie_podsumowanie' ) {

        if ( INTEGRACJA_GOOGLE_RODZAJ == 'universal' ) {

            $kod_google_header .= "<!-- Google Analytics -->\n";
            $kod_google_header .= "<script>\n";
            $kod_google_header .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";

            $kod_google_header .= "ga('create', '".INTEGRACJA_GOOGLE_ID."', 'auto');\n";
            $kod_google_header .= "ga('require', 'displayfeatures');\n";
            $kod_google_header .= "ga('send', 'pageview');\n";

            $kod_google_header .= "</script>\n";
            $kod_google_header .= "<!-- End Google Analytics -->\n";
        
        } else {

            $kod_google_header .= "<script type=\"text/javascript\">\n";

            $kod_google_header .= "    var _gaq = _gaq || [];\n";
            $kod_google_header .= "    _gaq.push(['_setAccount', '".INTEGRACJA_GOOGLE_ID."']);\n";
            $kod_google_header .= "    _gaq.push(['_setDomainName', '".str_replace('http://', '', ADRES_URL_SKLEPU)."']);\n";
            $kod_google_header .= "    _gaq.push(['_trackPageview']);\n\n";

            $kod_google_header .= "    (function() {\n";
            $kod_google_header .= "    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n";
            if ( INTEGRACJA_GOOGLE_ADWORDS == 'tak' ) {
                $kod_google_header .= "    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';\n";
            } else {
                $kod_google_header .= "    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n";
            }
            $kod_google_header .= "    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n";
            $kod_google_header .= "    })();\n";
            $kod_google_header .= "</script>\n";
        }

    }
    unset($ex);

}
$tpl->dodaj('__GOOGLE_ANALYTICS', $kod_google_header);
unset($kod_google_header);

// plik css do kalendarza - tylko dla wersji mobilnej
$tpl->dodaj('__CSS_KALENDARZ', '');

// Breadcrumb
$nawigacja = new Nawigacja;
$nawigacja->dodaj($GLOBALS['tlumacz']['STRONA_GLOWNA'], ADRES_URL_SKLEPU, 0);

$Wyglad = new Wyglad();

// jezeli nie jest urzadzanie mobilne
if ( $_SESSION['mobile'] == 'nie' ) {

    // baner popup
    if (( isset($GLOBALS['bannery']->info['POPUP']) && count($GLOBALS['bannery']->info['POPUP']) > 0 ) && ( !isset($_COOKIE['popup_shopGold']) || (int)$_COOKIE['popup_shopGold'] == 1 ))  {

      $tpl->dodaj('__JS_POPUP', '<script type="text/javascript" src="javascript/banner_popup.php"></script>');
      $tpl->dodaj('__CSS_POPUP', ',banner_popup');
      $tpl->dodaj('__TRESC_POPUP', $GLOBALS['bannery']->bannerWyswietlPopUp());
      
    } else {
      //
      if ( ( isset($GLOBALS['bannery']->info['POPUP']) && count($GLOBALS['bannery']->info['POPUP']) > 0 ) ) {
             //
             $WaznoscCookie = '0';
             if ( BANNER_POPUP_WAZNOSC_COOKIE > 0 && isset($_COOKIE['popup_shopGold_time']) && (int)$_COOKIE['popup_shopGold_time'] > 0 ) {
                  $WaznoscCookie = (int)$_COOKIE['popup_shopGold_time'];
             }
             //
             if ( (int)$_COOKIE['popup_shopGold'] > 0 ) {
                 setcookie("popup_shopGold", (int)$_COOKIE['popup_shopGold'] - 1, $WaznoscCookie, '/');
             }
             //
             unset($WaznoscCookie);
             //
      }
      //
      $tpl->dodaj('__JS_POPUP', '');
      $tpl->dodaj('__CSS_POPUP', '');
      $tpl->dodaj('__TRESC_POPUP', '');
    }

    // style css
    // style dodatkowe np listingow
    $tpl->dodaj('__CSS_PLIK', '');
    // jezeli nie jest strona glowna laduje css z podstronami
    $tpl->dodaj('__CSS_PLIK_GLOWNY', '');
    if ($GLOBALS['stronaGlowna'] == false) {
        $tpl->dodaj('__CSS_PLIK_GLOWNY', ',podstrony');
    }
    
    // wysuwany widget CENEO
    if ( ZAKLADKA_CENEO_WLACZONA == 'tak' && ZAKLADKA_CENEO_KOD != '') {
        $tpl->dodaj('__WIDGET_CENEO', ZAKLADKA_CENEO_KOD);
    } else {
        $tpl->dodaj('__WIDGET_CENEO', '');
    }

    // wysuwany widget Okazje.info
    if ( ZAKLADKA_OKAZJE_INFO_WLACZONA == 'tak' && ZAKLADKA_OKAZJE_INFO_KOD != '') {
        $tpl->dodaj('__WIDGET_OKAZJE_INFO', ZAKLADKA_OKAZJE_INFO_KOD);
    } else {
        $tpl->dodaj('__WIDGET_OKAZJE_INFO', '');
    }

    // wysuwany widget OPINEO
    if ( ZAKLADKA_OPINEO_WLACZONA == 'tak' && ZAKLADKA_OPINEO_KOD != '') {
        $tpl->dodaj('__WIDGET_OPINEO', ZAKLADKA_OPINEO_KOD);
    } else {
        $tpl->dodaj('__WIDGET_OPINEO', '');
    }

    // --------------------- definicje wygladu -----------------------------
    // ustalanie tla sklepu
    if (TLO_SKLEPU_RODZAJ == 'obraz') {
        $tpl->dodaj('__TLO_SKLEPU', "style=\"background:url('" . KATALOG_ZDJEC . "/" . TLO_SKLEPU . "') " . TLO_SKLEPU_POWTARZANIE . "; background-attachment:fixed;\"");
      } else { 
        $tpl->dodaj('__TLO_SKLEPU', 'style="background:#' . strtolower(TLO_SKLEPU) . '"');
    }

    // szerokosc sklepu
    $tpl->dodaj('__SZEROKOSC_SKLEPU', SZEROKOSC_SKLEPU);
    // szerokosc lewej kolumny + 15 na margines
    $tpl->dodaj('__SZEROKOSC_LEWEJ_KOLUMNY', SZEROKOSC_LEWEJ_KOLUMNY + 15);
    // szerokosc prawej kolumny + 15 na margines
    $tpl->dodaj('__SZEROKOSC_PRAWEJ_KOLUMNY', SZEROKOSC_PRAWEJ_KOLUMNY + 15);
    
    $SzerokoscSrodek = SZEROKOSC_SKLEPU;
    
    // moduly srodkowe nad czescia glowna sklepu z boxami   
    $tpl->dodaj('__MODULY_SRODKOWE_GORA', $Wyglad->SrodekSklepu( 'gora', (( $GLOBALS['stronaGlowna'] == true ) ? array(1,2) : array(1,3) ) ));
    
    // moduly srodkowe pod czescia glowna sklepu z boxami  
    $tpl->dodaj('__MODULY_SRODKOWE_DOL', $Wyglad->SrodekSklepu( 'dol', (( $GLOBALS['stronaGlowna'] == true ) ? array(1,2) : array(1,3) ) ));
    
    // moduly srodkowe w czesci glownej sklepu na podstronach
    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_GORA', '');
    $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_DOL', '');
    
    if ($GLOBALS['stronaGlowna'] != true ) {
        //
        $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_GORA', $Wyglad->SrodekSklepu( 'srodek', array(1,3), 'gora' ));
        $tpl->dodaj('__MODULY_SRODKOWE_PODSTRONA_DOL', $Wyglad->SrodekSklepu( 'srodek', array(1,3), 'dol' ));
        //
    }
    
    $tpl->dodaj('__LEWA_KOLUMNA', '');  
    
    $LewaKolumnaWlaczona = false;
    $PrawaKolumnaWlaczona = false;
    
    // czy tylko na podstronach
    if ($GLOBALS['stronaGlowna'] != true || ($GLOBALS['stronaGlowna'] == true && CZY_WLACZONA_LEWA_WSZEDZIE == 'nie')) {
    
        if (CZY_WLACZONA_LEWA_KOLUMNA == 'tak' && ($GLOBALS['kolumny'] == 'wszystkie' || $GLOBALS['kolumny'] == 'wszystkie_lewa')) {
            $SzerokoscSrodek = $SzerokoscSrodek - (SZEROKOSC_LEWEJ_KOLUMNY + 15);
            // boxy lewa kolumna
            $tpl->dodaj('__LEWA_KOLUMNA', $Wyglad->KolumnaBoxu('lewa'));
            //
            $LewaKolumnaWlaczona = true;
        }
        
    }
    
    $tpl->dodaj('__PRAWA_KOLUMNA', '');

    // czy tylko na podstronach
    if ($GLOBALS['stronaGlowna'] != true || ($GLOBALS['stronaGlowna'] == true && CZY_WLACZONA_PRAWA_WSZEDZIE == 'nie')) {
    
        if (CZY_WLACZONA_PRAWA_KOLUMNA == 'tak' && ($GLOBALS['kolumny'] == 'wszystkie' || $GLOBALS['kolumny'] == 'wszystkie_prawa')) {
            $SzerokoscSrodek = $SzerokoscSrodek - (SZEROKOSC_PRAWEJ_KOLUMNY + 15);
            // boxy prawa kolumna
            $tpl->dodaj('__PRAWA_KOLUMNA', $Wyglad->KolumnaBoxu('prawa'));
            //
            $PrawaKolumnaWlaczona = true;
        }
        
    }
    
    if ( $LewaKolumnaWlaczona == false && $PrawaKolumnaWlaczona == false ) {
         $GLOBALS['kolumny'] = 'srodkowa';
    }
    if ( $LewaKolumnaWlaczona == false && $PrawaKolumnaWlaczona == true ) {
         $GLOBALS['kolumny'] = 'wszystkie_prawa';
    }
    if ( $LewaKolumnaWlaczona == true && $PrawaKolumnaWlaczona == false ) {
         $GLOBALS['kolumny'] = 'wszystkie_lewa';
    }
    
    unset($LewaKolumnaWlaczona, $PrawaKolumnaWlaczona);

    $tpl->dodaj('__SZEROKOSC_SRODKOWEJ_KOLUMNY', $SzerokoscSrodek);

    // uzywane w niektorych szablonach jezeli srodek ma miec margines w stosunku do sklepu
    $tpl->dodaj('__SZEROKOSC_SRODKOWEJ_KOLUMNY_MINUS_10', $SzerokoscSrodek - 10);
    $tpl->dodaj('__SZEROKOSC_SRODKOWEJ_KOLUMNY_MINUS_20', $SzerokoscSrodek - 20);
    
    // preloader obrazkow
    $tpl->dodaj('__FUNKCJA_PRELOADERA', '');
    if ( PRELOAD_OBRAZKOW == 'tak' && WygladMobilny::UrzadzanieMobilne() == false ) {
        // ladowanie obrazkow
        $tpl->dodaj('__FUNKCJA_PRELOADERA', $Wyglad->PrzegladarkaJavaScript( "$.ZaladujObrazki(false);" ));   
    }
    
    // ikony jezykow
    $tpl->dodaj('__ZMIANA_JEZYKA', $Wyglad->ZmianaJezyka());
    
} else {

    $tpl->dodaj('__LOGO_SKLEPU_MOBILNE', '<a id="LinkLogo" href="/"><img src="' . KATALOG_ZDJEC . '/' . NAGLOWEK_MOBILNY . '" alt="' . DANE_NAZWA_FIRMY_SKROCONA . '" title="' . DANE_NAZWA_FIRMY_SKROCONA . '" /></a>');

    $WygladMobilny = new WygladMobilny();
    
    // jezyki
    $tpl->dodaj('__MOBILE_JEZYKI', $WygladMobilny->MobilnyZmianaJezyka());    

    // box kategorii
    $tpl->dodaj('__MOBILE_BOX_KATEGORIE', $WygladMobilny->BoxKategorie());  
    
    // box producentow
    $tpl->dodaj('__MOBILE_BOX_PRODUCENCI', $WygladMobilny->BoxProducenci());  
    
    // modul aktualnosci
    $tpl->dodaj('__MOBILE_MODUL_AKTUALNOSCI', $WygladMobilny->ModulAktualnosci());     
    
    // modul nowosci
    $tpl->dodaj('__MOBILE_MODUL_NOWOSCI', $WygladMobilny->ModulProduktow('nowosci', MOBILNY_ILE_NOWOSCI)); 

    // modul promocje
    $tpl->dodaj('__MOBILE_MODUL_PROMOCJE', $WygladMobilny->ModulProduktow('promocje', MOBILNY_ILE_PROMOCJI)); 

    // modul polecane
    $tpl->dodaj('__MOBILE_MODUL_POLECANE', $WygladMobilny->ModulProduktow('polecane', MOBILNY_ILE_POLECANE)); 
    
    // modul hity
    $tpl->dodaj('__MOBILE_MODUL_HITY', $WygladMobilny->ModulProduktow('hity', MOBILNY_ILE_HITOW));    
    
}    

// moduly stale
$ModulyStale = $Wyglad->ModulyStale();
$tpl->dodaj('__MODULY_STALE', $ModulyStale);
unset($ModulyStale);

// logo/naglowek
if (NAGLOWEK_RODZAJ == 'kod') {
    $tpl->dodaj('__LOGO_SKLEPU', htmlspecialchars_decode(NAGLOWEK));
  } else {   
    $tpl->dodaj('__LOGO_SKLEPU', '<a id="LinkLogo" href="/"><img src="' . KATALOG_ZDJEC . '/' . NAGLOWEK . '" alt="' . DANE_NAZWA_FIRMY_SKROCONA . '" title="' . DANE_NAZWA_FIRMY_SKROCONA . '" /></a>');
}

// gorne menu
$tpl->dodaj('__GORNE_MENU', '<ul>' . $Wyglad->Linki('gorne_menu') . '</ul>');

// dolne menu
$tpl->dodaj('__DOLNE_MENU', '<ul>' . $Wyglad->Linki('dolne_menu') . '</ul>');

// stopka

// pierwsza kolumna stopki
$tpl->dodaj('__PIERWSZA_KOLUMNA_STOPKI_NAGLOWEK', $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_PIERWSZA']);
$tpl->dodaj('__PIERWSZA_KOLUMNA_STOPKI_LINKI', '<ul>' . $Wyglad->Linki('pierwsza_stopka') . '</ul>');

// druga kolumna stopki
$tpl->dodaj('__DRUGA_KOLUMNA_STOPKI_NAGLOWEK', $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_DRUGA']);
$tpl->dodaj('__DRUGA_KOLUMNA_STOPKI_LINKI', '<ul>' . $Wyglad->Linki('druga_stopka') . '</ul>');

// trzecia kolumna stopki
$tpl->dodaj('__TRZECIA_KOLUMNA_STOPKI_NAGLOWEK', $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_TRZECIA']);
$tpl->dodaj('__TRZECIA_KOLUMNA_STOPKI_LINKI', '<ul>' . $Wyglad->Linki('trzecia_stopka') . '</ul>');

// czwarta kolumna stopki
$tpl->dodaj('__CZWARTA_KOLUMNA_STOPKI_NAGLOWEK', $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_CZWARTA']);
$tpl->dodaj('__CZWARTA_KOLUMNA_STOPKI_LINKI', '<ul>' . $Wyglad->Linki('czwarta_stopka') . '</ul>');

// piata kolumna stopki
$tpl->dodaj('__PIATA_KOLUMNA_STOPKI_NAGLOWEK', $GLOBALS['tlumacz']['STOPKA_NAGLOWEK_PIATA']);
$tpl->dodaj('__PIATA_KOLUMNA_STOPKI_LINKI', '<ul>' . $Wyglad->Linki('piata_stopka') . '</ul>');

// schowek
$tpl->dodaj('__ILOSC_PRODUKTOW_SCHOWKA', (isset($GLOBALS['schowekKlienta']->IloscProduktow) ? $GLOBALS['schowekKlienta']->IloscProduktow: ''));

// koszyk
$ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
$tpl->dodaj('__ILOSC_PRODUKTOW_KOSZYKA', $ZawartoscKoszyka['ilosc']);
$tpl->dodaj('__WARTOSC_KOSZYKA_BRUTTO', $GLOBALS['waluty']->WyswietlFormatCeny($ZawartoscKoszyka['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false));  

// kompresja styli css
$tpl->dodaj('__KOMPRESJA_CSS', (( KOMPRESJA_CSS == 'tak' ) ? 'css' : 'ncss' ));

// liczniki odwiedzin
$tpl->dodaj('__ILOSC_ODWIEDZIN', $GLOBALS['licznikOdwiedzinSklepu']);
unset($GLOBALS['licznikOdwiedzinSklepu']);

if ( LICZNIK_ODWIEDZIN_DATA == '' ) {
    $GLOBALS['db']->open_query("UPDATE settings SET value = '" . time() . "' WHERE code = 'LICZNIK_ODWIEDZIN_DATA'");
    $tpl->dodaj('__DATA_LICZNIKA_ODWIEDZIN', date('d-m-Y',time()));
} else {
    $tpl->dodaj('__DATA_LICZNIKA_ODWIEDZIN', date('d-m-Y',LICZNIK_ODWIEDZIN_DATA));
}

$tpl->dodaj('__INFO_SG',Funkcje::Sg('PGEgaHJlZj0iaHR0cDovL3d3dy5zaG9wR29sZC5wbCI+T3Byb2dyYW1vd2FuaWUgc2tsZXB1IHNob3Bnb2xkLnBsPC9hPg=='));
?>