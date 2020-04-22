<?php

if(!class_exists('ot_shopping_discount')) {

  class ot_shopping_discount {
    var $tytul, $wyjscie;

    function ot_shopping_discount( $parametry ) {
      global $zamowienie;

      $Tlumaczenie          = $GLOBALS['tlumacz'];

      $this->paramatery     = $parametry;

      $this->tytul          = $Tlumaczenie['OT_SHOPPING_DISCOUNT_TYTUL'];
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
      
      if ( isset($GLOBALS['koszykKlienta']) ) {

          // ustalenie czy klient nalezy do grupy dla ktorej sa naliczane nizki
          if ( !empty($this->paramatery['parametry']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW']) ) {
          
              if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
              
                  $tablica_grup = explode(';',$this->paramatery['parametry']['ZNIZKI_KOSZYKA_GRUPA_KLIENTOW']);
                  if ( !in_array($_SESSION['customers_groups_id'], $tablica_grup) ) {
                    return;
                  }
                  unset($tablica_grup);
                  
                } else {
                
                  return;
                  
              }
          
          }
          
          $zawartosc_koszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
          
          // ustalenie wartosci lub ilosci produktow w zamowieniu
          $wartosc_koszyka = 0;
          $ilosc_koszyka = 0;
          foreach ( $_SESSION['koszyk'] as $produkt ) {
              //
              if ( $produkt['promocja'] == 'nie' || ( $produkt['promocja'] == 'tak' && $this->paramatery['parametry']['ZNIZKI_KOSZYKA_PROMOCJE'] == 'tak' ) ) {
                  //
                  $wartosc_koszyka += $produkt['cena_brutto'] * $produkt['ilosc'];
                  $ilosc_koszyka +=  $produkt['ilosc'];
                  //
              }
              //
          }
          
          // ustalenie znizki w zaleznosci od wartosci zamowien
          if ( $wartosc_koszyka == 0 ) {
          
            return;
            
          } else {
          
            $tablica_znizek = preg_split("/[:;]/" , $this->paramatery['parametry']['ZNIZKI_KOSZYKA_PROGI_ZNIZEK']);

            $znizka = 0;
            for ($i = 0, $c = count($tablica_znizek); $i < $c; $i+=2) {
              //
              // jezeli znizka jest zalezna od wartosci koszyka
              if ( $this->paramatery['parametry']['ZNIZKI_KOSZYKA_SPOSOB'] == 'kwota' ) {
                  //
                  if ( $wartosc_koszyka > $tablica_znizek[$i] ) {
                    $znizka = $tablica_znizek[$i+1];
                  }
                  //
                } else {
                  //
                  if ( $ilosc_koszyka > $tablica_znizek[$i] ) {
                    $znizka = $tablica_znizek[$i+1];
                  }
                  //
              }
              //
            }
            
          }

          if ( $znizka == 0 ) {
          
            return;
            
          }

          $wartosc_pomniejszenia = 0;
          if ( isset($_SESSION['kuponRabatowy']) ) {
              $wartosc_pomniejszenia = $_SESSION['kuponRabatowy']['kupon_wartosc'];
          }
          $wartosc_koszyka -= $wartosc_pomniejszenia;

          // ustalenie wartosci znizki
          $wartosc_znizki = round($wartosc_koszyka * ( $znizka / 100 ), 2);
          
          if ( $wartosc_znizki <= 0 ) {
               return;
          }

          $wynik = array();

          $wynik = array('id' => $this->id,
                         'text' => $this->tytul . ' (' . $znizka . '%)',
                         'prefix' => $this->prefix,
                         'klasa' => $this->klasa,
                         'wartosc' => $wartosc_znizki,
                         'sortowanie' => $this->sortowanie);
                         
          unset($zawartosc_koszyka, $wartosc_koszyka, $znizka, $wartosc_znizki, $ilosc_koszyka);

          return $wynik;
          
      }

    }
    
  }

}
?>