<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['id_poz'])) {
        $id_poz = $filtr->process($_GET['id_poz']);
       } else {
        $id_poz = 0;
    }
    if (!isset($_GET["product_id"])) {
        $_GET["product_id"] = 0;
    }    

    $db->delete_query('customers_basket' , " customers_id = '".$filtr->process($_GET["id_poz"])."' AND products_id = '".$filtr->process($_GET["product_id"])."'");

    if (isset($_GET['product_id'])) {
      unset($_GET["product_id"]);
    }

    Funkcje::PrzekierowanieURL('klienci_edytuj.php?id_poz='.(int)$id_poz.Funkcje::Zwroc_Wybrane_Get(array('zakladka'),true));
}
?>