<?php

class Nawigacja {
  var $_sciezka;

  public function nawigacja() {
    $this->reset();
  }

  public function reset() {
    $this->_sciezka = array();
  }

  public function dodaj($tytul, $link = '', $unshift = 0) {
      switch ($unshift) {
       case 0:
        $this->_sciezka[] = array('tytul' => $tytul, 'link' => $link);
        break;
       case 1:
        array_unshift($this->_sciezka, array('tytul' => $tytul, 'link' => $link));
        break;
      }      
    }

  public function sciezka($separator = ' - ') {
    $tekst = '';

    for ( $i=0, $n=count($this->_sciezka); $i < $n; $i++ ) {
      if (isset($this->_sciezka[$i]['link']) && $this->_sciezka[$i]['link'] != '' ) {

        if ( $i == '0' ) {
          $tekst .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a rel="nofollow" href="' . $this->_sciezka[$i]['link'] . '" class="NawigacjaLink"  itemprop="url"><span itemprop="title">' . $this->_sciezka[$i]['tytul'] . '</span></a></span>';
        } else {
          if ( $i < $n-1 ) {
            $tekst .= '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="' . $this->_sciezka[$i]['link'] . '" class="NawigacjaLink"  itemprop="url"><span itemprop="title">' . $this->_sciezka[$i]['tytul'] . '</span></a></span>';
          } else {
            $tekst .= '<span class="Nawigacja">' . $this->_sciezka[$i]['tytul'] . '</span>';
          }
        }

      } else {
        $tekst .= '<span class="Nawigacja">' . $this->_sciezka[$i]['tytul'] . '</span>';
      }

      if (($i+1) < $n) $tekst .= '<span class="Nawigacja">' . $separator . '</span>';

    }

    return $tekst;
  }
}

?>
