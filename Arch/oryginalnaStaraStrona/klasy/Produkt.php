<?php

class Produkt {

    protected $infoSql;

    // uzywane funkcje
    /*
    ProduktInfo() - ogolne informacje o produkcie
    ProduktDodatkoweZdjecia() - dodatkowe zdjecia produktu - zwraca w formie tablicy
    ProduktKupowanie( id ) - kupowanie produktu - koszyk, ilosc, czy mozna kupowac
    ProduktRecenzje() - recenzje produktu - w formie tablicy
    ProduktCzasWysylki() - czas wysylki produktu - ilosc dni
    ProduktStanProduktu() - stan produktu - nowy/uzywany
    ProduktGwarancja() - gwarancja produktu
    ProduktDostepnosc() - dostepnosc produktu - w formie tablicy
    ProduktProducent() - dane producenta produktu
    ProduktZnizkiZalezneOdIlosci() - okresla znizke produktu w zaleznosci od ilosci w koszyku - wartosc liczbowa
    ProduktZnizkiZalezneOdIlosciTablica() - zwraca tablice z znizkami od ilosci w koszyku
    ProduktDodatkowePola() - dodatkowe pola do produktu
    ProduktDodatkowePolaTekstowe() - dodatkowe pola tekstowe do produktu
    ProduktCechyIlosc() - ilosc cech produktu - przekazuje do $this->cechyIlosc
    ProduktWartoscCechy() - cena wybranych cech
    ProduktCechyGeneruj() - generuje cechy na karcie produktu
    ProduktCechyNrKatalogowy() - podaje nr katalogowy dla danych cech produktu
    ProduktLinki() - linki do produktu
    ProduktDodatkoweZakladki() - dodatkowe zakladki do produktu
    ProduktPliki() - pliki do produktu
    ProduktYoutube() - filmy youtube
    ProduktFilmyFLV() - filmy flv
    ProduktMp3() - pliki muzyczne Pmp3
    */

    public function Produkt( $id_produktu, $szerokoscObrazka = '', $wysokoscObrazka = '', $nazwaKlasyKoszyka = '', $preloadImg = true ) {
    
        $this->id_produktu = $id_produktu;
        
        if ($nazwaKlasyKoszyka == '') {
            $this->cssKoszyka = 'DoKoszyka';
            $this->cssKoszykaTekst = $GLOBALS['tlumacz']['PRZYCISK_DO_KOSZYKA'];
          } else {
            $this->cssKoszyka = $nazwaKlasyKoszyka;
            $this->cssKoszykaTekst = $GLOBALS['tlumacz']['PRZYCISK_DODAJ_DO_KOSZYKA'];
        }
        
        if ($szerokoscObrazka == '') {
            $this->szerImg = SZEROKOSC_OBRAZEK_MALY;
          } else {
            $this->szerImg = $szerokoscObrazka;
        }
        
        if ($wysokoscObrazka == '') {
            $this->wysImg = WYSOKOSC_OBRAZEK_MALY;
          } else {
            $this->wysImg = $wysokoscObrazka;
        }
        
        $this->jezykDomyslnyId = $_SESSION['domyslnyJezyk']['id'];

        // informacje ogolne o produkcie
        $this->info = array();
        // tablica zapytanie sql
        $this->infoSql = '';
        // informacje o glownym zdjeciu
        $this->fotoGlowne = array();
        // informacje o ikonkach
        $this->ikonki = array();        
        // informacje o recenzjach
        $this->recenzje = array();  
        $this->recenzjeSrednia = array(); 
        // informacje o dostepnosci
        $this->dostepnosc = array(); 
        // czas wysylki
        $this->czas_wysylki = '';
        // czas wysylki - ilosc dni
        $this->czas_wysylki_dni = ''; 
        // stan produktu
        $this->stan_produktu = '';
        // gwarancja produktu
        $this->gwarancja = '';        
        // informacje o producencie
        $this->producent = array(); 
        // meta tagi
        $this->metaTagi = array();
        // dodatkowe pola opisowe - obok zdjecia lub pod opisem
        $this->dodatkowePolaFoto = array();
        $this->dodatkowePolaOpis = array();
        $this->dodatkowePola = array();
        // dodatkowe pola tekstowe
        $this->dodatkowePolaTekstowe = array();
        // linki
        $this->Linki = array();
        // dodatkowe zakladki
        $this->dodatkoweZakladki = array();
        // pliki
        $this->Pliki = array();     
        // filmy youtube
        $this->Youtube = array();     
        // filmy flv
        $this->FilmyFlv = array();       
        // pliki mp3
        $this->Mp3 = array();              
        // input ilosci i wartosci ilosci zakupu
        $this->inputIlosc = array();
        // znizka w zaleznosci od iloscu produktow w koszyku
        $this->znizkiZalezneOdIlosci = '';        
        // czy produkt ma cechy - trzeba do tego wywolac funkcje ProduktCechy
        $this->cechyIlosc = 0;  
        
        // czy do obrazka ma byc dodawana klasa do preloadera obrazkow  
        $this->preloadImg = $preloadImg;
        
        // unikalny id produktu dla unikniecia dubli
        $this->idUnikat = rand(1,99999) . '_';
        
        // zwraca czy produkt jest czy nie
        $this->CzyJestProdukt = $this->ProduktInfo();
        
    }
    
