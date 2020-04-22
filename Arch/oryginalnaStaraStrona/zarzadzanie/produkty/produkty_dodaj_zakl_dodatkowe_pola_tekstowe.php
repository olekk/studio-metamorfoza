<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_19" style="display:none;">

    <div class="info_content">
    
        <div class="cechy_naglowek">Wybierz dodatkowe pola tekstowe jakie mają być dostępne w produkcie</div>
        
        <div class="cechy_info">
            <div class="ostrzezenie">
                Pola będą wyświetlane we wszystkich wersjach językowych sklepu.
            </div>
        </div>    
    
        <?php
        // utworzy tablice z polami jakie sa przypisane do produktu
        $przypisanePolaProduktu = array();
        
        if ($id_produktu > 0) {
        
            $zapytanie_pola = "select products_text_fields_id from products_to_text_fields where products_id = '" . $id_produktu . "'";
            $sqls = $db->open_query($zapytanie_pola);
            //
            while ($infs = $sqls->fetch_assoc()) {
                $przypisanePolaProduktu[] = $infs['products_text_fields_id'];
            }
            $db->close_query($sqls);
            unset($zapytanie_pola);    
        
        }
        
        // lista dostepnych pol
        $zapytanie_pola = "select * from products_text_fields pt, products_text_fields_info ptd where pt.products_text_fields_id = ptd.products_text_fields_id and ptd.languages_id = '".$_SESSION['domyslny_jezyk']['id']."' order by pt.products_text_fields_order";
        $sqls = $db->open_query($zapytanie_pola);
        //
        while ($infs = $sqls->fetch_assoc()) {
            //
            ?>
            <table class="polaTxt"><tr>
              <td><input type="checkbox" name="pole_txt_<?php echo $infs['products_text_fields_id']; ?>" value="<?php echo $infs['products_text_fields_id']; ?>" <?php echo ((in_array($infs['products_text_fields_id'], $przypisanePolaProduktu)) ? 'checked="checked"' : ''); ?> /></td>
              
              <?php
              // typ pola
              switch( $infs['products_text_fields_type'] ) {
                  case 0: $typ_pola = 'pole tekstowe <b>Input</b>'; break;
                  case 1: $typ_pola = 'pole tekstowe <b>Textarea</b>'; break;
                  case 2: $typ_pola = 'pole z możliwością <b>Wgrania pliku</b>'; break;
              }              
              ?>
              
              <td><?php echo $infs['products_text_fields_name'] . ' - <span>' . $typ_pola . '</span>'; ?></td>
              
              <?php
              unset($typ_pola);
              ?>
            </tr></table>
            <?php
            //
        }
        //
        $db->close_query($sqls);
        unset($zapytanie_pola, $przypisanePolaProduktu);                                                                          
        ?>                      
    </div>

</div>      

<?php } ?>