<?php

  // plik
  $WywolanyPlik = 'reklamacje_szczegoly';

  include('start.php');

  if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_GET['id']) && $_GET['id'] != '' ) {

      $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'REKLAMACJE') ), $GLOBALS['tlumacz'] );

      $zapytanie = "SELECT * FROM complaints cu
                    LEFT JOIN customers c ON cu.complaints_customers_id = c.customers_id
                    LEFT JOIN address_book a ON c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id where cu.complaints_rand_id = '" . $filtr->process($_GET['id']) . "'";

      $sql = $GLOBALS['db']->open_query($zapytanie);
    
      if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {

        $tablica = array();
    
        $info = $sql->fetch_assoc();

        $id_reklamacji = $info['complaints_id'];

        // pobieranie informacji od uzytkownikach
        if ($info['complaints_service'] > 0) {
          //
          $zapytanie_uzytkownicy = "SELECT * FROM admin WHERE admin_id = '" . $info['complaints_service'] . "'";
          $sql_uzytkownicy = $GLOBALS['db']->open_query($zapytanie_uzytkownicy);
          $uzytkownicy = $sql_uzytkownicy->fetch_assoc();
          $obsluga = $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname'];
          $GLOBALS['db']->close_query($sql_uzytkownicy); 
          unset($zapytanie_uzytkownicy, $uzytkownicy);
        } else {
          $obsluga = '-';
        }
         //

        $tablica = array('id' => $filtr->process($_GET['id']),
                         'tytul_reklamacji' => $info['complaints_subject'],
                         'data_zgloszenia' => date('d-m-Y H:i:s', strtotime($info['complaints_date_created'])),
                         'data_modyfikacji' => date('d-m-Y H:i:s', strtotime($info['complaints_date_modified'])),
                         'nazwa_klienta' => $info['entry_firstname'] . ' ' . $info['entry_lastname'],
                         'adres_klienta_1' => $info['entry_street_address'],
                         'adres_klienta_2' => $info['entry_postcode']. ' ' . $info['entry_city'],
                         'email' => $info['complaints_customers_email'],
                         'numer_zamowienia' => $info['complaints_customers_orders_id'],
                         'opiekun' => $obsluga,
                         'aktualny_status' => Reklamacje::pokazNazweStatusuReklamacji($info['complaints_status_id'],$_SESSION['domyslnyJezyk']['id']));
                         
        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);

        $zapytanie_statusy = "SELECT complaints_status_id, date_added, customer_notified, comments FROM complaints_status_history WHERE complaints_id = '" . (int)$id_reklamacji . "' ORDER BY date_added";
        $sql_statusy = $GLOBALS['db']->open_query($zapytanie_statusy);

        $tablica_statusow = array();
        if ((int)$GLOBALS['db']->ile_rekordow($sql_statusy) > 0) {
          while ($info_statusy = $sql_statusy->fetch_assoc()) {
            $tablica_statusow[] = array(
                                      'id_statusu' => $info_statusy['complaints_status_id'],
                                      'klient_powiadomiony' => $info_statusy['customer_notified'],
                                      'data_dodania' => date('d-m-Y H:i:s', strtotime($info_statusy['date_added'])),
                                      'komentarz' => $info_statusy['comments'],
                                      'status' => Reklamacje::pokazNazweStatusuReklamacji($info_statusy['complaints_status_id'],$_SESSION['domyslnyJezyk']['id']));
                                      
          }
        }
        $GLOBALS['db']->close_query($sql_statusy);
        unset($zapytanie_statusy, $info_statusy);

        // meta tagi
        $Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
        $tpl->dodaj('__META_TYTUL', $Meta['tytul']);
        $tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
        $tpl->dodaj('__META_OPIS', $Meta['opis']);
        unset($Meta);

        // breadcrumb
        $nawigacja->dodaj($GLOBALS['tlumacz']['PANEL_KLIENTA'],Seo::link_SEO('panel_klienta.php', '', 'inna'));
        $nawigacja->dodaj($GLOBALS['tlumacz']['PRZEGLADAJ_REKLAMACJE'],Seo::link_SEO('reklamacje_przegladaj.php', '', 'inna'));
        $nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_SZCZEGOLY_REKLAMACJI']);
        $tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

        // wyglad srodkowy
        $srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik), $tablica, $tablica_statusow);

        //parametry do podstawienia
        $srodek->dodaj('__ID_ZGLOSZENIA', $tablica['id']);
        $srodek->dodaj('__TYTUL_ZGLOSZENIA', $tablica['tytul_reklamacji']);
        $srodek->dodaj('__DATA_ZGLOSZENIA', $tablica['data_zgloszenia']);
        $srodek->dodaj('__DATA_MODYFIKACJI', $tablica['data_modyfikacji']);
        $srodek->dodaj('__OPIEKUN_ZGLOSZENIA', $tablica['opiekun']);
        $srodek->dodaj('__STATUS_ZGLOSZENIA', $tablica['aktualny_status']);
        $srodek->dodaj('__NAZWA_KLIENTA', $tablica['nazwa_klienta']);
        $srodek->dodaj('__EMAIL_KLIENTA', $tablica['email']);
        $srodek->dodaj('__NUMER_ZAMOWIENIA', $tablica['numer_zamowienia']);

        $tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

        unset($srodek, $WywolanyPlik, $tablica);

      } else {
        //
        $GLOBALS['db']->close_query($sql);
        unset($WywolanyPlik, $zapytanie);    
        //
        Funkcje::PrzekierowanieURL('brak-strony.html'); 
        //
      }      

      include('koniec.php');

  } else {

      Funkcje::PrzekierowanieSSL( 'logowanie.html' );

  }
?>