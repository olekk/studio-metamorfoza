<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $ilosc_wynikow = '30';

    $zapytanie = "SELECT * FROM comparisons";
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / $ilosc_wynikow);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    $db->close_query($sql);

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            // informacje o produktach - zakres
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array(''),
                                      array('Nazwa'),
                                      array('Typ eksportu','center'),
                                      array('Data eksportu','center'),
                                      array('Ilość wyeksportowanych produktów','center'));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['comparisons_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['comparisons_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['comparisons_id'].'">';
                  }    

                  if ( $info['comparisons_export_type'] == '1' ) {
                    $tryb_eksportu = 'tylko zaznaczone produkty';
                  } elseif (  $info['comparisons_export_type'] == '2' ) {
                    $tryb_eksportu = 'tylko zaznaczone kategorie';
                  } else {
                    $tryb_eksportu = 'wszystkie produkty';
                  }
                  $tablica = array(array($info['comparisons_id'],'center'),
                                   array('<img src="obrazki/porownywarki/'.$info['comparisons_plugin'].'.png" alt="" title="">','center', 'padding:0px;'),
                                   array($info['comparisons_name']),
                                   array($tryb_eksportu,'center'),
                                   array( ($info['comparisons_last_export'] != '' && $info['comparisons_last_export'] != '0000-00-00 00:00:00' ? date('d-m-Y H:i',strtotime($info['comparisons_last_export'])) : '---' ),'center'),
                                   array( ($info['comparisons_products_exported'] != '0' ? $info['comparisons_products_exported'] : '---'),'center'));
                                   
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['comparisons_id'];
                  
                  $tekst .= '<a href="porownywarki/porownywarki_eksport.php'.$zmienne_do_przekazania.'"><img src="obrazki/xml_maly.png" alt="Wykonanie eksportu do porównywarki" title="Wykonanie eksportu do porównywarki" /></a>';
                  $tekst .= '<a href="porownywarki/porownywarki_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  
                  if ( file_exists('../xml/'.$info['comparisons_plugin'].'.xml') ) {
                    $tekst .= '<a href="../xml/'.$info['comparisons_plugin'].'.xml"><img src="obrazki/zobacz.png" alt="Przejrzyj plik" title="Przejrzyj plik" /></a>';
                  } else {
                    $tekst .= '<img src="obrazki/zobacz_off.png" alt="Przejrzyj plik" title="Przejrzyj plik" />';
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
            
            <div id="naglowek_cont">Porównywarki produktów</div>     

            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('porownywarki/porownywarki.php', $zapytanie, $ile_licznika, $ile_pozycji, 'comparisons_id', $ilosc_wynikow); ?>
            //]]>
            </script>                

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
