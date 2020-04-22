<?php

$LimitZapytania = 4;

$TablicaArtykulow = Aktualnosci::TablicaAktualnosciLimit( $LimitZapytania );

$IloscArtykulow = count($TablicaArtykulow);

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
        // ************************ wyglad artykulu - poczatek **************************
        //
        echo '<div class="PozycjaModulu">';
        
          echo '<div class="PozycjaMargines">';

              //
              echo '<h2>' . $Artykul['link'] . '</h2>';
              //
              echo '<span class="Aktualnosc">';

              echo '<em>' . $Artykul['data'] . '</em>';

              echo '</span>';   
              //          
              echo $Artykul['opis_krotki'];
              //
              // czy pokazywac przycisk szczegolow 
              if (strlen($Artykul['opis']) > 10) {
                  echo '<div class="cl"></div><a href="' . $Artykul['seo'] . '" class="przycisk MargPrzycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a>';
              }
              //
              
          echo '</div>';
        
        echo '</div>';
        //
        // ************************ wyglad artykulu - koniec **************************
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
    
    echo '<div class="cl"></div>';
      
}

unset($IloscArtykulow, $LiczniKolumn, $LimitZapytania, $TablicaArtykulow);
?>