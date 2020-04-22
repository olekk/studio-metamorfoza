<?php
chdir('../'); 

if (isset($_POST['id']) && (int)$_POST['id'] > 0) { 

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        //
        $Produkt = new Produkt( (int)$_POST['id'] );
        //    
        echo Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], ZDJECIE_LISTING_POWIEKSZENIE_SZEROKOSC, ZDJECIE_LISTING_POWIEKSZENIE_WYSOKOSC);
        //
        unset($Produkt);
  
    }
    
}

?>