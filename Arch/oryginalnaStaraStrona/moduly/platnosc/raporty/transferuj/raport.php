<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_TRANSFERUJ_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);


if(!empty($_POST) && in_array($_SERVER['REMOTE_ADDR'],explode(',',PLATNOSC_TRANSFERUJ_IP))) {

    $sid = PLATNOSC_TRANSFERUJ_ID;
    $tr_id = $_POST['tr_id'];
    $tr_amount = $_POST['tr_amount'];
    $tr_crc = $_POST['tr_crc'];
    $kod = PLATNOSC_TRANSFERUJ_CRC;

    if ( !isset($_POST['tr_id']) || !isset($_POST['tr_amount']) || !isset($_POST['tr_crc']) ) {
        die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
    }

    if ( md5($sid.$tr_id.$tr_amount.$tr_crc.$kod) == $_POST['md5sum'] ) {

        $tr_paid = $_POST['tr_paid'];
        $order_id = base64_decode($_POST['tr_crc']);
        $status_transakcji = $_POST['tr_status'];

        if ($status_transakcji == 'TRUE') {

            $komentarz  = 'Numer transakcji: ' . $_POST['tr_id'] . '<br />';
            $komentarz .= 'Data transakcji: ' . $_POST['tr_date'] . '<br />';
            $komentarz .= 'Status transakcji: wykonana';
            if ( isset($_POST['tr_error']) && $_POST['tr_error'] == 'overpay' ) {
                $komentarz .= ' (nadpłata) wpłacona kwota: ' . $tr_amount;
            } elseif ( isset($_POST['tr_error']) && $_POST['tr_error'] == 'surcharge' ) {
                $komentarz .= ' (niedopłata) wpłacona kwota: ' . $tr_amount;
            }

            if ( PLATNOSC_TRANSFERUJ_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_TRANSFERUJ_STATUS_ZAMOWIENIA;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }

            $pola = array(
                    array('orders_id ',(int)$order_id),
                    array('orders_status_id',$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified ','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmiana statusu zamowienia
            $pola = array(
                    array('orders_status ',$status_zamowienia_id),
                    array('payment_info ',''),
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$order_id . "'");
            unset($pola);
        }
    }
}
echo "TRUE";
exit;

?>