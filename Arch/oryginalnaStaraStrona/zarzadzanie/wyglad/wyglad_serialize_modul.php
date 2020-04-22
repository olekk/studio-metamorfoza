<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    $zmienneAktualizacji = $_POST['modul'];
    
    // jezeli bedzie kasowane
    if (isset($_POST['skasuj']) && $filtr->process($_POST['skasuj']) == '1') {
        $pola = array(
                array('modul_status','0'),
                array('modul_position','srodek'));
        
        $sql = $db->update_query('theme_modules', $pola, " modul_id = '".$filtr->process($_POST['idmodul'])."'");	
        unset($pola);
    }    

    $sort = 1;
    foreach ($zmienneAktualizacji as $idModulu) {
    
        $pola = array(
                array('modul_status','1'),
                array('modul_sort',$sort),
                array('modul_position',$filtr->process($_POST['typ'])));
        
        $sql = $db->update_query('theme_modules', $pola, " modul_id = '".$idModulu."'");	
        unset($pola);

        $sort++;
    
    }
    
    // sprawdza czy sa w bazie boxy niewlaczone
    $sqls = $db->open_query("select * from theme_modules where modul_status = '0' and modul_position = '" . $filtr->process($_POST['typ']) . "'");
    if ((int)$db->ile_rekordow($sqls) > 0) {
        echo '1';
      } else {
        echo '0';
    }
    
}
?>