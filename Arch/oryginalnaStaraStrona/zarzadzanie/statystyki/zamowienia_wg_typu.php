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
            <div class="naglowek">Typy zamówień</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje zamówienia wg poszczególnych typów</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                      
                    
                    <form action="statystyki/zamowienia_wg_typu.php" method="post" id="statForm" class="cmxform">
                    
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
                    
                    <div id="zakresDat">
                        <span>Przedział czasowy wyników od:</span>
                        <input type="text" id="data_od" name="data_od" value="<?php echo ((isset($_GET['data_od'])) ? $filtr->process($_GET['data_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                        <input type="text" id="data_do" name="data_do" value="<?php echo ((isset($_GET['data_do'])) ? $filtr->process($_GET['data_do']) : ''); ?>" size="10" class="datepicker" />
                    </div>    

                    <div class="wyszukaj_przycisk" style="margin-top:7px"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                    
                    <?php
                    if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                      echo '<div id="wyszukaj_ikona" style="margin-top:8px"><a href="statystyki/zamowienia_wg_typu.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                     

                    <div class="cl"></div>
                    
                    </form>
                    
                    <div class="NadWykres">
                        <div id="wykres"></div>  
                    </div>                    

                    <?php
                    $SumaBylo = false;
                    //
                    $warunki_szukania = '';
                    $przekazGet = '?data=';
                    if ( isset($_GET['data_od']) && $_GET['data_od'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')));
                        $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
                        $przekazGet .= strtotime($filtr->process($_GET['data_od'] . ' 00:00:00')) . ',';
                      } else {
                        $przekazGet .= '0,';
                    }

                    if ( isset($_GET['data_do']) && $_GET['data_do'] != '' ) {
                        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')));
                        $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";
                        $przekazGet .= strtotime($filtr->process($_GET['data_do'] . ' 23:59:59')) . ',';
                      } else {
                        $przekazGet .= '0,';                        
                    }
                    
                    
                    $TabWynik = array();
                    for ($n = 1; $n < 5; $n++) {
                        //
                        $zapytanie = "select orders_id, date_purchased from orders where orders_source = '" . $n . "' " . $warunki_szukania;
                        $sql = $db->open_query($zapytanie);
                        if ($db->ile_rekordow($sql) > 0) {
                            $SumaBylo = true;
                        }
                        $TrescZam = '';
                        if ($db->ile_rekordow($sql) == 1) {
                            $TrescZam = ' <span>zamówienie</span>';
                        }
                        if ($db->ile_rekordow($sql) > 1 && $db->ile_rekordow($sql) < 5) {
                            $TrescZam = ' <span>zamówienia</span>';
                        }
                        if ($db->ile_rekordow($sql) > 4 || $db->ile_rekordow($sql) == 0) {
                            $TrescZam = ' <span>zamówień</span>';
                        }                      
                        $TabWynik[$n] = (int)$db->ile_rekordow($sql) . $TrescZam;
                        $db->close_query($sql);                    
                        //
                        unset($TrescZam);
                    }
                    //
                    
                    if ($SumaBylo == true) {                    
                    
                        $Wynik = '<table class="tblStatystyki">';
                        
                        $Wynik .= '<tr class="TyNaglowek">';
                        $Wynik .= '<td>Typ zamówienia</td>';
                        $Wynik .= '<td align="right">Ilość zamówień</td>';
                        $Wynik .= '</tr>';                        

                        $Wynik .= '<tr>';
                        $Wynik .= '<td class="linkProd"><a href="sprzedaz/zamowienia.php?typ=2">Zamówienia bez rejestracji klienta</a></td>';
                        $Wynik .= '<td class="wynikStat">' . $TabWynik[2] . '</td>';
                        $Wynik .= '</tr>';

                        $Wynik .= '<tr>';
                        $Wynik .= '<td class="linkProd"><a href="sprzedaz/zamowienia.php?typ=1">Zamówienia z rejestracją klienta</a></td>';
                        $Wynik .= '<td class="wynikStat">' . $TabWynik[1] . '</td>';
                        $Wynik .= '</tr>';

                        $Wynik .= '<tr>';
                        $Wynik .= '<td class="linkProd"><a href="sprzedaz/zamowienia.php?typ=3">Zamówienia ręczne (dodawane z poziomu panelu administracyjnego)</a></td>';
                        $Wynik .= '<td class="wynikStat">' . $TabWynik[4] . '</td>';
                        $Wynik .= '</tr>'; 

                        $Wynik .= '<tr>';
                        $Wynik .= '<td class="linkProd"><a href="sprzedaz/zamowienia.php?typ=4">Zamówienia z Allegro</a></td>';
                        $Wynik .= '<td class="wynikStat">' . $TabWynik[3] . '</td>';
                        $Wynik .= '</tr>';                    
                        
                        $Wynik .= '</table>';
                        
                        echo $Wynik;
                        
                        unset($Wynik, $TabWynik);
                        ?>
                        
                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykres",
                        "970", "350", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/zamowienia_wg_typu_wykres.php<?php echo $przekazGet; ?>"}, {"wmode" : "transparent"} );
                        </script>   
                        
                        <br /><br />
                        
                        <div class="NadWykres">
                            <div id="wykresWartosc"></div>  
                        </div>                         

                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykresWartosc",
                        "970", "350", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/zamowienia_wg_typu_wartosc_wykres.php<?php echo $przekazGet; ?>"}, {"wmode" : "transparent"} );
                        </script>         

                        <br /><br />

                        <?php
                        $zapytanieWaluty = "select code, title, symbol from currencies";
                        $sqlWaluta = $db->open_query($zapytanieWaluty);
    
                        $TabWynik = array();
                        $Lb = 0;  
                        
                        while ($infr = $sqlWaluta->fetch_assoc()) {
                        
                            $TabWynik[$Lb]['waluta'] = $infr['title'];
                            $TabWynik[$Lb]['walutaKod'] = $infr['code'];
                            $TabWynik[$Lb]['walutaSymbol'] = $infr['symbol'];

                            for ($n = 1; $n < 5; $n++) {
                            
                                $zapytanie = "select sum(value) as suma_zamowien , o.currency
                                                from orders o, orders_total ot
                                               where o.orders_source = '".$n."' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '" . $infr['code'] . "'" . $warunki_szukania;       
                                 
                                $sql = $db->open_query($zapytanie);
                                
                                $info = $sql->fetch_assoc();
                                $WartoscZamowien = $info['suma_zamowien'];           
                                $db->close_query($sql);

                                $TabWynik[$Lb]['wartosc_' . $n] = $WartoscZamowien;
                            
                            }
                            
                            $Lb++;

                        }           
                        
                        $db->close_query($sqlWaluta);
                        unset($zapytanieWaluty);                        

                        $Wynik = '<table class="tblStatystyki">';

                        $Wynik .= '<tr class="TyNaglowek">';
                        $Wynik .= '<td>&nbsp;</td>';
                        $Wynik .= '<td style="text-align:right">Zamówienia z rejestracją klienta</td>';
                        $Wynik .= '<td style="text-align:right">Zamówienia bez rejestracji klienta</td>';
                        $Wynik .= '<td style="text-align:right">Zamówienia z Allegro</td>';
                        $Wynik .= '<td style="text-align:right">Zamówienia ręczne</td>';
                        $Wynik .= '</tr>';
                        
                        for ($v = 0, $c = count($TabWynik); $v < $c; $v++) {
                            //
                            $Wynik .= '<tr>';
                            $Wynik .= '<td class="TblNaglowek">' . $TabWynik[$v]['waluta'] . '</td>';
                            $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($TabWynik[$v]['wartosc_1'], false, $TabWynik[$v]['walutaKod']) . '</td>';
                            $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($TabWynik[$v]['wartosc_2'], false, $TabWynik[$v]['walutaKod']) . '</td>';
                            $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($TabWynik[$v]['wartosc_3'], false, $TabWynik[$v]['walutaKod']) . '</td>';
                            $Wynik .= '<td class="walutaZam">' . $waluty->FormatujCene($TabWynik[$v]['wartosc_4'], false, $TabWynik[$v]['walutaKod']) . '</td>';
                            $Wynik .= '</tr>';
                            //
                        }

                        $Wynik .= '</table>';
                        
                        echo $Wynik;
                        
                        unset($Wynik, $TabWynik);                        
                        ?>

                    <?php
                    } else {
                        //
                        echo '<div style="margin:10px">Brak statystyk ...</div>';
                        //
                    }
                    ?>                    

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}