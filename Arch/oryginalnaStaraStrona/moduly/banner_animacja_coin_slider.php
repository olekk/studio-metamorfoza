<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_BANNER_ANIMACJA_ILOSC_GRAFIK;Ilość wyświetlanych grafik w animacji;5;1,2,3,4,5,6,7,8,9,10}}
// {{MODUL_BANNER_ANIMACJA_GRUPA;Grupa wyświetlanych bannerów;ANIMACJA_SRODKOWA;BoxyModuly::ListaGrupBannerow()}}
// {{MODUL_BANNER_ANIMACJA_WYSOKOSC;Wysokość animacji;250;100,150,180,200,220,250,270,300,320,350,380,400,450}}
// {{MODUL_BANNER_ANIMACJA_EFEKT;Efekt animacji;losowo;losowo,wirowanie,od naroznika,od gory dolu}}
// {{MODUL_BANNER_ANIMACJA_NAWIGACJA;Czy pokazywać elementy nawigacyjne;tak;tak,nie}}
// {{MODUL_BANNER_ANIMACJA_CZAS;Czas w sekundach pomiedzy kolejnymi grafikami;5;1,2,3,4,5,6,7,8,9,10,15,20}}
// {{MODUL_BANNER_ANIMACJA_TEKST_EFEKT;Efekt animacji tekstu;zanikanie;zanikanie,przesuwanie}}
//

// zmienne bez definicji
$LimitZapytania = 5;
$grupaBannerow = 'ANIMACJA_SRODKOWA';
$animacjaWysokosc = 250;
$efektAnimacji = 'losowo';
$nawigacja = 'tak';
$czasAnimacji = 5;
$efektAnimacjiTekstu = 'zanikanie';

if ( defined('MODUL_BANNER_ANIMACJA_ILOSC_GRAFIK') ) {
   $LimitZapytania = (int)MODUL_BANNER_ANIMACJA_ILOSC_GRAFIK;
}
if ( defined('MODUL_BANNER_ANIMACJA_GRUPA') ) {
   $grupaBannerow = MODUL_BANNER_ANIMACJA_GRUPA;
}
if ( defined('MODUL_BANNER_ANIMACJA_WYSOKOSC') ) {
   $animacjaWysokosc = MODUL_BANNER_ANIMACJA_WYSOKOSC;
}
if ( defined('MODUL_BANNER_ANIMACJA_EFEKT') ) {
   $efektAnimacji = MODUL_BANNER_ANIMACJA_EFEKT;
}
if ( defined('MODUL_BANNER_ANIMACJA_NAWIGACJA') ) {
   $nawigacja = MODUL_BANNER_ANIMACJA_NAWIGACJA;
}
$nawigacja = ( ($nawigacja == 'tak') ? 'true' : 'false' );

if ( defined('MODUL_BANNER_ANIMACJA_CZAS') ) {
   $czasAnimacji = MODUL_BANNER_ANIMACJA_CZAS;
}
if ( defined('MODUL_BANNER_ANIMACJA_TEKST_EFEKT') ) {
   $efektAnimacjiTekstu = MODUL_BANNER_ANIMACJA_TEKST_EFEKT;
}

switch ($efektAnimacji) {
    case 'losowo':
        $efektAnimacji = 'random';
        break;
    case 'wirowanie':
        $efektAnimacji = 'swirl';
        break;
    case 'od naroznika':
        $efektAnimacji = 'rain';
        break;
    case 'od gory dolu':
        $efektAnimacji = 'straight';
        break;        
}

switch ($efektAnimacjiTekstu) {
    case 'zanikanie':
        $efektAnimacjiTekstu = 'fade';
        break;
    case 'przesuwanie':
        $efektAnimacjiTekstu = 'scroll';
        break;       
}

if ( isset($GLOBALS['bannery']->info[$grupaBannerow]) ) {

    echo '<script type="text/javascript" src="programy/coinSlider/coin-slider.js"></script>';

    $Tablica = $GLOBALS['bannery']->info[$grupaBannerow];

    if ( count($Tablica) > 0 ) {

      $wybrane_bannery = Funkcje::wylosujElementyTablicyJakoTablica($Tablica,$LimitZapytania);
      
      echo '<div id="coin-slider">';

      foreach ($wybrane_bannery as $banner ) {

            $GLOBALS['bannery']->bannerWyswietlAnimowany($banner);
      }
      
      echo '</div>';
      
      unset($wybrane_bannery);

    }
    
    echo '<script>
	  $(document).ready(function() {
       var szerNadrzedna = $("#coin-slider").parent().width();
       $("#coin-slider").coinslider({ width: szerNadrzedna, height:' . $animacjaWysokosc . ', effect:"' . $efektAnimacji . '", textEffect:\'' . $efektAnimacjiTekstu . '\', navigation:' . $nawigacja . ', titleSpeed: 1000, sDelay: 40, delay:' . ($czasAnimacji * 1000) . ' });
	  });
    </script>';
    
    unset($Tablica);
    
}

unset($LimitZapytania, $grupaBannerow, $animacjaWysokosc, $efektAnimacji, $efektAnimacjiTekstu, $nawigacja, $czasAnimacji);

?>