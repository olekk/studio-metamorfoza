<?php
// czy jest zapytanie
if (count($TablicaProducentow) > 0) {  

    $LiczniKolumn = 1;

    foreach ($TablicaProducentow As $IdProducenta => $TablicaDane) {
        //
        // sekcja wiersza
        // jezeli nowa kolumna to tworzy nowa sekcje
        if ( $LiczniKolumn == 1 ) {
             //
             echo '<div class="SekcjaModulu LiniaDolnaSekcji">';
             //
        }
        //

        echo '<div class="PozycjaModulu">';
        
            echo '<div class="PozycjaMargines" style="text-align:center">';

                //
                echo '<div style="height:' . (WYSOKOSC_OBRAZEK_MALY + 10) . 'px">';
                //
                if ( !empty($TablicaDane['Foto']) ) {
                    //
                    echo '<a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdProducenta, 'producent' ) . '">' . Funkcje::pokazObrazek($TablicaDane['Foto'], $TablicaDane['Nazwa'], SZEROKOSC_OBRAZEK_MALY, WYSOKOSC_OBRAZEK_MALY) . '</a>';
                    //
                }
                //
                echo '</div>';
                
                // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
                $SumaProduktow = '';
                if (LISTING_ILOSC_PRODUKTOW == 'tak') {
                    $SumaProduktow = ' ('.$TablicaDane['IloscProduktow'] . ')';
                }            
                //
                echo '<h3><a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdProducenta, 'producent' ) . '">' . $TablicaDane['Nazwa'] . $SumaProduktow . '</a></h3>';
                //
            
            echo '</div>';

        echo '</div>';
        //

        // zamykanie sekcji
        if ($LiczniKolumn == 2) {
            //
            echo '<div class="cl"></div></div>';      
            $LiczniKolumn = 0;
            //
        }

        $LiczniKolumn++;
    }
    
    if ( $LiczniKolumn == 2 ) { 
         //
         echo '<div class="cl"></div></div>';      
         //
    }    

    unset($LiczniKolumn, $info);
    
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_ZDJEC_GALERII}</div>';
  
}

?>