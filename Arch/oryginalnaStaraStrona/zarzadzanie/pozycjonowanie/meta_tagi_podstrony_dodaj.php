<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $zapytanie = "SELECT MAX(page_id) AS id FROM headertags";
        $sql = $db->open_query($zapytanie);
        $info = $sql->fetch_assoc();   

        $id_dodanej_pozycji = $info['id'] + 1;
        $db->close_query($sql);
        unset($zapytanie, $info);

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //        
            $pola = array(
                    array('page_id',$id_dodanej_pozycji),
                    array('page_name',$filtr->process($_POST["skrypt"])),
                    array('page_title',( $_POST['tytul_'.$w] == '' ? $filtr->process($_POST['tytul_0']) : $filtr->process($_POST['tytul_'.$w]))),
                    array('page_description',( $_POST['opis_'.$w] == '' ? $filtr->process($_POST['opis_0']) : $filtr->process($_POST['opis_'.$w]))),
                    array('page_keywords',( $_POST['slowa_'.$w] == '' ? $filtr->process($_POST['slowa_0']) : $filtr->process($_POST['slowa_'.$w]))),
                    array('append_default',$filtr->process($_POST['domyslne_'.$w])),
                    array('sortorder',$filtr->process($_POST['sortowanie_'.$w])),
                    array('language_id',$ile_jezykow[$w]['id']));

            $db->insert_query('headertags' , $pola);
            unset($pola);
            //          
        }              
        //

        Funkcje::PrzekierowanieURL('meta_tagi_podstrony.php?id_poz='.$id_dodanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodanie pozycji</div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $.validator.addMethod("valueNotEquals", function (value, element, arg) {
              return arg != value;
            }, "Wybierz opcję");

            $("#metaForm").validate({
              rules: {
                skrypt: { required: true, valueNotEquals: "0" }
              },
              messages: {
                skrypt: {
                  valueNotEquals: "Brak plików dla których można zdefiniować dane"
                }
              }

            });
          });
          //]]>
          </script>         

          <form action="pozycjonowanie/meta_tagi_podstrony_dodaj.php" method="post" id="metaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodanie danych</div>
            
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
                                    <label class="required">Tytuł strony:</label>
                                    <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" id="tytul_0" class="required" />
                                   <?php } else { ?>
                                    <label>Tytuł strony:</label>   
                                    <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" />
                                   <?php } ?>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                   

                                <p>
                                  <label>Opis strony:</label>   
                                  <textarea name="opis_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" cols="117" rows="3"></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                  

                                <p>
                                  <label>Słowa kluczowe:</label>   
                                  <textarea name="slowa_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" cols="117" rows="3"></textarea>
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>                                  

                                <p>
                                  <label>Czy dołączać wartości domyślne:</label>
                                  <input type="radio" name="domyslne_<?php echo $w; ?>" value="1" class="toolTipTop" title="Do sekcji META będą dołączone wartości domyślne ustawione dla serwisu" checked="checked" /> tak
                                  <input type="radio" name="domyslne_<?php echo $w; ?>" value="0" class="toolTipTop" title="Do sekcji META nie będą dołączone wartości domyślne ustawione dla serwisu" /> nie
                                </p>

                                <p>
                                  <label>Jak dołączać wartości domyślne:</label>
                                  <input type="radio" name="sortowanie_<?php echo $w; ?>" value="1" class="toolTipTop" title="Wartości domyślne ustawione dla serwisu dołączone na początku" checked="checked" /> początek
                                  <input type="radio" name="sortowanie_<?php echo $w; ?>" value="0" class="toolTipTop" title="Wartości domyślne ustawione dla serwisu dołączone po wartościach indywidualnych"  /> koniec
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
                      <label class="required">Nazwa skryptu:</label>
                      <?php
                      $tablica_plikow = Funkcje::ListaPlikow( '', false, 
                        array('koniec.php',
                              'blad.php',
                              'brak_strony.php',
                              'start.php',
                              'produkt.php',
                              'produkty.php',
                              'ankieta.php',
                              'formularz.php',
                              'galeria.php',
                              'kategoria_artykulow.php',
                              'platnosc_koniec.php',
                              'listing.php',
                              'listing_dol.php',
                              'listing_gora.php',
                              'index.php',
                              'partner.php',
                              'strona_informacyjna.php',
                              'reklama.php',
                              'pp_bannery.php'
                        ));
                      echo Funkcje::RozwijaneMenu('skrypt', $tablica_plikow,'', 'style="width:300px;" class="toolTipText" title="Nazwa skryptu generującego stronę, dla której są definiowane META TAGI"');
                      ?>
                    </p>
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('meta_tagi_podstrony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','pozycjonowanie');">Powrót</button>           
                </div>                 
          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
