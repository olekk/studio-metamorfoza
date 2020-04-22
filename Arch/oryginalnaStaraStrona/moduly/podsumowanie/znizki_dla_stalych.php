<?php

if(!class_exists('ot_loyalty_discount')) {

  class ot_loyalty_discount {
    var $tytul, $wyjscie;

    function ot_loyalty_discount( $parametry ) {
      global $zamowienie;

      $Tlumaczenie          = $GLOBALS['tlumacz'];

      $this->paramatery     = $parametry;

      $this->tytul          = $Tlumaczenie['OT_LOYALTY_DISCOUNT_TYTUL'];
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

      if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {

        // ustalenie czy klient nalezy do grupy dla ktorej sa naliczane nizki
        $tablica_grup = explode(';',$this->paramatery['parametry']['STALI_KLIENCI_GRUPA_KLIENTOW']);
        if ( !in_array($_SESSION['customers_groups_id'], $tablica_grup) && !empty($this->paramatery['parametry']['STALI_KLIENCI_GRUPA_KLIENTOW']) ) {
          return;
        }

        //obliczenie dotychczasowych wartosci zamowien klienta
        $zapytanie = "SELECT 
          o.date_purchased, o.currency_value, ot.value 
          FROM orders o 
          LEFT JOIN orders_total ot ON (o.orders_id = ot.orders_id) 
          WHERE o.customers_dummy_account != '1' AND o.customers_id = '" . (int)$_SESSION['customer_id'] . "' AND ot.class = 'ot_subtotal' AND o.orders_status = '" . $this->paramatery['parametry']['STALI_KLIENCI_STATUS_ZAMOWIEN'] . "' ORDER BY date_purchased DESC";

          $sql = $GLOBALS['db']->open_query($zapytanie);

          if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

            // okres z jakiego sa pobierane zamowienia
            $okres_naliczania = $this->paramatery['parametry']['STALI_KLIENCI_OKRES_NALICZANIA_ZAMOWIEN'];

            $wartosc_wszystkich_zamowien = 0;

            while ( $info = $sql->fetch_assoc() ) {
              switch ($okres_naliczania) {
                case '99':
                  $wartosc_wszystkich_zamowien += ($info['value'] / $info['currency_value']);
                  break;
                case '1':
                  $rok = 60*60*24*365;
                  if ( time() - strtotime($info['date_purchased']) < $rok ) {
                    $wartosc_wszystkich_zamowien += ($info['value'] / $info['currency_value']);
                  }
                  break;
                case '3':
                  $kwartal = 60*60*24*92;
                  if ( time() - strtotime($info['date_purchased']) < $kwartal ) {
                    $wartosc_wszystkich_zamowien += ($info['value'] / $info['currency_value']);
                  }
                  break;
              }
            }
            
            unset($okres_naliczania);
            
          } else {
          
            return;
            
          }

        // ustalenie znizki w zaleznosci od wartosci zamowien
        if ( $wartosc_wszystkich_zamowien == 0 ) {
        
          return;
          
        } else {
        
          $tablica_znizek = preg_split("/[:;]/" , $this->paramatery['parametry']['STALI_KLIENCI_PROGI_ZNIZEK']);

          $znizka = 0;
          for ($i = 0, $c = count($tablica_znizek); $i < $c; $i+=2) {
            if ( $wartosc_wszystkich_zamowien > $tablica_znizek[$i] ) {
              $znizka = $tablica_znizek[$i+1];
              //break;
            }
          }
          
        }

        if ( $znizka == 0 ) {
        
          return;
          
        }

        // ustalenie wartosci produktow w zamowieniu
        $wartosc_znizki = 0;
        foreach ( $_SESSION['koszyk'] as $rekord ) {
            if ( $rekord['promocja'] == 'nie' || ( $rekord['promocja'] == 'tak' && RABATY_PROMOCJE == 'tak' ) ) {
                $wartosc_znizki += $rekord['cena_brutto']*$rekord['ilosc'];
            }
        }

        $wartosc_pomniejszenia = 0;
        if ( isset($_SESSION['kuponRabatowy']) ) {
            $wartosc_pomniejszenia = $_SESSION['kuponRabatowy']['kupon_wartosc'];
        }
        $wartosc_znizki -= $wartosc_pomniejszenia;

        // ustalenie wartosci znizki
        $wartosc_znizki = round($wartosc_znizki * ( $znizka / 100 ), 2);

        if ( $wartosc_znizki == 0 ) {

          return;
        }

        $wynik = array();

        $wynik = array('id' => $this->id,
                       'text' => $this->tytul . ' (' . $znizka . '%)',
                       'prefix' => $this->prefix,
                       'klasa' => $this->klasa,
                       'wartosc' => $wartosc_znizki,
                       'sortowanie' => $this->sortowanie);
                       
        unset($tablica_grup, $wartosc_wszystkich_zamowien, $tablica_znizek, $wartosc_znizki, $wartosc_znizki, $wartosc_pomniejszenia);

        return $wynik;
        
      }

      return;
      
    }
    
  }

}
?>