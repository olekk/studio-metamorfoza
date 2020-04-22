<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $zapytanie = "SELECT ce.fields_id, ce.fields_order, ce.fields_input_type, ce.fields_required_status, cei.fields_name, ce.fields_status, ce.fields_input_type, ce.fields_type FROM customers_extra_fields ce, customers_extra_fields_info cei WHERE cei.fields_id = ce.fields_id AND cei.languages_id ='1' ORDER BY ce.fields_order";

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
                                      array('Nazwa pola'),
                                      array('Typ pola','center'),
                                      array('Sort','center'), 
                                      array('Wymagane','center'),
                                      array('Status','center')
            );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['fields_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['fields_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['fields_id'].'">';
                  }       

                  //typ pola
                  switch( $info['fields_input_type'] ) {
                      case  0: $typ_pola = 'Input' . (($info['fields_type'] == 'kalendarz') ? ' (kalendarz)' : ''); break;
                      case  1: $typ_pola = 'Textarea'; break;
                      case  2: $typ_pola = 'Radio'; break;
                      case  3: $typ_pola = 'Checkbox'; break;
                      case  4: $typ_pola = 'Select'; break;
                      default: $typ_pola = 'Input'; break;
                  }

                  $tablica = array(array($info['fields_id'] . '<input type="hidden" name="id[]" value="'.$info['fields_id'].'" />','center'),
                                   array($info['fields_name'],'left'),
                                   array($typ_pola,'center')
                  ); 

                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['fields_id'].'" value="'.$info['fields_order'].'" class="sort_prod" />','center');    

                  // wymagane czy nie wymagane
                  if ($info['fields_required_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest wymagane'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole nie jest nieaktywne'; }               
                  $tablica[] = array('<a href="slowniki/dodatkowe_pola_klienci_wymagane.php?id_poz='.$info['fields_id'].'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');

                  // aktywana czy nieaktywna
                  if ($info['fields_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'To pole jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'To pole jest nieaktywne'; }               
                  $tablica[] = array('<a href="slowniki/dodatkowe_pola_klienci_status.php?id_poz='.$info['fields_id'].'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['fields_id'];
                  $tekst .= '<a href="slowniki/dodatkowe_pola_klienci_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  $tekst .= '<a href="slowniki/dodatkowe_pola_klienci_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Dodatkowe pola do klientów</div>     

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="slowniki/dodatkowe_pola_klienci_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            <div style="clear:both;"></div>      

            <form action="slowniki/dodatkowe_pola_klienci_akcja.php" method="post" class="cmxform">            
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('slowniki/dodatkowe_pola_klienci.php', $zapytanie, $ile_licznika, $ile_pozycji, 'fields_id'); ?>
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

}
