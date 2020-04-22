<?php

$Tablica = Producenci::TablicaProducenci();

if (count($Tablica) > 1) {

    echo '<ul>';
    
    $Licznik = 1;
    $Css = '';

    foreach ( $Tablica as $Producent ) {
        //
        echo '<li' . $Css . '><a href="' . Seo::link_SEO( $Producent['Nazwa'], $Producent['IdProducenta'], 'producent' ) . '">' . $Producent['Nazwa'] . '</a></li>';
        //
        if ( $Licznik > 5 ) {
             $Css = ' class="Ukryj"';
        }
        //
        $Licznik++;
        //
    }
    
    echo '</ul>';
    
    if ( $Licznik > 5 ) {
         echo '<div class="cl"></div> <span class="PokazWszystkie" id="WszystkieProducent">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</span>';
    }    
    
}

unset($Tablica, $Licznik, $Css);

?>