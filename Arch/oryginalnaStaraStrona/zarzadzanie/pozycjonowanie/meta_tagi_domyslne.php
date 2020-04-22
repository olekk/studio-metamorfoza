<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        // kasuje rekordy w tablicy
        $db->truncate_query('headertags_default');        
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('default_title',( $_POST['tytul_'.$w] == '' ? $filtr->process($_POST['tytul_0']) : $filtr->process($_POST['tytul_'.$w]))),
                    array('default_description',( $_POST['opis_'.$w] == '' ? $filtr->process($_POST['opis_0']) : $filtr->process($_POST['opis_'.$w]))),
                    array('default_keywords',( $_POST['slowa_'.$w] == '' ? $filtr->process($_POST['slowa_0']) : $filtr->process($_POST['slowa_'.$w]))),
                    array('default_index_title',( $_POST['tytul_'.$w] == '' ? $filtr->process($_POST['tytul_index_0']) : $filtr->process($_POST['tytul_index_'.$w]))),
                    array('default_index_description',( $_POST['opis_'.$w] == '' ? $filtr->process($_POST['opis_index_0']) : $filtr->process($_POST['opis_index_'.$w]))),
                    array('default_index_keywords',( $_POST['slowa_'.$w] == '' ? $filtr->process($_POST['slowa_index_0']) : $filtr->process($_POST['slowa_index_'.$w]))),                    
                    array('language_id',$ile_jezykow[$w]['id']));

            $db->insert_query('headertags_default' , $pola);
            unset($pola);
            //           
        }

        $wynik = '<div id="meta" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';
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
            setTimeout(function() {
                $('#meta').fadeOut();
            }, 3000);
          });
          //]]>
          </script>         

          <form action="pozycjonowanie/meta_tagi_domyslne.php" method="post" id="metaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych sekcji META strony</div>
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <span class="maleInfo">Ustawienie domyślnych wartości znaczników tytuł, opis, słowa kluczowe dla strony głównej sklepu oraz wszystkich pozostałych podstron, które nie posiadają definiowanych własnych znaczników META.</span>
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
                            $zapytanie_jezyk = "select * from headertags_default where language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <div class="tytulMeta">
                                    Wartości domyślne dla strony głównej sklepu
                                </div>
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Domyślny tytuł:</label>
                                    <input type="text" name="tytul_index_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo Funkcje::formatujTekstInput($nazwa['default_index_title']); ?>" class="required" />
                                   <?php } else { ?>
                                    <label>Domyślny tytuł:</label>   
                                    <input type="text" name="tytul_index_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo Funkcje::formatujTekstInput($nazwa['default_index_title']); ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['default_index_title'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                

                                <p>
                                  <label>Domyślny opis:</label>   
                                  <textarea name="opis_index_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_index_description']; ?></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['default_index_description'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                

                                <p>
                                  <label>Domyślne słowa kluczowe:</label>   
                                  <textarea name="slowa_index_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_index_keywords']; ?></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['default_index_keywords'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>                                 
                                
                                <div class="tytulMeta">
                                    Wartości domyślne dla podstron sklepu
                                </div>
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Domyślny tytuł:</label>
                                    <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronyNazwa_<?php echo $w; ?>')" value="<?php echo $nazwa['default_title']; ?>" class="required" />
                                   <?php } else { ?>
                                    <label>Domyślny tytuł:</label>   
                                    <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronyNazwa_<?php echo $w; ?>')" value="<?php echo $nazwa['default_title']; ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowPodstronyNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['default_title'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowPodstronyNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                

                                <p>
                                  <label>Domyślny opis:</label>   
                                  <textarea name="opis_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronyOpis_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_description']; ?></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowPodstronyOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['default_description'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowPodstronyOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                

                                <p>
                                  <label>Domyślne słowa kluczowe:</label>   
                                  <textarea name="slowa_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowPodstronySlowa_<?php echo $w; ?>')" cols="117" rows="3"><?php echo $nazwa['default_keywords']; ?></textarea>
                                </p> 

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowPodstronySlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['default_keywords'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowPodstronySlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>                                 
                                
                            </div>
                            <?php                    
                            $db->close_query($sqls);
                            unset($zapytanie_jezyk);
                        }                    
                        ?>                      
                    </div>
                    
                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0');
                    //]]>
                    </script>  
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo $wynik; ?>
                </div>                 
          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
