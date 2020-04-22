<?php
chdir('../');            

if (isset($_POST['id']) && !empty($_POST['id'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $GLOBALS['koszykKlienta']->AktualizujKomentarz( $filtr->process($_POST['id']), $filtr->process($_POST['komentarz']) );

    }
    
}
?>