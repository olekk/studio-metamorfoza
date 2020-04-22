<?php
if ( (int)$db->ile_rekordow($sql_pola) > 0  ) {
?>

<div class="obramowanie_tabeli" style="margin-top:10px;">

    <table class="listing_tbl list_poj">

        <tr class="div_naglowek">
          <td colspan="2">Dodatkowe informacje do zamówienia</td>
        </tr>
        
        <?php
        while ( $dodatkowePola = $sql_pola->fetch_assoc() ) {

          echo '<tr><td>' . $dodatkowePola['fields_name'] . ':</td>';
      
          $wartosc_zapytanie = "SELECT value FROM orders_to_extra_fields WHERE orders_id = '" . (int)$_GET['id_poz'] . "' AND fields_id = '" . $dodatkowePola['fields_id'] . "'";
          $wartosc_info = $db->open_query($wartosc_zapytanie);
          
          $wartosc = $wartosc_info->fetch_assoc();
          
          $db->close_query($wartosc_info);
          unset($wartosc_zapytanie);     
          
          echo '<td class="InfoNormal">' . nl2br('<span id="fields_' . $dodatkowePola['fields_id'] . '" class="zamowienie_pole">' . htmlentities($wartosc['value'], ENT_QUOTES, "UTF-8") . '</span>', true);

          echo '<span class="edit_pole"><img src="obrazki/edytuj.png" alt="Edytuj dane" title="Edytuj dane" class="toolTipTop" onclick="edytuj_dod_pole(' . $dodatkowePola['fields_id'] . ')" /></span>';
          
          echo '</td></tr>';

        }
        ?>
 
    </table>
  
</div>

<?php

}

$db->close_query($sql_pola);
unset($dodatkowe_pola_zamowienia);
?>