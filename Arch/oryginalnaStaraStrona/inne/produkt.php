<?php
chdir('../'); 

if (isset($_POST['id']) && isset($_POST['cechy'])) {

    $PodzielId = explode('_', $_POST['id']);

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (!Sesje::TokenSpr() && (int)$PodzielId[1] > 0) {
        echo 'false';
        exit;
    }

    //
    $Produkt = new Produkt( (int)$PodzielId[1] );    
    $Produkt->ProduktDostepnosc();
    
    if ( !empty($_POST['cechy']) ) {
        $Produkt->ProduktKupowanie( $filtr->process($_POST['cechy']) ); 
      } else {
        $Produkt->ProduktKupowanie();
    }    

    // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
    $Przecinek = 2;
    // jezeli sa wartosci calkowite to dla pewnosci zrobi int
    if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
        $Przecinek = 0;
    }    
    
    //
    // dostepnosc domyslna produktu
    $DostepnoscProduktu = '';
    
    if ( !empty($Produkt->dostepnosc['dostepnosc'])) {
         //
         $DostepnoscProduktu = $Produkt->dostepnosc['dostepnosc'];
         // 
    }      
    //
    
    // ogolny nr katalogowy produktu
    $NrKatalogowy = '';
    
    if ( $Produkt->info['nr_katalogowy'] != '' ) {
         //
         $NrKatalogowy = $Produkt->info['nr_katalogowy'];
         //
    }
    
    // ilosc magazynowa produktu
    if ( KARTA_PRODUKTU_MAGAZYN_FORMA == 'liczba' ) {
         $Ilosc = number_format( $Produkt->zakupy['ilosc_magazyn'], $Przecinek, '.', '' ) . ' ' . $Produkt->info['jednostka_miary'];   
       } else {
         $Ilosc = Produkty::PokazPasekMagazynu($Produkt->zakupy['ilosc_magazyn']);
    }
    
    // dostepnosc cechy
    if ( !empty($Produkt->zakupy['nazwa_dostepnosci']) ) {
         //
         $DostepnoscProduktu = $Produkt->zakupy['nazwa_dostepnosci'];
         //
    }
    
    // nr katalogowy cechy
    if ( !empty($Produkt->zakupy['nr_kat_cechy']) ) {
        
         $NrKatalogowy = $Produkt->zakupy['nr_kat_cechy'];
    
    }
    
    // czy wogole produkt mozna kupic
    if ( $Produkt->zakupy['mozliwe_kupowanie'] == 'nie' ) {
         $Kupowanie = 'nie';
        } else {
         $Kupowanie = 'tak';
    }

    echo json_encode( array("kupowanie" => $Kupowanie, "dostepnosc" => $DostepnoscProduktu, 'nrkat' => $NrKatalogowy, 'ilosc' => $Ilosc ) );

    unset($DostepnoscProduktu, $Kupowanie, $NrKatalogowy, $Produkt, $Ilosc);
    
}

?>