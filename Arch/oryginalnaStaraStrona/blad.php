<?php

// wczytanie ustawien inicjujacych system
require_once(dirname(__FILE__).'/ustawienia/init.php');

// sprawdza czy nie bylo przekierowania adresow
Przekierowania::SprawdzPrzekierowania();

if ( strpos($_SERVER['REDIRECT_URL'], 'favicon') === false ) {

    switch($_SERVER['REDIRECT_STATUS']) {
      case 400:
          header("Location: blad-400.html");
          break;
      case 401:
          header("Location: blad-401.html");
          break;
      case 403:
          header("Location: blad-403.html");
          break;
      case 404:
          header("Location: brak-strony.html");
          break;
      case 500:
          header("Location: blad-500.html");
          break;
      case 503:
          header("Location: blad-503.html");
          break;
      default:
          header("Location: brak-strony.html");
        break;
    }

}

exit();
?>