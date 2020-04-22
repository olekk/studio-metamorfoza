<?php
if (!isset($_COOKIE['akcept18plus'])) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );

    echo "\n\n";
    
    echo '<div id="Blokada18"></div>';
    
    echo '<div id="BlokadaOkno">
    
            <strong>{__TLUMACZ:BLOKADA_18_NAGLOWEK}</strong>
            
            <div class="BlokadaTekst">
                {__TLUMACZ:BLOKADA_18_INFO}
            </div>
            
            <div class="BlokadaPrzyciski">
                <span class="przycisk18plus wejdz">{__TLUMACZ:BLOKADA_18_WEJDZ}</span>
                <span class="przycisk18plus zrezygnuj">{__TLUMACZ:BLOKADA_18_REZYGNACJA}</span>
            </div>
            
          </div>';
    
    echo '<script type="text/javascript">';
    echo '$.Blokada18plus();';
    echo '</script>';
        
}
?>