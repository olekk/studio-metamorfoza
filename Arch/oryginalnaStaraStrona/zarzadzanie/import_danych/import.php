<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && Sesje::TokenSpr()) {


    if ($_POST['typ'] == 'kategorie' && $_POST['format_importu'] == 'xml') {
        //
        // dodawanie czy aktualizacja
        $CzyDodawanie = false;
        if ($_POST['rodzaj_import'] == 'dodawanie') {
           $CzyDodawanie = true;
        }        
        // tylko jezyk polski
        $ile_jezykow = array( array('id' => '1','kod' => 'pl') ); 
        //
        include('import_danych/import_struktura_xml_kategorie.php');  
        //
    }
    
    
    if ($_POST['typ'] == 'kategorie' && $_POST['format_importu'] == 'csv') {
        //
        // dodawanie czy aktualizacja
        $CzyDodawanie = false;
        if ($_POST['rodzaj_import'] == 'dodawanie') {
           $CzyDodawanie = true;
        }        
        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        //
        // tworzy tablice z nazwami naglowkow i danymi z pliku csv
        $file = new SplFileObject("../import/" . $_POST['plik']);
        $file->seek( 0 );
        $DefinicjeCSV = $file->current();
        //
        // stworzenie tablicy z definicjami
        $TabDefinicji = explode($_POST['separator'], $DefinicjeCSV);
        $TablicaDef = array();

        foreach ($TabDefinicji as $Definicja) {

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
        //
        // plik do przypisania danych do tablic z pliku csv
        include('import_danych/import_struktura_csv.php');      
        // kategorie i podkategorie
        include('import_danych/import_kategorie.php');              
        //
        echo json_encode( array("suma" => ((int)$_POST['limit'] + 1), "dodane" => 0, 'aktualizacja' => 0, 'nazwy' => '' ) );
    }    


    if ($_POST['typ'] != 'kategorie' && ($_POST['format_importu'] == 'xml' || $_POST['format_importu'] == 'csv')) {
        //
        include('import_danych/definicja_pol.php'); 
        
        // odpowiednie ladowanie danych
        if ($_POST['format_importu'] == 'xml') {
            //
            // tworzy tablice z nazwami naglowkow i danymi z pliku xml
            if ($_POST['plik'] == 'url' && strpos($_POST['adres_url'], '.xml') > -1) {
                // 
                $dane_produktow = simplexml_load_file($_POST['adres_url']); 
                //
              } else if ($_POST['plik'] != 'url') {
                //
                $dane_produktow = simplexml_load_file("../import/" . $_POST['plik']); 
                //
            } 
            //
          } else if ($_POST['format_importu'] == 'csv') {
            //
            // tworzy tablice z nazwami naglowkow i danymi z pliku csv
            $file = new SplFileObject("../import/" . $_POST['plik']);
            $file->seek( 0 );
            $DefinicjeCSV = $file->current();
            //
            // stworzenie tablicy z definicjami
            $TabDefinicji = explode($_POST['separator'], $DefinicjeCSV);
            $TablicaDef = array();

            foreach ($TabDefinicji as $Definicja) {

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
            //
        }
        
        // dodatkowe zapytania do wyciagniecia danych szablonu xml - uzywane np przy imporcie z zewnetrznych xml - np ceneo
        if ($_POST['format_importu'] == 'xml' && isset($_POST['struktura']) && $_POST['struktura'] != 'xml') {
            //
            // dane do szablonu
            $CzyWszystkieKategorie = true;
            $TablicaKategorii = array();
            //
            if (isset($_POST['szablon']) && (int)$_POST['szablon'] > 0) {
                //
                $zapytanie = "select * from tpl_xml where tpl_xml_id = '".(int)$_POST['szablon']."'";
                $sql = $db->open_query($zapytanie);  
                //
                $info = $sql->fetch_assoc();
                if ($info['tpl_xml_range'] == '0') { $CzyWszystkieKategorie = false; }
                //
                $podzielKategorie = explode('#',$info['tpl_xml_categories_text']);
                for ($c = 0, $cnt = count($podzielKategorie); $c < $cnt; $c++) {
                    //
                    $DodatkowyPodzial = explode(':',$podzielKategorie[$c]);
                    $TablicaKategorii[] = array( $DodatkowyPodzial[0], $DodatkowyPodzial[1] );
                    //
                }
                //
            }        
            //
            if ( isset($_POST['zakres_importu']) ) {
                //
                $nowaPost = unserialize(stripslashes($_POST['zakres_importu']));
                foreach ( $nowaPost as $pol ) {
                    $_POST[$pol] = '1';
                }
                unset($nowaPost);
                //
            }
            //
        }
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        
        $poczatekPetli = (int)$_POST['limit'];
        $koniecPetli = $poczatekPetli + 40;
        
        if ($koniecPetli > (int)$_POST['ilosc_linii']) {
            if ($_POST['struktura'] == 'csv') {
                $koniecPetli = (int)$_POST['ilosc_linii'];
              } else {
                $koniecPetli = (int)$_POST['ilosc_linii'] + 1;
            }
        }
        
        $DodanaIlosc = 0;
        $AktualizowanaIlosc = 0;
        $NazwyProduktow = '';
        
        for ($imp = $poczatekPetli; $imp < $koniecPetli; $imp++) {
        
            $_POST['limit'] = $imp;

            // wczytywanie odpowiedniej struktury plikow
            switch ($_POST['struktura']) {
                case 'csv':
                    // plik do przypisania danych do tablic z pliku csv
                    include('import_danych/import_struktura_csv.php'); 
                    break;
                case 'xml':
                    // plik do przypisania danych do tablic z pliku xml
                    include('import_danych/import_struktura_xml.php'); 
                    break;
                default:
                    // plik do przypisania danych do tablic z pliku xml w formacie zewnetrznym np ceneo
                    if ( is_file('import_danych/plugin/' . $filtr->process($_POST['struktura']) . '.php') ) {
                         include('import_danych/plugin/' . $filtr->process($_POST['struktura']) . '.php'); 
                    }
                    break;                  
            }    
            
            // jezeli wogole jest cos do importu
            if (count($TablicaDane) > 0) {

                // dodawanie czy aktualizacja
                $CzyDodawanie = false;
                if ($_POST['rodzaj_import'] == 'dodawanie') {
                   $CzyDodawanie = true;
                }
                
                // kategorie i podkategorie
                include('import_danych/import_kategorie.php');    
                
                // ------------------------------- *************** -----------------------------
                // dodawanie lub aktualizowanie produktu
                // ------------------------------- *************** -----------------------------

                // jezeli jest numer katalogowy
                if (isset($TablicaDane['Nr_katalogowy']) && trim($TablicaDane['Nr_katalogowy']) != '' && $_POST['typ'] != 'kategorie') {

                    //
                    $wBazieJestNrKat = false;
                    //

                    // sprawdza czy jest produkt w bazie
                    $zapytanieNrKatProdukt = "select products_id, products_model from products where products_model = '" . addslashes($filtr->process($TablicaDane['Nr_katalogowy'])) . "'";
                    $sqlModel = $db->open_query($zapytanieNrKatProdukt);
                    //            
                    
                    if ((int)$db->ile_rekordow($sqlModel) > 0) {
                        $wBazieJestNrKat = true;
                        //
                        $info = $sqlModel->fetch_assoc();
                        $id_aktualizowanej_pozycji = $info['products_id'];
                        $db->close_query($sqlModel);
                        unset($info);            
                    }
                    
                    // sprawdza czy jezeli jest aktualizacja to jest nr katalogoway lub jak jest dodawanie to czy nie ma nr kat
                    if (($wBazieJestNrKat == true && $CzyDodawanie == false) || ($wBazieJestNrKat == false && $CzyDodawanie == true)) {
                    
                        // licznik 
                        if ($CzyDodawanie == true) {
                            $DodanaIlosc++;
                          } else {
                            $AktualizowanaIlosc++;
                        }
                        
                        if (isset($TablicaDane['Nazwa_produktu_struktura']) && trim($TablicaDane['Nazwa_produktu_struktura']) != '') {
                            $NazwyProduktow .= '<li>' . trim($TablicaDane['Nazwa_produktu_struktura']) . '</li>';
                           } else {
                            $NazwyProduktow .= '<li><span>nr katalogowy:</span> ' . trim($TablicaDane['Nr_katalogowy']) . '</li>';
                        }                      
                    
                        // podwyzszanie procentowe cen dla xml
                        if ($_POST['format_importu'] == 'xml') {
                            //
                            if ((float)$_POST['marza'] != 0) {
                                //
                                if (isset($TablicaDane['Cena_brutto']) && (float)$TablicaDane['Cena_brutto'] > 0) {
                                    $TablicaDane['Cena_brutto'] = $TablicaDane['Cena_brutto'] + ($TablicaDane['Cena_brutto'] * ((float)$_POST['marza']/100));
                                }
                                if (isset($TablicaDane['Cena_poprzednia']) && (float)$TablicaDane['Cena_poprzednia'] > 0) {
                                    $TablicaDane['Cena_poprzednia'] = $TablicaDane['Cena_poprzednia'] + ($TablicaDane['Cena_poprzednia'] * ((float)$_POST['marza']/100));
                                }                    
                                //
                                for ($w = 2; $w <= ILOSC_CEN ; $w++) {
                                    //
                                    if (isset($TablicaDane['Cena_brutto_'.$w]) && (float)$TablicaDane['Cena_brutto_'.$w] > 0) {
                                        //
                                        $TablicaDane['Cena_brutto_'.$w] = $TablicaDane['Cena_brutto_'.$w] + ($TablicaDane['Cena_brutto_'.$w] * ((float)$_POST['marza']/100));
                                        //
                                    }
                                    if (isset($TablicaDane['Cena_poprzednia_'.$w]) && (float)$TablicaDane['Cena_poprzednia_'.$w] > 0) {
                                        //
                                        $TablicaDane['Cena_poprzednia_'.$w] = $TablicaDane['Cena_poprzednia_'.$w] + ($TablicaDane['Cena_poprzednia_'.$w] * ((float)$_POST['marza']/100));
                                        //
                                    }                                    
                                    // 
                                }        
                            }
                            //
                        }        
                    
                        // dodawanie do tablicy Products
                        $pola = array();
                        for ($pol = 0, $cn = count($TablicaProducts); $pol < $cn; $pol++) {
                        
                            if (isset($TablicaDane[$TablicaProducts[$pol][1]]) && trim($TablicaDane[$TablicaProducts[$pol][1]]) != '') {
                                //
                                $poleCsv = $filtr->process($TablicaProducts[$pol][1]);
                                //
                                $byl_zapis = false;
                                //
                                // jezeli pole to cena sprawdza czy jest taka ilosc cen w bazie
                                if (strpos($poleCsv,'Cena_brutto_') > -1) {
                                    $jakiNrCeny = explode('_',$poleCsv);
                                    if ((int)$jakiNrCeny[2] <= ILOSC_CEN) {
                                        //
                                        // jezeli jest aktualizacja nie mozna zmienic nr katalogowego
                                        if ($CzyDodawanie == false && $TablicaProducts[$pol][0] == 'products_model') {
                                            echo '';
                                          } else {
                                            $pola[] = array($TablicaProducts[$pol][0],$filtr->process($TablicaDane[$TablicaProducts[$pol][1]]));
                                            $byl_zapis = true;
                                        }
                                        //
                                    }
                                }

                                // jezeli sa to pola tak/nie to zamiast tak lub nie trzeba wstawic 1 lub 0
                                if ($poleCsv == 'Nowosc' || $poleCsv == 'Nasz_hit' || $poleCsv == 'Polecany' || $poleCsv == 'Promocja' || $poleCsv == 'Do_porownywarek' || $poleCsv == 'Negocjacja' || $poleCsv == 'Status' || $poleCsv == 'Gabaryt' || $poleCsv == 'Darmowa_dostawa') {
                                    //
                                    if (strtolower($TablicaDane[$poleCsv]) == 'tak') {
                                        //
                                        $pola[] = array($TablicaProducts[$pol][0],'1');
                                        $byl_zapis = true;
                                        //
                                      } else {
                                        //
                                        $pola[] = array($TablicaProducts[$pol][0],'0');
                                        $byl_zapis = true;
                                        //                  
                                    }
                                }       
                                
                                // jezeli nie bylo zapisu i nie jest to cena hurtowa to robi normalny zapis
                                if ($byl_zapis == false && strpos($poleCsv,'Cena_brutto_') === false) {
                                    //
                                    $pola[] = array($TablicaProducts[$pol][0],$filtr->process($TablicaDane[$TablicaProducts[$pol][1]]));
                                    //
                                }
                                //
                                unset($poleCsv);
                                //

                            }
                            
                        }
                        
                        // aktualizacja vat i netto oraz cen hurtowych
                        include('import_danych/import_ceny.php');        

                        // dostepnosc produktow
                        include('import_danych/import_dostepnosc.php');             
                        
                        // jednostka miary
                        include('import_danych/import_jm.php'); 
                        
                        // stan produktu
                        include('import_danych/import_stan_produktu.php');                         
                        
                        // gwarancja
                        include('import_danych/import_gwarancja.php');                          
                        
                        // producent
                        include('import_danych/import_producent.php');         
                  
                        // waluta
                        include('import_danych/import_waluta.php'); 
                        
                        
                        if ($CzyDodawanie == true) {
                            //
                            // data dodania produktu
                            $pola[] = array('products_date_added','now()');
                            $pola[] = array('customers_group_id','0');
                            //
                            // dodawanie do tablicy Products
                            $db->insert_query('products' , $pola);
                            $id_dodanej_pozycji = $db->last_id_query();
                            unset($pola);
                            //
                          } else {
                            //
                            // aktualizowanie tablicy Products
                            $db->update_query('products' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "'");
                            unset($pola);
                            //
                        }
                        

                        // dodatkowe zdjecia
                        include('import_danych/import_foto.php');      
                        
                        // dodatkowe zakladki
                        include('import_danych/import_zakladki.php');           

                        // linki
                        include('import_danych/import_linki.php'); 
                        
                        // pliki
                        include('import_danych/import_pliki.php');   

                        // youtube
                        include('import_danych/import_youtube.php');    

                        // filmy flv
                        include('import_danych/import_filmy.php');                          
                        
                        // pliki mp3
                        include('import_danych/import_mp3.php');    

                        // pliki elektroniczne
                        include('import_danych/import_pliki_elektroniczne.php');                           
                        
                        // dodatkowe pola
                        include('import_danych/import_dodatkowe_pola.php');  
                        
                        // dodawanie do tablicy Products description
                        $pola = array();
                        if ($CzyDodawanie == true) {
                            $pola[] = array('products_id',$id_dodanej_pozycji);
                            $pola[] = array('language_id',$_SESSION['domyslny_jezyk']['id']); 
                        }
                        //
                        $ByloJakiesPole = false;
                        //
                        for ($pol = 0, $cn1 = count($TablicaProductsDescription); $pol < $cn1; $pol++) {
                        
                            if (isset($TablicaDane[$TablicaProductsDescription[$pol][1]]) && trim($TablicaDane[$TablicaProductsDescription[$pol][1]]) != '') {
                                //
                                $pola[] = array($TablicaProductsDescription[$pol][0],$filtr->process($TablicaDane[$TablicaProductsDescription[$pol][1]]));
                                //
                                $ByloJakiesPole = true;
                            }
                        
                        }
                        if ($CzyDodawanie == true) {
                            $db->insert_query('products_description', $pola); 
                          } else if ($ByloJakiesPole == true) {
                            $db->update_query('products_description' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
                        }                  
                        unset($pola); 
                        
                        // ---------------------------------------------------------------
                        // dodawanie do innych jezykow jak sa inne jezyki
                        for ($j = 0, $cn2 = count($ile_jezykow); $j < $cn2; $j++) {
                            //
                            $kod_jezyka = $ile_jezykow[$j]['kod'];
                            //
                            // dodawanie do tablicy Products description
                            $pola = array();
                            if ($CzyDodawanie == true) {
                                $pola[] = array('products_id',$id_dodanej_pozycji);
                                $pola[] = array('language_id',$ile_jezykow[$j]['id']);
                            }
                            $ByloJakiesPole = false;
                            //
                            for ($pol = 0, $cn3 = count($TablicaProductsDescription); $pol < $cn3; $pol++) {
                            
                                if (isset($TablicaDane[$TablicaProductsDescription[$pol][1] . '_' . $kod_jezyka]) && trim($TablicaDane[$TablicaProductsDescription[$pol][1] . '_' . $kod_jezyka]) != '') {
                                    //
                                    $pola[] = array($TablicaProductsDescription[$pol][0],$filtr->process($TablicaDane[$TablicaProductsDescription[$pol][1] . '_' . $kod_jezyka]));
                                    //
                                    $ByloJakiesPole = true;
                                }
                            
                            }
                            //
                            if ($CzyDodawanie == true && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                $db->insert_query('products_description', $pola); 
                              } else if ($ByloJakiesPole == true && $ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                $db->update_query('products_description' , $pola, "products_id = '" . $id_aktualizowanej_pozycji . "' and language_id = '".$ile_jezykow[$j]['id']."'");
                            }                       
                            unset($pola);   
                            //
                            unset($kod_jezyka);
                            //
                        }        
                        
                        // jezeli importuje kategorie i produkty
                        if (($BylyKategorie == true && $CzyDodawanie == false) || $CzyDodawanie == true) {        

                            // jezeli jest aktualizacja i sa w pliku kategorie to czyscie tablice powiazan produktu z kategoriami
                            if ($CzyDodawanie == false && $BylyKategorie == true) {
                                // kasuje rekordy w tablicy
                                $db->delete_query('products_to_categories' , " products_id = '".$id_aktualizowanej_pozycji."'");                  
                            }
                            
                            // dodawanie do tablicy Products to Categories
                            $pola = array();
                            $pola[] = array('products_id',(($CzyDodawanie == true) ? $id_dodanej_pozycji : $id_aktualizowanej_pozycji));
                            $pola[] = array('categories_id',$parent);
                            $db->insert_query('products_to_categories' , $pola);
                            unset($pola); 

                        }
                        
                        include('import_danych/import_cechy.php');

                    }
              
                }

            }
        
        }
        
        echo json_encode( array("suma" => $imp, "dodane" => $DodanaIlosc, 'aktualizacja' => $AktualizowanaIlosc, 'nazwy' => $NazwyProduktow ) );
        
        unset($imp, $DodanaIlosc, $AktualizowanaIlosc, $NazwyProduktow);

    }
}
?>