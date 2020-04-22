<?php

class NewsletterPopup { 

    public static function DodajKuponNewslettera( $adresEmail ) {
    
        // pobiera konfiguracje modulu stalego newsletter popup
        
        $zapytanie = "select * from theme_modules_fixed_settings where modul_id = '5'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        while ( $info = $sql->fetch_assoc() ) {
            //
            define( $info['modul_settings_code'], $info['modul_settings_value'] );
            //
        }    
        $GLOBALS['db']->close_query($sql);
        unset($info, $zapytanie);      
    
        // zapisywanie kuponu do bazy
        
        $DopuszczalneZnaki = '1234567890QWERTYUIOPASDFGHJKKLZXCVBNM';
        $KodKuponu = '';
        for ($i=0; $i <= 10; $i++)
        {
            $KodKuponu .= $DopuszczalneZnaki[rand()%(strlen($DopuszczalneZnaki))];
        }     
        unset($DopuszczalneZnaki);
        
        //            
        $pola = array(
                array('coupons_status','1'),
                array('coupons_name',$KodKuponu),
                array('coupons_description','Kupon za zapisanie do newslettera, email: ' . $adresEmail),
                array('coupons_discount_type',(( RODZAJ_KUPONU != 'procent' ) ? 'fixed' : 'percent' )),   
                array('coupons_discount_value',(int)WARTOSC_KUPONU),
                array('coupons_min_order',(int)WARTOSC_KUPONU_MIN_ZAMOWIENIE),
                array('coupons_min_quantity','0'),
                array('coupons_quantity','1'),
                array('coupons_specials','1'),
                array('coupons_date_added','now()'),
                array('coupons_email',$adresEmail),
                array('coupons_customers_groups_id',0),
                array('coupons_date_end','0000-00-00'),
                array('coupons_date_start','0000-00-00'),
                
        );

        //			
        $GLOBALS['db']->insert_query('coupons' , $pola);	
        unset($pola);  

        return $KodKuponu;
    
    }
    
    public static function WyslijKuponNewslettera( $kupon, $adresEmail ) {
    
        $zapytanie = "select title, content, templates_id, language_id, destination from newsletters where newsletters_id = '" . NEWSLETTER_ID . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);    
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
    
            // wyslanie maila
            
            $info = $sql->fetch_assoc();

            $email = new Mailing;

            // podmiana linku do wypisania z newslettera
            $cont = $info['content'];
            
            // jezeli newsletter to mailing musi byc inny link wypisania
            if ((int)$info['destination'] == 5) {
                //
                $cont = str_replace('{LINK}','<a href="'.ADRES_URL_SKLEPU.'/mailing-wypisz.html/email='.$adresEmail.'">',$cont);
                $cont = str_replace('{/LINK}','</a>',$cont);
                //
              } else {
                //
                $cont = str_replace('{LINK}','<a href="'.ADRES_URL_SKLEPU.'/newsletter-wypisz.html/email='.$adresEmail.'">',$cont);
                $cont = str_replace('{/LINK}','</a>',$cont);            
                //
            }
            
            define('KUPON_RABATOWY', $kupon);
            
            $nadawca_email   = INFO_EMAIL_SKLEPU;
            $nadawca_nazwa   = INFO_NAZWA_SKLEPU;
            $adresat_email   = $adresEmail;
            $adresat_nazwa   = $adresEmail;
            $temat           = $info['title'];
            $cc              = '';
            $tekst           = $cont;
            $zalaczniki      = array();
            $szablon         = $info['templates_id'];
            $jezyk           = $info['language_id'];

            $tekst = Funkcje::parsujZmienne($tekst);
            $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $tekst);        

            $wiadomosc = $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, false);    

        }
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);        
    
    }
    
}

?>