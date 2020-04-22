<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    $LiczniKolumn = 1;
    
    // okresla szerokosc pojedynczego produktu w listingu - w %
    $SzerokoscPola = (int)(100 / $IloscProduktow);
    //    
    
    echo '<div class="ProduktyPopUp LiniaGorna">';
    
        // jezeli akcesoria dodatkowe
        if ( $AkcesoriaDodatkowe == true ) {
            //
            echo '<strong>' . $GLOBALS['tlumacz']['LISTING_PRODUKTY_POPUP_AKCESORIA'] . '</strong>';
            //
          } else {
            //
            echo '<strong>' . $GLOBALS['tlumacz']['LISTING_PRODUKTY_POPUP_PODOBNE'] . '</strong>';
            //          
        }
    
        echo '<div class="LiniaDolna TabelaTbl">';

        while ($info = $sql->fetch_assoc()) {
            //
            // ************************ wyglad produktu - poczatek **************************
            //
            echo '<div class="ProduktPopUp" style="width:' . $SzerokoscPola . '%">';          

                $ProduktPopUp = new Produkt( $info['products_id'], 60, 60, '', false );
                // elementy kupowania
                $ProduktPopUp->ProduktKupowanie();                
                //      
                echo '<div class="Foto">'.$ProduktPopUp->fotoGlowne['zdjecie_link'].'</div>';
                //
                echo '<div class="ProdCena">';
                
                    echo '<h3>' . $ProduktPopUp->info['link'] . '</h3>' . $ProduktPopUp->info['cena'];
                
                    echo '<div class="Zakup">';
                    
                        // jezeli jest aktywne kupowanie produktow
                        if ($ProduktPopUp->zakupy['mozliwe_kupowanie'] == 'tak') {
                            //
                            echo '<span class="IloscProduktu">' . $ProduktPopUp->zakupy['input_ilosci'] . '<em>' . $ProduktPopUp->zakupy['jednostka_miary'] . '</em> </span>' . $ProduktPopUp->zakupy['przycisk_kup'];
                            //
                        }            
                        //
                        
                    echo '</div>';  
                
                echo '</div>';
                
                unset($ProduktPopUp);
                    
            echo '</div>';
            //
            // ************************ wyglad produktu - koniec **************************
            //
        }
        
        echo '</div>'; 
    
    echo '</div>'; 

    unset($info, $SzerokoscPola);
      
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>