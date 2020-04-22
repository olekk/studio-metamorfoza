<?php
chdir('../'); 
 
if (isset($_GET['login']) || isset($_GET['spr']) || isset($_GET['przypomnienie'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (!Sesje::TokenSpr()) {
        echo 'false';
        exit;
    }
}

if (isset($_GET['login'])) {
    //
    $request = $filtr->process(trim(strtolower($_REQUEST['email'])));
    $valid = 'false';

    $zapytanie = "SELECT customers_email_address, customers_nick FROM customers WHERE (customers_email_address = '".$request."' or customers_nick = '".$request."') and customers_guest_account != '1'";

    $sql = $db->open_query($zapytanie); 
    if ((int)$db->ile_rekordow($sql) > 0) {
        $valid = 'true';
    }

    $db->close_query($sql);
    unset($zapytanie);

    echo $valid;
    //
}

if (isset($_GET['przypomnienie'])) {
    //
    $request = $filtr->process(trim(strtolower($_REQUEST['emailprzypomnienie'])));
    $valid = 'false';

    $zapytanie = "SELECT customers_email_address FROM customers WHERE customers_email_address = '".$request."' AND customers_status = '1' AND customers_guest_account != '1'";

    $sql = $db->open_query($zapytanie); 
    if ((int)$db->ile_rekordow($sql) > 0) {
        $valid = 'true';
    }

    $db->close_query($sql);
    unset($zapytanie);

    echo $valid;
    //
}

if (isset($_GET['spr'])) {
    //
    if (isset($_POST['email']) && isset($_POST['haslo'])) {
        //
        $mail = $filtr->process($_POST['email']);
        $haslo = $filtr->process($_POST['haslo']);
        
        $valid = 'false';
        
        $zapytanie = "SELECT customers_email_address, customers_nick, customers_status, customers_password FROM customers WHERE (customers_email_address = '".$mail."' or customers_nick = '".$mail."') and customers_guest_account != '1'";

        $sql = $db->open_query($zapytanie); 
        if ((int)$db->ile_rekordow($sql) > 0) {
            //
            $valid = 'true';
            //
            $info = $sql->fetch_assoc();  
            if (!Klient::sprawdzHasloKlienta($haslo, $info['customers_password'])) {
                $valid = 'false';
            }
            //
        }

        $db->close_query($sql);
        unset($zapytanie, $mail, $haslo); 

        echo $valid;
        //
      } else {
        //
        echo 'false';
        //
    }
}

?>