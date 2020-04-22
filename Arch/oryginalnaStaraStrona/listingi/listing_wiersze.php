<?php
// czy jest zapytanie
if ($IloscProduktow > 0) { 

    $LiczniWierszy = 1;
    
    while ($info = $sql->fetch_assoc()) {
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        echo '<div class="Wiersz LiniaDolna">';
      
            //
            $Produkt = new Produkt( $info['products_id'] );
            $Produkt->ProduktProducent();
            
            // elementy kupowania
            $Produkt->ProduktKupowanie();
            //             
            
            // dostepnosc produktu
            if ( LISTING_DOSTEPNOSC == 'tak' ) {
                 $Produkt->ProduktDostepnosc();
            }
            
            echo '<div class="Foto">'.$Produkt->fotoGlowne['zdjecie_link_ikony'].'</div>';
            //
            echo '<div class="ProdCena" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+30) . 'px">';
            
            echo '<h3>' . $Produkt->info['link'] . '</h3>';

            echo $Produkt->info['cena'];
            
            echo '<ul class="ListaOpisowa">';
            
            // czy jest producent
            if ( !empty($Produkt->producent['nazwa'])) {
              echo '<li>{__TLUMACZ:PRODUCENT}: <b>' . $Produkt->producent['link'] . '</b></li>';
            }               
            
            // czy numer katalogowy
            if ( LISTING_NR_KATALOGOWY == 'tak' && !empty($Produkt->info['nr_katalogowy'])) {
                echo '<li>{__TLUMACZ:NUMER_KATALOGOWY}: <b>' . $Produkt->info['nr_katalogowy'] . '</b></li>';
            }       

            // czy jest dostepnosc produktu
            if ( LISTING_DOSTEPNOSC == 'tak' ) {
                //
                if ( !empty($Produkt->dostepnosc['dostepnosc']) ) {
                    //
                    // jezeli dostepnosc jest obrazkiem wyswietli tylko obrazek
                    if ( $Produkt->dostepnosc['obrazek'] == 'tak' ) {
                        //
                        echo '<li>' . $Produkt->dostepnosc['dostepnosc'] . '</li>';
                      } else {
                        echo '<li>{__TLUMACZ:DOSTEPNOSC}: <b> ' . $Produkt->dostepnosc['dostepnosc'] . '</b></li>';
                        //
                    }
                }            
                //
            }
            
            // czy jest stan magazynowy produktu
            if ( LISTING_STAN_MAGAZYNOWY == 'tak' && MAGAZYN_SPRAWDZ_STANY == 'tak' ) {
                echo '<li>{__TLUMACZ:STAN_MAGAZYNOWY}: <b> ' . $Produkt->zakupy['ilosc_magazyn_jm'] . '</b></li>';
            }            
            
            echo '</ul>';
            
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
                
                // jezeli jest aktywne dodawanie do schowka
                if (PRODUKT_SCHOWEK_STATUS == 'tak') {
                    //
                    echo '<span onclick="DoSchowka(' . $Produkt->info['id'] . ')" class="Schowek">{__TLUMACZ:LISTING_DODAJ_DO_SCHOWKA}</span>';
                    //
                }
            
            echo '</div><div class="cl"></div>';   

            // data dostepnosci
            if ( !empty($Produkt->info['data_dostepnosci']) ) {
                echo '<div class="DataDostepnosci">{__TLUMACZ:DOSTEPNY_OD_DNIA} <b>' . $Produkt->info['data_dostepnosci'] . '</b></div>';
            }        
            
            echo '</div>';
            //
            unset($Produkt);
            //
            
        echo '</div>';
        //
        // ************************ wyglad produktu - koniec **************************
        //
        
        $LiczniWierszy++;
        
    }

    unset($info, $LiczniWierszy);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_PRODUKTOW}</div>';
  
}

$GLOBALS['db']->close_query($sql); 

unset($IloscProduktow, $zapytanie);  
?>