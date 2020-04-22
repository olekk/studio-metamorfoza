<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $lista_kategorii = '';
      if ( $_POST['typ'] == '2' ) {
        $lista_kategorii = implode(',', $_POST['id_kat'] );
      }

      $lista_pol = '';
      if ( $_POST['format'] == '1' ) {
        if ( count($_POST['id_extra_field']) > 0 ) {
          foreach ( $_POST['id_extra_field'] as $val ) {
            if ( $_POST['desc_extra_field'][$val] != '' ) {
              $lista_pol .= $val . ':' . $filtr->process($_POST['desc_extra_field'][$val]) . ',';
            }
          }

        }
        $lista_pol = substr($lista_pol, 0, -1);
      }

      $pola = array(
              array('comparisons_availability',$filtr->process($_POST['dostepnosc'])),
              array('comparisons_conditions',$filtr->process($_POST['stan'])),
              array('comparisons_export_type',$filtr->process($_POST['typ'])),
              array('comparisons_export_quantity',$filtr->process($_POST['stan_magazynu'])),
              array('comparisons_categories',$lista_kategorii),
              array('comparisons_extra_fields',$lista_pol),
              array('comparisons_name_info',(int)$_POST['dodatkowa_nazwa'])
      );

      $sql = $db->update_query('comparisons', $pola, " comparisons_id = '".(int)$_POST["id"]."'");	
      unset($pola);

      Funkcje::PrzekierowanieURL('porownywarki.php?id_poz='.(int)$_POST["id"]);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów eksportu dla porównywarek</div>
    <div id="cont">

      <form action="porownywarki/porownywarki_edytuj.php" method="post" id="porownywarkiForm" class="cmxform">
        <div class="poleForm">

            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            } 

            $zapytanie = "SELECT * FROM comparisons WHERE comparisons_id = '".(int)$_GET['id_poz']."'";
            $sql = $db->open_query($zapytanie);
            
            if ( $db->ile_rekordow($sql) > 0 )  {
                
                $info = $sql->fetch_assoc();

                // fragment do mapowania kategorii dla Google Shopping
                if ( $info['comparisons_plugin'] == 'googleshopping' ) {

                    if ( $info['comparisons_conditions'] == '0' ) {
                        $info['comparisons_conditions'] = '1';
                    }

                    // adres pliku kaktegorii w serwisie Google
                    $url  = 'http://www.google.com/basepages/producttype/taxonomy.pl-PL.txt';
                    // lokalny plik kategorii
                    $path = 'cache/taxonomy.pl-PL.txt';

                    // pobiera date modyfikacji pliku z kategoriami
                    if (file_exists($path)) {
                        $dataPliku = filemtime($path);
                    }
                    // jezeli plik jest starszy niz 1 dzien, to pobiera nowy
                    if ( time() - $dataPliku > 3600 *24 ) { 

                        $czyJestPlikZdalny= Funkcje::remoteFileExists($url);

                        if ($czyJestPlikZdalny) {
                            $fp = fopen($path, 'w');
                             
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_FILE, $fp);
                             
                            $data = curl_exec($ch);
                             
                            curl_close($ch);
                            fclose($fp);
                        }
                    }

                    //Zapytanie sql wyciągające categorie i tworzace tablice
                    $zapytanie_kategorie = "
                          SELECT cd.categories_name, c.categories_id, c.parent_id, ga.categories_google 
                            FROM categories AS c
                            LEFT JOIN categories_description AS cd ON cd.categories_id=c.categories_id
                            LEFT JOIN google_categories ga ON ga.categories_id = c.categories_id
                            WHERE cd.language_id= '1'
                            ORDER BY c.sort_order";

                    $sql_kategorie = $db->open_query($zapytanie_kategorie);

                    while ($info_kategorie = $sql_kategorie->fetch_assoc()) {
                        $tablica_kategorii[$info_kategorie['categories_id']] = array ('id' => $info_kategorie['categories_id'], 'nadrzedna' => $info_kategorie['parent_id'], 'nazwa_kategorii' => $info_kategorie['categories_name'], 'kategoria_google' => $info_kategorie['categories_google']);
                    }

                    $db->close_query($sql_kategorie);
                    unset($zapytanie_kategorie, $info_kategorie);    

                    //przeksztalcenie tablicy kategorii na tablice z pokategoriami
                    $tree = Kategorie::kategorieNaDrzewo($tablica_kategorii);
                    unset($tablica_kategorii);    
                }

                $tablicaDostepnosci = array();
                $tablicaDostepnosci = Porownywarki::TablicaDostepnosciNiezdefiniowanych($info['comparisons_plugin']);
                if ( $info['comparisons_plugin'] == 'starcode' ) {
                    $tablicaDostepnosci = Porownywarki::TablicaDostepnosciNiezdefiniowanych('nokaut');
                }
                ?>
                
                <div class="naglowek">Edycja danych <?php echo $info['comparisons_name']; ?></div>

                <div class="pozycja_edytowana">
                
                  <div class="info_content">

                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

                  <?php
                  if ( count($tablicaDostepnosci) > 0 ) {
                      ?>
                      <p>
                        <label>Domyślna dostępność:</label>
                        <?php
                        echo Funkcje::RozwijaneMenu('dostepnosc', $tablicaDostepnosci, $info['comparisons_availability'], 'style="width:300px;" class="toolTipText" title="Dostępność produktu - jeżeli nie została zdefiniowana bezpośrednio dla towaru. Musi być zgodna ze specyfikacją porównywarki."');
                        unset($tablicaDostepnosci);
                        ?>
                      </p>
                      <?php
                  } else {
                    echo '<input type="hidden" name="dostepnosc" value="1" />';
                  }
                  ?>

                  <p>
                    <label>Domyślny stan produktu:</label>
                    <input type="radio" value="1" name="stan" <?php echo (($info['comparisons_conditions'] == '1') ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Produkt jest nowy i fabrycznie zapakowany" /> nowy
                    <input type="radio" value="2" name="stan" <?php echo (($info['comparisons_conditions'] == '2') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Produkt był używany, nie ma fabrycznego opakowania,itp." /> używany
                    <input type="radio" value="3" name="stan" <?php echo (($info['comparisons_conditions'] == '3') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Produkt był używany ale jest po regeneracji, np. cartridże do drukarek" /> odnowiony
                  </p>

                  <p>
                    <label>Czy eksportować tylko produkty ze stanem więszym od 0:</label>
                    <input type="radio" value="0" name="stan_magazynu" <?php echo ($info['comparisons_export_quantity'] == '0' ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Eksportowane będą prdukty niezależnie od ilości w magazynie" /> nie
                    <input type="radio" value="1" name="stan_magazynu" <?php echo ($info['comparisons_export_quantity'] == '1' ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Eksportowane będą tylko produkty, których stan magazynowy jest większy od 0" /> tak
                  </p>                   

                  <p>
                    <label>Czy przy eksporcie dodawać <b>Dodatkową nazwę</b> produktu:</label>
                    <input type="radio" value="0" name="dodatkowa_nazwa" <?php echo ($info['comparisons_name_info'] == '0' ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Dodatkowa nazwa produktu nie będzie dodawana" /> nie
                    <input type="radio" value="1" name="dodatkowa_nazwa" <?php echo ($info['comparisons_name_info'] == '1' ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Do nazw produktów będzie dodawana Dodatkowa nazwa produktu zdefiniowana podczas edycji produktu" /> tak
                  </p>                   

                  <p>
                    <label>Typ eksportu:</label>
                    <input type="radio" value="0" name="typ" onclick="$('#drzewo').slideUp()" <?php echo (($info['comparisons_export_type'] == '0') ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Typ eksportu - wszystkie produkty - eksport wszystkich produktów z bazy danych" /> wszystkie produkty
                    <input type="radio" value="1" name="typ" onclick="$('#drzewo').slideUp()" <?php echo (($info['comparisons_export_type'] == '1') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Typ eksportu - tylko zaznaczone produkty - eksport produktów z zaznaczoną opcją Do porownywarek" /> tylko zaznaczone produkty                     
                    <input type="radio" value="2" name="typ" onclick="$('#drzewo').slideDown()" <?php echo (($info['comparisons_export_type'] == '2') ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Typ eksportu - tylko zaznaczone kategorie - eksport produktów z wybranych kategorii" /> tylko zaznaczone kategorie
                  </p> 

                  <div id="drzewo" <?php echo ( $info['comparisons_export_type'] != '2' ? 'style="display:none;margin:10px;width:950px;"' : 'style="margin:10px;width:950px;"' ); ?> >
                    <p>Kategorie eksportowane do porównywarki</p>                           

                    <?php
                    $przypisane_kategorie = explode(',', $info['comparisons_categories']);
                    //
                    if ( count($przypisane_kategorie) > 10 ) {
                        //
                        echo '<ul id="drzewoKategorii">';
                        foreach(Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica) {
                            //
                            echo Kategorie::WyswietlDrzewoKategoriiCheckbox($IdKategorii, $Tablica, $przypisane_kategorie);
                            //
                        }    
                        echo '</ul>';
                        //
                    } else {
                        //
                        echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                        //
                        $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                          $podkategorie = false;
                          if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                          //
                          $check = '';
                          if ( in_array($tablica_kat[$w]['id'], $przypisane_kategorie) ) {
                              $check = 'checked="checked"';
                          }
                          //  
                          echo '<tr>
                                  <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" '.$check.' /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                                  <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                </tr>
                                '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                        }
                        echo '</table>';
                        unset($tablica_kat,$podkategorie);

                        if ( count($przypisane_kategorie) > 0 ) {

                          foreach ( $przypisane_kategorie as $val ) {
                              
                                  $sciezka = Kategorie::SciezkaKategoriiId($val, 'categories');
                                  $cSciezka = explode("_",$sciezka);                    
                                  if (count($cSciezka) > 1) {
                                      //
                                      $ostatnie = strRpos($sciezka,'_');
                                      $analiza_sciezki = str_replace("_",",",substr($sciezka,0,$ostatnie));
                                      ?>
                                      <script type="text/javascript">
                                      //<![CDATA[            
                                      podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo $info['comparisons_categories']; ?>');
                                      //]]>
                                      </script>
                                  <?php
                                  unset($sciezka,$cSciezka);
                                  }
                            
                          } 
                              
                          unset($przypisane_kategorie);  
                        }
                    }
                    unset($KategorieRabaty);
                    ?>
                  </div>

                  <p>
                    <label>Format pliku:</label>
                    <input type="radio" value="0" name="format" onclick="$('#pola').slideUp()" <?php echo ($info['comparisons_extra_fields'] == '' ? 'checked="checked"' : ''); ?>  class="toolTipTop" title="Format eksportowanego pliku - standardowy plik porównywarki" /> standardowy
                    <input type="radio" value="1" name="format" onclick="$('#pola').slideDown()" <?php echo ($info['comparisons_extra_fields'] != '' ? 'checked="checked"' : ''); ?> class="toolTipTop" title="Format eksportowanego pliku - uwzględnia dodatkowe pola do produktów" /> własny 
                  </p> 

                  <div id="pola" <?php echo ( $info['comparisons_extra_fields'] == '' ? 'style="display:none;margin:10px;width:950px;"' : 'style="margin:10px;width:950px;"' ); ?> >
                    <p>Dodatkowe pola eksportowane do porównywarki</p>                           

                    <?php
                    $zapytanie_pola = "SELECT * FROM products_extra_fields ORDER BY products_extra_fields_order";
                    $sql_pola = $db->open_query($zapytanie_pola);
                    if ( $db->ile_rekordow($sql_pola) > 0 )  {

                        $tablica_pol = array();
                        if ( $info['comparisons_extra_fields'] != '' ) {
                          $dodatkowe_pola_tablica = explode(',', $info['comparisons_extra_fields']);
                          for ( $i = 0, $c = count($dodatkowe_pola_tablica); $i < $c; $i++ ) {
                            $podtablica = explode(':', $dodatkowe_pola_tablica[$i]);
                            $tablica_pol[$podtablica['0']] = $podtablica['1'];
                          }
                        }
                        echo '<table class="pkc" cellpadding="0" cellspacing="0" style="width:98%">';
                        while ( $info_pola = $sql_pola->fetch_assoc() ) {
                          $check = '';
                          $wartosc = '';
                          if ( isset($tablica_pol[$info_pola['products_extra_fields_id']]) ) {
                            $check = 'checked="checked"';
                            $wartosc = $tablica_pol[$info_pola['products_extra_fields_id']];
                          }
                          echo '<tr>
                                  <td style="width:20px;text-align:center"><input type="checkbox" value="'.$info_pola['products_extra_fields_id'].'" name="id_extra_field['.$info_pola['products_extra_fields_id'].']" '.$check.' /></td><td>'.$info_pola['products_extra_fields_name'].'</td><td><input type="text" name="desc_extra_field['.$info_pola['products_extra_fields_id'].']"  value="'.$wartosc.'" size="50" class="toolTipText" title="Nazwa pola w generowanym pliku XML - powinna być zgodna z dokumentacją integracji danej porównywarki.<br />W razie wątpliwości należy się zwrócić do obsługi serwisu porównywarki." /></td>
                                </tr>';
                        }
                        echo '</table>';
                    } else {
                      echo '<div class="ostrzezenie" style="margin-left:15px;">Nie ma zdefiniowanych dodatkowych pól do produktów - nie można dodać własnych pól do pliku XML</div>';
                    }
                    $db->close_query($sql_pola);
                    unset($zapytanie_pola, $info_pola);

                    ?>
                  </div>
                  
                  <?php
                  // mapowanie kategorii Google Shopping
                  if ( $info['comparisons_plugin'] == 'googleshopping' ) {
                      ?>
                      <script type="text/javascript">
                        //<![CDATA[
                        function mapuj_kategorie(id_kategorii) {
                            $(function() {
                                var id = id_kategorii;
                                $.colorbox({
                                    title: "Przypisanie kategorii Google zakupy do kategorii sklepu",
                                    ajax:true,
                                    data: true,
                                    scrolling: true,
                                    width:'1200',
                                    height:'80%',
                                    overlayClose: false,
                                    initialWidth:50,
                                    initialHeight:50,
                                    href:"ajax/google_mapowanie_kategorii.php?id="+id,
                                });

                            });
                        }
                        function mapuj_kategorie_usun(id) {
                            var id    = id;
                            $('#ekr_preloader').css('display','block');
                            $.post('ajax/google_zapisanie_kategorii.php', { akcja: "usun", id: id }, 
                                function(data) {}
                            );
                            $("#wartosc_" + id).html('');
                            $('#ekr_preloader').fadeOut();
                            $("#usun_" + id).hide();
                        }

                        //]]>
                      </script>

                      <div style="padding:3px 10px 4px;">

                        <div class="poleForm">

                            <div class="naglowek">
                                Kategorie sklepu -> Google shopping <br />
                                <div class="ostrzezenie" style="margin:10px 5px 5px 0px">Żeby sklep wyeksportował dane do porównywarki MUSZĄ być powiązane kategorie sklepu z kategoriami porównywarki</div>
                            </div>

                            <div id="Mapowanie">
                                <?php
                                echo '<table style="width:100%">';
                                echo Porownywarki::TablicaNaWierszeGoogle($tree);
                                echo '</table>';
                                ?>
                            </div>

                        </div>
                      </div>

                      <?php
                  }
                  ?>

                  </div>

                </div>
                
                <div class="przyciski_dolne">
                    <input type="submit" class="przyciskNon" value="Zapisz dane" />
                    <button type="button" class="przyciskNon" onclick="cofnij('porownywarki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','porownywarki');">Powrót</button> 
                </div>            
            
            <?php 
            
            $db->close_query($sql);
            unset($zapytanie, $info);
                    
            } else {
            
                echo '<div class="naglowek">Edycja danych</div><div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>
        </div>
      </form>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} 


?>
