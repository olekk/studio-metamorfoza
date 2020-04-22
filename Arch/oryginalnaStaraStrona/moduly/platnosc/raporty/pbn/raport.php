<?php
chdir('../../../../');

require_once('ustawienia/init.php');
$e = array();

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PBN_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);

if ( !isset($_POST['newStatus']) || !isset($_POST['paymentId']) ) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

$hash = sha1($_POST['newStatus'] . $_POST['transAmount'] . $_POST['paymentId'] . PLATNOSC_PBN_HASLO );

if ( $hash == $_POST['hash'] ) {

    $status = get_status($_POST['newStatus']);

    $komentarz = 'Data transakcji: ' . date("d-m-Y H:i:s") . '<br />';
    $komentarz .= 'Status transakcji: ' . $status['message'] . '<br />';
    $komentarz .= 'Zamowienie ID: ' . ltrim($_POST['paymentId'], '0') . '<br />';

    if ( $_POST['newStatus'] == '2203' || $_POST['newStatus'] == '2303' ) {

        if ( PLATNOSC_PBN_STATUS_ZAMOWIENIA > 0 ) {
            $status_zamowienia_id = PLATNOSC_PBN_STATUS_ZAMOWIENIA;
        } else {
             $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
        }

    } else {
         $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
    }

    $pola = array(
            array('orders_id ',(int)ltrim($_POST['paymentId'], '0')),
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
    $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)ltrim($_POST['paymentId'], '0') . "'");
    unset($pola);

    echo 'OK';
    exit;

} else {

    echo "PROBLEM";
    exit;
}

function get_status($status){

  switch ($status) {
    case 2203: return array('code' => $status, 'message' => 'transakcja zatwierdzona'); break;
    case 2303: return array('code' => $status, 'message' => 'transakcja zatwierdzona'); break;
    case 2202: return array('code' => $status, 'message' => 'transakcja odrzucona'); break;
    case 2302: return array('code' => $status, 'message' => 'transakcja odrzucona'); break;
    case 2201: return array('code' => $status, 'message' => 'transakcja przeterminowana'); break;
    case 2301: return array('code' => $status, 'message' => 'transakcja przeterminowana'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}

?>