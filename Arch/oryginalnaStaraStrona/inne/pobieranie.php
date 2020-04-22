<?php
chdir('../');            

if (isset($_GET['id']) && (int)$_GET['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $zapytanie = "SELECT products_file FROM products_file WHERE products_file_unique_id = " . sqrt((int)$_GET['id']);
        $sql = $GLOBALS['db']->open_query($zapytanie);
        
        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
        
            $info = $sql->fetch_assoc();

            Funkcje::pobierzPlik($info['products_file']);
            
            unset($info);
            
        } else {
        
            echo 'Nie mozna pobrac pliku ...';
        
        }
        
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie);
        
    } else {
    
        echo 'Nie mozna pobrac pliku ...';
    
    }
}
        
?>