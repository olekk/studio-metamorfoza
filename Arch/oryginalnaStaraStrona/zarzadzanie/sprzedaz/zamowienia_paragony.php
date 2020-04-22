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
        $warunki_szukania = " AND receipts_nr = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_zamowienia_od']) && $_GET['szukaj_data_zamowienia_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_zamowienia_od'] . ' 00:00:00')));
        $warunki_szukania .= " AND receipts_date_generated >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_zamowienia_do']) && $_GET['szukaj_data_zamowienia_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_zamowienia_do'] . ' 23:59:59')));
        $warunki_szukania .= " AND receipts_date_generated <= '".$szukana_wartosc."'";
    }
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }    

    $zapytanie = "SELECT * FROM receipts " . $warunki_szukania;

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
                $sortowanie = 'receipts_date_generated desc, cast(receipts_nr as unsigned) desc';
                break;
            case "sort_a2":
                $sortowanie = 'receipts_date_generated asc, cast(receipts_nr as unsigned) asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'receipts_date_generated desc';
                break;
            case "sort_a4":
                $sortowanie = 'receipts_date_generated asc';
                break;
            case "sort_a5":
                $sortowanie = 'orders_id desc';
                break;
            case "sort_a6":
                $sortowanie = 'orders_id asc';
                break;                  
        }            
    } else { $sortowanie = 'receipts_date_generated desc, cast(receipts_nr as unsigned) desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Numer paragonu', 'center'),            
                                      array('Numer zamówienia', 'center'),
                                      array('Data zamówienia', 'center'),
                                      array('Wartość zamówienia', 'center'),
                                      array('Data sprzedaży', 'center'),
                                      array('Data wystawienia', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['receipts_id']) {
                     $tekst .= '<tr class="pozycja_on">';
                   } else {
                     $tekst .= '<tr class="pozycja_off">';
                  }        

                  $tablica = array();

                  $tablica[] = array(NUMER_PARAGONU_PREFIX . str_pad($info['receipts_nr'], FAKTURA_NUMER_ZERA_WIODACE, 0, STR_PAD_LEFT) . strftime(NUMER_PARAGONU_SUFFIX, strtotime($info['receipts_date_generated'])),'center');
                  $tablica[] = array($info['orders_id'],'center');
                  
                  $zamowienie = new Zamowienie($info['orders_id']);
                  $tablica[] = array(date('d-m-Y',strtotime( $zamowienie->info['data_zamowienia'] )),'center');
                  $tablica[] = array($zamowienie->info['wartosc_zamowienia'],'center');
                  unset($zamowienie);
                  
                  $tablica[] = array(date('d-m-Y',strtotime($info['receipts_date_sell'])),'center');
                  $tablica[] = array(date('d-m-Y',strtotime($info['receipts_date_generated'])),'center');                  

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $tekst .= '<a href="sprzedaz/zamowienia_paragon_pdf.php'.$zmienne_do_przekazania.'&amp;id='.$info['receipts_id'].'"><img src="obrazki/pdf.png" alt="Wydrukuj paragon" title="Wydrukuj paragon" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'"><img src="obrazki/lista_wojewodztw.png" alt="Pokaż szczegóły zamówienia" title="Pokaż szczegóły zamówienia" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_paragon_usun.php?id_poz='.(int)$info['receipts_id'].'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  
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
            
            <div id="naglowek_cont">Zestawienie paragonów</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia_paragony.php" method="post" id="paragonyForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj paragon nr:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? $filtr->process($_GET['szukaj']) : ''); ?>" size="10" />
                </div>  
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Data wystawienia:</span>
                    <input type="text" id="data_zamowienia_od" name="szukaj_data_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_od'])) ? $filtr->process($_GET['szukaj_data_zamowienia_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_zamowienia_do" name="szukaj_data_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_do'])) ? $filtr->process($_GET['szukaj_data_zamowienia_do']) : ''); ?>" size="10" class="datepicker" />
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
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia_paragony.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>
            
            <form action="sprzedaz/zamowienia_paragony_pdf.php" method="post" class="cmxform">

                <div id="sortowanie">
                <span>Sortowanie: </span>
                <a id="sort_a1" class="sortowanie" href="sprzedaz/zamowienia_paragony.php?sort=sort_a1">nr paragonu malejąco</a>
                <a id="sort_a2" class="sortowanie" href="sprzedaz/zamowienia_paragony.php?sort=sort_a2">nr paragonu rosnąco</a>
                <a id="sort_a3" class="sortowanie" href="sprzedaz/zamowienia_paragony.php?sort=sort_a3">data wystawienia malejąco</a>
                <a id="sort_a4" class="sortowanie" href="sprzedaz/zamowienia_paragony.php?sort=sort_a4">data wystawienia rosnąco</a>
                <a id="sort_a5" class="sortowanie" href="sprzedaz/zamowienia_paragony.php?sort=sort_a5">nr zamówienia malejąco</a>
                <a id="sort_a6" class="sortowanie" href="sprzedaz/zamowienia_paragony.php?sort=sort_a6">nr zamówienia rosnąco</a>                
                </div>             

                <table style="width:1020px">
                    <tr>
                      <td style="width:100%;vertical-align:top" colspan="2">

                        <div id="wynik_zapytania"></div>
                        <div id="aktualna_pozycja">1</div>

                        <div id="akcja">
                          <div id="akc">
                            Drukuj zestawienie: 
                            <select name="data_wydruku_mc" id="data_wydruku_mc">
                                <option value="01" <?php echo ( date('m') == '01' ? 'selected="selected"' : ''); ?>>styczeń</option>
                                <option value="02" <?php echo ( date('m') == '02' ? 'selected="selected"' : ''); ?>>luty</option>
                                <option value="03" <?php echo ( date('m') == '03' ? 'selected="selected"' : ''); ?>>marzec</option>
                                <option value="04" <?php echo ( date('m') == '02' ? 'selected="selected"' : ''); ?>>kwiecień</option>
                                <option value="05" <?php echo ( date('m') == '05' ? 'selected="selected"' : ''); ?>>maj</option>
                                <option value="06" <?php echo ( date('m') == '06' ? 'selected="selected"' : ''); ?>>czerwiec</option>
                                <option value="07" <?php echo ( date('m') == '07' ? 'selected="selected"' : ''); ?>>lipiec</option>
                                <option value="08" <?php echo ( date('m') == '08' ? 'selected="selected"' : ''); ?>>sierpień</option>
                                <option value="09" <?php echo ( date('m') == '09' ? 'selected="selected"' : ''); ?>>wrzesień</option>
                                <option value="10" <?php echo ( date('m') == '10' ? 'selected="selected"' : ''); ?>>październik</option>
                                <option value="11" <?php echo ( date('m') == '11' ? 'selected="selected"' : ''); ?>>listopad</option>
                                <option value="12" <?php echo ( date('m') == '12' ? 'selected="selected"' : ''); ?>>grudzień</option>
                            </select>
                            <select name="data_wydruku_rok" id="data_wydruku_rok">
                                <option value="2005" <?php echo ( date('Y') == '2005' ? 'selected="selected"' : ''); ?>>2005</option>
                                <option value="2006" <?php echo ( date('Y') == '2006' ? 'selected="selected"' : ''); ?>>2006</option>
                                <option value="2007" <?php echo ( date('Y') == '2007' ? 'selected="selected"' : ''); ?>>2007</option>
                                <option value="2008" <?php echo ( date('Y') == '2008' ? 'selected="selected"' : ''); ?>>2008</option>
                                <option value="2009" <?php echo ( date('Y') == '2009' ? 'selected="selected"' : ''); ?>>2009</option>
                                <option value="2010" <?php echo ( date('Y') == '2010' ? 'selected="selected"' : ''); ?>>2010</option>
                                <option value="2011" <?php echo ( date('Y') == '2011' ? 'selected="selected"' : ''); ?>>2011</option>
                                <option value="2012" <?php echo ( date('Y') == '2012' ? 'selected="selected"' : ''); ?>>2012</option>
                                <option value="2013" <?php echo ( date('Y') == '2013' ? 'selected="selected"' : ''); ?>>2013</option>
                                <option value="2014" <?php echo ( date('Y') == '2014' ? 'selected="selected"' : ''); ?>>2014</option>
                                <option value="2015" <?php echo ( date('Y') == '2015' ? 'selected="selected"' : ''); ?>>2015</option>
                                <option value="2016" <?php echo ( date('Y') == '2016' ? 'selected="selected"' : ''); ?>>2016</option>
                                <option value="2017" <?php echo ( date('Y') == '2017' ? 'selected="selected"' : ''); ?>>2017</option>
                                <option value="2018" <?php echo ( date('Y') == '2018' ? 'selected="selected"' : ''); ?>>2018</option>
                                <option value="2019" <?php echo ( date('Y') == '2019' ? 'selected="selected"' : ''); ?>>2019</option>
                                <option value="2020" <?php echo ( date('Y') == '2020' ? 'selected="selected"' : ''); ?>>2020</option>
                            </select>
                            
                            
                            &nbsp; &nbsp; lub zakres dat:
                            <input type="text" name="data_od" value="" size="10" class="datepicker" /> &nbsp; do &nbsp;
                            <input type="text" name="data_do" value="" size="10" class="datepicker" />                            
                            
                            
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
                  <td style="text-align:right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Wygeneruj zestawienie PDF" /></td>
                </tr>
                <?php } ?>                
              </table>

            </form>

            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            <?php Listing::pokazAjax('sprzedaz/zamowienia_paragony.php', $zapytanie, $ile_licznika, $ile_pozycji, 'receipts_id'); ?>
            //]]>
            </script>              

        </div>
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
