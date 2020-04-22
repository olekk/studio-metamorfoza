<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYPAL_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

include('moduly/platnosc/raporty/paypal/ipnlistener.php');
$listener = new IpnListener();

if ( PLATNOSC_PAYPAL_SANDBOX == '1' ) {
    $listener->use_sandbox = true;
} else {
    $listener->use_sandbox = false;
}

try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    exit(0);
}

$parameters = array();
$parameters = $listener->getTextReport();
$status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
$komentarz = '';

if ($verified) {

    while (list($key, $value) = each($parameters)) {
        $raport .= $key . '=' . $value . "\n";
    }

    $komentarz  .= 'Numer transakcji: ' . $parameters['txn_id'] . '<br />';
    $komentarz  .= 'Status transakcji: ' . $parameters['payment_status'] . '<br />';
    $komentarz  .= 'Data transakcji: ' . $parameters['payment_date'] . '<br />';
    $komentarz  .= 'Kwota wpłaty: ' . $parameters['mc_gross'] . ' ' .  $parameters['mc_currency'];

    if ($parameters['payment_status'] == 'Pending') {
        $komentarz .= '<br />' . $parameters['pending_reason'];
    } elseif ( ($parameters['payment_status'] == 'Reversed') || ($parameters['payment_status'] == 'Refunded') ) {
        $komentarz .= '<br />' . $parameters['reason_code'];
    }

    if ( ($parameters['payment_status'] == 'Completed' || $parameters['payment_status'] == 'Zakończona' ) && PLATNOSC_PAYPAL_STATUS_ZAMOWIENIA > 0 ) {
        $status_zamowienia_id = PLATNOSC_PAYPAL_STATUS_ZAMOWIENIA;
    }

    $pola = array(
            array('orders_id ',(int)$parameters['invoice']),
            array('orders_status_id',$status_zamowienia_id),
            array('date_added','now()'),
            array('customer_notified ','0'),
            array('customer_notified_sms','0'),
            array('comments',$komentarz)
    );
    $GLOBALS['db']->insert_query('orders_status_history' , $pola);
    unset($pola);

    // zmina statusu zamowienia
    $pola = array(
            array('orders_status ',$status_zamowienia_id),
            array('payment_info ',''),
    );
    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$parameters['invoice'] . "'");
    unset($pola);


} else {

    reset($parameters);

    $komentarz  .= 'Numer transakcji: ' . $parameters['txn_id'] . '<br />';
    $komentarz  .= 'Status transakcji: ' . $parameters['payment_status'] . '<br />';
    $komentarz  .= 'Data transakcji: ' . $parameters['payment_date'] . '<br />';
    $komentarz  .= 'Uwagi: ' . $parameters['pending_reason'] . ' ' .  $parameters['reason_code'];


    $pola = array(
            array('orders_id ',(int)$parameters['invoice']),
            array('orders_status_id',$status_zamowienia_id),
            array('date_added','now()'),
            array('customer_notified ','0'),
            array('customer_notified_sms','0'),
            array('comments',$komentarz)
    );

    $GLOBALS['db']->insert_query('orders_status_history' , $pola);
    unset($pola);

}

?>
