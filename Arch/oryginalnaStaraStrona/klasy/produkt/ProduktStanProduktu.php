<?php

if ( isset($pobierzFunkcje) ) {

    $TablicaStanProduktu = array();
    //
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('ProduktStanProduktu_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) {
    
        $zapytanie = "select cp.products_condition_id, cpd.products_condition_name from products_condition cp, products_condition_description cpd where cp.products_condition_id = cpd.products_condition_id and cpd.language_id = '" . $this->jezykDomyslnyId . "'";
        $sqls = $GLOBALS['db']->open_query($zapytanie);
        //
        while ($infs = $sqls->fetch_assoc()) {
          $TablicaStanProduktu[$infs['products_condition_id']] = $infs['products_condition_name'];
        }
        $GLOBALS['db']->close_query($sqls);    
        unset($zapytanie, $infs);            
        
        $GLOBALS['cache']->zapisz('ProduktStanProduktu_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaStanProduktu, CACHE_INNE);
        
      } else {

        $TablicaStanProduktu = $WynikCache;     
        
    }
    
    if ( isset( $TablicaStanProduktu[$this->infoSql['products_condition_products_id']] ) ) {
         //
         $this->stan_produktu = $TablicaStanProduktu[$this->infoSql['products_condition_products_id']];            
         //
    }
    
    unset($TablicaStanProduktu, $WynikCache);
        
}
       
?>