<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $allegro = new Allegro();

    $katalog = KATALOG_SKLEPU.'allegro/';
    $ile_pozycji = '';

    $szablony = scandir($katalog);
    natcasesort($szablony);

    if( count($szablony) > 2 ) {
		foreach( $szablony as $szablon ) {
			if( file_exists($katalog . $szablon) && $szablon != '.' && $szablon != '..' && is_dir($katalog . $szablon) ) {
				$ile_pozycji++;
			}
		}

    }

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_licznika = ($ile_pozycji / 200);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Lp.','center'),
                                      array('Foto','center'),
                                      array('Katalog','center'),
                                      array('Domyślny','center'));

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            $lp = '1';

            foreach( $szablony as $szablon ) {
              if( file_exists($katalog . $szablon) && $szablon != '.' && $szablon != '..' && is_dir($katalog . $szablon) ) {
              
                if ( file_exists($katalog . $szablon.'/szablon.txt') ) {

                    if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $szablon) {
                      $tekst .= '<tr class="pozycja_on">';
                    } else {
                      $tekst .= '<tr class="pozycja_off">';
                    }        

                    $tablica = array(array($lp,'center'));

                    if ( file_exists($katalog . $szablon.'/screen.jpg') ) {
                        $tgm = Funkcje::pokazObrazek('allegro/'.$szablon.'/screen.jpg', $szablon, '100', '100', '', false, '');
                    } else {
                        $tgm = Funkcje::pokazObrazek(KATALOG_ZDJEC.'/domyslny.gif', $szablon, '100', '100', '', false, '');
                    }
                    $tablica[] = array($tgm,'center');            

                    $tablica[] = array($szablon,'center');

                    // domyslny
                    if ($allegro->polaczenie['CONF_DEFAULT_TEMPLATE'] == $szablon) { 
                      $obraz = '<img src="obrazki/aktywny_on.png" alt="Ten szablon jest domyślny" title="Ten szablon jest domyślny" />'; 
                    } else { 
                      $obraz = '-'; 
                    }              
                    $tablica[] = array($obraz,'center');    

                    $tekst .= $listing_danych->pozycje($tablica);
                    $tekst .= '<td class="rg_right">';
                      
                    $zmienne_do_przekazania = '?id_poz='.$szablon;
                    $tekst .= '<a href="allegro/szablony_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                      
                    $tekst .= '</td></tr>';
                    $lp++;
                    
                }

              }
            }
            $tekst .= '</table>';
            //
            echo $tekst;
            //

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
            
            <div id="naglowek_cont">Szablony aukcji Allegro</div>     

            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

            <script type="text/javascript">
            //<![CDATA[
            <?php    
            $zakres = '0,200';
            echo 'osc_ajax(\'allegro/szablony.php\',\''.$zakres.'\','.$ile_licznika.',\''.Funkcje::Zwroc_Get(array('parametr'),true).'\',\'200\');'; 
            ?>
            //]]>
            </script>
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
