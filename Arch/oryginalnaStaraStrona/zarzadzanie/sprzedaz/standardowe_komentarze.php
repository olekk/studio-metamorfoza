<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_POST['nazwy']) && $_POST['nazwy'] == 'tak' ) {

    if ( isset($_POST['jezyk']) && (int)$_POST['jezyk'] > 0 && isset($_POST['id']) && (int)$_POST['id'] > 0 ) {

        $zapytanie = "SELECT soc.comments_id, soc.comments_id, soc.comments_name
                        FROM standard_order_comments soc 
                       WHERE soc.status_id = '" . (int)$_POST['id'] . "'
                    ORDER BY soc.sort_order";
        
        $sql = $db->open_query($zapytanie);
        
        echo '<option value="0">--- wybierz z listy ---</option>';
        
        while ($info = $sql->fetch_assoc()) {
        
          echo '<option value="' . $info['comments_id'] . '">' . $info['comments_name'] . '</option>';

        }

        $db->close_query($sql);
        unset($zapytanie, $info);
        
    } else {
    
        echo '<option selected="selected" value="0">--- najpierw wybierz status zamówienia ---</option>';
    
    }
    
}

if ( isset($_POST['nazwy']) && $_POST['nazwy'] == 'nie' ) {

    if ( isset($_POST['jezyk']) && (int)$_POST['jezyk'] > 0 && isset($_POST['id']) && (int)$_POST['id'] > 0 ) {

        $zapytanie = "SELECT socd.comments_id, socd.comments_text 
                        FROM standard_order_comments_description socd
                       WHERE socd.languages_id = '" . (int)$_POST['jezyk'] . "' and socd.comments_id = '" . (int)$_POST['id'] . "'";
        
        $sql = $db->open_query($zapytanie);
        $info = $sql->fetch_assoc();
        
        // nr dokumentu kuriera
        if ( strpos( $info['comments_text'], '{' ) > -1 && !isset($_POST['tryb']) ) {
             //
             $zamowienie = new Zamowienie( (int)$_POST['id_zamowienia'] );
             //
             // nr przesylki
             define('NR_PRZESYLKI', $zamowienie->dostawy_nr_przesylki);
             
             // wartosc zamowienia
             define('WARTOSC_ZAMOWIENIA', $zamowienie->info['wartosc_zamowienia']);

             // ilosc punktow
             define('ILOSC_PUNKTOW', $zamowienie->ilosc_punktow);

             // dokument sprzedazy
             define('DOKUMENT_SPRZEDAZY', $zamowienie->info['dokument_zakupu_nazwa']);
             
             // forma platnosci
             define('FORMA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);
                
             // forma wysylki
             define('FORMA_WYSYLKI', $zamowienie->info['wysylka_modul']);

             // link plikow elektronicznych
             define('LINK_PLIKOW_ELEKTRONICZNYCH', ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link);
             
             $info['comments_text'] = Funkcje::parsujZmienne($info['comments_text']);
             $info['comments_text'] = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $info['comments_text']);             
             
             unset($zamowienie);
        }
        
        echo $info['comments_text'];

        $db->close_query($sql);
        unset($zapytanie, $info);
        
    }
    
}    
?>
