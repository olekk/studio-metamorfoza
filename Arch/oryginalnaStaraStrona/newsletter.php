<?php

// plik
$WywolanyPlik = 'newsletter';

include('start.php');

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('NEWSLETTER') ), $GLOBALS['tlumacz'] );

$srodek->dodaj('__TEKST_DO_WYSWIETLENIA', '');

if (isset($_GET['email']) && isset($_GET['akcja']) && Funkcje::CzyPoprawnyMail($filtr->process($_GET['email']))) {
    //
    if ($_GET['akcja'] == 'potwierdz') {
        //
        // sprawdza czy jest adres w bazie
        $zapytanie = "SELECT subscribers_email_address FROM subscribers WHERE subscribers_email_address = '" . $filtr->process($_GET['email']) . "'";

        $sql = $db->open_query($zapytanie); 
        if ((int)$db->ile_rekordow($sql) > 0) {        
        
            $pola = array(
                    array('customers_newsletter','1'),
                    array('date_account_accept','now()'));
                    
            $GLOBALS['db']->update_query('subscribers' , $pola, " subscribers_email_address = '" . $filtr->process($_GET['email']) . "'");	
            unset($pola);      

            $srodek->dodaj('__TEKST_DO_WYSWIETLENIA', $GLOBALS['tlumacz']['NEWSLETTER_INFO_O_POTWIERDZENIU']);
        
        } else {
        
            $srodek->dodaj('__TEKST_DO_WYSWIETLENIA', $GLOBALS['tlumacz']['NEWSLETTER_BLAD']);
        
        }
        
        $db->close_query($sql);
        unset($zapytanie);

    }
    
    if ($_GET['akcja'] == 'usun') {
        //
        // sprawdza czy jest adres w bazie
        $zapytanie = "SELECT subscribers_email_address, customers_id FROM subscribers WHERE subscribers_email_address = '" . $filtr->process($_GET['email']) . "'";

        $sql = $db->open_query($zapytanie); 
        if ((int)$db->ile_rekordow($sql) > 0) { 

            $info = $sql->fetch_assoc(); 
            
            // jezeli jest to klient sklepu to wylaczy tylko z newslettera
            if ((int)$info['customers_id'] > 0) {
        
                $pola = array(array('customers_newsletter','0'), 
                              array('customers_newsletter_group',''));
                $GLOBALS['db']->update_query('subscribers' , $pola, " subscribers_email_address = '" . $filtr->process($_GET['email']) . "'");	
                unset($pola);
                
                $pola = array(array('customers_newsletter','0'), 
                              array('customers_newsletter_group',''));
                $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '" . (int)$info['customers_id'] . "'");	
                unset($pola);                

              } else {
              
                $db->delete_query('subscribers' , " subscribers_email_address = '" . $filtr->process($_GET['email']) . "'");	            
              
            }
            
            unset($info);

            $srodek->dodaj('__TEKST_DO_WYSWIETLENIA', $GLOBALS['tlumacz']['NEWSLETTER_USUNIECIE']);
        
        } else {
        
            $srodek->dodaj('__TEKST_DO_WYSWIETLENIA', $GLOBALS['tlumacz']['NEWSLETTER_BLAD']);
        
        }
        
        $db->close_query($sql);
        unset($zapytanie);

    }    
    
    // usuniecie adresu z mailingu
    if ($_GET['akcja'] == 'mailing') {
        //
        // sprawdza czy jest adres w bazie
        $zapytanie = "SELECT mailing_email_address FROM mailing WHERE mailing_email_address = '" . $filtr->process($_GET['email']) . "'";

        $sql = $db->open_query($zapytanie); 
        if ((int)$db->ile_rekordow($sql) > 0) { 

            $db->delete_query('mailing' , " mailing_email_address = '" . $filtr->process($_GET['email']) . "'");	            
              
            $srodek->dodaj('__TEKST_DO_WYSWIETLENIA', $GLOBALS['tlumacz']['NEWSLETTER_USUNIECIE']);
        
        } else {
        
            $srodek->dodaj('__TEKST_DO_WYSWIETLENIA', $GLOBALS['tlumacz']['NEWSLETTER_BLAD']);
        
        }
        
        $db->close_query($sql);
        unset($zapytanie);

    }      
   
} else {

    $srodek->dodaj('__TEKST_DO_WYSWIETLENIA', $GLOBALS['tlumacz']['NEWSLETTER_BLAD']);

}

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_NEWSLETTER']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>