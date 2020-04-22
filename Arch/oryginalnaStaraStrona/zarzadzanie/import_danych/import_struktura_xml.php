<?php
// stworzenie tablicy z definicjami
$TablicaDane = array();

if (isset($dane_produktow->Produkt[(int)$_POST['limit']])) {

    foreach ($dane_produktow->Produkt[(int)$_POST['limit']]->children() as $child) {

        //
        $ByloDodanie = false;
        //

        // jezeli sa zdjecia dodatkowe
        if ($child->getName() == 'Zdjecia_dodatkowe') {
            $ileDodZdjec = count($dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->children());
            //
            for ($r = 0; $r < $ileDodZdjec; $r++) {
                //
                if ( isset($dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_link) ) {
                     //
                     $TablicaDane['Zdjecie_dodatkowe_' . ($r + 1)] = $dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_link;
                     //
                     if ( $dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_opis ) {                
                          $TablicaDane['Zdjecie_dodatkowe_opis_' . ($r + 1)] = $dane_produktow->Produkt[(int)$_POST['limit']]->Zdjecia_dodatkowe->Zdjecie[$r]->Zdjecie_opis;
                     }
                     //
                }
            }
            $ByloDodanie = true;
        }
        
        // jezeli sa kategorie
        if ($child->getName() == 'Kategoria') {
            //
            if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Kategoria)) {
                //
                $ZawartoscKat = explode('/',$dane_produktow->Produkt[(int)$_POST['limit']]->Kategoria);
                for ($p = 0, $c = count($ZawartoscKat); $p < $c; $p++) {
                    //
                    $TablicaDane['Kategoria_'.($p + 1).'_nazwa'] = $ZawartoscKat[$p];                
                    //
                }
                //
                unset($ZawartoscKat);
                //
            }
            //

            $ByloDodanie = true;  
            //
        } 

        // jezeli sa dodatkowe zakladki
        if ($child->getName() == 'Dodatkowe_zakladki') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_zakladki->Dodatkowa_zakladka[$s])) {
                    //
                    $ZawartoscZakladki = $dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_zakladki->Dodatkowa_zakladka[$s];
                    foreach ($ZawartoscZakladki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Dodatkowa_zakladka_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Dodatkowa_zakladka_'.($s + 1).'_opis'] = $ZakTr;
                        }                
                    }
                    unset($ZawartoscZakladki);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }  

        // jezeli sa dodatkowe linki
        if ($child->getName() == 'Linki') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Linki->Link[$s])) {
                    //
                    $ZawartoscLinkow = $dane_produktow->Produkt[(int)$_POST['limit']]->Linki->Link[$s];
                    foreach ($ZawartoscLinkow->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Link_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Link_'.($s + 1).'_opis'] = $ZakTr;
                        }                        
                        if ($ZakTr->getName() == 'Url') {
                            $TablicaDane['Link_'.($s + 1).'_url'] = $ZakTr;
                        }                
                    }
                    unset($ZawartoscLinkow);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }    

        // jezeli sa pliki
        if ($child->getName() == 'Pliki') {
            //
            for ($s = 0; $s < 5; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Pliki->Plik[$s])) {
                    //
                    $ZawartoscPliki = $dane_produktow->Produkt[(int)$_POST['limit']]->Pliki->Plik[$s];
                    foreach ($ZawartoscPliki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Plik_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Plik_'.($s + 1).'_opis'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Plik_'.($s + 1).'_plik'] = $ZakTr;
                        }   
                        if ($ZakTr->getName() == 'Logowanie') {
                            $TablicaDane['Plik_'.($s + 1).'_logowanie'] = $ZakTr;
                        }                     
                    }
                    unset($ZawartoscPliki);
                    //
                }
                //
            }
            $ByloDodanie = true;  
            //
        }    
        
        // jezeli sa pliki elektroniczne
        if ($child->getName() == 'Pliki_elektroniczne') {
            //
            for ($s = 0; $s < 101; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_elektroniczne->Plik_elektroniczny[$s])) {
                    //
                    $ZawartoscPliki = $dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_elektroniczne->Plik_elektroniczny[$s];
                    foreach ($ZawartoscPliki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Plik_elektroniczny_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Plik_elektroniczny_'.($s + 1).'_plik'] = $ZakTr;
                        }                       
                    }
                    unset($ZawartoscPliki);
                    //
                }
                //
            }
            $ByloDodanie = true;  
            //
        }          
        
        // jezeli sa filmy youtube
        if ($child->getName() == 'Filmy_youtube') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Filmy_youtube->Youtube[$s])) {
                    //
                    $ZawartoscFilmow = $dane_produktow->Produkt[(int)$_POST['limit']]->Filmy_youtube->Youtube[$s];
                    foreach ($ZawartoscFilmow->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Youtube_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Url') {
                            $TablicaDane['Youtube_'.($s + 1).'_url'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Youtube_'.($s + 1).'_opis'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Szerokosc') {
                            $TablicaDane['Youtube_'.($s + 1).'_szerokosc'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wysokosc') {
                            $TablicaDane['Youtube_'.($s + 1).'_wysokosc'] = $ZakTr;
                        }                        
                    }
                    unset($ZawartoscFilmow);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }        
        
        // jezeli sa filmy flv
        if ($child->getName() == 'Filmy') {
            //
            for ($s = 0; $s < 4; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Filmy->Film[$s])) {
                    //
                    $ZawartoscFilmow = $dane_produktow->Produkt[(int)$_POST['limit']]->Filmy->Film[$s];
                    foreach ($ZawartoscFilmow->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Film_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Film_'.($s + 1).'_plik'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Opis') {
                            $TablicaDane['Film_'.($s + 1).'_opis'] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Pelen_ekran') {
                            $TablicaDane['Film_'.($s + 1).'_ekran'] = $ZakTr;
                        }                        
                        if ($ZakTr->getName() == 'Szerokosc') {
                            $TablicaDane['Film_'.($s + 1).'_szerokosc'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wysokosc') {
                            $TablicaDane['Film_'.($s + 1).'_wysokosc'] = $ZakTr;
                        }                        
                    }
                    unset($ZawartoscFilmow);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }            
        
        // jezeli sa mp3
        if ($child->getName() == 'Pliki_mp3') {
            //
            for ($s = 0; $s < 16; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_mp3->Plik_mp3[$s])) {
                    //
                    $ZawartoscPliki = $dane_produktow->Produkt[(int)$_POST['limit']]->Pliki_mp3->Plik_mp3[$s];
                    foreach ($ZawartoscPliki->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Nazwa_mp3_'.($s + 1)] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Plik') {
                            $TablicaDane['Plik_mp3_'.($s + 1)] = $ZakTr;
                        }                     
                    }
                    unset($ZawartoscPliki);
                    //
                }
                //
            }
            $ByloDodanie = true;  
            //
        }          
        
        // jezeli sa dodatkowe pola
        if ($child->getName() == 'Dodatkowe_pola') {
            //
            for ($s = 0; $s < 100; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_pola->Dodatkowe_pole[$s])) {
                    //
                    $ZawartoscPol = $dane_produktow->Produkt[(int)$_POST['limit']]->Dodatkowe_pola->Dodatkowe_pole[$s];
                    foreach ($ZawartoscPol->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_nazwa'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wartosc') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_wartosc'] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Link') {
                            $TablicaDane['Dodatkowe_pole_'.($s + 1).'_link'] = $ZakTr;
                        }                         
                    }
                    unset($ZawartoscPol);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }

        // jezeli sa cechy
        if ($child->getName() == 'Cechy') {
            //
            for ($s = 0; $s < 100; $s++) {
                //
                if (isset($dane_produktow->Produkt[(int)$_POST['limit']]->Cechy->Cecha[$s])) {
                    //
                    $ZawartoscCecha = $dane_produktow->Produkt[(int)$_POST['limit']]->Cechy->Cecha[$s];
                    foreach ($ZawartoscCecha->children() as $ZakTr) {
                        if ($ZakTr->getName() == 'Nazwa') {
                            $TablicaDane['Cecha_nazwa_'.($s + 1)] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Wartosc') {
                            $TablicaDane['Cecha_wartosc_'.($s + 1)] = $ZakTr;
                        } 
                        if ($ZakTr->getName() == 'Cena') {
                            $TablicaDane['Cecha_cena_'.($s + 1)] = $ZakTr;
                        }
                        if ($ZakTr->getName() == 'Waga') {
                            $TablicaDane['Cecha_waga_'.($s + 1)] = $ZakTr;
                        }                     
                    }
                    unset($ZawartoscCecha);
                    //
                }
                //
            }
            $ByloDodanie = true;        
        }     

        // jezeli jest to standardowe pole 
        if ($ByloDodanie == false) {
        
            if ( trim($child->getName()) == 'Nazwa_produktu' ) {
                 $TablicaDane['Nazwa_produktu_struktura'] = trim($child); 
            }
        
            $TablicaDane[trim($child->getName())] = trim($child);
        }

    }

}
/*
echo count($TablicaDane) . '<br>';

for ($q = 0; $q < count($TablicaDef); $q++) {
    echo $TablicaDef[$q] . ' - ' . $TablicaDane[$q] .  "<br />";
}
*/

?>