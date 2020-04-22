<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    include 'programy/openChart/php-ofc-library/open-flash-chart.php';

    $d = array();

    $warunki_szukania = '';
    if ( isset($_GET['data']) ) {
        $TworzTab = explode(',',$_GET['data']);
        //
        if ((int)$TworzTab[0] > 0) {
            //
            $szukana_wartosc = date('Y-m-d H:i:s', (int)$TworzTab[0]);
            $warunki_szukania .= " and date_purchased >= '".$szukana_wartosc."'";
            //
        }
        if ((int)$TworzTab[1] > 0) {
            //
            $szukana_wartosc = date('Y-m-d H:i:s', (int)$TworzTab[1]);
            $warunki_szukania .= " and date_purchased <= '".$szukana_wartosc."'";
            //
        }    
    }

    // oblicza ogolna ilosc zamowien wg parametrow
    $zapytanieCalosc = "select payment_method, date_purchased from orders where payment_method != '' " . $warunki_szukania;
    $sqlc = $db->open_query($zapytanieCalosc);
    $SumaZamowien = (int)$db->ile_rekordow($sqlc);
    unset($zapytanieCalosc);

    $zapytanie = "select distinct payment_method from orders where payment_method != ''";
    $sql = $db->open_query($zapytanie);
    while ($info = $sql->fetch_assoc()) {
        //
        $zapytanieJedn = "select payment_method, date_purchased from orders where payment_method = '".$info['payment_method']."'" . $warunki_szukania;
        $sqlc = $db->open_query($zapytanieJedn);
        //
        if ((int)$db->ile_rekordow($sqlc) > 0) {
            $tmp = new pie_value($db->ile_rekordow($sqlc), $info['payment_method']);
            $tmp->set_tooltip( $info['payment_method'] . ' ('.round((((int)$db->ile_rekordow($sqlc) / $SumaZamowien) * 100), 2).'%)<br>Ilość zamówień: #val#');
            $tmp->set_label( $info['payment_method'] , '#898989', 10 );        
            $d[] = $tmp;
        }
        //                    
    }
    $db->close_query($sql);
    unset($info, $zapytanie);

    $pie = new pie();
    $pie->set_animate( true );
    $pie->set_label_colour( '#432BAF' );
    $pie->set_alpha( 0.75 );

    $pie->set_tooltip( '#label#<br>$#val# (#percent#)' );
    $pie->set_values( $d );

    $pie->set_colours(
        array(
            '#E2D66A',
            '#6AE2DE',
            '#6A9DE2',
            '#9A6AE2',
            '#DE6AE2',
            '#E26A86',
            '#BCE26A'        
        ) );

    $pie->set_values( $d );

    $chart = new open_flash_chart();
    $chart->set_bg_colour( '#FFFFFF' );
    $chart->add_element( $pie );

    $t = new tooltip();
    $t->set_shadow( false );
    $t->set_stroke( 1 );
    $t->set_colour( "#BFBFBF" );
    $t->set_background_colour( "#FFFFFF" );
    $t->set_title_style( "{font-size:10px; color:#646463; }" );
    $t->set_body_style( "{font-size:9px; font-weight:normal; color:#878786;}" );
    $chart->set_tooltip( $t );

    echo $chart->toPrettyString();

}
?>