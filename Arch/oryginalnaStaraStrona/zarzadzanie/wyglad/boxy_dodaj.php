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
                array('box_status','0'),
                array('box_type',$filtr->process($_POST['tryb'])),
                array('box_header',$filtr->process($_POST['naglowek'])),
                array('box_description',$filtr->process($_POST['opis'])),
                array('box_display',$filtr->process($_POST['wyswietla'])),
                array('box_localization',$filtr->process((int)$_POST['polozenie'])),
                array('box_rwd',(int)$_POST['rwd']),
                array('box_rwd_resolution',(int)$_POST['rwd_mala_rozdzielczosc']));                
             
        // jezeli wybrano plik php
        if ($_POST['tryb'] == 'plik') {
            $pola[] = array('box_file',$filtr->process($_POST['plik']));
        }
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'strona') {
            $pola[] = array('box_pages_id',$filtr->process($_POST['stronainfo']));
        }       
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'java') {
            $pola[] = array('box_code',$_POST['kod']);
        }    

        // jezeli jest indywidualny box
        if ($_POST['box_wyglad'] == '1') {
            $pola[] = array('box_theme',$filtr->process($_POST['box_wyglad']));
            $pola[] = array('box_theme_file',$filtr->process($_POST['plik_wyglad']));
          } else {
            $pola[] = array('box_theme','0');
        }         
        
        $sql = $db->insert_query('theme_box' , $pola);
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
                    array('box_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('box_title',$filtr->process($_POST['nazwa_'.$w])));           
            $sql = $db->insert_query('theme_box_description' , $pola);
            unset($pola);
        }
        //
        // jezeli jest strona informacyjna doda do strony info ze jest wyswietlana w boxie
        if ($_POST['tryb'] == 'strona') {
            $pola = array( array('pages_modul',2) );
            $db->update_query('pages' , $pola, 'pages_id = ' . $filtr->process($_POST['stronainfo']));
            //
            // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
            Funkcje::UsuwanieWygladu('strona',$filtr->process($_POST["stronainfo"]));
            //            
        }          
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('boxy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('boxy.php');
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
            $("#boxForm").validate({
              rules: {
                nazwa_0: {
                  required: true
                },
                plik_wyglad: {
                  required: function(element) {
                    if ($("#wyglad").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  }
                },
                plik: {
                  required: function(element) {
                    if ($("#tryb_0").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  }
                }                 
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane"
                },
                plik_wyglad: {
                  required: "Pole jest wymagane"
                },
                plik: {
                  required: "Pole jest wymagane"
                }                
              }
            });
          });
          
          function zmien_tryb(id) {
            if ($('#tryb_' + id).css('display') == 'none') {
                $('#tryb_0').css('display','none'); 
                $('#tryb_1').css('display','none');
                $('#tryb_2').css('display','none');
                //
                $('#tryb_' + id).slideDown();
            }
          }  

          function zmien_wyglad(id) {
            if (id == 0) {
                $('#wyglad').slideUp();
               } else {
                $('#wyglad').slideDown();
            }
          }     
          
          function zmien_rwd(id) {
              if (id == 0) {
                  $('#wyglad_rwd').slideUp();
                 } else {
                  $('#wyglad_rwd').slideDown();
              }
          }             
          //]]>
          </script>     

          <form action="wyglad/boxy_dodaj.php" method="post" id="boxForm" class="cmxform">          

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
                                <label class="required">Nazwa boxu:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label>Nazwa boxu:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" />
                               <?php } ?>
                            </p> 
                                        
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>                
            
                <p>
                  <label>Rodzaj boxu:</label>
                  <input type="radio" value="plik" name="tryb" class="toolTipTop" onclick="zmien_tryb(0)" title="Box będzie wyświetlał zawartość generowaną przez plik napisany w języku PHP" checked="checked" /> plik php
                  
                  <?php
                  // sprawdza czy sa strony informacje
                  $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and p.pages_modul = '0'";
                  $sqls = $db->open_query($zapytanie_tmp);      
                  if ((int)$db->ile_rekordow($sqls) > 0) {
                  ?>
                  <input type="radio" value="strona" name="tryb" class="toolTipTop" onclick="zmien_tryb(1)" title="Box będzie wyświetlał treść wybranej strony informacyjnej" /> strona informacyjna
                  <?php } ?>
                  
                  <input type="radio" value="java" name="tryb" class="toolTipTop" onclick="zmien_tryb(2)" title="Box będzie wyświetlał wynik działania skryptu" /> dowolny skrypt np javascript
                </p> 

                <p>
                    <label>Nagłówek boxu:</label>
                    <input type="radio" value="1" name="naglowek" class="toolTipTop" title="W boxie będzie wyświetał się nagłówek z nazwą boxu" checked="checked" /> tak
                    <input type="radio" value="0" name="naglowek" class="toolTipTop" title="W boxie nie będzie się wyświetał nagłówek z nazwą boxu - tylko sama treść boxu" /> nie
                </p>
                
                <p>
                    <label>Wygląd boxu:</label>
                    <input type="radio" value="0" name="box_wyglad" onclick="zmien_wyglad(0)" class="toolTipTop" title="Zawartość boxu będzie wyświetlana w standardowym wyglądzie boxu szablonu" checked="checked" /> standardowy
                    <input type="radio" value="1" name="box_wyglad" onclick="zmien_wyglad(1)" class="toolTipTop" title="Zawartość boxu będzie wyświetlana w indywidualnym wyglądzie boxu szablonu" /> indywidualny
                </p>   

                <div id="wyglad" style="display:none">
                    <p>
                        <label class="required">Nazwa pliku w szablonie:</label>
                        <input type="text" name="plik_wyglad" id="plik_wyglad" value="" size="40" class="toolTipText" title="Nazwa pliku definiującego wygląd w szablonie np. moj_box.tp" />
                    </p>
                </div>     

                <p>
                    <label>Wyświetlanie boxu:</label>
                    <input type="radio" value="1" name="polozenie" class="toolTipTop" title="Box będzie wyświetlany na wszystkich stronach" checked="checked" /> wszystkie strony
                    <input type="radio" value="3" name="polozenie" class="toolTipTop" title="Box będzie wyświetlany tylko na podstronach (bez strony głównej)" /> tylko podstrony
                    <input type="radio" value="2" name="polozenie" class="toolTipTop" title="Box będzie wyświetlany tylko na stronie głównej sklepu" /> tylko strona główna
                </p>                 
                
                <div id="tryb_0">
                    <p>
                        <label class="required">Nazwa pliku:</label>
                        <input type="text" name="plik" id="plik" value="" size="40" />
                    </p>
                </div>

                <div id="tryb_1" style="display:none">
                    <p>
                        <label>Wybierz stronę informacyjną:</label>
                        <?php
                        // pobieranie danych o stronach informacyjnych
                        $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and p.pages_modul = '0'";
                        $sqls = $db->open_query($zapytanie_tmp);
                        //
                        $tablica = array();
                        while ($infs = $sqls->fetch_assoc()) { 
                            $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title']);
                        }
                        $db->close_query($sqls); 
                        unset($zapytanie_tmp, $infs);    
                        //                          
                        echo Funkcje::RozwijaneMenu('stronainfo', $tablica); 
                        unset($tablica);
                        ?>
                    </p>
                </div>

                <div id="tryb_2" style="display:none">
                    <p>
                        <label>Wstaw kod:</label>
                        <textarea cols="120" rows="15" name="kod"></textarea>
                    </p>
                </div>     

                <p>
                    <label>Opis boxu:</label>
                    <textarea name="opis" rows="5" cols="70" class="toolTipTopText" title="Opis co będzie wyświetlał box - informacja tylko dla administratora sklepu"></textarea>
                </p>  

                <p>
                    <label>Co wyświetla ?</label>
                    <input name="wyswietla" type="text" size="40" value="" class="toolTipTopText" title="Co będzie wyświetlał box - informacja tylko dla administratora sklepu" />
                </p> 

                <br />

                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:100%;" />
                
                <div class="tytul_rwd">Ustawienia RWD</div>
                
                <p>
                    <label>Czy box działa w wersji RWD ?</label>
                    <input type="radio" value="0" name="rwd" class="toolTipTop" onclick="zmien_rwd(0)" title="Box jest przystosowany do wyświetlania w wersji RWD" /> nie
                    <input type="radio" value="1" name="rwd" class="toolTipTop" onclick="zmien_rwd(1)" title="Box nie jest przystosowany do wyświetlania w wersji RWD" checked="checked" /> tak
                </p>    

                <div id="wyglad_rwd">
                
                    <p>
                        <label>Czy zmieniać wygląd boxu przy małych rozdzielczościach ?</label>
                        <input type="radio" value="0" name="rwd_mala_rozdzielczosc" class="toolTipTop" title="Box będzie widoczny przy małych rozdzielczościach ekranu" /> bez zmian
                        <input type="radio" value="1" name="rwd_mala_rozdzielczosc" class="toolTipTop" title="Box nie będzie widoczny przy małych rozdzielczościach ekranu" checked="checked" /> ma być niewidoczny
                        <input type="radio" value="2" name="rwd_mala_rozdzielczosc" class="toolTipTop" title="Box będzie zminimalizowany z możliwością rozwinięcia całej treści boxu" /> ma być zminimalizowany
                    </p>

                </div>                
                
                <script type="text/javascript">
                //<![CDATA[
                gold_tabs('0');
                //]]>
                </script>                 
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('boxy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
