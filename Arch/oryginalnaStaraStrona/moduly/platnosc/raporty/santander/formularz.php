<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_SANTANDER_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    $formularz = '';
    while (list($key, $value) = each($parametry)) {
        if ( $key != 'rodzaj_platnosci' ) {
            $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
        }
    }

    $tekst = '<form action="https://wniosek.eraty.pl/formularz/" method="post" name="payform" class="cmxform">
              <div style="text-align:center;padding:5px;">{__TLUMACZ:PRZEJDZ_DO_WNIOSKU_RATALNEGO}:<br /><br />';
              $tekst .= $formularz;
              $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_KUPUJE_Z_SANTANDER}" />
              </div>
              </form>';

    return $tekst;
}
?>