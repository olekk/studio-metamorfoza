<?php

$TablicaProducts = array();
$TablicaProducts[] = array('products_model','Nr_katalogowy');
$TablicaProducts[] = array('products_man_code','Kod_producenta');
$TablicaProducts[] = array('products_id_private','Id_produktu_magazyn');
$TablicaProducts[] = array('products_ean','Kod_ean');
$TablicaProducts[] = array('products_weight','Waga');
$TablicaProducts[] = array('products_quantity','Ilosc_produktow');
$TablicaProducts[] = array('products_minorder','Min_ilosc_zakupu');
$TablicaProducts[] = array('products_maxorder','Max_ilosc_zakupu');
$TablicaProducts[] = array('products_quantity_order','Przyrost_ilosci');
$TablicaProducts[] = array('products_pack_type','Gabaryt');
$TablicaProducts[] = array('products_price_tax','Cena_brutto');
$TablicaProducts[] = array('products_old_price','Cena_poprzednia');
$TablicaProducts[] = array('products_retail_price','Cena_katalogowa');
$TablicaProducts[] = array('products_adminnotes','Notatki_produktu');
$TablicaProducts[] = array('products_date_available','Data_dostepnosci');
if ( ILOSC_CEN > 1 ) {
    $TablicaProducts[] = array('products_price_tax_2','Cena_brutto_2');
    $TablicaProducts[] = array('products_old_price_2','Cena_poprzednia_2');
    $TablicaProducts[] = array('products_retail_price_2','Cena_katalogowa_2');
}
if ( ILOSC_CEN > 2 ) {
    $TablicaProducts[] = array('products_price_tax_3','Cena_brutto_3');
    $TablicaProducts[] = array('products_old_price_3','Cena_poprzednia_3');
    $TablicaProducts[] = array('products_retail_price_3','Cena_katalogowa_3');
}
if ( ILOSC_CEN > 3 ) {
    $TablicaProducts[] = array('products_price_tax_4','Cena_brutto_4');
    $TablicaProducts[] = array('products_old_price_4','Cena_poprzednia_4');
    $TablicaProducts[] = array('products_retail_price_4','Cena_katalogowa_4');
}
if ( ILOSC_CEN > 4 ) {
    $TablicaProducts[] = array('products_price_tax_5','Cena_brutto_5');
    $TablicaProducts[] = array('products_old_price_5','Cena_poprzednia_5');
    $TablicaProducts[] = array('products_retail_price_5','Cena_katalogowa_5');
}
if ( ILOSC_CEN > 5 ) {
    $TablicaProducts[] = array('products_price_tax_6','Cena_brutto_6');
    $TablicaProducts[] = array('products_old_price_6','Cena_poprzednia_6');
    $TablicaProducts[] = array('products_retail_price_6','Cena_katalogowa_6');
}
if ( ILOSC_CEN > 6 ) {
    $TablicaProducts[] = array('products_price_tax_7','Cena_brutto_7');
    $TablicaProducts[] = array('products_old_price_7','Cena_poprzednia_7');
    $TablicaProducts[] = array('products_retail_price_7','Cena_katalogowa_7');
}
if ( ILOSC_CEN > 7 ) {
    $TablicaProducts[] = array('products_price_tax_8','Cena_brutto_8');
    $TablicaProducts[] = array('products_old_price_8','Cena_poprzednia_8');
    $TablicaProducts[] = array('products_retail_price_8','Cena_katalogowa_8');
}
if ( ILOSC_CEN > 8 ) {
    $TablicaProducts[] = array('products_price_tax_9','Cena_brutto_9');
    $TablicaProducts[] = array('products_old_price_9','Cena_poprzednia_9');
    $TablicaProducts[] = array('products_retail_price_9','Cena_katalogowa_9');
}
if ( ILOSC_CEN > 9 ) {
    $TablicaProducts[] = array('products_price_tax_10','Cena_brutto_10');
    $TablicaProducts[] = array('products_old_price_10','Cena_poprzednia_10');
    $TablicaProducts[] = array('products_retail_price_10','Cena_katalogowa_10');
}
$TablicaProducts[] = array('new_status','Nowosc');
$TablicaProducts[] = array('star_status','Nasz_hit');
$TablicaProducts[] = array('featured_status','Polecany');
$TablicaProducts[] = array('specials_status','Promocja');
$TablicaProducts[] = array('export_status','Do_porownywarek');
$TablicaProducts[] = array('products_make_an_offer','Negocjacja');
$TablicaProducts[] = array('free_shipping_status','Darmowa_dostawa');
$TablicaProducts[] = array('products_image','Zdjecie_glowne');
$TablicaProducts[] = array('products_image_description','Zdjecie_glowne_opis');
$TablicaProducts[] = array('products_status','Status');

$TablicaProductsDescription = array();
$TablicaProductsDescription[] = array('products_name','Nazwa_produktu');
$TablicaProductsDescription[] = array('products_name_info','Dodatkowa_nazwa_produktu');
$TablicaProductsDescription[] = array('products_description','Opis');
$TablicaProductsDescription[] = array('products_short_description','Opis_krotki');
$TablicaProductsDescription[] = array('products_meta_title_tag','Meta_tytul');
$TablicaProductsDescription[] = array('products_meta_desc_tag','Meta_opis');
$TablicaProductsDescription[] = array('products_meta_keywords_tag','Meta_slowa');
        
?>