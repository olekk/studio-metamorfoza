<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{MODUL_NOWOSCI_JEDNO_ZDJECIE_ILOSC_PRODUKTOW;Ilość wyświetlanych produktów;5;5,6,7,8,9,10,15,20}}
//

// zmienne bez definicji
$LimitZapytania = 5;

if ( defined('MODUL_NOWOSCI_JEDNO_ZDJECIE_ILOSC_PRODUKTOW') ) {
   $LimitZapytania = (int)MODUL_NOWOSCI_JEDNO_ZDJECIE_ILOSC_PRODUKTOW;
}

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, 'nowosci');

if (count($WybraneProdukty) > 0) {
      
    $fotoProduktow = '';
    $linkiProduktow = '';

    for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
        //
        $Produkt = new Produkt( $WybraneProdukty[$v] );
        //        
        $fotoProduktow .= '<li id="fnj_' . $Produkt->info['id'] . '">' . $Produkt->fotoGlowne['zdjecie_ikony'] . '</li>';
        $linkiProduktow .= '<h3 id="nj_' . $Produkt->info['id'] . '">' . $Produkt->info['link'] . '<span class="Ceny">' . $Produkt->info['cena'] . '</span></h3>';
        //
        unset($Produkt);
        //
    }
    
    echo '<article class="ProduktZdjecieLista" id="ListaNowosci">';
    
        echo '<div class="FotoJedno" style="width:' . (SZEROKOSC_OBRAZEK_MALY+30) . 'px"><ul>';
        
        echo $fotoProduktow;
        
        echo '</ul></div>';
    
        echo '<div class="NazwyProduktow">';
        
        echo $linkiProduktow;
        
        echo '</div>';
    
    echo '</article>';
    
    echo Wyglad::PrzegladarkaJavaScript( "$.ProduktyListaZdjecie( 'ListaNowosci' );" );

    unset($fotoProduktow, $linkiProduktow);
      
}

unset($WybraneProdukty, $LimitZapytania);
?>