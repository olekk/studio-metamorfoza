<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    // rozdziela serializowane dane z ajaxa na tablice POST
    parse_str($_POST['data'], $PostTablica);
    unset($_POST['data']);
    $_POST = $PostTablica;
    
    if (get_magic_quotes_gpc()) {
        $_POST = Funkcje::stripslashes_array($_POST);
    }

    if (isset($_POST['emailprzypomnienie']) && $_POST['emailprzypomnienie'] != '' && Sesje::TokenSpr()) {
    
        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('LOGOWANIE') ), $GLOBALS['tlumacz'] );

        $jezyk_maila = $_SESSION['domyslnyJezyk']['id'];

        $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$jezyk_maila."' WHERE t.email_var_id = 'EMAIL_PRZYPOMNIENIE_HASLA_KLIENTA'";
        $sql = $GLOBALS['db']->open_query($zapytanie_tresc);
        $tresc = $sql->fetch_assoc();        

        $zapytanie_klient = "SELECT customers_id, customers_email_address, customers_firstname, customers_lastname, language_id FROM customers WHERE customers_status = '1' and customers_email_address = '".$filtr->process($_POST['emailprzypomnienie'])."' and customers_guest_account != '1'";
        $sql_klient = $GLOBALS['db']->open_query($zapytanie_klient);
        $info_klient = $sql_klient->fetch_assoc();

        $haslo = Funkcje::generujHaslo();

        $haslo_zakodowane = Funkcje::zakodujHaslo($haslo);
        $pola = array(array('customers_password',$haslo_zakodowane));
        $GLOBALS['db']->update_query('customers' , $pola, " customers_id = '".(int)$info_klient['customers_id']."'");
        unset($pola);

        define('HASLO', $haslo);  
        define('BIEZACA_DATA', date("d-m-Y H:i:s"));  
        define('KLIENT_IP', $filtr->process($_POST["adres_ip"]));  

        $email = new Mailing;

        if ( $tresc['email_file'] != '' ) {
            $tablicaZalacznikow = explode(';', $tresc['email_file']);
        } else {
            $tablicaZalacznikow = array();
        }

        $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
        $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
        $cc              = Funkcje::parsujZmienne($tresc['dw']);

        $adresat_email   = $filtr->process($_POST['emailprzypomnienie']);
        $adresat_nazwa   = $info_klient['customers_firstname'] . ' ' . $info_klient['customers_lastname'];

        $temat           = Funkcje::parsujZmienne($tresc['email_title']);
        $tekst           = $tresc['description'];
        $zalaczniki      = $tablicaZalacznikow;
        $szablon         = $tresc['template_id'];
        $jezyk           = (int)$jezyk_maila;

        $tekst = Funkcje::parsujZmienne($tekst);
        $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $tekst);

        $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki);

        $GLOBALS['db']->close_query($sql_klient);
        $GLOBALS['db']->close_query($sql);
        unset($wiadomosc, $zapytanie_klient, $info_klient, $tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $temat, $tekst, $zalaczniki, $szablon, $jezyk);   

        echo '<div id="PopUpInfo">';  

        echo $GLOBALS['tlumacz']['ODZYSKIWANIE_HASLA_SUKCES'];

        echo '</div>';
        
        echo '<div id="PopUpPrzyciski">';
        
            if ( WLACZENIE_SSL == 'tak' ) {
                $link = ADRES_URL_SKLEPU_SSL . '/logowanie.html';
              } else {
                $link = 'logowanie.html';
            }     

            echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_ZALOGUJ'].'</a>';             
        
            if ( WLACZENIE_SSL == 'tak' ) {
              $link = ADRES_URL_SKLEPU;
            } else {
              $link = '/';
            }                
            echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_DO_STRONY_GLOWNEJ'].'</a>'; 
            unset($link);

        echo '</div>'; 

        unset($link);

    }    
}
?>