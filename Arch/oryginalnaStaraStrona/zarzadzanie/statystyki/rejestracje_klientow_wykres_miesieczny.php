<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $Okres          = '24';

    include 'programy/openChart/php-ofc-library/open-flash-chart.php';
    
    $title = new title( 'Rejestracje klientów za ostatnie '.$Okres.' miesiące' );
    $title->set_style( "{font-size: 13px; font-family: Tahoma; font-weight: normal; color: #797979; text-align: center; margin-bottom:10px;}" );

    $chart = new open_flash_chart();
    
    $TabOsX = array();
    $IloscKliento = array();
    $IloscKlientowTxt = array();

    $Ilosc          = array();
    $Przedzial      = array();
    $Wynik          = array();

    $dataBiezaca    = date('Y-m-d') . ' 23:59:59';
    $dataPoczatkowa = date('Y-m-d', strtotime("-".$Okres." month")) . ' 00:00:00';

    //policzenie ilosci w zadanym okresie czasu
    $zapytanie = "SELECT EXTRACT(YEAR FROM ci.customers_info_date_account_created) AS rok, EXTRACT(MONTH FROM ci.customers_info_date_account_created) AS miesiac, COUNT( ci.customers_info_date_account_created ) AS ilosc
                      FROM customers_info ci
                      WHERE ci.customers_info_date_account_created BETWEEN '".$dataPoczatkowa."' AND '".$dataBiezaca."'
                      GROUP BY MONTH(ci.customers_info_date_account_created), YEAR(ci.customers_info_date_account_created)";

    $sql = $db->open_query($zapytanie);
    while ($info = $sql->fetch_assoc()) {
        $Ilosc[$info['rok'].'-'.($info['miesiac'] < 10 ? '0'.$info['miesiac'] : $info['miesiac'])] = $info['ilosc'];
    }

    //utworzenie tablicy dni dla zadanego przedzialu czasu
    for ($z = 0; $z < $Okres; $z++) {
        $tmp = date('Y-m', strtotime("-".$z." month"));
        $Przedzial[] = $tmp;
    }
    unset($z, $tmp);

    //utworzenie tablicy dni z ilosciami w kazdym dniu
    for ( $i = 0, $c = count($Przedzial); $i < $c; $i++ ) {
        if ( isset($Ilosc[$Przedzial[$i]]) ) {
            $Wynik[$Przedzial[$i]] = $Ilosc[$Przedzial[$i]];
        } else {
            $Wynik[$Przedzial[$i]] = 0;
        }
    }

    unset($i, $c, $Przedzial);
    $db->close_query($sql);
    unset($info, $zapytanie);

    $WynikOdwrocony = array_reverse($Wynik);

    foreach ( $WynikOdwrocony as $key => $value) {

        $ilosc_klientow = (int)$value;

        // tablica do wartosci zamowien
        $TabOsX[] = date("m-Y", strtotime($key));
        $IloscKlientow[] = $ilosc_klientow;
             
        $tmp = new solid_dot( $ilosc_klientow );
        $tmp->colour('#3A7901')->tooltip( "Zarejestrowanych klientów: #val#" );    
        $IloscKlientowTxt[] = $tmp; 
        unset($tmp);
             
    }

    $d = new solid_dot();
    $d->size(4)->halo_size(5)->colour('#83BC25');

    $line = new line();
    $line->set_default_dot_style($d);
    $line->set_width( 1 );
    $line->set_colour( '#83BC25' );
    $line->set_values( $IloscKlientowTxt );
    
    $line->on_show(new line_on_show('drop', 0.5, 0.2));

    $x = new x_axis();
    
    $labels = new x_axis_labels();
    $labels->set_labels( $TabOsX );
    $labels->rotate(90); 
    $x->set_labels($labels);
    
    $chart->set_x_axis( $x );
    
    // jezeli wartosc zamowien = 0
    if (max($IloscKlientow) == 0) {
        $IloscKlientow = array(1);
    }
    
    $b = new y_axis();
    $b->set_range( 0, max($IloscKlientow) + ((max($IloscKlientow) * 0.2) < 1 ? 1 : (max($IloscKlientow) * 0.2)), (int)(max($IloscKlientow) / 5) );
    $chart->set_y_axis( $b );

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
    $chart->add_element( $line );

    echo $chart->toPrettyString();
    
}
?>
