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

        $zapytanie = "SELECT products_text_fields_status FROM products_text_fields WHERE products_text_fields_id = ".$filtr->process($_GET['id_poz']);
        $sql = $db->open_query($zapytanie);

        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc();
            
            // jezeli jest wlaczone - wylaczy
            if ($info['products_text_fields_status'] == '1') {
                $pola = array(array('products_text_fields_status','0'));
                $db->update_query('products_text_fields' , $pola, " products_text_fields_id = '".$filtr->process($_GET['id_poz'])."'");
                unset($pola);                           
            }
            
            // jezeli jest wylaczone - wlaczy
            if ($info['products_text_fields_status'] == '0') {
                $pola = array(array('products_text_fields_status','1'));
                $db->update_query('products_text_fields' , $pola, " products_text_fields_id = '".$filtr->process($_GET['id_poz'])."'");
                unset($pola);                 
            }

            unset($info);

        }
        
        $db->close_query($sql);    
        
        Funkcje::PrzekierowanieURL('dodatkowe_pola_tekstowe.php?id_poz='.(int)$id_poz);
    
    } else {
    
        Funkcje::PrzekierowanieURL('dodatkowe_pola_tekstowe.php');
    
    }
}
?>