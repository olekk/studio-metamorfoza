<?php

chdir('../');
//
define('POKAZ_ILOSC_ZAPYTAN', false);
define('DLUGOSC_SESJI', '9000');
define('NAZWA_SESJI', 'eGold');
//
require_once('ustawienia/ustawienia_db.php');
//      
include 'klasy/Bazadanych.php';
$db = new Bazadanych();
include 'klasy/Sesje.php';
$session = new Sesje((int)DLUGOSC_SESJI);

if (!empty($_REQUEST['weryfikacja'])) {

    if (empty($_SESSION['weryfikacja']) || trim(strtolower($_REQUEST['weryfikacja'])) != $_SESSION['weryfikacja']) {

        echo 'BLAD';

    } else {

        echo 'OK';

    }

}

?>