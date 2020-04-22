<?php
// producent
if (isset($TablicaDane['Producent']) && trim($TablicaDane['Producent']) != '') {
  
    // sprawdza czy producent jest juz w bazie
    $zapytanieProducent = "select manufacturers_name, manufacturers_id from manufacturers where manufacturers_name = '" . addslashes($filtr->process($TablicaDane['Producent'])) . "'";
    $sqlc = $db->open_query($zapytanieProducent);
    //    
    if ((int)$db->ile_rekordow($sqlc) > 0) {
        //
        $info = $sqlc->fetch_assoc();
        $pola[] = array('manufacturers_id',$info['manufacturers_id']);
        //   
        $db->close_query($sqlc);
        unset($info);
        
     } else {
       
        // jezeli nie ma producenta to doda go do bazy
        $pole = array(array('manufacturers_name',$filtr->process($TablicaDane['Producent'])));   
        $db->insert_query('manufacturers' , $pole); 
        $id_dodanego_producenta = $db->last_id_query();
        unset($pole);
        //
        $pole = array(
                array('manufacturers_id',$id_dodanego_producenta),
                array('languages_id',$_SESSION['domyslny_jezyk']['id']),
                array('manufacturers_meta_title_tag',$filtr->process($TablicaDane['Producent'])),
                array('manufacturers_meta_desc_tag',$filtr->process($TablicaDane['Producent'])),   
                array('manufacturers_meta_keywords_tag',$filtr->process($TablicaDane['Producent'])));        
        $db->insert_query('manufacturers_info' , $pole);  
        unset($pole);
        //
        // dodanie id producenta do bazy produktu
        $pola[] = array('manufacturers_id',$id_dodanego_producenta);
        // 
    
        // dodawanie do innych jezykow jak sa inne jezyki
        for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
            //
            $pole = array(
                    array('manufacturers_id',$id_dodanego_producenta),
                    array('languages_id',$ile_jezykow[$j]['id']),
                    array('manufacturers_meta_title_tag',$filtr->process($TablicaDane['Producent'])),
                    array('manufacturers_meta_desc_tag',$filtr->process($TablicaDane['Producent'])),   
                    array('manufacturers_meta_keywords_tag',$filtr->process($TablicaDane['Producent'])));                    
            if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                $db->insert_query('manufacturers_info' , $pole);
            }            
            unset($pole);            
            //
            unset($kod_jezyka, $NazwaTmp);
            //
        }         
        
        unset($id_dodanego_producenta);
    }
    unset($zapytanieProducent);
    
}  
?>