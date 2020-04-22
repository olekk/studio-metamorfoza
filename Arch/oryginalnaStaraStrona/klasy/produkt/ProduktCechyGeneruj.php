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
        
            $Wynik = '<table>';    
            
            // jezeli jest kontrola magazynowa cech to utworzy tablice zeby sprawdzac czy cecha jest w stock zeby nie wyswietlac cech ktorych nie ma w magazynie
            $TablicaStock = array();
            
            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' ) {
            
                // pobieranie magazynu z product_stock
                $zapytanieStock = "SELECT DISTINCT products_stock_attributes FROM products_stock WHERE products_stock_quantity > 0 and products_id = '" . $this->id_produktu . "'";                  
                $sqlStock = $GLOBALS['db']->open_query($zapytanieStock);

                while ($stock = $sqlStock->fetch_assoc()) {
                    //
                    $podzielTb = explode(',', $stock['products_stock_attributes']);
                    foreach ($podzielTb as $podzial) {
                        $TablicaStock[] = $podzial;
                    }
                    //
                }
                
                $GLOBALS['db']->close_query($sqlStock);                     
                unset($zapytanieStock);    

            }
        
            while ($cecha = $sql->fetch_assoc()) {

                // szuka wartosci dla cechy
                $zapytanieWartosci = "SELECT * FROM products_attributes pa, products_options_values_to_products_options pop 
                                              WHERE pa.products_id = '" . $this->id_produktu . "' and pa.options_id = '" . $cecha['options_id'] . "' AND
                                                    pa.options_id = pop.products_options_id AND
                                                    pa.options_values_id = pop.products_options_values_id
                                           ORDER BY pop.products_options_values_sort_order";
                                           
                $sqlWartosc = $GLOBALS['db']->open_query($zapytanieWartosci);
                
                $TablicaDoWyboru = array();
                
                if ( KARTA_PRODUKTU_CECHY_WYBOR == 'tak' ) {
                     $TablicaDoWyboru[] = array('id' => '', 'text' => $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE']);
                }
                
                while ($wartosc = $sqlWartosc->fetch_assoc()) {
                
                    // sprawdza status wartosci cechy
                    if ( isset($GLOBALS['WartosciCech'][$wartosc['options_values_id']]) && $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['status'] == 'tak' ) {
                
                        $CiagDoId = '';
                        
                        // jezeli produkt ma cechy ktore wplywaja na wartosc
                        if ( $this->info['typ_cech'] == 'cechy' ) {
                        
                            // wartosc 
                            if ($GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') {
                                //
                                $WspolczynnikRabatu = 1;
                                if ( $this->info['rabat_produktu'] > 0 ) {
                                    $WspolczynnikRabatu = (100 - $this->info['rabat_produktu']) / 100;
                                }
                                // dodawanie rabatu do cech
                                $wartosc['options_values_price_tax'] = round( $wartosc['options_values_price_tax'] * $WspolczynnikRabatu , 2);
                                $wartosc['options_values_price'] = round( $wartosc['options_values_price'] * $WspolczynnikRabatu , 2);
                                //
                                $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $wartosc['options_values_price_tax'], $wartosc['options_values_price'], 0, $this->infoSql['products_currencies_id'], false );
                                $CiagDoId .= $TablicaCenyProduktu['netto'] . ',' . $TablicaCenyProduktu['brutto'] . ',';
                                //
                                unset($TablicaCenyProduktu, $WspolczynnikRabatu);
                                //
                              } else {
                                //
                                $CiagDoId .= '0,' . $wartosc['options_values_price_tax'] . ',';
                                //
                            }
                            
                            // prefix
                            $CiagDoId .= (($wartosc['price_prefix'] == '-') ? '-' : '+') . ',';
                            // rodzaj - procent czy kwota
                            $CiagDoId .= (($GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') ? '$' : '%') . ',';
                            
                        }
                        
                        // id
                        $CiagDoId .= $wartosc['options_values_id'];
                        
                        $CiagTekstu = '';
                        // nazwa
                        $CiagTekstu .= $GLOBALS['WartosciCech'][$wartosc['options_values_id']]['nazwa'];
                        
                        if ( $this->info['typ_cech'] == 'cechy' ) {
                                                    
                            if ( KARTA_PRODUKTU_CECHY_WARTOSC == 'tak' ) {
                        
                                // cena
                                if ( $wartosc['options_values_price_tax'] > 0 ) {
                                    //
                                    if ($GLOBALS['NazwyCech'][$cecha['options_id']]['rodzaj'] == 'kwota') {
                                        //
                                        $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $wartosc['options_values_price_tax'], $wartosc['options_values_price'], 0, $this->infoSql['products_currencies_id'], true );
                                        $CiagTekstu .= ' ' . (($wartosc['price_prefix'] == '-') ? '-' : '+') . ' ' . $TablicaCenyProduktu['brutto'] . ' ';
                                        unset($TablicaCenyProduktu);
                                        //
                                      } else {
                                        //
                                        $CiagTekstu .= ' ' . (($wartosc['price_prefix'] == '-') ? '-' : '+') . ' ' . $wartosc['options_values_price_tax'] . '% ';
                                        //
                                    }
                                    //
                                }
                                
                            }
                            
                        }
                        
                        if ( in_array( $cecha['options_id'] . '-' . $wartosc['options_values_id'], $TablicaStock ) || MAGAZYN_SPRAWDZ_STANY == 'nie' || ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'nie' ) || MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'tak' ) {
                        
                             $TablicaDoWyboru[] = array('id' => $CiagDoId, 'text' => $CiagTekstu);
                             
                        }

                        unset($CiagDoId, $CiagTekstu);
                    
                    }
                    
                }
                
                $GLOBALS['db']->close_query($sqlWartosc);                     
                unset($zapytanieWartosci);                    
                
                // jezeli jest opis do cechy
                if ( trim($GLOBALS['NazwyCech'][$cecha['options_id']]['opis']) != '' ) {
                     //
                     $Wynik .= '<tr><td><div class="CechaOpis" id="CechaOpis_' . $GLOBALS['NazwyCech'][$cecha['options_id']]['id'] . '">' . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . '</div></td><td>';
                     //
                   } else {
                     //
                     $Wynik .= '<tr><td>' . $GLOBALS['NazwyCech'][$cecha['options_id']]['nazwa'] . '</td><td>';
                     //
                }                    

                if ( (count($TablicaDoWyboru) > 1 && KARTA_PRODUKTU_CECHY_WYBOR == 'tak') || (count($TablicaDoWyboru) > 0 && KARTA_PRODUKTU_CECHY_WYBOR == 'nie') ) {
                    
                    // jezeli jest select
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'lista' ) {
                        //
                        $Wynik .= Funkcje::RozwijaneMenu('cecha_' . $cecha['options_id'], $TablicaDoWyboru, '', ' onchange="ZmienCeche(\'' . $this->idUnikat . $this->id_produktu . '\')"');
                        //
                    }
                    // jezeli jest radio
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'radio' ) {
                        //
                        $LicznikRadio = 0;
                        foreach ($TablicaDoWyboru As $Wartosc) {
                            //
                            if ( $Wartosc['id'] != '' ) {
                                $Wynik .= '<div class="Radio"><input type="radio" value="' . $Wartosc['id'] . '" onchange="ZmienCeche(\'' . $this->idUnikat . $this->id_produktu . '\')" name="cecha_' . $cecha['options_id'] . '" ' . (($LicznikRadio == 0) ? 'checked="checked" ' : '') . '/> ' . $Wartosc['text'] . '</div>';
                                $LicznikRadio++;
                            }
                            //
                        }
                        unset($LicznikRadio);
                        //
                    }                
                    // jezeli sa obrazki
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'foto' ) {
                        //
                        $LicznikRadio = 0;
                        foreach ($TablicaDoWyboru As $IdCechy => $Wartosc) {
                            //
                            if ( $Wartosc['id'] != '' ) {
                                //
                                $Wynik .= '<div class="Foto">';
                                //
                                // ustala sam nr id wartosci cechy
                                if ( $this->info['typ_cech'] == 'cechy' ) {
                                     // jezeli sa cechy to musi podzielic ciag na tablice
                                     $SamoIdTb = explode(',', $Wartosc['id']);
                                     $SamoId = $SamoIdTb[4];
                                     unset($SamoIdTb);
                                   } else {
                                     // jezeli jest ceny dla kombinacji to mozna bezposrednio pobrac id
                                     $SamoId = $Wartosc['id'];
                                }
                                //
                                if (!empty($GLOBALS['WartosciCech'][$SamoId]['foto'])) {
                                    //
                                    // jezeli jest wlaczona mozliwosc powiekszenia obrazka cechy
                                    if ( KARTA_PRODUKTU_CECHY_OBRAZ_POWIEKSZENIE == 'tak' && $_SESSION['mobile'] != 'tak' ) {
                                         $Wynik .= '<div><a class="ZdjecieCechy" title="' . $Wartosc['text'] . '" href="' . KATALOG_ZDJEC . '/' . $GLOBALS['WartosciCech'][$SamoId]['foto'] . '">' . Funkcje::pokazObrazek($GLOBALS['WartosciCech'][$SamoId]['foto'], $Wartosc['text'], SZEROKOSC_CECH, WYSOKOSC_CECH, array(), '', 'maly') . '</a></div>';
                                       } else {
                                         $Wynik .= '<div>' . Funkcje::pokazObrazek($GLOBALS['WartosciCech'][$SamoId]['foto'], $Wartosc['text'], SZEROKOSC_CECH, WYSOKOSC_CECH, array(), '', 'maly') . '</div>';
                                    }
                                    //
                                }
                                //
                                $Wynik .= '<div><input type="radio" value="' . $Wartosc['id'] . '" onchange="ZmienCeche(\'' . $this->idUnikat . $this->id_produktu . '\')" name="cecha_' . $cecha['options_id'] . '" ' . (($LicznikRadio == 0) ? 'checked="checked" ' : '') . '/> ' . $Wartosc['text'] . '</div>';
                                $LicznikRadio++;
                                //
                                $Wynik .= '</div>';
                                //
                                unset($SamoId);
                                //
                            }
                            //
                        }
                        unset($LicznikRadio);
                        //
                    }    

                } else {
                
                    // jezeli jest select
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'lista' ) {
                        //
                        $Wynik .= Funkcje::RozwijaneMenu('cecha_' . $cecha['options_id'], array( array('id' => '', 'text' => $GLOBALS['tlumacz']['BRAK_WARTOSCI_CECH']) ) );
                        //
                    }
                    // jezeli jest radio
                    if ( $GLOBALS['NazwyCech'][$cecha['options_id']]['typ'] == 'radio' ) {
                        //
                        $Wynik .= '<div class="Radio"><input type="radio" value="" name="cecha_' . $cecha['options_id'] . '" checked="checked" /> ' . $GLOBALS['tlumacz']['BRAK_WARTOSCI_CECH'] . '</div>';
                        //
                    }         
                    
                    $Wynik .= '</td></tr>';
                
                }
                
                unset($TablicaDoWyboru);

            }
            
            unset($TablicaStock);
                     
            $Wynik .= '</table>';

        }
        
        $GLOBALS['db']->close_query($sql); 
        
        unset($zapytanieCechy);
        
        if ( strpos($Wynik, '<td>') === false ) {
            $Wynik = '';
        }
    
    }
    
    // generowanie tablicy javascript w przypadku jezeli produkt z cechami ma osobne ceny
    
    $Wynik = '<input type="hidden" value="' . $this->info['typ_cech'] . '" id="TypCechy" />' . $Wynik;
    
    $CiagJs = '';
    if ( $this->info['typ_cech'] == 'ceny' ) {
    
        $CiagJs .= '<script>' . "\n";
        
        $CiagJs .= 'var opcje = [];' . "\n";
        
        $DodatkoweCeny = '';
        if ( (int)ILOSC_CEN > 1 ) {
            //
            for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
                //
                $DodatkoweCeny .= 'products_stock_price_' . $n . ', products_stock_price_tax_' . $n . ',';
                //
            }
            //
        }            
        
        // szuka cech produktu        
        $zapytanieCechy = "SELECT DISTINCT " . $DodatkoweCeny . " products_stock_attributes, products_stock_price, products_stock_price_tax 
                                      FROM products_stock
                                     WHERE products_id = '" . $this->id_produktu . "'";
        unset($DodatkoweCeny);

        $sql = $GLOBALS['db']->open_query($zapytanieCechy);

        $i = 0;
        while ($cecha = $sql->fetch_assoc()) {
             
            // jezeli klient ma inny poziom cen
            if ( $_SESSION['poziom_cen'] > 1 ) {
                //
                // jezeli cena w innym poziomie nie jest pusta
                if ( $cecha['products_stock_price_' . $_SESSION['poziom_cen']] > 0 ) {
                    //
                    $cecha['products_stock_price_tax'] = $cecha['products_stock_price_tax_' . $_SESSION['poziom_cen']];
                    $cecha['products_stock_price'] = $cecha['products_stock_price_' . $_SESSION['poziom_cen']];
                    //
                }
                //
            }                   
        
            if ( $cecha['products_stock_price_tax'] > 0 ) {
            
                // rabaty klienta od ceny produktu
                $CenaRabatyCechy = $this->CenaProduktuPoRabatach( $cecha['products_stock_price'], $cecha['products_stock_price_tax'] );
                $cecha['products_stock_price'] = $CenaRabatyCechy['netto'];
                $cecha['products_stock_price_tax'] = $CenaRabatyCechy['brutto'];

                // ceny bez formatowania - same kwoty po przeliczeniu
                $TablicaCenyProduktuCechy = $GLOBALS['waluty']->FormatujCene( $cecha['products_stock_price_tax'], $cecha['products_stock_price'], 0, $this->infoSql['products_currencies_id'], false );
                   
                $CiagJs .= 'opcje[\'x' . str_replace(',', 'x', $cecha['products_stock_attributes']) . '\'] = \'' . $TablicaCenyProduktuCechy['netto'] . ';' . $TablicaCenyProduktuCechy['brutto'] . '\';' . "\n";
                $i++;
                
                unset($CenaRabatyCechy, $TablicaCenyProduktuCechy);
                
            }
            
        }
        unset($i);
        
        $CiagJs .= '</script>';
    
    }
  
}
       
?>