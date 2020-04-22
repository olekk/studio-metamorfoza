<?php

if (!class_exists('ot_discount_coupon')) {

    class ot_discount_coupon {
    
        var $tytul, $wyjscie;

        function ot_discount_coupon( $parametry ) {
            global $zamowienie;

            $Tlumaczenie        = $GLOBALS['tlumacz'];

            $this->paramatery   = $parametry;

            $this->tytul        = $Tlumaczenie['OT_DISCOUNT_COUPON_TYTUL'];
            $this->sortowanie   = $this->paramatery['sortowanie'];
            $this->prefix       = $this->paramatery['prefix'];
            $this->klasa        = $this->paramatery['klasa'];
            $this->sortowanie   = $this->paramatery['sortowanie'];
            $this->ikona        = '';
            $this->wyswietl     = false;
            $this->id           = $this->paramatery['id'];

            unset($Tlumaczenie);

        }

        function przetwarzanie() {

            if ( isset($_SESSION['kuponRabatowy']) ) {

                $wynik = array('id' => $this->id,
                               'text' => $this->tytul . ': ' . $_SESSION['kuponRabatowy']['kupon_kod'],
                               'prefix' => $this->prefix,
                               'klasa' => $this->klasa,
                               'wartosc' => $_SESSION['kuponRabatowy']['kupon_wartosc'],
                               'sortowanie' => $this->sortowanie);

                return $wynik;
                
            }

            return;
        }
        
    }

}
?>