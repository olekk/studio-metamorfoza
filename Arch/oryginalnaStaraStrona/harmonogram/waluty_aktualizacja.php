<?php
chdir('../'); 

// ************** czesc kodu wymagana w przypadku zadan harmonogramu zadan **************

// zmienna zeby nie odczytywalo ponownie crona
$BrakCron = true;

// wczytanie ustawien inicjujacych system
//require_once('ustawienia/init.php');
define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');
define('WLACZENIE_CACHE', 'tak');

require_once('ustawienia/ustawienia_db.php');
include('klasy/Bazadanych.php');
$db = new Bazadanych();
include('klasy/Funkcje.php');
include('klasy/CacheSql.php');

$GLOBALS['cache'] = new CacheSql();

// ************** koniec **************

// funkcja aktualizacji kursu
function nazwa_aktualnego_kursu() {

    // pobieranie danych curlem
    $tabela = curl_init();
    curl_setopt($tabela, CURLOPT_URL, 'http://www.nbp.pl/Kursy/KursyA.html');
    curl_setopt ($tabela, CURLOPT_RETURNTRANSFER, 1);
    $tresc = curl_exec ($tabela);
    curl_close ($tabela);

    $wzorzec = '/xml\/[\d\w]+\.xml/';
    $sukces  = preg_match($wzorzec, $tresc, $pasujace);

    if (empty($pasujace))
    exit('Blad: Nie znaleziono tabeli kursow.');

    return 'http://www.nbp.pl/Kursy/'.$pasujace[0];
  
}

$er_url = nazwa_aktualnego_kursu();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $er_url);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
$rates = curl_exec ($ch);
curl_close ($ch);

$xml = new SimpleXMLElement($rates);

$zapytanieDomyslnaWaluta = "SELECT c.code FROM languages l LEFT JOIN currencies c ON c.currencies_id = l.currencies_default WHERE languages_default = '1'";
$sqlDomyslnaWaluta = $db->open_query($zapytanieDomyslnaWaluta);
$waluta = $sqlDomyslnaWaluta->fetch_assoc();
$DomyslnaWaluta = $waluta['code'];
$db->close_query($sqlDomyslnaWaluta);
unset($zapytanieDomyslnaWaluta,$sqlDomyslnaWaluta,$waluta);


if ( $DomyslnaWaluta == 'PLN' ) {

    foreach ($xml->pozycja as $pozycja) {
    
        $zapytanie = "select currencies_id, currencies_marza, code from currencies where code = '" . $pozycja->kod_waluty . "'";
        $sql = $db->open_query($zapytanie);

        if ($db->ile_rekordow($sql) > 0) {
        
            $waluta = $sql->fetch_assoc();              
            $marza = $waluta['currencies_marza'];
            if ((int)$marza > 0) {
                $mar = 1 + ( $marza/100 );
                $mar = 1;
               } else {
                $mar = 1;
            }

            $pola = array(
                          array('value', (1/str_replace(',', '.', $pozycja->kurs_sredni)) * $mar ),
                          array('last_updated', 'now()' ),
                          );

            $db->update_query('currencies' , $pola, " code = '" . $pozycja->kod_waluty . "'");
            
        }
        
        $db->close_query($sql);
        unset($sql);
        
    }
    
    $pola = array(
                  array('value', '1' ),
                  array('last_updated', 'now()' ),
                  );
    
    $db->update_query('currencies' , $pola, " code = '" . $DomyslnaWaluta . "'");    
    
  } else {
  
    foreach ($xml->pozycja as $pozycja) {
    
        if ( $pozycja->kod_waluty == $DomyslnaWaluta ) {

            $przelicznik = str_replace(',', '.', $pozycja->kurs_sredni);

        }
      
    }
    
    $zapytanie = "select currencies_id, currencies_marza, code from currencies where code != '" . $DomyslnaWaluta . "'";
    $sql = $db->open_query($zapytanie);

    if ($db->ile_rekordow($sql) > 0) {
        
        while ($waluta = $sql->fetch_assoc()) {
        
            $kurs = 0;
            $marza = $waluta['currencies_marza'];
            if ((int)$marza > 0) {
                $mar = 1 + ( $marza/100 );
                $mar = 1;
              } else {
                $mar = 1;
            }
            
            if ( $waluta['code'] == 'PLN' && $domyslna_waluta['kod'] != 'PLN' ) {

                foreach ($xml->pozycja as $pozycja) {
                  if ( $pozycja->kod_waluty == $DomyslnaWaluta ) {
                    $kurs = round(str_replace(',', '.', $pozycja->kurs_sredni),4);
                  }
                }                     
                 
              } else {
                       
                foreach ($xml->pozycja as $pozycja) {
                  if ( $pozycja->kod_waluty == $waluta['code'] ) {
                    $kurs = round($przelicznik / str_replace(',', '.', $pozycja->kurs_sredni),4);
                  }
                }
                
            }

            $pola = array(
                          array('value', $kurs * $mar ),
                          array('last_updated', 'now()' ),
                          );
            
            $db->update_query('currencies' , $pola, " code = '" . $waluta['code'] . "'");	  

        }
        
        $pola = array(
                      array('value', '1' ),
                      array('last_updated', 'now()' ),
                      );
        
        $db->update_query('currencies' , $pola, " code = '" . $DomyslnaWaluta . "'");              
        
    }
    
    $db->close_query($sql);
    unset($sql);
    
}

// ************** czesc kodu wymagana w przypadku zadan harmonogramu zadan - jezeli skrypt dotyczy produktow - musi zostac wyczyszczony cache **************

$GLOBALS['cache']->UsunCacheProduktow();

// ************** koniec **************

?>