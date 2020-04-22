<?php

class PDFKartaProduktu {
  
  public static function WydrukKartyProduktuPDF($id_produktu) {
  
    $Produkt = new Produkt( $id_produktu ); 
    $Produkt->ProduktDostepnosc();
    $Produkt->ProduktCzasWysylki();
    $Produkt->ProduktProducent();
    $Produkt->ProduktDodatkowePola();    
    $Produkt->ProduktDodatkoweZakladki();

    // -----------------------------------------------------------------------------
    $html = '<style>
                h1 { padding:0px; margin:0px; font-size:21pt; font-weight:normal; }
                h2 { padding:0px; margin:0px; font-size:14pt; font-weight:normal; }
                .data_generowania { font-size:7pt; text-align:right; }
                .link_do_produktu { font-size:8pt; text-align:right; }
                .link_do_produktu a { font-size:8pt; text-decoration:none; }
                .dane_a { width:150px; border-bottom:1px solid #d3d3d3; }
                .dane_b { width:260px; font-weight:bold; border-bottom:1px solid #d3d3d3; }
                .dane_cena { width:260px; font-weight:bold; color:#ff0000; border-bottom:1px solid #d3d3d3; }
                .dane_cena_poprzednia { width:260px; font-weight:bold; border-bottom:1px solid #d3d3d3; text-decoration: line-through; }
                .naglowek_cechy { font-size:11pt; color:#517b8c; }
             </style>';
             
    $html .= '<div class="data_generowania">' . $GLOBALS['tlumacz']['PDF_AKTUALNE_NA'] . ' ' . date('d-m-Y H:i',time()) . '</div>
    
              <div class="link_do_produktu">' . $GLOBALS['tlumacz']['PDF_LINK_DO_PRODUKTU'] . ' <a href="' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '">' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '</a></div> <br /><br />
    
              <table cellspacing="0" cellpadding="0" border="0" style="width:640px">
                <tr>
                    <td style="width:220px; vertical-align:top;">'.Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], SZEROKOSC_OBRAZEK_MALY, WYSOKOSC_OBRAZEK_MALY).'</td>
                    <td style="width:420px; vertical-align:top;">
                    
                        <table cellspacing="0" cellpadding="5" border="0" class="pozycje">
                        
                            <tr><td colspan="2"><h1>' . $Produkt->info['nazwa'] . '</h1></td></tr>';
                            
                            // ceny produktu
                            // jezeli wogole jest cena
                            if ($Produkt->info['jest_cena'] == 'tak') {
                            
                                if (CENY_BRUTTO_NETTO == 'tak') { 
                                
                                    $html .= '<tr>
                                                <td class="dane_a">' . $GLOBALS['tlumacz']['CENA_BRUTTO'] . '</td>
                                                <td class="dane_cena">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ) . '</td>
                                              </tr>';

                                    $html .= '<tr>
                                                <td class="dane_a">' . $GLOBALS['tlumacz']['CENA_NETTO'] . '</td>
                                                <td class="dane_b">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_netto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ) . '</td>
                                              </tr>';                                            
                                
                                } else {
                                
                                    $html .= '<tr>
                                                <td class="dane_a">' . $GLOBALS['tlumacz']['CENA'] . '</td>
                                                <td class="dane_cena">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_brutto_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ) . '</td>
                                              </tr>';  
                                              
                                }
                                
                                // jezeli jest cena poprzednia
                                if ( $Produkt->info['cena_poprzednia_bez_formatowania'] > 0 ) {
                                    $html .= '<tr>
                                                <td class="dane_a">' . $GLOBALS['tlumacz']['CENA_POPRZEDNIA'] . '</td>
                                                <td class="dane_cena_poprzednia">' . $GLOBALS['waluty']->WyswietlFormatCeny( $Produkt->info['cena_poprzednia_bez_formatowania'], $_SESSION['domyslnaWaluta']['id'], true, false ) . '</td>
                                              </tr>';   
                                }
                                
                            }
                                
                            // data dostepnosci produktu
                            if ( !empty($Produkt->info['data_dostepnosci']) ) {
                                $html .= '<tr>
                                            <td class="dane_a">' . $GLOBALS['tlumacz']['DOSTEPNY_OD_DNIA'] . '</td>
                                            <td class="dane_b">' . $Produkt->info['data_dostepnosci'] . '</td>
                                          </tr>';
                            }
                                          
                            // dostepnosc - w formie tekstu
                            if ( !empty($Produkt->dostepnosc['dostepnosc']) ) { 
                                $html .= '<tr>
                                            <td class="dane_a">' . $GLOBALS['tlumacz']['DOSTEPNOSC'] . '</td>
                                            <td class="dane_b">' . $Produkt->dostepnosc['dostepnosc'] . '</td>
                                          </tr>';
                            }
                            
                            // stan magazynowy
                            if ( MAGAZYN_SPRAWDZ_STANY == 'tak' && PDF_POKAZ_STAN_MAGAZYNOWY == 'tak' ) {
                                //
                                // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
                                $Przecinek = 2;
                                // jezeli sa wartosci calkowite to dla pewnosci zrobi int
                                if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
                                    $Przecinek = 0;
                                }                              
                                if ( $Produkt->info['ilosc'] > 0 ) { 
                                    $html .= '<tr>
                                                <td class="dane_a">' . $GLOBALS['tlumacz']['STAN_MAGAZYNOWY'] . '</td>
                                                <td class="dane_b">' . number_format( $Produkt->info['ilosc'], $Przecinek, '.', '' ) . ' ' . $Produkt->info['jednostka_miary'] . '</td>
                                              </tr>';
                                }       
                                unset($Przecinek);
                                //
                            }
                            
                            // czas wysylki
                            if ( !empty($Produkt->czas_wysylki) ) { 
                                $html .= '<tr>
                                            <td class="dane_a">' . $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . '</td>
                                            <td class="dane_b">' . $Produkt->czas_wysylki . '</td>
                                          </tr>';
                            }  

                            // nr katalogowy
                            if ( !empty($Produkt->info['nr_katalogowy']) && PDF_POKAZ_NUMER_KATALOGOWY == 'tak' ) { 
                                $html .= '<tr>
                                            <td class="dane_a">' . $GLOBALS['tlumacz']['NUMER_KATALOGOWY'] . '</td>
                                            <td class="dane_b">' . $Produkt->info['nr_katalogowy'] . '</td>
                                          </tr>';
                            }                 

                            // kod ean
                            if ( !empty($Produkt->info['ean']) && PDF_POKAZ_NUMER_EAN == 'tak' ) { 
                                $html .= '<tr>
                                            <td class="dane_a">' . $GLOBALS['tlumacz']['KOD_EAN'] . '</td>
                                            <td class="dane_b">' . $Produkt->info['ean'] . '</td>
                                          </tr>';
                            }

                            // pkwiu
                            if ( !empty($Produkt->info['pkwiu']) && PDF_POKAZ_NUMER_PKWIU == 'tak' ) { 
                                $html .= '<tr>
                                            <td class="dane_a">' . $GLOBALS['tlumacz']['PKWIU'] . '</td>
                                            <td class="dane_b">' . $Produkt->info['pkwiu'] . '</td>
                                          </tr>';
                            }              

                            // dodatkowe pola obok zdjecia
                            if ( count($Produkt->dodatkowePolaFoto) > 0 ) {
                                //
                                foreach ( $Produkt->dodatkowePolaFoto as $Pole ) {
                                    $html .= '<tr>
                                                <td class="dane_a">' . $Pole['nazwa'] . '</td>
                                                <td class="dane_b">' . $Pole['wartosc'] . '</td>
                                              </tr>'; 
                                }
                                //
                            }                            
                        
                        $html .= '</table>';
                        
                    $html .= '</td>
                </tr>
            </table>
            
            <br />';
            
            if ( $Produkt->info['opis'] != '' ) {
                //
                $dom = new domDocument;
                libxml_use_internal_errors(true);
                $dom->loadHTML($Produkt->info['opis']);
                $dom->preserveWhiteSpace = false;
                $images = $dom->getElementsByTagName('img');
                foreach ($images as $image) {
                    if ( !is_file(KATALOG_SKLEPU . $image->getAttribute('src') ) ) {
                        $Produkt->info['opis'] = str_replace( $image->getAttribute('src'), '/' . KATALOG_ZDJEC . '/domyslny_pdf.gif', $Produkt->info['opis']);
                    }
                }
                //
            }

            $html .= '<h2>' . $GLOBALS['tlumacz']['ZAKLADKA_OPIS_PRODUKTU'] . '</h2> <br />' . PDFKartaProduktu::px2pt($Produkt->info['opis']) . '<br /><br />';
            //$html .= '<h2>' . $GLOBALS['tlumacz']['ZAKLADKA_OPIS_PRODUKTU'] . '</h2> <br />' . $Produkt->info['opis'] . '<br /><br />';
            
            if ( count($Produkt->dodatkowePolaOpis) > 0 ) {
                //
                foreach ( $Produkt->dodatkowePolaOpis as $Pole ) {
                    //
                    $html .= $Pole['nazwa'] . ': <b>' . $Pole['wartosc'] . '</b> <br />';
                    //
                }
                //
                $html .= '<br />';
                //
            }            
            
            // cechy produktu
            
            // jezeli produkt ma cene
            if ($Produkt->info['jest_cena'] == 'tak') {
            
                $cechy = $Produkt->ProduktCechyGenerujPDF();
                if ( $cechy != '') {
                    //
                    $html .= '<div class="naglowek_cechy">' . $GLOBALS['tlumacz']['PDF_OPCJE_PRODUKTU'] . '</div> <br />';
                    $html .= $cechy . '<br />';
                    //
                }
                unset($cechy);
                
            }
            
            // dodatkowe zakladki
            if ( count($Produkt->dodatkoweZakladki) > 0 ) { 
            
                foreach ( $Produkt->dodatkoweZakladki as $DodatkowaZakladka ) {
                    //
                    $html .= '<h2>' . $DodatkowaZakladka['nazwa'] . '</h2> <br />' . $DodatkowaZakladka['tresc'] . '<br /><br />';
                    //
                }
                
            }
            unset($DodatkowaZakladka);            

    return $html;

  }

  static function MyCallback($matches) {
      return floor(($matches[1]*0.5)).'pt';
  }

  public static function px2pt($text) {

      $text = preg_replace_callback('/([0-9]+)px/', "PDFKartaProduktu::MyCallback", $text);

      return $text;

  }

}
?>