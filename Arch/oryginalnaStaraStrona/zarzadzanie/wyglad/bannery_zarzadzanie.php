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
    if (isset($_GET['grupa']) && !empty($_GET['grupa'])) {
        $szukana_wartosc = $filtr->process($_GET['grupa']);
        $warunki_szukania = " and b.banners_group = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    $zapytanie = "select * from banners b, banners_group bg where b.banners_group = bg.banners_group_code " . $warunki_szukania;
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }
    
    $db->close_query($sql);
    
    $zapytanie .= " order by banners_group, sort_order";
    
    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];
            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID','center'),
                                      array('Nazwa grupy','center'),
                                      array('Opis grupy','center'),
                                      array('Obrazek', 'center'),
                                      array('Rozdzielczość', 'center'),
                                      array('Nazwa banneru','center'),
                                      array('Dostępny dla języka','center'),
                                      array('Data dodania','center'),
                                      array('Ilość kliknięć','center'),
                                      array('Sort','center'),
                                      array('Status','center')
                                      );
                                      
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['banners_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['banners_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['banners_id'].'">';
                  }       

                  $tablica = array(array($info['banners_id'] . '<input type="hidden" name="id[]" value="'.$info['banners_id'].'" />','center'),
                                   array($info['banners_group'],'center'),
                                   array($info['banners_group_title'],'center'));  
                                   
                  if (!empty($info['banners_image'])) {
                    $tgm = Funkcje::pokazObrazek($info['banners_image'], $info['banners_image'], '80', '80');
                   } else { 
                    $tgm = '-';
                  }
                  $tablica[] = array($tgm,'center');

                  if (!empty($info['banners_image']) && is_file('../' . KATALOG_ZDJEC . '/'.$info['banners_image'])) {
                    if ( file_exists('../' . KATALOG_ZDJEC . '/'.$info['banners_image']) ) {
                      // wielkosc pliku
                      $Kb = filesize('../' . KATALOG_ZDJEC . '/'.$info['banners_image']);
                      
                      // ustalenie czy plik jest obrazkiem
                      //
                      $Rodzielczosc = '-';
                      if ( $Kb > 0 ) {
                          //
                          // czy plik jest obrazkiem
                          if (getimagesize('../' . KATALOG_ZDJEC . '/'.$info['banners_image']) != false) {
                              //
                              list($szerokosc, $wysokosc) = getimagesize('../' . KATALOG_ZDJEC . '/'.$info['banners_image']);
                              $tgm = $szerokosc . ' x ' . $wysokosc;
                              //
                          }
                      }                                            
                      // 
                    } else {
                      $tgm = 'brak pliku';
                    }
                  } else { 
                    $tgm = '-';
                  }
                  $tablica[] = array($tgm,'center');  
                  unset($szerokosc, $wysokosc, $Kb);                  
                  
                  $tablica[] = array($info['banners_title'],'center');
                  
                  $jaki_jezyk = 'wszystkie dostępne';
                  $jezyki = Funkcje::TablicaJezykow();
                  for ($w = 0, $c = count($jezyki); $w < $c; $w++) {
                       if ($jezyki[$w]['id'] == $info['languages_id']) {
                           $jaki_jezyk = $jezyki[$w]['text'];
                       }
                  }
                  $tablica[] = array($jaki_jezyk,'center');                  

                  $tablica[] = array(((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',strtotime($info['date_added'])) : '-'),'center','white-space:nowrap'); 
                  
                  $tablica[] = array($info['banners_clicked'],'center');
                  
                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['banners_id'].'" value="'.$info['sort_order'].'" class="sort_prod" />','center');
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['banners_id'];      
                  
                  // aktywana czy nieaktywna
                  if ($info['status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten banner jest aktywny'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten banner jest nieaktywny'; }              
                  $tablica[] = array('<a href="wyglad/bannery_zarzadzanie_status.php'. $zmienne_do_przekazania .'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');                          

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $tekst .= '<a href="wyglad/bannery_zarzadzanie_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  $tekst .= '<a href="wyglad/bannery_zarzadzanie_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Bannery</div>     
            
            <div id="wyszukaj">
                <form action="wyglad/bannery_zarzadzanie.php" method="post" id="poForm" class="cmxform"> 
                  
                <div class="wyszukaj_select">
                    <span>Wyświetl bannery tylko dla grupy:</span>                
                    <?php
                    $zapytanie_tmp = "select distinct * from banners_group order by banners_group_code asc";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica = array();
                    $tablica[] = array('id' => 0, 'text' => '-- dowolna --');
                    while ($infs = $sqls->fetch_assoc()) { 
                        $tablica[] = array('id' => $infs['banners_group_code'], 'text' => $infs['banners_group_code'] . ' - ' . $infs['banners_group_title']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);                   

                    echo Funkcje::RozwijaneMenu('grupa', $tablica, '', 'style="width:400px"');                    
                    ?>                    
                </div> 

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="wyglad/bannery_zarzadzanie.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
            </div>

            <?php         
            if (count($tablica) > 0) {
            ?>
                <div id="pozycje_ikon">
                    <div>
                        <a class="dodaj" href="wyglad/bannery_zarzadzanie_dodaj.php">dodaj nową pozycję</a>
                    </div>            
                </div>
            <?php
            } else {
                ?>
                <div id="pozycje_ikon">
                    <div>
                        <span class="ostrzezenie">Nie można dodać nowego banneru - nie są zdefiniowane grupy bannerów</span>
                    </div>
                </div>
                <?php
            }
            unset($tablica);
            ?>
            <div style="clear:both;"></div> 

            <form action="wyglad/bannery_zarzadzanie_akcja.php" method="post" class="cmxform">            
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('wyglad/bannery_zarzadzanie.php', $zapytanie, $ile_licznika, $ile_pozycji, 'banners_id'); ?>
            //]]>
            </script>             

            <?php if ($ile_pozycji > 0) { ?>
            <div><input type="submit" style="float:right" class="przyciskNon" value="Zapisz zmiany" /></div>
            <?php } ?> 
            
            <div class="cl"></div>

            </form>            
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>