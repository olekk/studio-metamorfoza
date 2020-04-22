<?php
chdir('../../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plugin']) && !empty($_POST['plugin']) && isset($_POST['offset']) && (int)$_POST['offset'] > -1 && Sesje::TokenSpr()) {

    $porownywarki = new Porownywarki($_POST['plugin'], $_POST['offset'], $_POST['limit'], '>');

    if ( count($porownywarki->produkty) > 0 ) {

        $tablica_dostepnosci = $porownywarki->TablicaDostepnosci($_POST['plugin']);

        $tablica_dostepnosci_tmp = $porownywarki->TablicaDostepnosciNiezdefiniowanych($_POST['plugin']);
        foreach ( $tablica_dostepnosci_tmp as $rekord ) {
            $dostepnosc_tmp[$rekord['id']] = $rekord['text'];
        }
        unset($tablica_dostepnosci_tmp);

        if ( $porownywarki->stan_domyslny == '1' ) {
            $stan_produktu = 'new';
        } elseif ( $porownywarki->stan_domyslny == '2' ) {
            $stan_produktu = 'used';
        } elseif ( $porownywarki->stan_domyslny == '3' ) {
            $stan_produktu = 'refurbished';
        }

        $CoDoZapisania = '';
        $DoZapisaniaXML = '';
        $plik = KATALOG_SKLEPU . 'xml/' . $filtr->process($_POST['plugin']) . '.xml';

        // uchwyt pliku, otwarcie do dopisania
        $fp = fopen($plik, "a");
        // blokada pliku do zapisu
        flock($fp, 2);

        //dane do zapisania do pliku START

        for ( $i = 0, $c = count($porownywarki->produkty); $i < $c; $i++ ) {
        
            if ( $porownywarki->produkty[$i]['kategoria_google'] != '' ) {

                //Pobranie i sprawdzenie ustawienia dostepnosci produktu - specyficzne dla porownywarki
                $dostepnosc = $porownywarki->produkty[$i]['dostepnosc_produktu'];

                if ( $porownywarki->produkty[$i]['dostepnosc_produktu'] != '0' && $porownywarki->produkty[$i]['dostepnosc_produktu'] != '') {
                    $dostepnosc = $tablica_dostepnosci[$porownywarki->produkty[$i]['dostepnosc_produktu']];
                } else {
                    $dostepnosc = $tablica_dostepnosci[$porownywarki->dotepnosc_domyslna];
                }

                $DoZapisaniaXML .= "<item>\n";

                $DoZapisaniaXML .= "    <g:id>".$porownywarki->produkty[$i]['id_produktu']."</g:id>\n";
                $DoZapisaniaXML .= "    <title><![CDATA[".$porownywarki->produkty[$i]['nazwa_produktu']."]]></title>\n";
                $DoZapisaniaXML .= "    <description><![CDATA[".$porownywarki->produkty[$i]['opis_produktu']."]]></description>\n";
                $DoZapisaniaXML .= "    <g:google_product_category><![CDATA[".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['kategoria_google'])."]]></g:google_product_category>\n";
                $DoZapisaniaXML .= "    <g:product_type><![CDATA[".$porownywarki->produkty[$i]['kategoria_produktu']."]]></g:product_type>\n";
                $DoZapisaniaXML .= "    <link><![CDATA[".$porownywarki->produkty[$i]['url_produktu']."]]></link>\n";
                $DoZapisaniaXML .= "    <g:image_link><![CDATA[".$porownywarki->produkty[$i]['zdjecie_produktu']."]]></g:image_link>\n";
                $DoZapisaniaXML .= "    <g:price>".str_replace('zł', 'PLN', $waluty->FormatujCene($porownywarki->produkty[$i]['cena_brutto_produktu'], false))."</g:price>\n";
                $DoZapisaniaXML .= "    <g:brand>".$porownywarki->produkty[$i]['producent_produktu']."</g:brand>\n";

                $DoZapisaniaXML .= "    <g:shipping_weight>".round($porownywarki->produkty[$i]['waga_produktu'], 2)." kg</g:shipping_weight>\n";

                $DoZapisaniaXML .= "    <g:availability>".$dostepnosc_tmp[$dostepnosc]."</g:availability>\n";
                $DoZapisaniaXML .= "    <g:condition>".$stan_produktu."</g:condition>\n";

                $DoZapisaniaXML .= "</item>\n";

            }

        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania  = "<?xml version=\"1.0\"?>\n";
            $CoDoZapisania .= "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">\n";
            $CoDoZapisania .= "<channel>\n";
            $CoDoZapisania .= "<title>".DANE_NAZWA_FIRMY_PELNA."</title>\n";
            $CoDoZapisania .= "<link>".ADRES_URL_SKLEPU."</link>\n";
            $CoDoZapisania .= "<description>Google Shopping Feed</description>\n";

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
            $CoDoZapisania .= "</channel>";
            $CoDoZapisania .= "</rss>";
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