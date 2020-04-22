<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_BANNER_STATYCZNY_ILOSC_PRODUKTOW;Ilość wyświetlanych bannerów;1;1,2,3,4,5,6,7,8,9}}
// {{MODUL_BANNER_STATYCZNY_ILOSC_GRUPA;Grupa wyświetlanych bannerów;STATYCZNE;BoxyModuly::ListaGrupBannerow()}}
//

// zmienne bez definicji
$LimitZapytania = 1;
$grupaBannerow = 'STATYCZNE';

if ( defined('MODUL_BANNER_STATYCZNY_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_BANNER_STATYCZNY_ILOSC_PRODUKTOW;
}
if ( defined('MODUL_BANNER_STATYCZNY_ILOSC_GRUPA') ) {
   $grupaBannerow = MODUL_BANNER_STATYCZNY_ILOSC_GRUPA;
}

if ( isset($GLOBALS['bannery']->info[$grupaBannerow]) ) {

    $tablica = $GLOBALS['bannery']->info[$grupaBannerow];

    if ( count($tablica) > 0 ) {

      $wybrane_bannery = Funkcje::wylosujElementyTablicyJakoTablica($tablica,$LimitZapytania);

      foreach ($wybrane_bannery as $banner ) {

        echo '<div class="BanneryStatyczne">';

          $GLOBALS['bannery']->bannerWyswietlStatyczny($banner);

        echo '</div>';

      }
      
      unset($wybrane_bannery);

    }
    
    unset($tablica);
    
}

unset($LimitZapytania, $grupaBannerow);

?>