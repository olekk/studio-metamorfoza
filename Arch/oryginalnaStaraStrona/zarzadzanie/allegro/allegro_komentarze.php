<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {
    
        $allegro = new Allegro(true,true);

        $tabelaKomentarzy = $allegro->domyFeedbackLimit();
        
        $licznikKomentarzy = 0;
        
        $DoZapisaniaXML = '';

        foreach ($tabelaKomentarzy as $klucz => $wartosc ) {
        
            foreach ($wartosc as $komentarz) {
            
                // tylko pozytywne komentarze
                if ( (int)$komentarz[3] == 1 ) {
            
                    $DoZapisaniaXML .= "   <komentarz id=\"" . $licznikKomentarzy . "\">\n";
                    $DoZapisaniaXML .= "       <nick><![CDATA[" . $komentarz[10] . "]]></nick>\n";
                    $DoZapisaniaXML .= "       <nick_id>" . $komentarz[0] . "</nick_id>\n";
                    $DoZapisaniaXML .= "       <nick_komentarze>" . $komentarz[11] . "</nick_komentarze>\n";
                    $DoZapisaniaXML .= "       <typ>1</typ>\n";
                    $DoZapisaniaXML .= "       <data><![CDATA[" . $komentarz[2] . "]]></data>\n";
                    $DoZapisaniaXML .= "       <opis><![CDATA[" . $komentarz[4] . "]]></opis>\n";
                    $DoZapisaniaXML .= "   </komentarz>\n";  

                }
   
            }
            
            $licznikKomentarzy++;
            
        }    
        
        unset($allegro, $tabelaKomentarzy);
        
        $CoDoZapisania = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $CoDoZapisania .= "<offers xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1\">\n";
        $CoDoZapisania .= "<komentarze>\n";
        
        $CoDoZapisania .= $DoZapisaniaXML;

        $CoDoZapisania .= "</komentarze>\n";
        $CoDoZapisania .= "</offers>";
        
        unset($DoZapisaniaXML);

        $plikDoZapisu = KATALOG_SKLEPU . 'xml/komentarze.xml';
        
        // uchwyt pliku, otwarcie do dopisania
        $fp = fopen($plikDoZapisu, "w");
        // blokada pliku do zapisu
        flock($fp, 2); 

        fwrite($fp, $CoDoZapisania);

        // zapisanie danych do pliku
        flock($fp, 3);
        // zamkniecie pliku
        fclose($fp);        
        
        unset($CoDoZapisania);
        
        Funkcje::PrzekierowanieURL('/zarzadzanie/integracje/konfiguracja_zakladki.php?aktualizacja');
    
    } else {
    
        Funkcje::PrzekierowanieURL('allegro_logowanie.php?strona=konfiguracja_zakladki');
    
    }

}

?>