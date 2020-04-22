<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (count($_SESSION['produktyPorownania']) > 0) {

    if (Sesje::TokenSpr()) {

        //
        $DoPorownaniaId = '';
        foreach ($_SESSION['produktyPorownania'] AS $Id) {
            $DoPorownaniaId .= $Id . ',';
        }
        $DoPorownaniaId = substr($DoPorownaniaId, 0, -1);
        //
        $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId);
        //
        $sqlNazwy = $db->open_query($zapNazwy);
        //
        $DefinicjePol = array('nazwa', 'zdjecie', 'cena', 'nr_kat', 'producent', 'opis');
        //
        $TablicaProduktow = array();
        $TablicaProduktow[] = array( 'nazwa' => $GLOBALS['tlumacz']['NAZWA_PRODUKTU'],
                                     'zdjecie' => $GLOBALS['tlumacz']['INFO_FOTO'],
                                     'cena' => $GLOBALS['tlumacz']['CENA'],
                                     'nr_kat' => $GLOBALS['tlumacz']['NUMER_KATALOGOWY'],
                                     'producent' => $GLOBALS['tlumacz']['PRODUCENT'],
                                     'opis' => $GLOBALS['tlumacz']['OPIS']);
        //
        while ($infc = $sqlNazwy->fetch_assoc()) {
            //
            $Produkt = new Produkt( $infc['products_id'], '', '', '', false );
            
            if ( $Produkt->CzyJestProdukt ) {
            
                $Produkt->ProduktProducent();
                $Produkt->ProduktDodatkowePola();
                //
                $DodatkowePola = '<div class="DodatkowePola">';
                foreach ($Produkt->dodatkowePolaOpis AS $TablicaPola) {
                    $DodatkowePola .= '<div class="PolaTbl">';
                    $DodatkowePola .= '<div>' . $TablicaPola['nazwa'] . ':</div><div><b>' . $TablicaPola['wartosc'] . '</b></div>';
                    $DodatkowePola .= '</div>';
                }
                $DodatkowePola .= '</div>';
                //
                $TablicaProduktow[] = array( 'nazwa' => '<h3>' . $Produkt->info['link'] . '</h3>',
                                             'zdjecie' => '<div class="Foto">' . $Produkt->fotoGlowne['zdjecie_ikony'] . '</div>',
                                             'cena' => $Produkt->info['cena'],
                                             'nr_kat' => $Produkt->info['nr_katalogowy'],
                                             'producent' => $Produkt->info['nazwa_producenta'],
                                             'opis' => '<div class="Opisy">' . $Produkt->info['opis'] . $DodatkowePola . '</div>');          
                unset($Produkt, $DodatkowePola);
                //
                
            }
            
        }
        $db->close_query($sqlNazwy); 
        unset($zapNazwy, $DoPorownaniaId, $infc);
        //
        echo '<table id="PorownywarkaTable" style="width:' . ((count($TablicaProduktow) * 250) + 200) . 'px">';
        //
        for ($t = 0, $u = count($DefinicjePol); $t < $u; $t++) {
            //
            echo '<tr>';
            //
            foreach ($TablicaProduktow AS $Wartosc) {
                //
                echo '<td>' . $Wartosc[ $DefinicjePol[$t] ] . '</td>';
                //
            }
            //
            echo '</tr>';
            //
        }
        //
        echo '</table>';
        //

    }
    
}

?>