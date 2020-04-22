<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] >= 0 && Sesje::TokenSpr()) { 

    $id_produktu = (int)$_GET['id_produktu'];
    
    $ile_jezykow = Funkcje::TablicaJezykow();
    $jezyk_szt = count($ile_jezykow);
 
    ?>

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = (int)$_GET['id_tab'];
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                      
    ?>                   
    </div>
    
    <div style="clear:both"></div>
    
    <script type="text/javascript">
    //<![CDATA[
    $(document).ready(function(){
        pokazChmurki();   
    });
    //]]>
    </script>     
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {
            ?>
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
            <?php for ($l = 1; $l < 5; $l++) { ?>
            
                <?php
                if ($id_produktu > 0) {
                    // pobieranie danych jezykowych
                    $zapytanie_tmp = "select distinct * from products_film where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_film_id = '" . $l . "'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    $opis = $sqls->fetch_assoc();     
                    //
                    $products_flv_name = $opis['products_film_name'];
                    $products_flv_file = $opis['products_film_file'];
                    $products_flv_screen = $opis['products_film_full_size'];
                    $products_flv_description = $opis['products_film_description'];
                    $products_flv_width = $opis['products_film_width'];
                    $products_flv_height = $opis['products_film_height'];
                    //
                  } else {
                    //
                    $products_flv_name = '';
                    $products_flv_file = '';
                    $products_flv_screen = '';
                    $products_flv_description = '';
                    $products_flv_width = '';
                    $products_flv_height = '';
                    //                  
                }
                ?>            
            
                <div class="nagl_linki">Klip filmowy nr <span><?php echo $l; ?></span></div>

                <p>
                  <label>Nazwa filmu:</label>
                  <input type="text" name="flv_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $products_flv_name; ?>" />
                </p> 

                <?php if ($w == 0) { ?>                
                <p>
                  <label>Plik filmu:</label>
                  <input type="text" name="flv_plik_<?php echo $l; ?>" size="80" class="toolTipTopText" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików" ondblclick="openFileBrowser('flv_plik_<?php echo $l; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="flv_plik_<?php echo $l; ?>" value="<?php echo $products_flv_file; ?>" />
                  <span class="usun_plik toolTipTopText" data="flv_plik_<?php echo $l; ?>" title="Usuń przypisany plik"></span>
                </p>
                
                <p>
                  <label>Możliwość włączenia trybu pełnoekranowego:</label>
                  <input type="radio" name="flv_ekran_<?php echo $l; ?>" value="1" class="toolTipTopText" title="Klient będzie miał możliwość powiększenia filmu na cały ekran" <?php echo (($products_flv_screen == '1') ? 'checked="checked"' : ''); ?> /> tak
                  <input type="radio" name="flv_ekran_<?php echo $l; ?>" value="0" class="toolTipTopText" title="Klient nie będzie miał możliwości powiększenia filmu na cały ekran" <?php echo (($products_flv_screen == '0' || empty($products_flv_screen)) ? 'checked="checked"' : ''); ?> /> nie
                </p>                 
                <?php } ?>
                
                <p>
                  <label>Opis filmu:</label>
                  <textarea cols="70" rows="4" name="flv_opis_<?php echo $l; ?>_<?php echo $w; ?>"><?php echo $products_flv_description; ?></textarea>
                </p> 

                <?php if ($w == 0) { ?>
                <p>
                  <label>Szerokość klipu w pikselach:</label>
                  <input type="text" name="flv_szerokosc_<?php echo $l; ?>" class="calkowita" size="5" value="<?php echo $products_flv_width; ?>" />
                </p>   

                <p>
                  <label>Wysokość klipu w pikselach:</label>
                  <input type="text" name="flv_wysokosc_<?php echo $l; ?>" class="calkowita" size="5" value="<?php echo $products_flv_height; ?>" />
                </p>
                <?php } ?>
                
                <?php
               
                if ($id_produktu > 0) {
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $opis);
                }
                unset($products_flv_name, $products_flv_url, $products_flv_description, $products_flv_width, $products_flv_height);
                
            } ?>
                
            </div>
            <?php                    
        }   
        ?>                      
    </div>

    <script type="text/javascript">
    //<![CDATA[
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>', '',760,400);
    //]]>
    </script>  

<?php } ?>