<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] > 0) {
        
        if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
        
            // jezeli zmiana statusu zamowienia
            if ( (int)$_POST['akcja_dolna'] == 1 ) {

                $zapytanie_tresc = "SELECT t.sender_name, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_text_id = '2'";
                $sql = $db->open_query($zapytanie_tresc);

                $tresc = $sql->fetch_assoc();

                define('STATUS_ZAMOWIENIA', Sprzedaz::pokazNazweStatusuZamowienia( (int)$_POST['status'], (int)$_POST["jezyk"] ));

                if ( $tresc['email_file'] != '' ) {
                  $tablicaZalacznikow = explode(';', $tresc['email_file']);
                } else {
                  $tablicaZalacznikow = array();
                }

                foreach ($_POST['opcja'] as $pole) {
                
                    $komentarz = $_POST['komentarz'];

                    //
                    $pola = array(
                            array('orders_status',$filtr->process($_POST['status'])),
                            array('last_modified ','now()'));

                    $db->update_query('orders' , $pola, " orders_id  = '".(int)$pole."'");	
                    unset($pola);
                                        
                    // dane zamowienia 
                    $zamowienie = new Zamowienie((int)$pole);
                    
                    // podstawia dane pod zmienne w statusie zamowienia
                    
                    // nr przesylki
                    $komentarz = str_replace('{NR_PRZESYLKI}', $zamowienie->dostawy_nr_przesylki, $komentarz);                    

                    // wartosc zamowienia
                    $komentarz = str_replace('{WARTOSC_ZAMOWIENIA}', $zamowienie->info['wartosc_zamowienia'], $komentarz);  

                    // ilosc punktow
                    $komentarz = str_replace('{ILOSC_PUNKTOW}', $zamowienie->ilosc_punktow, $komentarz);  

                    // dokument sprzedazy
                    $komentarz = str_replace('{DOKUMENT_SPRZEDAZY}', $zamowienie->info['dokument_zakupu_nazwa'], $komentarz);  
                   
                    // forma platnosci
                    $komentarz = str_replace('{FORMA_PLATNOSCI}', $zamowienie->info['metoda_platnosci'], $komentarz);  
                      
                    // forma wysylki
                    $komentarz = str_replace('{FORMA_WYSYLKI}', $zamowienie->info['wysylka_modul'], $komentarz);  

                    // link plikow elektronicznych
                    $komentarz = str_replace('{LINK_PLIKOW_ELEKTRONICZNYCH}', ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link, $komentarz);  

                    if ( isset($_POST['info_mail']) ) {

                        $powiadomienie_mail = $_POST['info_mail'];

                        $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
                        $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
                        $cc              = Funkcje::parsujZmienne($tresc['dw']);

                        $adresat_email   = $zamowienie->klient['adres_email'];
                        $adresat_nazwa   = $zamowienie->klient['nazwa'];

                        $temat           = str_replace('{NUMER_ZAMOWIENIA}', $pole, $tresc['email_title']);

                        $tekst           = str_replace('{NUMER_ZAMOWIENIA}', $pole, $tresc['description']);
                        $tekst           = str_replace('{DATA_ZAMOWIENIA}', date('d-m-Y',strtotime($zamowienie->info['data_zamowienia'])), $tekst);
                        $tekst           = str_replace('{LINK}', Seo::link_SEO('zamowienia_szczegoly.php',(int)$pole,'zamowienie','',true), $tekst);
                        
                        if ( isset($_POST["dolacz_komentarz"]) ) {
                            $tekst = str_replace('{KOMENTARZ}', $filtr->process($komentarz), $tekst);
                        } else {
                            $tekst = str_replace('{KOMENTARZ}', '', $tekst);
                        }                        
                        
                        $zalaczniki      = $tablicaZalacznikow;
                        $szablon         = $tresc['template_id'];
                        $jezyk           = (int)$_POST["jezyk"];

                        $tekst = Funkcje::parsujZmienne($tekst);
                        $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', $tekst);

                        $email = new Mailing;

                        $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
                        
                        unset($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);
                    
                    } else {
                    
                        $powiadomienie_mail = '0';
                      
                    }

                    if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' && isset($_POST['info_sms']) ) {

                        if ( Klienci::CzyNumerGSM($zamowienie->klient['telefon']) ) {
                        
                            $adresat   = $zamowienie->klient['telefon'];
                            $wiadomosc = str_replace('{NUMER_ZAMOWIENIA}', $pole, $tresc['description_sms']);
                            $wiadomosc = strip_tags(Funkcje::parsujZmienne($wiadomosc));

                            SmsApi::wyslijSms($adresat, $wiadomosc);

                            $powiadomienie_sms = $_POST['info_sms'];
                            unset($adresat, $wiadomosc);

                        } else {
                        
                            $powiadomienie_sms = '0';
                          
                        }
                      
                    } else {
                    
                      $powiadomienie_sms = '0';
                      
                    }

                    //
                    $pola = array(
                            array('orders_id ',(int)$pole),
                            array('orders_status_id',$filtr->process($_POST['status'])),
                            array('date_added','now()'),
                            array('customer_notified ',$powiadomienie_mail),
                            array('customer_notified_sms',$powiadomienie_sms),
                            array('comments',$filtr->process($komentarz))
                    );

                    $db->insert_query('orders_status_history' , $pola);
                    unset($pola);
                    
                    // zatwierdzenie punktow z zakupy
                    if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
                        //
                        if ( isset($_POST['zatwierdz_punkty']) && (int)$_POST['zatwierdz_punkty'] == 1 ) {
                            //
                            if ( $zamowienie->punkty_id > 0 && $zamowienie->ilosc_punktow_dodania > 0 ) {
                                //                        
                                Klienci::dodajPunktyKlienta( $zamowienie->klient['id'], '2', (int)$pole, $zamowienie->ilosc_punktow, 1, $zamowienie->punkty_id );
                                //
                            }
                            //
                        }
                    }           

                    unset($zamowienie, $komentarz);

                }   

            }
            
            // jezeli generowanie zamowien pdf
            if ( (int)$_POST['akcja_dolna'] == 2 ) {
            
                require_once('../tcpdf/config/lang/pol.php');
                require_once('../tcpdf/tcpdf.php');            
                
                $i18n = new Translator($db, '1');
                $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI', 'KLIENCI_PANEL', 'PRODUKT', 'ZAMOWIENIE_REALIZACJA') );

                class MYPDF extends TCPDF {

                    public function Footer() {
                      global $tlumacz;
                        $this->SetY(-15);
                        $this->SetFont('helvetica', 'I', 8);
                        $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
                        $this->Cell(0, 0, $tlumacz['LISTING_STRONA'].' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
                    }
                    
                }

                $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                $pdf->SetCreator('shopGold');
                $pdf->SetAuthor('shopGold');
                $pdf->SetTitle($tlumacz['DRUKUJ_ZAMOWIENIE']);
                $pdf->SetSubject($tlumacz['DRUKUJ_ZAMOWIENIE']);
                $pdf->SetKeywords($tlumacz['DRUKUJ_ZAMOWIENIE']);

                if (file_exists(KATALOG_SKLEPU . KATALOG_ZDJEC . '/'.PDF_PLIK_NAGLOWKA)) {
                    //
                    $plik_naglowka = PDF_PLIK_NAGLOWKA;
                    $szerokosc_pliku_naglowka = PDF_PLIK_NAGLOWKA_SZEROKOSC;
                    //
                } else {
                    //
                    $plik_naglowka = '';
                    $szerokosc_pliku_naglowka = '';
                    //
                }
                $pdf->SetHeaderData($plik_naglowka, $szerokosc_pliku_naglowka, DANE_NAZWA_FIRMY_SKROCONA, ADRES_URL_SKLEPU ."\n".INFO_EMAIL_SKLEPU);

                $pdf->SetFont('dejavusans', '', 6);

                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // ---------------------------------------------------------
                
                foreach ($_POST['opcja'] as $pole) {

                    $pdf->AddPage();
                    $pdf->SetFont('dejavusans', '', 8);
                    //
                    $zamowienie = new Zamowienie((int)$pole, PDF_ZAMOWIENIE_SORTOWANIE_PRODUKTOW);
                    //
                    $text = PDFZamowienie::WydrukZmowieniaPDF();
                    
                    $pdf->writeHTML($text, true, false, false, false, '');
                    //
                    unset($text, $zamowienie);
                    
                }

                $pdf->Output('zestawienie_zamowien_'.time().'.pdf', 'D');

            }
            
            // jezeli jest laczenie zamowien
            if ( (int)$_POST['akcja_dolna'] == 3 ) {
                  
                  Funkcje::PrzekierowanieURL('zamowienia_laczenie.php?id=' . base64_encode(implode(',', $_POST['opcja'])));
                  
            }
            
            // jezeli jest pobieranie zamowien csv
            if ( (int)$_POST['akcja_dolna'] == 4 ) {
                  
                  Funkcje::PrzekierowanieURL('zamowienia_pobierz.php?id=' . base64_encode(implode(',', $_POST['opcja'])));
                  
            }            

        }
     
    }
    
    Funkcje::PrzekierowanieURL('zamowienia.php');
    
}
?>