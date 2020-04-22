<?php
chdir('../');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

  $wynikDoAjaxa = '';
  $wynik = Array();

  $allegro = new Allegro(true, true);

  // utworzenie tablicy krajow 
  $tablica_krajow = $allegro->doGetCountries();
  $tablica_k = array();

  for ( $k = 0, $c = count($tablica_krajow); $k < $c; $k++ ) {
  
    $pomocnicza = Funkcje::object2array($tablica_krajow[$k]);
    $tablica_k[$pomocnicza['country-id']] = $pomocnicza['country-name'];
    
  }

  // wybranie porcji 25 aukcji z bazy do synchronizacji 
  $zapytanie = "SELECT allegro_id, auction_id, products_id, auction_status FROM allegro_auctions WHERE DATE_SUB(CURDATE(),INTERVAL 180 DAY) <= products_date_end LIMIT ".$_POST['limit']." OFFSET ".$_POST['offset']." ";
  $sql = $db->open_query($zapytanie);

  // utworzenie tablicy aukcji do pobrania z Allegro
  $aukcje = array();
  
  if ( $db->ile_rekordow($sql) > 0 ) {
  
    while ($info = $sql->fetch_assoc()) {
    
      if ( $info['auction_status'] == '-1' ) {
      
        $numer_aukcji_oczekujacej = $allegro->doVerifyItem( $info['auction_id'] );
        
        if ( is_array($numer_aukcji_oczekujacej) ) {
        
          $pola = array(
                  array('auction_id',$numer_aukcji_oczekujacej['item-id']),
                  array('auction_status','1'));
                  
          $db->update_query('allegro_auctions' , $pola, " allegro_id = '".$info['allegro_id']."'");	
          unset($pola);
          
          $aukcje[] = floatval($numer_aukcji_oczekujacej['item-id']);
          
          unset($numer_aukcji_oczekujacej);

        }
        
      } else {
      
        $aukcje[] = floatval($info['auction_id']);
        
      }
    }
    
  }
  $db->close_query($sql);
  unset($zapytanie, $info, $sql);

  // pobranie szczegolow aukcji
  foreach ( array_chunk( $aukcje, 25 ) as $chunk ) {
  
    if ( count($chunk) > 0 ) {
        $wynik[] = $allegro->doGetItemsInfo( $chunk, '0', '0' );
        $tranzakcje_tmp[] = $allegro->doGetTransactionsIDs( $chunk, 'seller' );
    }
    
  }

  if ( !empty($wynik['0']) ) {

    $formularze = Array();
    $tranzakcje = Array();
    $tranzakcje = $tranzakcje_tmp['0'];

    foreach ( array_chunk( $tranzakcje, 25 ) as $chunk_trans ) {
    
      if ( count($chunk_trans) > 0 ) {
        $formularze[] = $allegro->doGetPostBuyFormsDataForSellers( $chunk_trans );
      }
      
    }

    for ( $i = 0, $c = count($wynik['0']['array-item-list-info']); $i < $c; $i++ ) {
      $tablica_tmp  = Funkcje::object2array($wynik['0']['array-item-list-info'][$i]);
      $tablica_aukcja[] = Funkcje::object2array($tablica_tmp['item-info']);
    }
    
    unset($tablica_tmp);

    $tablica_aukcji = array();

    // aktualizacja informacji o aukcjach w bazie danych
    while (list($key, $value) = each($tablica_aukcja)) {

      $pola = array(
              array('auction_quantity',(int)$value['it-quantity']),
              array('auction_date_start',date("Y-m-d H:i:s",$value['it-starting-time'])),
              array('auction_date_end',date("Y-m-d H:i:s",$value['it-ending-time'])),
              array('products_start_price',$value['it-starting-price']),
              array('products_now_price',$value['it-price']),
              array('products_min_price',$value['it-reserve-price']),
              array('products_buy_now_price',$value['it-buy-now-price']),
              array('auction_bids',(int)$value['it-bid-count']),
              array('auction_hits',(int)$value['it-hit-count']),
              array('auction_status',(int)$value['it-ending-info']),
              array('products_sold',$value['it-starting-quantity'] - $value['it-quantity']),
              array('synchronization',0));
              
        if ( $value['it-ending-info'] == '1' ) {
          $pola[] = array('auction_buy_now',(int)$value['it-buy-now-active']);
        }

        $db->update_query('allegro_auctions' , $pola, " auction_id = '".$value['it-id']."'");	
        unset($pola);

        $link = '';
        if ( isset($_POST['serwer']) && $_POST['serwer'] == 'nie' ) {
          $link = 'http://allegro.pl/item' .  $value['it-id'] . '_webapi.html';
        } else {
          $link = 'http://allegro.pl.webapisandbox.pl/show_item.php?item='.$value['it-id'];
        }                          
        
        $wynikDoAjaxa .= '<a href="' . $link . '">' . $value['it-id'] . '</a> - aukcja została przetworzona<br />';
        
        unset($link);        
        
        // pobranie informacji o kupujacych w danej aukcji
        if ( $value['it-bid-count'] > 0 ) {
        
          $tablica_ofert = $allegro->doGetBidItem2( $value['it-id'] );

          for ( $i = 0, $c = count($tablica_ofert); $i < $c; $i++ ) {

            $tablica_tmp  = Funkcje::object2array($tablica_ofert[$i]);
            while (list($key, $value) = each($tablica_tmp)) {

              $id_aukcji     = $value['0'];
              $id_kupujacego = $value['1'];
              $data_zakupu   = $value['7'];
              $zapytanie_k = "SELECT allegro_auction_id, auction_id, buyer_id FROM allegro_auctions_sold WHERE auction_id = '".$id_aukcji."' AND buyer_id = '".$id_kupujacego."'";
              $sqlk = $db->open_query($zapytanie_k);

              $tablica_aukcji[] = floatval($id_aukcji);

              if ( $db->ile_rekordow($sqlk) == 0 ) {
              
                $pola = array(
                        array('auction_id',$value['0']),
                        array('buyer_id',$value['1']),
                        array('buyer_name',$value['2']),
                        array('buyer_status',(int)$value['4']),
                        array('auction_quantity',(int)$value['5']),
                        array('auction_price',$value['6']),
                        array('auction_buy_date',$value['7']),
                        array('auction_status',(int)$value['8']),
                        array('auction_lost_date',$value['9']),
                        array('auction_lost_status',$value['11']),
                        array('auction_lost_text',$value['10']),
                        array('date_last_modified','now()'));
                        
                $db->insert_query('allegro_auctions_sold' , $pola);	
                
              } else {
              
                $infok = $sqlk->fetch_assoc();
                
                $pola = array(
                        array('buyer_status',(int)$value['4']),
                        array('auction_status',(int)$value['8']),
                        array('auction_quantity',(int)$value['5']),
                        array('auction_price',$value['6']),
                        array('auction_buy_date',$value['7']),
                        array('auction_lost_date',$value['9']),
                        array('auction_lost_status',$value['11']),
                        array('auction_lost_text',$value['10']),
                        array('date_last_modified','now()'));
                        
                $db->update_query('allegro_auctions_sold' , $pola, " allegro_auction_id = '".$infok['allegro_auction_id']."'");	

              }
              unset($sqlk, $infok, $pola);

            }
            unset($tablica_tmp);

          }
          
        }
        
    }

    // pobranie adresu kupujacych
    if ( count($tablica_aukcji) > 0 ) {
    
      foreach ( array_chunk( $tablica_aukcji, 25 ) as $chunk_aukcje ) {
      
        if ( count($chunk_aukcje) > 0 ) {

          $kupujacy_info_array = array();
          $kupujacy_info_array = $allegro->doGetPostBuyData( $chunk_aukcje );
          
          for ( $i = 0, $c = count($kupujacy_info_array); $i < $c; $i++ ) {
          
            $kupujacy_info = Funkcje::object2array($kupujacy_info_array[$i]);
            $kupujacy_info1 = $kupujacy_info['users-post-buy-data'];
            
            for ( $j = 0, $t = count($kupujacy_info1); $j < $t; $j++ ) {

              $kupujacy_info2 = Funkcje::object2array($kupujacy_info1[$j]);
              $kupujacy_kontakt = Funkcje::object2array($kupujacy_info2['user-data']);
              $kupujacy_dostawa = Funkcje::object2array($kupujacy_info2['user-sent-to-data']);

              $pola = array(
                      array('buyer_email_address',$kupujacy_kontakt['user-email']),
                      array('buyer_first_name',$kupujacy_kontakt['user-first-name']),
                      array('buyer_last_name',$kupujacy_kontakt['user-last-name']),
                      array('buyer_company',$kupujacy_kontakt['user-company']),
                      array('buyer_street',$kupujacy_kontakt['user-address']),
                      array('buyer_postcode',$kupujacy_kontakt['user-postcode']),
                      array('buyer_city',$kupujacy_kontakt['user-city']),
                      array('buyer_phone',$kupujacy_kontakt['user-phone']),
                      array('buyer_state_id',$kupujacy_kontakt['user-state-id']),
                      array('buyer_country',$tablica_k[$kupujacy_kontakt['user-country-id']]),
                      array('buyer_shipping_first_name',$kupujacy_dostawa['user-first-name']),
                      array('buyer_shipping_last_name',$kupujacy_dostawa['user-last-name']),
                      array('buyer_shipping_company',$kupujacy_dostawa['user-company']),
                      array('buyer_shipping_street',$kupujacy_dostawa['user-address']),
                      array('buyer_shipping_postcode',$kupujacy_dostawa['user-postcode']),
                      array('buyer_shipping_city',$kupujacy_dostawa['user-city']),
                      array('buyer_shipping_country',( $kupujacy_dostawa['user-country-id'] != '' && $kupujacy_dostawa['user-country-id'] != '0' ? $tablica_k[$kupujacy_dostawa['user-country-id']] : '' )));
                      
              $db->update_query('allegro_auctions_sold' , $pola, "  buyer_id = '".$kupujacy_kontakt['user-id']."'");	

            }
            
          }
          
          unset($kupujacy_info_array);

        }
        
      }

    }

    // pobranie formularzy pozakupowych wypelnionych przez kupujacych
    if ( count($formularze) > 0 ) {
    
      for ( $i = 0, $c = count($formularze['0']); $i < $c; $i++ ) {

        $tablica_formularzy = Funkcje::object2array($formularze['0'][$i]);

        $aukcje_id                 = '';
        $kupujacy_id               = $tablica_formularzy['post-buy-form-buyer-id'];
        $tablica_formularz_aukcje  = Funkcje::object2array($tablica_formularzy['post-buy-form-items']);
        $tablica_formularz_faktura = Funkcje::object2array($tablica_formularzy['post-buy-form-invoice-data']);
        $tablica_formularz_wysylka = Funkcje::object2array($tablica_formularzy['post-buy-form-shipment-address']);

        for ( $j = 0, $t = count($tablica_formularz_aukcje); $j < $t; $j++ ) {
        
          $tablica_pojedyncza_aukcja = Funkcje::object2array($tablica_formularz_aukcje[$j]);
          $id_aukcji = $tablica_pojedyncza_aukcja['post-buy-form-it-id'];

          $pola = array(
                  array('auction_postbuy_forms','1'));
                  
          $db->update_query('allegro_auctions_sold' , $pola, " auction_id = '".$id_aukcji."' AND buyer_id = '".$kupujacy_id."'");	

          $zapytanie_t = "SELECT auction_id, transaction_id, buyer_id FROM allegro_transactions WHERE transaction_id = '".$tablica_formularzy['post-buy-form-id']."' AND buyer_id = '".$tablica_formularzy['post-buy-form-buyer-id']."' AND auction_id = '".$id_aukcji."'";
          $sqlt = $db->open_query($zapytanie_t);

          if ( $db->ile_rekordow($sqlt) == 0 ) {

            $pola = array(
                    array('auction_id',$id_aukcji),
                    array('transaction_id',$tablica_formularzy['post-buy-form-id']),
                    array('buyer_id',$tablica_formularzy['post-buy-form-buyer-id']),
                    array('post_buy_form_amount',$tablica_formularzy['post-buy-form-amount']),

                    array('post_buy_form_it_quantity',$tablica_pojedyncza_aukcja['post-buy-form-it-quantity']),
                    array('post_buy_form_it_amount',$tablica_pojedyncza_aukcja['post-buy-form-it-amount']),

                    array('post_buy_form_postage_amount',$tablica_formularzy['post-buy-form-postage-amount']),
                    array('post_buy_form_invoice_option',$tablica_formularzy['post-buy-form-invoice-option']),
                    array('post_buy_form_msg_to_seller',$tablica_formularzy['post-buy-form-msg-to-seller']),

                    array('billing_post_buy_form_adr_country',$tablica_formularz_faktura['post-buy-form-adr-country']),
                    array('billing_post_buy_form_adr_street',$tablica_formularz_faktura['post-buy-form-adr-street']),
                    array('billing_post_buy_form_adr_postcode',$tablica_formularz_faktura['post-buy-form-adr-postcode']),
                    array('billing_post_buy_form_adr_city',$tablica_formularz_faktura['post-buy-form-adr-city']),
                    array('billing_post_buy_form_adr_full_name',$tablica_formularz_faktura['post-buy-form-adr-full-name']),
                    array('billing_post_buy_form_adr_company',$tablica_formularz_faktura['post-buy-form-adr-company']),
                    array('billing_post_buy_form_adr_phone',$tablica_formularz_faktura['post-buy-form-adr-phone']),
                    array('billing_post_buy_form_adr_nip',$tablica_formularz_faktura['post-buy-form-adr-nip']),
                    array('billing_post_buy_form_adr_type',$tablica_formularz_faktura['post-buy-form-adr-type']),

                    array('shipping_post_buy_form_adr_country',$tablica_formularz_wysylka['post-buy-form-adr-country']),
                    array('shipping_post_buy_form_adr_street',$tablica_formularz_wysylka['post-buy-form-adr-street']),
                    array('shipping_post_buy_form_adr_postcode',$tablica_formularz_wysylka['post-buy-form-adr-postcode']),
                    array('shipping_post_buy_form_adr_city',$tablica_formularz_wysylka['post-buy-form-adr-city']),
                    array('shipping_post_buy_form_adr_full_name',$tablica_formularz_wysylka['post-buy-form-adr-full-name']),
                    array('shipping_post_buy_form_adr_company',$tablica_formularz_wysylka['post-buy-form-adr-company']),
                    array('shipping_post_buy_form_adr_phone',$tablica_formularz_wysylka['post-buy-form-adr-phone']),
                    array('shipping_post_buy_form_adr_nip',$tablica_formularz_wysylka['post-buy-form-adr-nip']),
                    array('shipping_post_buy_form_adr_type',$tablica_formularz_wysylka['post-buy-form-adr-type']),

                    array('post_buy_form_pay_type',$allegro->pokazPlatnosc($tablica_formularzy['post-buy-form-pay-type'])),
                    array('post_buy_form_pay_id',$tablica_formularzy['post-buy-form-pay-id']),
                    array('post_buy_form_pay_status',$tablica_formularzy['post-buy-form-pay-status']),
                    array('post_buy_form_date_init',$tablica_formularzy['post-buy-form-date-init']),
                    array('post_buy_form_payment_amount',$tablica_formularzy['post-buy-form-payment-amount']),
                    array('post_buy_form_shipment_id',$allegro->pokazDostawe($tablica_formularzy['post-buy-form-shipment-id'])));
                    
            $db->insert_query('allegro_transactions' , $pola);
          
          } else {
          
            $infot = $sqlt->fetch_assoc();

            $pola = array(
                    array('auction_id',$id_aukcji),
                    array('post_buy_form_amount',$tablica_formularzy['post-buy-form-amount']),

                    array('post_buy_form_it_quantity',$tablica_pojedyncza_aukcja['post-buy-form-it-quantity']),
                    array('post_buy_form_it_amount',$tablica_pojedyncza_aukcja['post-buy-form-it-amount']),

                    array('post_buy_form_postage_amount',$tablica_formularzy['post-buy-form-postage-amount']),
                    array('post_buy_form_invoice_option',$tablica_formularzy['post-buy-form-invoice-option']),
                    array('post_buy_form_msg_to_seller',$tablica_formularzy['post-buy-form-msg-to-seller']),

                    array('billing_post_buy_form_adr_country',$tablica_formularz_faktura['post-buy-form-adr-country']),
                    array('billing_post_buy_form_adr_street',$tablica_formularz_faktura['post-buy-form-adr-street']),
                    array('billing_post_buy_form_adr_postcode',$tablica_formularz_faktura['post-buy-form-adr-postcode']),
                    array('billing_post_buy_form_adr_city',$tablica_formularz_faktura['post-buy-form-adr-city']),
                    array('billing_post_buy_form_adr_full_name',$tablica_formularz_faktura['post-buy-form-adr-full-name']),
                    array('billing_post_buy_form_adr_company',$tablica_formularz_faktura['post-buy-form-adr-company']),
                    array('billing_post_buy_form_adr_phone',$tablica_formularz_faktura['post-buy-form-adr-phone']),
                    array('billing_post_buy_form_adr_nip',$tablica_formularz_faktura['post-buy-form-adr-nip']),
                    array('billing_post_buy_form_adr_type',$tablica_formularz_faktura['post-buy-form-adr-type']),

                    array('shipping_post_buy_form_adr_country',$tablica_formularz_wysylka['post-buy-form-adr-country']),
                    array('shipping_post_buy_form_adr_street',$tablica_formularz_wysylka['post-buy-form-adr-street']),
                    array('shipping_post_buy_form_adr_postcode',$tablica_formularz_wysylka['post-buy-form-adr-postcode']),
                    array('shipping_post_buy_form_adr_city',$tablica_formularz_wysylka['post-buy-form-adr-city']),
                    array('shipping_post_buy_form_adr_full_name',$tablica_formularz_wysylka['post-buy-form-adr-full-name']),
                    array('shipping_post_buy_form_adr_company',$tablica_formularz_wysylka['post-buy-form-adr-company']),
                    array('shipping_post_buy_form_adr_phone',$tablica_formularz_wysylka['post-buy-form-adr-phone']),
                    array('shipping_post_buy_form_adr_nip',$tablica_formularz_wysylka['post-buy-form-adr-nip']),
                    array('shipping_post_buy_form_adr_type',$tablica_formularz_wysylka['post-buy-form-adr-type']),

                    array('post_buy_form_pay_type',$allegro->pokazPlatnosc($tablica_formularzy['post-buy-form-pay-type'])),
                    array('post_buy_form_pay_id',$tablica_formularzy['post-buy-form-pay-id']),
                    array('post_buy_form_pay_status',$tablica_formularzy['post-buy-form-pay-status']),
                    array('post_buy_form_date_init',$tablica_formularzy['post-buy-form-date-init']),
                    array('post_buy_form_payment_amount',$tablica_formularzy['post-buy-form-payment-amount']),
                    array('post_buy_form_shipment_id',$allegro->pokazDostawe($tablica_formularzy['post-buy-form-shipment-id'])));

            $db->update_query('allegro_transactions' , $pola, " transaction_id = '".$tablica_formularzy['post-buy-form-id']."' AND buyer_id = '".$tablica_formularzy['post-buy-form-buyer-id']."' AND auction_id = '".$id_aukcji."'");	
            
          }
          
          unset($pola, $infot);
          
        }
        
      }
      
    }
    
  }

  // wybranie porcji 25 aukcji z bazy do synchronizacji
  $zapytanie_anul = "SELECT distinct transaction_id FROM allegro_transactions WHERE DATE_SUB(CURDATE(),INTERVAL 180 DAY) <= post_buy_form_date_init LIMIT ".$_POST['limit']." OFFSET ".$_POST['offset']." ";
  $sql_anul = $db->open_query($zapytanie_anul);

  // utworzenie tablicy tranzakcji do pobrania z Allegro
  $tranzakcje_anul = array();
  
  if ( $db->ile_rekordow($sql_anul) > 0 ) {
  
    while ($info_anul = $sql_anul->fetch_assoc()) {
      $tranzakcje_anul[] = floatval($info_anul['transaction_id']);
    }
    
  }
  
  $db->close_query($sql_anul);
  unset($zapytanie_anul, $info_anul, $sql_anul);

  // pobranie szczegolow tranzakcji anulowanych
  $tranzakcje1 = array();
  
  foreach ( array_chunk( $tranzakcje_anul, 25 ) as $chunk_anul ) {
  
    if ( count($chunk_anul) > 0 ) {
        $tranzakcje1[] = $allegro->doGetPostBuyFormsDataForSellers( $chunk_anul );
    }
    
  }

  if ( count($tranzakcje1) > 0 ) {
  
    for ( $i =0, $c = count($tranzakcje1['0']); $i < $c; $i++ ) {
    
        $tranzakcja = Funkcje::object2array($tranzakcje1['0'][$i]);
        $pola = array(
                array('post_buy_form_pay_status',$tranzakcja['post-buy-form-pay-status']),
                array('post_buy_form_pay_type',$allegro->pokazPlatnosc($tranzakcja['post-buy-form-pay-type'])),
                array('post_buy_form_shipment_id',$allegro->pokazDostawe($tranzakcja['post-buy-form-shipment-id'])));

        if ( $tranzakcja['post-buy-form-date-recv'] != '' ) {
          $pola[] = array('post_buy_form_date_init',$tranzakcja['post-buy-form-date-recv']);
        }
        
        if ( $tranzakcja['post-buy-form-date-cancel'] != '' ) {
          $pola[] = array('post_buy_form_date_init',$tranzakcja['post-buy-form-date-cancel']);
        }
        
        $db->update_query('allegro_transactions' , $pola, " transaction_id = '".$tranzakcja['post-buy-form-id']."'");	
    }
    
  }

  unset($chunk, $chunk_trans, $chunk_anul);

  echo $wynikDoAjaxa;
  
}
?>