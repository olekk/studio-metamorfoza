<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    $LiczniKolumn = 1;

    foreach ( $GLOBALS['schowekKlienta']->IloscProduktowTablicaId AS $IdSchowka ) {
    
        $Produkt = new Produkt( $IdSchowka );
                  
        //
        // sekcja wiersza
        // jezeli nowa kolumna to tworzy nowa sekcje
        if ( $LiczniKolumn == 1 ) {
             //
             echo '<div class="SekcjaModulu LiniaDolnaSekcji">';
             //
        }
        //
        
        if ( $Produkt->CzyJestProdukt ) {

            echo '<div class="PozycjaModulu">';
            
                echo '<div class="PozycjaMargines Produkt">';
           
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
                    
                    echo '<br /> <span class="UsunSchowek" onclick="UsunZeSchowka(' . $Produkt->info['id'] . ')">{__TLUMACZ:SCHOWEK_USUN_ZE_SCHOWKA}</span>';                

                echo '</div>';

            echo '</div>';
            //

        }
        
        //
        unset($Produkt);                
        
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
    
    unset($LiczniKolumn);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUKTOW}</div>';
  
}

unset($IloscProduktow);
?>