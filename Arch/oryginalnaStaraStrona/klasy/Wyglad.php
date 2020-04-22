<?php

class Wyglad {

    public function __construct() {
        //
        // Tworzenie tablicy z plikami boxow i modulow
        //
        // pliki z boxami
        $this->PlikiBoxy = $this->pobierzPliki('boxy');
        // pliki z boxami
        $this->PlikiBoxyLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/boxy_lokalne');    
        // pliki indywidualne wygladu szablonu z boxami
        $this->PlikiBoxySzablon = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/boxy_wyglad');           
        // pliki z modulami
        $this->PlikiModuly = $this->pobierzPliki('moduly');  
        // pliki z modulami w szablonie
        $this->PlikiModulySzablon = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/moduly_wyglad');         
        // pliki z modulami lokalnymi
        $this->PlikiModulyLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/moduly_lokalne'); 
        // pliki z listingami lokalnymi
        $this->PlikiListingiLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne');   
        // pliki z modulami stalymi
        $this->PlikiModulyStale = $this->pobierzPliki('moduly_stale'); 
        // pliki z tresciami lokalnymi
        $this->PlikiTresciLokalne = $this->pobierzPliki('szablony/'.DOMYSLNY_SZABLON.'/tresc');          
        //   
        // Tworzenie tablic z nazwami do menu
        //
        // strony informacyjne
        $this->StronyInformacyjne = $this->PobierzNazwyMenu('strona'); 
        // galerie
        $this->Galerie = $this->PobierzNazwyMenu('galeria');         
        // formularze
        $this->Formularze = $this->PobierzNazwyMenu('formularz'); 
        // kategorie artykulow
        $this->KategorieArtykulow = $this->PobierzNazwyMenu('kategoria');         
        // artykuly
        $this->Artykuly = $this->PobierzNazwyMenu('artykul');          
    }
    
