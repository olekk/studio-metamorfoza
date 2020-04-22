<?php

class WygladMobilny {

    public static function UstalSzablon( $DomyslnySzablonSklepu ) {
    
        $isMobile = WygladMobilny::UrzadzanieMobilne();

        // dodatek do szablonu mobilnego
        $mobileSzablon = '.mobile';
        
        // reczne przelaczanie na mobila
        if ( !isset($_SESSION['mobileReczne']) ) {
             $_SESSION['mobileReczne'] = '';
        }
            
        if ( !isset($_SESSION['mobile']) ) {
             $_SESSION['mobile'] = 'nie';
        }
        if ( !isset($_SESSION['rwd']) ) {
             $_SESSION['rwd'] = 'nie';
        }      

        // dodaje do sesji informacje czy jest urzadzenie mobilne
        if ( !isset($_SESSION['mobile_urzadzenie']) ) {
             $_SESSION['mobile_urzadzenie'] = (($isMobile) ? 'tak' : 'nie');
        }        
        
        // jezeli szablon jest rwd
        if ( substr($DomyslnySzablonSklepu, -4) == '.rwd' ) {
             //
             $_SESSION['rwd'] = 'tak';
             $_SESSION['mobile'] = 'nie';
             //
           } else {
             //
             $_SESSION['rwd'] = 'nie';
             //
        }
        
        // jezeli nie jest wybrany szablon rwd to mozna przelaczyc na mobilny
        if ( $_SESSION['rwd'] != 'tak' ) {
        
            // jezeli jest szablon mobilny i jest rozpoznawanie urzadzen mobilnych
            if ( $_SESSION['mobileReczne'] == '' ) {
                //
                if ( $isMobile && MOBILNY_ROZPOZNAWANIE == 'tak' && SZABLON_MOBILNY == 'tak' ) {
                     $_SESSION['mobile'] = 'tak'; 
                   } else {
                     $_SESSION['mobile'] = 'nie';        
                }
                //
            }

            // lub jezeli jest reczne wywolanie szablonu mobilnego
            if ( isset($_GET['mobile']) && SZABLON_MOBILNY == 'tak' ) {
                 //
                 $_SESSION['mobile'] = 'tak';
                 $_SESSION['mobileReczne'] = 'bylo';
                 //
              } else if ( isset($_GET['mobile']) && SZABLON_MOBILNY == 'nie' ) {
                 //
                 $_SESSION['mobile'] = 'nie';
                 //
            }        
            // wywolanie oryginalnego szablonu
            if ( isset($_GET['nomobile']) ) {
                 //
                 $_SESSION['mobile'] = 'nie';
                 $_SESSION['mobileReczne'] = 'bylo';
                 //
            }  

        }
        
        if ( $_SESSION['mobile'] == 'nie' ) {
             $mobileSzablon = '';
        } 

        // zwraca nazwe szablonu sklepu - domyslny czy mobile
        if ( $_SESSION['mobile'] == 'tak' && SZABLON_MOBILNY == 'tak' ) {
             //
             if (is_dir('szablony/' . $DomyslnySzablonSklepu . $mobileSzablon)) {
                $DomyslnySzablonSklepu = $DomyslnySzablonSklepu . $mobileSzablon;
             } else {
                $DomyslnySzablonSklepu = 'standardowy' . $mobileSzablon;
             }
             //
        }      

        // zmiana szablonu reczna
        if ( isset($_GET['szablon']) || isset($_SESSION['szablon']) ) {
             //
             $Szablon = ((isset($_GET['szablon'])) ? $_GET['szablon'] : $_SESSION['szablon']);
             //
             if (is_dir('szablony/' . $Szablon)) {
                 $DomyslnySzablonSklepu = $Szablon;
                 $_SESSION['szablon'] = $Szablon;
                 $_SESSION['mobile'] = 'nie';
             }
             //
        }
        if ( isset($_GET['noszablon']) ) {
             //
             unset($_SESSION['szablon']);
             Funkcje::PrzekierowanieURL('/'); 
             //
        }
                
        return $DomyslnySzablonSklepu;
    
    }
    
