<?php

class MetaTagi {

  // funkcja zwraca meta tagi dla wybranej strony
  public static function ZwrocMetaTagi( $link = '' ) {
    global $filtr;
    
    $link = str_replace('/', '', $link);

    // pobieranie wartosci domyslnych
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('MetaTagiDomyslne_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);   
    
    if ( !$WynikCache ) {  
    
        $zapytanieDomyslne = "SELECT default_title, 
                                     default_keywords, 
                                     default_description, 
                                     default_index_title, 
                                     default_index_keywords, 
                                     default_index_description 
                                FROM headertags_default 
                               WHERE language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";
                               
        $sqlDomyslne = $GLOBALS['db']->open_query($zapytanieDomyslne);
        
        $Domyslne = $sqlDomyslne->fetch_assoc();
        
        $GLOBALS['db']->close_query($sqlDomyslne);
        unset($zapytanieDomyslne, $sqlDomyslne);
        
        $GLOBALS['cache']->zapisz('MetaTagiDomyslne_' . $_SESSION['domyslnyJezyk']['kod'], $Domyslne, CACHE_INNE);   
  
      } else {
      
        $Domyslne = $WynikCache;
        
    }    
    
    unset($WynikCache);
    
    // jezeli jest to strona glowna sklepu
    
    if ( $link == 'strona_glowna' ) {
    
        $metaTytul = $Domyslne['default_index_title'];
        $metaSlowa = $Domyslne['default_index_keywords'];
        $metaOpis = $Domyslne['default_index_description']; 
        
      } else {
      
        $metaTytul = $Domyslne['default_title'];
        $metaSlowa = $Domyslne['default_keywords'];
        $metaOpis = $Domyslne['default_description']; 

    }

    if ( $link != '' ) {
    
        // cache zapytania 
        $WynikCache = $GLOBALS['cache']->odczytaj('MetaTagiPodstrony_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);   
        
        $Podstrony = array();
        
        if ( !$WynikCache ) {
    
            $zapytanie = "SELECT page_name, page_title, page_keywords, page_description, append_default, sortorder FROM headertags WHERE language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            $Podstrony = array();
            while ( $info = $sql->fetch_assoc() ) {
                //
                $Podstrony[] = array('nazwa_pliku' => $info['page_name'],
                                     'tytul' => $info['page_title'],
                                     'slowa_kluczowe' => $info['page_keywords'],
                                     'opis' => $info['page_description'],
                                     'domyslne' => $info['append_default'],
                                     'sort' => $info['sortorder']);
                //
            }
            $GLOBALS['db']->close_query($sql);  
            unset($zapytanie, $info);
            
            $GLOBALS['cache']->zapisz('MetaTagiPodstrony_' . $_SESSION['domyslnyJezyk']['kod'], $Podstrony, CACHE_INNE);   
            
        } else {
        
            $Podstrony = $WynikCache;
            
        }            

        foreach ( $Podstrony as $Podstrona) {
            //
            if ( $Podstrona['nazwa_pliku'] == $filtr->process($link) ) {
                //
                // jezeli ma dodawac wartosc domyslna
                if ($Podstrona['domyslne'] == 1) {
                    //
                    // czy na poczatku czy na koncu
                    if ($Podstrona['sort'] == 1) {
                        //
                        $metaTytul = $Domyslne['default_title'] . ' ' . $Podstrona['tytul'];
                        $metaSlowa = $Domyslne['default_keywords'] . ' ' . $Podstrona['slowa_kluczowe'];
                        $metaOpis = $Domyslne['default_description'] . ' ' . $Podstrona['opis'];
                        //
                      } else {
                        //
                        $metaTytul = $Podstrona['tytul'] . ' ' . $Domyslne['default_title'];
                        $metaSlowa = $Podstrona['slowa_kluczowe'] . ' ' . $Domyslne['default_keywords'];
                        $metaOpis = $Podstrona['opis'] . ' ' . $Domyslne['default_description'];
                        //
                    }
                    //
                } else {
                    //
                    $metaTytul = $Podstrona['tytul'];
                    $metaSlowa = $Podstrona['slowa_kluczowe'];
                    $metaOpis = $Podstrona['opis'];            
                    //
                }
                //
            }
            //
        }
        
        unset($Podstrony);

    }

    $tablica = array( 'tytul' => $metaTytul,
                      'opis' => $metaOpis,
                      'slowa' => $metaSlowa );
    
    unset($metaTytul, $metaSlowa, $metaOpis, $Domyslne);    

    return $tablica;
  }  
  
} 

?>