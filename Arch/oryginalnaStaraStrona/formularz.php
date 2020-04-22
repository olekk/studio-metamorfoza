<?php

// plik
$WywolanyPlik = 'formularz';

include('start.php');

if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {

        // sprawdzenie czy formularz zawiera CAPTCHA
        $infoc['form_captcha'] = '1';
        $zapytanie = "SELECT id_form, form_captcha FROM form WHERE id_form = '" . (int)$_GET['id'] . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        $infoc = $sql->fetch_assoc();
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie);

        if ( ($infoc['form_captcha'] == '0') || ($infoc['form_captcha'] == '1' && isset($_SESSION['weryfikacja']) && $_SESSION['weryfikacja'] == $filtr->process($_POST['weryfikacja'])) ) {
        
            $NazwaProduktu = '';
            if (isset($_POST['produkt'])) {
                $Produkt = new Produkt( (int)$_POST['produkt'] );
                $NazwaProduktu = $Produkt->info['nazwa'];
            }

            //
            $zapytanie = "SELECT * FROM form_description WHERE id_form = '" . (int)$_GET['id'] . "' AND language_id = '".$_SESSION['domyslnyJezyk']['id']."'";
            $sql = $GLOBALS['db']->open_query($zapytanie);    
            //
            $info = $sql->fetch_assoc();

            $nadawca_email   = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
            $nadawca_nazwa   = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);
            $cc              = '';
            $odpowiedz_email = Funkcje::parsujZmienne(INFO_EMAIL_SKLEPU);
            $odpowiedz_nazwa = Funkcje::parsujZmienne(INFO_NAZWA_SKLEPU);

            $adresat_email = array();
            $adresat_email[] = $filtr->process($_POST['odbiorca']);
            
            $adresat_nazwa   = $filtr->process(INFO_NAZWA_SKLEPU);

            $temat           = str_replace('{PRODUKT}', $NazwaProduktu, $info['form_title_email']);
            $temat           = str_replace('{INFO_NAZWA_SKLEPU}', INFO_NAZWA_SKLEPU, $temat);

            $zalaczniki      = Array();
            $szablon         = $info['template_email_id'];
            $jezyk           = (int)$_SESSION['domyslnyJezyk']['id'];
            
            $nazwa_formularza = $info['form_name'];
            
            $tekst = '';

            if (!empty($info['form_text_email'])) {
                //
                $info['form_text_email'] = str_replace('{PRODUKT}', $NazwaProduktu, $info['form_text_email']);
                $info['form_text_email'] = str_replace('{INFO_NAZWA_SKLEPU}', INFO_NAZWA_SKLEPU, $info['form_text_email']);
                //
                $tekst .= '<div style="font-size:120%;padding:2px;padding-bottom:10px;">' . $info['form_text_email'] . '</div>';
            }
            
            $tekst .= '<table cellpadding="10">';
            
            if (isset($_POST['produkt'])) {
                // nazwa produktu z linkiem
                $tekst .= '<tr><td style="padding-right:10px;white-space:nowrap;">'. $GLOBALS['tlumacz']['NAZWA_PRODUKTU'] . '</td><td style="font-weight:bold">' . $Produkt->info['link_z_domena'] . '</td></tr>';     
            }
            
            $GLOBALS['db']->close_query($sql);
            unset($info, $zapytanie, $NazwaProduktu);
            
            // szuka nazw pol w bazie
            $zapytanie = "SELECT id_field, form_field_name, form_field_email, form_field_email_header, form_field_input_limit FROM form_field WHERE id_form = '" . (int)$_GET['id'] . "' AND language_id = '".$_SESSION['domyslnyJezyk']['id']."' ORDER BY form_field_sort";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            $TablicaPol = array();
            while ($info = $sql->fetch_assoc()) {
                //
                // jezeli jest typ pola email - a nie jest wpisany poprawny adres email - przerywa wysylanie
                if ($info['form_field_input_limit'] == 'email') {
                    if ( !Funkcje::CzyPoprawnyMail($filtr->process($_POST['fields_' . $info['id_field']])) ) {
                        Funkcje::PrzekierowanieURL( 'index.php' ); 
                    }
                }
                //
                // jezeli ma wyslac maile na inne adresy
                if ($info['form_field_email'] == 1) {
                    if (isset($_POST['fields_' . $info['id_field']])) {
                        $adresat_email[] = $filtr->process($_POST['fields_' . $info['id_field']]);
                        //
                        // jezeli nie jest poprawny adres email w formularzu - wraca ponownie do wyslania
                        if ( !Funkcje::CzyPoprawnyMail($filtr->process($_POST['fields_' . $info['id_field']])) ) {
                             //
                             $GLOBALS['db']->close_query($sql);
                             $adresPowrotu = Seo::link_SEO($nazwa_formularza, (int)$_GET['id'], 'formularz');
                             //
                             if (isset($_POST['produkt'])) {
                                 $adresPowrotu .= '/produkt=' . (int)$_POST['produkt'];
                             }
                             //
                             Funkcje::PrzekierowanieURL( $adresPowrotu ); 
                             //
                        }
                        //
                    }
                }
                // jezeli dany mail ma byc naglowkiem maila to zmieni nadawce
                if ($info['form_field_email_header'] == 1) {
                    if (isset($_POST['fields_' . $info['id_field']])) {
                        $odpowiedz_email = $filtr->process($_POST['fields_' . $info['id_field']]);
                        $odpowiedz_nazwa = $filtr->process($_POST['fields_' . $info['id_field']]);
                    }
                }
                //
                $TablicaPol['fields_' . $info['id_field']] = $info['form_field_name'];
                //
            }
            
            $GLOBALS['db']->close_query($sql);
            unset($info, $zapytanie, $nazwa_formularza);    

            // pobieranie tablicy post 
            foreach ($_POST as $klucz => $wartosc) {
                //
                if ( strpos($klucz, 'fields_') > -1 ) {
                    //
                    // jezeli wartosc jest tablica
                    $WartPola = '';
                    if (is_array($filtr->process($wartosc))) {
                        foreach ( $filtr->process($wartosc) as $warPola ) {
                            $WartPola .= $warPola . ', ';
                        }
                        $WartPola = substr($WartPola,0 , -2);
                      } else {
                        $WartPola = $filtr->process($wartosc);
                    }
                    if (empty($WartPola)) {
                        $WartPola = '-';
                    }
                    
                    // jezeli pole jest walutowe
                    $ZnakWaluty = '';
                    if ( isset($_POST[$klucz . '_waluta']) ) {
                        $ZnakWaluty = ' ' . $_SESSION['domyslnaWaluta']['symbol'];
                    }
                    if ( strpos($klucz, '_waluta') === false && strpos($klucz, '_plik') === false && strpos($klucz, '_kalendarz') === false ) {
                        $tekst .= '<tr><td style="padding-right:10px;white-space:nowrap;">' . ((isset($TablicaPol[$klucz])) ? $TablicaPol[$klucz] : '') . '</td><td style="font-weight:bold">' . $WartPola . $ZnakWaluty . '</td></tr>';
                    }
                    unset($ZnakWaluty);

                    // jezeli pole to pole file
                    if ( strpos($klucz, '_plik') > -1 ) {
                        $klucz = str_replace('_plik', '', $klucz);
                        $tekst .= '<tr><td style="padding-right:10px;white-space:nowrap;">' . ((isset($TablicaPol[$klucz])) ? $TablicaPol[$klucz] : '') . '</td><td style="font-weight:bold">' . ((isset($_FILES[$klucz]['name']) && $_FILES[$klucz]['name'] != '') ? $_FILES[$klucz]['name'] : '-') . '</td></tr>';
                        //
                    }
                }
                //
            }
            unset($TablicaPol);
            
            $zalaczniki = $_FILES;
            
            $tekst .= '</table>';

            // wysylanie maili
            foreach ( $adresat_email as $adres_email ) {
                //
                $email = new Mailing;
                $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adres_email, $adresat_nazwa, '', $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email,$odpowiedz_nazwa);
                unset($email);
                //
            }

            unset($wiadomosc, $nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email,$odpowiedz_nazwa, $infoc, $_SESSION['weryfikacja']); 

            if ( WLACZENIE_SSL == 'tak' ) {
                Funkcje::PrzekierowanieSSL('formularz-sukces-fs-'.(int)$_GET['id'].'.html' . ((isset($_POST['produkt']) && (int)$_POST['produkt'] > 0) ? '/produkt=' . (int)$_POST['produkt'] : ''));
            } else {
                Funkcje::PrzekierowanieURL('formularz-sukces-fs-'.(int)$_GET['id'].'.html' . ((isset($_POST['produkt']) && (int)$_POST['produkt'] > 0) ? '/produkt=' . (int)$_POST['produkt'] : ''));
            }
            
        } else {

          Funkcje::PrzekierowanieURL('brak-strony.html');
          if ( isset($_SESSION['weryfikacja']) ) {
            unset($_SESSION['weryfikacja']); 
          }
          
        }
        
    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html');
        
    }
    
}    

