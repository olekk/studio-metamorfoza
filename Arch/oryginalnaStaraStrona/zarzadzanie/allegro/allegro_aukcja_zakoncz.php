<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {

      $allegro = new Allegro(true, true);

      if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
      
          $wynik     = '';
          $komunikat = '';
          //
          $wynik = $allegro->doFinishItem( floatval($_POST['aukcja_id']), $_POST['odwolanie'], $_POST['powod'] );
          //
          if ( $wynik == '1' ) {
          
            $pola = array(
                    array('auction_status','2'));
                    
            $db->update_query('allegro_auctions' , $pola, " allegro_id = '".(int)$_POST["id"]."'");	
            unset($pola);

            Funkcje::PrzekierowanieURL('allegro_aukcje.php?id_poz='.(int)$_POST["id"].'');
            
          }
          
      }
      
      unset($allegro);

    } else {
    
      $allegro = new Allegro(true, true);
      echo $allegro->PokazBlad('Błąd', 'Nie jesteś zalogowany w serwisie Allegro', 'allegro/allegro_logowanie.php');
      
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    if (isset($_POST["id"]) ) $_GET['id_poz'] = $_POST["id"];
    ?>
    
    <div id="naglowek_cont">Obsługa aukcji</div>
    <div id="cont">
    
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
            $(document).ready(function() {

              $("#allegroForm").validate({
               rules: {
                  powod: {required: function() {var wynik = true; if ( $("input[name='odwolanie']:checked", "#allegroForm").val() == "0" ) { wynik = false; } return wynik; }}
               }
              });

            });
          //]]>
          </script>                    

          <form action="allegro/allegro_aukcja_zakoncz.php" method="post" class="cmxform" id="allegroForm" >          

          <div class="poleForm">
            <div class="naglowek">Zakończenie aukcji</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id = '" . $filtr->process($_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="aukcja_id" value="<?php echo $info['auction_id']; ?>" />
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <p>
                      Czy zakończyć aukcję ?
                    </p>                    
                    
                    <p>
                      <label>Odwołać oferty:</label>
                      <input type="radio" value="0" name="odwolanie" onclick="$('#div_powod').slideUp();" checked="checked" /> nie
                      <input type="radio" value="1" name="odwolanie" onclick="$('#div_powod').slideDown();" /> tak
                    </p> 

                    <p id="div_powod" style="display:none;">
                      <label class="required">Powód odwołania:</label>
                      <textarea id="powod" name="powod" value="" cols="50" rows="5"></textarea>
                      
                      <span class="maleInfo" style="margin-left:165px">W przypadku jeżeli mają być odwołane oferty trzeba podać powód odwołania ofert</span>
                    </p>
                    
                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zakończ aukcję" />
                  <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                </div>

                <?php
                unset($info);
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            unset($zapytanie);            
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}