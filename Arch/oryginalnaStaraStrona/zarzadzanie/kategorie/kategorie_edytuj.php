<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id = $filtr->process($_POST['id']);
    
        $pola = array(
                array('categories_image',$filtr->process($_POST['zdjecie'])),
                array('categories_icon',$filtr->process($_POST['ikona'])),
                array('sort_order',$filtr->process($_POST['sort'])));
                
        if ( isset($_POST['kolor_status']) ) {
             $pola[] = array('categories_color',$filtr->process($_POST['kolor']));
             $pola[] = array('categories_color_status',1);
           } else {
             $pola[] = array('categories_color','');
             $pola[] = array('categories_color_status',0);
        }
        
        if ( isset($_POST['kolor_status_tla']) ) {
             $pola[] = array('categories_background_color',$filtr->process($_POST['kolor_tla']));
             $pola[] = array('categories_background_color_status',1);
           } else {
             $pola[] = array('categories_background_color','');
             $pola[] = array('categories_background_color_status',0);
        }        
        
        $sql = $db->update_query('categories' , $pola, " categories_id = '".$id."'");
        
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('categories_description' , " categories_id = '".$filtr->process($_POST["id"])."'");        
        
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
                    array('categories_id',$id),
                    array('categories_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('categories_meta_title_tag',$filtr->process($_POST['tytul_'.$w])),
                    array('categories_meta_desc_tag',$filtr->process($_POST['opis_'.$w])),        
                    array('categories_meta_keywords_tag',$filtr->process($_POST['slowa_'.$w])),
                    array('categories_description',$filtr->process($_POST['edytor_'.$w])),
                    array('categories_info_name',$filtr->process($_POST['info_nazwa_'.$w])),
                    array('categories_info_text',$filtr->process($_POST['edytor_info_'.$w])),                    
                    array('language_id',$ile_jezykow[$w]['id']));        
            $sql = $db->insert_query('categories_description' , $pola);  
            unset($pola);
            //
        }

        unset($ile_jezykow);    

        if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
          
            Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
          
          } else {
          
            Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.$id);
            
        } 

    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php');     
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

        <form action="kategorie/kategorie_edytuj.php" method="post" id="poForm" class="cmxform"> 
    
        <div class="poleForm">
            <div class="naglowek">Edycja danych</div>        

            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from categories where categories_id = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                
                ?>
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['categories_id']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>                     

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                    <!-- Skrypt do walidacji formularza -->
                    <script type="text/javascript">
                    //<![CDATA[
                    $(document).ready(function() {
                    $("#poForm").validate({
                      rules: {
                        nazwa_0: {
                          required: true
                        },               
                      },
                      messages: {
                        nazwa_0: {
                          required: "Pole jest wymagane"
                        },             
                      }
                    });
                    });
                    //]]>
                    </script> 
                    
                    <script type="text/javascript" src="programy/jscolor/jscolor.js"></script> 

                    <p>
                      <label>Ścieżka zdjęcia:</label>           
                      <input type="text" name="zdjecie" size="95" value="<?php echo $info['categories_image']; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto"  />                 
                      <span class="usun_zdjecie toolTipTopText" data="foto" title="Usuń przypisane zdjęcie"></span>
                    </p>      

                    <div id="divfoto" style="padding-left:10px;display:none">
                        <label>Zdjęcie:</label>
                        <span id="fofoto">
                            <span class="zdjecie_tbl">
                                <img src="obrazki/_loader_small.gif" alt="" />
                            </span>
                        </span> 

                        <?php if (!empty($info['categories_image'])) { ?>
                        <script type="text/javascript">
                        //<![CDATA[            
                        pokaz_obrazek_ajax('foto', '<?php echo $info['categories_image']; ?>')
                        //]]>
                        </script>  
                        <?php } ?>   
                        
                    </div>
                    
                    <p>
                      <label>Grafika ikony:</label>           
                      <input type="text" name="ikona" size="95" value="<?php echo $info['categories_icon']; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('ikona','','<?php echo KATALOG_ZDJEC; ?>')" id="ikona"  />                 
                      <span class="usun_zdjecie toolTipTopText" data="ikona" title="Usuń przypisany obrazek"></span>
                    </p>      

                    <div id="divikona" style="padding-left:10px;display:none">
                        <label>Ikona:</label>
                        <span id="foikona">
                            <span class="zdjecie_tbl">
                                <img src="obrazki/_loader_small.gif" alt="" />
                            </span>
                        </span> 

                        <?php if (!empty($info['categories_icon'])) { ?>
                        <script type="text/javascript">
                        //<![CDATA[            
                        pokaz_obrazek_ajax('ikona', '<?php echo $info['categories_icon']; ?>')
                        //]]>
                        </script>  
                        <?php } ?>   
                        
                    </div>                    
                    
                    <p>
                      <label>Kolejność wyświetlania:</label>
                      <input type="text" name="sort" size="5" value="<?php echo $info['sort_order']; ?>" id="sort" />
                    </p> 

                    <p>
                      <label>Kolor nazwy kategorii:</label>
                      <input name="kolor" class="color toolTipTopText" style="-moz-box-shadow:none" value="<?php echo $info['categories_color']; ?>" size="8" title="Określa kolor wyświetlania nazwy kategorii w boxie kategorii" /> 
                      <input type="checkbox" name="kolor_status" class="toolTipTopText" value="1" <?php echo (($info['categories_color_status'] == 1) ? 'checked="checked"' : ''); ?>  title="Czy nazwa kategorii ma być wyświetlana w kolorze ?" /> wyświetlaj nazwę tej kategorii w kolorze
                    </p>   

                    <p>
                      <label>Kolor tła nazwy kategorii:</label>
                      <input name="kolor_tla" class="color toolTipTopText" style="-moz-box-shadow:none" value="<?php echo $info['categories_background_color']; ?>" size="8" title="Określa kolor tła pod nazwą kategorii w boxie kategorii" /> 
                      <input type="checkbox" name="kolor_status_tla" class="toolTipTopText" value="1" <?php echo (($info['categories_background_color_status'] == 1) ? 'checked="checked"' : ''); ?> title="Czy tło nazwy kategorii ma być wyświetlane w kolorze ?" /> wyświetlaj tło tej kategorii w kolorze
                    </p>                     

                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\',940,200,\'normalny\',\'edytor_info_\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <?php
                                // pobieranie danych jezykowych
                                $zapytanie_jezyk = "select distinct * from categories_description where categories_id = '".$filtr->process($_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                $sqls = $db->open_query($zapytanie_jezyk);
                                $kategoria = $sqls->fetch_assoc();   
                                ?>                                    
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Nazwa kategorii:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($kategoria['categories_name']); ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label>Nazwa kategorii:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($kategoria['categories_name']); ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <p>
                                  <label>Meta Tagi - Tytuł:</label>
                                  <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo $kategoria['categories_meta_title_tag']; ?>" />                                  
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($kategoria['categories_meta_title_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>
                                
                                <p>
                                  <label>Meta Tagi - Opis:</label>
                                  <input type="text" name="opis_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="<?php echo $kategoria['categories_meta_desc_tag']; ?>" />
                                </p>   
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($kategoria['categories_meta_desc_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                
                                
                                <p>
                                  <label>Meta Tagi - Słowa kluczowe:</label>
                                  <input type="text" name="slowa_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="<?php echo $kategoria['categories_meta_keywords_tag']; ?>" />
                                </p> 

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($kategoria['categories_meta_keywords_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>                                
                                
                                <br />

                                <p>
                                  <label style="margin-bottom:8px;">Opis kategorii:</label>
                                  <textarea cols="50" rows="20" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo $kategoria['categories_description']; ?></textarea>
                                </p> 
                                
                                <br />
                                
                                <div class="maleInfo">Informacja wyświetlana w formie zakładki na karcie produktu dla produktów przypisanych do kategorii</div>
                                
                                <p>
                                  <label>Nazwa zakładki:</label>
                                  <input type="text" name="info_nazwa_<?php echo $w; ?>" size="80" value="<?php echo $kategoria['categories_info_name']; ?>" />
                                </p>  
                                
                                <p style="padding-top:5px">
                                  <textarea cols="50" rows="30" id="edytor_info_<?php echo $w; ?>" name="edytor_info_<?php echo $w; ?>"><?php echo $kategoria['categories_info_text']; ?></textarea>                                  
                                </p>

                                <?php
                                $db->close_query($sqls);
                                unset($kategoria); 
                                ?>

                            </div>
                            <?php                    
                        }                    
                        ?>                      
                    </div>
                    
                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0','edytor_',940,200,'normalny','edytor_info_');
                    //]]>
                    </script> 
                    
                </div>
                    
                <div class="przyciski_dolne">
                
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  
                  <?php 
                  // jezeli jest get zakladka wraca do ustawien wygladu
                  if (isset($_GET['zakladka']) ) { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
                  
                  <?php } else { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>
                  
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