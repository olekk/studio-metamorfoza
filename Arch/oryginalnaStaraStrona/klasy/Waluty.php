<?php

class Waluty {

    public function Waluty() {

        $this->waluty = array();
        $this->waluty_id = array();
        //
        
        // cache zapytania
        $WynikCacheWaluty = $GLOBALS['cache']->odczytaj('Waluty', CACHE_INNE);
        $WynikCacheWalutyId = $GLOBALS['cache']->odczytaj('WalutyId', CACHE_INNE);
        
        if ( !$WynikCacheWaluty || !$WynikCacheWalutyId ) {

            $zapytanie = "select currencies_id, code, title, symbol, decimal_point, value, currencies_marza from currencies";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            while ($waluta = $sql->fetch_assoc()) { 
            
                $this->waluty[$waluta['code']] = array('nazwa' => $waluta['title'],
                                                       'symbol' => $waluta['symbol'],
                                                       'separator' => $waluta['decimal_point'],
                                                       'przelicznik' => (( $waluta['value'] == 0 ) ? 1 : $waluta['value']),
                                                       'marza' => $waluta['currencies_marza'],
                                                       'id' => $waluta['currencies_id']);
                                                       
                $this->waluty_id[$waluta['currencies_id']] = array('code' => $waluta['code'],
                                                                   'symbol' => $waluta['symbol']); 
                                                                                                                    
            }
            
            $GLOBALS['cache']->zapisz('Waluty', $this->waluty, CACHE_INNE);    
            $GLOBALS['cache']->zapisz('WalutyId', $this->waluty_id, CACHE_INNE);                              
            
            $GLOBALS['db']->close_query($sql);
            unset($waluta, $zapytanie);

        } else {
        
            $this->waluty = $WynikCacheWaluty;
            
            $this->waluty_id = $WynikCacheWalutyId;
        
        }
        
        unset($WynikCacheWaluty, $WynikCacheWalutyId);
        
    }

