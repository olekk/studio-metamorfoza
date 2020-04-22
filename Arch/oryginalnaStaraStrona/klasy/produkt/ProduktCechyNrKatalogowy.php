<?php

if ( isset($pobierzFunkcje) ) {

    // szuka czy dana kombinacja cech nie ma unikalnego nr katalogowego
    $NrKatalogowyCechy = $this->info['nr_katalogowy'];

    if ( !empty($cechy) && strpos($cechy, '-') > -1 && strpos($cechy, '-gratis') == false ) {
    
        $zapytanie_cechy = "SELECT products_stock_model FROM products_stock WHERE products_stock_attributes = '" . str_replace('x', ',', $cechy) . "' and products_id = '" . $this->info['id'] . "'";
        $sql_nr_kat_cechy = $GLOBALS['db']->open_query($zapytanie_cechy);
        //
        if ((int)$GLOBALS['db']->ile_rekordow($sql_nr_kat_cechy) > 0) {
            $info_nr_kat_cechy = $sql_nr_kat_cechy->fetch_assoc();
            //
            if (!empty($info_nr_kat_cechy['products_stock_model'])) {
                $NrKatalogowyCechy = $info_nr_kat_cechy['products_stock_model'];
            }
            //
            unset($info_nr_kat_cechy);
        }   
        //
        $GLOBALS['db']->close_query($sql_nr_kat_cechy);  
        //   
    
    }

}  

?>