    private function ProduktInfo() {
    
        $DodatkoweCeny = '';
        if ( (int)ILOSC_CEN > 1 ) {
            //
            for ($n = 2; $n <= (int)ILOSC_CEN; $n++) {
                //
                $DodatkoweCeny .= 'p.products_price_tax_' . $n . ', p.products_price_' . $n . ', p.products_old_price_' . $n . ', p.products_retail_price_' . $n . ', ';
                //
            }
            //
        }

        $zapProdukt = "SELECT p.products_id,
                          p.products_quantity,
                          p.products_model,
                          p.products_man_code,
                          p.products_ean,
                          p.products_pkwiu,
                          p.products_image,
                          p.products_image_description,
                          p.products_price_tax,
                          p.products_price,
                          p.products_retail_price,
                          " . $DodatkoweCeny . "
                          p.products_old_price,
                          p.products_currencies_id,
                          p.products_tax_class_id,
                          p.products_availability_id,
                          p.products_shipping_time_id,
                          p.products_status,
                          p.products_buy,
                          p.products_accessory,
                          p.products_weight,
                          p.products_pack_type,
                          p.products_comments,                          
                          p.new_status,
                          p.specials_status,
                          p.specials_date, 
                          p.specials_date_end,                          
                          p.featured_status,
                          p.featured_date, 
                          p.featured_date_end,                          
                          p.star_status,
                          p.star_date,
                          p.star_date_end,
                          p.products_jm_id,
                          p.products_minorder,
                          p.products_maxorder,
                          p.products_quantity_order,
                          p.products_discount,
                          p.shipping_method,
                          p.shipping_cost,
                          p.products_make_an_offer,
                          p.free_shipping_status,
                          p.products_date_available,
                          p.options_type,
                          p.products_condition_products_id,
                          p.products_warranty_products_id,
                          p.products_type,
                          m.manufacturers_id,
                          m.manufacturers_name,
                          m.manufacturers_image,
                          pd.products_seo_url,
                          pd.products_name,
                          pd.products_name_info,
                          pd.products_description,
                          pd.products_short_description,
                          pd.products_meta_title_tag,
                          pd.products_meta_desc_tag,
                          pd.products_meta_keywords_tag,
                          pd.products_viewed,
                          ptc.categories_id,
                          t.tax_rate
                      FROM products p
                      LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $this->jezykDomyslnyId . "'
                      LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                      RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                      RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                      LEFT JOIN tax_rates t ON t.tax_rates_id = p.products_tax_class_id
                      WHERE p.products_id = '" . $this->id_produktu . "' and p.products_status = '1'" . $GLOBALS['warunekProduktu'];

        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $this->id_produktu . '_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_PRODUKTY, true);   

        if ( !$WynikCache ) {
            $sqlProdukt = $GLOBALS['db']->open_query($zapProdukt);      
            $IleRekordow = (int)$GLOBALS['db']->ile_rekordow($sqlProdukt);
          } else {
            $IleRekordow = count($WynikCache);
        }        
                      
        if ( $IleRekordow > 0 ) {
        
            if ( !$WynikCache ) {
                $this->infoSql = $sqlProdukt->fetch_assoc();
                //
                $GLOBALS['cache']->zapisz('Produkt_Id_' . $this->id_produktu . '_' . $_SESSION['domyslnyJezyk']['kod'], $this->infoSql, CACHE_PRODUKTY, true);
            } else {
                $this->infoSql = $WynikCache;
            }   

            // jezeli klient ma inny poziom cen
            if ( $_SESSION['poziom_cen'] > 1 ) {
                //
                // jezeli cena w innym poziomie nie jest pusta
                if ( $this->infoSql['products_price_' . $_SESSION['poziom_cen']] > 0 ) {
                    //
                    $this->infoSql['products_price_tax'] = $this->infoSql['products_price_tax_' . $_SESSION['poziom_cen']];
                    $this->infoSql['products_price'] = $this->infoSql['products_price_' . $_SESSION['poziom_cen']];
                    //
                }
                //
                // cena poprzednia przy promocji
                if ( $this->infoSql['products_old_price_' . $_SESSION['poziom_cen']] > 0 ) {
                    //
                    $this->infoSql['products_old_price'] = $this->infoSql['products_old_price_' . $_SESSION['poziom_cen']];
                    //
                }
                //
                // cena katalogowa
                if ( $this->infoSql['products_retail_price_' . $_SESSION['poziom_cen']] > 0 ) {
                    //
                    $this->infoSql['products_retail_price'] = $this->infoSql['products_retail_price_' . $_SESSION['poziom_cen']];
                    //
                }
                //
            }            
            
            // ustawienia promocji - sprawdzi czy produkt nie jest cena promocyjna z datami - jezeli daty nie lapia sie na aktualny czas to przyjmie cene poprzednia
            if ( ((strtotime($this->infoSql['specials_date']) > time() && $this->infoSql['specials_date'] != '0000-00-00 00:00:00') || (strtotime($this->infoSql['specials_date_end']) < time() && $this->infoSql['specials_date_end'] != '0000-00-00 00:00:00') ) && $this->infoSql['specials_status'] == 1 && $this->infoSql['products_old_price'] > 0 ) {
                //
                $this->infoSql['products_price_tax'] = $this->infoSql['products_old_price'];
                // 
                // obliczanie netto i vatu             
                $netto = round( $this->infoSql['products_price_tax'] / (1 + (Funkcje::StawkaPodatekVat( $this->infoSql['products_tax_class_id'] )/100)), 2);
                $podatek = $this->infoSql['products_price_tax'] - $netto;
                //
                $this->infoSql['products_price'] = $netto;
                $this->infoSql['products_tax'] = $podatek;
                //
                $this->infoSql['products_old_price'] = 0;
                $this->infoSql['specials_status'] = 0;
                //
                unset($netto, $podatek);
            }
            
            // jezeli nie jest zdefiniowana jednostka miary
            if ( empty($this->infoSql['products_jm_id']) ) {
                 $this->infoSql['products_jm_id'] = 0;
            }

            // ustala jaka ma byc tresc linku
            $linkSeo = ((trim($this->infoSql['products_seo_url']) != '') ? $this->infoSql['products_seo_url'] : $this->infoSql['products_name']);

            // rabaty klienta od ceny produktu
            $CenaRabaty = $this->CenaProduktuPoRabatach( $this->infoSql['products_price'], $this->infoSql['products_price_tax'] );
            $this->infoSql['products_price'] = $CenaRabaty['netto'];
            $this->infoSql['products_price_tax'] = $CenaRabaty['brutto'];

            // cena produktu
            $CenaProduktu = $GLOBALS['waluty']->PokazCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $this->infoSql['products_currencies_id'] );
            // uzywane do autouzupelnienia - pokazuje tylko cene brutto
            $CenaProduktuBrutto = $GLOBALS['waluty']->PokazCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $this->infoSql['products_currencies_id'], 'nie' );
            
