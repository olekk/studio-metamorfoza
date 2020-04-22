<?php
chdir('../'); 
//
if (isset($_POST['value'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        if ( isset($_SESSION['rodzajDostawy']['opis']) ) {
            unset($_SESSION['rodzajDostawy']['opis']);
        }
        $_SESSION['rodzajDostawy']['opis'] = $_POST['value'];

    }
    
}

?>