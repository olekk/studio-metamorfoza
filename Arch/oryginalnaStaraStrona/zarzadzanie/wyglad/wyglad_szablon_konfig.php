<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr() && isset($_POST['dane'])) {

    $PodzielDane = explode(';', $filtr->process($_POST['dane']));
    
    $DozwoloneStale = array('SZEROKOSC_SKLEPU',
                            'CZY_WLACZONA_LEWA_KOLUMNA',
                            'CZY_WLACZONA_PRAWA_KOLUMNA',
                            'SZEROKOSC_LEWEJ_KOLUMNY',
                            'SZEROKOSC_PRAWEJ_KOLUMNY',
                            'TLO_SKLEPU',
                            'TLO_SKLEPU_RODZAJ',
                            'NAGLOWEK_RODZAJ',
                            'NAGLOWEK');
    
    foreach ( $PodzielDane as $Stala ) {
    
        $PodzielStala = explode(':', $Stala);
        
        if ( count($PodzielStala) == 2 ) {
        
            if ( in_array( $filtr->process(trim($PodzielStala[0])), $DozwoloneStale) ) {
        
                $pola = array(
                        array('value',$filtr->process(trim($PodzielStala[1])))); 

                $sql = $db->update_query('settings', $pola, " code = '" . $filtr->process(trim($PodzielStala[0])) . "'");	
                
                unset($pola); 

            }
        
        }
    
    }
    
    unset($DozwoloneStale, $PodzielDane);

}
?>