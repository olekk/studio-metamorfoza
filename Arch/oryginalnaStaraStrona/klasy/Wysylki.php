<?php

class Wysylki {

  public function Wysylki( $kraj_id, $idProduktu = '', $WagaProduktu = '', $CenaProduktu = '', $WysylkiProduktu = '', $GabarytProduktu = '', $KosztWysylkiProduktu = '0' ) {

    // wybrane panstwo
    $this->kraj = $kraj_id;

    // czy przesylka ma byc liczona dla produktu czy koszyka
    $this->produktId           = $idProduktu;
    $this->produktWaga         = $WagaProduktu;
    $this->produktCena         = $CenaProduktu;
    $this->produktDostepne     = $WysylkiProduktu;
    $this->produktGabaryt      = $GabarytProduktu;
    $this->produktKosztWysylki = $KosztWysylkiProduktu;

    // tablica dostepnych wysylek
    $this->wysylki = array();
    $this->wysylki_parametry = array();

    // ustalenie wagi zamowienia i wartosci zamowienia
    $this->waga_zamowienia = 0;
    $this->wartosc_zamowienia = 0;
    $this->ilosc_produktow = 0;

    if ( $this->produktId == '' && $this->produktWaga == '' && $this->produktCena == '' && $this->produktDostepne == '' ) {
        foreach ( $_SESSION['koszyk'] as $rekord ) {
          $this->waga_zamowienia += $rekord['waga']*$rekord['ilosc'];
          $this->wartosc_zamowienia += $rekord['cena_brutto']*$rekord['ilosc'];
          $this->ilosc_produktow += $rekord['ilosc'];
        }
    } else {
        $this->waga_zamowienia = $this->produktWaga;
        $this->wartosc_zamowienia = $this->produktCena;
        $this->ilosc_produktow = 1;
    }

    $this->DostepneWysylki();

  }

