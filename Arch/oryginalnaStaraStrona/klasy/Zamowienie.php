<?php

class Zamowienie {

  var $info, $podsumowanie, $produkty, $klient, $dostawa, $platnik;

  function Zamowienie($id_zamowienia, $sortowanie = '') {
  
    $this->info                  = array();
    $this->podsumowanie          = array();
    $this->produkty              = array();
    $this->klient                = array();
    $this->dostawa               = array();
    $this->platnik               = array();
    $this->statusy               = array();
    $this->dostawy               = array();
    $this->sprzedaz_online       = false;
    $this->sprzedaz_online_pliki = array();
    $this->sprzedaz_online_link  = '/';
    $this->waga_produktow        = 0;

    $this->zapytanie($id_zamowienia, $sortowanie);
    
  }

  function zapytanie($id_zamowienia, $sortowanie) {

     $zapytanie_suma = "SELECT title, text, class, tax, tax_class_id, value, sort_order, orders_total_id, prefix FROM orders_total WHERE orders_id = '" . (int)$id_zamowienia . "' ORDER BY sort_order";
     $sql_suma = $GLOBALS['db']->open_query($zapytanie_suma);
     
     $ogolna_wartosc_zamowienia = 0;
     $wartosc_zamowienia        = 0;
     $vat_domyslny              = Funkcje::domyslnyPodatekVat(); 

     while ($info_suma = $sql_suma->fetch_assoc()) {
     
        // jezeli pozycja nie ma vat id to przypisze domyslny
        if ( (int)$info_suma['tax_class_id'] == 0 ) {
            //
            $info_suma['tax_class_id'] = $vat_domyslny['id'];
            $info_suma['tax'] = $vat_domyslny['stawka'];
            //
        }
     
        $tablica_vat = Produkty::PokazStawkeVAT($info_suma['tax_class_id'], true); 
            
        $this->podsumowanie[] = array('tytul'           => $info_suma['title'],
                                      'tekst'           => $info_suma['text'],
                                      'klasa'           => $info_suma['class'],
                                      'wartosc'         => $info_suma['value'],
                                      'vat_id'          => $info_suma['tax_class_id'],
                                      'vat_stawka'      => $info_suma['tax'],
                                      'vat_info'        => ((isset($tablica_vat['opis_krotki'])) ? $tablica_vat['opis_krotki'] : ''),                                      
                                      'sortowanie'      => $info_suma['sort_order'],
                                      'orders_total_id' => $info_suma['orders_total_id'],
                                      'prefix'          => $info_suma['prefix']);
                                
        if ($info_suma['class'] == 'ot_total') {
            $ogolna_wartosc_zamowienia = strip_tags($info_suma['text']);
            $wartosc_zamowienia        = $info_suma['value'];
        }
        
        unset($tablica_vat);
        
     }
     
     $GLOBALS['db']->close_query($sql_suma);
     
     unset($zapytanie_suma,$info_suma, $vat_domyslny);

     $zapytanie_zamowienie = "SELECT orders_id, customers_id, currency, currency_value, payment_method, payment_info, invoice_dokument, shipping_tax, date_purchased, orders_status, last_modified, shipping_module, shipping_info, shipping_module, reference, tracker_ip, service, customers_name, customers_company, customers_nip, customers_street_address, customers_city, customers_postcode, customers_state, customers_country, customers_telephone, customers_email_address, customers_dummy_account, delivery_name, delivery_company, delivery_nip, delivery_pesel, delivery_street_address, delivery_city, delivery_postcode, delivery_state, delivery_country, billing_name, billing_company, billing_nip, billing_pesel, billing_street_address,  billing_city, billing_postcode, billing_state, billing_country, orders_source, orders_file_shopping FROM orders WHERE orders_id = '" . (int)$id_zamowienia . "'";

     $sql_zamowienie = $GLOBALS['db']->open_query($zapytanie_zamowienie);
     $info_zamowienie = $sql_zamowienie->fetch_assoc();

     // domyslny jak klient nie ma jezyka                    
     $jezyk = '1';
     $zapytanie_jezyk = "SELECT c.customers_id, c.language_id, c.customers_id_private
                         FROM customers c WHERE c.customers_id = '".(int)$info_zamowienie['customers_id']."'";
                  
     $sql_jezyk = $GLOBALS['db']->open_query($zapytanie_jezyk);
     
     if ((int)$GLOBALS['db']->ile_rekordow($sql_jezyk) > 0) {
     
       $info_jezyk = $sql_jezyk->fetch_assoc();
       $jezyk = $info_jezyk['language_id'];
       $nrKlientaMagazyn = $info_jezyk['customers_id_private'];
       unset($info_jezyk);
       
     }
     
     $GLOBALS['db']->close_query($sql_jezyk);
     unset($zapytanie_jezyk);     

     // nazwa dokumentu sprzedazy
     $dokument = (($info_zamowienie['invoice_dokument'] == 0) ? 'DOKUMENT_SPRZEDAZY_PARAGON' : 'DOKUMENT_SPRZEDAZY_FAKTURA');
     $zapytanie_dokument = "select tv.translate_value, tc.translate_constant_id from translate_constant tc, translate_value tv where tv.translate_constant_id = tc.translate_constant_id and tc.translate_constant = '" . $dokument . "' and tv.language_id = '" . $jezyk . "'";
     
     $sql_dokument = $GLOBALS['db']->open_query($zapytanie_dokument);
     
     $info_dokument = $sql_dokument->fetch_assoc();
     $dokument_nazwa = $info_dokument['translate_value'];
     unset($info_dokument); 

     $GLOBALS['db']->close_query($sql_dokument);
     unset($info_dokument, $dokument);       

     $this->info = array('id_zamowienia'           => $info_zamowienie['orders_id'],
                         'waluta'                  => $info_zamowienie['currency'],
                         'waluta_kurs'             => $info_zamowienie['currency_value'],
                         'metoda_platnosci'        => $info_zamowienie['payment_method'],
                         'platnosc_info'           => $info_zamowienie['payment_info'],
                         'dokument_zakupu'         => $info_zamowienie['invoice_dokument'],
                         'dokument_zakupu_nazwa'   => $dokument_nazwa,
                         'wysylka_vat'             => $info_zamowienie['shipping_tax'],
                         'data_zamowienia'         => $info_zamowienie['date_purchased'],
                         'status_zamowienia'       => $info_zamowienie['orders_status'],
                         'data_modyfikacji'        => $info_zamowienie['last_modified'],
                         'wysylka_modul'           => $info_zamowienie['shipping_module'],
                         'wysylka_info'            => $info_zamowienie['shipping_info'],
                         'referer'                 => $info_zamowienie['reference'],
                         'adres_ip'                => $info_zamowienie['tracker_ip'],
                         'opiekun'                 => $info_zamowienie['service'],
                         'typ_zamowienia'          => $info_zamowienie['orders_source'],
                         'wartosc_zamowienia'      => $ogolna_wartosc_zamowienia,
                         'wartosc_zamowienia_val'  => $wartosc_zamowienia,
                         'ilosc_pobran_plikow'     => $info_zamowienie['orders_file_shopping']);

     $this->klient = array('nazwa'                => $info_zamowienie['customers_name'],
                           'id'                   => $info_zamowienie['customers_id'],
                           'id_klienta_magazyn'   => $nrKlientaMagazyn,
                           'firma'                => $info_zamowienie['customers_company'],
                           'nip'                  => $info_zamowienie['customers_nip'],
                           'ulica'                => $info_zamowienie['customers_street_address'],
                           'miasto'               => $info_zamowienie['customers_city'],
                           'kod_pocztowy'         => $info_zamowienie['customers_postcode'],
                           'wojewodztwo'          => $info_zamowienie['customers_state'],
                           'kraj'                 => $info_zamowienie['customers_country'],
                           'telefon'              => $info_zamowienie['customers_telephone'],
                           'adres_email'          => $info_zamowienie['customers_email_address'],
                           'gosc'                 => $info_zamowienie['customers_dummy_account'],
                           'jezyk'                => $jezyk);
                           
     unset($jezyk);

     $this->dostawa = array('nazwa'               => $info_zamowienie['delivery_name'],
                            'firma'               => $info_zamowienie['delivery_company'],
                            'nip'                 => $info_zamowienie['delivery_nip'],
                            'pesel'               => $info_zamowienie['delivery_pesel'],
                            'ulica'               => $info_zamowienie['delivery_street_address'],
                            'miasto'              => $info_zamowienie['delivery_city'],
                            'kod_pocztowy'        => $info_zamowienie['delivery_postcode'],
                            'wojewodztwo'         => $info_zamowienie['delivery_state'],
                            'kraj'                => $info_zamowienie['delivery_country']);

     $this->platnik = array('nazwa'               => $info_zamowienie['billing_name'],
                            'firma'               => $info_zamowienie['billing_company'],
                            'nip'                 => $info_zamowienie['billing_nip'],
                            'pesel'               => $info_zamowienie['billing_pesel'],
                            'ulica'               => $info_zamowienie['billing_street_address'],
                            'miasto'              => $info_zamowienie['billing_city'],
                            'kod_pocztowy'        => $info_zamowienie['billing_postcode'],
                            'wojewodztwo'         => $info_zamowienie['billing_state'],
                            'kraj'                => $info_zamowienie['billing_country']);
                            
     $GLOBALS['db']->close_query($sql_zamowienie);
     unset($info_zamowienie, $sql_zamowienie, $zapytanie_zamowienie, $nrKlientaMagazyn);                               

     $zapytanie_dostawy = "SELECT orders_shipping_id, orders_shipping_type, orders_shipping_number, orders_shipping_weight, orders_parcels_quantity, orders_shipping_status, orders_shipping_date_created, orders_shipping_date_modified, orders_shipping_comments FROM orders_shipping WHERE orders_id = '" . (int)$id_zamowienia . "'";

     $sql_dostawy = $GLOBALS['db']->open_query($zapytanie_dostawy);

     if ((int)$GLOBALS['db']->ile_rekordow($sql_dostawy) > 0) {
     
       while ($info_dostawy = $sql_dostawy->fetch_assoc()) {

         $index_dostaw = $info_dostawy['orders_shipping_id'];
         $this->dostawy[$index_dostaw] = array('rodzaj_przesylki'    => $info_dostawy['orders_shipping_type'],
                                              'numer_przesylki'      => $info_dostawy['orders_shipping_number'],
                                              'waga_przesylki'       => $info_dostawy['orders_shipping_weight'],
                                              'ilosc_paczek'         => $info_dostawy['orders_parcels_quantity'],
                                              'status_przesylki'     => $info_dostawy['orders_shipping_status'],
                                              'data_utworzenia'      => $info_dostawy['orders_shipping_date_created'],
                                              'data_aktualizacji'    => $info_dostawy['orders_shipping_date_modified'],
                                              'komentarz'            => $info_dostawy['orders_shipping_comments']);
       }
       
     }
     
     $GLOBALS['db']->close_query($sql_dostawy);
     unset($info_dostawy, $sql_dostawy, $zapytanie_dostawy);           

     // sortowanie produktow - uzywane w zamowieniu PDF
     $sortowanieProduktow = 'op.products_name';
     if ( $sortowanie != '' ) {
        //
        switch ($sortowanie) {
            case 'nazwa produktu':
                $sortowanieProduktow = 'op.products_name';                             
                break; 
            case 'cena':
                $sortowanieProduktow = 'op.final_price_tax';                           
                break; 
            case 'numer katalogowy':
                $sortowanieProduktow = 'op.products_model';                    
                break; 
            case 'ilosc':
                $sortowanieProduktow = 'op.products_quantity, op.products_name';                    
                break;                  
        }           
        //
     }       

     $zapytanie_produkty = "SELECT
                            op.orders_products_id,
                            op.products_id,
                            op.products_name,
                            op.products_model,
                            op.products_pkwiu,
                            op.products_price,
                            op.products_price_tax,
                            op.products_tax,
                            op.products_tax_class_id,
                            op.products_quantity,
                            op.final_price,
                            op.final_price_tax,
                            op.products_id,
                            op.orders_id,
                            op.products_comments,
                            op.products_text_fields,
                            op.products_shipping_time,
                            op.products_warranty,
                            op.products_condition,
                            p.products_weight,
                            p.products_image,
                            p.products_id_private,
                            p.manufacturers_id,
                            p.products_currencies_id,
                            p.products_jm_id,
                            p.products_ean,
                            p.products_warranty_products_id,
                            p.products_condition_products_id,
                            p.products_shipping_time_id,
                            m.manufacturers_name
                            FROM orders_products op
                            LEFT JOIN products p ON op.products_id = p.products_id
                            LEFT JOIN manufacturers m ON p.manufacturers_id = m.manufacturers_id
                            WHERE orders_id = '" . (int)$id_zamowienia . "' ORDER BY " . $sortowanieProduktow;
                            
     unset($sortowanieProduktow);                              

     $sql_produkty = $GLOBALS['db']->open_query($zapytanie_produkty);

     while ($info_produkty = $sql_produkty->fetch_assoc()) {

        $index = $info_produkty['orders_products_id'];
        // 
        // okresla czy z przecinkiem czy bez
        if ( isset( $GLOBALS['jednostkiMiary'][ $info_produkty['products_jm_id'] ] ) ) {
             //
             // jezeli calkowite
             if ( $GLOBALS['jednostkiMiary'][ $info_produkty['products_jm_id'] ]['typ'] == 1 ) {
                  //
                  $info_produkty['products_quantity'] = (int)$info_produkty['products_quantity'];
                  //
             }
             //
        } else {
             //
             if ( $GLOBALS['jednostkiMiary'][0]['typ'] == 1 ) {
                  //
                  // sprawdzi czy wartosc ilosci nie jest ulamkowa
                  if ( (int)$info_produkty['products_quantity'] == $info_produkty['products_quantity'] ) {
                       $info_produkty['products_quantity'] = (int)$info_produkty['products_quantity'];
                  }
                  //
             }
             //
        }
        
        // jezeli nie ma id stawki vat
        if ( $info_produkty['products_tax_class_id'] == 0 ) {
            //
            $sql_tmp = $GLOBALS['db']->open_query("SELECT * FROM tax_rates WHERE tax_rate = '" . $info_produkty['products_tax'] . "'");
            $info_tmp = $sql_tmp->fetch_assoc();
            $info_produkty['products_tax_class_id'] = $info_tmp['tax_rates_id'];
            $GLOBALS['db']->close_query($sql_tmp);
            unset($info_tmp);               
            //
        }        
      
        // jezeli id produktu = 0 - reczne dodanie
        if ( $info_produkty['products_id'] == 0 ) {
            //
            // ustali id waluty
            $info_produkty['products_currencies_id'] = $_SESSION['domyslnaWaluta']['id'];
        }     
        
        $tablica_vat = Produkty::PokazStawkeVAT($info_produkty['products_tax_class_id'], true);    
        
        //
        $this->produkty[$index] = array('ilosc'               => $info_produkty['products_quantity'],
                                        'nazwa'               => $info_produkty['products_name'],
                                        'link'                => (($info_produkty['products_id'] > 0) ? '<a href="' . Seo::link_SEO( $info_produkty['products_name'], $info_produkty['products_id'], 'produkt' ) . '">' . $info_produkty['products_name'] . '</a>' : '<a>' . $info_produkty['products_name'] . '</a>'),
                                        'zdjecie_produktu'    => Funkcje::pokazObrazek($info_produkty['products_image'], $info_produkty['products_name'], '40', '40', array(), 'class="Zdjecie"', 'maly'),
                                        'zdjecie'             => $info_produkty['products_image'],
                                        'products_id'         => $info_produkty['products_id'],
                                        'id_produktu'         => $info_produkty['products_id'],
                                        'id_produktu_magazyn' => $info_produkty['products_id_private'],
                                        'model'               => $info_produkty['products_model'],
                                        'pkwiu'               => $info_produkty['products_pkwiu'],
                                        'ean'                 => $info_produkty['products_ean'],
                                        'gwarancja'           => ( $info_produkty['products_warranty'] != '' ? $info_produkty['products_warranty'] : '' ),
                                        'stan'                => ( $info_produkty['products_condition'] != ''  ? $info_produkty['products_condition'] : '' ),
                                        'czas_wysylki'        => ( $info_produkty['products_shipping_time'] != '' ? $info_produkty['products_shipping_time'] : '' ),
                                        'jm'                  => ( $info_produkty['products_jm_id'] == '' || $info_produkty['products_jm_id'] == '0' ? $GLOBALS['jednostkiMiary'][0]['id'] : $info_produkty['products_jm_id']),
                                        'tax'                 => $info_produkty['products_tax'],
                                        'tax_id'              => $info_produkty['products_tax_class_id'],
                                        'tax_info'            => ((isset($tablica_vat['opis_krotki'])) ? $tablica_vat['opis_krotki'] : ''),
                                        'cena_netto'          => $info_produkty['products_price'],
                                        'cena_koncowa_netto'  => $info_produkty['final_price'],
                                        'cena_brutto'         => $info_produkty['products_price_tax'],
                                        'cena_koncowa_brutto' => $info_produkty['final_price_tax'],
                                        'weight'              => $info_produkty['products_weight'],
                                        'komentarz'           => $info_produkty['products_comments'],
                                        'pola_txt'            => $info_produkty['products_text_fields'],
                                        'producent'           => $info_produkty['manufacturers_name'],
                                        'orders_products_id'  => $info_produkty['orders_products_id'],
                                        'id_waluty'           => $info_produkty['products_currencies_id']);
                                        
        unset($tablica_vat);

        $this->waga_produktow += $info_produkty['products_weight'] * $info_produkty['products_quantity'];

        $zapytanie_cechy = "SELECT * , p.options_values_weight
                            FROM orders_products_attributes pa
                            LEFT JOIN products_options po ON pa.products_options_id = po.products_options_id AND po.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            LEFT JOIN products_attributes p ON pa.products_options_id = p.options_id AND p.options_values_id = pa.products_options_values_id
                            WHERE orders_id = '" . (int)$id_zamowienia . "'
                            AND orders_products_id = '" . (int)$info_produkty['orders_products_id'] . "'  ORDER BY po.products_options_sort_order asc ";
                            
        $sql_cechy = $GLOBALS['db']->open_query($zapytanie_cechy);

        if ((int)$GLOBALS['db']->ile_rekordow($sql_cechy) > 0) {
        
          while ($info_cechy = $sql_cechy->fetch_assoc()) {

            $subindex = $info_cechy['products_options_id'];

            $this->produkty[$index]['attributes'][$subindex] = array('cecha'                         => $info_cechy['products_options'],
                                                                     'id_cechy'                      => $info_cechy['products_options_id'],
                                                                     'wartosc'                       => $info_cechy['products_options_values'],
                                                                     'id_wartosci'                   => $info_cechy['products_options_values_id'],
                                                                     'prefix'                        => $info_cechy['price_prefix'],
                                                                     'cena_netto'                    => $info_cechy['options_values_price'],
                                                                     'podatek'                       => $info_cechy['options_values_tax'],
                                                                     'cena_brutto'                   => $info_cechy['options_values_price_tax'],
                                                                     'orders_products_attributes_id' => $info_cechy['orders_products_attributes_id']);

            $this->waga_produktow += $info_cechy['options_values_weight'] * $info_produkty['products_quantity'];
            $subindex++;
          }
          
        }
        
        $GLOBALS['db']->close_query($sql_cechy);
        unset($info_cechy, $sql_cechy, $zapytanie_cechy);           
        
        // sprzedaz elektroniczna
        $zapytanie_online = "SELECT products_file_shopping_unique_id, products_file_shopping_name, products_file_shopping 
                               FROM products_file_shopping
                              WHERE products_id = '" . (int)$info_produkty['products_id'] . "' and language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";

        $sql_online = $GLOBALS['db']->open_query($zapytanie_online); 

        if ((int)$GLOBALS['db']->ile_rekordow($sql_online) > 0) {
        
          $this->sprzedaz_online = true;
        
          while ($info_online = $sql_online->fetch_assoc()) {

            // sprawdzi czy plik istnieje
            if ( file_exists($info_online['products_file_shopping']) ) { 
                //
                $this->sprzedaz_online_pliki[] = array('plik'        => $info_online['products_file_shopping'],
                                                       'nazwa_pliku' => $info_online['products_file_shopping_name'],
                                                       'id_pliku'    => $info_online['products_file_shopping_unique_id']);
                //
            }

          }
          
        }     

        $GLOBALS['db']->close_query($sql_online);
        unset($info_online, $sql_online, $zapytanie_online);           
                              
     }

     // generowanie linku do pobrania sprzedazy elektronicznej
     if ( $this->sprzedaz_online == true ) {
          //
          $UnikalnyLinkTablica = array( 'nr_zamowienia'   => (int)$id_zamowienia,
                                        'adres_email'     => $this->klient['adres_email'],
                                        'data_zamowienia' => $this->info['data_zamowienia'] );
          //
          $UnikalnyLink = base64_encode(serialize($UnikalnyLinkTablica));
          $UnikalnyLinkWynik = '';
          
          $Wstawki = array(10,20,35,40,52,60,71,92);
          $WstawkiZnaki = array('bbb','nnn','ttt','RRR','WWW','QQQ','OOO','ppp','VVV','NNN','ccc');
          
          for ( $r = 0; $r <= strlen($UnikalnyLink); $r++ ) {
                $UnikalnyLinkWynik .= substr($UnikalnyLink, strlen($UnikalnyLink) - $r, 1);
                //
                if ( in_array($r, $Wstawki) ) {
                     $UnikalnyLinkWynik .= $WstawkiZnaki[ rand(0,9) ];
                }
                //
          }
          
          unset($Wstawki, $WstawkiZnaki);
          //
          $this->sprzedaz_online_link = 'AAV' . $UnikalnyLinkWynik . '-d-' . (int)$id_zamowienia . '.html';
          //
          // dodanie linku do plikow
          for ( $f = 0; $f < count($this->sprzedaz_online_pliki); $f++ ) {
              //
              $KodowanyIdPliku = $this->sprzedaz_online_pliki[$f]['id_pliku'] * $this->sprzedaz_online_pliki[$f]['id_pliku'];
              //
              $TablicaCyfr = array(1,2,3,4,5,6,7,8,9,0);
              $TablicaLiter = array('b','v','q','w','c','g','z','t','u','d');
              $IdWynik = str_replace($TablicaCyfr, $TablicaLiter, $KodowanyIdPliku);
              //
              $this->sprzedaz_online_pliki[$f]['plik_pobrania'] = $this->sprzedaz_online_link . '/pobierz=' . $IdWynik;
              //
              unset($KodowanyIdPliku, $TablicaCyfr, $TablicaLiter, $IdWynik);
              //
          }
          //
     }
     
     $GLOBALS['db']->close_query($sql_produkty);
     unset($info_produkty, $sql_produkty, $zapytanie_produkty);        
     
     // usuwanie duplikatow plikow z tablicy plikow elektronicznych
     $this->sprzedaz_online_pliki = Funkcje::CzyscTabliceUnikalne($this->sprzedaz_online_pliki); 

     $zapytanie_statusy = "SELECT orders_status_history_id, orders_status_id, date_added, customer_notified, customer_notified_sms, comments FROM orders_status_history WHERE orders_id = '" . (int)$id_zamowienia . "' ORDER BY date_added DESC";
     $sql_statusy = $GLOBALS['db']->open_query($zapytanie_statusy);

     while ($info_statusy = $sql_statusy->fetch_assoc()) {

          $s = $info_statusy['orders_status_history_id'];
          
          $this->statusy[$s] = array('zamowienie_status_id' => $info_statusy['orders_status_history_id'],
                                     'status_id'            => $info_statusy['orders_status_id'],
                                     'status_nazwa'         => Funkcje::pokazNazweStatusuZamowienia($info_statusy['orders_status_id'], $_SESSION['domyslnyJezyk']['id']),
                                     'data_dodania'         => date('d-m-Y H:i:s',strtotime($info_statusy['date_added'])),
                                     'powiadomienie_mail'   => $info_statusy['customer_notified'],
                                     'powiadomienie_sms'    => $info_statusy['customer_notified_sms'],
                                     'komentarz'            => $info_statusy['comments']);
                                     
     }
     
     $GLOBALS['db']->close_query($sql_statusy);
     unset($info_statusy, $sql_statusy, $zapytanie_statusy);           

  }
    
