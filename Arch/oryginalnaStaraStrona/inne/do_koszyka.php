<?php
chdir('../');            

if (isset($_POST['id'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        $id = $_POST['id'];
        //
        $Produkt = new Produkt( (int)$id );
        $Produkt->ProduktKupowanie();   
        $Produkt->ProduktDodatkowePolaTekstowe();
        //
        $IloscDoDodaniaDoKoszyka = (float)$_POST['ilosc'];
        //
        
        $rodzaj_ceny = 'baza';
        $cena = '';
        if ( isset($_POST['akcja']) && !empty($_POST['akcja']) && isset($_POST['cena']) ) {
            $rodzaj_ceny = $filtr->process($_POST['akcja']);
            $cena = $filtr->process($_POST['cena']);
        }
        
        // ciag cech
        if ( isset($_POST['cechy']) ) {
            $cechy = Funkcje::CechyProduktuPoId( $filtr->process($_POST['cechy']), true );        
          } else {
            $cechy = array();
        }
        
        $id = (int)$_POST['id'] . $filtr->process($_POST['cechy']);
        
        // jezeli jest komentarz
        $komentarz = $filtr->process($_POST['komentarz']);
        
        // jezeli sa dodatkowe pola tekstowe
        $polaTxt = $filtr->process($_POST['txt']);     

        // miejsce dodania produktu - listing czy karta produktu
        $miejsce = 'karta';
        if ( isset($_POST['miejsce']) ) {
             if ( (int)$_POST['miejsce'] == 1 ) {
                   $miejsce = 'lista';
             }
        }
        
        // czy dodac do koszyka
        $DodajDoKoszyka = false;
        
        // czy akcesoria dodatkowe
        $InfoAkcesoria = false;
        
        // jezeli produkt nie ma cech lub do koszyka sa przekazane wszystkie cechy produktu 
        if ( (int)$Produkt->cechyIlosc == 0 || ( count($cechy) == (int)$Produkt->cechyIlosc && count($cechy) > 0 ) ) {
             $DodajDoKoszyka = true;
        }
        
        // jezeli jest dodawany do koszyka z listingu a ma dodatkowe pola tekstowe
        if ( $miejsce == 'lista' && count($Produkt->dodatkowePolaTekstowe) > 0 ) {
             $DodajDoKoszyka = false;
        }
        
        // sprawdzi czy produkt nie jest jako tylko akcesoria dodatkowe i czy jest w koszyku produkt z ktorym mozna go kupic
        if ( $Produkt->info['status_akcesoria'] == 'tak' ) {
             //
             // ustawia ze ma sie wyswietlic info ze trzeba kupic z innym produktem
             $InfoAkcesoria = true;
             $DodajDoKoszyka = false;
             //
             // tablica do id produktow ktore maja akcesoria dodatkowe o danym id produktu
             $TablicaId = array();
             //
             $zapytanie = "select distinct pacc_products_id_master from products_accesories where pacc_products_id_slave = '" . $Produkt->info['id'] . "'";
             $sql = $GLOBALS['db']->open_query($zapytanie);    
             //
             while ($info = $sql->fetch_assoc()) {
                 $TablicaId[] = $info['pacc_products_id_master'];
             }
             //
             $GLOBALS['db']->close_query($sql);
             unset($info, $zapytanie);  
             //
             // sprawdzi czy w koszyku jest produkt z ktorym mozna kupic ten produkt
             foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
                //
                $IdProduktuKoszyka = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] );
                //
                if ( in_array($IdProduktuKoszyka, $TablicaId) ) {
                     $InfoAkcesoria = false;
                     $DodajDoKoszyka = true;
                     break;
                }
                //
                unset($IdProduktuKoszyka);
                //
             }
             //
             unset($TablicaId);
             //
        }
        
        if ( $DodajDoKoszyka == true ) {
            //
            echo '<div id="PopUpDodaj">';
            //       
            echo $GLOBALS['tlumacz']['INFO_DO_KOSZYKA_DODANY_PRODUKT'] . ' <br />';
            
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            
            echo $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . ': <b>' . $filtr->process($_POST['ilosc']) . '</b> ' . $Produkt->info['jednostka_miary'];

            echo '</div>';
            
            if ( PRODUKT_OKNO_PRODUKTY_POPUP == 'tak' ) {
            
                // ukryty div zeby nie animowalo koszyka
                echo '<div id="BrakAnimacjiKoszyka" style="display:none"></div>';
            
                if ( $_SESSION['mobile'] != 'tak' ) {
                
                    $AkcesoriaDodatkowe = false;
                    $IloscProduktow = 0;                
                
                    if ( PRODUKT_OKNO_PRODUKTY_POPUP_TYP != 'tylko podobne' ) {
                
                        // lista produktow - akcesoria dodatkowe
                        $zapytanie = Produkty::SqlProduktyAkcesoriaDodatkowe( $Produkt->info['id'], 3 );
                        $sql = $GLOBALS['db']->open_query($zapytanie);    
                        //
                        $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
                        //
                        $AkcesoriaDodatkowe = true;
                        
                    }
                    
                    // jezeli nie ma akces dodatkowych poszuka podobnych
                    if ( $IloscProduktow == 0 && PRODUKT_OKNO_PRODUKTY_POPUP_TYP != 'tylko akcesoria' ) {
                        //
                        $AkcesoriaDodatkowe = false;
                        //
                        $zapytanie = Produkty::SqlProduktyPodobne( $Produkt->info['id'], 3 );
                        $sql = $GLOBALS['db']->open_query($zapytanie);    
                        //
                        $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);
                        //                
                    }

                    if ( file_exists('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_produkty_popup.php') ) {
                        //
                        require('szablony/'.DOMYSLNY_SZABLON.'/listingi_lokalne/listing_produkty_popup.php');
                        //
                      } else {
                        //
                        require('listingi/listing_produkty_popup.php');
                        //
                    }
                
                }
                
            }

            echo '<div id="PopUpPrzyciski">';
            
                // przeladowanie strony i ewentualny powrot do zakladki - akcesoria dodatkowe
                echo '<script type="text/javascript">';
                echo 'function przeladuj() { ';

                if (isset($_POST['wroc']) && $_POST['wroc'] != '') {
                    echo "ustawCookie('zakladka','" . $filtr->process($_POST['wroc']) . "',1);";
                }
                
                echo 'stronaReload();';
                echo '}';
                echo '</script>';
            
                echo '<span onclick="przeladuj()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                echo '<a href="' . Seo::link_SEO('koszyk.php', '', 'inna') . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_KOSZYKA'] . '</a>';
                
            echo '</div>';
            //
            
            // jezeli jest cos dodania
            if ( $IloscDoDodaniaDoKoszyka > 0 ) {
                $GLOBALS['koszykKlienta']->DodajDoKoszyka( $id, $IloscDoDodaniaDoKoszyka, $komentarz, $polaTxt, $rodzaj_ceny, $cena ); 
            }
            
            unset($IloscDoDodaniaDoKoszyka);
            
        } else {
        
            if ( $InfoAkcesoria == true ) {
            
                echo '<div id="PopUpInfo" class="TylkoGratis">';

                echo str_replace('{PRODUKT}', $Produkt->info['nazwa'], $GLOBALS['tlumacz']['PRODUKT_INFO_AKCESORIA']) . ' <br />'; 
                
                echo '</div>';
                
                echo '<div id="PopUpPrzyciski">';
                    echo '<span onclick="stronaReload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
                echo '</div>';            
            
            } else {
        
                echo '<div id="PopUpDodaj" class="KonieczneCechy">';

                echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
                
                echo $GLOBALS['tlumacz']['PRODUKT_INFO_CECHY'] . ' <br />'; 
                
                echo '</div>';
                
                echo '<div id="PopUpPrzyciski">';
                    echo '<a href="' . $Produkt->info['adres_seo'] . '" class="przycisk">' . $GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_SZCZEGOLOW_PRODUKTU'] . '</a>';
                echo '</div>';
                
            }

        }

        //
        unset($Produkt, $cechy, $DodajDoKoszyka, $miejsce);
        //
 
    }
    
}
?>