<?php

if ($prot->wyswietlStrone && empty($GLOBALS['uprawnieniaZakladki']['zakladkaKlienci'])) {

    $zapytanie = "SELECT c.customers_id, c.customers_telephone, c.customers_firstname, c.customers_lastname, c.customers_email_address, c.customers_guest_account, ci.customers_info_date_account_created, ca.entry_city, ca.entry_company 
                  FROM customers c 
                  LEFT JOIN customers_info ci ON ci.customers_info_id = c.customers_id 
                  LEFT JOIN address_book ca ON ca.customers_id = c.customers_id AND ca.address_book_id = c.customers_default_address_id
                  ORDER BY ci.customers_info_date_account_created DESC LIMIT 5";
    $sql = $db->open_query($zapytanie);
    
    if ((int)$db->ile_rekordow($sql) > 0) {
    
        echo '<table class="tblModuly Klienci">';
        while ($info = $sql->fetch_assoc()) {
            echo '<tr>';
            echo '<td>'.$info['customers_firstname']. '<br />'. $info['customers_lastname'].'</td>';
            echo '<td>'.( $info['customers_guest_account'] == '1' ? '<img src="obrazki/gosc.png" class="toolTipTop" alt="Klient bez rejestracji" title="Klient bez rejestracji" />' : '' ). '</td>';
            echo '<td><span><a href="klienci/klienci_wyslij_email.php?id_poz='.$info['customers_id'].'">'.$info['customers_email_address'].'</a></span></td>';
            echo '<td>'.date('d-m-Y',strtotime($info['customers_info_date_account_created'])).'<br /><span>godz: '.date('H:i',strtotime($info['customers_info_date_account_created'])).'</span></td>';
            echo '<td><a href="klienci/klienci_edytuj.php?id_poz='.$info['customers_id'].'"><img src="obrazki/zobacz.png" class="toolTipTop" alt="Zobacz szczegóły" title="Zobacz szczegóły" /></a></td>';
            echo '</tr>';
        }
        
        echo '</table>';

    } else {
    
        echo '<span class="maleInfo">W sklepie nie ma nowo zarejestrowanych klientów ...</span>';
        
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

} else {

    echo '<div class="ModulyOstrzezenie">Nie posiadasz uprawień do przeglądania tego elementu.</div>';

}
?>
