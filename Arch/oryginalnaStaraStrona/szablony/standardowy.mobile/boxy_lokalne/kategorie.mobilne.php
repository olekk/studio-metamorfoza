<?php

echo '<ul>';

foreach(Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica) {
    //
	  echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica, array(), 0);
    //
}

echo '</ul>';

?>