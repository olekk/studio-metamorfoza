<?php
// {{format Ceneo}}
// tylko dane do pobierania
if ( isset($_GET['tylko_rekordy']) ) {
    ?>
    <div id="dodXML">
        <span>Przy dodawaniu z pliku ceneo zostaną pobrane i dodane do sklepu:</span>
        <input style="margin-left:0px" type="checkbox" name="dod_zakres_kategoria" value="1" checked="checked" /> kategoria 
        <input type="checkbox" name="dod_zakres_nazwa_produktu" value="1" checked="checked" disabled="disabled" /> nazwa produktu
        <input type="checkbox" name="dod_zakres_nr_kat" value="1" checked="checked" disabled="disabled" /> nr katalogowy
        <input type="checkbox" name="dod_zakres_kod_prod" value="1" checked="checked" /> kod producenta
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
        <span>Przy aktualizacji z pliku ceneo zostaną pobrane i zaktualizowane:</span>
        <input style="margin-left:0px" type="checkbox" name="akt_zakres_nazwa_produktu" value="1" /> nazwa produktu
        <input type="checkbox" name="akt_zakres_kod_prod" value="1" /> kod producenta
        <input type="checkbox" name="akt_zakres_ilosc" value="1" checked="checked" /> stan magazynowy
        <input type="checkbox" name="akt_zakres_cena" value="1" checked="checked" /> cena brutto
        <input type="checkbox" name="akt_zakres_dostepnosc" value="1" checked="checked" /> dostępność produktu
        <input type="checkbox" name="akt_zakres_waga" value="1" /> waga
        <input type="checkbox" name="akt_zakres_opis" value="1" /> opis  
    </div>
    <?php
    exit;
}

// stworzenie tablicy z definicjami i struktura importu
$TablicaDane = array();

if ( isset($dane_produktow->group) ) {
     $produkt = $dane_produktow->group->o[(int)$_POST['limit']];
   } else {
     $produkt = $dane_produktow->o[(int)$_POST['limit']];
}     

if (isset($produkt)) {

    $Importuj = false;
    
    if ( $CzyWszystkieKategorie == true ) {
         $Importuj = true;
    }
    
    // sprawdza czy jest nazwa produktu
    if ($produkt->name != '') {

        // kategorie
        if (isset($produkt->cat) && isset($_POST['zakres_kategoria'])) {
            //
            // bedzie szukal po tablicy kategorii czy dana kategoria z ceneo jest w tablicy szablonu
            // ustali marze dla ceny jezeli jest
            if (count($TablicaKategorii) > 0) {
                //
                for ($d = 0, $c = count($TablicaKategorii); $d < $c; $d++) {
                    if (trim(strtoupper($TablicaKategorii[$d][0])) == trim(strtoupper($produkt->cat))) {
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
                    $ZawartoscKat = explode('/',$produkt->cat);
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

            // atrybuty
            $atrs = $produkt->attributes();

            // cena brutto
            if ( isset($_POST['zakres_cena']) ) {
                 $TablicaDane['Cena_brutto'] = $atrs['price'];
            }
            
            // podatek vat
            if ( $_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_cena']) ) {
                $TablicaDane['Podatek_Vat'] = (int)$_POST['vat'];            
            }

            // dostepnosc produktu
            if ( isset($_POST['zakres_dostepnosc']) ) {
                $TablicaDane['Dostepnosc'] = '';
                if ($atrs['avail'] != '') {
                    //
                    // sprawdza dostepnosc dla ceneo
                    $zapytanieDostepnosc = "select pd.products_availability_name from products_availability p, products_availability_description pd where p.products_availability_id = pd.products_availability_id and ceneo = '" . (int)$atrs['avail'] . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                    $sqlc = $db->open_query($zapytanieDostepnosc);
                    //    
                    if ((int)$db->ile_rekordow($sqlc) > 0) {
                        $info = $sqlc->fetch_assoc();
                        $TablicaDane['Dostepnosc'] = $info['products_availability_name'];
                        $db->close_query($sqlc);
                        unset($info);        
                    }
                }
            }
            
            // waga produktu
            if ( isset($_POST['zakres_waga']) ) {
                $TablicaDane['Waga'] = $atrs['weight']; 
            }
            
            // ilosc produktow
            if ( isset($_POST['zakres_ilosc']) ) {
                $TablicaDane['Ilosc_produktow'] = $atrs['stock'];      
            }
            
            // nazwa produktu
            $TablicaDane['Nazwa_produktu_struktura'] = $produkt->name; 
            if ( isset($_POST['zakres_nazwa_produktu']) ) {
                $TablicaDane['Nazwa_produktu'] = $produkt->name; 
            }

            // opis produktu
            if ( isset($_POST['zakres_opis']) ) {
                $TablicaDane['Opis'] = $produkt->desc;
            }

            // atrybuty z pliku
            $LicznikPola = 1;
            //            
            for ($s = 0, $cs = count($produkt->attrs->a); $s < $cs; $s++) {
                //
                $ceneo_atrs = $produkt->attrs->a[$s]->attributes();
                //
                switch (strtoupper($ceneo_atrs['name'])) {
                    case 'PRODUCENT':
                        // tylko jezeli jest dodawanie
                        if ($_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_producent'])) {
                            $TablicaDane['Producent'] = $produkt->attrs->a[$s];
                        }
                        break;
                    case 'KOD_PRODUCENTA':
                        $TablicaDane['Nr_katalogowy'] = $produkt->attrs->a[$s];
                        $TablicaDane['Kod_producenta'] = $produkt->attrs->a[$s];
                        break;
                    case 'EAN':
                        if ($_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_parametry'])) {
                            $TablicaDane['Kod_ean'] = $produkt->attrs->a[$s];
                        }
                        break;
                    default:
                        if ($_POST['rodzaj_import'] == 'dodawanie' && isset($_POST['zakres_parametry'])) {
                            $TablicaDane['Dodatkowe_pole_' . $LicznikPola . '_nazwa'] = str_replace('_', ' ', $ceneo_atrs['name']);
                            $TablicaDane['Dodatkowe_pole_' . $LicznikPola . '_wartosc'] = $produkt->attrs->a[$s];
                            $LicznikPola++;
                        }
                }        
                //
            }
            unset($LicznikPola);

            // jezeli nr katalogowy jest pusty to utworzy numer katalogowy na podstawie id
            if (trim($TablicaDane['Nr_katalogowy']) == '') {
                if ($atrs['id'] != '') {
                    $TablicaDane['Nr_katalogowy'] = $atrs['id'];
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
            
                // zdjecie produktu
                $zdjecie_atrs = $produkt->imgs->main;

                // zapisywanie zdjecia na serwerze
                $zdjecie_atrs['url'] = str_replace('https','http',$zdjecie_atrs['url']);
                $url = $zdjecie_atrs['url'];
                
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
                        PobieranieCurl::ZapiszObraz($zdjecie_atrs['url'], $katalog);
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