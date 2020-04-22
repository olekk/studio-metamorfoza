<?php

class Podsumowanie {

  public function Podsumowanie() {

    unset($_SESSION['podsumowanieZamowienia']);
    //  tablica dostepnych platnosci
    $this->podsumowanie = array();
    $this->podsumowanie_parametry = array();

    $this->DostepnePodsumowanie();

  }

  //  funkcja zwraca w formie tablicy dostepne pozycje podsumowania zamowienia
  public function DostepnePodsumowanie() {

    // utworzenie tablicy parametrow
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('PodsumowanieParametry', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie_parametry = "SELECT modul_id, kod, wartosc FROM modules_total_params";

        $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
        while ($info_parametry = $sql_parametry->fetch_assoc()) {
          $this->podsumowanie_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
        }
        $GLOBALS['db']->close_query($sql_parametry);
        unset($zapytanie_parametry, $info_parametry);
        
        $GLOBALS['cache']->zapisz('PodsumowanieParametry', $this->podsumowanie_parametry, CACHE_INNE);
        
      } else {
     
       $this->podsumowanie_parametry = $WynikCache;
    
    }   

    unset($WynikCache);
    
    // podsumowanie
    
    $PodsumowanieTablica = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Podsumowanie', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $zapytanie = "SELECT id, nazwa, skrypt, klasa, sortowanie, prefix 
                        FROM modules_total
                       WHERE status = '1'
                       ORDER BY sortowanie";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        while ($info = $sql->fetch_assoc()) {
          $PodsumowanieTablica[] = $info;
        }
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);
        
        $GLOBALS['cache']->zapisz('Podsumowanie', $PodsumowanieTablica, CACHE_INNE);
        
      } else {
     
        $PodsumowanieTablica = $WynikCache;
    
    }       

    foreach ( $PodsumowanieTablica as $info ) {    

      $tablica_podsumowania = array('id' => $info['id'],
                                   'text' => $info['nazwa'],
                                   'skrypt' => $info['skrypt'],
                                   'sortowanie' => $info['sortowanie'],
                                   'prefix' => $info['prefix'],
                                   'klasa' => $info['klasa'],
                                   'parametry' => ( isset($this->podsumowanie_parametry[$info['id']]) && count($this->podsumowanie_parametry) > 0 ? $this->podsumowanie_parametry[$info['id']] : array() ));

      require_once('moduly/podsumowanie/'.$info['skrypt']);
      $podsumowanie = new $info['klasa']($tablica_podsumowania);

      if ( count($podsumowanie->przetwarzanie()) > 0 ) {
        $this->podsumowanie[$info['id']] = $podsumowanie->przetwarzanie();
        $_SESSION['podsumowanieZamowienia'][$this->podsumowanie[$info['id']]['klasa']] = $this->podsumowanie[$info['id']];
      }

    }
    
    unset($PodsumowanieTablica);

    if ( count($this->podsumowanie) == '0' ) {
    
      $this->podsumowanie['0'] = array('id' => '0',
                                       'text' => '0',
                                       'prefix' => '',
                                       'wartosc' => '---');
                                       
    }

  }
  
  // funkcja generujaca i wyswietlajaca podsumwanie zamowienia w koszyku
  public function Generuj() {

    $wynik = '';
    foreach ( $this->podsumowanie as $rekord ) {

      $styl = 'ListaPodsumowaniaCena';
      if ( $rekord['klasa'] != 'ot_total' ) {
        if ( $rekord['prefix'] == '1' ) {
          $styl = 'ListaPodsumowaniaCena';
        } elseif ( $rekord['prefix'] == '0' ) {
          $styl = 'ListaPodsumowaniaCenaUjemna';
        }

        $wynik .= '<div class="ListaTblPodsumowania">';
            $wynik .= '<div>' . $rekord['text'] . '</div>';            
            $wynik .= '<div class="'.$styl.'">'.$GLOBALS['waluty']->WyswietlFormatCeny($rekord['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false).'</div>';
        $wynik .= '</div>';

      } else {

        $wynik .= '<div class="ListaTblPodsumowania">';
            $wynik .= '<div>' . $rekord['text'] . '</div>';            
            $wynik .= '<div class="ListaPodsumowaniaSumaCena">'.$GLOBALS['waluty']->WyswietlFormatCeny($rekord['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false).'</div>';
        $wynik .= '</div>';

      }

    }

    $wynik .= '';
    return $wynik;

  }

  // funkcja generujaca i wyswietlajaca podsumwanie zamowienia w potwierdzeniu zamowienia
  public function GenerujWPotwierdzeniu() {

    $wynik = '';

    foreach ( $this->podsumowanie as $rekord ) {

      if ( $rekord['klasa'] != 'ot_total' ) {
        if ( $rekord['prefix'] == '1' ) {
          $styl = 'Wartosc';
        } elseif ( $rekord['prefix'] == '0' ) {
          $styl = 'WartoscUjemna';
        }

        $wynik .= '<tr>';
            $wynik .= '<td class="Pusta">&nbsp;</td><td class="Pusta">&nbsp;</td><td class="Tekst">' . $rekord['text'] . '</td>';            
            $wynik .= '<td class="'.$styl.'">'.$GLOBALS['waluty']->WyswietlFormatCeny($rekord['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false).'</td>';
        $wynik .= '</tr>';

      } else {

        $wynik .= '<tr>';
            $wynik .= '<td class="Pusta">&nbsp;</td><td class="Pusta">&nbsp;</td><td class="Tekst">' . $rekord['text'] . '</td>';            
            $wynik .= '<td class="Suma">'.$GLOBALS['waluty']->WyswietlFormatCeny($rekord['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false).'</td>';
        $wynik .= '</tr>';

      }

    }

    $wynik .= '';
    return $wynik;

  }
} 

?>