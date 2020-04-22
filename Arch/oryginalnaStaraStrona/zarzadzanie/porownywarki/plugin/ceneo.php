<?php
chdir('../../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) {

    $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit']);

    if ( count($porownywarki->produkty) > 0 ) {

        $tablica_dostepnosci = Porownywarki::TablicaDostepnosci( $_POST['plugin'] );

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

            if ( $porownywarki->produkty[$i]['ilosc_produktu'] > 0) {
                $ilosc = ' stock="'.round($porownywarki->produkty[$i]['ilosc_produktu'], 0).'"';
            } else {
                $ilosc = '';
            }

            if ( $porownywarki->produkty[$i]['waga_produktu'] > 0) {
                $waga = ' weight="'.round($porownywarki->produkty[$i]['waga_produktu'], 2).'"';
            } else {
                $waga = '';
            }


            $DoZapisaniaXML .= "<o id=\"".$porownywarki->produkty[$i]['id_produktu']."\" url=\"".$porownywarki->produkty[$i]['url_produktu']."\" price=\"".$porownywarki->produkty[$i]['cena_brutto_produktu']."\" avail=\"".$dostepnosc."\" set=\"0\" ".$waga." ".$ilosc." >\n";

            $DoZapisaniaXML .= "<cat>\n";
            $DoZapisaniaXML .= "    <![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]>\n";
            $DoZapisaniaXML .= "</cat>\n";
            $DoZapisaniaXML .= "<name>\n";
            $DoZapisaniaXML .= "    <![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]>\n";
            $DoZapisaniaXML .= "</name>\n";
            $DoZapisaniaXML .= "<imgs>\n";
            $DoZapisaniaXML .= "    <main url=\"".$porownywarki->produkty[$i]['zdjecie_produktu']."\" />\n";
            $DoZapisaniaXML .= "</imgs>\n";
            $DoZapisaniaXML .= "<desc>\n";
            $DoZapisaniaXML .= "    <![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]>\n";
            $DoZapisaniaXML .= "</desc>\n";
            $DoZapisaniaXML .= "<attrs>\n";
            $DoZapisaniaXML .= "    <a name=\"Producent\">\n";
            $DoZapisaniaXML .= "        <![CDATA[".$porownywarki->produkty[$i]['producent_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </a>\n";
            $DoZapisaniaXML .= "    <a name=\"Kod_producenta\">\n";
            $DoZapisaniaXML .= "        <![CDATA[".$porownywarki->produkty[$i]['kod_producenta_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </a>\n";
            $DoZapisaniaXML .= "    <a name=\"EAN\">\n";
            $DoZapisaniaXML .= "        <![CDATA[".$porownywarki->produkty[$i]['numer_ean_produktu']."]]>\n";
            $DoZapisaniaXML .= "    </a>\n";

            //dodatkowe pola do produktu START
            if ( count($porownywarki->produkty[$i]['pola']) > 0 ) {
                foreach ( $porownywarki->produkty[$i]['pola'] as $key => $value ) {
                    $DoZapisaniaXML .= "    <a name=\"".$key."\">\n";
                    $DoZapisaniaXML .= "        <![CDATA[".$value."]]>\n";
                    $DoZapisaniaXML .= "    </a>\n";
                }
            }
            //dodatkowe pola do produktu KONIEC

            $DoZapisaniaXML .= "</attrs>\n";
            $DoZapisaniaXML .= "</o>\n";


        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            $CoDoZapisania .= "<offers xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1\">\n";
            $CoDoZapisania .= "<group name=\"other\">\n";

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
            $CoDoZapisania .= "</group>\n";
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