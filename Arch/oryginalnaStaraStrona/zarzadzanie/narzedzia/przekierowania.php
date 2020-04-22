<?php
chdir('../');            

// wczytanie ustawien inicjujacych cron
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        // aktualizacja stalej
        $pola = array(
                array('value',$filtr->process($_POST['aktywny']))
        );
        $db->update_query('settings' , $pola, " code = 'PRZEKIEROWANIA'");       
    
        $db->delete_query('location');    
    
        for ($r = 1; $r < 500; $r++) {
            if ((isset($_POST['url_od_'.$r]) && !empty($_POST['url_od_'.$r])) && (isset($_POST['url_do_'.$r]) && !empty($_POST['url_do_'.$r]))) {
                //
                $pola = array( array('urlf',trim($filtr->process($_POST['url_od_'.$r]), '/')),
                               array('urlt',trim($filtr->process($_POST['url_do_'.$r]), '/')) );
                $db->insert_query('location' , $pola);
                
                unset($pola); 
                //
            }
        }    

        Funkcje::PrzekierowanieURL('przekierowania.php?zapis');
      
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Przekierowania adresów URL</div>
    <div id="cont">

      <div class="poleForm">
      
        <script type="text/javascript">
        //<![CDATA[       
        $(document).ready(function() {
          setTimeout(function() {
            $('#za').fadeOut();
          }, 3000);
        });        
        function dodaj_url() {
            ile_pol = parseInt($("#ile_url").val()) + 1;
            //
            $.get('ajax/dodaj_url.php', { id: ile_pol }, function(data) {
                $('#wyniki').append(data);
                $("#ile_url").val(ile_pol);
            });
        } 
        function usun_url(id) {
            $('#url_' + id).remove();
        }
        //]]>
        </script>      
      
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 
        
            <div class="obramowanie_tabeliSpr">

                <form action="narzedzia/przekierowania.php" method="post" id="urlForm" class="cmxform">   

                <table class="listing_tbl">

                    <tr class="div_naglowek polowa">
                        <td>Adres pierwotny</td>
                        <td>Adres wtórny</td>
                    </tr>
                
                    <tr>
                        <td colspan="2" id="wyniki">
                        
                            <div class="przekierowanie">
                                Czy moduł przekierowań adresów URL ma być włączony ? 
                                <input type="radio" value="tak" name="aktywny" <?php echo ((PRZEKIEROWANIA == 'tak') ? 'checked="checked"' : ''); ?> /> <b>tak</b>
                                <input type="radio" value="nie" name="aktywny" <?php echo ((PRZEKIEROWANIA == 'nie') ? 'checked="checked"' : ''); ?> /> <b>nie</b>                      
                            </div>
                            
                            <?php
                            $zapytanie = "select distinct * from location";
                            $sql = $db->open_query($zapytanie); 
                            
                            if ((int)$db->ile_rekordow($sql) > 0) {

                                $g = 1;
                                while ($info = $sql->fetch_assoc()) {
                                ?>     
                            
                                <div class="url" id="url_<?php echo $g; ?>">
                                    <input type="text" size="20" value="<?php echo $info['urlf']; ?>" name="url_od_<?php echo $g; ?>" />
                                    <input type="text" size="20" value="<?php echo $info['urlt']; ?>" name="url_do_<?php echo $g; ?>" />
                                    <img onclick="usun_url('<?php echo $g; ?>')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                </div>
                    
                                <?php 
                                
                                $g++;
                                
                                }
                                
                                $ileUrl = $g - 1;
                                unset($g);
                            
                            } else {
                            
                                ?>
                            
                                <div class="url" id="url_1">
                                    <input type="text" size="20" value="" name="url_od_1" />
                                    <input type="text" size="20" value="" name="url_do_1" />
                                    <img onclick="usun_url('1')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                </div>                            
                            
                                <?php 
                            
                                $ileUrl = 1;
                                
                            } 
                            ?>
                            
                        </td>
                    </tr>

                </table>
                
                <div>
                    <input value="<?php echo $ileUrl; ?>" type="hidden" name="ile_url" id="ile_url" />
                    <input type="hidden" name="akcja" value="zapisz" />
                </div>
                
                <div style="padding:10px;">
                    <span class="dodaj" onclick="dodaj_url()" style="cursor:pointer">dodaj kolejne przekierowanie</span>
                </div>     

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ((isset($_GET['zapis'])) ? '<div id="za" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zapisane</div>' : ''); ?>
                </div>                
                
                </form>

            </div>
            
        </div>
        
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
