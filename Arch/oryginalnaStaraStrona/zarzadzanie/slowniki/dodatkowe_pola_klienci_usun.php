<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //			
        $db->delete_query('customers_extra_fields' , " fields_id = '".$filtr->process($_POST["id"])."'");
        $db->delete_query('customers_extra_fields_info' , " fields_id = '".$filtr->process($_POST["id"])."'");
        $db->delete_query('customers_to_extra_fields' , " fields_id = '".$filtr->process($_POST["id"])."'");

        //
        Funkcje::PrzekierowanieURL('dodatkowe_pola_klienci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="slowniki/dodatkowe_pola_klienci_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from customers_extra_fields where fields_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                ?>

                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <p>
                      <?php echo $wynik_sprawdzenia; ?>Czy skasować pozycje ?
                      
                      <br /><br /><span class="ostrzezenie">Dodatkowe pole zostanie również usunięte z kont klientów.
                      
                      <?php
                      $sprawdzenie = "select fields_id from customers_to_extra_fields where fields_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
                      $wynik = $db->open_query($sprawdzenie);
                      if ((int)$db->ile_rekordow($wynik) > 0) {
                         echo '<br /><span class="czerwony">To pole jest przypisane do '.(int)$db->ile_rekordow($wynik).' klientów. Po usunięciu dane zostaną utracone.</span><br />';
                      }  
                      unset($sprawdzenie, $wynik);
                      ?>
                      
                      </span>                      
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola_klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
                </div>

            <?php
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