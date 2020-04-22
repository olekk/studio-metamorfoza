<?php
if (!isset($_COOKIE['akceptCookie'])) {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('MODULY_STALE') ), $GLOBALS['tlumacz'] );

    echo "\n\n";
    
    echo '<script type="text/javascript">';
    echo 'var infoCookieTekst = "{__TLUMACZ:INFO_COOKIE_TEKST}";';
    echo 'var infoCookieAkcept = "{__TLUMACZ:INFO_COOKIE_ZAMKNIJ}";';
    echo '$.InfoCookie();';
    echo '</script>';
        
}
?>