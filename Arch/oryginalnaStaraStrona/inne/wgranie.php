<?php
chdir('../');

if ( isset($_GET['src']) ) {
    //
    $zrobTablice = explode(';', base64_decode($_GET['src']));
    //
    if ( count($zrobTablice) == 2 ) {
        //
        // typ obrazka
        $obraz = false;
        switch( strtolower($zrobTablice[1]) ) {
            case 'jpg': 
                $typ = 'image/jpeg'; 
                $obraz = true;
                break;
            case 'jpeg': 
                $typ = 'image/jpeg'; 
                $obraz = true;
                break;
            case 'png': 
                $typ = 'image/png'; 
                $obraz = true;
                break;
            case 'gif': 
                $typ = 'image/gif'; 
                $obraz = true;
                break;
        }
        //
        if ( file_exists('wgrywanie/' . $zrobTablice[0] . '.' . $zrobTablice[1]) ) {
             //
             // jezeli to obrazek
             if ( $obraz == true ) {
                 //
                 $obrazPlik = 'wgrywanie/' . $zrobTablice[0] . '.' . $zrobTablice[1];
                 //
                 header("Content-Type: " . $typ);
                 header("Content-Length: " . filesize($obrazPlik));
                 @readfile($obrazPlik);    
                 exit;
                 //
               } else {
                 //
                 $plik = 'wgrywanie/' . $zrobTablice[0] . '.' . $zrobTablice[1];
                 //
                 header("Pragma: public");
                 header("Expires: 0");
                 header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                 header("Cache-Control: public");
                 header("Content-Description: File Transfer");
                 header("Content-Type: application/force-download");
                 header("Content-Disposition: attachment; filename=" . $zrobTablice[0] . '.' . $zrobTablice[1] . ";");
                 header("Content-Transfer-Encoding: binary");
                 header("Content-Length: " . filesize($plik));
                 @readfile($plik);
                 exit;                 
                 //
             }
             //
        } else {
            //
            echo 'Brak pliku ...';
            exit;
            //
        }
        //
    }
    //
}
?>