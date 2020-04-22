<?php

class Bannery {

    public function Bannery() {

        // tablica wlaczonych bannerow
        $this->info = array();

        $this->BannerInfo();
    }

    public function BannerInfo() {
    
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Bannery_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_BANNERY);    
        
        if ( !$WynikCache && !is_array($WynikCache) ) {

            $zapytanie = "SELECT b.banners_id, b.banners_title, b.banners_url, b.banners_image, b.banners_image_text, b.banners_group, b.banners_html_text, b.banners_clicked, b.status, b.sort_order FROM banners b WHERE b.status = '1' AND (b.languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' OR b.languages_id = '0') ORDER BY b.sort_order";

            $sql = $GLOBALS['db']->open_query($zapytanie);

            while ( $info = $sql->fetch_assoc() ) {
                $this->info[$info['banners_group']][] = array('id_bannera' => $info['banners_id'],
                                                              'nazwa_bannera' => $info['banners_title'],
                                                              'grupa' => $info['banners_group'],
                                                              'adres_url_bannera' => $info['banners_url'],
                                                              'obrazek_bannera' => $info['banners_image'],
                                                              'obrazek_alt_bannera' => $info['banners_image_text'],
                                                              'tekst_bannera' => $info['banners_html_text'],
                                                              'klikniecia_bannera' => $info['banners_clicked']);
            }
            
            $GLOBALS['db']->close_query($sql);    
            unset($zapytanie, $info);
            
            $GLOBALS['cache']->zapisz('Bannery_' . $_SESSION['domyslnyJezyk']['kod'], $this->info, CACHE_BANNERY);
            
        } else {
        
            $this->info = $WynikCache;
        
        }
        