            // jezeli cena jest rowna 0
            if ( $this->infoSql['products_price_tax'] <= 0 ) {            
                $CenaProduktu = '<span class="BrakCeny">' . $GLOBALS['tlumacz']['CENA_ZAPYTAJ_O_CENE'] . '</span>';
                $CenaProduktuBrutto = '';
            }        
            // jezeli ceny sa tylko widoczne dla klientow zalogowanych
            if ( CENY_DLA_WSZYSTKICH == 'nie' && ((int)$_SESSION['customer_id'] == 0 || $_SESSION['gosc'] == '1')) {
                $CenaProduktu = '<span class="CenaDlaZalogowanych">' . $GLOBALS['tlumacz']['CENA_TYLKO_DLA_ZALOGOWANYCH'] . '</span>';
                $CenaProduktuBrutto = '';
            }
            
            // ceny bez formatowania - same kwoty po przeliczeniu - cena brutto, netto i promocyjna
            $TablicaCenyProduktu = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_price_tax'], $this->infoSql['products_price'], $this->infoSql['products_old_price'], $this->infoSql['products_currencies_id'], false );
            
            // sprawdzi czy cena katalogowa nie jest nizsza od glownej
            if ( $this->infoSql['products_retail_price'] < $this->infoSql['products_price_tax'] ) {
                 $this->infoSql['products_retail_price'] = 0;
            }
            
