<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    // jezeli jest wypisanie
    $_GET['wypisz'] = 'nie';
    if ( isset($_POST['wypisz']) && $_POST['wypisz'] == 'tak' ) {
         $_GET['wypisz'] = 'tak';
    }

    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;
    
    if (get_magic_quotes_gpc()) {
        $_POST = Funkcje::stripslashes_array($_POST);
    }
    
    // opcja do zapisania z boxu
    
    if ( !isset($_POST['popup']) ) {

        if (isset($_POST['email']) && Sesje::TokenSpr()) {

            $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('NEWSLETTER') ), $GLOBALS['tlumacz'] );

            if( !filter_var($filtr->process($_POST['email']), FILTER_VALIDATE_EMAIL) ) {
            
                echo '<div id="PopUpInfo">';
                
                echo $GLOBALS['tlumacz']['BLAD_ZLY_EMAIL'];
                
                echo '</div>';
                
            } else {

                // jezeli jest wypisanie z newslettera
                if ( $_GET['wypisz'] == 'tak' ) {
                
                    //
                    // sprawdza czy jest adres w bazie
                    $zapytanie = "SELECT subscribers_email_address, customers_id FROM subscribers WHERE subscribers_email_address = '" . $filtr->process($_POST['email']) . "'";

                    $sql = $db->open_query($zapytanie); 
                    if ((int)$db->ile_rekordow($sql) > 0) { 

                        $info = $sql->fetch_assoc(); 
                        
                        // jezeli jest to klient sklepu to wylaczy tylko z newslettera
                        if ((int)$info['customers_id'] > 0) {
                    
                            $pola = array(array('customers_newsletter','0'), 
                                          array('customers_newsletter_group',''));
                            $GLOBALS['db']->update_query('subscribers' , $pola, " subscribers_email_address = '" . $filtr->process($_POST['email']) . "'");	
                            unset($pola);
                            
                            $pola = array(array('customers_newsletter','0'), 
                                          array('customers_newsletter_group',''));
                            $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                            unset($pola);                

                          } else {
                          
                            $db->delete_query('subscribers' , " subscribers_email_address = '" . $filtr->process($_POST['email']) . "'");	            
                          
                        }
                        
                        unset($info);

                        echo '<div id="PopUpUsun">';
                    
                        echo $GLOBALS['tlumacz']['NEWSLETTER_USUNIECIE'];
                        
                        echo '</div>';                    
                    
                    } else {
                    
                        echo '<div id="PopUpUsun">';
                    
                        echo $GLOBALS['tlumacz']['NEWSLETTER_BLAD'];
                        
                        echo '</div>';                   
                    
                    }
                    
                    $db->close_query($sql);
                    unset($zapytanie);          
                
                } else {
                
                    // jezeli jest dodawanie do newslettera
                    echo '<div id="PopUpDodaj">';
                    
                    // sprawdza czy takiego adresu juz nie ma w bazie
                    $zapytanie = "SELECT customers_id, subscribers_email_address, customers_newsletter FROM subscribers WHERE subscribers_email_address = '" . $filtr->process($_POST['email']) . "'";
                    $sql = $db->open_query($zapytanie); 

                    if ((int)$db->ile_rekordow($sql) == 0) {

                        if (NEWSLETTER_AKTYWACJA == 'nie') {
                    
                            // dopisywanie do bazy
                        
                            $pola = array(
                                    array('subscribers_email_address',$filtr->process($_POST['email'])),
                                    array('customers_newsletter','1'),
                                    array('date_added','now()'),
                                    array('date_account_accept','now()'),
                                    array('ip_host',$_SERVER['REMOTE_ADDR'])
                            );
                            $GLOBALS['db']->insert_query('subscribers' , $pola);	
                            
                            unset($pola, $ip);     
                            
                            echo $GLOBALS['tlumacz']['NEWSLETTER_DODANY_EMAIL'] . ' <br />';
                            
                        } else {
                        
                            $pola = array(
                                    array('subscribers_email_address',$filtr->process($_POST['email'])),
                                    array('customers_newsletter','0'),
                                    array('date_added','now()'),
                                    array('ip_host',$_SERVER['REMOTE_ADDR'])
                            );
                            $GLOBALS['db']->insert_query('subscribers' , $pola);	        
                        
                            echo $GLOBALS['tlumacz']['NEWSLETTER_INFO_DODANIE_POTWIERDZENIE'] . ' <br />';
                            
                            $jezyk_maila = $_SESSION['domyslnyJezyk']['id'];

                            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = 'EMAIL_POTWIERDZENIE_EMAIL_NEWSLETTERA'";
                            $sqlz = $GLOBALS['db']->open_query($zapytanie_tresc);
                            $tresc = $sqlz->fetch_assoc();        

                            $email = new Mailing;

                            if ( $tresc['email_file'] != '' ) {
                                $tablicaZalacznikow = explode(';', $tresc['email_file']);
                            } else {
                                $tablicaZalacznikow = array();
                            }

                            $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                            $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
                            $cc              = Funkcje::parsujZmienne($tresc['dw']);

                            $adresat_email   = $filtr->process($_POST['email']);
                            $adresat_nazwa   = 'klient';

                            $temat           = Funkcje::parsujZmienne($tresc['email_title']);
                            $tekst           = $tresc['description'];
                            $zalaczniki      = $tablicaZalacznikow;
                            $szablon         = $tresc['template_id'];
                            $jezyk           = (int)$jezyk_maila;
                            
                            $tekst = str_replace('{LINK}','<a href="'.ADRES_URL_SKLEPU."/newsletter-potwierdzenie.html/email=" . $filtr->process($_POST['email']).'">',$tekst);
                            $tekst = str_replace('{/LINK}','</a>',$tekst);                      

                            $tekst = Funkcje::parsujZmienne($tekst);
                            $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $tekst);

                            $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);

                            $GLOBALS['db']->close_query($sqlz);
                            unset($wiadomosc, $tresc, $zapytanie_tresc);               
                        
                        }

                    } else {
                    
                        $info = $sql->fetch_assoc();
                    
                        // jezeli jest to klient a nie jest zapisany
                        
                        if ($info['customers_id'] > 0 && $info['customers_newsletter'] == '0') {
                        
                            $pola = array(array('customers_newsletter','1'),
                                          array('date_added','now()'));
                            $GLOBALS['db']->update_query('subscribers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                            unset($pola);
                            
                            $pola = array(array('customers_newsletter','1'));
                            $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                            unset($pola);      
                            
                            echo $GLOBALS['tlumacz']['NEWSLETTER_DODANY_EMAIL'] . ' <br />'; 

                        // jezeli nie jest to klient i nie jest zapisany
                        
                        } else if ($info['customers_id'] == 0 && $info['customers_newsletter'] == '0') {
                        
                            $pola = array(array('customers_newsletter','1'),
                                          array('date_added','now()'));
                            $GLOBALS['db']->update_query('subscribers' , $pola, " subscribers_email_address = '" . $info['subscribers_email_address'] . "'");	
                            unset($pola);

                            echo $GLOBALS['tlumacz']['NEWSLETTER_DODANY_EMAIL'] . ' <br />'; 

                        } else {
                    
                            // jezeli taki adres jest juz w bazie
                            echo $GLOBALS['tlumacz']['NEWSLETTER_DODANY_EMAIL_JUZ_JEST'] . ' <br />';
                            
                        }
                        
                        unset($info);
                    
                    }

                    echo '</div>';
                    
                    $db->close_query($sql);
                    unset($zapytanie);

                }
                
            }
            
            echo '<div id="PopUpPrzyciski">';
            
                    if ( WLACZENIE_SSL == 'tak' ) {
                      $link = ADRES_URL_SKLEPU;
                    } else {
                      $link = '/';
                    }                
                    echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_DO_STRONY_GLOWNEJ'].'</a>'; 
                    unset($link);
                
            echo '</div>';

        }
        
    }
    
    // newsletter z popup
    
    if ( isset($_POST['popup']) ) {

        if (isset($_POST['email']) && Sesje::TokenSpr()) {

            $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('NEWSLETTER','MODULY_STALE') ), $GLOBALS['tlumacz'] );

            if( !filter_var($filtr->process($_POST['email']), FILTER_VALIDATE_EMAIL) ) {
            
                echo '<span>' . $GLOBALS['tlumacz']['BLAD_ZLY_EMAIL'] . '</span>';
                
            } else {
            
                if ( isset($_POST['zgoda_newsletter']) ) {

                    // sprawdza czy takiego adresu juz nie ma w bazie
                    $zapytanie = "SELECT customers_id, subscribers_email_address, customers_newsletter FROM subscribers WHERE subscribers_email_address = '" . $filtr->process($_POST['email']) . "'";
                    $sql = $db->open_query($zapytanie); 

                    if ((int)$db->ile_rekordow($sql) == 0) {

                        // dopisywanie do bazy
                    
                        $pola = array(
                                array('subscribers_email_address',$filtr->process($_POST['email'])),
                                array('customers_newsletter','1'),
                                array('date_added','now()'),
                                array('date_account_accept','now()'),
                                array('ip_host',$_SERVER['REMOTE_ADDR'])
                        );
                        $GLOBALS['db']->insert_query('subscribers' , $pola);	
                        
                        unset($pola, $ip);     

                        // dodaje kupon do bazy
                        $KodKuponu = NewsletterPopup::DodajKuponNewslettera( $filtr->process($_POST['email']) );
                        
                        // wysyla kupon na maila
                        NewsletterPopup::WyslijKuponNewslettera( $KodKuponu, $filtr->process($_POST['email']) );
                        
                        // tworzy ciasteczko zeby nie wyswietlac okna
                        setcookie("newsletterPopup", "tak", time() + ((60*60*24*30)*12), '/');
                        
                        echo '<strong class="DodanyNewsletter">' . $GLOBALS['tlumacz']['NEWSLETTER_POPUP_DODANY_EMAIL'] . ' </strong> <br />';
                        
                        unset($KodKuponu);
                       
                    } else {
                    
                        $info = $sql->fetch_assoc();
                    
                        // jezeli jest to klient a nie jest zapisany
                        
                        if ($info['customers_id'] > 0 && $info['customers_newsletter'] == '0') {
                        
                            $pola = array(array('customers_newsletter','1'),
                                          array('date_added','now()'));
                            $GLOBALS['db']->update_query('subscribers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                            unset($pola);
                            
                            $pola = array(array('customers_newsletter','1'));
                            $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                            unset($pola);      

                            // dodaje kupon do bazy
                            $KodKuponu = NewsletterPopup::DodajKuponNewslettera( $filtr->process($_POST['email']) );
                            
                            // wysyla kupon na maila
                            NewsletterPopup::WyslijKuponNewslettera( $KodKuponu, $filtr->process($_POST['email']) );                        
                            
                            // tworzy ciasteczko zeby nie wyswietlac okna
                            setcookie("newsletterPopup", "tak", time() + ((60*60*24*30)*12), '/'); 

                            echo '<strong class="DodanyNewsletter">' . $GLOBALS['tlumacz']['NEWSLETTER_POPUP_DODANY_EMAIL'] . '</strong> <br />'; 

                        // jezeli nie jest to klient i nie jest zapisany
                        
                        } else if ($info['customers_id'] == 0 && $info['customers_newsletter'] == '0') {
                        
                            $pola = array(array('customers_newsletter','1'),
                                          array('date_added','now()'));
                            $GLOBALS['db']->update_query('subscribers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                            unset($pola);

                            // dodaje kupon do bazy
                            $KodKuponu = NewsletterPopup::DodajKuponNewslettera( $filtr->process($_POST['email']) );
                            
                            // wysyla kupon na maila
                            NewsletterPopup::WyslijKuponNewslettera( $KodKuponu, $filtr->process($_POST['email']) );                        
                            
                            // tworzy ciasteczko zeby nie wyswietlac okna
                            setcookie("newsletterPopup", "tak", time() + ((60*60*24*30)*12), '/');

                            echo '<strong class="DodanyNewsletter">' . $GLOBALS['tlumacz']['NEWSLETTER_POPUP_DODANY_EMAIL'] . '</strong> <br />';                             

                        // jezeli jest adres ale nie zapisany
                                                    
                        } else {
                    
                            // jezeli taki adres jest juz w bazie
                            echo '<span>' . $GLOBALS['tlumacz']['NEWSLETTER_POPUP_EMAIL_JUZ_JEST'] . '</span> <br />';
                            
                        }
                        
                        unset($info);
                    
                    }

                    $db->close_query($sql);
                    unset($zapytanie);
                    
                } else {
                
                    echo '<span>' . $GLOBALS['tlumacz']['NEWSLETTER_POPUP_BRAK_ZGODY'] . '</span>';
                
                }

            }

        }
        
    }    
    
}
?>