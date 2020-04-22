<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and language_id = '".$_SESSION['domyslny_jezyk']['id']."' order by p.box_display, pd.box_title asc";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / 300);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa boxu','',' width:18%'),
                                      array('Rodzaj boxu'),
                                      array('Nagłówek boxu','center'),
                                      array('Co wyświetla ?','center', 'white-space: nowrap'),
                                      array('Opis boxu','','width:30%'),
                                      array('Miejsce wyświetlania','center'),
                                      array('RWD','center'),
                                      array('Wyświetlany w sklepie','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['box_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['box_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['box_id'].'">';
                  }   

                  $tablica = array(array($info['box_id'],'center'),
                                   array($info['box_title']));
                                   
                  $tek = '';
                  // plik php czy strona informacyjna
                  if ($info['box_type'] == 'plik') { 
                      //
                      $tek = '<span class="plik">'.$info['box_file'].'</span>'; 
                      //
                  }
                  if ($info['box_type'] == 'java') { 
                      //
                      $tek = '<span class="kodjava">Skrypt</span>'; 
                      //                  
                  }
                  if ($info['box_type'] == 'strona') { 
                      //
                      // nazwa strony informacyjnej
                      $strony = $db->open_query("select distinct pd.pages_title from pages_description pd where pd.pages_id = '".(int)$info['box_pages_id']."' and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
                      $sql_strona = $strony->fetch_assoc();
                      $tek = '<span class="strona">'.$sql_strona['pages_title'].'</span>'; 
                      
                      $db->close_query($strony);
                      unset($strony);
                      
                  }              
                  $tablica[] = array($tek);   
                  unset($tek);

                  // naglowek czy bez
                  if ($info['box_header'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Nagłówek jest wyświetlany w boxie'; } else { $obraz = 'aktywny_off.png'; $alt = 'Nagłówek nie jest wyświetlany w boxie'; }              
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" />','center');      

                  $tablica[] = array($info['box_display'],'center');                 
                  
                  $tablica[] = array($info['box_description']);
                  
                  $tek = 'wszędzie';
                  if ($info['box_localization'] == '3') { 
                      $tek = 'podstrony';
                  }
                  if ($info['box_localization'] == '2') { 
                      $tek = 'strona główna';
                  } 
                  $tablica[] = array($tek,'center');   
                  unset($tek);     

                  // czy rwd
                  if ($info['box_rwd'] == '1') { $obraz = '<img src="obrazki/rwd.png" alt="RWD" title="Ten box może być wyświetlany w szablonie RWD" />'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');    
                  unset($obraz);
                                           
                  // aktywany czy nieaktywny
                  if ($info['box_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten box jest wyświetlany w sklepie'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten box nie jest wyświetlany w sklepie'; }              
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" />','center');
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['box_id'];
                  $tekst .= '<a href="wyglad/boxy_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  $tekst .= '<a href="wyglad/boxy_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Boxy</div>     

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="wyglad/boxy_dodaj.php">dodaj nowy box</a>
                </div>            
            </div>
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <div id="legenda">
                <span class="plik"> box jest plikiem php</span>
                <span class="strona"> box wyświetla zawartość strony informacyjnej</span>
                <span class="kodjava"> box wyświetla wynik działania skryptu</span>
            </div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('wyglad/boxy.php', $zapytanie, $ile_licznika, $ile_pozycji, 'box_id', '300'); ?>
            //]]>
            </script>              
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
