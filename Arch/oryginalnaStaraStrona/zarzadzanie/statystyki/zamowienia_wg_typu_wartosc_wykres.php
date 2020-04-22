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
    
    $TabWynik = array();
    $Lb = 0;
    
    while ($infr = $sqlWaluta->fetch_assoc()) {
    
        $TabWynik[$Lb]['waluta'] = $infr['title'];
        $TabWynik[$Lb]['walutaKod'] = $infr['code'];

        for ($n = 1; $n < 5; $n++) {
        
            $zapytanie = "select sum(value) as suma_zamowien , o.currency
                            from orders o, orders_total ot
                           where o.orders_source = '".$n."' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '" . $infr['code'] . "'" . $warunki_szukania;       
             
            $sql = $db->open_query($zapytanie);
            
            $info = $sql->fetch_assoc();
            $WartoscZamowien = $info['suma_zamowien'];           
            $db->close_query($sql);

            $TabWynik[$Lb]['wartosc_' . $n] = $WartoscZamowien;
        
        }
        
        $Lb++;

    }  
    
    $Legenda = array();
    $Wartosci_id_1 = array();
    $Wartosci_id_2 = array();
    $Wartosci_id_3 = array();
    $Wartosci_id_4 = array();
    
    $WartoscMax = 0;
    
    for ($v = 0, $c = count($TabWynik); $v < $c; $v++) {
        //                    
        $Legenda[] = $TabWynik[$v]['waluta'];
        //
        // zamowienia z rejestracja klienta
        $tmp = new bar_value( $TabWynik[$v]['wartosc_1'] );
        $tmp->set_tooltip( "Zamówienia z rejestracją klienta: #val# " . $waluty->ZwrocSymbolWalutyKod($TabWynik[$v]['walutaKod']) );
        $Wartosci_id_1[] = $tmp;        
        //
        // zamowienia bez rejestracji klienta
        $tmp = new bar_value( $TabWynik[$v]['wartosc_2'] );
        $tmp->set_tooltip( "Zamówienia bez rejestracji klienta: #val# " . $waluty->ZwrocSymbolWalutyKod($TabWynik[$v]['walutaKod']) );
        $Wartosci_id_2[] = $tmp;        
        //        
        // zamowienia z allegro
        $tmp = new bar_value( $TabWynik[$v]['wartosc_3'] );
        $tmp->set_tooltip( "Zamówienia z Allegro: #val# " . $waluty->ZwrocSymbolWalutyKod($TabWynik[$v]['walutaKod']) );
        $Wartosci_id_3[] = $tmp;        
        //        
        // zamowienia reczne
        $tmp = new bar_value( $TabWynik[$v]['wartosc_4'] );
        $tmp->set_tooltip( "Zamówienia ręczne: #val# " . $waluty->ZwrocSymbolWalutyKod($TabWynik[$v]['walutaKod']) );
        $Wartosci_id_4[] = $tmp;        
        //        
        for ($n = 1; $n < 5; $n++) {
            //
            if ($TabWynik[$v]['wartosc_' . $n] > $WartoscMax) {
                $WartoscMax = $TabWynik[$v]['wartosc_' . $n];
            }
            //
        }
    }   
    //
    if ($WartoscMax == 0) {
        $WartoscMax = 5;
    }
    //
    $WartoscMax = round($WartoscMax + ($WartoscMax*0.2));
    $Podzialka = (int)($WartoscMax / 5);
    
    $title = new title( 'Wartość sprzedaży wg typów zamówień' );
    $title->set_style( "{font-size: 13px; font-family: Tahoma; font-weight: normal; color: #797979; text-align: center; margin-bottom:10px;}" );    
    
    // tworzenie wykresu
    
    // zamowienia z rejestracja klienta
    $bar_1 = new bar_filled( '#87BD3A', '#A5B8AC' );
    $bar_1->set_values( $Wartosci_id_1 );
    
    // zamowienia bez rejestracji klienta
    $bar_2 = new bar_filled( '#4CB5E0', '#2B8DB6' );
    $bar_2->set_values( $Wartosci_id_2 );

    // zamowienia z allegro
    $bar_3 = new bar_filled( '#FBAE21', '#D58E0D' );
    $bar_3->set_values( $Wartosci_id_3 ); 

    // zamowienia reczne
    $bar_4 = new bar_filled( '#ED56EB', '#BE33BC' );
    $bar_4->set_values( $Wartosci_id_4 );    

    // animacja
    $bar_1->set_on_show(new bar_on_show('pop', 0.5, 0.2));
    $bar_2->set_on_show(new bar_on_show('pop', 0.5, 0.2));
    $bar_3->set_on_show(new bar_on_show('pop', 0.5, 0.2));
    $bar_4->set_on_show(new bar_on_show('pop', 0.5, 0.2));

    $chart = new open_flash_chart();
    $chart->set_title( $title );
    $chart->add_element( $bar_1 );
    $chart->add_element( $bar_2 );
    $chart->add_element( $bar_3 );
    $chart->add_element( $bar_4 );    
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
