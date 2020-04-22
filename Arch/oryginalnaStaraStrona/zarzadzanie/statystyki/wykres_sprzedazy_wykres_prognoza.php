<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    include 'programy/openChart/php-ofc-library/open-flash-chart.php';
    
    $zapytanieWaluty = "select code, title from currencies";
    $sqlWaluta = $db->open_query($zapytanieWaluty);
    
    $TabWynik = array();
    $Lb = 0;
    
    while ($infr = $sqlWaluta->fetch_assoc()) {

        $zapytanie = "select o.orders_id,
                             o.currency,
                             o.date_purchased, 
                             ot.orders_id,
                             ot.value, 
                             ot.class
                        from orders o, orders_total ot
                        where o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '" . $infr['code'] . "'
                             and year(o.date_purchased) = '".date('Y',time())."' and month(o.date_purchased) = '".date('m',time())."'";                                    
                             
                             
        $sql = $db->open_query($zapytanie);
        
        $WartoscZamowien = 0;
        
        while ($info = $sql->fetch_assoc()) {
            //
            $WartoscZamowien = $WartoscZamowien + $info['value'];
            //
        }            
        $db->close_query($sql);

        $TabWynik[$Lb]['waluta'] = $infr['title'];
        $TabWynik[$Lb]['walutaKod'] = $infr['code'];
        $TabWynik[$Lb]['wartosc'] = $WartoscZamowien;
        
        $Lb++;

    }  
    
    $Legenda = array();
    $Wartosci = array();
    
    $WartoscMax = 0;
    
    for ($v = 0, $c = count($TabWynik); $v < $c; $v++) {
        //                    
        $dzisiejszyDzien = date("j");
        $iloscDniMiesiac = date("t");
        $m_amt = round( $TabWynik[$v]['wartosc'], 0 );
        $prognoza = round( ($m_amt / $dzisiejszyDzien) * $iloscDniMiesiac, 0);                           
        //
        $Legenda[] = $TabWynik[$v]['waluta'];
        //
        $tmp = new bar_value( $prognoza );
        $tmp->set_tooltip( "Szacowana sprzedaż: #val# " . $waluty->ZwrocSymbolWalutyKod($TabWynik[$v]['walutaKod']) );
        //
        $Wartosci[] = $tmp;        
        //
        if ($prognoza > $WartoscMax) {
            $WartoscMax = $prognoza;
        }
    }   
    //
    if ($WartoscMax == 0) {
        $WartoscMax = 5;
    }
    //
    $WartoscMax = round($WartoscMax + ($WartoscMax*0.2));
    $Podzialka = (int)($WartoscMax / 5);
    
    $title = new title( 'Prognoza sprzedaży na ' . date('m',time()) . '.' . date('Y',time()) .' na podstawie sprzedaży w ' . date('m',time()) . '.' . date('Y',time()));
    $title->set_style( "{font-size: 13px; font-family: Tahoma; font-weight: normal; color: #797979; text-align: center; margin-bottom:10px;}" );    
    
    // tworzenie wykresu
    $bar = new bar_filled( '#AADE5F', '#A5B8AC' );
    // przypisanie wartosci
    $bar->set_values( $Wartosci );

    // animacja
    $bar->set_on_show(new bar_on_show('pop', 0.5, 0.2));

    $chart = new open_flash_chart();
    $chart->set_title( $title );
    $chart->add_element( $bar );
    $chart->set_bg_colour( '#FFFFFF' );

    // dolna legenda x
    $x_labels = new x_axis_labels();
    $x_labels->set_labels( $Legenda );

    // legenda pozioma
    $x = new x_axis();
    $x->set_labels( $x_labels );
    $chart->set_x_axis( $x );

    // leganda pionowa
    $y = new y_axis();
    $y->set_range( 0, (int)$WartoscMax , $Podzialka);
    $chart->set_y_axis( $y );

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
