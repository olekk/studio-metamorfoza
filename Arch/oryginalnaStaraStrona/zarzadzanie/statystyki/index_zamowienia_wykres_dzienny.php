<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

include 'programy/openChart/php-ofc-library/open-flash-chart.php';
    
$chart = new open_flash_chart();

$Ilosc          = array();
$Przedzial      = array();
$Wynik          = array();

$Okres          = '15';
$dataBiezaca    = date('Y-m-d', strtotime("+1 day"));
$dataPoczatkowa = date('Y-m-d', strtotime("-".$Okres." day"));

//policzenie ilosci w zadanym okresie czasu
$zapytanie = "SELECT DATE(o.date_purchased) AS data, SUM(ot.value/o.currency_value) AS wartosc, COUNT(o.date_purchased) AS ilosc
                  FROM orders o
                  LEFT JOIN orders_total ot ON ot.orders_id = o.orders_id AND ot.class = 'ot_total'
                  WHERE o.date_purchased BETWEEN '".$dataPoczatkowa."' AND '".$dataBiezaca."'
                  GROUP BY DATE(o.date_purchased)";

$sql = $db->open_query($zapytanie);
while ($info = $sql->fetch_assoc()) {
    $Ilosc[$info['data']] = array($info['wartosc'], $info['ilosc']);
}

//utworzenie tablicy dni dla zadanego przedzialu czasu
for ($z = 0; $z < $Okres; $z++) {
    $tmp = date('Y-m-d', strtotime("-".$z." day"));
    $Przedzial[] = $tmp;
}
unset($z, $tmp);

//utworzenie tablicy dni z wartosciami i ilosciami w kazdym dniu
for ( $i = 0, $c = count($Przedzial); $i < $c; $i++ ) {
    if ( isset($Ilosc[$Przedzial[$i]]) ) {
        $Wynik[$Przedzial[$i]] = array($Ilosc[$Przedzial[$i]][0], $Ilosc[$Przedzial[$i]][1]);
    } else {
        $Wynik[$Przedzial[$i]] = array(0, 0);
    }
}

unset($i, $c, $Przedzial);
$db->close_query($sql);
unset($info, $zapytanie);

$WynikOdwrocony = array_reverse($Wynik);

foreach ( $WynikOdwrocony as $key => $value) {

    $TabOsX[] = date("d-m", strtotime($key));

    $tmp = new solid_dot( $value[1] );
    $tmp->colour('#3A7901')->tooltip( "Ilość zamówień: #val#" );  
    $IloscZamTxt[] = $tmp;    

    $IloscZam[] = $value[1];
    unset($tmp);

    $tmp = new bar_value( $value[0] );
    $tmp->set_tooltip( "Wartość zamówień: #val# PLN" );    
    $WartoscZamTxt[] = $tmp;

    $WartoscZam[] = $value[0];
    unset($tmp);

}

    $bar = new bar_filled( '#B4C9DD', '#91A4B6' );
    $bar->set_values( $WartoscZamTxt );
    
    $bar->set_on_show(new bar_on_show('grow-up', 0.5, 0.2));

    $d = new solid_dot();
    $d->size(4)->halo_size(5)->colour('#83BC25');

    $line = new line();
    $line->set_default_dot_style($d);
    $line->set_width( 1 );
    $line->set_colour( '#83BC25' );
    $line->set_values( $IloscZamTxt );
    $line->attach_to_right_y_axis();
    
    $line->on_show(new line_on_show('drop', 0.5, 0.2));

    $t = new x_axis();

    $labels = new x_axis_labels();
    $labels->set_labels( $TabOsX );
    $labels->rotate(90);  
    $t->set_labels($labels);

    $chart->set_x_axis( $t );
    
    // jezeli wartosc zamowien = 0
    if (max($WartoscZam) == 0) {
        $WartoscZam = array(1);
    }

    $b = new y_axis();
    $b->set_range( 0, max($WartoscZam) + (max($WartoscZam) * 0.2), (int)(max($WartoscZam) / 5) );
    $chart->set_y_axis( $b );

    $y = new y_axis_right();
    $y->set_range( 0, max($IloscZam) + 1, (int)(max($IloscZam) * 0.1) );
    //$y->set_labels( array('Zero','One','Two','Three','Four','Five','Six','Seven','Eight') );

    $chart->set_y_axis_right( $y );

    $t = new tooltip();
    $t->set_shadow( false );
    $t->set_stroke( 1 );
    $t->set_colour( "#BFBFBF" );
    $t->set_background_colour( "#FFFFFF" );
    $t->set_title_style( "{font-size:10px; color:#646463; }" );
    $t->set_body_style( "{font-size:9px; font-weight:normal; color:#878786;}" );
    $chart->set_tooltip( $t );

    $chart->set_bg_colour( '#FFFFFF' );
    $chart->set_title( $title );
    $chart->add_element( $bar );
    $chart->add_element( $line );

    echo $chart->toPrettyString();

?>
