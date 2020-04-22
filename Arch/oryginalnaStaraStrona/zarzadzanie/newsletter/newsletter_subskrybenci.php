<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and (subscribers_email_address like '%".$szukana_wartosc."%')";
    }
    
    // jezeli jest opcja
    if (isset($_GET['opcja']) && !empty($_GET['opcja'])) {
        switch ($filtr->process($_GET['opcja'])) {
            case "1":
                $warunki_szukania .= " and customers_id > 0";
                break;
            case "2":
                $warunki_szukania .= " and customers_id = 0";
                break;               
        }     
    }     
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }    

    $zapytanie = "select distinct * from subscribers ".$warunki_szukania;
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a3":
                $sortowanie = 'customers_newsletter asc, subscribers_email_address asc';
                break; 
            case "sort_a2":
                $sortowanie = 'customers_newsletter desc, subscribers_email_address asc';
                break;                 
            case "sort_a1":
                $sortowanie = 'subscribers_email_address asc';
                break;
            case "sort_a4":
                $sortowanie = 'subscribers_email_address desc';
                break; 
            case "sort_a5":
                $sortowanie = 'date_added desc, subscribers_email_address asc';
                break; 
            case "sort_a6":
                $sortowanie = 'date_added asc, subscribers_email_address asc';
                break;                 
        }            
    } else { $sortowanie = 'subscribers_email_address asc'; }    
    
    $zapytanie .= " order by ".$sortowanie;    
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('ID', 'center'),
                                      array('Adres email'),
                                      array('Klient'),
                                      array('Data zapisania', 'center', 'white-space: nowrap'),
                                      array('Data aktywacji', 'center', 'white-space: nowrap'),
                                      array('Nr IP', 'center'),                                  
                                      array('Status', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['subscribers_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['subscribers_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['subscribers_id'].'">';
                  }        

                  $tablica = array();
                  
                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['subscribers_id'].'" /><input type="hidden" name="id[]" value="'.$info['subscribers_id'].'" />','center');
                  
                  $tablica[] = array($info['subscribers_id'],'center');
                  
                  $tablica[] = array($info['subscribers_email_address']);
                  
                  if ($info['customers_id'] > 0) {
                     //
                     // ustala nazwe klienta
                     $sqlData = $db->open_query("select customers_firstname, customers_lastname from customers where customers_id = '".(int)$info['customers_id']."'");
                     $infoData = $sqlData->fetch_assoc(); 
                     $tablica[] = array('<a class="klient" href="klienci/klienci_edytuj.php?id_poz='.(int)$info['customers_id'].'">'.$infoData['customers_firstname'].' '.$infoData['customers_lastname'].'</a>');
                     $db->close_query($sqlData);
                     unset($sqlDatak, $infoData);                  
                     //
                   } else {
                     //
                     $tablica[] = array('-');
                     //
                  }
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',strtotime($info['date_added'])) : '-'),'center');
                  
                  if ($info['customers_id'] == 0) {
                     //
                     $tablica[] = array(((Funkcje::czyNiePuste($info['date_account_accept'])) ? date('d-m-Y H:i',strtotime($info['date_account_accept'])) : '-'),'center');
                     //
                    } else {
                     //
                     $tablica[] = array('-','center');
                     //
                  }
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['ip_host'])) ? $info['ip_host'] : '-'),'center');
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['subscribers_id'];         
                  
                  // aktywany czy nieaktywny
                  if ($info['customers_newsletter'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten klient jest zapisany do newslettera'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten klient nie jest zapisany do newslettera'; }               
                  $tablica[] = array('<a href="newsletter/newsletter_subskrybenci_status.php'.$zmienne_do_przekazania.'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');    
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';

                  if ((int)$info['customers_id'] == 0) {
                      //
                      $tekst .= '<a href="newsletter/newsletter_subskrybenci_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                      $tekst .= '<a href="newsletter/newsletter_subskrybenci_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                      //
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
            
            <div id="naglowek_cont">Newsletter subskrybenci</div>

            <div id="wyszukaj">
                <form action="newsletter/newsletter_subskrybenci.php" method="post" id="poForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj email:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="40" />
                </div>  
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Pokaż tylko:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- wszyscy --');
                    $tablica[] = array('id' => '1', 'text' => 'Tylko zarejestrowani klienci');
                    $tablica[] = array('id' => '2', 'text' => 'Klienci zapisani do newslettera bez rejestracji');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('opcja', $tablica, ((isset($_GET['opcja'])) ? $filtr->process($_GET['opcja']) : '')); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="newsletter/newsletter_subskrybenci.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="newsletter/newsletter_subskrybenci_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a3" class="sortowanie" href="newsletter/newsletter_subskrybenci.php?sort=sort_a3">nieaktywne</a>
            <a id="sort_a2" class="sortowanie" href="newsletter/newsletter_subskrybenci.php?sort=sort_a2">aktywne</a>            
            <a id="sort_a1" class="sortowanie" href="newsletter/newsletter_subskrybenci.php?sort=sort_a1">adres email rosnąco</a>
            <a id="sort_a4" class="sortowanie" href="newsletter/newsletter_subskrybenci.php?sort=sort_a4">adres email malejąco</a>
            <a id="sort_a5" class="sortowanie" href="newsletter/newsletter_subskrybenci.php?sort=sort_a5">data dodania malejąco</a>
            <a id="sort_a6" class="sortowanie" href="newsletter/newsletter_subskrybenci.php?sort=sort_a6">data dodania rosnąco</a>            
            </div>             

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="newsletter/newsletter_subskrybenci_dodaj.php">dodaj nową pozycję</a>
                </div>            
                <?php if ($ile_pozycji > 0) { ?>
                <div style="float:right">
                    <a class="export" href="newsletter/newsletter_subskrybenci_export.php">eksportuj dane do pliku (wszystkie adresy)</a>
                </div>
                <div style="float:right">
                    <a class="export" href="newsletter/newsletter_subskrybenci_export.php?zapisani">eksportuj dane do pliku (tylko zapisanych do newslettera)</a>
                </div>                
                <?php } ?>                 
            </div>
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            
            <div id="akcja">
                <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                <div class="lf" style="padding-right:20px">
                    <span onclick="akcja(1)">zaznacz wszystkie</span>
                    <span onclick="akcja(2)">odznacz wszystkie</span>
                </div>
   
                <div id="akc">
                    Wykonaj akcje: 
                    <select name="akcja_dolna" id="akcja_dolna">
                        <option value="0"></option>
                        <option value="1">usuń zaznaczone pozycje</option>
                        <option value="2">zmień status zaznaczonych na aktywne</option>
                        <option value="3">zmień status zaznaczonych na nieaktywne</option>
                    </select>
                </div>
                <div style="clear:both;"></div>
            </div>             
            
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <?php if ($ile_pozycji > 0) { ?>
            <div style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
            <?php } ?>       

            </form>      

            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('newsletter/newsletter_subskrybenci.php', $zapytanie, $ile_licznika, $ile_pozycji, 'subscribers_id'); ?>
            //]]>
            </script>                

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
