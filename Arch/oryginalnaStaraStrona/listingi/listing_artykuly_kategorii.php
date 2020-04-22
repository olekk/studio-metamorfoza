<?php
// czy jest zapytanie
if ($IloscArtykulow > 0) { 

    $LicznikKolumn = 1;
    $LicznikWierszy = 1;
    
    // okresla szerokosc pojedynczego artykulu w listingu - w %
    $SzerokoscPola = (int)(100 / AKTUALNOSCI_ILOSC_KOLUMN);
    //    
    
    // jezeli jest szablon rwd musi utworzyc kontener diva ktory bedzie zawieral klase ilosci kolumn w najwiekszej rozdzielczosci
    if ( $_SESSION['rwd'] == 'tak' ) {
    
        echo '<div class="OknaRwd Kol-' . AKTUALNOSCI_ILOSC_KOLUMN . '">';
    
    }        

    foreach ( $TablicaArtykulow as $Artykul ) {
        //
        // jezeli szablon nie jest rwd tworzy kontener elementow
        if ( $_SESSION['rwd'] == 'nie' ) {
            //
            // jezeli nowa kolumna to tworzy nowa sekcje
            if ( $LicznikKolumn == 1 ) {
                 //
                 echo '<div class="SekcjaRownaOstatnia LiniaDolna">';
                 //
            }
            //
        }
        //
        // ************************ wyglad artykulu - poczatek **************************
        //
        // rozne css w zaleznosci od szablonu
        if ( $_SESSION['rwd'] == 'nie' ) {
             //
             echo '<article class="KomorkaTbl LiniaPrawa" style="width:' . $SzerokoscPola . '%">';
             //
           } else {
             //
             echo '<article class="KomorkaTbl OknoRwd">';
             //
        }
        
        //
        echo '<h2>' . $Artykul['link'] . '</h2>';
        //
        echo '<span class="DaneAktualnosci">';
        
            // czy pokazywac date dodania artykulu
            if ( AKTUALNOSCI_DATA_LISTING == 'tak' ) {
                //
                echo '<em class="DataDodania">' . $Artykul['data'] . '</em>';
                //
            }    
            // czy pokazywac ilosc odslon
            if ( AKTUALNOSCI_ILOSC_ODSLON_LISTING == 'tak' ) {
                //
                echo '<em class="IloscOdslon">{__TLUMACZ:ILOSC_WYSWIETLEN} ' . $Artykul['wyswietlenia'] . '</em>';
                //
            }    
        
        echo '</span>';   
        //        
        echo '<div class="TrescAktualnosci">';
            //
            echo $Artykul['opis_krotki'];
            //
        
            // czy pokazywac przycisk szczegolow 
            if (AKTUALNOSCI_PRZYCISK_ZOBACZ_LISTING == 'tak' && strlen($Artykul['opis']) > 10) {
                //
                echo '<div class="cl"></div><a href="' . $Artykul['seo'] . '" class="przycisk MargPrzycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a>';
                //
            }
            //
        echo '</div>';
        //        
        
        echo '</article>';
        //
        // ************************ wyglad artykulu - koniec **************************
        //
        // zamykanie sekcji - jezeli szablon nie jest rwd
        if ( $_SESSION['rwd'] == 'nie' ) {
            //          
            if ($LicznikKolumn == AKTUALNOSCI_ILOSC_KOLUMN) {
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
        $IleZostalo = ($IloscArtykulow - (($LicznikWierszy - 1) * AKTUALNOSCI_ILOSC_KOLUMN));
        if ($IleZostalo < AKTUALNOSCI_ILOSC_KOLUMN && $IleZostalo > 0) {
             // tworzy puste komorki
             for ($v = 1; $v <= (AKTUALNOSCI_ILOSC_KOLUMN - $IleZostalo); $v++) {
                  echo '<article class="KomorkaTbl" style="width:' . $SzerokoscPola . '%"></article>';
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
    
    unset($SzerokoscPola);
    
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_ARTYKULOW}</div>';
  
}

unset($IleZostalo, $LicznikKolumn, $LicznikWierszy);    
?>