<?php
// {{format StarCode}}
// tylko dane do pobierania
if ( isset($_GET['tylko_rekordy']) ) {
    ?>
    <div id="dodXML">
        <span>Przy dodawaniu z pliku starcode zostaną pobrane i dodane do sklepu:</span>
        <input style="margin-left:0px" type="checkbox" name="dod_zakres_kategoria" value="1" checked="checked" /> kategoria 
        <input type="checkbox" name="dod_zakres_nazwa_produktu" value="1" checked="checked" disabled="disabled" /> nazwa produktu
        <input type="checkbox" name="dod_zakres_nr_kat" value="1" checked="checked" disabled="disabled" /> nr katalogowy
        <input type="checkbox" name="dod_zakres_ilosc" value="1" checked="checked" /> stan magazynowy
        <input type="checkbox" name="dod_zakres_cena" value="1" checked="checked" disabled="disabled" /> cena brutto
        <input type="checkbox" name="dod_zakres_dostepnosc" value="1" checked="checked" /> dostępność produktu
        <input type="checkbox" name="dod_zakres_waga" value="1" checked="checked" /> waga <br />
        <input style="margin-left:0px" type="checkbox" name="dod_zakres_opis" value="1" checked="checked" /> opis  
        <input type="checkbox" name="dod_zakres_producent" value="1" checked="checked" /> producent
        <input type="checkbox" name="dod_zakres_zdjecie" value="1" checked="checked" /> zdjęcie
        <input type="checkbox" name="dod_zakres_parametry" value="1" checked="checked" /> dodatkowe parametry
    </div>
    
    <div id="aktXML" style="display:none">
        <span>Przy aktualizacji z pliku starcode zostaną pobrane i zaktualizowane:</span>
        <input style="margin-left:0px" type="checkbox" name="akt_zakres_nazwa_produktu" value="1" /> nazwa produktu
        <input type="checkbox" name="akt_zakres_ilosc" value="1" checked="checked" /> stan magazynowy
        <input type="checkbox" name="akt_zakres_cena" value="1" checked="checked" /> cena brutto
        <input type="checkbox" name="akt_zakres_dostepnosc" value="1" checked="checked" /> dostępność produktu
        <input type="checkbox" name="dod_zakres_waga" value="1" checked="checked" /> waga
        <input type="checkbox" name="akt_zakres_opis" value="1" /> opis  
    </div>
    <?php
    exit;
}

// stworzenie tablicy z definicjami i struktura importu
$TablicaDane = array();

$produkt = $dane_produktow->product[(int)$_POST['limit']];

