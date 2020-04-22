<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_0" style="display:none;">

    <div class="info_tab" style="padding-top:0px">
    <?php
    $licznik_zakladek = $tab_0;
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
            ?>
            
            <div id="info_tab_id_<?php echo $w + $liczba; ?>" style="display:none;">
            
                <?php
                if ($id_produktu > 0) {
                    // pobieranie danych jezykowych
                    $zapytanie_jezyk = "select distinct products_name, products_name_info from products_description where products_id = '".$id_produktu."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                    $sqls = $db->open_query($zapytanie_jezyk);
                    $prod = $sqls->fetch_assoc();
                    //
                    $nazwa_produktu = $prod['products_name'];
                    $dodatkowa_nazwa = $prod['products_name_info'];
                    //
                  } else {
                    //
                    $nazwa_produktu = '';
                    $dodatkowa_nazwa = '';
                    //
                }
                ?>
            
                <p>
                   <?php if ($w == '0') { ?>
                    <label class="required">Nazwa produktu:</label>
                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($nazwa_produktu); ?>" id="nazwa_0" />
                   <?php } else { ?>
                    <label>Nazwa produktu:</label>   
                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($nazwa_produktu); ?>" />
                   <?php } ?>
                </p> 
                
                <p>
                   <label>Dodatkowa nazwa:</label>
                   <input type="text" name="nazwa_info_<?php echo $w; ?>" size="65" class="toolTipText" title="Dodatkowa informacja przy eksporcie do porównywarek" value="<?php echo Funkcje::formatujTekstInput($dodatkowa_nazwa); ?>" />
                </p>

                <?php
                if ($id_produktu > 0) {  
                    $db->close_query($sqls);
                    unset($prod);
                }
                unset($nazwa_produktu, $dodatkowa_nazwa);                  

                ?>
              
            </div>
            <?php                    
        }                    
        ?>                      
    </div>          

    <?php
    // pobieranie danych od produkcie z tablicy products
    $zapytanie_produkt = "select * from products where products_id = '".$id_produktu."'";
    $sqls = $db->open_query($zapytanie_produkt);
    $prod = $sqls->fetch_assoc();
    ?>    

    <table style="width:100%">
        <tr>
            <td style="width:60%;vertical-align:top">
            
                <p>
                  <label>Czy produkt jest aktywny ?</label>
                  <input type="radio" name="status" value="1" <?php echo (($prod['products_status'] == '1' || empty($prod['products_status'])) ? 'checked="checked"' : ''); ?> /> tak
                  <input type="radio" name="status" value="0" <?php echo (($prod['products_status'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                </p>    
                
                <p>
                  <label>Rodzaj produktu:</label>
                  <?php
                  echo Funkcje::RozwijaneMenu('rodzaj_produktu', Produkty::TablicaRodzajProduktow(), $prod['products_type'], 'style="width:200px"');
                  ?>
                </p>                

                <p>
                  <label>Czy produkt można kupować ?</label>
                  <input type="radio" name="kupowanie" value="1" <?php echo (($prod['products_buy'] == '1' || empty($prod['products_buy'])) ? 'checked="checked"' : ''); ?> /> tak
                  <input type="radio" name="kupowanie" value="0" <?php echo (($prod['products_buy'] == '0') ? 'checked="checked"' : ''); ?> /> nie
                </p>  

                <p>
                  <label>Czy produkt będzie dostępny tylko jako akcesoria dodatkowe ?</label>                  
                  <input type="radio" name="akcesoria" value="1" class="toolTipTopText" title="Produkt będzie dostępny tylko jako akcesoria dodatkowe i nie będzie go można zakupić osobno" <?php echo (($prod['products_accessory'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                  <input type="radio" name="akcesoria" value="0" <?php echo ((empty($prod['products_accessory'])) ? 'checked="checked"' : ''); ?> /> nie
                </p>                 
            
                <?php
                $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                ?>
                
                <table style="margin:10px">
                    <tr>
                        <td><label>Produkt <b style="color:#549f11">widoczny</b> tylko dla grup klientów:</label></td>
                        <td>
                            <?php                        
                            foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" ' . ((in_array($GrupaKlienta['id'], explode(',', $prod['customers_group_id']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                            }              
                            ?>
                        </td>
                    </tr>
                </table>

                <table style="margin:10px">
                    <tr>
                        <td><label>Produkt <b style="color:#373737">niewidoczny</b> dla grup klientów:</label></td>
                        <td>
                            <?php                        
                            foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="nie_grupa_klientow[]" ' . ((in_array($GrupaKlienta['id'], explode(',', $prod['not_customers_group_id']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                            }              
                            ?>
                        </td>
                    </tr>
                </table>
                
                <?php
                unset($TablicaGrupKlientow);
                ?>
                
                <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to produkt będzie widoczny dla wszystkich klientów.</div>

                <p>
                  <label>Kolejność wyświetlania:</label>
                  <input type="text" name="sort" size="5" value="<?php echo ((Funkcje::czyNiePuste($prod['sort_order'])) ? $prod['sort_order'] : ''); ?>" />
                </p>

                <p>
                  <label>Data dodania:</label>
                  <input type="text" name="data_dodania" size="20" value="<?php echo ((Funkcje::czyNiePuste($prod['products_date_added'])) ? date('d-m-Y H:i',strtotime($prod['products_date_added'])) : date('d-m-Y H:i',time())); ?>" />
                </p>                 
            
                <p>
                  <label>Nr katalogowy:</label>
                  <input type="text" name="nr_kat" size="30" value="<?php echo $prod['products_model']; ?>" />
                </p> 
                
                <p>
                  <label>Kod producenta:</label>
                  <input type="text" name="kod_producenta" size="30" value="<?php echo $prod['products_man_code']; ?>" />
                </p> 
                
                <p>
                  <label>Id produktu w programie magazynowym:</label>
                  <input type="text" name="nr_kat_klienta" size="30" value="<?php echo $prod['products_id_private']; ?>" />
                </p>                
                
                <p>
                  <label>Kod EAN:</label>
                  <input type="text" name="nr_ean" size="30" value="<?php echo $prod['products_ean']; ?>" />
                </p> 
                
                <p>
                  <label>PWKIU:</label>
                  <input type="text" name="pkwiu" size="30" value="<?php echo $prod['products_pkwiu']; ?>" />
                </p>                                         
                
                <p>
                  <label>Waga:</label>
                  <input type="text" name="waga" size="8" value="<?php echo ((Funkcje::czyNiePuste($prod['products_weight'])) ? $prod['products_weight'] : ''); ?>" class="toolTipText Waga" title="Waga w kg np: 1 = 1kg , 0.2 = 200 gram" />
                </p>

                <p>
                  <label>Dostępny od dnia:</label>
                  <input type="text" id="data_dostepnosci" name="data_dostepnosci" value="<?php echo ((Funkcje::czyNiePuste($prod['products_date_available'])) ? date('d-m-Y',strtotime($prod['products_date_available'])) : ''); ?>" size="20" class="datepicker" />
                </p>

                <p>
                  <label>Producent:</label>                                       
                  <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), $prod['manufacturers_id'], 'style="width:200px"'); ?>
                </p>
                
                <p>
                  <label>Stan dostępności:</label>                                       
                  <?php echo Funkcje::RozwijaneMenu('dostepnosci', Produkty::TablicaDostepnosci('-- brak --'), $prod['products_availability_id'], 'style="width:200px"'); ?>
                </p>     

                <p>
                  <label>Wysyłka:</label>                                        
                  <?php echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --'), $prod['products_shipping_time_id'], 'style="width:200px"'); ?>
                </p> 

                <p>
                  <label>Stan produktu:</label>
                  <?php
                  //                  
                  $domyslnyStan = 0;
                  $sqle = $db->open_query("select * from products_condition cp, products_condition_description cpd where cp.products_condition_id = cpd.products_condition_id and cpd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");

                  $tablica = array();
                  $tablica[] = array('id' => '0', 'text' => '-- brak --');

                  while ($stan = $sqle->fetch_assoc()) {
                       $tablica[] = array('id' => $stan['products_condition_id'], 'text' => $stan['products_condition_name']);
                       //
                       if ($stan['products_condition_default'] == 1) {
                           $domyslnyStan = $stan['products_condition_id'];
                       }
                  }
                  $db->close_query($sqle);
                  
                  $wybierz = $prod['products_condition_products_id'];
                  if (( empty($prod['products_condition_products_id']) || $prod['products_condition_products_id'] == '0' ) && $id_produktu == 0 ) {
                      $wybierz = $domyslnyStan;
                  }
                  //
                  echo Funkcje::RozwijaneMenu('stan_produktu', $tablica, $wybierz, 'style="width:200px"'); 
                  //
                  unset($stan, $tablica, $domyslnyStan, $wybierz);
                  ?>
                </p>    

                <p>
                  <label>Gwarancja:</label>                                        
                  <?php echo Funkcje::RozwijaneMenu('gwarancja', Produkty::TablicaGwarancjaProduktow('-- brak --'), $prod['products_warranty_products_id'], 'style="width:200px"'); ?>
                </p>                  

                <p>
                  <label>Ilość w magazynie:</label>
                  <input type="text" name="ilosc" size="5" value="<?php echo ((Funkcje::czyNiePuste($prod['products_quantity'])) ? $prod['products_quantity'] : ''); ?>" class="toolTip" title="Ilość produktu w magazynie, nie należy wypełniać pola jeżeli produkt ma cechy ze stanem magazynowym - wtedy ilość zostanie obliczona na podstawie magazynu cech" />
                </p> 
                
                <script type="text/javascript">
                //<![CDATA[
                function SprawdzIlosci(id) {
                    $('#opis_ilosci').html('');
                    $('#opis_ilosci').hide();
                    $.post("ajax/jednostka_miary.php?tok=<?php echo Sesje::Token(); ?>", { id: id }, function(data){ $('#opis_ilosci').html(data); $('#opis_ilosci').slideDown('fast'); });
                };
                //]]>
                </script>                
                
                <p>
                  <label>Jednostka miary:</label>
                  <?php
                  // musi ustalic co ma wyswietlic domyslnie
                  //
                  $tablica = array();
                  $domyslnaJm = 0;
                  $sqle = $db->open_query("select * from products_jm s, products_jm_description sd where s.products_jm_id = sd.products_jm_id and sd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' order by s.products_jm_default, sd.products_jm_name");  
                  while ($jm = $sqle->fetch_assoc()) {
                       $tablica[] = array('id' => $jm['products_jm_id'],
                                          'text' => $jm['products_jm_name']);
                       //
                       if ($jm['products_jm_default'] == 1) {
                           $domyslnaJm = $jm['products_jm_id'];
                       }
                  }
                  $db->close_query($sqle);

                  $wybierz = $prod['products_jm_id'];
                  if (empty($prod['products_jm_id'])) {
                      $wybierz = $domyslnaJm;
                  }
                  //
                  echo Funkcje::RozwijaneMenu('jednostka_miary', $tablica, $wybierz, 'style="width:200px" onchange="SprawdzIlosci(this.value)"'); 
                  //
                  unset($jm, $tablica, $domyslnaJm);
                  ?>
                </p>    

                <div id="opis_ilosci"></div>
                
                <script type="text/javascript">
                //<![CDATA[
                SprawdzIlosci(<?php echo $wybierz; ?>);
                //]]>
                </script>     

                <?php unset($wybierz); ?>
                
                <p>
                  <label>Minimalna ilość zakupu:</label>
                  <input type="text" name="min_ilosc" size="8" value="<?php echo ((Funkcje::czyNiePuste($prod['products_minorder'])) ? $prod['products_minorder'] : ''); ?>" class="toolTip" title="Ilość produktów jako klient może zakupić minimalnie" />
                </p> 

                <p>
                  <label>Maksymalna ilość zakupu:</label>
                  <input type="text" name="max_ilosc" size="8" value="<?php echo ((Funkcje::czyNiePuste($prod['products_maxorder'])) ? $prod['products_maxorder'] : ''); ?>" class="toolTip" title="Ilość produktów jako klient może zakupić maksymalnie" />
                </p>   
                
                <p>
                  <label>Przyrost ilości:</label>
                  <input type="text" name="ilosc_zbiorcza" size="8" value="<?php echo ((Funkcje::czyNiePuste($prod['products_quantity_order'])) ? $prod['products_quantity_order'] : ''); ?>" class="toolTip" title="Klient będzie mogł zakupić tylko wielokrotność tej wartości" />
                </p>                 

                <p>
                  <label>Produkt gabarytowy:</label>
                  <input type="radio" name="gabaryt" value="1" <?php echo (($prod['products_pack_type'] == '1') ? 'checked="checked"' : ''); ?> /> gabaryt
                  <input type="radio" name="gabaryt" value="0" <?php echo (($prod['products_pack_type'] == '0' || empty($prod['products_pack_type'])) ? 'checked="checked"' : ''); ?> /> zwykły
                </p>  
                
                <p>
                  <label>Indywidualny koszt wysyłki:</label>
                  <input type="text" name="koszt_wysylki" size="8" value="<?php echo ((Funkcje::czyNiePuste($prod['shipping_cost'])) ? $prod['shipping_cost'] : ''); ?>" class="toolTip" title="Indywidualny koszt wysyłki produktu - jeżeli w koszyku jest produkt z indywidualnym kosztem pozostałe metody wysyłki są niedostępne (wartość pusta lub 0 oznacza brak indywidualnego kosztu wysyłki)" />
                </p>

                <p>
                  <label>Komentarze do produktu:</label>
                  <input type="radio" name="komentarz" value="1" <?php echo (($prod['products_comments'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                  <input type="radio" name="komentarz" value="0" <?php echo (($prod['products_comments'] == '0' || empty($prod['products_comments'])) ? 'checked="checked"' : ''); ?> /> nie
                </p>                                    

                <p>
                  <label>Notatki produktu:</label>
                  <textarea name="notatki" cols="30" rows="7"><?php echo $prod['products_adminnotes']; ?></textarea>
                </p>        
                
                <script type="text/javascript">
                //<![CDATA[                        
                function dodaj_znizke() {
                    ile_znizek = parseInt($("#ile_znizek").val()) + 1;
                    //
                    $('#wyniki_znizek').append('<div id="znizka_'+ile_znizek+'" class="znizka"></div>');
                    //
                    $.get('ajax/dodaj_znizke.php', { id: ile_znizek }, function(data) {
                        $('#znizka_'+ile_znizek).html(data);
                        $("#ile_znizek").val(ile_znizek);
                    });
                }                
                //]]>
                </script>        

                <div id="znizka_ramka"> 
                    <div id="znizka_tytul">Zniżki procentowe zależne od ilości produktów w koszyku:</div>
                    
                    <div id="wyniki_znizek">
                        <?php
                        $ile_juz_bylo_pozycji = 1;
                        if (!empty($prod['products_discount'])) {
                            $znizki_produktow = explode(';',$prod['products_discount']);
                            if (count($znizki_produktow) > 0) {
                                //
                                for ($a = 0, $c = count($znizki_produktow); $a < $c; $a++) {
                                    //
                                    $podtablica_pozycji = explode(':',$znizki_produktow[$a]);
                                    //
                                    ?>
                                    <div id="znizka_<?php echo $ile_juz_bylo_pozycji; ?>" class="znizka">
                                        od <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[0], 2, '.', ''); ?>" name="znizki_od_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /> 
                                        do <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[1], 2, '.', ''); ?>" name="znizki_do_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /> 
                                        zniżka <input class="kropka" type="text" value="<?php echo number_format($podtablica_pozycji[2], 2, '.', ''); ?>" name="znizki_wart_<?php echo $ile_juz_bylo_pozycji; ?>" size="7" /> %
                                    </div>                           
                                    <?php
                                    unset($podtablica_pozycji);
                                    $ile_juz_bylo_pozycji++;
                                }
                                //
                            }
                        }
                        ?>
                        <div id="znizka_<?php echo $ile_juz_bylo_pozycji; ?>" class="znizka">
                            od <input class="kropka" type="text" value="" name="znizki_od_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" />
                            do <input class="kropka" type="text" value="" name="znizki_do_<?php echo $ile_juz_bylo_pozycji; ?>" size="5" /> 
                            zniżka <input class="kropka" type="text" value="" name="znizki_wart_<?php echo $ile_juz_bylo_pozycji; ?>" size="7" /> %
                        </div>
                    </div>
                    
                    <input value="<?php echo $ile_juz_bylo_pozycji; ?>" type="hidden" name="ile_znizek" id="ile_znizek" />  
                    
                    <div style="padding-top:10px"><span class="dodaj" onclick="dodaj_znizke()" style="cursor:pointer">dodaj pozycję</span></div>
                </div>

            </td>
            <td style="width:40%;vertical-align:top">

                <?php
                $vat = Produkty::TablicaStawekVat('', true, true);
                $domyslny_vat = $vat[1];
                
                // jezeli jest edycja produktu to musi zmienic domyslny vat
                if ($id_produktu > 0) {
                    //
                    foreach ( $vat[0] as $poz_vat ) {
                        //
                        $tb_tmp = explode('|', $poz_vat['id']);
                        if ( $tb_tmp[1] == $prod['products_tax_class_id'] ) {
                             $domyslny_vat = $poz_vat['id'];
                        }
                        //
                    }
                    //
                    unset($poz_vat);
                }                
                ?>
            
                <table class="tbl_cena">
                    <tr>
                        <td style="padding-bottom:15px">Stawka VAT:</td>
                        <td style="padding-bottom:15px" colspan="2">
                            <?php echo Funkcje::RozwijaneMenu('vat', $vat[0], $domyslny_vat, ' id="vat"'); ?>
                        </td>
                    </tr>
                    
                    <?php
                    unset($vat, $domyslny_vat);
                    ?>
                    
                    <tr><td colspan="3" class="tbl_cena_naglowek" style="padding-bottom:7px">Cena produktu:</td></tr>
                    <tr>
                        <td align="center"><span style="white-space:nowrap;">Cena nr</span></td>
                        <td align="center"><span>Cena netto</span></td>
                        <td align="center"><span>VAT</span></td>
                        <td align="center"><span>Cena brutto</span></td>
                    </tr>
                    <?php 
                    for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
                    <tr>
                        <td align="center"><?php echo $x; ?>.</td>
                        <td><input type="text" class="oblicz" name="cena_<?php echo $x; ?>" size="9" value="<?php echo ((Funkcje::czyNiePuste($prod['products_price' . (($x > 1) ? '_'.$x : '')])) ? $prod['products_price' . (($x > 1) ? '_'.$x : '')] : ''); ?>" id="cena_<?php echo $x; ?>" /></td>
                        <td><input type="text" name="v_at_<?php echo $x; ?>" size="5" value="<?php echo ((Funkcje::czyNiePuste($prod['products_price' . (($x > 1) ? '_'.$x : '')])) ? $prod['products_tax' . (($x > 1) ? '_'.$x : '')] : ''); ?>" id="v_at_<?php echo $x; ?>" /></td>
                        <td><input type="text" class="oblicz_brutto min" name="brut_<?php echo $x; ?>" size="9" value="<?php echo ((Funkcje::czyNiePuste($prod['products_price_tax' . (($x > 1) ? '_'.$x : '')])) ? $prod['products_price_tax' . (($x > 1) ? '_'.$x : '')] : ''); ?>" id="brut_<?php echo $x; ?>" /></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3" style="padding:8px">
                            <div style="padding-bottom:5px;font-weight:bold">Ceny podane w walucie:</div>
                            <?php
                            $sql = $db->open_query("select * from currencies");  
                            $tab = array();
                            // jezeli jest edycja produktu
                            if ($id_produktu > 0) {
                                $domyslnaWal = $prod['products_currencies_id'];
                              } else {
                                $domyslnaWal = $domyslna_waluta['id'];
                            }                            
                            while ($cust = $sql->fetch_assoc()) {
                                echo '<input type="radio" name="waluta" value="'.$cust['currencies_id'].'" '.(($cust['currencies_id'] == $domyslnaWal) ? 'checked="checked"' : '').' /> ' . $cust['title'] . '<br />';
                            }
                            unset($domyslnaWal);
                            ?>                                            
                        </td>
                    </tr>                                                                                        
                </table>  
                
                <div class="tbl_cena_naglowek" style="padding:5px">Cena katalogowa:</div>               

                <div class="modul"> 
                    <div>
                        <span class="dat">Cena katalogowa:</span><input type="text" name="cena_katalogowa_1" class="toolTipTop kropkaPusta greaterThan" data-linked="brut_1" title="Cena będzie wyświetlana jako cena katalogowa produktu - wyższa niż cena sklepowa" value="<?php echo ((Funkcje::czyNiePuste($prod['products_retail_price'])) ? $prod['products_retail_price'] : ''); ?>" size="20" />
                    </div>                                            
                    
                    <?php
                    for ($x = 2; $x <= ILOSC_CEN; $x++) { ?>
                        
                    <div>
                        <span class="dat">Dla ceny nr <?php echo $x; ?>:</span><input type="text" name="cena_katalogowa_<?php echo $x; ?>" class="toolTipTop kropkaPusta greaterThan" title="Cena będzie wyświetlana jako cena katalogowa produktu - wyższa niż cena sklepowa" value="<?php echo ((Funkcje::czyNiePuste($prod['products_retail_price_' . $x])) ? $prod['products_retail_price_' . $x] : ''); ?>" size="20" data-linked="brut_<?php echo $x; ?>" />
                    </div> 

                    <?php } ?>     
                </div>
                
                <div class="tbl_cena_naglowek" style="padding:5px">Produkt widoczny w modułach:</div>               

                <div class="modul">
                    <?php
                    if ( NOWOSCI_USTAWIENIA == 'automatycznie wg daty dodania' ) {
                    ?>
                    <span class="toolTipTop" title="Opcja nieaktywna - nowości określane na podstawie daty dodania"><input type="checkbox" disabled="disabled" name="nowosc" value="1" <?php echo (($prod['new_status'] == '1') ? 'checked="checked"' : ''); ?> /> <span class="wylaczony">produkt jako <b>NOWOŚĆ</b></span></span>
                    <?php } else { ?>
                    <input type="checkbox" name="nowosc" value="1" <?php echo (($prod['new_status'] == '1') ? 'checked="checked"' : ''); ?> /> produkt jako <b>NOWOŚĆ</b>
                    <?php } ?>
                </div>
                
                <div class="modul"> 
                    <input type="checkbox" name="hit" value="1" <?php echo (($prod['star_status'] == '1') ? 'checked="checked"' : ''); ?> /> produkt jako <b>NASZ HIT</b>
                     
                    <div>
                        <span class="dat">Data rozpoczęcia:</span><input type="text" id="data_hit_od" name="data_hit_od" value="<?php echo ((Funkcje::czyNiePuste($prod['star_date'])) ? date('d-m-Y',strtotime($prod['star_date'])) : ''); ?>" size="20" class="datepicker" />
                    </div>
                    <div>
                        <span class="dat">Data zakończenia:</span><input type="text" id="data_hit_do" name="data_hit_do" value="<?php echo ((Funkcje::czyNiePuste($prod['star_date_end'])) ? date('d-m-Y',strtotime($prod['star_date_end'])) : ''); ?>" size="20" class="datepicker" />
                    </div>
                </div>
                
                <div class="modul"> 
                    <input type="checkbox" name="polecany" value="1" <?php echo (($prod['featured_status'] == '1') ? 'checked="checked"' : ''); ?> /> produkt jako <b>POLECANY</b>
                     
                    <div>
                        <span class="dat">Data rozpoczęcia:</span><input type="text" id="data_polecany_od" name="data_polecany_od" value="<?php echo ((Funkcje::czyNiePuste($prod['featured_date'])) ? date('d-m-Y',strtotime($prod['featured_date'])) : ''); ?>" size="20" class="datepicker" />
                    </div>
                    <div>
                        <span class="dat">Data zakończenia:</span><input type="text" id="data_polecany_do" name="data_polecany_do" value="<?php echo ((Funkcje::czyNiePuste($prod['featured_date_end'])) ? date('d-m-Y',strtotime($prod['featured_date_end'])) : ''); ?>" size="20" class="datepicker" />
                    </div>
                </div>

                <div class="modul"> 
                    <input type="checkbox" name="promocja" value="1" <?php echo (($prod['specials_status'] == '1') ? 'checked="checked"' : ''); ?> /> produkt jako <b>PROMOCJA</b>
                    
                    <div>
                        <span class="dat">Cena poprzednia:</span><input type="text" name="cena_poprzednia" id="cena_poprzednia" class="toolTipTop" title="Cena będzie wyświetlana jako przekreślona - pole musi być wypełnione żeby produkt wyświetlał się jako promocja" value="<?php echo ((Funkcje::czyNiePuste($prod['products_old_price'])) ? $prod['products_old_price'] : ''); ?>" size="20" />
                    </div>                                            
                    
                    <?php
                    for ($x = 2; $x <= ILOSC_CEN; $x++) { ?>
                        
                    <div>
                        <span class="dat">Dla ceny nr <?php echo $x; ?>:</span><input type="text" name="cena_poprzednia_<?php echo $x; ?>" id="cena_poprzednia_<?php echo $x; ?>" class="toolTipTop" title="Cena będzie wyświetlana jako przekreślona - pole musi być wypełnione żeby produkt wyświetlał się jako promocja" value="<?php echo ((Funkcje::czyNiePuste($prod['products_old_price_' . $x])) ? $prod['products_old_price_' . $x] : ''); ?>" size="20" />
                    </div> 

                    <?php } ?>
                     
                    <div>
                        <span class="dat">Data rozpoczęcia:</span><input type="text" id="data_promocja_od" name="data_promocja_od" value="<?php echo ((Funkcje::czyNiePuste($prod['specials_date'])) ? date('d-m-Y',strtotime($prod['specials_date'])) : ''); ?>" size="20"  class="datepicker" />
                    </div>
                    <div style="margin-left:100px">
                        godz: 
                        <select name="data_promocja_od_godzina">
                        <?php
                        $godz = ((Funkcje::czyNiePuste($prod['specials_date'])) ? date('H',strtotime($prod['specials_date'])) : '0');
                        for ($c = 0;$c < 24; $c++) { 
                            $chec = '';
                            if ($godz == $c) { 
                                $chec = ' selected="selected"';
                            }
                            echo '<option value="'.$c.'"'.$chec.'>'.$c.'</option>'; 
                        } 
                        unset($godz);
                        ?>
                        </select>
                        min: 
                        <select name="data_promocja_od_minuty">
                        <?php
                        $min = ((Funkcje::czyNiePuste($prod['specials_date'])) ? date('i',strtotime($prod['specials_date'])) : '0');
                        for ($c = 0;$c < 6; $c++) { 
                            $chec = '';
                            if ($min == $c*10) { 
                                $chec = ' selected="selected"';
                            }
                            echo '<option value="'.($c*10).'"'.$chec.'>'.($c*10).'</option>'; 
                        } 
                        unset($min);
                        ?>
                        </select>                                              
                    </div>
                    <div style="padding:5px">
                        <span class="dat">Data zakończenia:</span><input type="text" id="data_promocja_do" name="data_promocja_do" value="<?php echo ((Funkcje::czyNiePuste($prod['specials_date_end'])) ? date('d-m-Y',strtotime($prod['specials_date_end'])) : ''); ?>" size="20" class="datepicker" />
                    </div>
                    <div style="margin-left:100px">
                        godz: 
                        <select name="data_promocja_do_godzina">
                        <?php
                        $godz = ((Funkcje::czyNiePuste($prod['specials_date_end'])) ? date('H',strtotime($prod['specials_date_end'])) : '0');
                        for ($c = 0;$c < 24; $c++) { 
                            $chec = '';
                            if ($godz == $c) { 
                                $chec = ' selected="selected"';
                            }
                            echo '<option value="'.$c.'"'.$chec.'>'.$c.'</option>'; 
                        } 
                        unset($godz);
                        ?>
                        </select>
                        min: 
                        <select name="data_promocja_do_minuty">
                        <?php
                        $min = ((Funkcje::czyNiePuste($prod['specials_date_end'])) ? date('i',strtotime($prod['specials_date_end'])) : '0');
                        for ($c = 0;$c < 6; $c++) { 
                            $chec = '';
                            if ($min == $c*10) { 
                                $chec = ' selected="selected"';
                            }
                            echo '<option value="'.($c*10).'"'.$chec.'>'.($c*10).'</option>'; 
                        } 
                        unset($min);
                        ?>                        
                        </select>                                              
                    </div>                                            
                </div> 
                
                <div class="modul">
                    <input type="checkbox" name="export" value="1" <?php echo (($prod['export_status'] == '1') ? 'checked="checked"' : ''); ?> /> produkt eksportowany do <b>PORÓWNYWAREK</b>
                </div>  

                <div class="modul">
                    <input type="checkbox" name="negocjacja" value="1" <?php echo (($prod['products_make_an_offer'] == '1') ? 'checked="checked"' : ''); ?> /> pozwalaj na <b>NEGOCJACJE CENY</b>
                </div> 

                <div class="modul">
                    <input type="checkbox" class="toolTipTop" title="Dla tego produktu koszty wysyłki dla wszystkich dostępnych wysyłek będą wynosiły 0 zł" name="darmowa_dostawa" value="1" <?php echo (($prod['free_shipping_status'] == '1') ? 'checked="checked"' : ''); ?> /> produkt objęty <b>DARMOWĄ DOSTAWĄ</b>
                </div>                  

                <?php
                unset($tablica, $info);
                ?>

            </td>
            
        </tr>
        
    </table>

</div>

<?php } ?>