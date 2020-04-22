<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if (!isset($_POST['PARAMETRY']['WYSYLKA_DOSTEPNE_PLATNOSCI']))
          $_POST['PARAMETRY']['WYSYLKA_DOSTEPNE_PLATNOSCI'] = array();
        if (!isset($_POST['PARAMETRY']['WYSYLKA_KRAJE_DOSTAWY']))
          $_POST['PARAMETRY']['WYSYLKA_KRAJE_DOSTAWY'] = array();
        if (!isset($_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW']))
          $_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW'] = array();
        if (!isset($_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE']))
          $_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'] = array();          
        //
        // aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["NAZWA"])),
                array('sortowanie',$filtr->process($_POST["SORT"])),
                array('status',$filtr->process($_POST["STATUS"])),
                array('integracja',$filtr->process($_POST["INTEGRACJA"])));
                
        //
        $pola[] = array('skrypt',$filtr->process($_POST["SKRYPT"]));
        $pola[] = array('klasa',$filtr->process($_POST["KLASA"]));

        $db->update_query('modules_shipping' , $pola, " id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        // aktualizacja nazwy modulu w tablicy orders
        //$pola = array(
        //        array('shipping_module',$filtr->process($_POST["NAZWA"])),
        //);
        //$db->update_query('orders' , $pola, " shipping_module = '".$_POST["STARA_NAZWA"]."'");	
        //unset($pola);

        // aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".(int)$_POST["id"]."_TYTUL'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.(int)$_POST["id"].'_TYTUL'),
            array('section_id', '4'));
            
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
                        array('language_id',$ile_jezykow[$w]['id']));
                        
            } else {
            
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_0'])),
                        array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id']));
                        
            }
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        
        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".(int)$_POST["id"]."_OBJASNIENIE'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.(int)$_POST["id"].'_OBJASNIENIE'),
            array('section_id', '4'));
            
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
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        

        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".(int)$_POST["id"]."_INFORMACJA'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.(int)$_POST["id"].'_INFORMACJA'),
            array('section_id', '4'));
            
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        $db->delete_query('translate_value', "translate_constant_id = '".(int)$_POST["ID_TLUMACZENIA_INFORMACJI"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['INFORMACJA_'.$w])),
                    array('translate_constant_id',(int)$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $sql = $db->insert_query('translate_value' , $pola);
            unset($pola);
        }        

        while (list($key, $value) = each($_POST['PARAMETRY'])) {

          if (is_array($value)) $value = implode(";", $value);$value = str_replace("\r\n",", ", $value);
          if ( $key != 'WYSYLKA_GABARYT') {
              $pola = array(
                      array('wartosc',($value != '0' ? $filtr->process($value) : '')));
                      
          } else {
              $pola = array(
                      array('wartosc',$filtr->process($value)));
                      
          }
          $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = '".$key."'");	
          unset($pola);
        }

        switch ($_POST['PARAMETRY']['WYSYLKA_RODZAJ_OPLATY']) {
          case '1':
            $pola = array(
                    array('wartosc',$filtr->process($_POST['parametry_stale_przedzial']['0']).':'.$filtr->process($_POST['parametry_stale_wartosc']['0'])));
                    
            $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
            unset($pola);
            break;
          case '2':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_waga_przedzial'], $_POST['parametry_waga_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('wartosc',implode(";", $koszt_wysylki)));
                    
            $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
            unset($pola);
            break;
          case '3':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_cena_przedzial'], $_POST['parametry_cena_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('wartosc',implode(";", $koszt_wysylki)));
                    
            $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
            unset($pola);
            break;
          case '4':
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_sztuki_przedzial'], $_POST['parametry_sztuki_wartosc']);
            $pola = array(
                    array('wartosc',implode(";", $koszt_wysylki)));
                    
            $db->update_query('modules_shipping_params' , $pola, " modul_id = '".(int)$_POST["id"]."' AND kod = 'WYSYLKA_KOSZT_WYSYLKI'");	
            unset($pola);
            break;
        }

        //
        Funkcje::PrzekierowanieURL('wysylka.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
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

          <form action="moduly/wysylka_edytuj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }

            $zapytanie = "SELECT * FROM modules_shipping WHERE id = '" . (int)$filtr->process($_GET['id_poz']) . "'";
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
                      <input type="text" name="NAZWA" size="73" value="<?php echo $info['nazwa']; ?>" id="nazwa" class="toolTipText" title="Robocza nazwa widoczna w panelu administracyjnym sklepu" />
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
                      $tlumaczenie = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_TYTUL';
                      $objasnienie = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_OBJASNIENIE';
                      $informacja  = 'WYSYLKA_' . (int)$_GET['id_poz'] . '_INFORMACJA';

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
                          unset($zapytanie_jezyk, $sqls);

                          // pobieranie danych jezykowych
                          $zapytanie_jezyk = "SELECT DISTINCT * FROM translate_constant w LEFT JOIN translate_value t ON w.translate_constant_id = t.translate_constant_id  WHERE translate_constant = '".$informacja."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                          $sqls = $db->open_query($zapytanie_jezyk);
                          $inform = $sqls->fetch_assoc();   
                          ?>

                          <p>
                            <label>Dodatkowa informacja wyświetlana w potwierdzeniu zamówienia:</label>
                            <textarea cols="80" rows="3" name="INFORMACJA_<?php echo $w; ?>"><?php echo $inform['translate_value']; ?></textarea>
                          </p> 

                          <?php
                          $db->close_query($sqls);
                          unset($zapytanie_jezyk, $sqls);
                          ?>
                        </div>
                        <?php
                      }
                      ?>
                      
                    </div>

                    <input type="hidden" name="ID_TLUMACZENIA" value="<?php echo $nazwa['translate_constant_id']; ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_OBJASNIENIA" value="<?php echo $objasn['translate_constant_id']; ?>" />
                    <input type="hidden" name="ID_TLUMACZENIA_INFORMACJI" value="<?php echo $inform['translate_constant_id']; ?>" />

                    <p>
                      <label class="required">Kolejność wyswietlania:</label>
                      <input type="text" name="SORT" size="5" value="<?php echo $info['sortowanie']; ?>" id="sort" class="calkowita" />
                      <div class="maleInfo" style="margin:0px 5px 0px 282px">Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania.</div>
                    </p>

                    <p>
                      <label>Status:</label>
                      <input type="radio" value="1" name="STATUS" <?php echo (($info['status'] == '1') ? 'checked="checked"' : ''); ?> /> włączony
                      <input type="radio" value="0" name="STATUS" <?php echo (($info['status'] == '0') ? 'checked="checked"' : ''); ?> /> wyłączony
                    </p>

                    <?php

                    $zapytanie_parametry = "SELECT * FROM modules_shipping_params WHERE modul_id = '" . (int)$filtr->process($_GET['id_poz']) . "' ORDER BY sortowanie";
                    $sql_parametry = $db->open_query($zapytanie_parametry);
                                
                    if ((int)$db->ile_rekordow($sql_parametry) > 0) {
                    
                      $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                    
                      while ( $info_parametry = $sql_parametry->fetch_assoc() ) {

                        if ( $info_parametry['kod'] == 'WYSYLKA_GABARYT' ) {

                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';

                          echo '&nbsp;<input type="radio" value="1" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '1') ? 'checked="checked"' : '').' class="toolTipTop" title="Czy wysyłka ma być dostępna tylko dla produktów gabarytowych" /> tak';
                          echo '<input type="radio" value="0" name="PARAMETRY['.$info_parametry['kod'].']" '.(($info_parametry['wartosc'] == '0') ? 'checked="checked"' : '').'class="toolTipTop" title="Czy wysyłka ma być dostępna tylko dla produktów gabarytowych" /> nie';
                          echo '</p>';
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_STAWKA_VAT' ) {
                        
                          echo '<p>';
                          echo '<label>Podatek VAT:</label>';
                          
                          $vat = Produkty::TablicaStawekVat('', true, true);
                          $domyslny_vat = $vat[1];
                          $podzial_vat = explode('|', $info_parametry['wartosc']);
                          
                          if (count($podzial_vat) == 2) {
                              //
                              $domyslny_vat = $info_parametry['wartosc'];
                              //
                          }            

                          echo Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $vat[0], $domyslny_vat);                          
                          echo '</p>';
                          
                          unset($vat, $domyslny_vat, $podzial_vat);

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_RODZAJ_OPLATY' ) {

                            if ( $info['klasa'] != 'wysylka_indywidualna' ) {
                                  $rodzaj_oplaty = $info_parametry['wartosc'];

                                  $tablica_oplat[] = array('id' => 1, 'text' => 'Opłata stała');
                                  $tablica_oplat[] = array('id' => 2, 'text' => 'Opłata zależna od wagi zamówienia');
                                  $tablica_oplat[] = array('id' => 3, 'text' => 'Opłata zależna od wartości zamówienia');
                                  $tablica_oplat[] = array('id' => 4, 'text' => 'Opłata zależna od ilości produktów');

                                  echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';
                                  echo '<p>';
                                  echo '<label>'.$info_parametry['nazwa'].':</label>';
                                  echo '&nbsp;'.Funkcje::RozwijaneMenu('PARAMETRY['.$info_parametry['kod'].']', $tablica_oplat, $info_parametry['wartosc'], ' id="'.$info_parametry['kod'].'" onclick="zmien_pola()" style="width:350px;margin-left:-3px;"');
                                  echo '</p>';
                            }

                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_GRUPA_KLIENTOW' ) {

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';

                          //
                          echo '<div>';
                          echo '<table style="margin:10px"><tr>';

                          echo '<td><label>'.$info_parametry['nazwa'].':</label></td>';
                          echo '<td>';
                          foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                              echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array($GrupaKlienta['id'], explode(';', $info_parametry['wartosc']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                          }               
                          echo '</td>';

                          echo '</tr></table>';
                          echo '</div>';
                          
                          echo '<div class="maleInfo" style="margin:5px 5px 5px 280px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>';
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE' ) {

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';

                          //
                          echo '<div>';
                          echo '<table style="margin:10px"><tr>';

                          echo '<td><label>'.$info_parametry['nazwa'].':</label></td>';
                          echo '<td>';
                          foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                              echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" ' . ((in_array($GrupaKlienta['id'], explode(';', $info_parametry['wartosc']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                          }               
                          echo '</td>';

                          echo '</tr></table>';
                          echo '</div>';
                          
                          echo '<div class="maleInfo" style="margin:5px 5px 5px 280px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>';
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_KRAJE_DOSTAWY' ) {

                          $tablica_krajow = explode(';',$info_parametry['wartosc']);

                          $zapytanie_kraje = "SELECT DISTINCT c.countries_iso_code_2, cd.countries_name  
                                              FROM countries c
                                              LEFT JOIN countries_description cd ON c.countries_id = cd. countries_id AND cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'
                                              ORDER BY cd.countries_name";
                          $sqlc = $db->open_query($zapytanie_kraje);
                          //

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';
                          echo '<select name="PARAMETRY['.$info_parametry['kod'].'][]" multiple="multiple" id="multipleHeaders">';

                          while ($infc = $sqlc->fetch_assoc()) { 
                            $wybrany = '';
                            if ( in_array($infc['countries_iso_code_2'], $tablica_krajow ) ) {
                              $wybrany = 'selected="selected"';
                            }
                            echo '<option value="'.$infc['countries_iso_code_2'].'" '.$wybrany.'>'.$infc['countries_name'].'</option>';
                          }
                          echo '</select>';
                          echo '</p>';
                          
                          echo '<div class="ostrzezenie" style="margin:5px 5px 5px 280px">Do wysyłki musi być przypisany minimum jednen kraj.</div>';
                          
                          $db->close_query($sqlc);
                          unset($zapytanie_kraje, $infc);
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_DOSTEPNE_PLATNOSCI' ) {

                          $tablica_platnosci = explode(';',$info_parametry['wartosc']);

                          echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';

                          $wszystkie_platnosci_tmp = Array();
                          $wszystkie_platnosci_tmp = Moduly::TablicaPlatnosciId();

                          echo '<select name="PARAMETRY['.$info_parametry['kod'].'][]" multiple="multiple" id="multipleHeaders1">';
                          foreach ( $wszystkie_platnosci_tmp as $value ) {
                            $wybrany = '';
                            if ( in_array($value['id'], $tablica_platnosci ) ) {
                              $wybrany = 'selected="selected"';
                            }
                            echo '<option value="'.$value['id'].'" '.$wybrany.' >'.$value['text'].'</option>';
                          }
                          echo '</select>';
                          echo '</p>';
                          
                          echo '<div class="ostrzezenie" style="margin:5px 5px 5px 280px">Do wysyłki musi być przypisana minimum jedna forma płatności.</div>';
                          
                        } elseif ( $info_parametry['kod'] == 'WYSYLKA_KOSZT_WYSYLKI' ) {

                            if ( $info['klasa'] != 'wysylka_indywidualna' ) {
                                  $tablica_kosztow = explode(';',$info_parametry['wartosc']);
                                  
                                  echo '<div>';
                                  echo '<label style="margin-left:10px;margin-top:5px;">'.$info_parametry['nazwa'].' (brutto):</label>';
                                  echo '</div>';

                                  // koszty stale
                                  echo '<div id="kosztyStale" '.($rodzaj_oplaty != '1' ? 'style="display:none; margin-top:-25px;"' : 'style="margin-top:-25px;"').'>';
                                  if ( $rodzaj_oplaty == '1' ) {
                                    $koszt = explode(':', $tablica_kosztow['0']);
                                    echo '<div style="margin-left:280px;padding-bottom:3px;" id="stale1"><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />';
                                    echo '<input class="kropka" type="text" name="parametry_stale_wartosc[]" value="'.$koszt['1'].'" /></div>';
                                  } else {
                                    echo '<div style="margin-left:280px;padding-bottom:3px;" id="stale1"><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />';
                                    echo '<input class="kropka" type="text" name="parametry_stale_wartosc[]" value="0" /></div>';
                                  }
                                  echo '</div>';

                                  // koszty zalezne od wagi zamowienia
                                  echo '<div id="kosztyWaga" '.($rodzaj_oplaty != '2' ? 'style="display:none; margin-top:-25px;"' : 'style="margin-top:-25px;"').'>';
                                  if ( $rodzaj_oplaty == '2' ) {
                                    for ( $i = 0, $c = count($tablica_kosztow); $i < $c; $i++ ) {
                                      $idDiv = $i+1;
                                      $koszt = explode(':', $tablica_kosztow[$i]);
                                      echo '<div style="margin-left:280px;padding-bottom:3px;" id="waga'.$idDiv.'">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_waga_przedzial[]" value="'.number_format($koszt['0'],2,'.','').'" /> kg &nbsp; ';
                                      echo '<input class="kropka" type="text" name="parametry_waga_wartosc[]" value="'.number_format($koszt['1'],2,'.','').'" /> ' . $domyslna_waluta['symbol'] . '</div>';
                                    }
                                  } else {
                                      echo '<div style="margin-left:280px;padding-bottom:3px;" id="waga1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_waga_przedzial[]" value="0" /> kg &nbsp; ';
                                      echo '<input class="kropka" type="text" name="parametry_waga_wartosc[]" value="0" /> ' . $domyslna_waluta['symbol'] . '</div>';
                                  }
                                  echo '<div style="padding-left:280px;padding-top:5px;">';
                                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyWaga\',\'waga\', \'kg\', \'do\', \'' . $domyslna_waluta['symbol'] . '\' )" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyWaga\',\'waga\')" style="cursor:pointer; '.(count($tablica_kosztow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>';
                                  echo '</div>';

                                  echo '</div>';

                                  // koszty zalezne od wartosci zamowienia
                                  echo '<div id="kosztyCena" '.($rodzaj_oplaty != '3' ? 'style="display:none; margin-top:-25px;"' : 'style="margin-top:-25px;"').'>';
                                  if ( $rodzaj_oplaty == '3' ) {
                                    for ( $i = 0, $c = count($tablica_kosztow); $i < $c; $i++ ) {
                                      $idDiv = $i+1;
                                      $koszt = explode(':', $tablica_kosztow[$i]);
                                      echo '<div style="margin-left:280px;padding-bottom:3px;" id="cena'.$idDiv.'">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_cena_przedzial[]" value="'.$koszt['0'].'" /> ' . $domyslna_waluta['symbol'] . ' &nbsp; ';
                                      echo '<input class="kropka" type="text" name="parametry_cena_wartosc[]" value="'.number_format($koszt['1'],2,'.','').'" /> ' . $domyslna_waluta['symbol'] . '</div>';
                                    }
                                  } else {
                                      echo '<div style="margin-left:280px;padding-bottom:3px;" id="cena1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_cena_przedzial[]" value="0" /> ' . $domyslna_waluta['symbol'] . ' &nbsp; ';
                                      echo '<input class="kropka" type="text" name="parametry_cena_wartosc[]" value="0" /> ' . $domyslna_waluta['symbol'] . '</div>';
                                  }
                                  echo '<div style="padding-left:280px;padding-top:5px;">';
                                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyCena\',\'cena\', \'' . $domyslna_waluta['symbol'] . '\', \'do\', \'' . $domyslna_waluta['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyCena\',\'cena\')" style="cursor:pointer; '.(count($tablica_kosztow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>';
                                  echo '</div>';

                                  echo '</div>';

                                  // koszty zalezne od ilosci sztuk produktow
                                  echo '<div id="kosztySztuki" '.($rodzaj_oplaty != '4' ? 'style="display:none; margin-top:-25px;"' : 'style="margin-top:-25px;"').'>';
                                  if ( $rodzaj_oplaty == '4' ) {
                                    for ( $i = 0, $c = count($tablica_kosztow); $i < $c; $i++ ) {
                                      $idDiv = $i+1;
                                      $koszt = explode(':', $tablica_kosztow[$i]);
                                      echo '<div style="margin-left:280px;padding-bottom:3px;" id="sztuki'.$idDiv.'">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_sztuki_przedzial[]" value="'.$koszt['0'].'" /> szt. &nbsp; ';
                                      echo '<input class="kropka" type="text" name="parametry_sztuki_wartosc[]" value="'.number_format($koszt['1'],2,'.','').'" /> ' . $domyslna_waluta['symbol'] . '</div>';
                                    }
                                  } else {
                                      echo '<div style="margin-left:280px;padding-bottom:3px;" id="sztuki1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_sztuki_przedzial[]" value="0" /> szt. &nbsp; ';
                                      echo '<input class="kropka" type="text" name="parametry_sztuki_wartosc[]" value="0" /> ' . $domyslna_waluta['symbol'] . '</div>';
                                  }
                                  echo '<div style="padding-left:280px;padding-top:5px;">';
                                  echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztySztuki\',\'sztuki\', \'szt.\', \'do\', \'' . $domyslna_waluta['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztySztuki\',\'sztuki\')" style="cursor:pointer; '.(count($tablica_kosztow) > 1 ? '' : 'display:none;').'">usuń pozycję</span>';
                                  echo '</div>';
                                  echo '</div>';
                                  
                                  echo '<div class="maleInfo" style="margin:15px 5px 5px 280px">Koszty wysyłek należy podawać w kwotach brutto.</div>';
                                  
                            } else {
                            
                                  echo '<div><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />';
                                  echo '<input type="hidden" name="parametry_stale_wartosc[]" value="0" /></div>';
                                  
                            }
                            
                        } elseif ( strpos($info_parametry['kod'], 'WYSYLKA_ODBIOR_OSOBISTY_PUNKT' ) !== false ) {
                        
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';
                          echo '&nbsp;<textarea cols="80" rows="3" name="PARAMETRY['.$info_parametry['kod'].']">'.$info_parametry['wartosc'].'</textarea>';
                          echo '</p>';
                          
                        } else {
                        
                          echo '<p>';
                          echo '<label>'.$info_parametry['nazwa'].':</label>';
                          echo '<input type="text" size="'.($info_parametry['kod'] == 'WYSYLKA_SLEDZENIE_URL' ? '80' : '35').'" name="PARAMETRY['.$info_parametry['kod'].']" value="'.$info_parametry['wartosc'].'" id="'.$info_parametry['kod'].'" '.($info_parametry['kod'] == 'WYSYLKA_SLEDZENIE_URL' ? 'class="toolTipText" title="Adres url pod którym klient będzie mógł śledzić lokalizację przesyłki, po uzupełnieniu numeru przesyłki w edycji zamówienia. "' : 'class="kropka"').' />';
                          echo '</p>';
                          
                        }
                       
                      }
                      
                      unset($TablicaGrupKlientow);

                    }

                    $db->close_query($sql_parametry);
                    unset($zapytanie_parametry, $info_parametry);

                    if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { 
                    
                        ?>
                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                        <p>
                          <label class="required">Skrypt:</label>   
                          <input type="text" name="SKRYPT" id="skrypt" size="53" value="<?php echo $info['skrypt']; ?>" class="toolTipText" title="Nazwa skryptu realizującego funkcje modułu." onkeyup="updateKeySkrypt();" />
                        </p>

                        <p>
                          <label class="required">Nazwa klasy:</label>   
                          <input type="text" name="KLASA" id="klasa" size="53" value="<?php echo $info['klasa']; ?>" class="toolTipText" title="Nazwa klasy realizującej funkcje modułu." onkeyup="updateKeyKlasa();" />
                        </p>

                      <?php } else { ?>
                      
                        <input type="hidden" name="SKRYPT" value="<?php echo $info['skrypt']; ?>" />
                        <input type="hidden" name="KLASA" value="<?php echo $info['klasa']; ?>" />
                        
                    <?php } ?>
                    
                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0');
                    //]]>
                    </script>  
                      
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('wysylka','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','moduly');">Powrót</button>           
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