<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        // kasuje rekordy w tablicy
        //$db->delete_query('headertags' , " page_id = '".$filtr->process($_POST["id"])."'");        
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //        
            $id_jezyka = $w+1;
            $pola = array(
                    array('page_name',$filtr->process($_POST["skrypt"])),
                    array('page_title',( $_POST['tytul_'.$w] == '' ? $filtr->process($_POST['tytul_0']) : $filtr->process($_POST['tytul_'.$w]))),
                    array('page_description',( $_POST['opis_'.$w] == '' ? $filtr->process($_POST['opis_0']) : $filtr->process($_POST['opis_'.$w]))),
                    array('page_keywords',( $_POST['slowa_'.$w] == '' ? $filtr->process($_POST['slowa_0']) : $filtr->process($_POST['slowa_'.$w]))),
                    array('append_default',$filtr->process($_POST['domyslne_'.$w])),
                    array('sortorder',$filtr->process($_POST['sortowanie_'.$w])));
            $db->update_query('headertags' , $pola, " page_id = '".(int)$filtr->process($_POST["id"])."' AND language_id = '".$id_jezyka."'");	
            unset($pola);
            //          
        }              
        //
        Funkcje::PrzekierowanieURL('meta_tagi_podstrony.php?id_poz='.$_POST["id"]);
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
            $("#metaForm").validate({
              rules: {
                tytul: {
                  required: true
                }               
              }
            });
          });
          //]]>
          </script>         

          <form action="pozycjonowanie/meta_tagi_podstrony_edytuj.php" method="post" id="metaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
                <?php
                if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {
                    ?>

                    <div class="pozycja_edytowana">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo $filtr->process($_GET['id_poz']); ?>" />
                        
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
                                $zapytanie_jezyk = "SELECT DISTINCT * FROM headertags where page_id = '".$filtr->process($_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                $sqls = $db->open_query($zapytanie_jezyk);
                                $nazwa = $sqls->fetch_assoc();   
                                
                                $nazwa_skryptu = $nazwa['page_name'];

                                ?>
                                
                                <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                
                                    <p>
                                       <?php if ($w == '0') { ?>
                                        <label class="required">Tytuł strony:</label>
                                        <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo $nazwa['page_title']; ?>" id="tytul_0" class="required" />
                                       <?php } else { ?>
                                        <label>Tytuł strony:</label>   
                                        <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo $nazwa['page_title']; ?>" />
                                       <?php } ?>
                                    </p> 
                                    
                                    <p class="LicznikMeta">
                                      <label></label>
                                      Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['page_title'])); ?></span>
                                      zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                    </p>                                    

                                    <p>
                                      <label>Opis strony:</label>   
                                      <textarea name="opis_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['page_description']; ?></textarea>
                                    </p> 
                                    
                                    <p class="LicznikMeta">
                                      <label></label>
                                      Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['page_description'])); ?></span>
                                      zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                    </p>                                     

                                    <p>
                                      <label>Słowa kluczowe:</label>   
                                      <textarea name="slowa_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['page_keywords']; ?></textarea>
                                    </p>

                                    <p class="LicznikMeta">
                                      <label></label>
                                      Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['page_keywords'])); ?></span>
                                      zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                    </p>                                      

                                    <p>
                                      <label>Czy dołączać wartości domyślne:</label>
                                      <input type="radio" name="domyslne_<?php echo $w; ?>" value="1" class="toolTipTop" title="Do sekcji META będą dołączone wartości domyślne ustawione dla serwisu" <?php echo (($nazwa['append_default'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                                      <input type="radio" name="domyslne_<?php echo $w; ?>" value="0" class="toolTipTop" title="Do sekcji META nie będą dołączone wartości domyślne ustawione dla serwisu" <?php echo (($nazwa['append_default'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                                    </p>

                                    <p>
                                      <label>Jak dołączać wartości domyślne:</label>
                                      <input type="radio" name="sortowanie_<?php echo $w; ?>" value="1" class="toolTipTop" title="Wartości domyślne ustawione dla serwisu dołączone na początku" <?php echo (($nazwa['sortorder'] == '1') ? 'checked="checked"' : ''); ?> /> początek
                                      <input type="radio" name="sortowanie_<?php echo $w; ?>" value="0" class="toolTipTop" title="Wartości domyślne ustawione dla serwisu dołączone po wartościach indywidualnych" <?php echo (($nazwa['sortorder'] == '0') ? 'checked="checked"' : ''); ?> /> koniec
                                    </p>

                                </div>
                                <?php                    
                                $db->close_query($sqls);
                                unset($zapytanie_jezyk, $nazwa);
                            }                    
                            ?>                      
                        </div>
                        
                        <script type="text/javascript">
                        //<![CDATA[
                        gold_tabs('0');
                        //]]>
                        </script>  
                        
                        <p>
                          <label>Nazwa skryptu:</label>
                          <input type="text" name="skrypt" size="53" value="<?php echo $nazwa_skryptu; ?>" readonly="readonly" />
                        </p>
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('meta_tagi_podstrony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','pozycjonowanie');">Powrót</button>           
                    </div>

                    <?php
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
