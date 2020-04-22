<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ( $_POST['typ_pola'] == '0' || $_POST['typ_pola'] == '1' ) {
          $wartosc_pola = 'NULL';
        }
        
        $typ = '';
        if ( $_POST['typ_pola'] == '6' ) {
             $_POST['typ_pola'] = 0;
             $typ = 'kalendarz';
        }        
        
        $pola = array(
                array('fields_input_type',$filtr->process($_POST['typ_pola'])),
                array('fields_status','1'),
                array('fields_required_status',$filtr->process($_POST['wymagalnosc_pola'])),
                array('fields_order',$filtr->process($_POST['sort'])),
                array('fields_type',$typ));
                
         unset($typ);
        
        $sql = $db->insert_query('customers_extra_fields' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {

            if ( isset($_POST['wartosc_pola_'.$w]) ) {
              if (!empty($_POST['wartosc_pola_'.$w])) {
                $wartosc_pola = $filtr->process($_POST['wartosc_pola_'.$w]);
              } else {
                $wartosc_pola = $filtr->process($_POST['wartosc_pola_0']);
              }
            } else {
                $wartosc_pola = '';
            }
            //
            if (!empty($_POST['nazwa_'.$w])) {
              $nazwa = $filtr->process($_POST['nazwa_'.$w]);
            } else {
              $nazwa = $filtr->process($_POST['nazwa_0']);
            }

            $pola = array(
                    array('fields_id',$id_dodanej_pozycji),
                    array('languages_id',$ile_jezykow[$w]['id']),
                    array('fields_name',$nazwa),
                    array('fields_input_value',$wartosc_pola)
            );
            $sql = $db->insert_query('customers_extra_fields_info' , $pola);
            unset($pola);
        }
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('dodatkowe_pola_klienci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('dodatkowe_pola_klienci.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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

          <form action="slowniki/dodatkowe_pola_klienci_dodaj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
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
                    
                        $tbjezyki[] = $w;
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required">Nazwa:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label>Nazwa:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="45" value="" />
                               <?php } ?>
                            </p> 
                            
                            <p id="wart_<?php echo $w; ?>" style="display:none">
                                <label id="label_wartosc_pola_<?php echo $w; ?>">Wartość pola:</label>
                                <textarea rows="4" cols="60" name="wartosc_pola_<?php echo $w; ?>" id="wartosc_pola_<?php echo $w; ?>" disabled="disabled"></textarea>
                             </p> 
                             
                        </div>
                        
                        <?php                    
                    }                    
                    ?>                      
                </div>                
            
                <p>
                  <label>Typ pola:</label>
                  <input type="radio" value="0" name="typ_pola" checked="checked" onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','ukryj')" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika INPUT pozwala na wpisanie tylko jednego wiersza tekstu" /> Input
                  <input type="radio" value="6" name="typ_pola" onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','ukryj')" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika INPUT - tylko wybór daty z kalendarza" /> Input (kalendarz)
                  <input type="radio" value="1" name="typ_pola" onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','ukryj')" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika TEXTAREA pozwala na wpisanie wielu wierszy tekstu" /> Textarea
                  <input type="radio" value="2" name="typ_pola" onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','pokaz')" class="toolTipTop" title="Pole jednokrotnego wyboru" /> Radio Button
                  <input type="radio" value="3" name="typ_pola" class="toolTipTop" title="Pole wielokrotnego wyboru"  onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','pokaz')" /> Checkbox
                  <input type="radio" value="4" name="typ_pola" onclick="zmien_wartosc('<?php echo implode(',', $tbjezyki); ?>','pokaz')" class="toolTipTop" title="Pole listy rozwijanej" /> Drop down menu
                </p> 
                
                <?php
                unset($tbjezyki);
                ?>                

                <p>
                  <label>Wymagane:</label>
                  <input type="radio" value="1" name="wymagalnosc_pola" checked="checked" class="toolTipTop" title="Wypełnienie pola będzie wymagane podczas rejestracji klienta" /> tak
                  <input type="radio" value="0" name="wymagalnosc_pola" class="toolTipTop" title="Wypełnienie pola nie będzie wymagane podczas rejestracji klienta" /> nie
                </p> 

                <p>
                  <label class="required">Kolejność wyświetlania:</label>
                  <input type="text" name="sort" id="sort" value="" size="5" />
                </p>           

                <script type="text/javascript">
                //<![CDATA[
                gold_tabs('0');
                //]]>
                </script>                 
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola_klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}