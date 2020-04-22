<?php

class Stronicowanie {

    static public function PokazStrony($sql, $LinkDoPrzenoszenia) {  
        //
        $IleProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
        
        // zabezpieczenie zeby nie mozna bylo wyswietlic wiecej niz ilosc na stronie x 3
        if ( $_SESSION['listing_produktow'] > LISTING_PRODUKTOW_NA_STRONIE * 3 ) {
             $_SESSION['listing_produktow'] = LISTING_PRODUKTOW_NA_STRONIE;
        }        
        
        $IleStron = $IleProduktow / $_SESSION['listing_produktow'];
        //
        if ($IleStron != (int)$IleStron) {
            $IleStron = (int)$IleStron + 1;
        }
        //
        if (!isset($_GET['s']) || $_GET['s'] <= 0) {
            $_GET['s'] = 1;
        }        
        // jezeli ktos wpisze z reki strone
        if ((int)$_GET['s'] > $IleStron) {
            $_GET['s'] = $IleStron;
        }
        //
        $LewoPrawo = 2;
        $AktualnaStrona = (int)$_GET['s'];
        //
        // poczatek stron
        if ($AktualnaStrona - $LewoPrawo <= 0) {
            $PoczatekStron = 1;
          } else {
            $PoczatekStron = $AktualnaStrona - $LewoPrawo;
        }
        //
        // koniec stron
        if ($AktualnaStrona + $LewoPrawo > $IleStron) {
            $KoniecStron = $IleStron;
          } else {
            $KoniecStron = $AktualnaStrona + $LewoPrawo;
        }    
        //
        $DoWyniku = '';
        for ($st = $PoczatekStron; $st <= $KoniecStron; $st++) {
            //
            $Css = '';
            // jezeli jest aktualnie wyswietlana strona
            if ($st == $AktualnaStrona) {
                $Css = ' class="Aktywna"';
            }
            $DoWyniku .= '<a' . $Css . ' href="' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','idkat','idproducent'), false, '/')) . '/s=' . $st . '">' . $st . '</a>';
        }
        //
        // jezeli pierwsza pozycja jest wieksza od 1
        if ($PoczatekStron > 1) {
            $DoWyniku = '<a href="' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','idkat','idproducent'), false, '/')) . '/s=1">1</a> ... ' . $DoWyniku;
        }
        // jezeli ostatnia strona jest mniejsza od maksymalnej ilosci stron
        if ($KoniecStron < $IleStron) {
            $DoWyniku = $DoWyniku . ' ... <a href="' . $LinkDoPrzenoszenia . str_replace('\%', '[proc]', Funkcje::Zwroc_Get(array('s','idkat','idproducent'), false, '/')) . '/s=' . $IleStron . '">' . $IleStron . '</a>';
        }  
        //
        return array( $DoWyniku, ($AktualnaStrona - 1) * $_SESSION['listing_produktow'] );
    }
    
}

?>