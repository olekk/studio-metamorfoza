<?php
if ( isset($toks) ) {
?>

    <div id="zakl_id_4" style="display:none;" class="pozycja_edytowana">

        <div class="obramowanie_tabeli">
        
          <table class="listing_tbl">
          
            <tr class="div_naglowek">
              <td style="text-align:left;">
                <div class="lf">Uwagi obsługi sklepu o kliencie</div>
                <div class="LinEdytuj"><a href="sprzedaz/zamowienia_uwagi_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=4">edytuj</a></div>
              </td>
            </tr>

            <tr class="pozycja_off">
                <td  style="text-align:left;">
                    <?php 
                    if ( $zamowienie->klient['uwagi'] != '' ) {
                        echo $zamowienie->klient['uwagi'];
                    } else {
                        echo 'brak';
                    }
                    ?>
                </td>
             </tr>
          </table>
          
        </div>

        <div class="obramowanie_tabeli" style="margin-top:10px;">
        
          <table class="listing_tbl">
          
            <tr class="div_naglowek">
              <td style="text-align:left;">
                <div class="lf">Uwagi obsługi sklepu do zamówienia</div>
                <div class="LinEdytuj"><a href="sprzedaz/zamowienia_uwagi_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=4">edytuj</a></div>
              </td>
            </tr>

            <tr class="pozycja_off">
                <td  style="text-align:left;">
                    <?php 
                    if ( $zamowienie->info['uwagi'] != '' ) {
                        echo $zamowienie->info['uwagi'];
                    } else {
                        echo 'brak';
                    }
                    ?>
                </td>
             </tr>
          </table>
          
        </div>

    </div>  
    
<?php
}
?>        