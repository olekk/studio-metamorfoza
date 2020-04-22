<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['id_poz'])) {
        $id_poz = $filtr->process($_GET['id_poz']);
       } else {
        $id_poz = 0;
    }

    if ((int)$id_poz > 0) {

        $zapytanie = "select poll_login from poll where id_poll = ".$filtr->process($_GET['id_poz']);
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            if ($info['poll_login'] == '1') {
                $pola = array(array('poll_login','0'));
                $db->update_query('poll' , $pola, " id_poll = '".$filtr->process($_GET['id_poz'])."'");                
            }
            
            if ($info['poll_login'] == '0') {
                $pola = array(array('poll_login','1'));
                $db->update_query('poll' , $pola, " id_poll = '".$filtr->process($_GET['id_poz'])."'");                 
            }

            unset($pola,$info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('ankiety.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('ankiety.php');
    
    }
}
?>