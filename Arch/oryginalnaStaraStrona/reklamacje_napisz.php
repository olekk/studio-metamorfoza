<?php

 // plik
$WywolanyPlik = 'reklamacje_napisz';

include('start.php');

//po wypelnieniu formularza
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    if ( Sesje::TokenSpr(true) ) {

        if ( $_POST['zamowienie_id'] != '' && $_POST['temat'] != '' && $_POST['wiadomosc'] != '' ) {

            //zapisanie danych klienta do bazy - START
            $Id_Reklamacji = Reklamacje::UtworzIdReklamacji(15);
            $pola = array(
                        array('complaints_rand_id',$Id_Reklamacji),
                        array('complaints_customers_orders_id',$filtr->process($_POST["zamowienie_id"])),
                        array('complaints_subject',$filtr->process($_POST["temat"])),
                        array('complaints_date_created','now()'),
                        array('complaints_date_modified','now()'),
                        array('complaints_service',''),
                        array('complaints_status_id',Reklamacje::domyslnyStatusReklamacji()),
                        array('complaints_customers_id',(int)$_SESSION['customer_id']),
                        array('complaints_customers_name',$filtr->process($_POST["imie"]) . ' ' . $filtr->process($_POST["nazwisko"])),
                        array('complaints_customers_email',$filtr->process($_POST["email"])),
            );

            $GLOBALS['db']->insert_query('complaints' , $pola);
            $id_dodanej_pozycji = $GLOBALS['db']->last_id_query();

            unset($pola);
                
            $pola = array(
                    array('complaints_id',$id_dodanej_pozycji),
                    array('complaints_status_id',Reklamacje::domyslnyStatusReklamacji()),
                    array('date_added','now()'),
                    array('comments',$filtr->process($_POST["wiadomosc"]))
            );

            $db->insert_query('complaints_status_history' , $pola);
            unset($pola);


            //Wyslanie maila do klienta - START
            $jezyk_maila = $_SESSION['domyslnyJezyk']['id'];

            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = 'EMAIL_REKLAMACJA_ZGLOSZENIE'";

            $sql = $GLOBALS['db']->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();        

            define('LINK', ( WLACZENIE_SSL == 'tak' ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU )."/reklamacje-szczegoly-rs-".$Id_Reklamacji.".html");  
            define('BIEZACA_DATA', date("d-m-Y H:i:s"));  
            define('KLIENT_IP', $filtr->process($_POST["adres_ip"]));  
            define('KLIENT', $filtr->process($_POST["imie"]) . ' ' . $filtr->process($_POST["nazwisko"]));  
            define('NUMER_ZAMOWIENIA', $filtr->process($_POST["zamowienie_id"]));  
            define('NUMER_REKLAMACJI', $Id_Reklamacji);  
            define('TYTUL_REKLAMACJI', $filtr->process($_POST["temat"]));
            define('OPIS_REKLAMACJI', $filtr->process($_POST["wiadomosc"]));

            $email = new Mailing;

            if ( $tresc['email_file'] != '' ) {
                $tablicaZalacznikow = explode(';', $tresc['email_file']);
            } else {
                $tablicaZalacznikow = array();
            }

            $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
            $cc              = Funkcje::parsujZmienne($tresc['dw']);

            $adresat_email   = $filtr->process($_POST['email']);
            $adresat_nazwa   = $filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko']);

            $temat           = Funkcje::parsujZmienne($tresc['email_title']);
            $tekst           = $tresc['description'];
            $zalaczniki      = $tablicaZalacznikow;
            $szablon         = $tresc['template_id'];
            $jezyk           = (int)$jezyk_maila;

            $tekst = Funkcje::parsujZmienne($tekst);
            $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $tekst);

            $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);

            $GLOBALS['db']->close_query($sql);
            unset($wiadomosc, $tresc, $zapytanie_tresc);             

            Funkcje::PrzekierowanieURL('reklamacje-napisz-sukces.html');

        }

    } else {
    
        Funkcje::PrzekierowanieURL('brak-strony.html');
        
    }        
        
}

if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {

    $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

    $tablica = array();
    $zapytanie_klient = "SELECT customers_firstname, customers_lastname, customers_email_address FROM customers WHERE customers_id = '".(int)$_SESSION['customer_id']."'";

    $sql_klient = $GLOBALS['db']->open_query($zapytanie_klient);
    $info_klient  = $sql_klient->fetch_assoc();

    $tablica = array('imie' => $info_klient['customers_firstname'],
                     'nazwisko' => $info_klient['customers_lastname'],
                     'email' => $info_klient['customers_email_address']);
                     
    $GLOBALS['db']->close_query($sql_klient);
    unset($zapytanie_klient, $info_klient);

    $tablica['zamowienia'][] = array('id' => '',
                                     'text' => $GLOBALS['tlumacz']['LISTING_WYBIERZ_OPCJE']);

    $zapytanie_zamowienia = "SELECT orders_id, date_purchased FROM orders WHERE customers_id = '".(int)$_SESSION['customer_id']."' ORDER BY orders_id DESC";
    $sql_zamowienia = $GLOBALS['db']->open_query($zapytanie_zamowienia);
    if ((int)$GLOBALS['db']->ile_rekordow($sql_zamowienia) > 0) {
      while ( $info_zamowienia = $sql_zamowienia->fetch_assoc() ) {
        $tablica['zamowienia'][] = array(
                                      'id' => $info_zamowienia['orders_id'],
                                      'text' => $GLOBALS['tlumacz']['KLIENT_NUMER_ZAMOWIENIA'] . ': ' . $info_zamowienia['orders_id'] . '; ' . $GLOBALS['tlumacz']['DATA_ZAMOWIENIA'] . ': ' . date('d-m-Y H:i:s',strtotime($info_zamowienia['date_purchased'])));
      }
    }

    // meta tagi
    $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
    $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
    $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
    $tpl->dodaj('__META_OPIS', $Meta['opis']);
    unset($Meta);

    // breadcrumb
    $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
    $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZGLOSZENIE_REKLAMACJI']);
    $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

    // wyglad srodkowy
    $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

    //parametry do podstawienia
    $srodek->dodaj('__DOMYSLNY_SZABLON', DOMYSLNY_SZABLON);
    $srodek->dodaj('__IMIE_KLIENTA', $tablica['imie']);
    $srodek->dodaj('__NAZWISKO_KLIENTA', $tablica['nazwisko']);
    $srodek->dodaj('__EMAIL_KLIENTA', $tablica['email']);
    $srodek->dodaj('__ZAMOWIENIA_KLIENTA', Funkcje::RozwijaneMenu('zamowienie_id', $tablica['zamowienia'], '', 'class="required" style="width:70%" id="zmowienieDropDown"'));
    
    $srodek->dodaj('__TOKEN',Sesje::Token());

    $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

    unset($srodek, $WywolanyPlik,  $tablica);

    include('koniec.php');
    
} else {

    Funkcje::PrzekierowanieSSL( 'logowanie.html' );

}
?>