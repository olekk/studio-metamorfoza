<?php
chdir('../'); 
//

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

ini_set('display_errors', 1);

// jezeli nie ma id klienta
if ( !isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) {
    Funkcje::PrzekierowanieURL('/');
}

// jezeli koszyk jest pusty
if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() <= 0 ) {
    Funkcje::PrzekierowanieURL('/');
}

// jezeli nie ma wybranej metody wysylki
if ( !isset($_SESSION['rodzajDostawy']) ) {
    Funkcje::PrzekierowanieURL('koszyk.html');
}

// jezeli nie ma wybranej metody platnosci
if ( !isset($_SESSION['rodzajPlatnosci']) ) {
    Funkcje::PrzekierowanieURL('koszyk.html');
}

// jezeli nie ma adresu wysylki
if ( !isset($_SESSION['adresDostawy']) ) {
    Funkcje::PrzekierowanieURL('koszyk.html');
}

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

$rodzajPlatnosciOpis = '';
if ( 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_payu' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_dotpay' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_przelewy24' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_pbn' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_payeezy' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_santander' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_lukas' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_bank' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_paypal' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_cashbill' && 
    $_SESSION['rodzajPlatnosci']['platnosc_klasa'] != 'platnosc_transferuj' ) {

        $rodzajPlatnosciOpis = $_SESSION['rodzajPlatnosci']['opis'];

}

// zapisanie informacji do tablicy orders
$pola_info = array(
             array('invoice_dokument',$_SESSION['adresFaktury']['dokument']),
             array('customers_id',$filtr->process($_SESSION['customer_id'])),
             array('customers_name',$filtr->process($_SESSION['adresDostawy']['imie']) . ' ' . $filtr->process($_SESSION['adresDostawy']['nazwisko'])),
             array('customers_company',$filtr->process($_SESSION['adresDostawy']['firma'])),
             array('customers_nip',''),
             array('customers_pesel',''),
             array('customers_street_address',$filtr->process($_SESSION['adresDostawy']['ulica'])),
             array('customers_city',$filtr->process($_SESSION['adresDostawy']['miasto'])),
             array('customers_postcode',$filtr->process($_SESSION['adresDostawy']['kod_pocztowy'])),
             array('customers_state', Klient::pokazNazweWojewodztwa($_SESSION['adresDostawy']['wojewodztwo'])),
             array('customers_country',Klient::pokazNazwePanstwa($_SESSION['adresDostawy']['panstwo'])),
             array('customers_telephone',$filtr->process($_SESSION['adresDostawy']['telefon'])),
             array('customers_email_address',$filtr->process($_SESSION['customer_email'])),
             array('customers_dummy_account',$filtr->process($_SESSION['gosc'])),
             array('last_modified','now()'),
             array('date_purchased','now()'),
             array('orders_status',Funkcje::PokazDomyslnyStatusZamowienia()),
             array('orders_source', ( $_SESSION['gosc'] == '1' ? '2' : '1' )),
             array('currency',$_SESSION['domyslnaWaluta']['kod']),
             array('currency_value',$_SESSION['domyslnaWaluta']['przelicznik']),
             array('payment_method',$filtr->process($_SESSION['rodzajPlatnosci']['platnosc_nazwa'])),
             array('payment_info',$rodzajPlatnosciOpis),
             array('shipping_module',$filtr->process($_SESSION['rodzajDostawy']['wysylka_nazwa'])),
             array('shipping_info',( isset($_SESSION['rodzajDostawy']['opis']) ? $_SESSION['rodzajDostawy']['opis'] : '' )),
             array('reference', ((isset($_SESSION['referencja'])) ? $filtr->process($_SESSION['referencja']) : '')),
             array('tracker_ip', $filtr->process($_SESSION['ippp'])));

$pola_dostawa = array(
                array('delivery_name',$filtr->process($_SESSION['adresDostawy']['imie']) . ' ' . $filtr->process($_SESSION['adresDostawy']['nazwisko'])),
                array('delivery_company',$filtr->process($_SESSION['adresDostawy']['firma'])),
                array('delivery_nip',''),
                array('delivery_pesel',''),
                array('delivery_street_address',$filtr->process($_SESSION['adresDostawy']['ulica'])),
                array('delivery_city',$filtr->process($_SESSION['adresDostawy']['miasto'])),
                array('delivery_postcode',$filtr->process($_SESSION['adresDostawy']['kod_pocztowy'])),
                array('delivery_state',Klient::pokazNazweWojewodztwa($_SESSION['adresDostawy']['wojewodztwo'])),
                array('delivery_country',Klient::pokazNazwePanstwa($_SESSION['adresDostawy']['panstwo'])));

$pola_platnik = array(
                array('billing_name',trim($filtr->process($_SESSION['adresFaktury']['imie']) . ' ' . $filtr->process($_SESSION['adresFaktury']['nazwisko']))),
                array('billing_company',$filtr->process($_SESSION['adresFaktury']['firma'])),
                array('billing_nip',$filtr->process($_SESSION['adresFaktury']['nip'])),
                array('billing_pesel',(isset($_SESSION['adresFaktury']['pesel']) ? $filtr->process($_SESSION['adresFaktury']['pesel']) : '')),
                array('billing_street_address',$filtr->process($_SESSION['adresFaktury']['ulica'])),
                array('billing_city',$filtr->process($_SESSION['adresFaktury']['miasto'])),
                array('billing_postcode',$filtr->process($_SESSION['adresFaktury']['kod_pocztowy'])),
                array('billing_state',Klient::pokazNazweWojewodztwa($_SESSION['adresFaktury']['wojewodztwo'])),
                array('billing_country',Klient::pokazNazwePanstwa($_SESSION['adresFaktury']['panstwo'])));

