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

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz' && Sesje::TokenSpr()) {

        $GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('REJESTRACJA') ), $GLOBALS['tlumacz'] );

        //zapisanie danych klienta do sesji - START

        if (!isset($_SESSION['adresDostawy'])) {
            $_SESSION['adresDostawy'] = array();
        }

        $krajPrzedZmiana = $_SESSION['krajDostawy']['id'];
        unset($_SESSION['adresDostawy']);

        $_SESSION['adresDostawy'] = array('imie' => $filtr->process($_POST['imie']),
                                          'nazwisko' => $filtr->process($_POST['nazwisko']),
                                          'firma' => $filtr->process($_POST['nazwa_firmy']),
                                          'ulica' => $filtr->process($_POST['ulica']),
                                          'kod_pocztowy' => $filtr->process($_POST['kod_pocztowy']),
                                          'miasto' => $filtr->process($_POST['miasto']),
                                          'telefon' => ( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' ),
                                          'panstwo' => $filtr->process($_POST['panstwo']),
                                          'wojewodztwo' => ( isset($_POST['wojewodztwo']) ? $filtr->process($_POST['wojewodztwo']) : '' )
        );

        if (!isset($_SESSION['adresFaktury'])) {
            $_SESSION['adresFaktury'] = array();
        }
        unset($_SESSION['adresFaktury']);
        
        // jezeli faktura na osobe fizyczna
        $imie = ''; $nazwisko = ''; $pesel = '';
        if ( isset($_POST['osobowosc']) && $_POST['osobowosc'] == '1' ) {
            $imie = $filtr->process($_POST['imieFaktura']);
            $nazwisko = $filtr->process($_POST['nazwiskoFaktura']);
            if ( isset($_POST['peselFaktura']) ) {
                $pesel = $filtr->process($_POST['peselFaktura']);
            } else {
                $pesel = '';
            }
        }
        $firma = ''; $nip = '';
        if ( isset($_POST['osobowosc']) && $_POST['osobowosc'] == '0' ) {
            $firma = $filtr->process($_POST['nazwa_firmyFaktura']);
            $nip = $filtr->process($_POST['nip_firmyFaktura']);
        }              
        //     
        
        $_SESSION['adresFaktury'] = array('imie' => $imie,
                                          'nazwisko' => $nazwisko,
                                          'pesel' => $pesel,
                                          'firma' => $firma,
                                          'nip' => $nip,
                                          'ulica' => $filtr->process($_POST['ulicaFaktura']),
                                          'kod_pocztowy' => $filtr->process($_POST['kod_pocztowyFaktura']),
                                          'miasto' => $filtr->process($_POST['miastoFaktura']),
                                          'panstwo' => $filtr->process($_POST['panstwoFaktura']),
                                          'wojewodztwo' => ( isset($_POST['wojewodztwoFaktura']) ? $filtr->process($_POST['wojewodztwoFaktura']) : '' )
        );
        
        unset($imie, $nazwisko, $firma, $nip);

        if ( $krajPrzedZmiana != $_POST['panstwo'] ) {

            $zapytanie_panstwo = "SELECT c.countries_iso_code_2
                                    FROM countries c
                                    WHERE c.countries_id = '".$filtr->process($_POST['panstwo'])."'";

            $sql_panstwo = $GLOBALS['db']->open_query($zapytanie_panstwo);
            $info_panstwo = $sql_panstwo->fetch_assoc();

            unset($_SESSION['krajDostawy'], $_SESSION['rodzajDostawy'], $_SESSION['rodzajPlatnosci']);

            $_SESSION['krajDostawy'] = array();
            $_SESSION['krajDostawy'] = array('id' => $filtr->process($_POST['panstwo']),
                                             'kod' => $info_panstwo['countries_iso_code_2']);
            unset($zapytanie_panstwo);
            $GLOBALS['db']->close_query($sql_panstwo);

            echo '<div id="PopUpInfo">';  

            echo 'Zmieniono kraj dostawy. Należy ponownie wybrać rodzaj wysyłki i płatności';

            echo '</div>';
            
            echo '<div id="PopUpPrzyciski">';
            
                if ( WLACZENIE_SSL == 'tak' ) {
                  $link = ADRES_URL_SKLEPU_SSL . '/koszyk.html';
                } else {
                  $link = 'koszyk.html';
                }

                echo '<a href="' . $link . '" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_PRZEJDZ_DO_KOSZYKA'].'</a>'; 
                unset($link);

            echo '</div>';

        }

    }

}
?>