<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    $LiczniWierszy = 1;

    while ($info = $sql->fetch_assoc()) {
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        echo '<div class="Lista LiniaDolna">';

            //
            $ProduktLista = new Produkt( $info['products_id'], SZEROKOSC_LISTING_KARTA_PRODUKTU, WYSOKOSC_LISTING_KARTA_PRODUKTU ); 
            //
            echo '<div class="Foto">'.$ProduktLista->fotoGlowne['zdjecie_link_ikony'].'</div>';
            //
            echo '<div class="ProdCena" style="margin-left:' . (SZEROKOSC_LISTING_KARTA_PRODUKTU+20) . 'px">';            

            echo '<h3>' . $ProduktLista->info['link'] . '</h3>' . $ProduktLista->info['cena'];

            echo '</div><div class="cl"></div>';
            //
            unset($ProduktLista);
            //
            
        echo '</div>';
        //
        // ************************ wyglad produktu - koniec **************************
        //
        
        $LiczniWierszy++;
                
    }

    unset($info, $LiczniWierszy);
    
    // nie kasowac !!
    $IleBedzieKolumn++;
      
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>