<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    unset($_SESSION['hash']);
    unset($_SESSION['allegro_user_id']);
    unset($_SESSION['allegro_user_login']);

    $allegro = new Allegro(true);
    if (isset($_GET['wyloguj']) && $_GET['wyloguj'] == 'ok') {
    
      unset($_SESSION['hash']);
      unset($_SESSION['allegro_user_id']);
      unset($_SESSION['allegro_user_login']);
      
      if ( isset($_GET['strona']) && $_GET['strona'] != '' ) {
      
        if ( $_GET['strona'] == 'konfiguracja_zakladki' ) {
             $link = '/zarzadzanie/integracje/konfiguracja_zakladki.php';
           } else {
             $link = $_GET['strona'].'.php';
        }
        
      } else {
      
        $link = 'allegro_logowanie.php';
        
      }
      
      echo $allegro->PokazBlad('Komunikat', 'Użytkownik został wylogowany z serwisu Allegro', $link);
      exit;
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $api_wersja = $allegro->doGetSysStatusLocal('1');
      
      $logowanie = $allegro->doLogin($_POST['login'], $_POST['password'], $api_wersja);

      if (is_array($logowanie) && $logowanie["session-handle-part"] != "") {

        $hash = $logowanie["session-handle-part"] . ':' . time();
        $_SESSION['hash']                   = $hash;
        $_SESSION['allegro_user_id']        = $logowanie["user-id"];
        $_SESSION['allegro_user_login']     = $_POST['login'];

        if ( isset($_POST["id"]) && $_POST["id"] != '' && isset($_POST['next']) && $_POST['next'] == 'produkty' ) {
        
          Funkcje::PrzekierowanieURL('allegro_wystaw_aukcje.php?id_poz='.(int)$_POST["id"].Funkcje::Zwroc_Get(array('id_poz','x','y'),true));
          
        } else {
        
          if ( isset($_POST['strona']) && $_POST['strona'] != '' ) {
          
            if ( $_POST['strona'] == 'konfiguracja_zakladki' ) {
          
                Funkcje::PrzekierowanieURL('allegro_komentarze.php');
              
              } else {
              
                Funkcje::PrzekierowanieURL($_POST['strona'].'.php');
                
            }
            
          } else {
          
            Funkcje::PrzekierowanieURL('index.php');
            
          }
        }
      }
      //
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Logowanie do serwisu Allegro</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Zaloguj</div>

        <!-- Skrypt do walidacji formularza -->
        <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#allegroForm").validate();
          });
        //]]>
        </script>

        <form action="allegro/allegro_logowanie.php<?php echo Funkcje::Zwroc_Get(array('next')); ?>" method="post" id="allegroForm" class="cmxform"> 
          <div class="pozycja_edytowana">  

            <div class="info_content allegro_logowanie">
            
              <input type="hidden" name="akcja" value="zapisz" />
              <?php if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) { ?>
              <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
              <?php } ?>
              <?php if ( isset($_GET['strona']) && $_GET['strona'] != '' ) { 
              ?>
              <input type="hidden" name="strona" value="<?php echo $filtr->process($_GET['strona']); ?>" />
              <?php } ?>

              <p><span class="ostrzezenie">Ze względów bezpieczeństwa hasło do Allegro nie jest zapisywane w bazie tylko w sesji użytkownika w postaci zaszyfrowanej.</span></p>

              <br />
              
              <p>
                <label class="required">Login:</label>
                <input type="text" name="login" id="login" value="" size="35" class="required" />
              </p>

              <p>
                <label class="required">Hasło:</label>
                <input type="password" name="password" id="password" value="" size="35" class="required" />
              </p>

            </div>

            <div class="przyciski_dolne" style="padding-left:0px">
              <input type="submit" class="przyciskNon" value="Zaloguj" />
            </div>                 

          </div>
        </form>

      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
