<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT * FROM modules_payment ORDER BY status DESC, sortowanie";

    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / 200);
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
                                      array('Nazwa modułu','center'),
                                      array('Koszt płatności','center'),
                                      array('Minimalny<br />koszt płatności','center'),
                                      array('Minimalna<br />wartość zam.','center'),
                                      array('Maksymalna<br />wartość zam.','center'),
                                      array('Sort','center'),
                                      array('Status','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $zapytanie_params = "SELECT * FROM modules_payment_params WHERE modul_id = '".(int)$info['id']."'";
                  $sql_params = $db->open_query($zapytanie_params);
                  if ((int)$db->ile_rekordow($sql_params) > 0) {
                    $tablica_params = array();
                    while ( $info_params = $sql_params->fetch_assoc() ) {
                      $tablica_params[$info_params['kod']] = $info_params['wartosc'];
                    }
                  }

                  $tablica = array(array($info['id'],'center'),
                                   array($info['nazwa'],'left'),
                                   array( (is_numeric($tablica_params['PLATNOSC_KOSZT']) ? $waluty->FormatujCene($tablica_params['PLATNOSC_KOSZT'],false) : $tablica_params['PLATNOSC_KOSZT'] ),'center'),
                                   array( (is_numeric($tablica_params['PLATNOSC_KOSZT']) ? '---' : $waluty->FormatujCene($tablica_params['PLATNOSC_KOSZT_MINIMUM'],false)),'center'),
                                   array( ($tablica_params['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'] > 0 ? $waluty->FormatujCene($tablica_params['PLATNOSC_WARTOSC_ZAMOWIENIA_MIN'],false) : '---'),'center'),
                                   array( ($tablica_params['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'] > 0 ? $waluty->FormatujCene($tablica_params['PLATNOSC_WARTOSC_ZAMOWIENIA_MAX'],false) : '---'),'center'),
                                   array($info['sortowanie'],'center')
                  );  

                  // domyslny
                  if ($info['status'] == '1') { $obraz = '<img src="obrazki/aktywny_on.png" alt="Moduł jest włączony" title="Moduł jest włączony" />'; } else { $obraz = '<img src="obrazki/aktywny_off.png" alt="Moduł jest wyłączony" title="Moduł jest wyłączony" />'; }              
                  $tablica[] = array($obraz,'center');                                    
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['id'];
                  $tekst .= '<a href="moduly/platnosc_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  
                  if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) {
                    $tekst .= '<a href="moduly/platnosc_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  }

                  $tekst .= '</td></tr>';

                  $db->close_query($sql_params);
                  unset($zapytanie_params,$info_params);        

                  
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
            
            <div id="naglowek_cont">Moduły płatności</div>     

            <div id="pozycje_ikon">
                <div>
                  <?php if ( isset($_SESSION['programista']) && $_SESSION['programista'] == '1' ) { ?>
                    <a class="dodaj" href="moduly/platnosc_dodaj.php">dodaj nową pozycję</a>
                  <?php } ?>
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
            <?php Listing::pokazAjax('moduly/platnosc.php', $zapytanie, $ile_licznika, $ile_pozycji, 'id', '200'); ?>
            //]]>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
