<?php
// dodatkowe zdjecia

// przy aktualizacji sprawdza czy sa jakies dod zdjecia w csv - jezeli tak to skasuje dod zdjecia w bazie i doda z pliku csv
$nieMaZdjec = true;
if ($CzyDodawanie == false) {
    //
    for ($w = 1; $w < 11 ; $w++) {
        if (isset($TablicaDane['Zdjecie_dodatkowe_'.$w]) && trim($TablicaDane['Zdjecie_dodatkowe_'.$w]) != '') {
            $nieMaZdjec = false;
        }
    }
    //
    if ($nieMaZdjec == false) {
        // kasuje rekordy w tablicy
        $db->delete_query('additional_images' , " products_id = '".$id_aktualizowanej_pozycji."'");      
    }
    //
}

for ($w = 1; $w < 11 ; $w++) {
    //
    if (isset($TablicaDane['Zdjecie_dodatkowe_'.$w]) && trim($TablicaDane['Zdjecie_dodatkowe_'.$w]) != '') {
        //
        $pola = array(
                array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji)),
                array('popup_images',$filtr->process($TablicaDane['Zdjecie_dodatkowe_'.$w])));   

        if (isset($TablicaDane['Zdjecie_dodatkowe_opis_'.$w]) && trim($TablicaDane['Zdjecie_dodatkowe_opis_'.$w]) != '') {
            $pola[] = array('images_description',$filtr->process($TablicaDane['Zdjecie_dodatkowe_opis_'.$w]));  
        }
        
        $db->insert_query('additional_images' , $pola);
        unset($pola);
        //
    }
    // 
}     
// 
?>