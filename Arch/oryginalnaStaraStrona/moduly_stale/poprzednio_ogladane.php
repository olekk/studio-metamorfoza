<?php
if (count($_SESSION['produktyPoprzednioOgladane']) > 0 && !isset($_COOKIE['oknoPoprzednie']) && $_SESSION['mobile'] == 'nie') {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );
    
    //
    $licz = 1;
    //
    
    echo "\n\n";
    
    echo '<div id="PrzyklejOstatnieProd">
            <span class="Zamknij" title="{__TLUMACZ:PRZYCISK_ZAMKNIJ}"></span>
            <span class="Rozwiniecie">{__TLUMACZ:OSTATNIO_OGLADANE_PRODUKTY}</span>';
    
    echo '<ul>';
    //
    $OstatnioOgladane = array_reverse($_SESSION['produktyPoprzednioOgladane']);
    //    
    foreach ($OstatnioOgladane AS $Id) {
        //
        if ( $licz < 11 ) {

            $Produkt = new Produkt( $Id );

            if ( isset($Produkt->info['link']) ) {
                 echo '<li>' . $Produkt->info['link'] . '</li>';
            }

            unset($Produkt);
            //
            $licz++;
            //
        }
    }
    
    echo '</ul>';
    
    echo '</div>';
    
    echo '<script type="text/javascript">';
    echo '$.OstatnioOgladane();';
    echo '</script>';    
    
    //
    unset($licz, $OstatnioOgladane);
    //    

}
?>