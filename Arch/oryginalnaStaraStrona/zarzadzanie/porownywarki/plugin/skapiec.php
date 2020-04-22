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

            $DoZapisaniaXML .= "     <item>\n";
            $DoZapisaniaXML .= "     <compid>".$porownywarki->produkty[$i]['id_produktu']."</compid>\n";
            $DoZapisaniaXML .= "     <url>".$porownywarki->produkty[$i]['url_produktu']."</url>\n";
            $DoZapisaniaXML .= "     <vendor>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['producent_produktu'])."</vendor>\n";
            $DoZapisaniaXML .= "     <model>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['numer_katalogowy_produktu'])."</model>\n";
            $DoZapisaniaXML .= "     <desc>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['nazwa_produktu'])."</desc>\n";
            $DoZapisaniaXML .= "     <desclong>".Porownywarki::TekstZamienEncje($porownywarki->produkty[$i]['opis_produktu'])."</desclong>\n";
            $DoZapisaniaXML .= "     <price>".$porownywarki->produkty[$i]['cena_brutto_produktu']."</price>\n";
            $DoZapisaniaXML .= "     <catid>".$porownywarki->produkty[$i]['kategoria_produktu_id']."</catid>\n";
            $DoZapisaniaXML .= "     <fotb>".$porownywarki->produkty[$i]['zdjecie_produktu']."</fotb>\n";
            $DoZapisaniaXML .= "     </item>\n";

        }
        //dane do zapisania do pliku END

        // jezeli poczatek pliku
        if ((int)$_POST['offset'] == 0) {
            ///
            $CoDoZapisania    = "<?xml version=\"1.0\"?>\n";
            $CoDoZapisania .= "<XMLDATA>\n";
            $CoDoZapisania .= "     <version>12</version>\n";
            $CoDoZapisania .= "     <header>\n";
            $CoDoZapisania .= "     <name>".DANE_NAZWA_FIRMY_PELNA."</name>\n";
            $CoDoZapisania .= "     <www>".ADRES_URL_SKLEPU."</www>\n";
            $CoDoZapisania .= "     <time>".date('Y-m-d',time())."</time>\n";
            $CoDoZapisania .= "     </header>\n";
            $CoDoZapisania .= "     <category>\n";

            //wydrukowanie listy kategorii
            $zapytanie_kategorie = "SELECT c.categories_id, c.parent_id, cd.categories_name FROM categories c, categories_description cd WHERE cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' AND cd.categories_id = c.categories_id ";

            if ( $porownywarki->typ_eksportu == '2' ) {
                $zapytanie_kategorie .= " AND c.categories_id IN (".$porownywarki->kategorie_do_eksportu.")";
            }

            $sql_kategorie = $db->open_query($zapytanie_kategorie);

            while($info_kategorie = $sql_kategorie->fetch_assoc()) {
                $CoDoZapisania .= "     <catitem>\n";
                $CoDoZapisania .= "     <catid>".$info_kategorie['categories_id']."</catid>\n";
                $CoDoZapisania .= "     <parentid>".$info_kategorie['parent_id']."</parentid>\n";
                $CoDoZapisania .= "     <catname>".Porownywarki::TekstZamienEncje($info_kategorie['categories_name'])."</catname>\n";
                $CoDoZapisania .= "     </catitem>\n";
            }
            $CoDoZapisania .= "     </category>\n";
            $CoDoZapisania .= "     <data>\n";


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
            $CoDoZapisania .= "     </data>\n";
            $CoDoZapisania .= "</XMLDATA>\n";
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