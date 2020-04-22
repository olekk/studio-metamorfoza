<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;

    if (get_magic_quotes_gpc()) {
        $_POST = Funkcje::stripslashes_array($_POST);
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('REJESTRACJA') ), $GLOBALS['tlumacz'] );

        //zapisanie danych klienta do bazy - START
        $zakodowane_haslo = Funkcje::zakodujHaslo($filtr->process($_POST["haslo"]));
        $pola = array(
                array('customers_firstname',$filtr->process($_POST['imie'])),
                array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                array('customers_email_address',$filtr->process($_POST['email'])),
                array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                array('customers_fax',( isset($_POST['fax']) ? $filtr->process($_POST['fax']) : '' )),
                array('customers_password',$zakodowane_haslo),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_guest_account','0'),
                array('customers_discount','0.00'),
                array('customers_groups_id','1'),
                array('customers_status',(KLIENT_AKTYWACJA == 'tak' ? '1' : '0')),
                array('customers_agreement',( isset($_POST['regulamin']) ? '1' : '0')),
                array('customers_przetwarzanie',( isset($_POST['przetwarzanie']) ? '1' : '0')),
                array('customers_shopping_points', ( SYSTEM_PUNKTOW_STATUS == 'tak' ? SYSTEM_PUNKTOW_PUNKTY_REJESTRACJA : '' )),
                array('customers_dod_info',''),
                array('language_id',$_SESSION['domyslnyJezyk']['id']));
        
        if (isset($_POST['nick'])) {
          $pola[] = array('customers_nick',$filtr->process($_POST['nick']));
        }        

        if (isset($_POST['plec'])) {
          $pola[] = array('customers_gender',$filtr->process($_POST['plec']));
        }
            
        if (isset($_POST['data_urodzenia'])) {
          $pola[] = array('customers_dob', date('Y-m-d', strtotime($filtr->process($_POST['data_urodzenia']))));
        }

        $sql = $GLOBALS['db']->insert_query('customers' , $pola);
        $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();
        unset($pola);

        $pola = array(
                array('customers_info_id',$id_dodanej_pozycji),
                array('customers_info_number_of_logons','0'),
                array('customers_info_date_account_created','now()'),
                array('customers_info_date_account_last_modified','now()'));
                
        $sql = $GLOBALS['db']->insert_query('customers_info' , $pola);
        unset($pola);

        $pola = array(
                array('customers_id',$id_dodanej_pozycji),
                array('entry_company',$filtr->process($_POST['nazwa_firmy'])),
                array('entry_nip',$filtr->process($_POST['nip_firmy'])),
                array('entry_firstname',$filtr->process($_POST['imie'])),
                array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                array('entry_street_address',$filtr->process($_POST['ulica'])),
                array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                array('entry_city',$filtr->process($_POST['miasto'])),
                array('entry_country_id',$filtr->process($_POST['panstwo'])),
                array('entry_zone_id',(isset($_POST['wojewodztwo']) ? $filtr->process($_POST['wojewodztwo']) : '')));
        
        if (isset($_POST['pesel'])) {
          $pola[] = array('entry_pesel',$filtr->process($_POST['pesel']));
        }        

        $sql = $GLOBALS['db']->insert_query('address_book' , $pola);
        $id_dodanej_pozycji_adres = $GLOBALS['db']->last_id_query();
        unset($pola);

        $pola = array(
                array('customers_default_address_id',$id_dodanej_pozycji_adres));

        $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '".(int)$id_dodanej_pozycji."'");	
        unset($pola);

        // dodatkowe pola klientow
        $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type 
                                      FROM customers_extra_fields ce 
                                     WHERE ce.fields_status = '1'";

        $sql = $GLOBALS['db']->open_query($dodatkowe_pola_klientow);

        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

          while ( $dodatkowePola = $sql->fetch_assoc() ) {
          
            $wartosc = '';
            $pola = array();
            if ( $dodatkowePola['fields_input_type'] != '3' ) {
                //
                if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
                  //
                  $pola = array(
                          array('customers_id',$id_dodanej_pozycji),
                          array('fields_id',$dodatkowePola['fields_id']),
                          array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']])));
                  //
                }
                //
            } else {
                //
                if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
                  //
                  foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
                    $wartosc .= $value . "\n";
                  }
                  $pola = array(
                          array('customers_id',$id_dodanej_pozycji),
                          array('fields_id',$dodatkowePola['fields_id']),
                          array('value',$filtr->process($wartosc)));
                }
                //
            }

            if ( count($pola) > 0 ) {
              $pola[] = array('language_id', $_SESSION['domyslnyJezyk']['id']);
              $GLOBALS['db']->insert_query('customers_to_extra_fields' , $pola);
            }
            unset($pola);
            
          }
          
        }
        //

        // dane do newslettera
        
        // najpierw usuwa dane jezeli juz kiedys byl dodany taki email
        $db->delete_query('subscribers' , " subscribers_email_address = '".$filtr->process($_POST['email'])."'"); 
        //    
        $pola = array(
                array('customers_id',$id_dodanej_pozycji),
                array('subscribers_email_address',$filtr->process($_POST['email'])),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('date_added','now()')
        );

        $sql = $GLOBALS['db']->insert_query('subscribers' , $pola);
        unset($pola);

        // dane do punktow
        if ( SYSTEM_PUNKTOW_STATUS == 'tak' && (int)SYSTEM_PUNKTOW_PUNKTY_REJESTRACJA > 0 ) {
          $pola = array(
                  array('customers_id',$id_dodanej_pozycji),
                  array('points',(int)SYSTEM_PUNKTOW_PUNKTY_REJESTRACJA),
                  array('date_added','now()'),
                  array('date_confirm','now()'),
                  array('points_status','2'),
                  array('points_type','RJ'),
          );

          $sql = $GLOBALS['db']->insert_query('customers_points' , $pola);
          unset($pola);
        }

        // zapisanie danych klienta do bazy - KONIEC

        // wyslanie maila do klienta - START
        $jezyk_maila = $_SESSION['domyslnyJezyk']['id'];
        if ( KLIENT_AKTYWACJA == 'tak' ) {
            $warunek = 'EMAIL_REJESTRACJA_KLIENTA_KONTO_AKTYWNE';
        } else {
            $warunek = 'EMAIL_REJESTRACJA_KLIENTA_KONTO_NIEAKTYWNE';
        }

        $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = '".$warunek."'";

        $sql = $GLOBALS['db']->open_query($zapytanie_tresc);
        $tresc = $sql->fetch_assoc();        

        if ( WLACZENIE_SSL == 'tak' ) {
            define('LINK', ADRES_URL_SKLEPU_SSL."/logowanie.html");
        } else {
            define('LINK', ADRES_URL_SKLEPU."/logowanie.html");
        }
        define('HASLO', $filtr->process($_POST["haslo"]));  
        define('LOGIN', ( isset($_POST['nick']) && $_POST['nick'] != '' ? $_POST['nick'] : $filtr->process($_POST['email']) ));  
        define('BIEZACA_DATA', date("d-m-Y H:i:s"));  
        define('KLIENT_IP', $filtr->process($_POST["adres_ip"]));  

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
        $adresat_nazwa   = $filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko']);

        $temat           = Funkcje::parsujZmienne($tresc['email_title']);
        $tekst           = $tresc['description'];
        $zalaczniki      = $tablicaZalacznikow;
        $szablon         = $tresc['template_id'];
        $jezyk           = (int)$jezyk_maila;

        $tekst = Funkcje::parsujZmienne($tekst);
        $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $tekst);

        $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);

        $GLOBALS['db']->close_query($sql);
        unset($wiadomosc, $tresc, $zapytanie_tresc);             

        if ( KLIENT_AKTYWACJA == 'tak' ) {
        
          // zarejestrowanie sesji klienta - START
          $_SESSION['customer_id'] = $id_dodanej_pozycji;
          $_SESSION['customer_email'] = $filtr->process($_POST['email']);
          $_SESSION['customer_default_address_id'] = $id_dodanej_pozycji_adres;
          $_SESSION['customer_firstname'] = $filtr->process($_POST['imie']);
          $_SESSION['customers_groups_id'] = '1';
          $_SESSION['gosc'] = '0';
          //
          // minimalne zamowienie
          $zapytanieMinZam = "SELECT customers_groups_min_amount FROM customers_groups WHERE customers_groups_id = '1'";
          $sqlMinZam= $GLOBALS['db']->open_query($zapytanieMinZam);  
          $infoMinZam = $sqlMinZam->fetch_assoc(); 
          //
          $_SESSION['min_zamowienie'] = $infoMinZam['customers_groups_min_amount'];
          //
          $GLOBALS['db']->close_query($sqlMinZam); 
          unset($zapytanieMinZam, $infoMinZam);             
          //
          $zapytanie_kraj = "SELECT countries_id, countries_iso_code_2 FROM countries WHERE countries_id = '".(int)$_POST['panstwo']."'";
          $sql_kraj = $GLOBALS['db']->open_query($zapytanie_kraj);
          $wynik_kraj = $sql_kraj->fetch_assoc();
          $_SESSION['krajDostawy'] = array();
          $_SESSION['krajDostawy'] = array('id' => $wynik_kraj['countries_id'],
                                           'kod' => $wynik_kraj['countries_iso_code_2']);
          $GLOBALS['db']->close_query($sql_kraj);
          unset($zapytanie_kraj, $wynik_kraj); 

          $wojewodztwo = '';
          if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
              if ( isset($_POST['wojewodztwo']) ) {
                  $wojewodztwo = $filtr->process($_POST['wojewodztwo']);
              }
          }

          if (!isset($_SESSION['adresDostawy'])) {
            $_SESSION['adresDostawy'] = array();
          }
          $_SESSION['adresDostawy'] = array('imie' => $filtr->process($_POST['imie']),
                                            'nazwisko' => $filtr->process($_POST['nazwisko']),
                                            'firma' => $filtr->process($_POST['nazwa_firmy']),
                                            'ulica' => $filtr->process($_POST['ulica']),
                                            'kod_pocztowy' => $filtr->process($_POST['kod_pocztowy']),
                                            'miasto' => $filtr->process($_POST['miasto']),
                                            'telefon' => ( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' ),
                                            'panstwo' => $filtr->process($_POST['panstwo']),
                                            'wojewodztwo' => $wojewodztwo
          );

          if (!isset($_SESSION['adresFaktury'])) {
            $_SESSION['adresFaktury'] = array();
          }
          $_SESSION['adresFaktury'] = array('imie' => $filtr->process($_POST['imie']),
                                            'nazwisko' => $filtr->process($_POST['nazwisko']),
                                            'firma' => $filtr->process($_POST['nazwa_firmy']),
                                            'nip' => $filtr->process($_POST['nip_firmy']),
                                            'ulica' => $filtr->process($_POST['ulica']),
                                            'kod_pocztowy' => $filtr->process($_POST['kod_pocztowy']),
                                            'miasto' => $filtr->process($_POST['miasto']),
                                            'panstwo' => $filtr->process($_POST['panstwo']),
                                            'wojewodztwo' => $wojewodztwo
          );

          new Klient($id_dodanej_pozycji);
          
          // przelicza koszyk klienta po zarejestrowaniu
          $GLOBALS['koszykKlienta']->PrzeliczKoszyk(); 
          
          // zarejestrowanie sesji klienta - KONIEC
        }

        echo '<div id="PopUpInfo">';  
        
        if (KLIENT_AKTYWACJA == 'tak') { 

            echo $GLOBALS['tlumacz']['REJESTRACJA_KONTO_AKTYWNE_SUKCES'];
            
        } else {
        
            echo $GLOBALS['tlumacz']['REJESTRACJA_KONTO_NIEAKTYWNE_SUKCES'];
        
        }

        echo '</div>';
        
        echo '<div id="PopUpPrzyciski">';
        
            if (KLIENT_AKTYWACJA == 'tak') { 
        
                if ( WLACZENIE_SSL == 'tak' ) {
                    $link = ADRES_URL_SKLEPU_SSL . '/panel-klienta.html';
                  } else {
                    $link = 'panel-klienta.html';
                }                  
            
                echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PANEL_KLIENTA'].'</a>';
                
                unset($link);   

                if ( WLACZENIE_SSL == 'tak' ) {
                    $link = ADRES_URL_SKLEPU_SSL . '/koszyk.html';
                  } else {
                    $link = 'koszyk.html';
                }                  
            
                echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_KOSZYKA'].'</a>';                    
                
                unset($link);
                
                echo '<br /><br />';

            }
            
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
?>