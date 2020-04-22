<?php

if ( isset($pobierzFunkcje) ) {

    // dodatkowo ustalanie wagi produktu z kombinacja cech
    $TablicaCechy = Funkcje::CechyProduktuPoId( $cechy, true );
    $WagaCechy = 0;
    
    for ($g = 0, $n = count($TablicaCechy); $g < $n; $g++) {
    
        $zapytanie = "SELECT DISTINCT options_values_weight
                                 FROM products_attributes
                                WHERE products_id = '" . $this->id_produktu . "' AND 
                                      options_id = '" . $TablicaCechy[$g]['cecha'] . "' AND 
                                      options_values_id = '" . $TablicaCechy[$g]['wartosc'] . "'";
        
        $sql = $GLOBALS['db']->open_query($zapytanie);
        $cecha = $sql->fetch_assoc();
        //
        $WagaCechy += $cecha['options_values_weight'];
        //
        unset($zapytanie, $cecha);
        //
        $GLOBALS['db']->close_query($sql);         
    
    } 

    unset($TablicaCechy);
    
    // dzieli na tablice
    $cechyTb = explode('x', $cechy);
    $cechyTmp = array();
    for ($g = 1; $g < count($cechyTb); $g++) {
        $cechyTmp[] = $cechyTb[$g];
    }
    $cechy = implode(',', $cechyTmp);
    unset($cechyTb, $cechyTmp);

    $DodatkoweCeny = '';
    if ( (int)ILOSC_CEN > 1 ) {
        //
        for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
            //
            $DodatkoweCeny .= 'products_stock_price_' . $n . ', products_stock_price_tax_' . $n . ',';
            //
        }
        //
    }               

    // szuka cech produktu do ustalenia ceny produktu z cechami    
    $zapytanieCechy = "SELECT DISTINCT " . $DodatkoweCeny . " products_stock_attributes, products_stock_price, products_stock_price_tax, products_stock_tax
                                  FROM products_stock
                                 WHERE products_id = '" . $this->id_produktu . "' and products_stock_attributes = '" . $cechy . "'";

    unset($cechy, $DodatkoweCeny);

    $sql = $GLOBALS['db']->open_query($zapytanieCechy);
    $cecha = $sql->fetch_assoc();
    
    // jezeli klient ma inny poziom cen
    if ( $_SESSION['poziom_cen'] > 1 ) {
        //
        // jezeli cena w innym poziomie nie jest pusta
        if ( $cecha['products_stock_price_' . $_SESSION['poziom_cen']] > 0 ) {
            //
            $cecha['products_stock_price_tax'] = $cecha['products_stock_price_tax_' . $_SESSION['poziom_cen']];
            $cecha['products_stock_price'] = $cecha['products_stock_price_' . $_SESSION['poziom_cen']];
            //
        }
        //
    }          
    
    // jezeli nie ma indywidualnej cechy dla kombinacji cech przyjmuje cene domyslna produktu
    if ( $cecha['products_stock_price_tax'] == 0 ) {
    
        $cecha['products_stock_price'] = $this->infoSql['products_price'];   
        $cecha['products_stock_price_tax'] = $this->infoSql['products_price_tax'];  
    
    } else {

        // rabaty klienta od ceny produktu
        $CenaRabatyCechy = $this->CenaProduktuPoRabatach( $cecha['products_stock_price'], $cecha['products_stock_price_tax'] );
        $cecha['products_stock_price'] = $CenaRabatyCechy['netto'];
        $cecha['products_stock_price_tax'] = $CenaRabatyCechy['brutto'];
        unset($CenaRabatyCechy);
        
    }

    $GLOBALS['db']->close_query($sql);  
    unset($zapytanieCechy);

    $TablicaCen = $GLOBALS['waluty']->FormatujCene( $cecha['products_stock_price_tax'], $cecha['products_stock_price'], 0, $this->infoSql['products_currencies_id'], false );

}
       
?>