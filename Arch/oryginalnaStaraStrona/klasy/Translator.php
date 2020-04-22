<?php

/**
* Klasa do obslugi tlumaczen
*/

class Translator {

  public function Translator($jezyk = '1') {
    $this->language = $jezyk;
  }

  public function & tlumacz($sekcja = null, $element = null, $glowne = false) {

    $warunek = '';
    if (isset($sekcja)) {
        
        if (is_array($sekcja)) {
            
            $warunek .= " AND ( "; 
            foreach ($sekcja as $sek) {
                //
                 $warunek .= "s.section_name = '" . $sek . "' OR ";
                //
            }
            $warunek = substr($warunek, 0, -3) . " )";
        
        } else {
        
            $warunek = " AND s.section_name = '" . $sekcja . "'";
            
        }
        $zapytanie = "SELECT
                      e.translate_constant_id AS id, e.translate_constant AS element, 
                      ec.translate_value AS content
                      FROM (translate_constant e, translate_section s, translate_value ec)
                      WHERE e.translate_constant_id = ec.translate_constant_id AND
                      e.section_id = s.section_id " . $warunek . " AND
                      ec.language_id = '" . $this->language . "'";

    } else {
        $zapytanie = "SELECT
                      e.translate_constant_id AS id, e.translate_constant AS element, 
                      ec.translate_value AS content
                      FROM (translate_constant e, translate_section s, translate_value ec)
                      WHERE e.translate_constant_id = ec.translate_constant_id AND
                      e.section_id = s.section_id AND
                      ec.language_id = '" . $this->language . "'";
    }

    if (isset($element)) {
        $zapytanie .= " AND e.translate_constant = '" . $element . "'";
    }
   
    $elem = array();  
    
    // cache zapytania
    $WynikCache = false;
    
    // cache tylko dla glownych tlumaczen w start.php
    if ( $glowne == true ) {
         $WynikCache = $GLOBALS['cache']->odczytaj('Tlumaczenia_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);   
    }

    if ( !$WynikCache ) {        

        $rezultat = $GLOBALS['db']->open_query($zapytanie);

        while ($wiersz = $rezultat->fetch_assoc()) {
          
            $zastapElement = false;

            if (!isset($elem[$wiersz['element']])) {
                $zastapElement = true;
            }

            if ($zastapElement == true) {
                $elem[$wiersz['element']] = $wiersz['content'];
            }

        }

        $GLOBALS['db']->close_query($rezultat); 
        
        unset($wiersz);

        if ( $glowne == true ) {
             $GLOBALS['cache']->zapisz('Tlumaczenia_' . $_SESSION['domyslnyJezyk']['kod'], $elem, CACHE_INNE);   
        }
        
      } else {
      
        $elem = $WynikCache;
        
    }

    unset($zapytanie);
    return $elem;
  }

}
?>