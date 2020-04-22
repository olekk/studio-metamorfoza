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
            <div class="naglowek">Najczęściej kupowane produkty</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje 100 najczęściej kupowanych produktów w sklepie</span>
                    
                    <?php
                    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
                    ?>                     
                    
                    <form action="statystyki/najczesciej_kupowane.php" method="post" id="statForm" class="cmxform">
                    
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
                      echo '<div id="wyszukaj_ikona" style="margin-top:8px"><a href="statystyki/najczesciej_kupowane.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                    }
                    ?>                     
                    
                    <div class="cl"></div>
                    
                    </form>
                    
                    <?php
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

                    $zapytanie = "select o.date_purchased, 
                                         op.products_name, 
                                         op.products_id, 
                                         sum(op.products_quantity) as ilosc
                                    from orders as o, orders_products as op 
                                    where o.orders_id = op.orders_id ".$warunki_szukania."
                                    GROUP by products_id ORDER BY ilosc DESC, op.products_name limit 100";
                                    
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>
                        
                        <div class="NadWykres">
                            <div id="wykres"></div>                                              
                        </div>
                        
                        <br />
                        
                        <table class="tblStatystyki">

                        <tr class="TyNaglowek">
                            <td>Lp</td>
                            <td>Nazwa produktu</td>
                            <td>Ilość sprzedanych</td>
                        </tr>  
                        
                        <?php
                        $poKolei = 1;
                        while ($info = $sql->fetch_assoc()) {
                        
                            echo '<tr>';
                            
                            echo '<td class="poKolei">' . $poKolei . '</td>'; 
                            
                            // jezeli nie ma nazwy 
                            if ($info['products_name'] == '') {
                                 echo '<td class="linkProd">-- brak nazwy --</td>';
                               } else {
                                 echo '<td class="linkProd"><a href="produkty/produkty_edytuj.php?id_poz=' . $info['products_id'] . '" ' . (($poKolei < 11) ? 'style="font-weight:bold"' : ''). '>' . $info['products_name'] . '</a></td>';
                            }
                            
                            echo '<td class="wynikStat">' . $info['ilosc'] . '<span>jm</span></td>';
                            echo '</tr>';
                            
                            $poKolei++;
                        
                        }            
                        $db->close_query($sql);
                        unset($poKolei);
                        ?>
                        
                        </table>
                        
                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykres",
                        "970", "200", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/najczesciej_kupowane_wykres.php<?php echo $przekazGet; ?>"}, {"wmode" : "transparent"} );
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