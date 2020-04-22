<?php

class Producenci {

    public static function TablicaProducenci($Ilosc = false) {
    
        $TablicaProducenci = array();
        
        // pobiera dane z bazy
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        if (LISTING_ILOSC_PRODUKTOW == 'tak' && $Ilosc == false) {
        
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('ProducenciIlosc', CACHE_PRODUCENCI, true);
            
            if ( !$WynikCache && !is_array($WynikCache) ) {
            
                $sql = $GLOBALS['db']->open_query("SELECT m.manufacturers_id as IdProducenta, 
                                                          m.manufacturers_name as Nazwa, 
                                                          m.manufacturers_image as Foto, ( 
                                                          SELECT count( px.products_id ) 
                                                            FROM products px 
                                                       LEFT JOIN products_to_categories p2x ON px.products_id = p2x.products_id 
                                                       LEFT JOIN categories cx ON p2x.categories_id = cx.categories_id 
                                                           WHERE m.manufacturers_id = px.manufacturers_id 
                                                             AND cx.categories_status = '1' 
                                                             AND px.products_status = '1' " . str_replace('p.', 'px.', $GLOBALS['warunekProduktu']) . " ) as IloscProduktow 
                                                     FROM manufacturers m 
                                                LEFT JOIN products p ON m.manufacturers_id = p.manufacturers_id AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . "
                                                LEFT JOIN products_to_categories p2c ON p.products_id = p2c.products_id 
                                                LEFT JOIN categories c ON p2c.categories_id = c.categories_id AND c.categories_status = '1' 
                                                 GROUP BY m.manufacturers_id 
                                                 ORDER BY m.manufacturers_name");           
                                                   
            }
          
          } else {
          
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('Producenci', CACHE_PRODUCENCI, true);
            
            if ( !$WynikCache && !is_array($WynikCache) ) {
                  
                $sql = $GLOBALS['db']->open_query("SELECT m.manufacturers_id as IdProducenta, 
                                                          m.manufacturers_name as Nazwa,
                                                          m.manufacturers_image as Foto
                                                       FROM manufacturers m
                                                  LEFT JOIN products p ON m.manufacturers_id = p.manufacturers_id
                                                  LEFT JOIN products_to_categories p2c ON p.products_id = p2c.products_id
                                                  LEFT JOIN categories c ON p2c.categories_id = c.categories_id
                                                      WHERE c.categories_status = '1'
                                                        AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . "
                                                   GROUP BY m.manufacturers_id
                                                   ORDER BY m.manufacturers_name"); 

            }
          
        }
        
        if ( !$WynikCache && !is_array($WynikCache) ) {

            while ($info = $sql->fetch_assoc()) {
                //
                if ((LISTING_ILOSC_PRODUKTOW == 'tak' && $Ilosc == false && $info['IloscProduktow'] > 0) || LISTING_ILOSC_PRODUKTOW == 'nie')  {
                    $TablicaProducenci[$info['IdProducenta']] = $info;
                }
                //
            }   
            
            if (LISTING_ILOSC_PRODUKTOW == 'tak' && $Ilosc == false) {
                $GLOBALS['cache']->zapisz('ProducenciIlosc', $TablicaProducenci, CACHE_PRODUCENCI, true);
              } else {
                $GLOBALS['cache']->zapisz('Producenci', $TablicaProducenci, CACHE_PRODUCENCI, true);
            }

          } else {
          
            $TablicaProducenci = $WynikCache;
          
        }
        
        unset($WynikCache);
    
        return $TablicaProducenci;
    
    }
    
    // zwraca nazwa lub logo producenta
    public static function NazwaProducenta($id) {

        $zapytanie_tmp = "select distinct * from manufacturers where manufacturers_id = '" . $id . "'";
        $sqls = $GLOBALS['db']->open_query($zapytanie_tmp);
        //
        $infs = $sqls->fetch_assoc();
        $Tablica = array('id' => $infs['manufacturers_id'], 'nazwa' => $infs['manufacturers_name']);

        $GLOBALS['db']->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //  
        return $Tablica;
        
    }    

  
    // zwraca tablice z producentami - tylko id i nazwe - do selectow
    public static function TablicaProducenciSelect($brak = '') {
    
        $TablicaProducentow = Producenci::TablicaProducenci();
        //
        $Tablica = array();

        $Tablica[] = array('id' => 0, 'text' => $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE']);

        foreach ( $TablicaProducentow as $Producent ) {
            $Tablica[] = array('id' => $Producent['IdProducenta'], 'text' => $Producent['Nazwa']);
        }

        unset($TablicaProducentow);
        //  
        return $Tablica;
        
    }   

    // zwraca id producenta to jakiego nalezy produkt
    static function ProduktProducent($id = '0') {
        //
        $zapytanie = "SELECT manufacturers_id FROM products WHERE products_id = " . $id;

        $sql = $GLOBALS['db']->open_query($zapytanie);
        $info = $sql->fetch_assoc();

        $GLOBALS['db']->close_query($sql); 

        unset($zapytanie);
        
        return $info['manufacturers_id'];
        //
    } 
  
} 

?>