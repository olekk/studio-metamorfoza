<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //
        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["id"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['nazwa_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_'.$w])),
                        array('translate_constant_id',(int)$_POST["id"]),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_0'])),
                        array('translate_constant_id',(int)$_POST["id"]),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        
        //
        
        if (isset($_SESSION['programista']) && $_SESSION['programista'] == '1') {
            //
            $pola = array(array('translate_constant',$filtr->process($_POST['zmienna'])));
            $db->update_query('translate_constant' , $pola, "translate_constant_id = '".(int)$_POST["id"]."'");
            unset($pola);            
            //
        }
              
        Funkcje::PrzekierowanieURL('tlumaczenia.php?id_poz='.(int)$_POST["id"]);
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
                nazwa_0: {
                  required: true
                }            
              }
            });
          });
          //]]>
          </script>     
          
          <form action="slowniki/tlumaczenia_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>

              <?php
            
              if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
              }    

              $zapytanie = "SELECT w.translate_constant_id AS id, w.translate_constant AS wyrazenie, s.section_name AS sekcja, w.section_id AS idsekcji, t.translate_value AS tresc FROM translate_section AS s, translate_constant AS w, translate_value AS t
              WHERE t.language_id = '".$_SESSION['domyslny_jezyk']['id']."' AND w.section_id = s.section_id AND t.translate_constant_id = w.translate_constant_id AND w.translate_constant_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";

              $sql = $db->open_query($zapytanie);
            
              if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

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
                            
                        // pobieranie danych jezykowych
                        $zapytanie_jezyk = "select distinct * from translate_value where translate_constant_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                        $sqls = $db->open_query($zapytanie_jezyk);
                        $nazwa = $sqls->fetch_assoc();   
                        ?>
                                
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Treść:</label>
                                    <textarea cols="120" rows="10" name="nazwa_<?php echo $w; ?>" id="nazwa_0"><?php echo $nazwa['translate_value']; ?></textarea>
                                   <?php } else { ?>
                                    <label>Treść:</label>
                                    <textarea cols="120" rows="10" name="nazwa_<?php echo $w; ?>"><?php echo $nazwa['translate_value']; ?></textarea>
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                                
                         <?php                    
                         $db->close_query($sqls);
                         unset($zapytanie_jezyk, $nazwa);
                         }                    
                         ?>                      
                    </div>

                    <p>
                      <label>Nazwa zmiennej:</label>   
                      <input type="text" name="zmienna" id="zmienna" size="53" value="<?php echo $info['wyrazenie']; ?>" class="toolTipText" title="To pole nie podlega edycji - nazwa zmiennej, która będzie zastępowana przetłumaczonym tekstem." <?php echo ((isset($_SESSION['programista']) && $_SESSION['programista'] == 1) ? '' : 'readonly="readonly"'); ?> />
                    </p>

                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0');
                    //]]>
                    </script>                    
                   
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('tlumaczenia','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
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
