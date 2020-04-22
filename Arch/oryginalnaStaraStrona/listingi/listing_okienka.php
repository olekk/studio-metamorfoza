<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    $LicznikKolumn = 1;
    $LicznikWierszy = 1;
    
    // okresla szerokosc pojedynczego produktu w listingu - w %
    $SzerokoscPola = (int)(100 / LISTING_ILOSC_KOLUMN);
    //    
    
    // jezeli jest szablon rwd musi utworzyc kontener diva ktory bedzie zawieral klase ilosci kolumn w najwiekszej rozdzielczosci
    if ( $_SESSION['rwd'] == 'tak' ) {
    
        echo '<div class="OknaRwd Kol-' . LISTING_ILOSC_KOLUMN . '">';
    
    }    

    while ($info = $sql->fetch_assoc()) {
        //
        // jezeli szablon nie jest rwd tworzy kontener elementow
        if ( $_SESSION['rwd'] == 'nie' ) {
            //
            // jezeli nowa kolumna to tworzy nowa sekcje
            if ( $LicznikKolumn == 1 ) {
                 //
                 echo '<div class="SekcjaRowna LiniaDolna">';
                 //
            }
            //
        }
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        // rozne css w zaleznosci od szablonu
        if ( $_SESSION['rwd'] == 'nie' ) {
             //
             echo '<div class="Okno LiniaPrawa" style="width:' . $SzerokoscPola . '%">';
             //
           } else {
             //
             echo '<div class="Okno OknoRwd">';
             //
        }
        
        // klasa do wysuwanego przycisku animacji
        echo '<div class="AnimacjaZobacz">';

            $Produkt = new Produkt( $info['products_id'] );
            // elementy kupowania
            $Produkt->ProduktKupowanie();                
            //      
            echo '<div class="Zobacz"><strong>' . $Produkt->info['link_szczegoly'] . '</strong></div>'; 
            //        
            echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
            //
            echo '<div class="ProdCena">';
            
                echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
            
                echo '<div class="Zakup">';
                
                    // jezeli jest aktywne kupowanie produktow
                    if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                        //
                        echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                        //
                    }            
                    
                echo '</div>';  
                
                // data dostepnosci
                if ( !empty($Produkt->info['data_dostepnosci']) ) {
                    echo '<div class="DataDostepnosci">{__TLUMACZ:DOSTEPNY_OD_DNIA} <b>' . $Produkt->info['data_dostepnosci'] . '</b></div>';
                }                      
            
            echo '</div>';
            
            unset($Produkt);
            //                
            
        echo '</div>'; 
            
        echo '</div>';
        //
        // ************************ wyglad produktu - koniec **************************
        //
        // zamykanie sekcji - jezeli szablon nie jest rwd
        if ( $_SESSION['rwd'] == 'nie' ) {
            //          
            if ($LicznikKolumn == LISTING_ILOSC_KOLUMN) {
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
        $IleProduktowZostalo = ($IloscProduktow - (($LicznikWierszy - 1) * LISTING_ILOSC_KOLUMN));
        if ($IleProduktowZostalo < LISTING_ILOSC_KOLUMN && $IleProduktowZostalo > 0) {
             // tworzy puste komorki
             for ($v = 1; $v <= (LISTING_ILOSC_KOLUMN - $IleProduktowZostalo); $v++) {
                  echo '<div class="Okno" style="width:' . $SzerokoscPola . '%"></div>';
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
    
    unset($info, $IleProduktowZostalo, $LicznikWierszy, $LicznikKolumn, $SzerokoscPola);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUKTOW}</div>';
  
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>