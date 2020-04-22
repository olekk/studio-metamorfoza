<?php

if(!class_exists('wysylka_odbior_osobisty')) {
  class wysylka_odbior_osobisty {

    // class constructor
    function wysylka_odbior_osobisty( $parametry = array(), $kraj = '', $idProduktu = '', $WagaProduktu = '', $CenaProduktu = '', $WysylkiProduktu = '', $GabarytProduktu = '' ) {
      global $zamowienie, $Tlumaczenie;

        $Tlumaczenie = $GLOBALS['tlumacz'];

        $this->paramatery  = $parametry;

        // czy przesylka ma byc liczona dla produktu czy koszyka
        $this->produktId      = $idProduktu;
        $this->produktWaga    = $WagaProduktu;
        $this->produktCena    = $CenaProduktu;
        $this->produktWysylki = $WysylkiProduktu;
        $this->produktGabaryt = $GabarytProduktu;

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

      // sprawdzenie czy dostawa jest dostepna wla wszystkich produktow w koszyku
      if ( $this->produktId == '' && $this->produktWaga == '' && $this->produktCena == '' ) {
          foreach ( $_SESSION['koszyk'] as $rekord ) {
            if ( $rekord['wysylki'] != '' ) {
              $dostepne = explode(';',$rekord['wysylki']);
              if (!in_array($this->id, $dostepne) ) {
                return;
              }
            }
            if ( $this->gabaryt == '0' ) {
                if ( $rekord['gabaryt'] == '1' ) {
                    return;
                }
            }
          }
      } else {
          if ( $this->produktWysylki != '' ) {
              $dostepne = explode(';',$this->produktWysylki);
              if (!in_array($this->id, $dostepne) ) {
                return;
              }
          }
          if ( $this->gabaryt == '0' ) {
              if ( $this->produktGabaryt == '1' ) {
                  return;
              }
          }
      }

      $tablica_kosztow = preg_split("/[:;]/" , $this->koszty);

        switch ($this->rodzaj_oplaty) {

          // jezeli jest stala oplata
          case '1':
            $koszt_wysylki = $tablica_kosztow[count($tablica_kosztow)-1];
            $this->wyswietl = true;
            break;

          // jezeli oplata jest wg wagi zamowienia
          case '2':
            // jezeli laczna waga zamowienia przekracza maksymalna wage z tablicy kosztow wysylki - przesylka niedostepna
            if ( $this->waga_zamowienia > $tablica_kosztow[count($tablica_kosztow)-2] || $this->waga_zamowienia > $this->max_waga ) {
              $this->ilosc_paczek = ceil($this->waga_zamowienia/$tablica_kosztow[count($tablica_kosztow)-2]);
              $this->waga_zamowienia = $this->waga_zamowienia / $this->ilosc_paczek;
            }

            for ($i = 0, $c = count($tablica_kosztow); $i < $c; $i+=2) {
              if ( $this->waga_zamowienia <= $tablica_kosztow[$i] ) {
                $koszt_wysylki = $tablica_kosztow[$i+1];
                break;
              }
            }

            // jezeli ilosc paczek jest wieksza niz 1 - dzieli na mniejsze wagi
            if ( $this->ilosc_paczek > 1 ) {
              $koszt_wysylki = $koszt_wysylki * $this->ilosc_paczek;
            }
            $this->wyswietl = true;
            break;

          // jezeli oplata jest wg wartosci zamowienia
          case '3':
            for ($i = 0, $c = count($tablica_kosztow); $i < $c; $i+=2) {
              if ( $this->wartosc_zamowienia <= $GLOBALS['waluty']->PokazCeneBezSymbolu($tablica_kosztow[$i],'',true) ) {
                $koszt_wysylki = $tablica_kosztow[$i+1];
                break;
              }
            }
            $this->wyswietl = true;
            break;

          // jezeli oplata jest wg ilosci produktow w zamowieniu
          case '4':
            for ($i = 0, $c = count($tablica_kosztow); $i < $c; $i+=2) {
              if ( $this->ilosc_produktow <= $tablica_kosztow[$i] ) {
                $koszt_wysylki = $tablica_kosztow[$i+1];
                break;
              }
            }
            $this->wyswietl = true;
            break;

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
        global $Tlumaczenie;

        $tekst = '';
        $odbior = '';

        $punkt_domyslny = '<div class="ListaTbl"><div style="width:25px;float:left;"><input id="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1" type="radio" value="'.DANE_NAZWA_FIRMY_PELNA . ', ' . DANE_ADRES_LINIA_1 . ', ' . DANE_ADRES_LINIA_2 . ', ' . DANE_MIASTO.'" name="lokalizacja" checked="checked" /></div><div><label title="" for="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1">'.DANE_NAZWA_FIRMY_PELNA . ', ' . DANE_ADRES_LINIA_1 . ', ' . DANE_ADRES_LINIA_2 . ', ' . DANE_MIASTO.'</label></div></div>';

        $tekst .= '
            <h3>'.$Tlumaczenie['ODBIOR_OSOBISTY_WYBIERZ_PUNKT'].'</h3>
            <div class="ListaWyboru"><div id="ListaOpcjiWysylki">';

            if ( $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1'] != '' ) {
                $zaznaczony = '';
                if ( isset($_SESSION['rodzajDostawy']['opis']) && $_SESSION['rodzajDostawy']['opis'] == $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1'] ) {
                    $zaznaczony = 'checked="checked"';
                }
                if ( !isset($_SESSION['rodzajDostawy']['opis']) ) {
                    $_SESSION['rodzajDostawy']['opis'] = $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1'];
                    $zaznaczony = 'checked="checked"';
                }
                $odbior .= '<div class="ListaTbl"><div style="width:25px;float:left;"><input id="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1" type="radio" value="'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1'].'" name="lokalizacja" ' . $zaznaczony . ' /></div><div><label title="" for="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1">'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1'].'</label></div></div>';
            }
            if ( $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2'] != '' ) {
                $zaznaczony = '';
                if ( isset($_SESSION['rodzajDostawy']['opis']) && $_SESSION['rodzajDostawy']['opis'] == $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2'] ) {
                    $zaznaczony = 'checked="checked"';
                }
                $odbior .= '<div class="ListaTbl"><div style="width:25px;float:left;"><input id="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2" type="radio" value="'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2'].'" name="lokalizacja" ' . $zaznaczony . ' /></div><div><label title="" for="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2">'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2'].'</label></div></div>';
            }
            if ( $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3'] != '' ) {
                $zaznaczony = '';
                if ( isset($_SESSION['rodzajDostawy']['opis']) && $_SESSION['rodzajDostawy']['opis'] == $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3'] ) {
                    $zaznaczony = 'checked="checked"';
                }
                $odbior .= '<div class="ListaTbl"><div style="width:25px;float:left;"><input id="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3" type="radio" value="'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3'].'" name="lokalizacja" ' . $zaznaczony . ' /></div><div><label title="" for="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3">'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3'].'</label></div></div>';
            }
            if ( $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_4'] != '' ) {
                $zaznaczony = '';
                if ( isset($_SESSION['rodzajDostawy']['opis']) && $_SESSION['rodzajDostawy']['opis'] == $this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_4'] ) {
                    $zaznaczony = 'checked="checked"';
                }
                $odbior .= '<div class="ListaTbl"><div style="width:25px;float:left;"><input id="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_4" type="radio" value="'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_4'].'" name="lokalizacja" ' . $zaznaczony . ' /></div><div><label title="" for="WYSYLKA_ODBIOR_OSOBISTY_PUNKT_4">'.$this->paramatery['parametry']['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_4'].'</label></div></div>';
            }

            if ( $odbior == '' ) {
                $tekst .= $punkt_domyslny;
            } else {
                $tekst .= $odbior;
            }

            $tekst .= '</div></div>';

        return $tekst;
    }

  }
}
?>