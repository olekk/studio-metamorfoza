<?php
$WywolanyPlik    = 'platnosci_sukces';

include('start.php');

if ( !isset($_SESSION['platnoscElektroniczna']) ) {

    $tablicaGet = $_GET;
    if ( isset($_POST) ) {
    
        $tablicaPost = $_POST;
        $platnosci = array_merge($tablicaGet, $tablicaPost);
        
    } else {
    
        $platnosci = $tablicaGet;
        
    }
    if ( isset($platnosci['nrzam']) && $platnosci['nrzam'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['nrzam'];
        unset($platnosci['nrzam']);
        
    }

    //Numer zamowienia z CashBill
    if ( isset($platnosci['userdata']) && $platnosci['userdata'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['userdata'];
        unset($platnosci['userdata']);
        
    }

    //Numer zamowienia z Payeezy
    if ( isset($platnosci['order_id']) && $platnosci['order_id'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['order_id'];
        unset($platnosci['order_id']);
        
    }

    if ( isset($platnosci['orderNumber']) && $platnosci['orderNumber'] != '' ) {
    
        $platnosci['zamowienie_id'] = $platnosci['orderNumber'];
        unset($platnosci['orderNumber']);
        
    }

    $_SESSION['platnoscElektroniczna'] = $platnosci;

    unset($tablicaGet, $tablicaPost, $platnosci);
    Funkcje::PrzekierowanieURL('platnosc_koniec.php');

}


if ( !isset($_SESSION['platnoscElektroniczna']['zamowienie_id']) ) {
    $_SESSION['platnoscElektroniczna']['zamowienie_id'] = '0';
}

if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 ) {

    // sprawdzenie czy istnieje zamowienie w bazie danych
    $zapytanie = "SELECT orders_id FROM orders WHERE orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "' AND customers_id = '".(int)$_SESSION['customer_id']."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('PLATNOSCI','PRZYCISKI') ), $GLOBALS['tlumacz'] );

        $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
        // meta tagi
        $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
        $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
        $tpl->dodaj('__META_OPIS', $Meta['opis']);
        unset($Meta);

        $komunikat       = '';
        $komunikat_bledu = '';

        // ##############################################################################
        // platnosc DotPay
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'dotpay' ) {
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc Payeezy
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'payeezy' ) {

            $komentarz = '';

            //$status_zamowienia_id  = Funkcje::PokazDomyslnyStatusZamowienia();
            //$tranzakcjaPoprawna    = false;

            //if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' && $_SESSION['platnoscElektroniczna']['response_code'] == '35' ) {
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik          = 'platnosci_sukces';
                $komunikat             = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];

            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $status_zamowienia_id  = Funkcje::PokazDomyslnyStatusZamowienia();

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
                $komentarz          .= $GLOBALS['tlumacz']['DATA_TRANZAKCJI'].': ' . date("d-m-Y H:i:s") . '<br />';
                $komentarz          .= 'Status transakcji: ' . $_SESSION['platnoscElektroniczna']['message'];

                $pola = array(
                        array('orders_id ',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                        array('orders_status_id',$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified ','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                $blad = $_SESSION['platnoscElektroniczna']['message'];
                $komunikat_bledu = $blad;

            }

            //unset($tranzakcjaPoprawna, $komentarz);

        }

        // ##############################################################################
        // platnosc PayByNet
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'pbn' ) {
        
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
            
        }

        // ##############################################################################
        // platnosc Przelewy24
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'przelewy24' ) {
        
            $status_zamowienia_id  = Funkcje::PokazDomyslnyStatusZamowienia();
            $tranzakcjaPoprawna    = false;

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik          = 'platnosci_sukces';
                $komunikat             = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];

                $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_PRZELEWY24_%'";
                $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

                while ($info_p = $sql_p->fetch_assoc()) {
                    define($info_p['kod'], $info_p['wartosc']);
                }
                $GLOBALS['db']->close_query($sql_p);
                unset($zapytanie_p, $info_p, $sql_p);

                $zapytanie_p = "SELECT value FROM orders_total WHERE orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "' AND class='ot_total'";
                $sql_p = $db->open_query($zapytanie_p);
                
                if ($GLOBALS['db']->ile_rekordow($sql_p) > 0 ) {
                    $info_p = $sql_p->fetch_assoc();
                    $kwota = $info_p['value'];
                }
                
                $GLOBALS['db']->close_query($sql_p);
                unset($zapytanie_p, $info_p, $sql_p);

                $WYNIK = PlatnosciElektroniczne::p24_weryfikuj(PLATNOSC_PRZELEWY24_ID,$_SESSION['platnoscElektroniczna']["p24_session_id"], $_SESSION['platnoscElektroniczna']["p24_order_id"], $kwota*100, PLATNOSC_PRZELEWY24_SANDBOX);

                if($WYNIK[0] == "TRUE") {

                    $komentarz           = $GLOBALS['tlumacz']['NUMER_TRANZAKCJI'].': ' . $_SESSION['platnoscElektroniczna']['p24_order_id_full'] . '<br />';
                    $komentarz          .= $GLOBALS['tlumacz']['DATA_TRANZAKCJI'].': ' . date("d-m-Y H:i:s") . '<br />';
                    $tranzakcjaPoprawna  = true;

                    if ( PLATNOSC_PRZELEWY24_STATUS_ZAMOWIENIA > 0 ) {
                        $status_zamowienia_id = PLATNOSC_PRZELEWY24_STATUS_ZAMOWIENIA;
                    }

                } else {

                    $komentarz       = $GLOBALS['tlumacz']['NUMER_TRANZAKCJI'].': ' . $_SESSION['platnoscElektroniczna']['p24_order_id_full'] . '<br />';
                    $komentarz      .= $GLOBALS['tlumacz']['DATA_TRANZAKCJI'].': ' . date("d-m-Y H:i:s") . '<br />';
                    $komentarz      .= $GLOBALS['tlumacz']['PLATNOSCI_BLAD'].': ' . $WYNIK[1] . ' ' . $WYNIK[2];
                    $komunikat_bledu = $WYNIK[1] . ' ' . $WYNIK[2];

                }

            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];

                $blad = PlatnosciElektroniczne::p24_tablicaBledow($_SESSION['platnoscElektroniczna']['p24_error_code']);

                $komentarz  = $GLOBALS['tlumacz']['NUMER_TRANZAKCJI'].': ' . $_SESSION['platnoscElektroniczna']['p24_order_id_full'] . '<br />';
                $komentarz .= $GLOBALS['tlumacz']['DATA_TRANZAKCJI'].': ' . date("d-m-Y H:i:s") . '<br />';
                $komentarz .= $GLOBALS['tlumacz']['PLATNOSCI_BLAD'].': ' . $blad;

                $komunikat_bledu = $blad;

                unset($blad);
            }

            $pola = array(
                    array('orders_id ',(int)$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                    array('orders_status_id',$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified ','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            if ( $tranzakcjaPoprawna ) {
                $pola = array(
                        array('orders_status ',$status_zamowienia_id),
                        array('payment_info ',''),
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
                unset($pola);
            }

            unset($tranzakcjaPoprawna, $komentarz);
            
        }

        // ##############################################################################
        // platnosc PayU
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'payu' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];

                $blad = PlatnosciElektroniczne::payu_tablicaBledow($_SESSION['platnoscElektroniczna']['error']);
                $komunikat_bledu = $blad;
            }
        }

        // ##############################################################################
        // platnosc PayPal
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'paypal' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                if ( isset($_SESSION['platnoscElektroniczna']['error']) ) {
                    $komunikat_bledu = $_SESSION['platnoscElektroniczna']['error'];
                }
            }
        }

        // ##############################################################################
        // platnosc Transferuj
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'transferuj' ) {

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc CashBill
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'cashbill' ) {
            if ( $_SESSION['platnoscElektroniczna']['status'] == 'ok' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['DZIEKUJEMY_ZA_PLATNOSC'];
                
            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'err' ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_BLAD'];
                
            }
        }

        // ##############################################################################
        // platnosc Santander raty
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'santander' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {

                $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_SANTANDER_%'";
                $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

                while ($info_p = $sql_p->fetch_assoc()) {
                    define($info_p['kod'], $info_p['wartosc']);
                }
                $GLOBALS['db']->close_query($sql_p);
                unset($zapytanie_p, $info_p, $sql_p);


                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'];

                $komentarz  = $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'].': ' . $_SESSION['platnoscElektroniczna']['id_wniosku'] . '<br />';
                $komentarz .= $GLOBALS['tlumacz']['DATA_REJESTRACJI_WNIOSKU_RATALNEGO'].': ' . date("d-m-Y H:i:s") . '<br />';

                if ( PLATNOSC_LUKAS_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_LUKAS_STATUS_ZAMOWIENIA;
                }

            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'];
                $komentarz       = '';

                if ( isset($_SESSION['platnoscElektroniczna']['id_wniosku']) ) {
                    $komentarz  .= $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'].': ' . $_SESSION['platnoscElektroniczna']['id_wniosku'] . '<br />';
                }
                $komentarz .= $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'].'<br />';

            }

            $pola = array(
                    array('orders_id ',$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                    array('orders_status_id',$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified ','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            $pola = array(
                    array('orders_status ',$status_zamowienia_id),
                    array('payment_info ',''),
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
            unset($pola);
        }

        // ##############################################################################
        // platnosc mBank RATY
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'mbank' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();

            $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_MBANK_%'";
            $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

            while ($info_p = $sql_p->fetch_assoc()) {
                define($info_p['kod'], $info_p['wartosc']);
            }
            $GLOBALS['db']->close_query($sql_p);
            unset($zapytanie_p, $info_p, $sql_p);

            $sig = md5($_SESSION['platnoscElektroniczna']['nrwniosku'] . $_SESSION['platnoscElektroniczna']['zamowienie_id'] . PLATNOSC_MBANK_NUMER_SKLEPU);

            if ( $sig == $_SESSION['platnoscElektroniczna']['sig'] ) {
            
                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'] . '<br /><br />' . $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'] .': <b>'.$_SESSION['platnoscElektroniczna']['nrwniosku'].'</b>';
                $komentarz       = '';

                if ( isset($_SESSION['platnoscElektroniczna']['nrwniosku']) ) {
                    $komentarz  .= $GLOBALS['tlumacz']['NUMER_WNIOSKU_RATALNEGO'].': ' . $_SESSION['platnoscElektroniczna']['nrwniosku'] . '<br />';
                }

                if ( PLATNOSC_MBANK_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_MBANK_STATUS_ZAMOWIENIA;
                }

                $pola = array(
                        array('orders_id ',$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                        array('orders_status_id',$status_zamowienia_id),
                        array('date_added','now()'),
                        array('customer_notified ','0'),
                        array('customer_notified_sms','0'),
                        array('comments',$komentarz)
                );
                $GLOBALS['db']->insert_query('orders_status_history' , $pola);
                unset($pola);

                // zmina statusu zamowienia
                $pola = array(
                        array('orders_status ',$status_zamowienia_id),
                        array('payment_info ',''),
                );
                $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
                unset($pola);

            } else {
                Funkcje::PrzekierowanieURL('/');
            }
        }

        // ##############################################################################
        // platnosc Lukas RATY
        if ( $_SESSION['platnoscElektroniczna']['typ'] == 'agricole' ) {

            $status_zamowienia_id = Funkcje::PokazDomyslnyStatusZamowienia();

            if ( $_SESSION['platnoscElektroniczna']['status'] == 'OK' ) {

                $zapytanie_p = "SELECT kod, wartosc FROM modules_payment_params WHERE kod LIKE '%_LUKAS_%'";
                $sql_p = $GLOBALS['db']->open_query($zapytanie_p);

                while ($info_p = $sql_p->fetch_assoc()) {
                    define($info_p['kod'], $info_p['wartosc']);
                }
                $GLOBALS['db']->close_query($sql_p);
                unset($zapytanie_p, $info_p, $sql_p);


                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_SUKCES']);
                $WywolanyPlik    = 'platnosci_sukces';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_PRZYJETY'];

                $komentarz = $GLOBALS['tlumacz']['DATA_REJESTRACJI_WNIOSKU_RATALNEGO'].': ' . date("d-m-Y H:i:s") . '<br />';

                if ( PLATNOSC_LUKAS_STATUS_ZAMOWIENIA > 0 ) {
                    $status_zamowienia_id = PLATNOSC_LUKAS_STATUS_ZAMOWIENIA;
                }

            } elseif ( $_SESSION['platnoscElektroniczna']['status'] == 'FAIL' ) {

                $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_PLATNOSCI_BLAD']);
                $WywolanyPlik    = 'platnosci_blad';
                $komunikat       = $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'];
                $komentarz       = '';

                $komentarz .= $GLOBALS['tlumacz']['PLATNOSCI_WNIOSEK_RATALNY_ANULOWANY'].'<br />';

            }

            $pola = array(
                    array('orders_id ',$_SESSION['platnoscElektroniczna']['zamowienie_id']),
                    array('orders_status_id',$status_zamowienia_id),
                    array('date_added','now()'),
                    array('customer_notified ','0'),
                    array('customer_notified_sms','0'),
                    array('comments',$komentarz)
            );
            $GLOBALS['db']->insert_query('orders_status_history' , $pola);
            unset($pola);

            // zmina statusu zamowienia
            $pola = array(
                    array('orders_status ',$status_zamowienia_id),
                    array('payment_info ',''),
            );
            $GLOBALS['db']->update_query('orders' , $pola, "orders_id = '" . (int)$_SESSION['platnoscElektroniczna']['zamowienie_id'] . "'");
            unset($pola);
        }

        // breadcrumb
        $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

        // wyglad srodkowy
        $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

        $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());
        $tpl->dodaj('__KOMUNIKAT', $komunikat);
        $tpl->dodaj('__KOMUNIKAT_BLEDU', $komunikat_bledu);

        unset($srodek, $WywolanyPlik);

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $sql);

        unset($_SESSION['platnoscElektroniczna']);

        include('koniec.php');

        if ( $_SESSION['gosc'] == '1' ) {
            unset($_SESSION['adresDostawy'], $_SESSION['adresFaktury'], $_SESSION['customer_firstname'], $_SESSION['customer_default_address_id'], $_SESSION['customer_id']);
        }

    } else {
    
        Funkcje::PrzekierowanieURL('/');
        
    }

} else {

   Funkcje::PrzekierowanieURL('/');

}


?>