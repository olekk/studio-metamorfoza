<?php
// czy jest zapytanie
if ($IloscArtykulow > 0) { 

    $LiczniKolumn = 1;

    foreach ( $TablicaArtykulow as $Artykul ) {
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
                echo '<h2>' . $Artykul['link'] . '</h2>';
                //
                echo '<span class="DaneAktualnosci">';
                // czy pokazywac date dodania artykulu
                if ( AKTUALNOSCI_DATA_LISTING == 'tak' ) {
                    echo '<em class="DataDodania">' . $Artykul['data'] . '</em>';
                }    
                // czy pokazywac ilosc odslon
                if ( AKTUALNOSCI_ILOSC_ODSLON_LISTING == 'tak' ) {
                    echo '<em class="IloscOdslon">{__TLUMACZ:ILOSC_WYSWIETLEN} ' . $Artykul['wyswietlenia'] . '</em>';
                }      
                echo '</span>';   
                //          
                echo $Artykul['opis_krotki'];
                //
                // czy pokazywac przycisk szczegolow 
                if (AKTUALNOSCI_PRZYCISK_ZOBACZ_LISTING == 'tak' && strlen($Artykul['opis']) > 10) {
                    echo '<div class="cl"></div><a href="' . $Artykul['seo'] . '" class="przycisk MargPrzycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a>';
                }
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

    unset($LiczniKolumn);
    
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_ARTYKULOW}</div>';
  
}

unset($IloscArtykulow);

?>