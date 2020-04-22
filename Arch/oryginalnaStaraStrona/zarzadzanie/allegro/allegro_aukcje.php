<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $allegro = new Allegro();

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " AND ap.auction_id LIKE '%".$szukana_wartosc."%' OR ap.products_name LIKE '%".$szukana_wartosc."%'";
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and ap.auction_status = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_format']) && $_GET['szukaj_format'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_format']);
        if ( $szukana_wartosc == '2' ) $szukana_wartosc = '0';
        $warunki_szukania .= " and ap.auction_buy_now = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_zakonczenia_od']) && $_GET['szukaj_data_zakonczenia_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_zakonczenia_od'] . ' 00:00:00')));
        $warunki_szukania .= " and ap.products_date_end >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_zakonczenia_do']) && $_GET['szukaj_data_zakonczenia_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_zakonczenia_do'] . ' 00:00:00')));
        $warunki_szukania .= " and ap.products_date_end <= '".$szukana_wartosc."'";
    }

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "SELECT ap.*, p.products_image, p.products_model, p.products_quantity AS iloscMagazyn, p.products_jm_id, pj.products_jm_quantity_type, m.manufacturers_name 
                  FROM allegro_auctions ap 
                  LEFT JOIN products p ON p.products_id = ap.products_id 
                  LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                  LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                  " . $warunki_szukania;
                  
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
                $sortowanie = 'ap.synchronization desc, ap.products_date_end DESC';
                break;
            case "sort_a2":
                $sortowanie = 'ap.synchronization desc, ap.products_date_end ASC';
                break;                 
            case "sort_a3":
                $sortowanie = 'ap.auction_id desc';
                break;
            case "sort_a4":
                $sortowanie = 'ap.auction_id asc';
                break;                 
        }            
    } else { $sortowanie = 'ap.synchronization desc, ap.products_date_end DESC'; }    

    // informacje o produktach - zakres
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Akcja','center'),
                                      array('ID aukcji','center'),
                                      array('Foto','center'),
                                      array('Nazwa produktu', '', 'width:40%'),
                                      array('Format','center'),
                                      array('Data rozpoczęcia','center'),
                                      array('Data zakończenia','center'),
                                      array('Ilość wystawiona / magazyn','center'),
                                      array('Ofert','center'),
                                      array('Sprzedane','center'),
                                      array('Wyświe- tleń','center'),
                                      array('Warianty','center'),
                                      array('Status','center'));
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
          
                $ilosc_magazyn = $info['iloscMagazyn'];

                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['allegro_id']) {
                   $tekst .= '<tr class="pozycja_on" id="sk_'.$info['allegro_id'].'">';
                 } else {
                   $tekst .= '<tr class="pozycja_off" id="sk_'.$info['allegro_id'].'">';
                }      

                $link = '';
                if ( Allegro::SerwerAllegro() == 'nie' ) {
                  $link = 'http://allegro.pl/item' .  $info['auction_id'] . '_webapi.html';
                } else {
                  $link = 'http://allegro.pl.webapisandbox.pl/show_item.php?item='.$info['auction_id'];
                }

                $nazwa_produktu = '<b><a href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'">'.$info['products_name'].'</a></b>';
                if (trim($info['products_model']) != '') {
                  $nazwa_produktu .= '<span class="male_nr_kat">Nr kat: <b>'.$info['products_model'].'</b></span>';
                }
                // pobieranie danych o producencie
                if (trim($info['manufacturers_name']) != '') {                     
                  $nazwa_produktu .= '<span class="male_producent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
                }                  

                $wyswietl_cechy = '';

                if ( isset($info['products_stock_attributes']) && $info['products_stock_attributes'] != '' ) {

                  $tablica_kombinacji_cech = explode(';', $info['products_stock_attributes']);
                  
                  for ( $t = 0, $c = count($tablica_kombinacji_cech); $t < $c; $t++ ) {
                  
                    $tablica_wartosc_cechy = explode('-', $tablica_kombinacji_cech[$t]);

                    $nazwa_cechy = Funkcje::NazwaCechy( (int)$tablica_wartosc_cechy['0'] );
                    $nazwa_wartosci_cechy = Funkcje::WartoscCechy( (int)$tablica_wartosc_cechy['1'] );

                    $wyswietl_cechy .= '<span class="male_cecha">'.$nazwa_cechy . ': <b>' . $nazwa_wartosci_cechy . '</b></span>';
                    
                    unset($tablica_wartosc_cechy);
                    
                  }
                  
                  unset($tablica_kombinacji_cech);
                  
                  // jezeli jest powiazanie cech z magazynem
                  if ( CECHY_MAGAZYN == 'tak' ) {

                      $cechy_produktu = str_replace(';', ',' , $info['products_stock_attributes']);
                      
                      $zapytanie_ilosc_cechy = "SELECT * 
                                                  FROM products_stock
                                                 WHERE products_id = '" . (int)$info['products_id']. "' 
                                                   AND products_stock_attributes = '".$cechy_produktu."'";
                                                   
                      $sql_ilosc_cechy = $db->open_query($zapytanie_ilosc_cechy);

                      if ((int)$db->ile_rekordow($sql_ilosc_cechy) > 0) {
                      
                          $info_ilosc_cechy = $sql_ilosc_cechy->fetch_assoc();
                          $ilosc_magazyn = $info_ilosc_cechy['products_stock_quantity'];
                          
                      }
                      
                      $db->close_query($sql_ilosc_cechy);
                      
                      unset($zapytanie_ilosc_cechy, $info_ilosc_cechy, $cechy_produktu);

                  }

                }
                
                if (!empty($wyswietl_cechy)) {                     
                  $nazwa_produktu .= $wyswietl_cechy;
                }
                
                if ( $info['auction_status'] == '1' ) {
                  $status_img = '<img src="obrazki/allegro_trwa.png" alt="Aukcja trwa" title="Aukcja trwa" />';
                } elseif ( $info['auction_status'] == '2' ) {
                  $status_img = '<img src="obrazki/allegro_zakonczona.png" alt="Aukcja zakończona" title="Aukcja zakończona" />';
                } elseif ( $info['auction_status'] == '-1' ) {
                  $status_img = '<img src="obrazki/allegro_czeka.png" alt="Aukcja czeka na wystawienie" title="Aukcja czeka na wystawienie" />';
                } elseif ( $info['auction_status'] == '3' ) {
                  $status_img = '<img src="obrazki/allegro_zakonczona.png" alt="Aukcja zakończona przed czasem" title="Aukcja zakończona przed czasem" />';
                }
                
                if ( $info['auction_buy_now'] == '1' ) {
                  $format_img = '<img src="obrazki/allegro_kup_teraz.png" alt="Aukcja Kup teraz" title="Aukcja Kup teraz" />';
                } elseif ( $info['auction_buy_now'] == '0' ) {
                  $format_img = '<img src="obrazki/allegro_licytacja.png" alt="Aukcja z Licytacją" title="Aukcja z Licytacją" />';
                }
                if ( $info['auction_type'] == '1' ) {
                  $format_img = '<img src="obrazki/allegro_sklep.png" alt="Aukcja - Sklep" title="Aukcja - Sklep" />';
                }
                
                if ( $info['variants'] == '1' ) {
                  $warianty_img = '<img src="obrazki/allegro_warianty.png" alt="Aukcja - wielowariantowa" title="Aukcja - wielowariantowa" />';
                } else {
                  $warianty_img = '-';
                }

                if ( $info['synchronization'] != '1' ) {
                  $akcja = '<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['allegro_id'].'" /><input type="hidden" name="id[]" value="'.$info['allegro_id'].'" />';
                } else {
                  $akcja = '<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['allegro_id'].'" disabled="disabled" /><input type="hidden" name="id[]" value="'.$info['allegro_id'].'" />';
                }
                
                // nr aukcji rzeczywisty
                $nr_aukcji = $info['auction_id'];
                if ( $info['auction_id'] < (time() + 100 *86400) ) {
                     $nr_aukcji = '';
                }
                
                // jezeli jednostka miary calkowita
                if ( $info['products_jm_quantity_type'] == 1 ) {
                     $ilosc_magazyn = (int)$ilosc_magazyn;
                }           

                if ( !empty($info['products_image']) ) {
                     //
                     $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                     $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                     $tgm .= Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true);
                     $tgm .= '</div>';
                     //
                   } else { 
                     //
                     $tgm = '-';
                     //
                }                  
                
                // sprawdzanie czy ilosc w magazynie nie jest mniejsza niz na allegro
                $ilosci_magazynu = $info['auction_quantity'] . ' / ' . $ilosc_magazyn;
                if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && $info['auction_quantity'] > $ilosc_magazyn ) {
                    $ilosci_magazynu = '<span class="magazyn_blad toolTipTop" title="Ilość produktów na Allegro jest większa niż stan magazynowy w sklepie">' . $ilosci_magazynu . '</span>';
                }
                
                $data_zakonczenia_allegro = '-';
                if ( !empty($info['auction_date_end']) && strtotime($info['auction_date_end']) > 0 ) {
                    $data_zakonczenia_allegro = date('d-m-Y H:i:s',strtotime($info['auction_date_end']));
                } else {
                    $data_zakonczenia_allegro = 'do wyczerpania';
                }

                $tablica = array(array($akcja,'center'),
                                 array((($info['synchronization'] == 1) ? '<span class="brakSynch toolTipTop" title="Nie zostały pobrane dane aukcji z Allegro"></span><br />' : '') . (($nr_aukcji != '') ? '<a href="'.$link.'">'.$info['auction_id'].'</a>' : ''),'center'),
                                 array($tgm, 'center'),
                                 array($nazwa_produktu,'left'),
                                 array( $format_img,'center'),
                                 array(((!empty($info['auction_date_start'])) ? date('d-m-Y H:i:s',strtotime($info['auction_date_start'])) : '-'),'center'),
                                 array($data_zakonczenia_allegro,'center'),
                                 array($ilosci_magazynu,'center'),
                                 array($info['auction_bids'],'center'),
                                 array($info['products_sold'],'center'),
                                 array($info['auction_hits'],'center'),
                                 array($warianty_img,'center'),
                                 array($status_img,'center'));
                                 
                unset($nr_aukcji, $tgm, $ilosci_magazynu);
                
                $tekst .= $listing_danych->pozycje($tablica);
                
                $tekst .= '<td class="rg_right">';
                
                $zmienne_do_przekazania = '?id_poz='.$info['allegro_id'];

                $tekst .= '<a href="allegro/allegro_aukcja_szczegoly.php'.$zmienne_do_przekazania.'"><img src="obrazki/zobacz.png" alt="Szczegóły aukcji" title="Szczegóły aukcji" /></a>';
                
                if ($info['synchronization'] == 0) {
                
                    if ( strtotime($info['products_date_end']) > ( time() - ( 60*60*24*30 )) ) {
                      $tekst .= '<a href="allegro/allegro_duplikuj_aukcje.php'.$zmienne_do_przekazania.'"><img src="obrazki/allegro_lapka.png" alt="Wystaw kolejną aukcję tego przedmiotu" title="Wystaw kolejną aukcję tego przedmiotu" /></a>';
                    } else {
                      $tekst .= '<img src="obrazki/allegro_lapka_off.png" alt="Aukcja jest za stara - nie można jej wznowić" title="Aukcja jest za stara - nie można jej wznowić" />';
                    }

                    if ( $info['auction_status'] == '1' ) {
                      $tekst .= '<a href="allegro/allegro_aukcja_zakoncz.php'.$zmienne_do_przekazania.'"><img src="obrazki/wyloguj.png" alt="Zakończ aukcje na Allegro" title="Zakończ aukcje na Allegro" /></a>';
                    } else {
                      $tekst .= '<img src="obrazki/wyloguj_off.png" alt="Aukcja zakończona" title="Aukcja zakończona" />';
                    }
                    
                    if ( $info['variants'] == '0' ) {
                      $tekst .= '<br /><br /><a href="allegro/allegro_aukcja_zaktualizuj_ilosc.php'.$zmienne_do_przekazania.'&ilosc='.$ilosc_magazyn.'"><img src="obrazki/powrot.png" alt="Zaktualizuj ilość przedmiotów na aukcji" title="Zaktualizuj ilość przedmiotów na aukcji" /></a>';
                    } else {
                      $tekst .= '<br /><br />';
                    }
                    
                }
                
                $tekst .= '<a href="allegro/allegro_aukcja_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Usuń aukcję z bazy danych" title="Usuń aukcję z bazy danych" /></a>';
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

            <div id="naglowek_cont">Obsługa aukcji - data ostatniej synchronizacji : <?php echo date("d-m-Y H:i:s", $allegro->polaczenie['CONF_LAST_SYNCHRONIZATION']); ?></div>             
            <?php
            if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {
              ?>
              <div style="float:right; margin:-32px 130px 0px 0px;"><a class="usun" href="allegro/allegro_logowanie.php?wyloguj=ok&strona=allegro_aukcje">Wyloguj z Allegro</a></div>
              <?php
            } else {
              ?>
              <div style="float:right; margin:-32px 130px 0px 0px;"><a class="dodaj" href="allegro/allegro_logowanie.php?strona=allegro_aukcje">Zaloguj do Allegro</a></div>
              <?php
            }
            ?>
            <div class="cl"></div>

            <div id="wyszukaj">
                <form action="allegro/allegro_aukcje.php" method="post" id="allegroForm" class="cmxform">

                    <div id="wyszukaj_text">
                        <span>Wyszukaj aukcje:</span>
                        <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="40" />
                    </div>  
                    
                    <div class="wyszukaj_select" style="margin-left:10px;">
                        <span>Status:</span>
                        <?php
                        $tablia_status= Array();
                        $tablia_status[] = array('id' => '0', 'text' => 'dowolny');
                        $tablia_status[] = array('id' => '1', 'text' => 'trwająca');
                        $tablia_status[] = array('id' => '2', 'text' => 'zakończona');
                        $tablia_status[] = array('id' => '3', 'text' => 'zakończona przed czasem');
                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                    </div>  

                    <div class="wyszukaj_select" style="margin-left:10px;">
                        <span>Format:</span>
                        <?php
                        $tablia_zrodlo= Array();
                        $tablia_zrodlo[] = array('id' => '0', 'text' => 'dowolny');
                        $tablia_zrodlo[] = array('id' => '1', 'text' => 'kup teraz');
                        $tablia_zrodlo[] = array('id' => '2', 'text' => 'licytacja');
                        echo Funkcje::RozwijaneMenu('szukaj_format', $tablia_zrodlo, ((isset($_GET['szukaj_format'])) ? $filtr->process($_GET['szukaj_format']) : '')); ?>
                    </div>  

                    <div class="cl" style="height:9px"></div>
                    <div class="wyszukaj_select">
                        <span  style="width:94px">Data końca:</span>
                        <input type="text" id="data_zakonczenia_od" name="szukaj_data_zakonczenia_od" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_od'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_zakonczenia_do" name="szukaj_data_zakonczenia_do" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_do'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_do']) : ''); ?>" size="10" class="datepicker" />
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
                  echo '<div id="wyszukaj_ikona"><a href="allegro/allegro_aukcje.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 

                <div style="clear:both"></div>
            </div> 

            <?php
            // sprawdza czy sa jakies aukcjie niezsynchronizowane
            $zapytanieSynch = "SELECT synchronization FROM allegro_auctions WHERE synchronization = 1";
            $sqlSynch = $db->open_query($zapytanieSynch);
            
            if ((int)$db->ile_rekordow($sqlSynch) > 0) {
                ?>
                
                <div id="brakSynchronizacji">             
                    <span>Należy wykonać synchronizację z Allegro - nie wszystkie aukcje mają aktualne dane</span>
                    <form action="allegro/allegro_synchronizuj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>" method="post" class="cmxform">
                      <input type="hidden" name="akcja" value="synchronizuj" />
                      <input type="hidden" name="powrot" value="allegro_aukcje" />
                      <input type="submit" class="przyciskBut" value="Pobierz aktualne dane o aukcjach z Allegro" />
                    </form>                
                </div>
                
                <div style="clear:both"></div>
                
                <?php
            }
            
            $db->close_query($sqlSynch);
            unset($zapytanieSynch);        
            ?>              
            
            <form action="allegro/allegro_aukcje_akcja.php" method="post" class="cmxform">
            
                <div id="sortowanie">
                    <span>Sortowanie: </span>
                    <a id="sort_a1" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a1">daty końca malejąco</a>
                    <a id="sort_a2" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a2">daty końca rosnąco</a>
                    <a id="sort_a3" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a3">numeru malejąco</a>
                    <a id="sort_a4" class="sortowanie" href="allegro/allegro_aukcje.php?sort=sort_a4">numeru rosnąco</a>
                </div>    

                <table style="width:1020px">
                      <tr>
                          <td style="width:100%;vertical-align:top" colspan="2">

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
                                  <option value="1">usuń zaznaczone aukcje z bazy</option>
                                  <option value="2">usuń zaznaczone aukcje z bazy i zakończ aukcje przed czasem</option>
                                  <option value="3">wystaw kolejne aukcje dla zaznaczonych produktów</option>
                                  <option value="4">zakończ zaznaczone aukcje przed czasem</option>
                                  <option value="5">zaktualizuj ilość produktów na aukcjach</option>
                                </select>
                              </div>
                              <div style="clear:both;"></div>
                            </div>
                          </td>
                       </tr>

                      <tr><td><div id="page"></div></td></tr>

                       <tr>
                          <td>
                            <div id="dolny_pasek_stron"></div>
                            <div id="pokaz_ile_pozycji"></div>
                            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
                  
                          </td>
                      </tr>

                    <?php if ($ile_pozycji > 0) { ?>
                    <tr>
                      <td style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Wykonaj" /></td>
                    </tr>
                    <?php } ?>                
                </table>

              </form>

            <div style="text-align:right;margin-top:10px;">
                <form action="allegro/allegro_synchronizuj.php" method="post" class="cmxform">

                   <div>
                       <input type="hidden" name="akcja" value="synchronizuj" />
                       <input type="hidden" name="powrot" value="allegro_aukcje" />
                       <input type="hidden" name="strona" value="<?php echo str_replace(".php", "", basename($_SERVER["SCRIPT_NAME"])); ?>" />
                       <input type="submit" class="przyciskBut" value="Pobierz aktualne dane o aukcjach z Allegro" />
                   </div>
              
                </form>
             </div>

            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('allegro/allegro_aukcje.php', $zapytanie, $ile_licznika, $ile_pozycji, 'allegro_id'); ?>
            //]]>
            </script>                

        </div>

        <?php 
        if ($ile_pozycji < 1) {
          ?>
          <form action="allegro/allegro_synchronizuj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>" method="post" class="cmxform">
            <input type="hidden" name="akcja" value="synchronizuj" />
            <input type="hidden" name="powrot" value="allegro_aukcje" />
            <input type="submit" class="przyciskBut" value="Pobierz aktualne dane o aukcjach z Allegro" />
          </form>
          <?php
        } 
        ?>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
