<?php

if ( isset($pobierzFunkcje) ) {

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('DostepnosciAutomatyczne', CACHE_INNE);   
    
    if ( !$WynikCache && !is_array($WynikCache) ) {

        $zapytanie = "SELECT GROUP_CONCAT(CONVERT(quantity, CHAR(8)),':', CONVERT(products_availability_id, CHAR(8)) ORDER BY quantity DESC SEPARATOR ',') as wartosc FROM products_availability WHERE mode = '1'";

        $sql = $GLOBALS['db']->open_query($zapytanie);

        while ($info = $sql->fetch_assoc()) {
            $TablicaDostepnosci = $info['wartosc'];
        }
        
        $GLOBALS['db']->close_query($sql); 
        
        unset($zapytanie, $info);
        
        $GLOBALS['cache']->zapisz('DostepnosciAutomatyczne', $TablicaDostepnosci, CACHE_INNE);

      } else {
     
        $TablicaDostepnosci = $WynikCache;
    
    }
    
    $dostepnosc_id = '0';
    
    if ( count($TablicaDostepnosci) > 0 ) {

        $Tablica = preg_split("/[:,]/" , $TablicaDostepnosci);
        for ( $i = 0, $c = count($Tablica); $i < $c; $i += 2 ) {
          if ($iloscProduktu >= $Tablica[$i]) {
            $dostepnosc_id = $Tablica[$i+1];
            break;
          }
        }
        
    }
    
    unset($WynikCache, $TablicaDostepnosci, $Tablica);

}
       
?>