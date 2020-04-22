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
                array('quantity',(($_POST['tryb'] == '1') ? $filtr->process($_POST['ilosc']) : '0')),
                array('mode',$filtr->process($_POST['tryb'])),
                array('shipping_mode',$filtr->process($_POST['kupowanie'])),
                array('image',$filtr->process($_POST['zdjecie'])),
                array('okazje',$filtr->process($_POST['okazje'])),
                array('nokaut',$filtr->process($_POST['nokaut'])),
                array('ceneo',$filtr->process($_POST['ceneo'])),
                array('smartbay',$filtr->process($_POST['smartbay'])),
                array('googleshopping',$filtr->process($_POST['googleshopping'])),
                );
        
        $sql = $db->insert_query('products_availability' , $pola);
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
                    array('products_availability_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('products_availability_name',$filtr->process($_POST['nazwa_'.$w])));           
            $sql = $db->insert_query('products_availability_description' , $pola);
            unset($pola);
        }
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('dostepnosci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('dostepnosci.php');
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
                ilosc: {
                  required: function(element) {
                    if ($("#il").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  }               
                },              
                nazwa_0: {
                  required: true
                }                
              },
              messages: {
                ilosc: {
                  required: "Pole jest wymagane"
                },              
                nazwa_0: {
                  required: "Pole jest wymagane"
                }               
              }
            });
          });
          //]]>
          </script>     

          <form action="slowniki/dostepnosci_dodaj.php" method="post" id="slownikForm" class="cmxform">          

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
            
                <p>
                  <label>Tryb wyświetlania:</label>
                  <input type="radio" value="1" name="tryb" onclick="$('#il').slideDown()" checked="checked" class="toolTipTopText" title="Tryb automatyczny - oznacza wyświetlanie dostępności produktu w zależności od stanu magazynowego" /> automatyczny
                  <input type="radio" value="0" name="tryb" onclick="$('#il').slideUp()" class="toolTipTopText" title="Tryb ręczny - oznacza przypisanie na stałe dostępności do produktu" /> ręczny                  
                </p> 

                <p id="il">
                  <label class="required">Od jakiej ilości produktów dostępność jest widoczna ?</label>
                  <input type="text" name="ilosc" class="calkowita" id="ilosc" value="" size="5" />
                </p>
                
                <p>
                  <label>Czy można przy tej dostępności kupować ?</label>
                  <input type="radio" value="1" name="kupowanie" checked="checked" /> tak
                  <input type="radio" value="0" name="kupowanie" /> nie               
                </p>                                

                <p>
                  <label>Ścieżka zdjęcia:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" />                 
                  <span class="usun_zdjecie toolTipTopText" data="foto" title="Usuń przypisane zdjęcie"></span>
                </p>      

                <div id="divfoto" style="padding-left:10px; display:none">
                  <label>Zdjęcie:</label>
                  <span id="fofoto">
                      <span class="zdjecie_tbl">
                          <img src="obrazki/_loader_small.gif" alt="" />
                      </span>
                  </span> 
                </div>   
                
                <p style="padding:15px;padding-left:23px;"><span style="color:#ff0000">Wybierz jakiej dostępności dla porównywarek odpowiada dodawana dostępność</span></p>
                
                <p>
                  <label>Status dostępności CENEO:</label> 
                  <?php
                  $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('ceneo');
                  echo Funkcje::RozwijaneMenu('ceneo', $tablica, '', 'style="width:300px;"');
                  unset($tablica);
                  ?>             
                </p>    

                <p>
                  <label>Status dostępności NOKAUT:</label>
                  <?php
                  $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('nokaut');
                  echo Funkcje::RozwijaneMenu('okazje', $tablica, '', 'style="width:300px;"');
                  unset($tablica);
                  ?>                         
                </p>   

                <p>
                  <label>Status dostępności OKAZJE.info:</label>  
                  <?php
                  $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('okazje');
                  echo Funkcje::RozwijaneMenu('okazje', $tablica, '', 'style="width:300px;"');
                  unset($tablica);
                  ?>                 
                </p>                   
                
                    <p>
                      <label>Status dostępności SMARTBAY:</label>  
                      <?php
                      $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('smartbay');
                      echo Funkcje::RozwijaneMenu('smartbay', $tablica, '', 'style="width:300px;"');
                      unset($tablica);
                      ?>                 
                    </p> 
                    
                    <p>
                      <label>Status dostępności Google shopping:</label>  
                      <?php
                      $tablica = Porownywarki::TablicaDostepnosciNiezdefiniowanych('googleshopping');
                      echo Funkcje::RozwijaneMenu('googleshopping', $tablica, '', 'style="width:300px;"');
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

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
