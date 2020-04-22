<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  switch ( $_GET['akcja']) {

    case 'usun':

      $PlikDoUsuniecia = KATALOG_SKLEPU . 'xml/DHL/' . $_GET['przesylka'];

      if ( is_file($PlikDoUsuniecia) ) {
        @unlink($PlikDoUsuniecia);
      }

      $db->delete_query('orders_shipping' , " orders_id = '".$filtr->process($_GET["id_poz"])."' AND orders_shipping_number = '".$_GET['przesylka']."'");

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_GET["id_poz"].'&zakladka='.$filtr->process($_GET["zakladka"]));
      break;

    case 'pobierz':

      $PlikDoPobrania = KATALOG_SKLEPU . 'xml/DHL/' . $_GET['przesylka'];

      header('Content-type: text/xml');
      header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'"');
      readfile($PlikDoPobrania);
      exit();
      break;

  }
  
}

?>