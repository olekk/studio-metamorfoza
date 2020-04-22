<?php
// waluta
if (isset($TablicaDane['Waluta']) && trim($TablicaDane['Waluta']) != '') {
    //
    $zapytanieWaluta = "select currencies_id, code from currencies where code = '" . addslashes($filtr->process($TablicaDane['Waluta'])) . "'";
    $sql = $db->open_query($zapytanieWaluta); 
    if ((int)$db->ile_rekordow($sql) > 0) {
        //
        $info = $sql->fetch_assoc();
        $pola[] = array('products_currencies_id',$info['currencies_id']);
        //   
        $JestPodatek = true;
        $db->close_query($sql);
    }
} else {
    //
    if ($CzyDodawanie == true) {
        //
        // jezeli nie ma waluty a jest dodawanie produktu przyjmuje domyslna
        $pola[] = array('products_currencies_id',$domyslna_waluta['id']);
        //
    }
    //
}    
?>