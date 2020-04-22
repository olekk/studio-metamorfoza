<?php
// czy jest zapytanie
if (count($TablicaProducentow) > 0) { 

    $LicznikKolumn = 1;
    $LicznikWierszy = 1;
    
    // okresla szerokosc pojedynczego pola w listingu - w %
    $SzerokoscPola = (int)(100 / LISTING_ILOSC_KOLUMN_PRODUCENT);
    // 

    // jezeli jest szablon rwd musi utworzyc kontener diva ktory bedzie zawieral klase ilosci kolumn w najwiekszej rozdzielczosci
    if ( $_SESSION['rwd'] == 'tak' ) {
    
        echo '<div class="OknaRwd Kol-' . LISTING_ILOSC_KOLUMN_PRODUCENT . '">';
    
    }         

    foreach ($TablicaProducentow As $IdProducenta => $TablicaDane) {
        //
        // jezeli szablon nie jest rwd tworzy kontener elementow
        if ( $_SESSION['rwd'] == 'nie' ) {
            //
            // jezeli nowa kolumna to tworzy nowa sekcje
            if ( $LicznikKolumn == 1 ) {
                 //
                 echo '<div class="ProducentTbl LiniaDolna">';
                 //
            }
            //
        }  
        //
        // ************************ wyglad producenta **************************
        //
        // rozne css w zaleznosci od szablonu
        if ( $_SESSION['rwd'] == 'nie' ) {
             //
             echo '<div class="Producent LiniaPrawa" style="width:' . $SzerokoscPola . '%">';
             //
           } else {
             //
             echo '<div class="Producent OknoRwd">';
             //
        }
        
        //
        echo '<div style="height:' . (WYSOKOSC_OBRAZEK_MALY + 10) . 'px">';
        //
        if ( !empty($TablicaDane['Foto']) ) {
            //
            echo '<a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdProducenta, 'producent' ) . '">' . Funkcje::pokazObrazek($TablicaDane['Foto'], $TablicaDane['Nazwa'], SZEROKOSC_OBRAZEK_MALY, WYSOKOSC_OBRAZEK_MALY, array(), '', 'maly', true, false, false) . '</a>';
            //
        }
        //
        echo '</div>';
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em>('.$TablicaDane['IloscProduktow'] . ')</em>';
        }            
        //
        echo '<h3><a href="' . Seo::link_SEO( $TablicaDane['Nazwa'], $IdProducenta, 'producent' ) . '">' . $TablicaDane['Nazwa'] . $SumaProduktow . '</a></h3>';
        //

        echo '</div>';
        //
        // ************************ wyglad producenta **************************
        //
        // zamykanie sekcji - jezeli szablon nie jest rwd
        if ( $_SESSION['rwd'] == 'nie' ) {
            //          
            if ($LicznikKolumn == LISTING_ILOSC_KOLUMN_PRODUCENT) {
                //
                echo '</div>';      
                $LicznikKolumn = 0;
                $LicznikWierszy++;
                //
            }
            //
        }

        $LicznikKolumn++;
    }

    // jezeli w ostatnim wierszu jest mniej pozycji niz ilosc kolumn trzeba zamknac sekcje - jezeli szablon nie jest rwd
    if ( $_SESSION['rwd'] == 'nie' ) {
        //
        $IleZostalo = (count($TablicaProducentow) - (($LicznikWierszy - 1) * LISTING_ILOSC_KOLUMN_PRODUCENT));
        if ($IleZostalo < LISTING_ILOSC_KOLUMN_PRODUCENT && $IleZostalo > 0) {
             // tworzy puste komorki
             for ($v = 1; $v <= (LISTING_ILOSC_KOLUMN_PRODUCENT - $IleZostalo); $v++) {
                  echo '<div class="Producent" style="width:' . $SzerokoscPola . '%"></div>';
             }    
             echo '</div>';   
        } 
        //
    } else {
        //
        // jezeli szablon jest rwd to zamyka kontener elementow
        echo '</div>';
        //
        echo '<div class="cl"></div>';
        //
    }    

    unset($IleZostalo, $LicznikWierszy, $LicznikKolumn, $SzerokoscPola);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUCENTOW}</div>';
  
}

?>