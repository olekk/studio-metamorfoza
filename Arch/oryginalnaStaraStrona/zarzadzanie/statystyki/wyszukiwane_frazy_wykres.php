<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    include 'programy/openChart/php-ofc-library/open-flash-chart.php';

    $d = array();

    // get jezyka
    $IdJezyka = 1; // domyslnie jezyk id 1
    if (isset($_GET['jezyk']) && (int)$_GET['jezyk']) {
        $IdJezyka = (int)$_GET['jezyk'];
    }
    //
    $zapytanie = "select * from customers_searches where language_id = '".$IdJezyka."' order by freq desc limit 20";
    $sql = $db->open_query($zapytanie);
    while ($info = $sql->fetch_assoc()) {
        //
        if ($info['freq'] > 0) {
            $tmp = new pie_value( (int)$info['freq'], $info['search_key']);
            $tmp->set_tooltip( $info['search_key'] . '<br>Ilość wyszukań: #val#');
            $tmp->set_label( $info['search_key'] , '#898989', 10 );        
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