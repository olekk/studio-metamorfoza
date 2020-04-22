<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// do zwrocenia
$blad = $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_BLAD'];
$doWyswietlenia = '';
$doZapisu = '';

if (Sesje::TokenSpr()) {

    // jaki plik jest wgrywany
    if (isset($_POST['wgrywane'])) {
    
        $wgrywanyPlik = $_POST['wgrywane'];
        
        // id pola jakie jest obslugiwane
        $ustalId = explode('_', $wgrywanyPlik);
        
        if ( (int)$ustalId[1] > 0 ) {
        
            // szukan danych w bazie o id pola
            $zapytanie = "select products_text_fields_file_type, products_text_fields_file_size from products_text_fields where products_text_fields_id = '" . (int)$ustalId[1] . "'";
            $sql = $db->open_query($zapytanie);    
            $info = $sql->fetch_assoc();

            $dozwoloneRozszerzenia = array();
            $rozszerzenia = explode(',', $info['products_text_fields_file_type']);
            
            foreach ( $rozszerzenia as $roz ) {
                $dozwoloneRozszerzenia[] = trim($roz);
            }
            
            unset($rozszerzenia);
            
            $niedozwoloneRozszerzenia = array('php', 'html', 'htm', 'php4', 'php5', 'js');

            if (isset($_FILES[$wgrywanyPlik]) && $_FILES[$wgrywanyPlik]['error'] == 0) {
            
                $bezBledu = true;

                // czy jest poprawne rozszerzenia
                $rozszerzeniePliku = pathinfo($_FILES[$wgrywanyPlik]['name'], PATHINFO_EXTENSION);
                if ( !in_array(strtolower($rozszerzeniePliku), $dozwoloneRozszerzenia) || in_array(strtolower($rozszerzeniePliku), $niedozwoloneRozszerzenia) ) {
                    $blad = $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_BLAD_FORMAT'];
                    $bezBledu = false;
                }
                
                // czy jest odpowiednia wielkosc pliku
                if ( $_FILES[$wgrywanyPlik]['size'] > ( $info['products_text_fields_file_size'] * 1048576 ) ) {
                    $blad = $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_BLAD_WIELKOSC'];
                    $bezBledu = false;
                }
                
                // jezeli plik jest odpowiedni
                if ( $bezBledu == true ) {
                
                    $blad = '';
                
                    // nazwa losowa pliku
                    $tablicaLosowa = 'abcdefghijklmnopqrstuwxyz123456789_-';
                    $nazwaLosowa = '';
                    for ($t = 0; $t < 40; $t++ ) {
                        $nazwaLosowa .= substr( $tablicaLosowa, rand(0,36), 1 );
                    }
                    //

                    if (move_uploaded_file($_FILES[$wgrywanyPlik]['tmp_name'], 'wgrywanie/' . $nazwaLosowa . '.' . $rozszerzeniePliku)) {
                        //
                        // jezeli jest to obrazek to wyswietli podglad
                        if ( in_array(strtolower($rozszerzeniePliku), array('png', 'jpg', 'jpeg', 'gif')) ) {
                             //
                             $doWyswietlenia = "<img src='inne/wgranie.php?src=" . base64_encode($nazwaLosowa . ";" . $rozszerzeniePliku) . "' />";
                             //
                           } else {
                             //
                             $doWyswietlenia = "<b class='wgrano'>" . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_SUKCES'] . "</b>";
                             //
                        }
                    } else {
                        //
                        $blad = $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_BLAD'];
                        //
                    }
                    
                    $doZapisu = $nazwaLosowa . '.' . $rozszerzeniePliku;
                    
                }
                
                unset($bezBledu);
                    
            }
            
            $GLOBALS['db']->close_query($sql); 
            unset($zapytanie, $info, $dozwoloneRozszerzenia, $niedozwoloneRozszerzenia);            
        
        }
        
        unset($wgrywanyPlik);

    }
    
}

echo $blad . '##' . $doWyswietlenia . '##' . $doZapisu;
        
unset($blad, $doWyswietlenia, $doZapisu);

?>