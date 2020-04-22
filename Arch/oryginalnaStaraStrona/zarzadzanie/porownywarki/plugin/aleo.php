<?php
chdir('../../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) {

    $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit']);

    if ( count($porownywarki->produkty) > 0 ) {

        $CoDoZapisania = '';
        $DoZapisaniaXML = '';
        $plik = KATALOG_SKLEPU . 'xml/' . $filtr->process($_POST['plugin']) . '.xml';

        // uchwyt pliku, otwarcie do dopisania
        $fp = fopen($plik, "a");
        // blokada pliku do zapisu
        flock($fp, 2);

        //dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            $DoZapisaniaXML .= "<offer>\n";
            $DoZapisaniaXML .= "    <id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";

            $DoZapisaniaXML .= "    <name>".Funkcje::przytnijTekst(Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu']),100)."</name>\n";
            $DoZapisaniaXML .= "    <image>".$porownywarki->produkty[$i]['zdjecie_produktu']."</image>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
            $DoZapisaniaXML .= "    <property name=\"ean\">".$porownywarki->produkty[$i]['numer_ean_produktu']."</property>\n";
            $DoZapisaniaXML .= "    <property name=\"catalog_number\">".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['kod_producenta_produktu'])."</property>\n";
            $DoZapisaniaXML .= "    <category><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></category>\n";
            $DoZapisaniaXML .= "    <producer><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></producer>\n";
            $DoZapisaniaXML .= "    <price>".$porownywarki->produkty[$i]['cena_netto_produktu']."</price>\n";

            //dodatkowe pola do produktu START
            if ( count($porownywarki->produkty[$i]['pola']) > 0 ) {
                foreach ( $porownywarki->produkty[$i]['pola'] as $key => $value ) {
                    $DoZapisaniaXML .= "    <property name=\"".$key."\">".Porownywarki::TekstZamienEncje($value)."</property>\n";
                }
            }
            //dodatkowe pola do produktu KONIEC

            $DoZapisaniaXML .= "</offer>\n";

        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<nokaut>\n";
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