$pola = Array();
$pola = array_merge( $pola_info, $pola_dostawa, $pola_platnik );

$GLOBALS['db']->insert_query('orders' , $pola);
$id_dodanej_pozycji_zamowienia = $GLOBALS['db']->last_id_query();
unset($pola);

$PodsumowanieTablica = array();
$PunktyZaZakup = 0;

// zapisanie informacji do tablicy orders_total
foreach ( $_SESSION['podsumowanieZamowienia'] as $podsumowanie ) {

    $pola = array(
            array('orders_id',$id_dodanej_pozycji_zamowienia),
            array('title',$podsumowanie['text']),
            array('text',$waluty->PokazCeneSymbol($podsumowanie['wartosc'], $_SESSION['domyslnaWaluta']['kod'])),
            array('value',$podsumowanie['wartosc']),
            array('prefix',$podsumowanie['prefix']),
            array('class',$podsumowanie['klasa']),
            array('sort_order',$podsumowanie['sortowanie']));
            
    if ( isset($podsumowanie['vat_id']) && isset($podsumowanie['vat_stawka']) ) {
        //
        $pola[] = array('tax',$podsumowanie['vat_stawka']);
        $pola[] = array('tax_class_id',$podsumowanie['vat_id']);
        //
    }
    
    // jezeli jest koszt platnosci przyjmie vat z wysylki
    if ( $podsumowanie['klasa'] == 'ot_payment' ) {
        //
        if ( isset($_SESSION['rodzajDostawy']['wysylka_vat_id']) && isset($_SESSION['rodzajDostawy']['wysylka_vat_stawka']) ) {
            //
            $pola[] = array('tax',$_SESSION['rodzajDostawy']['wysylka_vat_stawka']);
            $pola[] = array('tax_class_id',$_SESSION['rodzajDostawy']['wysylka_vat_id']);
            //        
        }
        //
    }

    // naliczanie punktow za zakup jezeli jest zarejestrowany klient
    if ( $_SESSION['gosc'] == '0' ) {
    
        if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
            //
            if ( isset($_SESSION['punktyKlienta']) ) {
                //
                if ( SYSTEM_PUNKTOW_PUNKTY_ZA_PLATNOSCI_PUNKTAMI == 'tak' ) {
                    if ( $podsumowanie['prefix'] == '1' && $podsumowanie['klasa'] != 'ot_shipping' && $podsumowanie['klasa'] != 'ot_payment' ) {
                        $PunktyZaZakup += ceil(($podsumowanie['wartosc']/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                    } elseif ( $podsumowanie['prefix'] == '0' ) {
                        $PunktyZaZakup -= ceil(($podsumowanie['wartosc']/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                    }
                }
                //
            } else {
                //
                if ( $podsumowanie['prefix'] == '1' && $podsumowanie['klasa'] != 'ot_shipping' && $podsumowanie['klasa'] != 'ot_payment' ) {
                    $PunktyZaZakup += ceil(($podsumowanie['wartosc']/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                } elseif ( $podsumowanie['prefix'] == '0' ) {
                    $PunktyZaZakup -= ceil(($podsumowanie['wartosc']/$_SESSION['domyslnaWaluta']['przelicznik']) * SYSTEM_PUNKTOW_WARTOSC);
                }
                //
            }
            //
        }
        
    }

    $GLOBALS['db']->insert_query('orders_total' , $pola);
    unset($pola);
    
    // generowanie do maila
    $PodsumowanieTablica[$podsumowanie['sortowanie']] = array( 'nazwa'   => $podsumowanie['text'],
                                                               'wartosc' => $podsumowanie['wartosc'],
                                                               'klasa'   => $podsumowanie['klasa'] );

}

// zapisanie informacji o produkcie

// generuje tablice globalne z nazwami cech
Funkcje::TabliceCech();         
//

$CechyProdukty = array();
$IdProduktowZamowienia = array();

foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    //

    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
    
    $Produkt->ProduktCzasWysylki();
    $Produkt->ProduktStanProduktu();
    $Produkt->ProduktGwarancja();

    $IdProduktowZamowienia[] = Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] );
    $CechyProduktu = '';

    $AktualnaIloscProduktu = $Produkt->info['ilosc'];
    $IloscProdukuPoSprzedazy = $AktualnaIloscProduktu - $TablicaZawartosci['ilosc'];

    // zapisanie informacji do tablicy orders_products
    $KombinacjaCech = explode('x', $TablicaZawartosci['id']);
    $IdProduktu = $KombinacjaCech['0'];

    array_shift($KombinacjaCech);
    if ( count($KombinacjaCech) > 0 ) {
        $CechyProduktu = implode(",", $KombinacjaCech);
    }

    $pola = array(
            array('orders_id',$id_dodanej_pozycji_zamowienia),
            array('products_id',$IdProduktu),
            array('products_name',$Produkt->info['nazwa']),
            array('products_model',$TablicaZawartosci['nr_katalogowy']),
            array('products_pkwiu',$Produkt->info['pkwiu']),
            array('products_quantity',$TablicaZawartosci['ilosc']),
            array('products_shipping_time',$Produkt->czas_wysylki),
            array('products_warranty',strip_tags($Produkt->gwarancja)),
            array('products_condition',$Produkt->stan_produktu),
            array('products_tax',$Produkt->info['stawka_vat']),
            array('products_tax_class_id',$Produkt->info['stawka_vat_id']),
            array('products_price',$Produkt->info['cena_netto_bez_formatowania']),
            array('products_price_tax',$Produkt->info['cena_brutto_bez_formatowania']),
            array('final_price',$TablicaZawartosci['cena_netto']),
            array('final_price_tax',$TablicaZawartosci['cena_brutto']),
            array('products_comments',$TablicaZawartosci['komentarz']),
            array('products_text_fields',$TablicaZawartosci['pola_txt']),
            array('products_stock_attributes',$CechyProduktu));

    //
    $GLOBALS['db']->insert_query('orders_products', $pola);
    $id_dodanej_pozycji_produkt = $GLOBALS['db']->last_id_query();
    unset($pola);

    // aktualizacja stanu magazynowego produktu
    if ( MAGAZYN_SPRAWDZ_STANY == 'tak' ) {
    
        $AktualnaIloscProduktu = $Produkt->info['ilosc'];
        $IloscProdukuPoSprzedazy = $AktualnaIloscProduktu - $TablicaZawartosci['ilosc'];

        $pola = array(array('products_quantity',$IloscProdukuPoSprzedazy));
        
        $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . (int)$IdProduktu . "'");
        unset($pola);
        
    }

    // aktualizacja ilosci sprzedanych produktow
    $zapytanie_sprzedane = "SELECT products_ordered FROM products WHERE products_id = '".(int)$IdProduktu."'";
    $sql_sprzedane = $GLOBALS['db']->open_query($zapytanie_sprzedane);
    $sprzedane = $sql_sprzedane->fetch_assoc();  
    $sprzedane_akt = $sprzedane['products_ordered'] + $TablicaZawartosci['ilosc'];

    $pola = array(
            array('products_ordered',$sprzedane_akt));
            
    $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . (int)$IdProduktu . "'");

    $GLOBALS['db']->close_query($sql_sprzedane);         
    unset($zapytanie_sprzedane, $sprzedane, $pola);

    // zapisanie informacji do tablicy orders_products_attributes
    if ( count($KombinacjaCech) > 0 ) {

        $TablicaWybranychCech = $Produkt->ProduktCechyTablica($TablicaZawartosci['id']);

        foreach ( $TablicaWybranychCech as $Cecha ) {
        
            $CechyProdukty[ $TablicaZawartosci['id'] ][] = array( 'cecha'   => $Cecha['nazwa_cechy'],
                                                                                                    'wartosc' => $Cecha['nazwa_wartosci'] );
            $pola = array(
                    array('orders_id',$id_dodanej_pozycji_zamowienia),
                    array('orders_products_id',$id_dodanej_pozycji_produkt),
                    array('products_options',$Cecha['nazwa_cechy']),
                    array('products_options_id',$Cecha['id_cechy']),
                    array('products_options_values',$Cecha['nazwa_wartosci']),
                    array('products_options_values_id',$Cecha['id_wartosci']),
                    array('options_values_price',$Cecha['cena']['netto']),
                    array('options_values_tax',$Cecha['cena']['brutto']),
                    array('options_values_price_tax',$Cecha['kwota_vat']['brutto']),
                    array('price_prefix',$Cecha['prefix']));

            $GLOBALS['db']->insert_query('orders_products_attributes' , $pola);
            unset($pola);
        }

        // aktualizacja stanu magazynowego cech produktu
        if ( CECHY_MAGAZYN == 'tak' ) {
        
            $zapytanie = "SELECT products_stock_id, products_stock_quantity 
                          FROM products_stock 
                          WHERE products_id = '".$IdProduktu."' 
                          AND products_stock_attributes = '".$CechyProduktu."'";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            $cecha = $sql->fetch_assoc();

            $AktualnaIloscCechProduktu = $cecha['products_stock_quantity'];
            $IloscCechProdukuPoSprzedazy = $AktualnaIloscCechProduktu - $TablicaZawartosci['ilosc'];
            
            if ( $IloscCechProdukuPoSprzedazy > 0 ) {
                $pola = array(array('products_stock_quantity',$IloscCechProdukuPoSprzedazy));
                $GLOBALS['db']->update_query('products_stock' , $pola, "products_stock_id = '" . (int)$cecha['products_stock_id'] . "'");
            } else {
                $pola = array(array('products_stock_quantity',0));
                $GLOBALS['db']->update_query('products_stock' , $pola, "products_stock_id = '" . (int)$cecha['products_stock_id'] . "'");
            }
            
            $GLOBALS['db']->close_query($sql);         
            unset($zapytanie, $cecha, $pola, $AktualnaIloscCechProduktu, $IloscCechProdukuPoSprzedazy);
            
        }

    }

    $GLOBALS['cache']->UsunCacheProduktow();

}

