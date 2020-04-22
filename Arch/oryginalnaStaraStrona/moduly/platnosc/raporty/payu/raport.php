<?php
chdir('../../../../');
require_once('ustawienia/init.php');

$server = 'www.platnosci.pl';
$server_script = '/paygw/UTF/Payment/get';

$zapytanie = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PAYU_%'";
$sql = $db->open_query($zapytanie);

while ($info = $sql->fetch_assoc()) {
    define($info['kod'], $info['wartosc']);
}
$GLOBALS['db']->close_query($sql);
unset($zapytanie, $info, $sql);


if (!isset($_POST['pos_id']) || !isset($_POST['session_id']) || !isset($_POST['ts']) || !isset($_POST['sig'])) {
    die('ERROR: EMPTY PARAMETERS'); //-- brak wszystkich parametrow
}

if ($_POST['pos_id'] != PLATNOSC_PAYU_POS_ID) {
    die('ERROR: WRONG POS ID');  //--- bledny numer POS
}

$sig = md5( $_POST['pos_id'] . $_POST['session_id'] . $_POST['ts'] . PLATNOSC_PAYU_KEY_2);

if ($_POST['sig'] != $sig) {
    die('ERROR: WRONG SIGNATURE');  //--- bledny podpis
}

$ts = time();
$sig = md5( PLATNOSC_PAYU_POS_ID . $_POST['session_id'] . $ts . PLATNOSC_PAYU_KEY_1);

$parameters = "pos_id=" . PLATNOSC_PAYU_POS_ID . "&session_id=" . $_POST['session_id'] . "&ts=" . $ts . "&sig=" . $sig;

$curl = true;
$status = false;
$data = '';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://' . $server . $server_script);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$platnosci_response = curl_exec($ch);
curl_close($ch);

$tablicaOdpowiedzi = Funkcje::Xml2Array($platnosci_response, 'trans');

$status = get_status($tablicaOdpowiedzi);

if ($status['code'] == 99 || ($status['code'] > 0 && $status['code'] <= 7 )) { //--- rozpoznany status transakcji

    if ( isset($tablicaOdpowiedzi['order_id']) && is_numeric($tablicaOdpowiedzi['order_id']) && ($tablicaOdpowiedzi['order_id'] > 0) ) {

        $komentarz = '';
        if ( !is_array($tablicaOdpowiedzi['init']) ) {
            $data .= 'Data rozpoczęcia: ' . $tablicaOdpowiedzi['init'] . '<br />';
        }
        if ( !is_array($tablicaOdpowiedzi['sent']) ) {
            $data .= 'Data wysłania: ' . $tablicaOdpowiedzi['sent'] . '<br />';
        }
        if ( !is_array($tablicaOdpowiedzi['recv']) ) {
            $data .= 'Data odbioru: ' . $tablicaOdpowiedzi['recv'] . '<br />';
        }
        if ( !is_array($tablicaOdpowiedzi['cancel']) ) {
            $data .= 'Data anulowania: ' . $tablicaOdpowiedzi['cancel'] . '<br />';
        }
        $komentarz = 'Numer transakcji: ' . $tablicaOdpowiedzi['id'] . '<br />';
        $komentarz .= $data . 'Status transakcji: ' . $status['message'];

        // zapisanie informacji w historii statusow zamowien w przypadku zakonczenia tranzkcji
        if ( $status['code'] == 99 ) {

            if ( PLATNOSC_PAYU_STATUS_ZAMOWIENIA > 0 ) {
                $status_zamowienia_id = PLATNOSC_PAYU_STATUS_ZAMOWIENIA;
            } else {
                $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();
            }
            //
            $pola = array(
                    array('orders_id ',(int)$tablicaOdpowiedzi['order_id']),
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
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$tablicaOdpowiedzi['order_id'] . "'");
            unset($pola);
        }

        // zapisanie informacji w historii statusow zamowien w przypadku anulowania lub odrzucenia tranzakcji
        if ( $status['code'] == 2 || $status['code'] == 3 || $status['code'] == 6 || $status['code'] == 7 ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();

            $zapytanie = "SELECT comments  FROM orders_status_history WHERE comments = '".$komentarz."'";
            $sql = $db->open_query($zapytanie);

            if ($GLOBALS['db']->ile_rekordow($sql) > 0 ) {
            } else {
                //
                $pola = array(
                        array('orders_id ',(int)$tablicaOdpowiedzi['order_id']),
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
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$tablicaOdpowiedzi['order_id'] . "'");
                unset($pola);
            }
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $sql);

        }
    }
    echo 'OK';
    exit;

} else {

    echo "res: " . print_r($status,true) ."<br>";
    echo "ERROR in Response";
}


function get_status($parts){

  if ($parts['pos_id'] != PLATNOSC_PAYU_POS_ID) return array('code' => false,'message' => 'błędny numer POS');	//--- bledny numer POS
  $sig = md5($parts['pos_id'].$parts['session_id'].$parts['order_id'].$parts['status'].$parts['amount'].$parts['desc'].$parts['ts'].PLATNOSC_PAYU_KEY_2);
  if ($parts['sig'] != $sig) return array('code' => false,'message' => 'błędny podpis'); //--- bledny podpis
  switch ($parts['status']) {
    case 1: return array('code' => $parts['status'], 'message' => 'nowa'); break;
    case 2: return array('code' => $parts['status'], 'message' => 'anulowana'); break;
    case 3: return array('code' => $parts['status'], 'message' => 'odrzucona'); break;
    case 4: return array('code' => $parts['status'], 'message' => 'rozpoczęta'); break;
    case 5: return array('code' => $parts['status'], 'message' => 'oczekuje na odbiór'); break;
    case 6: return array('code' => $parts['status'], 'message' => 'autoryzacja odmowna'); break;
    case 7: return array('code' => $parts['status'], 'message' => 'płatność odrzucona'); break;
    case 99: return array('code' => $parts['status'], 'message' => 'płatność odebrana - zakończona'); break;
    case 888: return array('code' => $parts['status'], 'message' => 'błędny status'); break;
    default: return array('code' => false, 'message' => 'brak statusu'); break;
  }
}

?>