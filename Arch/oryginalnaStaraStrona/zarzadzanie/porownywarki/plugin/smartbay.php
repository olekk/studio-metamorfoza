<?php
chdir('../../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) {

    $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit'], ' / ');

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

            $DoZapisaniaXML .= "<product>\n";
            $DoZapisaniaXML .= "    <id>".$porownywarki->produkty[$i]['id_produktu']."</id>\n";
            $DoZapisaniaXML .= "    <name><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></name>\n";
            $DoZapisaniaXML .= "    <producer><![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]></producer>\n";
            $DoZapisaniaXML .= "    <code><![CDATA[".$porownywarki->produkty[$i]['numer_katalogowy_produktu']."]]></code>\n";
            $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
            $DoZapisaniaXML .= "    <url><![CDATA[".$porownywarki->produkty[$i]['url_produktu']."]]></url>\n";
            $DoZapisaniaXML .= "    <price><![CDATA[".$porownywarki->produkty[$i]['cena_brutto_produktu']."]]></price>\n";
            $DoZapisaniaXML .= "    <category><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></category>\n";
            $DoZapisaniaXML .= "    <image><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></image>\n";
            $DoZapisaniaXML .= "    <availability>".$dostepnosc."</availability>\n";

            //dodatkowe pola do produktu START
            if ( count($porownywarki->produkty[$i]['pola']) > 0 ) {
                $DoZapisaniaXML .= "    <params>\n";
                foreach ( $porownywarki->produkty[$i]['pola'] as $key => $value ) {
                    $DoZapisaniaXML .= "        <parm_name><![CDATA[".$key."]]></parm_name>\n";
                    $DoZapisaniaXML .= "        <parm_value><![CDATA[".$value."]]></parm_value>\n";
                }
                $DoZapisaniaXML .= "    </params>\n";
            }
            //dodatkowe pola do produktu KONIEC
            $DoZapisaniaXML .= "</product>\n";

        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<offers>\n";
            $CoDoZapisania .= "<stat>\n";
            $CoDoZapisania .= "<num>".$_POST['ilosc_rekordow']."</num>\n";
            $CoDoZapisania .= "<ver>2.1</ver>\n";
            $CoDoZapisania .= "</stat>\n";

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
            $CoDoZapisania .= "</offers>";
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