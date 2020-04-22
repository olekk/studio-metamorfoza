<?php
chdir('../');         

if (isset($_POST['pole']) && !empty($_POST['pole'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {
    
        $pole = $filtr->process($_POST['pole']);
    
        $zapytanie = Produkty::SqlAutoUzupelnienie();

        $sql = $db->open_query($zapytanie);
        
        if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) { 
        
            $zwroc = '<table>';
            $bylProdukt = 0;
            
            while ($produkt = $sql->fetch_assoc()) {

                $pozycjaCiagu = mb_strpos(mb_strtolower($produkt['products_name'], 'UTF-8'), mb_strtolower($pole, 'UTF-8'));

                if ( $pozycjaCiagu > -1 ) {
                
                    $Produkt = new Produkt( $produkt['products_id'], 40, 40, '', false );
            
                    $zwroc .= '<tr onclick="$.pobierzAutoodpowiedz(' . $produkt['products_id'] . ')">';

                    $zwroc .= '<td>' . $Produkt->fotoGlowne['zdjecie'] . '</td>';
                    
                    $zwroc .= '<td>';
                    
                    $zwroc .= '<input type="hidden" value="' .  str_replace('"', '&quot;',$Produkt->info['nazwa']) . '" id="auto_' . $produkt['products_id'] . '" />';

                    $zwroc .= mb_substr( $Produkt->info['nazwa'], 0, $pozycjaCiagu );
                    
                    $zwroc .= '<span class="zaznacz">' . mb_substr( $Produkt->info['nazwa'], $pozycjaCiagu, mb_strlen($pole) ) . '</span>';
                    
                    $zwroc .= mb_substr( $Produkt->info['nazwa'], $pozycjaCiagu + mb_strlen($pole), mb_strlen($Produkt->info['nazwa']) ); 
                    
                    $zwroc .= '</td>';
                    
                    $zwroc .= '<td>' . $Produkt->info['cena_brutto'] . '</td>';
                    
                    $zwroc .= '</tr>';
                    
                    unset($Produkt);    

                    $bylProdukt++;
                    
                    if ( $bylProdukt == (int)$_POST['limit'] ) {
                         break;
                    }
                    
                }
                
                unset($pozycjaCiagu);
                
            }
            
            $zwroc .= '</table>';
            
            if ( $bylProdukt > 0 ) {
                 echo $zwroc;
            }
            
            unset($zwroc);

        }
        
        $db->close_query($sql);
        unset($zapytanie);
        
        unset($pole);

    }
    
}

?>