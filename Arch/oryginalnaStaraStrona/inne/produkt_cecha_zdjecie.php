<?php
chdir('../'); 

if (isset($_POST['id']) && isset($_POST['cechy'])) {

    $PodzielId = explode('_', $_POST['id']);

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (!Sesje::TokenSpr() && (int)$PodzielId[1] > 0) {
        echo 'false';
        exit;
    }
    
    // szuka w bazie obrazka cechy
    $zapytanie = "select products_stock_image from products_stock where products_id = '" . (int)$PodzielId[1] . "' and products_stock_attributes = '" . substr(str_replace('x',',', $filtr->process($_POST['cechy'])),1) . "'";
    $sql = $db->open_query($zapytanie);    
    $info = $sql->fetch_assoc(); 
    
    if ( $info['products_stock_image'] != '' ) {
        //
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML( Funkcje::pokazObrazek($info['products_stock_image'], '', SZEROKOSC_MINIATUREK_KARTA_PRODUKTU, WYSOKOSC_MINIATUREK_KARTA_PRODUKTU, array(), 'class="Zdjecie" id="Foto1"', 'maly') );
        $xpath = new DOMXPath($doc);
        $imgs = $xpath->query("//img");
        for ($i=0; $i < $imgs->length; $i++) {
            $img = $imgs->item($i);
            $src = $img->getAttribute("src");
        }
        //
        echo json_encode( array("male" => $src, 
                                "srednie" => Funkcje::pokazObrazek($info['products_stock_image'], '', SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, array(), 'class="Zdjecie"', 'sredni'), 
                                "duze" => KATALOG_ZDJEC . '/' . $info['products_stock_image'] ) );
        //
    } else {
    
        $Produkt = new Produkt( (int)$PodzielId[1] );    
        //
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML( Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], SZEROKOSC_MINIATUREK_KARTA_PRODUKTU, WYSOKOSC_MINIATUREK_KARTA_PRODUKTU, array(), 'class="Zdjecie" id="Foto1"', 'maly') );
        $xpath = new DOMXPath($doc);
        $imgs = $xpath->query("//img");
        for ($i=0; $i < $imgs->length; $i++) {
            $img = $imgs->item($i);
            $src = $img->getAttribute("src");
        }
        //
        echo json_encode( array("male" => $src, 
                                "srednie" => Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], SZEROKOSC_OBRAZEK_SREDNI, WYSOKOSC_OBRAZEK_SREDNI, array(), 'class="Zdjecie"', 'sredni'), 
                                "duze" => KATALOG_ZDJEC . '/' . $Produkt->fotoGlowne['plik_zdjecia'] ) );
                                
        unset($Produkt);

    }
    
    $db->close_query($sql);   
    unset($info, $zapytanie);
    
}

?>