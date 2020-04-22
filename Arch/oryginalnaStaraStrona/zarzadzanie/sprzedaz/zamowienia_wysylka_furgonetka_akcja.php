<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $apiKurier       = new FurgonetkaApi();

  if ( !isset($_SESSION['furgonetkahash']) ) {
      $apiKurier->doLogin();
  } else {
      $hash = explode(':', $_SESSION['furgonetkahash']);
      if ( time() - $hash[1] > 600 ) {
          unset($_SESSION['furgonetkahash']);
          $apiKurier->doLogin();
      }
      unset($hash);
  }

  switch ( $_GET['akcja']) {

    case 'zamow':

      $blad = '';
      $params['packages_ids'] = array($_GET['przesylka']);

      $wynik = $apiKurier->doPackagesOrder( $params );

      $status = $wynik->getName();


      if ($status == 'success') {

           $pola = array(
                   array('orders_shipping_number',$wynik->packages->node->package_no),
                   array('orders_shipping_status','1'),
                   array('orders_shipping_date_modified','now()')
           );

           $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."' AND orders_id = '".(int)$_GET["id_poz"]."'");

           unset($pola);

           include('naglowek.inc.php');
           echo Okienka::pokazOkno('Zamówienie przesyłki', $wynik->packages->node->package_no . ' - została zamówiona', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
           include('stopka.inc.php');

      } elseif ($status == 'error') {

          foreach($wynik->error as $error) {
            if(isset($error->field)) {
                $blad .= $error->field .': ';
            }
            $blad .= $error->message;
          }

          include('naglowek.inc.php');
          echo Okienka::pokazOkno('Błąd', $blad, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
          include('stopka.inc.php');
      }

      break;

    case 'usun':

      $blad = '';
      $params['package_id'] = $_GET['przesylka'];

      $wynik = $apiKurier->doPackageDelete( $params );

      $status = $wynik->getName();

      if ($status == 'success') {

           $db->delete_query('orders_shipping' , " orders_shipping_number = '".$filtr->process($_GET["przesylka"])."' AND orders_id = '".(int)$_GET["id_poz"]."'");  

           include('naglowek.inc.php');
           echo Okienka::pokazOkno('Usunięcie przesyłki', $_GET['przesylka'] . ' - została usunięta', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
           include('stopka.inc.php');

      } elseif ($status == 'error') {
          foreach($wynik->error as $error) {
            if(isset($error->field)) {
                $blad .= $error->field .': ';
            }
            $blad .= $error->message;
          }
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $blad, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');
      }

      break;

    case 'etykieta':

      $blad = '';
      $params['package_no'] = $_GET['przesylka'];
      $params['no_docs'] = false;

      $wynik = $apiKurier->doPackageDetails( $params );

      $status = $wynik->getName();

      if ($status == 'success') {

          if(!empty($wynik->label)) {
              header('Content-type: application/pdf');
              header('Content-Disposition: attachment; filename="'.$_GET['przesylka'].'.pdf"');
              echo base64_decode($wynik->label->base64);
          } else {
              include('naglowek.inc.php');
              echo Okienka::pokazOkno('Błąd', 'Brak danych w serwisie Furgonetka - być może paczka jest starsza niż 30 dni.', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
              include('stopka.inc.php');
          }

      } elseif ($status == 'error') {
          foreach($wynik->error as $error) {
            if(isset($error->field)) {
                $blad .= $error->field .': ';
            }
            $blad .= $error->message;
          }
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $blad, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');
      }

      break;

    case 'tracking':

      $blad = '';
      $params['package_no'] = $_GET['przesylka'];
      $params['no_docs'] = true;
      //$params['service'] = $_GET['serwis'];

      $wynik = $apiKurier->doPackageDetails( $params );

      $status = $wynik->getName();

      if ($status == 'success') {
          if ( isset($wynik->tracking->node['0']) && $wynik->tracking->node['0']->description != '' ) {

            $pola = array(
                   array('orders_shipping_status',$wynik->tracking->node['0']->description),
                   array('orders_shipping_date_modified','now()')
            );

            $db->update_query('orders_shipping' , $pola, " orders_shipping_number = '".$_GET["przesylka"]."' AND orders_id = '".(int)$_GET["id_poz"]."'");

            unset($pola);

            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Szczegóły przesyłki', 'Aktualny status przesyłki : ' . $wynik->tracking->node['0']->description, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
            include('stopka.inc.php');

          } else {

            include('naglowek.inc.php');
            echo Okienka::pokazOkno('Szczegóły przesyłki', 'Brak danych o bieżącym statusie w serwisie Furgonetka', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
            include('stopka.inc.php');

          }

      } elseif ($status == 'error') {
          foreach($wynik->error as $error) {
            if(isset($error->field)) {
                $blad .= $error->field .': ';
            }
            $blad .= $error->message;
          }
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $blad, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');
      }

      break;

    /*
    case 'doladuj':

      $blad = '';
      $params['amount'] = 100;

      $wynik = $apiKurier->doPay( $params );

      $status = $wynik->getName();

      if ($status == 'success') {

           include('naglowek.inc.php');
           echo Okienka::pokazOkno('Konto zostało doładowane', 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
           include('stopka.inc.php');

      } elseif ($status == 'error') {
          foreach($wynik->error as $error) {
            if(isset($error->field)) {
                $blad .= $error->field .': ';
            }
            $blad .= $error->message;
          }
        include('naglowek.inc.php');
        echo Okienka::pokazOkno('Błąd', $blad, 'sprzedaz/zamowienia_szczegoly.php'.Funkcje::Zwroc_Get(array('przesylka','x','y'))); 
        include('stopka.inc.php');
      }

      break;
      */

}

}

?>