<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        //
        $Produkt = new Produkt( (int)$_POST['id'] );
        //

        if (!isset($_POST['akcja'])) {
        
            echo '<div id="PopUpDodaj">';
        
            echo $GLOBALS['tlumacz']['INFO_DO_SCHOWKA_DODANY_PRODUKT'] . ' <br />';
            
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            
            echo '</div>';
            
            echo '<div id="PopUpPrzyciski">';
            
                echo '<span onclick="stronaReload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                echo '<a href="' . Seo::link_SEO('schowek.php', '', 'inna') . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_SCHOWKA'].'</a>';
                
            echo '</div>';
                
            //
            $GLOBALS['schowekKlienta']->DodajDoSchowka( (int)$_POST['id'] );  

        } else if (isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {
        
            echo '<div id="PopUpUsun">';
        
            echo $GLOBALS['tlumacz']['INFO_DO_SCHOWKA_USUNIETY_PRODUKT'] . ' <br />';
            
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            
            echo '</div>';
            
            echo '<div id="PopUpPrzyciski">';
            
                echo '<span onclick="stronaReload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                
            echo '</div>';
                
            //
            $GLOBALS['schowekKlienta']->UsunZeSchowka( (int)$_POST['id'] );    
        
        }
        
        //
        unset($Produkt);
        //

    }
    
}
?>