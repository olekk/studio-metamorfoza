<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // jezeli cecha ma byc obrazkowa nie moze byc w filtrach
        if ($_POST['obrazek'] == '1') {
            $_POST['filtr'] = '0';
        }
        //        
        $pola = array(
                array('products_extra_fields_name',$filtr->process($_POST["nazwa"])),
                array('products_extra_fields_order',$filtr->process($_POST["sort"])),
                array('products_extra_fields_status','1'),
                array('languages_id',$filtr->process($_POST["jezyk"])),
                array('products_extra_fields_filter',$filtr->process($_POST['filtr'])),
                array('products_extra_fields_search',$filtr->process($_POST["szukanie"])),
                array('products_extra_fields_view',$filtr->process($_POST["widocznosc"])),
                array('products_extra_fields_allegro',$filtr->process($_POST["allegro"])),
                array('products_extra_fields_location',$filtr->process($_POST["polozenie"])),
                array('products_extra_fields_image',$filtr->process($_POST["obrazek"]))
                );
        //	
        $db->insert_query('products_extra_fields' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('dodatkowe_pola.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('dodatkowe_pola.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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

          <form action="slowniki/dodatkowe_pola_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
            
                <p>
                  <label class="required">Nazwa pola:</label>
                  <input type="text" name="nazwa" id="nazwa" value="" size="35" />
                </p>
                
                <p>
                  <label>Dostępne dla wersji językowej:</label>
                  <?php
                  $tablica_jezykow = Funkcje::TablicaJezykow(true);                 
                  echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,0);
                  ?>                  
                </p>   

                <p>
                  <label>Wyświetlane w formie obrazka:</label>
                  <input type="radio" value="0" name="obrazek" onclick="$('#filtr').slideDown()" checked="checked" /> nie
                  <input type="radio" value="1" name="obrazek" onclick="$('#filtr').slideUp()" /> tak                       
                </p>      
                
                <div id="filtr">
                
                    <p>
                      <label>Czy pole ma być wyświetlane w filtrach w listingu produktów:</label>
                      <input type="radio" value="0" name="filtr" checked="checked" /> nie
                      <input type="radio" value="1" name="filtr" /> tak
                    </p>

                    <p>
                      <label>Czy pole ma być wyświetlane w wyszukiwaniu zaawansowanym produktów:</label>
                      <input type="radio" value="0" name="szukanie" checked="checked" /> nie
                      <input type="radio" value="1" name="szukanie" /> tak
                    </p>

                </div>   

                <p>
                  <label>Czy wyświetlać pole na karcie produktu:</label>
                  <input type="radio" value="0" name="widocznosc" onclick="$('#widok').slideUp()" /> nie
                  <input type="radio" value="1" name="widocznosc" onclick="$('#widok').slideDown()" checked="checked" /> tak               
                </p>        
                
                <p>
                  <label>Czy wyświetlać pole na aukcjach Allegro:</label>
                  <input type="radio" value="0" name="allegro" /> nie
                  <input type="radio" value="1" name="allegro" checked="checked" /> tak               
                </p>                   

                <div id="widok">

                    <p>
                      <label>Miejsce wyświetlania:</label>
                      <input type="radio" value="foto" class="toolTipTop" title="Dodatkowe pola będą wyświetlane na karcie produktu obok zdjęcia razem z dostępnością, nr kat, czasem wysyłki" name="polozenie" checked="checked" /> obok zdjęcia produktu
                      <input type="radio" value="opis" class="toolTipTop" title="Dodatkowe pola będą wyświetlane na karcie produktu pod opisem produktu" name="polozenie" /> pod opisem produktu                
                    </p>  

                </div>                                   

                <p>
                  <label>Kolejność wyświetlania:</label>
                  <input type="text" name="sort" value="" size="5" />
                </p> 

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}