        unset($WynikCache);

    }

    public function bannerWyswietlStatyczny($banner) {

        // jezeli banner jest tylko w postaci tekstu
        if ( $banner['obrazek_bannera'] == '' && $banner['adres_url_bannera'] == '' && $banner['tekst_bannera'] != '' ) {
            echo htmlspecialchars_decode($banner['tekst_bannera']);
        }

        // jezeli banner jest tylko w postaci grafiki
        if ( $banner['obrazek_bannera'] != '' ) {

            if (file_exists(KATALOG_ZDJEC . '/'.$banner['obrazek_bannera']) && !empty($banner['obrazek_bannera'])) {
                // pobranie parametrow pliku
                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/'.$banner['obrazek_bannera']);

                // jezeli jest to obrazek
                if( $typ == '1' || $typ == '2' || $typ == '3' ) {
                    if ( $banner['adres_url_bannera'] != '' ) {
                        echo '<a href="reklama-b-' . $banner['id_bannera'] . '.html"><img src="' . KATALOG_ZDJEC . '/'.$banner['obrazek_bannera'].'" alt="'.$banner['nazwa_bannera'].'" '.$atrybuty.' /></a>';
                    } else {
                        echo '<img src="' . KATALOG_ZDJEC . '/'.$banner['obrazek_bannera'].'" alt="'.$banner['nazwa_bannera'].'" '.$atrybuty.' />';
                    }

                // jezeli jest to flash
                } else if ( $typ == '4' || $typ == '13' ) {
                    if ( $banner['adres_url_bannera'] != '' ) {
                        echo '<div onmousedown="klikSWFBanner('.$banner['id_bannera'].')">';
                        echo Funkcje::pokazFlash( $banner['nazwa_bannera'], KATALOG_ZDJEC . '/'.$banner['obrazek_bannera'] , $szerokosc, $wysokosc);
                        echo '</div>';
                    } else {
                        echo Funkcje::pokazFlash( $banner['nazwa_bannera'], '' . KATALOG_ZDJEC . '/'.$banner['obrazek_bannera'] , $szerokosc, $wysokosc);
                    }
                    // w kazdym innym wypadku koniec
                } else {
                    return;
                }
            } else {
                return;
            }
        }

        return;

    }
    
    public function bannerWyswietlAnimowany($banner) {

        // jezeli banner jest tylko w postaci tekstu
        if ( $banner['obrazek_bannera'] == '' ) {
                return;
        }

        // jezeli banner jest tylko w postaci grafiki
        if ( $banner['obrazek_bannera'] != '' ) {

            if (file_exists(KATALOG_ZDJEC . '/'.$banner['obrazek_bannera']) && !empty($banner['obrazek_bannera'])) {
                // pobranie parametrow pliku
                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/'.$banner['obrazek_bannera']);

                // jezeli jest to obrazek
                if( $typ == '1' || $typ == '2' || $typ == '3' ) {

                    echo '<a href="reklama-b-' . $banner['id_bannera'] . '.html">';
                    echo '<img src="' . KATALOG_ZDJEC . '/'.$banner['obrazek_bannera'].'" alt="'.$banner['nazwa_bannera'].'" />';
                    
                    if ( !empty($banner['obrazek_alt_bannera']) ) {
                         echo '<span>' . $banner['obrazek_alt_bannera'] . '</span>';
                    }
                    
                    echo '</a>';

                } else {
                
                    return;
                    
                }
                
            } else {
            
                return;
                
            }
            
        }

        return;

    }    
    
    public function bannerWyswietlAnimowanyFancySlider($banner) {

        // jezeli banner jest tylko w postaci tekstu
        if ( $banner['obrazek_bannera'] == '' ) {
                return;
        }

        // jezeli banner jest tylko w postaci grafiki
        if ( $banner['obrazek_bannera'] != '' ) {

            if (file_exists(KATALOG_ZDJEC . '/'.$banner['obrazek_bannera']) && !empty($banner['obrazek_bannera'])) {
                // pobranie parametrow pliku
                list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/'.$banner['obrazek_bannera']);

                // jezeli jest to obrazek
                if( $typ == '1' || $typ == '2' || $typ == '3' ) {

                    $alt = '';
                    if ( !empty($banner['obrazek_alt_bannera']) ) {
                        $alt = '<b>' . $banner['obrazek_alt_bannera'] . '</b>';
                    }
                    
                    echo '<img src="' . KATALOG_ZDJEC . '/'.$banner['obrazek_bannera'].'" alt="'.$alt.'" />';
                    
                    echo '<a href="reklama-b-' . $banner['id_bannera'] . '.html"></a>';
                    
                    unset($alt);

                } else {
                
                    return;
                    
                }
                
            } else {
            
                return;
                
            }
            
        }

        return;

    }       

    public function bannerWyswietlPopUp() {

        $tablica_bannerow = $this->info['POPUP'];

        $wybranyBanner = Funkcje::wylosujElementyTablicyJakoTablica($tablica_bannerow,'1');
        
        $wybranyBanner = $wybranyBanner[0];

        $wynik = '';

        if (file_exists(KATALOG_ZDJEC . '/'.$wybranyBanner['obrazek_bannera']) && !empty($wybranyBanner['obrazek_bannera'])) {
            list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize(KATALOG_ZDJEC . '/'.$wybranyBanner['obrazek_bannera']);

            // jezeli jest to obrazek
            if( $typ == '1' || $typ == '2' || $typ == '3' ) {
                if ( $wybranyBanner['adres_url_bannera'] != '' ) {
                    $tresc = '<a href="reklama-b-' . $wybranyBanner['id_bannera'] . '.html"><img src="' . KATALOG_ZDJEC . '/'.$wybranyBanner['obrazek_bannera'].'" alt="'.$wybranyBanner['nazwa_bannera'].'" '.$atrybuty.' /></a>';
                } else {
                    $tresc = '<img src="' . KATALOG_ZDJEC . '/'.$wybranyBanner['obrazek_bannera'].'" alt="'.$wybranyBanner['nazwa_bannera'].'" '.$atrybuty.' />';
                }

            // jezeli jest to flash
            } else if ( $typ == '4' || $typ == '13' ) {
                $tresc = Funkcje::pokazFlash( $wybranyBanner['nazwa_bannera'], KATALOG_ZDJEC . '/'.$wybranyBanner['obrazek_bannera'] , $szerokosc, $wysokosc);
                // w kazdym innym wypadku koniec
            } else {
                return;
            }

            $wynik = '
            <div id="popupZawartosc" style="display:none;">
                <div id="banerZamknij"><span></span></div>
                <div style="clear:both;"></div>
                '.$tresc.'
            </div>';
            
        } else if (!empty($wybranyBanner['tekst_bannera']))  {
            //
            $wynik = '
            <div id="popupZawartosc" style="display:none;">
                <div id="banerZamknij"><span></span></div>
                <div style="clear:both;"></div>
                <div id="tloPopUpText">'.html_entity_decode($wybranyBanner['tekst_bannera']).'</div>
            </div>';            
            //
        }

        return $wynik;

    }


} 

?>