<?php
// czy jest zapytanie
if ($IloscObrazkow > 0) { 

    $LiczniKolumn = 1;

    while ($info = $sql->fetch_assoc()) {
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
        
            echo '<div class="PozycjaMargines">';

                //
                if (is_file(KATALOG_ZDJEC . '/' . $info['gallery_image'])) {
                    echo '<div style="height:' . ($WysImg + 10) . 'px">';          
                    echo '<a class="ZdjecieGalerii" href="' . KATALOG_ZDJEC . '/' . $info['gallery_image']. '" title="' . $info['gallery_image_alt'] . '">' . Funkcje::pokazObrazek($info['gallery_image'], $info['gallery_image_alt'], $SzeImg, $WysImg) . '</a>';   
                    echo '</div>';
                }
                //
                echo $info['gallery_image_description'];
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