<?php
//
if (isset($_GET['id']) && $_GET['id'] != '' ) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    //
    $request = $filtr->process(trim(strtolower($_REQUEST['id'])));

    $zapytanie = "SELECT banners_url, banners_clicked FROM banners WHERE banners_id = '".(int)$request."'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    $info = $sql->fetch_assoc();

    $klikniecia = $info['banners_clicked'] + 1;
    $LinkDoPrzenoszenia = $info['banners_url'];

    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie, $info);

    $pola = array(array('banners_clicked',$klikniecia));

    $GLOBALS['db']->update_query('banners' , $pola, " banners_id = '".(int)$request."'");
    unset($pola);
    
    if ( empty($LinkDoPrzenoszenia) ) {
         $LinkDoPrzenoszenia = '/';
    }

    header('Location: ' . $LinkDoPrzenoszenia);
    exit();

    //
}

?>