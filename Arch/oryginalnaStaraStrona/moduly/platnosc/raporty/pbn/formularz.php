<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PBN_%'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        define($info['kod'], $info['wartosc']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    $time = time();
    if (is_numeric(PLATNOSC_PBN_NUMER_TERMIN)) {
        $pay_time = (int)PLATNOSC_PBN_NUMER_TERMIN * 60;
    } else {
        $pay_time = 50000 * 60;
    }
    $pay_time                       = $time + $pay_time;
    $parametry['date_valid']        = '<date_valid>' . date('d-m-Y H:i:s', $pay_time) . '</date_valid>';

    $parametry['hash']              = '<hash>' . sha1($parametry['id_client'] . $parametry['id_trans'] . $parametry['date_valid'] . $parametry['amount'] . $parametry['currency'] .$parametry['email'] . $parametry['account'] . $parametry['accname'] .$parametry['backpage'] .  $parametry['backpagereject'] . $parametry['password']) . '</hash>';

    $formData = '';
    while (list($key, $value) = each($parametry)) {
        if ( $key != 'rodzaj_platnosci' && $key != 'password' ) {
            $formData .= $value;
        }
    }

    $formDataToSend = base64_encode($formData);

    $tekst = '<form action="https://'.( PLATNOSC_PBN_SANDBOX == '1' ? 'pbn.paybynet.com.pl/PayByNetT/trans.do' : 'pbn.paybynet.com.pl/PayByNet/trans.do') .'" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
    $tekst .= '<input type="hidden" value="'.$formDataToSend.'" name="hashtrans">';
    $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br />
                   </div>
              </form>';

    return $tekst;

}
?>