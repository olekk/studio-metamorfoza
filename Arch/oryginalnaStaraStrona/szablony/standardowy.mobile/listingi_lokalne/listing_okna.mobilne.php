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

                $Produkt = new Produkt( $info['products_id'] );
                //              
                echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link'].'</div>';
                //
                echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];

                // elementy kupowania
                $Produkt->ProduktKupowanie();                  
            
                echo '<div class="Zakup">';
                
                    // jezeli jest aktywne kupowanie produktow
                    if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                        //
                        echo $Produkt->zakupy['input_ilosci'] . $Produkt->zakupy['przycisk_kup'];
                        //
                    }            
                    
                echo '</div>'; 
                
                // jezeli jest aktywne dodawanie do schowka
                if (PRODUKT_SCHOWEK_STATUS == 'tak') {
                    //
                    echo '<br /> <span class="DoSchowka" onclick="DoSchowka(' . $Produkt->info['id'] . ')">{__TLUMACZ:LISTING_DODAJ_DO_SCHOWKA}</span>';
                    //
                }                

                //
                unset($Produkt);
              
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

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUKTOW}</div>';
  
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);
?>