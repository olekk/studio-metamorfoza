<?php

$WybraneProdukty = Produkty::ProduktyModulowe($LimitZapytania, $WyswietlProdukty);

if (count($WybraneProdukty) > 0) {
      
      $LiczniKolumn = 1;
      
      for ($v = 0, $cs = count($WybraneProdukty); $v < $cs; $v++) {
          //
          // sekcja wiersza
          // jezeli nowa kolumna to tworzy nowa sekcje
          if ( $LiczniKolumn == 1 ) {
               //
               echo '<div class="SekcjaModulu LiniaDolnaSekcji">';
               //
          }
          //
          // ************************ wyglad produktu - poczatek **************************
          //
          echo '<div class="PozycjaModulu">';
          
              echo '<div class="PozycjaMargines Produkt">';

                  $Produkt = new Produkt( $WybraneProdukty[$v] );
                  //              
                  echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link'].'</div>';
                  //
                  echo '<h3>' . $Produkt->info['link'] . '</h3>' . $Produkt->info['cena'];

                    // elementy kupowania
                    $Produkt->ProduktKupowanie();                  
                
                    echo '<div class="Zakup">';
                    
                        // jezeli jest aktywne kupowanie produktow
                        if ($Produkt->zakupy['mozliwe_kupowanie'] == 'tak' || $Produkt->zakupy['pokaz_koszyk'] == 'tak') {
                            //
                            echo $Produkt->zakupy['input_ilosci'] . $Produkt->zakupy['przycisk_kup'];
                            //
                        }            
                        
                    echo '</div>'; 

                    //
                    unset($Produkt);
                
                echo '</div>';

            echo '</div>';
            //
            // ************************ wyglad produktu - koniec **************************
            //
            // zamykanie sekcji
            if ($LiczniKolumn == 2) {
                //
                echo '<div class="cl"></div></div>';      
                $LiczniKolumn = 0;
                //
            }
            
            $LiczniKolumn++;

      }
      
      // zamykanie sekcji
      if ($LiczniKolumn == 2) {
          //
          echo '<div class="cl"></div></div>';      
          $LiczniKolumn = 0;
          //
      }
      
      $LiczniKolumn++;

      unset($LiczniKolumn);
      
}

unset($WybraneProdukty, $LimitZapytania);
?>