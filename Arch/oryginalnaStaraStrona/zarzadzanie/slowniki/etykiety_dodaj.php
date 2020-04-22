<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["domyslny"] == '1') {
            $pola = array(array('label_default','0'));
            $db->update_query('print_labels' , $pola);
        }
        //
        $pola = array(
                array('brand',$filtr->process($_POST["producent"])),
                array('name',$filtr->process($_POST["typ"])),
                array('description',$filtr->process($_POST["opis"])),
                array('width',$filtr->process($_POST["szerokosc"])),
                array('height',$filtr->process($_POST["wysokosc"])),
                array('margin',$filtr->process($_POST["odstep"])),
                array('orientation',$filtr->process($_POST["orientacja"])),
                array('format',$filtr->process($_POST["format"])),
                array('cols',$filtr->process($_POST["kolumn"])),
                array('rows',$filtr->process($_POST["wierszy"])),
                array('topmargin',$filtr->process($_POST["margines_gorny"])),
                array('leftmargin',$filtr->process($_POST["margines_lewy"])),
                array('bordercolor',$filtr->process($_POST["kolor_ramki"])),
                array('borderwidth',$filtr->process($_POST["grubosc_ramki"])),
                array('border',$filtr->process($_POST["ramka"])),
                array('label_default',$filtr->process($_POST["domyslny"])),
        );
        //
        $db->insert_query('print_labels' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);
        
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('etykiety.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('etykiety.php');
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
          <script type="text/javascript" src="programy/jscolor/jscolor.js"></script> 

          <form action="slowniki/etykiety_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <p>
                  <label>Producent:</label>
                  <input type="text" name="producent" size="53" value="" id="producent" />
                </p>

                <p>
                  <label class="required">Typ:</label>
                  <input type="text" name="typ" size="53" value="" id="typ" class="required" />
                </p>

                <p>
                  <label>Opis:</label>
                  <input type="text" name="opis" size="53" value="" id="opis" />
                </p>

                <p>
                  <label class="required">Szerokość [mm]:</label>
                  <input type="text" name="szerokosc" size="20" value="" id="szerokosc" class="required toolTip" title="Szerokość pojedynczej etykiety" />
                </p>

                <p>
                  <label class="required">Wysokość [mm]:</label>
                  <input type="text" name="wysokosc" size="20" value="" id="wysokosc" class="required toolTip" title="Wysokość pojedynczej etykiety" />
                </p>

                <p>
                  <label class="required">Odstęp poziomy [mm]:</label>
                  <input type="text" name="odstep" size="20" value="" id="odstep" class="required toolTip" title="Poziomy odstęp pomiędzy etykietami" />
                </p>

                <p>
                  <label class="required">Górny margines [mm]:</label>
                  <input type="text" name="margines_gorny" size="20" value="" id="margines_gorny" class="required toolTip" title="Margines od górnej krawędzi arkusza" />
                </p>

                <p>
                  <label class="required">Lewy margines [mm]:</label>
                  <input type="text" name="margines_lewy" size="20" value="" id="margines_lewy" class="required toolTip" title="Margines od lewej krawędzi arkusza" />
                </p>

                <p>
                  <label class="required">Ilość kolumn:</label>
                  <input type="text" name="kolumn" size="20" value="" id="kolumn" class="required toolTip" title="Ilość kolumn etykiet na pojedynczym arkuszu papieru" />
                </p>

                <p>
                  <label class="required">Ilość wierszy:</label>
                  <input type="text" name="wierszy" size="20" value="" id="wierszy" class="required toolTip" title="Ilość wierszy etykiet na pojedynczym arkuszu papieru" />
                </p>

                <p>
                  <label class="required">Format arkusza:</label>
                  <?php
                  $tablica = array();
                  $tablica[] = array('id' => 'A4', 'text' => 'A4');
                  $tablica[] = array('id' => 'A5', 'text' => 'A5');
                  $tablica[] = array('id' => 'B5', 'text' => 'B5');
                  echo Funkcje::RozwijaneMenu('format', $tablica, 'A4', 'class="toolTipText" title="Format papieru arkusza etykiet"');
                  unset($tablica);
                  ?>
                </p>

                <p>
                  <label class="required">Orientacja arkusza:</label>
                  <?php
                  $tablica = array();
                  $tablica[] = array('id' => 'P', 'text' => 'Pionowa');
                  $tablica[] = array('id' => 'L', 'text' => 'Pozioma');
                  echo Funkcje::RozwijaneMenu('orientacja', $tablica, 'P', 'class="toolTipText" title="Orientacja wydruku arkusza etykiet"');
                  unset($tablica);
                  ?>
                </p>

                <p>
                  <label>Czy drukować ramkę:</label>
                  <input type="radio" value="0" name="ramka" checked="checked" /> nie
                  <input type="radio" value="1" name="ramka" /> tak                       
                </p>
                    
                <p>
                  <label>Kolor ramki:</label>
                  <input name="kolor_ramki" class="color" style="-moz-box-shadow:none" value="" size="8" />                    
                </p> 

                <p>
                  <label>Grubość ramki:</label>
                  <input type="text" name="grubosc_ramki" size="20" value="" id="grubosc_ramki" class="toolTip" title="Grubość ramki wokół etykiety" />
                </p>

                <p>
                  <label>Czy etykieta jest domyślna:</label>
                  <input type="radio" value="0" name="domyslny" checked="checked" /> nie
                  <input type="radio" value="1" name="domyslny" /> tak                       
                </p>

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('etykiety','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
