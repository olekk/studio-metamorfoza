<?php

if(!class_exists('ot_redemptions')) {
  class ot_redemptions {
    var $tytul, $wyjscie;

    function ot_redemptions( $parametry ) {
      global $zamowienie;

      $Tlumaczenie          = $GLOBALS['tlumacz'];

      $this->paramatery     = $parametry;

      $this->tytul          = $Tlumaczenie['OT_REDEMPTIONS_TYTUL'];
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

      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_SESSION['punktyKlienta']) ) {

        $wynik = array();

        $ilosc_punktow = $_SESSION['punktyKlienta']['punkty_ilosc'];
        $kwota_rabatu = $GLOBALS['waluty']->PokazCeneBezSymbolu((float)$ilosc_punktow/(float)SYSTEM_PUNKTOW_WARTOSC_PRZY_KUPOWANIU,'',true);

        $wartosc_zamowienia_do_punktow = 0;
        foreach ( $_SESSION['podsumowanieZamowienia'] as $podsumowanie ) {
          if ( $podsumowanie['prefix'] == '1' ) {
            if ( $podsumowanie['klasa'] == 'ot_shipping' ) {
              $wartosc_zamowienia_do_punktow;
            } else {
              $wartosc_zamowienia_do_punktow += $podsumowanie['wartosc'];
            }
          } elseif ( $podsumowanie['prefix'] == '0' ) {
            $wartosc_zamowienia_do_punktow -= $podsumowanie['wartosc'];
          }
        }

        if ( $kwota_rabatu > $wartosc_zamowienia_do_punktow ) {
          $kwota_rabatu = $wartosc_zamowienia_do_punktow;
        }


        $wynik = array('id' => $this->id,
                       'text' => $this->tytul,
                       'prefix' => $this->prefix,
                       'klasa' => $this->klasa,
                       'wartosc' => $kwota_rabatu,
                       'sortowanie' => $this->sortowanie);

        return $wynik;
      }

      return;
    }
  }

}
?>