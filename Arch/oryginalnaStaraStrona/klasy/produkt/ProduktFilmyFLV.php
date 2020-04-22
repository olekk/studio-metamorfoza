<?php

if ( isset($pobierzFunkcje) ) {

    $zapytanie = "SELECT * FROM products_film WHERE products_id = '" . $this->id_produktu . "' AND language_id = '" . $this->jezykDomyslnyId . "' ORDER BY products_film_id";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        //
        if ( !empty($info['products_film_file']) && !empty($info['products_film_file']) ) {
            //
            $SzerokoscFilmu = (((int)$info['products_film_width'] > 0) ? (int)$info['products_film_width'] : 300);
            $WysokoscFilmu = (((int)$info['products_film_height'] > 0) ? (int)$info['products_film_height'] : 300);
            //
            $this->FilmyFlv[] = array( 'id_film'   => $this->id_produktu . '_' . $info['products_film_id'],
                                       'nazwa'     => $info['products_film_name'],
                                       'opis'      => $info['products_film_description'],
                                       'ekran'     => (($info['products_film_full_size'] == '1') ? 'true' : 'false'),
                                       'film'      => $info['products_film_file'],
                                       'szerokosc' => $SzerokoscFilmu,
                                       'wysokosc'  => $WysokoscFilmu);
            // 
            unset($SzerokoscFilmu, $WysokoscFilmu);
        }            
    }
    $GLOBALS['db']->close_query($sql); 

    unset($zapytanie, $info);

}

?>        