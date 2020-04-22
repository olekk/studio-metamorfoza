<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
//$prot = new Dostep($db);

if (Sesje::TokenSpr()) {

  //if (!isset($_POST['limit'])) {

      $allegro = new Allegro(true);

      $offset = (int)$_POST['limit'];

      $tablica_pol = $allegro->doGetSellFormFieldsExtLimit($offset, '500');

      if ( is_array($tablica_pol['sell-form-fields']) ) {

          $form_object = $tablica_pol['sell-form-fields'];

          for ($i = 0, $c = count($form_object); $i < $c; $i++) {

            $form_array  = Funkcje::object2array($form_object[$i]);

            $pola = array();
            while (list($key, $value) = each($form_array)) {
              $pola[] = array(str_replace("-", "_", $key),$value);
            }

            $db->insert_query('allegro_fields' , $pola);
            unset($pola);
          }

          echo 'OK';

      } else {

          echo 'BLAD';
      }
      
  //}
}

?>