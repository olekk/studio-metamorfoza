<?php
chdir('../../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) {

    $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit'], ' > ' );

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
        
            $DoZapisaniaXML .= "<product id=\"".$porownywarki->produkty[$i]['id_produktu']."\">\n";

            $DoZapisaniaXML .= "<name>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu'])."</name>\n";
            $DoZapisaniaXML .= "<url>".$porownywarki->produkty[$i]['url_produktu']."</url>\n";
            $DoZapisaniaXML .= "<brand>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['producent_produktu'])."</brand>\n";
            $DoZapisaniaXML .= "<categories>\n";
            $DoZapisaniaXML .= "    <category>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['kategoria_produktu'])."</category>\n";
            $DoZapisaniaXML .= "</categories>\n";
            $DoZapisaniaXML .= "<photo>".$porownywarki->produkty[$i]['zdjecie_produktu']."</photo>\n";
            $DoZapisaniaXML .= "<description>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['opis_produktu'])."</description>\n";
            $DoZapisaniaXML .= "<price>".number_format($porownywarki->produkty[$i]['cena_brutto_produktu'], 2, ',', '')."</price>\n";

            $DoZapisaniaXML .= "<attributes>\n";
            $DoZapisaniaXML .= "    <attr>\n";
            $DoZapisaniaXML .= "        <name>EAN</name>\n";
            $DoZapisaniaXML .= "        <value>".$porownywarki->produkty[$i]['numer_ean_produktu']."</value>\n";
            $DoZapisaniaXML .= "    </attr>\n";

            //dodatkowe pola do produktu START
            if ( count($porownywarki->produkty[$i]['pola']) > 0 ) {
                foreach ( $porownywarki->produkty[$i]['pola'] as $key => $value ) {
                    $DoZapisaniaXML .= "    <attr>\n";
                    $DoZapisaniaXML .= "        <name>".$key."</name>\n";
                    $DoZapisaniaXML .= "        <value>".$value."</value>\n";
                    $DoZapisaniaXML .= "    </attr>\n";
                }
            }
            //dodatkowe pola do produktu KONIEC
            $DoZapisaniaXML .= "</attributes>\n";
            $DoZapisaniaXML .= "</product>\n";

        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania    = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $CoDoZapisania .= "<products";
            $CoDoZapisania .= "     xmlns=\"http://www.sklepy24.pl\"\n";
            $CoDoZapisania .= "     xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
            $CoDoZapisania .= "     xsi:schemaLocation=\"http://www.sklepy24.pl http://www.sklepy24.pl/formats/products.xsd\"\n";
            $CoDoZapisania .= "     date=\"".date("Y-m-d")."\">\n";

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
            $CoDoZapisania .= "</products>";
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