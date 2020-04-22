<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_POST['stala'] == 'NAGLOWEK') {

        $pola = array(
                array('value',htmlspecialchars($_POST['wart'])));
                
      } else {
      
        $pola = array(
                array('value',$filtr->process($_POST['wart'])));

    }
    
    $sql = $db->update_query('settings', $pola, " code = '".$filtr->process($_POST['stala'])."'");	
    unset($pola); 
    
}
?>