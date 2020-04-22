<?php

function PowtorzPlatnosc( $parametry, $zamowienie_id ) {

    $zapytanie = "SELECT wartosc FROM modules_payment_params WHERE kod = 'PLATNOSC_PAYU_KEY_1'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    while ($info = $sql->fetch_assoc()) {
        $klucz1 = $info['wartosc'];
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    $sygnatura = '';
    $ts = time();
    $session_id = session_id() . '-' . $parametry['order_id'] . '-'. substr(md5(time()), 16);
    $parameters = array();

    $parameters['pos_id'] = $parametry['pos_id'];
    $parameters['session_id'] = $session_id;
    $parameters['pos_auth_key'] = $parametry['pos_auth_key'];
    $parameters['amount'] = $parametry['amount'];
    $parameters['desc'] = $parametry['desc'];
    $parameters['desc2'] = $parametry['desc2'];
    $parameters['order_id'] = $parametry['order_id'];
    $parameters['first_name'] = $parametry['first_name'];
    $parameters['last_name'] = $parametry['last_name'];
    $parameters['street'] = $parametry['street'];
    $parameters['city'] = $parametry['city'];
    $parameters['post_code'] = $parametry['post_code'];
    $parameters['email'] = $parametry['email'];
    $parameters['phone'] = $parametry['phone'];
    $parameters['language'] = $parametry['language'];
    $parameters['client_ip'] = $parametry['client_ip'];
    $parameters['ts'] = $ts;

    $sygnatura .= $parameters['pos_id'];
    $sygnatura .= $parameters['session_id'];
    $sygnatura .= $parameters['pos_auth_key'];
    $sygnatura .= $parameters['amount'];
    $sygnatura .= $parameters['desc'];
    $sygnatura .= $parameters['desc2'];
    $sygnatura .= $parameters['order_id'];
    $sygnatura .= $parameters['first_name'];
    $sygnatura .= $parameters['last_name'];
    $sygnatura .= $parameters['street'];
    $sygnatura .= $parameters['city'];
    $sygnatura .= $parameters['post_code'];
    $sygnatura .= $parameters['email'];
    $sygnatura .= $parameters['phone'];
    $sygnatura .= $parameters['language'];
    $sygnatura .= $parameters['client_ip'];
    $sygnatura .= $ts;
    $sygnatura .= $klucz1;

    $parameters['sig'] = md5($sygnatura);


    $formularz = '';
    while (list($key, $value) = each($parameters)) {
        $formularz .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';
    }


    $tekst = '<form action="https://www.platnosci.pl/paygw/UTF/NewPayment" method="post" name="payform" class="cmxform">
                   <div style="text-align:center;padding:5px;">
                      {__TLUMACZ:PRZEJDZ_DO_PLATNOSCI}:<br /><br />';
    $tekst .= $formularz;
    $tekst .= '   <input class="przyciskZaplac" type="submit" id="submitButton" value="{__TLUMACZ:PRZYCISK_PRZEJDZ_DO_PLATNOSCI}" /><br />
                   </div>
              </form>';

    return $tekst;

}
?>