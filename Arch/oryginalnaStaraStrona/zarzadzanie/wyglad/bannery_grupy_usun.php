<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('banners_group' , " banners_group_id = '".$filtr->process($_POST["id"])."'"); 
        $db->delete_query('banners' , " banners_group = '".$filtr->process($_POST["kod"])."'"); 
        //
        Funkcje::PrzekierowanieURL('bannery_grupy.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="wyglad/bannery_grupy_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from banners_group where banners_group_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <input type="hidden" name="kod" value="<?php echo $info['banners_group_code']; ?>" />
                    
                    <p>
                      Czy skasować pozycje ? <br /><br />
                      <span class="ostrzezenie">Jeżeli do grupy są przypisane bannery zostaną one usunięte razem z grupą.</span>   
                    </p>   
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('bannery_grupy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button> 
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