<?php

class Aktualnosci {

    // zwraca tablice z aktualnosciami
    public static function TablicaAktualnosci() {
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('Aktualnosci_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_AKTUALNOSCI, true);
        //
        if ( !$WynikCache ) {
            // 
            // dodatkowy warunek dla grup klientow
            $warunekTmp = " and (n.newsdesk_customers_group_id = '0'";
            if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
                $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", n.newsdesk_customers_group_id)";
            }
            $warunekTmp .= ") "; 
            //            
            $zapytanie = "SELECT n.newsdesk_id,
                                 n.newsdesk_date_added,
                                 n.newsdesk_customers_group_id,
                                 nd.newsdesk_article_name,
                                 nd.newsdesk_article_short_text,
                                 nd.newsdesk_article_description,
                                 nd.newsdesk_article_viewed,
                                 nd.newsdesk_meta_title_tag,
                                 nd.newsdesk_meta_desc_tag,
                                 nd.newsdesk_meta_keywords_tag,
                                 ntc.categories_id,
                                 ncd.categories_name
                            FROM newsdesk n
                       LEFT JOIN newsdesk_description nd ON n.newsdesk_id = nd.newsdesk_id AND nd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'  
                       LEFT JOIN newsdesk_to_categories ntc ON n.newsdesk_id = ntc.newsdesk_id
                       LEFT JOIN newsdesk_categories_description ncd ON ncd.categories_id = ntc.categories_id AND ncd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'                             
                           WHERE n.newsdesk_status = '1'" . $warunekTmp . "
                        ORDER BY n.newsdesk_date_added desc, nd.newsdesk_article_name";
                        
            unset($warunekTmp);
                        
            $_sql = $GLOBALS['db']->open_query($zapytanie);
            
            $TablicaArtykulow = array();

            if ((int)$GLOBALS['db']->ile_rekordow($_sql) > 0) {
                //
                $licznik = 0;
                //
                while ($_info = $_sql->fetch_assoc()) {
                    //
                    $TablicaArtykulow[] = array('id' => $_info['newsdesk_id'],
                                                'tytul' => $_info['newsdesk_article_name'],
                                                'link' => '<a href="' . Seo::link_SEO( $_info['newsdesk_article_name'], $_info['newsdesk_id'], 'aktualnosc' ) . '">' . $_info['newsdesk_article_name'] . '</a>',
                                                'seo' => Seo::link_SEO( $_info['newsdesk_article_name'], $_info['newsdesk_id'], 'aktualnosc' ),
                                                'opis_krotki' => $_info['newsdesk_article_short_text'],
                                                'opis' => $_info['newsdesk_article_description'],
                                                'data' => date('d-m-Y',strtotime($_info['newsdesk_date_added'])),
                                                'id_kategorii' => $_info['categories_id'],
                                                'nazwa_kategorii' => $_info['categories_name'],
                                                'wyswietlenia' => $_info['newsdesk_article_viewed'],
                                                'meta_tytul' => $_info['newsdesk_meta_title_tag'],
                                                'meta_opis' => $_info['newsdesk_meta_desc_tag'],
                                                'meta_slowa' => $_info['newsdesk_meta_keywords_tag']);
                    //
                }
                unset($_info);
                //
            }
            
            $GLOBALS['db']->close_query($_sql); 
            unset($zapytanie); 
            //
            $GLOBALS['cache']->zapisz('Aktualnosci_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaArtykulow, CACHE_AKTUALNOSCI, true);
            //
        } else { 
            //
            if (count($WynikCache)) {
                $TablicaArtykulow = $WynikCache;
            }
            //
        }            
        
        return $TablicaArtykulow;
        
    }
    
    // zwraca tablice ze artykulami z konkretnej kategorii - id
    public static function TablicaAktualnosciKategoria( $IdKategorii = 0, $ilosc = 9999 ) {

        $TablicaArtykulowKategorii = array();
        
        $TablicaArtykulow = Aktualnosci::TablicaAktualnosci();
        
        if ( count($TablicaArtykulow) > 0 ) {
        
            $licznik = 0;
            foreach ( $TablicaArtykulow as $Artykul ) {
                //
                if ( $licznik < $ilosc ) {
                    //
                    if ( $Artykul['id_kategorii'] == $IdKategorii ) {
                         $TablicaArtykulowKategorii[] = $Artykul;
                         //
                         $licznik++;
                         //                         
                    }
                    //
                } else {
                    //
                    break;
                    //
                }
                //
            }
            
        }
        
        unset($TablicaArtykulow);
        
        return $TablicaArtykulowKategorii;    

    }
    
    // zwraca tablice z okreslona iloscia artykulow
    public static function TablicaAktualnosciLimit( $ilosc = 9999 ) {

        $TablicaArtykulowIlosc = array();
        
        $TablicaArtykulow = Aktualnosci::TablicaAktualnosci();
        
        if ( count($TablicaArtykulow) > 0 ) {
        
            $licznik = 0;
            foreach ( $TablicaArtykulow as $Artykul ) {
                //
                if ( $licznik < $ilosc ) {
                    //
                    $TablicaArtykulowIlosc[] = $Artykul;
                    //
                } else {
                    //
                    break;
                    //
                }
                //
                $licznik++;
                //
            }
        
        }
        
        unset($TablicaArtykulow);
        
        return $TablicaArtykulowIlosc;    

    }    
    
    // zwraca tablice z danymi aktualnosci o konkretnym ID
    public static function AktualnoscId( $id ) {
        //
        $WynikArtykul = '';
        
        $TablicaArtykulow = Aktualnosci::TablicaAktualnosci();
        
        if ( count($TablicaArtykulow) > 0 ) {
        
            foreach ( $TablicaArtykulow as $Artykul ) {
                //
                if ( $Artykul['id'] == $id ) {
                     $WynikArtykul = $Artykul;
                     break;
                }
                //
            }
            
        }
        
        unset($TablicaArtykulow);
        
        return $WynikArtykul;
        
    }   

    // zwraca tablice z kategoriami aktualnosci
    public static function TablicaKategorieAktualnosci( $tylkoAktywne = true ) {
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('Aktualnosci_Kategorie_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_AKTUALNOSCI);
        //
        if ( !$WynikCache ) {
            //        
            $zapytanie = "SELECT nc.categories_id,
                                 nc.categories_image,
                                 ncd.categories_name,
                                 ncd.categories_description,
                                 ncd.categories_meta_title_tag,
                                 ncd.categories_meta_desc_tag,
                                 ncd.categories_meta_keywords_tag
                            FROM newsdesk_categories nc
                       LEFT JOIN newsdesk_categories_description ncd ON nc.categories_id = ncd.categories_id AND ncd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'                
                        ORDER BY ncd.categories_name";
                        
            $_sql = $GLOBALS['db']->open_query($zapytanie);
            
            $TablicaKategoriiArtykulow = array();

            if ((int)$GLOBALS['db']->ile_rekordow($_sql) > 0) {
                //
                $licznik = 0;
                //
                while ($_info = $_sql->fetch_assoc()) {
                    //
                    $TablicaKategoriiArtykulow[ $_info['categories_id'] ] = array('id' => $_info['categories_id'],
                                                                                  'foto' => $_info['categories_image'],
                                                                                  'nazwa' => $_info['categories_name'],
                                                                                  'link' => '<a href="' . Seo::link_SEO( $_info['categories_name'], $_info['categories_id'], 'kategoria_aktualnosci' ) . '">' . $_info['categories_name'] . '</a>',
                                                                                  'seo' => Seo::link_SEO( $_info['categories_name'], $_info['categories_id'], 'kategoria_aktualnosci' ),
                                                                                  'opis' => $_info['categories_description'],
                                                                                  'meta_tytul' => $_info['categories_meta_title_tag'],
                                                                                  'meta_opis' => $_info['categories_meta_desc_tag'],
                                                                                  'meta_slowa' => $_info['categories_meta_keywords_tag']);
                    //
                }
                unset($_info);
                //
                /*
                // usunie kategorie w ktorych nie ma artykulow
                if ( $tylkoAktywne == true ) {
                    //
                    $WszystkieArtykuly = Aktualnosci::TablicaAktualnosci();
                    //                    
                    foreach ( $TablicaKategoriiArtykulow as $KategoriaAktualnosci ) {
                    
                        $saArtykuly = false;
                        foreach ( $WszystkieArtykuly as $Artykul ) {
                            //
                            if ( $Artykul['id_kategorii'] == $KategoriaAktualnosci['id'] ) {
                                 $saArtykuly = true;
                                 break;
                            }
                            //
                        }
                        
                        if ( $saArtykuly == false ) {
                             //unset( $TablicaKategoriiArtykulow[ $KategoriaAktualnosci['id'] ] );
                        }
                    
                    }
                    //
                    unset($WszystkieArtykuly);
                    //
                }
                */
                //
            }
            
            $GLOBALS['db']->close_query($_sql); 
            unset($zapytanie); 
            //
            $GLOBALS['cache']->zapisz('Aktualnosci_Kategorie_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaKategoriiArtykulow, CACHE_AKTUALNOSCI);
            //
        } else { 
            //
            if (count($WynikCache)) {
                $TablicaKategoriiArtykulow = $WynikCache;
            }
            //
        }            
        
        return $TablicaKategoriiArtykulow;   
        
    }
    
    // zwraca tablice z danymi aktualnosci o konkretnym ID
    public static function KategoriaAktualnoscId( $id ) {
        //
        $WynikKategoriaArtykul = '';
        
        $TablicaKategoriiArtykulow = Aktualnosci::TablicaKategorieAktualnosci();
        
        if ( count($TablicaKategoriiArtykulow) > 0 ) {
        
            foreach ( $TablicaKategoriiArtykulow as $Kategoria ) {
                //
                if ( $Kategoria['id'] == $id ) {
                     $WynikKategoriaArtykul = $Kategoria;
                     break;
                }
                //
            }
            
        }
        
        unset($TablicaKategoriiArtykulow);
        
        return $WynikKategoriaArtykul;
        
    }       
    
}
?>