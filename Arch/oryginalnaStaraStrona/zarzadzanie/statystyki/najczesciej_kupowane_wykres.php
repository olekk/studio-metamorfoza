<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // get jezyka
    $IdJezyka = 1; // domyslnie jezyk id 1
    if (isset($_GET['jezyk']) && (int)$_GET['jezyk']) {
        $IdJezyka = (int)$_GET['jezyk'];
    }
    //

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    include 'programy/openChart/php-ofc-library/open-flash-chart.php';

    $title = new title( '20 najczęściej kupowanych produktów' );
    $title->set_style( "{font-size: 13px; font-family: Tahoma; font-weight: normal; color: #797979; text-align: center; margin-bottom:10px;}" );


    $warunki_szukania = '';
    if ( isset($_GET['data']) ) {
        $TworzTab = explode(',',$_GET['data']);
        //
        if ((int)$TworzTab[0] > 0) {
            //
            $szukana_wartosc = date('Y-m-d H:i:s', (int)$TworzTab[0]);
            $warunki_szukania .= " and o.date_purchased >= '".$szukana_wartosc."'";
            //
        }
        if ((int)$TworzTab[1] > 0) {
            //
            $szukana_wartosc = date('Y-m-d H:i:s', (int)$TworzTab[1]);
            $warunki_szukania .= " and o.date_purchased <= '".$szukana_wartosc."'";
            //
        }     
    }

    // wartosc maksymalna
    $zapytanie = "select o.date_purchased, 
                         op.products_name, 
                         op.products_id, 
                         sum(op.products_quantity) as ilosc
                    from orders as o, orders_products as op 
                    where o.orders_id = op.orders_id ".$warunki_szukania."
                    GROUP by products_id ORDER BY ilosc DESC limit 1";  
    $sql = $db->open_query($zapytanie);
    $info = $sql->fetch_assoc();
    $WartoscMax = round($info['ilosc'] + ($info['ilosc']*0.2));	
    $Podzialka = (int)($WartoscMax / 5);
    $db->close_query($sql);
    unset($zapytanie, $info);

    // szukanie produktow
    $zapytanie = "select o.date_purchased, 
                         op.products_name, 
                         op.products_id, 
                         sum(op.products_quantity) as ilosc
                    from orders as o, orders_products as op 
                    where o.orders_id = op.orders_id ".$warunki_szukania."
                    GROUP by products_id ORDER BY ilosc DESC, op.products_name limit 20";
    $sql = $db->open_query($zapytanie);                

    $wartosci = array();
    $opisy = array();

    while ($info = $sql->fetch_assoc()) {
        //
        if ($info['products_name'] == '') {
            $info['products_name'] = '-- brak nazwy --';
        }
        //
        $tmp = new bar_value( (float)$info['ilosc'] );
        $tmp->set_tooltip( $info['products_name'] ."<br>" . "Ilość wyświetleń: #val#" );
        //
        $wartosci[] = $tmp;
        $opisy[] = '-';
        //
    }

    $db->close_query($sql);

    // tworzenie wykresu
    $bar = new bar_filled( '#E2D66A', '#A5B8AC' );
    // przypisanie wartosci
    $bar->set_values( $wartosci );

    // animacja
    $bar->set_on_show(new bar_on_show('grow-up', 0.5, 0.2));

    $chart = new open_flash_chart();
    $chart->set_title( $title );
    $chart->add_element( $bar );
    $chart->set_bg_colour( '#FFFFFF' );

    // dolna legenda x
    $x_labels = new x_axis_labels();
    $x_labels->set_colour( '#FFFFFF' );
    $x_labels->set_labels( $opisy );

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