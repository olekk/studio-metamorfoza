<?php
chdir('../');            

if (isset($_POST['id']) || isset($_POST['idwiele'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
      
        echo '<div id="PopUpInfo">';
    
        echo $GLOBALS['tlumacz']['INFO_AKTUALIZACJA_ILOSCI_KOSZYKA'] . ' <br />';
      
        // dla pojedynczego produktu
        if ( isset($_POST['id']) ) {

            $id = $_POST['id'];
            //        
            $Produkt = new Produkt( (int)Funkcje::SamoIdProduktuBezCech($id) );
            //
            echo '<h3>' . $Produkt->info['nazwa'] . '</h3>';
            //
            $GLOBALS['koszykKlienta']->ZmienIloscKoszyka( $id, (float)$_POST['ilosc'] ); 
            //
            unset($Produkt, $id);
            //
            
        }
        
        // dla wielu produktow
        if ( isset($_POST['idwiele']) ) {
          
              foreach ( $_POST['idwiele'] as $TablicaWieluId ) {
                
                  $id = $TablicaWieluId[0];
                  //        
                  $Produkt = new Produkt( (int)Funkcje::SamoIdProduktuBezCech($id) );
                  //
                  $GLOBALS['koszykKlienta']->ZmienIloscKoszyka( $id, (float)$TablicaWieluId[1] ); 
                  //
                  unset($Produkt, $id);
                  //                

              }          
          
        }
        
        echo '</div>';
        
        echo '<div id="PopUpPrzyciski">';
        
            echo '<span onclick="stronaReload()" class="przycisk">'.$GLOBALS['tlumacz']['PRZYCISK_KONTYNUUJ_ZAKUPY'].'</span>';
            
        echo '</div>';

    }
    
}
?>