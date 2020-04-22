<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;1,2,3,4,5,6,7,8,9,10}}
// {{MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;1,2,3,4,5}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$IloscKolumn = 2;

if ( defined('MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_PRODUKTY_Z_RECENZJAMI_ILOSC_KOLUMN;
}

$WybraneProdukty = Produkty::ProduktyModuloweRecenzje($LimitZapytania);

if (count($WybraneProdukty) > 0) {
      
    $LicznikKolumn = 1;
    $LicznikWierszy = 1;
    
    // okresla szerokosc pojedynczego produktu w listingu - w %
    $SzerokoscPola = (int)(100 / $IloscKolumn);
    //   

    // jezeli jest szablon rwd musi utworzyc kontener diva ktory bedzie zawieral klase ilosci kolumn w najwiekszej rozdzielczosci
    if ( $_SESSION['rwd'] == 'tak' ) {
    
        echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';
    
    }      

    for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
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
             echo '<article class="ProduktZlozony LiniaPrawa" style="width:' . $SzerokoscPola . '%">';
             //
           } else {
             //
             echo '<article class="ProduktZlozony OknoRwd">';
             //
        }

        //
        $Produkt = new Produkt( $WybraneProdukty[$v] );
        $Produkt->ProduktRecenzje();
        //                      
        echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
        //
        echo '<div class="ProdCena" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+40) . 'px">';
        
        echo '<h3>' . $Produkt->info['link'] . '</h3>';
        //
        echo '<div class="Ocena">' . $Produkt->recenzjeSrednia['srednia_ocena_obrazek'];
        
        echo '<span>{__TLUMACZ:SREDNIA_OCENA_PRODUKTU}: <strong>' .$Produkt->recenzjeSrednia['srednia_ocena'] . '/5 </strong> <br /> ({__TLUMACZ:ILOSC_GLOSOW}: ' . $Produkt->recenzjeSrednia['ilosc_glosow'] . ')</span>';
        
        echo '</div>';          
        //
        echo '</div>';
        
        echo '<div class="cl"></div>';
        
        echo '<div class="Opis LiniaGorna">' . $Produkt->info['opis_krotki'] . '</div>';
        //
        unset($Produkt);
        //

        echo '</article>';
        //
        // ************************ wyglad produktu - koniec **************************
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
        $IleProduktowZostalo = ($cs - (($LicznikWierszy - 1) * $IloscKolumn));
        if ($IleProduktowZostalo < $IloscKolumn && $IleProduktowZostalo > 0) {
           // tworzy puste komorki
           for ($v = 1; $v <= ($IloscKolumn - $IleProduktowZostalo); $v++) {
                echo '<div class="ProduktZlozony" style="border:0px;width:' . $SzerokoscPola . '%"></div>';
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

    unset($IleProduktowZostalo, $LicznikWierszy, $LicznikKolumn, $SzerokoscPola);
      
}

unset($WybraneProdukty, $IloscKolumn, $LimitZapytania);
?>