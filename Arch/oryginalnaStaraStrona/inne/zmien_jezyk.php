<?php
chdir('../'); 

// Ustalanie domyslnego jezyka
if ( isset($_POST['jezyk']) ) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {

        if (isset($_POST['jezyk']) && $_POST['jezyk'] != '' ) {
            $jezyk = new Jezyki((int)$_POST['jezyk']);
          } else {
            $jezyk = new Jezyki();
        }
        $_SESSION['domyslnyJezyk'] = $jezyk->tablicaJezyka;
        //
        
        // przy zmianie jezyka zmienia rowniez walute na domyslna
        $id = $_SESSION['domyslnyJezyk']['waluta'];
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
        unset($db, $session, $waluty, $jezyk, $id, $kod, $waluta);
        //
    }
    
}

?>