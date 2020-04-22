<?php
chdir('../../../../');

require_once('ustawienia/init.php');

$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_DOTPAY_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);


if ( !isset($_POST['id']) || !isset($_POST['control']) || !isset($_POST['t_id']) ) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

if ( $_POST['id'] != PLATNOSC_DOTPAY_ID ) {
    $e[]=1;
}

$orginal_amount  = $_POST['orginal_amount'];
$tab             = explode(" ", $orginal_amount);
$orginal_amount  = $tab[0];
$kwota           = $_POST['control'];

if ( number_format((double)$orginal_amount,2) != number_format((double)$kwota, 2) ) {
    $e[]=2;
}

if ( strlen($_POST['t_id']) < 5 ) {
    $e[]=3;
}

$sig = md5( PLATNOSC_DOTPAY_PIN . ':' . PLATNOSC_DOTPAY_ID . ':' . $_POST['control'] . ':' . $_POST['t_id'] . ':' . $_POST['amount'] . ':' . $_POST['email'] . ':' . (isset($_POST['service']) ? $_POST['service'] : '') . ':' . (isset($_POST['code']) ? $_POST['code'] : '' ) . ':' . (isset($_POST['username']) ? $_POST['username'] : '' ) . ':' . (isset($_POST['password']) ? $_POST['password'] : '' ) . ':' . $_POST['t_status'] );

if ( $sig != $_POST['md5'] ) {
    $e[]=5;
}

if ( count($e) > 0 ) {

    print "AP-OSC PROBLEM: $e[0]";
    exit;

} else {

    $status = get_status($_POST['t_status']);

    $komentarz = 'Numer transakcji: ' . $_POST['t_id'] . '<br />';
    $komentarz .= 'Data transakcji: ' . $_POST['t_date'] . '<br />';
    $komentarz .= 'Status transakcji: ' . $status['message'];

    $zapytanie = "SELECT orders_id FROM orders WHERE customers_email_address = '" . $_POST['email'] . "' ORDER BY date_purchased DESC LIMIT 1";
    $sql = $db->open_query($zapytanie);
    if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {
        $info = $sql->fetch_assoc();
        if ( $_POST['t_status'] == '2' ) {

            if ( PLATNOSC_DOTPAY_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_DOTPAY_STATUS_ZAMOWIENIA;
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

            // zmina statusu zamowienia
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
    exit;

}

function get_status($status){

  switch ($status) {
    case 1: return array('code' => $status, 'message' => 'NOWA'); break;
    case 2: return array('code' => $status, 'message' => 'WYKONANA'); break;
    case 3: return array('code' => $status, 'message' => 'ODMOWNA'); break;
    case 4: return array('code' => $status, 'message' => 'ANULOWANA'); break;
    case 5: return array('code' => $status, 'message' => 'REKLAMACJA'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}
?>