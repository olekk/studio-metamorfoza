<?php

if ( isset($pobierzFunkcje) ) {

    $TablicaCechy = Funkcje::CechyProduktuPoId( $cechy, true );
    
    $CenaCechBrutto = 0;
    $CenaCechNetto = 0;
    $WagaCechy = 0;
    
    for ($g = 0, $n = count($TablicaCechy); $g < $n; $g++) {
    
        $zapytanie = "SELECT DISTINCT pa.options_values_weight, 
                                      pa.options_values_price, 
                                      pa.options_values_tax, 
                                      pa.options_values_price_tax, 
                                      pa.price_prefix,
                                      po.products_options_value
                                 FROM products_attributes pa, products_options po
                                WHERE pa.options_id = po.products_options_id AND 
                                      pa.products_id = '" . $this->id_produktu . "' AND 
                                      pa.options_id = '" . $TablicaCechy[$g]['cecha'] . "' AND 
                                      pa.options_values_id = '" . $TablicaCechy[$g]['wartosc'] . "'";
        
        $sql = $GLOBALS['db']->open_query($zapytanie);
        $cecha = $sql->fetch_assoc();
        //
        $WspolczynnikRabatu = 1;
        if ( $this->info['rabat_produktu'] > 0 ) {
            $WspolczynnikRabatu = (100 - $this->info['rabat_produktu']) / 100;
        }          
        //
        // jezeli jest cecha kwotowa przeliczy waluty na domyslna
        if ( $cecha['products_options_value'] == 'kwota' ) {
            //
            // zwraca tablice z cenna netto i brutto
            $cenyCech = $GLOBALS['waluty']->FormatujCene($cecha['options_values_price_tax'], $cecha['options_values_price'], 0, $this->infoSql['products_currencies_id'], false);                
            //
            $cecha['options_values_price_tax'] = $cenyCech['brutto'];
            $cecha['options_values_price'] = $cenyCech['netto'];
            //
            unset($cenyCech);
            //
        }
        //
        if ( $cecha['price_prefix'] == '-' ) {
            //
            if ( $cecha['products_options_value'] == 'kwota' ) {
                //
                // dodaje rabaty do produktu do wartosci cech
                $CenaCechBrutto -= round( $cecha['options_values_price_tax'] * $WspolczynnikRabatu, 2);
                $CenaCechNetto -= round( $cecha['options_values_price'] * $WspolczynnikRabatu, 2);
                //
            }
            if ( $cecha['products_options_value'] == 'procent' ) {
                //
                $CenaCechBrutto -= round( $this->info['cena_brutto_bez_formatowania'] * ($cecha['options_values_price_tax'] / 100), 2);
                $CenaCechNetto -= round( $this->info['cena_netto_bez_formatowania'] * ($cecha['options_values_price_tax'] / 100), 2);
                //
            }                
            //
          } else {
            //
            if ( $cecha['products_options_value'] == 'kwota' ) {
                //
                // dodaje rabaty do produktu do wartosci cech
                $CenaCechBrutto += round( $cecha['options_values_price_tax'] * $WspolczynnikRabatu, 2);
                $CenaCechNetto += round( $cecha['options_values_price'] * $WspolczynnikRabatu, 2);
                //
            }
            if ( $cecha['products_options_value'] == 'procent' ) {
                //
                $CenaCechBrutto += round( $this->info['cena_brutto_bez_formatowania'] * ($cecha['options_values_price_tax'] / 100), 2);
                $CenaCechNetto += round( $this->info['cena_netto_bez_formatowania'] * ($cecha['options_values_price_tax'] / 100), 2);
                //
            } 
            //              
        }
        // dodawanie wagi
        $WagaCechy += $cecha['options_values_weight'];
        //
        unset($zapytanie, $cecha, $WspolczynnikRabatu);
        //
        $GLOBALS['db']->close_query($sql);         
    
    }
    
    $TablicaCen = $GLOBALS['waluty']->FormatujCene( $CenaCechBrutto, $CenaCechNetto, 0, $_SESSION['domyslnaWaluta']['id'], false );
   
}
       
?>