  // funkcja zwraca w formie tablicy dostepne wysylki
  public function DostepneWysylki() {
    global $tablica_wysylki;

    // utworzenie tablicy parametrow
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('WysylkiParametry', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie_parametry = "SELECT modul_id, kod, wartosc FROM modules_shipping_params";

        $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
        while ($info_parametry = $sql_parametry->fetch_assoc()) {
          $this->wysylki_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
        }
        $GLOBALS['db']->close_query($sql_parametry);
        unset($zapytanie_parametry, $info_parametry);
        
        $GLOBALS['cache']->zapisz('WysylkiParametry', $this->wysylki_parametry, CACHE_INNE);
        
      } else {
     
       $this->wysylki_parametry = $WynikCache;
    
    }       

    unset($WynikCache);
    
    // wysylki
    
    $WysylkiTablica = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Wysylki', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie = "SELECT id, nazwa, skrypt, klasa, sortowanie 
                        FROM modules_shipping
                       WHERE status = '1'
                       ORDER BY sortowanie";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
          $WysylkiTablica[] = $info;
        }
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);
        
        $GLOBALS['cache']->zapisz('Wysylki', $WysylkiTablica, CACHE_INNE);
        
      } else {
     
        $WysylkiTablica = $WynikCache;
    
    }       

    foreach ( $WysylkiTablica as $info ) {

      if ( $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] == '' || $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] == '0' ) $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] = '9999999';
      if ( $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] == '' || $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] == '0') $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] = '9999999';

      // jezeli laczna waga zamowienia przekracza maksymalna wage lub wartosc dla przesylki - przesylka niedostepna
      if ( $this->waga_zamowienia <= $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WAGA'] && $this->wartosc_zamowienia <= $this->wysylki_parametry[$info['id']]['WYSYLKA_MAKSYMALNA_WARTOSC'] ) {

        // sprawdzenie czy przesylka jest dostepna do danego kraju
        $tablica_krajow = explode(';', $this->wysylki_parametry[$info['id']]['WYSYLKA_KRAJE_DOSTAWY']);
        if ( in_array( $this->kraj, $tablica_krajow ) ) {

          $tablica_wysylki = array( 'id' => $info['id'],
                                    'text' => $info['nazwa'],
                                    'skrypt' => $info['skrypt'],
                                    'klasa' => $info['klasa'],
                                    'sortowanie' => $info['sortowanie'],
                                    'waga_zamowienia' => $this->waga_zamowienia,
                                    'wartosc_zamowienia' => $this->wartosc_zamowienia,
                                    'ilosc_produktow' => $this->ilosc_produktow,
                                    'parametry' => $this->wysylki_parametry[$info['id']]);

          require_once('moduly/wysylka/'.$info['klasa'].'.php');
          $wysylka = new $info['klasa']($tablica_wysylki, $this->kraj, $this->produktId, $this->produktWaga, $this->produktCena, $this->produktDostepne, $this->produktGabaryt, $this->produktKosztWysylki);

          if ( count($wysylka->przetwarzanie()) > 0 ) {
            $this->wysylki[$info['id']] = $wysylka->przetwarzanie();
          }

        }
      }
      
    }
    
    unset($WysylkiTablica);
    
    if ( count($this->wysylki) < 1 ) {
    
      $this->wysylki['0'] = array('id' => '0',
                                  'klasa' => $info['klasa'],
                                  'text' => '0',
                                  'wartosc' => '---',
                                  'vat_id' => '1',
                                  'vat_stawka' => '23',
                                  'dostepne_platnosci' => '',
                                  'objasnienie' => '',
                                  'wysylka_free' => '0');
      
    } else {
    
      if ( count($_SESSION['koszyk']) > 0 ) {
    
          // jezeli wszystkie produkty w koszyku maja przesylke gratis to wyzuruje koszty wszystkich wysylek
          $ProduktyWysylkaGratis = true;
          //
          foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
              //
              $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
              
              if ($Produkt->CzyJestProdukt == true) {
                  //
                  if ( $Produkt->info['darmowa_wysylka'] == 'nie' ) {
                       $ProduktyWysylkaGratis = false;
                  }
                  //
              }
              
              unset($Produkt);
              //
          }
          
          if ( $ProduktyWysylkaGratis == true ) {
              //
              foreach ( $this->wysylki as $klucz => $wartosc ) {
                   $this->wysylki[$klucz]['wartosc'] = 0;
                   $this->wysylki[$klucz]['wysylka_free'] = 0;
              } 
              //
          }
          
          unset($ProduktyWysylkaGratis);
          
      }
    
    }

  }
  
  // funkcja wykonywana podczas potwierdzenia zamowienia
  public function Potwierdzenie( $wysylka_id, $wysylka_klasa ) {
    global $tablica_wysylki;

    //utworzenie tablicy parametrow
    $zapytanie_parametry = "SELECT modul_id, kod, wartosc 
                              FROM modules_shipping_params
                             WHERE modul_id = '".$wysylka_id."'";

    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);

    while ($info_parametry = $sql_parametry->fetch_assoc()) {
      $this->wysylki_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
    }

    $tablica_wysylek = array('id' => $wysylka_id,
                             'wysylka_id' => '',
                             'text' => '',
                             'skrypt' => $wysylka_klasa.'.php',
                             'klasa' => $wysylka_klasa,
                             'sortowanie' => '',
                             'waga_zamowienia' => $this->waga_zamowienia,
                             'wartosc_zamowienia' => $this->wartosc_zamowienia,
                             'ilosc_produktow' => $this->ilosc_produktow,
                             'parametry' => $this->wysylki_parametry[$wysylka_id]);

    $GLOBALS['db']->close_query($sql_parametry);
    unset($zapytanie_parametry, $info_parametry);

    require_once('moduly/wysylka/'.$wysylka_klasa.'.php');
    $wysylka = new $wysylka_klasa($tablica_wysylek);

    return $wysylka->potwierdzenie();

  }

} 

?>