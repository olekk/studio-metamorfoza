<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
      
$WskaznikPrzeskoku = 50;

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {


    // ----------------------- ogolna tablica dostepnosci
    $Dostepnosci = array();
    //
    $zapytanieDostepnosc = "select products_availability_name, products_availability_id, language_id from products_availability_description";
    $sqlc = $db->open_query($zapytanieDostepnosc);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Dostepnosci[$infs['products_availability_id']][$infs['language_id']] = $infs['products_availability_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieDostepnosc);
    // dostepnosci koniec
    
    // ----------------------- ogolna tablica terminow wysylek
    $TerminyWysylek = array();
    //
    $zapytanieTerminyWysylek = "select products_shipping_time_name, products_shipping_time_id, language_id from products_shipping_time_description";
    $sqlc = $db->open_query($zapytanieTerminyWysylek);  
    while ($infs = $sqlc->fetch_assoc()) {
        $TerminyWysylek[$infs['products_shipping_time_id']][$infs['language_id']] = $infs['products_shipping_time_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieTerminyWysylek);
    // terminy wysylek koniec    
    
    // ----------------------- ogolna tablica stanu produktow
    $StanProduktow = array();
    //
    $zapytanieStanProduktow = "select products_condition_name, products_condition_id, language_id from products_condition_description";
    $sqlc = $db->open_query($zapytanieStanProduktow);  
    while ($infs = $sqlc->fetch_assoc()) {
        $StanProduktow[$infs['products_condition_id']][$infs['language_id']] = $infs['products_condition_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieStanProduktow);
    // stan produktow koniec    

    // ----------------------- ogolna tablica gwarancji produktow
    $Gwarancje = array();
    //
    $zapytanieGwarancje = "select products_warranty_name, products_warranty_id, language_id from products_warranty_description";
    $sqlc = $db->open_query($zapytanieGwarancje);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Gwarancje[$infs['products_warranty_id']][$infs['language_id']] = $infs['products_warranty_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieGwarancje);
    // stan gwarancji produktow

    // ----------------------- ogolna tablica jednostek miar
    $JednostkiMiary = array();
    //
    $zapytanieJednostkiMiary = "select products_jm_name, products_jm_id, language_id from products_jm_description";
    $sqlc = $db->open_query($zapytanieJednostkiMiary);  
    while ($infs = $sqlc->fetch_assoc()) {
        $JednostkiMiary[$infs['products_jm_id']][$infs['language_id']] = $infs['products_jm_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieJednostkiMiary);
    // jednostki miary koniec

    // ----------------------- ogolna tablica podatku vat
    $Vat = array();
    //
    $zapytanieVat = "select tax_rates_id, tax_rate from tax_rates";
    $sqlc = $db->open_query($zapytanieVat);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Vat[$infs['tax_rates_id']] = $infs['tax_rate'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieVat);
    // vat koniec

    // ----------------------- ogolna tablica producentow
    $Producenci = array();
    //
    $zapytanieProducent = "select manufacturers_id, manufacturers_name from manufacturers";
    $sqlc = $db->open_query($zapytanieProducent);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Producenci[$infs['manufacturers_id']] = $infs['manufacturers_name'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieProducent);
    // producenci koniec

    // ----------------------- ogolna tablica walut
    $Walut = array();
    //
    $zapytanieWaluta = "select currencies_id, code from currencies";
    $sqlc = $db->open_query($zapytanieWaluta);  
    while ($infs = $sqlc->fetch_assoc()) {
        $Walut[$infs['currencies_id']] = $infs['code'];
    }
    $db->close_query($sqlc);
    unset($infs, $zapytanieWaluta);
    // waluty koniec
    

    // jezeli jest eksport produktu w jezyku pl lub wszystkich jezykach
    if (isset($_POST['zakres']) && ($_POST['zakres'] == 'pl' || $_POST['zakres'] == 'wszystkie' || $_POST['zakres'] == 'pl_bez_kategorii' || $_POST['zakres'] == 'wszystkie_bez_kategorii')) {
    
        // pobieranie danych konfiguracji exportu
        $zapytanie_konfig = "select code, status from export_configuration";
        $sql_konfig = $db->open_query($zapytanie_konfig);  

        $Konfiguracja = array();
        while ( $info_konfig = $sql_konfig->fetch_assoc() ) {
            //
            $Konfiguracja[ $info_konfig['code'] ] = $info_konfig['status'];
            //
        }
        
        $db->close_query($sql_konfig);
        unset($info_konfig);            

        $zapytanie = "select distinct * from products order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        // jezeli sa warunki
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'producent') {
            $zapytanie = "select distinct * from products where manufacturers_id = '" . (int)$_POST['filtr'] . "' order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'kategoria') {
            $zapytanie = "select distinct * from products p, products_to_categories pc where p.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['filtr'] . "' order by p.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }    

        // export z tablicy products
        
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $CoDoZapisania = '';
            $DoZapisaniaXML = '';
        
            // uchwyt pliku, otwarcie do dopisania
            $fp = fopen($filtr->process($_POST['plik']), "a");
            // blokada pliku do zapisu
            flock($fp, 2);
            
            $Suma = $_POST['limit'];
        
            // jezeli tylko jezyk polski to tworzy tablice tylko z id polski
            if ((isset($_POST['zakres']) && ($_POST['zakres'] == 'pl' || $_POST['zakres'] == 'pl_bez_kategorii')) || $_POST['format'] == 'xml') {
                $ile_jezykow = array( array('id' => '1','kod' => 'pl') ); 
              } else {            
                $ile_jezykow = Funkcje::TablicaJezykow();
            }
            
            // tablica z nazwy pol dodatkowych  
            $TablicaDodatkowePola = array();
            //
            // jezeli jest tylko jeden jezyk - polski
            if (count($ile_jezykow) == 1) {
                $zapytaniePolaNazwa = "select languages_id, products_extra_fields_id, products_extra_fields_name from products_extra_fields where products_extra_fields_id in (select distinct products_extra_fields_id from products_to_products_extra_fields where products_extra_fields_value != '') and (languages_id = '0' or languages_id = '".$_SESSION['domyslny_jezyk']['id']."') order by languages_id";
               } else {
                $zapytaniePolaNazwa = "select languages_id, products_extra_fields_id, products_extra_fields_name from products_extra_fields where products_extra_fields_id in (select distinct products_extra_fields_id from products_to_products_extra_fields where products_extra_fields_value != '') order by languages_id";       
            }
            $sqlw = $db->open_query($zapytaniePolaNazwa);                      
            while ($infoPoleNazwa = $sqlw->fetch_assoc()) {
                $TablicaDodatkowePola[ $infoPoleNazwa['products_extra_fields_id'] ] = array( 'jezyk_id' => $infoPoleNazwa['languages_id'],
                                                                                             'pole_id' => $infoPoleNazwa['products_extra_fields_id'],
                                                                                             'nazwa_pola' => $infoPoleNazwa['products_extra_fields_name'] );
            }
            //
            $db->close_query($sqlw);
            unset($infoPoleNazwa, $zapytaniePolaNazwa);                    
            
            // ---------------------------------------------------------------------------
            // okresla ile jest cech w sklepie zeby nie robic pustych pol - ile maksymalnie maja przypisane produkty cech
            $zapytanieCechy = "select products_id, count(options_id) as ilosc_cech from products_attributes group by products_id order by ilosc_cech desc limit 0,1";        
            $sqlc = $db->open_query($zapytanieCechy);
            $infoCechy = $sqlc->fetch_assoc();
            $ileCech = $infoCechy['ilosc_cech'] + 1; 
            //
            $db->close_query($sqlc);
            unset($infoCechy, $zapytaniePola);             
            // ---------------------------------------------------------------------------            

            while ($info = $sql->fetch_assoc()) {
            
                $DoZapisaniaXML .= '  <Produkt>' . "\r\n";
                $NaglowekCsv = '';
            
                // generowanie kategorii     
                if ( isset($Konfiguracja['Kategoria']) && $Konfiguracja['Kategoria'] == 1 ) {
                    //
                    // do jakiej kategorii nalezy produkt
                    $zapytanieKategoria = "select * from products_to_categories where products_id = '" . (int)$info['products_id'] . "' order by categories_default desc";
                    $sqlc = $db->open_query($zapytanieKategoria);  
                    $infs = $sqlc->fetch_assoc();
                    //
                    if ((int)$infs['categories_id'] > 0) {
                        $sCiezka = Kategorie::SciezkaKategoriiId((int)$infs['categories_id'], 'categories');
                        $sciezka = explode("_",$sCiezka);          
                      } else {
                        $sciezka = array();
                    }
                    //
                    $db->close_query($sqlc);
                    unset($infs, $zapytanieKategoria);
                    
                    $DoZapisaniaXMLKategorie = '';

                    for ($c = 1; $c < 11; $c++) {
                        //
                        // sprawdza czy jest id
                        if (isset($sciezka[$c - 1])) {
                            $ids = $sciezka[$c - 1];
                          } else {
                            $ids = 9999999999;
                        }
                        //
                        for ($w = 0, $cl = count($ile_jezykow); $w < $cl; $w++) {
                            //
                            $zapytanieKategoria = "select * from categories_description cd, categories c where c.categories_id = cd.categories_id and c.categories_id = '" . $ids . "' and cd.language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqlc = $db->open_query($zapytanieKategoria);  
                            $infs = $sqlc->fetch_assoc();                
                            //            
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }
                            
                            $NaglowekCsv .= 'Kategoria_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_name']) . '";';
                            
                            if (!empty($infs['categories_name'])) {
                                $DoZapisaniaXMLKategorie .= Funkcje::CzyszczenieTekstu($infs['categories_name']).'/';
                            }                    
                            
                            // dodatkowe dane do kategorii exportuje tylko jak sa wybrane wszystkie opcje
                            if ($_POST['zakres'] == 'pl' || $_POST['zakres'] == 'wszystkie') {
                                //
                                $NaglowekCsv .= 'Kategoria_'.$c.'_zdjecie;';
                                $CoDoZapisania .= '"' . $infs['categories_image'] . '";';               
                      
                                $NaglowekCsv .= 'Kategoria_'.$c.'_opis' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_description']) . '";';               
                        
                                $NaglowekCsv .= 'Kategoria_'.$c.'_meta_tytul' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_meta_title_tag']) . '";';                
                         
                                $NaglowekCsv .= 'Kategoria_'.$c.'_meta_opis' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_meta_desc_tag']) . '";';               
                      
                                $NaglowekCsv .= 'Kategoria_'.$c.'_meta_slowa' . $Przedrostek . ';';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['categories_meta_keywords_tag']) . '";';
                                //
                            }

                            $db->close_query($sqlc);
                            unset($infs, $zapytanieKategoria);                
                        }
                    }
                    
                    if ($DoZapisaniaXMLKategorie != '') {
                        //
                        $DoZapisaniaXML .= '      <Kategoria><![CDATA[';
                        $DoZapisaniaXML .= substr($DoZapisaniaXMLKategorie, 0, strlen($DoZapisaniaXMLKategorie)-1) . ']]></Kategoria>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLKategorie);
                    //
                }
                
                // nr katalogowy
                $NaglowekCsv .= 'Nr_katalogowy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_model']) . '";';
                if (!empty($info['products_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_model']).']]></Nr_katalogowy>' . "\r\n";
                }            
                
                // ilosc produktow
                if ( isset($Konfiguracja['Ilosc_produktow']) && $Konfiguracja['Ilosc_produktow'] == 1 ) {
                    //                
                    $NaglowekCsv .= 'Ilosc_produktow;';
                    $CoDoZapisania .= '"' . $info['products_quantity'] . '";';
                    $DoZapisaniaXML .= '      <Ilosc_produktow>'.$info['products_quantity'].'</Ilosc_produktow>' . "\r\n";    
                    //
                }
                
                // min ilosc zakupow
                if ( isset($Konfiguracja['Min_ilosc_zakupu']) && $Konfiguracja['Min_ilosc_zakupu'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Min_ilosc_zakupu;';
                    $CoDoZapisania .= '"' . $info['products_minorder'] . '";';
                    $DoZapisaniaXML .= '      <Min_ilosc_zakupu>'.$info['products_minorder'].'</Min_ilosc_zakupu>' . "\r\n";     
                    //
                }
                
                // max ilosc zakupow
                if ( isset($Konfiguracja['Max_ilosc_zakupu']) && $Konfiguracja['Max_ilosc_zakupu'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Max_ilosc_zakupu;';
                    $CoDoZapisania .= '"' . $info['products_maxorder'] . '";';
                    $DoZapisaniaXML .= '      <Max_ilosc_zakupu>'.$info['products_maxorder'].'</Max_ilosc_zakupu>' . "\r\n";
                    //
                }
                
                // przyrost ilosci
                if ( isset($Konfiguracja['Przyrost_ilosci']) && $Konfiguracja['Przyrost_ilosci'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Przyrost_ilosci;';
                    $CoDoZapisania .= '"' . $info['products_quantity_order'] . '";';
                    $DoZapisaniaXML .= '      <Przyrost_ilosci>'.$info['products_quantity_order'].'</Przyrost_ilosci>' . "\r\n";                 
                    //
                }
                                
                // waga
                if ( isset($Konfiguracja['Waga']) && $Konfiguracja['Waga'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Waga;';
                    $CoDoZapisania .= '"' . $info['products_weight'] . '";';
                    $DoZapisaniaXML .= '      <Waga>'.$info['products_weight'].'</Waga>' . "\r\n"; 
                    //
                }
                
                // dostepnosc produktu
                if ( isset($Konfiguracja['Data_dostepnosci']) && $Konfiguracja['Data_dostepnosci'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Data_dostepnosci;';
                    //
                    $DataDostepnosci = '';
                    //
                    if ( !empty($info['products_date_available']) && $info['products_date_available'] != '0000-00-00' ) {
                         $DataDostepnosci = date('Y-m-d',strtotime($info['products_date_available']));
                    }
                    //
                    $CoDoZapisania .= '"' . $DataDostepnosci . '";';
                    //
                    if ( $DataDostepnosci != '' ) {
                         $DoZapisaniaXML .= '      <Data_dostepnosci><![CDATA['.$DataDostepnosci.']]></Data_dostepnosci>' . "\r\n"; 
                    }
                    //
                    unset($DataDostepnosci);
                    //
                }                
                     
                // kod producenta
                if ( isset($Konfiguracja['Kod_producenta']) && $Konfiguracja['Kod_producenta'] == 1 ) {
                    //                     
                    $NaglowekCsv .= 'Kod_producenta;';
                    $CoDoZapisania .= '"' . $info['products_man_code'] . '";';
                    $DoZapisaniaXML .= '      <Kod_producenta><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_man_code']).']]></Kod_producenta>' . "\r\n";
                    //
                }
                
                // id produktu programu magazynowego
                if ( isset($Konfiguracja['Id_produktu_magazyn']) && $Konfiguracja['Id_produktu_magazyn'] == 1 ) {
                    //                
                    $NaglowekCsv .= 'Id_produktu_magazyn;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_id_private']) . '";';
                    if (!empty($info['products_id_private'])) {
                        $DoZapisaniaXML .= '      <Id_produktu_magazyn><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_id_private']).']]></Id_produktu_magazyn>' . "\r\n";
                    }                
                    //
                }
                
                // ean
                if ( isset($Konfiguracja['Kod_ean']) && $Konfiguracja['Kod_ean'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Kod_ean;';
                    $CoDoZapisania .= '"' . $info['products_ean'] . '";';
                    if (!empty($info['products_model'])) {
                        $DoZapisaniaXML .= '      <Kod_ean><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_ean']).']]></Kod_ean>' . "\r\n";
                    }            
                    //
                }
                
                // gabaryt
                if ( isset($Konfiguracja['Gabaryt']) && $Konfiguracja['Gabaryt'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Gabaryt;';
                    if ($info['products_pack_type'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Gabaryt>tak</Gabaryt>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Gabaryt>nie</Gabaryt>' . "\r\n";
                    }
                    //
                }
                                
                // podatek vat
                //
                $StawkaPodatku = '';
                if ( isset($Vat[(int)$info['products_tax_class_id']]) ) {
                    $StawkaPodatku = $Vat[(int)$info['products_tax_class_id']];
                }
                //
                if ( isset($Konfiguracja['Podatek_Vat']) && $Konfiguracja['Podatek_Vat'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Podatek_Vat;';
                    $CoDoZapisania .= '"' . $StawkaPodatku . '";'; 
                    $DoZapisaniaXML .= '      <Podatek_Vat>'.$StawkaPodatku.'</Podatek_Vat>' . "\r\n";
                    //
                }
                //
                unset($StawkaPodatku);
                
                // cena brutto
                if ( isset($Konfiguracja['Cena_brutto']) && $Konfiguracja['Cena_brutto'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Cena_brutto;';
                    $CoDoZapisania .= '"' . $info['products_price_tax'] . '";'; 
                    $DoZapisaniaXML .= '      <Cena_brutto>'.$info['products_price_tax'].'</Cena_brutto>' . "\r\n";
                    //
                }
                  
                // ceny brutto hurtowe
                if ( isset($Konfiguracja['Cena_brutto_x']) && $Konfiguracja['Cena_brutto_x'] == 1 ) {
                    //                     
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                        if ($x > 1) {
                            $NaglowekCsv .= 'Cena_brutto_'.$x.';';
                            $CoDoZapisania .= '"' . $info['products_price_tax_'.$x]. '";';
                            if ($info['products_price_tax_'.$x] > 0) {
                                $DoZapisaniaXML .= '      <Cena_brutto_'.$x.'>'.$info['products_price_tax_'.$x].'</Cena_brutto_'.$x.'>' . "\r\n";
                            }
                        }
                    }
                    //
                }
                                    
                // cena poprzednia
                if ( isset($Konfiguracja['Cena_poprzednia']) && $Konfiguracja['Cena_poprzednia'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Cena_poprzednia;';
                    $CoDoZapisania .= '"' . $info['products_old_price'] . '";'; 
                    if ($info['products_old_price'] > 0) {
                        $DoZapisaniaXML .= '      <Cena_poprzednia>'.$info['products_old_price'].'</Cena_poprzednia>' . "\r\n";
                    }
                    //
                }
                                  
                // ceny poprzednie hurtowe
                if ( isset($Konfiguracja['Cena_poprzednia_x']) && $Konfiguracja['Cena_poprzednia_x'] == 1 ) {
                    //                      
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                        if ($x > 1) {
                            $NaglowekCsv .= 'Cena_poprzednia_'.$x.';';
                            $CoDoZapisania .= '"' . $info['products_old_price_'.$x]. '";';
                            if ($info['products_old_price_'.$x] > 0) {
                                $DoZapisaniaXML .= '      <Cena_poprzednia_'.$x.'>'.$info['products_old_price_'.$x].'</Cena_poprzednia_'.$x.'>' . "\r\n";
                            }
                        }
                    }     
                    //
                }
                  
                // cena katalogowa
                if ( isset($Konfiguracja['Cena_katalogowa']) && $Konfiguracja['Cena_katalogowa'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Cena_katalogowa;';
                    $CoDoZapisania .= '"' . $info['products_retail_price'] . '";'; 
                    if ($info['products_retail_price'] > 0) {
                        $DoZapisaniaXML .= '      <Cena_katalogowa>'.$info['products_retail_price'].'</Cena_katalogowa>' . "\r\n";
                    }
                    //
                }
                                  
                // ceny katalogowe hurtowe
                if ( isset($Konfiguracja['Cena_katalogowa_x']) && $Konfiguracja['Cena_katalogowa_x'] == 1 ) {
                    //                      
                    for ($x = 1; $x <= ILOSC_CEN; $x++) {
                        if ($x > 1) {
                            $NaglowekCsv .= 'Cena_katalogowa_'.$x.';';
                            $CoDoZapisania .= '"' . $info['products_retail_price_'.$x]. '";';
                            if ($info['products_retail_price_'.$x] > 0) {
                                $DoZapisaniaXML .= '      <Cena_katalogowa_'.$x.'>'.$info['products_retail_price_'.$x].'</Cena_katalogowa_'.$x.'>' . "\r\n";
                            }
                        }
                    }                   
                    //
                }
                  
                // nowosc
                if ( isset($Konfiguracja['Nowosc']) && $Konfiguracja['Nowosc'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Nowosc;';
                    if ($info['new_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Nowosc>tak</Nowosc>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Nowosc>nie</Nowosc>' . "\r\n";
                    }   
                    //
                }
                  
                // nasz hit
                if ( isset($Konfiguracja['Nasz_hit']) && $Konfiguracja['Nasz_hit'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Nasz_hit;';
                    if ($info['star_status'] == 1) {                
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Nasz_hit>tak</Nasz_hit>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Nasz_hit>nie</Nasz_hit>' . "\r\n";
                    }   
                    //
                }
                  
                // polecany
                if ( isset($Konfiguracja['Polecany']) && $Konfiguracja['Polecany'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Polecany;';
                    if ($info['featured_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Polecany>tak</Polecany>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Polecany>nie</Polecany>' . "\r\n";
                    } 
                    //
                }
                  
                // promocja
                if ( isset($Konfiguracja['Promocja']) && $Konfiguracja['Promocja'] == 1 ) {
                    //                    
                    $NaglowekCsv .= 'Promocja;';
                    if ($info['specials_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Promocja>tak</Promocja>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Promocja>nie</Promocja>' . "\r\n";
                    } 
                    //
                }
                  
                // do porownywarek
                if ( isset($Konfiguracja['Do_porownywarek']) && $Konfiguracja['Do_porownywarek'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Do_porownywarek;';
                    if ($info['export_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Do_porownywarek>tak</Do_porownywarek>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Do_porownywarek>nie</Do_porownywarek>' . "\r\n";
                    }  
                    //
                }
                 
                // negocjacja
                if ( isset($Konfiguracja['Negocjacja']) && $Konfiguracja['Negocjacja'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Negocjacja;';
                    if ($info['products_make_an_offer'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Negocjacja>tak</Negocjacja>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Negocjacja>nie</Negocjacja>' . "\r\n";
                    }
                    //
                }
                                 
                // darmowa dostawa
                if ( isset($Konfiguracja['Darmowa_dostawa']) && $Konfiguracja['Darmowa_dostawa'] == 1 ) {
                    //                 
                    $NaglowekCsv .= 'Darmowa_dostawa;';
                    if ($info['free_shipping_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Darmowa_dostawa>tak</Darmowa_dostawa>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Darmowa_dostawa>nie</Darmowa_dostawa>' . "\r\n";
                    }                
                    //
                }
                  
                // zdjecie glowne
                if ( isset($Konfiguracja['Zdjecie_glowne']) && $Konfiguracja['Zdjecie_glowne'] == 1 ) {
                    //                     
                    $NaglowekCsv .= 'Zdjecie_glowne;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_image']) . '";'; 
                    if (!empty($info['products_image'])) {
                        $DoZapisaniaXML .= '      <Zdjecie_glowne>'.Funkcje::CzyszczenieTekstu($info['products_image']).'</Zdjecie_glowne>' . "\r\n";
                    }            
                    //
                }
                
                // zdjecie glowne - alt
                if ( isset($Konfiguracja['Zdjecia_opis']) && $Konfiguracja['Zdjecia_opis'] == 1 ) {
                    //                     
                    $NaglowekCsv .= 'Zdjecie_glowne_opis;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_image_description']) . '";'; 
                    if (!empty($info['products_image_description'])) {
                        $DoZapisaniaXML .= '      <Zdjecie_glowne_opis><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_image_description']).']]></Zdjecie_glowne_opis>' . "\r\n";
                    }            
                    //
                }                
                  
                // status
                if ( isset($Konfiguracja['Status']) && $Konfiguracja['Status'] == 1 ) {
                    //                  
                    $NaglowekCsv .= 'Status;';
                    if ($info['products_status'] == 1) {
                        $CoDoZapisania .= '"tak";';
                        $DoZapisaniaXML .= '      <Status>tak</Status>' . "\r\n";
                      } else {
                        $CoDoZapisania .= '"nie";';
                        $DoZapisaniaXML .= '      <Status>nie</Status>' . "\r\n";
                    }               
                    //
                }
                
                // notatki produktu
                if ( isset($Konfiguracja['Notatki_produktu']) && $Konfiguracja['Notatki_produktu'] == 1 ) {
                    //                   
                    $NaglowekCsv .= 'Notatki_produktu;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_adminnotes']) . '";';
                    //
                    if ( trim($info['products_adminnotes']) != '' ) {
                         $DoZapisaniaXML .= '      <Notatki_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_adminnotes']).']]></Notatki_produktu>' . "\r\n"; 
                    }
                    //
                    unset($DataDostepnosci);
                    //
                }                  
                
                // export z tablicy products description
                
                for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                    //
                    $zapytanieOpisy = "select distinct * from products_description where products_id = '".$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqlo = $db->open_query($zapytanieOpisy); 

                    $infoOpisy = $sqlo->fetch_assoc();
                    
                    $Przedrostek = '';
                    if ($ile_jezykow[$w]['kod'] != 'pl') {
                        $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                    }
                    
                    // nazwa produktu
                    if ( isset($Konfiguracja['Nazwa_produktu']) && $Konfiguracja['Nazwa_produktu'] == 1 ) {
                        //                        
                        $NaglowekCsv .= 'Nazwa_produktu' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infoOpisy['products_name']) . '";';
                        if (!empty($infoOpisy['products_name'])) {
                            $DoZapisaniaXML .= '      <Nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_name']).']]></Nazwa_produktu>' . "\r\n";
                        }   
                        //
                    }

                    // nazwa produktu
                    if ( isset($Konfiguracja['Dodatkowa_nazwa_produktu']) && $Konfiguracja['Dodatkowa_nazwa_produktu'] == 1 ) {
                        //                    
                        $NaglowekCsv .= 'Dodatkowa_nazwa_produktu' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infoOpisy['products_name_info']) . '";';
                        if (!empty($infoOpisy['products_name_info'])) {
                            $DoZapisaniaXML .= '      <Dodatkowa_nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_name_info']).']]></Dodatkowa_nazwa_produktu>' . "\r\n";
                        }                        
                        //
                    }

                    // opis
                    if ( isset($Konfiguracja['Opis']) && $Konfiguracja['Opis'] == 1 ) {
                        //                     
                        $NaglowekCsv .= 'Opis' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infoOpisy['products_description']) . '";';
                        if (!empty($infoOpisy['products_description'])) {
                            $DoZapisaniaXML .= '      <Opis><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_description']).']]></Opis>' . "\r\n";
                        }                
                        //
                    }

                    // opis krotki
                    if ( isset($Konfiguracja['Opis_krotki']) && $Konfiguracja['Opis_krotki'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Opis_krotki' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infoOpisy['products_short_description']) . '";';
                        if (!empty($infoOpisy['products_short_description'])) {
                            $DoZapisaniaXML .= '      <Opis_krotki><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_short_description']).']]></Opis_krotki>' . "\r\n";
                        }                 
                        //
                    }

                    // meta tytul
                    if ( isset($Konfiguracja['Meta_tytul']) && $Konfiguracja['Meta_tytul'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Meta_tytul' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_title_tag']) . '";';
                        if (!empty($infoOpisy['products_meta_title_tag'])) {
                            $DoZapisaniaXML .= '      <Meta_tytul><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_title_tag']).']]></Meta_tytul>' . "\r\n";
                        }                
                        //
                    }

                    // meta opis
                    if ( isset($Konfiguracja['Meta_opis']) && $Konfiguracja['Meta_opis'] == 1 ) {
                        //                     
                        $NaglowekCsv .= 'Meta_opis' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_desc_tag']) . '";';   
                        if (!empty($infoOpisy['products_meta_desc_tag'])) {
                            $DoZapisaniaXML .= '      <Meta_opis><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_desc_tag']).']]></Meta_opis>' . "\r\n";
                        } 
                        //
                    }
                    
                    // meta slowa
                    if ( isset($Konfiguracja['Meta_slowa']) && $Konfiguracja['Meta_slowa'] == 1 ) {
                        //                      
                        $NaglowekCsv .= 'Meta_slowa' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_keywords_tag']) . '";'; 
                        if (!empty($infoOpisy['products_meta_keywords_tag'])) {
                            $DoZapisaniaXML .= '      <Meta_slowa><![CDATA['.Funkcje::CzyszczenieTekstu($infoOpisy['products_meta_keywords_tag']).']]></Meta_slowa>' . "\r\n";
                        }
                        //
                    }
                                        
                    $db->close_query($sqlo);
                    unset($infoOpisy);        
                    //
                }

                // jednostka miary
                if ( isset($Konfiguracja['Jednostka_miary']) && $Konfiguracja['Jednostka_miary'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $NazwaJednostkiMiary = '';
                        if ( isset($JednostkiMiary[(int)$info['products_jm_id']][$ile_jezykow[$w]['id']]) ) {
                            //
                            $NazwaJednostkiMiary = $JednostkiMiary[(int)$info['products_jm_id']][1];
                            //
                        }                       
                        
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }
                        
                        $NaglowekCsv .= 'Jednostka_miary' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . $NazwaJednostkiMiary . '";';
                        if (!empty($NazwaJednostkiMiary)) {
                            $DoZapisaniaXML .= '      <Jednostka_miary><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaJednostkiMiary).']]></Jednostka_miary>' . "\r\n";
                        }                
                        
                        unset($NazwaJednostkiMiary);
                        //
                    }
                    //
                }
                
                // termin wysylki
                if ( isset($Konfiguracja['Termin_wysylki']) && $Konfiguracja['Termin_wysylki'] == 1 ) {
                    //                 
                    $NazwaTerminuWysylki = '';
                    if ( isset($TerminyWysylek[(int)$info['products_shipping_time_id']][ $_SESSION['domyslny_jezyk']['id'] ]) ) {
                        //
                        $NazwaTerminuWysylki = $TerminyWysylek[(int)$info['products_shipping_time_id']][ $_SESSION['domyslny_jezyk']['id'] ];
                        //
                    }                       

                    $NaglowekCsv .= 'Termin_wysylki;';
                    $CoDoZapisania .= '"' . $NazwaTerminuWysylki . '";';
                    if (!empty($NazwaTerminuWysylki)) {
                        $DoZapisaniaXML .= '      <Termin_wysylki><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaTerminuWysylki).']]></Termin_wysylki>' . "\r\n";
                    }                
                    
                    unset($NazwaTerminuWysylki);
                    //
                }   

                // stan produktow
                if ( isset($Konfiguracja['Stan_produktu']) && $Konfiguracja['Stan_produktu'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $NazwaStanProduktu = '';
                        if ( isset($StanProduktow[(int)$info['products_condition_products_id']][$ile_jezykow[$w]['id']]) ) {
                            //
                            $NazwaStanProduktu = $StanProduktow[(int)$info['products_condition_products_id']][1];
                            //
                        }                       
                        
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }
                        
                        $NaglowekCsv .= 'Stan_produktu' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . $NazwaStanProduktu . '";';
                        if (!empty($NazwaStanProduktu)) {
                            $DoZapisaniaXML .= '      <Stan_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaStanProduktu).']]></Stan_produktu>' . "\r\n";
                        }                
                        
                        unset($NazwaStanProduktu);
                        //
                    }
                    //
                }     

                // gwarancje
                if ( isset($Konfiguracja['Gwarancja']) && $Konfiguracja['Gwarancja'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $NazwaGwarancja = '';
                        if ( isset($Gwarancje[(int)$info['products_warranty_products_id']][$ile_jezykow[$w]['id']]) ) {
                            //
                            $NazwaGwarancja = $Gwarancje[(int)$info['products_warranty_products_id']][1];
                            //
                        }                       
                        
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }
                        
                        $NaglowekCsv .= 'Gwarancja' . $Przedrostek . ';';
                        $CoDoZapisania .= '"' . $NazwaGwarancja . '";';
                        if (!empty($NazwaGwarancja)) {
                            $DoZapisaniaXML .= '      <Gwarancja><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaGwarancja).']]></Gwarancja>' . "\r\n";
                        }                
                        
                        unset($NazwaGwarancja);
                        //
                    }
                    //
                }                 
                
                // dostepnosc
                if ( isset($Konfiguracja['Dostepnosc']) && $Konfiguracja['Dostepnosc'] == 1 ) {
                    //                 
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        }            
                        //
                        if ((int)$info['products_availability_id'] != '99999') {
                            //        
                            $NazwaDostepnosci = '';
                            if ( isset($Dostepnosci[(int)$info['products_availability_id']][$ile_jezykow[$w]['id']]) ) {
                                //
                                $NazwaDostepnosci = $Dostepnosci[(int)$info['products_availability_id']][1];
                                //
                            }                        

                            $NaglowekCsv .= 'Dostepnosc' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaDostepnosci) . '";';
                            if (!empty($NazwaDostepnosci)) {
                                $DoZapisaniaXML .= '      <Dostepnosc><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaDostepnosci).']]></Dostepnosc>' . "\r\n";
                            }                     
                            
                            unset($NazwaDostepnosci);
                            //
                           } else {
                            //
                            $NaglowekCsv .= 'Dostepnosc' . $Przedrostek . ';';
                            $CoDoZapisania .= '"AUTOMATYCZNY";';  
                            $DoZapisaniaXML .= '      <Dostepnosc><![CDATA[AUTOMATYCZNY]]></Dostepnosc>' . "\r\n";
                            //
                        }                
                    }       
                    //
                }
                
                // producent
                if ( isset($Konfiguracja['Producent']) && $Konfiguracja['Producent'] == 1 ) {
                    //                  
                    $NazwaProducenta = '';
                    if ( isset($Producenci[(int)$info['manufacturers_id']]) ) {
                         $NazwaProducenta = $Producenci[(int)$info['manufacturers_id']];
                    }
                    //
                    $NaglowekCsv .= 'Producent;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaProducenta) . '";'; 
                    if (!empty($NazwaProducenta)) {
                        $DoZapisaniaXML .= '      <Producent><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaProducenta).']]></Producent>' . "\r\n";
                    }            
                    //
                    unset($NazwaProducenta);
                    //
                }
                
                // waluta
                if ( isset($Konfiguracja['Waluta']) && $Konfiguracja['Waluta'] == 1 ) {
                    //                  
                    $KodWaluty = '';
                    if ( isset($Walut[(int)$info['products_currencies_id']]) ) {
                         $KodWaluty = $Walut[(int)$info['products_currencies_id']];
                    }
                    //
                    $NaglowekCsv .= 'Waluta;';
                    $CoDoZapisania .= '"' . $KodWaluty . '";';
                    $DoZapisaniaXML .= '      <Waluta><![CDATA['.$KodWaluty.']]></Waluta>' . "\r\n";            
                    //
                    unset($KodWaluty);
                    //
                }
                
                // dodatkowe zdjecia
                if ( isset($Konfiguracja['Zdjecia_dodatkowe']) && $Konfiguracja['Zdjecia_dodatkowe'] == 1 ) {
                    //                 
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($w = 1; $w < 11; $w++) {
                        //
                        $zapytanieZdjecie = "select popup_images, images_description from additional_images where products_id = '" . (int)$info['products_id'] . "' limit ".($w-1).",1";
                        $sqlc = $db->open_query($zapytanieZdjecie);  
                        $infs = $sqlc->fetch_assoc();
                        
                        $NaglowekCsv .= 'Zdjecie_dodatkowe_' . $w . ';';
                        $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['popup_images']) . '";';
                        
                        if ( isset($Konfiguracja['Zdjecia_opis']) && $Konfiguracja['Zdjecia_opis'] == 1 ) {
                          
                            $NaglowekCsv .= 'Zdjecie_dodatkowe_opis_' . $w . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['images_description']) . '";';
                            
                        }
                        
                        if (!empty($infs['popup_images'])) {
                          
                            $DoZapisaniaXMLTmp .= '          <Zdjecie>' . "\r\n";
                            
                            $DoZapisaniaXMLTmp .= '              <Zdjecie_link>' . Funkcje::CzyszczenieTekstu($infs['popup_images']).'</Zdjecie_link>' . "\r\n";
                            
                            if ( isset($Konfiguracja['Zdjecia_opis']) && $Konfiguracja['Zdjecia_opis'] == 1 ) {
                              
                                if (!empty($infs['images_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Zdjecie_opis><![CDATA['.Funkcje::CzyszczenieTekstu($infs['images_description']).']]></Zdjecie_opis>' . "\r\n";
                                }                                 
                              
                            }
                            
                            $DoZapisaniaXMLTmp .= '          </Zdjecie>' . "\r\n";
                            
                        }                
                        
                        $db->close_query($sqlc);
                        unset($infs, $zapytanieZdjecie);
                        //
                    }                       
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Zdjecia_dodatkowe>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Zdjecia_dodatkowe>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);            
                    //
                }
                
                // dodatkowe zakladki
                if ( isset($Konfiguracja['Dodatkowe_zakladki']) && $Konfiguracja['Dodatkowe_zakladki'] == 1 ) {
                    //                  
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieZakladka = "select distinct * from products_info where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_info_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieZakladka);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Dodatkowa_zakladka_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_info_name']) . '";'; 
                            
                            $NaglowekCsv .= 'Dodatkowa_zakladka_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_info_description']) . '";';
                            
                            if (!empty($infs['products_info_name']) && !empty($infs['products_info_description'])) {
                                $DoZapisaniaXMLTmp .= '          <Dodatkowa_zakladka>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_info_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_info_description'].']]></Opis>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '          </Dodatkowa_zakladka>' . "\r\n";
                            }           

                            $db->close_query($sqlc);
                            unset($infs, $zapytanieZakladka);
                            //          
                        }
                    }
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Dodatkowe_zakladki>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Dodatkowe_zakladki>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);            
                    //
                }
                
                // linki
                if ( isset($Konfiguracja['Linki']) && $Konfiguracja['Linki'] == 1 ) {
                    //                 
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieLinki = "select distinct * from products_link where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_link_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieLinki);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Link_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_link_name']) . '";'; 
                            
                            $NaglowekCsv .= 'Link_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_link_description']) . '";';                      
                            
                            // adres url tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Link_'.$c.'_url;';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_link_url']) . '";';                 
                            }
                            
                            if (!empty($infs['products_link_name']) && !empty($infs['products_link_url'])) {
                                $DoZapisaniaXMLTmp .= '          <Link>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_link_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Url>'.$infs['products_link_url'].'</Url>' . "\r\n";
                                
                                if (!empty($infs['products_link_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_link_description'].']]></Opis>' . "\r\n";
                                }                        
                                
                                $DoZapisaniaXMLTmp .= '          </Link>' . "\r\n";
                            }                    
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytanieLinki);
                            //          
                        }
                    }    
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Linki>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Linki>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);        
                    //
                }

                // filmy youtube
                if ( isset($Konfiguracja['Youtube']) && $Konfiguracja['Youtube'] == 1 ) {
                    //                    
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieYoutube = "select distinct * from products_youtube where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_film_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieYoutube);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Youtube_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_film_name']) . '";'; 
                            
                            // adres url tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Youtube_'.$c.'_url;';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_film_url']) . '";';                 
                            }
                            
                            $NaglowekCsv .= 'Youtube_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_film_description']) . '";';  

                            // szerokosc i wysokosc tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Youtube_'.$c.'_szerokosc;';
                                $CoDoZapisania .= '"' . (int)$infs['products_film_width'] . '";';  
                                $NaglowekCsv .= 'Youtube_'.$c.'_wysokosc;';
                                $CoDoZapisania .= '"' . (int)$infs['products_film_height'] . '";';                         
                            }                    
                            
                            if (!empty($infs['products_film_name']) && !empty($infs['products_film_url'])) {
                                $DoZapisaniaXMLTmp .= '          <Youtube>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_film_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Url><![CDATA['.$infs['products_film_url'].']]></Url>' . "\r\n";
                                
                                if (!empty($infs['products_film_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_film_description'].']]></Opis>' . "\r\n";
                                }
                                if (!empty($infs['products_film_width'])) {
                                    $DoZapisaniaXMLTmp .= '              <Szerokosc>'.$infs['products_film_width'].'</Szerokosc>' . "\r\n";
                                }
                                if (!empty($infs['products_film_height'])) {
                                    $DoZapisaniaXMLTmp .= '              <Wysokosc>'.$infs['products_film_height'].'</Wysokosc>' . "\r\n";
                                }
                                $DoZapisaniaXMLTmp .= '          </Youtube>' . "\r\n";
                            }                    
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytanieYoutube);
                            //          
                        }
                    }    
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Filmy_youtube>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Filmy_youtube>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);                 
                    //
                }
                
                // filmy flv
                if ( isset($Konfiguracja['Filmy_FLV']) && $Konfiguracja['Filmy_FLV'] == 1 ) {
                    //                     
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 5; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytanieFlv = "select distinct * from products_film where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_film_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytanieFlv);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Film_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_film_name']) . '";'; 
                            
                            // adres pliku tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Film_'.$c.'_plik;';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_film_file']) . '";';                 
                            }
                            
                            $NaglowekCsv .= 'Film_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_film_description']) . '";';  

                            // szerokosc i wysokosc tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Film_'.$c.'_ekran;';
                                if ($infs['products_film_full_size'] == 1) {
                                    $CoDoZapisania .= '"tak";';
                                  } else {
                                    $CoDoZapisania .= '"nie";';
                                }                                               
                                $NaglowekCsv .= 'Film_'.$c.'_szerokosc;';
                                $CoDoZapisania .= '"' . (int)$infs['products_film_width'] . '";';  
                                $NaglowekCsv .= 'Film_'.$c.'_wysokosc;';
                                $CoDoZapisania .= '"' . (int)$infs['products_film_height'] . '";';                         
                            }                    
                            
                            if (!empty($infs['products_film_name']) && !empty($infs['products_film_file'])) {
                                $DoZapisaniaXMLTmp .= '          <Film>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_film_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_film_file'].'</Plik>' . "\r\n";
                                
                                if (!empty($infs['products_film_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_film_description'].']]></Opis>' . "\r\n";
                                }
                                if ($infs['products_film_full_size'] == 1 && !empty($infs['products_film_full_size'])) {
                                    $DoZapisaniaXMLTmp .= '              <Pelen_ekran>tak</Pelen_ekran>' . "\r\n";
                                  } else {
                                    $DoZapisaniaXMLTmp .= '              <Pelen_ekran>nie</Pelen_ekran>' . "\r\n";
                                }                           
                                if (!empty($infs['products_film_width'])) {
                                    $DoZapisaniaXMLTmp .= '              <Szerokosc>'.$infs['products_film_width'].'</Szerokosc>' . "\r\n";
                                }
                                if (!empty($infs['products_film_height'])) {
                                    $DoZapisaniaXMLTmp .= '              <Wysokosc>'.$infs['products_film_height'].'</Wysokosc>' . "\r\n";
                                }
                                $DoZapisaniaXMLTmp .= '          </Film>' . "\r\n";
                            }                    
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytanieFlv);
                            //          
                        }
                    }              
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Filmy>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Filmy>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);              
                    //
                }
                
                // pliki
                if ( isset($Konfiguracja['Pliki']) && $Konfiguracja['Pliki'] == 1 ) {
                    //                  
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($c = 1; $c < 6; $c++) {
                        //
                        for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                            //
                            $zapytaniePliki = "select distinct * from products_file where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_file_id = '".$c."'";        
                            $sqlc = $db->open_query($zapytaniePliki);  
                            $infs = $sqlc->fetch_assoc();                
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Plik_'.$c.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_file_name']) . '";'; 
                            
                            $NaglowekCsv .= 'Plik_'.$c.'_opis' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_file_description']) . '";';                 
                            
                            // plik i logowanie tylko dla jezyka polskiego
                            if ($ile_jezykow[$w]['kod'] == 'pl') {
                                $NaglowekCsv .= 'Plik_'.$c.'_plik;';
                                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_file']) . '";'; 
                                //
                                $NaglowekCsv .= 'Plik_'.$c.'_logowanie;';
                                if ($infs['products_file_login'] == 1) {
                                    $CoDoZapisania .= '"tak";';
                                  } else {
                                    $CoDoZapisania .= '"nie";';
                                }                   
                            }
                            
                            if (!empty($infs['products_file']) && !empty($infs['products_file_name'])) {
                                $DoZapisaniaXMLTmp .= '          <Plik>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_file_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_file'].'</Plik>' . "\r\n";
                                
                                if (!empty($infs['products_file_description'])) {
                                    $DoZapisaniaXMLTmp .= '              <Opis><![CDATA['.$infs['products_file_description'].']]></Opis>' . "\r\n";
                                }
         
                                if ($infs['products_file_login'] == 1 && !empty($infs['products_file_name'])) {
                                    $DoZapisaniaXMLTmp .= '              <Logowanie>tak</Logowanie>' . "\r\n";
                                  } else if (!empty($infs['products_file_name'])) {
                                    $DoZapisaniaXMLTmp .= '              <Logowanie>nie</Logowanie>' . "\r\n";
                                }     

                                $DoZapisaniaXMLTmp .= '          </Plik>' . "\r\n";
                            }
                            
                            $db->close_query($sqlc);
                            unset($infs, $zapytaniePliki);
                            //          
                        }
                    } 
                    //
                }
                
                // pliki elektroniczne
                if ( isset($Konfiguracja['Pliki_elektroniczne']) && $Konfiguracja['Pliki_elektroniczne'] == 1 ) {
                    //                    
                    $DoZapisaniaXMLTmp = '';
                    //
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        for ($t = 1; $t < 11; $t++) {
                            //
                            $zapytaniePliki = "select distinct products_file_shopping_name, products_file_shopping, language_id from products_file_shopping where products_id = '".(int)$info['products_id']."' and language_id = '" .$ile_jezykow[$w]['id']."' order by 	products_file_shopping_unique_id limit ".($t-1).",1";       
                            $sqlc = $db->open_query($zapytaniePliki);  
                            $infs = $sqlc->fetch_assoc();      
                            //
                            $Przedrostek = '';
                            if ($ile_jezykow[$w]['kod'] != 'pl') {
                                $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                            }        

                            $NaglowekCsv .= 'Plik_elektroniczny_'.$t.'_nazwa' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_file_shopping_name']) . '";'; 
                            
                            $NaglowekCsv .= 'Plik_elektroniczny_'.$t.'_plik' . $Przedrostek . ';';
                            $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infs['products_file_shopping']) . '";';                 
                            
                            if (!empty($infs['products_file_shopping']) && !empty($infs['products_file_shopping_name'])) {
                                $DoZapisaniaXMLTmp .= '          <Plik_elektroniczny>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_file_shopping_name'].']]></Nazwa>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_file_shopping'].'</Plik>' . "\r\n";
                                $DoZapisaniaXMLTmp .= '          </Plik_elektroniczny>' . "\r\n";
                            }
                            //
                        
                            $db->close_query($sqlc);
                            unset($infs, $zapytaniePliki);
                            //  
                        }
                        //
                    }           

                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Pliki_elektroniczne>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Pliki_elektroniczne>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);  
                    //
                }

                // pliki mp3
                if ( isset($Konfiguracja['Pliki_mp3']) && $Konfiguracja['Pliki_mp3'] == 1 ) {
                    //                  
                    $DoZapisaniaXMLTmp = '';
                    
                    for ($w = 1; $w < 16; $w++) {
                        //
                        $zapytanieZdjecie = "select * from products_mp3 where products_id = '" . (int)$info['products_id'] . "' order by products_mp3_id limit ".($w-1).",1";
                        $sqlc = $db->open_query($zapytanieZdjecie);  
                        $infs = $sqlc->fetch_assoc();
                        
                        $NaglowekCsv .= 'Plik_mp3_' . $w . ';';
                        $CoDoZapisania .= '"' . $infs['products_mp3_file'] . '";';
                        $NaglowekCsv .= 'Nazwa_mp3_' . $w . ';';
                        $CoDoZapisania .= '"' . $infs['products_mp3_name'] . '";';                
                        
                        if (!empty($infs['products_mp3_file']) && !empty($infs['products_mp3_name'])) {
                            $DoZapisaniaXMLTmp .= '          <Plik_mp3>' . "\r\n";
                            $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$infs['products_mp3_name'].']]></Nazwa>' . "\r\n";
                            $DoZapisaniaXMLTmp .= '              <Plik>'.$infs['products_mp3_file'].'</Plik>' . "\r\n";
                            $DoZapisaniaXMLTmp .= '          </Plik_mp3>' . "\r\n";
                        }               
                        
                        $db->close_query($sqlc);
                        unset($infs, $zapytanieZdjecie);
                        //
                    }                         
                    
                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Pliki_mp3>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Pliki_mp3>' . "\r\n";
                        //
                    }
                    unset($DoZapisaniaXMLTmp);             
                    //
                }
                
                // dodatkowe pola
                if ( isset($Konfiguracja['Dodatkowe_pola']) && $Konfiguracja['Dodatkowe_pola'] == 1 ) {
                    //                    
                    $DoZapisaniaXMLTmp = '';
                    
                    $nr_c = 1;
                    
                    for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                        //
                        $Przedrostek = '';
                        if ($ile_jezykow[$w]['kod'] != 'pl') {
                            $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                        } 
                        //
                        foreach ( $TablicaDodatkowePola as $DodatkowePole ) {
                            //
                            if ( $DodatkowePole['jezyk_id'] == $ile_jezykow[$w]['id'] || ($DodatkowePole['jezyk_id'] == 0 && $w == 0) ) {
                                //
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_nazwa' . $Przedrostek . ';';
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_wartosc' . $Przedrostek . ';';                                
                                $NaglowekCsv .= 'Dodatkowe_pole_' . $nr_c . '_link;';                                
                                //
                                $zapytaniePola = "select products_extra_fields_id, products_extra_fields_value, products_extra_fields_link from products_to_products_extra_fields where products_id = '".(int)$info['products_id']."' and products_extra_fields_id = '" . $DodatkowePole['pole_id'] . "'";        
                                $sqlc = $db->open_query($zapytaniePola);
                                $infoPole = $sqlc->fetch_assoc();
                                //
                                if ((int)$db->ile_rekordow($sqlc) > 0) {
                                    //
                                    $CoDoZapisania .= '"' . $DodatkowePole['nazwa_pola'] . '";'; 
                                    $CoDoZapisania .= '"' . $infoPole['products_extra_fields_value'] . '";';
                                    $CoDoZapisania .= '"' . $infoPole['products_extra_fields_link'] . '";';
                                    //
                                    if (!empty($DodatkowePole['nazwa_pola']) && !empty($infoPole['products_extra_fields_value'])) {
                                        $DoZapisaniaXMLTmp .= '          <Dodatkowe_pole>' . "\r\n";
                                        $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$DodatkowePole['nazwa_pola'].']]></Nazwa>' . "\r\n";
                                        $DoZapisaniaXMLTmp .= '              <Wartosc><![CDATA['.$infoPole['products_extra_fields_value'].']]></Wartosc>' . "\r\n";
                                        
                                        if ( trim($infoPole['products_extra_fields_link']) != '' ) {
                                             $DoZapisaniaXMLTmp .= '              <Link><![CDATA['.$infoPole['products_extra_fields_link'].']]></Link>' . "\r\n";
                                        }
                                        
                                        $DoZapisaniaXMLTmp .= '          </Dodatkowe_pole>' . "\r\n"; 
                                    }                                     
                                    //
                                  } else {
                                    //
                                    $CoDoZapisania .= '"";'; 
                                    $CoDoZapisania .= '"";';
                                    $CoDoZapisania .= '"";';
                                    //
                                }
                                //
                                $db->close_query($sqlc);
                                unset($zapytaniePola); 
                                //
                                $nr_c++;                            
                                //                                
                            }
                            //
                        }
                        //
                    }
                    
                    unset($nr_c);
                        
                    if ($DoZapisaniaXMLTmp != '') {
                        // 
                        $DoZapisaniaXML .= '      <Dodatkowe_pola>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Dodatkowe_pola>' . "\r\n";
                        //
                    }            
                    unset($DoZapisaniaXMLTmp);  
                    //
                }
                
                // cechy produktu
                if ( isset($Konfiguracja['Cechy_produktu']) && $Konfiguracja['Cechy_produktu'] == 1 ) {
                    //                   
                    $DoZapisaniaXMLTmp = '';
                    
                    //
                    for ($c = 1; $c < $ileCech; $c++) {
                        //
                        $zapytanieCechy = "select distinct * from products_attributes where products_id = '".(int)$info['products_id']."' order by options_id limit ".($c-1).",1";        
                        $sqlc = $db->open_query($zapytanieCechy); 

                        if ((int)$db->ile_rekordow($sqlc) > 0 || $_POST['format'] == 'csv') {                          

                            $infoCecha = $sqlc->fetch_assoc();            

                            for ($w = 0, $cw = count($ile_jezykow); $w < $cw; $w++) {
                                //
                                $Przedrostek = '';
                                if ($ile_jezykow[$w]['kod'] != 'pl') {
                                    $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                                }        

                                // nazwa cechy
                                $NaglowekCsv .= 'Cecha_nazwa_' . $c . $Przedrostek . ';';
                                $nazwa_cechy = Funkcje::NazwaCechy((int)$infoCecha['options_id'], $ile_jezykow[$w]['id']);
                                $CoDoZapisania .= '"' . $nazwa_cechy . '";'; 
                                
                                //
                                // wartosc cechy
                                $Przedrostek = '';
                                if ($ile_jezykow[$w]['kod'] != 'pl') {
                                    $Przedrostek = '_' . $ile_jezykow[$w]['kod'];
                                }        

                                $NaglowekCsv .= 'Cecha_wartosc_' . $c . $Przedrostek . ';';
                                $wartosc_cechy = Funkcje::WartoscCechy($infoCecha['options_values_id'], $ile_jezykow[$w]['id']);
                                $CoDoZapisania .= '"' . $wartosc_cechy . '";';
                                
                                if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                    //
                                    $DoZapisaniaXMLTmp .= '          <Cecha>' . "\r\n";
                                    $DoZapisaniaXMLTmp .= '              <Nazwa><![CDATA['.$nazwa_cechy.']]></Nazwa>' . "\r\n";
                                    $DoZapisaniaXMLTmp .= '              <Wartosc><![CDATA['.$wartosc_cechy.']]></Wartosc>' . "\r\n";
                                    //   
                                }             
                                //          
                            }
                            
                            // waga cechy
                            $NaglowekCsv .= 'Cecha_waga_' . $c . ';';
                            $CoDoZapisania .= '"' . $infoCecha['options_values_weight'] . '";';
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                //
                                $DoZapisaniaXMLTmp .= '              <Waga>'.$infoCecha['options_values_weight'].'</Waga>' . "\r\n";
                                //   
                            }                
                            
                            // cena cechy
                            $NaglowekCsv .= 'Cecha_cena_' . $c . ';';
                            //
                            $PrefixCeny = '';
                            if ( $infoCecha['price_prefix'] == '-' ) {
                                 $PrefixCeny = '-';
                            }
                            //
                            $CoDoZapisania .= '"' . $PrefixCeny . $infoCecha['options_values_price_tax'] . '";';
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy) && $info['options_type'] == 'cechy') {
                                //
                                $DoZapisaniaXMLTmp .= '              <Cena>'.$PrefixCeny. $infoCecha['options_values_price_tax'].'</Cena>' . "\r\n";
                                //   
                            }                  
                            //
                            
                            if (!empty($wartosc_cechy) && !empty($nazwa_cechy)) {
                                $DoZapisaniaXMLTmp .= '          </Cecha>' . "\r\n";
                            }
                            
                            unset($wartosc_cechy, $nazwa_cechy, $infoCecha); 

                        }
                        
                        $db->close_query($sqlc);
                        unset($zapytanieCechy);
                                    
                    }   

                    if ($DoZapisaniaXMLTmp != '') {
                        //
                        $DoZapisaniaXML .= '      <Cechy>' . "\r\n";
                        $DoZapisaniaXML .= $DoZapisaniaXMLTmp;
                        $DoZapisaniaXML .= '      </Cechy>' . "\r\n";
                        //
                    }            
                    unset($DoZapisaniaXMLTmp);              
                    //
                }
                
                $CoDoZapisania .= 'KONIEC' . "\r\n";

                $DoZapisaniaXML .= '  </Produkt>' . "\r\n" . "\r\n";
                
                $Suma++;

            }
              
            if ($_POST['format'] == 'csv') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
                }
                //
            }      

            // jezeli jest do zapisu xml
            if ($_POST['format'] == 'xml') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    ///
                    $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n". "\r\n";
                    $CoDoZapisania .= '<Produkty>' . "\r\n" . "\r\n";
                    $CoDoZapisania .= $DoZapisaniaXML;
                    //
                  } else {
                    //
                    $CoDoZapisania = $DoZapisaniaXML;
                    //
                }
                //
                // koniec pliku
                if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= $Suma) {
                    $CoDoZapisania .= '</Produkty>' . "\r\n";
                }
                unset($Suma);
            }            
            
            fwrite($fp, $CoDoZapisania);
            
            // zapisanie danych do pliku
            flock($fp, 3);
            // zamkniecie pliku
            fclose($fp);  

            unset($DoZapisaniaXML, $ileDodatkowychPol, $ileCech);
        
        }
    }
    
    
    // jezeli jest eksport cech
    if (isset($_POST['zakres']) && $_POST['zakres'] == 'cechy') {  

        $zapytanie = "select distinct * from products_stock order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        // jezeli sa warunki
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'producent') {
            //
            $DodatkoweCeny = '';
            if ( (int)ILOSC_CEN > 1 ) {
                //
                for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
                    //
                    $DodatkoweCeny .= 'products_stock_price_' . $n . ', products_stock_price_tax_' . $n . ',';
                    //
                }
                //
            }         
            $zapytanie = "select distinct " . $DodatkoweCeny . " p.products_id, p.manufacturers_id, ps.products_id, ps.products_stock_price, ps.products_stock_price_tax, ps.products_stock_attributes, ps.products_stock_image, ps.products_stock_quantity, ps.products_stock_availability_id, ps.products_stock_model from products p, products_stock ps where p.products_id = ps.products_id and p.manufacturers_id = '" . (int)$_POST['filtr'] . "' order by ps.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
            unset($DodatkoweCeny);
            //
        }
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'kategoria') {
            $zapytanie = "select distinct * from products_stock ps, products_to_categories pc where ps.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['filtr'] . "' order by ps.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }        
        
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $CoDoZapisania = '';
            $DoZapisaniaXML = '';

            // uchwyt pliku, otwarcie do dopisania
            $fp = fopen($filtr->process($_POST['plik']), "a");
            // blokada pliku do zapisu
            flock($fp, 2);
            
            $Suma = $_POST['limit'];
        
            while ($info = $sql->fetch_assoc()) {
            
                $NaglowekCsv = '';
                $DoZapisaniaXML .= '  <Produkt>' . "\r\n";
            
                $zapytanieNazwaProduktu = "select distinct p.products_id, p.products_model, pd.products_id, pd.products_name from products p, products_description pd where p.products_id = '".$info['products_id']."' and p.products_id = pd.products_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                $sqlNazwa = $db->open_query($zapytanieNazwaProduktu);
                $infc = $sqlNazwa->fetch_assoc();
                
                $NaglowekCsv .= 'Nr_katalogowy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_model']) . '";';   
                if (!empty($infc['products_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_model']).']]></Nr_katalogowy>' . "\r\n";
                }              
                
                $NaglowekCsv .= 'Nazwa_produktu;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_name']) . '";'; 
                if (!empty($infc['products_name'])) {
                    $DoZapisaniaXML .= '      <Nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_name']).']]></Nazwa_produktu>' . "\r\n";
                }            

                $db->close_query($sqlNazwa);
                unset($infc, $zapytanieNazwaProduktu); 

                $NaglowekCsv .= 'Nr_katalogowy_cechy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($info['products_stock_model']) . '";';
                if (!empty($info['products_stock_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy_cechy><![CDATA['.Funkcje::CzyszczenieTekstu($info['products_stock_model']).']]></Nr_katalogowy_cechy>' . "\r\n";
                }            

                // teraz rozpisuje cechy
                $NaglowekCsv .= 'Nazwa_wartosc_cechy;';
                $tablica_Cech = explode(',',$info['products_stock_attributes']);
                //
                $ciagCechy = '';
                for ($q = 0, $cq = count($tablica_Cech); $q < $cq; $q++) {
                    //
                    $NazwaWartosc = explode('-',$tablica_Cech[$q]);
                    $ciagCechy .= Funkcje::NazwaCechy( $NazwaWartosc[0] , '1' ) . ': ' . Funkcje::WartoscCechy( $NazwaWartosc[1] , '1' ) . ', ';
                    //
                }        
                $CoDoZapisania .= '"' . substr($ciagCechy, 0, strlen($ciagCechy)-2) . '";';  
                $DoZapisaniaXML .= '      <Nazwa_wartosc_cechy><![CDATA['.substr($ciagCechy, 0, strlen($ciagCechy)-2).']]></Nazwa_wartosc_cechy>' . "\r\n";           
                //

                $NaglowekCsv .= 'Ilosc_produktow;';
                $CoDoZapisania .= '"' . $info['products_stock_quantity'] . '";'; 
                $DoZapisaniaXML .= '      <Ilosc_produktow>'.$info['products_stock_quantity'].'</Ilosc_produktow>' . "\r\n"; 
                
                $NaglowekCsv .= 'Zdjecie;';
                $CoDoZapisania .= '"' . $info['products_stock_image'] . '";'; 
                $DoZapisaniaXML .= '      <Zdjecie>'.$info['products_stock_image'].'</Zdjecie>' . "\r\n";                 
                
                // ceny produktu z kombinacja cech
                $NaglowekCsv .= 'Cena_brutto_cechy;';
                $CoDoZapisania .= '"' . $info['products_stock_price_tax'] . '";'; 
                
                if ( $info['products_stock_price_tax'] > 0 ) {
                     $DoZapisaniaXML .= '      <Cena_brutto_cechy>'.$info['products_stock_price_tax'].'</Cena_brutto_cechy>' . "\r\n";   
                }

                if ( (int)ILOSC_CEN > 1 ) {    
                
                    for ( $x = 2; $x < ILOSC_CEN + 1; $x++ ) {

                        $NaglowekCsv .= 'Cena_brutto_cechy_' . $x . ';';
                        $CoDoZapisania .= '"' . $info['products_stock_price_tax_' . $x] . '";'; 
                        
                        if ( $info['products_stock_price_tax_' . $x] > 0 ) {
                             $DoZapisaniaXML .= '      <Cena_brutto_cechy_' . $x . '>'.$info['products_stock_price_tax_' . $x].'</Cena_brutto_cechy_' . $x . '>' . "\r\n";   
                        }

                    }

                }                    

                // dostepnosc
                //
                if ((int)$info['products_stock_availability_id'] != '99999') {
                    //
                    $NazwaDostepnosci = '';
                    if ( isset($Dostepnosci[(int)$info['products_stock_availability_id']][1]) ) {
                        //
                        $NazwaDostepnosci = $Dostepnosci[(int)$info['products_stock_availability_id']][1];
                        //
                    }

                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaDostepnosci) . '";';
                    if (!empty($NazwaDostepnosci)) {
                        $DoZapisaniaXML .= '      <Dostepnosc><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaDostepnosci).']]></Dostepnosc>' . "\r\n";
                    }  

                    unset($NazwaDostepnosci);

                   } else if ((int)$info['products_stock_availability_id'] == '99999') {
                    //
                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"AUTOMATYCZNY";';
                    $DoZapisaniaXML .= '      <Dostepnosc><![CDATA[AUTOMATYCZNY]]></Dostepnosc>' . "\r\n";                
                    //
                }

                $CoDoZapisania .= 'KONIEC' . "\r\n";

                $DoZapisaniaXML .= '  </Produkt>' . "\r\n" . "\r\n";
                
                $Suma++;
                
            }
            
            if ($_POST['format'] == 'csv') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
                }
                //
            }      

            // jezeli jest do zapisu xml
            if ($_POST['format'] == 'xml') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    ///
                    $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n". "\r\n";
                    $CoDoZapisania .= '<Produkty>' . "\r\n" . "\r\n";
                    $CoDoZapisania .= $DoZapisaniaXML;
                    //
                  } else {
                    //
                    $CoDoZapisania = $DoZapisaniaXML;
                    //
                }
                //
                // koniec pliku
                if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= $Suma) {
                    $CoDoZapisania .= '</Produkty>' . "\r\n";
                }
                unset($Suma);
            }            
            
            fwrite($fp, $CoDoZapisania);
            
            // zapisanie danych do pliku
            flock($fp, 3);
            // zamkniecie pliku
            fclose($fp);    

            unset($DoZapisaniaXML);
            
        }    

    }
    
    
    // jezeli jest ilosc, dostepnosc i cena
    if (isset($_POST['zakres']) && $_POST['zakres'] == 'cena_ilosc') {  

        $zapytanie = "select distinct * from products order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        // jezeli sa warunki
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'producent') {
            $zapytanie = "select distinct * from products where manufacturers_id = '" . (int)$_POST['filtr'] . "' order by products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }
        if (isset($_POST['filtr']) && $_POST['filtr_rodzaj'] == 'kategoria') {
            $zapytanie = "select distinct * from products p, products_to_categories pc where p.products_id = pc.products_id and pc.categories_id = '" . (int)$_POST['filtr'] . "' order by p.products_id limit ".(int)$_POST['limit']."," . $WskaznikPrzeskoku;
        }   
        
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $CoDoZapisania = '';
            $DoZapisaniaXML = '';

            // uchwyt pliku, otwarcie do dopisania
            $fp = fopen($filtr->process($_POST['plik']), "a");
            // blokada pliku do zapisu
            flock($fp, 2);
            
            $Suma = $_POST['limit'];
        
            while ($info = $sql->fetch_assoc()) {
            
                $NaglowekCsv = '';
                $DoZapisaniaXML .= '  <Produkt>' . "\r\n";
            
                $zapytanieNazwaProduktu = "select distinct p.products_id, p.products_model, pd.products_id, pd.products_name from products p, products_description pd where p.products_id = '".$info['products_id']."' and p.products_id = pd.products_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                $sqlNazwa = $db->open_query($zapytanieNazwaProduktu);
                $infc = $sqlNazwa->fetch_assoc();
                
                $NaglowekCsv .= 'Nr_katalogowy;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_model']) . '";';
                if (!empty($infc['products_model'])) {
                    $DoZapisaniaXML .= '      <Nr_katalogowy><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_model']).']]></Nr_katalogowy>' . "\r\n";
                }             
                
                $NaglowekCsv .= 'Nazwa_produktu;';
                $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['products_name']) . '";'; 
                if (!empty($infc['products_name'])) {
                    $DoZapisaniaXML .= '      <Nazwa_produktu><![CDATA['.Funkcje::CzyszczenieTekstu($infc['products_name']).']]></Nazwa_produktu>' . "\r\n";
                }             

                $db->close_query($sqlNazwa);
                unset($infc, $zapytanieNazwaProduktu); 

                $NaglowekCsv .= 'Ilosc_produktow;';
                $CoDoZapisania .= '"' . $info['products_quantity'] . '";';
                $DoZapisaniaXML .= '      <Ilosc_produktow>'.$info['products_quantity'].'</Ilosc_produktow>' . "\r\n";           
                
                // dostepnosc
                //
                if ((int)$info['products_availability_id'] != '99999') {
                    //
                    $NazwaDostepnosci = '';
                    if ( isset($Dostepnosci[(int)$info['products_availability_id']][1]) ) {
                        //
                        $NazwaDostepnosci = $Dostepnosci[(int)$info['products_availability_id']][1];
                        //
                    }                        
                    
                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($NazwaDostepnosci) . '";';
                    if (!empty($NazwaDostepnosci)) {
                        $DoZapisaniaXML .= '      <Dostepnosc><![CDATA['.Funkcje::CzyszczenieTekstu($NazwaDostepnosci).']]></Dostepnosc>' . "\r\n";
                    }                
                    
                    unset($NazwaDostepnosci);
                    //
                   } else {
                    //
                    $NaglowekCsv .= 'Dostepnosc;';
                    $CoDoZapisania .= '"AUTOMATYCZNY";';
                    $DoZapisaniaXML .= '      <Dostepnosc><![CDATA[AUTOMATYCZNY]]></Dostepnosc>' . "\r\n";                 
                    //
                }
                
                // cena brutto
                $NaglowekCsv .= 'Cena_brutto;';
                $CoDoZapisania .= '"' . $info['products_price_tax'] . '";'; 
                $DoZapisaniaXML .= '      <Cena_brutto>'.$info['products_price_tax'].'</Cena_brutto>' . "\r\n";

                // ceny brutto hurtowe
                for ($x = 1; $x <= ILOSC_CEN; $x++) {
                    if ($x > 1) {
                        $NaglowekCsv .= 'Cena_brutto_'.$x.';';
                        $CoDoZapisania .= '"' . $info['products_price_tax_'.$x]. '";'; 
                        if ($info['products_price_'.$x] > 0) {
                            $DoZapisaniaXML .= '      <Cena_brutto_'.$x.'>'.$info['products_price_tax_'.$x].'</Cena_brutto_'.$x.'>' . "\r\n";
                        }                    
                    }
                }

                $CoDoZapisania .= 'KONIEC' . "\r\n";

                $DoZapisaniaXML .= '  </Produkt>' . "\r\n" . "\r\n";
                
                $Suma++;

            }
            
            if ($_POST['format'] == 'csv') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
                }
                //
            }      

            // jezeli jest do zapisu xml
            if ($_POST['format'] == 'xml') {
                // jezeli poczatek pliku
                if ( (int)$_POST['limit'] == 0 ) {
                    ///
                    $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n". "\r\n";
                    $CoDoZapisania .= '<Produkty>' . "\r\n" . "\r\n";
                    $CoDoZapisania .= $DoZapisaniaXML;
                    //
                  } else {
                    //
                    $CoDoZapisania = $DoZapisaniaXML;
                    //
                }
                //
                // koniec pliku
                if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= $Suma) {
                    $CoDoZapisania .= '</Produkty>' . "\r\n";
                }
                unset($Suma);
            }            
            
            fwrite($fp, $CoDoZapisania);
            
            // zapisanie danych do pliku
            flock($fp, 3);
            // zamkniecie pliku
            fclose($fp);       

            unset($DoZapisaniaXML);
            
        }    

    } 

    unset($JednostkiMiary, $Dostepnosci, $Vat, $Producenci, $Walut);

}
?>