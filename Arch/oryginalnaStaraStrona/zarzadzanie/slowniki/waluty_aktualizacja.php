<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

function nazwa_aktualnego_kursu() {

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

if ( $domyslna_waluta['kod'] == 'PLN' ) {

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
      
    }
    
    $pola = array(
                  array('value', '1' ),
                  array('last_updated', 'now()' ),
                  );
    
    $db->update_query('currencies' , $pola, " code = '" . $domyslna_waluta['kod'] . "'");    
    
  } else {
  
    foreach ($xml->pozycja as $pozycja) {
    
      if ( $pozycja->kod_waluty == $domyslna_waluta['kod'] ) {
      
        $przelicznik = str_replace(',', '.', $pozycja->kurs_sredni);
        
      }
      
    }
    
    $zapytanie = "select currencies_id, currencies_marza, code from currencies where code != '" . $domyslna_waluta['kod'] . "'";
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
                  if ( $pozycja->kod_waluty == $domyslna_waluta['kod'] ) {
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
        
        $db->update_query('currencies' , $pola, " code = '" . $domyslna_waluta['kod'] . "'");              
        
        
    }
}

if (isset($_GET['wroc']) & $_GET['wroc'] == 'tak') {
    Funkcje::PrzekierowanieURL('waluty.php');
}

?>