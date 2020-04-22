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
                array('nazwa',$filtr->process($_POST["NAZWA_MODULU"])),
                array('sortowanie',$filtr->process($_POST["SORT"])),
                array('status',$filtr->process($_POST["STATUS"])),
        );
        //
        $pola[] = array('skrypt',$filtr->process($_POST["SKRYPT"]));
        $pola[] = array('klasa',$filtr->process($_POST["KLASA"]));

        $db->update_query('modules_payment' , $pola, " id = '".(int)$_POST["id"]."'");
        unset($pola);
        
        //aktualizacja nazwy modulu w tablicy orders
        //$pola = array(
        //        array('payment_method',$filtr->process($_POST["NAZWA"])),
        //);
        //$db->update_query('orders' , $pola, " payment_method = '".$_POST["STARA_NAZWA"]."'");	
        //unset($pola);


        //Aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".(int)$_POST["id"]."_TYTUL'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.(int)$_POST["id"].'_TYTUL'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            if (!empty($_POST['NAZWA_'.$w])) {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_'.$w])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            } else {
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_0'])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id'])
                 );
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        // ##############
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".(int)$_POST["id"]."_OBJASNIENIE'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.(int)$_POST["id"].'_OBJASNIENIE'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA_OBJASNIENIA"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['OBJASNIENIE_'.$w])),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id'])
            );
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }

        // ##############
        $db->delete_query('translate_constant', "translate_constant='PLATNOSC_".(int)$_POST["id"]."_TEKST'");
        $pola = array(
            array('translate_constant','PLATNOSC_'.(int)$_POST["id"].'_TEKST'),
            array('section_id', '19')
            );
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA_TEKST_INFO"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['TEKST_INFO_'.$w])),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id'])
            );
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }

        while (list($key, $value) = each($_POST['PARAMETRY'])) {
          if (is_array($value)) $value = implode(";", $value);
          //$value = str_replace("\r\n",", ", $value);
          $pola = array(
                  array('wartosc',$filtr->process($value)),
                 );
          $db->update_query('modules_payment_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = '".$key."'");	
          unset($pola);
        }

        //aktualizacja listy kategorii dla Lukas Raty
        if ( isset($_POST['id_kat']) && is_array($_POST['id_kat']) ) {
            $kategorie_wykluczone = implode(',',$_POST['id_kat']);
            $pola = array(
                    array('wartosc',$kategorie_wykluczone),
                 );
            $db->update_query('modules_payment_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'PLATNOSC_LUKAS_KATEGORIE'");	
           unset($pola);
        }

        //
        Funkcje::PrzekierowanieURL('platnosc.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <script type="text/javascript" src="moduly/moduly.js"></script>

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#modulyForm").validate({
              rules: {
                NAZWA_MODULU: {
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

          <form action="moduly/platnosc_edytuj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }

            $zapytanie = "SELECT * FROM modules_payment WHERE id = '" . (int)$filtr->process($_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo (int)$filtr->process($_GET['id_poz']); ?>" />
                    
                    <input type="hidden" name="STARA_NAZWA" value="<?php echo $info['nazwa']; ?>" />

                    <p>
                      <label class="required">Nazwa modułu:</label>
                      <input type="text" name="NAZWA_MODULU" size="73" value="<?php echo $info['nazwa']; ?>" id="nazwa" class="toolTipText Tekst" title="Robocza nazwa widoczna w panelu administracyjnym sklepu" />
                    </p>

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                
                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';

                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\', \'650\',\'200\', \'\')">'.$ile_jezykow[$w]['text'].'</span>';
                }                    
                ?>                   
                </div>
                
                <div style="clear:both"></div>
                
                    <div class="info_tab_content">
                      <?php
                      $tlumaczenie = 'PLATNOSC_' . (int)$_GET['id_poz'] . '_TYTUL';
                      $objasnienie = 'PLATNOSC_' . (int)$_GET['id_poz'] . '_OBJASNIENIE';
                      $tekstInfoPotwierdzenie = 'PLATNOSC_' . (int)$_GET['id_poz'] . '_TEKST';

                      for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        ?>
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                          <?php
                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$tlumaczenie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $nazwa = $sqls->fetch_assoc();   
                          ?>

                          <p>
                            <?php if ($w == '0') { ?>
                              <label class="required">Treść wyświetlana w sklepie:</label>
                              <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>" id="nazwa_0"><?php echo $nazwa['translate_value']; ?></textarea>
                            <?php } else { ?>
                              <label>Treść wyświetlana w sklepie:</label>
                              <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>"><?php echo $nazwa['translate_value']; ?></textarea>
                            <?php } ?>
                          </p> 

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk);

                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$objasnienie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $objasn = $sqls->fetch_assoc();   
                          ?>

                          <p>
                            <label>Treść objaśnienia w sklepie:</label>
                            <textarea cols="80" rows="3" name="OBJASNIENIE_<?php echo $w; ?>"><?php echo $objasn['translate_value']; ?></textarea>
                          </p> 

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk);

                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$tekstInfoPotwierdzenie."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $tekst = $sqls->fetch_assoc();   
                          ?>

                          <label style="margin-left:10px;">Opis wyświetlany przed zatwierdzeniem zamówienia:</label>
                          <div class="edytor" style="margin:-33px 0px 10px 268px;">
                            <textarea cols="60" rows="30" id="edytor_<?php echo $w; ?>" name="TEKST_INFO_<?php echo $w; ?>"><?php echo $tekst['translate_value']; ?></textarea>
                          </div>   

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk);
                          ?>
                        </div>
                        <?php
                      }
                      ?>
                    </div>

                    <input type="hidden" name="ID_TLUMACZENIA" value="<?php echo $nazwa['translate_constant_id']; ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_OBJASNIENIA" value="<?php echo $objasn['translate_constant_id']; ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_TEKST_INFO" value="<?php echo $tekst['translate_constant_id']; ?>" />

                    <p>
                      <label class="required">Kolejność wyswietlania:</label>
                      <input type="text" name="SORT" size="5" value="<?php echo $info['sortowanie']; ?>" id="sort" class="bestupper toolTip" title="Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania." />
                    </p>

                    <p>
                      <label>Status:</label>
                      <input type="radio" value="1" name="STATUS" <?php echo (($info['status'] == '1') ? 'checked="checked"' : ''); ?> /> włączony
                      <input type="radio" value="0" name="STATUS" <?php echo (($info['status'] == '0') ? 'checked="checked"' : ''); ?> /> wyłączony
                    </p>

                    <?php

                    $zapytanie_parametry = "SELECT * FROM modules_payment_params WHERE modul_id = '" . (int)$filtr->process($_GET['id_poz']) . "' ORDER BY sortowanie";
                    $sql_parametry = $db->open_query($zapytanie_parametry);
                    
                    $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                
                    if ((int)$db->ile_rekordow($sql_parametry) > 0) {
                      while ( $info_parametry = $sql_parametry->fetch_assoc() ) {

                          if ( strpos($info_parametry['kod'], '_STATUS_ZAMOWIENIA' ) !== false ) {
                              $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- Domyślny ---');
                              echo '<p>';
                              echo '<label>'.$info_parametry['nazwa'].':</label>';
                              echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica, $info_parametry['wartosc'],' style="width: 350px;margin-left:3px;"');
                              echo '</p>';
                              unset($tablica);
                          } elseif ( strpos($info_parametry['kod'], '_GRUPA_KLIENTOW' ) !== false ) {
                            
                              echo '<div>';
                              echo '<table style="margin:10px"><tr>';
                          
                              echo '<td><label>'.$info_parametry['nazwa'].':</label></td>';
                              echo '<td>';
                              foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                  echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array($GrupaKlienta['id'], explode(';', $info_parametry['wartosc']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                              }               
                              echo '</td>';  

                              echo '</tr></table></div>';
                            
                          } elseif ( strpos($info_parametry['kod'], '_LOGO' ) !== false ) {
                              echo '<p>';
                              echo '<label>'.$info_parametry['nazwa'].':</label>';
                              echo '&nbsp;<input id="foto" type="text" size="65" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" ondblclick="openFileBrowser(\'foto\',\'\',\'images\')" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" class="toolTipTop" />';
                              echo '</p>';
                          } elseif ( strpos($info_parametry['kod'], 'PLATNOSC_PAYU_RATY_WLACZONE' ) !== false ) {
                              echo '<p>';
                              echo '<label>'.$info_parametry['nazwa'].':</label>';
                              ?>
                              <input type="radio" value="tak" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'tak') ? 'checked="checked"' : ''); ?> /> włączone
                              <input type="radio" value="nie" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == 'nie') ? 'checked="checked"' : ''); ?> /> wyłączone
                              <?php
                            echo '</p>';
                          } elseif ( strpos($info_parametry['kod'], 'PLATNOSC_TRANSFERUJ_ONLINE' ) !== false ) {
                              echo '<p>';
                              echo '<label>'.$info_parametry['nazwa'].':</label>';
                              ?>
                              <input type="radio" value="1" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                              <input type="radio" value="0" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                              <?php
                            echo '</p>';
                          } elseif ( strpos($info_parametry['kod'], '_KATEGORIE' ) !== false ) {
                              echo '<div style="padding-left:10px;padding-top:4px;">';
                              echo '<label>'.$info_parametry['nazwa'].':</label>';
                              echo '<div id="drzewoPlatnosci"><table class="pkc" cellpadding="0" cellspacing="0">';
                              //
                              $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                              for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                //
                                $check = '';
                                if ( in_array($tablica_kat[$w]['id'], explode(',', $info_parametry['wartosc'])) ) {
                                    $check = 'checked="checked"';
                                }
                                //  
                                echo '<tr>
                                        <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" '.$check.' /> '.$tablica_kat[$w]['text'].'</td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                              }
                              echo '</table></div>';
                              unset($tablica_kat,$podkategorie);

                              if ( $info_parametry['wartosc'] != '' ) {
                                // pobieranie id kategorii do jakich jest przypisany produkt
                                $przypisane_kategorie = $info_parametry['wartosc'];
                                $kate = explode(',', $przypisane_kategorie);

                                foreach ( $kate as $val ) {

                                      $sciezka = Kategorie::SciezkaKategoriiId($val, 'categories');
                                      $cSciezka = explode("_",$sciezka);                    
                                      if (count($cSciezka) > 1) {
                                          //
                                          $ostatnie = strRpos($sciezka,'_');
                                          $analiza_sciezki = str_replace("_",",",substr($sciezka,0,$ostatnie));
                                          ?>
                                          <script type="text/javascript">
                                          //<![CDATA[            
                                          podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo $przypisane_kategorie; ?>');
                                          //]]>
                                          </script>
                                      <?php
                                      unset($sciezka,$cSciezka);
                                      }

                                }

                                unset($przypisane_kategorie);  
                              }
                              echo '</div>';
                          } else {
                            if ( strpos($info_parametry['kod'], '_SANDBOX' ) !== false ) {
                              echo '<p>';
                              echo '<label>'.$info_parametry['nazwa'].':</label>';
                              ?>
                              <input type="radio" value="1" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                              <input type="radio" value="0" name="<?php echo 'PARAMETRY['.$info_parametry['kod'].']'; ?>" <?php echo (($info_parametry['wartosc'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                              <?php
                              echo '</p>';

                            } else {
                              echo '<p>';
                              echo '<label '.($info_parametry['sortowanie'] > 10 ? 'class="required"' : '' ).'>'.$info_parametry['nazwa'].':</label>';
                              echo '&nbsp;<input type="text" size="65" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" '.($info_parametry['sortowanie'] < 10 && $info_parametry['kod'] != 'PLATNOSC_KOSZT' ? 'class="kropka"' : 'class="required"' ).' />';
                              echo '</p>';
                            }
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
                          <input type="text" name="SKRYPT" id="skrypt" size="65" value="<?php echo $info['skrypt']; ?>" class="toolTipText" title="Nazwa skryptu realizującego funkcje modułu." onkeyup="updateKeySkrypt();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> />
                        </p>

                        <p>
                          <label class="required">Nazwa klasy:</label>   
                          <input type="text" name="KLASA" id="klasa" size="65" value="<?php echo $info['klasa']; ?>" class="toolTipText" title="Nazwa klasy realizującej funkcje modułu." onkeyup="updateKeyKlasa();" <?php echo ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ? '' : 'readonly="readonly"' ); ?> />
                        </p>
                    <?php } else { ?>
                      <input type="hidden" name="SKRYPT" value="<?php echo $info['skrypt']; ?>" />
                      <input type="hidden" name="KLASA" value="<?php echo $info['klasa']; ?>" />
                    <?php } ?>

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

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

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