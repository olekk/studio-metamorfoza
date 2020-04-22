<?php
// pliki
for ($w = 1; $w < 5 ; $w++) {
    //
    if ((isset($TablicaDane['Film_'.$w.'_nazwa']) && trim($TablicaDane['Film_'.$w.'_nazwa']) != '') && (isset($TablicaDane['Film_'.$w.'_plik']) && trim($TablicaDane['Film_'.$w.'_plik']) != '')) {
        //
        
        // sprawdza czy takie pole jest juz w bazie
        $dodajBoNieMa = false;
        //
        if ( $CzyDodawanie == false ) {
            //
            $zapytanieSpr = "select products_film_id from products_film where products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
            $sqlSpr = $db->open_query($zapytanieSpr);  
            if ((int)$db->ile_rekordow($sqlSpr) == 0) {
                $dodajBoNieMa = true;
            }
            $db->close_query($sqlSpr);
            //
        }
        
        $pola = array(
                array('products_film_id',$w),
                array('products_film_name',$filtr->process($TablicaDane['Film_'.$w.'_nazwa'])),
                array('products_film_file',$filtr->process($TablicaDane['Film_'.$w.'_plik'])));
                
        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
            $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']); 
        }                   
                
        if (isset($TablicaDane['Film_'.$w.'_opis']) && trim($TablicaDane['Film_'.$w.'_opis']) != '') {
            //
            $pola[] = array('products_film_description',$filtr->process($TablicaDane['Film_'.$w.'_opis']));
            //
        }  
        
        if (isset($TablicaDane['Film_'.$w.'_ekran']) && trim($TablicaDane['Film_'.$w.'_ekran']) != '') {
            //
            if (strtolower($TablicaDane['Film_'.$w.'_ekran']) == 'tak') {
                $pola[] = array('products_film_full_size','1');
              } else {
                $pola[] = array('products_film_full_size','0');
            }
            //
          } else {
            $pola[] = array('products_film_full_size','0');
        }         

        if (isset($TablicaDane['Film_'.$w.'_szerokosc']) && trim($TablicaDane['Film_'.$w.'_szerokosc']) != '') {
            //
            $pola[] = array('products_film_width',(int)$TablicaDane['Film_'.$w.'_szerokosc']);
            //
        }         
        
        if (isset($TablicaDane['Film_'.$w.'_wysokosc']) && trim($TablicaDane['Film_'.$w.'_wysokosc']) != '') {
            //
            $pola[] = array('products_film_height',(int)$TablicaDane['Film_'.$w.'_wysokosc']);
            //
        }         
  
        if ($CzyDodawanie == true || $dodajBoNieMa == true) {
            $db->insert_query('products_film', $pola); 
          } else {
            $db->update_query('products_film' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        }        
        unset($pola, $dodajBoNieMa, $zapytanieSpr);
        
        
        // ---------------------------------------------------------------
        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
        
            // sprawdza czy takie pole jest juz w bazie
            $dodajBoNieMa = false;
            //
            if ( $CzyDodawanie == false ) {
                //
                $zapytanieSpr = "select products_film_id from products_film where products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'";
                $sqlSpr = $db->open_query($zapytanieSpr);  
                if ((int)$db->ile_rekordow($sqlSpr) == 0) {
                    $dodajBoNieMa = true;
                }
                $db->close_query($sqlSpr);
                //
            }        
        
            //
            $kod_jezyka = $ile_jezykow[$j]['kod'];
            //
            $NazwaTmp = '';
            if (isset($TablicaDane['Film_'.$w.'_nazwa_' . $kod_jezyka]) && trim($TablicaDane['Film_'.$w.'_nazwa_' . $kod_jezyka]) != '') {
                $NazwaTmp = $filtr->process($TablicaDane['Film_'.$w.'_nazwa_' . $kod_jezyka]);
            }
            //
            $pola = array(
                    array('products_film_id',$w),
                    array('products_film_name',$NazwaTmp),
                    array('products_film_file',$filtr->process($TablicaDane['Film_'.$w.'_plik']))); 

            if ($CzyDodawanie == true || $dodajBoNieMa == true) {
                $pola[] = array('products_id', (($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
                $pola[] = array('language_id',$ile_jezykow[$j]['id']); 
            }                           
                    
            if (isset($TablicaDane['Film_'.$w.'_opis_' . $kod_jezyka]) && trim($TablicaDane['Film_'.$w.'_opis_' . $kod_jezyka]) != '') {
                //
                $pola[] = array('products_film_description',$filtr->process($TablicaDane['Film_'.$w.'_opis_' . $kod_jezyka]));
                //
            }
            
            if (isset($TablicaDane['Film_'.$w.'_ekran']) && trim($TablicaDane['Film_'.$w.'_ekran']) != '') {
                //
                if (strtolower($TablicaDane['Film_'.$w.'_ekran']) == 'tak') {
                    $pola[] = array('products_film_full_size','1');
                  } else {
                    $pola[] = array('products_film_full_size','0');
                }
                //
              } else {
                $pola[] = array('products_film_full_size','0');
            }              
            
            if (isset($TablicaDane['Film_'.$w.'_szerokosc']) && trim($TablicaDane['Film_'.$w.'_szerokosc']) != '') {
                //
                $pola[] = array('products_film_width',(int)$TablicaDane['Film_'.$w.'_szerokosc']);
                //
            }         
            
            if (isset($TablicaDane['Film_'.$w.'_wysokosc']) && trim($TablicaDane['Film_'.$w.'_wysokosc']) != '') {
                //
                $pola[] = array('products_film_height',(int)$TablicaDane['Film_'.$w.'_wysokosc']);
                //
            }              

            if (($CzyDodawanie == true || $dodajBoNieMa == true) && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('products_film', $pola); 
              } else if ($NazwaTmp != '') {
                $db->update_query('products_film' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and products_film_id = '".$w."' and language_id = '".$ile_jezykow[$j]['id']."'");
            }                     
            unset($pola);                
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }           
    }
    // 
}
unset($info);       
// 
?>