// zapisanie informacji do tablicy customers_points
//
if ( $PunktyZaZakup > 0 && $_SESSION['gosc'] == '0' ) {

    $pola = array(
            array('customers_id',(int)$_SESSION['customer_id']),
            array('orders_id',$id_dodanej_pozycji_zamowienia),
            array('points',$PunktyZaZakup),
            array('date_added','now()'),
            array('points_status','1'),
            array('points_type','SP')
    );
    $GLOBALS['db']->insert_query('customers_points' , $pola);
    unset($pola);
    
}

// zapisanie informacji w historii statusow zamowien
//
$pola = array(
        array('orders_id ',(int)$id_dodanej_pozycji_zamowienia),
        array('orders_status_id',Funkcje::PokazDomyslnyStatusZamowienia()),
        array('date_added','now()'),
        array('customer_notified ','1'),
        array('customer_notified_sms','0'),
        array('comments',$filtr->process($_POST['komentarz'])));
        
$GLOBALS['db']->insert_query('orders_status_history' , $pola);
unset($pola);

// zapisanie informacji o wykorzystaniu kuponu rabatowego
//
if ( isset($_SESSION['kuponRabatowy']) ) {
    $pola = array(
            array('coupons_id ',$_SESSION['kuponRabatowy']['kupon_id']),
            array('orders_id',(int)$id_dodanej_pozycji_zamowienia));
            
    $GLOBALS['db']->insert_query('coupons_to_orders' , $pola);
    unset($pola);
    // aktualizacja informacji w bazie kuponow
    //
    $zapytanie = "SELECT coupons_id, coupons_quantity 
                          FROM coupons 
                          WHERE coupons_id = '".$_SESSION['kuponRabatowy']['kupon_id']."'";

    $sql = $GLOBALS['db']->open_query($zapytanie);
    $kupon = $sql->fetch_assoc();
    $AktualnaIloscKuponow = $kupon['coupons_quantity'];
    $IloscKuponowPoSprzedazy = $AktualnaIloscKuponow - 1;
    
    if ( $IloscKuponowPoSprzedazy > 0 ) {
        $pola = array(array('coupons_quantity',$IloscKuponowPoSprzedazy));
        $GLOBALS['db']->update_query('coupons' , $pola, "coupons_id = '" . (int)$_SESSION['kuponRabatowy']['kupon_id'] . "'");
    } else {
        $pola = array(
                array('coupons_quantity',$IloscKuponowPoSprzedazy),
                array('coupons_status','0'));
        $GLOBALS['db']->update_query('coupons' , $pola, "coupons_id = '" . (int)$_SESSION['kuponRabatowy']['kupon_id'] . "'");
    }
    
    $GLOBALS['db']->close_query($sql);         
    unset($zapytanie, $kupon, $pola);

    $pola = array(
            array('coupons_id',(int)$_SESSION['kuponRabatowy']['kupon_id']),
            array('customers_id',(int)$_SESSION['customer_id']),
            array('orders_id',(int)$id_dodanej_pozycji_zamowienia));
            
    $GLOBALS['db']->insert_query('coupons_to_customers' , $pola);
    unset($pola);

    unset($_SESSION['kuponRabatowy']);
}

