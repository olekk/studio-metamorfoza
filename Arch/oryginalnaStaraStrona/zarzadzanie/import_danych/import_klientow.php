<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && Sesje::TokenSpr()) {

    $file = new SplFileObject("../import/" . $_POST['plik']);
    $file->seek( 0 );
    $DefinicjeCSV = $file->current(); 

    // stworzenie tablicy z definicjami
    $TabDefininicji = explode($_POST['separator'], $DefinicjeCSV);
    $TablicaDef = array();

    foreach ($TabDefininicji as $Definicja) {

        $Definicja = trim($Definicja);
        
        // jezeli ciag zaczyna sie od " lub ' i konczy na " lub ' to trzeba to wyczyscic
        if ((substr($Definicja, 0, 1) == "'") && (substr($Definicja, (strlen($Definicja) - 1), 1) == "'")) {
            $Definicja = substr($Definicja, 1, (strlen($Definicja) - 2));
        }
        if ((substr($Definicja, 0, 1) == '"') && (substr($Definicja, (strlen($Definicja) - 1), 1) == '"')) {
            $Definicja = substr($Definicja, 1, (strlen($Definicja) - 2));
        }        
        $TablicaDef[] = trim($Definicja);

    }        


    // ------------------------------- *************** -----------------------------
    // import danych
    // ------------------------------- *************** -----------------------------
    
    $poczatekPetli = (int)$_POST['limit'];
    $koniecPetli = $poczatekPetli + 10;
    
    if ($koniecPetli > (int)$_POST['ilosc_linii']) {
        $koniecPetli = (int)$_POST['ilosc_linii'];
    }    
    
    for ($imp = $poczatekPetli; $imp < $koniecPetli; $imp++) {
    
        $_POST['limit'] = $imp;
        
        $linia = (int)$_POST['limit'];

        // przejescie do wybranej linii
        $file->seek( $linia );
        $DaneCsv = $file->current(); 

        // tworzenie tablicy poszczegolnych pol
        $TabDaneCsv = explode($_POST['separator'], $DaneCsv);
        $TablicaDane = array();

        // przypisanie danych do tablicy
        // tablica bedzie miala postac np
        // $TablicaDane[Nr_katalogowy] = jakas wartosc
        //

        if (count($TabDaneCsv) > 0) {
            //
            for ($q = 0, $c = count($TablicaDef); $q < $c; $q++) {
                
                if (isset($TabDaneCsv[$q])) {
                    //
                    $TabDaneCsv[$q] = trim($TabDaneCsv[$q]);
                    //
                    // jezeli ciag zaczyna sie od " lub ' i konczy na " lub ' to trzeba to wyczyscic
                    if ((substr($TabDaneCsv[$q], 0, 1) == "'") && (substr($TabDaneCsv[$q], (strlen($TabDaneCsv[$q]) - 1), 1) == "'")) {
                        $TabDaneCsv[$q] = substr($TabDaneCsv[$q], 1, (strlen($TabDaneCsv[$q]) - 2));
                    }
                    if ((substr($TabDaneCsv[$q], 0, 1) == '"') && (substr($TabDaneCsv[$q], (strlen($TabDaneCsv[$q]) - 1), 1) == '"')) {
                        $TabDaneCsv[$q] = substr($TabDaneCsv[$q], 1, (strlen($TabDaneCsv[$q]) - 2));
                    }               
                    //
                    $TablicaDane[$TablicaDef[$q]] = trim($TabDaneCsv[$q]);
                }
                
            }
            //
        }       


        // jezeli jest numer katalogowy
        if (isset($TablicaDane['Adres_email']) && trim($TablicaDane['Adres_email']) != '') {
        
            // sprawdza czy nr kat jest w bazie
            $zapytanieCzyJestEmail = "select distinct customers_email_address from customers where customers_email_address = '" . $filtr->process($TablicaDane['Adres_email']) . "'";
            $sqlAdresEmail = $db->open_query($zapytanieCzyJestEmail);
            //        
            
            if ((int)$db->ile_rekordow($sqlAdresEmail) == 0) {
            
                $pola = array();
                
                // Nick
                if (isset($TablicaDane['Nick']) && trim($TablicaDane['Nick']) != '') {
                    //
                    // musi sprawdzic czy nie ma juz takiego nicku w bazie
                    $zapytanieCzyJestNick = "select distinct customers_nick from customers where customers_nick = '" . addslashes($filtr->process($TablicaDane['Nick'])) . "'";
                    $sqlNick = $db->open_query($zapytanieCzyJestNick);    
                    //
                    if ((int)$db->ile_rekordow($sqlNick) == 0) {
                        $pola[] = array('customers_nick',$TablicaDane['Nick']);
                    }
                    $db->close_query($sqlNick);
                    unset($zapytanieCzyJestNick, $infn);    
                    //
                }
                
                // Id klienta w programie magazynowym
                if (isset($TablicaDane['IdMagazyn']) && trim($TablicaDane['IdMagazyn']) != '') { $pola[] = array('customers_id_private',$TablicaDane['IdMagazyn']); }                                
                
                // Imie
                if (isset($TablicaDane['Imie']) && trim($TablicaDane['Imie']) != '') { $pola[] = array('customers_firstname',$TablicaDane['Imie']); }                

                // Nazwisko
                if (isset($TablicaDane['Nazwisko']) && trim($TablicaDane['Nazwisko']) != '') { $pola[] = array('customers_lastname',$TablicaDane['Nazwisko']); }                                

                // Adres_email
                if (isset($TablicaDane['Adres_email']) && trim($TablicaDane['Adres_email']) != '') { $pola[] = array('customers_email_address',$TablicaDane['Adres_email']); }                
                
                // Telefon
                if (isset($TablicaDane['Telefon']) && trim($TablicaDane['Telefon']) != '') { $pola[] = array('customers_telephone',$TablicaDane['Telefon']); } 

                // Haslo
                if (isset($TablicaDane['Haslo']) && trim($TablicaDane['Haslo']) != '') { $pola[] = array('customers_password',$TablicaDane['Haslo']); }

                // Newsletter
                $NewsletterKlienta = '0';
                if (isset($TablicaDane['Newsletter']) && trim($TablicaDane['Newsletter']) != '') { 
                    //
                    if (strtolower($TablicaDane['Newsletter']) == 'tak') {
                        $NewsletterKlienta = '1';
                        $pola[] = array('customers_newsletter','1'); 
                       } else {
                        $pola[] = array('customers_newsletter','0'); 
                    }
                    //
                }

                // Znizka
                if (isset($TablicaDane['Znizka']) && trim($TablicaDane['Znizka']) != '') { $pola[] = array('customers_discount',$TablicaDane['Znizka']); } 

                // Grupa klientow
                if (isset($TablicaDane['Grupa_klientow']) && trim($TablicaDane['Grupa_klientow']) != '') {
                    //
                    // musi sprawdzic czy nie ma juz takiej grupy w bazie
                    $zapytanieNazwaGrupy = "select distinct customers_groups_id, customers_groups_name from customers_groups where customers_groups_name = '" . addslashes($filtr->process($TablicaDane['Grupa_klientow'])) . "'";
                    $sqlNazwaGrupy = $db->open_query($zapytanieNazwaGrupy);    
                    //
                    if ((int)$db->ile_rekordow($sqlNazwaGrupy) > 0) {
                        //
                        $infn = $sqlNazwaGrupy->fetch_assoc();
                        $pola[] = array('customers_groups_id',$infn['customers_groups_id']);
                        //
                      } else {
                        //
                        $pola[] = array('customers_groups_id','1');
                        //
                    }
                    $db->close_query($sqlNazwaGrupy);
                    unset($zapytanieNazwaGrupy, $infn);                    
                    //
                }                
                
                // Status
                $StatusKlienta = '0';
                if (isset($TablicaDane['Status']) && trim($TablicaDane['Status']) != '') { 
                    //
                    if (strtolower($TablicaDane['Status']) == 'aktywny') {
                        $StatusKlienta = '1';
                        $pola[] = array('customers_status','1'); 
                       } else {
                        $pola[] = array('customers_status','0'); 
                    }
                    //
                }
                
                $pola[] = array('customers_dod_info','');
                $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']);

                $db->insert_query('customers' , $pola);
                $id_dodanej_pozycji = $db->last_id_query();
                unset($pola);                
                
                                  
                // tablica z adresem
                $pola = array();
                
                $pola[] = array('customers_id',$id_dodanej_pozycji);
                
                // Firma
                if (isset($TablicaDane['Firma']) && trim($TablicaDane['Firma']) != '') { $pola[] = array('entry_company',$TablicaDane['Firma']); }  

                // Nip
                if (isset($TablicaDane['Nip']) && trim($TablicaDane['Nip']) != '') { $pola[] = array('entry_nip',$TablicaDane['Nip']); }

                // Pesel
                if (isset($TablicaDane['Pesel']) && trim($TablicaDane['Pesel']) != '') { $pola[] = array('entry_pesel',$TablicaDane['Pesel']); }
                
                // Imie
                if (isset($TablicaDane['Imie']) && trim($TablicaDane['Imie']) != '') { $pola[] = array('entry_firstname',$TablicaDane['Imie']); }                

                // Nazwisko
                if (isset($TablicaDane['Nazwisko']) && trim($TablicaDane['Nazwisko']) != '') { $pola[] = array('entry_lastname',$TablicaDane['Nazwisko']); }                   

                // Ulica
                if (isset($TablicaDane['Ulica']) && trim($TablicaDane['Ulica']) != '') { $pola[] = array('entry_street_address',$TablicaDane['Ulica']); }

                // Kod_pocztowy
                if (isset($TablicaDane['Kod_pocztowy']) && trim($TablicaDane['Kod_pocztowy']) != '') { $pola[] = array('entry_postcode',$TablicaDane['Kod_pocztowy']); }

                // Miasto
                if (isset($TablicaDane['Miasto']) && trim($TablicaDane['Miasto']) != '') { $pola[] = array('entry_city',$TablicaDane['Miasto']); }

                // Kraj
                if (isset($TablicaDane['Kraj']) && trim($TablicaDane['Kraj']) != '') {
                    //
                    // szuka id kraju
                    $zapytanieKraj = "select distinct countries_id, countries_name from countries_description where countries_name = '" . addslashes($filtr->process($TablicaDane['Kraj'])) . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                    $sqlKraj = $db->open_query($zapytanieKraj);    
                    //
                    if ((int)$db->ile_rekordow($sqlKraj) > 0) {
                        //
                        $infn = $sqlKraj->fetch_assoc();
                        $pola[] = array('entry_country_id',$infn['countries_id']);
                        //
                      } else {
                        //
                        $zapytanieDomyslnyKraj = "select distinct c.countries_id from countries c, countries_description cd where c.countries_id = cdcountries_id and c.countries_default = '1'";
                        $sqlDomyslnyKraj = $db->open_query($zapytanieDomyslnyKraj);    
                        $infk = $sqlDomyslnyKraj->fetch_assoc();                        
                        //
                        $pola[] = array('entry_country_id',$infk['ountries_id']);
                        //
                        $db->close_query($sqlKraj);
                        unset($zapytanieDomyslnyKraj, $infk);
                        //
                    }
                    $db->close_query($sqlKraj);
                    unset($zapytanieKraj, $infn);                    
                    //
                }

                $db->insert_query('address_book' , $pola);
                $id_dodanej_pozycji_adres = $db->last_id_query();
                unset($pola);           

                
                // przypisanie id adresu do customers
                
                $pola = array( array('customers_default_address_id',$id_dodanej_pozycji_adres) );

                $db->update_query('customers' , $pola, " customers_id = '".(int)$id_dodanej_pozycji."'");	
                unset($pola);

                // tablica customers_info

                $pola = array(
                        array('customers_info_id',$id_dodanej_pozycji),
                        array('customers_info_number_of_logons','0'),
                        array('customers_info_date_account_created','now()'),
                        array('customers_info_date_account_last_modified','now()')
                );
                $db->insert_query('customers_info' , $pola);
                unset($pola);  

                // tablica subscribers              

                $pola = array(
                        array('customers_id',$id_dodanej_pozycji),
                        array('subscribers_email_address',$TablicaDane['Adres_email']),
                        array('customers_newsletter',$NewsletterKlienta)
                );

                $sql = $db->insert_query('subscribers' , $pola);
                unset($pola);                
                
            }
            
        }
    
    }
    
    echo $imp;
}
?>