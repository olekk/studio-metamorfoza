<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;
    
    if (get_magic_quotes_gpc()) {
        $_POST = Funkcje::stripslashes_array($_POST);
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI_PANEL') ), $GLOBALS['tlumacz'] );

        $haslo = $filtr->process($_POST['nowe_haslo']);
        $haslo_zakodowane = Funkcje::zakodujHaslo($haslo);

        $pola = array( array('customers_password',$haslo_zakodowane) );
        
        $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '".(int)$_POST['id']."'");	
        unset($pola);
        
        $pola = array( array('customers_info_date_account_last_modified','now()') );
        
        $GLOBALS['db']->update_query('customers_info' , $pola, " customers_info_id = '".(int)$_POST['id']."'");	
        unset($pola);  

        echo '<div id="PopUpInfo">';  

        echo $GLOBALS['tlumacz']['KLIENT_ZMIANA_HASLA_SUKCES'];

        echo '</div>';
        
        echo '<div id="PopUpPrzyciski">';
        
            if ( WLACZENIE_SSL == 'tak' ) {
                $link = ADRES_URL_SKLEPU_SSL . '/panel-klienta.html';
              } else {
                $link = 'panel-klienta.html';
            }                  
        
            echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PANEL_KLIENTA'].'</a>';
            
            unset($link);   
            
            if ( WLACZENIE_SSL == 'tak' ) {
              $link = ADRES_URL_SKLEPU;
            } else {
              $link = '/';
            }                
            echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_DO_STRONY_GLOWNEJ'].'</a>'; 
            unset($link);

        echo '</div>'; 

        unset($link);

    }    
    
}
?>