<?php
chdir('../');  

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_modu = $filtr->process($_POST['id']);
        //
        $pola = array(
                array('modul_status',$filtr->process($_POST['status'])),
                array('modul_file',$filtr->process($_POST['plik'])),
                array('modul_title',$filtr->process($_POST['nazwa'])),
                array('modul_description',$filtr->process($_POST['opis'])));

        $sql = $db->update_query('theme_modules_fixed' , $pola, " modul_id = '".$id_modu."'");
        unset($pola);
        //
        foreach ( $_POST as $Pole => $Wartosc ) {
            //            
            if ( strpos($Pole, 'KONFIGURACJA__') > -1 ) {
                //
                $pola = array();
                $pola[] = array('modul_settings_value', $Wartosc);
                $sql = $db->update_query('theme_modules_fixed_settings' , $pola, " modul_settings_code = '".str_replace('KONFIGURACJA__', '', $Pole)."'");
                unset($pola);                
                //
            }
            //
        }
        //        
        Funkcje::PrzekierowanieURL('srodek_stale.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">        

          <form action="wyglad/srodek_stale_edytuj.php" method="post" id="srodekForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from theme_modules_fixed where modul_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                
                <!-- Skrypt do walidacji formularza -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                    $("#modForm").validate({
                      rules: {
                        nazwa: {
                          required: true
                        },
                        plik: {
                          required: true
                        }              
                      },
                      messages: {
                        nazwa: {
                          required: "Pole jest wymagane"
                        },
                        plik: {
                          required: "Pole jest wymagane"
                        }                
                      }
                    });
                });            
                //]]>
                </script>                  
            
                <div class="pozycja_edytowana">

                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
        
                    <p>
                        <label class="required">Nazwa modułu:</label>
                        <input type="text" name="nazwa" size="45" value="<?php echo $info['modul_title']; ?>" id="nazwa" />
                    </p>
                    
                    <p>
                        <label>Opis modułu:</label>
                        <textarea name="opis" rows="5" cols="70" class="toolTip" title="Opis co będzie wyświetlał moduł - informacja tylko dla administratora sklepu"><?php echo $info['modul_description']; ?></textarea>
                    </p> 

                    <p>
                        <label class="required">Nazwa pliku modułu:</label>
                        <input type="text" name="plik" id="plik" value="<?php echo $info['modul_file']; ?>" size="40" class="toolTipText" title="Nazwa pliku definiującego wygląd modułu (pliki muszą znajdować się w katalogu /moduly_stale" />
                    </p>    

                    <p>
                        <label>Czy moduł ma być włączony ?</label>
                        <input type="radio" value="1" name="status" <?php echo (($info['modul_status'] == 1) ? 'checked="checked"' : ''); ?> /> tak
                        <input type="radio" value="0" name="status" <?php echo (($info['modul_status'] == 0) ? 'checked="checked"' : ''); ?> /> nie
                    </p> 
                    
                    <?php
                    // dodatkowa konfiguracja modulu
                    $zapytanieKonfiguracja = "select * from theme_modules_fixed_settings where modul_id = '" . $info['modul_id'] . "'";
                    $sqlKonfiguracja = $db->open_query($zapytanieKonfiguracja);
                    
                    if ((int)$db->ile_rekordow($sqlKonfiguracja) > 0) {
                    
                        while ( $infp = $sqlKonfiguracja->fetch_assoc() ) {
                        
                            echo '<p>';
                            echo '<label>' . $infp['modul_settings_description'] . '</label>';
                            
                            // jezeli jest pole wyboru
                            if ( strpos($infp['modul_settings_value_limit'], '/') > 0 ) {
                                //
                                $Pola = explode('/',$infp['modul_settings_value_limit']);
                                foreach ($Pola as $Pole) {
                                    echo '<input type="radio" name="KONFIGURACJA__' . $infp['modul_settings_code'] . '" value="' . $Pole . '" ' . (($Pole == $infp['modul_settings_value']) ? 'checked="checked"' : '') . '/> ' . $Pole;
                                }
                                unset($Pola);
                                //
                              } else if ( $infp['modul_settings_code'] != 'NEWSLETTER_ID' ) {
                                //
                                echo '<input type="text" name="KONFIGURACJA__' . $infp['modul_settings_code'] . '" size="8" value="' . $infp['modul_settings_value'] . '" />';
                                //
                              } else {
                                //
                                $zapytanieNewsletter = 'SELECT DISTINCT newsletters_id, title FROM newsletters order by title'; 
                                $sqlNewsletter = $db->open_query($zapytanieNewsletter);
              
                                $tablica = array();
                                while ($infoNewsletter = $sqlNewsletter->fetch_assoc()) {
                                       $tablica[] = array('id' => $infoNewsletter['newsletters_id'],
                                                          'text' => $infoNewsletter['title']);
                                }

                                echo Funkcje::RozwijaneMenu('KONFIGURACJA__' . $infp['modul_settings_code'], $tablica, $infp['modul_settings_value'], 'style="width:200px"'); 
                 
                                $db->close_query($sqlNewsletter);
                                unset($tablica,$zapytanieNewsletter, $infoNewsletter);                                                    
                                //                            
                            }
                            
                            echo '</p>';
                        
                        }
                        
                    }  

                    $db->close_query($sqlKonfiguracja);
                    unset($infp, $zapytanieKonfiguracja);                     
                    ?>

                    </div>
                
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('srodek_stale','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info, $zapytanie);            
            
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