    public static function UrzadzanieMobilne() {
        
        // rozpoznaje urzadzanie mobilne
        
        if( isset($_SERVER['HTTP_USER_AGENT']) ) {
            $isMobile = (bool)preg_match('#\b(ip(hone|od|ad)|android|opera m(ob|in)i|windows (phone|ce)|blackberry|tablet'.
                                '|s(ymbian|eries60|amsung)|p(laybook|alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
                                '|mobile|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );  
        } else {
            $isMobile = false;
        }
        
        return $isMobile;

    }

    public static function BoxKategorie() {
    
        $TrescBoxu = '';
        
        if ( $_SESSION['mobile'] == 'tak' && SZABLON_MOBILNY == 'tak' ) {
        
            ob_start();
            require('szablony/'.DOMYSLNY_SZABLON.'/boxy_lokalne/kategorie.mobilne.php');
            $_wynik = ob_get_contents();
            ob_end_clean();                         
            $TrescBoxu = $_wynik;
            unset($_wynik);

        }

        return $TrescBoxu;

    }
    
    public static function BoxProducenci() {
    
        $TrescBoxu = '';

        if ( MOBILNY_BOX_PRODUCENCI == 'tak' && $_SESSION['mobile'] == 'tak' && SZABLON_MOBILNY == 'tak' ) {
        
            ob_start();
            require('szablony/'.DOMYSLNY_SZABLON.'/boxy_lokalne/producenci.mobilne.php');
            $_wynik = ob_get_contents();
            ob_end_clean();                         
            $TrescBoxu = $_wynik;
            unset($_wynik); 
        
        }
        
        define('PRODUCENCI_MOBILNE', strlen($TrescBoxu));        

        return $TrescBoxu;

    }   

    public static function ModulAktualnosci() {
    
        $TrescModulu = '';

        if ( MOBILNY_MODUL_AKTUALNOSCI == 'tak' && $_SESSION['mobile'] == 'tak' && SZABLON_MOBILNY == 'tak' ) {

            ob_start();
            require('szablony/'.DOMYSLNY_SZABLON.'/moduly_lokalne/aktualnosci.mobilne.php');
            $_wynik = ob_get_contents();
            ob_end_clean();                         
            $TrescModulu = $_wynik;
            unset($_wynik);

        }
        
        define('AKTUALNOSCI_MOBILNE', strlen($TrescModulu));  

        return $TrescModulu;

    }    

    public static function ModulProduktow( $Produkty = 'nowosci', $LimitZapytania = 4 ) {
    
        $TrescModulu = '';
        
        if ( $_SESSION['mobile'] == 'tak' && SZABLON_MOBILNY == 'tak' ) {
        
            ob_start();
            //
            $WyswietlProdukty = $Produkty;
            //
            require('szablony/'.DOMYSLNY_SZABLON.'/moduly_lokalne/produkty.mobilne.php');
            $_wynik = ob_get_contents();
            ob_end_clean();                         
            $TrescModulu = $_wynik;
            unset($_wynik, $WyswietlProdukty);  
            
            if ( $Produkty == 'hity' ) {
                 //    
                 if ( MOBILNY_MODUL_HITY == 'nie' ) {
                     $TrescModulu = '';
                 }
                 //
                 define('HITY_MOBILNE', strlen($TrescModulu));
                 //
            }
            
            if ( $Produkty == 'nowosci' ) {
                 //
                 if ( MOBILNY_MODUL_NOWOSCI == 'nie' ) {
                     $TrescModulu = '';
                 }
                 //
                 define('NOWOSCI_MOBILNE', strlen($TrescModulu));  
                 //
            }

            if ( $Produkty == 'promocje' ) {
                 //
                 if ( MOBILNY_MODUL_PROMOCJE == 'nie' ) {
                     $TrescModulu = '';
                 }
                 //
                 define('PROMOCJE_MOBILNE', strlen($TrescModulu));
                 //
            }
            
            if ( $Produkty == 'polecane' ) {
                 //
                 if ( MOBILNY_MODUL_POLECANE == 'nie' ) {
                     $TrescModulu = '';
                 }
                 //
                 define('POLECANE_MOBILNE', strlen($TrescModulu));
                 //
            }

        }
        
        return $TrescModulu;

    }     
    
    public static function MobilnyZmianaJezyka() {
        global $Wyglad;
    
        if ( $_SESSION['mobile'] == 'tak' && SZABLON_MOBILNY == 'tak' ) {
    
            return $Wyglad->ZmianaJezyka();
            
        }
    
    }
  
} 

?>