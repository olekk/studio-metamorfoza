<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from complaints_status s, complaints_status_description sd where s.complaints_status_id = sd.complaints_status_id and sd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' order by s.complaints_status_type, sd.complaints_status_name";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
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
                                      array('Nazwa','center'),
                                      array('Kolor','center'),
                                      array('Typ','center'),
                                      array('Domyślny','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['complaints_status_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['complaints_status_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['complaints_status_id'].'">';
                  }       

                  $tablica = array(array($info['complaints_status_id'],'center'),
                                   array($info['complaints_status_name'])); 

                  // kolor      
                  $tablica[] = array('<span class="status_reklamacji_kolor" style="background:#'.$info['complaints_status_color'].'">&nbsp;</span>','center');                                   

                  // typ
                  switch( $info['complaints_status_type'] ) {
                      case  1: $typ_pola = 'Nowe'; break;
                      case  2: $typ_pola = 'W realizacji'; break;
                      case  3: $typ_pola = 'Zamknięte (zrealizowane)'; break;
                      case  4: $typ_pola = 'Zamknięte (niezrealizowane)'; break;
                  }      
                  $tablica[] = array($typ_pola,'center');                   

                  // domyslny
                  if ($info['complaints_status_default'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten status jest domyślny" title="Ten status jest domyślny" />'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');                    

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['complaints_status_id'];
                  $tekst .= '<a href="reklamacje/reklamacje_statusy_komentarze.php?status_id='.$info['complaints_status_id'].'"><img src="obrazki/lista_wojewodztw.png" alt="Standardowe komentarze do reklamacji" title="Standardowe komentarze do reklamacji" /></a>';
                  $tekst .= '<a href="reklamacje/reklamacje_statusy_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  if ( $info['complaints_status_default'] != '1' ) {
                    $tekst .= '<a href="reklamacje/reklamacje_statusy_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  } else {
                    $tekst .= '<img src="obrazki/kasuj_off.png" alt="Nie można usunąć domyślnego statusu" title="Nie można usunąć domyślnego statusu" />';
                  }                   
                  
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
            
            <div id="naglowek_cont">Statusy reklamacji</div>     

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="reklamacje/reklamacje_statusy_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('reklamacje/reklamacje_statusy.php', $zapytanie, $ile_licznika, $ile_pozycji, 'complaints_status_id'); ?>
            //]]>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
