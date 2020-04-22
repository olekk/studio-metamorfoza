<?php
chdir('../../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) {

    $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit']);

    if ( count($porownywarki->produkty) > 0 ) {

        $tablica_dostepnosci            = Porownywarki::TablicaDostepnosci('nokaut');

        $CoDoZapisania = '';
        $DoZapisaniaXML = '';
        $plik = KATALOG_SKLEPU . 'xml/' . $filtr->process($_POST['plugin']) . '.xml';

        // uchwyt pliku, otwarcie do dopisania
        $fp = fopen($plik, "a");
        // blokada pliku do zapisu
        flock($fp, 2);

        //dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            //Pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
            $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

            if ( $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
            } else {
                $dostepnosc = $porownywarki->dotepnosc_domyslna;
            }

            $DoZapisaniaXML .= "<product>\n";
            $DoZapisaniaXML .= "    <id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";

            $DoZapisaniaXML .= "    <name><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></name>\n";
            $DoZapisaniaXML .= "    <producer><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></producer>\n";
            $DoZapisaniaXML .= "    <code><![CDATA[".$porownywarki->produkty[$i]['numer_ean_produktu']."]]></code>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
            $DoZapisaniaXML .= "    <url><![CDATA[".$porownywarki->produkty[$i]['url_produktu']."]]></url>\n";
            $DoZapisaniaXML .= "    <price>".$porownywarki->produkty[$i]['cena_brutto_produktu']."</price>\n";
            $DoZapisaniaXML .= "    <category><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></category>\n";
            $DoZapisaniaXML .= "    <image><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></image>\n";
            $DoZapisaniaXML .= "    <weight>".$porownywarki->produkty[$i]['waga_produktu']."</weight>\n";
            $DoZapisaniaXML .= "    <quantity>".$porownywarki->produkty[$i]['ilosc_produktu']."</quantity>\n";
            $DoZapisaniaXML .= "    <availability>".$dostepnosc."</availability>\n";

            // wyszukiwanie cech z tablicy
            $cechy_zapytanie = "SELECT DISTINCT pa.options_id, po.products_options_name FROM products_attributes pa, products_options po WHERE pa.products_id = '".$porownywarki->produkty[$i]['id_produktu']."' AND pa.options_id = po.products_options_id AND po.language_id = '".$_SESSION['domyslny_jezyk']['id']."' ORDER BY po.products_options_sort_order ASC";
            $sqlq = $db->open_query($cechy_zapytanie); 
            
            if ((int)$db->ile_rekordow($sqlq) > 0) {

            $DoZapisaniaXML .= "    <parms>\n";
            
            while ($cecha_pozycja = $sqlq->fetch_assoc()) {

                $DoZapisaniaXML .= "    <parm_name>".$cecha_pozycja['products_options_name']."</parm_name>\n";

                // wyszukiwanie wartosci cechy z tablicy
                $cechy_wartosci_zapytanie = "SELECT DISTINCT pv.products_options_values_id FROM products_attributes pa, products_options_values_to_products_options pv WHERE pa.products_id = '".$porownywarki->produkty[$i]['id_produktu']."' AND pa.options_id = '".$cecha_pozycja['options_id']."' AND pa.options_values_id = pv.products_options_values_id ORDER BY pv.products_options_values_sort_order ASC";
                $sqlw = $db->open_query($cechy_wartosci_zapytanie); 

                while ($cecha_wartosc = $sqlw->fetch_assoc()) {

                     $cechy = "SELECT * FROM products_options_values WHERE language_id = '".$_SESSION['domyslny_jezyk']['id']."' AND products_options_values_id = '".$cecha_wartosc['products_options_values_id']."'";
                     $sqlc = $db->open_query($cechy);
                     $cecha = $sqlc->fetch_assoc();
                     $DoZapisaniaXML .= "       <parm_value>".$cecha['products_options_values_name']."</parm_value>\n";

                     $db->close_query($sqlc);
                     unset($cecha);     
                }
                $db->close_query($sqlw);
                unset($cecha_wartosc);
            }
            $db->close_query($sqlq);
            unset($cecha_pozycja);

            $DoZapisaniaXML .= "    </parms>\n";
            
            }

            //dodatkowe pola do produktu START
            if ( count($porownywarki->produkty[$i]['pola']) > 0 ) {
                foreach ( $porownywarki->produkty[$i]['pola'] as $key => $value ) {
                    $DoZapisaniaXML .= "    <property name=\"".$key."\">".$value."</property>\n";
                }
            }
            //dodatkowe pola do produktu KONIEC


            $DoZapisaniaXML .= "</product>\n";

        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<!DOCTYPE starcode SYSTEM \"http://www.starcode.pl/xml/dtd/starcode_xml.dtd\">\n";
            $CoDoZapisania .= "    <offers>\n";
            $CoDoZapisania .= "    <stat>\n";
            $CoDoZapisania .= "    <num>".$_POST['ilosc_rekordow']."</num>\n";
            $CoDoZapisania .= "    <ver>2.0</ver>\n";
            $CoDoZapisania .= "    </stat>\n";

            $CoDoZapisania .= $DoZapisaniaXML;
            //
        } else {
            //
            $CoDoZapisania = $DoZapisaniaXML;
            //
        }
        //
        // koniec pliku
        if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] <= (int)$_POST['offset'] + (int)$_POST['limit']) {
            $CoDoZapisania .= "    </offers>\n";
        }
    }
 
    fwrite($fp, $CoDoZapisania);

    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp); 

    unset($CoDoZapisania);    

}
echo 'OK';

?>