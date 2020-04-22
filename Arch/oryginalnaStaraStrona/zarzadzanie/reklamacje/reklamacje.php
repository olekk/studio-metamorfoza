<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_GET['zakladka']) ) unset($_GET['zakladka']);
if ( isset($_SESSION['waluta_reklamacje']) ) unset($_SESSION['waluta_reklamacje']);

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and (cu.complaints_customers_name LIKE '%".$szukana_wartosc."%' OR cu.complaints_customers_email LIKE '%".$szukana_wartosc."%' OR cu.complaints_rand_id LIKE '%".$szukana_wartosc."%')";
    }

    if ( isset($_GET['szukaj_data_od']) && $_GET['szukaj_data_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_od'] . ' 00:00:00')));
        $warunki_szukania .= " and cu.complaints_date_created >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_do']) && $_GET['szukaj_data_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_do'] . ' 23:59:59')));
        $warunki_szukania .= " and cu.complaints_date_created <= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and cu.complaints_status_id = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['opiekun']) && $_GET['opiekun'] > 0 ) {
        $szukana_wartosc = $filtr->process($_GET['opiekun']);
        $warunki_szukania .= " and cu.complaints_service = '".$szukana_wartosc."'";
    }    

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "SELECT * FROM complaints cu
                    LEFT JOIN customers c ON cu.complaints_customers_id = c.customers_id
                    LEFT JOIN address_book a ON c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id " . $warunki_szukania;

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
                $sortowanie = 'cu.complaints_date_created desc';
                break;
            case "sort_a2":
                $sortowanie = 'cu.complaints_date_created asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'cu.complaints_customers_name desc';
                break;
            case "sort_a4":
                $sortowanie = 'cu.complaints_customers_name asc';
                break; 
            case "sort_a5":
                $sortowanie = 'cu.complaints_customers_orders_id desc';
                break;
            case "sort_a6":
                $sortowanie = 'cu.complaints_customers_orders_id asc';
                break;                        
        }            
    } else { $sortowanie = 'cu.complaints_date_created desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {

            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Nr zgłoszenia', 'center'),
                                      array('Klient', 'center'),
                                      array('Tytuł zgłoszenia', 'center'),
                                      array('Data zgłoszenia', 'center'),
                                      array('Nr zamówienia', 'center'),
                                      array('Status', 'center'),
                                      array('Opiekun reklamacji', 'center'));

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['complaints_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['complaints_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['complaints_id'].'">';
                  }        

                  $tablica = array();

                  $tablica[] = array($info['complaints_id'],'center');
                  
                  $tablica[] = array($info['complaints_rand_id'],'center');
                  
                  $wyswietlana_nazwa = '';
                  // jezeli klient jest z bazy
                  if ($info['complaints_customers_id'] > 0) {
                      //
                      if ($info['entry_company'] != '') {
                        $wyswietlana_nazwa .= '<span class="firma"">'.$info['entry_company'] . '</span>';
                      }
                      $wyswietlana_nazwa .= $info['entry_firstname'] . ' ' . $info['entry_lastname'] . '<br />';
                      $wyswietlana_nazwa .= $info['entry_street_address']. '<br />';
                      $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'];
                      //
                    } else {
                      //
                      $wyswietlana_nazwa = nl2br($info['complaints_customers_name'] . ' <br />' . $info['complaints_customers_address']);
                      //
                  }
                  // email
                  if (!empty($info['complaints_customers_email'])) {
                      $wyswietlana_nazwa .= '<span class="maly_mail">' . $info['complaints_customers_email'] . '</span>';
                  }
                  
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');
                  
                  $tablica[] = array($info['complaints_subject']);

                  $tablica[] = array(date('d-m-Y H:i',strtotime($info['complaints_date_created'])),'center');

                  $tablica[] = array($info['complaints_customers_orders_id'],'center');
       
                  $tablica[] = array(Reklamacje::pokazNazweStatusuReklamacji($info['complaints_status_id']), 'center');
                  
                  // opiekun zamowienia
                  $zapytanie_tmp = "select distinct * from admin where admin_id = '".(int)$info['complaints_service']."'";
                  $sqls = $db->open_query($zapytanie_tmp);
                  if ((int)$db->ile_rekordow($sqls) > 0) {
                      $infs = $sqls->fetch_assoc();
                      $Opiekun = '<span class="opiekun">'.$infs['admin_firstname'] . ' ' . $infs['admin_lastname'] . '</span>';
                      $db->close_query($sqls);
                     } else {
                      $Opiekun = '-';
                  }
                  unset($zapytanie_tmp, $infs);    
                  //
                                  
                  $tablica[] = array($Opiekun,'center');                  
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['complaints_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $tekst .= '<a href="reklamacje/reklamacje_reklamacja_pdf.php'.$zmienne_do_przekazania.'"><img src="obrazki/pdf_2.png" alt="Wydruk reklamacji" title="Wydruk reklamacji" /></a>';
                  $tekst .= '<a href="reklamacje/reklamacje_szczegoly.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  $tekst .= '<a href="reklamacje/reklamacje_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  
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
      
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function() {
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_reklamacje.php', 50, 400 );

            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });                
        });
        //]]>
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Reklamacje</div>

            <div id="wyszukaj">
                <form action="reklamacje/reklamacje.php" method="post" id="reklamacjeForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="40" />
                </div>  
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Data zgłoszenia:</span>
                    <input type="text" id="data_reklamacje_od" name="szukaj_data_od" value="<?php echo ((isset($_GET['szukaj_data_od'])) ? $filtr->process($_GET['szukaj_data_od']) : ''); ?>" size="12" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_reklamacje_do" name="szukaj_data_do" value="<?php echo ((isset($_GET['szukaj_data_do'])) ? $filtr->process($_GET['szukaj_data_do']) : ''); ?>" size="12" class="datepicker" />
                </div>

                <div class="cl" style="height:9px"></div>                

                <div class="wyszukaj_select">
                    <span>Status:</span>
                    <?php
                    $tablia_status= Array();
                    $tablia_status = Reklamacje::ListaStatusowReklamacji(true);
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="width:240px"'); ?>
                </div>

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Opiekun:</span>
                    <?php
                    // pobieranie informacji od uzytkownikach
                    $zapytanie_tmp = "select distinct * from admin where admin_groups_id = '2' order by admin_lastname, admin_firstname";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica_user = array();
                    $tablica_user[] = array('id' => 0, 'text' => 'dowolny');
                    while ($infs = $sqls->fetch_assoc()) { 
                    $tablica_user[] = array('id' => $infs['admin_id'], 'text' => $infs['admin_firstname'] . ' ' . $infs['admin_lastname']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //
                    echo Funkcje::RozwijaneMenu('opiekun', $tablica_user, ((isset($_GET['opiekun'])) ? $filtr->process($_GET['opiekun']) : ''), ' style="width:150px"'); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="reklamacje/reklamacje.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        

            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a1">data zgłoszenia malejąco</a>
            <a id="sort_a2" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a2">data zgłoszenia rosnąco</a>
            <a id="sort_a3" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a3">klient malejąco</a>
            <a id="sort_a4" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a4">klient rosnąco</a>
            <a id="sort_a5" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a5">nr zamówienia malejąco</a>
            <a id="sort_a6" class="sortowanie" href="reklamacje/reklamacje.php?sort=sort_a6">nr zamówienia rosnąco</a>            
            </div>             

              <div id="pozycje_ikon">
                  <div>
                      <a class="dodaj" href="reklamacje/reklamacje_dodaj.php<?php echo ( isset($_GET['klient_id']) ? '?klient_id='.$filtr->process((int)$_GET['klient_id']) : ''); ?>">dodaj nową reklamacje</a>
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
            <?php Listing::pokazAjax('reklamacje/reklamacje.php', $zapytanie, $ile_licznika, $ile_pozycji, 'complaints_id'); ?>
            //]]>
            </script>             

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
