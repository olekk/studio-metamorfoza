<?php

$TablicaKategoriiArtykulow = Aktualnosci::TablicaKategorieAktualnosci();

if (count($TablicaKategoriiArtykulow) > 0) {
    //
    echo '<ul class="Lista BezLinii">';
    //
    foreach ( $TablicaKategoriiArtykulow as $Kategoria ) {
        //
        $aktywnaKategoria = '';
        //
        if ( isset($_GET['idkatart']) && $Kategoria['id'] == (int)$_GET['idkatart'] ) {
             //
             $aktywnaKategoria = 'style="font-weight:bold"';
             //
        }
        //
        echo '<li><a ' . $aktywnaKategoria . ' href="' . $Kategoria['seo'] . '">' . $Kategoria['nazwa'] . '</a></li>';
        //
        unset($aktywnaKategoria);
        //
    }
    //
    echo '</ul>';
    //
}

unset($TablicaKategoriiArtykulow);

?>