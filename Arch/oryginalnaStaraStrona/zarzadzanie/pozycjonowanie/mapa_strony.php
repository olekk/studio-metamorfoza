<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');     

    ?>

    <div id="naglowek_cont">Generowanie mapy strony XML</div>
    <div id="cont">
    
          <div class="poleForm">
            <div class="naglowek">Generowanie mapy strony</div>
            
                <form action="pozycjonowanie/mapa_strony_utworz.php" method="post" class="cmxform">   

                <div class="poleForm" id="daneXML">
                    <div class="naglowek">Dane o mapie witryny</div>

                    <div id="daneXMLPlik">
                    
                        <?php
                        /*
                        // wersja wielojezykowa
                    
                        <table style="width:100%">
                            <tr>
                            
                            <?php
                            $jezyki = "SELECT * FROM languages WHERE status = '1' ORDER BY sort_order";                        
                            $sql = $db->open_query($jezyki);
                            $ile_jezykow = (int)$db->ile_rekordow($sql);
                            
                            $ile = 0;
                            while ($Lang = $sql->fetch_assoc()) {        

                                echo '<td style="width:33%"> <img src="../' . KATALOG_ZDJEC . '/'.$Lang['image'].'" alt="'.$Lang['name'].'" title="'.$Lang['name'].'" /> ';

                                if (file_exists('../sitemap_'.$Lang['code'].'.xml')) { ?>
                                
                                    <span>Nazwa pliku:</span> <a href="<?php echo ADRES_URL_SKLEPU; ?>/xml/sitemap_<?php echo $Lang['code']; ?>.xml">sitemap_<?php echo $Lang['code']; ?>.xml</a> <br />
                                    <span>Rozmiar pliku:</span> <?php echo filesize('../sitemap_'.$Lang['code'].'.xml'); ?> bajtów<br />
                                    <span>Data utworzenia:</span> <?php echo date('d-m-Y H:i',filemtime('../sitemap_'.$Lang['code'].'.xml')); ?> <br />
                                    
                                    <?php
                                    $zapis = 'tak';
                                    if (!is_writeable('../sitemap_'.$Lang['code'].'.xml')) {
                                        $zapis = 'nie';
                                    }
                                    ?>
                                    
                                    <span>Możliwy zapis:</span> <?php echo $zapis; ?>
                                    
                                    <?php if ($zapis == 'nie') { ?>
                                        <div class="ostrzezenie">
                                            UWAGA !! Plik sitemap_<?php echo $Lang['code']; ?>.xml nie ma uprawnień do zapisu i nie będzie można zapisać danych wynikowych.
                                        </div>                        
                                    <?php }
                                
                                } else {
                                
                                    ?>
                                    
                                    <div class="ostrzezenie">
                                        UWAGA !! Plik sitemap_<?php echo $Lang['code']; ?>.xml nie istnieje !! Sklep spróbuje utworzyć plik i zapisać w nim dane.
                                    </div>                                     
                                    
                                    <?php
                                
                                }

                                echo '</td>';
                                
                                $ile++;
                                
                                if ($ile == 3 && $ile_jezykow > 3) {
                                    
                                    echo '</tr>
                                            <tr><td colspan="3" class="linia"></td></tr>
                                          <tr>';
                                    $ile = 0;
                                    
                                }
                                
                                
                            }
                            
                            $db->close_query($sql);
                            unset($Lang, $jezyki, $sql, $zapis);
                            ?>
                            
                            </tr>
                        </table>
                        
                        */
                        ?>
                        
                        <?php
                        if (file_exists('../sitemap.xml')) { 
                        ?>
                                
                            <span>Nazwa pliku:</span> <a href="<?php echo ADRES_URL_SKLEPU; ?>/sitemap.xml">sitemap.xml</a> <br />
                            <span>Rozmiar pliku:</span> <?php echo filesize('../sitemap.xml'); ?> bajtów<br />
                            <span>Data utworzenia:</span> <?php echo date('d-m-Y H:i',filemtime('../sitemap.xml')); ?> <br />
                                        
                            <?php
                            $zapis = 'tak';
                            if (!is_writeable('../sitemap.xml')) {
                                $zapis = 'nie';
                            }
                            ?>
                                        
                            <span>Możliwy zapis:</span> <?php echo $zapis; ?>
                            
                            <?php if ($zapis == 'nie') { ?>
                                <div class="ostrzezenie">
                                    UWAGA !! Plik sitemap.xml nie ma uprawnień do zapisu i nie będzie można zapisać danych wynikowych.
                                </div>                        
                            <?php }
                                
                        } else {
                        
                            ?>
                            
                            <div class="ostrzezenie">
                                UWAGA !! Plik sitemap.xml nie istnieje !! Sklep spróbuje utworzyć plik i zapisać w nim dane.
                            </div>                                     
                            
                            <?php
                        
                        }
                        ?>
                        
                        <br />
                        
                        <span class="maleInfo" style="margin-left:0px">Plik XML z mapą strony zapisywany jest w katalogu <b>głównym sklepu</b></span>
                    
                    </div>
                    
                </div>
            
                <table class="tblEdycja">
                    <tr>
                    
                        <td class="lewa">

                            <input type="checkbox" style="border:0px" value="1" name="kategorie" checked="checked" /> czy uwzględniać <strong>kategorie</strong> przy generowaniu mapy ? <br />
                            <input type="checkbox" style="border:0px" value="1" name="strony_info" checked="checked" /> czy uwzględniać <strong>strony informacyjne</strong> przy generowaniu mapy ? <br />
                            <input type="checkbox" style="border:0px" value="1" name="ankiety" checked="checked" /> czy uwzględniać <strong>ankiety</strong> przy generowaniu mapy ? <br />
                            <input type="checkbox" style="border:0px" value="1" name="galerie" checked="checked" /> czy uwzględniać <strong>galerie</strong> przy generowaniu mapy ? <br />
                            <input type="checkbox" style="border:0px" value="1" name="formularze" checked="checked" /> czy uwzględniać <strong>formularze</strong> przy generowaniu mapy ? <br />
                            <input type="checkbox" style="border:0px" value="1" name="producenci" checked="checked" /> czy uwzględniać <strong>producentów</strong> przy generowaniu mapy ? <br />
                            <input type="checkbox" style="border:0px" value="1" name="aktualnosci" checked="checked" /> czy uwzględniać <strong>aktualności</strong> przy generowaniu mapy ? <br />
                            <input type="checkbox" style="border:0px" value="1" name="recenzje" checked="checked" /> czy uwzględniać <strong>recenzje</strong> przy generowaniu mapy ?
                            
                            <br /><br />
                            
                            Jak często zmienia się zawartość strony ?
                            
                            <select name="index">
                                <option value="always">always – nieustająco</option>
                                <option value="hourly">hourly – co godzinę</option>
                                <option value="daily">daily – raz na dzień</option>
                                <option value="weekly" selected="selected">weekly – co tydzień</option>
                                <option value="monthly">monthly - raz w miesiącu</option>
                                <option value="yearly">yearly – raz na rok</option>
                                <option value="never">never - nigdy się nie zmienia</option>
                            </select>
                            
                            <?php
                            /*
                            // wersja wielojezykowa                            

                            <br /><br />
                            
                            W jakim języku wygenerować mapę strony ? <br />                            

                            <?php
                            $jezyki = "SELECT * FROM languages WHERE status = '1' ORDER BY sort_order";
                            $sql = $db->open_query($jezyki);
                            while ($wartosciJezykow = $sql->fetch_assoc()) {
                             echo '<input type="radio" value="'.$wartosciJezykow['languages_id'].'" name="jezyk" '.( $wartosciJezykow['languages_default'] == '1' ? 'checked="checked"' : '' ).' />'.$wartosciJezykow['name'];
                            }
                            $db->close_query($sql);
                            unset($wartosciJezykow, $jezyki);
                            ?>     
                            
                            */
                            ?>

                            <br /><br />
                            
                            <input type="submit" class="przyciskBut" style="margin-left:0px" value="Generuj plik XML" />

                        </td>
                        
                        <td class="prawa">
                        
                            <input type="text" value="0.8" class="ulamek" name="priorytet_produkty" size="4" /> priorytet dla <strong>produktów</strong> <br />    
                            
                            <input type="checkbox" style="border:0px" value="1" name="automat" checked="checked" /> czy sklep sam ma obliczyć priorytet dla produktów w zależności od wielkości sprzedaży <br />
                            <input type="checkbox" style="border:0px" value="1" name="obrazki" /> czy dodać do mapy strony linki do zdjęć produktów <br /><br />
                            
                            <input type="text" value="0.9" class="ulamek" name="priorytet_kategorie" size="4" /> priorytet dla <strong>kategorii</strong> <br />    
                            <input type="text" value="0.7" class="ulamek" name="priorytet_strony_info" size="4" /> priorytet dla <strong>stron informacyjnych</strong> <br />    
                            <input type="text" value="0.6" class="ulamek" name="priorytet_ankiety" size="4" /> priorytet dla <strong>ankiet</strong> <br />    
                            <input type="text" value="0.7" class="ulamek" name="priorytet_galerie" size="4" /> priorytet dla <strong>galerii</strong> <br />    
                            <input type="text" value="0.7" class="ulamek" name="priorytet_formularze" size="4" /> priorytet dla <strong>formularzy</strong> <br />    
                            <input type="text" value="0.6" class="ulamek" name="priorytet_producenci" size="4" /> priorytet dla <strong>producentów</strong> <br />    
                            <input type="text" value="0.5" class="ulamek" name="priorytet_aktualnosci" size="4" /> priorytet dla <strong>aktualności</strong> <br />    
                            <input type="text" value="0.5" class="ulamek" name="priorytet_recenzje" size="4" /> priorytet dla <strong>recenzji</strong> <br />    
                        
                        </td>

                    </tr>
                </table>
                
                </form>
                
          </div>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>