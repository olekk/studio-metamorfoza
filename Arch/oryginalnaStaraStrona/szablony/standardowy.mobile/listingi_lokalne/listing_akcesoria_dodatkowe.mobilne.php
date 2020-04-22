<?php
if ($IloscProduktow > 0) {
      
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
        
            echo '<div class="PozycjaMargines Produkt">';

                $ProduktAkcesoria = new Produkt( $info['products_id'] );
                //              
                echo '<div class="Foto">'.$ProduktAkcesoria->fotoGlowne['zdjecie_link'].'</div>';
                //
                echo '<h3>' . $ProduktAkcesoria->info['link'] . '</h3>' . $ProduktAkcesoria->info['cena'];

                // elementy kupowania
                $ProduktAkcesoria->ProduktKupowanie();                  
            
                echo '<div class="Zakup">';
                
                    // jezeli jest aktywne kupowanie produktow
                    if ($ProduktAkcesoria->zakupy['mozliwe_kupowanie'] == 'tak' || $ProduktAkcesoria->zakupy['pokaz_koszyk'] == 'tak') {
                        //
                        echo $ProduktAkcesoria->zakupy['input_ilosci'] . $ProduktAkcesoria->zakupy['przycisk_kup'];
                        //
                    }            
                    
                echo '</div>'; 

                //
                unset($ProduktAkcesoria);
              
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
      
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);
?>