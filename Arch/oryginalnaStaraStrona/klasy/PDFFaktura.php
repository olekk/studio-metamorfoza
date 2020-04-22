<?php

class PDFFaktura {
  
  public static function WydrukFakturyPDF($zamowienie_id, $faktura_id = '', $faktura_typ, $rodzaj) {
    global $filtr;

    $waluty = new Waluty();
    $slownie = new KwotaSlownie();
    $html = '';

    $stawki_tablica = Array();

    $sql_tmp = $GLOBALS['db']->open_query("SELECT * FROM tax_rates ORDER BY sort_order");
    
    while ($stawki_vat = $sql_tmp->fetch_assoc()) {
    
      $stawki_tablica[] = $stawki_vat['tax_rate'].'|'.$stawki_vat['tax_short_description'];
      if ( $stawki_vat['tax_default'] == '1' ) {
        $domyslny_vat = $stawki_vat['tax_rate'].'|'.$stawki_vat['tax_short_description'];
      }
      
    }
    $GLOBALS['db']->close_query($sql_tmp);

    $dane = PDFFaktura::FakturaGeneruj();

    $zapytanie = "SELECT o.currency, o.currency_value FROM orders o WHERE o.orders_id = '".(int)$_GET['id_poz']."'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

      $zapytanie_kraj = "SELECT DISTINCT cd.countries_name  
                            FROM countries c
                            LEFT JOIN countries_description cd ON c.countries_id = cd. countries_id AND cd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'
                            WHERE c.countries_default = '1'";
      $sqlc = $GLOBALS['db']->open_query($zapytanie_kraj);
      $infoc = $sqlc->fetch_assoc();

      $info = $sql->fetch_assoc();

      $waluta_zamowienia = $info['currency'];

      $numer_faktury = str_pad((int)$_GET['id_poz'], FAKTURA_NUMER_ZERA_WIODACE, 0, STR_PAD_LEFT) . strftime(NUMER_FAKTURY_SUFFIX, strtotime($dane['platnik']['invoices_date_sell']));

      $sprzedawca = DANE_NAZWA_FIRMY_PELNA.'<br />'.DANE_ADRES_LINIA_1.'<br />'.( DANE_ADRES_LINIA_2 != '' ? DANE_ADRES_LINIA_2.'<br />' : '' ).DANE_KOD_POCZTOWY.' '.DANE_MIASTO;

      $nabywca  =  ( $dane['platnik']['invoices_billing_company_name'] != '' ? $dane['platnik']['invoices_billing_company_name'].'<br />' : '' );
      $nabywca .=  ( $dane['platnik']['invoices_billing_company_name'] == '' ? $dane['platnik']['invoices_billing_name'].'<br />' : '' );
      $nabywca .=  $dane['platnik']['invoices_billing_street_address'].'<br />';
      $nabywca .=  $dane['platnik']['invoices_billing_postcode'].' '.$dane['platnik']['invoices_billing_city'];
      if ( $dane['platnik']['invoices_billing_country'] != '' && $dane['platnik']['invoices_billing_country'] != $infoc['countries_name'] ) {
        $nabywca .=  '<br>'.$dane['platnik']['invoices_billing_country'];
      }

      $html = '<style>
                    .naglowekTekst { font-size:9pt; }
                    .malyTekst { font-size:8pt; }
                    .malyTekstTlo { font-size:8pt; background-color:#e7e7e7; border-top:#c0c0c0 1px solid; border-left:#c0c0c0 1px solid; text-align:left; }
                    .malyTekstMniejszy { font-size:6pt; }
                    .malyTekstMniejszyNaglowek { text-align:center; font-size:6pt; background-color:#e7e7e7; border-left:#c0c0c0 1px solid; border-top:#c0c0c0 1px solid; border-right:#c0c0c0 1px solid; }
                    .malyTekstBold { font-size:8pt; font-weight:bold; }
                    .normalnyTekst { font-size:10pt; }
                    .malyTekstItalic { font-size:8pt; font-style:italic; }
                    .naglowekFaktura { font-size:12pt; font-weight:bold; border: #c0c0c0 1px solid; }
                    .klient { background-color:#ffffff; color:#000000; }
                    .male_nr_kat { font-weight:normal; color:#5b5a5a; }
                    .male_producent { font-weight:normal; color:#5b5a5a; }
                    .tekstDoZaplaty { font-size: 12pt; font-weight:bold; text-align:left; }     
                    .ramkaNaglowka { border-top:#c0c0c0 1px solid; border-left:#c0c0c0 1px solid; border-bottom:#c0c0c0 1px solid; font-size:7pt; background-color:#e7e7e7; color:#000000; text-align:center; }
                    .malyTekstPozycje { font-size:8pt; border-left:#c0c0c0 1px solid; text-align:right; }
                    .podsumowanieTlo { text-align:right; font-size:8pt; background-color:#e7e7e7; border-bottom:#c0c0c0 1px solid; border-right:#c0c0c0 1px solid; border-left:#c0c0c0 1px solid; border-top:#c0c0c0 1px solid; }
                    .podsumowanie { text-align:right; font-size:8pt; border-bottom:#c0c0c0 1px solid; border-left:#c0c0c0 1px solid; border-top:#c0c0c0 1px solid; }
                    .polePodpisu { height:70px; border-left:#c0c0c0 1px solid; border-right:#c0c0c0 1px solid; border-bottom:#c0c0c0 1px solid; }
               </style>';      

      $html .= '
      <table cellspacing="0" cellpadding="2" border="0" style="width:640px">
      
        <tr>
          <td class="malyTekst" style="width:390px;">'.$GLOBALS['tlumacz']['FAKTURA_NAZWA_BANKU'].': <span class="malyTekstBold">'.DANE_NAZWA_BANKU.'</span></td>
          <td class="malyTekst" style="width:100px; text-align:right">'.$GLOBALS['tlumacz']['FAKTURA_MIEJSCOWOSC'].':</td>
          <td class="malyTekst" style="width:150px; text-align:right">'.DANE_MIASTO.'</td>
        </tr>
        
        <tr>
          <td class="malyTekst" style="width:390px;">'.$GLOBALS['tlumacz']['FAKTURA_NUMER_KONTA'].': <span class="malyTekstBold">'.DANE_NUMER_KONTA_BANKOWEGO.'</span></td>
          <td class="malyTekst" style="width:100px; text-align:right">'.$GLOBALS['tlumacz']['FAKTURA_DATA_WYSTAWIENIA'].': </td>
          <td class="malyTekst" style="width:150px; text-align:right">'.date('d-m-Y', strtotime($dane['platnik']['invoices_date_generated'])).'</td>
        </tr>
        
      </table>';
      
      $html .= '<div style="height:15px">&nbsp;</div>';

      $html .= '
      <table cellspacing="0" cellpadding="5" border="0" style="width:640px">
      
        <tr>
          <td colspan="2" style="text-align:center" class="naglowekFaktura">Faktura pro-forma : '. $numer_faktury.'</td>
        </tr>
        
        <tr>
          <td colspan="2" style="text-align:center" class="naglowekTekst">'.$rodzaj.'</td>
        </tr>
        
      </table>';

      $html .= '
      <table cellspacing="0" cellpadding="5" border="0" style="width:640px">
      
        <tr>
          <td class="malyTekstTlo" style="width:50%;">'.$GLOBALS['tlumacz']['FAKTURA_SPRZEDAWCA'].'</td>
          <td class="malyTekstTlo" style="width:50%; border-right:#c0c0c0 1px solid;">'.$GLOBALS['tlumacz']['FAKTURA_NABYWCA'].'</td>
        </tr>
        
        <tr>
          <td class="normalnyTekst" style="border:#c0c0c0 1px solid;">
          
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr><td style="height:70px" class="malyTekst">'.$sprzedawca.'</td></tr>
              <tr><td class="malyTekst">'.$GLOBALS['tlumacz']['FAKTURA_NIP'].': '.DANE_NIP.'</td></tr>
            </table>
            
          </td>

          <td class="normalnyTekst" style="border:#c0c0c0 1px solid;">
          
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%">
              <tr><td style="height:70px" class="malyTekst">'.$nabywca.'</td></tr>
              <tr><td class="malyTekst">';
              
              if ( $dane['platnik']['invoices_billing_nip'] != '' ) {
                  //
                  $html .= $GLOBALS['tlumacz']['FAKTURA_NIP'] . ': ' . $dane['platnik']['invoices_billing_nip'];
                  //
              } else if ( $dane['platnik']['invoices_billing_pesel'] != '' ) {
                  //
                  $html .= $GLOBALS['tlumacz']['FAKTURA_PESEL'] . ': ' . $dane['platnik']['invoices_billing_pesel'];
                  // 
              }
              
            $html .= '</td></tr>
            </table>
            
          </td>
        </tr>
        
      </table>
      
      <br />&nbsp;
      
      <div class="malyTekstBold">'.$GLOBALS['tlumacz']['FAKTURA_SPOSOB_ZAPLATY'].': '.$dane['platnik']['invoices_payment_type'].'</div>';

      $html .= '<br />';

      $html .= '
      <table cellspacing="0" cellpadding="2" border="0" style="width:640px">
      
        <tr>
          <td style="width:30px" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_LP'].'</td>
          <td style="width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '340px' : '230px' ) . '" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_NAZWA_TOWARU'].'</td>';
          
          if ( FAKTURA_ZWOLNIENIE_VAT == 'nie' ) { 
              $html .= '<td style="width:50px" class="ramkaNaglowka">PKWiU</td>';
          }    
              
          $html .= '<td style="width:60px;text-align:center" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_ILOSC'].'</td>
          <td style="width:30px;text-align:center" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_JEDNOSTKA_MIARY'].'</td>';
          
          if ( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) { 
          
              $html .= '<td style="width:90px" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_CENA_JEDNOSTKOWA'].'</td>
                        <td style="width:90px; border-bottom:#c0c0c0 1px solid; border-right:#c0c0c0 1px solid" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_WARTOSC'].'</td>';

            } else {
          
              $html .= '<td style="width:60px" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_CENA_NETTO'].'</td>
                        <td style="width:60px" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_WARTOSC_NETTO'].'</td>
                        <td style="width:40px;text-align:center" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_STAWKA_VAT'].'</td>
                        <td style="width:80px; border-bottom:#c0c0c0 1px solid; border-right:#c0c0c0 1px solid" class="ramkaNaglowka">'.$GLOBALS['tlumacz']['FAKTURA_WARTOSC_BRUTTO'].'</td>';
                        
          }
          
        $html .= '</tr>';

        $i = 1;

        foreach ( $dane['produkty'] as $produkt ) {
        
            if ( $produkt['invoices_products_price'] > 0 ) {

                $ilosc                = Funkcje::KropkaPrzecinek($produkt['invoices_products_quantity']);
                $cena_brutto          = Funkcje::KropkaPrzecinek($produkt['invoices_products_price_tax']);
                $cena_netto           = Funkcje::KropkaPrzecinek($produkt['invoices_products_price']);
                $vat                  = Funkcje::KropkaPrzecinek(substr($produkt['invoices_products_tax'],strpos($produkt['invoices_products_tax'], '|')+1));
                $wartosc_brutto       = Funkcje::KropkaPrzecinek($produkt['invoices_total_price_tax']);
                $wartosc_vat          = Funkcje::KropkaPrzecinek($produkt['invoices_total_value_tax']);
                $wartosc_netto        = Funkcje::KropkaPrzecinek($produkt['invoices_total_price']);

                $szczegoly = nl2br($produkt['invoices_products_name']);

                $html .= '
                <tr>
                  <td class="malyTekstPozycje" style="width:30px;text-align:center">'.$i.'</td>
                  <td class="malyTekstPozycje" style="text-align:left; width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '340px' : '230px' ) . '">'.$szczegoly.'</td>';
                  
                  if ( FAKTURA_ZWOLNIENIE_VAT == 'nie' ) { 
                      $html .= '<td class="malyTekstPozycje" style="width:50px">'.$produkt['invoices_products_pkwiu'].'</td>';
                  }
                  
                  $html .= '<td class="malyTekstPozycje" style="width:60px;text-align:center">'.$ilosc.'</td>
                  <td class="malyTekstPozycje" style="width:30px;text-align:center">';
                  if ( $produkt['invoices_products_jm'] != '99999' ) {
                    $html .= Produkty::PokazJednostkeMiary($produkt['invoices_products_jm']);
                  } else {
                    $html .= 'szt.';
                  }
                  $html .= '</td>';
                  
                  if ( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) { 
                  
                      $html .= '<td style="width:90px" class="malyTekstPozycje">'.$cena_brutto.'</td>
                                <td class="malyTekstPozycje" style="width:90px; border-right:#c0c0c0 1px solid;">'.$wartosc_brutto.'</td>';

                    } else {              
                  
                      $html .= '<td class="malyTekstPozycje" style="width:60px">'.$cena_netto.'</td>
                                <td class="malyTekstPozycje" style="width:60px">'.$wartosc_netto.'</td>
                                <td class="malyTekstPozycje" style="width:40px;text-align:center">'.$vat.'</td>
                                <td class="malyTekstPozycje" style="width:80px; border-right:#c0c0c0 1px solid;">'.$wartosc_brutto.'</td>';
                      
                  }
                      
                $html .= '</tr>';  

                $i++;
                
            }
          
        }

        $html .= '
          <tr>
            <td colspan="' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '4' : '5' ) . '" class="malyTekst" style="width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '460px' : '400px' ) . '; border-top:#c0c0c0 1px solid;"></td>
            <td class="malyTekstBold" style="width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '90px' : '60px' ) . '; border-top:#c0c0c0 1px solid;">'.$GLOBALS['tlumacz']['FAKTURA_RAZEM'].'</td>';
            
            if ( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) { 
            
                $html .= '<td style="width:90px; border-right:#c0c0c0 1px solid;" class="podsumowanieTlo">'.Funkcje::KropkaPrzecinek($waluty->PokazCeneBezSymbolu($dane['suma']['invoices_total_tax'], $waluta_zamowienia)).'</td>';
            
              } else {
              
                $html .= PDFFaktura::PodzielPodsumowanie( $dane['suma_rozbicie'], $waluta_zamowienia, 0, 1, false );
                
            }
            
        $html .= '</tr>';
        
        // rozbicie na podatki
        if ( FAKTURA_ZWOLNIENIE_VAT == 'nie' ) { 

            $html .= PDFFaktura::PodzielPodsumowanie( $dane['suma_rozbicie'], $waluta_zamowienia, 2, 99, true );

        }

      $html .= '</table>';
      
      $html .= '<div style="height:15px">&nbsp;</div>';

      if ( isset($dane['rabat'] ) && count($dane['rabat']) > 0 ) {

          $WartoscRabatu = 0;
          $html .= '<table cellspacing="0" cellpadding="2" border="0" style="width:436px">';
          foreach ( $dane['rabat'] as $rabat ) {
          
              $WartoscRabatu += $rabat['invoices_rabat_tax'];
              
              $html .= '
                  <tr>
                      <td style="width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '550px' : '560px' ) . ';text-align:right;" class="podsumowanie">'.$rabat['tytul'].'</td>
                      <td style="width:' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '90px' : '80px' ) . 'px;border-right:#c0c0c0 1px solid;" class="podsumowanie">'.Funkcje::KropkaPrzecinek($waluty->PokazCeneBezSymbolu($rabat['invoices_rabat_tax'], $waluta_zamowienia), $waluta_zamowienia).'</td>
                  </tr>';
                  
          }
          $html .= '</table>';
          
          $dane['suma']['invoices_total_tax'] = $dane['suma']['invoices_total_tax'] - $WartoscRabatu;
      }

      $slownie->setCurrency($waluta_zamowienia);
      $kwota = $slownie->convertPrice($waluty->PokazCeneBezSymbolu($dane['suma']['invoices_total_tax'], $waluta_zamowienia));
      
      $html .= '<div style="height:30px">&nbsp;</div>

      <table cellspacing="0" cellpadding="0" border="0" style="width:640px">
      
        <tr>
          <td class="tekstDoZaplaty" style="width:130px">'.$GLOBALS['tlumacz']['FAKTURA_DO_ZAPLATY'].':</td>
          <td class="tekstDoZaplaty" style="width:510px">'.Funkcje::KropkaPrzecinek($waluty->PokazCeneSymbol($dane['suma']['invoices_total_tax'], $waluta_zamowienia)).'</td>
        </tr>
        
        <tr>
          <td class="malyTekst" style="width:130px">'.$GLOBALS['tlumacz']['FAKTURA_SLOWNIE'].':</td>
          <td class="malyTekst" style="width:510px">'.$kwota.'</td>
        </tr>
        
      </table>';
      
      $html .= '<div style="height:30px">&nbsp;</div>';

      $html .= '
      <table cellspacing="0" cellpadding="0" border="0" style="width:670px">
      
        <tr>
          <td class="malyTekstMniejszyNaglowek" style="width:200px">&nbsp;<br />'.$GLOBALS['tlumacz']['FAKTURA_NABYWCA'].'<br /></td>
          <td class="malyTekstMniejszy" style="width:240px"></td>
          <td class="malyTekstMniejszyNaglowek" style="width:200px">&nbsp;<br />'.$GLOBALS['tlumacz']['FAKTURA_SPRZEDAWCA'].'<br /></td>
        </tr>
        
        <tr>
          <td class="polePodpisu" style="width:200px"></td>
          <td style="width:240px; height:70px;"></td>
          <td class="polePodpisu" style="width:200px"></td>
        </tr>
        
      </table>';

      if ( $dane['platnik']['invoices_comments'] != '' ) {
      
        $html .= '<div style="height:30px">&nbsp;</div>
        
        <table cellspacing="0" cellpadding="5" border="0" style="width:640px">
        
          <tr>
            <td class="malyTekstBold">'.$GLOBALS['tlumacz']['FAKTURA_UWAGI'].':<br />&nbsp;</td>
          </tr>
          
          <tr>
            <td class="malyTekst">'.$dane['platnik']['invoices_comments'].'</td>
          </tr>
          
        </table>';
        
      }
      
      if ( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) { 
      
          $html .= '<div>&nbsp;<br />&nbsp;</div>
          
                    <table cellspacing="0" cellpadding="0" border="0" style="width:640px"><tr>
                        <td class="malyTekst">' . $GLOBALS['tlumacz']['FAKTURA_ZWOLNIENIE_PODSTAWA_PRAWNA'] . '</td>
                    </tr></table>'; 

      }       
      
    }
    return $html;

  }

  public static function PodzielPodsumowanie( $tablica, $waluta_zamowienia, $poczatek = 1, $koniec = 99, $bez_tr = false ) {
    global $waluty;
    
      $licznik = 1;
      $html = '';
      
      foreach ( $tablica as $podsuma ) {
      
        if ( $licznik >= $poczatek ) {

            $html .=
            (( $bez_tr == true ) ? '<tr><td colspan="' . (( FAKTURA_ZWOLNIENIE_VAT == 'tak' ) ? '5' : '6' ) . '" class="malyTekst" style="width:460px;"></td>' : '' ) . '
              <td style="width:60px" class="podsumowanie">'.Funkcje::KropkaPrzecinek($waluty->PokazCeneBezSymbolu($podsuma['invoices_total'], $waluta_zamowienia)).'</td>
              <td style="width:40px;text-align:center" class="podsumowanie">'.Funkcje::KropkaPrzecinek($podsuma['invoices_tax']).'</td>
              <td style="width:80px; border-right:#c0c0c0 1px solid;" class="podsumowanie">'.Funkcje::KropkaPrzecinek($waluty->PokazCeneBezSymbolu($podsuma['invoices_total_tax'], $waluta_zamowienia)).'</td>
              ' . (( $bez_tr == true ) ? '</tr>' : '' );    

        }

        if ( $licznik == $koniec ) {
             break;
        }
        
        $licznik++;
          
      }
      
      return $html;

  }    

  public static function FakturaGeneruj() {
    global $filtr, $zamowienie, $waluty;

      $tablica_vat  = array();
      $tablica_vat  = Produkty::TablicaStawekVat();
      
      $jednostka_domyslna = Funkcje::domyslnaJednostkaMiary();
      $vat_domyslny       = Funkcje::domyslnyPodatekVat();

      // pobranie informacji o platniku z zamowienia
      $pola['platnik'] = array('orders_id' => $filtr->process($_GET['id_poz']),
                               'invoices_nr' => $filtr->process($_GET['id_poz']),
                               'invoices_date_sell' => date('Y-m-d', strtotime($zamowienie->info['data_zamowienia'])),
                               'invoices_date_generated' => date('Y-m-d', time()),
                               'invoices_billing_name' => $zamowienie->platnik['nazwa'],
                               'invoices_billing_company_name' => $zamowienie->platnik['firma'],
                               'invoices_billing_nip' => $zamowienie->platnik['nip'],
                               'invoices_billing_pesel' => $zamowienie->platnik['pesel'],
                               'invoices_billing_street_address' => $zamowienie->platnik['ulica'],
                               'invoices_billing_city' => $zamowienie->platnik['miasto'],
                               'invoices_billing_postcode' => $zamowienie->platnik['kod_pocztowy'],
                               'invoices_billing_country' => $zamowienie->platnik['kraj'],
                               'invoices_payment_type' => $zamowienie->info['metoda_platnosci'],
                               'invoices_comments' => '');

      $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();

      $razem_wartosc_brutto       = 0;
      $razem_wartosc_netto        = 0;
      $razem_wartosc_vat          = 0;

      $podsumowanie_tablica       = array();

      // pobranie informacji o produktach z zamowienia
      foreach ( $zamowienie->produkty as $produkt ) {

        $szczegoly = $produkt['nazwa'] . "\n";

        // informacje o cechach produktu do wyswietlenie na fakturze
        if ( FAKTURA_NAZWA_CECHY == 'tak' ) {
          $wyswietl_cechy = '';
          if (isset($produkt['attributes']) && (count($produkt['attributes']) > 0)) {
            foreach ($produkt['attributes'] as $cecha ) {
              $wyswietl_cechy .= '- ' . $cecha['cecha'] . ':' . $cecha['wartosc'] . "\n";
            }
          }
        }

        if ( FAKTURA_NAZWA_NUMER_KATALOGOWY == 'tak' ) {
          if (trim($produkt['model']) != '') {
            $szczegoly .= '- ' . $produkt['model']."\n";
          }
        }

        if ( FAKTURA_NAZWA_PRODUCENT == 'tak' ) {
          if (trim($produkt['producent']) != '') {                     
            $szczegoly .= '- ' . $produkt['producent']."\n";
          }
        }
        // wyswietlenie cech produktu
        if (!empty($wyswietl_cechy)) {                     
          $szczegoly .= $wyswietl_cechy;
        }

        // wyliczenie cen do wysietlenia na fakturze
        $ilosc                = $produkt['ilosc'];
        $cena_brutto          = $produkt['cena_koncowa_brutto'];
        $cena_netto           = $produkt['cena_netto'];
        $vat                  = $produkt['tax'];
        $vat_id               = $produkt['tax_id'];
        $vat_info             = $produkt['tax_info'];         
        $wartosc_brutto       = $waluty->PokazCeneBezSymbolu($cena_brutto * $ilosc);
        $wartosc_vat          = $waluty->PokazCeneBezSymbolu($wartosc_brutto * ( $vat / ( 100 + $vat ) ));
        $wartosc_netto        = $waluty->PokazCeneBezSymbolu($wartosc_brutto - $wartosc_vat);

        // obliczenie wartosci do podsumowania na fakturze
        $razem_wartosc_brutto = $razem_wartosc_brutto+$wartosc_brutto;
        $razem_wartosc_netto  = $razem_wartosc_netto+$wartosc_netto;
        $razem_wartosc_vat    = $razem_wartosc_vat+$wartosc_vat;

        $podatek_vat = '0|zw';
        
        // jezeli produkt ma przypisany id vat
        if ( $vat_id > 0 ) {
             //
             $podatek_vat = round($vat,0).'|'.$vat_info;
             //
           } else {
             //
             $sql_tmp = $db->open_query("SELECT * FROM tax_rates WHERE tax_rates_id = '".$produkt['tax_id']."'");
             if ((int)$db->ile_rekordow($sql_tmp) > 0) {
               $info_tmp = $sql_tmp->fetch_assoc();
               $podatek_vat = $info_tmp['tax_rate'].'|'.$info_tmp['tax_short_description'];
             }
             $db->close_query($sql_tmp);
             unset($info_tmp);              
             //
        }               

        $pola['produkty'][] = array('orders_id' => $filtr->process($_GET['id_poz']),
                                    'invoices_products_name' => $szczegoly,
                                    'invoices_products_pkwiu' => $produkt['pkwiu'],
                                    'invoices_products_jm' => ( $produkt['jm'] != '0' && $produkt['jm'] != '' ? $produkt['jm'] : $jednostka_domyslna ),
                                    'invoices_products_quantity' => $produkt['ilosc'],
                                    'invoices_products_price' => $cena_netto,
                                    'invoices_products_price_tax' => $cena_brutto,
                                    'invoices_products_tax' => $podatek_vat,
                                    'invoices_total_price' => $wartosc_netto,
                                    'invoices_total_price_tax' => $wartosc_brutto,
                                    'invoices_total_value_tax' => $wartosc_vat);

        // obliczenie wartosc do podsumowania dla roznych stawek VAT
        for ( $x = 0, $cnt = count($tablica_vat); $x < $cnt; $x++ ) {
        
          if ( $podatek_vat == $tablica_vat[$x]['id'] ) {
          
            if ( isset($podsumowanie_tablica[$x]) && count($podsumowanie_tablica[$x]) > 0 ) {
            
              $podsumowanie_tablica[$x] = array('stawka_vat' => substr($tablica_vat[$x]['id'],strpos($tablica_vat[$x]['id'], '|')+1),
                                                'razem_wartosc_brutto' => $podsumowanie_tablica[$x]['razem_wartosc_brutto'] +  $wartosc_brutto,
                                                'razem_wartosc_netto' => $podsumowanie_tablica[$x]['razem_wartosc_netto'] +  $wartosc_netto,
                                                'razem_wartosc_vat' => $podsumowanie_tablica[$x]['razem_wartosc_vat'] +  $wartosc_vat);
                                                
            } else {
            
              $podsumowanie_tablica[$x] = array(
                                            'stawka_vat' => substr($tablica_vat[$x]['id'],strpos($tablica_vat[$x]['id'], '|')+1),
                                            'razem_wartosc_brutto' => $wartosc_brutto,
                                            'razem_wartosc_netto' => $wartosc_netto,
                                            'razem_wartosc_vat' => $wartosc_vat);
                                            
            }
            
          }
          
        }
        
      }

      // dopisanie dodatkowych pozycji z zamowienia poza produktami majacych wplyw na wartosc podsumowania
      $dostawa_cena_brutto = 0;
      $dostawa_cena_netto  = 0;
      $dostawa_nazwa = 'Dostawa';

      foreach ( $zamowienie->podsumowanie as $dodatki ) {

        if ( $dodatki['klasa'] != 'ot_subtotal' && $dodatki['prefix'] != '9' && $dodatki['prefix'] != '0' ) {

            $ilosc = '1';

            if ($dodatki['klasa'] !=  'ot_shipping' && $dodatki['klasa'] !=  'ot_payment' ) {

                $vat                  = $dodatki['vat_stawka'];
                $podatek_vat          = round($dodatki['vat_stawka'],0) . '|' . $dodatki['vat_info'];

                $cena_brutto          = $dodatki['wartosc'];
                $cena_netto           = $waluty->PokazCeneBezSymbolu($dodatki['wartosc'] - ($dodatki['wartosc'] * ( $vat / ( 100 + $vat ) )));

                $wartosc_brutto       = $waluty->PokazCeneBezSymbolu($cena_brutto * $ilosc);
                $wartosc_vat          = $waluty->PokazCeneBezSymbolu($wartosc_brutto * ( $vat / ( 100 + $vat ) ));
                $wartosc_netto        = $waluty->PokazCeneBezSymbolu($wartosc_brutto - $wartosc_vat);

                $razem_wartosc_brutto = $razem_wartosc_brutto + $wartosc_brutto;
                $razem_wartosc_netto  = $razem_wartosc_netto + $wartosc_netto;
                $razem_wartosc_vat    = $razem_wartosc_vat + $wartosc_vat;

                for ( $x = 0, $cnt = count($tablica_vat); $x < $cnt; $x++ ) {
                
                    if ( $podatek_vat == $tablica_vat[$x]['id'] ) {
                    
                        if ( isset($podsumowanie_tablica[$x]) && count($podsumowanie_tablica[$x]) > 0 ) {
                        
                            $podsumowanie_tablica[$x] = array('stawka_vat' => substr($tablica_vat[$x]['id'],strpos($tablica_vat[$x]['id'], '|')+1),
                                                              'razem_wartosc_brutto' => $podsumowanie_tablica[$x]['razem_wartosc_brutto'] +  $wartosc_brutto,
                                                              'razem_wartosc_netto' => $podsumowanie_tablica[$x]['razem_wartosc_netto'] +  $wartosc_netto,
                                                              'razem_wartosc_vat' => $podsumowanie_tablica[$x]['razem_wartosc_vat'] +  $wartosc_vat);
                                                              
                        } else {
                        
                            $podsumowanie_tablica[$x] = array('stawka_vat' => substr($tablica_vat[$x]['id'],strpos($tablica_vat[$x]['id'], '|')+1),
                                                              'razem_wartosc_brutto' => $wartosc_brutto,
                                                              'razem_wartosc_netto' => $wartosc_netto,
                                                              'razem_wartosc_vat' => $wartosc_vat);
                                                              
                        }
                        
                    }
                    
                }

                $pola['produkty'][] = array('orders_id' => $filtr->process($_GET['id_poz']),
                                            'invoices_products_name' => $dodatki['tytul'],
                                            'invoices_products_pkwiu' => '',
                                            'invoices_products_jm' => $jednostka_domyslna,
                                            'invoices_products_quantity' => $ilosc,
                                            'invoices_products_price' => $cena_netto,
                                            'invoices_products_price_tax' => $cena_brutto,
                                            'invoices_products_tax' => $podatek_vat,
                                            'invoices_total_price' => $wartosc_netto,
                                            'invoices_total_price_tax' => $wartosc_brutto,
                                            'invoices_total_value_tax' => $wartosc_vat);

          } else {

              if ( $dodatki['klasa'] ==  'ot_shipping' ) {
                  $dostawa_nazwa = $dodatki['tytul'];
                  $dostawa_vat_id = $dodatki['vat_id'];
                  $dostawa_vat_stawka = $dodatki['vat_stawka'];
                  $dostawa_vat_info = $dodatki['vat_info'];                
              }
              $dostawa_cena_brutto += $dodatki['wartosc'];
          
          }

        }

        if ( $dodatki['prefix'] == '0' ) {

            $rabat_vat            = $vat_domyslny['stawka'];       
          
            $rabat_ilosc          = '1';
            $rabat_cena_brutto    = $dodatki['wartosc'];
            $rabat_cena_netto     = $waluty->PokazCeneBezSymbolu($dodatki['wartosc'] - ($dodatki['wartosc'] * ( $rabat_vat / ( 100 + $rabat_vat ) )));
          
            $rabat_wartosc_brutto = $waluty->PokazCeneBezSymbolu($rabat_cena_brutto * $rabat_ilosc);
            $rabat_wartosc_vat    = $waluty->PokazCeneBezSymbolu($rabat_wartosc_brutto * ( $rabat_vat / ( 100 + $rabat_vat ) ));
            $rabat_wartosc_netto  = $waluty->PokazCeneBezSymbolu($rabat_wartosc_brutto - $rabat_wartosc_vat);

            $pola['rabat'][$dodatki['tytul']] = array('orders_id' => $filtr->process($_GET['id_poz']),
                                                      'tytul' => $filtr->process($dodatki['tytul']),
                                                      'invoices_rabat_value_tax' => $rabat_wartosc_brutto,
                                                      'invoices_rabat' => $rabat_wartosc_netto,
                                                      'invoices_rabat_tax' => $rabat_wartosc_brutto);
                          
        }

      }
      
      unset($vat_domyslny);

      if ( $dostawa_cena_brutto > 0 ) {

          $dostawa_vat = '23';
          $dostawa_podatek_vat = '23|23';
          
          if ( isset($dostawa_vat_id) && (int)$dostawa_vat_id > 0 ) {
               $dostawa_vat = $dostawa_vat_stawka;
               $dostawa_podatek_vat = round($dostawa_vat_stawka,0) . '|' . $dostawa_vat_info;
          }
          
          unset($dostawa_vat_stawka, $dostawa_vat_id, $dostawa_vat_info);

          $dostawa_wartosc_brutto       = $waluty->PokazCeneBezSymbolu($dostawa_cena_brutto);
          $dostawa_wartosc_vat          = $waluty->PokazCeneBezSymbolu($dostawa_cena_brutto * ( $dostawa_vat / ( 100 + $dostawa_vat ) ));
          $dostawa_wartosc_netto        = $waluty->PokazCeneBezSymbolu($dostawa_wartosc_brutto - $dostawa_wartosc_vat);

          $dostawa_cena_netto           = $waluty->PokazCeneBezSymbolu($dostawa_wartosc_brutto - ($dostawa_wartosc_brutto * ( $dostawa_vat / ( 100 + $dostawa_vat ) )));
          $dostawa_cena_brutto          = $waluty->PokazCeneBezSymbolu($dostawa_wartosc_brutto);

          $razem_wartosc_brutto = $razem_wartosc_brutto + $dostawa_wartosc_brutto;
          $razem_wartosc_netto  = $razem_wartosc_netto + $dostawa_wartosc_netto;
          $razem_wartosc_vat    = $razem_wartosc_vat + $dostawa_wartosc_vat;

          for ( $x = 0, $cnt = count($tablica_vat); $x < $cnt; $x++ ) {
          
              if ( $dostawa_podatek_vat == $tablica_vat[$x]['id'] ) {
              
                  if ( isset($podsumowanie_tablica[$x]) && count($podsumowanie_tablica[$x]) > 0 ) {
                  
                      $podsumowanie_tablica[$x] = array('stawka_vat' => substr($tablica_vat[$x]['id'],strpos($tablica_vat[$x]['id'], '|')+1),
                                                        'razem_wartosc_brutto' => $podsumowanie_tablica[$x]['razem_wartosc_brutto'] +  $dostawa_wartosc_brutto,
                                                        'razem_wartosc_netto' => $podsumowanie_tablica[$x]['razem_wartosc_netto'] +  $dostawa_wartosc_netto,
                                                        'razem_wartosc_vat' => $podsumowanie_tablica[$x]['razem_wartosc_vat'] +  $dostawa_wartosc_vat);
                                                        
                  } else {
                  
                      $podsumowanie_tablica[$x] = array('stawka_vat' => substr($tablica_vat[$x]['id'],strpos($tablica_vat[$x]['id'], '|')+1),
                                                        'razem_wartosc_brutto' => $dostawa_wartosc_brutto,
                                                        'razem_wartosc_netto' => $dostawa_wartosc_netto,
                                                        'razem_wartosc_vat' => $dostawa_wartosc_vat);
                                                        
                  }
              }
          }

          $pola['produkty'][] = array('orders_id' => $filtr->process($_GET['id_poz']),
                                      'invoices_products_name' => $dostawa_nazwa,
                                      'invoices_products_pkwiu' => '',
                                      'invoices_products_jm' => $jednostka_domyslna,
                                      'invoices_products_quantity' => '1',
                                      'invoices_products_price' => $dostawa_cena_netto,
                                      'invoices_products_price_tax' => $dostawa_cena_brutto,
                                      'invoices_products_tax' => $dostawa_podatek_vat,
                                      'invoices_total_price' => $dostawa_wartosc_netto,
                                      'invoices_total_price_tax' => $dostawa_wartosc_brutto,
                                      'invoices_total_value_tax' => $dostawa_wartosc_vat);
      }

      $pola['suma'] = array('orders_id' => $filtr->process($_GET['id_poz']),
                            'invoices_tax' => 'x',
                            'invoices_total_value_tax' => $razem_wartosc_vat,
                            'invoices_total' => '0',
                            'invoices_total_tax' => $razem_wartosc_brutto);

      ksort($podsumowanie_tablica);
      
      foreach ( $podsumowanie_tablica as $pozycja) {
      
        $razemVat   = round(($pozycja['razem_wartosc_brutto'] * ( $pozycja['stawka_vat'] / ( 100 + $pozycja['stawka_vat']))),2);
        $razemNetto = round(($pozycja['razem_wartosc_brutto'] - $razemVat),2);

        $pola['suma_rozbicie'][] = array('orders_id' => $filtr->process($_GET['id_poz']),
                                         'invoices_tax' => $pozycja['stawka_vat'],
                                         'invoices_total_value_tax' => $razemVat,
                                         'invoices_total' => $razemNetto,
                                         'invoices_total_tax' => $pozycja['razem_wartosc_brutto']);
                                         
      }
      
      return $pola;
  }
 
}
?>