<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_BANNER_ANIMACJA_FANCY_ILOSC_GRAFIK;Ilość wyświetlanych grafik w animacji;5;1,2,3,4,5,6,7,8,9,10}}
// {{MODUL_BANNER_ANIMACJA_FANCY_GRUPA;Grupa wyświetlanych bannerów;ANIMACJA_SRODKOWA;BoxyModuly::ListaGrupBannerow()}}
// {{MODUL_BANNER_ANIMACJA_FANCY_WYSOKOSC;Wysokość animacji;250;100,150,180,200,220,250,270,300,320,350,380,400,450}}
// {{MODUL_BANNER_ANIMACJA_FANCY_EFEKT;Efekt animacji;fala od dolu;gora falowanie,przemiennie,kurtyna,fontanna od gory,losowo od gory,fala od dolu}}
// {{MODUL_BANNER_ANIMACJA_FANCY_NAWIGACJA;Czy pokazywać elementy nawigacyjne;tak;tak,nie}}
// {{MODUL_BANNER_ANIMACJA_FANCY_CZAS;Czas w sekundach pomiedzy kolejnymi grafikami;5;1,2,3,4,5,6,7,8,9,10,15,20}}
//

// zmienne bez definicji
$LimitZapytania = 5;
$grupaBannerow = 'ANIMACJA_SRODKOWA';
$animacjaWysokosc = 250;
$efektAnimacji = 'fala od dolu';
$nawigacja = 'tak';
$czasAnimacji = 5;

if ( defined('MODUL_BANNER_ANIMACJA_FANCY_ILOSC_GRAFIK') ) {
   $LimitZapytania = (int)MODUL_BANNER_ANIMACJA_FANCY_ILOSC_GRAFIK;
}
if ( defined('MODUL_BANNER_ANIMACJA_FANCY_GRUPA') ) {
   $grupaBannerow = MODUL_BANNER_ANIMACJA_FANCY_GRUPA;
}
if ( defined('MODUL_BANNER_ANIMACJA_FANCY_WYSOKOSC') ) {
   $animacjaWysokosc = MODUL_BANNER_ANIMACJA_FANCY_WYSOKOSC;
}
if ( defined('MODUL_BANNER_ANIMACJA_FANCY_EFEKT') ) {
   $efektAnimacji = MODUL_BANNER_ANIMACJA_FANCY_EFEKT;
}
if ( defined('MODUL_BANNER_ANIMACJA_FANCY_NAWIGACJA') ) {
   $nawigacja = MODUL_BANNER_ANIMACJA_FANCY_NAWIGACJA;
}
$nawigacja = ( ($nawigacja == 'tak') ? 'true' : 'false' );

if ( defined('MODUL_BANNER_ANIMACJA_FANCY_CZAS') ) {
   $czasAnimacji = MODUL_BANNER_ANIMACJA_FANCY_CZAS;
}

switch ($efektAnimacji) {
    case 'gora falowanie':
        $efektAnimacji = 'effect: "wave"';
        break;
    case 'przemiennie':
        $efektAnimacji = 'effect: "zipper"';
        break;
    case 'kurtyna':
        $efektAnimacji = 'effect: "curtain"';
        break;
    case 'fontanna od gory':
        $efektAnimacji = 'position: "top", direction: "fountain"';
        break;    
    case 'losowo od gory':
        $efektAnimacji = 'position: "top", direction: "random"';
        break;  
    case 'fala od dolu':
        $efektAnimacji = 'position: "bottom", direction: "right"';
        break;        
}

if ( isset($GLOBALS['bannery']->info[$grupaBannerow]) ) {

    echo '<script type="text/javascript" src="programy/fancyTransitions/jqFancyTransitions.js"></script>';

    $Tablica = $GLOBALS['bannery']->info[$grupaBannerow];

    if ( count($Tablica) > 0 ) {

      $wybrane_bannery = Funkcje::wylosujElementyTablicyJakoTablica($Tablica,$LimitZapytania);
      
      echo '<div id="fancy-slider">';

      foreach ($wybrane_bannery as $banner ) {

            $GLOBALS['bannery']->bannerWyswietlAnimowanyFancySlider($banner);
      }
      
      echo '</div>';
      
      unset($wybrane_bannery);

    }
    
    echo '<script>
	  $(document).ready(function() {
       var szerNadrzedna = $("#fancy-slider").parent().width();
       $("#fancy-slider").jqFancyTransitions({ width: szerNadrzedna, height:' . $animacjaWysokosc . ', links: true, delay:' . ($czasAnimacji * 1000) . ', ' . $efektAnimacji . ', navigation:' . $nawigacja . ' });
	  });
    </script>';
    
    unset($Tablica);
    
}

unset($LimitZapytania, $grupaBannerow, $animacjaWysokosc, $efektAnimacji, $nawigacja, $czasAnimacji);

?>