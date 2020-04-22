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
            <div class="naglowek">Klienci wg zamówień</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport listę klientów sklepu wg ilości i wartości zamówień</span>
                    
                    <br />
                    
                    <div id="wybor">
                    
                    <?php
                    $zmienne = Funkcje::Zwroc_Get(array('str','typ'), true);
                    ?>
                  
                    <span>Sortowanie:</span>
                    <a class="sortowanie<?php echo ((!isset($_GET['typ']) || (isset($_GET['typ']) && $_GET['typ'] == 'wartosc')) ? '_zaznaczone' : ''); ?>" href="statystyki/klienci_zamowienia.php?typ=wartosc<?php echo $zmienne; ?>">wg wartości zamówień</a>
                    <a class="sortowanie<?php echo ((isset($_GET['typ']) && $_GET['typ'] == 'ilosc') ? '_zaznaczone' : ''); ?>" href="statystyki/klienci_zamowienia.php?typ=ilosc<?php echo $zmienne; ?>">wg ilości zamówień</a>

                    <span style="margin-left:20px">Dla waluty:</span>
                    
                    <?php
                    $zapytanieWaluty = "select code, title from currencies";
                    $sqlWaluta = $db->open_query($zapytanieWaluty);
                    
                    $zmienne = Funkcje::Zwroc_Get(array('str','waluta'), true);
                    
                    while ($infr = $sqlWaluta->fetch_assoc()) {      
                        if (!isset($_GET['waluta']) && $infr['code'] == $_SESSION['domyslna_waluta']['kod']) {
                            echo '<a class="sortowanie_zaznaczone" href="statystyki/klienci_zamowienia.php?waluta='.$infr['code'].$zmienne.'">'.$infr['title'].'</a>';
                        } else if (isset($_GET['waluta']) && $infr['code'] == $_GET['waluta']) {
                            echo '<a class="sortowanie_zaznaczone" href="statystyki/klienci_zamowienia.php?waluta='.$infr['code'].$zmienne.'">'.$infr['title'].'</a>';
                        } else {
                            echo '<a class="sortowanie" href="statystyki/klienci_zamowienia.php?waluta='.$infr['code'].$zmienne.'">'.$infr['title'].'</a>';
                        }
                    }
                    $db->close_query($sqlWaluta);
                    unset($zapytanieWaluty);                    
                    ?>
                    
                    </div>

                    <?php
                    // ile na stronie
                    $IleNaStronie = 50;
                    
                    $PoczatekLimit = 0;
                    if (isset($_GET['str']) && (int)$_GET['str'] > 0) {
                        $PoczatekLimit = (int)$_GET['str'] * $IleNaStronie;
                    }
                    
                    // waluta
                    $JakaWaluta = $_SESSION['domyslna_waluta']['kod'];
                    if (isset($_GET['waluta'])) {
                        $JakaWaluta = $filtr->process($_GET['waluta']);
                    }
                    
                    // zapytanie bez limitu - ogolna ilosc 
                    $zapytanie = "select c.customers_id, 
                                         c.customers_firstname, 
                                         c.customers_lastname,
                                         c.customers_discount, 
                                         ci.customers_info_number_of_logons, 
                                         c.customers_guest_account, 
                                         count(DISTINCT o.orders_id) as ilosc_zamowien, 
                                         sum(ot.value) as wartosc_zamowien,
                                         o.currency
                                    from customers c, 
                                         orders_total ot, 
                                         orders o, 
                                         customers_info ci 
                                   where c.customers_id = ci.customers_info_id AND 
                                         c.customers_id = o.customers_id AND
                                         o.orders_id = ot.orders_id AND
                                         ot.class = 'ot_total' AND
                                         o.currency = '".$JakaWaluta."' AND
                                         c.customers_guest_account = '0'
                                   group by c.customers_firstname, c.customers_lastname order by " . ((isset($_GET['typ']) && $_GET['typ'] == 'ilosc') ? "ilosc_zamowien" : "wartosc_zamowien") . " DESC";
                    $sql = $db->open_query($zapytanie);
                    $OgolnaIlosc = (int)$db->ile_rekordow($sql);
                    
                    $db->close_query($sql);
                    unset($zapytanie);
                    
                    
                    // zapytanie z limitem
                    $zapytanie = "select c.customers_id, 
                                         c.customers_firstname, 
                                         c.customers_lastname,
                                         c.customers_discount, 
                                         c.customers_groups_id,
                                         ci.customers_info_number_of_logons, 
                                         c.customers_guest_account, 
                                         count(DISTINCT o.orders_id) as ilosc_zamowien, 
                                         sum(ot.value) as wartosc_zamowien,
                                         o.currency
                                    from customers c, 
                                         orders_total ot, 
                                         orders o, 
                                         customers_info ci 
                                   where c.customers_id = ci.customers_info_id AND 
                                         c.customers_id = o.customers_id AND
                                         o.orders_id = ot.orders_id AND
                                         ot.class = 'ot_total' AND
                                         o.currency = '".$JakaWaluta."' AND
                                         c.customers_guest_account = '0'
                                   group by c.customers_firstname, c.customers_lastname order by " . ((isset($_GET['typ']) && $_GET['typ'] == 'ilosc') ? "ilosc_zamowien" : "wartosc_zamowien") . " DESC limit " . $PoczatekLimit . "," . $IleNaStronie;
                    
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>

                        <table class="tblStatystyki" style="margin-top:8px">
                        
                        <tr class="TyNaglowek">
                            <td style="text-align:center">Lp</td>
                            <td style="text-align:center">Id</td>
                            <td>Imię i nazwisko</td>
                            <td>Grupa klientów</td>
                            <td style="text-align:center">Ilość logowań</td>
                            <td style="text-align:center">Ilość zamówień</td>
                            <td style="text-align:right">Wartość zamówień</td>
                            <td style="text-align:right">Zniżki klienta</td>
                        </tr>                       
                        
                        <?php
                        $poKolei = 1 + $PoczatekLimit;
                        while ($info = $sql->fetch_assoc()) {
                        
                            echo '<tr>';
                            
                            echo '<td class="poKolei">' . $poKolei . '</td>'; 
                            echo '<td class="inne">' . $info['customers_id'] . '</td>';
                            echo '<td class="linkProd" style="width:20%"><a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">' . $info['customers_firstname'] . ' ' . $info['customers_lastname'] . '</a></td>';
                            echo '<td class="inne">' . Klienci::pokazNazweGrupyKlientow($info['customers_groups_id']) . '</td>';
                            echo '<td class="inne">' . $info['customers_info_number_of_logons'] . '</td>';
                            echo '<td class="inne">' . $info['ilosc_zamowien'] . '</td>';
                            echo '<td class="wynikStat">' . $waluty->FormatujCene($info['wartosc_zamowien'], false, $info['currency']) . '</td>';
                            
                            // znizki klienta
                            $ZnizkiKlienta = '';
                            $TblZnizki = Klienci::ZnizkiKlienta($info['customers_id'], $info['customers_discount']);
                            //
                            if (count($TblZnizki) > 0) {
                                //
                                $ZnizkiKlienta .= '<table>';
                                //
                                for ($j = 0, $c = count($TblZnizki); $j < $c; $j++) {
                                    if ($TblZnizki[$j][2] != 0) {                                    
                                        if ($TblZnizki[$j][0] == $TblZnizki[$j][1]) {
                                            //
                                            $ZnizkiKlienta .= '<tr><td><strong>' . $TblZnizki[$j][0] . '</strong>:</td><td style="width:60px"><span>' . $TblZnizki[$j][2] . ' %</span></td></tr>';
                                            //
                                          } else {
                                            //
                                            $ZnizkiKlienta .= '<tr><td>' . $TblZnizki[$j][0] . ' <strong>' . $TblZnizki[$j][1] . '</strong>:</td><td style="width:60px"><span>' . $TblZnizki[$j][2] . ' %</span></td></tr>';
                                            //
                                        }
                                    }
                                }
                                //
                                $ZnizkiKlienta .= '</table>';
                                //
                            }
                            echo '<td class="znizki">' . $ZnizkiKlienta . '</td>';

                            echo '</tr>';
                            
                            $poKolei++;
                        
                        }            
                        unset($poKolei);
                        $db->close_query($sql);
                        ?>
                        
                        </table>
                        
                        <div id="dolne_strony">
                            <?php
                            $limit = $OgolnaIlosc / $IleNaStronie;
                            if ($limit < ($OgolnaIlosc / $IleNaStronie)) {
                                $limit++;
                            }
                            //
                            for ($c = 0; $c < $limit; $c++) {
                                //
                                $Rozszerzenie = '_off';
                                if ((!isset($_GET['str']) || (int)$_GET['str'] == 0) && $c == 0) {
                                    $Rozszerzenie = '_on';
                                }
                                if (isset($_GET['str']) && (int)$_GET['str'] == $c) {
                                    $Rozszerzenie = '_on';
                                }
                                //
                                // sprawdzanie czy są jakies zmienne GET
                                $zmienne = Funkcje::Zwroc_Get(array('str'),(((isset($_GET['waluta']) || isset($_GET['typ'])) && $c > 0) ? true : false));
                                //
                                if ($c == 0) {
                                    echo '<a class="a_buts' . $Rozszerzenie . '" href="statystyki/klienci_zamowienia.php'.$zmienne.'">1</a>';
                                  } else {
                                    echo '<a class="a_buts' . $Rozszerzenie . '" href="statystyki/klienci_zamowienia.php?str='.$c.$zmienne.'">' . ($c + 1) . '</a>';
                                }
                            }
                            //
                            echo '<span id="ileStr">Wyświetlanie: ' . ($PoczatekLimit + 1) . ' do ' . (($PoczatekLimit + $IleNaStronie) > $OgolnaIlosc ? $OgolnaIlosc : ($PoczatekLimit + $IleNaStronie)) . ' z ' . $OgolnaIlosc . '</span>';
                            ?>
                        </div>
                        
                        <div class="cl"></div>
                        
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