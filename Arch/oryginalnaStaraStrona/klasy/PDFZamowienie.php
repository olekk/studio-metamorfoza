<?php

class PDFZamowienie {
  
  public static function WydrukZmowieniaPDF($zamowienie_id) {
    global $zamowienie;

    if ( !isset($_GET['id_poz']) ) {
        $_GET['id_poz'] = $zamowienie_id;
    }

    $waluty = new Waluty();

    // -----------------------------------------------------------------------------
    $html = '<style>
                .naglowek { background-color:#e7e7e7; color:#000000; }
                .klient { background-color:#ffffff; color:#000000; }
                .male_nr_kat { font-weight:normal; color:#5b5a5a; }
                .male_producent { font-weight:normal; color:#5b5a5a; }
                .malyTekstBold { font-size:8pt; font-weight:bold; }
                .malyTekstDodatkowy { font-size:6pt; font-weight:normal; border-top:1px solid #e7e7e7; text-align:justify; }
           </style>';      

    $html .= '
    <table cellspacing="0" cellpadding="5" border="1" style="width:640px">
    
        <tr>
            <td class="naglowek" style="width:25%;">'.$GLOBALS['tlumacz']['KLIENT_NUMER_ZAMOWIENIA'].':</td>
            <td class="klient" style="width:75%;">'.$_GET['id_poz'].'</td>
        </tr>
        
        <tr>
            <td class="naglowek">'.$GLOBALS['tlumacz']['DATA_ZAMOWIENIA'].':</td>
            <td class="klient">'.$zamowienie->info['data_zamowienia'].'</td>
        </tr>
        
        <tr>
            <td class="naglowek">'.$GLOBALS['tlumacz']['SPOSOB_ZAPLATY'].':</td>
            <td class="klient">'.$zamowienie->info['metoda_platnosci'].'</td>
        </tr>
        
        <tr>
            <td class="naglowek">'.$GLOBALS['tlumacz']['SPOSOB_DOSTAWY'].':</td>
            <td class="klient">'.$zamowienie->info['wysylka_modul'] . ( $zamowienie->info['wysylka_info'] != '' ? ' ('.$zamowienie->info['wysylka_info'].')' : '' ).'</td>
        </tr>
        
        <tr>
            <td class="naglowek">'.$GLOBALS['tlumacz']['DOKUMENT_SPRZEDAZY'].':</td>
            <td class="klient">'.$zamowienie->info['dokument_zakupu_nazwa'].'</td>
        </tr>';

    if ( PDF_ZAMOWIENIE_POKAZ_WAGE == 'tak' ) {

        $html .= '
        <tr>
            <td class="naglowek">'.$GLOBALS['tlumacz']['KOSZYK_WAGA_PRODUKTOW'].'</td>
            <td class="klient">'.number_format($zamowienie->waga_produktow, 3, ',', '').' '.$GLOBALS['tlumacz']['KOSZYK_WAGA_PRODUKTOW_JM'].'</td>
        </tr>';        
        
    }

    $html .= '</table>';
    
    $html .= '<br />';

    $html .= '
    <table cellspacing="0" cellpadding="5" border="1" style="width:640px">
    
        <tr>
            <td class="naglowek">'.$GLOBALS['tlumacz']['KLIENT_ZAMAWIAJACY'].'</td>
            <td class="naglowek">'.$GLOBALS['tlumacz']['KLIENT_ADRES_WYSYLKI'].'</td>
            <td class="naglowek">'.$GLOBALS['tlumacz']['KLIENT_ADRES_PLATNIKA'].'</td>
        </tr>
        
        <tr>
            <td class="klient">'. Klient::PokazAdresKlienta('klient') .'</td>
            <td class="klient">'. Klient::PokazAdresKlienta('dostawa') .'</td>
            <td class="klient">'. Klient::PokazAdresKlienta('platnik') .'</td>
        </tr>
        
    </table>';
    
    $html .= '<br />';

    $html .= '
    <table cellspacing="0" cellpadding="5" border="1" style="width:640px">
    
        <tr>
            <td class="naglowek" style="width:40px; text-align:center;">ID</td>';
            
            if ( PDF_ZAMOWIENIE_POKAZ_ZDJECIE_PRODUKTU == 'tak' ) {
                 //
                 $html .= '<td class="naglowek" style="width:70px">'.$GLOBALS['tlumacz']['INFO_FOTO'].'</td>
                           <td class="naglowek" style="width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '290px' : '200px' ) . '">'.$GLOBALS['tlumacz']['NAZWA_PRODUKTU'].'</td>';
                 //
               } else {
                 //
                 $html .= '<td class="naglowek" style="width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '360px' : '270px' ) . '">'.$GLOBALS['tlumacz']['NAZWA_PRODUKTU'].'</td>';
                 //
            }
            
            $html .= '<td class="naglowek" style="width:60px; text-align:center;">'.$GLOBALS['tlumacz']['ILOSC_PRODUKTOW'].'</td>';
            
            if ( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) {
                //
                $html .= '<td class="naglowek" style="width:90px; text-align:center;">'.$GLOBALS['tlumacz']['CENA_JEDNOSTKOWA'].'</td>';
                //
              } else {
                //
                $html .= '<td class="naglowek" style="width:90px; text-align:center;">'.$GLOBALS['tlumacz']['CENA_NETTO'].'</td>
                          <td class="naglowek" style="width:90px; text-align:center;">'.$GLOBALS['tlumacz']['CENA_BRUTTO'].'</td>';
                //
            }
            
            $html .= '<td class="naglowek" style="width:90px; text-align:center;">'.$GLOBALS['tlumacz']['KLIENT_WARTOSC_ZAMOWIENIA'].'</td>
        </tr>';

        $WartoscVat = 0;

        foreach ( $zamowienie->produkty as $produkt ) {

          $wyswietl_cechy = '';
          if (isset($produkt['attributes']) && (count($produkt['attributes']) > 0)) {
            foreach ($produkt['attributes'] as $cecha ) {
              $wyswietl_cechy .= '<br /><span class="male_nr_kat">'.$cecha['cecha'] . ': <b>' . Funkcje::KropkaPrzecinek($cecha['wartosc']) . '</b></span>';
            }
          }
          
          $wyswietl_pola_tekstowe = '';
          if ( $produkt['pola_txt'] != '' ) {
            //
            $poleTxt = Funkcje::serialCiag($produkt['pola_txt']);
            foreach ( $poleTxt as $wartoscTxt ) {
                // jezeli pole to plik
                if ( $wartoscTxt['typ'] == 'plik' ) {
                    $wyswietl_pola_tekstowe .= '<br /><span class="male_nr_kat">' . $wartoscTxt['nazwa'] . ': <a href="' . ADRES_URL_SKLEPU . '/inne/wgranie.php?src=' . base64_encode(str_replace('.',';',$wartoscTxt['tekst'])) . '"><b>' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</b></a></span>';
                  } else {
                    $wyswietl_pola_tekstowe .= '<br /><span class="male_nr_kat">' . $wartoscTxt['nazwa'] . ': <b>' . $wartoscTxt['tekst'] . '</b></span>';
                }                  
            }
            unset($poleTxt);
            //
          }          

          $html .= '<tr>
                      <td style="text-align:center">'.(($produkt['id_produktu'] > 0) ? $produkt['id_produktu'] : '-').'</td>';
                      
                      if ( PDF_ZAMOWIENIE_POKAZ_ZDJECIE_PRODUKTU == 'tak' ) {
                          $html .= '<td style="text-align:center">' . Funkcje::pokazObrazek($produkt['zdjecie'], '', 60, 60) . '</td>';
                      }
                      
                      $html .= '<td style="align:left">'.
                        '<b>'.$produkt['nazwa'].'</b>'.
                        ( trim($produkt['model']) != '' && PDF_ZAMOWIENIE_POKAZ_NUMER_KATALOGOWY == 'tak' ? '<br /><span class="male_nr_kat">'.$GLOBALS['tlumacz']['NUMER_KATALOGOWY'].': <b>'.$produkt['model'].'</b></span>' : '' ).
                        ( trim($produkt['ean']) != '' && PDF_POKAZ_NUMER_EAN == 'tak' ? '<br /><span class="male_nr_kat">'.$GLOBALS['tlumacz']['KOD_EAN'].': <b>'.$produkt['ean'].'</b></span>' : '' ).
                        ( trim($produkt['producent']) != '' && PDF_ZAMOWIENIE_POKAZ_PRODUCENT == 'tak' ? '<br /><span class="male_producent">'.$GLOBALS['tlumacz']['PRODUCENT'].': <b>'.$produkt['producent'].'</b></span>' : '' ) .
                        ( trim($produkt['czas_wysylki']) != '' ? '<br /><span class="male_nr_kat">'.$GLOBALS['tlumacz']['CZAS_WYSYLKI'].': <b>'.$produkt['czas_wysylki'].'</b></span>' : '' ).
                        ( trim($produkt['gwarancja']) != '' ? '<br /><span class="male_nr_kat">'.$GLOBALS['tlumacz']['GWARANCJA'].': <b>'.$produkt['gwarancja'].'</b></span>' : '' ).
                        ( trim($produkt['stan']) != '' ? '<br /><span class="male_nr_kat">'.$GLOBALS['tlumacz']['STAN_PRODUKTU'].': <b>'.$produkt['stan'].'</b></span>' : '' ).
                        ( !empty($wyswietl_cechy) ? $wyswietl_cechy : '' ) .
                        ( trim($produkt['komentarz']) != '' ? '<br /><span class="male_nr_kat">'.$GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'].' <b>'.$produkt['komentarz'].'</b></span>' : '' ) . 
                        $wyswietl_pola_tekstowe . '
                      </td>
                      <td style="text-align:center">' . Funkcje::KropkaPrzecinek($produkt['ilosc']) . ' ' . ((PDF_ZAMOWIENIE_POKAZ_JM == 'tak') ? Produkty::PokazJednostkeMiary($produkt['jm']) : '') . '</td>';
                      
                      if ( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) { 
                          //
                          $html .= '<td style="text-align:right; white-space:nowrap;">' . Funkcje::KropkaPrzecinek($waluty->WyswietlFormatCeny($produkt['cena_koncowa_brutto'], $waluty->waluty[$zamowienie->info['waluta']]['id'], true)) . '</td>';
                          //
                        } else {
                          //
                          $html .= '<td style="text-align:right; white-space:nowrap;">' . Funkcje::KropkaPrzecinek($waluty->WyswietlFormatCeny($produkt['cena_koncowa_netto'], $waluty->waluty[$zamowienie->info['waluta']]['id'], true)) . '</td>
                                    <td style="text-align:right; white-space:nowrap;">' . Funkcje::KropkaPrzecinek($waluty->WyswietlFormatCeny($produkt['cena_koncowa_brutto'], $waluty->waluty[$zamowienie->info['waluta']]['id'], true)) . '</td>';
                          //
                      }
                                    
                      $html .= '<td style="text-align:right; white-space:nowrap;">' . Funkcje::KropkaPrzecinek($waluty->WyswietlFormatCeny($produkt['cena_koncowa_brutto'] * $produkt['ilosc'], $waluty->waluty[$zamowienie->info['waluta']]['id'], true)) . '</td>
                    </tr>';
                    
          $WartoscVat = $WartoscVat + ($produkt['ilosc'] * ( $produkt['cena_koncowa_brutto'] - $produkt['cena_koncowa_netto'] ) );
        }

    $html .= '</table>';
    
    $html .= '<div style="height:15px">&nbsp;</div>';

    $html .= '<table cellspacing="0" cellpadding="5" border="0" style="width:640px">';

    for ($i = 0, $n = count($zamowienie->podsumowanie); $i < $n; $i++) {
    
      $html .= '
      <tr>
        <td style="width:80%">' . $zamowienie->podsumowanie[$i]['tytul'] . '</td>
        <td style="width:20%; text-align:right;"><b> ' . Funkcje::KropkaPrzecinek($waluty->WyswietlFormatCeny($zamowienie->podsumowanie[$i]['wartosc'], $waluty->waluty[$zamowienie->info['waluta']]['id']), true) . '</b></td>
       </tr>';

       if ( $i == 0 && $WartoscVat > 0 && FAKTURA_ZWOLNIENIE_VAT == 'nie' ) {
            $html .= '
            <tr>
                <td style="width:80%">'.$GLOBALS['tlumacz']['W_TYM_WARTOSC_VAT'].'</td>
                <td style="width:20%; text-align:right;"><b>' . Funkcje::KropkaPrzecinek($waluty->WyswietlFormatCeny($WartoscVat, $waluty->waluty[$zamowienie->info['waluta']]['id']), true) . '</b></td>
            </tr>';
       }
    }
    
    $html .= '</table>';
    
    $KomentarzKlienta = Klient::pokazKomentarzZamowienia($_GET['id_poz']);
    
    if ( trim($KomentarzKlienta) != '' ) {
    
        $html .= '<div style="height:15px">&nbsp;</div>';

        $html .= '<table cellspacing="0" cellpadding="5" border="0" style="width:640px">
          
              <tr>
                <td class="malyTekstBold">'.$GLOBALS['tlumacz']['KLIENT_KOMENTARZ'].':</td>
              </tr>
              
              <tr>
                <td>'.Klient::pokazKomentarzZamowienia($_GET['id_poz']).'</td>
              </tr>
              
            </table>';
          
    }

    unset($KomentarzKlienta);
    
    // historia zamowienia
    
    if ( PDF_ZAMOWIENIE_POKAZ_HISTORIE == 'tak' ) {
    
        if ( count($zamowienie->statusy) > 0 ) {
        
            $html .= '<div style="height:15px">&nbsp;</div>';
      
            $html .= '<table cellspacing="0" cellpadding="5" border="0" style="width:640px">
            
                  <tr>
                    <td class="malyTekstBold" colspan="3">'.$GLOBALS['tlumacz']['HISTORIA_REALIZACJI_ZAMOWIENIA'].':</td>
                  </tr>';
        
            foreach ( $zamowienie->statusy as $status ) {

              $html .= '<tr>
                          <td style="width:110px">'.date('d-m-Y H:i', strtotime($zamowienie->statusy[$status['zamowienie_status_id']]['data_dodania'])).'</td>
                          <td style="width:130px">'.Funkcje::pokazNazweStatusuZamowienia($zamowienie->statusy[$status['zamowienie_status_id']]['status_id']).'</td>
                          <td style="width:400px">'.$zamowienie->statusy[$status['zamowienie_status_id']]['komentarz'].'</td>
                        </tr>';
                        
            }
            
            $html .= '</table>';
          
        }  

    }

    $DodatkowyTekst = PDF_ZAMOWIENIE_TEKST;

    if ( trim($DodatkowyTekst) != '' ) {
    
        $html .= '<div style="height:15px">&nbsp;</div>';

        $html .= '<table cellspacing="0" cellpadding="5" border="0" style="width:640px">
          
              <tr>
                <td class="malyTekstDodatkowy">'.nl2br($DodatkowyTekst).'</td>
              </tr>
              
            </table>';
            
    }
    
    unset($DodatkowyTekst);

    return $html;

  }

}
?>