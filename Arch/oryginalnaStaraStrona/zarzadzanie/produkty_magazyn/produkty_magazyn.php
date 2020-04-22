<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
    
    include('produkty_magazyn/produkty_magazyn_filtry.php');

    $zapytanie = 'SELECT DISTINCT
                         p.products_id, 
                         p.products_price_tax, 
                         p.products_old_price,
                         p.products_quantity, 
                         p.manufacturers_id,
                         p.products_image, 
                         p.products_price_tax,
                         p.products_model, 
                         p.products_man_code,
                         p.products_date_added, 
                         p.products_status,
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,                          
                         p.products_availability_id,
                         p.products_shipping_time_id,
                         p.products_currencies_id,
                         pd.products_id, 
                         pd.language_id, 
                         pd.products_name, 
                         m.manufacturers_id,
                         m.manufacturers_name,
                         pj.products_jm_quantity_type
                  FROM products p
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id' . $warunki_szukania;

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = 'SELECT p.products_id 
                         FROM products p
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id' . $warunki_szukania;
    
    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    include('produkty_magazyn/produkty_magazyn_sortowanie.php');  

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];

            $sql = $db->open_query($zapytanie);
            
            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID','center');
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa produktu', '', 'width:40%');
            $tablica_naglowek[] = array('Magazyn','center');
            $tablica_naglowek[] = array('Stan dostępności','center');
            $tablica_naglowek[] = array('Wysyłka','center');
            $tablica_naglowek[] = array('Cena','center');
            $tablica_naglowek[] = array('Status','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['products_id'].'" /><input type="hidden" name="id[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['products_id'],'center');
                  
                  // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                  $info['products_name'] = Funkcje::PodzielNazwe($info['products_name']);
                  $info['products_model'] = Funkcje::PodzielNazwe($info['products_model']);
                  
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

                  $tablica[] = array($tgm,'center');    

                  // dodatkowa zmienna do wylaczania mozliwosci zmiany statusu produktu jezeli kategoria
                  // do ktorej nalezy jest wylaczona
                  $wylacz_status = true;
                  
                  // nazwa produktu i kategorie do jakich jest przypisany
                  $do_jakich_kategorii_przypisany = '<span class="male_kat">Kategoria: ';
                  $kategorie = $db->open_query("select distinct categories_id from products_to_categories where products_id = '".(int)$info['products_id']."'");
                  //
                  if ( (int)$db->ile_rekordow($kategorie) > 0 ) {
                      while ($id_kategorii = $kategorie->fetch_assoc()) {
                          // okreslenie nazwy kategorii
                          if ((int)$id_kategorii['categories_id'] == '0') {
                              $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
                              $wylacz_status = false;
                            } else {
                              //
                              if ( isset($TablicaKategorii[(int)$id_kategorii['categories_id']]) ) {
                                  //
                                  $do_jakich_kategorii_przypisany .= '<span style="color:#ff0000">'.$TablicaKategorii[(int)$id_kategorii['categories_id']]['text'].'</span>, ';
                                  //
                                  if ($TablicaKategorii[(int)$id_kategorii['categories_id']]['status'] == '1') {
                                     $wylacz_status = false;
                                  }
                                  //
                              }
                              //
                          }
                      }
                    } else {
                      $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
                      $wylacz_status = false;
                  }
                  $do_jakich_kategorii_przypisany = substr($do_jakich_kategorii_przypisany,0,-2);
                  $do_jakich_kategorii_przypisany .= '</span>';
                  
                  $db->close_query($kategorie);
                  unset($kategorie);
                  
                  $nr_kat = '';
                  if (trim($info['products_model']) != '') {
                      $nr_kat = '<span class="male_nr_kat">Nr kat: <b>'.$info['products_model'].'</b></span>';
                  }
                  
                  $kod_producenta = '';
                  if (trim($info['products_man_code']) != '') {
                      $kod_producenta = '<span class="male_nr_kat">Kod prod: <b>'.$info['products_man_code'].'</b></span>';
                  }

                  // pobieranie danych o producencie
                  $prd = '';
                  if (trim($info['manufacturers_name']) != '') {                     
                      //
                      $prd = '<span class="male_producent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
                      //
                  }                    
                  
                  $tgm = '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $prd;
                  $tablica[] = array($tgm);
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $kod_producenta, $prd);
                  
                  // ilosc
                  // jezeli jednostka miary calkowita
                  if ( $info['products_jm_quantity_type'] == 1 ) {
                       $info['products_quantity'] = (int)$info['products_quantity'];
                  }                    
                  // musi sprawdzic czy nie jest wlaczony stan magazynowy cech i produkt nie ma cech
                  $InputIlosc = '<input type="text" name="ilosc_'.$info['products_id'].'" value="'.$info['products_quantity'].'" class="pole_edycja" onchange="zamien_krp(this,0,' . $info['products_jm_quantity_type'] . ')" />';
                  if (CECHY_MAGAZYN == 'tak') {
                      $cechy = "select distinct * from products_attributes where products_id = '".$info['products_id']."'";
                      $sqlc = $db->open_query($cechy); 
                      //
                      if ($db->ile_rekordow($sqlc) > 0) {
                          $InputIlosc = '<div class="iloscCechy chmurka" title="Ilość określana na podstawie sumy stanów magazynowych cech"><input type="text" name="ilosc_'.$info['products_id'].'" value="'.$info['products_quantity'].'" class="pole_edycja" disabled="disabled" /></div>';
                      }
                      //
                      $db->close_query($sqlc);
                  }
                  
                  $tablica[] = array($InputIlosc,'right');                   
                  
                  // stan dostepnosci
                  $tablica[] = array(Funkcje::RozwijaneMenu('dostepnosc_'.$info['products_id'], Produkty::TablicaDostepnosci('-- brak --'), $info['products_availability_id'], 'style="width:120px"'));
                  
                  // termin wysyłki
                  $tablica[] = array(Funkcje::RozwijaneMenu('wysylka_'.$info['products_id'], Produkty::TablicaCzasWysylki('-- brak --'), $info['products_shipping_time_id'], 'style="width:90px"'));     
                  
                  $status_promocja = '';
                  if ( ((strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (strtotime($info['specials_date_end']) < time() && $info['specials_date_end'] != '0000-00-00 00:00:00') ) && $info['specials_status'] == '1' ) {                             
                      $status_promocja = '<div class="wylaczonaPromocja toolTipTop" title="Produkt nie jest wyświetlany jako promocja ze względu na datę rozpoczęcia lub zakończenia promocji"></div>';
                  }                   

                  $tablica[] = array( $status_promocja . (((float)$info['products_old_price'] == 0) ? '' : '<div class="stara_cena">' . $waluty->FormatujCene($info['products_old_price'], false, $info['products_currencies_id']) . '</div>') . 
                                     '<div class="cena">'.$waluty->FormatujCene($info['products_price_tax'], false, $info['products_currencies_id']).'</div>','center', 'white-space: nowrap'); 
                                     
                  unset($status_promocja);

                  // aktywany czy nieaktywny
                  $tablica[] = array((($wylacz_status == true) ? '<div class="wylKat" title="Kategoria do której należy produkt jest wyłączona">' : '') . '<input type="checkbox" style="border:0px" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' />' . (($wylacz_status == true) ? '</div>' : ''),'center');                                     

                  $tekst .= $listing_danych->pozycje($tablica);
                    
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['products_id'];      
                                      
                  $tekst .= '<td class="rg_right" style="width:10%">';
                  $tekst .= '<a href="produkty_magazyn/produkty_magazyn_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>'; 
                  $tekst .= '<a href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'"><img src="obrazki/domek.png" alt="Przejdź do edycji produktu" title="Przejdź do edycji produktu" /></a>';
                  $tekst .= '</td></tr>';                  

                  unset($tablica);
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
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php', 50, 350 );
            
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
        
            <div id="naglowek_cont">Magazyn produktów</div>
            
            <div id="wyszukaj">
                <form action="produkty_magazyn/produkty_magazyn.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span style="width:110px">Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
                </div>  
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Producent:</span>                                     
                    <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : ''), ' style="width:120px"'); ?>
                </div>
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Stan dostępności:</span>                                         
                    <?php 
                    echo Funkcje::RozwijaneMenu('dostep', Produkty::TablicaDostepnosci('-- brak --'), ((isset($_GET['dostep'])) ? $filtr->process($_GET['dostep']) : ''), ' style="width:220px"'); 
                    ?>
                </div>  
                
                <div class="cl" style="height:9px"></div>
                
                <div class="wyszukaj_select">
                    <span style="width:110px">ID lub nr kat:</span>
                    <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? $filtr->process($_GET['nrkat']) : ''); ?>" size="20" />
                </div>                 
                
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Cena brutto:</span>
                    <input type="text" name="cena_od" value="<?php echo ((isset($_GET['cena_od'])) ? $filtr->process($_GET['cena_od']) : ''); ?>" size="10" /> do
                    <input type="text" name="cena_do" value="<?php echo ((isset($_GET['cena_do'])) ? $filtr->process($_GET['cena_do']) : ''); ?>" size="10" />
                </div>
                
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Termin wysyłki:</span>
                    <?php
                    echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --'), ((isset($_GET['wysylka'])) ? $filtr->process($_GET['wysylka']) : ''), ' style="width:140px"'); 
                    ?>
                </div>

                <div class="cl" style="height:9px"></div>
                
                <?php  
                //
                $tablica = array();
                $tablica[] = array('id' => '', 'text' => '-- dowolny --');
                $tablica[] = array('id' => 'tak', 'text' => 'aktywne');
                $tablica[] = array('id' => 'nie', 'text' => 'nieaktywne');
                //             
                ?>
                <div class="wyszukaj_select">
                    <span style="width:110px">Status:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('status', $tablica, ((isset($_GET['status'])) ? $filtr->process($_GET['status']) : ''), ' style="width:100px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>        

                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Ilość magazynu:</span>
                    <input type="text" name="ilosc_od" class="calkowita" value="<?php echo ((isset($_GET['ilosc_od'])) ? $filtr->process($_GET['ilosc_od']) : ''); ?>" size="4" /> do
                    <input type="text" name="ilosc_do" class="calkowita" value="<?php echo ((isset($_GET['ilosc_do'])) ? $filtr->process($_GET['ilosc_do']) : ''); ?>" size="4" />
                </div> 

                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Data dodania:</span>
                    <input type="text" id="data_dodania_od" name="szukaj_data_dodania_od" value="<?php echo ((isset($_GET['szukaj_data_dodania_od'])) ? $filtr->process($_GET['szukaj_data_dodania_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dodania_do" name="szukaj_data_dodania_do" value="<?php echo ((isset($_GET['szukaj_data_dodania_do'])) ? $filtr->process($_GET['szukaj_data_dodania_do']) : ''); ?>" size="8" class="datepicker" />
                </div>                  

                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['kategoria_id'])) { 
                    echo '<div><input type="hidden" name="kategoria_id" value="'.(int)$_GET['kategoria_id'].'" /></div>';
                }   
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="produkty_magazyn/produkty_magazyn.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>            
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="produkty_magazyn/produkty_magazyn_akcja.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>" method="post" class="cmxform">
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a1">brak</a>
            <a id="sort_a17" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a17">nazwy rosnąco</a>
            <a id="sort_a2" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a2">nazwy malejąco</a>
            <a id="sort_a7" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a7">nr katalogowy rosnąco</a>
            <a id="sort_a8" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a8">nr katalogowy malejąco</a> 
            <a id="sort_a9" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a9">cena rosnąco</a>
            <a id="sort_a10" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a10">cena malejąco</a>             
            <a id="sort_a3" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a3">aktywne</a>
            <a id="sort_a4" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a4">nieaktywne</a>
            <div style="margin-left:77px">
                <a id="sort_a5" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a5">daty dodania rosnąco</a>
                <a id="sort_a6" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a6">daty dodania malejąco</a> 
                <a id="sort_a11" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a11">ilość rosnąco</a>
                <a id="sort_a12" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a12">ilość malejąco</a>  
                <a id="sort_a13" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a13">ID malejąco</a>
                <a id="sort_a14" class="sortowanie" href="produkty_magazyn/produkty_magazyn.php?sort=sort_a14">ID rosnąco</a>                
            </div>
            </div>        
            
            <div style="clear:both;"></div>       

            <div id="pozycje_ikon">
                <div style="float:right">
                    <a class="export" href="produkty_magazyn/produkty_magazyn_export.php">eksportuj dane do pliku</a>
                </div>
            </div>
            <div style="clear:both;"></div>               
            
            <?php 
            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                $cSciezka = explode("_",$sciezka);
               } else {
                $cSciezka = array();
            }
            ?>

            <table style="width:1020px">
                <tr>
                    <td style="width:250px;vertical-align:top">
                    
                        <div class="okno_kateg">
                            <div class="okno_naglowek" style="padding:5px; padding-bottom:8px;">Kategorie</div>
                            <?php
                            echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                            $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                            for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                // sprawdza czy nie jest wybrana
                                $style = '';
                                if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                                    if ((int)$_GET['kategoria_id'] == $tablica_kat[$w]['id']) {
                                        $style = ' style="color:#ff0000"';
                                    }
                                }
                                //
                                echo '<tr>
                                        <td class="lfp"><a href="produkty_magazyn/produkty_magazyn.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'produkty_magazyn\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            if ( count($tablica_kat) == 0 ) {
                                 echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                            }                            
                            echo '</table>';
                            unset($tablica_kat,$podkategorie,$style);
                            ?>        

                            <?php 
                            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                                $cSciezka = explode("_",$sciezka);                    
                                if (count($cSciezka) > 1) {
                                    //
                                    $ostatnie = strRpos($sciezka,'_');
                                    $analiza_sciezki = str_replace("_",",",substr($sciezka,0,$ostatnie));
                                    ?>
                                    <script type="text/javascript">
                                    //<![CDATA[            
                                    podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','produkty_magazyn');
                                    //]]>
                                    </script>
                                <?php
                                unset($sciezka,$cSciezka);
                                }
                            } ?>
                        </div>
                        
                    </td>
                    <td style="width:760px;vertical-align:top;padding-left:10px">
                    
                        <div id="wynik_zapytania" style="width:760px"></div>
                        <div id="aktualna_pozycja">1</div>
                        
                        <script type="text/javascript">
                        //<![CDATA[
                        $(document).ready(function() {
                            $("#akcja_dolna").change( function () {
                                var va = $("#akcja_dolna").val();
                                if (va == '4') {
                                    $("#wart_dostepnosc").css('display','block');
                                   } else {
                                    $("#wart_dostepnosc").css('display','none');
                                }
                                if (va == '5') {
                                    $("#wart_wysylka").css('display','block');
                                   } else {
                                    $("#wart_wysylka").css('display','none');
                                }                                
                            });
                        });
                        //]]>
                        </script>                         
                        
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
                                    <?php
                                    /*
                                    <option value="1">zmień status zaznaczonych na nieaktywne</option>
                                    <option value="2">zmień status zaznaczonych na aktywne</option>
                                    */
                                    ?>
                                    <option value="3">usuń zaznaczone produkty</option>
                                    <option value="4">zmień stan dostępności zaznaczonych</option>
                                    <option value="5">zmień termin wysyłki zaznaczonych</option>
                                </select>
                            </div>
                            <div style="clear:both;"></div>
                            
                            <div id="wart_dostepnosc" style="display:none">
                                Stan dostępności: <?php echo Funkcje::RozwijaneMenu('dostepnosc', Produkty::TablicaDostepnosci('-- brak --')); ?>
                            </div>
                            
                            <div id="wart_wysylka" style="display:none">
                                Termin wysyłki: <?php echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --')); ?>
                            </div>                             
                            
                        </div>                           
                        
                        <div id="dolny_pasek_stron"></div>
                        <div id="pokaz_ile_pozycji"></div>
                        <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
                        
                        <?php if ($ile_pozycji > 0) { ?>
                        <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
                        <?php } ?>                          
                        
                    </td>
                </tr>

            </table>
            
            </form>

            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('produkty_magazyn/produkty_magazyn.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_id'); ?>
            //]]>
            </script>        
                
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
