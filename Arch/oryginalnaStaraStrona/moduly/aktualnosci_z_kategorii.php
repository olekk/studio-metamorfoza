<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_AKTUALNOSCI_Z_KATEGORII_KATEGORIA;Z jakiej kategorii mają być wyświetlone artykuły;0;BoxyModuly::ListaKategoriiAktualnosci()}}
// {{MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane artykuły;2;1,2,3,4,5}}
// {{MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_ODSLON_LISTING;Czy w pokazywać ilość odsłon artykułu;tak;tak,nie}}
// {{MODUL_AKTUALNOSCI_Z_KATEGORII_DATA;Czy w pokazywać datę dodania artykułu;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 4;
$IloscKolumn = 2;
$IloscOdslon = 'tak';
$WyswietlDate = 'tak';

if ( defined('MODUL_AKTUALNOSCI_Z_KATEGORII_KATEGORIA') ) {
   $IdKategorii = MODUL_AKTUALNOSCI_Z_KATEGORII_KATEGORIA;
 } else {
   $IdKategorii = '0';
}
if ( defined('MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_ARTYKULOW') ) {
   $LimitZapytania = (int)MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_ARTYKULOW;
}
if ( defined('MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_KOLUMN;
}
if ( defined('MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_ODSLON_LISTING') ) {
   $IloscOdslon = MODUL_AKTUALNOSCI_Z_KATEGORII_ILOSC_ODSLON_LISTING;
}
if ( defined('MODUL_AKTUALNOSCI_Z_KATEGORII_DATA') ) {
   $WyswietlDate = MODUL_AKTUALNOSCI_Z_KATEGORII_DATA;
}

$TablicaArtykulow = Aktualnosci::TablicaAktualnosciKategoria( $IdKategorii, $LimitZapytania );

$IloscArtykulow = count($TablicaArtykulow);

if ($IloscArtykulow > 0) {

    $LicznikKolumn = 1;
    $LicznikWierszy = 1;
    
    // okresla szerokosc pojedynczego artykulu w listingu - w %
    $SzerokoscPola = (int)(100 / $IloscKolumn);
    //      
    
    // jezeli jest szablon rwd musi utworzyc kontener diva ktory bedzie zawieral klase ilosci kolumn w najwiekszej rozdzielczosci
    if ( $_SESSION['rwd'] == 'tak' ) {
    
        echo '<div class="OknaRwd Kol-' . $IloscKolumn . '">';
    
    }    
    
    foreach ( $TablicaArtykulow as $Artykul ) {
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
        // ************************ wyglad artykulu - poczatek **************************
        //
        // rozne css w zaleznosci od szablonu
        if ( $_SESSION['rwd'] == 'nie' ) {
             //
             echo '<article class="AktProsta LiniaPrawa" style="width:' . $SzerokoscPola . '%">';
             //
           } else {
             //
             echo '<article class="AktProsta OknoRwd">';
             //
        }

        //
        echo '<h2>' . $Artykul['link'] . '</h2>';
        //
        echo '<span class="DaneAktualnosci">';
        // czy pokazywac date dodania artykulu
        if ( $WyswietlDate == 'tak' ) {
            echo '<em class="DataDodania">' . $Artykul['data'] . '</em>';
        }    
        // czy pokazywac ilosc odslon
        if ( $IloscOdslon == 'tak' ) {
            echo '<em class="IloscOdslon">{__TLUMACZ:ILOSC_WYSWIETLEN} ' . $Artykul['wyswietlenia'] . '</em>';
        }      
        echo '</span>';   
        //          
        echo $Artykul['opis_krotki'];
        //
        // czy pokazywac przycisk szczegolow 
        if (strlen($Artykul['opis']) > 10) {
            echo '<div class="cl"></div><a href="' . $Artykul['seo'] . '" class="przycisk MargPrzycisk">{__TLUMACZ:PRZYCISK_PRZECZYTAJ_CALOSC}</a>';
        }
        //
        
        echo '</article>';
        //
        // ************************ wyglad artykulu - koniec **************************
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
        $IleZostalo = ($IloscArtykulow - (($LicznikWierszy - 1) * $IloscKolumn));
        if ($IleZostalo < $IloscKolumn && $IleZostalo > 0) {
           // tworzy puste komorki
           for ($v = 1; $v <= ($IloscKolumn - $IleZostalo); $v++) {
                echo '<div class="AktProsta" style="border:0px;width:' . $SzerokoscPola . '%"></div>';
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
      
}

unset($IdKategorii, $IloscArtykulow, $IloscKolumn, $IloscOdslon, $WyswietlDate, $TablicaArtykulow);
?>