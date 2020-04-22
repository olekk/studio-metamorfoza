<?php

if ( isset($pobierzFunkcje) ) {

    $ZnizkaTablica = array();

    if ($this->znizkiZalezneOdIlosci != '') {
        //
        $JakieZnizki = explode(';', $this->znizkiZalezneOdIlosci);
        //
        for ($k = 0, $l = count($JakieZnizki); $k < $l; $k++) {
            //
            $PodzialZnizki = explode(':', $JakieZnizki[$k]);
            $ZnizkaTablica[] = array('od'     => $PodzialZnizki[0],
                                     'do'     => $PodzialZnizki[1],
                                     'znizka' => $PodzialZnizki[2]);
            unset($PodzialZnizki);
            //
        }
        //
        unset($JakieZnizki);
        //         
    }

}
       
?>