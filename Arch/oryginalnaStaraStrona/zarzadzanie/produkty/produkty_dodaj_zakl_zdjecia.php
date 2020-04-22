<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_3" style="display:none;">

    <script type="text/javascript">
    //<![CDATA[                        
    function dodaj_zdjecie() {
        var ile_pol = parseInt($("#ile_pol").val()) + 1;
        //
        $.get('ajax/dodaj_zdjecie.php', { id: ile_pol, katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            $('#wyniki tr:last').after('<tr id="wyniki'+ile_pol+'">' + data + '</tr>');
            $("#ile_pol").val(ile_pol);
            //
            pokazChmurki();            
        });
    } 
    function usun_zdjecie(id) {
        $('#wyniki' + id).remove();
    }
    //]]>
    </script>

    <div class="info_content">
    
        <div class="ostrzezenie" style="margin:8px">Pierwsze zdjęcie na liście zostanie ustawione jako zdjęcie główne.</div>
        
        <br /><br />
        
        <div class="ramka_foto">
        
            <table class="tbl_foto" id="wyniki">
                <tr class="tbl_foto_naglowek">
                    <td style="width:8%"><span>Sort</span></td>
                    <td style="width:15%"><span>Zdjęcie</span></td>
                    <td style="width:37%"><span>Ścieżka zdjęcia</span></td>
                    <td style="width:37%"><span>Opis (znacznik alt i title)</span></td>
                    <td style="width:3%"><span>Usuń</span></td>
                </tr>
                
                <tr id="wyniki1">    
                    <td>                              
                        <input type="text" name="sort_1" size="2" value="1" class="sort_zdjecie" disabled="disabled" style="display:none" />                 
                    </td>                 
                    <td style="padding:5px">
                        <div id="divfoto_1" style="padding-left:10px; display:none">
                            <div id="fofoto_1">
                                <img src="obrazki/_loader_small.gif" alt="" />
                            </div>
                        </div>
                        <?php if (!empty($prod['products_image'])) { ?>
                        <script type="text/javascript">
                        //<![CDATA[            
                        pokaz_obrazek_ajax('foto_1', '<?php echo $prod['products_image']; ?>')
                        //]]>
                        </script>                          
                        <?php } ?>
                    </td>
                    <td>                              
                        <input type="text" name="zdjecie_1" value="<?php echo $prod['products_image']; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto_1','','<?php echo KATALOG_ZDJEC; ?>','produkt')" id="foto_1" />                 
                    </td> 
                    <td>                              
                        <input type="text" name="alt_1" value="<?php echo $prod['products_image_description']; ?>" />                 
                    </td> 
                    <td>    
                        <img onclick="usun_zdjecie('1')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" />
                    </td>
                </tr>                    
                    
                <?php
                $ile_dodatkowych_zdjec = 0;
                //
                if ($id_produktu > 0) {
                    //
                    $ktoreZdjecie = 2;
                    //
                    // pobieranie danych o dodatkowych zdjeciach produktu
                    $zapytanie_zdjecie = "select distinct * from additional_images where products_id = '".$id_produktu."' order by sort_order";
                    $sqls = $db->open_query($zapytanie_zdjecie);
                    //
                    while ($zdjecie = $sqls->fetch_assoc()) {
                        ?>
                       
                        <tr id="wyniki<?php echo $ktoreZdjecie; ?>">    
                            <td>                              
                                <input type="text" name="sort_<?php echo $ktoreZdjecie; ?>" size="2" value="<?php echo $zdjecie['sort_order']; ?>" class="toolTipTopText sort_zdjecie" title="Kolejność wyświetlania zdjęć na karcie produktu" />                 
                            </td>                         
                            <td style="padding:5px">
                                <div id="divfoto_<?php echo $ktoreZdjecie; ?>" style="padding-left:10px; display:none">
                                    <div id="fofoto_<?php echo $ktoreZdjecie; ?>">
                                        <img src="obrazki/_loader_small.gif" alt="" />
                                    </div>
                                </div>
                                <script type="text/javascript">
                                //<![CDATA[            
                                pokaz_obrazek_ajax('foto_<?php echo $ktoreZdjecie; ?>', '<?php echo $zdjecie['popup_images']; ?>')
                                //]]>
                                </script>                                   
                            </td>
                            <td>                              
                                <input type="text" name="zdjecie_<?php echo $ktoreZdjecie; ?>" value="<?php echo $zdjecie['popup_images']; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto_<?php echo $ktoreZdjecie; ?>','','<?php echo KATALOG_ZDJEC; ?>','produkt')" id="foto_<?php echo $ktoreZdjecie; ?>" />                 
                            </td> 
                            <td>                              
                                <input type="text" name="alt_<?php echo $ktoreZdjecie; ?>" value="<?php echo $zdjecie['images_description']; ?>" />                 
                            </td> 
                            <td>
                                <img onclick="usun_zdjecie('<?php echo $ktoreZdjecie; ?>')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" />
                            </td>
                        </tr>

                        <?php
                        
                        $ktoreZdjecie++;
                        
                    }
                    //
                    $ile_dodatkowych_zdjec = (int)$db->ile_rekordow($sqls);
                    //
                    $db->close_query($sqls); 
                    unset($zapytanie_zdjecie, $zapytanie_zdjecie, $ktoreZdjecie);
                }
                ?>

            </table>
            
        </div>
        
        <input value="<?php echo ($ile_dodatkowych_zdjec + 1); ?>" type="hidden" name="ile_pol" id="ile_pol" />
        
        <div style="padding:10px;padding-top:20px">
            <span class="dodaj" onclick="dodaj_zdjecie()" style="cursor:pointer">dodaj kolejne zdjęcie</span>
            <span class="dodaj" onclick="openFileBrowser('','','<?php echo KATALOG_ZDJEC; ?>','produkt')" style="cursor:pointer;margin-left:30px;">otwórz przeglądarkę zdjęć</span>
        </div>  
        
        <?php
        unset($ile_dodatkowych_zdjec);
        ?>
        
    </div>
        
</div>

<?php } ?>
