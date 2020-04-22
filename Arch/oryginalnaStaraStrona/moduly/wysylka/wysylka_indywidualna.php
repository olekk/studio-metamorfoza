<?php

if(!class_exists('wysylka_indywidualna')) {
  class wysylka_indywidualna {

    // class constructor
    function wysylka_indywidualna( $parametry = array(), $kraj = '', $idProduktu = '', $WagaProduktu = '', $CenaProduktu = '', $WysylkiProduktu = '', $GabarytProduktu = '', $KosztWysylkiProduktu = '0' ) {
      global $zamowienie;

        $Tlumaczenie = $GLOBALS['tlumacz'];

        $this->paramatery  = $parametry;

        // czy przesylka ma byc liczona dla produktu czy koszyka
        $this->produktId           = $idProduktu;
        $this->produktWaga         = $WagaProduktu;
        $this->produktCena         = $CenaProduktu;
        $this->produktWysylki      = $WysylkiProduktu;
        $this->produktGabaryt      = $GabarytProduktu;
        $this->produktKosztWysylki = $KosztWysylkiProduktu;

        $this->tytul        = ( isset($Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_TYTUL']) ? $Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_TYTUL'] : '' );
        $this->objasnienie  = ( isset($Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_OBJASNIENIE']) ? $Tlumaczenie['WYSYLKA_'.$this->paramatery['id'].'_OBJASNIENIE'] : '' );
        $this->kolejnosc    = $this->paramatery['sortowanie'];
        $this->klasa        = $this->paramatery['klasa'];
        $this->ikona        = '';
        $this->wyswietl     = false;
        $this->id           = $this->paramatery['id'];
        $this->ilosc_paczek = 1;

        $this->gabaryt       = $this->paramatery['parametry']['WYSYLKA_GABARYT'];
        $this->stawka_vat    = $this->paramatery['parametry']['WYSYLKA_STAWKA_VAT'];
        $this->pkwiu         = $this->paramatery['parametry']['WYSYLKA_PKWIU'];
        $this->max_waga      = $this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_WAGA'];
        $this->max_wartosc   = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['WYSYLKA_MAKSYMALNA_WARTOSC'],'',true);
        $this->darmowa       = $GLOBALS['waluty']->PokazCeneBezSymbolu($this->paramatery['parametry']['WYSYLKA_DARMOWA_WYSYLKA'],'',true);
        $this->rodzaj_oplaty = $this->paramatery['parametry']['WYSYLKA_RODZAJ_OPLATY'];
        $this->kraje         = $this->paramatery['parametry']['WYSYLKA_KRAJE_DOSTAWY'];
        $this->koszty        = $this->paramatery['parametry']['WYSYLKA_KOSZT_WYSYLKI'];
        $this->platnosci     = $this->paramatery['parametry']['WYSYLKA_DOSTEPNE_PLATNOSCI'];
        $this->grupa         = $this->paramatery['parametry']['WYSYLKA_GRUPA_KLIENTOW'];
        $this->grupa_wylacz  = $this->paramatery['parametry']['WYSYLKA_GRUPA_KLIENTOW_WYLACZENIE'];

        $this->waga_zamowienia    = $this->paramatery['waga_zamowienia'];
        $this->wartosc_zamowienia = $this->paramatery['wartosc_zamowienia'];
        $this->ilosc_produktow    = $this->paramatery['ilosc_produktow'];

        unset($Tlumaczenie);
    }

    function przetwarzanie() {

      $wynik = array();
      $koszt_wysylki = 0;
      $dostepna = false;

      if ( $this->grupa != '' && $_SESSION['gosc'] == '1' ) {
          return;
      }

      // ustalenie czy przesylka zawiera sie w dopuszczalnej wartości zamowienia
      if ( $this->max_wartosc != '0' && $this->max_wartosc != '' && $this->wartosc_zamowienia > $this->max_wartosc ) {
          return ;
      }

      // ustalenie czy klient nalezy do grupy dla ktorej dostepna jest wysylka
      if ( $this->grupa != '' ) {

            if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {

                $tablica_grup = explode(';',$this->grupa);
                if ( !in_array($_SESSION['customers_groups_id'], $tablica_grup) ) {
                    return;
                }

            }
      }
      
      // ustalenie czy klient nalezy do grupy ktora nie jest dostepna dla tej wysylki
      if ( $this->grupa_wylacz != '' ) {

            if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {

                $tablica_grup = explode(';',$this->grupa_wylacz);
                if ( in_array($_SESSION['customers_groups_id'], $tablica_grup) ) {
                    return;
                }
                unset($tablica_grup);

            }
      }         

      // sprawdzenie czy dostawa jest dostepna dla wszystkich produktow w koszyku
      if ( $this->produktId == '' && $this->produktWaga == '' && $this->produktCena == '' ) {
          foreach ( $_SESSION['koszyk'] as $rekord ) {
            // sprawdza czy jest indywidualny koszt wysylki
            if ( $rekord['koszt_wysylki'] > 0 ) {
                $this->wyswietl = true;
                $koszt_wysylki += $rekord['koszt_wysylki'] * $rekord['ilosc'];
            }
          }
      } else {
          // sprawdza czy jest indywidualny koszt wysylki
          if ( $this->produktKosztWysylki == 0 ) {
              return;
          } else {
              $this->wyswietl = true;
              $koszt_wysylki = $this->produktKosztWysylki;
          }
      }

      // jezeli jest darmowa wysylka
      if ( $this->darmowa != '0' && $this->darmowa != '' && $this->wartosc_zamowienia >= $this->darmowa ) {
        $koszt_wysylki = 0;
      }

      if ( $this->wyswietl ) {
      
        $vat_tb = explode('|', $this->stawka_vat);
        if ( count($vat_tb) == 2 ) {
            //
            $vat_id = $vat_tb[1];
            $vat_stawka = $vat_tb[0];
            //
          } else {
            //
            $vat_tb = Funkcje::domyslnyPodatekVat();
            $vat_id = $vat_tb['id'];
            $vat_stawka = $vat_tb['stawka'];        
            //
        }
        unset($vat_tb);      
      
        $wynik = array('id' => $this->id,
                       'klasa' => $this->klasa,
                       'text' => $this->tytul,
                       'wartosc' => $koszt_wysylki,
                       'vat_id' => $vat_id,
                       'vat_stawka' => $vat_stawka,                       
                       'dostepne_platnosci' => $this->platnosci,
                       'objasnienie' => $this->objasnienie,
                       'wysylka_free' => $this->darmowa);
                       
      }

      return $wynik;
    }

    function potwierdzenie() {
        return;
    }

  }
}
?>