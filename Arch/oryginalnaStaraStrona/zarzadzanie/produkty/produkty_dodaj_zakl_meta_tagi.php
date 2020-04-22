<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_6" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_6;
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                      
    ?>                   
    </div>
    
    <div style="clear:both"></div>
    
    <div class="info_tab_content">
    
        <?php
        for ($w = 0; $w < $jezyk_szt; $w++) {
        
            if ($id_produktu > 0) {  
                // pobieranie danych jezykowych
                $zapytanie_tmp = "select distinct * from products_description where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                $sqls = $db->open_query($zapytanie_tmp);
                $opis = $sqls->fetch_assoc();
                //
                $products_meta_title_tag = $opis['products_meta_title_tag'];
                $products_meta_desc_tag = $opis['products_meta_desc_tag'];
                $products_meta_keywords_tag = $opis['products_meta_keywords_tag'];
                $products_seo_url = $opis['products_seo_url'];
                //
              } else {
                //
                $products_meta_title_tag = '';
                $products_meta_desc_tag = '';
                $products_meta_keywords_tag = '';
                $products_seo_url = '';
                //
            }
            ?>  
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">

                <p>
                  <label>Meta Tagi - Tytuł:</label>
                  <textarea name="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $products_meta_title_tag; ?></textarea>
                </p> 
                
                <p class="LicznikMeta">
                  <label></label>
                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($products_meta_title_tag)); ?></span>
                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                </p>                
                
                <p>
                  <label>Meta Tagi - Opis:</label>
                  <textarea name="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $products_meta_desc_tag; ?></textarea>
                </p>  

                <p class="LicznikMeta">
                  <label></label>
                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($products_meta_desc_tag)); ?></span>
                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                </p>                 
                
                <p>
                  <label>Meta Tagi - Słowa kluczowe:</label>
                  <textarea name="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $products_meta_keywords_tag; ?></textarea>
                </p>    
                
                <p class="LicznikMeta">
                  <label></label>
                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($products_meta_keywords_tag)); ?></span>
                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                </p>                 
                
                <p>
                  <label>Adres URL:</label>
                  <input type="text" name="url_meta_<?php echo $w; ?>" size="80" value="<?php echo $products_seo_url; ?>" />
                </p>                                           
                
            </div>
            <?php

            if ($id_produktu > 0) {  
                $db->close_query($sqls); 
                unset($zapytanie_tmp, $opis);
            }
            unset($products_meta_title_tag, $products_meta_desc_tag, $products_meta_keywords_tag, $products_seo_url);        
                        
        }                    
        ?>                      
    </div>

</div>  

<?php } ?>