<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {


    $formularz = '';
    while (list($key, $value) = each($parametry)) {
        if ( $key != 'rodzaj_platnosci' ) {
            $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
        }
    }


    $tekst = '<form action="https://vpos.polcard.com.pl/vpos/ecom/service.htm" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
    $tekst .= $formularz;
    $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br />
                   </div>
              </form>';

    return $tekst;

}
?>