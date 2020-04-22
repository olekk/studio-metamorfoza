<?php
// czy jest zapytanie
if ($IloscObrazkow > 0) { 

    $LicznikKolumn = 1;
    $LicznikWierszy = 1;
    
    // okresla szerokosc pojedynczego artykulu w listingu - w %
    $SzerokoscPola = (int)(100 / $IloscKolumn);
    //    
    
    // jezeli jest szablon rwd musi utworzyc kontener diva ktory bedzie zawieral klase ilosci kolumn w najwiekszej rozdzielczosci
    if ( $_SESSION['rwd'] == 'tak' ) {
    
        echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';
    
    }     

    while ($info = $sql->fetch_assoc()) {
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
        // ************************ wyglad zdjecia - poczatek **************************
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
        if (is_file(KATALOG_ZDJEC . '/' . $info['gallery_image'])) {
            echo '<div style="height:' . ($WysImg + 10) . 'px">';

            if ( TEKST_COPYRIGHT_POKAZ == 'tak' || OBRAZ_COPYRIGHT_POKAZ == 'tak' ) {
                $zdjecie = Funkcje::pokazObrazekWatermark($info['gallery_image']);
            } else {
                $zdjecie = KATALOG_ZDJEC . '/' . $info['gallery_image'];
            }                
            
            echo '<a class="ZdjecieGalerii" href="' . $zdjecie . '" title="' . $info['gallery_image_alt'] . '">' . Funkcje::pokazObrazek($info['gallery_image'], $info['gallery_image_alt'], $SzeImg, $WysImg) . '</a>';   
            
            unset($zdjecie);
            
            echo '</div>';
        }
        //
        echo $info['gallery_image_description'];
        //

        echo '</article>';
        //
        // ************************ wyglad zdjecia - koniec **************************
        //
        // zamykanie sekcji - jezeli szablon nie jest rwd
        if ( $_SESSION['rwd'] == 'nie' ) {
            //          
            if ($LicznikKolumn == $IloscKolumn) {
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
        $IleZostalo = ($IloscObrazkow - (($LicznikWierszy - 1) * $IloscKolumn));
        if ($IleZostalo < $IloscKolumn && $IleZostalo > 0) {
             // tworzy puste komorki
             for ($v = 1; $v <= ($IloscKolumn - $IleZostalo); $v++) {
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
    
    unset($SzerokoscPola, $info, $LicznikKolumn, $LicznikWierszy, $IleZostalo);
    
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_ZDJEC_GALERII}</div>';
  
}

unset($WysImg, $SzeImg, $IloscKolumn); 
   
?>