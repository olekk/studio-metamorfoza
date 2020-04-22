<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {

    $NaglowekCsv = '';
    $CoDoZapisania = '';

    // uchwyt pliku, otwarcie do dopisania
    $fp = fopen($filtr->process($_POST['plik']), "a");
    // blokada pliku do zapisu
    flock($fp, 2);

    $ZapytanieKlient = "select * from customers where customers_guest_account = '0' order by customers_firstname, customers_lastname limit ".(int)$_POST['limit'].",1";
    $sqlKlient = $db->open_query($ZapytanieKlient);
    $infc = $sqlKlient->fetch_assoc();  

    // id domyslnego adresu
    $Adres = $infc['customers_default_address_id'];
    $IdKlient = $infc['customers_id'];
    
    $NaglowekCsv .= 'IdMagazyn;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_id_private']) . '";';    
    
    $NaglowekCsv .= 'Nick;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_nick']) . '";';
    
    $NaglowekCsv .= 'Imie;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_firstname']) . '";';

    $NaglowekCsv .= 'Nazwisko;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_lastname']) . '";';
    
    $NaglowekCsv .= 'Adres_email;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_email_address']) . '";';

    $NaglowekCsv .= 'Telefon;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_telephone']) . '";';
    
    $NaglowekCsv .= 'Haslo;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_password']) . '";'; 

    $NaglowekCsv .= 'Newsletter;';
    if ($infc['customers_newsletter'] == '1') {
        $CoDoZapisania .= '"tak";';        
      } else {
        $CoDoZapisania .= '"nie";';     
    }
    
    $NaglowekCsv .= 'Znizka;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($infc['customers_discount']) . '";'; 

    // grupa klienta
    $zapytanieGrupa = "select distinct customers_groups_name from customers_groups where customers_groups_id = '".$infc['customers_groups_id']."'";
    $sqlNazwaGrupa = $db->open_query($zapytanieGrupa);
    $infg = $sqlNazwaGrupa->fetch_assoc();

    $NaglowekCsv .= 'Grupa_klientow;';
    $CoDoZapisania .= '"' . $infg['customers_groups_name'] . '";'; 
    
    $db->close_query($sqlNazwaGrupa);
    unset($infg, $zapytanieGrupa);
    
    // status
    $NaglowekCsv .= 'Status;';
    if ($infc['customers_status'] == '1') {
        $CoDoZapisania .= '"aktywny";';        
      } else {
        $CoDoZapisania .= '"nieaktywny";';     
    }        
    
    $db->close_query($sqlKlient);
    unset($infc, $ZapytanieKlient);


    
    $ZapytanieKlientAdres = "select * from address_book where address_book_id = '" . $Adres . "' and customers_id = '" . $IdKlient . "'";
    $sqlAdres = $db->open_query($ZapytanieKlientAdres);
    $inft = $sqlAdres->fetch_assoc();        
    
    $NaglowekCsv .= 'Firma;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($inft['entry_company']) . '";';        
    
    $NaglowekCsv .= 'Nip;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($inft['entry_nip']) . '";';           
    
    $NaglowekCsv .= 'Pesel;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($inft['entry_pesel']) . '";';            
        
    $NaglowekCsv .= 'Ulica;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($inft['entry_street_address']) . '";'; 

    $NaglowekCsv .= 'Kod_pocztowy;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($inft['entry_postcode']) . '";';
    
    $NaglowekCsv .= 'Miasto;';
    $CoDoZapisania .= '"' . Funkcje::CzyszczenieTekstu($inft['entry_city']) . '";';

    // panstwo
    $zapytanieKraj = "select distinct countries_name from countries_description where countries_id = '".$inft['entry_country_id']."' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
    $sqlNazwaKraj = $db->open_query($zapytanieKraj);
    $infk = $sqlNazwaKraj->fetch_assoc();

    $NaglowekCsv .= 'Kraj;';
    $CoDoZapisania .= '"' . $infk['countries_name'] . '";'; 
    
    $db->close_query($sqlNazwaKraj);
    unset($infk, $zapytanieKraj, $Adres, $IdKlient);

    
    $CoDoZapisania .= 'KONIEC' . "\r\n";
    
    if ($_POST['limit'] == 0) {
        $CoDoZapisania = $NaglowekCsv . 'KONIEC' . "\r\n" . $CoDoZapisania;
    }          
    
    fwrite($fp, $CoDoZapisania);
    
    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp);        
        
}
?>