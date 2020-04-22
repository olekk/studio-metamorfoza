<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_CASHBILL_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

if ( !isset($_POST['service']) || !isset($_POST['orderid']) || !isset($_POST['amount']) || !isset($_POST['userdata']) || !isset($_POST['status']) ) {
    die('ERROR: BRAK WSZYSTKICH PARAMETROW'); //-- brak wszystkich parametrow
}

$service    = PLATNOSC_CASHBILL_ID;
$key        = PLATNOSC_CASHBILL_SECRET;

try 
{

    if( check_sign( $_POST, $key, $_POST['sign'] ) && $_POST['service'] == $service ) {


        $komentarz  = 'Numer transakcji: ' . $_POST['orderid'] . '<br />';
        $komentarz .= 'Status transakcji: ' . $_POST['status'];

        $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . $_POST['userdata'] . "' LIMIT 1";
        $sql = $db->open_query($zapytanie);

        if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {

            $info = $sql->fetch_assoc();

            if ( $_POST['status'] == 'ok' ) {

                if ( PLATNOSC_CASHBILL_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_CASHBILL_STATUS_ZAMOWIENIA;
                } else {
                    $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
                }

                $pola = array(
                        array('orders_id ',(int)$info['orders_id']),
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
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$info['orders_id'] . "'");
                unset($pola);

            } else {

                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
                $pola = array(
                        array('orders_id ',(int)$info['orders_id']),
                        array('orders_status_id',$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified ','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);
            }

        }
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);

        echo 'OK';
    }
}
catch (Exception $exception)
{
    echo 'ERROR: '.$exception->getMessage();
}

function check_sign($data, $key, $sign) {
    if ( md5( $data['service'].$data['orderid'].$data['amount'].$data['userdata'].$data['status'].$key ) == $sign ) {
        return true;
    } else {
        return false;
    }
}

?>