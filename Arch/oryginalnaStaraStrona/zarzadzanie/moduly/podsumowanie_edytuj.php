<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if ( isset($_POST['PARAMETRY']) && $filtr->process($_POST['klasa']) ==  'ot_loyalty_discount' ) {
            if (!isset($_POST['PARAMETRY']['STALI_KLIENCI_GRUPA_KLIENTOW']))
              $_POST['PARAMETRY']['STALI_KLIENCI_GRUPA_KLIENTOW'] = array();
        }
        
        if ( isset($_POST['PARAMETRY']) && $filtr->process($_POST['klasa']) ==  'ot_shopping_discount' ) {
            if (!isset($_POST['PARAMETRY']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW']))
              $_POST['PARAMETRY']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW'] = array();
        }        

        //
        // Aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["nazwa"])),
                array('sortowanie',$filtr->process($_POST["sort"])),
                array('status',$filtr->process($_POST["status"])),
                array('prefix',$filtr->process($_POST["prefix"]))
        );
        //
        $pola[] = array('skrypt',$filtr->process($_POST["skrypt"]));
        $pola[] = array('klasa',$filtr->process($_POST["klasa"]));

        $db->update_query('modules_total' , $pola, " id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        //Aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='".strtoupper($filtr->process($_POST['klasa']))."_TYTUL'");
        $pola = array(
                array('translate_constant',strtoupper($filtr->process($_POST['klasa'])).'_TYTUL'),
                array('section_id', '3')
                );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["id_tlumaczenia"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['nazwa_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_'.$w])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['nazwa_0'])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        

        if ( isset($_POST['PARAMETRY']) && count($_POST['PARAMETRY']) > 0 ) {
        
            while (list($key, $value) = each($_POST['PARAMETRY'])) {
            
              if (is_array($value)) $value = implode(";", $value);$value = str_replace("\r\n",", ", $value);
              $pola = array(
                      array('wartosc',($value != '0' ? $filtr->process($value) : ''))
              );
              $db->update_query('modules_total_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = '".$key."'");	
              unset($pola);
              
            }
            
        }

        if ( isset($_POST['parametry_prog_przedzial']) && isset($_POST['parametry_prog_wartosc']) ) {
        
            $progi_znizek = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_prog_przedzial'], $_POST['parametry_prog_wartosc']);
            $pola = array(
                    array('wartosc',implode(";", $progi_znizek))
            );
            
            if ( $filtr->process($_POST["klasa"]) == 'ot_loyalty_discount' ) {
                 $db->update_query('modules_total_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'STALI_KLIENCI_PROGI_ZNIZEK'");	
            }
            if ( $filtr->process($_POST["klasa"]) == 'ot_shopping_discount' ) {
                 $db->update_query('modules_total_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'ZNIZKI_KOSZYKA_PROGI_ZNIZEK'");	
            }            
            unset($pola);
            
        }

        //
       Funkcje::PrzekierowanieURL('podsumowanie.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <script type="text/javascript" src="javascript/jquery.bestupper.min.js"></script>        
          <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
          <script type="text/javascript" src="javascript/jquery.application.js"></script>
          <script type="text/javascript" src="moduly/moduly.js"></script>

          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
             $('.bestupper').bestupper();
          });
          //]]>
          </script>     

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#modulyForm").validate({
              rules: {
                nazwa_0: {
                  required: true
                },
                skrypt: {
                  required: true
                },
                klasa: {
                  required: true
                },
                sort: {
                  required: true
                }
              },
              messages: {
                nazwa_0: {
                  required: "Pole jest wymagane"
                }               
              }
            });
          });
          //]]>
          </script>        

          <form action="moduly/podsumowanie_edytuj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }

            $zapytanie = "SELECT * FROM modules_total WHERE id = '" . (int)$filtr->process($_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$filtr->process($_GET['id_poz']); ?>" />
                    
                    <p>
                      <label class="required">Nazwa modułu:</label>
                      <input type="text" name="nazwa" size="73" value="<?php echo $info['nazwa']; ?>" id="nazwa" class="toolTipText" title="Robocza nazwa widoczna w panelu administracyjnym sklepu" />
                    </p>

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
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
                      $tlumaczenie = strtoupper($info['klasa']) . '_TYTUL';

                      for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                            
                        // pobieranie danych jezykowych
                        $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$tlumaczenie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                        $sqls = $db->open_query($zapytanie_jezyk);
                        $nazwa = $sqls->fetch_assoc();   
                        ?>
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required">Treść wyświetlana w sklepie:</label>
                                <textarea cols="80" rows="3" name="nazwa_<?php echo $w; ?>" id="nazwa_0"><?php echo $nazwa['translate_value']; ?></textarea>
                               <?php } else { ?>
                                <label>Tresc:</label>
                                <textarea cols="80" rows="3" name="nazwa_<?php echo $w; ?>"><?php echo $nazwa['translate_value']; ?></textarea>
                               <?php } ?>
                            </p> 
                                        
                        </div>
                        <?php
                        $db->close_query($sqls);
                        unset($zapytanie_jezyk);
                        
                       }
                       ?>
                       
                    </div>
                    
                    <input type="hidden" name="id_tlumaczenia" value="<?php echo $nazwa['translate_constant_id']; ?>" />

                    <p>
                      <label class="required">Kolejność wyswietlania:</label>
                      <input type="text" name="sort" size="5" value="<?php echo $info['sortowanie']; ?>" id="sort" class="bestupper toolTip" title="Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania." />
                    </p>
                    
                    <?php
                    if ( $info['klasa'] != 'ot_total' && $info['klasa'] != 'ot_subtotal' ) { ?>
                      <p>
                        <label>Status:</label>
                        <input type="radio" value="1" name="status" <?php echo (($info['status'] == '1') ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Czy moduł ma być wliczany do wartości zamówienia" /> włączony
                        <input type="radio" value="0" name="status" <?php echo (($info['status'] == '0') ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Czy moduł ma być wliczany do wartości zamówienia" /> wyłączony          
                      </p>
                    <?php } else { ?>
                      <input type="hidden" name="status" id="status" value="1" />
                    <?php } ?>
                    
                    <p>
                      <label>Wartość zamówienia:</label>
                      <input type="radio" value="1" name="prefix" <?php echo (($info['prefix'] == '1') ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Czy moduł ma być dodawany czy odejmowany przy wyliczaniu wartości zamówienia" /> zwiększa
                      <input type="radio" value="0" name="prefix" <?php echo (($info['prefix'] == '0') ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Czy moduł ma być dodawany czy odejmowany przy wyliczaniu wartości zamówienia" /> zmniejsza
                      <input type="radio" value="9" name="prefix" <?php echo (($info['prefix'] == '9') ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Czy moduł ma być dodawany czy odejmowany przy wyliczaniu wartości zamówienia" /> brak
                    </p>                     

                    <?php

                    $zapytanie_parametry = "SELECT * FROM modules_total_params WHERE modul_id = '" . (int)$filtr->process($_GET['id_poz']) . "' ORDER BY sortowanie";
                    $sql_parametry = $db->open_query($zapytanie_parametry);
                                
                    if ((int)$db->ile_rekordow($sql_parametry) > 0) {
                    
                      while ( $info_parametry = $sql_parametry->fetch_assoc() ) {

                        if ( $info_parametry['kod'] == 'STALI_KLIENCI_OKRES_NALICZANIA_ZAMOWIEN' ) {
                        
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';
                          echo '&nbsp;<input type="radio" value="3" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '3') ? 'checked="checked"' : '').' class="toolTipTop" title="Zliczane są zamówienia z ostatniego kwartału" /> kwartalnie';
                          echo '<input type="radio" value="1" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '1') ? 'checked="checked"' : '').' class="toolTipTop" title="Zliczane są zamówienia z ostatniego roku" /> rocznie';
                          echo '<input type="radio" value="99" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '99') ? 'checked="checked"' : '').' class="toolTipTop" title="Zliczane są wszystkie zamówienia klienta" /> wszystkie';
                          echo '</p>';
                          
                        }
                        
                        if ( $info_parametry['kod'] == 'STALI_KLIENCI_STATUS_ZAMOWIEN' ) {
                        
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';
                          $tablica_statusow = Sprzedaz::ListaStatusowZamowien( false );
                          echo '&nbsp;'.Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica_statusow, $info_parametry['wartosc'], ' id="'.$info_parametry['kod'].'"');
                          echo '</p>';
                          
                        }
                        
                        if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_PROMOCJE' ) {
                        
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].'</label>';
                          echo '&nbsp;<input type="radio" value="tak" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : '').' /> tak';
                          echo '<input type="radio" value="nie" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : '').' /> nie';
                          echo '</p>';
                          
                        }    

                        if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_SPOSOB' ) {
                        
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].'</label>';
                          echo '&nbsp;<input type="radio" value="kwota" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'kwota') ? 'checked="checked"' : '').' class="toolTipTop" title="Zniżka zależna od wartości produktów w koszyku" /> wartość produktów';
                          echo '<input type="radio" value="ilosc" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == 'ilosc') ? 'checked="checked"' : '').' class="toolTipTop" title="Zniżka zależna od ilości produktów w koszyku" /> ilość produktów';
                          echo '</p>';
                          
                        }                        
                        
                        if ( $info_parametry['kod'] == 'STALI_KLIENCI_PROGI_ZNIZEK' || $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_PROGI_ZNIZEK' ) {
                        
                          $tablica_progow = explode(';',$info_parametry['wartosc']);

                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';
                          echo '</p>';

                          echo '<div id="progiPrzedzial" style="margin-top:-25px;margin-bottom:10px;" >';
                          
                          $symbol = $domyslna_waluta['symbol'];
                          if ( $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_PROGI_ZNIZEK' ) {
                              $symbol .= ' / szt.';
                          }
                          
                          for ( $i = 0, $c = count($tablica_progow); $i < $c; $i++ ) {
                            $idDiv = $i+1;
                            $koszt = explode(':', $tablica_progow[$i]);
                            echo '<div style="margin-left:285px;padding-bottom:3px;" id="prog'.$idDiv.'">powyżej &nbsp; <input class="kropka" type="text" size="10" name="parametry_prog_przedzial[]" value="' . number_format($koszt['0'],2,'.','') . '" /> ' . $symbol . ' &nbsp; ';
                            echo '<input class="kropka" type="text" name="parametry_prog_wartosc[]" value="' . number_format($koszt['1'],2,'.','') . '" /> %</div>';
                          }
                          
                          echo '<div style="padding-left:285px;padding-top:5px;">';
                          echo '<span class="dodaj" onclick="dodaj_pozycje(\'progiPrzedzial\',\'prog\', \'' . $symbol . '\', \'powyżej\', \'%\')" style="cursor:pointer">dodaj pozycję</span> &nbsp; &nbsp; <span class="usun" onclick="usun_pozycje(\'progiPrzedzial\',\'prog\')" style="cursor:pointer; '.(count($tablica_progow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>';
                          echo '</div>';
                          echo '</div>';
                          
                          unset($symbol);
                          
                        }
                        
                        if ( $info_parametry['kod'] == 'STALI_KLIENCI_GRUPA_KLIENTOW'  || $info_parametry['kod'] == 'ZNIZKI_KOSZYKA_GRUPA_KLIENTOW' ) {
                        
                          $tablica_grup = explode(';',$info_parametry['wartosc']);
                          $tablica_tmp = Klienci::ListaGrupKlientow(false);
                          
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';
                          echo '<select name="PARAMETRY['.$info_parametry['kod'].'][]" multiple="multiple" id="multipleHeaders">';
                          foreach ( $tablica_tmp as $rekord ) {
                            $wybrany = '';
                            if ( in_array($rekord['id'], $tablica_grup ) ) {
                              $wybrany = 'selected="selected"';
                            }
                            echo '<option value="'.$rekord['id'].'" '.$wybrany.'>'.$rekord['text'].'</option>';
                          }
                          echo '</select>';
                          echo '</p>';
                          
                          echo '<div class="ostrzezenie" style="margin:5px 5px 5px 280px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>';
                          
                          unset($tablica_grup, $wybrany);  
                            
                        }

                      }

                    }

                    $db->close_query($sql_parametry);
                    unset($zapytanie_parametry, $info_parametry);

                    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { 
                      ?>

                      <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                      <p>
                          <label class="required">Skrypt:</label>   
                          <input type="text" name="skrypt" id="skrypt" size="53" value="<?php echo $info['skrypt']; ?>" class="toolTipText" title="Nazwa skryptu realizującego funkcje modułu." onkeyup="updateKeySkrypt();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> />
                      </p>

                      <p>
                          <label class="required">Nazwa klasy:</label>   
                          <input type="text" name="klasa" id="klasa" size="53" value="<?php echo $info['klasa']; ?>" class="toolTipText" title="Nazwa klasy realizującej funkcje modułu." onkeyup="updateKeyKlasa();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> />
                      </p>

                    <?php } else { ?>
                    
                      <input type="hidden" name="skrypt" id="skrypt" value="<?php echo $info['skrypt']; ?>" />
                      <input type="hidden" name="klasa" id="klasa" value="<?php echo $info['klasa']; ?>" />
                      
                    <?php } ?>

                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0');
                    //]]>
                    </script>  
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('podsumowanie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>           
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