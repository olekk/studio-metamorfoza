<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // Aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["nazwa"])),
                array('skrypt',$filtr->process($_POST["skrypt"])),
                array('klasa',$filtr->process($_POST["klasa"])),
                array('sortowanie',$filtr->process($_POST["sort"])),
                array('status',$filtr->process($_POST["status"])),
                array('prefix',$filtr->process($_POST["prefix"]))
        );
        //	
        $db->insert_query('modules_total' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);


        //Aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='".strtoupper($filtr->process($_POST['klasa']))."_TYTUL'");
        $pola = array(
            array('translate_constant',strtoupper($filtr->process($_POST['klasa'])).'_TYTUL'),
            array('section_id', '3')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['nazwa_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_'.$w])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_0'])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('podsumowanie.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('podsumowanie.php');
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
            $("#modulyForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                skrypt: {
                  required: true
                },
                klasa: {
                  required: true
                },
                sort: {
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

          <script type="text/javascript">
          //<![CDATA[
          function updateKeySkrypt() {
              var key=$("#skrypt").val();
              key=key.replace(" ","_");
              $("#skrypt").val(key);
          }
          function updateKeyKlasa() {
              var key=$("#klasa").val();
              key=key.replace(" ","_");
              $("#klasa").val(key);
          }
          //]]>
          </script>     


          <form action="moduly/podsumowanie_dodaj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                  <p>
                    <label class="required">Nazwa:</label>
                    <input type="text" name="nazwa" size="73" value="" id="nazwa" />
                  </p>

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
                        ?>     
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required">Treść wyświetlana w sklepie:</label>
                                <textarea cols="120" rows="3" name="nazwa_<?php echo $w; ?>" id="nazwa_0"></textarea>
                               <?php } else { ?>
                                <label>Tresc:</label>
                                <textarea cols="120" rows="3" name="nazwa_<?php echo $w; ?>"></textarea>
                               <?php } ?>
                            </p> 
                                        
                        </div>
                        <?php                    
                         }                    
                         ?>
                  </div>

                  <p>
                    <label class="required">Kolejność wyswietlania:</label>
                    <input type="text" name="sort" size="5" value="" id="sort" class="bestupper toolTip" title="Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania." />
                  </p>

                  <p>
                    <label>Status:</label>
                    <input type="radio" value="1" name="status" checked="checked" class="toolTipTop" title="Czy moduł ma być wliczany do wartości zamówienia" /> włączony
                    <input type="radio" value="0" name="status" class="toolTipTop" title="Czy moduł ma być wliczany do wartości zamówienia" /> wyłączony
                  </p>

                  <p>
                    <label>Wartość zamówienia:</label>
                    <input type="radio" value="1" name="prefix" checked="checked"  class="toolTipTop" title="Czy moduł ma być dodawany czy odejmowany przy wyliczaniu wartości zamówienia" /> zwiększa
                    <input type="radio" value="0" name="prefix"  class="toolTipTop" title="Czy moduł ma być dodawany czy odejmowany przy wyliczaniu wartości zamówienia" /> zmniejsza
                    <input type="radio" value="9" name="prefix"  class="toolTipTop" title="Czy moduł ma być dodawany czy odejmowany przy wyliczaniu wartości zamówienia" /> brak
                  </p>                     

                  <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                  <p>
                   <label class="required">Skrypt:</label>   
                   <input type="text" name="skrypt" id="skrypt" size="53" value="" class="toolTipText" title="Nazwa skryptu realizującego funkcje modułu." onkeyup="updateKeySkrypt();" />
                  </p>

                  <p>
                    <label class="required">Nazwa klasy:</label>   
                    <input type="text" name="klasa" id="klasa" size="53" value="" class="toolTipText" title="Nazwa klasy realizującej funkcje modułu." onkeyup="updateKeyKlasa();" />
                  </p>

                  <script type="text/javascript">
                  //<![CDATA[
                  gold_tabs('0');
                  //]]>
                  </script>                    

                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('podsumowanie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}