<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_PROMOCJE_OKNA_ZLOZONE_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20}}
// {{MODUL_PROMOCJE_OKNA_ZLOZONE_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;1,2,3,4,5}}
// {{MODUL_PROMOCJE_OKNA_ZLOZONE_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
// {{MODUL_PROMOCJE_OKNA_ZLOZONE_OPIS;Czy wyświetać opis i dostępność produktu;tak;tak,nie}}
// {{MODUL_PROMOCJE_OKNA_ZLOZONE_PRODUCENT;Czy wyświetać producenta;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$IloscKolumn = 2;
$MoznaKupic = 'tak';
$WyswietlOpis = 'tak';
$WyswietlProducent = 'tak';

if ( defined('MODUL_PROMOCJE_OKNA_ZLOZONE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_PROMOCJE_OKNA_ZLOZONE_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_PROMOCJE_OKNA_ZLOZONE_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_PROMOCJE_OKNA_ZLOZONE_ILOSC_KOLUMN;
}
if ( defined('MODUL_PROMOCJE_OKNA_ZLOZONE_KUPOWANIE') ) {
   $MoznaKupic = MODUL_PROMOCJE_OKNA_ZLOZONE_KUPOWANIE;
}
if ( defined('MODUL_PROMOCJE_OKNA_ZLOZONE_OPIS') ) {
   $WyswietlOpis = MODUL_PROMOCJE_OKNA_ZLOZONE_OPIS;
}
if ( defined('MODUL_PROMOCJE_OKNA_ZLOZONE_PRODUCENT') ) {
   $WyswietlProducent = MODUL_PROMOCJE_OKNA_ZLOZONE_PRODUCENT;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'promocje');

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
        $Produkt->ProduktDostepnosc();
        $Produkt->ProduktProducent();
        //                      
        echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
        //
        echo '<div class="ProdCena" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+40) . 'px">';
        
        echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
            
        // czy jest producent
        if (!empty($Produkt->producent['nazwa']) && $WyswietlProducent == 'tak') {
          echo '<b class="Producent"><em>{__TLUMACZ:PRODUCENT}:</em> ' . $Produkt->producent['link'] . '</b>';
        }    

        if ( $MoznaKupic == 'tak' ) {
      
          // elementy kupowania
          $Produkt->ProduktKupowanie();                  

          // jezeli jest aktywne kupowanie produktow
          if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
              //
              echo '<div class="Zakup">';
              
              echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
              
              echo '</div>';
              //
          }            

        }              

        echo '</div>';
        //
        echo '<div class="cl"></div>';
        
        if ( $WyswietlOpis == 'tak' ) {
        
              echo '<div class="Opis LiniaGorna">';
              
              // czy jest dostepnosc produktu
              if (!empty($Produkt->dostepnosc['dostepnosc'])) {
                  //
                  // jezeli dostepnosc jest obrazkiem wyswietli tylko obrazek
                  if ( $Produkt->dostepnosc['obrazek'] == 'tak' ) {
                      //
                      echo '<b class="Dostepnosc">' . $Produkt->dostepnosc['dostepnosc'] . '</b>';
                    } else {
                      echo '<b class="Dostepnosc"><em>{__TLUMACZ:DOSTEPNOSC}:</em> ' . $Produkt->dostepnosc['dostepnosc'] . '</b>';
                      //
                  }
              }

              echo $Produkt->info['opis_krotki'] . '</div>';
              
        }
        
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

unset($WybraneProdukty, $IloscKolumn, $LimitZapytania, $MoznaKupic, $WyswietlOpis, $WyswietlProducent);
?>