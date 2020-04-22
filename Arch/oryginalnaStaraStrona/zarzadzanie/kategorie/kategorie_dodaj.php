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
                array('categories_icon',$filtr->process($_POST['ikona'])),
                array('parent_id',$filtr->process($_POST['id_kat'])),
                array('sort_order',$filtr->process($_POST['sort'])),
                array('categories_status','1'));
                
        if ( isset($_POST['color_status']) ) {
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
        
        $sql = $db->insert_query('categories' , $pola);
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
                    array('categories_description',$filtr->process($_POST['edytor_'.$w])),
                    array('categories_info_name',$filtr->process($_POST['info_nazwa_'.$w])),
                    array('categories_info_text',$filtr->process($_POST['edytor_info_'.$w])));                    
            $sql = $db->insert_query('categories_description' , $pola);
            unset($pola);
            //
        }

        unset($ile_jezykow);    

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('kategorie.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="kategorie/kategorie_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
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
                  <label>Kategoria nadrzędna:</label>
                </p> 
                
                <div id="drzewo">
                    <?php
                    //
                    echo '<table class="pkc" cellpadding="0" cellspacing="0">
                          <tr>
                            <td class="lfp" colspan="2"><input type="radio" value="0" name="id_kat" checked="checked" />-- brak kategorii nadrzędnej --</td>
                          </tr>';
                    //
                    $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                    for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                        $podkategorie = false;
                        if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                        //
                        echo '<tr>
                                <td class="lfp"><input type="radio" value="'.$tablica_kat[$w]['id'].'" name="id_kat" /> '.$tablica_kat[$w]['text'].'</td>
                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                              </tr>
                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                    }
                    echo '</table>';
                    unset($tablica_kat,$podkategorie);
                    ?> 
                </div>

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
                
                <p>
                  <label>Grafika ikony:</label>           
                  <input type="text" name="ikona" size="95" value="" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('ikona','','<?php echo KATALOG_ZDJEC; ?>')" id="ikona"  />                 
                  <span class="usun_zdjecie toolTipTopText" data="ikona" title="Usuń przypisany obrazek"></span>
                </p>      

                <div id="divikona" style="padding-left:10px;display:none">
                    <label>Ikona:</label>
                    <span id="foikona">
                        <span class="zdjecie_tbl">
                            <img src="obrazki/_loader_small.gif" alt="" />
                        </span>
                    </span> 
                </div>                  
                
                <p>
                  <label>Kolejność wyświetlania:</label>
                  <input type="text" name="sort" size="5" value="" id="sort" />
                </p>      

                <p>
                  <label>Kolor nazwy kategorii:</label>
                  <input name="kolor" class="color toolTipTopText" style="-moz-box-shadow:none" value="" size="8" title="Określa kolor wyświetlania nazwy kategorii w boxie kategorii" /> 
                  <input type="checkbox" name="color_status" class="toolTipTopText" value="1" title="Czy nazwa kategorii ma być wyświetlana w kolorze ?" /> wyświetlaj nazwę tej kategorii w kolorze
                </p>    

                <p>
                  <label>Kolor tła nazwy kategorii:</label>
                  <input name="kolor_tla" class="color toolTipTopText" style="-moz-box-shadow:none" value="" size="8" title="Określa kolor tła pod nazwą kategorii w boxie kategorii" /> 
                  <input type="checkbox" name="kolor_status_tla" class="toolTipTopText" value="1" title="Czy tło nazwy kategorii ma być wyświetlane w kolorze ?" /> wyświetlaj tło tej kategorii w kolorze
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
                            
                            <br />

                            <p>
                              <label style="margin-bottom:8px;">Opis kategorii:</label>
                              <textarea cols="50" rows="20" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </p> 
                            
                            <br />
                            
                            <div class="maleInfo">Informacja wyświetlana w formie zakładki na karcie produktu dla produktów przypisanych do kategorii</div>
                            
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
              <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>       
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
