<?php

if ( isset($pobierzFunkcje) ) {

    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();       

    $Wynik = '';
    
    if ( count($GLOBALS['NazwyCech']) && count($GLOBALS['WartosciCech']) ) {

        // szuka cech produktu        
        $zapytanieCechy = "SELECT DISTINCT pa.options_id 
                                      FROM products_attributes pa, products_options po 
                                     WHERE pa.products_id = '" . $this->id_produktu . "' AND
                                           pa.options_id = po.products_options_id
                                  ORDER BY po.products_options_sort_order";
                                        
        $sql = $GLOBALS['db']->open_query($zapytanieCechy);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
            $Wynik = '';    
        
            while ($cecha = $sql->fetch_assoc()) {
            
                // szuka wartosci dla cechy
                $zapytanieWartosci = "SELECT * FROM products_attributes pa, products_options_values_to_products_options pop 
                                              WHERE pa.products_id = '" . $this->id_produktu . "' and pa.options_id = '" . $cecha['options_id'] . "' AND
                                                    pa.options_id = pop.products_options_id AND
                                                    pa.options_values_id = pop.products_options_values_id
                                           ORDER BY pop.products_options_values_sort_order";
                                           
                $sqlWartosc = $GLOBALS['db']->open_query($zapytanieWartosci);
                
                $TablicaDoWyboru = array();
                
                while ($wartosc = $sqlWartosc->fetch_assoc()) {
                
                    // sprawdza status wartosci cechy
                    if ( isset($GLOBALS['WartosciCech'][$wartosc['options_values_id']]) && $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['status'] == 'tak' ) {                    
                
                        $CiagTekstu = $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['nazwa'] . ' ';
                        
                        if ( $this->info['typ_cech'] == 'cechy' ) {
                          
                            if ( KARTA_PRODUKTU_CECHY_WARTOSC == 'tak' ) {
                        
                                if ( $wartosc['options_values_price_tax'] > 0 ) {
                                    //
                                    $CiagTekstu .= '(';
                                    //
                                    if ($GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') {
                                        //
                                        $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $wartosc['options_values_price_tax'], $wartosc['options_values_price'], 0, $this->infoSql['products_currencies_id'], true );
                                        $CiagTekstu .= (($wartosc['price_prefix'] == '-') ? '-' : '+') . ' ' . $TablicaCenyProduktu['brutto'];
                                        unset($TablicaCenyProduktu);
                                        //
                                      } else {
                                        //
                                        $CiagTekstu .= (($wartosc['price_prefix'] == '-') ? '-' : '+') . ' ' . $wartosc['options_values_price_tax'] . '%';
                                        //
                                    }
                                    //
                                    $CiagTekstu .= ')';
                                    //
                                }  
                                
                            }

                        }

                        $CiagTekstu .= ', ';

                        $TablicaDoWyboru[] = array('text' => $CiagTekstu);

                        unset($CiagTekstu);
                        
                    }
                    
                }
                
                $GLOBALS['db']->close_query($sqlWartosc);                     
                unset($zapytanieWartosci);                       

                if ( count($TablicaDoWyboru) > 0 ) {
                
                    $Wynik .= '<b>' . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . '</b>: ';

                    $SameWartosci = '';
                    
                    foreach ($TablicaDoWyboru As $Wartosc) {
                    
                        $SameWartosci .= $Wartosc['text'];

                    }
                    
                    $Wynik .= substr($SameWartosci, 0, -2);
                    unset($SameWartosci);
                    
                    $Wynik .= '<br />';
                
                }
                
                unset($TablicaDoWyboru);

            }

        }
        
        $GLOBALS['db']->close_query($sql); 
        
        unset($zapytanieCechy);
        
        if ( strpos($Wynik, '<br') === false ) {
            $Wynik = '';
        }
        
    }

}

?>