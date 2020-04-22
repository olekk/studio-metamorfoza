<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // aktualizacja zapisu w tablicy modulow
        $pola = array(
                array('nazwa',$filtr->process($_POST["NAZWA"])),
                array('skrypt',$filtr->process($_POST["SKRYPT"])),
                array('klasa',$filtr->process($_POST["KLASA"])),
                array('sortowanie',$filtr->process($_POST["SORT"])),
                array('status',$filtr->process($_POST["STATUS"])));
                
        //
        $db->insert_query('modules_shipping' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);


        // aktualizacja tlumaczen
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".$id_dodanej_pozycji."_TYTUL'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.$id_dodanej_pozycji.'_TYTUL'),
            array('section_id', '4'));
            
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
                        array('language_id',$ile_jezykow[$w]['id']));
                        
            } else {
            
                $pola = array(
                        array('translate_value',$filtr->process($_POST['NAZWA_0'])),
                        array('translate_constant_id',$id_dodanego_wyrazenia),
                        array('language_id',$ile_jezykow[$w]['id']));
            }
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);

        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".$id_dodanej_pozycji."_OBJASNIENIE'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.$id_dodanej_pozycji.'_OBJASNIENIE'),
            array('section_id', '4'));
            
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
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);


        // ##############
        $db->delete_query('translate_constant', "translate_constant='WYSYLKA_".$id_dodanej_pozycji."_INFORMACJA'");
        $pola = array(
            array('translate_constant','WYSYLKA_'.$id_dodanej_pozycji.'_INFORMACJA'),
            array('section_id', '4'));
            
        $db->insert_query('translate_constant' , $pola);
        $id_dodanego_wyrazenia = $db->last_id_query();
        unset($pola);

        //
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('translate_value',$filtr->process($_POST['INFORMACJA_'.$w])),
                    array('translate_constant_id',$id_dodanego_wyrazenia),
                    array('language_id',$ile_jezykow[$w]['id']));
                    
            $db->insert_query('translate_value' , $pola);
            unset($pola);
        }
        unset($id_dodanego_wyrazenia);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Przesyłka gabarytowa'),
                array('kod','WYSYLKA_GABARYT'),
                array('sortowanie','1'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_GABARYT'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Stawka VAT'),
                array('kod','WYSYLKA_STAWKA_VAT'),
                array('sortowanie','3'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_STAWKA_VAT'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','PKWIU'),
                array('kod','WYSYLKA_PKWIU'),
                array('sortowanie','4'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_PKWIU'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Maksymalna waga przesyłki'),
                array('kod','WYSYLKA_MAKSYMALNA_WAGA'),
                array('sortowanie','5'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MAKSYMALNA_WAGA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Maksymalna wartość zamówienia'),
                array('kod','WYSYLKA_MAKSYMALNA_WARTOSC'),
                array('sortowanie','6'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_MAKSYMALNA_WARTOSC'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Darmowa wysyłka od kwoty'),
                array('kod','WYSYLKA_DARMOWA_WYSYLKA'),
                array('sortowanie','7'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_DARMOWA_WYSYLKA'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Rodzaj opłaty'),
                array('kod','WYSYLKA_RODZAJ_OPLATY'),
                array('sortowanie','10'),
                array('wartosc',$filtr->process($_POST['PARAMETRY']['WYSYLKA_RODZAJ_OPLATY'])));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        switch ($_POST['PARAMETRY']['WYSYLKA_RODZAJ_OPLATY']) {
          case '1':
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',$filtr->process($_POST['parametry_stale_przedzial']['0']).':'.$filtr->process($_POST['parametry_stale_wartosc']['0'])));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
          case '2':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_waga_przedzial'], $_POST['parametry_waga_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',implode(";", $koszt_wysylki)));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
          case '3':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_cena_przedzial'], $_POST['parametry_cena_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',implode(";", $koszt_wysylki)));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
          case '4':
            $koszt_wysylki = array_map("Moduly::PolaczWartosciTablic", $_POST['parametry_sztuki_przedzial'], $_POST['parametry_sztuki_wartosc']);
            foreach (array_keys($koszt_wysylki, '0:0', true) as $key) {
                unset($koszt_wysylki[$key]);
            }
            $pola = array(
                    array('modul_id',$id_dodanej_pozycji),
                    array('nazwa','Koszt wysyłki'),
                    array('kod','WYSYLKA_KOSZT_WYSYLKI'),
                    array('sortowanie','12'),
                    array('wartosc',implode(";", $koszt_wysylki)));
                    
            $db->insert_query('modules_shipping_params' , $pola);
            unset($pola);
            break;
        }

        $grupy_klientow = implode(";", $_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW']);
        $grupy_klientow = str_replace("\r\n",", ", $grupy_klientow);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Dostępna dla grup klientów'),
                array('kod','WYSYLKA_GRUPA_KLIENTOW'),
                array('sortowanie','14'),
                array('wartosc',$grupy_klientow));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);
        
        $grupy_klientow = implode(";", $_POST['PARAMETRY']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE']);
        $grupy_klientow = str_replace("\r\n",", ", $grupy_klientow);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Niedostępna dla grup klientów'),
                array('kod','WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'),
                array('sortowanie','14'),
                array('wartosc',$grupy_klientow));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);        

        $kraje_dostawy = implode(";", $_POST['PARAMETRY']['WYSYLKA_KRAJE_DOSTAWY']);
        $kraje_dostawy = str_replace("\r\n",", ", $kraje_dostawy);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Kraje dostawy'),
                array('kod','WYSYLKA_KRAJE_DOSTAWY'),
                array('sortowanie','20'),
                array('wartosc',$kraje_dostawy));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        $dostepne_platnosci = implode(";", $_POST['PARAMETRY']['WYSYLKA_DOSTEPNE_PLATNOSCI']);
        $dostepne_platnosci = str_replace("\r\n",", ", $dostepne_platnosci);
        $pola = array(
                array('modul_id',$id_dodanej_pozycji),
                array('nazwa','Dostępne płatności'),
                array('kod','WYSYLKA_DOSTEPNE_PLATNOSCI'),
                array('sortowanie','15'),
                array('wartosc',$dostepne_platnosci));
                
        $db->insert_query('modules_shipping_params' , $pola);
        unset($pola);

        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('wysylka.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('wysylka.php');
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

          <form action="moduly/wysylka_dodaj.php" method="post" id="modulyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                  <p>
                    <label class="required">Nazwa modułu:</label>
                    <input type="text" name="NAZWA" size="73" value="" id="nazwa" class="toolTipText" title="Robocza nazwa widoczna w panelu administracyjnym sklepu" />
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

                      for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                            
                        // pobieranie danych jezykowych
                        ?>     
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                               <?php if ($w == '0') { ?>
                                <label class="required">Treść wyświetlana w sklepie:</label>
                                <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>" id="nazwa_0"></textarea>
                               <?php } else { ?>
                                <label>Treść wyświetlana w sklepie:</label>
                                <textarea cols="80" rows="3" name="NAZWA_<?php echo $w; ?>"></textarea>
                               <?php } ?>
                            </p> 
                                        
                            <p>
                              <label>Treść objaśnienia w sklepie:</label>
                              <textarea cols="80" rows="3" name="OBJASNIENIE_<?php echo $w; ?>"></textarea>
                            </p> 

                            <p>
                                <label>Dodatkowa informacja wyświetlana w potwierdzeniu zamówienia:</label>
                                <textarea cols="80" rows="3" name="INFORMACJA_<?php echo $w; ?>"></textarea>
                            </p> 

                        </div>
                        <?php                    
                         }                    
                         ?>
                  </div>

                  <p>
                    <label class="required">Kolejność wyswietlania:</label>
                    <input type="text" name="SORT" size="5" value="" id="sort" class="calkowita" />
                    <div class="maleInfo" style="margin:0px 5px 0px 282px">Kolejność wyswietlania określa jednocześnie w jakiej kolejności dany moduł będzie liczony do podsumowania.</div>
                  </p>

                  <p>
                    <label>Status:</label>
                    <input type="radio" value="1" name="STATUS" checked="checked" /> włączony
                    <input type="radio" value="0" name="STATUS" class="toolTipTop" /> wyłączony
                  </p>

                  <?php
                  echo '<p>';
                  echo '<label>Przesyłka gabarytowa:</label>';

                  echo '<input type="radio" value="1" name="PARAMETRY[WYSYLKA_GABARYT]" class="toolTipTop" title="Czy wysyłka ma być dostępna tylko dla produktów gabarytowych" /> tak';
                  echo '<input type="radio" value="0" name="PARAMETRY[WYSYLKA_GABARYT]" checked="checked" class="toolTipTop" title="Czy wysyłka ma być dostępna tylko dla produktów gabarytowych" /> nie';
                  echo '</p>';
                  
                  echo '<p>';
                  echo '<label>Podatek VAT:</label>';
                  
                  $vat = Produkty::TablicaStawekVat('', true, true);
                  $domyslny_vat = $vat[1];

                  echo Funkcje::RozwijaneMenu('PARAMETRY[WYSYLKA_STAWKA_VAT]', $vat[0], $domyslny_vat);                          
                  echo '</p>';
                  
                  unset($vat, $domyslny_vat);                  

                  echo '<p>';
                  echo '<label>PKWIU:</label>';
                  echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_PKWIU]" value="" id="WYSYLKA_PKWIU" />';
                  echo '</p>';

                  echo '<p>';
                  echo '<label>Maksymalna waga przesyłki:</label>';
                  echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_MAKSYMALNA_WAGA]" value="" id="WYSYLKA_MAKSYMALNA_WAGA" class="kropka" />';
                  echo '</p>';

                  echo '<p>';
                  echo '<label>Maksymalna wartość zamówienia:</label>';
                  echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_MAKSYMALNA_WARTOSC]" value="" id="WYSYLKA_MAKSYMALNA_WARTOSC" class="kropka" />';
                  echo '</p>';

                  echo '<p>';
                  echo '<label>Darmowa wysyłka od kwoty:</label>';
                  echo '<input type="text" size="35" name="PARAMETRY[WYSYLKA_DARMOWA_WYSYLKA]" value="" id="WYSYLKA_DARMOWA_WYSYLKA" class="kropka" />';
                  echo '</p>';

                  echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';

                  $tablica_oplat[] = array('id' => 1, 'text' => 'Opłata stała');
                  $tablica_oplat[] = array('id' => 2, 'text' => 'Opłata zależna od wagi zamówienia');
                  $tablica_oplat[] = array('id' => 3, 'text' => 'Opłata zależna od wartości zamówienia');
                  $tablica_oplat[] = array('id' => 4, 'text' => 'Opłata zależna od ilości produktów');

                  echo '<p>';
                  echo '<label>Rodzaj opłaty:</label>';
                  echo Funkcje::RozwijaneMenu('PARAMETRY[WYSYLKA_RODZAJ_OPLATY]', $tablica_oplat, '', ' id="WYSYLKA_RODZAJ_OPLATY" onclick="zmien_pola()" style="width:350px"');
                  echo '</p>';

                  echo '<div>';
                  echo '<label style="margin-left:10px;margin-top:5px;">Koszt wysyłki (brutto):</label>';
                  echo '</div>';

                  // koszty stale
                  echo '<div id="kosztyStale" style="margin-top:-25px;">';
                  echo '<div style="margin-left:280px;padding-bottom:3px;" id="stale1"><input type="hidden" name="parametry_stale_przedzial[]" value="999999" />';
                  echo '<input class="kropka" type="text" name="parametry_stale_wartosc[]" value="0" /></div>';
                  echo '</div>';

                  // koszty zalezne od wagi zamowienia
                  echo '<div id="kosztyWaga" style="display:none; margin-top:-25px;">';
                    echo '<div style="margin-left:280px;padding-bottom:3px;" id="waga1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_waga_przedzial[]" value="0" /> kg &nbsp; ';
                    echo '<input class="kropka" type="text" name="parametry_waga_wartosc[]" value="0" /> ' . $domyslna_waluta['symbol'] . '</div>';
                    echo '<div style="padding-left:280px;padding-top:5px;">';
                    echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyWaga\',\'waga\', \'kg\', \'do\', \'' . $domyslna_waluta['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyWaga\',\'waga\')" style="cursor:pointer;">usuń pozycję</span>';
                    echo '</div>';
                  echo '</div>';

                  // koszty zalezne od wartosci zamowienia
                  echo '<div id="kosztyCena" style="display:none; margin-top:-25px;">';
                    echo '<div style="margin-left:280px;padding-bottom:3px;" id="cena1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_cena_przedzial[]" value="0" /> ' . $domyslna_waluta['symbol'] . ' &nbsp; ';
                    echo '<input class="kropka" type="text" name="parametry_cena_wartosc[]" value="0" /> ' . $domyslna_waluta['symbol'] . '</div>';
                    echo '<div style="padding-left:280px;padding-top:5px;">';
                    echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztyCena\',\'cena\', \'' . $domyslna_waluta['symbol'] . '\', \'do\', \'' . $domyslna_waluta['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztyCena\',\'cena\')" style="cursor:pointer;">usuń pozycję</span>';
                    echo '</div>';
                  echo '</div>';

                  // koszty zalezne od ilosci sztuk produktow
                  echo '<div id="kosztySztuki" style="display:none; margin-top:-25px;">';
                    echo '<div style="margin-left:280px;padding-bottom:3px;" id="sztuki1">do &nbsp; <input class="kropka" type="text" size="10" name="parametry_sztuki_przedzial[]" value="0" /> szt. &nbsp; ';
                    echo '<input class="kropka" type="text" name="parametry_sztuki_wartosc[]" value="0" /> ' . $domyslna_waluta['symbol'] . '</div>';
                    echo '<div style="padding-left:280px;padding-top:5px;">';
                    echo '<span class="dodaj" onclick="dodaj_pozycje(\'kosztySztuki\',\'sztuki\', \'szt.\', \'do\', \'' . $domyslna_waluta['symbol'] . '\')" style="cursor:pointer">dodaj pozycję</span>&nbsp;&nbsp;<span class="usun" onclick="usun_pozycje(\'kosztySztuki\',\'sztuki\')" style="cursor:pointer;">usuń pozycję</span>';
                    echo '</div>';
                  echo '</div>';
                  
                  echo '<div class="maleInfo" style="margin:15px 5px 5px 280px">Koszty wysyłek należy podawać w kwotach brutto.</div>';

                  echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';

                  echo '<div>';
                  echo '<table style="margin:10px"><tr>';
                  
                  $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);

                  echo '<td><label>Dostępna dla grup klientów:</label></td>';                  
                  echo '<td>';
                  foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                      echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" /> ' . $GrupaKlienta['text'] . '<br />';
                  }               
                  echo '</td>';
                  
                  echo '</tr></table>';
                  echo '</div>';
                  
                  echo '<div class="maleInfo" style="margin:5px 5px 5px 280px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>';
                  
                  echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';                  
                  
                  echo '<div>';
                  echo '<table style="margin:10px"><tr>';                  

                  echo '<td><label>Niedostępna dla grup klientów:</label></td>';
                  echo '<td>';
                  foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                      echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="PARAMETRY['.$info_parametry['kod'].'][]" /> ' . $GrupaKlienta['text'] . '<br />';
                  }               
                  echo '</td>';

                  unset($TablicaGrupKlientow);                  

                  echo '</tr></table>';
                  echo '</div>';
                  
                  echo '<div class="maleInfo" style="margin:5px 5px 5px 280px">Jeżeli nie zostanie wybrana żadna grupa klientów to moduł będzie aktywny dla wszystkich klientów.</div>';
                  
                  echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';
                  echo '<p>';
                  echo '<label>Dostępne płatności:</label>';

                  $wszystkie_platnosci_tmp = Array();
                  $wszystkie_platnosci_tmp = Moduly::TablicaPlatnosciId();

                  echo '<select name="PARAMETRY[WYSYLKA_DOSTEPNE_PLATNOSCI][]" multiple="multiple" id="multipleHeaders1">';
                  foreach ( $wszystkie_platnosci_tmp as $value ) {
                    echo '<option value="'.$value['id'].'" >'.$value['text'].'</option>';
                  }
                  echo '</select>';
                  echo '</p>';

                  echo '<div class="ostrzezenie" style="margin:5px 5px 5px 280px">Do wysyłki musi być przypisana minimum jedna forma płatności.</div>';

                  $zapytanie_kraje = "SELECT DISTINCT c.countries_iso_code_2, cd.countries_name  
                                      FROM countries c
                                      LEFT JOIN countries_description cd ON c.countries_id = cd. countries_id AND cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'
                                      ORDER BY cd.countries_name";
                  $sqlc = $db->open_query($zapytanie_kraje);
                  //

                  echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';
                  echo '<p>';
                  echo '<label>Kraje dostawy:</label>';
                  echo '<select name="PARAMETRY[WYSYLKA_KRAJE_DOSTAWY][]" multiple="multiple" id="multipleHeaders">';

                  while ($infc = $sqlc->fetch_assoc()) { 
                    echo '<option value="'.$infc['countries_iso_code_2'].'" >'.$infc['countries_name'].'</option>';
                  }
                  echo '</select>';
                  echo '</p>';

                  echo '<div class="ostrzezenie" style="margin:5px 5px 5px 280px">Do wysyłki musi być przypisany minimum jednen kraj.</div>';

                  $db->close_query($sqlc);
                  unset($zapytanie_kraje, $infc);  

                  if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { 
                  
                      ?>
                        
                      <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                      <p>
                       <label class="required">Skrypt:</label>   
                       <input type="text" name="SKRYPT" id="skrypt" size="53" value="" class="toolTipText" title="Nazwa skryptu realizującego funkcje modułu." onkeyup="updateKeySkrypt();" />
                      </p>

                      <p>
                        <label class="required">Nazwa klasy:</label>   
                        <input type="text" name="KLASA" id="klasa" size="53" value="" class="toolTipText" title="Nazwa klasy realizującej funkcje modułu." onkeyup="updateKeyKlasa();" />
                      </p>
                      
                  <?php } else { ?>
                  
                      <input type="hidden" name="SKRYPT" value="wysylka_standard.php" />
                      <input type="hidden" name="KLASA" value="wysylka_standard" />
                      
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

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}