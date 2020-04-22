<?php

class Przekierowania {

    public static function SprawdzPrzekierowania() {

        if ( PRZEKIEROWANIA == 'tak' ) {
            //

            $Przekierowania = array();
            
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('Przekierowania', CACHE_INNE);
            
            if ( !$WynikCache ) {
                $zapUrl = "select distinct urlf, urlt from location";
                $sql = $GLOBALS['db']->open_query($zapUrl);
                //
                while ($info = $sql->fetch_assoc()) {
                  $Przekierowania[ $info['urlf'] ] = $info['urlt'];      
                }
                //
                $GLOBALS['db']->close_query($sql);
                $GLOBALS['cache']->zapisz('Przekierowania', $Przekierowania, CACHE_INNE);
                //
                unset($zapUrl, $info, $sql);
            } else {
                $Przekierowania = $WynikCache;
            }   
            
            $Przekierowanie = false;

            $urlAktualny = trim($_SERVER['REQUEST_URI'], '/'); 
            foreach ( $Przekierowania as $Poprzedni => $Url ) {
            
                if ($urlAktualny == $Poprzedni && $urlAktualny != $Url ) {
                    $Przekierowanie = true;
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . ADRES_URL_SKLEPU . '/' . $Url);
                    header('Connection: close');
                    exit;
                }
            }
            
            unset($Przekierowania, $urlAktualny);
            
            return $Przekierowanie;
            
        }
            
    }

}
?>