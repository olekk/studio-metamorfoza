<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {
    
        $allegro = new Allegro(true,true);
        $tablica = array();

        for ( $i = 0, $c = count($_POST['opcja']); $i < $c; $i++ ) {

            $warunki = @unserialize($_POST['opcja'][$i]);

            $zapytanie = "SELECT * FROM allegro_auctions_sold WHERE auction_id = '".$warunki['aukcja_id']."' AND buyer_id = '".$warunki['buyer_id']."'";

            $sql = $db->open_query($zapytanie);

            if ( $db->ile_rekordow($sql) > 0 ) {
            
                while ($info = $sql->fetch_assoc()) {
                
                    $tablica[] = array(
                                     'fe-item-id' => floatval($info['auction_id']),
                                     'fe-use-comment-template' => '0',
                                     'fe-to-user-id' => $info['buyer_id'],
                                     'fe-comment' => $_POST['fe-comment'],
                                     'fe-comment-type' => $_POST['fe-comment-type'],
                                     'fe-op' => '2',
                                     'fe-rating' => array());
                }
              
            }

        }

        $blad = false;
        $komunikat = '';
        $wynik = $allegro->doFeedbackMany( $tablica );
        
        if ( count($wynik) > 0 ) {
        
            for ( $j = 0, $c = count($wynik); $j < $c; $j++ ) {
            
                $resultat = Funkcje::object2array($wynik[$j]);
                
                if ( $resultat['fe-id'] != '0' ) {
                
                    $pola = array(
                            array('auction_comments','1'));
                            
                    $db->update_query('allegro_auctions_sold' , $pola, " auction_id = '".$tablica[$j]['fe-item-id']."' AND buyer_id = '".$tablica[$j]['fe-to-user-id']."'");	
                    unset($pola);
                  
                } else {
                
                    $komunikat .= '<b>'.$resultat['fe-fault-code'] . ':</b><br />' . $resultat['fe-fault-desc'] . ' - ' . $resultat['fe-item-id'] . '<br />';
                  
                }
              
            }
            
            if ( $komunikat != '' ) {
            
                echo $allegro->PokazBlad('Błąd', $komunikat, 'allegro_sprzedaz.php' );
                
            }
          
        }
        
        Funkcje::PrzekierowanieURL('allegro_sprzedaz.php');

    } else {
    
        Funkcje::PrzekierowanieURL('allegro_sprzedaz.php');
      
    }

}
?>