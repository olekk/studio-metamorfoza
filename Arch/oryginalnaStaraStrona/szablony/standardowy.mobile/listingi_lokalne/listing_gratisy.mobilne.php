<?php
$LiczniWierszy = 1;

foreach ( $Gratisy As $ProduktGratisowy ) {

    echo '<div class="Wiersz LiniaDolna">';
  
        //
        $Produkt = new Produkt( $ProduktGratisowy['id_gratisu'], (SZEROKOSC_OBRAZEK_MALY / 2), (WYSOKOSC_OBRAZEK_MALY / 2) );

        // elementy kupowania
        $Produkt->ProduktKupowanie();
        //            

        // okreslanie ceny gratisu
        $CenaGratisu = $GLOBALS['waluty']->FormatujCene($ProduktGratisowy['cena_gratisu'], 0, 0, $Produkt->info['id_waluty'], false);
    
        $CenaBruttoProduktu = $GLOBALS['waluty']->WyswietlFormatCeny($Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false);
        $CenaBruttoGratisu = $GLOBALS['waluty']->WyswietlFormatCeny($CenaGratisu['brutto'], $_SESSION['domyslnaWaluta']['id'], true, false);
                
        // pola ukryte - obowiazkowe
        echo '<input type="hidden" id="produkt_cena_' . $Produkt->zakupy['id_unikat'] . $Produkt->info['id'] . '" value="' . $CenaGratisu['brutto'] . '" />';
        echo $Produkt->zakupy['input_ilosci_gratis'];             
                
        echo '<div class="ProdCena">';
        
            echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';     
        
            echo '<div class="NazwaKoszyk">';
            
                echo '<h3>' . $Produkt->info['link'] . '</h3>';
                
                $InfoCena = str_replace('{ILOSC_GRATISOW}', $Produkt->zakupy['ilosc_gratisu'] . ' ' . $Produkt->zakupy['jednostka_miary'], $GLOBALS['tlumacz']['GRATIS_CENA']);
                $InfoCena = str_replace('{CENA_BRUTTO_PRODUKTU}', '<strong>' . $CenaBruttoProduktu . '</strong>', $InfoCena);
                $InfoCena = str_replace('{CENA_BRUTTO_GRATISU}', '<strong>' . $CenaBruttoGratisu . '</strong>', $InfoCena);
            
                echo $InfoCena .' <br /><br />';                

                // jezeli jest aktywne kupowanie produktow
                if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                    //
                    echo $Produkt->zakupy['przycisk_kup_gratis'];
                    //
                }

   
            echo '</div>';

        
        echo '</div>';
        //
        unset($InfoCena, $Produkt, $CenaGratisu, $CenaBruttoProduktu, $CenaBruttoGratisu);
        //
        
    echo '</div>';
    
    $LiczniWierszy++;
    
}

unset($LiczniWierszy);
      
?>