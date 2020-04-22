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
            $pola = array(array('countries_default','0'));
            $db->update_query('countries' , $pola);	        
        }
        //
        $pola = array(
                array('countries_default',$filtr->process($_POST["domyslny"])),
                array('countries_iso_code_2',$filtr->process($_POST["iso2"])),
                array('countries_iso_code_3',$filtr->process($_POST["iso3"])),
        );
        //	
        $db->insert_query('countries' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            // jezeli nazwa w innym jezyku nie jest wypelniona
            if ( $w > 0 ) {
                if (empty($_POST['nazwa_'.$w])) {
                    $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                }
            }
            //      
            $pola = array(
                    array('countries_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('countries_name',$filtr->process($_POST['nazwa_'.$w])));           
            $sql = $db->insert_query('countries_description' , $pola);
            unset($pola);
        }             
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('kraje.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('kraje.php');
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
                nazwa_0: {
                  required: true
                },
                iso2: {
                  required: true
                },
                iso3: {
                  required: true
                }
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane"
                }               
              }
            });
          });
          //]]>
          </script>     

          <form action="slowniki/kraje_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                }                    
                ?>                   
                </div>
                
                <div style="clear:both"></div>
                
                <div class="info_tab_content">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required">Nazwa:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label>Nazwa:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" />
                               <?php } ?>
                            </p> 
                                        
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script type="text/javascript">
                //<![CDATA[
                gold_tabs('0');
                //]]>
                </script>                  
            
                    <p>
                      <label class="required">Kod ISO-2:</label>
                      <input type="text" name="iso2" size="5" value="" id="iso2" />
                    </p>

                    <p>
                      <label class="required">Kod ISO-3:</label>
                      <input type="text" name="iso3" size="5" value="" id="iso3" />
                    </p>

                <p>
                  <label>Czy kraj jest domyślnym:</label>
                  <input type="radio" value="0" name="domyslny" checked="checked" /> nie
                  <input type="radio" value="1" name="domyslny" /> tak                       
                </p>  
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kraje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