    // funkcja do listowania plikow z katalogow
    public function pobierzPliki( $katalog ) {
        //
        $wynik = array();
        //
        if (is_dir( $katalog )) {
            if ($dh = opendir( $katalog )) { 
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && !is_dir( $katalog . '/' . $file)) { $wynik[] = $file; }
                }                            
                closedir($dh);
            }      
        }    
        //
        return $wynik;
    }

    // funkcja zwraca boxy z lewej lub prawej kolumny
    public function KolumnaBoxu( $strona ) {
    
        // dodatkowy warunek wyswietlania 
        $warunek = ' and tb.box_localization in (';
        if ( $GLOBALS['stronaGlowna'] == true ) {
             $warunek .= '1,2';
             $przedrostek = '_glowna';
        }
        if ( $GLOBALS['stronaGlowna'] == false ) {
             $warunek .= '1,3';
             $przedrostek = '_podstrony';
        }        
        $warunek .= ')';
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('BoxyKolumn_' . $_SESSION['domyslnyJezyk']['kod'] . '_' . $strona . $przedrostek, CACHE_WYGLAD); 
        $ByloCache = false;

        if ( !$WynikCache ) {        

            $zapytanie = "SELECT tb.box_file,
                                 tb.box_pages_id,
                                 tb.box_code,
                                 tb.box_type,
                                 tb.box_sort,
                                 tb.box_column,
                                 tb.box_header,
                                 tb.box_theme,
                                 tb.box_theme_file,
                                 tb.box_rwd,
                                 tb.box_rwd_resolution,
                                 td.box_title,
                                 pd.pages_id,
                                 pd.pages_title,
                                 pd.pages_short_text,
                                 pd.pages_text,
                                 p.nofollow
                            FROM theme_box tb
                       LEFT JOIN theme_box_description td ON tb.box_id = td.box_id AND td.language_id = '".$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages_description pd ON pd.pages_id = tb.box_pages_id AND pd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages p ON p.pages_id = tb.box_pages_id
                           WHERE tb.box_column = '" . $strona . "' AND 
                                 tb.box_status = '1'" . $warunek . " " . (($_SESSION['rwd'] == 'tak') ? " AND tb.box_rwd = '1' " : "") . "
                        ORDER BY tb.box_sort";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sql);

          } else {
          
            $IleRekordow = count($WynikCache);
            $ByloCache = true;
            
        }

        $DoWyswietlania = '';

        if ($IleRekordow > 0) { 
        
            $Tablica = array();
        
            if ( !$WynikCache ) {
                while ($info = $sql->fetch_assoc()) {
                    $Tablica[] = $info;
                }
                //
                $GLOBALS['cache']->zapisz('BoxyKolumn_' . $_SESSION['domyslnyJezyk']['kod'] . '_' . $strona . $przedrostek, $Tablica, CACHE_WYGLAD);
            } else {
                $Tablica = $WynikCache;
            }        

            foreach ( $Tablica as $info ) {
                //
                $box = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/boxy_wyglad/box.tp', $info);
                if ($info['box_theme'] == '1') {
                    if (in_array( $info['box_theme_file'], $this->PlikiBoxySzablon )) {
                        unset($box);
                        $box = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/boxy_wyglad/' . $info['box_theme_file'], $info);
                    }
                //
                // dodatkowe sprawdzenie czy nie ma indywidualnego pliku dla szablonu
                } else if (in_array( str_replace('.php', '.tp', $info['box_file']), $this->PlikiBoxySzablon )) {
                    unset($box);
                    $box = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/boxy_wyglad/' . str_replace('.php', '.tp', $info['box_file']), $info);                    
                }
                
                //
                // rodzaj strony
                //
                $ZawartoscBoxu = '';
                switch ($info['box_type']) {
                    case "strona":
                        // jezeli jest tekst skrocony
                        if (strlen($info['pages_short_text']) > 10) {
                            //
                            $ZawartoscBoxu = $info['pages_short_text'];
                            //
                            if (strlen($info['pages_text']) > 10) {
                                $ZawartoscBoxu .= '<div class="StronaInfo"><a class="przycisk" ' . (($info['nofollow'] == 1) ? 'rel="nofollow" ' : '') . 'href="' . Seo::link_SEO( $info['pages_title'], $info['pages_id'], 'strona_informacyjna' ) . '">' . $GLOBALS['tlumacz']['PRZYCISK_PRZECZYTAJ_CALOSC'] . '</a></div>';
                            }
                          } else {
                            //
                            $ZawartoscBoxu = $info['pages_text'];
                            //
                        }
                        break; 
                    case "plik":
                        //
                        // sprawdza czy jest indywidlany box w szablonie
                        if (in_array( $info['box_file'], $this->PlikiBoxyLokalne )) {
                            //
                            ob_start();
                            require('szablony/'.DOMYSLNY_SZABLON.'/boxy_lokalne/'.$info['box_file']);
                            $_wynik = ob_get_contents();
                            ob_end_clean();        
                            $ZawartoscBoxu = $_wynik;
                            unset($_wynik);
                            //
                          } else if (in_array( $info['box_file'], $this->PlikiBoxy )) {
                            //
                            ob_start();
                            require('boxy/'.$info['box_file']);
                            $_wynik = ob_get_contents();
                            ob_end_clean();                         
                            $ZawartoscBoxu = $_wynik;
                            unset($_wynik);
                            //
                          } else {
                            //
                            $box->dodaj('__TRESC_BOXU', '... brak pliku boxu ...');
                            //                      
                        }
                        break;  
                    case "java":
                        $ZawartoscBoxu = $info['box_code'];
                        break;                     
                }              
                
                // wyswietla box tylko jezeli ma jakas zawartosc
                if ( !empty($ZawartoscBoxu) ) {
                
                    // dodawanie strzalki do rozwijania przy RWD
                    if ( $info['box_rwd_resolution'] == 2 && $_SESSION['rwd'] == 'tak' ) {
                        //
                        // jezeli jest opcja minimalizowania boxu to dodana do naglowka strzalke do rozwijania
                        $box->dodaj('__NAGLOWEK_BOXU', $info['box_title'] . '<span class="BoxRozwinZwin BoxRozwin"></span>');
                        //
                      } else {
                        //
                        $box->dodaj('__NAGLOWEK_BOXU', $info['box_title']);
                        //
                    }
                    
                    $box->dodaj('__TRESC_BOXU', $ZawartoscBoxu);
                    //
                    // ukrywanie w RWD przy malych rozdzielczosciach
                    if ( ($info['box_rwd_resolution'] == 1 || $info['box_rwd_resolution'] == 2) && $_SESSION['rwd'] == 'tak' ) {
                         //
                         // jezeli jest ukrywanie boxu przy malych rozdzielczosciach to doda inna klase (caly box w div)
                         if ( $info['box_rwd_resolution'] == 1 ) {
                              $DoWyswietlania .= '<div class="BoxRwdUkryj">' . "\r\n" . $box->uruchom() . '</div>' . "\r\n";
                         }
                         // jezeli jest tylko minimalizowanie przy malych rozdzielczosciach to tylko wstawi caly box w nowy div
                         if ( $info['box_rwd_resolution'] == 2 ) {
                              $DoWyswietlania .= '<div class="BoxRwd">' . "\r\n" . $box->uruchom() . '</div>' . "\r\n";
                         }                         
                         //
                       } else {
                         //
                         $DoWyswietlania .= $box->uruchom();
                         //
                    }
                }
                //
            }
            //
        }
        
        unset($WynikCache, $przedrostek, $warunek);
        
        if ( $ByloCache == false ) {  
            $GLOBALS['db']->close_query($sql); 
            unset($zapytanie, $info);
        }
        
        unset($ByloCache, $Tablica, $IleRekordow, $box);

        return $DoWyswietlania;

    }  
    
    // funkcja zwraca moduly z czesci srodkowej sklepu
    // miejsce wyswietlania
    // 1 - wszedzie (na kazdej podstronie)
    // 2 - tylko strona glowna
    // 3 - tylko podstrony
    public function SrodekSklepu( $pozycjaModulow = 'srodek', $miejsceWyswietlania = array(2), $podstrony = '' ) {
        global $SzerokoscSrodek;
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('SrodekSklepu_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_WYGLAD); 
        $ByloCache = false;

        if ( !$WynikCache ) {         

            $zapytanie = "SELECT m.modul_file,
                                 m.modul_pages_id,
                                 m.modul_code,
                                 m.modul_type,
                                 m.modul_sort,
                                 m.modul_header,
                                 m.modul_theme,
                                 m.modul_theme_file,  
                                 m.modul_rwd,
                                 m.modul_rwd_resolution,                                 
                                 m.modul_localization,
                                 m.modul_localization_position,
                                 m.modul_position,
                                 md.modul_title,
                                 pd.pages_id,
                                 pd.pages_title,
                                 pd.pages_short_text,
                                 pd.pages_text,
                                 p.nofollow
                            FROM theme_modules m
                       LEFT JOIN theme_modules_description md ON m.modul_id = md.modul_id AND md.language_id = '".$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages_description pd ON pd.pages_id = m.modul_pages_id AND pd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'
                       LEFT JOIN pages p ON p.pages_id = m.modul_pages_id
                           WHERE m.modul_status = '1' " . (($_SESSION['rwd'] == 'tak') ? " AND m.modul_rwd = '1' " : "") . "
                        ORDER BY m.modul_sort";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sql);

          } else {
          
            $IleRekordow = count($WynikCache);
            $ByloCache = true;
            
        }            

        $DoWyswietlania = '';

        if ($IleRekordow > 0) { 
        
            $Tablica = array();
        
            if ( !$WynikCache ) {
                while ($info = $sql->fetch_assoc()) {
                    $Tablica[] = $info;
                }
                //
                $GLOBALS['cache']->zapisz('SrodekSklepu_' . $_SESSION['domyslnyJezyk']['kod'], $Tablica, CACHE_WYGLAD);
            } else {
                $Tablica = $WynikCache;
            }        

            foreach ( $Tablica as $info ) {
                //
                if ( $info['modul_position'] == $pozycjaModulow && in_array( $info['modul_localization'], $miejsceWyswietlania ) && (( $podstrony != '' ) ? $info['modul_localization_position'] == $podstrony : true ) ) {
                    //
                    $modul = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/moduly_wyglad/modul.tp', $info);
                    if ($info['modul_theme'] == '1') {
                        if (in_array( $info['modul_theme_file'], $this->PlikiModulySzablon )) {
                            unset($box);
                            $modul = new Szablony('szablony/'.DOMYSLNY_SZABLON.'/moduly_wyglad/' . $info['modul_theme_file'], $info);
                        }
                    } 
                    
                    // rodzaj strony
                    //
                    $ZawartoscModulu = '';
                    switch ($info['modul_type']) {
                        case "strona":
                            // jezeli jest tekst skrocony
                            if (strlen($info['pages_short_text']) > 10) {
                                //
                                $ZawartoscModulu = $info['pages_short_text'];
                                //
                                if (strlen($info['pages_text']) > 10) {
                                    $ZawartoscModulu .= '<div class="StronaInfo"><a class="przycisk" ' . (($info['nofollow'] == 1) ? 'rel="nofollow" ' : '') . 'href="' . Seo::link_SEO( $info['pages_title'], $info['pages_id'], 'strona_informacyjna' ) . '">' . $GLOBALS['tlumacz']['PRZYCISK_PRZECZYTAJ_CALOSC'] . '</a></div>';
                                }
                              } else {
                                //
                                $ZawartoscModulu = $info['pages_text'];
                                //
                            }
                            break;                        
                            break; 
                        case "plik":
                            //
                            // sprawdza czy jest indywidlany box w szablonie
                            if (in_array( $info['modul_file'], $this->PlikiModulyLokalne )) {
                                //
                                ob_start();
                                require('szablony/'.DOMYSLNY_SZABLON.'/moduly_lokalne/'.$info['modul_file']);
                                $_wynik = ob_get_contents();
                                ob_end_clean();        
                                $ZawartoscModulu = $_wynik;
                                unset($_wynik);
                                //
                              } else if (in_array( $info['modul_file'], $this->PlikiModuly )) {
                                //
                                ob_start();
                                require('moduly/'.$info['modul_file']);
                                $_wynik = ob_get_contents();
                                ob_end_clean();                         
                                $ZawartoscModulu = $_wynik;
                                unset($_wynik);
                                //
                              } else {
                                //
                                $modul->dodaj('__TRESC_MODULU', '... brak pliku modułu ...');
                                //                      
                            }
                            break;  
                        case "java":
                            $ZawartoscModulu = $info['modul_code'];
                            break;                     
                    }              
                    
                    // wyswietla box tylko jezeli ma jakas zawartosc
                    if ( !empty($ZawartoscModulu) ) {
                        $modul->dodaj('__NAGLOWEK_MODULU', $info['modul_title']);
                        $modul->dodaj('__TRESC_MODULU', $ZawartoscModulu);
                        //
                        // ukrywanie w RWD przy malych rozdzielczosciach
                        if ( $info['modul_rwd_resolution'] == 1 && $_SESSION['rwd'] == 'tak' ) {
                             //
                             // jezeli jest ukrywanie modulu przy malych rozdzielczosciach to doda inna klase (caly modul w div)
                             $DoWyswietlania .= '<div class="ModulRwdUkryj">' . "\r\n" . $modul->uruchom() . '</div>' . "\r\n";                       
                             //
                           } else {
                             //
                             $DoWyswietlania .= $modul->uruchom();
                             //
                        }
                        //
                    }
                    //
                }
                //
            }
            //
        }

        unset($WynikCache);
        
        if ( $ByloCache == false ) {  
            $GLOBALS['db']->close_query($sql); 
            unset($zapytanie, $info);
        }
        
        unset($ByloCache, $Tablica, $IleRekordow, $box);

        return $DoWyswietlania;

    }  
      
    // funkcja zwraca linki gornego menu, dolnego menu, stopki
    public function Linki( $rodzaj, $tagPoczatek = '<li>', $tagKoniec = '</li>') {

        $ciecie = '';
        //
        switch ($rodzaj) {
            case "gorne_menu":
                $ciecie = GORNE_MENU;
                break; 
            case "dolne_menu":
                $ciecie = DOLNE_MENU;
                break;      
            case "pierwsza_stopka":
                $ciecie = STOPKA_PIERWSZA;
                break;
            case "druga_stopka":
                $ciecie = STOPKA_DRUGA;
                break;
            case "trzecia_stopka":
                $ciecie = STOPKA_TRZECIA;
                break;
            case "czwarta_stopka":
                $ciecie = STOPKA_CZWARTA;
                break;
            case "piata_stopka":
                $ciecie = STOPKA_PIATA;
                break;
        }

        $DoWyswietlania = '';
        //
        if ($ciecie != '') {
            //
            $pozycje_menu = explode(',',$ciecie);
            //
            for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {

                $strona = explode(';', $pozycje_menu[$x]);
                
                switch ($strona[0]) {
                    case "strona":
                        //
                        if ( !empty($this->StronyInformacyjne[$strona[1]][0]) ) {
                            //
                            if ( is_array($this->StronyInformacyjne[$strona[1]]) && count($this->StronyInformacyjne[$strona[1]]) == 3 ) {
                                //                        
                                $DoWyswietlania .= $tagPoczatek . '<a ' . $this->StronyInformacyjne[$strona[1]][2] . ' href="' . $this->StronyInformacyjne[$strona[1]][1] . '">' . $this->StronyInformacyjne[$strona[1]][0]. '</a>' . $tagKoniec;
                                //
                              } else {
                                //
                                $DoWyswietlania .= $tagPoczatek . '<a' . $this->StronyInformacyjne[$strona[1]][1] . 'href="' . Seo::link_SEO( $this->StronyInformacyjne[$strona[1]][0], $strona[1], 'strona_informacyjna') . '">' . $this->StronyInformacyjne[$strona[1]][0]. '</a>' . $tagKoniec;
                                //
                            }
                            //
                        }
                        //
                        break;
                    case "galeria":
                        //
                        if ( !empty($this->Galerie[$strona[1]]) ) {
                            $DoWyswietlania .= $tagPoczatek . '<a href="' . Seo::link_SEO( $this->Galerie[$strona[1]], $strona[1], 'galeria') . '">' . $this->Galerie[$strona[1]]. '</a>' . $tagKoniec;
                        }
                        //
                        break; 
                    case "formularz":
                        //
                        if ( !empty($this->Formularze[$strona[1]]) ) {
                            $DoWyswietlania .= $tagPoczatek . '<a href="' . ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO( $this->Formularze[$strona[1]], $strona[1], 'formularz') . '">' . $this->Formularze[$strona[1]]. '</a>' . $tagKoniec;
                        }
                        //
                        break; 
                    case "kategoria":
                        //
                        if ( !empty($this->KategorieArtykulow[$strona[1]]) ) {
                            $DoWyswietlania .= $tagPoczatek . '<a href="' . Seo::link_SEO( $this->KategorieArtykulow[$strona[1]], $strona[1], 'kategoria_aktualnosci') . '">' . $this->KategorieArtykulow[$strona[1]]. '</a>' . $tagKoniec;
                        }
                        //
                        break;   
                    case "artykul":
                        //
                        if ( !empty($this->Artykuly[$strona[1]]) ) {
                            $DoWyswietlania .= $tagPoczatek . '<a href="' . Seo::link_SEO( $this->Artykuly[$strona[1]], $strona[1], 'aktualnosc') . '">' . $this->Artykuly[$strona[1]]. '</a>' . $tagKoniec;
                        }
                        //
                        break; 
                    case "kategproduktow":
                        //
                        $NazwaKategorii = Kategorie::NazwaKategoriiId($strona[1]);
                        //
                        if ( !empty($NazwaKategorii) ) {
                            $DoWyswietlania .= $tagPoczatek . '<a href="' . Seo::link_SEO( $NazwaKategorii, $strona[1], 'kategoria') . '">' . $NazwaKategorii . '</a>' . $tagKoniec;
                        }
                        //
                        unset($NazwaKategorii);
                        //
                        break;                            
                    case "artkategorie":
                        //
                        if ( !empty($this->KategorieArtykulow[$strona[1]]) ) {
                            //
                            $DoWyswietlania .= $tagPoczatek . '<a href="' . Seo::link_SEO( $this->KategorieArtykulow[$strona[1]], $strona[1], 'kategoria_aktualnosci') . '">' . $this->KategorieArtykulow[$strona[1]] . '</a>';
                            //
                            $TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria($strona[1]);
                            //
                            if ( count($TablicaArtykulow) > 0 ) {
                                //
                                $DoWyswietlania .= '<ul>';
                                //
                                foreach ( $TablicaArtykulow as $Artykul ) {
                                    //
                                    $DoWyswietlania .= '<li>' . $Artykul['link'] . '</li>';
                                    //
                                }
                                //
                                $DoWyswietlania .= '</ul>';
                                //
                            }
                            //
                            unset($TablicaArtykulow);
                            //
                            $DoWyswietlania .= $tagKoniec;
                            //
                        }
                        //
                        break;    
                    case "grupainfo":
                        //
                        $GrupyStron = StronyInformacyjne::TablicaGrupInfo();
                        //
                        if ( isset( $GrupyStron[$strona[1]] ) ) {
                            //
                            $DoWyswietlania .= $tagPoczatek . '<span>' . $GrupyStron[$strona[1]]['nazwa'] . '</span>';
                            //
                            $TablicaStronInformacyjnych = StronyInformacyjne::TablicaStronInfoGrupa( $GrupyStron[$strona[1]]['kod'] );
                            //
                            if ( count($TablicaStronInformacyjnych) > 0 ) {
                                //
                                $DoWyswietlania .= '<ul>';
                                //                            
                                foreach ( $TablicaStronInformacyjnych as $Strona ) {
                                    //
                                    $DoWyswietlania .= '<li>' . $Strona['link'] . '</li>';
                                    //
                                }
                                //
                                unset($TablicaStronInformacyjnych);
                                //
                                $DoWyswietlania .= '</ul>';
                                //
                            }
                            //
                            unset($GrupyStron);
                            //
                            $DoWyswietlania .= $tagKoniec;
                            //
                        }
                        //
                        break; 
                    case "prodkategorie":
                        //
                        $NazwaKategorii = Kategorie::NazwaKategoriiId($strona[1]);
                        //
                        if ( !empty($NazwaKategorii) ) {
                            //
                            $DoWyswietlania .= $tagPoczatek . '<a href="' . Seo::link_SEO( $NazwaKategorii, $strona[1], 'kategoria') . '">' . $NazwaKategorii . '</a>';
                            //
                            $TablicaPodkategorii = Kategorie::TablicaKategorieParent($strona[1]);
                            //
                            if ( count($TablicaPodkategorii) > 0 ) {
                                //
                                $DoWyswietlania .= '<ul>';
                                //
                                foreach ( $TablicaPodkategorii as $Podkategoria ) {
                                    //
                                    $DoWyswietlania .= '<li>' .  '<a href="' . Seo::link_SEO( $Podkategoria['text'], $strona[1] . '_' . $Podkategoria['id'], 'kategoria') . '">' . $Podkategoria['text'] . '</a>' . '</li>';
                                    //
                                }
                                //
                                $DoWyswietlania .= '</ul>';
                                //
                            }
                            //
                            unset($TablicaPodkategorii);
                            //
                            $DoWyswietlania .= $tagKoniec;
                            //
                        }
                        //
                        unset($NazwaKategorii);
                        //
                        break;                         
                }
            }
            
            unset($pozycje_menu);
            //
        }
        unset($ciecie);
        //
        return $DoWyswietlania;
        //
    }   
    
    public function PobierzNazwyMenu( $rodzaj = '' ) {  

        $Wynik = array();
        $ByloCache = false;
        //
        switch ($rodzaj) {
            case "strona":
            
                $Strony = StronyInformacyjne::TablicaStronInfo();
                
                if ( count($Strony) > 0 ) {
                
                    foreach ($Strony as $Strona) {
                        //
                        if ( !empty($Strona['url']) ) {
                            $Wynik[ $Strona['id'] ] = array($Strona['tytul'], $Strona['url'], (($Strona['nofollow'] == 1) ? ' rel="nofollow" ' : ' '));
                          } else { 
                            $Wynik[ $Strona['id'] ] = array($Strona['tytul'], (($Strona['nofollow'] == 1) ? ' rel="nofollow" ' : ' '));
                        }
                        //                    
                    }
                
                }
                
                unset($Strony);
                $ByloCache = true;

                break;
                
            case "galeria":
            
                // cache zapytania
                $WynikCache = $GLOBALS['cache']->odczytaj('Galerie_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_GALERIE, true);            

                if ( !$WynikCache ) {
                            
                    // dodatkowy warunek dla grup klientow
                    $warunekTmp = " and (p.gallery_customers_group_id = '0'";
                    if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                        $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", p.gallery_customers_group_id)";
                    }
                    $warunekTmp .= ") "; 
                    //                              
                    $sql = $GLOBALS['db']->open_query("select p.id_gallery, pd.gallery_name from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' and p.gallery_status = '1'" . $warunekTmp);
                    unset($warunekTmp);
                    
                    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                        //
                        while ($info = $sql->fetch_assoc()) {
                            $Wynik[ $info['id_gallery'] ] = $info['gallery_name'];
                        }
                        //
                    }
 
                    $GLOBALS['cache']->zapisz('Galerie_' . $_SESSION['domyslnyJezyk']['kod'], $Wynik, CACHE_GALERIE, true);
                    
                } else {
                
                    $Wynik = $WynikCache;
                    $ByloCache = true;
                    
                }
                
                unset($WynikCache);        
                
                break; 
                
            case "formularz":
            
                // cache zapytania
                $WynikCache = $GLOBALS['cache']->odczytaj('Formularze_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_FORMULARZE, true);            

                if ( !$WynikCache ) {
                                    
                    // dodatkowy warunek dla grup klientow
                    $warunekTmp = " and (p.form_customers_group_id = '0'";
                    if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                        $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", p.form_customers_group_id)";
                    }
                    $warunekTmp .= ") "; 
                    //                                            
                    $sql = $GLOBALS['db']->open_query("select p.id_form, pd.form_name from form p, form_description pd where p.id_form = pd.id_form and language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' and p.form_status = '1'" . $warunekTmp);
                    unset($warunekTmp);
                    
                    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                        //
                        while ($info = $sql->fetch_assoc()) {
                            $Wynik[ $info['id_form'] ] = $info['form_name'];
                        }
                        //
                    }
                    
                    $GLOBALS['cache']->zapisz('Formularze_' . $_SESSION['domyslnyJezyk']['kod'], $Wynik, CACHE_FORMULARZE, true);
                    
                } else {
                
                    $Wynik = $WynikCache;
                    $ByloCache = true;
                    
                }
                
                unset($WynikCache);        
                                    
                break; 
                
            case "kategoria":
            
                $ArtykulyKategorie = Aktualnosci::TablicaKategorieAktualnosci();
                
                if ( count($ArtykulyKategorie) > 0 ) {
                
                    foreach ($ArtykulyKategorie as $Kategoria) {
                        //
                        $Wynik[ $Kategoria['id'] ] = $Kategoria['nazwa'];
                        //                    
                    }
                
                }
                
                unset($ArtykulyKategorie);               
                $ByloCache = true;     
                
                break;   
                
            case "artykul":
            
                $Artykuly = Aktualnosci::TablicaAktualnosci();
                
                if ( count($Artykuly) > 0 ) {
                
                    foreach ($Artykuly as $Artykul) {
                        //
                        $Wynik[ $Artykul['id'] ] = $Artykul['tytul'];
                        //                    
                    }
                
                }
                
                unset($Artykuly);          
                $ByloCache = true;     
                
                break;     

        }

        if ( $ByloCache == false ) {
            $GLOBALS['db']->close_query($sql);                                                 
            unset($info); 
        }
        
        return $Wynik;
        //
    }     
    
    public function ModulyStale() {  
        global $i18n;
        //
        $DoWyswietlenia = '';
        //
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('ModulyStale', CACHE_INNE);      

        $Tablica = array();
        
        $ByloCache = false;

        if ( !$WynikCache && !is_array($WynikCache) ) {
                          
            $sql = $GLOBALS['db']->open_query("select modul_file from theme_modules_fixed where modul_status = '1'");
            //
            while ($info = $sql->fetch_assoc()) {
                if (in_array( $info['modul_file'], $this->PlikiModulyStale )) {
                    //
                    $Tablica[] = $info;
                    //
                }
            }
            //
            
            $GLOBALS['cache']->zapisz('ModulyStale', $Tablica, CACHE_INNE);   
            
        } else {
        
            $Tablica = $WynikCache;
            $ByloCache = true;
            
        }        
        //
        if ( count($Tablica) > 0 ) {
            //
            foreach ( $Tablica as $info ) {
                //
                ob_start();
                require('moduly_stale/' . $info['modul_file']);
                $_wynik = ob_get_contents();
                ob_end_clean();                         
                $DoWyswietlenia .= $_wynik;
                unset($_wynik);                        
                //
            }
            //
        }
        //
        if ( $ByloCache == false ) {  
            $GLOBALS['db']->close_query($sql); 
            unset($info);
        }        
        //
        unset($Tablica);
        //
        return $DoWyswietlenia;
        //
    }
    
    public function TrescLokalna( $plik ) {
        //
        if (in_array( $plik . '.tp', $this->PlikiTresciLokalne )) {
            //
            return 'szablony/'.DOMYSLNY_SZABLON.'/tresc/' . $plik . '.tp';
            //
          } else {
            //
            return 'szablony/__tresc/' . $plik . '.tp';
            //
        }
        //
    }
    
    public static function ZmianaJezyka() {
    
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Jezyki', CACHE_INNE);                     
                          
        if ( !$WynikCache ) {

            $zapytanie_box = "SELECT languages_id, name, code, image 
                              FROM languages
                              WHERE status = '1' ORDER BY sort_order";

            $sql_box = $GLOBALS['db']->open_query($zapytanie_box);
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sql_box);
            
            unset($zapytanie_box);
            
          } else {
          
            $IleRekordow = count($WynikCache);
            
        }    

        if ($IleRekordow > 1) {
        
            //
            $Tablica = array();
            $Tresc = '';
            //
            if ( !$WynikCache ) {
                while ($info_box = $sql_box->fetch_assoc()) {
                    $Tablica[] = $info_box;
                }
                //
                $GLOBALS['cache']->zapisz('Jezyki', $Tablica, CACHE_INNE);      
            } else {
                $Tablica = $WynikCache;
            }
            
            foreach ($Tablica as $info_box) {
                 $Tresc .= '<span class="Flaga" id="Jezyk'.$info_box['languages_id'].'"><img '.( $info_box['languages_id'] == $_SESSION['domyslnyJezyk']['id'] ? '' : 'class="FlagaOff"').' src="' . KATALOG_ZDJEC . '/'.$info_box['image'].'" alt="'.$info_box['name'].'" title="'.$info_box['name'].'" /></span>';
            }
            //
            
            unset($Tablica);
            //
            
            if ( !$WynikCache ) {
                $GLOBALS['db']->close_query($sql_box); 
            }        
            
            return $Tresc;
            
        }
    
    }    
    
    public static function PrzegladarkaJavaScript( $js, $wymus = false ) {
    
        $serwer = empty($_SERVER['HTTP_USER_AGENT']) ? '' : strtolower($_SERVER['HTTP_USER_AGENT']);

        $przegladarka = '';
        
        if(preg_match('/(chrome)[ \/]([\w.]+)/', $serwer))
                $przegladarka = 'chrome';
        elseif(preg_match('/(safari)[ \/]([\w.]+)/', $serwer))
                $przegladarka = 'safari';
        elseif(preg_match('/(opera)[ \/]([\w.]+)/', $serwer))
                $przegladarka = 'opera';
        elseif(preg_match('/(msie)[ \/]([\w.]+)/', $serwer))
                $przegladarka = 'msie';
        elseif(preg_match('/(mozilla)[ \/]([\w.]+)/', $serwer))
                $przegladarka = 'mozilla';

        preg_match('/(' . $przegladarka . ')[ \/]([\w]+)/', $serwer, $wersja);

        if ( ( $przegladarka == 'opera' || $przegladarka == 'msie' || $przegladarka == 'mozilla' || $przegladarka == 'chrome' ) && $wymus == false ) {
             return '<script> $(document).ready(function() { ' . $js . ' }); </script>';
          } else {
             return '<script> $(window).load(function() { ' . $js . ' }); </script>';
        }
        
    }
  
} 

?>