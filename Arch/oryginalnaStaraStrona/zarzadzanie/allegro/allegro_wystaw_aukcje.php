<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  //wczytanie klasy do obslugi Allegro
  $allegro = new Allegro(true, true);
  $komunikat = '';


  if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    Funkcje::PrzekierowanieURL('allegro_wystaw_aukcje.php?id_poz'.$_POST['id'].'');
  }

  //jezeli nie ma ustawionego ID produktu przekierowanie na strone listy produktow
  if ( isset($_GET['id_poz']) && $_GET['id_poz'] == '0' ) {
    Funkcje::PrzekierowanieURL('../produkty/produkty.php');
  }

  // wczytanie naglowka HTML
  include('naglowek.inc.php');
  ?>
  <style type="text/css">
    .info_tab_content label { width:200px; padding-left:0px; }
    .info_tab_content label.error { display:block; margin-left: 170px;}
    .info_content label { width:200px; padding-left:0px; }
    .info_content label.error { display:block; margin-left: 0px; }
  </style>
  <?php

  if ( $komunikat != '' ) {
    echo $komunikat;
  }

  $id_produktu = $filtr->process($_GET['id_poz']);

  $zapytanie = 'SELECT DISTINCT
                p.products_id, 
                p.products_image,
                p.products_model,
                p.products_price_tax,
                p.products_quantity,
                p.products_price_tax,
                p.products_pkwiu,
                p.products_tax_class_id,
                p.products_currencies_id,
                pd.products_id, 
                pd.language_id, 
                pd.products_name,
                pd.products_description
                FROM products p, products_description pd
                WHERE pd.products_id = p.products_id AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '" AND p.products_id = "'.$id_produktu.'"'; 

  $sql = $db->open_query($zapytanie);

  $parametry = $allegro->TablicaDefinicjiPol( false );

  ?>
    
    <div id="naglowek_cont">Obsługa Allegro</div>
    <div id="cont">

      <?php 
      if ( count($parametry) > 0  ) {
        ?>

        <!-- Skrypt do walidacji formularza -->
        <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#allegroForm").validate({
             rules: {
              fid_5: {range: [1, 1000],number: true}
             }
            });

            ckedit('fid_24','970','300px');

            // wystawienie aukcji.	
            $('#form_submit').click(function(){

              for ( instance in CKEDITOR.instances )
                  CKEDITOR.instances[instance].updateElement();

              var frm = $("#allegroForm");
              var response_text = $('#wystawianie');
              var response_form = $('#wynik');
              var response_dalej = $('#kontynuuj');
              var dane = frm.serialize();
              var daneTbl = frm.serializeArray();
              var proceed = true;

              response_text.hide();
 
              if (proceed == true) {
                response_text.html('<img src="obrazki/_loader.gif">').show();

                $.post('ajax/allegro_wystaw_aukcje.php?tok=<?php echo Sesje::Token(); ?>', dane, function(data){
                  response_form.slideUp();
                  response_text.html(data);
                  response_dalej.slideDown();
                });
              }

              return false;
            });

            // test aukcji.	
            $('#form_test').click(function(){

              for ( instance in CKEDITOR.instances )
                  CKEDITOR.instances[instance].updateElement();

              var frm = $("#allegroForm");
              var response_text = $('#wystawianie');
              var response_form = $('#wynik');
              var dane = frm.serialize();
              var daneTbl = frm.serializeArray();
              var proceed = true;

              response_text.hide();
 
              if (proceed == true) {
              
                response_text.html('<img src="obrazki/_loader.gif">').show();

                $.post('ajax/allegro_sprawdz_aukcje.php?tok=<?php echo Sesje::Token(); ?>', dane, function(data){
                  response_form.slideUp();
                  response_text.html(data);
                });
              }

              return false;
            });

            // podglad aukcji
            $('#form_preview').click(function(){

              for ( instance in CKEDITOR.instances )
                  CKEDITOR.instances[instance].updateElement();

              var frm = $("#allegroForm");
              var response_text = $('#wystawianie');
              var response_form = $('#wynik');
              var dane = frm.serialize();
              var daneTbl = frm.serializeArray();
              var proceed = true;

              response_text.hide();
 
              if (proceed == true) {

                response_text.html('<img src="obrazki/_loader.gif">').show();

                $.post('ajax/allegro_podglad_aukcji.php?tok=<?php echo Sesje::Token(); ?>', dane, function(data){
                  response_form.slideUp();
                  response_text.hide();
                  $.colorbox( { html:data, maxWidth:"98%", maxHeight:"98%", open:true, initialWidth:50, initialHeight:50, speed: 200, overlayClose:false, escKey:true, onLoad: function() {
                    $("#cboxClose").show();
                  }});

                });
              }

              return false;
            });

          });

          function pokazKategorieSklepu(wartosc) {
              var id = $(wartosc).val();
              if (id == '1') {
                $('#kategorieSklep').slideDown();
              } else {
                $('#kategorieSklep').slideUp();
              }
          }

          function rozwin(typ) {
              var $research = $('.'+typ);
              $research.find(".accordion").siblings("tr").fadeToggle(200);
              $research.find(".zwin").toggleClass("rozwin");
          };

          $(function() {
              var $research = $('.koszt_wysylki_dodatkowe');
              $research.find("tr").not('.accordion').hide();
              $research.find(".zwin").toggleClass("rozwin");
          });
          //]]>
        </script>        
        
        <form action="ajax/allegro_wystaw_aukcje.php" method="post" id="allegroForm" class="cmxform"> 

          <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="produkt_id" value="<?php echo $id_produktu; ?>" />
              <input type="hidden" name="fid_9" value="<?php echo $allegro->polaczenie['CONF_COUNTRY']; ?>" />
          </div>
          
          <div class="poleForm">
            <div class="naglowek">Wystawianie aukcji</div>

            <div class="pozycja_edytowana">

              <!-- Wczytanie danych o polaczeniu z Allegro -->
              <?php require_once('allegro_naglowek.php');

              if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();
                
                $zdjecie_glowne =  $info['products_image'];
                $id_kategorii = 0;
                
                // dodatkowe parametry allegro dla produktu
                $zapytanie_tmp = "select * from products_allegro_info where products_id = '".(int)$_GET['id_poz']."'";
                $sqls = $db->open_query($zapytanie_tmp);
                
                if ((int)$db->ile_rekordow($sql) > 0) {
                
                    $dane_allegro = $sqls->fetch_assoc();
                    //
                    if ( strlen($dane_allegro['products_description_allegro']) > 10 ) {
                         $info['products_description'] = $dane_allegro['products_description_allegro'];
                    } 
                    if ( $dane_allegro['products_price_allegro'] > 0 ) {
                         $info['products_price_tax'] = $dane_allegro['products_price_allegro'];
                      } else {
                         $info['products_price_tax'] = $waluty->FormatujCeneBezSymbolu($info['products_price_tax'], true, '', '', '2', $info['products_currencies_id']);
                    }
                    if ( !empty($dane_allegro['products_name_allegro']) ) {
                         $info['products_name'] = $dane_allegro['products_name_allegro'];
                    }
                    if ( !empty($dane_allegro['products_image_allegro']) ) {
                         $zdjecie_glowne = $dane_allegro['products_image_allegro'];
                    } 
                    //
                    $id_kategorii = $dane_allegro['products_cat_id_allegro'];
                    
                }
                                             
                //  
                // echo ukryte id kategorii
                echo '<div id="ukryte_id">' . $id_kategorii . '</div>';  
                
                $db->close_query($sqls);
                unset($zapytanie_tmp, $dane_allegro, $id_kategorii);

                ?>
                <div class="info_content">

                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                      <!-- Dane ogolne -->
                      <table style="width:100%" class="listing_tbl">
                        <tr class="accordion">
                          <td colspan="2" style="padding-left:10px; text-align:left">Wystawiany produkt</td>
                        </tr>
                        <?php
                        //FID-1 Nazwa produktu
                        $fid = '1';
                        $default = mb_substr($info['products_name'],0,50);
                        echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                        echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                        echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                        echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, 'maxlength="50" onkeyup="licznik_znakow(this,\'iloscZnakowNazwa\',50)"', true);                        
                        echo '<br /><span style="display:inline-block;margin:4px;">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakowNazwa">' . (50-strlen(utf8_decode($default))) . '</span></span>';
                        echo '</td></tr>';

                        $ilosc_wystawiana = $info['products_quantity'];
                        $cechy_poczatkowe = '';
                        // wyswietlenie cech produktu START
                        $i = 0;
                        $zapytanie_cechy = "SELECT DISTINCT popt.products_options_id, popt.products_options_name 
                                            FROM products_options popt, products_attributes patrib where patrib.products_id='" . (int)$id_produktu . "' and patrib.options_id = popt.products_options_id and popt.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and patrib.options_values_id != '0' ORDER BY popt.products_options_sort_order asc
                        ";

                        $sql_cechy = $db->open_query($zapytanie_cechy);

                        if ((int)$db->ile_rekordow($sql_cechy) > 0) {

                          $dostepne_cechy = array();
                          while ( $info_cechy = $sql_cechy->fetch_assoc() ) {

                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">&nbsp;</div>';
                            echo '<label>'.$info_cechy['products_options_name'].':</label></td><td class="pozycjaAllegro" >';
                            $tablica = Funkcje::lista_wartosci_cechy_produktu_allegro($info['products_id'], $info_cechy['products_options_id']);
                            $cechy_poczatkowe .= $info_cechy['products_options_id'] .'-'.$tablica['0']['id'].',';
                            echo Funkcje::RozwijaneMenu('cecha['.$info_cechy['products_options_id'].']', $tablica, '', 'style="width:250px;" id="cecha_'.$info_cechy['products_options_id'].'" onchange="stan()" ');
                            echo '</td></tr>';
                            $dostepne_cechy[] = $info_cechy['products_options_id'];
                            $i++;
                          }

                          $cechy_poczatkowe = substr($cechy_poczatkowe,0,-1);

                          if (CECHY_MAGAZYN == 'tak') {
                            $cec = "select distinct * from products_stock where products_id = '".$id_produktu ."' and products_stock_attributes = '".$cechy_poczatkowe."'";
                            $sqlw = $db->open_query($cec);
                            if ((int)$db->ile_rekordow($sqlw) > 0) {
                              $ilosc_cechy = $sqlw->fetch_assoc(); 
                              $ilosc_wystawiana = $ilosc_cechy['products_stock_quantity'];
                            } else {
                              $ilosc_wystawiana = 0;
                            }
                            $db->close_query($sqlw);

                            print "<script type=\"text/javascript\"  language=\"javascript\">";
                            print "//<![CDATA[\n";
                            print "var zmienna          = new Array()\n";
                            print "var tablica_wartosci = new Array()\n";

                            foreach($dostepne_cechy as $tym) print "zmienna.push('$tym')\n";

                            print "function stan() {\n";
                            print "for ( x=0; x < zmienna.length; x++) {\n";
                            print "  tablica_wartosci.push(zmienna[x]+'-'+$('#cecha_'+zmienna[x]+'').val());\n";
                            print "}\n";

                            print "$.post( \"ajax/sprawdz_ilosc_produktu.php?tok=" . Sesje::Token() . "\",\n";
                            print "{\n";
                            print "cechy             :tablica_wartosci,\n";
                            print "id_produktu       :'$id_produktu',\n";
                            print "},\n";
                            print "function(data){\n";
                            print "if ( data < 1 ) {\n";
                            print "$('#stanMagazyn').slideDown();\n";
                            print "}\n";
                            print "if ( data > 0 ) {\n";
                            print "$('#stanMagazyn').slideUp();\n";
                            print "}\n";
                            print "$('#fid_5').val(data);\n";

                            print "});\n";
                            print "tablica_wartosci = [];\n";
                            print "};\n";
                            print "//]]>\n";
                            print "</script>";

                          }

                        }

                        // wyswietlenie cech produktu END
                        echo '<tr id="stanMagazyn" class="pozycja_offAllegro" '.( $ilosc_wystawiana == 0 ? '' : 'style="display:none;"' ) .'><td colspan="2"><div class="ostrzezenie">Ostrzeżenie - wybranego produktu nie ma obecnie w magazynie - mimo to możesz go wystawić</div></td></tr>';

                        //FID-2 Kategoria
                        $fid = '2';
                        $default = '';
                        echo '<tr class="pozycja_offAllegro" id="idKategorii"><td class="pozycjaAllegro" style="width:225px">';
                        echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                        echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro">';
                        echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                        echo '</td></tr>';

                        ?>
                      </table>
                      
                      <div id="catsPL">
                        <div id="treePL" class="treeBox"></div>
                      </div> 

                      <p id="drzewo_allegro_cont">
                        <span id="drzewo_allegro">pokaż drzewo kategorii Allegro</span>
                      </p>                       

                      <!-- Dane dla wybranej kategorii -->
                      <div id="dodatkowe_opcje_kategorii" style="display: none;"></div>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                      <!-- Opis produktu -->
                      <table style="width:100%" class="listing_tbl opis">
                        <tr class="accordion">
                          <td style="text-align:right" colspan="2">
                            <div class="zwin" onclick="rozwin('opis');"></div><div>Opis produktu</div>
                          </td>
                        </tr>
                        <?php
                        //FID-24 Opis produktu
                        $opcje_platnosci = array(24);
                        $default = $info['products_description'];

                        echo '<tr><td style="padding:5px;"><textarea id="fid_24" name="fid_24" cols="90" rows="10">'.$default.'</textarea></td></tr>';

                        ?>
                      </table>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">                            

                      <!-- Zdjecia produktu -->
                      <table style="width:100%" class="listing_tbl foto">
                        <tr class="accordion">
                          <td style="text-align:right" colspan="2">
                            <div class="zwin" onclick="rozwin('foto');"></div><div>Zdjęcia produktu</div>
                          </td>
                        </tr>
                        <?php
                        //FID-16 Zdjecie glowne produktu
                        $foto = array(16);

                        for ( $i=0, $c = count($foto); $i < $c; $i++ ) {
                        
                          $fid = $foto[$i];
                          echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                          echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                          echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                          echo '<table>';
                          echo '<tr><td style="background:#fff;">' . Funkcje::pokazObrazek($zdjecie_glowne, $info['products_name'], '50', '50') . '</td>';
                          echo '<td style="text-align:center; background:#f4f4f4;"><input type="checkbox" value="'.$zdjecie_glowne.'" name="fid_16" checked="checked" /> dodaj do aukcji zdjęcie produktu</td></tr>';
                          echo '</table>';
                          echo '</td></tr>';
                          
                        }

                        $zapytanie_zdjecia = "SELECT * FROM additional_images WHERE products_id = '".$id_produktu."' order by sort_order";
                        $sql_zdjecia = $db->open_query($zapytanie_zdjecia);
                        
                        $wynik_dodatkowe_zdjecia = '';
                        $wynik_zdjecia_galeria = '';
                        
                        if ((int)$db->ile_rekordow($sql_zdjecia) > 0 || $info['products_image'] != '') {

                          $i = '17';
                          $wynik_dodatkowe_zdjecia .= '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                          $wynik_dodatkowe_zdjecia .= '<div class="pomoc">&nbsp;</div>';
                          
                          $wynik_zdjecia_galeria = $wynik_dodatkowe_zdjecia;
                          
                          $wynik_dodatkowe_zdjecia .= '<label>Zdjęcia w treści aukcji: <span class="szare">wyświetlane za pomocą znacznika [ZDJECIA] w szablonie aukcji</span></label></td><td class="pozycjaAllegro">';
                          $wynik_dodatkowe_zdjecia .= '<table><tr>';
                          
                          // pierwsze zdjecie w dodatkowych zdjeciach - glowne zdjecie produktu
                          $wynik_dodatkowe_zdjecia.= '<td style="background:#f4f4f4; text-align:center;">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '50', '50') . '<br /><input type="checkbox" value="'.$info['products_image'].'" name="dodatkowe_foto[]" /></td>';                       
                          
                          $wynik_zdjecia_galeria .= '<label>Zdjęcia do galerii: <span class="szare">wyświetlane za pomocą znacznika [GALERIA] w szablonie aukcji</span></label></td><td class="pozycjaAllegro">';
                          $wynik_zdjecia_galeria .= '<table><tr>';                          
                          
                          // pierwsze zdjecie w galerii - glowne zdjecie produktu
                          $wynik_zdjecia_galeria .= '<td style="background:#f4f4f4; text-align:center;">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '50', '50') . '<br /><input type="checkbox" value="'.$info['products_image'].'" name="galeria_foto[]" checked="checked" /></td>';

                          $col = 1;
                          while ( $info_zdjecia = $sql_zdjecia->fetch_assoc()) {
                          
                            if ( is_file('../' . KATALOG_ZDJEC . '/' . $info_zdjecia['popup_images']) ) {
                              $wynik_dodatkowe_zdjecia .= '<td style="background:#f4f4f4; text-align:center;">' . Funkcje::pokazObrazek($info_zdjecia['popup_images'], $info['products_name'], '50', '50') . '<br /><input type="checkbox" value="'.$info_zdjecia['popup_images'].'" name="dodatkowe_foto[]" /></td>';
                              $wynik_zdjecia_galeria .= '<td style="background:#f4f4f4; text-align:center;">' . Funkcje::pokazObrazek($info_zdjecia['popup_images'], $info['products_name'], '50', '50') . '<br /><input type="checkbox" value="'.$info_zdjecia['popup_images'].'" name="galeria_foto[]" checked="checked" /></td>';
                              $i++;
                              $col++;
                            }
                            
                            if ( $col == 8 || $col == 16 || $col == 24 ) {
                                $wynik_dodatkowe_zdjecia .= '</tr><tr>';
                                $wynik_zdjecia_galeria .= '</tr><tr>';
                            }
                            
                          }

                          $wynik_dodatkowe_zdjecia .= '</tr></table>';
                          $wynik_dodatkowe_zdjecia .= '</td></tr>';
                          
                          $wynik_zdjecia_galeria .= '</tr></table>';
                          $wynik_zdjecia_galeria .= '</td></tr>';                          
                          
                        }
                        
                        echo $wynik_dodatkowe_zdjecia;
                        
                        if ( (int)$db->ile_rekordow($sql_zdjecia) > 0 && $info['products_image'] != '') {
                            echo $wynik_zdjecia_galeria;
                        }
                        
                        $db->close_query($sql_zdjecia);
                        unset($zapytanie_zdjecia, $wynik_dodatkowe_zdjecia, $wynik_zdjecia_galeria);
                        ?>
                      </table>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">                            

                      <!-- Dane ogolne -->
                      <table style="width:100%" class="listing_tbl">
                        <tr class="accordion">
                          <td colspan="2" style="text-align:left; padding-left:10px;">Dane dotyczące sprzedaży</td>
                        </tr>
                        <?php
                        //FID-29 Format sprzedazy
                        $opcje_ogolne = array(29);
                        for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                          $fid = $opcje_ogolne[$i];

                          if ( isset($parametry[$fid]) ) {
                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                            $default = '';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, 'onchange="pokazKategorieSklepu(this);"', true);
                            echo '</td></tr>';
                          }
                        }
                        //FID-31 Kategoria w sklepie Allegro
                        $fid = '31';
                        $default = '';
                        echo '<tr id="kategorieSklep" class="pozycja_offAllegro" style="display:none"><td class="pozycjaAllegro" style="width:225px">';
                        echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                        echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                        echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                        echo '</td></tr>';


                        //FID-3  Data rozpoczecia aukcji
                        $opcje_ogolne = array(3);
                        for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                          $fid = $opcje_ogolne[$i];

                          if ( isset($parametry[$fid]) ) {
                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';

                            echo '<input name="fid_3" type="text" value="" id="datetimepicker"/>';
                            echo '</td></tr>';
                          }
                        }


                        //FID-4  Czas trwania aukcji
                        //FID-5  Liczba sztuk
                        //FID-28 Sztuki/komplety/pary
                        $opcje_ogolne = array(4,5,28);
                        for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                          $fid = $opcje_ogolne[$i];
                          $default = '';

                          if ( isset($parametry[$fid]) ) {
                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                            if ( $fid == '5' ) {
                              $default = $ilosc_wystawiana;
                            } else {
                              if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];
                            }

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                            echo '</td></tr>';
                          }
                        }

                        //FID-6 Cena wywolawcza
                        //FID-7 Cena minimalna
                        //FID-8 Cena Kup Teraz
                        $opcje_ogolne = array(6,7,8);
                        for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                          $fid = $opcje_ogolne[$i];
                          $default = '';

                          if ( isset($parametry[$fid]) ) {
                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .str_replace('\\','',$parametry[$fid]['sell_form_title']).':</label></td><td class="pozycjaAllegro" >';
                            if ( $fid == '8' ) {
                              $default = $info['products_price_tax'];
                            } else {
                              if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];
                            }

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                            echo '</td></tr>';
                          }
                        }

                        //FID-9 Kraj
                        //FID-10 Wojewodztwo
                        //FID-11 Miejscowosc
                        //FID-32 Kod pocztowy
                        $opcje_ogolne = array(9,10,11,32);
                        for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                          $fid = $opcje_ogolne[$i];
                          $default = '';

                          if ( isset($parametry[$fid]) ) {

                            if ( $parametry[$fid]['sell_form_id'] == '9') {

                                $TablicaPanstw = $allegro->doGetCountries();
                                $parametry[$fid]['sell_form_type'] = '4';
                                $default = '';
                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                foreach ( $TablicaPanstw as $Panstwo ) {
                                    $P = (array)$Panstwo;
                                    $parametry[$fid]['sell_form_desc'] .= $P['country-name'] . '|';
                                    $parametry[$fid]['sell_form_opts_values'] .= $P['country-id'] . '|';
                                    unset($P);
                                }

                            }

                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                            echo '</td></tr>';
                          }
                        }
                        ?>
                      </table>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">                            

                      <!-- Dane dotyczace transportu -->
                      <table style="width:100%" class="listing_tbl transport">
                        <tr class="accordion">
                          <td colspan="2">
                            <div class="zwin" onclick="rozwin('transport');"></div><div>Dane dotyczące transportu</div>
                          </td>
                        </tr>
                        <?php
                        //FID-12 Koszt wysylki pokrywa
                        //FID-13 Dodatkowe informacje
                        //FID-35 Darmowa wysylka
                        //FID-340 Termin wysylki
                        $opcje_wysylki = array(12,13,35,340);

                        for ( $i=0, $c = count($opcje_wysylki); $i < $c; $i++ ) {
                          $fid = $opcje_wysylki[$i];
                          $default = '';

                          if ( isset($parametry[$fid]) ) {
                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';

                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];
                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                            echo '</td></tr>';
                          }
                        }
                        ?>
                      </table>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">                            

                      <!-- Dane dotyczace platnosci -->
                      <table style="width:100%" class="listing_tbl platnosc">
                        <tr class="accordion">
                          <td colspan="2">
                            <div class="zwin" onclick="rozwin('platnosc');"></div><div>Dane dotyczące płatności</div>
                          </td>
                        </tr>
                        <?php
                        //FID-14 Formy platnosci
                        //FID-33 Pierwsze konto bankowe
                        //FID-34 Drugie konto bankowe
                        //FID-27 Dodatkowe uwagi
                        $opcje_platnosci = array(14,33,34,27);
                        $default = '';

                        for ( $i=0, $c = count($opcje_platnosci); $i < $c; $i++ ) {
                          $fid = $opcje_platnosci[$i];

                          if ( isset($parametry[$fid]) ) {
                            if ( $parametry[$fid]['sell_form_id'] == '33' || $parametry[$fid]['sell_form_id'] == '34' ) {
                              $parametry[$fid]['sell_form_field_desc'] = 'Format 26 cyfr pisanych łącznie';
                            }
                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                            echo '</td></tr>';
                          }

                        }
                        ?>
                      </table>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">                            

                      <!-- Dodatkowe opcje aukcji -->
                      <table style="width:100%" class="listing_tbl opcje">
                        <tr class="accordion">
                          <td colspan="2">
                            <div class="zwin" onclick="rozwin('opcje');"></div><div>Dodatkowe opcje</div>
                          </td>
                        </tr>
                        <?php
                        //FID-15 Dodatkowe opcje wystawiania aukcji
                        $opcje_platnosci = array(15);
                        $default = '';

                        for ( $i=0, $c = count($opcje_platnosci); $i < $c; $i++ ) {
                          $fid = $opcje_platnosci[$i];

                          if ( isset($parametry[$fid]) ) {
                            if ( $parametry[$fid]['sell_form_id'] == '33' || $parametry[$fid]['sell_form_id'] == '34' ) {
                              $parametry[$fid]['sell_form_field_desc'] = 'Format 26 cyfr pisanych łącznie';
                            }
                            echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                            echo '<div class="pomoc">'.$allegro->FormatujOpisPola( $parametry[$fid]['sell_form_field_desc'] ).'</div>';
                            echo '<label '.($parametry[$fid]['sell_form_opt'] == '1' ? 'class="required"' : '' ) . '>' .$parametry[$fid]['sell_form_title'].':</label></td><td class="pozycjaAllegro" >';
                            if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                            echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                            echo '</td></tr>';
                          }

                        }
                        ?>
                      </table>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">                            

                      <!-- Koszty wysylki -->
                      <table style="width:100%" class="listing_tbl koszt_wysylki">
                        <tr class="accordion">
                          <td style="width:225px">
                            <div class="zwin" onclick="rozwin('koszt_wysylki');"></div><div>Koszty wysyłki</div>
                          </td>
                          <td style="width:135px; padding-left:5px;">Pierwsza sztuka</td>
                          <td style="width:135px; padding-left:5px;">Druga sztuka</td>
                          <td style="padding-left:5px;">Ilość w paczce</td>
                        </tr>
                        <?php

                          $koszt_dostawy = array(36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60);

                          for ( $i=0, $c = count($koszt_dostawy); $i < $c; $i++ ) {
                            $fid = $koszt_dostawy[$i];

                            if ( isset($parametry[$fid]) ) {
                              if ( $allegro->parametry[$fid] != '' ) {

                                echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                                echo str_replace(" (pierwsza sztuka)", '', $parametry[$fid]['sell_form_title']).':</td><td class="pozycjaAllegro" style="width:135px" >';

                                $default = '';
                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                                echo '</td>';

                                echo '<td class="pozycjaAllegro" style="width:135px" >';
                                $fid = $koszt_dostawy[$i]+100;

                                $default = '';
                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                                echo '</td>';

                                echo '<td class="pozycjaAllegro" >';
                                $fid = $koszt_dostawy[$i]+200;

                                $default = '';
                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                                echo '</td>';

                                echo '</tr>';

                              }
                            }
                          }
                        ?>
                      </table>
                      
                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">                            

                      <!-- Dodatkowe koszty wysylki -->
                      <table style="width:100%" class="listing_tbl koszt_wysylki_dodatkowe">
                        <tr class="accordion">
                          <td style="width:225px">
                            <div class="zwin" onclick="rozwin('koszt_wysylki_dodatkowe');"></div><div>Dodatkowe opcje wysyłki</div>
                          </td>
                          <td style="width:135px; padding-left:5px;">Pierwsza sztuka</td>
                          <td style="width:135px; padding-left:5px;">Druga sztuka</td>
                          <td style="padding-left:5px;">Ilość w paczce</td>
                        </tr>
                        <?php

                          $opcje_ogolne = array(36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60);

                          for ( $i=0, $c = count($opcje_ogolne); $i < $c; $i++ ) {
                            $fid = $opcje_ogolne[$i];

                            if ( isset($parametry[$fid]) ) {
                              if ( $allegro->parametry[$fid] == '' ) {

                                echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                                echo str_replace(" (pierwsza sztuka)", '', $parametry[$fid]['sell_form_title']).':</td><td class="pozycjaAllegro" style="width:135px" >';

                                $default = '';
                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                                echo '</td>';

                                echo '<td class="pozycjaAllegro" style="width:135px" >';
                                $fid = $opcje_ogolne[$i]+100;

                                $default = '';
                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                                echo '</td>';

                                echo '<td class="pozycjaAllegro" >';
                                $fid = $opcje_ogolne[$i]+200;

                                $default = '';
                                if (isset($allegro->parametry[$fid])) $default = $allegro->parametry[$fid];

                                echo $allegro->StworzPole($parametry[$fid]['sell_form_id'], $parametry[$fid]['sell_form_title'], $parametry[$fid]['sell_form_type'], $parametry[$fid]['sell_form_opts_values'], $parametry[$fid]['sell_form_desc'], $parametry[$fid]['sell_form_opt'], $default, '', true);
                                echo '</td>';

                                echo '</tr>';

                              }
                            }
                          }
                        ?>
                      </table>

                    </div>
                    
                    <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

                      <!-- Szablon aukcji -->
                      <table style="width:100%" class="listing_tbl szablon">
                        <tr class="accordion">
                          <td style="text-align:right" colspan="2">
                            <div class="zwin" onclick="rozwin('szablon');"></div><div>Szablon aukcji</div>
                          </td>
                        </tr>
                        <?php
                        echo '<tr class="pozycja_offAllegro"><td class="pozycjaAllegro" style="width:225px">';
                        echo '<div class="pomoc">'.$allegro->FormatujOpisPola('Wybierz szablon który ma być użyty do wystawienia aukcji').'</div>';
                        echo '<label>Szablon aukcji:</label></td><td class="pozycjaAllegro" >';
                        echo Allegro::SzablonyAllegro( $allegro->polaczenie['CONF_DEFAULT_TEMPLATE'] );
                        echo '</td></tr>';
                        ?>
                      </table>
                      
                    </div>                    
                          
                </div>

                <?php 
              }
              ?>

            </div>
            
            <script src="javascript/jquery.KategorieAllegro.js" type="text/javascript"></script>
            <script type="text/javascript" src="programy/datetimepicker/jquery.datetimepicker.js"></script>
            <link rel="stylesheet" type="text/css" href="programy/datetimepicker/jquery.datetimepicker.css" />

            <script type="text/javascript">

                var currentDate = new Date();
                var MinutesLater = new Date(currentDate.getTime() + (1 * 60 * 1000));

                $('#datetimepicker').datetimepicker({
                dayOfWeekStart : 1,
                lang:'pl'
                });
                $('#datetimepicker').datetimepicker({value:MinutesLater,minDate:0,minTime:0,format:'d-m-Y H:i',step:5});

            </script>

            <script type="text/javascript">
              //<![CDATA[
              $(document).ready( function() {
              
                function dodatkoweOpcje(file,lisc) {
                
                  $('#dodatkowe_opcje_kategorii').slideUp();
                  var response_form = $('#dodatkowe_opcje_kategorii');
                  var dane = file;
                  var dzialaj = lisc;

                  if (dzialaj == true) {
                    $.post('ajax/allegro_wczytaj_opcje_kategorii.php?tok=<?php echo Sesje::Token(); ?>', { kategoria: dane }, function(data) {
                      if ( data != '') {
                        response_form.html(data);
                        response_form.slideDown();
                      } else {
                        response_form.slideUp();
                        response_form.empty();
                      }
                    });
                  }
                  
                }              
              
                $('#drzewo_allegro').click( function() {
                
                    $('#drzewo_allegro_cont').slideUp();
                    $('#catsPL').slideDown();                
              
                    $('#treePL').fileTree({
                      root: '0',
                      script: 'ajax/drzewo_allegro.php',
                      expandSpeed: 500,
                      collapseSpeed: 500,
                      multiFolder: false
                    },
                    function(file,name,lisc) {
                      $('#fid_2').val(file);
                      if ( file != '' ) {
                          $('#drzewo_allegro_cont').slideDown();
                          $('#catsPL').slideUp();
                      }
                      dodatkoweOpcje(file,lisc);
                    });
                    
                });
                
                $('#idKategorii input').removeClass('kropkaPustaZero').addClass('calkowita');
                
                $('#idKategorii input').change(function() {
                    $(this).val( parseInt($(this).val()) );
                    //
                    if (isNaN($(this).val())) {
                        $(this).val( '' );
                      } else {
                        $(this).val( parseInt($(this).val()) );
                    }
                    dodatkoweOpcje($(this).val(),true);
                });
                
                if ( parseInt($('#ukryte_id').html()) > 0 ) {
                    $('#idKategorii input').val($('#ukryte_id').html());
                }
                dodatkoweOpcje($('#ukryte_id').html(),true);                                
                
              });
              //]]>
            </script>        

            <div class="info_content">
              <div id="wystawianie" style="display:none;"></div>
              <div id="wynik" style="padding-bottom:20px;display:none;"></div>
            </div>

            <?php 
            if ((int)$db->ile_rekordow($sql) > 0) {
              $pochodzenie = explode('/', $_SERVER['HTTP_REFERER']);?>
              <div class="przyciski_dolne">
                <div id="przycisk_wystaw" style="float:left"><input id="form_submit" type="submit" class="przyciskNon" value="Wystaw aukcje" /></div>
                <div id="przycisk_sprawdz" style="float:left"><input id="form_test" type="submit" class="przyciskNon" value="Sprawdź poprawność" /></div>
                <div id="przycisk_podglad" style="float:left"><input id="form_preview" type="submit" class="przyciskNon" value="Podgląd aukcji" /></div>
                <button type="button" class="przyciskNon" onclick="cofnij('produkty','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','produkty');">Powrót</button>
              </div>
              <?php
            } else {
              ?>
              <div class="przyciski_dolne">
                <button type="button" class="przyciskNon" onclick="cofnij('index','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','');">Powrót</button>
              </div>
              <?php
            }
            ?>
          </div>
        </form>

        <?php
      }
      ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}

?>