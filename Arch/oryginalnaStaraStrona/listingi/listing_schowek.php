<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    foreach ( $GLOBALS['schowekKlienta']->IloscProduktowTablicaId AS $IdSchowka ) {
    
        $Produkt = new Produkt( $IdSchowka );
           
        if ( $Produkt->CzyJestProdukt ) {
            //
            // ************************ wyglad produktu - poczatek **************************
            //
            echo '<div class="SchowekPrd LiniaDolna">';
                //
                // elementy kupowania 
                $Produkt->ProduktKupowanie();            
                //             
                echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
                //
                echo '<div class="UsunSchowek"><span onclick="UsunZeSchowka(' . $Produkt->info['id'] . ')" class="Schowek">{__TLUMACZ:SCHOWEK_USUN_ZE_SCHOWKA}</span></div>';
                //
                echo '<div class="ProdCena LiniaPrawa" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+30) . 'px">';
                
                echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];
                
                echo '<div class="Opis LiniaOpisu">' . $Produkt->info['opis_krotki'] . '</div>'; 
                
                echo '<div class="Zakup">';
                
                    // jezeli jest aktywne kupowanie produktow
                    if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                        //
                        echo $Produkt->zakupy['input_ilosci'] . '<em>' . $Produkt->zakupy['jednostka_miary'] . '</em> ' . $Produkt->zakupy['przycisk_kup'];
                        //
                        echo '<span class="cls"></span>';
                        //
                    }
                    
                    // jezeli jest wlaczona porownywarka produktow
                    if (LISTING_POROWNYWARKA_PRODUKTOW == 'tak') {
                        //
                        // jezeli produkt byl dodany do porownania
                        if (in_array($Produkt->info['id'], $_SESSION['produktyPorownania'])) {
                            echo '<span onclick="Porownaj(' . $Produkt->info['id'] . ',\'wy\')" id="id' . $Produkt->info['id'] . '" class="PorownajWlaczone">{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}</span>';
                          } else {
                            echo '<span onclick="Porownaj(' . $Produkt->info['id'] . ',\'wl\')" id="id' . $Produkt->info['id'] . '" class="Porownaj">{__TLUMACZ:LISTING_DODAJ_DO_POROWNANIA}</span>';
                        }
                        //
                    }
                    
                echo '</div><div class="cl"></div>'; 
                
                echo '</div>';

            echo '</div>';
            //
            // ************************ wyglad produktu - koniec **************************
            //
        } else {
        
            $GLOBALS['schowekKlienta']->UsunZeSchowka( $IdSchowka );
        
        }
        //
        unset($Produkt);
        //        
    }

    unset($info);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUKTOW}</div>';
  
} 
?>