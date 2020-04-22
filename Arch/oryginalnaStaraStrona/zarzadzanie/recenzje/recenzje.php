<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['niezatwierdzone']) ) {
     //
     unset($_SESSION['filtry']['recenzje.php']);
     $_SESSION['filtry']['recenzje.php']['zatwierdzone'] = 2;
     //
     Funkcje::PrzekierowanieURL('recenzje.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and pd.products_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    // jezeli jest nr kat lub id
    if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {
        $szukana_wartosc = $filtr->process($_GET['nrkat']);
        $warunki_szukania = " and (p.products_model like '%".$szukana_wartosc."%' or p.products_id = ".(int)$szukana_wartosc.")";
        unset($szukana_wartosc);
    }
    
    // jezeli jest wybrana grupa klienta
    if (isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] > 0) {
        if ((int)$_GET['zatwierdzone'] == 1) {
            $warunki_szukania .= " and r.approved = '1'";
        }
        if ((int)$_GET['zatwierdzone'] == 2) {
            $warunki_szukania .= " and r.approved = '0'";
        }        
    }      

    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = $filtr->process((int)$_GET['kategoria_id']);
        $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
    
    $zapytanie = 'SELECT DISTINCT
                         p.products_id, 
                         p.products_image, 
                         p.products_model, 
                         p.products_man_code,
                         p.products_status,
                         pd.products_id, 
                         pd.language_id, 
                         pd.products_name,
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,                         
                         r.reviews_id,
                         r.products_id,
                         r.customers_id,
                         r.customers_name,
                         r.reviews_rating,
                         r.date_added,
                         r.approved,
                         rd.reviews_text
                  FROM reviews r
                         LEFT JOIN reviews_description rd ON rd.reviews_id = r.reviews_id
                         LEFT JOIN products p ON p.products_id = r.products_id
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '"
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '') . $warunki_szukania;   


    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a11":
                $sortowanie = 'pd.products_name asc, p.products_id';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc, p.products_id';
                break;
            case "sort_a3":
                $sortowanie = 'p.products_status desc, pd.products_name, p.products_id';
                break;  
            case "sort_a4":
                $sortowanie = 'p.products_status asc, pd.products_name, p.products_id';
                break;                        
            case "sort_a7":
                $sortowanie = 'p.products_model asc, p.products_id';
                break;
            case "sort_a8":
                $sortowanie = 'p.products_model desc, p.products_id';
                break;    
            case "sort_a1":
                $sortowanie = 'r.date_added desc, pd.products_name, p.products_id';
                break;
            case "sort_a12":
                $sortowanie = 'r.date_added asc, pd.products_name, p.products_id';
                break;                            
            case "sort_a13":
                $sortowanie = 'r.approved asc, pd.products_name, p.products_id';
                break;
            case "sort_a14":
                $sortowanie = 'r.approved desc, pd.products_name, p.products_id';
                break; 
            case "sort_a15":
                $sortowanie = 'p.products_id desc';
                break;
            case "sort_a16":
                $sortowanie = 'p.products_id asc';
                break;                          
        }            
    } else { $sortowanie = 'r.date_added desc, pd.products_name, p.products_id'; } 

    $zapytanie .= " order by ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];
                        
            $sql = $db->open_query($zapytanie);
            
            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID opinii','center');
            $tablica_naglowek[] = array('ID prod','center');
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa produktu', '', 'width:30%');
            $tablica_naglowek[] = array('Data dodania / Wystawił / Ocena / Treść recenzji', '', 'width:60%');
            $tablica_naglowek[] = array('Zatwier- dzona','center');
            $tablica_naglowek[] = array('Status produktu','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['reviews_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['reviews_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['reviews_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['reviews_id'].'" /><input type="hidden" name="id[]" value="'.$info['reviews_id'].'" /><input type="hidden" name="id_prod[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['reviews_id'],'center');
                  
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
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $prd);
                  
                  // jezeli wystawiajacy to klient sklepu to bedzie to link do klienta
                  if ($info['customers_id'] > 0) {
                      $Klient = '<a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">' . $info['customers_name'] . '</a>';
                    } else {
                      $Klient = '<span>' . $info['customers_name'] . '</span>';
                  }
                  
                  // data dodania recenzji
                  $tablica[] = array('<div class="data_dodania">Data dodania: <span>'.((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y H:i',strtotime($info['date_added'])) : '-').'</span></div>' .
                                     '<div class="wystawil">Wystawił: '.$Klient.'</div>' .
                                     '<div class="ocena">Ocena: <img src="obrazki/recenzje/star_'.$info['reviews_rating'].'.png" alt="Ocena '.$info['reviews_rating'].'/5" title="Ocena '.$info['reviews_rating'].'/5" /></div>' .
                                     '<div class="komentarz">'.$info['reviews_text'].'</div>');
                        
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['reviews_id'];
                  // jezeli jest klient
                  if ( $info['customers_id'] > 0 ) {
                      $zmienne_do_przekazania .= '&amp;id=' . $info['customers_id'];      
                  }
                  
                  // zatwierdzona czy nie
                  if ($info['approved'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ta recenzja jest zatwierdzona'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ta recenzja nie jest zatwierdzona'; }               
                  $tablica[] = array('<a href="recenzje/recenzje_status.php'.$zmienne_do_przekazania.'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');                    
                  
                  // jezeli promocja ma date i data poczatkowa jest wieksza od dzisiejszej lub koncowa wczesniejsza od dzisiejszej to wylacza checkboxa zmiany statusu - produkt musi byc wylaczony
                  $Wylacz = '';
                  $TekstWylacz = '';
                  if ( ((strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (strtotime($info['specials_date_end']) < time()  && $info['specials_date_end'] != '0000-00-00 00:00:00') ) && $info['products_status'] == '0') {
                     $Wylacz = ' disabled="disabled"';
                     $TekstWylacz = ' title="Produkt nieaktywny ze względu na datę rozpoczęcia lub zakończenia promocji"';
                  }
                  
                  $tablica[] = array((($wylacz_status == true) ? '<div class="wylKat" title="Kategoria do której należy produkt jest wyłączona">' : '') . '<input type="checkbox" style="border:0px" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' ' . $TekstWylacz . $Wylacz .  ' />' . (($wylacz_status == true) ? '</div>' : ''),'center');                                  

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $zmienne_do_przekazania = '?id_poz='.$info['reviews_id'];     

                  $tekst .= '<td class="rg_right" style="width:10%">';
                  $tekst .= '<a href="recenzje/recenzje_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>'; 
                  $tekst .= '<a href="recenzje/recenzje_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
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
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php?typ=recenzje', 50, 350 );
          });
          //]]>
        </script>    

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Recenzje produktów</div>
            
            <div id="wyszukaj">
                <form action="recenzje/recenzje.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span>Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="35" />
                </div>  
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>ID lub nr kat:</span>
                    <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? $filtr->process($_GET['nrkat']) : ''); ?>" size="25" />
                </div>                 
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Zatwierdzone:</span>   
                    <select name="zatwierdzone">
                        <option value="0" <?php echo ((!isset($_GET['zatwierdzone'])) ? 'selected="selected"' : ''); ?>>-- wszystkie --</option>
                        <option value="1" <?php echo ((isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] == 1) ? 'selected="selected"' : ''); ?>>zatwierdzone</option>
                        <option value="2" <?php echo ((isset($_GET['zatwierdzone']) && (int)$_GET['zatwierdzone'] == 2) ? 'selected="selected"' : ''); ?>>niezatwierdzone</option>
                    </select>
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
                  echo '<div id="wyszukaj_ikona"><a href="recenzje/recenzje.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>            
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="recenzje/recenzje_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a11" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a11">nazwy rosnąco</a>
            <a id="sort_a2" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a2">nazwy malejąco</a>
            <a id="sort_a7" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a7">nr katalogowy rosnąco</a>
            <a id="sort_a8" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a8">nr katalogowy malejąco</a>           
            <a id="sort_a3" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a3">aktywne</a>
            <a id="sort_a4" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a4">nieaktywne</a>
            <a id="sort_a1" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a1">daty dodania rosnąco</a>
            <a id="sort_a12" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a12">daty dodania malejąco</a>             
            <div style="margin-left:77px">
                <a id="sort_a13" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a13">zatwierdzone</a>
                <a id="sort_a14" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a14">niezatwierdzone</a>                 
                <a id="sort_a15" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a15">ID malejąco</a>
                <a id="sort_a16" class="sortowanie" href="recenzje/recenzje.php?sort=sort_a16">ID rosnąco</a>                
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
            
            <?php
            // przycisk dodania nowej recenzji
            ?>
            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="recenzje/recenzje_dodaj.php">dodaj nową recenzję</a>
                </div>
                <div id="legenda" style="float:right">
                    <span class="klient"> klient jest zarejestrowany w sklepie</span>
                </div>                  
            </div>
            
            <div style="clear:both;"></div>            

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
                                        <td class="lfp"><a href="recenzje/recenzje.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'recenzje\')" />' : '').'</td>
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
                                    podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','recenzje');
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
                                    <option value="1">usuń zaznaczone recenzje</option>
                                </select>
                            </div>
                            <div style="clear:both;"></div>
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
            <?php Listing::pokazAjax('recenzje/recenzje.php', $zapytanie, $ile_licznika, $ile_pozycji, 'reviews_id'); ?>
            //]]>
            </script>              
 
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
