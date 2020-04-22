<?php
chdir('../../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) {

    $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit']);

    if ( count($porownywarki->produkty) > 0 ) {

        $tablica_dostepnosci            = Porownywarki::TablicaDostepnosci( $_POST['plugin'] );

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

            $DoZapisaniaXML .= "<offer>\n";
            $DoZapisaniaXML .= "    <id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";

            $DoZapisaniaXML .= "    <name><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></name>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
            $DoZapisaniaXML .= "    <url><![CDATA[".$porownywarki->produkty[$i]['url_produktu']."]]></url>\n";
            $DoZapisaniaXML .= "    <image><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></image>\n";
            $DoZapisaniaXML .= "    <weight>".$porownywarki->produkty[$i]['waga_produktu']."</weight>\n";
            $DoZapisaniaXML .= "    <price>".$porownywarki->produkty[$i]['cena_brutto_produktu']."</price>\n";
            $DoZapisaniaXML .= "    <category><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></category>\n";
            $DoZapisaniaXML .= "    <producer><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></producer>\n";
            $DoZapisaniaXML .= "    <instock>".$porownywarki->produkty[$i]['ilosc_produktu']."</instock>\n";
            $DoZapisaniaXML .= "    <property name=\"EAN\">".$porownywarki->produkty[$i]['numer_ean_produktu']."</property>\n";

            //dodatkowe pola do produktu START
            if ( count($porownywarki->produkty[$i]['pola']) > 0 ) {
                foreach ( $porownywarki->produkty[$i]['pola'] as $key => $value ) {
                    $DoZapisaniaXML .= "    <property name=\"".$key."\">".$value."</property>\n";
                }
            }
            //dodatkowe pola do produktu KONIEC

            $DoZapisaniaXML .= "    <availability>".$dostepnosc."</availability>\n";

            $DoZapisaniaXML .= "</offer>\n";

        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<!DOCTYPE nokaut SYSTEM \"http://www.nokaut.pl/integracja/nokaut.dtd\">\n";
            $CoDoZapisania .= "<nokaut generator=\"shopGold\" ver=\"1.0\">\n";
            $CoDoZapisania .= "    <offers>\n";

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
            $CoDoZapisania .= "</nokaut>";
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