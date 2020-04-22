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
                    $zapytanie_tmp = "select distinct * from products_youtube where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."' and products_film_id = '" . $l . "'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    $opis = $sqls->fetch_assoc();     
                    //
                    $products_film_name = $opis['products_film_name'];
                    $products_film_url = $opis['products_film_url'];
                    $products_film_description = $opis['products_film_description'];
                    $products_film_width = $opis['products_film_width'];
                    $products_film_height = $opis['products_film_height'];
                    //
                  } else {
                    //
                    $products_film_name = '';
                    $products_film_url = '';
                    $products_film_description = '';
                    $products_film_width = '';
                    $products_film_height = '';
                    //                  
                }
                ?>            
            
                <div class="nagl_linki">Klip filmowy nr <span><?php echo $l; ?></span></div>
                
                <div class="ostrzezenie" style="margin:10px">Należy wkleić tylko nr ID filmu, np. z linku http://www.youtube.com/watch?v=BvtXXXAF8 będzie to BvtXXXAF8</div>
                
                <p>
                  <label>Nazwa filmu:</label>
                  <input type="text" name="film_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $products_film_name; ?>" />
                </p> 

                <?php if ($w == 0) { ?>                
                <p>
                  <label>Adres URL:</label>
                  <input type="text" name="film_url_<?php echo $l; ?>" size="80" value="<?php echo $products_film_url; ?>" />
                </p>
                <?php } ?>
                
                <p>
                  <label>Opis filmu:</label>
                  <textarea cols="70" rows="4" name="film_opis_<?php echo $l; ?>_<?php echo $w; ?>"><?php echo $products_film_description; ?></textarea>
                </p> 

                <?php if ($w == 0) { ?>
                <p>
                  <label>Szerokość klipu w pikselach:</label>
                  <input type="text" name="film_szerokosc_<?php echo $l; ?>" class="calkowita" size="5" value="<?php echo $products_film_width; ?>" />
                </p>   

                <p>
                  <label>Wysokość klipu w pikselach:</label>
                  <input type="text" name="film_wysokosc_<?php echo $l; ?>" class="calkowita" size="5" value="<?php echo $products_film_height; ?>" />
                </p>
                <?php } ?>
                
                <?php
               
                if ($id_produktu > 0) {
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $opis);
                }
                unset($products_film_name, $products_film_url, $products_film_description, $products_film_width, $products_film_height);
                
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