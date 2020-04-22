<?php

if ( isset($pobierzFunkcje) ) {

    // czy wogole sa jakies dodatkowe pola
    if ( Funkcje::OgolnaIloscDodatkowychPol() > 0 ) {

        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_pola_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);

        if ( !$WynikCache && !is_array($WynikCache) ) {
            //
            $zapytanie = "SELECT pef.products_extra_fields_id, pef.products_extra_fields_location, pef.products_extra_fields_image, pef.products_extra_fields_name, ptf.products_extra_fields_value, ptf.products_extra_fields_link
                            FROM products_extra_fields pef
                       LEFT JOIN products_to_products_extra_fields ptf
                              ON ptf.products_extra_fields_id = pef.products_extra_fields_id
                        WHERE ptf.products_id = '" . $this->id_produktu . "' AND pef.products_extra_fields_status = '1' AND pef.products_extra_fields_view = '1' AND ptf.products_extra_fields_value <> '' AND (pef.languages_id = '0' OR pef.languages_id = '" . $this->jezykDomyslnyId . "')
                     ORDER BY products_extra_fields_order";
                     
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            $zapisz = array();
            while ($info = $sql->fetch_assoc()) {
                //
                $zapisz[] = array( 'id'          => $info['products_extra_fields_id'],
                                   'lokalizacja' => $info['products_extra_fields_location'],
                                   'zdjecie'     => $info['products_extra_fields_image'],
                                   'nazwa'       => $info['products_extra_fields_name'],
                                   'wartosc'     => $info['products_extra_fields_value'],
                                   'link'        => $info['products_extra_fields_link'] );
                //
            }
            //
            $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_pola_' . $_SESSION['domyslnyJezyk']['kod'], $zapisz, CACHE_INNE);
            //
            $GLOBALS['db']->close_query($sql);
            //
            $WynikCache = $zapisz;
            //
            unset($zapytanie, $zapisz, $info);
            //
        }     

        foreach ( $WynikCache as $info ) {
            //
            // sprawdzi czy nie jest link
            $Wartosc = $info['wartosc'];
            // sprawdzi czy nie jest obrazek
            if ( $info['zdjecie'] == 1 ) {
                $Wartosc = '<img src="' . KATALOG_ZDJEC . '/' . $info['wartosc'] . '" alt="" />';
            }
            if ( !empty($info['link']) ) {
                $Wartosc = '<a href="' . $info['link'] . '">' . $Wartosc . '</a>';
            }                
            //
            // jezeli wyswietlane obok zdjecia
            if ( $info['lokalizacja'] == 'foto' ) {
                $this->dodatkowePolaFoto[] = array( 'nazwa'   => $info['nazwa'],
                                                    'wartosc' => $Wartosc );
            }
            // jezeli wyswietlane pod opisem
            if ( $info['lokalizacja'] == 'opis' ) {
                $this->dodatkowePolaOpis[] = array( 'nazwa'   => $info['nazwa'],
                                                    'wartosc' => $Wartosc );
            }  

            // wszystkie pola produktu po id
            $this->dodatkowePola[ $info['id'] ] = array( 'nazwa'   => $info['nazwa'],
                                                         'wartosc' => $Wartosc );
            //
            unset($Wartosc);
            //
        }

        unset($info);
        
    }
        
}
       
?>