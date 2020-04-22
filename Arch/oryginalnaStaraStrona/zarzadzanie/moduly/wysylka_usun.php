<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $tlumaczenie = 'WYSYLKA_'. $filtr->process($_POST["id"]) . '_TYTUL';
        $objasnienie = 'WYSYLKA_'. $filtr->process($_POST["id"]) . '_OBJASNIENIE';

        $zapytanie = "SELECT * FROM translate_constant WHERE translate_constant = '" .$tlumaczenie. "'";
        $sql = $db->open_query($zapytanie);
        $info = $sql->fetch_assoc();
        $id_wyrazenia = $info['translate_constant_id'];

        $db->delete_query('translate_constant' , " translate_constant = '".$tlumaczenie."'");
        $db->delete_query('translate_value' , " translate_constant_id = '".$id_wyrazenia."'");

        $db->close_query($sql);
        unset($zapytanie, $info, $id_wyrazenia, $tlumaczenie);

        $zapytanie = "SELECT * FROM translate_constant WHERE translate_constant = '" .$objasnienie. "'";
        $sql = $db->open_query($zapytanie);
        $info = $sql->fetch_assoc();
        $id_wyrazenia = $info['translate_constant_id'];

        $db->delete_query('translate_constant' , " translate_constant = '".$objasnienie."'");
        $db->delete_query('translate_value' , " translate_constant_id = '".$id_wyrazenia."'");

        $db->close_query($sql);
        unset($zapytanie, $info, $id_wyrazenia, $objasnienie);
        
        $db->delete_query('modules_shipping' , " id = '".(int)$filtr->process($_POST["id"])."'");
        $db->delete_query('modules_shipping_params' , " modul_id = '".(int)$filtr->process($_POST["id"])."'");
        //
        Funkcje::PrzekierowanieURL('wysylka.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="moduly/wysylka_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "SELECT * FROM modules_shipping WHERE id = '" . (int)$filtr->process($_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$filtr->process($_GET['id_poz']); ?>" />
                    <input type="hidden" name="klasa" value="<?php echo $info['klasa']; ?>" />

                    <p>
                      Czy skasować pozycje ?
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('wysylka','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button> 
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