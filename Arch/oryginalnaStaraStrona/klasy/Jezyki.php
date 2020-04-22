<?php

class Jezyki {
  
    var $tablicaJezyka;

    public function Jezyki($lng = '') {
        //
        $this->tablicaJezyka = array();
        //
        $zapytanie = "select languages_id, name, code, currencies_default, languages_default from languages";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( $lng == '' ) {
        
            while ( $wynik = $sql->fetch_assoc() ) {
                //
                // ustawia domyslny jezyk
                if ( $wynik['languages_default'] == 1 ) {
                     //
                     $this->tablicaJezyka = array('id' => $wynik['languages_id'],
                                                  'nazwa' => $wynik['name'],
                                                  'kod' => $wynik['code'],
                                                  'waluta' => $wynik['currencies_default']);
                     //
                     break;
                }
                //
            }
        
        } else { 
        
            while ( $wynik = $sql->fetch_assoc() ) {
                //
                // ustawia domyslny jezyk
                if ( $lng == $wynik['languages_id'] ) {
                     //
                     $this->tablicaJezyka = array('id' => $wynik['languages_id'],
                                                  'nazwa' => $wynik['name'],
                                                  'kod' => $wynik['code'],
                                                  'waluta' => $wynik['currencies_default']); 
                     break;
                     //
                }
                //
            } 

        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $wynik); 
    }

}
?>