// zapisanie informacji o wykorzystaniu punktów rabatowych
//
if ( isset($_SESSION['punktyKlienta']) ) {

    $punkty = new Punkty((int)$_SESSION['customer_id']);

    //Odjecie punktow wykorzystanych w tym zamowieniu
    //
    $AktualnaIloscPunktow = $punkty->suma;
    $IloscPunktowPoSprzedazy = $AktualnaIloscPunktow - $_SESSION['punktyKlienta']['punkty_ilosc'];

    $pola = array(
            array('customers_shopping_points',$IloscPunktowPoSprzedazy));
            
    $GLOBALS['db']->update_query('customers' , $pola, "customers_id = '" . (int)$_SESSION['customer_id'] . "'");


    // zapisanie informacji do tablicy customers_points
    //
    $pola = array(
            array('customers_id',(int)$_SESSION['customer_id']),
            array('orders_id',$id_dodanej_pozycji_zamowienia),
            array('points',$_SESSION['punktyKlienta']['punkty_ilosc']),
            array('date_added','now()'),
            array('date_confirm','now()'),
            array('points_status','4'),
            array('points_type','SC'));
            
    $GLOBALS['db']->insert_query('customers_points' , $pola);
    unset($pola);

    unset($_SESSION['punktyKlienta']);

}

// usuniecie rekordu z tablicy koszyka
$GLOBALS['db']->delete_query('customers_basket' , " customers_id = '".(int)$_SESSION['customer_id']."'");

// dodatkowe pola klientow
$dodatkowe_pola_zamowienia = "SELECT oe.fields_id, oe.fields_input_type 
                                FROM orders_extra_fields oe 
                               WHERE oe.fields_status = '1'";

$sql = $GLOBALS['db']->open_query($dodatkowe_pola_zamowienia);

