<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "select * from email_templates order by template_name";
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
                                      array('Nazwa'),
                                      array('Domyślny','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['template_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['template_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['template_id'].'">';
                  }         

                  $tablica = array(array($info['template_id'],'center'),
                                   array($info['template_name']));

                  if ((int)$info['template_default'] == '1') {
                      $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten szablon jest używany jako domyślny" title="Ten szablon jest używany jako domyślny" />';
                      $tablica[] = array($obraz,'center'); 
                    } else {
                      $tablica[] = array('-','center'); 
                  }                                   

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['template_id'];
                  $tekst .= '<a href="slowniki/emaile_szablony_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  if ( $info['template_id'] != '1' ) {
                    $tekst .= '<a href="slowniki/emaile_szablony_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  } else {
                    $tekst .= '<img src="obrazki/kasuj_off.png" alt="Nie można usunąć tej pozycji" title="Nie można usunąć tej pozycji" />';
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
            
            <div id="naglowek_cont">Szablony emaili</div>     

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="slowniki/emaile_szablony_dodaj.php">dodaj nową pozycję</a>
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
            <?php Listing::pokazAjax('slowniki/emaile_szablony.php', $zapytanie, $ile_licznika, $ile_pozycji, 'template_id'); ?>
            //]]>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
