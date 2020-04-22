<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_producent = $filtr->process($_POST['id']);
    
        $pola = array(
                array('manufacturers_image',$filtr->process($_POST['zdjecie'])),
                array('manufacturers_name',$filtr->process($_POST['nazwa'])));
        
        $sql = $db->update_query('manufacturers' , $pola, " manufacturers_id = '".$id_producent."'");
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('manufacturers_info' , " manufacturers_id = '".$id_producent."'");     
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            $pola = array(
                    array('manufacturers_id',$id_producent),
                    array('manufacturers_url',$filtr->process($_POST['url_'.$w])),
                    array('manufacturers_meta_title_tag',$filtr->process($_POST['tytul_'.$w])),
                    array('manufacturers_meta_desc_tag',$filtr->process($_POST['opis_'.$w])),      
                    array('manufacturers_meta_keywords_tag',$filtr->process($_POST['slowa_'.$w])),
                    array('manufacturers_description',$filtr->process($_POST['edytor_'.$w])),
                    array('manufacturers_info_name',$filtr->process($_POST['info_nazwa_'.$w])),
                    array('manufacturers_info_text',$filtr->process($_POST['edytor_info_'.$w])),
                    array('languages_id',$ile_jezykow[$w]['id']));           
            $sql = $db->insert_query('manufacturers_info' , $pola);
            unset($pola);
        }

        unset($ile_jezykow);    

        Funkcje::PrzekierowanieURL('producenci.php?id_poz='.$id_producent);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="producenci/producenci_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct m.manufacturers_id, m.manufacturers_name, m.manufacturers_image from manufacturers m where m.manufacturers_id = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['manufacturers_id']; ?>" />

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                    <!-- Skrypt do walidacji formularza -->
                    <script type="text/javascript">
                    //<![CDATA[
                    $(document).ready(function() {
                    $("#poForm").validate({
                      rules: {
                        nazwa: {
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

                    <p>
                      <label class="required">Nazwa producenta:</label>
                      <input type="text" name="nazwa" size="80" value="<?php echo Funkcje::formatujTekstInput($info['manufacturers_name']); ?>" id="nazwa" />
                    </p>                  

                    <p>
                      <label>Ścieżka zdjęcia:</label>           
                      <input type="text" name="zdjecie" size="95" value="<?php echo $info['manufacturers_image']; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" />                 
                      <span class="usun_zdjecie toolTipTopText" data="foto" title="Usuń przypisane zdjęcie"></span>
                    </p>      
                    
                    <div id="divfoto" style="padding-left:10px;display:none">
                      <label>Zdjęcie:</label>
                      <span id="fofoto">
                          <span class="zdjecie_tbl">
                              <img src="obrazki/_loader_small.gif" alt="" />
                          </span>
                      </span> 

                      <?php if (!empty($info['manufacturers_image'])) { ?>
                      <script type="text/javascript">
                      //<![CDATA[            
                      pokaz_obrazek_ajax('foto', '<?php echo $info['manufacturers_image']; ?>')
                      //]]>
                      </script> 
                      <?php } ?>   
                      
                    </div>                    

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
                            
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from manufacturers_info where manufacturers_id = '".$filtr->process((int)$_GET['id_poz'])."' and languages_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $producent = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                  <label>Adres URL do strony WWW:</label>
                                  <input type="text" name="url_<?php echo $w; ?>" size="120" value="<?php echo $producent['manufacturers_url']; ?>" />
                                </p>                         
                            
                                <p>
                                  <label>Meta Tagi - Tytuł:</label>
                                  <input type="text" name="tytul_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo $producent['manufacturers_meta_title_tag']; ?>" />
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($producent['manufacturers_meta_title_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                
                                
                                <p>
                                  <label>Meta Tagi - Opis:</label>
                                  <input type="text" name="opis_<?php echo $w; ?>" size="120"onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')"  value="<?php echo $producent['manufacturers_meta_desc_tag']; ?>" />
                                </p> 

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($producent['manufacturers_meta_desc_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                 
                                
                                <p>
                                  <label>Meta Tagi - Słowa kluczowe:</label>
                                  <input type="text" name="slowa_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="<?php echo $producent['manufacturers_meta_keywords_tag']; ?>" />
                                </p>  

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($producent['manufacturers_meta_keywords_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p> 

                                <br />

                                <p>
                                  <label style="margin-bottom:8px;">Opis producenta:</label>
                                  <textarea cols="50" rows="20" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo $producent['manufacturers_description']; ?></textarea>
                                </p> 
                                
                                <br />
                                
                                <div class="maleInfo">Informacja producenta wyświetlana w formie zakładki na karcie produktu dla produktów przypisanych do producenta</div>
                                
                                <p>
                                  <label>Nazwa zakładki:</label>
                                  <input type="text" name="info_nazwa_<?php echo $w; ?>" size="80" value="<?php echo $producent['manufacturers_info_name']; ?>" />
                                </p>  
                                
                                <p style="padding-top:5px">
                                  <textarea cols="50" rows="30" id="edytor_info_<?php echo $w; ?>" name="edytor_info_<?php echo $w; ?>"><?php echo $producent['manufacturers_info_text']; ?></textarea>                                  
                                </p>
                                
                            </div>
                            <?php    

                            $db->close_query($sqls);
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
                  <button type="button" class="przyciskNon" onclick="cofnij('producenci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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
    
} ?>