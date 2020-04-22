<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    if (isset($_GET['zakladka']) && $_GET['zakladka'] != '' ) {
      unset($_GET['zakladka']);
    }
    if (isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) {
      $_GET['id_poz'] = $_GET['klient_id'];
      unset($_GET['klient_id']); 
    }

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and CONCAT(c.customers_firstname, ' ', c.customers_lastname, c.customers_email_address, a.entry_company, a.entry_nip) LIKE '%".$szukana_wartosc."%'";
    }
    
    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = ( $_GET['szukaj_status'] == '1' ? '1' : '0' );
        $warunki_szukania .= " and c.customers_status = '".$szukana_wartosc."'";
    }    

    $zapytanie = "SELECT distinct
                         count(cp.orders_id) as ilosc_wpisow,
                         cp.customers_id,
                         c.customers_firstname,
                         c.customers_lastname,
                         c.customers_email_address,
                         c.customers_telephone,
                         c.customers_status,
                         c.customers_shopping_points,
                         c.pp_statistics,
                         a.entry_country_id, a.entry_city, a.entry_street_address, a.entry_postcode, a.entry_company
                    FROM customers_points cp,
                         customers c,
                         address_book a
                   WHERE c.customers_id = cp.customers_id AND
                         c.customers_id = a.customers_id AND c.customers_default_address_id = a.address_book_id AND
                         ((cp.points_type = 'PP' OR cp.points_type = 'PM') OR (c.pp_statistics > 0))  " . $warunki_szukania ."
                    GROUP BY cp.customers_id";  

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
                $sortowanie = 'c.customers_lastname desc';
                break;
            case "sort_a2":
                $sortowanie = 'c.customers_lastname asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'c.customers_email_address desc';
                break;
            case "sort_a4":
                $sortowanie = 'c.customers_email_address asc';
                break;                 
        }            
    } else { $sortowanie = 'c.customers_lastname desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Klient','', 'width:20%'),
                                      array('Kontakt'),
                                      array('Ilość pozycji', 'center'),
                                      array('Ogólna ilość pkt klienta','center'),
                                      array('Ilość pkt z programu partnerskiego <br /> (niezatwierdzone / zatwierdzone)','center'),
                                      array('Ilość wejść do sklepu <br /> przez bannery na stronie klienta','center'),
                                      array('Status', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['customers_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['customers_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['customers_id'].'">';
                  }       

                  $tablica = array();

                  $tablica[] = array($info['customers_id'],'center');
                  $wyswietlana_nazwa = '';
                  $kontakt = '';

                  if ( $info['entry_company'] != '' ) {
                    $wyswietlana_nazwa .= '<span class="firma"">'.$info['entry_company'] . '</span><br />';
                  }
                  $wyswietlana_nazwa .= $info['customers_firstname']. ' ' . $info['customers_lastname'] . '<br />';
                  $wyswietlana_nazwa .= $info['entry_street_address']. '<br />';
                  $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'] . '<br />';

                  $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');

                  if (!empty($info['customers_email_address'])) {
                     $kontakt .= '<span class="maly_mail">' . $info['customers_email_address'] . '</span>';
                  }
                  if (!empty($info['customers_telephone'])) {
                      $kontakt .= '<span class="maly_telefon">' . $info['customers_telephone'] . '</span>';
                  }
                  $tablica[] = array($kontakt,'','line-height:17px');

                  $tablica[] = array($info['ilosc_wpisow'],'center');
                  
                  $tablica[] = array($info['customers_shopping_points'],'center');
                  
                  // ile punktow z programu PP
                  // niezatwierdzone
                  $zapytPunkty = "select distinct sum(points) as ilePkt from customers_points where customers_id = '".$info['customers_id']."' and (points_type = 'PP' OR points_type = 'PM') and points_status = '2'";
                  $sql_stat = $db->open_query($zapytPunkty);
                  $wyn = $sql_stat->fetch_assoc(); 
                  $zatwierdzone = $wyn['ilePkt'];
                  $db->close_query($sql_stat);
                  unset($zapytPunkty, $wyn); 
                  
                  // zatwierdzone
                  $zapytPunkty = "select distinct sum(points) as ilePkt from customers_points where customers_id = '".$info['customers_id']."' and (points_type = 'PP' OR points_type = 'PM')";
                  $sql_stat = $db->open_query($zapytPunkty);
                  $wyn = $sql_stat->fetch_assoc(); 
                  $niezatwierdzone = $wyn['ilePkt'];
                  $db->close_query($sql_stat);
                  unset($zapytPunkty, $wyn);                   
                  
                  $tablica[] = array($niezatwierdzone . ((!empty($zatwierdzone)) ? ' / ' . $zatwierdzone : '') ,'center'); 
                  unset($zatwierdzone, $niezatwierdzone, $wyn); 
                  
                  $tablica[] = array($info['pp_statistics'],'center');

                  // aktywany czy nieaktywny
                  if ($info['customers_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Konto jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'Konto jest nieaktywne'; }                                 
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" />','center');                    

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['customers_id'];                   
                  
                  $tekst .= '<td class="rg_right">';
                  $tekst .= '<a href="program_partnerski/partnerzy_operacje.php'.$zmienne_do_przekazania.'"><img src="obrazki/lista_wojewodztw.png" alt="Lista operacji" title="Lista operacji" /></a>';                  
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
        
        <!-- Skrypt do autouzupelniania -->           
        <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_program_partnerski.php', 50, 400 );
          });
          //]]>
        </script>         

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Program partnerski - lista partnerów</div>

            <div id="wyszukaj">
                <form action="program_partnerski/partnerzy.php" method="post" id="klienciForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="40" />
                </div>  

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Status:</span>
                    <?php
                    $tablia_status= Array();
                    $tablia_status[] = array('id' => '0', 'text' => 'dowolny');
                    $tablia_status[] = array('id' => '1', 'text' => 'aktywny');
                    $tablia_status[] = array('id' => '2', 'text' => 'nieaktywny');
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="program_partnerski/partnerzy.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="program_partnerski/partnerzy.php?sort=sort_a1">nazwiska malejąco</a>
            <a id="sort_a2" class="sortowanie" href="program_partnerski/partnerzy.php?sort=sort_a2">nazwiska rosnąco</a>
            <a id="sort_a3" class="sortowanie" href="program_partnerski/partnerzy.php?sort=sort_a3">e-mail malejąco</a>
            <a id="sort_a4" class="sortowanie" href="program_partnerski/partnerzy.php?sort=sort_a4">e-mail rosnąco</a>
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
            <?php Listing::pokazAjax('program_partnerski/partnerzy.php', $zapytanie, $ile_licznika, $ile_pozycji, 'customers_id'); ?>
            //]]>
            </script>             
 
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
