<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);
if ($prot->wyswietlStrone) {

    $allegro  = new Allegro();
    $i18n     = new Translator($db, '1');
    $GLOBALS['tlumacz'] = $i18n->tlumacz( array('WYSYLKI','PODSUMOWANIE_ZAMOWIENIA','PLATNOSCI'), null, true );
    
    if ( isset($_POST['ajax']) && isset($_POST['tbl_danych']) ) {
         //
         $tablica_aukcji = explode('_', $_POST['tbl_danych']);
         //
         if ( count($tablica_aukcji) != 3 ) {
              //
              Funkcje::PrzekierowanieURL('allegro_sprzedaz.php');
              //
         }
         //
         $_GET['id_poz'] = $tablica_aukcji[0];
         $_GET['postform'] = $tablica_aukcji[1];
         $_GET['transaction_id'] = $tablica_aukcji[2];
         //
    }

    if ( !isset($_GET['id_poz']) ) {
      $_GET['id_poz'] = 0;
    }

    if ( isset($_GET['postform']) ) {

      if ( $_GET['postform'] == '1' ) {

        $zapytanie_tmp = "SELECT a.*, t.transaction_id, t.orders_id AS ordersId, t.post_buy_form_shipment_id, t.post_buy_form_postage_amount, t.post_buy_form_amount, t.post_buy_form_msg_to_seller FROM allegro_auctions_sold a LEFT JOIN allegro_transactions t ON t.auction_id = a.auction_id WHERE a.allegro_auction_id = '".(int)$_GET['id_poz']."' AND t.transaction_id= '".$_GET['transaction_id']."'";

      } elseif ( $_GET['postform'] == '0' ) {

        $zapytanie_tmp = "SELECT a.*, a.orders_id AS ordersId FROM allegro_auctions_sold a WHERE a.allegro_auction_id = '".(int)$_GET['id_poz']."'";

      }

      $sql_tmp = $db->open_query($zapytanie_tmp);

      if ( $db->ile_rekordow($sql_tmp) > 0 ) {
          
        while ($info_tmp = $sql_tmp->fetch_assoc()) {

            $numer_aukcji = $info_tmp['auction_id'];

            if ( $info_tmp['ordersId'] != '' && $info_tmp['ordersId'] != '0' ) {
            
                // jezeli jest ajax
                if ( isset($_POST['ajax']) ) {
                     //
                     $tekst = '<span>Dla aukcji o numerze: <b>' . $numer_aukcji . '</b> zamówienie było już wygenerowane - nie można tego wykonać powtórnie</span>';
                     //
                  } else {
                     //
                     $tekst = '<div class="ostrzezenie" style="margin:10px">Zamówienie już było wygenerowane - nie można tego wykonać powtórnie</div>';
                     //
                }
                              
            } else {

                //aktualizacja tablic customers* i address_book #############################################
                $zapytanie_cust = "SELECT customers_id FROM customers WHERE customers_email_address = '".$info_tmp['buyer_email_address']."'";
                $sql_cust = $db->open_query($zapytanie_cust);

                $zarejestrowany_uzytkownik = false;
                if ( $db->ile_rekordow($sql_cust) > 0 ) {
                  $info_cust =$sql_cust->fetch_assoc();
                  $zarejestrowany_uzytkownik = true;
                  $id_klienta_w_sklepie = $info_cust['customers_id'];
                } else {
                  $zakodowane_haslo = Funkcje::zakodujHaslo($info_tmp['buyer_id']);

                  $pola = array(
                          array('customers_nick',$info_tmp['buyer_name']),
                          array('customers_firstname',$info_tmp['buyer_first_name']),
                          array('customers_lastname',$info_tmp['buyer_last_name']),
                          array('customers_email_address',$info_tmp['buyer_email_address']),
                          array('customers_telephone',$info_tmp['buyer_phone']),
                          array('customers_fax',''),
                          array('customers_password',$zakodowane_haslo),
                          array('customers_newsletter','0'),
                          array('customers_discount','0'),
                          array('customers_groups_id','1'),
                          array('customers_status','0'),
                          array('customers_dod_info','klient z Allegro'),
                          array('customers_guest_account','1'),
                          array('language_id','1')
                  );
                  $db->insert_query('customers' , $pola);
                  $id_klienta_w_sklepie = $db->last_id_query();
                  unset($pola);

                  $pola = array(
                          array('customers_info_id',$id_klienta_w_sklepie),
                          array('customers_info_number_of_logons','0'),
                          array('customers_info_date_account_created','now()'),
                          array('customers_info_date_account_last_modified','now()')
                  );
                  $db->insert_query('customers_info' , $pola);
                  unset($pola);

                  $pola = array(
                          array('customers_id',$id_klienta_w_sklepie),
                          array('entry_company',$info_tmp['buyer_company']),
                          array('entry_nip',''),
                          array('entry_pesel',''),
                          array('entry_firstname',$info_tmp['buyer_first_name']),
                          array('entry_lastname',$info_tmp['buyer_last_name']),
                          array('entry_street_address',$info_tmp['buyer_street']),
                          array('entry_postcode',$info_tmp['buyer_postcode']),
                          array('entry_city',$info_tmp['buyer_city']),
                          array('entry_country_id','170'),
                          array('entry_zone_id',$info_tmp['buyer_state_id'])
                  );

                  $db->insert_query('address_book' , $pola);
                  $id_dodanej_pozycji = $db->last_id_query();
                  unset($pola);

                  $pola = array(
                          array('customers_default_address_id',$id_dodanej_pozycji)
                  );
                  $db->update_query('customers' , $pola, " customers_id = '".(int)$id_klienta_w_sklepie."'");
                  unset($pola);

                  // dane do newslettera
                  $db->delete_query('subscribers' , " customers_id = '".(int)$id_klienta_w_sklepie."'");   

                  $pola = array(
                          array('customers_id',$id_klienta_w_sklepie),
                          array('subscribers_email_address',$info_tmp['buyer_email_address']),
                          array('customers_newsletter','0'),
                          array('date_added','now()')
                  );

                  $db->insert_query('subscribers' , $pola);
                  unset($pola, $id_dodanej_pozycji);
                }
                #############################################################################################

                if ( $_GET['postform'] == '1' ) {

                  //aktualizacja tablicy orders
                  $zapytanie = "SELECT * FROM allegro_transactions WHERE auction_id = '".$info_tmp['auction_id']."' AND transaction_id = '".$_GET['transaction_id']."' ";
                  $sql = $db->open_query($zapytanie);

                  while ($info = $sql->fetch_assoc()) {

                    //aktualizacja tablicy orders
                    $pola_info = array(
                                 array('invoice_dokument',$info['post_buy_form_invoice_option']),
                                 array('customers_id',$id_klienta_w_sklepie),
                                 array('customers_name',$info['shipping_post_buy_form_adr_full_name']),
                                 array('customers_company',$info['shipping_post_buy_form_adr_company']),
                                 array('customers_nip',$info['shipping_post_buy_form_adr_nip']),
                                 array('customers_pesel',''),
                                 array('customers_street_address',$info['shipping_post_buy_form_adr_street']),
                                 array('customers_city',$info['shipping_post_buy_form_adr_city']),
                                 array('customers_postcode',$info['shipping_post_buy_form_adr_postcode']),
                                 array('customers_state',( $info_tmp['buyer_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_state_id']) : '' )),
                                 array('customers_country',$info_tmp['buyer_country']),
                                 array('customers_telephone',$info['shipping_post_buy_form_adr_phone']),
                                 array('customers_email_address',$info_tmp['buyer_email_address']),
                                 array('customers_dummy_account', ( $zarejestrowany_uzytkownik ? '0' : '1' )),
                                 array('last_modified','now()'),
                                 array('date_purchased',date('Y-m-d h:i:s', $info_tmp['auction_buy_date'])),
                                 array('orders_status',$allegro->polaczenie['CONF_ORDERS_STATUS']),
                                 array('orders_source','3'),
                                 array('currency',$_SESSION['domyslna_waluta']['kod']),
                                 array('currency_value',$_SESSION['domyslna_waluta']['przelicznik']),
                                 array('payment_method',$info['post_buy_form_pay_type']),
                                 array('payment_info',''),
                                 array('shipping_module',$info['post_buy_form_shipment_id']),
                                 array('shipping_info',''),
                                 array('reference','http://www.allegro.pl/item'.$info_tmp['auction_id'].'_webapi.html'));

                    $pola_dostawa = array(
                                    array('delivery_name',$info['shipping_post_buy_form_adr_full_name']),
                                    array('delivery_company',$info['shipping_post_buy_form_adr_company']),
                                    array('delivery_nip',$info['shipping_post_buy_form_adr_nip']),
                                    array('delivery_pesel',''),
                                    array('delivery_street_address',$info['shipping_post_buy_form_adr_street']),
                                    array('delivery_city',$info['shipping_post_buy_form_adr_city']),
                                    array('delivery_postcode',$info['shipping_post_buy_form_adr_postcode']),
                                    array('delivery_state', ( $info_tmp['buyer_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_state_id']) : '' ) ),
                                    array('delivery_country',( $info_tmp['buyer_shipping_country'] != '' ? $info_tmp['buyer_shipping_country'] : $info_tmp['buyer_country'])));

                    if ( $info['billing_post_buy_form_adr_type'] == '0' && $info['billing_post_buy_form_adr_country'] == '0' ) {
                    
                      $pola_platnik = array(
                                      array('billing_name',$info['shipping_post_buy_form_adr_full_name']),
                                      array('billing_company',$info['shipping_post_buy_form_adr_company']),
                                      array('billing_nip',$info['shipping_post_buy_form_adr_nip']),
                                      array('billing_pesel',''),
                                      array('billing_street_address',$info['shipping_post_buy_form_adr_street']),
                                      array('billing_city',$info['shipping_post_buy_form_adr_city']),
                                      array('billing_postcode',$info['shipping_post_buy_form_adr_postcode']),
                                      array('billing_state', ( $info_tmp['buyer_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_state_id']) : '' ) ),
                                      array('billing_country',$info_tmp['buyer_country']));
                                      
                    } else {
                    
                      $pola_platnik = array(
                                      array('billing_name',$info['billing_post_buy_form_adr_full_name']),
                                      array('billing_company',$info['billing_post_buy_form_adr_company']),
                                      array('billing_nip',$info['billing_post_buy_form_adr_nip']),
                                      array('billing_pesel',''),
                                      array('billing_street_address',$info['billing_post_buy_form_adr_street']),
                                      array('billing_city',$info['billing_post_buy_form_adr_city']),
                                      array('billing_postcode',$info['billing_post_buy_form_adr_postcode']),
                                      array('billing_state', ( $info_tmp['buyer_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_state_id']) : '' )));
                                      
                    }

                    $pola = Array();
                    $pola = array_merge( $pola_info, $pola_dostawa, $pola_platnik );

                    $db->insert_query('orders' , $pola);
                    $id_dodanej_pozycji_zamowienie = $db->last_id_query();
                    unset($pola);

                    // wyszukanie aukcji do wybranej tranzakcji
                    $zapytanie_aukcje = "
                    SELECT transaction_id, auction_id, post_buy_form_it_amount
                      FROM allegro_transactions
                      WHERE transaction_id='".$_GET['transaction_id']."'";

                    $sql_aukcje = $db->open_query($zapytanie_aukcje);

                    $aukcje_w_tranzakcji = '';
                    $wartosc_produktow_w_tranzakcji = 0;
                    while ($info_aukcje = $sql_aukcje->fetch_assoc()) {
                      $wartosc_produktow_w_tranzakcji += $info_aukcje['post_buy_form_it_amount'];
                      $aukcje_w_tranzakcji .= $info_aukcje['auction_id'].',';
                      $pola = array(
                              array('orders_id',$id_dodanej_pozycji_zamowienie)
                      );
                      $db->update_query('allegro_transactions' , $pola, " auction_id = '".$info_aukcje['auction_id']."' AND buyer_id = '".$info_tmp['buyer_id']."' AND transaction_id = '".$info_aukcje['transaction_id']."'");	
                      unset($pola);
                    }

                  }

                } elseif ( $_GET['postform'] == '0' ) {

                  //aktualizacja tablicy orders
                  $pola_info = array(
                               array('invoice_dokument','0'),
                               array('customers_id',$id_klienta_w_sklepie),
                               array('customers_name',$info_tmp['buyer_first_name'] . ' ' .$info_tmp['buyer_last_name']),
                               array('customers_company',$info_tmp['buyer_company']),
                               array('customers_nip',''),
                               array('customers_pesel',''),
                               array('customers_street_address',$info_tmp['buyer_street']),
                               array('customers_city',$info_tmp['buyer_city']),
                               array('customers_postcode',$info_tmp['buyer_postcode']),
                               array('customers_state', ( $info_tmp['buyer_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_state_id']) : '' ) ),
                               array('customers_country',$info_tmp['buyer_country']),
                               array('customers_telephone',$info_tmp['buyer_phone']),
                               array('customers_email_address',$info_tmp['buyer_email_address']),
                               array('customers_dummy_account', ( $zarejestrowany_uzytkownik ? '0' : '1' )),
                               array('last_modified','now()'),
                               array('date_purchased',date('Y-m-d h:i:s', $info_tmp['auction_buy_date'])),
                               array('orders_status',$allegro->polaczenie['CONF_ORDERS_STATUS']),
                               array('orders_source','3'),
                               array('currency',$_SESSION['domyslna_waluta']['kod']),
                               array('currency_value',$_SESSION['domyslna_waluta']['przelicznik']),
                               array('payment_method',''),
                               array('payment_info',''),
                               array('shipping_module',''),
                               array('shipping_info',''),
                               array('reference','http://www.allegro.pl/item'.$info_tmp['auction_id'].'_webapi.html'));

                  if ( $info_tmp['buyer_shipping_first_name'] == '' && $info_tmp['buyer_shipping_last_name'] == '' ) {
                  
                    $pola_dostawa = array(
                                    array('delivery_name',$info_tmp['buyer_first_name'] . ' ' .$info_tmp['buyer_last_name']),
                                    array('delivery_company',$info_tmp['buyer_company']),
                                    array('delivery_nip',''),
                                    array('delivery_pesel',''),
                                    array('delivery_street_address',$info_tmp['buyer_street']),
                                    array('delivery_city',$info_tmp['buyer_city']),
                                    array('delivery_postcode',$info_tmp['buyer_postcode']),
                                    array('delivery_state', ( $info_tmp['buyer_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_state_id']) : '' ) ),
                                    array('delivery_country',( $info_tmp['buyer_shipping_country'] != '' ? $info_tmp['buyer_shipping_country'] : $info_tmp['buyer_country'])));
                                    
                  } else {
                  
                    $pola_dostawa = array(
                                    array('delivery_name',$info_tmp['buyer_shipping_first_name'] . ' ' .$info_tmp['buyer_shipping_last_name']),
                                    array('delivery_company',$info_tmp['buyer_shipping_company']),
                                    array('delivery_nip',''),
                                    array('delivery_pesel',''),
                                    array('delivery_street_address',$info_tmp['buyer_shipping_street']),
                                    array('delivery_city',$info_tmp['buyer_shipping_city']),
                                    array('delivery_postcode',$info_tmp['buyer_shipping_postcode']),
                                    array('delivery_state', ( $info_tmp['buyer_shipping_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_shipping_state_id']) : '' ) ),
                                    array('delivery_country',( $info_tmp['buyer_shipping_country'] != '' ? $info_tmp['buyer_shipping_country'] : $info_tmp['buyer_country'])));
                                    
                  }

                  $pola_platnik = array(
                                  array('billing_name',$info_tmp['buyer_first_name'] . ' ' .$info_tmp['buyer_last_name']),
                                  array('billing_company',$info_tmp['buyer_company']),
                                  array('billing_nip',''),
                                  array('billing_pesel',''),
                                  array('billing_street_address',$info_tmp['buyer_street']),
                                  array('billing_city',$info_tmp['buyer_city']),
                                  array('billing_postcode',$info_tmp['buyer_postcode']),
                                  array('billing_state', ( $info_tmp['buyer_state_id'] != '' ? Klienci::pokazNazweWojewodztwa($info_tmp['buyer_state_id']) : '' ) ),
                                  array('billing_country',$info_tmp['buyer_country']));

                  $pola = Array();
                  $pola = array_merge( $pola_info, $pola_dostawa, $pola_platnik );

                  $db->insert_query('orders' , $pola);
                  $id_dodanej_pozycji_zamowienie = $db->last_id_query();
                  unset($pola);

                  $aukcje_w_tranzakcji = $info_tmp['auction_id'] . ',';
                  $wartosc_produktow_w_tranzakcji = $info_tmp['auction_price'] * $info_tmp['auction_quantity'];
                  $pola = array(
                          array('orders_id',$id_dodanej_pozycji_zamowienie)
                  );
                  $db->update_query(' allegro_auctions_sold' , $pola, " allegro_auction_id = '".(int)$_GET['id_poz']."' AND buyer_id = '".$info_tmp['buyer_id']."'");	
                  unset($pola);

                }

                // aktualizacja tablicy orders_status_history
                $komentarz = 'Zamówienie z Allegro; Dotyczy aukcji : ' . substr($aukcje_w_tranzakcji, 0, -1);
                
                $komentarz .= "<br />".'Nick kupującego : ' . $info_tmp['buyer_name'];
                
                if ( isset($info_tmp['post_buy_form_msg_to_seller']) && $info_tmp['post_buy_form_msg_to_seller'] != '' ) {
                  $komentarz .= "<br />".'Informacja od kupującego : ' . $info_tmp['post_buy_form_msg_to_seller'];
                }
                
                $pola = array(
                        array('orders_id ',(int)$id_dodanej_pozycji_zamowienie),
                        array('orders_status_id',$allegro->polaczenie['CONF_ORDERS_STATUS']),
                        array('date_added','now()'),
                        array('customer_notified ','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz));

                $db->insert_query('orders_status_history' , $pola);
                unset($pola, $komentarz);

                // aktualizacja tablicy orders_total ####################################################
                $zamowienie = new Zamowienie($id_dodanej_pozycji_zamowienie);
                $suma = new SumaZamowienia();
                $tablica_modulow = $suma->przetwarzaj_moduly();

                foreach ( $tablica_modulow as $podsumowanie ) {

                    $tekst_zamowienia = $waluty->FormatujCene($podsumowanie['wartosc']);

                    $pola = array(
                            array('orders_id',(int)$id_dodanej_pozycji_zamowienie),
                            array('title', $podsumowanie['text'] ),
                            array('text', $tekst_zamowienia ),
                            array('value', $podsumowanie['wartosc'] ),
                            array('prefix', $podsumowanie['prefix'] ),
                            array('class', $podsumowanie['klasa'] ),
                            array('sort_order', $podsumowanie['sortowanie'] ));
                            
                    unset($tekst_zamowienia);
                            
                    if ( isset($podsumowanie['vat_id']) && isset($podsumowanie['vat_stawka']) ) {
                        //
                        $pola[] = array('tax',$podsumowanie['vat_stawka']);
                        $pola[] = array('tax_class_id',$podsumowanie['vat_id']);
                        //
                    }                    

                    $db->insert_query('orders_total' , $pola);
                    unset($pola);
                    
                }
                //unset($_SESSION['koszyk']);

                if ( $_GET['postform'] == '1' ) {

                  //Zapisanie informacji o kosztach przesylki
                  $pola = array(
                          array('title','Koszt wysyłki'),
                          array('text', $waluty->FormatujCene($info_tmp['post_buy_form_postage_amount']) ),
                          array('value', $info_tmp['post_buy_form_postage_amount'] ),
                  );
                  $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_shipping'");	
                  unset($pola);

                  //Zapisanie informacji o wartosci zamowienia
                  $pola = array(
                          array('text', $waluty->FormatujCene($info_tmp['post_buy_form_amount']) ),
                          array('value', $info_tmp['post_buy_form_amount'] ),
                  );
                  $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_total'");	
                  unset($pola);

                  //Zapisanie informacji o wartosci produktow
                  $pola = array(
                          array('text', $waluty->FormatujCene($wartosc_produktow_w_tranzakcji) ),
                          array('value', $wartosc_produktow_w_tranzakcji ),
                  );
                  $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_subtotal'");	
                  unset($pola);

                } else {

                  //Zapisanie informacji o kosztach przesylki
                  $pola = array(
                          array('title','Koszt wysyłki'),
                          array('text', $waluty->FormatujCene(0.00) ),
                          array('value', 0 ),
                  );
                  $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_shipping'");	
                  unset($pola);

                  //Zapisanie informacji o wartosci zamowienia
                  $pola = array(
                          array('text', $waluty->FormatujCene($wartosc_produktow_w_tranzakcji) ),
                          array('value', $wartosc_produktow_w_tranzakcji ),
                  );
                  $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_total'");	
                  unset($pola);

                  //Zapisanie informacji o wartosci produktow
                  $pola = array(
                          array('text', $waluty->FormatujCene($wartosc_produktow_w_tranzakcji) ),
                          array('value', $wartosc_produktow_w_tranzakcji ),
                  );
                  $db->update_query('orders_total' , $pola, " orders_id = '".(int)$id_dodanej_pozycji_zamowienie."' AND class = 'ot_subtotal'");	
                  unset($pola);

                }

                $szukane_aukcje = substr($aukcje_w_tranzakcji, 0, -1);

                if ( $_GET['postform'] == '1' ) {
                
                  $zapytanie_produkty = "
                    SELECT a.auction_id, a.products_id, a.products_stock_attributes, a.products_name, t.transaction_id, t.post_buy_form_it_quantity, t.post_buy_form_it_amount, p.products_model, p.products_pkwiu, p.products_tax_class_id, p.products_quantity, ps.products_stock_attributes, ps.products_stock_quantity 
                      FROM allegro_transactions t
                      LEFT JOIN allegro_auctions a ON t.auction_id = a.auction_id 
                      LEFT JOIN products p ON p.products_id = a.products_id
                      LEFT JOIN products_stock ps ON ps.products_id = a.products_id AND ps.products_stock_attributes = a.products_stock_attributes
                      WHERE t.transaction_id = '".$_GET['transaction_id']."'";
                      
                } elseif ( $_GET['postform'] == '0' ) {
                  
                  $zapytanie_produkty = "
                    SELECT a.auction_id, a.products_id, a.products_stock_attributes, a.products_name, aas.auction_quantity, aas.auction_price, p.products_model, p.products_pkwiu, p.products_tax_class_id, p.products_quantity, ps.products_stock_attributes, ps.products_stock_quantity 
                      FROM allegro_auctions_sold aas
                      LEFT JOIN allegro_auctions a ON a.auction_id = aas.auction_id
                      LEFT JOIN products p ON p.products_id = a.products_id
                      LEFT JOIN products_stock ps ON ps.products_id = a.products_id AND ps.products_stock_attributes = a.products_stock_attributes
                      WHERE aas.allegro_auction_id = '".$_GET['id_poz']."'";
                }


                $sql_produkty = $db->open_query($zapytanie_produkty);

                $wartosc_vat_razem = 0;

                while ($info_produkty = $sql_produkty->fetch_assoc()) {

                  if ( $_GET['postform'] == '1' ) {
                    $ilosc_produktow    = $info_produkty['post_buy_form_it_quantity'];
                    $wartosc_brutto     = $info_produkty['post_buy_form_it_amount'];
                    $cena_brutto        = round($info_produkty['post_buy_form_it_amount'] / $info_produkty['post_buy_form_it_quantity'], 2);
                    $stawka_vat         = Produkty::PokazStawkeVAT( $info_produkty['products_tax_class_id'] );
                    $kwota_vat          = round($cena_brutto * ( $stawka_vat / (100 + $stawka_vat )), 2 );
                    $cena_netto         = $cena_brutto - $kwota_vat;
                    $wartosc_vat_razem += round($kwota_vat * $info_produkty['post_buy_form_it_quantity'], 2 );
                  } elseif ( $_GET['postform'] == '0' ) {
                    $ilosc_produktow    = $info_produkty['auction_quantity'];
                    $wartosc_brutto     = $info_produkty['auction_quantity'] * $info_produkty['auction_price'] ;
                    $cena_brutto        = $info_produkty['auction_price'];
                    $stawka_vat         = Produkty::PokazStawkeVAT( $info_produkty['products_tax_class_id'] );
                    $kwota_vat          = round($cena_brutto * ( $stawka_vat / (100 + $stawka_vat )), 2 );
                    $cena_netto         = $cena_brutto - $kwota_vat;
                    $wartosc_vat_razem += round($kwota_vat * $info_produkty['auction_quantity'], 2 );
                  }

                  $pola = array(
                          array('orders_id',(int)$id_dodanej_pozycji_zamowienie),
                          array('products_id', $info_produkty['products_id'] ),
                          array('products_model', $info_produkty['products_model'] ),
                          array('products_pkwiu', $info_produkty['products_pkwiu'] ),
                          array('products_name', $info_produkty['products_name'] ),
                          array('products_price', $cena_netto ),
                          array('products_price_tax', $cena_brutto ),
                          array('final_price', $cena_netto ),
                          array('final_price_tax', $cena_brutto ),
                          array('products_tax', $stawka_vat ),
                          array('products_tax_class_id', $info_produkty['products_tax_class_id'] ),
                          array('products_quantity', $ilosc_produktow ),
                          array('products_stock_attributes', $info_produkty['products_stock_attributes'] ));

                  $db->insert_query('orders_products' , $pola);
                  $id_dodanego_produktu = $db->last_id_query();
                  unset($pola);

                  //aktualizacja stanu magazynowego produktu
                  if ( MAGAZYN_SPRAWDZ_STANY == 'tak' ) {
                    $zaktualizowana_ilosc_produktu = $info_produkty['products_quantity'] - $ilosc_produktow;
                    $pola = array(
                            array('products_quantity', $zaktualizowana_ilosc_produktu ),
                    );
                    if ( $zaktualizowana_ilosc_produktu <= 0 && MAGAZYN_WYLACZ_PRODUKT == 'tak' ) {
                      $pola[] = array('products_status', '0' );
                    }
                    $db->update_query('products' , $pola, " products_id = '".(int)$info_produkty['products_id']."'");	
                    unset($pola);
                  }

                  if ( $info_produkty['products_stock_attributes'] != '' ) {

                    $tablica_kombinacji_cech = explode(';', $info_produkty['products_stock_attributes']);
                    for ( $t = 0, $c = count($tablica_kombinacji_cech); $t < $c; $t++ ) {
                      $tablica_wartosc_cechy = explode('-', $tablica_kombinacji_cech[$t]);

                      $zapytanie_nazwa_cechy = "SELECT 
                                  * 
                                  FROM products_options
                                  WHERE products_options_id = '" . (int)$tablica_wartosc_cechy['0']. "' 
                                  AND language_id =  '1'";
                                  
                      $sql_nazwa_cechy = $db->open_query($zapytanie_nazwa_cechy);

                      if ((int)$db->ile_rekordow($sql_nazwa_cechy) > 0) {
                        $info_nazwa_cechy = $sql_nazwa_cechy->fetch_assoc();
                        $nazwa_cechy = $info_nazwa_cechy['products_options_name'];
                      }

                      $zapytanie_wartosc_cechy = "SELECT 
                                  * 
                                  FROM products_options_values
                                  WHERE products_options_values_id = '" . (int)$tablica_wartosc_cechy['1']. "' 
                                  AND language_id =  '1'";
                                  
                      $sql_wartosc_cechy = $db->open_query($zapytanie_wartosc_cechy);

                      if ((int)$db->ile_rekordow($sql_wartosc_cechy) > 0) {
                        $info_wartosc_cechy = $sql_wartosc_cechy->fetch_assoc();
                        $nazwa_wartosci_cechy = $info_wartosc_cechy['products_options_values_name'];
                      }

                      $pola = array(
                              array('orders_id',$id_dodanej_pozycji_zamowienie),
                              array('orders_products_id',$id_dodanego_produktu),
                              array('products_options',$nazwa_cechy),
                              array('products_options_id',$tablica_wartosc_cechy['0']),
                              array('products_options_values',$nazwa_wartosci_cechy),
                              array('products_options_values_id',$tablica_wartosc_cechy['1']),
                              array('options_values_price','0'),
                              array('options_values_tax','0'),
                              array('options_values_price_tax','0'),
                              array('price_prefix','+'));

                      $db->insert_query('orders_products_attributes' , $pola);
                      unset($pola);
                    }

                    //aktualizacja stanu magazynowego cech produktu
                    if ( CECHY_MAGAZYN == 'tak' ) {
                      $ilosc_cech = $info_produkty['products_stock_quantity'] - $ilosc_produktow;
                      $pola = array(
                              array('products_stock_quantity', $ilosc_cech ));
                              
                      $db->update_query('products_stock' , $pola, " products_id = '".(int)$info_produkty['products_id']."' AND products_stock_attributes = '".$info_produkty['products_stock_attributes']."'");	
                      unset($pola);
                    }
                  }

                }

                if ( $_GET['postform'] == '1' ) {

                  // jezeli jest ajax
                  if ( isset($_POST['ajax']) ) {
                       //
                       $tekst = '<span>Utworzono zamówienie numer: <b>' . $id_dodanej_pozycji_zamowienie . '</b>; na podstawie transakcji numer: <b>' .$_GET['transaction_id']. '</b></span>';
                       //
                    } else {
                       //
                       $tekst = '<div id="zaimportowano">Utworzono zamówienie numer: ' . $id_dodanej_pozycji_zamowienie . '; na podstawie transakcji numer: ' .$_GET['transaction_id']. '</div>';
                       //
                  }              
                  
                } elseif ( $_GET['postform'] == '0' ) {
                
                  // jezeli jest ajax
                  if ( isset($_POST['ajax']) ) {
                       //
                       $tekst = '<span>Utworzono zamówienie numer: <b>' . $id_dodanej_pozycji_zamowienie . '</b>; <b style="color:#ff0000">aukcja bez wypełnionego formularza - należy uzupełnić informacje o płatności i wysyłce</b></span>';
                       //
                    } else {
                       //            
                       $tekst = '<div id="zaimportowano">Utworzono zamówienie numer: ' . $id_dodanej_pozycji_zamowienie . '</div><br />';
                       $tekst .= '<div class="ostrzezenie">Aukcja bez wypełnionego formularza - należy uzupełnić informacje o płatności i wysyłce</div>';
                       //
                  }
                  
                }
              
            }
            
        }

      }

    }
    
    if ( isset($_POST['ajax']) ) {
         //
         echo $tekst;
         //
    }    
    
    if ( !isset($_POST['ajax']) ) {

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <div id="naglowek_cont">Generowanie zamówień</div>
        <div id="cont">
              
            <div class="poleForm">
            
              <div class="naglowek">Zamówienie dla aukcji: <?php echo ( $db->ile_rekordow($sql_tmp) > 0 ? $numer_aukcji : '' ); ?></div>
              
                  <?php if ( $db->ile_rekordow($sql_tmp) > 0 ) { ?>
                  
                  <div class="pozycja_edytowana">
                  
                      <p>
                        <?php echo $tekst; ?>
                      </p>   
                   
                  </div>
                  
                  <?php } else { ?>
                  
                  <div class="pozycja_edytowana">
                  
                      <div class="ostrzezenie" style="margin:10px">
                        Brak danych do przetworzenia.
                      </div>   
                   
                  </div>
                  
                  <?php } ?>

                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('allegro_sprzedaz','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
                  </div>

            </div>                      

        </div>    
        
        <?php
        include('stopka.inc.php');
          
    }

}
?>