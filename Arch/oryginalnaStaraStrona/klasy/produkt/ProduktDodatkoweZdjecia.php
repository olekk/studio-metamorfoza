<?php

if ( isset($pobierzFunkcje) ) {

    $zapytanieFoto = "SELECT DISTINCT images_description, popup_images FROM additional_images WHERE products_id = '" . $this->id_produktu . "' order by sort_order";
    $sql = $GLOBALS['db']->open_query($zapytanieFoto);
    
    $DodatkoweZdjecia = array();
    
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
        while ($foto = $sql->fetch_assoc()) {
            $DodatkoweZdjecia[] = array( 'zdjecie' => $foto['popup_images'], 'alt' => $foto['images_description'] );
        }
        
    }
    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanieFoto, $info); 

}
       
?>