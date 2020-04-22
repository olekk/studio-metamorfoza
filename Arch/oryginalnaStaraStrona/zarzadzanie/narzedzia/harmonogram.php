<?php
chdir('../');            

// wczytanie ustawien inicjujacych cron
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
      $iloscGodzin = 0;

      while (list($key, $value) = each($_POST)) {
        if ( $key != 'akcja' && $key != 'cron' ) {
          $pola = array(
                  array('value',addslashes($value))
          );
          
          if ( strpos( strtoupper($key), '_GODZIN') > -1 ) {
               $iloscGodzin = (int)$value;
          }
          
          $db->update_query('settings' , $pola, " code = '".strtoupper($key)."'");	
          unset($pola);
        }
      }

      // oblicza nowy czas
      $przelicznikSekund = ($iloscGodzin * 60) * 60;
      $noweSekundy = 0;
      $aktualneSekundy = time();
      $noweSekundy = ((int)($aktualneSekundy / 3600) * 3600) + $przelicznikSekund;
      //      
      $db->open_query("UPDATE settings SET value = '" . $noweSekundy . "' WHERE code = 'CRON_" . (int)$_POST['cron'] . "_SEKUNDY'");
      
      unset($iloscGodzin);
      
      Funkcje::PrzekierowanieURL('harmonogram.php?cron=' . $_POST['cron']);
      
    }
    
    if ( isset($_GET['cron']) && (int)$_GET['cron'] > 0 && (int)$_GET['cron'] < 6 ) {
        //
        $cron = 'cron_' . (int)$_GET['cron'];
        $wynik = '<div id="'.$cron.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';
        //
      } else {
        //
        $cron = '';
        $wynik = '';
        //
    }
    
    $zapytanie = "SELECT * FROM settings WHERE type = 'cron' ORDER BY sort ";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    if ( $db->ile_rekordow($sql) > 0 ) {
      while ($info = $sql->fetch_assoc()) {
        $parametr[$info['code']] = array($info['value'], $info['limit_values'], $info['description'], $info['form_field_type']);
      }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Harmonogram zadań - cykliczne uruchamianie skryptów o określonych godzinach</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana" style="overflow:hidden;">  

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              <?php 
              for ( $b = 1; $b < 5; $b++ ) {
              ?>            
              $("#cron<?php echo $b; ?>Form").validate({
                rules: {
                  cron_<?php echo $b; ?>_skrypt: { required: function() {var wynik = true; if ( $("input[name='cron_<?php echo $b; ?>_status']:checked", "#cron<?php echo $b; ?>Form").val() == "nie" ) { wynik = false; } return wynik; }},
                }             
              });     
              <?php
              }
              ?>
              setTimeout(function() {
                $('#<?php echo $cron; ?>').fadeOut();
              }, 3000);
            });
            //]]>
          </script> 
          
          <span class="ostrzezenie">Pliki do harmonogramu zadań muszą znajdować w katalogu o nazwie /harmonogram. Skrypy z innych lokalizacji nie będą uruchamiane.</span>
          
          <?php
          $definicje = array();
          
          $definicje[1] = array( 'status' => CRON_1_STATUS, 'sekundy' => CRON_1_SEKUNDY );
          $definicje[2] = array( 'status' => CRON_2_STATUS, 'sekundy' => CRON_2_SEKUNDY );
          $definicje[3] = array( 'status' => CRON_3_STATUS, 'sekundy' => CRON_3_SEKUNDY );
          $definicje[4] = array( 'status' => CRON_4_STATUS, 'sekundy' => CRON_4_SEKUNDY );
          ?>
          
          <?php 
          for ( $b = 1; $b < 5; $b++ ) {
          ?>
          
          <div class="cron">
          
            <form action="narzedzia/harmonogram.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="cron<?php echo $b; ?>Form" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="cron" value="<?php echo $b; ?>" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Pozycje harmonogramu zadań - zadanie nr <?php echo $b; ?>
                      
                      <?php
                      if ( $definicje[$b]['status'] == 'tak' && $definicje[$b]['sekundy'] > 100 ) {
                           echo '<span>kolejne uruchomienie zaplanowane na datę minimum: <b>' . date('d-m-Y H:i:s', $definicje[$b]['sekundy']) . '</b></span>';
                      }
                      ?>
                      
                      </td>
                    </tr>

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz wykonywanie skryptu:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['CRON_' . $b . '_STATUS']['1'], $parametr['CRON_' . $b . '_STATUS']['0'], 'cron_' . $b . '_status', '', '', $parametr['CRON_' . $b . '_STATUS']['3'] );
                        ?>
                      </td>
                    </tr>                    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Nazwa skryptu który ma się wykonywać:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="cron_' . $b . '_skrypt" name="cron_' . $b . '_skrypt" value="'.$parametr['CRON_' . $b . '_SKRYPT']['0'].'" size="110" />';
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Opis zadania:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea id="cron_' . $b . '_opis" name="cron_' . $b . '_opis" cols="70" rows="3">'.$parametr['CRON_' . $b . '_OPIS']['0'].'</textarea>';
                        ?>
                      </td>
                    </tr>    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Co ile godzin ma się wykonywać ?</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['CRON_' . $b . '_ILOSC_GODZIN']['1'], $parametr['CRON_' . $b . '_ILOSC_GODZIN']['0'], 'cron_' . $b . '_ilosc_godzin', '', '', $parametr['CRON_' . $b . '_ILOSC_GODZIN']['3'] );
                        ?>
                      </td>
                    </tr>   

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $cron == 'cron_' . $b ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>  

          <?php
          }
          
          unset($definicje);
          ?>
          
        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
