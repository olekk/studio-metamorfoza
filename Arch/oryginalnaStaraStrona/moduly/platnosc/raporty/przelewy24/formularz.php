<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PRZELEWY24_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    $kluczCRC                           = session_id() . '-'. substr(md5(time()), 16) . '|' . PLATNOSC_PRZELEWY24_ID . '|' . $parametry['p24_kwota'] . '|' . PLATNOSC_PRZELEWY24_CRC;
    $kluczCRC                           = md5($kluczCRC);

    $parametry['p24_session_id'] = session_id() . '-'. substr(md5(time()), 16);
    $parametry['p24_crc'] = $kluczCRC;
    $parametry['p24_opis'] = 'Numer zamowienia: ' . (int)$zamowienie_id;

    $formularz = '';
    while (list($key, $value) = each($parametry)) {
        if ( $key != 'rodzaj_platnosci' ) {
            $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
        }
    }


    $tekst = '<form action="https://'.( PLATNOSC_PRZELEWY24_SANDBOX == '1' ? 'sandbox.przelewy24.pl/index.php' : 'secure.przelewy24.pl/index.php') .'" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
    $tekst .= $formularz;
    $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br />
                   </div>
              </form>';

    return $tekst;

}
?>