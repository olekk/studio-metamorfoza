<?php
if ( ( isset($_POST['akcja_dolna']) && (int)$_POST['akcja_dolna'] == 0 ) || !isset($_POST['akcja_dolna']) ) {
    header('Location: allegro_aukcje.php');
    exit;
}
    
if ( (int)$_POST['akcja_dolna'] == 1 ) {
    //
    include('allegro_aukcje_akcja_usun.php');
    //
}
if ( (int)$_POST['akcja_dolna'] == 2 ) {
    //
    include('allegro_aukcje_akcja_usun_zakoncz.php');
    //
}              
if ( (int)$_POST['akcja_dolna'] == 3 ) {
    //
    include('allegro_aukcje_akcja_wystaw.php');
    //
}
if ( (int)$_POST['akcja_dolna'] == 4 ) {
    //
    include('allegro_aukcje_akcja_zakoncz.php');
    //
}   
if ( (int)$_POST['akcja_dolna'] == 5 ) {
    //
    include('allegro_aukcje_akcja_ilosc.php');
    //
} 
?>