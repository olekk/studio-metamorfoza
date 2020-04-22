<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && Sesje::TokenSpr()) {

    if ($_POST['format_importu'] == 'csv') {
        //
        $TablicaProducts = array();
        $TablicaProducts[] = array('products_stock_model','Nr_katalogowy_cechy');
        $TablicaProducts[] = array('products_stock_quantity','Ilosc_produktow');
        $TablicaProducts[] = array('products_stock_image','Zdjecie');

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
        //
      } else if ($_POST['format_importu'] == 'xml') {
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
    }
    
    $ile_jezykow = Funkcje::TablicaJezykow();


    // ------------------------------- *************** -----------------------------
    // aktualizowanie danych
    // ------------------------------- *************** -----------------------------
    
    $poczatekPetli = (int)$_POST['limit'];
    $koniecPetli = $poczatekPetli + 50;
    
    if ($koniecPetli > (int)$_POST['ilosc_linii']) {
        if ($_POST['struktura'] == 'csv') {
            $koniecPetli = (int)$_POST['ilosc_linii'];
          } else {
            $koniecPetli = (int)$_POST['ilosc_linii'] + 1;
        }
    }    
    
    $AktualizowanaIlosc = 0;
    $NrKatalogoweCech = '';    
    
    for ($imp = $poczatekPetli; $imp < $koniecPetli; $imp++) {
    
        $_POST['limit'] = $imp;
        
        if ($_POST['format_importu'] == 'csv') {       
            //
            // plik do przypisania danych do tablic z pliku csv
            include('import_danych/import_struktura_csv.php');  
            //
          } else if ($_POST['format_importu'] == 'xml') {
            //
            // plik do przypisania danych do tablic z pliku xml
            include('import_danych/import_struktura_xml.php');  
            //
        }        

        // jezeli jest numer katalogowy
        if (isset($TablicaDane['Nr_katalogowy_cechy']) && trim($TablicaDane['Nr_katalogowy_cechy']) != '') {
        
            // sprawdza czy nr kat jest w bazie
            $zapytanieNrKatProdukt = "select distinct * from products_stock where products_stock_model = '" . addslashes($filtr->process($TablicaDane['Nr_katalogowy_cechy'])) . "'";
            $sqlModel = $db->open_query($zapytanieNrKatProdukt);
            //            
            
            if ((int)$db->ile_rekordow($sqlModel) > 0) {
            
                // licznik 
                $AktualizowanaIlosc++;
                
                if (isset($TablicaDane['Nr_katalogowy_cechy']) && trim($TablicaDane['Nr_katalogowy_cechy']) != '') {
                    $NrKatalogoweCech .= '<li><span>nr katalogowy:</span> ' . trim($TablicaDane['Nr_katalogowy_cechy']) . '</li>';
                }              
            
                $info = $sqlModel->fetch_assoc();

                $pola = array();
                
                if (isset($TablicaDane['Ilosc_produktow']) && trim($TablicaDane['Ilosc_produktow']) != '' && CECHY_MAGAZYN == 'tak') {
                    //
                    $pola[] = array('products_stock_quantity',$TablicaDane['Ilosc_produktow']);
                    //
                }
                
                // stawka podatku vat
                $podatekVat = 0;
                //
                $zapytaniePodatek = "select t.tax_rate from tax_rates t, products p where p.products_tax_class_id = t.tax_rates_id and p.products_id = '" . $info['products_id'] . "'";
                $sqlp = $db->open_query($zapytaniePodatek); 
                if ((int)$db->ile_rekordow($sqlp) > 0) {
                    //
                    $infp = $sqlp->fetch_assoc();
                    $podatekVat = $infp['tax_rate'];
                    //   
                    $db->close_query($sqlp);
                }   
                //                
                
                // ceny brutto kombinacji cech
                if (isset($TablicaDane['Cena_brutto_cechy']) && (float)($TablicaDane['Cena_brutto_cechy'] > 0)) {
                    //
                    $pola[] = array('products_stock_price_tax',(float)$TablicaDane['Cena_brutto_cechy']);
                    //                
                    $netto = round( (float)$TablicaDane['Cena_brutto_cechy'] / (1 + ($podatekVat/100)), 2);
                    $podatek = (float)$TablicaDane['Cena_brutto_cechy'] - $netto;
                    //
                    $pola[] = array('products_stock_price',$netto);
                    $pola[] = array('products_stock_tax',$podatek);
                    //
                    unset($netto, $podatek);
                    //
                }
                
                for ($w = 2; $w <= ILOSC_CEN ; $w++) {
                    
                    if (isset($TablicaDane['Cena_brutto_cechy_' . $w]) && (float)($TablicaDane['Cena_brutto_cechy_' . $w] > 0)) {
                        //
                        $pola[] = array('products_stock_price_tax_' . $w,(float)$TablicaDane['Cena_brutto_cechy_' . $w]);
                        //                
                        $netto = round( (float)$TablicaDane['Cena_brutto_cechy_' . $w] / (1 + ($podatekVat/100)), 2);
                        $podatek = (float)$TablicaDane['Cena_brutto_cechy_' . $w] - $netto;
                        //
                        $pola[] = array('products_stock_price_' . $w,$netto);
                        $pola[] = array('products_stock_tax_' . $w,$podatek);
                        //
                        unset($netto, $podatek);
                        //                        
                    }                
                
                }
                
                unset($podatekVat, $infp);
                
                if (isset($TablicaDane['Dostepnosc']) && trim($TablicaDane['Dostepnosc']) != '') {
                    //
                    if ($filtr->process($filtr->process($TablicaDane['Dostepnosc'])) == 'AUTOMATYCZNY') {
                        //
                        $pola[] = array('products_stock_availability_id','99999');       
                        //
                      } else {
                        //
                        // sprawdza czy dostepnosc jest juz w bazie
                        $zapytanieDostepnosc = "select p.products_availability_id, p.mode, pd.products_availability_id, pd.products_availability_name from products_availability p, products_availability_description pd where p.products_availability_id = pd.products_availability_id and p.mode = '0' and products_availability_name = '" . addslashes($filtr->process($TablicaDane['Dostepnosc'])) . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                        $sqlc = $db->open_query($zapytanieDostepnosc);
                        //    
                        if ((int)$db->ile_rekordow($sqlc) > 0) {
                            //
                            $infe = $sqlc->fetch_assoc();
                            $pola[] = array('products_stock_availability_id',$infe['products_availability_id']);
                            //   
                            $db->close_query($sqlc);
                            unset($infe);
                         } else {
                            // jezeli nie ma dostepnosci to doda ja do bazy
                            $pole = array(array('quantity','0')); 
                            $pole[] = array('mode','0');                        
                            $db->insert_query('products_availability' , $pole); 
                            $id_dodanej_dostepnosci = $db->last_id_query();
                            unset($pole);
                            //
                            $pole = array(
                                    array('products_availability_id',$id_dodanej_dostepnosci),
                                    array('language_id',$_SESSION['domyslny_jezyk']['id']),
                                    array('products_availability_name',$filtr->process($TablicaDane['Dostepnosc'])));           
                            $db->insert_query('products_availability_description' , $pole);  
                            unset($pole);
                            
                            // ---------------------------------------------------------------
                            // dodawanie do innych jezykow jak sa inne jezyki
                            for ($j = 0, $c = count($ile_jezykow); $j < $c; $j++) {
                                //
                                $kod_jezyka = $ile_jezykow[$j]['kod'];
                                //
                                $NazwaTmp = $filtr->process($TablicaDane['Dostepnosc']);
                                if (isset($TablicaDane['Dostepnosc_' . $kod_jezyka]) && trim($TablicaDane['Dostepnosc_' . $kod_jezyka]) != '') {
                                    $NazwaTmp = $filtr->process($TablicaDane['Dostepnosc_' . $kod_jezyka]);
                                }
                                //
                                $pole = array(
                                        array('products_availability_id',$id_dodanej_dostepnosci),
                                        array('language_id',$ile_jezykow[$j]['id']),
                                        array('products_availability_name',$NazwaTmp));
                                if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                                    $sql = $db->insert_query('products_availability_description' , $pole);
                                }
                                unset($pole);              
                                //
                                unset($kod_jezyka, $NazwaTmp);
                                //
                            }  
                            
                            //
                            // dodanie id dostepnosci do bazy produktu
                            $pola[] = array('products_stock_availability_id',$id_dodanej_dostepnosci);
                            // 
                            unset($id_dodanej_dostepnosci);
                        }
                        unset($zapytanieDostepnosc);
                        //
                    }
                }     

                if (isset($TablicaDane['Zdjecie']) && trim($TablicaDane['Zdjecie']) != '') {
                    //
                    $pola[] = array('products_stock_image',$TablicaDane['Zdjecie']);
                    //
                }                
                
                $db->update_query('products_stock' , $pola, "products_stock_id = '" . $info['products_stock_id'] . "'");
                
                if ( CECHY_MAGAZYN == 'tak' ) {
                    //
                    // trzeba takze zakualizowac ogolna ilosc stanu magazynowego produktu
                    $zapytanieIloscMagazynowa = "select products_stock_quantity from products_stock where products_id = '" . $info['products_id'] . "'";
                    $sql_ilosc = $db->open_query($zapytanieIloscMagazynowa);
                    //
                    $iloscMag = 0;
                    while ($infp = $sql_ilosc->fetch_assoc()) {
                        $iloscMag = $iloscMag + $infp['products_stock_quantity'];
                    }
                    //
                    $pole = array(
                            array('products_quantity',$iloscMag));
                    $db->update_query('products' , $pole, "products_id = '" . $info['products_id'] . "'");                    
                    //
                    $db->close_query($sql_ilosc);
                    unset($infp, $zapytanieIloscMagazynowa, $iloscMag);             
                    //
                }
                //
                
                $db->close_query($sqlModel);
                unset($info, $zapytanieNrKatProdukt); 
       
            }
            
        }
    
    }
    
    echo json_encode( array("suma" => $imp, "dodane" => 0, 'aktualizacja' => $AktualizowanaIlosc, 'nazwy' => $NrKatalogoweCech ) );

    unset( $imp, $AktualizowanaIlosc, $NrKatalogoweCech );
}
?>