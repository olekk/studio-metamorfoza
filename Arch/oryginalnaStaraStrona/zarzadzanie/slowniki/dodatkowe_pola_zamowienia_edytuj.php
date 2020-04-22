<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ( $_POST['typ_pola'] == '0' || $_POST['typ_pola'] == '1' || $_POST['typ_pola'] == '6' ) {
          $wartosc_pola = 'NULL';
        }
        
        $typ = '';
        if ( $_POST['typ_pola'] == '6' ) {
             $_POST['typ_pola'] = 0;
             $typ = 'kalendarz';
        }
        
        $pola = array(
                array('fields_input_type',$filtr->process($_POST['typ_pola'])),
                array('fields_required_status',$filtr->process($_POST['wymagalnosc_pola'])),
                array('fields_order',$filtr->process($_POST['sort'])),
                array('fields_type',$typ));
        
        unset($typ);
        //			
        $db->update_query('orders_extra_fields' , $pola, " fields_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //

        // kasuje rekordy w tablicy
        $db->delete_query('orders_extra_fields_info' , " fields_id = '".$filtr->process($_POST["id"])."'");
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {

            if ( $_POST['typ_pola'] != '0' && $_POST['typ_pola'] != '1' ) {

              if ( isset($_POST['wartosc_pola_'.$w]) ) {
                if (!empty($_POST['wartosc_pola_'.$w])) {
                  $wartosc_pola = $filtr->process($_POST['wartosc_pola_'.$w]);
                } else {
                  $wartosc_pola = $filtr->process($_POST['wartosc_pola_0']);
                }
              } else {
                $wartosc_pola = '';
              }
            } else {
                $wartosc_pola = '';
            }

            if (!empty($_POST['nazwa_'.$w])) {
              $nazwa = $filtr->process($_POST['nazwa_'.$w]);
            } else {
              $nazwa = $filtr->process($_POST['nazwa_0']);
            }
       
            $pola = array(
                    array('fields_id',$filtr->process($_POST["id"])),
                    array('languages_id',$ile_jezykow[$w]['id']),
                    array('fields_name',$nazwa),
                    array('fields_input_value',$wartosc_pola));
                    
            $sql = $db->insert_query('orders_extra_fields_info' , $pola);

            if ( $_POST['typ_pola'] != '0' && $_POST['typ_pola'] != '1' ) {

              $stara_wartosc_query = "SELECT fields_id, value 
                                        FROM orders_to_extra_fields 
                                       WHERE fields_id= '" . (int)$_POST["id"] . "'
                                         AND language_id = '" . $ile_jezykow[$w]['id'] . "'";
                  
              $tablica_wartosci_nowych = explode("\n", $_POST['wartosc_pola_'.$w]);
              $tablica_wartosci_starych = explode("\n", $_POST['stara_wartosc_pola_'.$w]);

              $sql = $db->open_query($stara_wartosc_query);

              if ( (int)$db->ile_rekordow($sql) > 0  ) {

                while ( $starePola = $sql->fetch_assoc() ) {

                  $nowa_wartosc = '';
                  $tablica_wartosci_starych_klienta = explode("\n", trim($starePola['value']));

                  for( $i = 0, $c = count($tablica_wartosci_starych); $i < $c; $i++) {
                    if ( in_array(trim($tablica_wartosci_starych[$i]), $tablica_wartosci_starych_klienta) ) {
                      $nowa_wartosc .= $tablica_wartosci_nowych[$i]."\n";
                    }
                  }

                  $pola_do_zmiany = array(
                                    array('value',rtrim($nowa_wartosc))
                  );

                  $db->update_query('orders_to_extra_fields' , $pola_do_zmiany, " fields_id = '".(int)$starePola['fields_id']."' AND language_id = '" . $ile_jezykow[$w]['id'] . "'");	
                }
                
              }
              
              $db->close_query($sql);

              unset($stara_wartosc_query, $pola_do_zmiany);
              unset($tablica_wartosci_nowych);
              unset($tablica_wartosci_starych);
              
            }
            unset($pola);
            //            
        }

        Funkcje::PrzekierowanieURL('dodatkowe_pola_zamowienia.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa_0: {
                  required: true
                },
                sort: {
                  required: true,
                  range: [0, 999],
                  number: true
                },
                wartosc_pola_0: {
                  required: function() {
                    var wynik = true;
                    if ( $("input[name='typ_pola']:checked", "#slownikForm").val() == "0" ) { wynik = false; }
                    if ( $("input[name='typ_pola']:checked", "#slownikForm").val() == "1" ) { wynik = false; }
                    return wynik;
                  }
                }
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane"
                },
                wartosc_pola_0: {
                  required: "Pole jest wymagane"
                },
                sort: {
                  required: "Pole jest wymagane"
                }
              }
            });
          });
          //]]>
          </script>     

          <form action="slowniki/dodatkowe_pola_zamowienia_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from orders_extra_fields where fields_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
                    <script type="text/javascript">
                    //<![CDATA[
                    function zmien_wartosc(nr, akcja) {
                        //
                        var tbnr = nr.split(',');
                        //
                        if ( akcja == 'ukryj' ) {
                            for (c = 0; c < tbnr.length; c++) {
                                $('#wart_' + tbnr[c]).slideUp();
                                $('#wartosc_pola_' + tbnr[c]).attr('disabled','disabled');
                                $('#wartosc_pola_' + tbnr[c]).val('');
                                $('#label_wartosc_pola_' + tbnr[c]).removeClass('required');
                                $('label[for=wartosc_pola_' + tbnr[c] + ']').hide();
                            }
                          } else {
                            for (c = 0; c < tbnr.length; c++) {
                                $('#wartosc_pola_' + tbnr[c]).removeAttr('disabled');
                                $('#label_wartosc_pola_0').addClass('required');
                                $('#wart_' + tbnr[c]).slideDown();
                            }
                        }
                    }                                       
                    //]]>
                    </script>                      
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php 
                        $tbjezyki = array();
                        
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "SELECT distinct * FROM orders_extra_fields_info WHERE fields_id = '".$filtr->process((int)$_GET['id_poz'])."' and languages_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            $tbjezyki[] = $w;
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Nazwa:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="70" value="<?php echo $nazwa['fields_name']; ?>" id="nazwa_0" />
                                   <?php } else { ?>
                                    <label>Nazwa:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="70" value="<?php echo $nazwa['fields_name']; ?>" />
                                   <?php } ?>
                                </p> 
                                
                                <p id="wart_<?php echo $w; ?>" <?php echo (($info['fields_input_type'] != '0') && ($info['fields_input_type'] != '1') ? '' : 'style="display:none"'); ?>>
                                  <label id="label_wartosc_pola_<?php echo $w; ?>"<?php echo ($w == '0' ? ((($info['fields_input_type'] != '0') && ($info['fields_input_type'] != '1')) ? 'class="required"' : '' ) : '' ); ?>>Wartość pola (wprowadź każdą wartość w osobnej linii):</label>
                                  <textarea rows="4" cols="60" name="wartosc_pola_<?php echo $w; ?>" id="wartosc_pola_<?php echo $w; ?>" <?php echo (($info['fields_input_type'] != '0') && ($info['fields_input_type'] != '1') ? '' : 'disabled'); ?>><?php echo $nazwa['fields_input_value']; ?></textarea>
                                </p> 
                                
                                <input type="hidden" name="stara_wartosc_pola_<?php echo $w; ?>" value="<?php echo $nazwa['fields_input_value']; ?>" />

                            </div>
                            
                            <?php
                            $db->close_query($sqls);
                            unset($nazwa);
                        }
                        ?>                      
                    </div>                
                    
                    <p>
                      <label>Typ pola:</label>
                      <input type="radio" value="0" name="typ_pola" <?php echo (($info['fields_input_type'] == '0' && $info['fields_type'] != 'kalendarz') ? 'checked="checked"' : ''); ?> onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','ukryj')" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika INPUT pozwala na wpisanie tylko jednego wiersza tekstu" /> Input
                      <input type="radio" value="6" name="typ_pola" <?php echo (($info['fields_input_type'] == '0' && $info['fields_type'] == 'kalendarz') ? 'checked="checked"' : ''); ?> onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','ukryj')" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika INPUT - tylko wybór daty z kalendarza" /> Input (kalendarz)
                      <input type="radio" value="1" name="typ_pola" <?php echo (($info['fields_input_type'] == '1') ? 'checked="checked"' : ''); ?> onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','ukryj')" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika TEXTAREA pozwala na wpisanie wielu wierszy tekstu" /> Textarea
                      <input type="radio" value="2" name="typ_pola" <?php echo (($info['fields_input_type'] == '2') ? 'checked="checked"' : ''); ?> onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','pokaz')" class="toolTipTop" title="Pole jednokrotnego wyboru" /> Radio Button
                      <input type="radio" value="3" name="typ_pola" <?php echo (($info['fields_input_type'] == '3') ? 'checked="checked"' : ''); ?> onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','pokaz')" class="toolTipTop" title="Pole wielokrotnego wyboru" /> Checkbox
                      <input type="radio" value="4" name="typ_pola" <?php echo (($info['fields_input_type'] == '4') ? 'checked="checked"' : ''); ?> onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','pokaz')" class="toolTipTop" title="Pole listy rozwijanej" /> Drop down menu
                    </p> 
                    
                    <?php
                    unset($tbjezyki);
                    ?>

                    <p>
                      <label>Wymagane:</label>
                      <input type="radio" value="1" name="wymagalnosc_pola" <?php echo (($info['fields_required_status'] == '1') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Wypełnienie pola będzie wymagane podczas rejestracji klienta" /> tak
                      <input type="radio" value="0" name="wymagalnosc_pola" <?php echo (($info['fields_required_status'] == '0') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Wypełnienie pola nie będzie wymagane podczas rejestracji klienta" /> nie
                    </p> 
                    
                    <p>
                      <label class="required">Kolejność wyświetlania:</label>
                      <input type="text" name="sort" id="sort" value="<?php echo $info['fields_order']; ?>" size="5" />
                    </p>           

                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0');
                    //]]>
                    </script>                 
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola_zamowienia','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
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