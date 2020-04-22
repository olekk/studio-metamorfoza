<?php

if ( isset($pobierzFunkcje) ) {

    $ZnizkaWynik = 0;

    if ($this->znizkiZalezneOdIlosci != '') {
        //
        $JakieZnizki = explode(';', $this->znizkiZalezneOdIlosci);
        //
        for ($k = 0, $l = count($JakieZnizki); $k < $l; $k++) {
            //
            $PodzialZnizki = explode(':', $JakieZnizki[$k]);
            if ($ilosc >= $PodzialZnizki[0] && $ilosc <= $PodzialZnizki[1]) {
                $ZnizkaWynik = (float)$PodzialZnizki[2];
            }
            unset($PodzialZnizki);
            //
        }
        //
        unset($JakieZnizki);
        //
    }

}
       
?>