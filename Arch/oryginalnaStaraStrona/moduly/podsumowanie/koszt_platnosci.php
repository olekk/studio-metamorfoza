<?php

if(!class_exists('ot_payment')) {
  class ot_payment {
    var $tytul, $wyjscie;

    function ot_payment( $parametry ) {
      global $zamowienie;

      $Tlumaczenie          = $GLOBALS['tlumacz'];
      
      $this->paramatery     = $parametry;

      $this->tytul          = $Tlumaczenie['OT_PAYMENT_TYTUL'];
      $this->sortowanie     = $this->paramatery['sortowanie'];
      $this->prefix         = $this->paramatery['prefix'];
      $this->klasa          = $this->paramatery['klasa'];
      $this->sortowanie     = $this->paramatery['sortowanie'];
      $this->ikona          = '';
      $this->wyswietl       = false;
      $this->id             = $this->paramatery['id'];

      unset($Tlumaczenie);

    }


    function przetwarzanie() {
      global $zamowienie;

      if ( isset($_SESSION['rodzajDostawy']) && ( isset($_SESSION['rodzajPlatnosci']) && $_SESSION['rodzajPlatnosci']['platnosc_koszt'] > 0 ) ) {
        $koszt_platnosci = $_SESSION['rodzajPlatnosci']['platnosc_koszt'];
      } else {
        return;
      }

      $wynik = array();

      $wynik = array('id' => $this->id,
                     'text' => $this->tytul,
                     'prefix' => $this->prefix,
                     'klasa' => $this->klasa,
                     'wartosc' => $GLOBALS['waluty']->PokazCeneBezSymbolu($koszt_platnosci,'',true),
                     'sortowanie' => $this->sortowanie);

      return $wynik;

    }
  }
}
?>