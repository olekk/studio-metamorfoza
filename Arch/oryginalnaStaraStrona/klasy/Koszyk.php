<?php

class Koszyk {

    public function Koszyk() {
        //
        if (!isset($_SESSION['koszyk'])) {
            $_SESSION['koszyk'] = array();
        }    
        //
    }
    
    public function PrzywrocKoszykZalogowanego() {
        //    
        $wynikPrzeliczania = false;
        //
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
            //
            // przeniesie produktow z bazy do sesji
            $zapytanie = "SELECT DISTINCT * FROM customers_basket WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "' and price_type = 'baza'";
            $sql = $GLOBALS['db']->open_query($zapytanie);        
            //
            while ($info = $sql->fetch_assoc()) {
                //
                $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $info['products_id'] ) );
                //
                if ($Produkt->CzyJestProdukt == true) {
                
                    //
                    if ($this->CzyJestWKoszyku($info['products_id']) == false) {
                        //
                        $_SESSION['koszyk'][$info['products_id']] = array('id'          => $info['products_id'],
                                                                          'ilosc'       => $info['customers_basket_quantity'],
                                                                          'komentarz'   => $info['products_comments'],
                                                                          'pola_txt'    => $info['products_text_fields'],
                                                                          'rodzaj_ceny' => 'baza');
                        //
                     } else {
                        //
                        $_SESSION['koszyk'][$info['products_id']]['ilosc'] += $info['customers_basket_quantity'];
                        //
                    }
                    //   

                    $this->SprawdzIloscProduktuMagazyn( $info['products_id'] );
                    
                    $wynikPrzeliczania = true;

                } else {
                
                    // jezeli nie jest aktywny usunie produkt z bazy                
                    $GLOBALS['db']->delete_query('customers_basket' , "products_id = '" . $info['products_id'] . "'");
                    //
                    
                }
                //
                unset($Produkt);
                //
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info);                  
            //
            $this->PrzeliczKoszyk();            
            //            
        }
        //
        return $wynikPrzeliczania;
        //
    }    
    
    // sprawdzanie ilosci produktow przy przywracaniu koszyka klienta oraz przy potwierdzeniu zamowienia - czy ktos nie kupil produktu
    public function SprawdzIloscProduktuMagazyn($id, $koszyk = false) {
    
        $KoncowaIlosc = $_SESSION['koszyk'][$id]['ilosc'];
        $Akcja = '';
        
        $ProduktKontrola = new Produkt( (int)Funkcje::SamoIdProduktuBezCech($id) );
        
        // jezeli produkt jest wylaczony to usuwa go z koszyka
        if ( $ProduktKontrola->CzyJestProdukt == false) {
             //
             $this->UsunZKoszyka( $id );
             return true;
             //
        }
        
        // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
        $Przecinek = 2;
        // jezeli sa wartosci calkowite to dla pewnosci zrobi int
        if ( $ProduktKontrola->info['jednostka_miary_typ'] == '1' ) {
            $Przecinek = 0;
        }
        //         
    
        // czy produkt ma cechy
        $cechy = '';
        
        if ( strpos($id, "x") > -1 ) {
            // wyciaga same cechy z produktu
            $cechy = substr( $id, strpos($id, "x"), strlen($id) );
        }   

        if ( $cechy != '' && MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && CECHY_MAGAZYN == 'tak' ) {
            $ProduktKontrola->ProduktKupowanie( $cechy ); 
          } else {
            $ProduktKontrola->ProduktKupowanie();
        }   

        // jezeli ilosc w magazynie jest mniej niz w koszyku
        if ( $ProduktKontrola->zakupy['ilosc_magazyn'] < $KoncowaIlosc && MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' ) {
             //
             $KoncowaIlosc = $ProduktKontrola->zakupy['ilosc_magazyn'];
             $Akcja = 'przelicz';
             //
        }
        
        // jezeli ilosc jest mniejsza o minimalnej
        if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && MAGAZYN_SPRZEDAJ_MIMO_BRAKU == 'nie' && $KoncowaIlosc < $ProduktKontrola->zakupy['minimalna_ilosc'] ) {
        
            // jezeli jest mniej niz wymagana ilosc - usunie produkt z koszyka
            $Akcja = 'usun';
        
        }
        
        // jezeli ilosc jest wieksza niz maksymalna
        if ( $KoncowaIlosc > $ProduktKontrola->zakupy['maksymalna_ilosc'] && $ProduktKontrola->zakupy['maksymalna_ilosc'] > 0 ) {
             //
             $KoncowaIlosc = $ProduktKontrola->zakupy['maksymalna_ilosc'];
             $Akcja = 'przelicz';
             //
        }
        
        // jezeli jest przyrost ilosci
        if ( $ProduktKontrola->zakupy['przyrost_ilosci'] > 0 ) {
            //
            $Przyrost = $ProduktKontrola->zakupy['przyrost_ilosci'];
            //
            if ( (int)(round(($KoncowaIlosc / $Przyrost) * 100, 2) / 100) != (round(($KoncowaIlosc / $Przyrost) * 100, 2) / 100) ) {
                // 
                $KoncowaIlosc = (int)($KoncowaIlosc / $Przyrost) * $Przyrost;
                $Akcja = 'przelicz';
                //
            }
            //
        }  
        
        if ( $KoncowaIlosc <= 0 ) {
             //
             $this->UsunZKoszyka( $id );
             return true;
             //
        }
        
        if ( $Akcja == 'przelicz' ) {
            //
            $_SESSION['koszyk'][$id]['ilosc'] = $KoncowaIlosc;
            //
          } else if ( $Akcja == 'usun' ) {
            //
            $this->UsunZKoszyka( $id );
            //
        }
        
        $_SESSION['koszyk'][$id]['ilosc'] = number_format( $_SESSION['koszyk'][$id]['ilosc'], $Przecinek, '.', '' );
        
        if ( $Akcja != '' ) {
             return true;
        }
    
    }

    // czysci sesje koszyka przy wylogowaniu - tylko dla zalogowanych klientow
    public function WyczyscSesjeKoszykZalogowanego() {
        //  
        if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
            //
            $_SESSION['koszyk'] = array(); 
            //           
        }
        //
    }      
    
    // sprawdza czy produkt jest w koszyku sesji
    public function CzyJestWKoszyku( $id ) {
        //
        // czy juz nie ma produktu w koszyku
        $ProduktJest = false;
        foreach ( $_SESSION['koszyk'] As $TablicaWartosci ) {
            //
            if ( $id == $TablicaWartosci['id'] ) {
                $ProduktJest = true;
            }
            //
        }
        //
        return $ProduktJest;
    }

    public function DodajDoKoszyka( $id, $ilosc, $komentarz, $pola_txt, $rodzaj_ceny = 'baza', $cena = 0 ) {
        //
        if ( $rodzaj_ceny == 'baza' ) {
            //
            if ($this->CzyJestWKoszyku($id) == false || KOSZYK_SPOSOB_DODAWANIA == 'tak') {
                //
                $LosowaWartosc = '';
                //
                if ( KOSZYK_SPOSOB_DODAWANIA == 'tak' ) {
                     //
                     $LosowaWartosc = '-' . rand(1,99999);
                     //
                }
                //
                $_SESSION['koszyk'][$id . $LosowaWartosc] = array('id'          => $id . $LosowaWartosc,
                                                                  'ilosc'       => $ilosc,
                                                                  'komentarz'   => $komentarz,  
                                                                  'pola_txt'    => $pola_txt,
                                                                  'rodzaj_ceny' => $rodzaj_ceny);
                //
             } else {
                //
                $_SESSION['koszyk'][$id]['ilosc'] += $ilosc;
                $_SESSION['koszyk'][$id]['komentarz'] .= $komentarz;
                $_SESSION['koszyk'][$id]['pola_txt'] = $pola_txt;
                //
            }
            //
        }
        if ( $rodzaj_ceny == 'gratis' ) {
            //
            $_SESSION['koszyk'][$id . '-gratis'] = array('id'          => $id . '-gratis',
                                                         'ilosc'       => $ilosc,
                                                         'komentarz'   => '',   
                                                         'pola_txt'    => '',
                                                         'rodzaj_ceny' => 'gratis',
                                                         'cena_brutto' => $cena);
            //
        }
        $this->PrzeliczKoszyk();
        //
        unset($ProduktJest);
        //  
    } 
    
    public function AktualizujKomentarz( $id, $komentarz ) {
        //
        $_SESSION['koszyk'][$id]['komentarz'] = $komentarz;
        //
    }
    
    public function ZmienIloscKoszyka( $id, $ilosc ) {
        //
        $_SESSION['koszyk'][$id]['ilosc'] = $ilosc;
        //
        $this->PrzeliczKoszyk();
        //
    }
    
    public function UsunZKoszyka( $id ) {
        //
        unset($_SESSION['koszyk'][$id]);
        //
        // usuwa z bazy jezeli jest zalogowany klient
        if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) { 
            //
            $GLOBALS['db']->delete_query('customers_basket' , "products_id = '" . $id . "' and  customers_id = '".(int)$_SESSION['customer_id']."'");	   
            //
        }
        //
        $this->PrzeliczKoszyk();
        // 
    } 

    public function PrzeliczKoszyk() {
        //
        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        
            //
            $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id']) );
            
            if ( $Produkt->CzyJestProdukt ) {
            
                // definicja czy jest tylko akcesoria dodatkowe
                $TylkoAkcesoria = false;
            
                // sprawdzi czy produkt nie jest jako tylko akcesoria dodatkowe i czy jest w koszyku produkt z ktorym mozna go kupic
                if ( $Produkt->info['status_akcesoria'] == 'tak' ) {
                     //
                     $TylkoAkcesoria = true;
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
                     foreach ($_SESSION['koszyk'] AS $TablicaZawartosciAkcesoria) {
                        //
                        $IdProduktuKoszyka = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosciAkcesoria['id'] );
                        //
                        if ( in_array($IdProduktuKoszyka, $TablicaId) ) {
                             $TylkoAkcesoria = false;
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

                // jezeli produkt moze byc tylko jako acesoria dodatkowe a nie ma produktu dla ktorego jest przypisany
                if ( $TylkoAkcesoria == true ) {
                
                     $this->UsunZKoszyka( $TablicaZawartosci['id'] );
                     
                  } else {
            
                    // elementy kupowania
                    $Produkt->ProduktKupowanie();
                    //
                                
                    //
                    // jezeli do koszyka jest dodawany normalny produkt
                    if ( $TablicaZawartosci['rodzaj_ceny'] == 'baza' ) {

                        $WartoscCechBrutto = 0;
                        $WartoscCechNetto = 0;
                        $WagaCechy = 0;
                        $Znizka = 1;
                        
                        // jezeli produkt ma cechy oraz cechy wplywaja na wartosc produktu to musi ustalic ceny cech
                        if ( strpos($TablicaZawartosci['id'], "x") > -1 && $Produkt->info['typ_cech'] == 'cechy' ) {
                            //
                            $DodatkoweParametryCechy = $Produkt->ProduktWartoscCechy( $TablicaZawartosci['id'] );
                            //
                            $WartoscCechBrutto = $DodatkoweParametryCechy['brutto'];
                            $WartoscCechNetto = $DodatkoweParametryCechy['netto'];
                            $WagaCechy = $DodatkoweParametryCechy['waga'];
                            //
                            unset($DodatkoweParametryCechy);
                            //
                            // lub jezeli sa stale ceny dla kombinacji cech
                        } else if ( $Produkt->info['typ_cech'] == 'ceny' ) {
                            //
                            $DodatkoweCenyCech = $Produkt->ProduktWartoscCechyCeny( $TablicaZawartosci['id'] );
                            //
                            $Produkt->info['cena_netto_bez_formatowania'] = $DodatkoweCenyCech['netto'];
                            $Produkt->info['cena_brutto_bez_formatowania'] = $DodatkoweCenyCech['brutto'];
                            $Produkt->info['vat_bez_formatowania'] = $DodatkoweCenyCech['brutto'] - $DodatkoweCenyCech['netto'];
                            $WagaCechy = $DodatkoweCenyCech['waga'];
                            //
                            unset($DodatkoweCenyCech);
                            //
                        }

                        //
                        $IloscSzt = $TablicaZawartosci['ilosc'];      
                        //
                        // znizki zalezne od ilosci
                        // warunki czy stosowac znizki od ilosci
                        $StosujZnizki = true;
                        
                        // jezeli nie ma sumowania rabatow
                        if ( ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'nie' && $Produkt->info['rabat_produktu'] != 0 ) {
                            $StosujZnizki = false;
                        }

                        // jezeli znizki zalezne od ilosci produktow w koszyku sa wlaczone dla promocji lub produkt nie jest w promocji
                        if ( ZNIZKI_OD_ILOSCI_PROMOCJE == 'nie' && $Produkt->ikonki['promocja'] == '1' ) {
                            $StosujZnizki = false;                
                        }
                        
                        if ( $StosujZnizki == true ) {
                                        
                            $IloscSztDoZnizek = 0;
                            
                            // jezeli produkty ze cechami maja byc traktowane jako osobne produkty
                            if ( ZNIZKI_OD_ILOSCI_PRODUKT_CECHY == 'nie' ) {
                            
                                // ---------------------------------------------------------------------------
                                // musi poszukac ile jest produktow z roznymi cechami i zsumowac produkty
                                foreach ($_SESSION['koszyk'] AS $TablicaDoZnizek) {
                                    //
                                    if (Funkcje::SamoIdProduktuBezCech($TablicaDoZnizek['id']) == Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id'])) {
                                        $IloscSztDoZnizek += $TablicaDoZnizek['ilosc'];
                                    }
                                    //
                                }
                                // ---------------------------------------------------------------------------
                                //
                                
                              } else {
                              
                                $IloscSztDoZnizek = $IloscSzt;
                                
                            }

                            if ($Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek ) > 0) {
                                $Znizka = 1 - ($Produkt->ProduktZnizkiZalezneOdIlosci( $IloscSztDoZnizek ) / 100);
                            }
                            //
                            unset($IloscSztDoZnizek);
                            //
                            
                        }

                        // jezeli nie ma znizki
                        if ($Znizka == 1) {
                            //
                            $CenaNetto = $Produkt->info['cena_netto_bez_formatowania'] + $WartoscCechNetto;
                            $CenaBrutto = $Produkt->info['cena_brutto_bez_formatowania'] + $WartoscCechBrutto;
                            $Vat = $Produkt->info['vat_bez_formatowania'];
                            //
                        } else {
                            //
                            $CenaBrutto = round( ($Produkt->info['cena_brutto_bez_formatowania']  + $WartoscCechBrutto) * $Znizka, CENY_MIEJSCA_PO_PRZECINKU );
                            $CenaNetto = round( $CenaBrutto / (1 + ($Produkt->info['stawka_vat'] / 100)), CENY_MIEJSCA_PO_PRZECINKU );
                            $Vat = $CenaBrutto - $CenaNetto;                
                            //
                        }
                        //
                    }
                    
                    // jezeli do koszyka jest dodawany gratis
                    if ( $TablicaZawartosci['rodzaj_ceny'] == 'gratis' ) {
                        //
                        $WagaCechy = 0;
                        $IloscSzt = $TablicaZawartosci['ilosc'];
                        //
                        if ( $TablicaZawartosci['cena_brutto'] > 0 ) {
                              //
                              $CenaBrutto = $TablicaZawartosci['cena_brutto'];
                              $CenaNetto = round( $CenaBrutto / (1 + ($Produkt->info['stawka_vat'] / 100)), CENY_MIEJSCA_PO_PRZECINKU );
                              $Vat = $CenaBrutto - $CenaNetto;
                              //
                          } else { 
                              //
                              $CenaBrutto = 0;
                              $CenaNetto = 0;
                              $Vat = 0;
                              //
                        }
                        //
                    }
                    
                    // usuwa wpis z koszyka sesji
                    unset($WartoscCechBrutto, $WartoscCechNetto);
                    
                    $NrKatalogowy = $Produkt->ProduktCechyNrKatalogowy( substr( $TablicaZawartosci['id'], strpos($TablicaZawartosci['id'], "x") + 1, strlen($TablicaZawartosci['id']) ) );
                    
                    //
                    // dodaje na nowo do koszyka sesji przeliczone wartosci
                    $_SESSION['koszyk'][$TablicaZawartosci['id']] = array('id'            => $TablicaZawartosci['id'],
                                                                          'ilosc'         => $IloscSzt,
                                                                          'cena_netto'    => $CenaNetto,
                                                                          'cena_brutto'   => $CenaBrutto,
                                                                          'vat'           => $Vat,
                                                                          'waga'          => $Produkt->info['waga'] + $WagaCechy,
                                                                          'promocja'      => (($Produkt->ikonki['promocja'] == 1) ? 'tak' : 'nie'),
                                                                          'gabaryt'       => $Produkt->info['gabaryt'],
                                                                          'wysylki'       => $Produkt->info['dostepne_wysylki'],
                                                                          'koszt_wysylki' => $Produkt->info['koszt_wysylki'],
                                                                          'nr_katalogowy' => $NrKatalogowy,
                                                                          'komentarz'     => $TablicaZawartosci['komentarz'],
                                                                          'pola_txt'      => $TablicaZawartosci['pola_txt'],
                                                                          'rodzaj_ceny'   => $TablicaZawartosci['rodzaj_ceny'],
                                                                          'id_kategorii'  => $Produkt->info['id_kategorii'],
                    );

                    // jezeli klient jest zalogowany to aktualizuje baze
                    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
                        //
                        if ($TablicaZawartosci['rodzaj_ceny'] == 'baza') {
                            //
                            // musi sprawdzic czy produkt jest juz w bazie
                            $zapytanie = "SELECT DISTINCT * FROM customers_basket WHERE products_id = '" . $TablicaZawartosci['id'] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'";
                            $sql = $GLOBALS['db']->open_query($zapytanie);   
                            //
                            if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {
                                // aktualizuje produkt
                                $pola = array(
                                        array('products_id',$TablicaZawartosci['id']),
                                        array('customers_id',(int)$_SESSION['customer_id']),
                                        array('customers_basket_quantity',$IloscSzt),
                                        array('products_price',$CenaNetto),
                                        array('products_price_tax',$CenaBrutto),
                                        array('products_tax',$Vat),
                                        array('products_weight',$Produkt->info['waga']),
                                        array('products_comments',$TablicaZawartosci['komentarz']),
                                        array('products_text_fields',$TablicaZawartosci['pola_txt']),
                                        array('products_model',$NrKatalogowy),
                                        array('price_type',$TablicaZawartosci['rodzaj_ceny']));
                                        
                                $GLOBALS['db']->update_query('customers_basket' , $pola, "products_id = '" . $TablicaZawartosci['id'] ."' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");	
                                unset($pola);                       
                                //                    
                            } else {
                                // jezeli go nie ma musi go dodac
                                $pola = array(
                                        array('products_id',$TablicaZawartosci['id']),
                                        array('customers_id',(int)$_SESSION['customer_id']),
                                        array('customers_basket_quantity',$IloscSzt),
                                        array('products_price',$CenaNetto),
                                        array('products_price_tax',$CenaBrutto),
                                        array('products_tax',$Vat),
                                        array('products_weight',$Produkt->info['waga']),
                                        array('products_comments',$TablicaZawartosci['komentarz']),
                                        array('products_text_fields',$TablicaZawartosci['pola_txt']),
                                        array('products_model',$NrKatalogowy),
                                        array('customers_basket_date_added','now()'),
                                        array('price_type',$TablicaZawartosci['rodzaj_ceny']));

                                $GLOBALS['db']->insert_query('customers_basket' , $pola);	
                                unset($pola);
                                //
                            }
                            //            
                        }
                        //
                    }
                    
                }
                
                unset($TylkoAkcesoria, $NrKatalogowy, $CenaNetto, $CenaBrutto, $Vat, $WagaCechy);
                
            } else {
            
                $this->UsunZKoszyka( $TablicaZawartosci['id'] );
            
            }
            //
            unset($Produkt);
            //            
        }
        //
        // sprawdzi czy nie trzeba skasowac jakis gratisow jezeli zmienila sie wartosc koszyka
        $JakieSaGratisy = Gratisy::TablicaGratisow( 'nie' );
        //   
        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
            //
            if ( $TablicaZawartosci['rodzaj_ceny'] == 'gratis' ) {
                //
                if (!isset($JakieSaGratisy[ Funkcje::SamoIdProduktuBezCech($TablicaZawartosci['id']) ])) {
                    $this->UsunZKoszyka($TablicaZawartosci['id']);
                }
                //
            }
            //
        }
        //

        if ( isset($_SESSION['rodzajDostawy']) && isset($_SESSION['rodzajPlatnosci']) ) {
            $i18n = new Translator($_SESSION['domyslnyJezyk']['id']);

            $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK', 'WYSYLKI', 'PODSUMOWANIE_ZAMOWIENIA', 'PLATNOSCI') ), $GLOBALS['tlumacz'] );

            $wysylki = new Wysylki( $_SESSION['krajDostawy']['kod'] );
            $tablicaWysylek = $wysylki->wysylki;
            $WysylkaID = $_SESSION['rodzajDostawy']['wysylka_id'];
            
            if ( isset($tablicaWysylek[$WysylkaID]) && count($tablicaWysylek[$WysylkaID]) > 0 ) {
            
                unset($_SESSION['rodzajDostawy']);
                $_SESSION['rodzajDostawy'] = array('wysylka_id' => $tablicaWysylek[$WysylkaID]['id'],
                                                   'wysylka_klasa' => $tablicaWysylek[$WysylkaID]['klasa'],
                                                   'wysylka_koszt' => $tablicaWysylek[$WysylkaID]['wartosc'],
                                                   'wysylka_nazwa' => $tablicaWysylek[$WysylkaID]['text'],
                                                   'wysylka_vat_id' => $tablicaWysylek[$WysylkaID]['vat_id'],
                                                   'wysylka_vat_stawka' => $tablicaWysylek[$WysylkaID]['vat_stawka'],                                                    
                                                   'dostepne_platnosci' => $tablicaWysylek[$WysylkaID]['dostepne_platnosci'] );

                $platnosci = new Platnosci( $_SESSION['rodzajDostawy']['wysylka_id'] );
                $tablicaPlatnosci = $platnosci->platnosci;
                $PlatnoscID = $_SESSION['rodzajPlatnosci']['platnosc_id'];
                unset($_SESSION['rodzajPlatnosci']);
                
                if ( isset($tablicaPlatnosci[$PlatnoscID]['id']) ) {
                    $_SESSION['rodzajPlatnosci'] = array('platnosc_id' => $tablicaPlatnosci[$PlatnoscID]['id'],
                                                         'platnosc_klasa' => $tablicaPlatnosci[$PlatnoscID]['klasa'],
                                                         'platnosc_koszt' => $tablicaPlatnosci[$PlatnoscID]['wartosc'],
                                                         'platnosc_nazwa' => $tablicaPlatnosci[$PlatnoscID]['text'] );
                }
                
            }
        }


    }

    public function ZawartoscKoszyka() {
        //
        $WartoscKoszykaNetto = 0;
        $WartoscKoszykaBrutto = 0;
        $WartoscKoszykaVat = 0;
        $IloscProduktowKoszyka = 0;
        $WagaProduktowKoszyka = 0;
        //
        $WartoscKoszykaNettoInne = 0;
        $WartoscKoszykaBruttoInne = 0;
        $WartoscKoszykaVatInne = 0;
        $IloscProduktowKoszykaInne = 0;
        //
        foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
            //
            $SumaBrutto = $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];
            $SumaNetto = $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'];
            $SumaVat = $SumaBrutto - $SumaNetto;
            //
            $WagaProduktowKoszyka += $TablicaZawartosci['waga'] * $TablicaZawartosci['ilosc'];
            //
            $WartoscKoszykaNetto += $SumaNetto;
            $WartoscKoszykaBrutto += $SumaBrutto;
            $WartoscKoszykaVat += $SumaVat;
            $IloscProduktowKoszyka += $TablicaZawartosci['ilosc'];
            //
            unset($SumaBrutto, $SumaNetto, $SumaVat);
            
            // suma innych produktow (np gratisow)
            if ( $TablicaZawartosci['rodzaj_ceny'] != 'baza' ) {
                //
                $SumaBrutto = $TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'];
                $SumaNetto = $TablicaZawartosci['cena_netto'] * $TablicaZawartosci['ilosc'];
                $SumaVat = $SumaBrutto - $SumaNetto;
                //
                $WartoscKoszykaNettoInne += $SumaNetto;
                $WartoscKoszykaBruttoInne += $SumaBrutto;
                $WartoscKoszykaVatInne += $SumaVat;
                //
                $IloscProduktowKoszykaInne += $TablicaZawartosci['ilosc'];;
                //
                unset($SumaBrutto, $SumaNetto, $SumaVat);
            }            
            //
        }
        //
        // wynik z _baza sa to produkty wg cen z bazy - odliczone np ceny gratisow - potrzebne do obliczania np gratisow
        $Wynik = array('netto'       => $WartoscKoszykaNetto,
                       'brutto'      => $WartoscKoszykaBrutto,
                       'vat'         => $WartoscKoszykaVat,
                       'ilosc'       => $IloscProduktowKoszyka,
                       'waga'        => $WagaProduktowKoszyka,
                       'ilosc_baza'  => $IloscProduktowKoszyka - $IloscProduktowKoszykaInne,
                       'netto_baza'  => $WartoscKoszykaNetto - $WartoscKoszykaNettoInne,
                       'brutto_baza' => $WartoscKoszykaBrutto - $WartoscKoszykaBruttoInne,
                       'vat_baza'    => $WartoscKoszykaVat - $WartoscKoszykaVatInne);
        //
        unset($WartoscKoszykaNetto, $WartoscKoszykaBrutto, $WartoscKoszykaVat, $IloscProduktowKoszyka, $WagaProduktowKoszyka, $WartoscKoszykaNettoInne, $WartoscKoszykaBruttoInne, $WartoscKoszykaVatInne, $IloscProduktowKoszykaInne);
        //
        return $Wynik;
        //
    }
    
    public function KoszykIloscProduktow() {
        //
        $ZawartoscKoszyka = $this->ZawartoscKoszyka();
        return $ZawartoscKoszyka['ilosc'];
        //
    }
    
    public function KoszykWartoscProduktow() {
        //
        $ZawartoscKoszyka = $this->ZawartoscKoszyka();
        return $ZawartoscKoszyka['brutto'];
        //
    }    

}

?>