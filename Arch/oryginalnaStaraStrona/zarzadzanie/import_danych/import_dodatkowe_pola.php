<?php
// przy aktualizacji sprawdza czy sa jakies dod pola w csv - jezeli tak to skasuje dod pola w bazie i doda z pliku csv
$nieMaDodPol = true;
if ($CzyDodawanie == false) {
    //
    for ($idPola = 1; $idPola < 100; $idPola++) {
        if ((isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa']) && trim($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa']) != '') && (isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc']) && trim($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc']) != '')) {
            $nieMaDodPol = false;
        }
    }
    //
    if ($nieMaDodPol == false) {
        // kasuje rekordy w tablicy
        $db->delete_query('products_to_products_extra_fields' , " products_id = '".$id_aktualizowanej_pozycji."'");      
    }
    //
}

// dodatkowe pola do produktu
for ($idPola = 1; $idPola < 100; $idPola++) {
    //
    if ((isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa']) && trim($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa']) != '') && (isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc']) && trim($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc']) != '')) {
        // sprawdza czy dodatkowe pole jest juz w bazie
        $zapytanieDodPole = "select products_extra_fields_id, products_extra_fields_name from products_extra_fields where products_extra_fields_name = '" . addslashes($filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa'])) . "' and languages_id = '".$_SESSION['domyslny_jezyk']['id']."'";
        $sqlc = $db->open_query($zapytanieDodPole);
        //    
        if ((int)$db->ile_rekordow($sqlc) > 0) {
            //
            $info = $sqlc->fetch_assoc();
            $IdPolaDodatkowego = $info['products_extra_fields_id'];
            //   
            $db->close_query($sqlc);
            unset($info);
         } else {
            // jezeli nie ma dodatkowego pola to doda je do bazy
            $pole = array(
                    array('products_extra_fields_name',$filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa'])),
                    array('products_extra_fields_status','1'),
                    array('languages_id',$_SESSION['domyslny_jezyk']['id'])
                    );   
            $db->insert_query('products_extra_fields' , $pole); 
            $IdPolaDodatkowego = $db->last_id_query();
            unset($pole);
            //
        }
        //
        // dodanie id pola do tablicy powiazania produktu i dod pol
        $pole = array(
                array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                array('products_extra_fields_id',$IdPolaDodatkowego),
                array('products_extra_fields_value',$filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc'])));  

        if ( isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_link']) ) {
             $pole[] = array('products_extra_fields_link',$filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_link']));
        }
        
        $db->insert_query('products_to_products_extra_fields' , $pole);  
        unset($pole);
        // 
        unset($IdPolaDodatkowego);
    }  
    //
    
    // ---------------------------------------------------------------
    // dodawanie do innych jezykow jak sa inne jezyki
    for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
        //
        $kod_jezyka = $ile_jezykow[$j]['kod'];
        //
        if ((isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa_' . $kod_jezyka]) && trim($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa_' . $kod_jezyka]) != '') && (isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc_' . $kod_jezyka]) && trim($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc_' . $kod_jezyka]) != '')) {
            // sprawdza czy dodatkowe pole jest juz w bazie
            $zapytanieDodPole = "select products_extra_fields_id, products_extra_fields_name from products_extra_fields where products_extra_fields_name = '" . addslashes($filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa_' . $kod_jezyka])) . "' and languages_id = '".$ile_jezykow[$j]['id']."'";
            $sqlc = $db->open_query($zapytanieDodPole);
            //    
            if ((int)$db->ile_rekordow($sqlc) > 0) {
                //
                $info = $sqlc->fetch_assoc();
                $IdPolaDodatkowego = $info['products_extra_fields_id'];
                //   
                $db->close_query($sqlc);
                unset($info);
             } else {
                // jezeli nie ma dodatkowego pola to doda je do bazy
                $pole = array(
                        array('products_extra_fields_name',$filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_nazwa_' . $kod_jezyka])),
                        array('products_extra_fields_status','1'),
                        array('languages_id',$ile_jezykow[$j]['id'])
                        );   
                if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                    $db->insert_query('products_extra_fields' , $pole); 
                }
                $IdPolaDodatkowego = $db->last_id_query();
                unset($pole);
                //
            }
            //
            // dodanie id pola do tablicy powiazania produktu i dod pol
            $pole = array(
                    array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                    array('products_extra_fields_id',$IdPolaDodatkowego),
                    array('products_extra_fields_value',$filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_wartosc_' . $kod_jezyka])));
                    
            if ( isset($TablicaDane['Dodatkowe_pole_'.$idPola.'_link']) ) {
                 $pole[] = array('products_extra_fields_link',$filtr->process($TablicaDane['Dodatkowe_pole_'.$idPola.'_link']));
            }                    
                    
            if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('products_to_products_extra_fields' , $pole);  
            }
            unset($pole);
            // 
            unset($IdPolaDodatkowego);
        }  
        //
    }      
}
?>