    // formatuje cene
    public function FormatujCene($kwota_brutto = 0, $kwota_netto = 0, $cena_promocyjna = 0, $id_waluty_produktu = 1, $formatuj_walute = true) {

        if ( $id_waluty_produktu == '' ) {
            $id_waluty_produktu = '1';
        }

        $kod_waluty_produktu  = $this->waluty_id[$id_waluty_produktu]['code'];
        $kod_waluty_domyslnej = $this->waluty_id[$_SESSION['domyslnaWaluta']['id']]['code'];

        $kwota_brutto = $kwota_brutto / $this->waluty[$kod_waluty_produktu]['przelicznik'];
        $kwota_netto = $kwota_netto / $this->waluty[$kod_waluty_produktu]['przelicznik'];
        $cena_promocyjna = $cena_promocyjna / $this->waluty[$kod_waluty_produktu]['przelicznik'];

        if ( $id_waluty_produktu == $_SESSION['domyslnaWaluta']['id'] ) {
            $przelicznik = 1 / $this->waluty[$kod_waluty_produktu]['przelicznik'];
            $marza = 1 + ( $this->waluty[$kod_waluty_produktu]['marza']/100 );
        } else {
            $przelicznik = 1 / $this->waluty[$kod_waluty_domyslnej]['przelicznik'];
            $marza = 1 + ( $this->waluty[$kod_waluty_domyslnej]['marza']/100 );
        }
        
        // jezeli wynik ma byc formatowany do postaci waluty
        if ( $formatuj_walute == true ) {
            //
            $wynikBrutto = number_format( round( ($kwota_brutto / $przelicznik) * $marza, CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, $this->waluty[$_SESSION['domyslnaWaluta']['kod']]['separator'], ' ') . ' ' . $this->waluty[$_SESSION['domyslnaWaluta']['kod']]['symbol'];
            $wynikNetto = number_format( round( ($kwota_netto / $przelicznik) * $marza, CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, $this->waluty[$_SESSION['domyslnaWaluta']['kod']]['separator'], ' ') . ' ' . $this->waluty[$_SESSION['domyslnaWaluta']['kod']]['symbol'];
            
            if ($cena_promocyjna > 0) {
                $wynikPromocja = number_format( round( ($cena_promocyjna / $przelicznik) * $marza, CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, $this->waluty[$_SESSION['domyslnaWaluta']['kod']]['separator'], ' ') . ' ' . $this->waluty[$_SESSION['domyslnaWaluta']['kod']]['symbol'];
              } else {
                $wynikPromocja = '';
            }
            //
            $wynik = array('brutto' => $wynikBrutto, 'netto' => $wynikNetto, 'promocja' => $wynikPromocja);
            //
          } else {
            //
            $wynikBrutto = number_format( round( ($kwota_brutto / $przelicznik) * $marza, CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, '.', '');
            $wynikNetto = number_format( round( ($kwota_netto / $przelicznik) * $marza, CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, '.', '');
            
            if ($cena_promocyjna > 0) {
                $wynikPromocja = number_format( round( ($cena_promocyjna / $przelicznik) * $marza, CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, '.', '');
              } else {
                $wynikPromocja = '';
            }            
            //
            $wynik = array('brutto' => $wynikBrutto, 'netto' => $wynikNetto, 'promocja' => $wynikPromocja);
            //
        }
        //
        unset($kwota_brutto, $kwota_netto, $cena_promocyjna, $id_waluty_produktu, $kod_waluty_produktu, $kod_waluty_domyslnej, $przelicznik, $marza);
        //        
        
        return $wynik;
    }
    

    // wyswietla cene
    public function PokazCene($kwota_brutto, $kwota_netto, $cena_promocyjna, $id_waluty_produktu = 1, $nettoBrutto = CENY_BRUTTO_NETTO) {
        
        // zwraca tablice z cenna netto i brutto
        $cena = $this->FormatujCene($kwota_brutto, $kwota_netto, $cena_promocyjna, $id_waluty_produktu);
        
        // jezeli jest cena promocyjna
        if ($cena['promocja'] != '') {
            $wyswietl = '<span class="CenaPromocyjna">';
            $wyswietl .= '<em class="CenaPoprzednia">' . $cena['promocja'] . '</em>';
          } else {
            $wyswietl = '<span class="Cena">';
        }        

        // jezeli wyswietlane jednoczenie netto i brutto
        if ($nettoBrutto == 'tak') {
            //
            $wyswietl .= '<em class="Brutto">' . $cena['brutto'] . ' <small>' . $GLOBALS['tlumacz']['BRUTTO'] . '</small></em>';
            $wyswietl .= '<em class="Netto">' . $cena['netto'] . ' <small>' . $GLOBALS['tlumacz']['NETTO'] . '</small></em>';
            //
        } else {
            //
            $wyswietl .= $cena['brutto'];
            //
        }
        
        $wyswietl .= '</span>';

        return $wyswietl;        
    
    }

    // wyswietla cene
    public function WyswietlFormatCeny($kwota, $id_waluty = 1, $formatuj = false, $klasaCss = true) {

        // dodaje do golej liczby miejsca po przecinku i odpowiednio , lub .
        if ($formatuj == true) {
            $kwota = number_format( round( $kwota, CENY_MIEJSCA_PO_PRZECINKU ), CENY_MIEJSCA_PO_PRZECINKU, $this->waluty[$_SESSION['domyslnaWaluta']['kod']]['separator'], ' ' );
        }
        
        $wyswietl = '';
        
        if ($klasaCss == true) {
            $wyswietl .= '<span class="Cena">';
        }
        
        $wyswietl .= $kwota . ' ' . $this->waluty_id[$id_waluty]['symbol'];
        
        if ($klasaCss == true) {
            $wyswietl .= '</span>';
        }
        
        unset($kwota);

        return $wyswietl;        
    }

    // wyswietla cene bez symbolu
    public function PokazCeneBezSymbolu($kwota, $kod_waluty = 'PLN', $przelicz = false) {

        if ( $kod_waluty == '' ) {
          $kod_waluty = $this->waluty_id[$_SESSION['domyslnaWaluta']['id']]['code'];
        } else {
          $kod_waluty = $kod_waluty;
        }

        $kwota = $kwota;

        if ( $przelicz ) {
          $przelicznik = 1 / $this->waluty[$kod_waluty]['przelicznik'];
          $marza = 1 + ( $this->waluty[$kod_waluty]['marza']/100 );

          $kwota = $kwota / $przelicznik * $marza;
        }
        
        unset($kod_waluty, $przelicznik);

        return number_format( round( $kwota, 2 ), 2, '.', '');
    }

    // wyswietla cene z symbolem
    public function PokazCeneSymbol($kwota, $kod_waluty, $przelicz = false) {

        if ( $kod_waluty == '' ) {
          $kod_waluty = $this->waluty_id[$_SESSION['domyslnaWaluta']['id']]['code'];
        } else {
          $kod_waluty = $kod_waluty;
        }

        $kwota = $kwota;

        if ( $przelicz ) {
          $przelicznik = 1 / $this->waluty[$kod_waluty]['przelicznik'];
          $marza = 1 + ( $this->waluty[$kod_waluty]['marza']/100 );

          $kwota = $kwota / $przelicznik * $marza;
        }

        $wyswietl = number_format( round($kwota, 2 ), 2, $this->waluty[$kod_waluty]['separator'], '') . ' ' . $this->waluty[$kod_waluty]['symbol'];
        
        unset($kod_waluty, $przelicznik);

        return $wyswietl;
    }

  }
?>
