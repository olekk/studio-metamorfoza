<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {

      $id_ponowne_allegro = (int)$_GET["id_poz"];
      include('allegro_duplikuj_aukcje_akcja.php');
      unset($id_ponowne_allegro);

      if ( is_array($rezultat) && count($rezultat['items-sell-again']) > 0) {
      
        $db->insert_query('allegro_auctions' , $pola);
        $wynik = '<div id="zaimportowano">Oferta czeka na ponowne wystawienie</div>';
        
      } else {
      
        if ( isset($rezultat['items-sell-not-found']) && count($rezultat['items-sell-not-found']) > 0 ) {
        
          foreach ( $rezultat['items-sell-not-found'] as $val ) {
            $wynik = '<div class="ostrzezenie">Aukcja nie została odnaleziona: ' . $val . '<br /></div>';
          }
          
        }
        
      }

      unset($pola);

    } else {
    
      $wynik = Okienka::pokazOkno('Błąd', 'Nie jesteś zalogowany w serwisie Allegro', 'allegro/allegro_logowanie.php');
      
    }
    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Obsługa aukcji</div>
    <div id="cont">

      <div class="poleForm">
      
        <div class="naglowek">Ponowne wystawienie aukcji</div>
        
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