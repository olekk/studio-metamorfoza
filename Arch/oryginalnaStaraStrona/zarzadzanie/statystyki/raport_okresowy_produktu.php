<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Raport sprzedaży dla produktu w określonym przedziale czasowym</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje sprzedaż wybranego produktu w określonym przedziale czasowym</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                     
                    
                    <form action="statystyki/raport_okresowy_produktu.php" method="post" id="statForm" class="cmxform">

                    <script type="text/javascript">
                      //<![CDATA[
                      $(document).ready(function() {
                        $('input.datepicker').Zebra_DatePicker({
                          format: 'd-m-Y',
                          inside: false,
                          direction: false,
                          readonly_element: true
                        });                
                      });
                      
                      function funkcja_produktu(id) {
                          $('#statForm').submit();
                      }
                      //]]>
                    </script>          
                    
                    <?php
                    if (isset($_GET['id_produkt']) && (int)$_GET['id_produkt'] > 0) {
                        echo '<input type="hidden" name="id_produkt" value="' . (int)$_GET['id_produkt'] . '" />';
                    }
                    ?>                    

                    <div id="zakresDat">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : ''); ?>" size="10" class="datepicker" />

                        <span style="margin-left:20px">Status:</span>
                        <?php
                        $tablia_status= Array();
                        $tablia_status = Sprzedaz::ListaStatusowZamowien(true);
                        echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="width:170px"'); ?>
                    </div>                     

                    <div class="wyszukaj_przycisk" style="margin-top:7px"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona" style="margin-top:8px"><a href="statystyki/raport_okresowy_produktu.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                    

                    <div class="cl"></div>
                    
                    <?php
                    if (!isset($_GET['id_produkt'])) {
                    ?>
                    
                    <table><tr>
                        <td style="vertical-align:top">
                        
                            <p style="font-weight:bold; height:30px;">
                            Wybierz kategorię z której chcesz wybrać <br />produkt do wyświetlania raportu
                            </p>                        
                        
                            <div id="drzewo" style="margin-left:10px; margin-top:7px; width:250px;">
                                <?php
                                //
                                echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                                //
                                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                    $podkategorie = false;
                                    if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                    //
                                    echo '<tr>
                                            <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                          </tr>
                                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                }
                                echo '</table>';
                                unset($tablica_kat,$podkategorie);   
                                ?>            
                            </div>        
                        </td>
                        <td style="vertical-align:top">
                        
                            <input type="hidden" id="rodzaj_modulu" value="raport_produkty" />
                            <div id="wynik_produktow_raport_produkty"></div> 
                            
                        </td>
                    </tr></table>                   
                    
                    <br />
                    
                    <?php } ?>
                    
                    </form>

                    <?php
                    //
                    $warunki_szukania = '';
                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";                     
                    }
                    
                    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '' ) {
                        $warunki_szukania .= " and o.orders_status = " . (int)$_GET['szukaj_status'] . " ";
                    }                    
                    
                    $os = 'and o.orders_status = " . $status . " ";';
                    $os = '';
                    //
                    
                    if (isset($_GET['id_produkt']) && (int)$_GET['id_produkt'] > 0) {

                        $Wynik = '<table class="tblStatystyki">';
                        
                        // szukanie danych o produkcie, zdjecie, nazwa
                        $zapytanie = 'SELECT DISTINCT
                                             p.products_id, 
                                             p.products_image,
                                             p.products_model,     
                                             pd.language_id, 
                                             pd.products_name
                                      FROM products p, products_description pd
                                      WHERE pd.products_id = p.products_id AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '" AND p.products_id = "'.(int)$_GET['id_produkt'].'"';   
                        
                        $sql = $db->open_query($zapytanie);
                        $info = $sql->fetch_assoc();
                        
                        $Wynik .= '<tr class="NazwaNaglowek">';
                        $Wynik .= '<td class="statZdjecie"><div>'.Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '50', '50').'</div></td>';
                        $Wynik .= '<td class="nazwa"><span>' . $info['products_name'] . '</span></td>';
                        $Wynik .= '<td class="wybierzInny" colspan="3"><a href="statystyki/raport_okresowy_produktu.php?filtr=nie">wybierz inny produkt</a></td>';
                        $Wynik .= '</tr>';      
                  
                        unset($zapytanie);
                        $db->close_query($sql);      

                        $zapytanie = "SELECT op.final_price_tax AS wartosc_brutto, 
                                             op.final_price AS wartosc_netto,
                                             o.date_purchased, 
                                             o.currency,
                                             op.products_name, 
                                             op.products_id,
                                             op.products_quantity AS ilosc, 
                                             op.products_model, 
                                             op.orders_products_id,
                                             IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), GROUP_CONCAT(DISTINCT ap.products_options ,'#', ap.products_options_values ORDER BY ap.products_options, ap.products_options_values SEPARATOR '|' ), '') as cechy
                                        FROM orders o 
                                        LEFT JOIN orders_products op ON o.orders_id = op.orders_id
                                        LEFT JOIN orders_products_attributes ap ON o.orders_id = ap.orders_id
                                       WHERE o.orders_id = op.orders_id AND 
                                             op.products_id = '" . (int)$_GET['id_produkt'] . "' AND
                                             IF ((SELECT distinct orders_id FROM orders_products_attributes WHERE orders_products_id = op.orders_products_id limit 1), op.orders_products_id = ap.orders_products_id, o.orders_id = o.orders_id)
                                             " . $warunki_szukania . "
                                    GROUP BY op.orders_products_id ORDER BY " . ((isset($_GET['typ'])) ? 'o.date_purchased, ' : '') . "op.products_name, cechy";

                        $sql = $db->open_query($zapytanie);

                        if ((int)$db->ile_rekordow($sql) > 0) {

                            $Wynik .= '<tr class="TyNaglowek">';
                            $Wynik .= '<td>Nr katalogowy</td>';
                            $Wynik .= '<td>Nazwa produktu</td>';
                            $Wynik .= '<td>Ilość sprzedanych</td>';
                            $Wynik .= '<td>Wartość netto</td>';
                            $Wynik .= '<td>Wartość brutto</td>';
                            $Wynik .= '</tr>';                      
                                         
                            // tworzenie tymczasowej tablicy do usuwania duplikatow
                            $ProduktyDuplikat = array();
                            while ($info = $sql->fetch_assoc()) {
                                //
                                $ProduktyDuplikat[] = array('id' => $info['products_id'],
                                                            'data_zamowienia' => date('d-m-Y',strtotime($info['date_purchased'])),
                                                            'model' => $info['products_model'],
                                                            'nazwa' => $info['products_name'],
                                                            'cechy' => $info['cechy'],
                                                            'ilosc' => $info['ilosc'],
                                                            'wartosc_netto' => $info['wartosc_netto'] * $info['ilosc'],
                                                            'wartosc_brutto' => $info['wartosc_brutto'] * $info['ilosc'],
                                                            'waluta' => $info['currency'],
                                                            'nazwa_cecha' => $info['products_name'] . $info['cechy']);
                                //
                            }
                            
                            // usuwanie duplikatow
                            $ProduktyBezDuplikatow = Statystyki::UsunDuplikaty($ProduktyDuplikat);
                            // tworzenie tablicy koncowej z produktami bez duplkatow - dupliaty polaczne i zsumowane
                            if ( isset($_GET['typ']) && $_GET['typ'] == 'data' ) {
                                $typ = 'data';
                            } else {
                                $typ = '';
                            }
                            $ProduktyKoncowe = Statystyki::TablicaKoncowa($ProduktyBezDuplikatow, $ProduktyDuplikat, $typ);

                            unset($ProduktyDuplikat, $ProduktyBezDuplikatow);
                            
                            // zeby wylistowac wszystkie produkty (z duplikatami) 
                            // foreach ($ProduktyDuplikat as $Produkt) {
                            
                            $PoprzedniaWartosc = '';
                            
                            foreach ($ProduktyKoncowe as $Produkt) {
                            
                                if ($PoprzedniaWartosc != ((isset($_GET['typ'])) ? $Produkt['data_zamowienia'] : $Produkt['nazwa'])) {
                                    //
                                    $Wynik .= '<tr class="NazwaNaglowek">';
                                    $Wynik .= '<td colspan="5"><span>' . ((isset($_GET['typ'])) ? $Produkt['data_zamowienia'] : $Produkt['nazwa'])   . '</span></td>';
                                    $Wynik .= '</tr>';                         
                                    //
                                }
                                
                                $Wynik .= '<tr>';
                                $Wynik .= '<td class="nrKat">' . $Produkt['model'] . '</td>';
                                $Wynik .= '<td class="linkProd"><a href="produkty/produkty_edytuj.php?id_poz=' . $Produkt['id'] . '">' . $Produkt['nazwa'] . Statystyki::PodzielCechy($Produkt['cechy']).'</a></td>';
                                $Wynik .= '<td class="inne">' . $Produkt['ilosc'] . '</td>';
                                $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($Produkt['wartosc_netto'], false, $Produkt['waluta']) . '</td>';
                                $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($Produkt['wartosc_brutto'], false, $Produkt['waluta']) . '</td>';                            
                                $Wynik .= '</tr>';
                                
                                if (isset($_GET['typ'])) {
                                    $PoprzedniaWartosc = $Produkt['data_zamowienia'];
                                  } else {
                                    $PoprzedniaWartosc = $Produkt['nazwa'];
                                }
                                
                            }                        

                            unset($info, $TrescZam);

                          } else {
                          
                            $Wynik .= '<tr><td style="padding:20px; border:0px; padding-left:0px;" colspan="7">Brak wyników ...</td></tr>';
                         
                        }
                              
                        unset($zapytanie);
                        $db->close_query($sql);

                        $Wynik .= '</table>';   
                        
                        echo $Wynik;
                        unset($Wynik);                          

                    }
                    ?>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}