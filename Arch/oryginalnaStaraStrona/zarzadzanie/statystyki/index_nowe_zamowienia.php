<?php
if ($prot->wyswietlStrone && empty($GLOBALS['uprawnieniaZakladki']['zakladkaZamowienia'])) {

    $zapytanie = "SELECT o.orders_id, o.customers_name, o.customers_id, o.date_purchased, o.last_modified, o.customers_dummy_account, o.customers_company, o.orders_status, ot.text as order_total, os.orders_status_color, osd.orders_status_name 
                  FROM orders_total ot
                  RIGHT JOIN orders o ON o.orders_id = ot.orders_id
                  LEFT JOIN orders_status os ON os.orders_status_id = o.orders_status
                  LEFT JOIN orders_status_description osd ON osd.orders_status_id = o.orders_status AND osd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'
                  WHERE ot.class = 'ot_total' ORDER BY orders_id DESC LIMIT 10";

    $sql = $db->open_query($zapytanie);
    
    if ((int)$db->ile_rekordow($sql) > 0) {
    
        echo '<table class="tblModuly Zamowienia">';
        
        while ($info = $sql->fetch_assoc()) {
            echo '<tr>';
            echo '<td style="width:30px"><div id="zamowienie_'.$info['orders_id'].'" class="zmzoom_zamowienie"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div></td>';
            echo '<td><b>'.$info['orders_id'].'</b></td>';
            echo '<td><a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'">'.$info['customers_name']. '</a></td>';
            echo '<td>'.date('d-m-Y',strtotime($info['date_purchased'])).'<br /><span> godz: '.date('H:i',strtotime($info['date_purchased'])).'</span></td>';
            echo '<td><b>'.$info['order_total']. '</b></td>';
            echo '<td><span style="color:#'.$info['orders_status_color'].';">'.$info['orders_status_name']. '</span></td>';
            echo '<td><a href="sprzedaz/zamowienia_szczegoly.php?id_poz='.$info['orders_id'].'"><img src="obrazki/zobacz.png" class="toolTipTop" alt="Zobacz szczegóły" title="Zobacz szczegóły" /></a></td>';
            echo '</tr>';
        }
        
        echo '</table>';

    } else {
    
        echo '<span class="maleInfo">W sklepie nie było jeszcze żadnych zamówień ...</span>';
        
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

} else {

    echo '<div class="ModulyOstrzezenie">Nie posiadasz uprawień do przeglądania tego elementu.</div>';

}
?>
