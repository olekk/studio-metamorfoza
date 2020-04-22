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
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and pd.products_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    // jezeli jest nr kat lub id
    if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {
        $szukana_wartosc = $filtr->process($_GET['nrkat']);
        $warunki_szukania = " and (p.products_model like '%".$szukana_wartosc."%' or p.products_man_code like '%".$szukana_wartosc."%' or p.products_id = ".(int)$szukana_wartosc.")";
        unset($szukana_wartosc);
    }
    
    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = $filtr->process((int)$_GET['klienci']);
        $warunki_szukania .= " and p.customers_group_id = '".$id_klienta."'";
        unset($id_klienta);
    }      

    // jezeli jest zakres cen
    if (isset($_GET['cena_od']) && (float)$_GET['cena_od'] > 0) {
        $cena = $filtr->process((float)$_GET['cena_od']);
        $warunki_szukania .= " and p.products_price_tax >= '".$cena."'";
        unset($cena);
    }
    if (isset($_GET['cena_do']) && (float)$_GET['cena_do'] > 0) {
        $cena = $filtr->process((float)$_GET['cena_do']);
        $warunki_szukania .= " and p.products_price_tax <= '".$cena."'";
        unset($cena);
    }    

    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = $filtr->process((int)$_GET['kategoria_id']);
        $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = $filtr->process((int)$_GET['producent']);
        $warunki_szukania .= " and p.manufacturers_id = '".$id_producenta."'";
        unset($id_producenta);
    } 
    
    $warunki_szukania .= ' and ( p.star_status = "1" or p.star_date != "0000-00-00" or p.star_date_end != "0000-00-00" ) ';

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

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
                         p.star_status,
                         p.star_date,
                         p.star_date_end,
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,                         
                         p.products_currencies_id,                         
                         pd.products_id, 
                         pd.language_id, 
                         pd.products_name, 
                         m.manufacturers_id,
                         m.manufacturers_name
                  FROM products p
                         '.((isset($_GET['kategoria_id'])) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id' . $warunki_szukania;

    if (!isset($_GET['kategoria_id']) && (!isset($_GET['szukaj']) || (isset($_GET['szukaj']) && empty($_GET['szukaj'])))) {
        $ZapytanieDlaPozycji = 'SELECT COUNT("products_id") as ile_pozycji FROM products p ' . $warunki_szukania;
      } else {
        $ZapytanieDlaPozycji = $zapytanie;
    }

    $sql = $db->open_query($ZapytanieDlaPozycji);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    if (!isset($_GET['kategoria_id']) && (!isset($_GET['szukaj']) || (isset($_GET['szukaj']) && empty($_GET['szukaj'])))) {
        $row = $sql->fetch_assoc();
        $ile_pozycji = $row['ile_pozycji'];
      } else {
        $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    }
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    $sortowanie = '';
    //
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a17":
                $sortowanie = 'pd.products_name asc, p.products_id';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc, p.products_id';
                break;
            case "sort_a3":
                $sortowanie = 'p.star_status desc, pd.products_name, p.products_id';
                break;  
            case "sort_a4":
                $sortowanie = 'p.star_status asc, pd.products_name, p.products_id';
                break;                        
            case "sort_a7":
                $sortowanie = 'p.products_model asc, p.products_id';
                break;
            case "sort_a8":
                $sortowanie = 'p.products_model desc, p.products_id';
                break;  
            case "sort_a9":
                $sortowanie = 'p.products_price_tax asc, p.products_id';
                break;
            case "sort_a10":
                $sortowanie = 'p.products_price_tax desc, p.products_id';
                break;  
            case "sort_a11":
                $sortowanie = 'p.star_date asc, pd.products_name, p.products_id';
                break;
            case "sort_a12":
                $sortowanie = 'p.star_date desc, pd.products_name, p.products_id';
                break;                            
            case "sort_a13":
                $sortowanie = 'p.star_date_end asc, pd.products_name, p.products_id';
                break;
            case "sort_a14":
                $sortowanie = 'p.star_date_end desc, pd.products_name, p.products_id';
                break; 
            case "sort_a15":
                $sortowanie = 'p.products_id desc';
                break;
            case "sort_a16":
                $sortowanie = 'p.products_id asc';
                break;                          
        }            
    }  

    $zapytanie .= (($sortowanie != '') ? " order by ".$sortowanie : '');    

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
            $tablica_naglowek[] = array('Data rozpoczęcia','center');
            $tablica_naglowek[] = array('Data zakończenia','center');
            $tablica_naglowek[] = array('Cena','center');
            $tablica_naglowek[] = array('Widok','center');
            $tablica_naglowek[] = array('Status produktu','center');
            
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
                  
                  $tablica[] = array(((Funkcje::czyNiePuste($info['star_date'])) ? date('d-m-Y',strtotime($info['star_date'])) : '-'),'center','white-space:nowrap'); 

                  $tablica[] = array(((Funkcje::czyNiePuste($info['star_date_end'])) ? date('d-m-Y',strtotime($info['star_date_end'])) : '-'),'center','white-space:nowrap');                     
                  
                  $status_promocja = '';
                  if ( ((strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (strtotime($info['specials_date_end']) < time() && $info['specials_date_end'] != '0000-00-00 00:00:00') ) && $info['specials_status'] == '1' ) {                             
                      $status_promocja = '<div class="wylaczonaPromocja toolTipTop" title="Produkt nie jest wyświetlany jako promocja ze względu na datę rozpoczęcia lub zakończenia promocji"></div>';
                  }                        
                  
                  $tablica[] = array( $status_promocja . (((float)$info['products_old_price'] == 0) ? '' : '<div class="cena_promocyjna">' . $waluty->FormatujCene($info['products_old_price'], false, $info['products_currencies_id']) . '</div>') . 
                                     '<div class="cena">'.$waluty->FormatujCene($info['products_price_tax'], false, $info['products_currencies_id']).'</div>','center', 'white-space: nowrap'); 
                                     
                  unset($status_promocja);

                  // wyswietlany czy nie
                  if ($info['star_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Ten produkt jest wyświetlany jako hit'; } else { $obraz = 'aktywny_off.png'; $alt = 'Ten produkt nie jest wyświetlany jako hit'; }               
                  $tablica[] = array('<img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" />','center'); 
                  
                  $tablica[] = array((($wylacz_status == true) ? '<div class="wylKat" title="Kategoria do której należy produkt jest wyłączona">' : '') . '<input type="checkbox" style="border:0px" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' />' . (($wylacz_status == true) ? '</div>' : ''),'center');                                     
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                    
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['products_id'];      
                                      
                  $tekst .= '<td class="rg_right" style="width:10%">';
                  $tekst .= '<a href="nasz_hit/nasz_hit_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>'; 
                  $tekst .= '<a href="nasz_hit/nasz_hit_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
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
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php?typ=hit', 50, 350 );
          });
          //]]>
        </script>     

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Nasz hit</div>
            
            <div id="wyszukaj">
                <form action="nasz_hit/nasz_hit.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span style="width:110px">Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="40" />
                </div>  
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Producent:</span>                                     
                    <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : ''), ' style="width:120px"'); ?>
                </div>
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : ''), ' style="width:150px"'); 
                    unset($tablica);
                    ?>
                </div> 
                
                <div class="cl" style="height:9px"></div>
                
                <div class="wyszukaj_select">
                    <span style="width:110px">ID lub nr kat:</span>
                    <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? $filtr->process($_GET['nrkat']) : ''); ?>" size="30" />
                </div>                 
                
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Cena brutto:</span>
                    <input type="text" name="cena_od" value="<?php echo ((isset($_GET['cena_od'])) ? $filtr->process($_GET['cena_od']) : ''); ?>" size="10" /> do
                    <input type="text" name="cena_do" value="<?php echo ((isset($_GET['cena_do'])) ? $filtr->process($_GET['cena_do']) : ''); ?>" size="10" />
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
                  echo '<div id="wyszukaj_ikona"><a href="nasz_hit/nasz_hit.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>           
                
                <div style="clear:both"></div>
            </div>        
            
            <form action="nasz_hit/nasz_hit_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a1">brak</a>
            <a id="sort_a17" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a17">nazwy rosnąco</a>
            <a id="sort_a2" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a2">nazwy malejąco</a>
            <a id="sort_a7" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a7">nr katalogowy rosnąco</a>
            <a id="sort_a8" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a8">nr katalogowy malejąco</a> 
            <a id="sort_a9" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a9">cena rosnąco</a>
            <a id="sort_a10" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a10">cena malejąco</a>             
            <a id="sort_a3" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a3">widoczne</a>
            <a id="sort_a4" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a4">niewidoczne</a>
            <div style="margin-left:77px">
                <a id="sort_a11" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a11">daty rozpoczęcia od</a>
                <a id="sort_a12" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a12">daty rozpoczęcia do</a> 
                <a id="sort_a13" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a13">daty zakończenia od</a>
                <a id="sort_a14" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a14">daty zakończenia do</a>                 
                <a id="sort_a15" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a15">ID malejąco</a>
                <a id="sort_a16" class="sortowanie" href="nasz_hit/nasz_hit.php?sort=sort_a16">ID rosnąco</a>                
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
            // przycisk dodania nowego hitu
            ?>
            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="nasz_hit/nasz_hit_dodaj.php">dodaj nowy hit</a>
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
                                        <td class="lfp"><a href="nasz_hit/nasz_hit.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'nasz_hit\')" />' : '').'</td>
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
                                    podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','nasz_hit');
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
                                if (va == '5' || va == '6' || va == '7' || va == '8') {
                                    $("#wart").css('display','block');
                                   } else {
                                    $("#wart").css('display','none');
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
                            
                            <div id="wart" style="display:none">
                                Wartość (+ lub -): <input type="text" name="wartosc" size="4" value="" class="toolTipTop" title="Wpisz wartość liczbową" />
                            </div>
                                                        
                            <div id="akc">
                                Wykonaj akcje: 
                                <select name="akcja_dolna" id="akcja_dolna">
                                    <option value="0"></option>
                                    <option value="1">usuń nasz hit z zaznaczonych produktów</option>
                                    <?php
                                    /*
                                    <option value="2">zmień status zaznaczonych na nieaktywne</option>
                                    <option value="3">zmień status zaznaczonych na aktywne</option>
                                    */
                                    ?>
                                    <option value="4">usuń zaznaczone produkty</option>
                                    <option value="5">wyzeruj datę rozpoczęcia zaznaczonych</option>
                                    <option value="6">wyzeruj datę zakończenia zaznaczonych</option>
                                    <option value="7">dodaj/odejmij ilość dni do daty rozpoczęcia</option>
                                    <option value="8">dodaj/odejmij ilość dni do daty zakończenia</option>
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
            <?php Listing::pokazAjax('nasz_hit/nasz_hit.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_id'); ?>
            //]]>
            </script>              

        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
