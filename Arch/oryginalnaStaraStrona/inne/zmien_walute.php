<?php
chdir('../'); 

// Ustalanie domyslnej waluty
if ( isset($_POST['waluta']) ) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {

        $id = $_SESSION['domyslnyJezyk']['waluta'];

        if ( isset($_POST['jezyk']) ) {
            $id = $_SESSION['domyslnyJezyk']['waluta'];
          } elseif ( isset($_POST['waluta']) ) {
            $id = (int)$_POST['waluta'];
        }
        $kod = $waluty->waluty_id[$id]['code'];

        $waluta = array('id'          => $id,
                      'nazwa'       => $waluty->waluty[$kod]['nazwa'],
                      'kod'         => $kod,
                      'symbol'      => $waluty->waluty[$kod]['symbol'],
                      'separator'   => $waluty->waluty[$kod]['separator'],
                      'przelicznik' => $waluty->waluty[$kod]['przelicznik'],
                      'marza'       => $waluty->waluty[$kod]['marza']);

        $_SESSION['domyslnaWaluta'] = $waluta;
        //
        // przelicza koszyk na nowa walute
        $GLOBALS['koszykKlienta']->PrzeliczKoszyk();
        //
        unset($db, $session, $waluty, $id, $kod, $waluta);
        //

    }
    
}

?>