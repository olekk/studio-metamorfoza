<?php

class Filtry {

    static public function IdProduktowDlaFiltrow( $typ = 'kategoria', $id = 0 ) {
    
        // jezeli jest kategoria
        if ($typ == 'kategoria') {
            $ZapytanieWarunkowe = "SELECT p.products_id
                                              FROM products p
                                         LEFT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                                         LEFT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                                             WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . " AND c.categories_id in (" . $id . ")";
        }
        
        // jezeli jest producent
        if ($typ == 'producent') {
            $ZapytanieWarunkowe = "SELECT p.products_id
                                              FROM products p
                                         LEFT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                                         LEFT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                                             WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . " AND p.manufacturers_id = '" . $id . "'";
        }
        
        // jezeli sa promocje
        if ($typ == 'promocje') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('PromocjeProste', CACHE_PROMOCJE, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlPromocjeProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', $WynikCache);
            }
            
            unset($WynikCache);
        }      

        // jezeli sa nowosci
        if ($typ == 'nowosci') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('NowosciProste', CACHE_NOWOSCI, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlNowosciProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', $WynikCache);
            }
            
            unset($WynikCache);        
        }   

        // jezeli sa polecane
        if ($typ == 'polecane') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('PolecaneProste', CACHE_POLECANE, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlPolecaneProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', $WynikCache);
            }
            
            unset($WynikCache);          
        }      

        // jezeli sa hity
        if ($typ == 'hity') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('PolecaneProste', CACHE_HITY, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlNaszHitProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', $WynikCache);
            }
            
            unset($WynikCache);         
        }    

        // jezeli sa bestsellery
        if ($typ == 'bestsellery') {
            $ZapytanieWarunkowe = Produkty::SqlBestselleryProste();
        }          
        
        // jezeli sa oczekiwane
        if ($typ == 'oczekiwane') {
            //
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('OczekiwaneProste', CACHE_OCZEKIWANE, true);
            
            if ( !$WynikCache ) {
                 $ZapytanieWarunkowe = Produkty::SqlOczekiwaneProste();
               } else {
                 $WynikCache = array_unique($WynikCache);
                 $ZapytanieWarunkowe = implode(',', $WynikCache);
            }
            
            unset($WynikCache);             
        }         

        return $ZapytanieWarunkowe;

    }

    // zwraca tablice z cechami dla danych id kategorii lub producenta
    static public function FiltrCech( $id = 0, $typ = 'kategoria' ) {  
        //
        $TablicaWyniku = array();
        //
        // generuje tylko jezeli nie mobilne
        if ( $_SESSION['mobile'] != 'tak' ) {
            // 
            $TablicaCech = "SELECT DISTINCT pa.options_id AS IdCechy,
                                            po.products_options_name AS NazwaCechy,
                                            po.products_options_images_enabled
                                       FROM products_attributes pa, 
                                            products_options po
                                      WHERE po.products_options_id = pa.options_id AND 
                                            po.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' AND
                                            po.products_options_filter = '1' AND 
                                            pa.products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                   ORDER BY po.products_options_sort_order";

            $TablicaWartosci = "SELECT DISTINCT pa.options_id AS IdCechy,
                                                pa.options_values_id AS IdWartosci,
                                                pov.products_options_values_name AS Wartosc,
                                                pov.products_options_values_thumbnail AS ObrazekCechy
                                           FROM products_attributes pa, 
                                                products_options po,
                                                products_options_values pov,
                                                products_options_values_to_products_options ptp
                                          WHERE pov.products_options_values_id = pa.options_values_id AND
                                                pa.options_id = po.products_options_id AND
                                                po.products_options_filter = '1' AND
                                                pov.products_options_values_id = ptp.products_options_values_id AND
                                                pov.products_options_values_status = '1' AND 
                                                pov.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' AND 
                                                pa.products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                       ORDER BY po.products_options_sort_order, ptp.products_options_values_sort_order";                          

            $sql = $GLOBALS['db']->open_query($TablicaWartosci);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //
                // tworzenie tablicy z wartosciami cech
                while ($info = $sql->fetch_assoc()) {
                    $TablicaWyniku[ $info['IdCechy'] ][ $info['IdWartosci'] ] = array( $info['Wartosc'], $info['ObrazekCechy'] );                
                }  
                //
                $GLOBALS['db']->close_query($sql);
                unset($zapytanie, $info);             
                //
                $sql = $GLOBALS['db']->open_query($TablicaCech);
                //
                // dodawanie do tablicy z wartosciami cech nazwy cechy
                while ($info = $sql->fetch_assoc()) {
                    $TablicaWyniku[ $info['IdCechy'] ][ 'nazwa' ] = $info['NazwaCechy']; 
                    // jezeli jest obrazkowa cecha
                    if ( $info['products_options_images_enabled'] == 'true' ) {
                         $TablicaWyniku[ $info['IdCechy'] ][ 'obrazek' ] = 'tak'; 
                       } else {
                         $TablicaWyniku[ $info['IdCechy'] ][ 'obrazek' ] = 'nie'; 
                    }
                    //
                }  
                //
                $GLOBALS['db']->close_query($sql);
                unset($zapytanie, $info);             
                //                
            }

            unset($TablicaCech, $TablicaWartosci);
            
        }

        // sprawdzanie pustych wpisow
        foreach ( $TablicaWyniku as $Klucz => $PozycjaCecha ) {
            //
            if ( count($PozycjaCecha) == 2 || !isset($PozycjaCecha['nazwa']) ) {
                 unset($TablicaWyniku[ $Klucz ]);
            }
            //
        }
        
        return $TablicaWyniku;
    }
    
    // zwraca tablice z dodatkowymi polami dla produktow dla danych id kategorii lub producenta
    static public function FiltrDodatkowePola( $id = 0, $typ = 'kategoria' ) {   
        //
        $TablicaWyniku = array();
        //
        // generuje tylko jezeli nie mobilne
        if ( $_SESSION['mobile'] != 'tak' ) {
            //        
            $TablicaPola = "SELECT products_extra_fields_id AS IdPola, 
                                   products_extra_fields_name AS NazwaPola
                              FROM products_extra_fields 
                             WHERE products_extra_fields_status = '1' AND
                                   products_extra_fields_filter = '1' AND
                                   (languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' OR languages_id = '0')
                          ORDER BY products_extra_fields_order";
                      
            $TablicaPolaWartosci = "SELECT DISTINCT products_extra_fields_id AS IdPola, 
                                                    products_id AS IdProduktu, 
                                                    products_extra_fields_value AS Wartosc
                                               FROM products_to_products_extra_fields 
                                              WHERE products_extra_fields_id in (
                                                        SELECT products_extra_fields_id 
                                                          FROM products_extra_fields 
                                                         WHERE products_extra_fields_status = '1' AND
                                                               products_extra_fields_filter = '1' AND
                                                               (languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' OR languages_id = '0') ) AND
                                                               products_id in (" . Filtry::IdProduktowDlaFiltrow($typ, $id) . ")
                                           ";      

            $sql = $GLOBALS['db']->open_query($TablicaPolaWartosci);

            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //
                // tworzenie tablicy z wartosciami pol          
                while ($info = $sql->fetch_assoc()) {
                    //
                    // sprawdza czy juz jest taka wartosc zeby nie robic duplikatow
                    if ( (isset($TablicaWyniku[ $info['IdPola'] ]) && !in_array($info['Wartosc'], $TablicaWyniku[ $info['IdPola'] ])) || !isset($TablicaWyniku[ $info['IdPola'] ]) ) {
                        $TablicaWyniku[ $info['IdPola'] ][ $info['IdProduktu'] ] = $info['Wartosc']; 
                    }                  
                    //
                }
                //
                $GLOBALS['db']->close_query($sql);
                unset($info);             
                
                foreach($TablicaWyniku as $k=>$v) {
                    natcasesort($v);
                    $TablicaWyniku_tmp[$k] = $v;
                }
                unset($TablicaWyniku);
                $TablicaWyniku = $TablicaWyniku_tmp;

                // cache zapytania
                $WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePolaNazwy_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
                
                if ( !$WynikCache ) {
                    //
                    $sql = $GLOBALS['db']->open_query($TablicaPola);
                    //           
                    $Nazwy = array();
                    while ($info = $sql->fetch_assoc()) {
                        $Nazwy[ $info['IdPola'] ] = $info['NazwaPola'];
                    }
                    //
                    $GLOBALS['cache']->zapisz('DodatkowePolaNazwy_' . $_SESSION['domyslnyJezyk']['kod'], $Nazwy, CACHE_INNE);
                    //
                    $GLOBALS['db']->close_query($sql);
                    unset($info);  
                    //     
                  } else {
                    //
                    $Nazwy = $WynikCache;
                    //
                }
                //
                unset($info, $WynikCache);             
                // 
                foreach ( $Nazwy as $Id => $Nazwa ) {
                    $TablicaWyniku[ $Id ][ 'nazwa' ] = $Nazwa;   
                }
     
            }
            
            unset($TablicaPola, $TablicaPolaWartosci, $TablicaWyniku_tmp);
        
        }
        
        return $TablicaWyniku;
    }    
    
    // generuje selecty dla tablicy z w/w funkcji
    static public function FiltrSelect( $tablica, $prefix = '' ) { 
        //
        $DoWyniku = '';
        //
        foreach ($tablica as $klucz => $wartosc) {
            //
            // jezeli cecha ma jakies wartosci
            if ( count($wartosc) > 1 ) {
                //
                $DoWyniku .= '<div class="Multi Filtry' . (($prefix == 'c') ? 'Cechy' . (($wartosc['obrazek'] == 'tak') ? 'Obrazek' : 'Tekst') : 'Pola') . '">';

                $ZaznaczonePozycje = array();
                if (isset($_GET[$prefix . $klucz])) {
                    $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET[$prefix . $klucz]);
                    //
                    if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                         $ZaznaczonePozycje = array();
                    }
                    //
                }
                
                if (count($ZaznaczonePozycje) > 0) {
                    $DoWyniku .= '<span><b class="Wlaczony">' . $wartosc['nazwa'] . '</b></span>';
                    
                  } else {
                    $DoWyniku .= '<span><b>' . $wartosc['nazwa'] . '</b></span>';
                }
                //
                $DoWyniku .= '<ul class="Wybor">';
                //
                foreach ($wartosc as $kluczWartosci => $nazwaWartosci) {
                    if ( (int)$kluczWartosci > 0 ) {
                        $TabTmp[] = array('id' => $kluczWartosci, 'text' => ((is_array($nazwaWartosci)) ? $nazwaWartosci[0] : $nazwaWartosci)); 
                        //
                        $Wlacz = '';
                        $WlaczLabel = '';
                        if (in_array($kluczWartosci, $ZaznaczonePozycje)) {
                            $Wlacz = 'checked="checked"';
                            $WlaczLabel = ' class="Wlaczony"';
                        }
                        //
                        $DoWyniku .= '<li>';
                        
                        // jezeli filtr jest obrazkowy
                        if ( isset($wartosc['obrazek']) && $wartosc['obrazek'] == 'tak' && is_array($nazwaWartosci) ) {
                            //
                            $DoWyniku .= '<div>' . Funkcje::pokazObrazek($nazwaWartosci[1], $nazwaWartosci[0], SZEROKOSC_OBRAZEK_FILTRY, WYSOKOSC_OBRAZEK_FILTRY, array(), '', 'maly', true, false, false) . '</div>';
                            //
                        }
                        
                        $DoWyniku .= '<input type="checkbox" name="' . $prefix . $klucz . '[' . $kluczWartosci . ']" ' . $Wlacz . ' /> <label' . $WlaczLabel . '>' . ((is_array($nazwaWartosci)) ? $nazwaWartosci[0] : $nazwaWartosci) . '</label></li>';                        
                    }
                }
                //
                $DoWyniku .= '</ul>';
                $DoWyniku .= '</div>';
                unset($TabTmp, $Wlacz, $ZaznaczonePozycje);
                //
            }
        }
        //
        return $DoWyniku;
    }
    
    // generuje select z producentami dla danych kategorii id
    static public function FiltrProducentaSelect( $id, $typ = '' ) { 
    
        $DoWyniku = '';
        //    
        // generuje tylko jezeli nie mobilne
        if ( $_SESSION['mobile'] != 'tak' ) {   
            //
            $data = date('Y-m-d');
            
            $WstawTyp = "p2c.categories_id in (" . $id . ")";
            switch ($typ) {
                case 'polecane':
                    $WstawTyp = "p.featured_status = '1'";
                    break;
                case 'nowosci':
                    $WstawTyp = "p.new_status = '1'";
                    break;   
                case 'promocje':
                    $WstawTyp = "p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
                    break; 
                case 'hity':
                    $WstawTyp = "p.star_status = '1'";
                    break; 
                case 'bestsellery':
                    $WstawTyp = "p.products_ordered > 0";
                    break;   
                case 'oczekiwane':
                    $WstawTyp = "p.products_date_available > '" . $data . "'";
                    break;                 
            }

            unset($data);
            //

            // jezeli nie ma id produktow 
            $zapytanie = "SELECT DISTINCT m.manufacturers_id, 
                                          m.manufacturers_name
                                     FROM products p, products_to_categories p2c, manufacturers m 
                                    WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . " AND 
                                          p.manufacturers_id = m.manufacturers_id AND 
                                          p.products_id = p2c.products_id AND 
                                          " . $WstawTyp . "
                                 ORDER BY m.manufacturers_name";
                             
            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //
                $DoWyniku .= '<div class="Multi FiltryProducent">';
                //
                
                $ZaznaczonePozycje = array();
                if (isset($_GET['producent'])) {
                    $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET['producent']);
                    //
                    if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                         $ZaznaczonePozycje = array();
                    }
                    //                    
                }                
                
                if (count($ZaznaczonePozycje) > 0) {
                    $DoWyniku .= '<span><b class="Wlaczony">' . $GLOBALS['tlumacz']['PRODUCENT'] . '</b></span>';
                  } else {
                    $DoWyniku .= '<span><b>' . $GLOBALS['tlumacz']['PRODUCENT'] . '</b></span>';
                }
                //
                $DoWyniku .= '<ul class="Wybor">';
                //
                while ($info = $sql->fetch_assoc()) {
                    if ( !empty($info['manufacturers_name']) ) {
                        //
                        $Wlacz = '';
                        $WlaczLabel = '';
                        if (in_array($info['manufacturers_id'], $ZaznaczonePozycje)) {
                            $Wlacz = 'checked="checked"';
                            $WlaczLabel = ' class="Wlaczony"';
                        }
                        //
                        $DoWyniku .= '<li><input type="checkbox" name="producent[' . $info['manufacturers_id'] . ']" ' . $Wlacz . ' /> <label' . $WlaczLabel . '>' . $info['manufacturers_name'] . '</label></li>';
                    }
                }
                $DoWyniku .= '</ul>';
                $DoWyniku .= '</div>';
                unset($TabTmp, $Wlacz, $ZaznaczonePozycje);
                //
            }
        
        }
        //
        return $DoWyniku;
    }      

    // generuje select z kategoriami dla danego producenta id
    static public function FiltrKategoriiSelect( $id, $typ = '' ) {  
    
        $DoWyniku = '';
        //    
        // generuje tylko jezeli nie mobilne
        if ( $_SESSION['mobile'] != 'tak' ) {
            //
            $data = date('Y-m-d');
            
            $WstawTyp = "p.manufacturers_id = '" . $id . "'";
            switch ($typ) {
                case 'polecane':
                    $WstawTyp = "p.featured_status = '1'";
                    break;
                case 'nowosci':
                    $WstawTyp = "p.new_status = '1'";
                    break; 
                case 'promocje':
                    $WstawTyp = "p.specials_status = '1' AND (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end)";
                    break;      
                case 'hity':
                    $WstawTyp = "p.star_status = '1'";
                    break; 
                case 'bestsellery':
                    $WstawTyp = "p.products_ordered > 0";
                    break;    
                case 'oczekiwane':
                    $WstawTyp = "p.products_date_available > '" . $data . "'";
                    break;                
            }    

            unset($data);        
            //      
            $zapytanie = "SELECT DISTINCT c.categories_id
                                     FROM products p, 
                                          products_to_categories p2c, 
                                          categories c
                                    WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . " AND 
                                          c.categories_status = '1' AND
                                          c.categories_view = '1' AND
                                          p.products_id = p2c.products_id AND 
                                          p2c.categories_id = c.categories_id AND
                                          " . $WstawTyp . "
                                 ORDER BY c.parent_id";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
                //
                $DoWyniku .= '<div class="Multi FiltryKategoria">';
                //
                
                $ZaznaczonePozycje = array();
                if (isset($_GET['kategoria'])) {
                    $ZaznaczonePozycje = Filtry::WyczyscFiltr($_GET['kategoria']);
                    //
                    if ( count($ZaznaczonePozycje) == 1 && $ZaznaczonePozycje[0] == -1 ) {
                         $ZaznaczonePozycje = array();
                    }
                    //                    
                }                
                
                if (count($ZaznaczonePozycje) > 0) {
                    $DoWyniku .= '<span><b class="Wlaczony">' . $GLOBALS['tlumacz']['KATEGORIA'] . '</b></span>';
                  } else {
                    $DoWyniku .= '<span><b>' . $GLOBALS['tlumacz']['KATEGORIA'] . '</b></span>';
                }
                //
                $DoWyniku .= '<ul class="Wybor">';
                //
                while ($info = $sql->fetch_assoc()) {
                    //
                    $Wlacz = '';
                    $WlaczLabel = '';
                    if (in_array($info['categories_id'], $ZaznaczonePozycje)) {
                        $Wlacz = 'checked="checked"';
                        $WlaczLabel = ' class="Wlaczony"';
                    }
                    //
                    $DoWyniku .= '<li><input type="checkbox" name="kategoria[' . $info['categories_id'] . ']" ' . $Wlacz . ' /> <label' . $WlaczLabel . '>' . Kategorie::SciezkaKategoriiId($info['categories_id'], 'nazwy', ' / ') . '</label></li>';
                    //
                }
                $DoWyniku .= '</ul>';
                $DoWyniku .= '</div>';
                unset($TabTmp, $Wlacz, $ZaznaczonePozycje);
                //
            }        
            //
        }
        
        return $DoWyniku;
    }     

    // generuje select filtrem nowosci
    static public function FiltrNowosciSelect() { 
    
        $DoWyniku = '';
        //    
        // generuje tylko jezeli nie mobilne
        if ( $_SESSION['mobile'] != 'tak' ) {     
            //
            $DoWyniku = '<div class="Multi FiltryNowosci">';
            //
            $ZaznaczonaPozycja = '';
            if (isset($_GET['nowosci']) && $_GET['nowosci'] == 'tak') {
                $DoWyniku .= '<span><b class="Wlaczony">' . $GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . '</b></span>';
                $ZaznaczonaPozycja = $_GET['nowosci'];
              } else {
                $DoWyniku .= '<span><b>' . $GLOBALS['tlumacz']['LISTING_TYLKO_NOWOSCI'] . '</b></span>';
            }
            //
            $DoWyniku .= '<ul class="Wybor">';
            //
            $DoWyniku .= '<li><input type="checkbox" name="nowosci" value="tak" ' . (($ZaznaczonaPozycja != '') ? 'checked="checked"' : '') . ' /> <label' . (($ZaznaczonaPozycja != '') ? ' class="Wlaczony"' : '') . '>' . $GLOBALS['tlumacz']['TAK'] . '</label></li>';
            //
            $DoWyniku .= '</ul>';
            $DoWyniku .= '</div>';
            unset($ZaznaczonaPozycja);    
            //
        }
        
        return $DoWyniku;
    }   

    // generuje select filtrem promocji
    static public function FiltrPromocjeSelect() { 
    
        $DoWyniku = '';
        //    
        // generuje tylko jezeli nie mobilne
        if ( $_SESSION['mobile'] != 'tak' ) { 
        
            $DoWyniku = '<div class="Multi FiltryPromocje">';
            //
            $ZaznaczonaPozycja = '';
            if (isset($_GET['promocje']) && $_GET['promocje'] == 'tak') {
                $DoWyniku .= '<span><b class="Wlaczony">' . $GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . '</b></span>';
                $ZaznaczonaPozycja = $_GET['promocje'];
              } else {
                $DoWyniku .= '<span><b>' . $GLOBALS['tlumacz']['LISTING_TYLKO_PROMOCJE'] . '</b></span>';
            }
            //
            $DoWyniku .= '<ul class="Wybor">';
            //
            $DoWyniku .= '<li><input type="checkbox" name="promocje" value="tak" ' . (($ZaznaczonaPozycja != '') ? 'checked="checked"' : '') . ' /> <label' . (($ZaznaczonaPozycja != '') ? ' class="Wlaczony"' : '') . '>' . $GLOBALS['tlumacz']['TAK'] . '</label></li>';
            //
            $DoWyniku .= '</ul>';
            $DoWyniku .= '</div>';
            unset($ZaznaczonaPozycja);  
            //
            
        }
        
        return $DoWyniku;
    }     

    // czysci GET id z prob wlaman
    static public function WyczyscFiltr( $get ) {
        //
        $Wartosci = explode(',', $get);
        //
        $Tablica = array();
        // wartosc bezpieczenstwa - zeby przy braku danych nie pokazywalo bledu
        foreach ( $Wartosci AS $Wartosc ) {
          if ((int)$Wartosc > 0) {
              //
              $Tablica[] = (int)$Wartosc;
              //
          }
        }
        //
        if ( count($Tablica) == 0 ) {
             $Tablica[] = -1;
        }
        //
        return $Tablica;
    }
        
    
}

?>