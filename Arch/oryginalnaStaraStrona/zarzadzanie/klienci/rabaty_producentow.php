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
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and ( CONCAT(m.discount_name, cg.customers_groups_name) LIKE '%".$szukana_wartosc."%' 
                                 or CONCAT(cu.customers_firstname,' ', cu.customers_lastname, ' ' , cu.customers_email_address) LIKE '%".$szukana_wartosc."%' 
                                 or a.entry_company LIKE '%".$szukana_wartosc."%' )";
    }
    
    if ( isset($_GET['szukaj_grupa']) && $_GET['szukaj_grupa'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_grupa']);
        $warunki_szukania .= " and m.discount_groups_id = '".$szukana_wartosc."'";
    }    
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = $filtr->process((int)$_GET['producent']);
        $warunki_szukania .= " and m.discount_manufacturers_id = '".$id_producenta."'";
        unset($id_producenta);
    }    
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }    

    $zapytanie = "select distinct
                        m.discount_id,
                        m.discount_name,
                        m.discount_groups_id,
                        m.discount_customers_id,
                        m.discount_manufacturers_id,
                        m.discount_discount,
                        cg.customers_groups_id,
                        cg.customers_groups_name,
                        cu.customers_id,
                        cu.customers_firstname,
                        cu.customers_lastname,
                        cu.customers_email_address,
                        cu.customers_default_address_id,
                        a.entry_company
                    FROM discount_manufacturers m
                        LEFT JOIN customers_groups cg ON m.discount_groups_id = cg.customers_groups_id
                        LEFT JOIN customers cu ON m.discount_customers_id = cu.customers_id
                        LEFT JOIN address_book a on cu.customers_id = a.customers_id and cu.customers_default_address_id = a.address_book_id " . $warunki_szukania;
                        
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
                $sortowanie = 'm.discount_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'm.discount_name desc';
                break;                 
            case "sort_a3":
                $sortowanie = 'm.discount_discount desc';
                break;
            case "sort_a4":
                $sortowanie = 'm.discount_discount asc';
                break;
            case "sort_a5":
                $sortowanie = 'cg.customers_groups_name asc';
                break;
            case "sort_a6":
                $sortowanie = 'cg.customers_groups_name desc';
                break; 
            case "sort_a7":
                $sortowanie = 'cu.customers_lastname asc';
                break;
            case "sort_a8":
                $sortowanie = 'cu.customers_lastname desc';
                break;                     
        }            
    } else { $sortowanie = 'm.discount_name asc'; }
    
    $zapytanie .= " ORDER BY ".$sortowanie;   

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa','center'),
                                      array('Domyślny rabat','center'),
                                      array('Grupa klientów','center'),
                                      array('Klient','center'),
                                      array('Producent','center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            // stworzy tablice z nazwami producentow
            $TablicaTmp = Funkcje::TablicaProducenci();
            $TablicaProducenci = array();
            foreach ($TablicaTmp as $Tmp) {
                $TablicaProducenci[$Tmp['id']] = $Tmp['text'];
            }
            unset($TablicaTmp);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['discount_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        
                  
                  $tablica = array();
                  
                  $tablica[] = array($info['discount_id'],'center');
                  
                  $tablica[] = array($info['discount_name'],'center');
                  
                  $tablica[] = array($info['discount_discount'] < 0 ? '<span class="zielony">'.$info['discount_discount'].'%</span>' : '<span class="czerwony">'.$info['discount_discount'].'%</span>','center');
                  
                  $tablica[] = array(((!empty($info['customers_groups_name'])) ? $info['customers_groups_name'] : '-'),'center');
                  
                  if (!empty($info['customers_lastname'])) {
                     $wyswietlana_nazwa = '';
                     if ( $info['entry_company'] != '' ) {
                        $wyswietlana_nazwa = '<span class="firma"">'.$info['entry_company'] . '</span><br />';
                     }                  
                     $tablica[] = array( $wyswietlana_nazwa . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '<br /><span class="maili">' . $info['customers_email_address'] . '</span>','center');
                     unset($wyswietlana_nazwa);
                    } else {
                     $tablica[] = array('-','center');
                  }
                  
                  $wszyscy_producenci = explode(',', $info['discount_manufacturers_id']);

                  foreach ( $wszyscy_producenci as $producent ) {
                              
                      if ( isset($TablicaProducenci[$producent]) ) {
                           $wyswietl .= '<span class="nazKatProd">' . $TablicaProducenci[$producent] . '</span> <br />';
                      }
                      
                  }
                  
                  $tablica[] = array($wyswietl,'center');   

                  unset($wyswietl, $wszyscy_producenci);

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['discount_id']; 
                  $tekst .= '<a href="klienci/rabaty_producentow_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  $tekst .= '<a href="klienci/rabaty_producentow_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek, $TablicaProducentow);        

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
            
            <div id="naglowek_cont">Rabaty dla producentów</div>  

            <div id="wyszukaj">
                <form action="klienci/rabaty_producentow.php" method="post" id="klienciForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
                </div> 

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Grupa:</span>
                    <?php
                    $tablica = Klienci::ListaGrupKlientow();
                    echo Funkcje::RozwijaneMenu('szukaj_grupa', $tablica, ((isset($_GET['szukaj_grupa'])) ? $filtr->process($_GET['szukaj_grupa']) : '')); ?>
                </div>                
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Producent:</span>                                        
                    <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : ''), ' style="width:120px"'); ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="klienci/rabaty_producentow.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a1">nazwa malejąco</a>
            <a id="sort_a2" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a2">nazwa rosnąco</a>
            <a id="sort_a3" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a3">rabat malejąco</a>
            <a id="sort_a4" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a4">rabat rosnąco</a>
            <a id="sort_a5" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a5">grupa klientów malejąco</a>
            <a id="sort_a6" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a6">grupa klientów rosnąco</a>
            <div style="margin-left:77px">
                <a id="sort_a7" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a7">nazwiska klientów malejąco</a>
                <a id="sort_a8" class="sortowanie" href="klienci/rabaty_producentow.php?sort=sort_a8">nazwiska klientów rosnąco</a>            
            </div>
            </div>               

            <div style="clear:both;"></div> 
            
            <?php
            $zapytanie_producent = "select * from manufacturers";
            $sqlm = $db->open_query($zapytanie_producent);
            if ((int)$db->ile_rekordow($sqlm) > 0) {         
            ?>
            
            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="klienci/rabaty_producentow_dodaj.php">dodaj nową pozycję</a>
                </div>            
            </div>
            <div style="clear:both;"></div>

            <?php
            }
            $db->close_query($sqlm);
            unset($zapytanie_producent);
            ?>
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('klienci/rabaty_producentow.php', $zapytanie, $ile_licznika, $ile_pozycji, 'discount_id'); ?>
            //]]>
            </script>                

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
