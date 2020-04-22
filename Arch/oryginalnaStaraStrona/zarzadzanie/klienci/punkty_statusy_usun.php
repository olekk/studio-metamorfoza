<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('customers_points_status' , " points_status_id = '".$filtr->process($_POST["id"])."'");  
        $db->delete_query('customers_points_status_description' , " points_status_id = '".$filtr->process($_POST["id"])."'"); 
        //
        Funkcje::PrzekierowanieURL('punkty_statusy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="klienci/punkty_statusy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            // id ktorych nie mozna skasowac
            $wykluczoneId = array(1,2,3,4);
            
            if ( !isset($_GET['id_poz']) || in_array((int)$_GET['id_poz'], $wykluczoneId) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            unset($wykluczoneId);    
            
            $zapytanie = "select * from customers_points_status where points_status_id = '" . $filtr->process((int)$_GET['id_poz']) . "' and points_status_id != '1' and points_status_id != '2' and points_status_id != '3' and points_status_id != '4'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

                    <p>
                      Czy skasować pozycje ?
                    </p>   

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('punkty_statusy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button> 
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