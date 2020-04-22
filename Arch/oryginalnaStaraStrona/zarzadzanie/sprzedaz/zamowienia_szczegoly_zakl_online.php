<?php
if ( isset($toks) ) {
?>    
    
    <div id="zakl_id_5" style="display:none;" class="pozycja_edytowana">
    
      <div class="obramowanie_tabeli">
      
        <table class="listing_tbl">
        
          <tr class="div_naglowek srodekTbl">
            <td>Link do pobrania plików</td>
          </tr>    
          
          <tr class="pozycja_off">
              <td style="text-align:center">              
                  <textarea cols="80" style="width:95%" rows="2"><?php echo ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link; ?></textarea>
                  <span class="maleInfo" style="display:inline-block">w/w link prowadzi bezpośrednio do strony z której klient może pobrać pliki, link można wysłać klientowi osobnym mailem</span>
              </td>
          </tr>
          
        </table>
        
      </div>
      
      <br />

      <div class="obramowanie_tabeli">

        <table class="listing_tbl">
          <tr class="pozycja_off" style="border-top:0px">
            <td><b>Ilość wejść na stronę pobierania plików:</b></td>
            <td class="InfoNormal" style="padding-left:20px"><span class="editSelPobrania" id="orders_file_shopping"><b><?php echo $zamowienie->info['ilosc_pobran_plikow']; ?></b>
            <?php 
            if ( $zamowienie->info['ilosc_pobran_plikow'] >= (int)SPRZEDAZ_ONLINE_ILOSC_POBRAN ) {
                 echo ' <em class="limit_wyczerpany">limit pobrań wyczerpany</em>';
            }
            echo '</span>';
            
            $ilosc_pobran = array();
            for ( $f = 1; $f <= (int)$zamowienie->info['ilosc_pobran_plikow']; $f++ ) {
                 $ilosc_pobran[$f] = $f;
            }                                
            echo "<span class=\"edit_pobieranie\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" onclick=\"edytuj_ilosc_pobran('Pobrania','".str_replace('"','%22',json_encode($ilosc_pobran))."')\" /></span>"; 
            unset($ilosc_pobran);
            ?>
            </td>
          </tr>
        </table>
        
      </div>
      
      <br />
      
      <?php
      if ( count($zamowienie->sprzedaz_online_historia) > 0) {
      ?>                          

      <div class="obramowanie_tabeli">
      
        <table class="listing_tbl">
        
          <tr class="div_naglowek srodekTbl">
            <td>Data pobrania</td>
            <td>Nazwa pliku</td>
            <td>Adres IP klienta</td>
          </tr>
          
          <?php
          foreach ( $zamowienie->sprzedaz_online_historia as $pobranie ) {
          ?>
            
            <tr class="pozycja_off online">
              <td><?php echo $pobranie['data_pobrania']; ?></td>
              <td><?php echo $pobranie['plik']; ?></td>
              <td><?php echo $pobranie['ip']; ?></td>
            </tr>
            
          <?php } ?>
          
        </table>
        
      </div>
        
      <?php } ?>
           
    </div> 
    
<?php
}
?>        