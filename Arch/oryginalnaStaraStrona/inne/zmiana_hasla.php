<?php
chdir('../'); 
//
if (isset($_GET['spr'])) {

    if (isset($_POST['id']) && isset($_POST['haslo'])) {

        // wczytanie ustawien inicjujacych system
        require_once('ustawienia/init.php');

        $valid = 'false';
        //
        $id = $filtr->process($_POST['id']);
        //
        $zapytanie = "SELECT customers_password FROM customers WHERE customers_id = '".(int)$id."'";

        $sql = $db->open_query($zapytanie); 
        if ((int)$db->ile_rekordow($sql) > 0) {

            $info = $sql->fetch_assoc();
            $hasloKodowane = $info['customers_password'];

            $stack = explode(':', $hasloKodowane);

            if (sizeof($stack) != 2) $valid = 'false';

            if (md5($stack[1] . $filtr->process($_POST['haslo'])) == $stack[0]) {
                $valid = 'true';
            }

        }

        $db->close_query($sql);
        unset($zapytanie, $info);

        echo $valid;
        //
    }
    
}

?>