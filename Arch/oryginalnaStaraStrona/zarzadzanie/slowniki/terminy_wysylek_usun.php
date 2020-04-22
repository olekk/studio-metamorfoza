<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('products_shipping_time' , " products_shipping_time_id = '".$filtr->process($_POST["id"])."'");  
        $db->delete_query('products_shipping_time_description' , " products_shipping_time_id = '".$filtr->process($_POST["id"])."'"); 
        //
        // usuwa wpis w produktach
        $pola = array(
                array('products_shipping_time_id','')
        );        
        $db->update_query('products' , $pola, " products_shipping_time_id = '".$filtr->process((int)$_POST["id"])."'");
        //        
        Funkcje::PrzekierowanieURL('terminy_wysylek.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="slowniki/terminy_wysylek_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_shipping_time where products_shipping_time_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
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
                  <button type="button" class="przyciskNon" onclick="cofnij('terminy_wysylek','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button> 
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