<?php if ($prot->wyswietlStrone) { ?>

<div id="zakl_id_5" style="display:none;">

    <div class="info_content">

        <?php 
        if ($id_produktu > 0 && $zadanieDuplikacja == false) {
            $id_rand = $id_produktu;
          } else {
            $id_rand = rand(900000000,1000000000); 
        }
        ?>
        <input type="hidden" id="id_unikalne" name="id_unikalne" value="<?php echo $id_rand; ?>" />
        
        <?php
        // czyszczenie cech ze staych porzuconych wartosci
        if (!isset($_POST['akcja'])) {
            $db->delete_query('products_attributes' , " products_id > 900000000");
            $db->delete_query('products_stock' , " products_id > 900000000");
        }
        
        
        // jezeli jest duplikacja to kopiuje tablice cech
        if ($zadanieDuplikacja == true && !isset($_POST['akcja'])) {
            //
            // kopiowanie tablicy products_attributes
            //
            $cechyKopiowane = "select distinct * from products_attributes where products_id = '" . (int)$id_produktu . "'";
            $sqlc = $db->open_query($cechyKopiowane); 
            while ($cecha = $sqlc->fetch_assoc()) {            
                $pola = array(
                        array('products_id',$id_rand),
                        array('options_id',$cecha['options_id']),
                        array('options_values_id',$cecha['options_values_id']),
                        array('options_values_price',$cecha['options_values_price']),
                        array('options_values_tax',$cecha['options_values_tax']),
                        array('options_values_price_tax',$cecha['options_values_price_tax']),
                        array('price_prefix',$cecha['price_prefix']),
                        array('options_values_weight',$cecha['options_values_weight'])
                        );        
                $sql = $db->insert_query('products_attributes', $pola);
                unset($pola);            
            }
            //
            // kopiowanie tablicy products_stock
            //
            $cechyKopiowane = "select distinct * from products_stock where products_id = '" . (int)$id_produktu . "'";
            $sqlc = $db->open_query($cechyKopiowane); 
            while ($cecha = $sqlc->fetch_assoc()) {            
                $pola = array(
                        array('products_id',$id_rand),
                        array('products_stock_attributes',$cecha['products_stock_attributes']),
                        array('products_stock_quantity',$cecha['products_stock_quantity']),
                        array('products_stock_availability_id',$cecha['products_stock_availability_id']),
                        array('products_stock_model',$cecha['products_stock_model']),
                        array('products_stock_price',$cecha['products_stock_price']),
                        array('products_stock_tax',$cecha['products_stock_tax']),
                        array('products_stock_price_tax',$cecha['products_stock_price_tax'])
                        );        
                        
                // ceny
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    //
                    $pola[] = array('products_stock_price_'.$x,$cecha['products_stock_price_'.$x]);
                    $pola[] = array('products_stock_tax_'.$x,$cecha['products_stock_tax_'.$x]);
                    $pola[] = array('products_stock_price_tax_'.$x,$cecha['products_stock_price_tax_'.$x]);
                    //
                }
        
                $sql = $db->insert_query('products_stock', $pola);
                unset($pola);            
            }            
        }   
        ?>        

        <script type="text/javascript" src="produkty/cechy.js"></script> 

        <div class="cechy_naglowek">Sposób obliczania końcowej wartości produktu z cechami</div>
        
        <div class="ramka_cech_rodzaj">
            <div id="wyborRodzajuCechy">
                <input type="radio" value="cechy" name="rodzaj_cechy" id="rodzajCechyCecha" onclick="typ_cechy('cechy')" <?php echo (((isset($prod['options_type']) && $prod['options_type'] == 'cechy') || !isset($prod['options_type'])) ? 'checked="checked"' : ''); ?> /> cena produktu obliczana wg wartości cech <br />
                <input type="radio" value="ceny" name="rodzaj_cechy" id="rodzajCechyCena" onclick="typ_cechy('ceny')" <?php echo ((isset($prod['options_type']) && $prod['options_type'] == 'ceny') ? 'checked="checked"' : ''); ?> /> cena produktu przypisana na stałe do kombinacji cech
            </div>        
        </div>
        
        <div class="cechy_naglowek">Wybierz cechę do dodania</div>
        
        <div class="cechy_info">
            <div class="ostrzezenie">
                Dodanie nowej cechy lub wartości spowoduje wyzerowanie wszystkich stanów magazynowych, dostępności, <b>cen produktów wg kombinacji cech</b>, nr katalogowych cech oraz indywidualnych zdjęć cech dla danego produktu.
            </div>
        </div>

        <table class="cechy"><tr>
            <td>Nazwa:</td>
            <td>
                <?php
                $cechy = "select distinct * from products_options where language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                $sqlc = $db->open_query($cechy);
                //
                $id_domyslne = 0;
                $tablica = array();
                //
                while ($cecha = $sqlc->fetch_assoc()) {
                    if ($id_domyslne == 0) {
                        $id_domyslne = $cecha['products_options_id'];
                    }
                    $tablica[] = array('id' => $cecha['products_options_id'], 'text' =>$cecha['products_options_name']);
                }
                $db->close_query($sqlc);
                
                echo Funkcje::RozwijaneMenu('cecha', $tablica, $id_domyslne, 'style="width:130px" id="id_cecha" onchange="zmien_ceche()"');
                
                unset($cecha, $tablica);
                ?>
            </td>
            <td>Wartość:</td>
            <td id="cech_wartosc">
                <?php
                $cechy = "select distinct po.products_options_values_name, pop.products_options_id, po.products_options_values_thumbnail, po.products_options_values_id, pop.products_options_values_id, pop.products_options_values_sort_order from products_options_values po, products_options_values_to_products_options pop where po.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and po.products_options_values_id = pop.products_options_values_id and pop.products_options_id = '".$id_domyslne."' order by pop.products_options_values_sort_order";
                $sqlc = $db->open_query($cechy);
                //
                $id_domyslna_wartosc = 0;
                $tablica = array();
                //
                while ($cecha = $sqlc->fetch_assoc()) {
                    if ($id_domyslna_wartosc == 0) {
                        $id_domyslna_wartosc = $cecha['products_options_values_id'];
                    }
                    $tablica[] = array('id' => $cecha['products_options_values_id'], 'text' => $cecha['products_options_values_name']);
                }
                $db->close_query($sqlc);
                
                echo Funkcje::RozwijaneMenu('cecha', $tablica, $id_domyslna_wartosc, 'style="width:130px" id="id_wartosc"');
                
                unset($cecha, $tablica, $id_domyslne);
                ?>
            </td>
            
            <td>
                <img src="obrazki/rozwin.png" id="dodaj_ceche" style="cursor:pointer" onclick="lista_cech()" alt="Dodaj cechę do produktu" title="Dodaj cechę do produktu" />
            </td>
        </tr></table>
        
        <div id="lista_cech">
        
        </div>
        
        <?php if ($id_produktu > 0) { ?>
        <script type="text/javascript">
        //<![CDATA[            
        lista_cech('wyswietl'<?php echo ((isset($prod['options_type']) && $prod['options_type'] == 'ceny') ? ",'tak'" : ""); ?>);
        //]]>
        </script>                          
        <?php } ?>        

    </div>
    
</div>

<?php } ?>