if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

  while ( $dodatkowePola = $sql->fetch_assoc() ) {
  
    $wartosc = '';
    $pola = array();
    if ( $dodatkowePola['fields_input_type'] != '3' ) {
        //
        if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
          //
          $pola = array(
                  array('orders_id',$id_dodanej_pozycji_zamowienia),
                  array('fields_id',$dodatkowePola['fields_id']),
                  array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']])));
        }
        //                
    } else {
        //
        if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
          //
          foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
            $wartosc .= $value . "\n";
          }
          $pola = array(
                  array('orders_id',$id_dodanej_pozycji_zamowienia),
                  array('fields_id',$dodatkowePola['fields_id']),
                  array('value',$filtr->process($wartosc)));
        }
        //
    }

    if ( count($pola) > 0 ) {
      $pola[] = array('language_id', $_SESSION['domyslnyJezyk']['id']);
      $GLOBALS['db']->insert_query('orders_to_extra_fields' , $pola);
    }
    unset($pola);
    
  }
  
}
//

// zapisanie do sesji informacji o zgodzie na przekazanie danych
$_SESSION['zgodaNaPrzekazanieDanych'] = ( isset($_POST['zgoda_opinie']) && $_POST['zgoda_opinie'] == '1' ? '1' : '0' );

// wyslanie maila
$jezyk_maila = $_SESSION['domyslnyJezyk']['id'];

$zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = 'EMAIL_ZAMOWIENIE'";
$sql = $GLOBALS['db']->open_query($zapytanie_tresc);
$tresc = $sql->fetch_assoc();  
        
$email = new Mailing;

if ( $tresc['email_file'] != '' ) {
    $tablicaZalacznikow = explode(';', $tresc['email_file']);
} else {
    $tablicaZalacznikow = array();
}

$nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
$nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
$kopia_maila     = Funkcje::parsujZmienne($tresc['dw']);

$adresat_email   = $filtr->process($_SESSION['customer_email']);
$adresat_nazwa   = $filtr->process($_SESSION['adresDostawy']['imie']) . ' ' . $filtr->process($_SESSION['adresDostawy']['nazwisko']);

define('NUMER_ZAMOWIENIA', $id_dodanej_pozycji_zamowienia); 

$temat           = Funkcje::parsujZmienne($tresc['email_title']);
$tekst           = $tresc['description'];
$zalaczniki      = $tablicaZalacznikow;
$szablon         = $tresc['template_id'];
$jezyk           = (int)$jezyk_maila;

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA', 'KOSZYK') ), $GLOBALS['tlumacz'] );

define('IMIE_NAZWISKO_KUPUJACEGO', $filtr->process($_SESSION['adresDostawy']['imie']) . ' ' . $filtr->process($_SESSION['adresDostawy']['nazwisko'])); 
define('ADRES_EMAIL_ZAMAWIAJACEGO', $filtr->process($_SESSION['customer_email'])); 

