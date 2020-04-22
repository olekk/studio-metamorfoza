<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        //
        $Produkt = new Produkt( (int)$_POST['id'] );
        //
        // okresla czy ilosc jest ulamkowa zeby pozniej odpowiednio sformatowac wynik
        $Przecinek = 2;
        // jezeli sa wartosci calkowite to dla pewnosci zrobi int
        if ( $Produkt->info['jednostka_miary_typ'] == '1' ) {
            $Przecinek = 0;
        }
        //         

        echo '<div id="PopUpInfo">';
        
        echo str_replace('{PRODUKT}', $Produkt->info['nazwa'], $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_INFO_POPUP']);
        
        echo '<table class="ZnizkiInfo" cellspacing="5" cellpadding="5">';

        echo '<tr class="Naglowek">
                <td>' . $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_ILOSC_SZTUK_POPUP'] . ' ' . $Produkt->info['jednostka_miary'] . '</td>
                <td>' . $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_ZNIZKA_POPUP'] . '</td>
              </tr>';

              foreach ( $Produkt->ProduktZnizkiZalezneOdIlosciTablica() As $Znizki ) {
              
                $ZakresZnizek = str_replace('{ZNIZKA_OD}', number_format($Znizki['od'], $Przecinek, '.', '' ) . ' ' . $Produkt->info['jednostka_miary'], $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_ZAKRES_POPUP']);
                $ZakresZnizek = str_replace('{ZNIZKA_DO}', number_format($Znizki['do'], $Przecinek, '.', '' ) . ' ' . $Produkt->info['jednostka_miary'], $ZakresZnizek);

                echo '<tr><td>' . $ZakresZnizek . '</td><td>' . number_format($Znizki['znizka'], $Przecinek, '.', '' ) . '%</td></tr>';
                
                unset($ZakresZnizek);
                
              }    

        echo '</table>';     

        echo '<br /><span class="Informacja">';    

        echo str_replace('{SKLADNIA}', ((ZNIZKI_OD_ILOSCI_PROMOCJE == 'tak') ? '' : '<b>'.$GLOBALS['tlumacz']['NIE'].'</b>'), $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_PROMOCJE_POPUP']) . ' ';
        
        echo str_replace('{SKLADNIA}', ((ZNIZKI_OD_ILOSCI_SUMOWANIE_RABATOW == 'tak') ? '' : '<b>'.$GLOBALS['tlumacz']['NIE'].'</b>'), $GLOBALS['tlumacz']['ZNIZKI_OD_ILOSCI_SUMOWANIE_POPUP']);

        echo '</span>';
        
        echo '</div>';
        
        //
        unset($Produkt);
        //

    }
    
}
?>