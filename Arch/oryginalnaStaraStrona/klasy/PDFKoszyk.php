<?php

class PDFKoszyk {
  
  public static function WydrukKoszykaPDF() {

    // -----------------------------------------------------------------------------
    $html = '<style>
                .naglowek { background-color:#e7e7e7; color:#000000; }
                .tabelaPdf td { vertical-align:middle; text-align:center; }
                .em { display:block; text-align:center; }
                .Brutto { font-style: normal; }
                .Netto { font-style: normal; }
                .sumaKoszyka { color:#ffffff; font-size:120%; background-color:#474747; }
                .wartosc { font-size:120%; }
                .data_generowania { font-size:7pt; text-align:right; }
           </style>';      
           
    $html .= '<div class="data_generowania">' . $GLOBALS['tlumacz']['PDF_STAN_KOSZYKA_NA_DZIEN'] . ' ' . date('d-m-Y H:i',time()) . '</div> <br />
    
              <table cellspacing="0" cellpadding="5" border="1" style="width:640px" class="tabelaPdf">
            
                <tr>
                    <td class="naglowek" style="width:70px;">' . $GLOBALS['tlumacz']['INFO_FOTO'] . '</td>            
                    <td class="naglowek" style="width:' . ((PDF_KOSZYK_POKAZ_NUMER_KATALOGOWY == 'tak') ? '190px' : '290px') . '; text-align:left;">' . $GLOBALS['tlumacz']['NAZWA_PRODUKTU'] . '</td>';
                    
                    if ( PDF_KOSZYK_POKAZ_NUMER_KATALOGOWY == 'tak' ) {
                        $html .= '<td class="naglowek" style="width:100px;">' . $GLOBALS['tlumacz']['NUMER_KATALOGOWY'] . '</td>';
                    }
                    
                    $html .= '<td class="naglowek" style="width:50px;">' . $GLOBALS['tlumacz']['ILOSC_PRODUKTOW'] . '</td>
                    <td class="naglowek" style="width:110px;">' . $GLOBALS['tlumacz']['CENA'] . '</td>
                    <td class="naglowek" style="width:120px;">' . $GLOBALS['tlumacz']['WARTOSC_PRODUKTOW'] . '</td>
                </tr>
                
              ';         
              
    // sprawdzi czy nie zmienil sie stan magazynowy produktu lub produkt nie jest wylaczony
    $stanKoszyka = false;
    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
        //
        $stanKoszyka = $GLOBALS['koszykKlienta']->SprawdzIloscProduktuMagazyn( $TablicaZawartosci['id'], true );
        //
    }
    if ( $stanKoszyka == true ) {
        //
        Funkcje::PrzekierowanieURL('koszyk.html');
        //
    }
    unset($stanKoszyka);              
              
    // generuje tablice globalne z nazwami cech
    Funkcje::TabliceCech();                 

    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {    

        //
        $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
        // 
    
        // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
        $Przecinek = 2;
        // jezeli sa wartosci calkowite to dla pewnosci zrobi int
        if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
            $Przecinek = 0;
        }      

        // czy produkt ma cechy
        $CechaPrd = Funkcje::CechyProduktuPoId( $TablicaZawartosci['id'] );
        $JakieCechy = '';
        if ( count($CechaPrd) > 0 ) {
            //
            for ($a = 0, $c = count($CechaPrd); $a < $c; $a++) {
                $JakieCechy .= '<br /> <small>' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></small>';
            }
            //
        }        
           
        $html .= '<tr>
                    <td style="width:70px;">' . Funkcje::pokazObrazek($Produkt->fotoGlowne['plik_zdjecia'], $Produkt->fotoGlowne['opis_zdjecia'], 50, 50) . '</td>                    
                    <td style="width:' . ((PDF_KOSZYK_POKAZ_NUMER_KATALOGOWY == 'tak') ? '190px' : '290px') . '; text-align:left">' . $Produkt->info['nazwa'] . $JakieCechy . '</td>';

                    if ( PDF_KOSZYK_POKAZ_NUMER_KATALOGOWY == 'tak' ) {
                        $html .= '<td style="width:100px;">' . $TablicaZawartosci['nr_katalogowy'] . '</td>';
                    }
                    
        $html .= '  <td style="width:50px;">' . number_format( $TablicaZawartosci['ilosc'], $Przecinek, '.', '' ) . '</td>
                    <td style="width:110px;">';

                    // zwraca tablice z cenna netto i brutto
                    $cena = $GLOBALS['waluty']->FormatujCene($TablicaZawartosci['cena_brutto'], $TablicaZawartosci['cena_netto'], 0, $_SESSION['domyslnaWaluta']['id']);
                    
                    // jezeli wyswietlane jednoczenie netto i brutto
                    if (CENY_BRUTTO_NETTO == 'tak') {
                        //
                        $html .= $cena['brutto'] . ' <small>' . $GLOBALS['tlumacz']['BRUTTO'] . '</small> <br />';
                        $html .= $cena['netto'] . ' <small>' . $GLOBALS['tlumacz']['NETTO'] . '</small>';
                        //
                    } else {
                        //
                        $html .= $cena['brutto'];
                        //
                    }
                    
                    unset($cena);

                    $html .= '</td><td style="width:120px;">';

                    // zwraca tablice z cenna netto i brutto
                    $cena = $GLOBALS['waluty']->FormatujCene($TablicaZawartosci['ilosc'] * $TablicaZawartosci['cena_brutto'], $TablicaZawartosci['ilosc'] * $TablicaZawartosci['cena_netto'], 0, $_SESSION['domyslnaWaluta']['id']);
                    
                    // jezeli wyswietlane jednoczenie netto i brutto
                    if (CENY_BRUTTO_NETTO == 'tak') {
                        //
                        $html .= $cena['brutto'] . ' <small>' . $GLOBALS['tlumacz']['BRUTTO'] . '</small> <br />';
                        $html .= $cena['netto'] . ' <small>' . $GLOBALS['tlumacz']['NETTO'] . '</small>';
                        //
                    } else {
                        //
                        $html .= $cena['brutto'];
                        //
                    }
                    
                    unset($cena);

                    $html .= '</td>                    
                  </tr>
                  
                  <tr>
                    <td colspan="' . ((PDF_KOSZYK_POKAZ_NUMER_KATALOGOWY == 'tak') ? '6' : '5') . '" style="text-align:left">' . $GLOBALS['tlumacz']['PDF_LINK_DO_PRODUKTU'] . ' <a href="' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '">' . ADRES_URL_SKLEPU . '/' . $Produkt->info['adres_seo'] . '</a></td>
                  </tr>';
        
        //
        unset($JakieCechy, $CechaPrd, $Produkt, $Przecinek);
        //           
        
    }

    $html .= '<tr>
                <td colspan="' . ((PDF_KOSZYK_POKAZ_NUMER_KATALOGOWY == 'tak') ? '5' : '4') . '" class="wartosc" style="text-align:right">' . $GLOBALS['tlumacz']['WARTOSC_PRODUKTOW'] . '</td>
                <td class="sumaKoszyka">';
                
                $ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
                
                // zwraca tablice z cenna netto i brutto
                $suma = $GLOBALS['waluty']->FormatujCene($ZawartoscKoszyka['brutto'], $ZawartoscKoszyka['netto'], 0, $_SESSION['domyslnaWaluta']['id']);
                
                // jezeli wyswietlane jednoczenie netto i brutto
                if (CENY_BRUTTO_NETTO == 'tak') {
                    //
                    $html .= $suma['brutto'] . ' <small>' . $GLOBALS['tlumacz']['BRUTTO'] . '</small> <br />';
                    $html .= $suma['netto'] . ' <small>' . $GLOBALS['tlumacz']['NETTO'] . '</small>';
                    //
                } else {
                    //
                    $html .= $suma['brutto'];
                    //
                }
                
                unset($suma);                
                
                $html .= '</td>
              </tr>';
    
    $html .= '</table>';
    
    unset($ZawartoscKoszyka);
    
    return $html;

  }

}
?>