if ( $_SESSION['gosc'] == '0' ) {
    define('LINK', ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/' . Seo::link_SEO('zamowienia_szczegoly.php',$id_dodanej_pozycji_zamowienia,'zamowienie')); 
} else {
    define('LINK', $GLOBALS['tlumacz']['BRAK_DOSTEPU_DO_HISTORII']); 
}

define('DATA_ZAMOWIENIA', date("d-m-Y H:i"));
define('DOKUMENT_SPRZEDAZY', ( (int)$_POST['dokument'] == 1 ? $GLOBALS['tlumacz']['DOKUMENT_SPRZEDAZY_FAKTURA'] : $GLOBALS['tlumacz']['DOKUMENT_SPRZEDAZY_PARAGON'] ));
define('FORMA_PLATNOSCI', $filtr->process($_SESSION['rodzajPlatnosci']['platnosc_nazwa'])); 
define('FORMA_WYSYLKI', $filtr->process($_SESSION['rodzajDostawy']['wysylka_nazwa'])); 
$WysylkaInformacja = '';

if ( isset($_SESSION['rodzajDostawy']['opis']) ) {
    $WysylkaInformacja .= $_SESSION['rodzajDostawy']['opis'];
}
if ( isset($_SESSION['rodzajDostawy']['informacja']) )  {
    if ( $WysylkaInformacja != '' ) {
        $WysylkaInformacja .= '<br />';
    }
    $WysylkaInformacja .= $_SESSION['rodzajDostawy']['informacja'];
}

define('OPIS_FORMY_WYSYLKI', $WysylkaInformacja );
define('OPIS_FORMY_PLATNOSCI', $rodzajPlatnosciOpis);

$ListaProduktow = '<table style="width:100%;border-collapse: collapse; border-spacing:0;">';

foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    //
    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
    // elementy kupowania
    $Produkt->ProduktKupowanie();   
    // czas wysylki
    $Produkt->ProduktCzasWysylki();
    // stan produktu
    if ( KARTA_PRODUKTU_STAN_PRODUKTU == 'tak' ) {
         $Produkt->ProduktStanProduktu();
    }  
    // gwarancja produktu
    if ( KARTA_PRODUKTU_GWARANCJA == 'tak' ) {
         $Produkt->ProduktGwarancja();
    }     
    //
    // jezeli jest kupowanie na wartosci ulamkowe to sformatuje liczbe
    if ( $Produkt->info['jednostka_miary_typ'] == '0' ) {
         $TablicaZawartosci['ilosc'] = number_format( $TablicaZawartosci['ilosc'] , 2, '.', '' );
    }
    //
    
    $JakieCechy = '';
    if ( isset($CechyProdukty[ $TablicaZawartosci['id'] ]) ) {
        //
        foreach ( $CechyProdukty[ $TablicaZawartosci['id'] ] As $CechaProduktu ) {
            //
            $JakieCechy .= '<br /><span style="font-size:80%">' . $CechaProduktu['cecha'] . ': ' . $CechaProduktu['wartosc'] . '</span>';
            //
        }
        //
    }
    
    //
    // czy produkt ma komentarz
    $KomentarzProduktu = '';
    if ( $TablicaZawartosci['komentarz'] != '' ) {
        //
        $KomentarzProduktu = '<br /><span style="font-size:80%">' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' ' . $TablicaZawartosci['komentarz'] . '</span>';
        //
    }
    // czy sa pola tekstowe
    $PolaTekstowe = '';
    if ( $TablicaZawartosci['pola_txt'] != '' ) {
        //
        $TblPolTxt = Funkcje::serialCiag($TablicaZawartosci['pola_txt']);
        foreach ( $TblPolTxt as $WartoscTxt ) {
            //
            // jezeli pole to plik
            if ( $WartoscTxt['typ'] == 'plik' ) {
                $PolaTekstowe .= '<br /><span style="font-size:80%">' . $WartoscTxt['nazwa'] . ':</span> <a style="font-size:80%" href="' . ADRES_URL_SKLEPU . '/inne/wgranie.php?src=' . base64_encode(str_replace('.',';',$WartoscTxt['tekst'])) . '">' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</a>';
              } else {
                $PolaTekstowe .= '<br /><span style="font-size:80%">' . $WartoscTxt['nazwa'] . ': ' . $WartoscTxt['tekst'] . '</span>';
            }            
        }
        unset($TblPolTxt);
        //
    }    
    //   
    // producent produktu
    $ProducentProduktu = '';
    if ( !empty($Produkt->info['nazwa_producenta']) ) {
         $ProducentProduktu = '<br /><span style="font-size:80%">' .  $GLOBALS['tlumacz']['PRODUCENT'] . ': ' . $Produkt->info['nazwa_producenta'] . '</span>';
    }
    // czas wysylki produktu
    $CzasWysylkiProduktu = '';
    if ( !empty($Produkt->czas_wysylki) ) {
         $CzasWysylkiProduktu = '<br /><span style="font-size:80%">' .  $GLOBALS['tlumacz']['CZAS_WYSYLKI'] . ': ' . $Produkt->czas_wysylki . '</span>';
    }   
    // stan produktu
    $StanProduktu = '';
    if ( !empty($Produkt->stan_produktu) ) {
         $StanProduktu = '<br /><span style="font-size:80%">' .  $GLOBALS['tlumacz']['STAN_PRODUKTU'] . ': ' . $Produkt->stan_produktu . '</span>';
    } 
    // gwarancja
    $GwarancjaProduktu = '';
    if ( !empty($Produkt->gwarancja) ) {
         //
         // jezeli gwarancja jest linkiem
         if ( strpos($Produkt->gwarancja, 'href=') > -1 && strpos($Produkt->gwarancja, 'href="htt') === false ) {
             //
             $Produkt->gwarancja = str_replace('href="', 'href="' . ADRES_URL_SKLEPU . '/', $Produkt->gwarancja);
             //
         }
         //
         $GwarancjaProduktu = '<br /><span style="font-size:80%">' .  $GLOBALS['tlumacz']['GWARANCJA'] . ': ' . str_replace('<a ', '<a style="font-size:90%" ',$Produkt->gwarancja) . '</span>';
    }      
    //
    $ListaProduktow .= '<tr>';
    $ListaProduktow .= '<td style="width:50%;padding:5px">' . 
                              $Produkt->info['link_z_domena'] . 
                              $JakieCechy . 
                              $PolaTekstowe . 
                              $KomentarzProduktu . 
                              $ProducentProduktu .
                              $CzasWysylkiProduktu .
                              $StanProduktu .
                              $GwarancjaProduktu .
                        '</td>';
    $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $TablicaZawartosci['nr_katalogowy'] . '</td>';
    $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $GLOBALS['waluty']->WyswietlFormatCeny($TablicaZawartosci['cena_brutto'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</td>';
    $ListaProduktow .= '<td style="width:5%;padding:5px;text-align:center">' . $TablicaZawartosci['ilosc'] . '</td>';
    $ListaProduktow .= '<td style="width:15%;padding:5px;text-align:center">' . $GLOBALS['waluty']->WyswietlFormatCeny($TablicaZawartosci['cena_brutto'] * $TablicaZawartosci['ilosc'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</td>';
    $ListaProduktow .= '</tr>';
    //
    unset($Produkt, $CechaPrd, $JakieCechy, $KomentarzProduktu, $PolaTekstowe, $ProducentProduktu, $CzasWysylkiProduktu, $StanProduktu, $GwarancjaProduktu);
    //    
} 

$ListaProduktow .= '</table>';

define('LISTA_PRODUKTOW', $ListaProduktow); 
unset($ListaProduktow, $CechyProdukty);

$PodsumowanieTekst = '';
$KoncowaWartoscZamowienia = 0;
foreach ( $PodsumowanieTablica as $Podsuma ) {
    //
    if ( $Podsuma['klasa'] != 'ot_total' ) {
         $PodsumowanieTekst .= $Podsuma['nazwa'] . ': ' . $GLOBALS['waluty']->WyswietlFormatCeny($Podsuma['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false) . '<br />';
       } else {
         $PodsumowanieTekst .= '<span style="font-size:120%;font-weight:bold">' . $Podsuma['nazwa'] . ': <span style="font-size:140%">' . $GLOBALS['waluty']->WyswietlFormatCeny($Podsuma['wartosc'], $_SESSION['domyslnaWaluta']['id'], true, false) . '</span></span><br />';
         $KoncowaWartoscZamowienia = $Podsuma['wartosc'];
    }
    //
}
define('MODULY_PODSUMOWANIA', $PodsumowanieTekst); 
unset($PodsumowanieTablica, $PodsumowanieTekst);

// jezeli jest wlaczony program partnerski dodanie punktow do konta klienta
if ( SYSTEM_PUNKTOW_STATUS == 'tak' && PP_STATUS == 'tak' ) {
    //
    $IdPartnera = 0;
    // ustalenie czy jest id partnera w bazie klienta lub cookie
    // pierwszenstwo maja dane pobrane z bazy
    if ( isset($_COOKIE['pp']) && (int)$_COOKIE['pp'] > 0 ) {
         $IdPartnera = (int)$_COOKIE['pp'];
         //
         // jezeli jest ciasteczko musi sprawdzic czy jest to pierwsze zamowienie klienta
         if ( PP_NALICZANIE == 'pierwsze' ) {
              //
              // jezeli byly zeruje id partnera
              if ( Klient::IloscZamowien( $_SESSION['customer_email'], 'mail', $id_dodanej_pozycji_zamowienia ) > 0 ) {
                   $IdPartnera = 0;
              }
              //
         }
         //
    }
    if ( isset($_SESSION['pp_id']) && (int)$_SESSION['pp_id'] > 0 && PP_NALICZANIE == 'wszystkie' ) {
         $IdPartnera = (int)$_SESSION['pp_id'];
    }

    // musi sprawdzic czy jest ciastko z nr id klienta (partnera) oraz czy id partnera 
    // nie jest takie same jak klienta - zeby sam sobie nie robil zamowien
    if ( $IdPartnera > 0 && $IdPartnera != (int)$_SESSION['customer_id'] ) {
    
        // wartosc prowizji
        if ( PP_SPOSOB_NALICZANIA == 'procent' ) {
            //
            $IloscPunktow = ( $KoncowaWartoscZamowienia * (PP_PROWIZJA_PROCENT/100) ) * SYSTEM_PUNKTOW_WARTOSC;
            //
          } else {
            $IloscPunktow = PP_PROWIZJA;
        }
    
        $pola = array(
                array('customers_id',$IdPartnera),
                array('orders_id',$id_dodanej_pozycji_zamowienia),
                array('points',$IloscPunktow),
                array('date_added','now()'),
                array('points_status','1'),
                array('points_type','PP'));
                
        unset($IloscPunktow);
                
        $GLOBALS['db']->insert_query('customers_points' , $pola);
        unset($pola);
        
        // przypisuje id partnera do klienta 
        // jezeli bedzie wlaczone przyznawanie punktow za kolejne zamowienia to sklep bedzie widzial jakie jest id
        $pola = array(array('pp_id_customers', $IdPartnera));		
        $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$_SESSION['customer_id'] . "'");	
        unset($pola);         
        
        // id partnera do programu partnerskiego
        if ( PP_NALICZANIE == 'wszystkie' && !isset($_SESSION['pp_id']) ) {
             $_SESSION['pp_id'] = $IdPartnera;
        }    
        // usuwa ciasteczko
        if ( isset($_COOKIE['pp']) ) {
             setcookie("pp", "", time() - 3600, '/');
        }
    
    }
}

if ( !empty($_POST['komentarz']) ) {
     define('KOMENTARZ_DO_ZAMOWIENIA', $GLOBALS['tlumacz']['KOMENTARZ_DO_ZAMOWIENIA'] . '<br />' .$filtr->process($_POST['komentarz'])); 
   } else {
     define('KOMENTARZ_DO_ZAMOWIENIA', '');
}
 
$dane_do_faktury = '';
$dane_do_faktury .= $_SESSION['adresFaktury']['imie'] . ' ' . $_SESSION['adresFaktury']['nazwisko'];
if ( trim($dane_do_faktury) != '' ) {
   $dane_do_faktury .= '<br />';
}
if ( $_SESSION['adresFaktury']['firma'] != '' ) {
    //
    $dane_do_faktury .= $_SESSION['adresFaktury']['firma'] . '<br />';
    $dane_do_faktury .= $_SESSION['adresFaktury']['nip'] . '<br />';
    //
}
$dane_do_faktury .= $_SESSION['adresFaktury']['ulica'] . '<br />';
$dane_do_faktury .= $_SESSION['adresFaktury']['kod_pocztowy'] . ' ' . $_SESSION['adresFaktury']['miasto'] . '<br />';
if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    //
    $dane_do_faktury .= Klient::pokazNazweWojewodztwa($_SESSION['adresFaktury']['wojewodztwo']) . '<br />';
    //
}
$dane_do_faktury .= Klient::pokazNazwePanstwa($_SESSION['adresFaktury']['panstwo']); 
define('ADRES_ZAMAWIAJACEGO', $dane_do_faktury); 
unset($dane_do_faktury);

$dane_do_wysylki = '';
$dane_do_wysylki .= $_SESSION['adresDostawy']['imie'] . ' ' . $_SESSION['adresDostawy']['nazwisko'];
if ( trim($dane_do_wysylki) != '' ) {
   $dane_do_wysylki .= '<br />';
}
if ( $_SESSION['adresDostawy']['firma'] != '' ) {
    //
    $dane_do_wysylki .= $_SESSION['adresDostawy']['firma'] . '<br />';
    //
}
$dane_do_wysylki .= $_SESSION['adresDostawy']['ulica'] . '<br />';
$dane_do_wysylki .= $_SESSION['adresDostawy']['kod_pocztowy'] . ' ' . $_SESSION['adresDostawy']['miasto'] . '<br />';
if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
    //
    $dane_do_wysylki .= Klient::pokazNazweWojewodztwa($_SESSION['adresDostawy']['wojewodztwo']) . '<br />';
    //
}
$dane_do_wysylki .= Klient::pokazNazwePanstwa($_SESSION['adresDostawy']['panstwo']) . '<br />';
if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
    //
    $dane_do_wysylki .= $GLOBALS['tlumacz']['TELEFON_SKROCONY'] . $_SESSION['adresDostawy']['telefon'] . '<br />';
    //
}
define('ADRES_DOSTAWY', $dane_do_wysylki); 
unset($dane_do_wysylki);

// sprzedaz elektroniczna - generowanie linku do pobrania - sprawdza czy sa w zamowieniu pliki ktore maja sprzedaz elektroniczna
$zapytanie_online = "SELECT products_file_shopping FROM products_file_shopping WHERE products_id in (" . implode(',', $IdProduktowZamowienia) . ") and language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";
$sql_online = $GLOBALS['db']->open_query($zapytanie_online); 

if ((int)$GLOBALS['db']->ile_rekordow($sql_online) > 0) {

    // klasa zamowienia do wygenerowania linku do pobrania
    $zamowienie = new Zamowienie( $id_dodanej_pozycji_zamowienia );
    define('LINK_PLIKOW_ELEKTRONICZNYCH', '<br /><b>' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA'] . ' <a style="text-decoration:underline" href="' . ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link . '">' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA_LINK'] . '</a></b><br />'); 
    unset($zamowienie);
    
  } else {
  
    define('LINK_PLIKOW_ELEKTRONICZNYCH', ''); 
  
}

$GLOBALS['db']->close_query($sql_online);
unset($IdProduktowZamowienia);

$tekst = Funkcje::parsujZmienne($tekst);
$tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $tekst);

$wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $kopia_maila, $temat, $tekst, $szablon, $jezyk, $zalaczniki);

$GLOBALS['db']->close_query($sql);
 
/* nieuzywane - dodatkowo do przeslania do administratora sklepu zamowienia w formacie csv
unset($email);
$email = new Mailing;
// kopia z zalacznikiem dla administratora sklepu
ob_start();
$_SESSION['pobranieZamowienia'] = 'tak';
require('zarzadzanie/sprzedaz/zamowienia_pobierz.php');
$wynikPliku = ob_get_contents();
ob_end_clean();                        
$te = array ( array('ciag' => $wynikPliku, 'plik' => 'zamowienie_nr_' . $id_dodanej_pozycji_zamowienia . '.csv', 'typ' => 'text/plain') );
$wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,INFO_EMAIL_SKLEPU, $adresat_nazwa, '', $temat, $tekst, $szablon, $jezyk, $zalaczniki, $te);
*/

if ( SMS_WLACZONE == 'tak' && SMS_NOWE_ZAMOWIENIE == 'tak' && SMS_ODBIORCA != '' ) {

    $adresat   = SMS_ODBIORCA;
    $wiadomosc = strip_tags(Funkcje::parsujZmienne($tresc['description_sms']));

    SmsApi::wyslijSms($adresat, $wiadomosc);

}

unset($wiadomosc, $tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $temat, $tekst, $zalaczniki, $szablon, $jezyk, $adresat); 

// wylaczenie produktu po zakupie
if ( MAGAZYN_WYLACZ_PRODUKT == 'tak' ) {

    foreach ($_SESSION['koszyk'] AS $TablicaZawartosci) {
    
        $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $TablicaZawartosci['id'] ) );
        $AktualnaIloscProduktu = $Produkt->info['ilosc'];
        
        if ( $AktualnaIloscProduktu <= 0 ) {
            $pola = array(array('products_status','0'));        
            $GLOBALS['db']->update_query('products' , $pola, "products_id = '" . $Produkt->info['id'] . "'");
            unset($pola);
        }
        
        unset($Produkt, $AktualnaIloscProduktu);
        
    }

}

// zapisanie do sesji id nowego zamowienia
$_SESSION['zamowienie_id'] = $id_dodanej_pozycji_zamowienia;

if ( PDF_ZAPISANIE_ZAMOWIENIA == 'tak' ) {
    include_once('pdf/zamowienie_plik.php');
}

Funkcje::PrzekierowanieSSL( '/zamowienie-podsumowanie.html' );

?>