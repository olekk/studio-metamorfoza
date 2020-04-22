<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $ciagKategorii = '';
        for ($w = 1; $w <= (int)$_POST['ile_input']; $w++) {
            if (isset($_POST['kategoria_' . $w])) {
                $ciagKategorii .= trim($filtr->process($_POST['kategoria_' . $w])) . ':' . (((float)$_POST['marza_' . $w] != '') ? (float)$_POST['marza_' . $w] : 0) . '#';
            }
        }
        $ciagKategorii = substr($ciagKategorii, 0, strlen($ciagKategorii)-1);
        //
        $pola = array(
                array('tpl_xml_name',$filtr->process($_POST['nazwa'])),
                array('tpl_xml_categories_text',$ciagKategorii),
                array('tpl_xml_range',$filtr->process($_POST['zakres'])));             
        //	
        $db->insert_query('tpl_xml' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
              
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('szablony_importu_xml.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('szablony_importu_xml.php');
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
            $("#xmlForm").validate({
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
          
          function dodaj_pozycje() {
            ile_pol = parseInt($("#ile_input").val()) + 1;
            //
            $('#kategorie_nazwy').append('<p id="k'+ile_pol+'"></p>');
            $('#k'+ile_pol).css('display','none');
            //
            $.get('ajax/kategoria_xml.php', { id: ile_pol }, function(data) {
                $('#k'+ile_pol).html(data);
                $("#ile_input").val(ile_pol);
                $('#k'+ile_pol).slideDown("fast");		
            });
          }       

          function usun_pozycje(id) {
            $('#k' + id).remove();
          }              
          //]]>
          </script>     

          <form action="narzedzia/szablony_importu_xml_dodaj.php" method="post" id="xmlForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <p>
                  <label class="required">Nazwa szablonu:</label>
                  <input type="text" name="nazwa" id="nazwa" value="" size="25" />
                </p>
                
                <div id="kategorie_nazwy">

                    <p id="k1">
                      <label>Nazwa/ścieżka kategorii:</label>
                      <input type="text" name="kategoria_1" value="" size="45" />
                      &nbsp; Marża:
                      <input type="text" name="marza_1" value="" size="5" /> % 
                      &nbsp; <span class="usun" onclick="usun_pozycje(1)" style="cursor:pointer">&nbsp;</span>                       
                    </p>
                    
                </div>
                
                <input type="hidden" name="ile_input" id="ile_input" value="1" />
                
                <p>
                    <label>&nbsp;</label>
                    <span class="dodaj" onclick="dodaj_pozycje()" style="cursor:pointer">dodaj pozycję</span>                    
                </p>

                <p>
                  <label>Zakres importu produktów i kategorii:</label>
                  <input type="radio" value="1" name="zakres" checked="checked" /> wszystkie kategorie
                  <input type="radio" value="0" name="zakres" /> tylko w/w wymienione
                </p>   

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('szablony_importu_xml','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','narzedzia');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
