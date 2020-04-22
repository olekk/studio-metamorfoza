<?php
chdir('../');            

if (isset($_POST['id']) && isset($_POST['akcja']) && $_POST['akcja'] == 'aktywuj') {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KUPONY_RABATOWE') ), $GLOBALS['tlumacz'] );

        $kod = $_POST['id'];
        //

        if ( $kod != '' ) {

            $kupon = new Kupony($kod);
            $tablica_kuponu = $kupon->kupon;

            if ( count($tablica_kuponu) > 0 ) {

              if ( $tablica_kuponu['kupon_status'] ) {
              
                echo '<div id="PopUpInfo">';

                echo $GLOBALS['tlumacz']['KUPON_AKTYWOWANY'] . '<br />';
                echo '<h3>' . $kod . '</h3>';
                
                // jezeli kupon nie obejmuje wszystkich produktow
                if ( $tablica_kuponu['mniejsza_wartosc'] ) {
                     echo '<br />' . $GLOBALS['tlumacz']['KUPON_WYBRANE_PRODUKTY'];
                }
                
                // info ze kuponem nie sa objete produkty promocyjne
                if ( $tablica_kuponu['warunek_promocja'] ) {
                     echo $GLOBALS['tlumacz']['KUPON_TYLKO_PROMOCJE']; 
                }

                if ( $tablica_kuponu['mniejsza_wartosc'] ) {
                     echo '. '. $GLOBALS['tlumacz']['KUPON_CZESCIOWO'] . '<br />';
                }                
                
                echo '</div>';
                $_SESSION['kuponRabatowy'] = $tablica_kuponu;
                
              } else {
              
                echo '<div id="PopUpUsun">';
                
                if ( !$tablica_kuponu['grupa_klientow'] ) {
                
                    echo $GLOBALS['tlumacz']['KUPON_NIE_SPELNIA_WARUNKOW'] . ' <br />';
                    
                    // info ze kuponem nie sa objete produkty promocyjne
                    if ( $tablica_kuponu['warunek_promocja'] ) {
                         echo $GLOBALS['tlumacz']['KUPON_WYBRANE_PRODUKTY'] . $GLOBALS['tlumacz']['KUPON_TYLKO_PROMOCJE'] . '. <br />';
                    }
                    
                  } else {
                  
                    echo $GLOBALS['tlumacz']['KUPON_TYLKO_GRUPA_KLIENTOW'];
                  
                }
                
                echo '</div>';
                
                unset($_SESSION['kuponRabatowy']);
                
              }

            } else {

              echo '<div id="PopUpUsun">';
              echo $GLOBALS['tlumacz']['KUPON_NIE_ISTNIEJE'] . ' <br />';
              echo '</div>';

              unset($_SESSION['kuponRabatowy']);

            }
        }

        echo '<div id="PopUpPrzyciski">';

        echo '<span onclick="stronaReload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';

        echo '</div>';


    }
    
}
?>