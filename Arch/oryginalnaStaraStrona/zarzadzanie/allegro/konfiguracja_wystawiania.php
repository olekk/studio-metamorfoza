<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

$allegro = new Allegro(true);

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $db->truncate_query('allegro_settings');
      while (list($key, $value) = each($_POST)) {
        if ( $key != 'akcja' ) {
          if ( stripos($key, 'conf') === false ) {
            if (is_array($value)) $value = array_sum($value);

            $pola = array(
                    array('params',strtoupper($key)),
                    array('value',$value)
            );
            $db->insert_query('allegro_settings' , $pola);
          } else {
            $pola = array(
                    array('value',$value)
            );
            $db->update_query('allegro_connect' , $pola, " params = '".strtoupper($key)."'");	
          }
        }
        unset($pola);
      }

      Funkcje::PrzekierowanieURL('konfiguracja_wystawiania.php');

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    <style type="text/css">
    .info_tab_content label { width:200px; padding-left:0px; }
    .info_tab_content label.error { display:block; margin-left: 170px; }
    .info_content label { width:200px; padding-left:0px; }
    .info_content label.error { display:block; margin-left: 0px; }
    </style>
    <?php

    $parametry = $allegro->TablicaDefinicjiPol( false );

    ?>

    <div id="naglowek_cont">Konfiguracja wystawiania aukcji Allegro</div>
    <div id="cont">


      <?php 
      if ( count($parametry) > 0  ) {

        $TablicaPanstw = $allegro->doGetCountries();

        ?>

        <!-- Skrypt do walidacji formularza -->
        <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#allegroForm").validate();
          });
          //]]>
        </script>        
        

        <form action="allegro/konfiguracja_wystawiania.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="allegroForm" class="cmxform"> 
          <input type="hidden" name="akcja" value="zapisz" />

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>

            <div class="pozycja_edytowana">

              <div class="info_content">
                  <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                    <table class="listing_tbl">
                      <tr class="div_naglowek">
                        <td align="left" colspan="2" style="padding-left:10px;">Parametry ogólne</td>
                      </tr>
                      <?php

                        $opcje_ogolne = array(4,5,9,10,11,32,29,28,15);

                        for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                          $fid = $opcje_ogolne[$i];

                          if ( isset($parametry[$fid]) ) {

                            if ( $parametry[$fid]['sell_form_id'] == '9' ) {
                                
                                $parametry[$fid]['sell_form_type'] = '4';

                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                foreach ( $TablicaPanstw as $Panstwo ) {
                                    $P = (array)$Panstwo;
                                    $parametry[$fid]['sell_form_desc'] .= $P['country-name'] . '|';
                                    $parametry[$fid]['sell_form_opts_values'] .= $P['country-id'] . '|';
                                    unset($P);
                                }
                                
                            }

                            echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].': ' . (isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '('.$fid.')' : '' ) . '</label></td><td >';

                            $default = '';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '');
                                echo '</td></tr>';
                          }
                        }

                      ?>
                    </table>

                  </div>

                  <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                    <table class="listing_tbl">
                      <tr class="div_naglowek">
                        <td align="left" colspan="2" style="padding-left:10px;">Zdjęcia</td>
                      </tr>
                      <?php

                      echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                      echo '<div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Maksymalna szerokość zdjęcia umieszczonego w opisie aukcji - należy dopasować do szerokości używanego szablonu aukcji." /></div>';
                      echo '<label>Szerokość zdjęcia w pikselach:</label></td><td ><input class="calkowita" type="text" value="' . (int)$allegro->polaczenie['CONF_FOTO_WIDTH'].'" size="20" name="conf_foto_width" /></td></tr>';

                      echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                      echo '<div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Maksymalna wysokość zdjęcia umieszczonego w opisie aukcji. Jeżeli nie zostanie podana żadna wartość zdjęcia będą wysyłane w oryginalnym rozmiarze." /></div>';
                      echo '<label>Wysokość zdjęcia w pikselach:</label></td><td ><input class="calkowita" type="text" value="' . (int)$allegro->polaczenie['CONF_FOTO_HEIGHT'].'" size="20" name="conf_foto_height" /></td></tr>';
                      
                      echo '<tr class="pozycja_offAllegro"><td colspan="2"><span class="maleInfo" style="margin-left:0px">Jeżeli nie zostanie podana wartość wysokości i szerokości zdjęcia będą wysyłane w oryginalnym rozmiarze.</span></td></tr>';
                      
                      // rozmiar logo producenta
                      echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                      echo '<div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Szerokość / wysokość logo producenta w szablonie aukcji wyświetlanego przez kod [LOGO_PRODUCENTA] - należy dopasować do szerokości używanego szablonu aukcji. Jeżeli nie zostanie podana żadna wartość logo będzie wyświetlae w oryginalnym rozmiarze." /></div>';
                      echo '<label>Szerokość / wysokość logotypu producenta w pikselach:</label></td><td>
                      <input type="text" value="' . $allegro->polaczenie['CONF_MANUFACTURERS_IMAGE_WIDTH'] . '" size="20" name="conf_manufacturers_image_width" />';
                      
                      ?>
                    </table>

                  </div>
                  
                  <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                    <table class="listing_tbl">
                      <tr class="div_naglowek">
                        <td align="left" colspan="2" style="padding-left:10px;">Galeria zdjęć</td>
                      </tr>
                      <?php

                      // szerokosc miniaturek
                      echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                      echo '<div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Szerokość / wysokość zdjęć miniaturek w galerii zdjęć produktu. Minimalna wartość to 30px. Maksymalna wartość to 200px." /></div>';
                      echo '<label>Szerokość / wysokość miniaturek zdjęć w galerii w pikselach:</label></td><td ><input class="calkowita" type="text" value="' . (int)$allegro->polaczenie['CONF_GALLERY_SMALL_IMAGE_WIDTH'].'" size="20" name="conf_gallery_small_image_width" /></td></tr>';

                      // szerokosc duzego zdjecia
                      echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                      echo '<div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Szerokość / wysokość dużego zdjęcia w galerii zdjęć produktu. Minimalna wartość to 400px. Maksymalna wartość to 800px." /></div>';
                      echo '<label>Szerokość / wysokość dużego zdjęcia w galerii w pikselach:</label></td><td ><input class="calkowita" type="text" value="' . (int)$allegro->polaczenie['CONF_GALLERY_BIG_IMAGE_WIDTH'].'" size="20" name="conf_gallery_big_image_width" /></td></tr>';

                      ?>
                    </table>

                  </div>  

                  <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                    <table class="listing_tbl">
                      <tr class="div_naglowek">
                        <td align="left" colspan="2" style="padding-left:10px;">Pozostałe aukcje sprzedającego</td>
                      </tr>
                      <?php

                      echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                      echo '<div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Ilość wyświetlanych na aukcji pozostałych aukcji oferowanych przez sprzedającego. Zmiana będzie widoczna tylko dla nowo wystawionych aukcji." /></div>';
                      echo '<label>Ilość wyświetlanych pozostałych aukcji:</label></td><td ><input class="calkowita" type="text" value="' . (int)$allegro->polaczenie['CONF_OTHER_IMAGE_COUNT'].'" size="20" name="conf_other_image_count" /></td></tr>';
                      ?>
                    </table>

                  </div>                   

                  <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                    <table class="listing_tbl">
                      <tr class="div_naglowek">
                        <td align="left" colspan="2" style="padding-left:10px;">Parametry wysyłki</td>
                      </tr>
                      <?php

                        $opcje_wysylki = array(12,13,35,340);

                        for ( $i=0, $c = count($opcje_wysylki); $i < $c; $i++ ) {
                          $fid = $opcje_wysylki[$i];

                          if ( isset($parametry[$fid]) ) {

                            echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].': ' . (isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '('.$fid.')' : '' ) . '</label></td><td >';

                            $default = '';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '');
                            echo '</td></tr>';

                          }
                        }

                      ?>
                    </table>

                  </div>
              

                  <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                    <table class="listing_tbl">
                      <tr class="div_naglowek">
                        <td align="left" colspan="2" style="padding-left:10px;">Parametry płatności</td>
                      </tr>
                      <?php

                        $opcje_platnosci = array(14,33,34,27);

                        for ( $i=0, $c = count($opcje_platnosci); $i < $c; $i++ ) {
                          $fid = $opcje_platnosci[$i];

                          if ( isset($parametry[$fid]) ) {

                            if ( $parametry[$fid]['sell_form_id'] == '33' || $parametry[$fid]['sell_form_id'] == '34' ) {
                              $parametry[$fid]['sell_form_field_desc'] = 'Format 26 cyfr pisanych łącznie';
                            }
                            echo '<tr class="pozycja_offAllegro"><td style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].': ' . (isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '('.$fid.')' : '' ) . '</label></td><td >';
                            $default = '';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '');
                            echo '</td></tr>';

                          }
                        }

                      ?>
                    </table>

                  </div>

                  <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                    <table class="listing_tbl">
                      <tr class="div_naglowek">
                      
                        <td style="text-align:left;padding-left:10px;">Koszty dostawy</td>
                        
                        <td style="text-align:left;padding-left:10px;">Pierwsza sztuka</td>
                        
                        <td style="text-align:left;padding-left:10px;">Druga sztuka</td>
                        
                        <td style="text-align:left;padding-left:10px;">Ilość w paczce</td>
                        
                      </tr>
                      <?php

                        $opcje_ogolne = array(36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60);

                        for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                          $fid = $opcje_ogolne[$i];

                          if ( isset($parametry[$fid]) ) {

                            echo '<tr class="pozycja_offAllegro"><td style="width:330px" class="DlugiePole">';
                            echo '<div class="pomoc">&nbsp;</div><label>';
                            echo str_replace(" (pierwsza sztuka)", '', $parametry[$fid]['sell_form_title']).': ' . (isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '('.$fid.')' : '' ) . '</td><td >';

                            $default = '';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '');
                            echo '</label></td>';

                            echo '<td>';
                            $fid = $opcje_ogolne[$i]+100;

                            $default = '';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '');
                            echo '</td>';

                            echo '<td >';
                            $fid = $opcje_ogolne[$i]+200;

                            $default = '';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '');
                            echo '</td>';

                            echo '</tr>';

                          }
                        }

                         echo '<tr class="pozycja_offAllegro"><td colspan="5"><div class="ostrzezenie">Powyższe parametry umożliwiają zdefiniowanie kosztów dostawy w aukcji. Jeżeli dana forma dostawy ma nie być dostępna, to proszę nie wypełniać pola określającego koszt.</div></td></tr>';
                      ?>
                    </table>

                  </div>

               </div>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
              </div>

            </div>
          </div>
        </form>

      <?php
      } else {
        echo '<div class="pozycja_edytowana"><span class="ostrzezenie">Nie zostały wczytane definicje pól z serwisu Allegro.</span></div>';
      }
      ?>
    </div>
    <?php
    include('stopka.inc.php');    
    
} ?>
