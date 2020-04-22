<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_PROMOCJE_PRZEWIJANE_ILOSC_PRODUKTOW;Ilość wyświetlanych w produktów;4;4,6,8,9,10,12,15,18,20}}
// {{MODUL_PROMOCJE_PRZEWIJANE_ILOSC_KOLUMN;W ilu kolumnach mają być wyświetlane produkty;2;2,3,4}}
// {{MODUL_PROMOCJE_PRZEWIJANE_ANIMACJA;Czy produkty mają się same animować;nie;tak,nie}}
// {{MODUL_PROMOCJE_PRZEWIJANE_RODZAJ_ANIMACJI;W jaki sposób mają być animowane produkty;zanikanie;zanikanie,animacja w pionie,animacja w poziomie}}
// {{MODUL_PROMOCJE_PRZEWIJANE_CZAS_CO_ILE;Co ile sekund ma się zmieniać animacja;4;3,4,5,6,7,8,9,10,12,15}}
// {{MODUL_PROMOCJE_PRZEWIJANE_KUPOWANIE;Czy wyświetać możliwość zakupu produktu;tak;tak,nie}}
//

// zmienne bez definicji
$LimitZapytania = 6;
$Animowac = 'nie';
$RodzajAnimacji = 'zanikanie';
$IloscKolumn = 2;
$CzasAnimacji = 5000;
$MoznaKupic = 'tak';

if ( defined('MODUL_PROMOCJE_PRZEWIJANE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_PROMOCJE_PRZEWIJANE_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_PROMOCJE_PRZEWIJANE_ILOSC_KOLUMN') ) {
   $IloscKolumn = (int)MODUL_PROMOCJE_PRZEWIJANE_ILOSC_KOLUMN;
}
if ( defined('MODUL_PROMOCJE_PRZEWIJANE_ANIMACJA') ) {
   $Animowac = MODUL_PROMOCJE_PRZEWIJANE_ANIMACJA;
}
if ( defined('MODUL_PROMOCJE_PRZEWIJANE_RODZAJ_ANIMACJI') ) {
    $RodzajAnimacji = MODUL_PROMOCJE_PRZEWIJANE_RODZAJ_ANIMACJI;
}
switch ($RodzajAnimacji) {
    case "zanikanie":
        $RodzajAnimacji = 'fade';
        break;
    case "animacja w pionie":
        $RodzajAnimacji = 'scrolltop';
        break;        
    case "animacja w poziomie":
        $RodzajAnimacji = 'scrollleft';
        break;             
}
if ( defined('MODUL_PROMOCJE_PRZEWIJANE_CZAS_CO_ILE') ) {
   $CzasAnimacji = (int)MODUL_PROMOCJE_PRZEWIJANE_CZAS_CO_ILE * 1000;
}
if ( defined('MODUL_PROMOCJE_PRZEWIJANE_KUPOWANIE') ) {
   $MoznaKupic = MODUL_PROMOCJE_PRZEWIJANE_KUPOWANIE;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'promocje');

if (count($WybraneProdukty) > 3) { 
      
    // okresla szerokosc pojedynczego produktu w listingu - w %
    $SzerokoscPola = (int)(100 / $IloscKolumn);
    //      
    
    echo '<div id="ModulPromocjePrzewijane" class="AnimModul">';

    echo '<div class="StronyAnim">';

    for ( $f = 1; $f <= ceil(count($WybraneProdukty) / $IloscKolumn); $f++ ) {
         echo '<b ' . (($f == 1) ? 'class="On"' : '') . '>' . $f . '</b>';
    }
    
    echo '</div>';
    
    echo '<div class="cl"></div>';
    
    echo '<ul><li>';
    
        echo '<div class="ElementyAnimacji Kol-' . $IloscKolumn . '">';
    
        for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
        
            //
            // ************************ wyglad produktu - poczatek **************************
            //
            // rozne css w zaleznosci od szablonu
            if ( $_SESSION['rwd'] == 'nie' ) {
                 //
                 // ostatniemu elementowi w wierszu nie doda klasy z linia
                 echo '<article class="ProduktProsty' . ( (($v + 1)%$IloscKolumn == 0) ? '' : ' LiniaPrawa' ) . '">';
                 //
               } else {
                 //
                 echo '<article class="ProduktProsty">';
                 //
            }
            
            $Produkt = new Produkt( $WybraneProdukty[$v] );
            //              
            echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
            //
            echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
            //
            if ( $MoznaKupic == 'tak' ) {
          
              // elementy kupowania
              $Produkt->ProduktKupowanie();                  
          
              echo '<div class="Zakup">';
              
                  // jezeli jest aktywne kupowanie produktow
                  if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                      //
                      echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                      //
                  }            
                  
              echo '</div>'; 

            }
              
            unset($Produkt);

            echo '</article>';
            
            //
            // ************************ wyglad produktu - koniec **************************
            //

        }
    
        echo '</div>';
    
    echo '</li></ul>';
    
    echo '<div class="cl"></div>';
    
    echo '</div>';
    
    echo Wyglad::PrzegladarkaJavaScript( "$('#ModulPromocjePrzewijane').ModulPrzewijanie( { modul: 'ModulPromocjePrzewijane', id: 'mpp', typ: '" . $RodzajAnimacji . "', czas: " . $CzasAnimacji . ", animacja: '" . $Animowac . "', kolumny: " . $IloscKolumn . " } );" );    

    unset($SzerokoscPola);
      
}

unset($CzasAnimacji, $WybraneProdukty, $IloscKolumn, $LimitZapytania, $RodzajAnimacji, $Animowac, $CzasAnimacji);
?>