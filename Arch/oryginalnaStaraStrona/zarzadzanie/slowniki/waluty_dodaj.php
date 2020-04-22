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
                array('title',$filtr->process($_POST["nazwa"])),
                array('code',$filtr->process($_POST["kod"])),
                array('symbol',$filtr->process($_POST["symbol"])),
                array('decimal_point',$filtr->process($_POST["separator"])),
                array('value',$filtr->process($_POST["przelicznik"])),
                array('currencies_marza',$filtr->process($_POST["prowizja"])),
                array('last_updated ','now()')
                );
        //	
        $db->insert_query('currencies' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
          
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('waluty.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('waluty.php');
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
                },
                kod: {
                  required: true
                },
                symbol: {
                  required: true
                },
                przelicznik: {
                  required: true
                }                   
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane"
                },
                kod: {
                  required: "Pole jest wymagane"
                },
                symbol: {
                  required: "Pole jest wymagane"
                },
                przelicznik: {
                  required: "Pole jest wymagane"
                }                 
              }
            });
          });
          //]]>
          </script>     

          <form action="slowniki/waluty_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <p>
                  <label class="required">Nazwa:</label>
                  <input type="text" name="nazwa" id="nazwa" value="" size="35" />
                </p>

                <p>
                  <label class="required">Kod:</label>
                  <input type="text" name="kod" id="kod" value="" size="5" class="toolTipText" title="kod waluty wg tabeli NBP" />
                </p>

                <p>
                  <label class="required">Symbol:</label>
                  <input type="text" name="symbol" id="symbol" value="" size="5" class="toolTipText" title="symbol waluty wyświetlany w sklepie" />
                </p>

                <p>
                  <label>Separator dziesiętny:</label>
                  <input type="radio" value="." name="separator" checked="checked" /> . (kropka)
                  <input type="radio" value="," name="separator" /> , (przecinek)
                </p>

                <p>
                  <label class="required">Przelicznik:</label>
                  <input type="text" name="przelicznik" id="przelicznik" value="" size="15" class="toolTipRzeczywista" title="przelicznik w stosunku do waluty domyślnej" />
                </p>                 

                <p>
                  <label>Prowizja na walucie:</label>
                  <input type="text" name="prowizja" value="" size="5" class="toolTip" title="wartość procentowa doliczana do kursu waluty - od 1 do 99" /> %
                </p>                

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('waluty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}