// dodatkowy warunek dla grup klientow
$warunekTmp = " and (f.form_customers_group_id = '0'";
if ( isset($_SESSION['customers_groups_id']) && (int)$_SESSION['customers_groups_id'] > 0 ) {
    $warunekTmp .= " or find_in_set(" . (int)$_SESSION['customers_groups_id'] . ", f.form_customers_group_id)";
}
$warunekTmp .= ") "; 

$zapytanie = "SELECT * FROM form f, form_description fd WHERE f.id_form = fd.id_form AND f.id_form = '" . (int)$_GET['id'] . "' AND f.form_status = '1' AND fd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'" . $warunekTmp;

unset($warunekTmp);

$sql = $GLOBALS['db']->open_query($zapytanie);

if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

    $info = $sql->fetch_assoc();
    
    // sprawdzenie linku SEO z linkiem w przegladarce
    if ( !isset($_GET['sukces']) ) {
        //
        Seo::link_Spr(Seo::link_SEO($info['form_name'], $info['id_form'], 'formularz'));
        //
    }
    
    //
    $Captcha = '';
    $NazwaProduktu = '';
    if (isset($_GET['produkt'])) {
        $Produkt = new Produkt( (int)$_GET['produkt'] );
        //
        // jezeli nie ma produktu lub produkt nie ma opcji negocjacji ceny
        if ($Produkt->CzyJestProdukt == false || ($Produkt->info['negocjacja'] == 'nie' && (int)$_GET['id'] == 4)) {
            Funkcje::PrzekierowanieURL('brak-strony.html');        
        }
        //
        $NazwaProduktu = $Produkt->info['nazwa'];
        
      // sprawdza czy nie jest to formularz z wymaganym id produktu
      // tylko zapytanie o produkt, negocjacja i polec znajomemu
    } else if ( ((int)$_GET['id'] == 2 || (int)$_GET['id'] == 3 || (int)$_GET['id'] == 4) && !isset($_GET['produkt']) ) {
        //
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
        //
    }
    //

    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    // meta tagi
    // zamiana nazwy produktu
    $info['form_meta_title_tag'] = str_replace('{PRODUKT}', $NazwaProduktu, $info['form_meta_title_tag']);  
    $info['form_meta_desc_tag'] = str_replace('{PRODUKT}', $NazwaProduktu, $info['form_meta_desc_tag']);  
    $info['form_meta_keywords_tag'] = str_replace('{PRODUKT}', $NazwaProduktu, $info['form_meta_keywords_tag']);  
    //
    $tpl->dodaj('__META_TYTUL', ((empty($info['form_meta_title_tag'])) ? $Meta['tytul'] : $info['form_meta_title_tag']));
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', ((empty($info['form_meta_keywords_tag'])) ? $Meta['slowa'] : $info['form_meta_keywords_tag']));
    $tpl->dodaj('__META_OPIS', ((empty($info['form_meta_desc_tag'])) ? $Meta['opis'] : $info['form_meta_desc_tag']));
    unset($Meta);
    
    // css do kalendarza
    $tpl->dodaj('__CSS_PLIK', ',zebra_datepicker');
    // dla wersji mobilnej
    $tpl->dodaj('__CSS_KALENDARZ', ',zebra_datepicker');
    
    //
    $NazwaFormularza = str_replace('{PRODUKT}', $NazwaProduktu, $info['form_name']);  

    // breadcrumb
    $nawigacja->dodaj($NazwaFormularza);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));
    
    // odbiorcy formularza    
    $TablicaOdbiorcow = array();
    for ($r = 1; $r < 6; $r++) {
        //
        if (!empty($info['form_email_' . $r]) && !empty($info['form_email_name_' . $r])) {
            $TablicaOdbiorcow[] = array('id' => $info['form_email_' . $r], 'text' => $info['form_email_name_' . $r]);
        }
        //
    }    

    // captcha
    if ( $info['form_captcha'] == 1 ) {
        $Captcha = true;
    }

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), count($TablicaOdbiorcow), $NazwaProduktu, $Captcha);  
    //
    $srodek->dodaj('__LINK', ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL."/" : '') . Seo::link_SEO($info['form_name'], $info['id_form'], 'formularz'));
    
    $srodek->dodaj('__ID_PRODUKTU', '');
    $srodek->dodaj('__LINK_PRODUKTU', '');
    if (isset($_GET['produkt'])) {
        $srodek->dodaj('__LINK_PRODUKTU', $Produkt->info['adres_seo'] );
        $srodek->dodaj('__ID_PRODUKTU', $Produkt->info['id'] );
    }

    //
    $srodek->dodaj('__NAGLOWEK_FORMULARZA', $NazwaFormularza);
    //
    $info['form_description'] = str_replace('{PRODUKT}', $NazwaProduktu, $info['form_description']);
    $info['form_description'] = str_replace('{INFO_NAZWA_SKLEPU}', INFO_NAZWA_SKLEPU, $info['form_description']);
    //    
    $srodek->dodaj('__OPIS_FORMULARZA', $info['form_description']);
    
    // jezeli nie ma odbiorcow przyjmie domyslny adres
    if (count($TablicaOdbiorcow) > 0) {
        $srodek->dodaj('__ODBIORCY', Funkcje::RozwijaneMenu('odbiorca', $TablicaOdbiorcow, '', ' style="width:50%"'));
      } else {
        $srodek->dodaj('__ODBIORCY', '<input type="hidden" name="odbiorca" value="' . INFO_EMAIL_SKLEPU . '" />');
    }
    unset($TablicaOdbiorcow);
    //    
    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie, $info, $NazwaProduktu, $NazwaFormularza);   
    
    //
    // wyszukiwanie poszczegolnych pozycji formularza
    $zapytanie = "SELECT * FROM form_field WHERE id_form = '" . (int)$_GET['id'] . "' AND language_id = '".$_SESSION['domyslnyJezyk']['id']."' ORDER BY form_field_sort";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    $PolaFormularza = '';
    $Walidacja = '';
    $PolaPlikow = false;
    //    
    while ($info = $sql->fetch_assoc()) {
        //
        $PolaFormularza .= '<p>';
        // sprawdza czy na koncu nie ma ?
        $Sepi = ':';
        if (substr($info['form_field_name'], -1) == '?') {
            $Sepi = '';
        }
        
        $RodzajWaluty = '';
        if ( $info['form_field_input_limit'] == 'waluta' ) {
            $RodzajWaluty .= ' ( ' . $_SESSION['domyslnaWaluta']['symbol'] . ' ) ';
        }                
        
        $PolaFormularza .= '<span>' . $info['form_field_name'] . $RodzajWaluty . $Sepi . ' ' . (($info['form_field_required'] == 1) ? '<em class="required"></em>': '') . '</span>'; 
        unset($Sepi, $RodzajWaluty);
        //
        $wartosci_pola_lista = explode("\n", $info['form_field_value']);
        $wartosci_pola_tablica = array();
        foreach($wartosci_pola_lista as $wartosc_pola) {
          $wartosc_pola = trim($wartosc_pola);
          $wartosci_pola_tablica[] = array('id' => $wartosc_pola, 'text' => $wartosc_pola);
        }        
        //
        switch($info['form_field_typ']) {
            // Pole typu INPUT
            case 0:
                
                $Kropka = '';
                if ( $info['form_field_input_limit'] == 'waluta' ) {
                    $Kropka = 'onchange="zamien_krp(this)"';
                    $PolaFormularza .= '<input type="hidden" name="fields_'.$info['id_field'].'_waluta" value="1" />';
                }    
                
                $Css = '';
                if ( $info['form_field_required'] == 1 ) {
                    $Css = 'required';
                }
                if ( $info['form_field_input_limit'] == 'kalendarz' ) {
                    $Css = ' datepicker';
                    $PolaFormularza .= '<input type="hidden" name="fields_'.$info['id_field'].'_kalendarz" value="1" />';
                }                 
                
                if ( $Css != '' ) {
                     $Css = 'class="' . $Css . '"';
                }
            
                $PolaFormularza .= '<input type="text" name="fields_'.$info['id_field'].'" ' . $Kropka . ' value="" id="fields_' . $info['id_field'] . '" ' . $Css .' size="' . $info['form_field_input_length'] . '" />';
                $WalidacjaInputKomunikat = '';
                $WalidacjaInputJs = '';
                
                unset($Kropka, $Css);
                
                // jezeli jest konieczne wypelnienie
                if ( (int)$info['form_field_required'] > 0 ) {
                    $WalidacjaInputKomunikat .= $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '. ';
                    $WalidacjaInputJs .= 'required: true,';
                }
                // jezeli jest podana min ilosc znakow
                if ( (int)$info['form_field_length'] > 0 && $info['form_field_input_limit'] != 'kalendarz' ) {
                    $WalidacjaInputKomunikat .= str_replace('{0}', (int)$info['form_field_length'], $GLOBALS['tlumacz']['BLAD_ZA_MALO_ZNAKOW_FORM']) . '. ';
                    $WalidacjaInputJs .= 'minlength: ' . (int)$info['form_field_length'] . ',';
                }
                // jezeli jest tylko liczba
                if ( $info['form_field_input_limit'] == 'liczby' || $info['form_field_input_limit'] == 'waluta' ) {
                    $WalidacjaInputKomunikat .= $GLOBALS['tlumacz']['BLAD_TYLKO_LICZBY'] . ' ';
                    $WalidacjaInputJs .= 'number: true,';
                }
                // jezeli jest tylko email
                if ( $info['form_field_input_limit'] == 'email' ) {
                    $WalidacjaInputKomunikat .= $GLOBALS['tlumacz']['BLAD_ZLY_EMAIL'] . '. ';
                    $WalidacjaInputJs .= 'email: true,';
                }                

                $PolaFormularza .= '<label class="error" for="fields_' . $info['id_field'].'" style="display:none">' . $WalidacjaInputKomunikat . '</label>';
                if ($WalidacjaInputJs != '') {
                    $Walidacja .= 'fields_' . $info['id_field'] . ': { ' . substr($WalidacjaInputJs, 0, -1) . ' },';
                }
                unset($WalidacjaInputKomunikat, $WalidacjaInputJs);

                break; 
                
            // Pole typu TEXTAREA
            case 1:
                $PolaFormularza .= '<textarea name="fields_' . $info['id_field'].'" cols="40" style="width:70%" rows="4" id="fields_' . $info['id_field'] . '" ' . (($info['form_field_required'] == 1) ? 'class="required"': '').'></textarea>';
                $WalidacjaInputKomunikat = '';
                $WalidacjaInputJs = '';   
                
                // jezeli jest konieczne wypelnienie
                if ( (int)$info['form_field_required'] > 0 ) {
                    $WalidacjaInputKomunikat .= $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '. ';
                    $WalidacjaInputJs .= 'required: true,';
                }
                // jezeli jest podana min ilosc znakow
                if ( (int)$info['form_field_length'] > 0 ) {
                    $WalidacjaInputKomunikat .= str_replace('{0}', (int)$info['form_field_length'], $GLOBALS['tlumacz']['BLAD_ZA_MALO_ZNAKOW_FORM']). '. ';
                    $WalidacjaInputJs .= 'minlength: ' . (int)$info['form_field_length'] . ',';
                }
                
                $PolaFormularza .= '<label class="error" for="fields_' . $info['id_field'].'" style="display:none">' . $WalidacjaInputKomunikat .'</label>';
                if ($WalidacjaInputJs != '') {
                    $Walidacja .= 'fields_' . $info['id_field'] . ': { ' . substr($WalidacjaInputJs, 0, -1) . ' },';
                }
                unset($WalidacjaInputKomunikat, $WalidacjaInputJs);
                
                break;   
                
            // Pole typu RADIO
            case 2:
                $cnt = 0;
                foreach($wartosci_pola_lista as $wartosc_pola) {
                    $wartosc_pola = trim($wartosc_pola);
                    $PolaFormularza .= '<input type="radio" value="'.$wartosc_pola.'" name="fields_' . $info['id_field'] . '" '.(($info['form_field_required'] == 1) ? 'class="required"': '').' /> ' . $wartosc_pola;

                    $cnt ++;
                    if ( $cnt < count($wartosci_pola_lista) ) {
                        $PolaFormularza .= '<br />';
                    }
                }
                unset($cnt);
                $PolaFormularza .= '<label class="error" for="fields_' . $info['id_field'] . '" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_JEDNA_OPCJE'] . '</label>';
                break;   
                
            // Pole typu CHECKBOX
            case 3:
                $cnt = 0;
                foreach($wartosci_pola_lista as $wartosc_pola) {
                    $wartosc_pola = trim($wartosc_pola);
                    $PolaFormularza .= '<input type="checkbox"  value="'.$wartosc_pola.'" name="fields_' . $info['id_field'] . '[]" '. (($info['form_field_required'] == 1) ? 'class="required"': '').' /> ' . $wartosc_pola;

                    $cnt ++;
                    if ( $cnt < count($wartosci_pola_lista) ) {
                        $PolaFormularza .= '<br />';
                    }
                }
                unset($cnt);
                $PolaFormularza .= '<label class="error" for="fields_' . $info['id_field'].'[]" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_OPCJE'] . '</label>';
                break; 
                
            // Pole typu SELECT
            case 4:
                $PolaFormularza .= Funkcje::RozwijaneMenu('fields_' . $info['id_field'], $wartosci_pola_tablica, '', ' style="width:50%"');
                break;   

            // Pole typu FILE
            case 5:
                $PolaFormularza .= '<input type="file" name="fields_'.$info['id_field'].'" id="fields_' . $info['id_field'] . '" />';
                $PolaFormularza .= '<input type="hidden" name="fields_'.$info['id_field'].'_plik" value="1" />';
                //
                $RozszerzeniaWalidacja = '';
                $RozszerzeniaWalidacjaText = '';
                if ( $info['form_field_file_type'] != '' ) {
                     $RozszerzeniaWalidacja = ', extension: "' . str_replace(',', '|', $info['form_field_file_type']) . '"';
                     $RozszerzeniaWalidacjaText = $GLOBALS['tlumacz']['BLAD_FORMAT_PLIKU'] . ' ' . $info['form_field_file_type'] . '. ';
                }
                $WielkoscWalidacja = '';
                $WielkoscWalidacjaText = '';
                if ( (int)$info['form_field_file_size'] > 0 ) {
                     $WielkoscWalidacja = ', filesize: ' . (($info['form_field_file_size'] * 1024) * 1024);
                     $WielkoscWalidacjaText = $GLOBALS['tlumacz']['BLAD_WIELKOSC_PLIKU'] . ' ' . $info['form_field_file_size'] . 'MB';
                }                
                //
                // komunikaty bledow
                if ( $RozszerzeniaWalidacjaText != '' || $WielkoscWalidacjaText != '' ) {
                     $PolaFormularza .= '<label class="error" for="fields_' . $info['id_field'].'" style="display:none">' . $RozszerzeniaWalidacjaText . $WielkoscWalidacjaText . '</label>';
                }
                //
                $Walidacja .= 'fields_' . $info['id_field'] . ': { required: false ' . $RozszerzeniaWalidacja . $WielkoscWalidacja . '  },';
                //
                unset($RozszerzeniaWalidacja, $WielkoscWalidacja, $RozszerzeniaWalidacjaText, $WielkoscWalidacjaText);
                //
                $PolaPlikow = true;
                break;                
        }
        //
        $PolaFormularza .= '</p>';
        //
    }
    //
    // jezeli byly pola plikow
    if ( $PolaPlikow == true ) {
         $srodek->dodaj('__TRYB_FORMULARZA', 'enctype="multipart/form-data"');
       } else {
         $srodek->dodaj('__TRYB_FORMULARZA', '');       
    }
    unset($PolaPlikow);
    
    // jezeli bylo cos z walidacji
    if ($Walidacja != '') {
        $Walidacja = '{ ignore: [], rules: { ' . substr($Walidacja,0 ,-1) . ' } }';
        $srodek->dodaj('__WALIDACJA', $Walidacja);
      } else {
        $srodek->dodaj('__WALIDACJA', '');
    }
    unset($Walidacja);
    //    
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie, $info);     
    //
    $srodek->dodaj('__POLA_FORMULARZA', $PolaFormularza);
    unset($PolaFormularza);
    //
    $srodek->dodaj('__TOKEN',Sesje::Token());
    //
  } else {
    //
    $GLOBALS['db']->close_query($sql); 
    unset($WywolanyPlik, $zapytanie, $info);
    //
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
    //    
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
unset($srodek, $WywolanyPlik);

include('koniec.php');

?>