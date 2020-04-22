<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {

      $allegro = new Allegro(true, true);

      $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '".(int)$_GET["id_poz"]."'";
      $sql = $db->open_query($zapytanie);

      if ( $db->ile_rekordow($sql) > 0 ) {
      
        $info = $sql->fetch_assoc();
      
        $id_aukcji = floatval($info['auction_id']);

        $pola = array(
                array('auction_quantity',floor($_GET['ilosc'])));

        $rezultat = $allegro->doChangeQuantityItem( $id_aukcji, floor($_GET['ilosc']) );

        if ( is_array($rezultat) && count($rezultat['item-quantity-left']) > 0) {
        
          $db->update_query('allegro_auctions' , $pola, " allegro_id = '".(int)$_GET["id_poz"]."'");
          
          unset($pola);
          $wynik = '<div id="zaimportowano">Ilość przedmiotów na aukcji została zaktualizowana</div>';
          
        } else {
        
          if ( count($rezultat['items-sell-not-found']) > 0 ) {
          
            foreach ( $rezultat['items-sell-not-found'] as $val ) {
              $wynik = '<div class="ostrzezenie">Aukcja nie została odnaleziona: ' . $val . '<br /></div>';
            }
            
          }
          
        }
        
        unset($info);
        
      }
      
      $db->close_query($sql);
      unset($zapytanie);      
      
      unset($allegro);

    } else {
    
      $wynik = Okienka::pokazOkno('Błąd', 'Nie jesteś zalogowany w serwisie Allegro', 'allegro/allegro_logowanie.php');
      
    }
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Obsługa aukcji</div>
    <div id="cont">

      <div class="poleForm">
      
        <div class="naglowek">Aktualizacja ilości przedmiotów</div>
        
        <div class="pozycja_edytowana">
          <?php
          if ( $wynik != '' ) {
            echo $wynik;
          }
          ?>
        </div>

        <div class="przyciski_dolne" id="przyciski" style="padding-left:0px;">
          <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
        </div>                    

      </div>
      
    </div>
    
    <?php
    include('stopka.inc.php');

}