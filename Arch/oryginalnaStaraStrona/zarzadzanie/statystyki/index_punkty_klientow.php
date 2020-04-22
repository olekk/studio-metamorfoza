<?php

if ($prot->wyswietlStrone && $_SESSION['grupaID'] == '1') {

    $zapytanie = "SELECT cp.unique_id,
                         cp.orders_id,
                         cp.points_type,
                         cp.points_status,
                         cp.reviews_id,
                         cp.points,
                         cp.customers_id,
                         cp.date_added,
                         c.customers_firstname,
                         c.customers_lastname,
                         ps.points_status_color,
                         psd.points_status_name
                    FROM customers_points cp
                    LEFT JOIN customers c ON c.customers_id = cp.customers_id
                    LEFT JOIN customers_points_status ps ON ps.points_status_id = cp.points_status
                    LEFT JOIN customers_points_status_description psd ON psd.points_status_id = cp.points_status AND psd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'
                   WHERE c.customers_id = cp.customers_id AND
                         cp.points_status != '2' AND cp.points_status != '4' AND cp.points_status != '3' ORDER BY cp.date_added desc LIMIT 5";



    $sql = $db->open_query($zapytanie);
    
    if ((int)$db->ile_rekordow($sql) > 0) {
    
        echo '<table class="tblModuly PunktyGl">';
        
        while ($info = $sql->fetch_assoc()) {
            echo '<tr>';
            echo '<td style="width:30px">';
            
            if ( $info['orders_id'] > 0 && ($info['points_type'] == "SP" || $info['points_type'] == "PP")) {
                     //
                echo '<div id="zamowienie_'. $info['unique_id'] . '_' .$info['orders_id'].'" class="zmzoom_punkty_zamowienie"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>';
                     //
            }
            if ( $info['reviews_id'] > 0 && $info['points_type'] == "RV") {
                     //
            echo '<div id="recenzja_'.$info['reviews_id'].'" class="zmzoom_punkty_recenzje"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>';
                     //
                  }                  

            echo '</td>';

            echo '<td>'.$info['customers_firstname'].' '.$info['customers_lastname'].'</td>';
            echo '<td>'.date('d-m-Y',strtotime($info['date_added'])).'<br /><span>godz: '.date('H:i',strtotime($info['date_added'])).'</span></td>';
            echo '<td>'.$info['points'].'</td>';
            echo '<td><span style="color:#'.$info['points_status_color'].';">'.$info['points_status_name'].'</span></td>';
            echo '<td><a href="klienci/klienci_punkty_status.php?pkt=1&amp;id='.$info['unique_id'].'&amp;id_poz='.$info['customers_id'].'"><img src="obrazki/zobacz.png" class="toolTipTop" alt="Zobacz szczegóły" title="Zobacz szczegóły" /></a></td>';
            echo '</tr>';
        }
        
        echo '</table>';
        
    } else {
    
        echo '<span class="maleInfo">W sklepie nie ma żadnych nowych punktów do zatwierdzenia ...</span>';
        
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

} else {

    echo '<div class="ModulyOstrzezenie">Nie posiadasz uprawień do przeglądania tego elementu.</div>';

}
?>
