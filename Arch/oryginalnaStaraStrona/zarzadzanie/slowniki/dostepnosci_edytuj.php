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
                array('shipping_mode',$filtr->process($_POST['kupowanie'])),
                array('quantity',(($_POST['tryb'] == '1') ? $filtr->process($_POST['ilosc']) : '0')),
                array('image',$filtr->process($_POST['zdjecie'])),
                array('okazje',$filtr->process($_POST['okazje'])),
                array('nokaut',$filtr->process($_POST['nokaut'])),
                array('ceneo',$filtr->process($_POST['ceneo'])),
                array('smartbay',$filtr->process($_POST['smartbay'])),
                array('googleshopping',$filtr->process($_POST['googleshopping'])),
                );
        
        $sql = $db->update_query('products_availability', $pola, " products_availability_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_availability_description' , " products_availability_id = '".$filtr->process($_POST["id"])."'");
        
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
                    array('products_availability_id',$filtr->process($_POST["id"])),
                    array('products_availability_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id'])
                    );           
            $sql = $db->insert_query('products_availability_description', $pola);
            unset($pola);
            //           
        }
        //
        Funkcje::PrzekierowanieURL('dostepnosci.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <form action="slowniki/dostepnosci_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products_availability where products_availability_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <!-- Skrypt do walidacji formularza -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                    $("#slownikForm").validate({
                      rules: {
                        <?php
                        if ( $info['mode'] == '1' ) {
                        ?>
                        ilosc: {
                          required: true,           
                        },  
                        <?php } ?>
                        nazwa_0: {
                          required: true
                        }                
                      },
                      messages: {
                        <?php
                        if ( $info['mode'] == '1' ) {
                        ?>      
                        ilosc: {
                          required: "Pole jest wymagane"
                        }, 
                        <?php } ?>                
                        nazwa_0: {
                          required: "Pole jest wymagane"
                        }               
                      }
                    });
                });
                //]]>
                </script>          
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <input type="hidden" name="tryb" id="tryb" value="<?php echo (int)$info['mode']; ?>" />
                    
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
                            $zapytanie_jezyk = "select distinct * from products_availability_description where products_availability_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['products_availability_name']; ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label>Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['products_availability_name']; ?>" />
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk, $nazwa);
                        }                    
                        ?>                      
                    </div>                    
                    
                    <?php
                    if ( $info['mode'] == '1' ) {
                    ?>
                    <p>
                      <label class="required">Od jakiej ilości produktów dostępność jest widoczna ?</label>
                      <input type="text" name="ilosc" class="calkowita" value="<?php echo $info['quantity']; ?>" size="5" />
                    </p>
                    <?php } ?>
                    
                    <p>
                      <label>Czy można przy tej dostępności kupować ?</label>
                      <input type="radio" value="1" name="kupowanie" <?php echo (($info['shipping_mode'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                      <input type="radio" value="0" name="kupowanie" <?php echo (($info['shipping_mode'] == '0') ? 'checked="checked"' : ''); ?> /> nie               
                    </p>                             

                    <p>
                      <label>Ścieżka zdjęcia:</label>           
                      <input type="text" name="zdjecie" size="95" value="<?php echo $info['image']; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" />                 
                      <span class="usun_zdjecie toolTipTopText" data="foto" title="Usuń przypisane zdjęcie"></span>
                    </p>      

                    <div id="divfoto" style="padding-left:10px;display:none">
                      <label>Zdjęcie:</label>
                      <span id="fofoto">
                          <span class="zdjecie_tbl">
                              <img src="obrazki/_loader_small.gif" alt="" />
                          </span>
                      </span> 

                      <?php if (!empty($info['image'])) { ?>
                      <script type="text/javascript">
                      //<![CDATA[            
                      pokaz_obrazek_ajax('foto', '<?php echo $info['image']; ?>')
                      //]]>
                      </script>
                      <?php } ?>       
                      
                    </div>    
                    
                    <p style="padding:15px;padding-left:23px;"><span style="color:#ff0000">Wybierz jakiej dostępności dla porównywarek odpowiada edytowana dostępność</span></p>
                    
                    <p>
                      <label>Status dostępności CENEO:</label> 
                      <?php
                      $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('ceneo');
                      echo Funkcje::RozwijaneMenu('ceneo', $tablica, $info['ceneo'], 'style="width:300px;"');
                      unset($tablica);
                      ?>             
                    </p>    

                    <p>
                      <label>Status dostępności NOKAUT:</label>
                      <?php
                      $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('nokaut');
                      echo Funkcje::RozwijaneMenu('nokaut', $tablica, $info['nokaut'], 'style="width:300px;"');
                      unset($tablica);
                      ?>                         
                    </p>   

                    <p>
                      <label>Status dostępności OKAZJE.info:</label>  
                      <?php
                      $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('okazje');
                      echo Funkcje::RozwijaneMenu('okazje', $tablica, $info['okazje'], 'style="width:300px;"');
                      unset($tablica);
                      ?>                 
                    </p> 
                    
                    <p>
                      <label>Status dostępności SMARTBAY:</label>  
                      <?php
                      $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('smartbay');
                      echo Funkcje::RozwijaneMenu('smartbay', $tablica, $info['smartbay'], 'style="width:300px;"');
                      unset($tablica);
                      ?>                 
                    </p> 
                    
                    <p>
                      <label>Status dostępności Google shopping:</label>  
                      <?php
                      $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('googleshopping');
                      echo Funkcje::RozwijaneMenu('googleshopping', $tablica, $info['googleshopping'], 'style="width:300px;"');
                      unset($tablica);
                      ?>                 
                    </p> 

                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0');
                    //]]>
                    </script>                         
                
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('dostepnosci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
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
