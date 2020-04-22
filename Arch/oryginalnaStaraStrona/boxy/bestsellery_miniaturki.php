<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_BESTSELLERY_MINIATURKI_ILOSC_PRODUKTOW;Ilość wyświetlanych w boxie produktów;4;1,2,3,4,5,6,7,8,9,10}}
// {{BOX_BESTSELLERY_ROZMIAR_IMG;Rozmiar zdjęcia produktu w pixelach;50;50,70,90,100,120,150,170}}
//

if ( defined('BOX_BESTSELLERY_MINIATURKI_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)BOX_BESTSELLERY_MINIATURKI_ILOSC_PRODUKTOW;
 } else {
   $LimitZapytania = 4;
}

if ( defined('BOX_BESTSELLERY_MINIATURKI_ROZMIAR_IMG') ) {
   $RozmiarImg = (int)BOX_BESTSELLERY_MINIATURKI_ROZMIAR_IMG;
 } else {
   $RozmiarImg = 50;
}

$WybraneProdukty = Produkty::ProduktyModuloweBestsellery($LimitZapytania);

if (count($WybraneProdukty) > 0) { 
   
    //
    echo '<ul class="BoxImgMaly">';
    //
    for ( $i = 0, $c = count($WybraneProdukty); $i < $c; $i++ ) {
        //
        $Produkt = new Produkt( $WybraneProdukty[$i], $RozmiarImg, $RozmiarImg );
        //
        echo '<li>
          <p class="Img" style="width:' . $RozmiarImg . 'px">' . $Produkt->fotoGlowne['zdjecie_link'] . '</p>
          <h3 class="PrdDane">' . $Produkt->info['link'] . $Produkt->info['cena'] . '</h3>
        </li>';

        unset($Produkt);
        //
    }
    //
    echo '</ul>';
    //
    echo '<div class="Wszystkie"><a href="bestsellery.html">{__TLUMACZ:ZOBACZ_WSZYSTKIE}</a></div>';
    
}

unset($RozmiarImg, $WybraneProdukty, $LimitZapytania);
?>