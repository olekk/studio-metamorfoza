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
                array('products_text_fields_type',$filtr->process($_POST['typ_pola'])),
                array('products_text_fields_order',$filtr->process($_POST['sort'])));
                
        if ( $_POST['typ_pola'] == '2' ) {
            //
            $pola[] = array('products_text_fields_file_type',$filtr->process($_POST['formaty']));
            $pola[] = array('products_text_fields_file_size',$filtr->process($_POST['rozmiar']));
            //
        } else {
            //
            $pola[] = array('products_text_fields_file_type','');
            $pola[] = array('products_text_fields_file_size','1');
            //        
        }
        //			
        $db->update_query('products_text_fields' , $pola, " products_text_fields_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //

        // kasuje rekordy w tablicy
        $db->delete_query('products_text_fields_info' , " products_text_fields_id = '".$filtr->process($_POST["id"])."'");
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {

            //
            if (!empty($_POST['nazwa_'.$w])) {
              $nazwa = $filtr->process($_POST['nazwa_'.$w]);
            } else {
              $nazwa = $filtr->process($_POST['nazwa_0']);
            }
            //        
            $pola = array(
                    array('products_text_fields_description',$filtr->process($_POST['edytor_'.$w])),
                    array('products_text_fields_id',$filtr->process($_POST["id"])),
                    array('languages_id',$ile_jezykow[$w]['id']),
                    array('products_text_fields_name',$nazwa),
                    array('products_text_fields_default_text',$filtr->process($_POST['domyslny_'.$w])));
                    
            $sql = $db->insert_query('products_text_fields_info' , $pola);
            unset($pola);
        }

        Funkcje::PrzekierowanieURL('dodatkowe_pola_tekstowe.php?id_poz='.(int)$_POST["id"]);
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
                },
                sort: {
                  required: true,
                  range: [0, 999],
                  number: true
                }
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane"
                },
                sort: {
                  required: "Pole jest wymagane"
                }
              }
            });
          });
          //]]>
          </script>     

          <form action="slowniki/dodatkowe_pola_tekstowe_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_text_fields where products_text_fields_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
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
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',\'940\',\'150\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        $slid_down = '';
                        $slid_up = '';
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "SELECT distinct * FROM products_text_fields_info WHERE products_text_fields_id = '".$filtr->process((int)$_GET['id_poz'])."' and languages_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="70" value="<?php echo $nazwa['products_text_fields_name']; ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label>Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="70" value="<?php echo $nazwa['products_text_fields_name']; ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <p>
                                    <label>Domyślny tekst:</label>   
                                    <input type="text" name="domyslny_<?php echo $w; ?>" class="toolTipTopText domyslne" title="Domyślny tekst wyświetlany w polu - kasowany automatycznie po kliknięciu przez klienta w pole tekstowe - nie dotyczy opcji wgrywania plików" size="50" value="<?php echo $nazwa['products_text_fields_default_text']; ?>" />
                                </p>                                 
                                
                                <div class="edytor">
                                  <textarea cols="50" rows="10" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo $nazwa['products_text_fields_description']; ?></textarea>
                                </div>  
                            
                            </div>
                            
                            <?php
                            $db->close_query($sqls);
                            unset($nazwa);
                        }
                        ?>                      
                    </div>                
                    
                    <p>
                      <label>Typ pola:</label>
                      <input type="radio" value="0" name="typ_pola" onclick="$('#plik').slideUp()" <?php echo (($info['products_text_fields_type'] == '0') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika INPUT pozwala na wpisanie tylko jednego wiersza tekstu" /> Input
                      <input type="radio" value="1" name="typ_pola" onclick="$('#plik').slideUp()" <?php echo (($info['products_text_fields_type'] == '1') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika TEXTAREA pozwala na wpisanie wielu wierszy tekstu" /> Textarea
                      <input type="radio" value="2" name="typ_pola" onclick="$('#plik').slideDown()" <?php echo (($info['products_text_fields_type'] == '2') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Pole z możliwością wgrania pliku" /> Wgrywanie pliku
                    </p> 

                    <p>
                      <label>Kolejność wyświetlania:</label>
                      <input type="text" name="sort" id="sort" value="<?php echo $info['products_text_fields_order']; ?>" size="5" />
                    </p>           
                    
                    <div id="plik" <?php echo (($info['products_text_fields_type'] != '2') ? 'style="display:none"' : ''); ?>>
                    
                        <p>
                          <label>Dopuszczalne formaty plików:</label>
                          <input type="text" name="formaty" value="<?php echo $info['products_text_fields_file_type']; ?>" size="50" class="toolTipTopText" title="Będzie można wgrać / dołączyć do produktu tylko pliki w podanych formatach - każdy format musi być rozdzielony przecinkiem np: jpg,png,gif" />
                        </p>

                        <p>
                          <label>Maksymalny rozmiar pliku:</label>
                          <input type="text" name="rozmiar" value="<?php echo $info['products_text_fields_file_size']; ?>" size="5" class="toolTipTop" title="Maksymalny rozmiar pliku jaki będzie można wgrać / dołączyć do produktu - w MB" />
                        </p>                     
                    
                    </div>                    

                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0','edytor_','940','150');
                    //]]>
                    </script>                 
                
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola_tekstowe','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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