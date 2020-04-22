<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // Aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["NAZWA"])),
                array('skrypt',$filtr->process($_POST["SKRYPT"])),
                array('klasa',$filtr->process($_POST["KLASA"])),
                array('sortowanie',$filtr->process($_POST["SORT"])),
                array('status',$filtr->process($_POST["STATUS"])),
        );
        //	
        $db->insert_query('modules_payment' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);


        //Aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".$id_dodanej_pozycji."_TYTUL'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.$id_dodanej_pozycji.'_TYTUL'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['NAZWA_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_'.$w])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_0'])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);
        // ################
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".$id_dodanej_pozycji."_OBJASNIENIE'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.$id_dodanej_pozycji.'_OBJASNIENIE'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['OBJASNIENIE_'.$w])),
                    array('translate_constant_id',$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id'])
            );
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);


        // ################
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".$id_dodanej_pozycji."_TLUMACZENIE'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.$id_dodanej_pozycji.'_TEKST'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['TEKST_INFO_'.$w])),
                    array('translate_constant_id',$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id'])
            );
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Koszt płatności'),
                array('kod','PLATNOSC_KOSZT'),
                array('sortowanie','1'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['PLATNOSC_KOSZT'])),
        );
        $db->insert_query('modules_payment_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Minimalny koszt płatności'),
                array('kod','PLATNOSC_KOSZT_MINIMUM'),
                array('sortowanie','2'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['PLATNOSC_KOSZT_MINIMUM'])),
        );
        $db->insert_query('modules_payment_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Wartość zamówienia od'),
                array('kod','PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'),
                array('sortowanie','3'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'])),
        );
        $db->insert_query('modules_payment_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Wartość zamówienia do'),
                array('kod','PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'),
                array('sortowanie','4'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'])),
        );
        $db->insert_query('modules_payment_params' , $pola);
        unset($pola);

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('platnosc.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('platnosc.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
          <script type="text/javascript" src="javascript/jquery.application.js"></script>
          <script type="text/javascript" src="moduly/moduly.js"></script>

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#modulyForm").validate({
              rules: {
                NAZWA: {
                  required: true
                },
                NAZWA_0: {
                  required: true
                },
                SKRYPT: {
                  required: true
                },
                KLASA: {
                  required: true
                },
                SORT: {
                  required: true
                }
              },
              messages: {
                NAZWA_0: {
                  required: "Pole jest wymagane"
                }               
              }
            });
          });
          //]]>
          </script>        

          <script type="text/javascript">
          //<![CDATA[
          function updateKeySkrypt() {
              var key=$("#skrypt").val();
              key=key.replace(" ","_");
              $("#skrypt").val(key);
          }
          function updateKeyKlasa() {
              var key=$("#klasa").val();
              key=key.replace(" ","_");
              $("#klasa").val(key);
          }
          //]]>
          </script>     

          <form action="moduly/platnosc_dodaj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                  <p>
                    <label class="required">Nazwa:</label>
                    <input type="text" name="NAZWA" size="73" value="" id="nazwa" />
                  </p>

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\', \'650\',\'200\', \'\')">'.$ile_jezykow[$w]['text'].'</span>';
                }                    
                ?>                   
                </div>
                
                <div style="clear:both"></div>
                

                  <div class="info_tab_content">
                      <?php

                      for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                            
                        // pobieranie danych jezykowych
                        ?>
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required">Treść wyświetlana w sklepie:</label>
                                <textarea cols="120" rows="3" name="NAZWA_<?php echo $w; ?>" id="nazwa_0"></textarea>
                               <?php } else { ?>
                                <label>Treść wyświetlana w sklepie:</label>
                                <textarea cols="120" rows="3" name="NAZWA_<?php echo $w; ?>"></textarea>
                               <?php } ?>
                            </p> 
                                        
                            <p>
                              <label>Treść objaśnienia w sklepie:</label>
                              <textarea cols="120" rows="3" name="OBJASNIENIE_<?php echo $w; ?>"></textarea>
                            </p> 

                            <label style="margin-left:10px;">Opis wyświetlany przed zatwierdzeniem zamówienia:</label>
                            <div class="edytor" style="margin:-33px 0px 10px 268px;">
                                <textarea cols="60" rows="30" id="edytor_<?php echo $w; ?>" name="TEKST_INFO_<?php echo $w; ?>"></textarea>
                            </div>

                        </div>
                        <?php                    
                         }                    
                         ?>
                  </div>

                  <p>
                    <label class="required">Kolejność wyswietlania:</label>
                    <input type="text" name="SORT" size="5" value="" id="sort" class="bestupper" />
                  </p>

                  <p>
                    <label>Status:</label>
                    <input type="radio" value="1" name="STATUS" checked="checked" /> włączony
                    <input type="radio" value="0" name="STATUS" class="toolTipTop" /> wyłączony
                  </p>

                  <p>
                    <label>Koszt płatności:</label>
                    <input type="text" size="35" name="PARAMETRY[PLATNOSC_KOSZT]" value="" id="PLATNOSC_KOSZT" />
                  </p>

                  <p>
                    <label>Minimalny koszt płatności:</label>
                    <input type="text" size="35" name="PARAMETRY[PLATNOSC_KOSZT_MINIMUM]" value="" id="PLATNOSC_KOSZT_MINIMUM" />
                  </p>

                  <p>
                    <label>Wartość zamówienia od:</label>
                    <input type="text" size="35" name="PARAMETRY[PLATNOSC_WARTOSC_ZAMOWIENIA_MIN]" value="" id="PLATNOSC_WARTOSC_ZAMOWIENIA_MIN" class="kropka" />
                  </p>

                  <p>
                    <label>Wartość zamówienia do:</label>
                    <input type="text" size="35" name="PARAMETRY[PLATNOSC_WARTOSC_ZAMOWIENIA_MAX]" value="" id="PLATNOSC_WARTOSC_ZAMOWIENIA_MAX" class="kropka" />
                  </p>

                  <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                  <p>
                   <label class="required">Skrypt:</label>   
                   <input type="text" name="SKRYPT" id="skrypt" size="53" value="" class="toolTipText" title="Nazwa skryptu realizującego funkcje modułu." onkeyup="updateKeySkrypt();" />
                  </p>

                  <p>
                    <label class="required">Nazwa klasy:</label>   
                    <input type="text" name="KLASA" id="klasa" size="53" value="" class="toolTipText" title="Nazwa klasy realizującej funkcje modułu." onkeyup="updateKeyKlasa();" />
                  </p>

                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0','edytor_', '650', '200', '');
                    //]]>
                    </script>                    

                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('platnosc','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>   
            </div>            

          </div>                      
          </form>

          <div class="objasnienia">
              <div class="objasnieniaTytul">Koszt płatności</div>
              <div class="objasnieniaTresc">W polu można zastosować wzór stosowany do obliczania prowizji od danej formy płatności, w miejsce x zostanie wstawiona suma wartości produktów i kosztu dostawy<br />Przykłady:<br /><br />

              <table class="tbl_opis">
                <tr class="tbl_opis_naglowek">
                  <td><span>Wartość pola</span></td>
                  <td><span>Format</span></td>
                  <td><span>Opis</span></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>pole puste lub 0</td>
                  <td>koszt płatności wynosi 0</td>
                </tr>
                <tr>
                  <td><code>11.50</code></td>
                  <td>liczba</td>
                  <td>koszt płatności wynosi 11,50, niezależnie od wartości zamówienia</td>
                </tr>
                <tr>
                  <td><code>x*0.035</code></td>
                  <td>x, znak mnożenia, liczba</td>
                  <td>koszt płatności zostanie wyliczony wg wzoru:<br /><code>(wartosc_produktow + koszt_dostawy) * 0,035</code></td>
                </tr>
                <tr>
                  <td><code>x*0.035+11.50</code></td>
                  <td>x, znak mnożenia, liczba, znak plus, liczba</td>
                  <td>koszt płatności zostanie wyliczony wg wzoru:<br /><code>(wartosc_produktow + koszt_dostawy) * 0,035 + 11,50</code></td>
                </tr>
              </table>
              </div>
          </div>
    </div>    
    
    <?php
    include('stopka.inc.php');

}