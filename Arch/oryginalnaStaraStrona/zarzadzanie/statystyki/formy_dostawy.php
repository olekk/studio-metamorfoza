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
            <div class="naglowek">Wybierane formy dostawy</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje wybierane podczas składania zamówień formy dostawy</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                    
                    
                    <form action="statystyki/formy_dostawy.php" method="post" id="statForm" class="cmxform">
                    
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
                      echo '<div id="wyszukaj_ikona" style="margin-top:8px"><a href="statystyki/formy_dostawy.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                    

                    <div class="cl"></div>
                    
                    </form>
                    
                    <div class="NadWykres">
                        <div id="wykres"></div>  
                    </div>                    

                    <?php
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
                    //                    
                    $zapytanie = "select distinct shipping_module from orders where shipping_module != ''";
                    $sql = $db->open_query($zapytanie);
                    
                    $Wynik = '<br /> <table class="tblStatystyki">';
                    
                    $Wynik .= '<tr class="TyNaglowek">';
                    $Wynik .= '<td>Forma dostawy</td>';
                    $Wynik .= '<td>Ilość zamówień</td>';
                    $Wynik .= '</tr>';   
                    
                    $SumaBylo = false;
                    
                    while ($info = $sql->fetch_assoc()) {
                        //
                        $zapytanieJedn = "select shipping_module, date_purchased from orders where shipping_module = '" . $info['shipping_module'] . "'" . $warunki_szukania;
                        $sqlc = $db->open_query($zapytanieJedn);
                        //
                        if ((int)$db->ile_rekordow($sqlc) > 0) {
                        
                            $Wynik .= '<tr>';
                            $Wynik .= '<td class="linkProd"><a href="sprzedaz/zamowienia.php?szukaj_wysylka='.$info['shipping_module'].'">' . $info['shipping_module'] . '</a></td>';
                            
                            if ($db->ile_rekordow($sqlc) == 1) {
                                $TrescZam = ' <span>zamówienie</span>';
                            }
                            if ($db->ile_rekordow($sqlc) > 1 && $db->ile_rekordow($sqlc) < 5) {
                                $TrescZam = ' <span>zamówienia</span>';
                            }
                            if ($db->ile_rekordow($sqlc) > 4 || $db->ile_rekordow($sqlc) == 0) {
                                $TrescZam = ' <span>zamówień</span>';
                            }                            
                            $Wynik .= '<td class="wynikStat">' . $db->ile_rekordow($sqlc) . $TrescZam . '</td>';
                            $Wynik .= '</tr>'; 

                            $SumaBylo = true;

                        }
                        //                    
                    }
                    $db->close_query($sql);
                    unset($info, $zapytanie, $TrescZam);
                    
                    $Wynik .= '</table>';
                    ?>
                    
                    <?php
                    if ($SumaBylo == true) {
                    
                        echo $Wynik;
                        unset($Wynik);
                    
                        ?>

                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykres",
                        "970", "350", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/formy_dostawy_wykres.php<?php echo $przekazGet; ?>"}, {"wmode" : "transparent"} );
                        </script>      

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