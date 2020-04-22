<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_modu = $filtr->process($_POST['id']);
        //
        $pola = array(
                array('modul_header',(int)$_POST['naglowek']),
                array('modul_description',$filtr->process($_POST['opis'])),
                array('modul_display',$filtr->process($_POST['wyswietla'])),
                array('modul_localization',(int)$_POST['polozenie']),
                array('modul_rwd',(int)$_POST['rwd']),
                array('modul_rwd_resolution',(int)$_POST['rwd_mala_rozdzielczosc']));                
                
        // jezeli ma byc wyswietlany na podstronach
        if ( (int)$_POST['polozenie'] == 2 ) {
            $pola[] = array('modul_localization_position','gora');
          } else {
            $pola[] = array('modul_localization_position',$filtr->process($_POST['modul_podstrony_polozenie']));
        }
             
        // jezeli wybrano plik php
        if ($_POST['tryb'] == 'plik') {
            $pola[] = array('modul_file',$filtr->process($_POST['plik']));         
        }
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'strona') {
            $pola[] = array('modul_pages_id',$filtr->process($_POST['stronainfo']));
        }       
        // jezeli wybrano strone informacyjna
        if ($_POST['tryb'] == 'java') {
            $pola[] = array('modul_code',$_POST['kod']);
        }    

        // jezeli jest indywidualny modul
        if ($_POST['modul_wyglad'] == '1') {
            $pola[] = array('modul_theme',$filtr->process($_POST['modul_wyglad']));
            $pola[] = array('modul_theme_file',$filtr->process($_POST['plik_wyglad']));
          } else {
            $pola[] = array('modul_theme','0');
            $pola[] = array('modul_theme_file','');
        }        
        
        $sql = $db->update_query('theme_modules' , $pola, " modul_id = '".$id_modu."'");
        unset($pola);
        
        $db->delete_query('theme_modules_description' , " modul_id = '".$id_modu."'");
        
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
                    array('modul_id',$id_modu),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('modul_title',$filtr->process($_POST['nazwa_'.$w])));           
            $sql = $db->insert_query('theme_modules_description' , $pola);
            unset($pola);
        }
        //
        // jezeli jest strona informacyjna doda do strony info ze jest wyswietlana w module
        if ($_POST['tryb'] == 'strona') {
            //
            // sprawdzi czy zostala zmieniona strona - jezeli tak to usunie ze starej ikone ze jest przypisana do modulu
            if ( $_POST['stronainfo'] != $_POST['poprzednie_id'] ) {
                //
                $pola = array( array('pages_modul',0) );
                $db->update_query('pages' , $pola, 'pages_id = ' . (int)$_POST['poprzednie_id']);
                unset($pola);
                //            
            }
            //
            $pola = array( array('pages_modul',1) );
            $db->update_query('pages' , $pola, 'pages_id = ' . (int)$_POST['stronainfo']);
            unset($pola);
            //
            // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach
            Funkcje::UsuwanieWygladu('strona',$filtr->process($_POST["stronainfo"]));
            //            
        }          
        //
        // sprawdza czy sa jakies dodatkowe ustawienia
        foreach($_POST as $key => $value) {    
            //
            if (substr($key,0,2) == "__") {
                //
                $pola = array(array('value',$filtr->process($value)));
                $sql = $db->update_query('settings' , $pola, " code = '".substr($key,2)."'");
                unset($pola);                
                //
            }
            //
        }
        //        
        if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
          
            Funkcje::PrzekierowanieURL('wyglad.php?zakladka='.(int)$_POST['zakladka']);
          
          } else if ( isset($_POST['strona']) && (int)$_POST['strona'] > 0 ) {
          
            Funkcje::PrzekierowanieURL('/zarzadzanie/strony_informacyjne/strony_informacyjne_edytuj.php?id_poz='.(int)$_POST['strona']);
          
          } else {
          
            Funkcje::PrzekierowanieURL('srodek.php?id_poz='.(int)$_POST["id"]);
            
        }
            
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">        

          <form action="wyglad/srodek_edytuj.php" method="post" id="srodekForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            if ( isset($_GET['id_modul']) && (int)$_GET['id_modul'] > 0 && isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) {
                 $_GET['id_strony'] = (int)$_GET['id_poz'];
                 $_GET['id_poz'] = (int)$_GET['id_modul'];                 
            }
            
            $zapytanie = "select * from theme_modules where modul_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <!-- Skrypt do walidacji formularza -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                $("#srodekForm").validate({
                  rules: {
                    <?php if ($info['modul_type'] == 'plik') { ?>
                    plik: {
                      required: true
                    },                 
                    <?php } ?>
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
                    } 
                  },
                  messages: {
                    <?php if ($info['modul_type'] == 'plik') { ?>
                    plik: {
                      required: "Pole jest wymagane"
                    },                   
                    <?php } ?>              
                    nazwa_0: {
                      required: "Pole jest wymagane"
                    },
                    plik_wyglad: {
                      required: "Pole jest wymagane"
                    }             
                  }
                });
                });
                
                function zmien_wyglad(id) {
                    if (id == 0) {
                        $('#wyglad').slideUp();
                       } else {
                        $('#wyglad').slideDown();
                    }
                }  

                function zmien_polozenie(id) {
                    if (id == 2) {
                        $('#podstrony').slideUp();
                       } else {
                        $('#podstrony').slideDown();
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
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>
                    
                    <?php if (isset($_GET['id_modul']) && (int)$_GET['id_modul'] > 0 && isset($_GET['id_strony']) && (int)$_GET['id_strony'] > 0 ) { ?>
                    <input type="hidden" name="strona" value="<?php echo (int)$_GET['id_strony']; ?>" />
                    <?php } ?>                    
                    
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
                            $zapytanie_jezyk = "select distinct * from theme_modules_description where modul_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Nazwa modułu:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['modul_title']; ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label>Nazwa modułu:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['modul_title']; ?>" />
                                   <?php } ?>
                                </p> 
                                            
                            </div>
                            <?php                    
                        }
                        $db->close_query($sqls);
                        unset($zapytanie_jezyk, $nazwa);
                        ?>                      
                    </div>   

                    <input type="hidden" value="<?php echo $info['modul_type']; ?>" name="tryb" />                    

                    <p>
                        <label>Nagłówek modułu:</label>
                        <input type="radio" value="1" name="naglowek" class="toolTipTop" title="W module będzie wyświetał się nagłówek z nazwą modułu" <?php echo (($info['modul_header'] == 1) ? 'checked="checked"' : ''); ?> /> tak
                        <input type="radio" value="0" name="naglowek" class="toolTipTop" title="W module nie będzie się wyświetał nagłówek z nazwą modułu - tylko sama treść" <?php echo (($info['modul_header'] == 0) ? 'checked="checked"' : ''); ?> /> nie
                    </p>
                    
                    <p>
                        <label>Wygląd modułu:</label>
                        <input type="radio" value="0" name="modul_wyglad" onclick="zmien_wyglad(0)" class="toolTipTop" title="Zawartość modułu będzie wyświetlana w standardowym wyglądzie" <?php echo (($info['modul_theme'] == 0) ? 'checked="checked"' : ''); ?> /> standardowy
                        <input type="radio" value="1" name="modul_wyglad" onclick="zmien_wyglad(1)" class="toolTipTop" title="Zawartość modułu będzie wyświetlana w indywidualnym wyglądzie" <?php echo (($info['modul_theme'] == 1) ? 'checked="checked"' : ''); ?> /> indywidualny
                    </p>   

                    <div id="wyglad" <?php echo (($info['modul_theme'] == 0) ? 'style="display:none"' : ''); ?>>
                        <p>
                            <label class="required">Nazwa pliku w szablonie:</label>
                            <input type="text" name="plik_wyglad" id="plik_wyglad" value="<?php echo $info['modul_theme_file']; ?>" size="40" class="toolTipText" title="Nazwa pliku definiującego wygląd w szablonie np. moj_modul.tp" />
                        </p>
                    </div>   

                    <p>
                        <label>Wyświetlanie modułu:</label>
                        <input type="radio" value="1" name="polozenie" onclick="zmien_polozenie(1)" class="toolTipTop" title="Moduł będzie wyświetlany na wszystkich stronach" <?php echo (($info['modul_localization'] == 1) ? 'checked="checked"' : ''); ?> /> wszystkie strony
                        <input type="radio" value="3" name="polozenie" onclick="zmien_polozenie(3)" class="toolTipTop" title="Moduł będzie wyświetlany tylko na podstronach (bez strony głównej)" <?php echo (($info['modul_localization'] == 3) ? 'checked="checked"' : ''); ?> /> tylko podstrony
                        <input type="radio" value="2" name="polozenie" onclick="zmien_polozenie(2)" class="toolTipTop" title="Moduł będzie wyświetlany tylko na stronie głównej sklepu" <?php echo (($info['modul_localization'] == 2) ? 'checked="checked"' : ''); ?> /> tylko strona główna
                    </p>                      

                    <div id="podstrony" <?php echo (($info['modul_localization'] == 2) ? 'style="display:none"' : ''); ?>>
                        <p>
                            <label>W którym miejscu wyświetlać moduł na podstronach sklepu ?</label>
                            <input type="radio" value="gora" name="modul_podstrony_polozenie" class="toolTipTop" title="Zawartość modułu będzie wyświetlana w części górnej sklepu" <?php echo (($info['modul_localization_position'] == 'gora') ? 'checked="checked"' : ''); ?> /> w części górnej sklepu nad główną treścią
                            <input type="radio" value="dol" name="modul_podstrony_polozenie" class="toolTipTop" title="Zawartość modułu będzie wyświetlana w części dolnej sklepu" <?php echo (($info['modul_localization_position'] == 'dol') ? 'checked="checked"' : ''); ?> /> w części dolnej sklepu pod główną treścią
                            <span class="maleInfo">Powyższe ustawienie jest używane jeżeli moduł jest wyświetlany na podstronach sklepu i jest dodany w menu Wygląd / Ustawienia wyglądu / Moduły środkowe - do wyświetlania w <b>części głównej sklepu</b> (w części gdzie są wyświetlane boxy)</span>
                        </p>                       
                    </div>                  
                    
                    <?php if ($info['modul_type'] == 'plik') { ?>
                        <p>
                            <label class="required">Nazwa pliku:</label>
                            <input type="text" name="plik" id="plik" value="<?php echo $info['modul_file']; ?>" size="40" />
                        </p>
                        
                        <?php
                        // jezeli jest plik modu
                        if (is_file('../moduly/' . $info['modul_file'])) {
                            //
                            $lines = file('../moduly/' . $info['modul_file']);
                            for ($i = 0, $j = count($lines); $i < $j; $i++) {
                                //
                                if (strpos($lines[$i],'{{') > -1) {
                                    //
                                    $preg = preg_match('|{{([0-9A-Za-ząćęłńóśźż _,;:-?()]+?)}}|', $lines[$i], $matches);
                                    //
                                    $PodzialOpis = explode(';',str_replace(array('{{','}}'), '', $matches[0]));
                                    //
                                    echo '<p>';
                                    echo '<label>' . $PodzialOpis[1] . ':</label>' . "\n";
                                    
                                    // pobieranie danych z settings o stalej
                                    $zapytanieDef = "select distinct code, value, limit_values from settings where code = '" . $PodzialOpis[0] . "'";
                                    $sqld = $db->open_query($zapytanieDef);
                                    
                                    if ((int)$db->ile_rekordow($sqld) > 0) {
                                        //
                                        $infd = $sqld->fetch_assoc();
                                        //
                                        if ( strpos($infd['limit_values'], '::') > -1 ) {
                                        
                                            eval('$WynikFunkcji = ' . $infd['limit_values'] . ';'); 

                                            echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $WynikFunkcji, $infd['value']);
 
                                            unset($WynikFunkcji);

                                          } else { 

                                            //
                                            $Pod = array();
                                            foreach (explode(',', $infd['limit_values']) as $Wart) {
                                                $Pod[] = array('id' => $Wart, 'text' => $Wart);
                                            }
                                            echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $Pod, $infd['value']);
                                            unset($Pod);
                                        
                                        }
                                        
                                        unset($infd);
                                        
                                        //
                                      } else {
                                        //
                                        if ( strpos($PodzialOpis[3], '::') > -1 ) {
                                        
                                            eval('$WynikFunkcji = ' . $PodzialOpis[3] . ';');

                                            echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $WynikFunkcji, $PodzialOpis[2]);

                                            unset($WynikFunkcji);

                                          } else { 
                                        
                                            $Pod = array();
                                            foreach (explode(',', $PodzialOpis[3]) as $Wart) {
                                                $Pod[] = array('id' => $Wart, 'text' => $Wart);
                                            }                                    
                                            echo Funkcje::RozwijaneMenu('__'.$PodzialOpis[0], $Pod, $PodzialOpis[2]);
                                            //
                                        }
                                        
                                        // jezeli nie ma stalej trzeba ja dodac do bazy
                                        if (Funkcje::czyNiePuste(strtoupper($PodzialOpis[0]))) {
                                            $pola = array(array('code',strtoupper($PodzialOpis[0])),
                                                          array('description',$PodzialOpis[1]),
                                                          array('type','box'),
                                                          array('value',$PodzialOpis[2]),
                                                          array('limit_values',$PodzialOpis[3]));
                                            $db->insert_query('settings' , $pola);
                                            unset($pola);
                                        }
                                        //

                                        $db->close_query($sqld); 
                                        unset($zapytanieDef); 
                                        
                                    }
                                    
                                    echo '</p>';
                                }
                                //
                            }
                            //
                        }
                        ?>                        
                    <?php } ?>

                    <?php if ($info['modul_type'] == 'strona') { ?>
                        <p <?php echo ((isset($_GET['id_modul'])) ? 'style="display:none"' : ''); ?>>
                            <label>Wybierz stronę informacyjną:</label>
                            <?php
                            // pobieranie danych o stronach informacyjnych
                            $zapytanie_tmp = "select distinct * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.link = '' and (p.pages_modul = '0' or p.pages_id = '" . $info['modul_pages_id'] . "')";
                            $sqls = $db->open_query($zapytanie_tmp);
                            //
                            $tablica = array();
                            while ($infs = $sqls->fetch_assoc()) { 
                                $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title']);
                            }
                            $db->close_query($sqls); 
                            unset($zapytanie_tmp, $infs);    
                            //                          
                            echo Funkcje::RozwijaneMenu('stronainfo', $tablica, $info['modul_pages_id']); 
                            unset($tablica);
                            ?>
                            
                            <input name="poprzednie_id" type="hidden" value="<?php echo $info['modul_pages_id']; ?>" />
                            
                        </p>
                    <?php } ?>

                    <?php if ($info['modul_type'] == 'java') { ?>
                        <p>
                            <label>Wstaw kod:</label>
                            <textarea cols="120" rows="15" name="kod"><?php echo htmlspecialchars(html_entity_decode($info['modul_code'])); ?></textarea>
                        </p>
                    <?php } ?>
                    
                    <p>
                        <label>Opis modułu:</label>
                        <textarea name="opis" rows="5" cols="70" class="toolTipTopText" title="Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu"><?php echo $info['modul_description']; ?></textarea>
                    </p>

                    <p>
                        <label>Co wyświetla ?</label>
                        <input name="wyswietla" type="text" size="40" value="<?php echo $info['modul_display']; ?>" class="toolTipTopText" title="Co będzie wyświetlał moduł - informacja tylko dla administratora sklepu" />
                    </p> 

                    <br />

                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:100%;" />
                    
                    <div class="tytul_rwd">Ustawienia RWD</div>
                    
                    <p>
                        <label>Czy moduł działa w wersji RWD ?</label>
                        <input type="radio" value="0" name="rwd" class="toolTipTop" onclick="zmien_rwd(0)" title="Moduł est przystosowany do wyświetlania w wersji RWD" <?php echo (($info['modul_rwd'] == 0) ? 'checked="checked"' : ''); ?> /> nie
                        <input type="radio" value="1" name="rwd" class="toolTipTop" onclick="zmien_rwd(1)" title="Moduł nie jest przystosowany do wyświetlania w wersji RWD" <?php echo (($info['modul_rwd'] == 1) ? 'checked="checked"' : ''); ?> /> tak
                    </p>    

                    <div id="wyglad_rwd" <?php echo (($info['modul_rwd'] == 0) ? 'style="display:none"' : ''); ?>>
                    
                        <p>
                            <label>Czy zmieniać wygląd modułu przy małych rozdzielczościach ?</label>
                            <input type="radio" value="0" name="rwd_mala_rozdzielczosc" class="toolTipTop" title="Moduł będzie widoczny przy małych rozdzielczościach ekranu" <?php echo (($info['modul_rwd_resolution'] == 0) ? 'checked="checked"' : ''); ?> /> bez zmian
                            <input type="radio" value="1" name="rwd_mala_rozdzielczosc" class="toolTipTop" title="Moduł nie będzie widoczny przy małych rozdzielczościach ekranu" <?php echo (($info['modul_rwd_resolution'] == 1) ? 'checked="checked"' : ''); ?> /> ma być niewidoczny
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
                
                  <?php 
                  // jezeli jest get zakladka wraca do ustawien wygladu
                  if (isset($_GET['zakladka']) ) { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
                  
                  <?php } else if (isset($_GET['id_modul']) && isset($_GET['id_strony']) ) { 
                  
                  $_GET['id_poz'] = $_GET['id_strony'];
                  ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne_edytuj','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','strony_informacyjne');">Powrót</button> 
                  
                  <?php } else { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('srodek','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button> 
                  
                  <?php } ?>
                  
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
