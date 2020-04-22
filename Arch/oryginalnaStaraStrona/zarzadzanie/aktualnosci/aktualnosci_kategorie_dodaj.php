<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('categories_image',$filtr->process($_POST['zdjecie'])),
                array('sort_order',$filtr->process($_POST['sort'])));
        
        $sql = $db->insert_query('newsdesk_categories' , $pola);
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
                    array('categories_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('categories_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('categories_meta_title_tag',$filtr->process($_POST['tytul_'.$w])),
                    array('categories_meta_desc_tag',$filtr->process($_POST['opis_'.$w])),        
                    array('categories_meta_keywords_tag',$filtr->process($_POST['slowa_'.$w])),
                    array('categories_description',$filtr->process($_POST['edytor_'.$w])));        
            $sql = $db->insert_query('newsdesk_categories_description' , $pola);
            unset($pola);
            //
        }

        unset($ile_jezykow);    

        Funkcje::PrzekierowanieURL('aktualnosci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="aktualnosci/aktualnosci_kategorie_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">    
            
                <input type="hidden" name="akcja" value="zapisz" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <!-- Skrypt do walidacji formularza -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                $("#poForm").validate({
                  rules: {
                    nazwa_0: {
                      required: true
                    }             
                  },
                  messages: {
                    nazwa_0: {
                      required: "Pole jest wymagane"
                    }              
                  }
                });
                });
                //]]>
                </script>                      

                <p>
                  <label>Ścieżka zdjęcia:</label>           
                  <input type="text" name="zdjecie" size="95" value="" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" />                 
                </p>      

                <div id="divfoto" style="padding-left:10px; display:none">
                    <label>Zdjęcie:</label>
                    <span id="fofoto">
                        <span class="zdjecie_tbl">
                            <img src="obrazki/_loader_small.gif" alt="" />
                        </span>
                    </span>  
                </div>
                
                <p>
                  <label>Kolejność wyświetlania:</label>
                  <input type="text" name="sort" size="5" value="" id="sort" />
                </p>              

                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\')">'.$ile_jezykow[$w]['text'].'</span>';
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
                                <label class="required">Nazwa kategorii:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label>Nazwa kategorii:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" />
                               <?php } ?>
                            </p> 
                            
                            <p>
                              <label>Meta Tagi - Tytuł:</label>
                              <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" />
                            </p> 
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                            </p>                              
                            
                            <p>
                              <label>Meta Tagi - Opis:</label>
                              <input type="text" name="opis_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="" />
                            </p>   
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                            </p>                            
                            
                            <p>
                              <label>Meta Tagi - Słowa kluczowe:</label>
                              <input type="text" name="slowa_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="" />
                            </p>    
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                            </p>                                
                            
                            <div class="edytor">
                              <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </div>                            

                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script type="text/javascript">
                //<![CDATA[
                gold_tabs('0','edytor_');
                //]]>
                </script> 
                
            </div>
            
            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>       
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
