<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('modul_status',$filtr->process($_POST['status'])),
                array('modul_file',$filtr->process($_POST['plik'])),
                array('modul_title',$filtr->process($_POST['nazwa'])),
                array('modul_description',$filtr->process($_POST['opis'])));
             
        $sql = $db->insert_query('theme_modules_fixed' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('srodek_stale.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('srodek_stale.php');
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
            $("#modForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                plik: {
                  required: true
                }              
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane"
                },
                plik: {
                  required: "Pole jest wymagane"
                }                
              }
            });
          });            
          //]]>
          </script>     

          <form action="wyglad/srodek_stale_dodaj.php" method="post" id="modForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
    
                <p>
                    <label class="required">Nazwa modułu:</label>
                    <input type="text" name="nazwa" size="45" value="" id="nazwa" />
                </p>
                
                <p>
                    <label>Opis modułu:</label>
                    <textarea name="opis" rows="5" cols="70" class="toolTip" title="Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu"></textarea>
                </p> 

                <p>
                    <label class="required">Nazwa pliku modułu:</label>
                    <input type="text" name="plik" id="plik" value="" size="40" class="toolTipText" title="Nazwa pliku definiującego wygląd modułu (pliki muszą znajdować się w katalogu /moduly_stale" />
                </p>    

                <p>
                    <label>Czy moduł ma być włączony ?</label>
                    <input type="radio" value="1" name="status" checked="checked" /> tak
                    <input type="radio" value="0" name="status" /> nie
                </p> 

                </div>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('srodek_stale','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
