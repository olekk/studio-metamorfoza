<?php

class Platnosci {

  public function Platnosci( $wysylka_id ) {

    // wybrana wysylka
    $this->wysylka = $wysylka_id;

    // tablica dostepnych platnosci
    $this->platnosci = array();
    $this->platnosci_parametry = array();

    $this->DostepnePlatnosci();

  }

  // funkcja zwraca w formie tablicy dostepne platnosci
  public function DostepnePlatnosci() {
    global $tablica_platnosci;

    // utworzenie tablicy parametrow
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('PlatnosciParametry', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie_parametry = "SELECT modul_id, kod, wartosc FROM modules_payment_params";

        $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
        while ($info_parametry = $sql_parametry->fetch_assoc()) {
          $this->platnosci_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
        }
        $GLOBALS['db']->close_query($sql_parametry);
        unset($zapytanie_parametry, $info_parametry);
        
        $GLOBALS['cache']->zapisz('PlatnosciParametry', $this->platnosci_parametry, CACHE_INNE);
        
      } else {
     
       $this->platnosci_parametry = $WynikCache;
    
    } 
    
    unset($WynikCache);
    
    // platnosci
    
    $PlatnosciTablica = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Platnosci', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie = "SELECT id, nazwa, skrypt, klasa, sortowanie 
                        FROM modules_payment
                       WHERE status = '1'
                    ORDER BY sortowanie";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
          $PlatnosciTablica[] = $info;
        }
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);
        
        $GLOBALS['cache']->zapisz('Platnosci', $PlatnosciTablica, CACHE_INNE);
        
      } else {
     
        $PlatnosciTablica = $WynikCache;
    
    }      
    
    foreach ( $PlatnosciTablica as $info ) {
    
      $tablica_platnosci = array('id' => $info['id'],
                                 'wysylka_id' => $this->wysylka,
                                 'text' => $info['nazwa'],
                                 'skrypt' => $info['skrypt'],
                                 'sortowanie' => $info['sortowanie'],
                                 'klasa' => $info['klasa'],
                                 'parametry' => $this->platnosci_parametry[$info['id']]);

      require_once('moduly/platnosc/'.$info['klasa'].'.php');
      $platnosc = new $info['klasa']($tablica_platnosci);

      if ( count($platnosc->przetwarzanie()) > 0 ) {
        $this->platnosci[$info['id']] = $platnosc->przetwarzanie();
      }

    }
    
    unset($PlatnosciTablica);

    if ( count($this->platnosci) == '0' ) {
      $this->platnosci['0'] = array('id' => '0',
                                    'klasa' => $info['klasa'],
                                    'text' => '0',
                                    'wartosc' => '---'
      );
    }

  }
  
  //funkcja wykonywana podczas potwierdzenia zamowienia
  public function Potwierdzenie( $platnosc_id, $platnosc_klasa ) {

    //utworzenie tablicy parametrow
    $zapytanie_parametry = "
        SELECT modul_id, kod, wartosc 
          FROM modules_payment_params
          WHERE modul_id = '".$platnosc_id."'
    ";

    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);

    while ($info_parametry = $sql_parametry->fetch_assoc()) {
      $this->platnosci_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
    }

    $tablica_platnosci = array('id' => $platnosc_id,
                               'wysylka_id' => '',
                               'text' => '',
                               'skrypt' => $platnosc_klasa.'.php',
                               'klasa' => $platnosc_klasa,
                               'sortowanie' => '',
                               'parametry' => $this->platnosci_parametry[$platnosc_id],
    );

    $GLOBALS['db']->close_query($sql_parametry);
    unset($zapytanie_parametry, $info_parametry);

    require_once('moduly/platnosc/'.$platnosc_klasa.'.php');
    $platnosc = new $platnosc_klasa($tablica_platnosci);

    return $platnosc->potwierdzenie();

  }

  //funkcja wykonywana po zlozeniu zamowienia - dotyczy platnosci elektronicznych
  public function Podsumowanie( $platnosc_id, $platnosc_klasa ) {

    //utworzenie tablicy parametrow
    $zapytanie_parametry = "
        SELECT modul_id, kod, wartosc 
          FROM modules_payment_params
          WHERE modul_id = '".$platnosc_id."'
    ";

    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);

    while ($info_parametry = $sql_parametry->fetch_assoc()) {
      $this->platnosci_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
    }

    $tablica_platnosci = array('id' => $platnosc_id,
                               'wysylka_id' => '',
                               'text' => '',
                               'skrypt' => $platnosc_klasa.'.php',
                               'klasa' => $platnosc_klasa,
                               'sortowanie' => '',
                               'parametry' => $this->platnosci_parametry[$platnosc_id],
    );

    $GLOBALS['db']->close_query($sql_parametry);
    unset($zapytanie_parametry, $info_parametry);

    require_once('moduly/platnosc/'.$platnosc_klasa.'.php');
    $platnosc = new $platnosc_klasa($tablica_platnosci);

    return $platnosc->podsumowanie();

  }

} 

?>