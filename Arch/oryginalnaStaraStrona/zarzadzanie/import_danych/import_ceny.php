<?php              
// podatek vat i aktualizacja ceny netto i brutto
$JestPodatek = false;
if (isset($TablicaDane['Podatek_Vat']) && (int)$TablicaDane['Podatek_Vat'] > 0) {
    //
    $zapytaniePodatek = "select tax_rates_id, tax_rate from tax_rates where tax_rate = '" . (int)$TablicaDane['Podatek_Vat'] . "'";
    $sql = $db->open_query($zapytaniePodatek); 
    if ((int)$db->ile_rekordow($sql) > 0) {
        //
        $info = $sql->fetch_assoc();
        $pola[] = array('products_tax_class_id',$info['tax_rates_id']);
        //   
        $JestPodatek = true;
        $db->close_query($sql);
    }
}

// jezeli jest dodawanie produktu
if ($CzyDodawanie == true && $JestPodatek == false) {
    //
    // jezeli nie ma podatku przyjmuje domyslny
    $zapytaniePodatek = "select tax_rates_id, tax_rate from tax_rates where tax_default = '1'";
    $sqlc = $db->open_query($zapytaniePodatek);
    //
    $info = $sqlc->fetch_assoc();
    $pola[] = array('products_tax_class_id',$info['tax_rates_id']);
    //
    $db->close_query($sqlc);
    //
  } else if ($CzyDodawanie == false && $JestPodatek == false) {
    //
    // jezeli nie ma podatku w pliku csv a jest aktualizacja to szuka jaki podatek ma przypisany produkt 
    $zapytaniePodatek = "select products_id, products_tax_class_id from products where products_id = '" . $id_aktualizowanej_pozycji . "'";
    $sqlc = $db->open_query($zapytaniePodatek);
    $infe = $sqlc->fetch_assoc();
    $db->close_query($sqlc);
    //
    $zapytaniePodatek = "select tax_rates_id, tax_rate from tax_rates where tax_rates_id = '" . (int)$infe['products_tax_class_id'] . "'";
    $sqlc = $db->open_query($zapytaniePodatek);
    //
    $info = $sqlc->fetch_assoc();
    //
    $db->close_query($sqlc);
    unset($infe);    
    //
}


// sluzy do przeliczenia na kwote netto i podatego
$wartoscPodatkuDlaProduktu = $info['tax_rate'];

// przeliczanie ceny na netto i vat
if (isset($TablicaDane['Cena_brutto']) && (float)$TablicaDane['Cena_brutto'] > 0) {
    //
    $netto = round( $TablicaDane['Cena_brutto'] / (1 + ($info['tax_rate']/100)), 2);
    $podatek = $TablicaDane['Cena_brutto'] - $netto;
    //
    $pola[] = array('products_price',$netto);
    $pola[] = array('products_tax',$podatek);
    //
    unset($netto, $podatek);
}
// 

// przeliczanie cen hurtowych od 1 do x na netto i vat
for ($w = 2; $w <= ILOSC_CEN ; $w++) {
    //
    if (isset($TablicaDane['Cena_brutto_'.$w]) && (float)$TablicaDane['Cena_brutto_'.$w] > 0) {
        //
        $netto = round( $TablicaDane['Cena_brutto_'.$w] / (1 + ($info['tax_rate']/100)), 2);
        $podatek = $TablicaDane['Cena_brutto_'.$w] - $netto;
        //
        $pola[] = array('products_price_'.$w,$netto);
        $pola[] = array('products_tax_'.$w,$podatek);
        //
        unset($netto, $podate);
    }
    // 
}
unset($info);       
//    
?>