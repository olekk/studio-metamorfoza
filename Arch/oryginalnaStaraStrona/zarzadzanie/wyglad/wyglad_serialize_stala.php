<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    $zmienneAktualizacji = $_POST[$_POST['typ']];
    
    $ciagDoZapisu = '';
    foreach ($zmienneAktualizacji as $idModulu) {
    
        $dodajPole = true;
        
        if (isset($_POST['skasuj']) && $filtr->process($_POST['skasuj']) == '1') {
        
            if ($filtr->process($_POST['idmodul']) == $idModulu) {
                
                $dodajPole = false;
                
            }
            
        }
        
        if ($dodajPole == true) {
    
            if (strpos($idModulu,'strona') > 0) {
                $ciagDoZapisu .= 'strona;'.(int)$idModulu.',';
            }
            if (strpos($idModulu,'galeria') > 0) {
                $ciagDoZapisu .= 'galeria;'.(int)$idModulu.',';
            }
            if (strpos($idModulu,'formularz') > 0) {
                $ciagDoZapisu .= 'formularz;'.(int)$idModulu.',';
            } 
            if (strpos($idModulu,'kategoria') > 0) {
                $ciagDoZapisu .= 'kategoria;'.(int)$idModulu.',';
            } 
            if (strpos($idModulu,'artykul') > 0) {
                $ciagDoZapisu .= 'artykul;'.(int)$idModulu.',';
            } 
            if (strpos($idModulu,'kategproduktow') > 0) {
                $ciagDoZapisu .= 'kategproduktow;'.(int)$idModulu.',';
            }             
            if (strpos($idModulu,'grupainfo') > 0) {
                $ciagDoZapisu .= 'grupainfo;'.(int)$idModulu.',';
            } 
            if (strpos($idModulu,'artkategorie') > 0) {
                $ciagDoZapisu .= 'artkategorie;'.(int)$idModulu.',';
            } 
            if (strpos($idModulu,'prodkategorie') > 0) {
                $ciagDoZapisu .= 'prodkategorie;'.(int)$idModulu.',';
            } 
            
        }
    
    }
    
    $ciagDoZapisu = substr($ciagDoZapisu, 0, strlen($ciagDoZapisu) - 1);

    $pola = array(array('value',$ciagDoZapisu));   
    
    $sql = $db->update_query('settings', $pola, " code = '".$filtr->process($_POST['stala'])."'");	
    unset($pola);    

}
?>