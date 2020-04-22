<?php
chdir('../');            

if (isset($_POST['akcja']) && $_POST['akcja'] == 'usun') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('PUNKTY') ), $GLOBALS['tlumacz'] );

        unset($_SESSION['punktyKlienta']);

        echo '<div id="PopUpUsun">';
        echo $GLOBALS['tlumacz']['PUNKTY_ZOSTALY_USUNIETE_ZAMOWIENIA'] . ' <br />';
        echo '</div>';

        echo '<div id="PopUpPrzyciski">';

        echo '<span onclick="stronaReload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';

        echo '</div>';


    }
    
}
?>