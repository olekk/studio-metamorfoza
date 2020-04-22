<?php

if ( isset($pobierzFunkcje) ) {

    $this->ProduktCechyIlosc();
    
    // jezeli jest podane id z cechami
    // sprawdzi jaki jest stan magazynowy danej kombinacji cech
    if ( $id != '' ) {
        //
        // sformatuj cechy
        $cechy = str_replace('x', ',', $id);
        $cechy = substr($cechy, 1, strlen($cechy));        
        //
        // szuka wartosci dla okreslonych cech w tablicy stock z cechami
        $zapytanieCechy = "SELECT products_stock_quantity, products_stock_availability_id, products_stock_model FROM products_stock WHERE products_id = '" . $this->id_produktu . "' and products_stock_attributes = '" . $cechy . "'";
        $sqlCecha = $GLOBALS['db']->open_query($zapytanieCechy); 
        $StanCechy = $sqlCecha->fetch_assoc();
        //        
        // jezeli jest magazyn cech to jako ilosc produktu przyjmie ilosc cechy
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && CECHY_MAGAZYN == 'tak' ) {
             $this->infoSql['products_quantity'] = $StanCechy['products_stock_quantity'];
        }
        //
        // podstawi nr katalogowy cechy
        if ( !empty($StanCechy['products_stock_model']) ) {
             $this->infoSql['products_model'] = $StanCechy['products_stock_model'];
        }
        
        // jezeli cecha ma dosteponosc to produkt przyjmie jej id
        if ( $StanCechy['products_stock_availability_id'] > 0 ) {
             $this->infoSql['products_availability_id'] = $StanCechy['products_stock_availability_id'];
        }
        //
        $GLOBALS['db']->close_query($sqlCecha);
        unset($zapytanieCechy, $StanCechy, $sqlCecha);
        //
    }
    
    // ustala co bedzie wpisane domyslnie w polu INPUT ilosci produktu
    $IloscZakupu = 1;
    
    // jezeli jest puste pola min ilosc zakupu
    if ($this->infoSql['products_minorder'] == 0) {
    
        // sprawdza czy jest przyrost ilosci
        if ($this->infoSql['products_quantity_order'] != 0) {
            $IloscZakupu = $this->infoSql['products_quantity_order'];
        }
        
      } else {
      
        // jezeli nie jest puste pola min zakupu
        $IloscZakupu = $this->infoSql['products_minorder'];
        
    }        
    
    $MinIlosc = ((float)$this->infoSql['products_minorder'] > 0 ? $this->infoSql['products_minorder'] : 0);
    $MaxIlosc = ((float)$this->infoSql['products_maxorder'] > 0 ? $this->infoSql['products_maxorder'] : 0);
    
    $PrzyrostIlosci = ((float)$this->infoSql['products_quantity_order'] > 0 ? $this->infoSql['products_quantity_order'] : 0);
             
    // tworzy pusta tablice dla zakupow
    $this->zakupy = array('mozliwe_kupowanie'   => 'nie',
                          'pokaz_koszyk'        => ( ($this->cechyIlosc > 0) ? 'tak' : 'nie' ),
                          'minimalna_ilosc'     => '',
                          'maksymalna_ilosc'    => '',
                          'przyrost_ilosci'     => '',    
                          'input_ilosci'        => '',
                          'jednostka_miary'     => '',
                          'ilosc_magazyn'       => '',
                          'ilosc_magazyn_jm'    => '',
                          'nr_kat_cechy'        => '',
                          'id_dostep_cechy'     => '',
                          'nazwa_dostepnosci'   => '',
                          'przycisk_kup'        => '',
                          'id_unikat'           => '',
                          'ilosc_gratisu'       => '',                             
                          'input_ilosci_gratis' => '',
                          'przycisk_kup_gratis' => '' );                                               
                               
    // sprawdza czy moze wyswietlic przycisk dodania do koszyka
    $PozwolNaZakup = true;
    
    // sprawdza czy mozna wyswietlic przycisk koszyka ( w listingu produktow )
    $PokazKoszyk = ( ($this->cechyIlosc > 0) ? true : false );

    // jezeli jest wlaczony stan magazynowy i wylaczone kupowanie mimo brakow to stan magazynowy musi byc wiekszy od 0
    if ( $MinIlosc > 0 ) {
        //
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_quantity'] < $MinIlosc ) {
            $PozwolNaZakup = false;
            $PokazKoszyk = false;
        }
        //
      } else {            
        //
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $this->infoSql['products_quantity'] <= 0 ) {
            $PozwolNaZakup = false;
            $PokazKoszyk = false;
        }
        //
    }
    // jezeli produkt nie ma ceny to wylacza mozliwosc zakupu
    if ( $this->infoSql['products_price_tax'] <= 0 && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }
    // jezeli ceny sa tylko widoczne dla klientow zalogowanych
    if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1') && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }
    
    // jezeli produkt ma wylaczone kupowanie
    if ( $this->infoSql['products_buy'] == '0' && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }
    
    // jezeli jest wogole w sklepie wylaczone kupowanie - sklep jako katalog produktow
    if ( PRODUKT_KUPOWANIE_STATUS == 'nie' && $PozwolNaZakup == true ) {
        $PozwolNaZakup = false;
        $PokazKoszyk = false;
    }        
    
    // sprawdza czy mozna kupowac przy dostepnosci produktu
    //
    $NazwaDostepnosci = '';
    //

    if ( $this->infoSql['products_availability_id'] > 0 ) {
        //
        // jezeli jest automatyczna dostepnosc
        if ( $this->infoSql['products_availability_id'] == '99999' ) {
            $Kupowanie = 'tak';
            $Automatyczna = $this->PokazIdDostepnosciAutomatycznych( $this->infoSql['products_quantity']);
            if ( $Automatyczna != '0' ) {
                 $Kupowanie = $GLOBALS['dostepnosci'][ $Automatyczna ]['kupowanie'];
                 // jezeli dostepnosc jest w formie obrazka
                 if ( !empty($GLOBALS['dostepnosci'][ $Automatyczna ]['foto']) ) {
                     $NazwaDostepnosci = '<img src="' . KATALOG_ZDJEC . '/' . $GLOBALS['dostepnosci'][ $Automatyczna ]['foto'] . '" alt="' . $GLOBALS['dostepnosci'][ $Automatyczna ]['dostepnosc'] . '" />';
                   } else {
                     $NazwaDostepnosci = $GLOBALS['dostepnosci'][ $Automatyczna ]['dostepnosc'];
                 }                
                 //                     
            }
           } else {
            $Kupowanie = $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['kupowanie'];
            //
            // jezeli dostepnosc jest w formie obrazka
            if ( !empty($GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['foto']) ) {
                $NazwaDostepnosci = '<img src="' . KATALOG_ZDJEC . '/' . $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['foto'] . '" alt="' . $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['dostepnosc'] . '" />';
              } else {
                $NazwaDostepnosci = $GLOBALS['dostepnosci'][ $this->infoSql['products_availability_id'] ]['dostepnosc'];
            }                
            //
        }  
        //
        if ( $Kupowanie == 'nie' ) {
             $PozwolNaZakup = false;
             // jezeli jest dostepnosc bez kupowania to nie pokaze koszyka
             // usuniecie wpisu spowoduje ze koszyk bedzie pokazywal sie zawsze jezeli produkt
             // bedzie mial cechy - niezaleznie od dostepnosci
             $PokazKoszyk = false;
        }
        //
    }
    //
    
    //
    $MinIlosc = (( $this->info['jednostka_miary_typ'] == '0' ) ? $MinIlosc : (int)$MinIlosc);
    $MaxIlosc = (( $this->info['jednostka_miary_typ'] == '0' ) ? $MaxIlosc : (int)$MaxIlosc);
    $IloscZakupu = (( $this->info['jednostka_miary_typ'] == '0' ) ? $IloscZakupu : (int)$IloscZakupu);
    //
    $this->zakupy = array('mozliwe_kupowanie'   => (( $PozwolNaZakup == true ) ? 'tak' : 'nie' ),
                          'pokaz_koszyk'        => (( $PokazKoszyk == true  ) ? 'tak' : 'nie' ),
                          'minimalna_ilosc'     => $MinIlosc,
                          'maksymalna_ilosc'    => $MaxIlosc,
                          'przyrost_ilosci'     => $PrzyrostIlosci,    
                          'domyslna_ilosc'      => $IloscZakupu,
                          'input_ilosci'        => '<input type="text" id="ilosc_' . $this->idUnikat . $this->id_produktu . '" value="' . $IloscZakupu . '" class="InputIlosc" size="4" onchange="SprIlosc(this,' . $MinIlosc . ',' . $this->info['jednostka_miary_typ'] . ')" name="ilosc" />',
                          'jednostka_miary'     => $this->info['jednostka_miary'],
                          'ilosc_magazyn'       => $this->infoSql['products_quantity'],
                          'ilosc_magazyn_jm'    => (( $this->info['jednostka_miary_typ'] == '0' ) ? $this->infoSql['products_quantity'] : (int)$this->infoSql['products_quantity']) . ' ' . $this->info['jednostka_miary'],
                          'nr_kat_cechy'        => $this->infoSql['products_model'],
                          'id_dostep_cechy'     => $this->infoSql['products_availability_id'],    
                          'nazwa_dostepnosci'   => $NazwaDostepnosci,
                          'przycisk_kup'        => '<span class="' . $this->cssKoszyka . '" onclick="return DoKoszyka(\'' . $this->idUnikat . $this->id_produktu . '\',\'dodaj\',' . $this->cechyIlosc . ',1)" title="' . $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . $this->info['nazwa'] . '">' . $this->cssKoszykaTekst . '</span>',
                          'przycisk_kup_karta'  => '<span class="' . $this->cssKoszyka . '" onclick="return DoKoszyka(\'' . $this->idUnikat . $this->id_produktu . '\',\'dodaj\',0,0)" title="' . $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . $this->info['nazwa'] . '">' . $this->cssKoszykaTekst . '</span>',
                          'id_unikat'           => $this->idUnikat,
                          'ilosc_gratisu'       => $IloscZakupu,
                          'input_ilosci_gratis' => '<input type="hidden" id="ilosc_' . $this->idUnikat . $this->id_produktu . '" value="' . $IloscZakupu . '" name="ilosc" />',
                          'przycisk_kup_gratis' => '<span class="' . $this->cssKoszyka . '" onclick="return DoKoszyka(\'' . $this->idUnikat . $this->id_produktu . '\',\'gratis\',' . $this->cechyIlosc . ',0)" title="' . $GLOBALS['tlumacz']['LISTING_DODAJ_DO_KOSZYKA'] . ' ' . $this->info['nazwa'] . '">' . $this->cssKoszykaTekst . '</span>');                                               
    //
    
    unset($NazwaDostepnosci, $PozwolNaZakup, $IloscZakupu, $MinIlosc, $MaxIlosc, $PrzyrostIlosci, $nrKatCechy, $idDostepnosciCechy);

}
       
?>