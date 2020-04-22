<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // jezeli pola ma byc obrazkowe nie moze byc w filtrach
        if ($_POST['obrazek'] == '1') {
            $_POST['filtr'] = '0';
        }
        //
        // jezeli jest zmiana formy wyswietlania na obrazek lub tekst to usuwa dane ze slownika
        if ( $_POST["obrazek"] != $_POST["poprzedni_typ"] ) {
            //
            $db->delete_query('products_extra_fields_book' , " products_extra_fields_id = '".(int)$_POST["id"]."'");  
            //
        }
        //
        $pola = array(
                array('products_extra_fields_name',$filtr->process($_POST["nazwa"])),
                array('products_extra_fields_order',$filtr->process($_POST["sort"])),
                array('products_extra_fields_status','1'),
                array('languages_id',$filtr->process($_POST["jezyk"])),
                array('products_extra_fields_filter',$filtr->process($_POST["filtr"])),
                array('products_extra_fields_search',$filtr->process($_POST["szukanie"])),
                array('products_extra_fields_view',$filtr->process($_POST["widocznosc"])),
                array('products_extra_fields_allegro',$filtr->process($_POST["allegro"])),
                array('products_extra_fields_location',$filtr->process($_POST["polozenie"])),
                array('products_extra_fields_image',$filtr->process($_POST["obrazek"]))
                );
        //			
        $db->update_query('products_extra_fields' , $pola, " products_extra_fields_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('dodatkowe_pola.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                }                
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane"
                }               
              }
            });
          });
          //]]>
          </script>        

          <form action="slowniki/dodatkowe_pola_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_extra_fields where products_extra_fields_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <p>
                      <label class="required">Nazwa pola:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['products_extra_fields_name']; ?>" size="35" />
                    </p>
                    
                    <p>
                      <label>Dostępne dla wersji językowej:</label>
                      <?php
                      $tablica_jezykow = Funkcje::TablicaJezykow(true);                 
                      echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,$info['languages_id']);
                      ?>                  
                    </p>   

                    <p>
                      <label>Wyświetlane w formie obrazka:</label>
                      <input type="radio" value="0" name="obrazek" onclick="$('#filtr').slideDown()" <?php echo (($info['products_extra_fields_image'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                      <input type="radio" value="1" name="obrazek" onclick="$('#filtr').slideUp()" <?php echo (($info['products_extra_fields_image'] == '1') ? 'checked="checked"' : ''); ?> /> tak                       
                    </p> 

                    <div class="maleInfo" style="margin:-10px 0px 0px 177px">
                      zmiana formy wyświetlania dodatkowego pola spowoduje usunięcie wartości w słowniku danego pola
                      <input type="hidden" value="<?php echo $info['products_extra_fields_image']; ?>" name="poprzedni_typ" />
                    </div>

                    <div id="filtr" <?php echo (($info['products_extra_fields_image'] == '1') ? 'style="display:none"' : ''); ?>>
                    
                        <p>
                          <label>Czy pole ma być wyświetlane w filtrach w listingu produktów:</label>
                          <input type="radio" value="0" name="filtr" <?php echo (($info['products_extra_fields_filter'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                          <input type="radio" value="1" name="filtr" <?php echo (($info['products_extra_fields_filter'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                        </p>

                        <p>
                          <label>Czy pole ma być wyświetlane w wyszukiwaniu zaawansowanym produktów:</label>
                          <input type="radio" value="0" name="szukanie" <?php echo (($info['products_extra_fields_search'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                          <input type="radio" value="1" name="szukanie" <?php echo (($info['products_extra_fields_search'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                        </p>

                    </div> 

                    <p>
                      <label>Czy wyświetlać pole na karcie produktu:</label>
                      <input type="radio" value="0" name="widocznosc" onclick="$('#widok').slideUp()" <?php echo (($info['products_extra_fields_view'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                      <input type="radio" value="1" name="widocznosc" onclick="$('#widok').slideDown()" <?php echo (($info['products_extra_fields_view'] == '1') ? 'checked="checked"' : ''); ?> /> tak               
                    </p> 

                    <p>
                      <label>Czy wyświetlać pole na aukcjach Allegro:</label>
                      <input type="radio" value="0" name="allegro" <?php echo (($info['products_extra_fields_allegro'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                      <input type="radio" value="1" name="allegro" <?php echo (($info['products_extra_fields_allegro'] == '1') ? 'checked="checked"' : ''); ?> /> tak               
                    </p>                    

                    <div id="widok" <?php echo (($info['products_extra_fields_view'] == '0') ? 'style="display:none"' : ''); ?>>

                        <p>
                          <label>Miejsce wyświetlania:</label>
                          <input type="radio" value="foto" class="toolTipTop" title="Dodatkowe pola będą wyświetlane na karcie produktu obok zdjęcia razem z dostępnością, nr kat, czasem wysyłki" name="polozenie" <?php echo (($info['products_extra_fields_location'] == 'foto') ? 'checked="checked"' : ''); ?> /> obok zdjęcia produktu
                          <input type="radio" value="opis" class="toolTipTop" title="Dodatkowe pola będą wyświetlane na karcie produktu pod opisem produktu" name="polozenie" <?php echo (($info['products_extra_fields_location'] == 'opis') ? 'checked="checked"' : ''); ?> /> pod opisem produktu                
                        </p>  

                    </div>

                    <p>
                      <label>Kolejność wyświetlania:</label>
                      <input type="text" name="sort" value="<?php echo $info['products_extra_fields_order']; ?>" size="5" />
                    </p> 

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}