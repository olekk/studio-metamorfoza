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
        $warunki_szukania = " AND aso.auction_id LIKE '%".$szukana_wartosc."%' OR t.transaction_id LIKE '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }
    
    if (isset($_GET['szukaj_nick']) && $_GET['szukaj_nick'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_nick']);
        $warunki_szukania = " AND aso.buyer_name LIKE '%".$szukana_wartosc."%' OR aso.buyer_email_address LIKE '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }    

    if ( isset($_GET['szukaj_data_zakonczenia_od']) && $_GET['szukaj_data_zakonczenia_od'] != '' ) {
        $szukana_wartosc = strtotime($filtr->process($_GET['szukaj_data_zakonczenia_od']));
        $warunki_szukania .= " and aso.auction_buy_date >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_data_zakonczenia_do']) && $_GET['szukaj_data_zakonczenia_do'] != '' ) {
        $szukana_wartosc = strtotime($filtr->process($_GET['szukaj_data_zakonczenia_do']));
        $warunki_szukania .= " and aso.auction_buy_date <= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_formularz']) && $_GET['szukaj_formularz'] != '0' ) {
        $szukana_wartosc = (int)$_GET['szukaj_formularz'];
        if ( $szukana_wartosc == 2 ) $szukana_wartosc = 0;
        $warunki_szukania .= " and aso.auction_postbuy_forms = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_zamowienie']) && $_GET['szukaj_zamowienie'] != '0' ) {
        $szukana_wartosc = (int)$_GET['szukaj_zamowienie'];
        if ( $szukana_wartosc == 1 ) {
             $warunki_szukania .= " and ((aso.orders_id != '' and aso.orders_id != '0') or (t.orders_id != '' and t.orders_id != '0'))";
        }
        if ( $szukana_wartosc == 2 ) {
             $warunki_szukania .= " and ((aso.orders_id = '' or aso.orders_id = '0') and (t.orders_id = '' or t.orders_id = '0'))";
        }        
        unset($szukana_wartosc);
    }    
    
    if ( isset($_GET['szukaj_komentarz']) && $_GET['szukaj_komentarz'] != '0' ) {
        $szukana_wartosc = (int)$_GET['szukaj_komentarz'];
        if ( $szukana_wartosc == 2 ) $szukana_wartosc = 0;
        $warunki_szukania .= " and aso.auction_comments = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "
      SELECT aso.allegro_auction_id, aso.auction_id, aso.buyer_name, aso.buyer_id, aso.buyer_email_address, aso.auction_postbuy_forms, aso.auction_buy_date, aso.auction_price, aso.auction_quantity, aso.orders_id AS ordersAcution, t.transaction_id, t.post_buy_form_it_quantity, t.orders_id AS ordersTrans, a.products_name, a.products_id, a.products_stock_attributes, t.post_buy_form_pay_status, aso.auction_comments 
        FROM allegro_auctions_sold aso 
        LEFT JOIN allegro_transactions t ON t.auction_id = aso.auction_id AND aso.buyer_id = t.buyer_id
        LEFT JOIN allegro_auctions a ON a.auction_id = aso.auction_id
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
                $sortowanie = 'auction_buy_date DESC, auction_id, buyer_id';
                break;
            case "sort_a2":
                $sortowanie = 'auction_buy_date ASC, auction_id, buyer_id';
                break;                 
            case "sort_a3":
                $sortowanie = 'buyer_id desc';
                break;
            case "sort_a4":
                $sortowanie = 'buyer_id asc';
                break;                 
        }            
    } else { $sortowanie = 'auction_buy_date DESC, auction_id, buyer_id'; }    

    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];   

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Info', 'center'),
                                      array('Akcja','center'),
                                      array('Numer aukcji','center'),
                                      array('Produkt','center'),
                                      array('Kupujący','center'),
                                      array('E-mail','center'),
                                      array('Ilość','center'),
                                      array('Data<br />zakupu','center'),
                                      array('Cena','center'),
                                      array('Formularz','center'),
                                      array('Numer<br />transakcji','center'),
                                      array('Numer<br />zam.','center'),
                                      array('Komentarz','center')
                                      );
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['allegro_auction_id']) {
                   $tekst .= '<tr class="pozycja_on" id="sk_'.$info['allegro_auction_id'].'">';
                 } else {
                   $tekst .= '<tr class="pozycja_off" id="sk_'.$info['allegro_auction_id'].'">';
                }        
                $link = '';

                $numer_zamowienia = '---';
                if ( $info['ordersAcution'] != '' && $info['ordersAcution'] != '0' ) {
                  $numer_zamowienia = '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['ordersAcution'].'" >'.$info['ordersAcution'].'</a>';
                }
                if ( $info['ordersTrans'] != '' && $info['ordersTrans'] != '0' ) {
                  $numer_zamowienia = '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['ordersTrans'].'" >'.$info['ordersTrans'].'</a>';
                }
                $tablica = Array();

                $tablica[] = array('<div id="aukcja_'.$info['allegro_auction_id'].'_'.$info['transaction_id'].'" class="zmzoom_aukcja"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>','center');

                $dane = array( 'aukcja_id' => $info['auction_id'],
                               'buyer_id' => $info['buyer_id'],
                               'id_poz' => $info['allegro_auction_id'],
                               'postform' => $info['auction_postbuy_forms'],
                               'transaction_id' => ((empty($info['transaction_id'])) ? '0' : $info['transaction_id']) );
                $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.htmlspecialchars(serialize($dane), ENT_QUOTES, 'UTF-8').'" />','center');
                unset($dane);
                
                $link = '';
                if ( Allegro::SerwerAllegro() == 'nie' ) {
                  $link = 'http://allegro.pl/item' .  $info['auction_id'] . '_webapi.html';
                } else {
                  $link = 'http://allegro.pl.webapisandbox.pl/show_item.php?item='.$info['auction_id'];
                }                  
                
                $tablica[] = array('<a href="' . $link . '">' . $info['auction_id'] . '</a>','center');
                
                unset($link);
                
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
                  
                }

                $tablica[] = array('<b><a href="produkty/produkty_edytuj.php?id_poz='.$info['products_id'].'">' . $info['products_name'] . '</a></b>' . ((!empty($wyswietl_cechy)) ? $wyswietl_cechy : '') . '','left', 'width:30%');
                $tablica[] = array($info['buyer_name'],'center');
                $tablica[] = array('<a href="mailto:'.$info['buyer_email_address'].'" >'.$info['buyer_email_address'].'</a>','center');
                $tablica[] = array( round(( $info['auction_postbuy_forms'] == '1' ? $info['post_buy_form_it_quantity'] : $info['auction_quantity'] ),0),'center');
                $tablica[] = array( date('d-m-Y H:i:s',$info['auction_buy_date']),'center');
                $tablica[] = array($waluty->FormatujCene($info['auction_price']),'center', 'white-space:nowrap;');

                $stan_tranzakcji = '<img src="obrazki/aktywny_off.png" alt="Kupujący nie wypełnił formularza pozakupowego" title="Kupujący nie wypełnił formularza pozakupowego" />';
                if ( $info['auction_postbuy_forms'] == '1' ) {
                  $stan_tranzakcji = '<img src="obrazki/aktywny_on.png" alt="Kupujący wypełnił formularz pozakupowy" title="Kupujący wypełnił formularz pozakupowy" />';
                  if ( $info['post_buy_form_pay_status'] == 'Anulowana' ) {
                    $stan_tranzakcji = '<img src="obrazki/uwaga.png" alt="Formularz pozakupowy został anulowany" title="Formularz pozakupowy został anulowany" />';
                  }
                }

                $tablica[] = array($stan_tranzakcji,'center');
                $tablica[] = array($info['transaction_id'],'center');
                $tablica[] = array($numer_zamowienia,'center');
                                 
                $komentarz  = '---';
                if ( $info['auction_comments'] == '1' ) {
                  $komentarz  = '<img src="obrazki/aktywny_on.png" alt="Do aukcji wystawiony został komentarz" title="Do aukcji wystawiony został komentarz" />';
                }
                $tablica[] = array($komentarz,'center');

                $tekst .= $listing_danych->pozycje($tablica);
                
                $tekst .= '<td class="rg_right">';
                $postbuyform = $info['auction_postbuy_forms'];

                $zmienne_do_przekazania = '?id_poz='.$info['allegro_auction_id'];
                $tekst .= '<a href="allegro/allegro_utworz_zamowienie.php'.$zmienne_do_przekazania.'&amp;postform='.$postbuyform.($postbuyform == '1' ? '&amp;transaction_id='.$info['transaction_id'] : '').'"><img src="obrazki/import.png" alt="Utwórz zamówienie" title="Utwórz zamówienie" /></a>';
                
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
          $(document).ready(function() {
               $('#akcja_dolna').change(function() {
                 if ( this.value == '0' || this.value == '2' ) {
                   $("#page").load('allegro/blank.php');
                 }
                 if ( this.value == '1' ) {
                   $("#page").load('allegro/allegro_wystaw_komentarze.php');
                 }
               });
          });
        </script>
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
            
            <div id="naglowek_cont">Obsługa sprzedaży - data ostatniej synchronizacji : <?php echo date("d-m-Y H:i:s", $allegro->polaczenie['CONF_LAST_SYNCHRONIZATION']); ?></div>

            <?php
            if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {
              ?>
              <div style="float:right; margin:-32px 130px 0px 0px;"><a class="usun" href="allegro/allegro_logowanie.php?wyloguj=ok&strona=allegro_sprzedaz">Wyloguj z Allegro</a></div>
              <?php
            } else {
              ?>
              <div style="float:right; margin:-32px 130px 0px 0px;"><a class="dodaj" href="allegro/allegro_logowanie.php?strona=allegro_sprzedaz">Zaloguj do Allegro</a></div>
              <?php
            }
            ?>
            <div class="cl"></div>

            <div id="wyszukaj">
                <form action="allegro/allegro_sprzedaz.php" method="post" id="allegroForm" class="cmxform">

                      <div id="wyszukaj_text">
                          <span style="width:180px">Wyszukaj aukcję lub transakcję:</span>
                          <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="15" />
                      </div>  
                      
                      <div class="wyszukaj_select" style="margin-left:10px;">
                          <span>Data sprzedaży:</span>
                          <input type="text" id="data_zakonczenia_od" name="szukaj_data_zakonczenia_od" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_od'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_od']) : ''); ?>" size="8" class="datepicker" />&nbsp;do&nbsp;
                          <input type="text" id="data_zakonczenia_do" name="szukaj_data_zakonczenia_do" value="<?php echo ((isset($_GET['szukaj_data_zakonczenia_do'])) ? $filtr->process($_GET['szukaj_data_zakonczenia_do']) : ''); ?>" size="8" class="datepicker" />
                      </div>  

                      <div class="wyszukaj_select" style="margin-left:10px;">
                          <span>Formularz:</span>
                          <?php
                          $tablica = Array();
                          $tablica[] = array('id' => '0', 'text' => 'wszystkie');
                          $tablica[] = array('id' => '1', 'text' => 'tak');
                          $tablica[] = array('id' => '2', 'text' => 'nie');
                          echo Funkcje::RozwijaneMenu('szukaj_formularz', $tablica, ((isset($_GET['szukaj_formularz'])) ? $filtr->process($_GET['szukaj_formularz']) : ''));
                          unset($tablica);
                          ?>
                      </div> 

                      <div class="cl" style="height:9px"></div>                      
                      
                      <div class="wyszukaj_select">
                          <span style="width:180px">Adres email lub nick:</span>
                          <input type="text" name="szukaj_nick" id="szukaj_nick" value="<?php echo ((isset($_GET['szukaj_nick'])) ? $filtr->process($_GET['szukaj_nick']) : ''); ?>" size="25" />
                      </div> 
                      
                      <div class="wyszukaj_select" style="margin-left:10px;">
                          <span>Zamówienie:</span>
                          <?php
                          $tablica = Array();
                          $tablica[] = array('id' => '0', 'text' => 'wszystkie');
                          $tablica[] = array('id' => '1', 'text' => 'tak');
                          $tablica[] = array('id' => '2', 'text' => 'nie');
                          echo Funkcje::RozwijaneMenu('szukaj_zamowienie', $tablica, ((isset($_GET['szukaj_zamowienie'])) ? $filtr->process($_GET['szukaj_zamowienie']) : ''));
                          unset($tablica);
                          ?>
                      </div> 
                      
                      <div class="wyszukaj_select" style="margin-left:10px;">
                          <span>Komentarz:</span>
                          <?php
                          $tablica = Array();
                          $tablica[] = array('id' => '0', 'text' => 'wszystkie');
                          $tablica[] = array('id' => '1', 'text' => 'tak');
                          $tablica[] = array('id' => '2', 'text' => 'nie');
                          echo Funkcje::RozwijaneMenu('szukaj_komentarz', $tablica, ((isset($_GET['szukaj_komentarz'])) ? $filtr->process($_GET['szukaj_komentarz']) : ''));
                          unset($tablica);
                          ?>
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
                  echo '<div id="wyszukaj_ikona"><a href="allegro/allegro_sprzedaz.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                

                <div style="clear:both"></div>
            </div> 

            <form action="allegro/allegro_akcja.php" method="post" class="cmxform">

                <div id="sortowanie">
                    <span>Sortowanie: </span>
                    <a id="sort_a1" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a1">daty końca rosnąco</a>
                    <a id="sort_a2" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a2">daty końca malejąco</a>
                    <a id="sort_a3" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a3">kupujący malejąco</a>
                    <a id="sort_a4" class="sortowanie" href="allegro/allegro_sprzedaz.php?sort=sort_a4">kupujący rosnąco</a>
                </div>             

              <table style="width:1020px" class="sprzedazAllegro">
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
                              <option value="1">napisz komentarz do zaznaczonych</option>
                              <option value="2">utwórz zamówienia dla zaznaczonych aukcji</option>
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
                <form action="allegro/allegro_synchronizuj.php<?php echo Funkcje::Zwroc_Get(array('id_poz')); ?>" method="post" class="cmxform">
                
                  <div>
                      <input type="hidden" name="akcja" value="synchronizuj" />
                      <input type="hidden" name="powrot" value="allegro_sprzedaz" />
                      <input type="hidden" name="strona" value="<?php echo str_replace(".php", "", basename($_SERVER["SCRIPT_NAME"])); ?>" />
                      <input type="submit" class="przyciskBut" value="Pobierz aktualne dane o aukcjach z Allegro" />
                  </div>
                  
                </form>
            </div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('allegro/allegro_sprzedaz.php', $zapytanie, $ile_licznika, $ile_pozycji, 'allegro_id'); ?>
            //]]>
            </script>                

        </div>

        <?php 
        if ($ile_pozycji < 1) {
          ?>
          <form action="allegro/allegro_synchronizuj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>" method="post" class="cmxform">
          <input type="hidden" name="akcja" value="synchronizuj" />
          <!-- <input type="submit" class="przyciskBut" value="Pobierz aktualne dane o aukcjach z Allegro" /> -->
          </form>
          <?php
        } 
        ?>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

} ?>
