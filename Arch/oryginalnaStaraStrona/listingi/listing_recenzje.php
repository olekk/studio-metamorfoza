<?php
// czy jest zapytanie
if ($IloscRecenzji > 0) { 

    while ($info = $sql->fetch_assoc()) {
        //
        // ************************ wyglad produktu - poczatek **************************
        //
        echo '<article class="Recenzje LiniaDolna">';
            //
            $Produkt = new Produkt( $info['products_id'] );
            $Produkt->ProduktRecenzje();
            //             
            echo '<div class="Foto">'.$Produkt->recenzje[$info['reviews_id']]['recenzja_zdjecie_link_ikony'].'</div>';
            //  
            echo '<div class="ProdRecenzja" style="margin-left:' . (SZEROKOSC_OBRAZEK_MALY+30) . 'px">';
                      
            echo '<h3>' . $Produkt->recenzje[$info['reviews_id']]['recenzja_link'] . '</h3> ' . $Produkt->recenzje[$info['reviews_id']]['recenzja_ocena_obrazek'];
            
            echo '<p class="RecenzjaTresc LiniaOpisu">' . $Produkt->recenzje[$info['reviews_id']]['recenzja_tekst_krotki'] . '</p>';
            
            echo '<p class="AutorData">';
            echo '{__TLUMACZ:AUTOR_RECENZJI}: <b>' . $Produkt->recenzje[$info['reviews_id']]['recenzja_oceniajacy'] . '</b> <br />';
            echo '{__TLUMACZ:DATA_NAPISANIA_RECENZJI}: <b>' . $Produkt->recenzje[$info['reviews_id']]['recenzja_data_dodania'] . '</b>';
            echo '</p>';
            
            echo '</div><div class="cl"></div>';
            //
            unset($Produkt);
            //
        echo '</article>';
        //
        // ************************ wyglad produktu - koniec **************************
        //
    }

    unset($info);
      
} else {

    echo '<div id="BrakProduktow" class="Informacja">{__TLUMACZ:BLAD_BRAK_RECENZJI}</div>';
  
}

$GLOBALS['db']->close_query($sql); 

unset($IloscRecenzji, $zapytanie);  
?>