  // funkcja generujaca dodatkowe pola do zamowien
  static public function pokazDodatkowePolaZamowienia($languages_id = '1' ) {
    global $i18n;

    $ciag_dodatkowych_pol ='';

    $dodatkowe_pola_zamowienia = "
      SELECT oe.fields_id, oe.fields_input_type, oe.fields_required_status, oei.fields_input_value, oei.fields_name, oe.fields_status, oe.fields_input_type, oe.fields_type 
        FROM orders_extra_fields oe, orders_extra_fields_info oei 
        WHERE oe.fields_status = '1' 
        AND oei.fields_id = oe.fields_id 
        AND oei.languages_id = '".$languages_id."'
        ORDER BY oe.fields_order";

    $sql = $GLOBALS['db']->open_query($dodatkowe_pola_zamowienia);

    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

      while ( $dodatkowePola = $sql->fetch_assoc() ) {

        $wartosc = '';

        $ciag_dodatkowych_pol .= '<p>';
        $ciag_dodatkowych_pol .= '<span>' . $dodatkowePola['fields_name'] . ': ' . (($dodatkowePola['fields_required_status' ]== 1) ? '<em class="required"></em>': '') . '</span>';

        $wartosci_pola_lista = explode("\n", $dodatkowePola['fields_input_value']);
        $wartosci_pola_tablica = array();
        
        foreach($wartosci_pola_lista as $wartosc_pola) {
          $wartosc_pola = trim($wartosc_pola);
          $wartosci_pola_tablica[] = array('id' => $wartosc_pola, 'text' => $wartosc_pola);
        }

        switch($dodatkowePola['fields_input_type']) {
          // Pole typu INPUT
          case 0:
            if ( $dodatkowePola['fields_type'] == 'kalendarz' ) {
                 $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required datefields"': 'class="datefields"').' size="30" />';
               } else {
                 $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" style="width:80%" />';
            }
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
            break;

          // Pole typu TEXTAREA
          case 1:
            $ciag_dodatkowych_pol .= '<textarea name="fields_' . $dodatkowePola['fields_id'].'" cols="40" style="width:80%" rows="4" id="fields_'.$dodatkowePola['fields_id'].'" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').'></textarea>';
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
            break;

          // Pole typu RADIO
          case 2:
            $cnt = 0;
            foreach($wartosci_pola_lista as $wartosc_pola) {
              $wartosc_pola = trim($wartosc_pola);
              $ciag_dodatkowych_pol .= '<input type="radio" value="'.htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8").'" name="fields_' . $dodatkowePola['fields_id'].'" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' /> ' . htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8") . '';

              $cnt++;
              if ( $cnt < count($wartosci_pola_lista) ) {
                $ciag_dodatkowych_pol .= '<br />';
              }
            }
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_JEDNA_OPCJE'] . '</label>';
            break;

          // Pole typu CHECKBOX
          case 3:
            $cnt = 0;
            foreach($wartosci_pola_lista as $wartosc_pola) {
              $wartosc_pola = trim($wartosc_pola);
              $ciag_dodatkowych_pol .= '<input type="checkbox"  value="'.htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8").'" name="fields_' . $dodatkowePola['fields_id'].'[]" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' /> ' . htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8");

              $cnt++;
              if ( $cnt < count($wartosci_pola_lista) ) {
                $ciag_dodatkowych_pol .= '<br />';
              }
            }
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'[]" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_OPCJE'] . '</label>';
            break;

          // Pole typu SELECT
          case 4:
              $ciag_dodatkowych_pol .= Funkcje::RozwijaneMenu('fields_' . $dodatkowePola['fields_id'], $wartosci_pola_tablica, '', ' style="width:80%"');
            break;

          default:
            $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" style="width:80%" />';
            break;
        }

        $ciag_dodatkowych_pol .= '</p>';
      }
      
       
    }
    $GLOBALS['db']->close_query($sql);
    
    unset($dodatkowe_pola_zamowienia, $dodatkowe_pola);   

    return $ciag_dodatkowych_pol;
  }      
    
}
?>
