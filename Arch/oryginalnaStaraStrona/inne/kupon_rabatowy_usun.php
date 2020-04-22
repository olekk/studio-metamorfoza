<?php
chdir('../');            

if (isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KUPONY_RABATOWE') ), $GLOBALS['tlumacz'] );

        unset($_SESSION['kuponRabatowy']);

        echo '<div id="PopUpUsun">';
        echo $GLOBALS['tlumacz']['KUPON_ZOSTAL_USUNIETY_ZAMOWIENIA'] . ' <br />';
        echo '</div>';

        echo '<div id="PopUpPrzyciski">';

        echo '<span onclick="stronaReload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';

        echo '</div>';


    }
    
}
?>