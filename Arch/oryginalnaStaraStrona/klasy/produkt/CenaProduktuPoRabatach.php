<?php

if ( isset($pobierzFunkcje) ) {

    // ustalanie rabatow dla klienta
    
    $cenaNetto = $netto;
    $cenaBrutto = $brutto;
    
    $Rabat = 0;
    $wszystkieZnizki = array();
    
    if ( isset($_SESSION['znizkiKlienta']) ) {
      // jezeli klient jest zalogowany
      if ( $this->infoSql['specials_status'] != '1' || ($this->infoSql['specials_status'] == '1' && RABATY_PROMOCJE == 'tak')) {
      
        // szuka wszystkich kategorii do jakich jest przypisany produkt
        $JakieKategorieMaProdukt = Kategorie::ProduktKategorie( $this->infoSql['products_id'] );
        //

        foreach( $_SESSION['znizkiKlienta'] as $znizkiKlienta ) {
          if ( $znizkiKlienta['0'] == 'Indywidualna' ) {
            $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
          }
          if ( $znizkiKlienta['0'] == 'Grupa klientów' ) {
            $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
          }
          if ( $znizkiKlienta['0'] == 'Producent' && $znizkiKlienta['1'] == $this->infoSql['manufacturers_id'] ) {
            $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
          }
          if ( $znizkiKlienta['0'] == 'Kategoria' && in_array($znizkiKlienta['1'], $JakieKategorieMaProdukt) ) {
            $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
          }
          if ( $znizkiKlienta['0'] == 'Produkt' && $znizkiKlienta['1'] == $this->infoSql['products_id'] ) {
            $wszystkieZnizki[] = abs(floatval($znizkiKlienta['2']));
          }
        }
        
        unset($JakieKategorieMaProdukt);
        
      }

      // sprawdzenie czy znizki maja byc sumowane czy nie
      if ( count($wszystkieZnizki) > 0 ) {
      
        if ( RABAT_SUMOWANIE == 'tak' ) {
          foreach ( $wszystkieZnizki as $wartosc ) {
            $Rabat = $Rabat + floatval($wartosc);
          }
        } else {
          $znizka = max($wszystkieZnizki);
          $Rabat = floatval($znizka);
        }
        
      }

      // sprawdzenie czy rabat nie przekracza maksymalnej wartosci
      if ( $Rabat > 0 ) {
        if ( $Rabat > abs(floatval(RABAT_MAKSYMALNA_WARTOSC)) ) $Rabat = abs(floatval(RABAT_MAKSYMALNA_WARTOSC));
      }

      // ustalenie cen z rabatem
      $cenaBrutto = $cenaBrutto - ( $cenaBrutto * $Rabat/100 );
      $cenaNetto = $cenaNetto - ( $cenaNetto * $Rabat/100 );

    } else {
    
      // jezeli klient nie jest zalogowany
      if ( NARZUT_NIEZALOGOWANI != '' && floatval(NARZUT_NIEZALOGOWANI) != 0 ) {
        $cenaBrutto = $cenaBrutto + ( $cenaBrutto * floatval(NARZUT_NIEZALOGOWANI)/100 );
        $cenaNetto = $cenaNetto + ( $cenaNetto * floatval(NARZUT_NIEZALOGOWANI)/100 );
      }
      
    }

}
       
?>