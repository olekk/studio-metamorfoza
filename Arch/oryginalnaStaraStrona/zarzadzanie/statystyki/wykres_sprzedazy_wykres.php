<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    include 'programy/openChart/php-ofc-library/open-flash-chart.php';
    
    $WalutaZapyt = $filtr->process($_GET['waluta']);

    // ilosc miesiacy w dacie dzisiejszej
    $miesiac = (date('Y',time()) * 12) + date('m',time());

    // zmienna do obliczenia tytulu wykresu
    $miesiacDo = $miesiac - 16;

    $title = new title( 'Wykres sprzedaży od ' . date('m',time()) . '.' . date('Y',time()) . ' do ' . ($miesiacDo - ((int)($miesiacDo/12) * 12)) . '.' . (int)($miesiacDo/12) . ' w walucie: ' . mb_convert_case($waluty->ZwrocSymbolWalutyKod($WalutaZapyt), MB_CASE_UPPER, "UTF-8") );
    $title->set_style( "{font-size: 13px; font-family: Tahoma; font-weight: normal; color: #797979; text-align: center; margin-bottom:10px;}" );

    $chart = new open_flash_chart();

    $TabOsX = array();
    $IloscZam = array();
    $WartoscZam = array();
    $WartoscZamTxt = array();

    for ($n = 1; $n <= 17; $n++) {
        //
        $ObliczRok = (int)($miesiac/12);
        if ( $ObliczRok == ($miesiac/12) ) {
             $ObliczRok = $ObliczRok - 1;
        }
        
        $ObliczMiesiac = $miesiac - ((int)($miesiac/12) * 12);
        
        if ( $ObliczMiesiac == 0 ) {
             $ObliczMiesiac = 12;
        }
        
        //
        $zapytanie = "select o.orders_id,
                             o.currency,
                             o.date_purchased, 
                             ot.orders_id,
                             ot.value, 
                             ot.class
                        from orders o, orders_total ot
                        where o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '".$WalutaZapyt."'
                             and year(o.date_purchased) = '".$ObliczRok."' and month(o.date_purchased) = '".$ObliczMiesiac."'"; 

        // zmniejszanie ilosci miesiacy
        $miesiac--;
                             
        $sql = $db->open_query($zapytanie);

        $IloscZamowien = 0;
        $WartoscZamowien = 0;

        while ($info = $sql->fetch_assoc()) {
            //
            $IloscZamowien++;
            $WartoscZamowien = $WartoscZamowien + $info['value'];
            //
        }            
        $db->close_query($sql);

        $TabOsX[] = $ObliczMiesiac.'.'.$ObliczRok;

        $tmp = new solid_dot( $IloscZamowien );
        $tmp->colour('#3A7901')->tooltip( "Ilość zamówień: #val#" );  
        $IloscZamTxt[] = $tmp;    

        $IloscZam[] = $IloscZamowien;
        unset($tmp);

        $tmp = new bar_value( $WartoscZamowien );
        $tmp->set_tooltip( "Wartość zamówień: #val# " . $waluty->ZwrocSymbolWalutyKod($WalutaZapyt) );    
        $WartoscZamTxt[] = $tmp;

        $WartoscZam[] = $WartoscZamowien;
        unset($tmp);
        //
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
    $t->set_labels_from_array( $TabOsX );
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
    
}
?>
