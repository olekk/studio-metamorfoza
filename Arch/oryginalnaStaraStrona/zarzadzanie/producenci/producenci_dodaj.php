<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('manufacturers_image',$filtr->process($_POST['zdjecie'])),
                array('manufacturers_name',$filtr->process($_POST['nazwa'])));
        
        $sql = $db->insert_query('manufacturers' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            $pola = array(
                    array('manufacturers_id',$id_dodanej_pozycji),
                    array('languages_id',$ile_jezykow[$w]['id']),
                    array('manufacturers_url',$filtr->process($_POST['url_'.$w])),
                    array('manufacturers_meta_title_tag',$filtr->process($_POST['tytul_'.$w])),
                    array('manufacturers_meta_desc_tag',$filtr->process($_POST['opis_'.$w])),      
                    array('manufacturers_meta_keywords_tag',$filtr->process($_POST['slowa_'.$w])),
                    array('manufacturers_description',$filtr->process($_POST['edytor_'.$w])),
                    array('manufacturers_info_name',$filtr->process($_POST['info_nazwa_'.$w])),
                    array('manufacturers_info_text',$filtr->process($_POST['edytor_info_'.$w])));          
            $sql = $db->insert_query('manufacturers_info' , $pola);
            unset($pola);
        }

        unset($ile_jezykow);    

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('producenci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('producenci.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="producenci/producenci_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
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
                  <input type="text" name="nazwa" size="80" value="" id="nazwa" />
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
                        
                            <p>
                              <label>Adres URL do strony WWW:</label>
                              <input type="text" name="url_<?php echo $w; ?>" size="120" value="" />
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
                        
                            <br />

                            <p>
                              <label style="margin-bottom:8px;">Opis producenta:</label>
                              <textarea cols="50" rows="20" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </p> 
                            
                            <br />
                            
                            <div class="maleInfo">Informacja producenta wyświetlana w formie zakładki na karcie produktu dla produktów przypisanych do producenta</div>
                            
                            <p>
                              <label>Nazwa zakładki:</label>
                              <input type="text" name="info_nazwa_<?php echo $w; ?>" size="80" value="" />
                            </p>  
                            
                            <p style="padding-top:5px">
                              <textarea cols="50" rows="30" id="edytor_info_<?php echo $w; ?>" name="edytor_info_<?php echo $w; ?>"></textarea>                                  
                            </p>                        

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
              <button type="button" class="przyciskNon" onclick="cofnij('producenci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>