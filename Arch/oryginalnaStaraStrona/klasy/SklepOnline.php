<?php

class SklepOnline {

    public static function IloscKlientowOnline() {

        $zapytanie  = 'SELECT session_data FROM session_data_customers WHERE session_id != "' . session_id() . '" LIMIT 200';
        
        $IloscKlientow = 0;
        $IloscZalogowanych = 0;

        $sql = $GLOBALS['db']->open_query($zapytanie);
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        
            while ($info = $sql->fetch_assoc()) {

                $TabTmp = SklepOnline::OdczytajDane($info['session_data']);

                if ( is_array($TabTmp) && count($TabTmp) > 0 ) {
                
                    //
                    // jezeli czas od ostatniego klikniecia nie byl wiekszy niz 10 min
                    //
                    if ( isset($TabTmp['stat']) ) {
                    
                        if ( $TabTmp['stat']['ostatnie_klikniecie'] > time() - 600 ) {
                            //
                            // jezeli klient jest zalogowany ma id > 0
                            if ( isset($TabTmp['customer_id']) && $TabTmp['customer_id'] > 0 ) {
                                 $IloscZalogowanych++;
                            }
                            if (isset($TabTmp['stat']['robot']) && $TabTmp['stat']['robot'] == 'nie') {
                                 $IloscKlientow++;
                            }
                        //
                        }
                        
                    }
                   
                } else {
                
                    $IloscKlientow++;
                
                }
            
            }
            
            $GLOBALS['db']->close_query($sql);
            unset($info);
         
        }    
        
        // jezeli klient jest zalogowany ma id > 0
        if ( $_SESSION['customer_id'] > 0 ) {
             $IloscZalogowanych++;
        }
        $IloscKlientow++;          

        return array( 'klienci_online' => $IloscKlientow, 'klienci_zalogowani' => $IloscZalogowanych );
    
    }
    
    public static function OdczytajDane( $data ) {
    
        if ( strlen($data) == 0) {
            return array();
        }
   
        preg_match_all('/(^|;|\})([a-zA-Z0-9_]+)\|/i', $data, $wzory, PREG_OFFSET_CAPTURE);

        $tablicaWynikowa = array();

        $ostatni = null;
        $klucz = '';
        foreach ( $wzory[2] as $wartosc ) {
            $roznica = $wartosc[1];
            if(!is_null( $ostatni))
            {
                $ciag = substr($data, $ostatni, $roznica - $ostatni );

                if ( SklepOnline::czySerial($ciag) ) {
                     $tablicaWynikowa[$klucz] = @unserialize($ciag);
                }
                
            }
            $klucz = $wartosc[0];

            $ostatni = $roznica + strlen( $klucz )+1;
        }

        $ciag = substr($data, $ostatni );
        
        if ( SklepOnline::czySerial($ciag) ) {
             $tablicaWynikowa[$klucz] = @unserialize($ciag);
        }
       
        return $tablicaWynikowa;
        
    }
    
    public static function czySerial($ciag) {
        if ( (stristr($ciag, '{' ) != false && stristr($ciag, '}' ) != false) || (stristr($ciag, ';' ) != false && stristr($ciag, ':' ) != false) ) {
            return true;
        } else {
            return false;
        }
    }  
    
}

?>