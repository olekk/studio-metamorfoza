<?php
$warunki_szukania = '';
// jezeli jest szukanie
if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
    $szukana_wartosc = $filtr->process($_GET['szukaj']);
    $warunki_szukania = " and pd.products_name like '%".$szukana_wartosc."%'";
    unset($szukana_wartosc);
}

// jezeli jest nr kat lub id
if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {
    $szukana_wartosc = $filtr->process($_GET['nrkat']);
    $warunki_szukania = " and (p.products_model like '%".$szukana_wartosc."%' or p.products_man_code like '%".$szukana_wartosc."%' or p.products_id = ".(int)$szukana_wartosc.")";
    unset($szukana_wartosc);
}

// jezeli jest wybrany status
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $warunki_szukania .= " and p.products_status = '".(($_GET['status'] == 'tak') ? '1' : '0')."'";
}    

// ilosc magazynu
if (isset($_GET['ilosc_od'])) {
    $ilosc = $filtr->process((float)$_GET['ilosc_od']);
    $warunki_szukania .= " and p.products_quantity >= '".$ilosc."'";
    unset($ilosc);
}
if (isset($_GET['ilosc_do'])) {
    $ilosc = $filtr->process((float)$_GET['ilosc_do']);
    $warunki_szukania .= " and p.products_quantity <= '".$ilosc."'";
    unset($ilosc);
}

// data dodania
if ( isset($_GET['szukaj_data_dodania_od']) && $_GET['szukaj_data_dodania_od'] != '' ) {
    $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_dodania_od'] . ' 00:00:00')));
    $warunki_szukania .= " and p.products_date_added >= '".$szukana_wartosc."'";
}
if ( isset($_GET['szukaj_data_dodania_do']) && $_GET['szukaj_data_dodania_do'] != '' ) {
    $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_dodania_do'] . ' 23:59:59')));
    $warunki_szukania .= " and p.products_date_added <= '".$szukana_wartosc."'";
}

// jezeli jest wybrana dostepnosc
if (isset($_GET['dostep']) && (int)$_GET['dostep'] > 0) {
    $id_dostepnosci = $filtr->process((int)$_GET['dostep']);
    $warunki_szukania .= " and p.products_availability_id = '".$id_dostepnosci."'";
    unset($id_dostepnosci);
}    

// jezeli jest wybrana wysylka
if (isset($_GET['wysylka']) && (int)$_GET['wysylka'] > 0) {
    $id_wysylka = $filtr->process((int)$_GET['wysylka']);
    $warunki_szukania .= " and p.products_shipping_time_id = '".$id_wysylka."'";
    unset($id_wysylka);
}     

// jezeli jest zakres cen
if (isset($_GET['cena_od']) && (float)$_GET['cena_od'] > 0) {
    $cena = $filtr->process((float)$_GET['cena_od']);
    $warunki_szukania .= " and p.products_price_tax >= '".$cena."'";
    unset($cena);
}
if (isset($_GET['cena_do']) && (float)$_GET['cena_do'] > 0) {
    $cena = $filtr->process((float)$_GET['cena_do']);
    $warunki_szukania .= " and p.products_price_tax <= '".$cena."'";
    unset($cena);
}    

// jezeli jest wybrana kategoria
if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
    $id_kategorii = $filtr->process((int)$_GET['kategoria_id']);
    $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
    unset($id_kategorii);
}

// jezeli jest wybrany producent
if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
    $id_producenta = $filtr->process((int)$_GET['producent']);
    $warunki_szukania .= " and p.manufacturers_id = '".$id_producenta."'";
    unset($id_producenta);
} 

if ( $warunki_szukania != '' ) {
  $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
}
 ?>