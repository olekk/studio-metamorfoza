<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        echo '<div id="PopUpInfo">';
        
        $zapytanie = "SELECT products_options_description FROM products_options WHERE language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' and products_options_id = '" . (int)$_POST['id'] . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        $info = $sql->fetch_assoc();

        echo $info['products_options_description'];
        
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info, $sql);        

        echo '</div>';

    }
    
}
?>