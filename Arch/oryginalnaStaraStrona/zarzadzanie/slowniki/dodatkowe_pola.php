<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " where products_extra_fields_name LIKE '%".$szukana_wartosc."%'";
    }    

    $zapytanie = "select * from products_extra_fields " . $warunki_szukania;
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'products_extra_fields_order, products_extra_fields_name';
                break;
            case "sort_a2":
                $sortowanie = 'products_extra_fields_name asc, products_extra_fields_order';
                break; 
            case "sort_a3":
                $sortowanie = 'products_extra_fields_name desc, products_extra_fields_order';
                break;                  
            case "sort_a4":
                $sortowanie = 'languages_id';
                break;
            case "sort_a5":
                $sortowanie = 'products_extra_fields_filter desc';
                break; 
            case "sort_a6":
                $sortowanie = 'products_extra_fields_location';
                break;                 
        }            
    } else { $sortowanie = 'products_extra_fields_order, products_extra_fields_name'; }    
    
    $zapytanie .= " order by ".$sortowanie;        

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa pola'),                                      
                                      array('Dostępne dla języka','center'),
                                      array('Wyświetlane jako obrazek','center'),
                                      array('Sort','center'),
                                      array('Filtry', 'center'),
                                      array('Wyszukiwanie','center'),
                                      array('Położenie', 'center'),
                                      array('Widoczny na karcie produktu','center'),
                                      array('Allegro','center'),
                                      array('Status','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_extra_fields_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_extra_fields_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_extra_fields_id'].'">';
                  }    

                  $tablica = array(array($info['products_extra_fields_id'] . '<input type="hidden" name="id[]" value="'.$info['products_extra_fields_id'].'" />','center'),
                                   array($info['products_extra_fields_name'])); 

                  $jaki_jezyk = 'wszystkie dostępne';
                  $jezyki = Funkcje::TablicaJezykow();
                  for ($w = 0, $c = count($jezyki); $w < $c; $w++) {
                       if ($jezyki[$w]['id'] == $info['languages_id']) {
                           $jaki_jezyk = $jezyki[$w]['text'];
                       }
                  }
                  $tablica[] = array($jaki_jezyk,'center');
                  
                  // czy jako obrazek
                  if ($info['products_extra_fields_image'] == '1') { $obraz = '<img src="obrazki/image_cechy.png" alt="Dodatkowe pole w formie obrazka" title="Dodatkowe pole w formie obrazka" />'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');  

                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['products_extra_fields_id'].'" value="'.$info['products_extra_fields_order'].'" class="sort_prod" />','center');  

                  // do filtrow
                  if ($info['products_extra_fields_filter'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest wyświetlane w filtrach w listingu produktów'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest wyświetlane w filtrach w listingu produktów'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" />','center');                    
                  
                  // do wyszukiwania
                  if ($info['products_extra_fields_search'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest wyświetlane w wyszukiwaniu zaawansowanym'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest wyświetlane w wyszukiwaniu zaawansowanym'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" />','center');                    

                  // polozenie
                  if ($info['products_extra_fields_view'] == '1') {
                      switch ($info['products_extra_fields_location']) {
                          case "foto":                  
                              $polozenie = 'Obok zdjęcia';
                              break;
                          case "opis":                  
                              $polozenie = 'Pod opisem produktu';
                              break;
                          default:
                              $polozenie = 'Pod opisem produktu';
                              break;
                      }
                    } else {
                      $polozenie = '-';
                  }
                  $tablica[] = array($polozenie, 'center');                   
                  unset($polozenie);
                  
                  // widoczny na karcie produktu czy nie
                  if ($info['products_extra_fields_view'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest widoczne na karcie produktu'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest widoczne na karcie produktu'; }               
                  $tablica[] = array('<a href="slowniki/dodatkowe_pola_widocznosc.php?id_poz='.$info['products_extra_fields_id'].'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');                                      
                  
                  // przekazywany do allegro
                  if ($info['products_extra_fields_allegro'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest widoczne na aukcjach Allegro'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest widoczne na aukcjach Allegro'; }               
                  $tablica[] = array('<a href="slowniki/dodatkowe_pola_widocznosc_allegro.php?id_poz='.$info['products_extra_fields_id'].'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');                                      
                                    
                  // aktywana czy nieaktywna
                  if ($info['products_extra_fields_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole jest nieaktywne'; }               
                  $tablica[] = array('<a href="slowniki/dodatkowe_pola_status.php?id_poz='.$info['products_extra_fields_id'].'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');                    
                                    
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['products_extra_fields_id'];
                  $tekst .= '<a href="slowniki/dodatkowe_pola_slowniki.php'.$zmienne_do_przekazania.'"><img src="obrazki/lista_wojewodztw.png" alt="Słownik nazw dodatkowego pola" title="Słownik nazw dodatkowego pola" /></a>';
                  $tekst .= '<a href="slowniki/dodatkowe_pola_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  $tekst .= '<a href="slowniki/dodatkowe_pola_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);        

        }
    }  
    
    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Dodatkowe pola do produktów</div> 
            
            <div id="wyszukaj">
                <form action="slowniki/dodatkowe_pola.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj pole:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="60" />
                </div>  
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>                 
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="slowniki/dodatkowe_pola.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>              

            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a1">wg sortowania</a>
            <a id="sort_a2" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a2">nazwy rosnąco</a>
            <a id="sort_a3" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a3">nazwy malejąco</a>
            <a id="sort_a4" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a4">przypisany język</a>
            <a id="sort_a5" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a5">przypisanie do filtra</a>
            <a id="sort_a6" class="sortowanie" href="slowniki/dodatkowe_pola.php?sort=sort_a6">położenie</a>
            </div>              

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="slowniki/dodatkowe_pola_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            <div style="clear:both;"></div>      

            <form action="slowniki/dodatkowe_pola_akcja.php" method="post" class="cmxform">            
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('slowniki/dodatkowe_pola.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_extra_fields_id'); ?>
            //]]>
            </script>             

            <?php if ($ile_pozycji > 0) { ?>
            <div>
            <input type="submit" style="float:right" class="przyciskNon" value="Zapisz zmiany" />
            </div>
            <?php } ?>            
            
            <div class="cl"></div>
            
            </form>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
