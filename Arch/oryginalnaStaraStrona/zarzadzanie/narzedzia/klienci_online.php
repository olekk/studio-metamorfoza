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
    
    <script type="text/javascript">
    //<![CDATA[
    function podgladKoszyka(sesja) {
        $.colorbox( { href:"ajax/koszyk.php?id=" + sesja, width:'1000', maxHeight:'90%', open:true, initialWidth:50, initialHeight:50 } ); 
    }
    function podgladRef(id) {
        if ($('#w' + id).css('display') == 'none') {
            $('#w' + id).show();
            $('#w' + id + ' div').slideDown();
          } else {
            $('#w' + id + ' div').slideUp( function() { $('#w' + id).hide(); } );
        }
    }
    function szczegoly_ip(ip, id) {
        $('.id_wyglad').hide();
        $('#ip_'+id).html('<div id="laduje_dane"><span>Ładuje dane ...</span></div>');
        $('#ip_'+id).css('display','block');
        $.get('ajax/ip_lokalizacja.php', { ip: ip, id: id }, function(data) {
            $('#ip_'+id).css('display','none');
            $('#ip_'+id).slideDown("fast");
            $('#ip_'+id).html(data);
        });
    }    
    //]]>
    </script>    

    <div id="caly_listing">

        <div id="naglowek_cont">Kto jest aktualnie w sklepie ?</div>
        
        <div class="odswiezenie">
            <span>Ostatnie odświeżenie o <b><?php echo date('H:i:s', time()); ?></b></span>
        </div>
        
        <div id="wynik_zapytania" style="overflow:visible">
        
            <table class="listing_tbl">
            
                <tr class="div_naglowek nagsrodek">
                    <td></td>
                    <td>Koszyk</td>
                    <td>Czas online</td>
                    <td>Nazwa</td>
                    <td>Adres IP</td>
                    <td>Czas wejścia</td> 
                    <td>Czas ostatniego kliknięcia</td>
                    <td>Ostatni URL</td>  
                    <td>Skąd trafił&nbsp;?</td>                     
                </tr>
                
                <?php
                $TablicaKlientow = SklepOnline::IloscKlientowOnline();
                
                $LicznikWierszy = 1;
                
                if ( count($TablicaKlientow['klienci']) > 0 ) {
                
                    $WynikOnlineCaly = '';
                    $PrawdopodobnyBlad = false;
                
                    foreach ($TablicaKlientow['klienci'] as $Online ) {
                        //

                        $WynikOnline = '<tr class="pozycja_off lista">';
                        
                        // aktywny czy nie
                        $WynikOnline .= '<td>';                    
                        if ( ((int)time() - (int)$Online['ostatnie_klikniecie']) < 900 ) {
                             $WynikOnline .= '<img class="toolTipTop" src="obrazki/aktywny_on.png" alt="" title="Aktywny" />';
                           } else {
                             $WynikOnline .= '<img class="toolTipTop" src="obrazki/aktywny_off.png" alt="" title="Nieaktywny" />';
                        }
                        $WynikOnline .= '</td>';
                        
                        // czy ma koszyk czy nie
                        $WynikOnline .= '<td>';
                        if ( count($Online['koszyk']) > 0 ) {
                             $WynikOnline .= '<img onclick="podgladKoszyka(\'' . $Online['sesja'] . '\')" class="toolTipTop cur" src="obrazki/koszyk.png" alt="" title="Klient ma w koszyku produkty" />';
                        }
                        $WynikOnline .= '</td>';                    
                        
                        // jezeli nie ma godzin
                        if ( $Online['pierwsze_klikniecie'] != '' ) {
                            //
                            if ( ((int)time() - (int)$Online['pierwsze_klikniecie']) < 3600 ) {
                                $WynikOnline .= '<td>' . date('i \m\i\n s \s', ((int)time() - (int)$Online['pierwsze_klikniecie'])) . '</td>';
                              } else {
                                $WynikOnline .= '<td>' . date('H \g\o\d\z i \m\i\n s \s', ((int)time() - (int)$Online['pierwsze_klikniecie']) - 3600) . '</td>';
                            }
                            //
                          } else {
                            //
                            $WynikOnline .= '<td>Brak danych</td>';
                            //
                        }
                        $WynikOnline .=  '<td>';
                        if ( $Online['robot'] == 'tak' ) {
                            //
                            $NazwaBota = 'Robot';
                            //
                            // szuka kolejnego ciagu po compatible;
                            $Agent = explode(';', $Online['przegladarka']);
                            for ( $g = 0, $gf = count($Agent); $g < $gf; $g++) {
                                //
                                if ( strpos($Agent[$g], 'compati') > -1 ) {
                                     $NazwaBota = $Agent[$g + 1];
                                     break;
                                }
                                //
                            }
                            // jezeli jednak nie znalazl to bedzie szukal slowa bot
                            if ( $NazwaBota == 'Robot' ) {
                                //
                                $Agent = explode(' ', $Online['przegladarka']);
                                for ( $g = 0, $gf = count($Agent); $g < $gf; $g++) {
                                    //
                                    if ( strpos(strtolower($Agent[$g]), 'bot') > -1 ) {
                                         $NazwaBota = $Agent[$g];
                                         break;
                                    }
                                    //
                                } 
                                //
                            }
                            //
                            $WynikOnline .= '<span class="robot"><span>' . $NazwaBota . '</span></span>';
                            //
                         } else { 
                            //
                            if ( $Online['klient'] > 0 ) {
                                //
                                $InformacjeKlienta = '';
                                //
                                $zapytanie = "SELECT c.customers_id, 
                                                     ci.customers_info_number_of_logons,
                                                     ci.customers_info_date_account_created,
                                                     CONCAT(c.customers_firstname, ' ', c.customers_lastname) as nazwa 
                                                FROM customers c
                                           LEFT JOIN customers_info ci on c.customers_id = ci.customers_info_id
                                               WHERE c.customers_id = '" . (int)$Online['klient'] . "'";  
                                               
                                $sql = $db->open_query($zapytanie);
                                $info = $sql->fetch_assoc();  
                                //
                                $InformacjeKlienta .= 'Data rejestracji: ' . date('d-m-Y H:i',strtotime($info['customers_info_date_account_created'])) . '<br />';
                                $InformacjeKlienta .= 'Ilość logowań: ' . $info['customers_info_number_of_logons'] . '<br />';
                                $InformacjeKlienta .= 'Ilość zamówień: ' . (int)Klienci::pokazIloscZamowienKlienta($info['customers_id']);
                                //
                                $WynikOnline .= '<a title="' . $InformacjeKlienta . '" href="klienci/klienci_edytuj.php?id_poz=' . (int)$Online['klient'] . '" class="klient toolTipTop"><span>' . $info['nazwa'] . '</span></a>';
                                //
                                unset($InformacjeKlienta);
                                //
                                $db->close_query($sql);
                                unset($zapytanie, $info);
                                //
                              } else {
                                //
                                $WynikOnline .= 'Gość';
                                //
                            }
                            //
                        }
                        $WynikOnline .= '</td>';

                        if ( $Online['nr_ip'] != '' ) {
                            //
                            $WynikOnline .= '<td><span class="ip cur" onclick="szczegoly_ip(\'' .  $Online['nr_ip'] . '\',\'' . $LicznikWierszy . '\')">' . $Online['nr_ip'] . '</span><div class="id_wyglad" id="ip_' . $LicznikWierszy . '"></div></td>';
                            //
                          } else { 
                            //
                            $WynikOnline .= '<td>Brak danych</td>';
                            //
                        }
                        $WynikOnline .= '<td>' . (($Online['pierwsze_klikniecie'] != '') ? date('H:i:s', $Online['pierwsze_klikniecie']) : 'Brak danych') . '</td>';
                        
                        if ( $Online['pierwsze_klikniecie'] == '' ) {
                             $PrawdopodobnyBlad = true;
                        }
                        
                        $WynikOnline .= '<td>' . (($Online['pierwsze_klikniecie'] != '') ? date('H:i:s', $Online['ostatnie_klikniecie']) : 'Brak danych') . '</td>';
                        
                        $WynikOnline .= '<td>';
                        if ( !empty($Online['ostatnia_strona']) ) {
                             $WynikOnline .= '<a class="urh blank" href="' . ADRES_URL_SKLEPU . $Online['ostatnia_strona'] . '">' . $Online['ostatnia_strona'] . '</a>';
                        }
                        $WynikOnline .= '</td>';
                        
                        $WynikOnline .= '<td>';
                        if ( !empty($Online['referencja']) ) {
                             $WynikOnline .= '<img onclick="podgladRef(' .  $LicznikWierszy . ')" class="toolTipTop cur" src="obrazki/powrot.png" alt="" title="Sprawdź skąd klient trafił do sklepu" />';
                        }
                        $WynikOnline .= '</td>';
                        
                        $WynikOnline .= '</tr>';
                        
                        $WynikOnline .= '<tr class="referer">';
                        $WynikOnline .= '<td colspan="9" id="w' . $LicznikWierszy . '"><div><a class="blank" href="' . $Online['referencja'] . '">' . $Online['referencja'] . '</a></div></td>';
                        $WynikOnline .= '</tr>';
                        //
                        $LicznikWierszy++;
                        //
                        
                        if ( $PrawdopodobnyBlad == false ) {
                             $WynikOnlineCaly .= $WynikOnline;
                        }
                        
                        unset($WynikOnline);
                    }
                    
                    echo $WynikOnlineCaly;   
                         
                } else {
                
                    echo '<tr><td colspan="9"><span class="maleInfo">Aktualnie nikogo nie ma w sklepie ...</span></td></tr>';
                    
                }
                ?>
                
            </table>     

        </div>
        
        <?php
        if ( $PrawdopodobnyBlad == true ) {
            echo '<span class="ostrzezenie">
                     Dane sesji klientów są zaszyfrowane lub sklep odwiedził robot który nie utworzył sesji. Nie wszystkie dane mogły zostać odczytane - dane bez szczegółów zostały pominięte i nie zostały wyświetlone.
                  </span>';
        }
        ?>      
        
    </div>
                
    <?php include('stopka.inc.php'); ?>

<?php } ?>
