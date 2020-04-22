<?php
if (!isset($_COOKIE['newsletterPopup'])) {

    $zapytanie = "select tmfd.modul_settings_code, tmfd.modul_settings_value from theme_modules_fixed tmf, theme_modules_fixed_settings tmfd where tmf.modul_id = tmfd.modul_id and tmf.modul_file = 'newsletter_popup.php'";
    $sqlPopup = $GLOBALS['db']->open_query($zapytanie);
    while ( $info = $sqlPopup->fetch_assoc() ) {
        //
        define( $info['modul_settings_code'], $info['modul_settings_value'] );
        //
    }    
    $GLOBALS['db']->close_query($sqlPopup);
    unset($info, $zapytanie);  

    if ( (int)WARTOSC_KUPONU > 0 ) {

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );

        echo "\n\n";
        
        echo '<div id="InfoNewsletter"></div>';
        
        echo '<div id="InfoNewsletterOkno">';
        
            echo '<div id="NewsletterZamknij"><span></span></div>';
            
            echo '<h4>{__TLUMACZ:NEWSLETTER_POPUP_NAGLOWEK}</h4>';
            
            // rodzaj kuponu
            if ( RODZAJ_KUPONU == 'procent' ) {
                 //
                 $Kupon = (int)WARTOSC_KUPONU . '%';
                 //
            } else {
                 //
                 $WartoscPrzeliczona = $CenaProduktu = $GLOBALS['waluty']->FormatujCene( (int)WARTOSC_KUPONU );             
                 $Kupon = $WartoscPrzeliczona['brutto'];
                 unset($WartoscPrzeliczona);
                 //
            }
        
            echo '{__TLUMACZ:NEWSLETTER_POPUP_OPIS} <b>' . $Kupon . '</b>';
            
            unset($Kupon);
            
            echo '<form action="/" onsubmit="return sprNewsletterPopup(this)" method="post" class="cmxform" id="newsletterPopup">';

                echo '<p>';

                    echo '<input type="text" name="email" id="emailNewsletterPopup" value="{__TLUMACZ:TWOJ_ADRES_EMAIL}" />';
                    
                echo '</p>'; 

                echo '<p>';        
                
                    echo '<input type="submit" id="submitNewsletterPopup" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_ZAPISZ}" />';
                    echo '<input type="hidden" name="popup" value="1" />';
                    
                echo '</p>';
                      
                echo '<div id="ZgodaPopup"><input type="checkbox" name="zgoda_newsletter" value="1" /> {__TLUMACZ:NEWSLETTER_POPUP_ZGODA}</div>';

                echo '<div id="BladDanych"></div>';
            
            echo '</form>';
            
            if ( WARTOSC_KUPONU_MIN_ZAMOWIENIE > 0 ) {
                //
                $WartoscPrzeliczona = $CenaProduktu = $GLOBALS['waluty']->FormatujCene( (int)WARTOSC_KUPONU_MIN_ZAMOWIENIE );             
                $KuponMinZamowienie = $WartoscPrzeliczona['brutto'];
                //        
                echo '<small>{__TLUMACZ:NEWSLETTER_POPUP_MIN_ZAMOWIENIE} ' . $KuponMinZamowienie . '</small>';
                //
                unset($WartoscPrzeliczona, $KuponMinZamowienie);
            }

        echo '</div>';
        
        echo '<script type="text/javascript">';
        echo '$.NewsletterPopup();';
        echo '</script>';   

    }

}
?>