<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT * FROM print_labels ORDER BY brand, name";

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
                                      array('Producent','center'),
                                      array('Typ','center'),
                                      array('Opis','center'),
                                      array('Rozmiar','center'),
                                      array('Format','center'),
                                      array('Kolumn','center'),
                                      array('Wierszy','center'),
                                      array('Domyślna','center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['id'].'">';
                  }      

                  $tablica = array(array($info['id'],'center'),
                                   array($info['brand'],'center'),
                                   array($info['name'],'center'),
                                   array($info['description'],'center'),
                                   array($info['width'].'x'.$info['height'],'center'),
                                   array($info['format'],'center'),
                                   array($info['cols'],'center'),
                                   array($info['rows'],'center')
                  );  

                  // domyslny
                  if ($info['label_default'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Ta etykieta jest domyślna" title="Ta etykieta jest domyślna" />'; } else { $obraz = '-'; }              
                  $tablica[] = array($obraz,'center');                                    
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['id'];
                  $tekst .= '<a href="slowniki/etykiety_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  if ( $info['label_default'] != '1' ) {
                    $tekst .= '<a href="slowniki/etykiety_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  } else {
                    $tekst .= '<img src="obrazki/kasuj_off.png" alt="Nie można usunąć domyslnej pozycji" title="Nie można usunąć domyslnej pozycji" />';
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
            
            <div id="naglowek_cont">Definicje etykiet adresowych</div>     

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="slowniki/etykiety_dodaj.php">dodaj nową pozycję</a>
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
            <?php Listing::pokazAjax('slowniki/etykiety.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id'); ?>
            //]]>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
