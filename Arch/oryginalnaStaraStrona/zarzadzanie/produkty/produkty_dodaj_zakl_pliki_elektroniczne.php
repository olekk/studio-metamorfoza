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
    
    function dodaj_plik_elektroniczny(id_jezyk) {
        ile_plikow = parseInt($("#ile_plikow_"+id_jezyk).val()) + 1;
        //
        $('#pliki_elektroniczne_'+id_jezyk).append('<div id="div_plik_elektroniczny_'+id_jezyk+'_'+ile_plikow+'"></div>');
        $('#div_plik_elektroniczny_'+id_jezyk+'_'+ile_plikow).css('display','none');
        //
        $.get('ajax/dodaj_plik_elektroniczny.php?tok=<?php echo Sesje::Token(); ?>', { ilosc: ile_plikow, id: id_jezyk }, function(data) {
            $('#div_plik_elektroniczny_'+id_jezyk+'_'+ile_plikow).html(data);
            $("#ile_plikow_"+id_jezyk).val(ile_plikow);
            
            $('#div_plik_elektroniczny_'+id_jezyk+'_'+ile_plikow).slideDown("fast");
        });
    }    
    //]]>
    </script>     
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {
            ?>
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
            <div id="pliki_elektroniczne_<?php echo $w; ?>">
                
                <?php
                $przypisane_pliki = array();
                
                if ($id_produktu > 0) {
                    // pobieranie danych jezykowych
                    $zapytanie_tmp = "select distinct * from products_file_shopping where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqls = $db->open_query($zapytanie_tmp);
                    while ($plik = $sqls->fetch_assoc()) {
                        //
                        $przypisane_pliki[ $plik['products_file_shopping_unique_id'] ] = array( 'id' => $plik['products_file_shopping_unique_id'],
                                                                                                'plik' => $plik['products_file_shopping'],
                                                                                                'nazwa' => $plik['products_file_shopping_name'] );
                        //
                    } 
                    //
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $plik);
                    //
                }

                if ( count($przypisane_pliki) > 0 ) {
                    //
                    $l = 1;
                    foreach ( $przypisane_pliki as $plik ) {
                      //
                      ?>
                      <div class="nagl_linki">Plik elektroniczny nr <span><?php echo $l; ?></span></div>
                      
                      <p>
                        <label>Nazwa do wyświetlania:</label>
                        <input type="text" name="plik_elektroniczny_nazwa_<?php echo $l; ?>_<?php echo $w; ?>" size="80" value="<?php echo $plik['nazwa']; ?>" />
                      </p> 

                      <p>
                        <label>Plik:</label>
                        <input type="text" name="plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>" size="80" class="toolTipTopText" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików" ondblclick="openFileAllBrowser('plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>')" id="plik_elektroniczny_<?php echo $l; ?>_<?php echo $w; ?>" value="<?php echo $plik['plik']; ?>" autocomplete="off" />
                      </p>               
                      
                      <?php
                      //   
                      $l++;
                    }
                    //
                    unset($l);
                    //
                }
                //            
                ?>      

            </div>

            <input value="<?php echo count($przypisane_pliki); ?>" type="hidden" name="ile_plikow_<?php echo $w; ?>" id="ile_plikow_<?php echo $w; ?>" />
            
            <div style="padding:10px;padding-top:20px;">
                <span class="dodaj" onclick="dodaj_plik_elektroniczny(<?php echo $w; ?>)" style="cursor:pointer">dodaj plik do pobrania</span>
            </div>   
            
            </div>
            <?php  
            //
            unset($przypisane_pliki);
        }   
        ?>       

    </div>   
    
    <script type="text/javascript">
    //<![CDATA[
    gold_tabs('<?php echo (int)$_GET['id_tab']; ?>', '',760,400);
    //]]>
    </script>          

<?php } ?>