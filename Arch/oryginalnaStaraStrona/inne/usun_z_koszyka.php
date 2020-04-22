<?php
chdir('../');            

if (isset($_POST['id']) && isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $id = $_POST['id'];
        //        
        $Produkt = new Produkt( $id );
        //
        
        echo '<div id="PopUpUsun">';
    
        echo $GLOBALS['tlumacz']['INFO_DO_KOSZYKA_USUNIETY_PRODUKT'] . ' <br />';
        
        echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
        
        echo '</div>';
        
        echo '<div id="PopUpPrzyciski">';
        
            echo '<span onclick="window.location.reload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
            
        echo '</div>';
            
        //
        $GLOBALS['koszykKlienta']->UsunZKoszyka( $id );    

        //
        unset($Produkt);
        //

    }
    
}
?>