if (isset($produkt)) {

    $Importuj = false;
    
    if ( $CzyWszystkieKategorie == true ) {
         $Importuj = true;
    }
    
    // sprawdza czy jest nazwa produktu
    if ($produkt->name != '') {

        // kategorie
        if (isset($produkt->category) && isset($_POST['zakres_kategoria'])) {
            //
            // bedzie szukal po tablicy kategorii czy dana kategoria z starcode jest w tablicy szablonu
            // ustali marze dla ceny jezeli jest
            if (count($TablicaKategorii) > 0) {
                //
                for ($d = 0, $c = count($TablicaKategorii); $d < $c; $d++) {
                    if (trim(strtoupper($TablicaKategorii[$d][0])) == trim(strtoupper($produkt->category))) {
                        $Importuj = true;
                        $_POST['marza'] = (float)$TablicaKategorii[$d][1];
                    }
                }
                //
            }
            
            // tylko jezeli jest dodawanie
            if ($_POST['rodzaj_import'] == 'dodawanie') {
                //
                if ($Importuj == true) {
                    //
                    $ZawartoscKat = explode('/',$produkt->category);
                    for ($p = 0, $cp = count($ZawartoscKat); $p < $cp; $p++) {
                        //
                        $TablicaDane['Kategoria_'.($p + 1).'_nazwa'] = $ZawartoscKat[$p];                
                        //
                    }
                    //
                    unset($ZawartoscKat);
                    //
                }
                //
            }
            
        } 

        if ($Importuj == true) {

            // cena brutto
            if ( isset($_POST['zakres_cena']) ) {
                 $TablicaDane['Cena_brutto'] = $produkt->price;
            }
            
            // podatek vat
            if ( $_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_cena']) ) {
                $TablicaDane['Podatek_Vat'] = (int)$_POST['vat'];            
            }
            
            // waga produktu
            if ( isset($_POST['zakres_waga']) ) {
                $TablicaDane['Waga'] = $produkt->weight;
            }            

            // dostepnosc produktu
            if ( isset($_POST['zakres_dostepnosc']) ) {
                $TablicaDane['Dostepnosc'] = $produkt->availability;
            }

            // nazwa produktu
            $TablicaDane['Nazwa_produktu_struktura'] = $produkt->name; 
            if ( isset($_POST['zakres_nazwa_produktu']) ) {
                $TablicaDane['Nazwa_produktu'] = $produkt->name; 
            }

            // opis produktu
            if ( isset($_POST['zakres_opis']) ) {
                $TablicaDane['Opis'] = $produkt->description;
            }
            
            // producent
            if ( isset($_POST['zakres_producent']) ) {
                $TablicaDane['Producent'] = $produkt->producer;
            }
            
            // ilosc produktow
            if ( isset($_POST['zakres_ilosc']) ) {
                $TablicaDane['Ilosc_produktow'] = $produkt->quantity;    
            }            

            // atrybuty z pliku
            $LicznikPola = 1;
            //            
            for ($s = 0, $cs = count($produkt->parms->parm_name); $s < $cs; $s++) {
                //
                $starcode_klucz = $produkt->parms->parm_name[$s];
                $starcode_wartosc = $produkt->parms->parm_value[$s];
                //
                if ($_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_parametry']) && $starcode_klucz != '' && $starcode_wartosc  != '') {
                    $TablicaDane['Dodatkowe_pole_' . $LicznikPola . '_nazwa'] = $starcode_klucz;
                    $TablicaDane['Dodatkowe_pole_' . $LicznikPola . '_wartosc'] = $starcode_wartosc;
                    $LicznikPola++;
                }     
                //
                unset($starcode_klucz, $starcode_wartosc);
            }
            unset($LicznikPola);
            
            $TablicaDane['Nr_katalogowy'] = $produkt->code;

            // jezeli nr katalogowy jest pusty to utworzy numer katalogowy na podstawie id
            if (trim($TablicaDane['Nr_katalogowy']) == '') {
                if ($produkt->id != '') {
                    $TablicaDane['Nr_katalogowy'] = $produkt->id;
                  } else {
                    // tylko jezeli jest dodawanie
                    if ($_POST['rodzaj_import'] == 'dodawanie') {
                        //                  
                        $TablicaDane['Nr_katalogowy'] = rand(21212,99999999);
                        //
                    }
                }
            }

            if ($_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_zdjecie']) ) {
            
                // zapisywanie zdjecia na serwerze
                $produkt->image = str_replace('https','http',$produkt->image);
                $url = $produkt->image;                
                
                if ( PobieranieCurl::CzyJestPlikObrazka($url) && $url != '' ) {

                    // samo zdjecie z katalogiem
                    $url = parse_url($url, PHP_URL_PATH);
                    $url = str_replace(" ", "%20", $url);
                    
                    $sciezka = explode('/', $url);
                    
                    if ( count($sciezka) > 1 ) {
                    
                        $samoZdjecie = implode('', array_slice( $sciezka, -1, 1));
                        
                        $podzialNa = array_slice( $sciezka, 0, -1);
                        $podzialNa = implode('/', $podzialNa);
                        
                        $katalog = '../' . KATALOG_ZDJEC . $podzialNa;
                        
                        if ( !file_exists( $katalog ) ) {
                             mkdir ( $katalog, 0777, true );
                        }
                        
                    } else {
                    
                        $samoZdjecie = implode('', $sciezka);
                    
                    }
                    
                    $katalog = $katalog . '/' . $samoZdjecie;
                                
                    if (!is_file($katalog)) {
                        //zapisanie pobranego obrazka na serwerze
                        PobieranieCurl::ZapiszObraz($produkt->image, $katalog);
                    }
                    
                    $TablicaDane['Zdjecie_glowne'] = str_replace('../' . KATALOG_ZDJEC . '/','',$katalog);
                    
                    unset($url, $sciezka, $samoZdjecie, $katalog);
                
                } else {
                
                    $TablicaDane['Zdjecie_glowne'] = '';
                
                }
            }
            
            // czyszczenie tablicy trim
            foreach ($TablicaDane as $Klucz => $Wartosc) {
                $TablicaDane[$Klucz] = trim(preg_replace('/[\r\n]+/', '', $Wartosc));
            }            
            
            // status
            $TablicaDane['Status'] = 'tak';
            
        }
    }
}
?>