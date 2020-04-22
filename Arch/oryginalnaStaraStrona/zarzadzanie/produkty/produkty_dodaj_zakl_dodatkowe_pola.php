<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_4" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php                 
    $licznik_zakladek = $tab_4;                       
    echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">Wszystkie języki</span>';
    $licznik_zakladek++;
    //
    $liczba = $licznik_zakladek;
    for ($w = 0; $w < $jezyk_szt; $w++) {
        echo '<span id="link_'.$licznik_zakladek.'" class="a_href_info_tab" onclick="gold_tabs(\''.$licznik_zakladek.'\')">'.$ile_jezykow[$w]['text'].'</span>';
        $licznik_zakladek++;
    }                    
    ?>                      
    </div>
    
    <div style="clear:both"></div>
    
    <script type="text/javascript" src="produkty/dodatkowe_pola.js"></script>       
    
    <div class="info_tab_content">
    
        <?php for ($w = -1; $w < $jezyk_szt; $w++) { ?>    
    
        <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
          
            <br />

            <div class="cechy_naglowek">Wybierz pole do dodania</div>
            
            <table class="cechy"><tr>
                <td>Nazwa pola:</td>
                <td>
                    <?php
                    $dod_pola = "select products_extra_fields_name, products_extra_fields_id from products_extra_fields where languages_id = '".(($w > -1) ? $ile_jezykow[$w]['id'] : 0)."' order by products_extra_fields_order, products_extra_fields_name";
                    $sqlc = $db->open_query($dod_pola);
                    //
                    $id_domyslne = 0;
                    $tablica = array();
                    //
                    while ($pole = $sqlc->fetch_assoc()) {
                        if ($id_domyslne == 0) {
                            $id_domyslne = $pole['products_extra_fields_id'];
                        }
                        $tablica[] = array('id' => $pole['products_extra_fields_id'], 'text' => $pole['products_extra_fields_name']);
                    }
                    $db->close_query($sqlc);
                    
                    echo Funkcje::RozwijaneMenu('dodatkowe_pole', $tablica, $id_domyslne, 'style="width:230px" id="id_dod_pola_' . (($w > -1) ? $ile_jezykow[$w]['id'] : 0) . '"');
                    
                    unset($dod_pola, $tablica, $id_domyslne);
                    ?>
                </td>

                <td>
                    <img src="obrazki/rozwin.png" style="cursor:pointer" onclick="dodaj_dodatkowe_pole(<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>)" alt="Dodaj pole do produktu" title="Dodaj pole do produktu" />
                </td>
            </tr></table>            

            <br />

            <?php
            if ($id_produktu > 0) {
            
                $dod_znacznik = '';
                if ( $w < 0 ) {
                     $dod_znacznik = '999_';
                }
            
                $zapytanie_pola = "select * from products_to_products_extra_fields pepf
                                      right join products_extra_fields pef on pepf.products_extra_fields_id = pef.products_extra_fields_id
                                           where pepf.products_id = '" . $id_produktu . "' and 
                                                 pef.languages_id = '" . (($w > -1) ? $ile_jezykow[$w]['id'] : 0) . "' and 
                                                 pepf.products_extra_fields_value != '' 
                                        order by pef.products_extra_fields_order, pef.products_extra_fields_name";

                $sqls = $db->open_query($zapytanie_pola);
                //
                if ($db->ile_rekordow($sqls) > 0) {
                    //
                    while ($infs = $sqls->fetch_assoc()) {
                        //
                        if ( $infs['products_extra_fields_image'] == 0 ) {
                            //
                            ?>
                            
                            <div class="pole_dodatkowe_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>" id="pole_nazwa_<?php echo $infs['products_extra_fields_id']; ?>">
                            
                                <div class="nagl_linki">
                                    <span class="pole_tekst toolTipTopText" title="Dodatkowe pole w formie tekstu"><?php echo $infs['products_extra_fields_name']; ?></span>
                                    <span class="usun_pole rg toolTipTopText" onclick="usun_pole(<?php echo $infs['products_extra_fields_id']; ?>)" title="Usuń pole"></span>
                                </div>
                                
                                <p>
                                  <label>Wartość:</label>
                                  <input type="text" onchange="usun_slownik(<?php echo $infs['products_extra_fields_id']; ?>)" name="pole_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>" size="75" value="<?php echo $infs['products_extra_fields_value']; ?>" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>" /> 
                                  <span class="slownik_pola toolTipTopText" onclick="pokaz_slownik(<?php echo $infs['products_extra_fields_id']; ?>)" title="Wyświetl / ukryj słownik dla pola opisowego"></span>
                                  <span class="pozycje_slownika" id="slownik_<?php echo $infs['products_extra_fields_id']; ?>"></span>
                                </p>
                                
                                <p>
                                  <label>Adres URL:</label>
                                  <input type="text" name="pole_url_<?php echo $dod_znacznik . $infs['products_extra_fields_id']; ?>" size="75" value="<?php echo $infs['products_extra_fields_link']; ?>" class="toolTipTopText" title="Wpisz adres www jeżeli dodatkowe pole ma być linkiem" />
                                </p>
                                
                            </div>
                            
                          <?php } else { ?>
                          
                            <div class="pole_dodatkowe_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>" id="pole_nazwa_<?php echo $infs['products_extra_fields_id']; ?>">
                          
                                <div class="nagl_linki">
                                    <span class="pole_obrazek toolTipTopText" title="Dodatkowe pole w formie grafiki"><?php echo $infs['products_extra_fields_name']; ?></span>
                                    <span class="usun_pole rg toolTipTopText" onclick="usun_pole(<?php echo $infs['products_extra_fields_id']; ?>)" title="Usuń pole"></span>
                                </div>
                                
                                <p>
                                  <label>Grafika / zdjęcie:</label>      
                                  <input type="text" onchange="usun_slownik(<?php echo $infs['products_extra_fields_id']; ?>)" name="pole_<?php echo $dod_znacznik; ?>zdjecie_<?php echo $infs['products_extra_fields_id']; ?>" size="75" value="<?php echo $infs['products_extra_fields_value']; ?>" class="toolTipTopText obrazek_pole" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto_pole_<?php echo $infs['products_extra_fields_id']; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>" />                 
                                  <span class="usun_zdjecie_pola toolTipTopText" data="foto_pole_<?php echo $infs['products_extra_fields_id']; ?>" title="Usuń przypisane zdjęcie"></span>
                                  <span class="slownik_pola toolTipTopText" onclick="pokaz_slownik(<?php echo $infs['products_extra_fields_id']; ?>)" title="Wyświetl / ukryj słownik dla pola opisowego"></span>
                                  <span class="pozycje_slownika" id="slownik_<?php echo $infs['products_extra_fields_id']; ?>"></span>
                                </p>
                                
                                <p>
                                  <label>Adres URL:</label>
                                  <input type="text" name="pole_url_<?php echo $dod_znacznik; ?>zdjecie_<?php echo $infs['products_extra_fields_id']; ?>" size="75" value="<?php echo $infs['products_extra_fields_link']; ?>" class="toolTipTopText" title="Wpisz adres www jeżeli dodatkowe pole ma być linkiem" />                      
                                </p>      

                                <div id="divfoto_pole_<?php echo $infs['products_extra_fields_id']; ?>" style="padding-left:10px; display:none">
                                  <label>Zdjęcie:</label>
                                  <span id="fofoto_pole_<?php echo $infs['products_extra_fields_id']; ?>">
                                      <span class="zdjecie_tbl">
                                          <img src="obrazki/_loader_small.gif" alt="" />
                                      </span>
                                  </span> 

                                  <?php if (!empty($infs['products_extra_fields_value'])) { ?>
                                  <script type="text/javascript">
                                  //<![CDATA[            
                                  pokaz_obrazek_ajax('foto_pole_<?php echo $infs['products_extra_fields_id']; ?>', '<?php echo $infs['products_extra_fields_value']; ?>')
                                  //]]>
                                  </script>                          
                                  <?php } ?> 
                                  
                                </div> 

                            </div>

                        <?php }
                        //
                    }
                    //
                }
                //
                $db->close_query($sqls);
                unset($zapytanie_pola);                                        
                
            }
            ?> 
            
            <span class="maleInfo" id="brak_pol_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>">Brak przypisanych dodatkowych pól dla tego języka</span>
            
            <script type="text/javascript">
            //<![CDATA[             
            if ( $('.pole_dodatkowe_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>').length > 0 ) {
                 $('#brak_pol_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>').hide();
               } else {
                 $('#brak_pol_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>').show();
            }        
            //]]>
            </script> 
    
            <div id="nowe_pola_<?php echo (($w > -1) ? $ile_jezykow[$w]['id'] : 0); ?>"></div>

        </div>
        
        <?php } ?>
    
    </div>

</div>      

<?php } ?>