            // cena katalogowa
            $CenaKatalogowa = $GLOBALS['waluty']->FormatujCene( $this->infoSql['products_retail_price'], 0, 0, $this->infoSql['products_currencies_id'], false );

            $this->info = array('id'                               => $this->infoSql['products_id'],
                                'status_kupowania'                 => (( $this->infoSql['products_buy'] == '1' ) ? 'tak' : 'nie' ),
                                'status_akcesoria'                 => (( $this->infoSql['products_accessory'] == '1' ) ? 'tak' : 'nie' ),
                                'ilosc'                            => $this->infoSql['products_quantity'],
                                'nr_katalogowy'                    => $this->infoSql['products_model'],
                                'kod_producenta'                   => $this->infoSql['products_man_code'],
                                'ean'                              => $this->infoSql['products_ean'],
                                'pkwiu'                            => $this->infoSql['products_pkwiu'],
                                'nazwa'                            => $this->infoSql['products_name'],
                                'nazwa_dodatkowa'                  => $this->infoSql['products_name_info'],
                                'nazwa_seo'                        => $linkSeo,
                                'adres_seo'                        => Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ),
                                'link'                             => '<a href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . $this->infoSql['products_name'] . '</a>',
                                'link_z_domena'                    => '<a href="' . ADRES_URL_SKLEPU . '/' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . $this->infoSql['products_name'] . '</a>',
                                'link_szczegoly'                   => '<a href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . $GLOBALS['tlumacz']['ZOBACZ_SZCZEGOLY'] . '</a>',
                                'jest_cena'                        => (( $CenaProduktuBrutto == '' ) ? 'nie' : 'tak' ),
                                'cena'                             => $CenaProduktu,
                                'cena_brutto'                      => $CenaProduktuBrutto,
                                'cena_brutto_bez_formatowania'     => $TablicaCenyProduktu['brutto'],
                                'cena_netto_bez_formatowania'      => $TablicaCenyProduktu['netto'],
                                'cena_poprzednia_bez_formatowania' => $TablicaCenyProduktu['promocja'],
                                'cena_katalogowa_bez_formatowania' => $CenaKatalogowa['brutto'],
                                'rabat_produktu'                   => $CenaRabaty['rabat'],
                                'vat_bez_formatowania'             => $TablicaCenyProduktu['brutto'] - $TablicaCenyProduktu['netto'],
                                'stawka_vat'                       => $this->infoSql['tax_rate'],
                                'stawka_vat_id'                    => $this->infoSql['products_tax_class_id'],
                                'opis'                             => $this->infoSql['products_description'],
                                'opis_krotki'                      => (( !empty($this->infoSql['products_short_description']) ) ? $this->infoSql['products_short_description'] : Funkcje::przytnijTekst(strip_tags($this->infoSql['products_description']), '250')),
                                'id_dostepnosci'                   => $this->infoSql['products_availability_id'],
                                'id_waluty'                        => $this->infoSql['products_currencies_id'],
                                'id_producenta'                    => $this->infoSql['manufacturers_id'],
                                'id_czasu_wysylki'                 => $this->infoSql['products_shipping_time_id'], 	
                                'nazwa_producenta'                 => $this->infoSql['manufacturers_name'],
                                'foto_producenta'                  => $this->infoSql['manufacturers_image'],
                                'jednostka_miary'                  => (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['nazwa'] : ''),
                                'jednostka_miary_typ'              => (( isset($GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['typ']) ) ? $GLOBALS['jednostkiMiary'][$this->infoSql['products_jm_id']]['typ'] : ''),
                                'waga'                             => $this->infoSql['products_weight'],
                                'gabaryt'                          => $this->infoSql['products_pack_type'],
                                'ilosc_wyswietlen'                 => $this->infoSql['products_viewed'],
                                'komentarze_do_produktu'           => (( $this->infoSql['products_comments'] == '1' ) ? 'tak' : 'nie' ),
                                'dostepne_wysylki'                 => $this->infoSql['shipping_method'],
                                'darmowa_wysylka'                  => (( $this->infoSql['free_shipping_status'] == '1' ) ? 'tak' : 'nie' ),
                                'koszt_wysylki'                    => $this->infoSql['shipping_cost'],
                                'negocjacja'                       => (( $this->infoSql['products_make_an_offer'] == '1' ) ? 'tak' : 'nie' ),
                                'data_dostepnosci'                 => ((Funkcje::czyNiePuste($this->infoSql['products_date_available'])) ? date('d-m-Y',strtotime($this->infoSql['products_date_available'])) : ''),
                                'typ_cech'                         => $this->infoSql['options_type'],
                                'id_kategorii'                     => $this->infoSql['categories_id'],
                                'typ_produktu'                     => $this->infoSql['products_type']
            );

            unset($TablicaCenyProduktu, $CenaRabaty, $CenaKatalogowa);
            
            // ciag znizek zaleznych od ilosci
            if (ZNIZKI_OD_ILOSCI_PROMOCJE == 'tak' || (ZNIZKI_OD_ILOSCI_PROMOCJE == 'nie' && $this->info['cena_poprzednia_bez_formatowania'] == 0)) {
               //
               if (ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'tak' || (ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'nie' && $this->info['rabat_produktu'] == 0)) {
                  //
                  $this->znizkiZalezneOdIlosci = $this->infoSql['products_discount'];
                  //
               }
               //
            }          
                                
            $this->metaTagi = array('tytul' => (( empty($this->infoSql['products_meta_title_tag']) ) ? strip_tags($this->infoSql['products_name']) : $this->infoSql['products_meta_title_tag']),
                                    'opis'  => (( empty($this->infoSql['products_meta_desc_tag']) ) ? strip_tags($this->infoSql['products_name']) : $this->infoSql['products_meta_desc_tag']),
                                    'slowa' => (( empty($this->infoSql['products_meta_keywords_tag']) ) ? strip_tags($this->infoSql['products_name']) : $this->infoSql['products_meta_keywords_tag']));
            
            
            // ustala jaka ma alt zdjecia
            $altFoto = htmlspecialchars(((!empty($this->infoSql['products_image_description'])) ? $this->infoSql['products_image_description'] : strip_tags($this->infoSql['products_name'])));
                        
            $this->ikonki = array();
            if ( strtotime($this->infoSql['star_date']) < time() || ((int)strtotime($this->infoSql['star_date_end']) > 0 && strtotime($this->infoSql['star_date_end']) < time()) ) {
              $this->ikonki['hit'] = $this->infoSql['star_status'];
            } else {
              $this->ikonki['hit'] = '0';
            }
            if ( strtotime($this->infoSql['specials_date']) < time() || ((int)strtotime($this->infoSql['specials_date_end']) > 0 && strtotime($this->infoSql['specials_date_end']) < time()) ) {
              $this->ikonki['promocja'] = $this->infoSql['specials_status'];
            } else {
              $this->ikonki['promocja'] = '0';          
            }
            //
            $this->ikonki['promocja_data_od'] = strtotime($this->infoSql['specials_date']);
            $this->ikonki['promocja_data_do'] = strtotime($this->infoSql['specials_date_end']);   
            //
            if ( strtotime($this->infoSql['featured_date']) < time() || ((int)strtotime($this->infoSql['featured_date_end']) > 0 && strtotime($this->infoSql['featured_date_end']) < time()) ) {
              $this->ikonki['polecany'] = $this->infoSql['featured_status'];
            } else {
              $this->ikonki['polecany'] = '0';
            }

            $this->ikonki['nowosc'] = $this->infoSql['new_status'];    

            $this->ikonki['darmowa_dostawa'] = $this->infoSql['free_shipping_status'];             
            
            // czy jest wypelnione pola obrazka glownego
            if ((empty($this->infoSql['products_image']) && POKAZ_DOMYSLNY_OBRAZEK == 'tak') || !empty($this->infoSql['products_image'])) {
                //
                if (empty($this->infoSql['products_image'])) {
                    $this->infoSql['products_image'] = 'domyslny.gif';
                }
                //
                $linkIdFoto = 'id="fot_' . $this->idUnikat . $this->id_produktu . '" ';
                //
                $this->fotoGlowne = array('plik_zdjecia'       => $this->infoSql['products_image'],
                                          'zdjecie_bez_css'    => Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), (($this->preloadImg == true ) ? 'class="Reload"' : ''), 'maly', true, $this->preloadImg),
                                          'zdjecie'            => Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', 'maly', true, $this->preloadImg),
                                          'zdjecie_ikony'      => Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', 'maly', true, $this->preloadImg),
                                          'zdjecie_link'       => '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', 'maly', true, $this->preloadImg) . '</a>',
                                          'zdjecie_link_ikony' => '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $this->infoSql['products_id'], 'produkt' ) . '">' . Funkcje::pokazObrazek($this->infoSql['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie' . (($this->preloadImg == true ) ? ' Reload' : '') . '"', 'maly', true, $this->preloadImg) . '</a>',
                                          'opis_zdjecia'       => $altFoto); 
                //
                unset($linkIdFoto);
                //
              } else {
                //
                $this->fotoGlowne = array('plik_zdjecia' => '', 'zdjecie_bez_css' => '', 'zdjecie' => '', 'zdjecie_ikony' => '', 'zdjecie_link'  => '', 'zdjecie_link_ikony' => '', 'opis_zdjecia' => '');           
                //
            }
            
            unset($linkSeo, $altFoto);
            
            return true;
        
        } else {
        
            return false;
            
        }
        
        if ( !$WynikCache ) {
            $GLOBALS['db']->close_query($sqlProdukt); 
        }        
                    
        unset($zapProdukt, $IleRekordow, $WynikCache);
    
    }    
    
    private function CenaProduktuPoRabatach($netto, $brutto) {
        //
        $pobierzFunkcje = true;
        include('produkt/CenaProduktuPoRabatach.php');
        unset($pobierzFunkcje);
        //
        return array( 'netto' => $cenaNetto, 'brutto' => $cenaBrutto, 'rabat' => $Rabat );
        //
    }
    
    // dodatkowe zdjecia produktu
    public function ProduktDodatkoweZdjecia() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkoweZdjecia.php');
        unset($pobierzFunkcje);
        //
        return $DodatkoweZdjecia;
        //
    }
    
    // kupowanie produktu
    public function ProduktKupowanie( $id = '' ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktKupowanie.php');
        unset($pobierzFunkcje);
        //
    }
    
    // recenzje produktu
    public function ProduktRecenzje() { 
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktRecenzje.php');
        unset($pobierzFunkcje);
        //
    }
    
    // czas wysylki produktu
    public function ProduktCzasWysylki() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCzasWysylki.php');
        unset($pobierzFunkcje);
        //
    }
    
    // stan produktu
    public function ProduktStanProduktu() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktStanProduktu.php');
        unset($pobierzFunkcje);
        //
    }   

    // gwarancja
    public function ProduktGwarancja() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktGwarancja.php');
        unset($pobierzFunkcje);
        //
    }    
    
    // dostepnosc produktu
    public function ProduktDostepnosc( $idDostepnosci = '', $iloscProduktu = '' ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDostepnosc.php');
        unset($pobierzFunkcje);
        //
    }
    
    // funkcja zwracajaca ID dostepnosci produktu dla dostepnosci automatycznych
    public function PokazIdDostepnosciAutomatycznych( $iloscProduktu ) {
        //
        $pobierzFunkcje = true;
        include('produkt/PokazIdDostepnosciAutomatycznych.php');
        unset($pobierzFunkcje);
        //
        return $dostepnosc_id;
        //
    }    
    
    // dane producenta
    public function ProduktProducent( $szerokoscImg = SZEROKOSC_LOGO_PRODUCENTA, $wysokoscImg = WYSOKOSC_LOGO_PRODUCENTA ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktProducent.php');
        unset($pobierzFunkcje);
        //
    }    
    
    // okresla znizke produktu w zaleznosci od ilosci w koszyku
    public function ProduktZnizkiZalezneOdIlosci( $ilosc ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktZnizkiZalezneOdIlosci.php');
        unset($pobierzFunkcje);
        //
        return $ZnizkaWynik; 
        //
    }
    
    // zwraca tablice z znizkami od ilosci w koszyku
    public function ProduktZnizkiZalezneOdIlosciTablica() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktZnizkiZalezneOdIlosciTablica.php');
        unset($pobierzFunkcje);
        //
        return $ZnizkaTablica;
        //    
    }    
    
    // dodatkowe pola do produktu
    public function ProduktDodatkowePola() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkowePola.php');
        unset($pobierzFunkcje);
        //
    }   
    
    // dodatkowe pola tekstowe do produktu
    public function ProduktDodatkowePolaTekstowe() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkowePolaTekstowe.php');
        unset($pobierzFunkcje);
        //
    }     

    // cechy produktu
    public function ProduktCechyIlosc() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyIlosc.php');
        unset($pobierzFunkcje);
        //
    }    
    
    // cena wybranych cech
    public function ProduktWartoscCechy( $cechy ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktWartoscCechy.php');
        unset($pobierzFunkcje);
        //
        return array( 'brutto' => $TablicaCen['brutto'], 'netto' => $TablicaCen['netto'], 'waga' => $WagaCechy ) ;
        //    
    }
    
    // cena produktu z okreslona kombinacja cech - uzywane jezeli produkt ma stale ceny dla cech
    public function ProduktWartoscCechyCeny( $cechy ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktWartoscCechyCeny.php');
        unset($pobierzFunkcje);
        //
        return array( 'brutto' => $TablicaCen['brutto'], 'netto' => $TablicaCen['netto'], 'waga' => $WagaCechy ) ; 
        //    
    }
    
    // podaje nr katalogowy dla danych cech produktu
    public function ProduktCechyNrKatalogowy( $cechy ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyNrKatalogowy.php');
        unset($pobierzFunkcje);
        //
        return $NrKatalogowyCechy;
        //        
    }
    
    public function ProduktCechyGeneruj() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyGeneruj.php');
        unset($pobierzFunkcje);
        //
        return $CiagJs . $Wynik;
        //
    }    
    
    public function ProduktCechyGenerujPDF() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyGenerujPDF.php');
        unset($pobierzFunkcje);
        //
        return $Wynik;
        //        
    }    

    // linki do produktu
    public function ProduktLinki() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktLinki.php');
        unset($pobierzFunkcje);
        //
    }  

    // dodatkowe zakladki do produktu
    public function ProduktDodatkoweZakladki() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktDodatkoweZakladki.php'); 
        unset($pobierzFunkcje);
        //
    }     
    
    // pliki do produktu
    public function ProduktPliki() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktPliki.php'); 
        unset($pobierzFunkcje);
        //
    }   

    // filmy youtube
    public function ProduktYoutube() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktYoutube.php'); 
        unset($pobierzFunkcje);
        //
    }   

    // filmy flv
    public function ProduktFilmyFLV() {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktFilmyFLV.php'); 
        unset($pobierzFunkcje);
        //
    }    

    // pliki muzyczne mp3
    public function ProduktMp3() { 
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktMp3.php');   
        unset($pobierzFunkcje);
        //    
    }
    
    // tablica wybranych cech
    public function ProduktCechyTablica( $cechy ) {
        //
        $pobierzFunkcje = true;
        include('produkt/ProduktCechyTablica.php'); 
        unset($pobierzFunkcje);
        //
        return $TablicaCech;
        //
    }
    
}
?>