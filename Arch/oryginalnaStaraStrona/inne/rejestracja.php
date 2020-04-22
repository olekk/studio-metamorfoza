<?php
chdir('../'); 

if (isset($_GET['email']) || isset($_GET['nick']) || isset($_GET['email_nowy']) || isset($_GET['spr']) ) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (!Sesje::TokenSpr()) {
        echo 'false';
        exit;
    }
}

if (isset($_GET['email'])) {
    //
    $request = $filtr->process(trim(strtolower($_REQUEST['email'])));
    $valid = 'true';

    $zapytanie = "SELECT customers_email_address FROM customers WHERE customers_email_address = '".$request."' AND customers_guest_account = '0'";
    
    // dodatkowy warunek podczas edycji konta
    if ( isset($_GET['klient']) && (int)$_GET['klient'] > 0 ) {
         $zapytanie .= ' AND customers_id != ' . (int)$_GET['klient'];
    }

    $sql = $db->open_query($zapytanie); 
    if ((int)$db->ile_rekordow($sql) > 0) {
        $valid = 'false';
    }
    
    $db->close_query($sql);
    unset($zapytanie);

    echo $valid;
    //
}

if (isset($_GET['nick'])) {
    //
    $request = $filtr->process(trim(strtolower($_REQUEST['nick'])));
    $valid = 'true';

    $zapytanie = "SELECT customers_nick FROM customers WHERE customers_nick = '".$request."' AND  customers_guest_account = '0'";
    
    // dodatkowy warunek podczas edycji konta
    if ( isset($_GET['klient']) && (int)$_GET['klient'] > 0 ) {
         $zapytanie .= ' AND customers_id != ' . (int)$_GET['klient'];
    }    

    $sql = $db->open_query($zapytanie); 
    if ((int)$db->ile_rekordow($sql) > 0) {
        $valid = 'false';
    }

    $db->close_query($sql);
    unset($zapytanie);

    echo $valid;
    //
}

if (isset($_GET['email_nowy'])) {
    //
    $request = $filtr->process(trim(strtolower($_REQUEST['email_nowy'])));
    $valid = 'true';

    $zapytanie = "SELECT customers_email_address FROM customers WHERE customers_email_address = '".$request."' AND customers_guest_account = '0'";

    $sql = $db->open_query($zapytanie); 
    if ((int)$db->ile_rekordow($sql) > 0) {
        $valid = 'false';
    }

    $db->close_query($sql);
    unset($zapytanie);

    echo $valid;
    //
}
?>