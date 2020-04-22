<?php

if(!class_exists('ot_subtotal')) {
  class ot_subtotal {
    var $tytul, $wyjscie;

    function ot_subtotal( $parametry ) {
      global $zamowienie;

      $Tlumaczenie          = $GLOBALS['tlumacz'];

      $this->paramatery     = $parametry;

      $this->tytul          = $Tlumaczenie['OT_SUBTOTAL_TYTUL'];
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

      // ustalenie wartosci produktow w zamowieniu
      $wartosc_produktow = 0;
      foreach ( $_SESSION['koszyk'] as $rekord ) {
        $wartosc_produktow += $rekord['cena_brutto']*$rekord['ilosc'];
      }

      $wynik = array();

      $wynik = array('id' => $this->id,
                     'text' => $this->tytul,
                     'prefix' => $this->prefix,
                     'klasa' => $this->klasa,
                     'wartosc' => $wartosc_produktow,
                     'sortowanie' => $this->sortowanie);

      return $wynik;
    }
  }

}
?>