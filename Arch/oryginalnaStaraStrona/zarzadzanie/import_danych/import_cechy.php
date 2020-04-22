<?php
// jezeli jest aktualizacja to sklep sprawdzi czy takie cechy sa juz w bazie, jezeli ich nie bedzie to przed dopisaniem cech skasuje wszystkie cechy dla danego produktu z bazy
if ($CzyDodawanie == false) {
    //
    $TablicaCechNazw = array();
    $TablicaCechWartosci = array();
    //
    for ($idCechy = 1; $idCechy < 100; $idCechy++) {
        //
        if ((isset($TablicaDane['Cecha_nazwa_'.$idCechy]) && trim($TablicaDane['Cecha_nazwa_'.$idCechy]) != '') && (isset($TablicaDane['Cecha_wartosc_'.$idCechy]) && trim($TablicaDane['Cecha_wartosc_'.$idCechy]) != '')) {
            //
            // sprawdza czy nazwa cechy jest juz w bazie
            $zapytanieCecha = "select products_options_id, products_options_name from products_options where products_options_name = '" . addslashes($filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy])) . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
            $sqlc = $db->open_query($zapytanieCecha);
            //    
            $info = $sqlc->fetch_assoc();
            $TablicaCechNazw[] = $info['products_options_id'];
            //
            // sprawdza czy wartosc dla danej cechy w bazie
            $zapytanieCecha = "select 
                                    pv.products_options_values_id, 
                                    pv.products_options_values_name, 
                                    pvp.products_options_id, 
                                    pvp.products_options_values_id 
                               from products_options_values pv, products_options_values_to_products_options pvp
                               where pv.products_options_values_id = pvp.products_options_values_id and pvp.products_options_id = '" . $info['products_options_id'] . "' and pv.products_options_values_name = '" . addslashes($filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy])) . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";

            $sqlp = $db->open_query($zapytanieCecha);
            //
            $db->close_query($sqlc);
            unset($info);            
            //
            $info = $sqlp->fetch_assoc();             
            $TablicaCechWartosci[] = $info['products_options_values_id'];
            //   
            $db->close_query($sqlp);
            unset($info);                
            //
        }
        //
    }
    //
    $TrzebaSkasowac = false;
    $licznikCech = 0;
    //
    for ($w = 0, $c = count($TablicaCechNazw); $w < $c; $w++) {
        //
        // sprawdza czy takie cechy sa przypisane do produktu
        $zapytanieCechaProdukt = "select products_attributes_id from products_attributes where options_id = '" . $TablicaCechNazw[$w] . "' and options_values_id = '" . $TablicaCechWartosci[$w] . "' and products_id = '" . $id_aktualizowanej_pozycji ."'";
        $sqlq = $db->open_query($zapytanieCechaProdukt);
        //    
        if ((int)$db->ile_rekordow($sqlq) == 0) {
            $TrzebaSkasowac = true;
           } else {
            $licznikCech++;
        }
        //
    }
    //
    if ($licznikCech != count($TablicaCechNazw)) {
        $TrzebaSkasowac = true;
    }
    
    if ($TrzebaSkasowac == true) {
        // kasuje rekordy w tablicy jezeli aktualizacja
        $db->delete_query('products_attributes' , " products_id = '".$id_aktualizowanej_pozycji."'");        
        $db->delete_query('products_stock' , " products_id = '".$id_aktualizowanej_pozycji."'"); 
        //
    }
}    


     
// cechy produktu
for ($idCechy = 1; $idCechy < 100; $idCechy++) {
    //
    if ((isset($TablicaDane['Cecha_nazwa_'.$idCechy]) && trim($TablicaDane['Cecha_nazwa_'.$idCechy]) != '') && (isset($TablicaDane['Cecha_wartosc_'.$idCechy]) && trim($TablicaDane['Cecha_wartosc_'.$idCechy]) != '')) {
        //
        // sprawdza czy nazwa cechy jest juz w bazie
        $zapytanieCecha = "select products_options_id, products_options_name from products_options where products_options_name = '" . $filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy]) . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
        $sqlc = $db->open_query($zapytanieCecha);
        //    
        if ((int)$db->ile_rekordow($sqlc) > 0) {
            //
            $info = $sqlc->fetch_assoc();
            $idNazwyCechy = $info['products_options_id'];
            //   
            $db->close_query($sqlc);
            unset($info);
            //
         } else {
            // jezeli nie ma nazwy cechy to doda ja do bazy
            // okreslanie kolejnego nr ID
            $zapytanie_cechy = "select max(products_options_id) + 1 as next_id from products_options";
            $sqls = $db->open_query($zapytanie_cechy);
            $wynik = $sqls->fetch_assoc();    
            $kolejne_id = $wynik['next_id'];
            $db->close_query($sqls);  
            //
            if ( (int)$kolejne_id == 0 ) {
                 $kolejne_id = 1;
            }
            //            
            $pole = array(
                    array('products_options_id',$kolejne_id),
                    array('products_options_name',$filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy])),
                    array('products_options_images_enabled','false'),
                    array('products_options_type','radio'),
                    array('products_options_value','kwota'),
                    array('language_id',$_SESSION['domyslny_jezyk']['id'])
                    );   
            $db->insert_query('products_options' , $pole); 
            $idNazwyCechy = $kolejne_id;
            unset($pole,$wynik);
            
            
            // ---------------------------------------------------------------
            // dodawanie do innych jezykow jak sa inne jezyki
            for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                //
                $kod_jezyka = $ile_jezykow[$j]['kod'];
                //
                $NazwaTmp = $filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy]);
                if (isset($TablicaDane['Cecha_nazwa_'.$idCechy.'_' . $kod_jezyka]) && trim($TablicaDane['Cecha_nazwa_'.$idCechy.'_' . $kod_jezyka]) != '') {
                    $NazwaTmp = $filtr->process($TablicaDane['Cecha_nazwa_'.$idCechy.'_' . $kod_jezyka]);
                }
                //
                $pole = array(
                        array('products_options_id',$kolejne_id),
                        array('products_options_name',$NazwaTmp),
                        array('products_options_images_enabled','false'),
                        array('products_options_type','radio'),
                        array('products_options_value','kwota'),
                        array('language_id',$ile_jezykow[$j]['id'])
                        );       
                if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                    $db->insert_query('products_options', $pole);  
                }
                unset($pole);               
                //
                unset($kod_jezyka, $NazwaTmp);
                //
            }      
            unset($pole,$kolejne_id);            
            
            //
        }
        unset($zapytanieCecha);        
        
        
        // bedzie szukal teraz czy jest wartosc dla danej cechy
        //
        $zapytanieCecha = "select 
                                pv.products_options_values_id, 
                                pv.products_options_values_name, 
                                pvp.products_options_id, 
                                pvp.products_options_values_id 
                           from products_options_values pv, products_options_values_to_products_options pvp
                           where pv.products_options_values_id = pvp.products_options_values_id and pvp.products_options_id = '" . $idNazwyCechy . "' and pv.products_options_values_name = '" . $filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy]) . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";

        $sqlc = $db->open_query($zapytanieCecha);
        //
        if ((int)$db->ile_rekordow($sqlc) > 0) {
            //
            $info = $sqlc->fetch_assoc();
            $idWartoscCechy = $info['products_options_values_id'];
            //   
            $db->close_query($sqlc);
            unset($info);
            //
         } else {
            // jezeli nie ma wartosci cechy to doda je do bazy
            //
            // okreslanie kolejnego nr ID
            $zapytanie_cechy = "select max(products_options_values_id) + 1 as next_id from products_options_values";
            $sqls = $db->open_query($zapytanie_cechy);
            $wynik = $sqls->fetch_assoc();    
            $kolejne_id = $wynik['next_id'];
            $db->close_query($sqls);
            //
            if ( (int)$kolejne_id == 0 ) {
                 $kolejne_id = 1;
            }
            //    
            $pole = array(
                    array('products_options_values_id',$kolejne_id),
                    array('language_id',$_SESSION['domyslny_jezyk']['id']),
                    array('products_options_values_name',$filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy]))
                    );   
            $db->insert_query('products_options_values' , $pole); 
            $idWartoscCechy = $kolejne_id;
            //
            unset($pole,$wynik);
            //
            
            // ---------------------------------------------------------------
            // dodawanie do innych jezykow jak sa inne jezyki
            for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                //
                $kod_jezyka = $ile_jezykow[$j]['kod'];
                //
                $NazwaTmp = $filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy]);
                if (isset($TablicaDane['Cecha_wartosc_'.$idCechy.'_' . $kod_jezyka]) && trim($TablicaDane['Cecha_wartosc_'.$idCechy.'_' . $kod_jezyka]) != '') {
                    $NazwaTmp = $filtr->process($TablicaDane['Cecha_wartosc_'.$idCechy.'_' . $kod_jezyka]);
                }
                //
                $pole = array(
                        array('products_options_values_id',$kolejne_id),
                        array('language_id',$ile_jezykow[$j]['id']),
                        array('products_options_values_name',$NazwaTmp)
                        );
                if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                    $db->insert_query('products_options_values', $pole);  
                }
                unset($pole);                 
                //
                unset($kod_jezyka, $NazwaTmp);
                //
            }  
            unset($pole,$kolejne_id);
            
        }
        unset($zapytanieCecha);
        
        
        // sprawdza czy jest juz bazie polaczenie nazwy cechy i wartosci cechy
        $zapytanieCecha = "select * from products_options_values_to_products_options 
                           where products_options_id = '" . $idNazwyCechy . "' and products_options_values_id = '" . $idWartoscCechy . "'";
        $sqlc = $db->open_query($zapytanieCecha);
        //
        if ((int)$db->ile_rekordow($sqlc) == 0) {        
            // wpis do bazy - polaczenie nazwy cechy i wartosci
            $pole = array(
                    array('products_options_id',$idNazwyCechy),
                    array('products_options_values_id',$idWartoscCechy));
            $db->insert_query('products_options_values_to_products_options', $pole);
            unset($pole);        
            //
        }
        unset($zapytanieCecha);
        
        // przypisanie cechy do produktu
        $pole = array(
                array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                array('options_id',$idNazwyCechy),
                array('options_values_id',$idWartoscCechy));
                
        // jezeli cecha ma cene
        if (isset($TablicaDane['Cecha_cena_'.$idCechy]) && (float)$TablicaDane['Cecha_cena_'.$idCechy] != 0) {
            $pole[] = array('options_values_price_tax',$filtr->process(abs((float)$TablicaDane['Cecha_cena_'.$idCechy])));
            //
            // ustalanie prefixu
            if ((float)$TablicaDane['Cecha_cena_'.$idCechy] < 0) {
                $pole[] = array('price_prefix','-');
              } else {
                $pole[] = array('price_prefix','+');
            }
            //
            // przeliczanie ceny na netto i vat
            //
            $netto = round( abs($TablicaDane['Cecha_cena_'.$idCechy]) / (1 + ($wartoscPodatkuDlaProduktu/100)), 2);
            $podatek = abs($TablicaDane['Cecha_cena_'.$idCechy]) - $netto;
            //
            $pole[] = array('options_values_price',$netto);
            $pole[] = array('options_values_tax',$podatek);
            //
            unset($netto, $podatek);
            //            
        }
        
        // jezeli cecha ma wage
        if (isset($TablicaDane['Cecha_waga_'.$idCechy]) && (float)$TablicaDane['Cecha_waga_'.$idCechy] > 0) {
            $pole[] = array('options_values_weight',$filtr->process(abs((float)$TablicaDane['Cecha_waga_'.$idCechy])));        
        }
        
        // sprawdza czy trzeba dopisac cechy do produkty czy tylko zaktualizowac
        if ($CzyDodawanie == false && $TrzebaSkasowac == false) {
            $db->update_query('products_attributes', $pole, " options_id = '" . $idNazwyCechy . "' and options_values_id = '" . $idWartoscCechy . "' and products_id = '" . $id_aktualizowanej_pozycji . "'");
          } else {
            $db->insert_query('products_attributes', $pole);
        }
        unset($pole);         

    }  
    //
}

?>