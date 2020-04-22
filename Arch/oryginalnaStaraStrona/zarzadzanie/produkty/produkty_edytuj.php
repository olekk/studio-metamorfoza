<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz_edycje') {
    
        // ustalanie ilosci zdjec
        $pola_zdjec = array();
        for ($r = 1; $r < 100; $r++) {
            if (isset($_POST['zdjecie_'.$r]) && !empty($_POST['zdjecie_'.$r])) {
                $pola_zdjec[] = array('zdjecie' => $filtr->process($_POST['zdjecie_'.$r]),
                                      'alt' => $filtr->process($_POST['alt_'.$r]),
                                      'sort' => $filtr->process($_POST['sort_'.$r]));
            }
        }
        
        $ilosc_produktow = $filtr->process($_POST['ilosc']);
    
        $pola = array(
                array('products_status',$_POST['status']),
                array('products_buy',(int)$_POST['kupowanie']),
                array('products_accessory',(int)$_POST['akcesoria']),
                array('products_date_added',((trim($_POST['data_dodania']) != '') ? date('Y-m-d H:i:s', strtotime($filtr->process($_POST['data_dodania']))) : 'now()')),
                array('customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0)),
                array('not_customers_group_id',((isset($_POST['nie_grupa_klientow'])) ? implode(',', $_POST['nie_grupa_klientow']) : 0)),
                array('sort_order',$filtr->process($_POST['sort'])),
                array('products_model',$filtr->process($_POST['nr_kat'])),
                array('products_man_code',$filtr->process($_POST['kod_producenta'])),
                array('products_id_private',$filtr->process($_POST['nr_kat_klienta'])),
                array('products_ean',$filtr->process($_POST['nr_ean'])),
                array('products_pkwiu',$filtr->process($_POST['pkwiu'])),
                array('products_weight',$filtr->process($_POST['waga'])),
                array('products_date_available',((!empty($_POST['data_dostepnosci'])) ? date('Y-m-d', strtotime($filtr->process($_POST['data_dostepnosci']))) : '')),
                array('manufacturers_id',$filtr->process($_POST['producent'])),
                array('products_availability_id',$filtr->process($_POST['dostepnosci'])),
                array('products_shipping_time_id',$filtr->process($_POST['wysylka'])),
                array('products_condition_products_id',$filtr->process($_POST['stan_produktu'])),
                array('products_warranty_products_id',$filtr->process($_POST['gwarancja'])),
                array('products_type',$filtr->process($_POST['rodzaj_produktu'])),
                array('products_quantity',$ilosc_produktow),               
                array('products_jm_id',$filtr->process($_POST['jednostka_miary'])),
                array('products_pack_type',$filtr->process($_POST['gabaryt'])),
                array('products_comments',$filtr->process($_POST['komentarz'])),
                array('products_minorder',$filtr->process($_POST['min_ilosc'])),
                array('products_maxorder',$filtr->process($_POST['max_ilosc'])),
                array('products_quantity_order',$filtr->process($_POST['ilosc_zbiorcza'])),
                array('shipping_cost',$filtr->process($_POST['koszt_wysylki'])),
                array('products_adminnotes',$filtr->process($_POST['notatki'])));
                
        // id podatku
        $stawka_vat = explode('|', $filtr->process($_POST['vat']));
        $pola[] = array('products_tax_class_id',$stawka_vat[1]);
        //         
                
                
        // pierwsze zdjecie produktu
        if (count($pola_zdjec) > 0) {
            $pola[] = array('products_image',$pola_zdjec[0]['zdjecie']);
            $pola[] = array('products_image_description',$pola_zdjec[0]['alt']);
          } else {
            $pola[] = array('products_image','');
            $pola[] = array('products_image_description','');          
        }
        
        // ceny produktu
        $pola[] = array('products_price',$filtr->process($_POST['cena_1']));
        $pola[] = array('products_tax',$filtr->process($_POST['v_at_1']));
        $pola[] = array('products_price_tax',$filtr->process($_POST['brut_1']));        
        $pola[] = array('products_retail_price',$filtr->process($_POST['cena_katalogowa_1'])); 
      
        // ceny
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
            if (isset($_POST['cena_'.$x]) && isset($_POST['v_at_'.$x]) && isset($_POST['brut_'.$x])) {
                $pola[] = array('products_price_'.$x,$filtr->process($_POST['cena_'.$x]));
                $pola[] = array('products_tax_'.$x,$filtr->process($_POST['v_at_'.$x]));
                $pola[] = array('products_price_tax_'.$x,$filtr->process($_POST['brut_'.$x]));
            }
            $pola[] = array('products_retail_price_'.$x,$filtr->process($_POST['cena_katalogowa_'.$x]));
        }
        
        $pola[] = array('products_currencies_id',$filtr->process($_POST['waluta']));
        // nowosci
        if (isset($_POST['nowosc'])) {
            $pola[] = array('new_status',$filtr->process($_POST['nowosc']));
        } else {
            $pola[] = array('new_status','0');
        }
        // nasz hit
        if (isset($_POST['hit']) && $_POST['hit'] == '1') {
            $pola[] = array('star_status',$filtr->process($_POST['hit']));
            if (!empty($_POST['data_hit_od'])) {
                $pola[] = array('star_date',date('Y-m-d', strtotime($filtr->process($_POST['data_hit_od']))));
              } else {
                $pola[] = array('star_date','0000-00-00');                     
            }
            if (!empty($_POST['data_hit_do'])) {
                $pola[] = array('star_date_end',date('Y-m-d', strtotime($filtr->process($_POST['data_hit_do']))));
              } else {
                $pola[] = array('star_date_end','0000-00-00');                  
            }
        } else {
            $pola[] = array('star_status','0');
            $pola[] = array('star_date','0000-00-00');
            $pola[] = array('star_date_end','0000-00-00');        
        }
        // polecany
        if (isset($_POST['polecany']) && $_POST['polecany'] == '1') {
            $pola[] = array('featured_status',$filtr->process($_POST['polecany']));
            if (!empty($_POST['data_polecany_od'])) {
                $pola[] = array('featured_date',date('Y-m-d', strtotime($filtr->process($_POST['data_polecany_od']))));
              } else {
                $pola[] = array('featured_date','0000-00-00');                  
            }
            if (!empty($_POST['data_polecany_do'])) {
                $pola[] = array('featured_date_end',date('Y-m-d', strtotime($filtr->process($_POST['data_polecany_do']))));  
              } else {
                $pola[] = array('featured_date_end','0000-00-00');                     
            }
        } else {
            $pola[] = array('featured_status','0');
            $pola[] = array('featured_date','0000-00-00');
            $pola[] = array('featured_date_end','0000-00-00');      
        }
        // promocja
        if (isset($_POST['promocja']) && !empty($_POST['cena_poprzednia']) && $_POST['promocja'] == '1') {
        
            $pola[] = array('products_old_price',$filtr->process($_POST['cena_poprzednia']));
            
            // ceny dla pozostalych poziomow cen
            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                if (isset($_POST['cena_poprzednia_'.$x])) {
                    $pola[] = array('products_old_price_'.$x,$filtr->process($_POST['cena_poprzednia_'.$x]));
                }
            }            
            
            $pola[] = array('specials_status',$filtr->process($_POST['promocja']));
            if (!empty($_POST['data_promocja_od'])) {
                $pola[] = array('specials_date',date('Y-m-d H:i:s', strtotime($filtr->process($_POST['data_promocja_od'])) + (int)$_POST['data_promocja_od_godzina'] * 3600 + (int)$_POST['data_promocja_od_minuty'] * 60 ));
              } else {
                $pola[] = array('specials_date','0000-00-00');                    
            }
            if (!empty($_POST['data_promocja_do'])) {
                $pola[] = array('specials_date_end',date('Y-m-d H:i:s', strtotime($filtr->process($_POST['data_promocja_do'])) + (int)$_POST['data_promocja_do_godzina'] * 3600 + (int)$_POST['data_promocja_do_minuty'] * 60 ));
              } else {
                $pola[] = array('specials_date_end','0000-00-00');                  
            }
        } else {
            $pola[] = array('products_old_price','0');
            //
            // ceny dla pozostalych poziomow cen
            for ($x = 2; $x <= ILOSC_CEN; $x++) {
                if (isset($_POST['cena_poprzednia_'.$x])) {
                    $pola[] = array('products_old_price_'.$x,'0');
                }
            }         
            //
            $pola[] = array('specials_status','0');
            $pola[] = array('specials_date','0000-00-00 00:00:00');
            $pola[] = array('specials_date_end','0000-00-00 00:00:00');       
        }
        // porownywarki
        if (isset($_POST['export'])) {
            $pola[] = array('export_status',$filtr->process($_POST['export']));   
        } else {
            $pola[] = array('export_status','0');  
        }
        // negocjacja
        if (isset($_POST['negocjacja'])) {
            $pola[] = array('products_make_an_offer',$filtr->process($_POST['negocjacja']));         
        } else {
            $pola[] = array('products_make_an_offer','0');           
        }
        // darmowa dostawa
        if (isset($_POST['darmowa_dostawa'])) {
            $pola[] = array('free_shipping_status',$filtr->process($_POST['darmowa_dostawa'])); 
        } else {
            $pola[] = array('free_shipping_status','0');               
        }
        
        // znizki zalezne od ilosci
        $znizki_do_zapisu = '';
        for ($w = 1; $w < 100; $w++) {
            if (isset($_POST['znizki_od_'.$w]) && isset($_POST['znizki_do_'.$w]) && isset($_POST['znizki_wart_'.$w])) {
                if (($_POST['znizki_od_'.$w] > 0 && $_POST['znizki_do_'.$w] > 0) && ($_POST['znizki_do_'.$w] > 0 && $_POST['znizki_od_'.$w] > 0)) {
                    $znizki_do_zapisu .= $filtr->process($_POST['znizki_od_'.$w]) . ":" . $filtr->process($_POST['znizki_do_'.$w]) . ":" . $filtr->process($_POST['znizki_wart_'.$w]) . ';';
                }
            }
        }
        $znizki_do_zapisu = substr($znizki_do_zapisu,0,-1);
        $pola[] = array('products_discount',$znizki_do_zapisu); 
        
        // dostepne wysylki
        if (isset($_POST['metody_wysylki'])) {
          $dostepne_wysylki = implode(';',$filtr->process($_POST['metody_wysylki']));
          $pola[] = array('shipping_method',$dostepne_wysylki);         
        } else {
          $pola[] = array('shipping_method','');         
        }

        $sql = $db->update_query('products' , $pola, 'products_id = ' . $filtr->process($_POST['id_produktu']));
        $id_edytowanej_pozycji = $filtr->process($_POST['id_produktu']);
        unset($pola);
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        
        // ---------------------------------- description
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_description' , " products_id = '".$id_edytowanej_pozycji."'");           
        
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            // jezeli nazwa w innym jezyku nie jest wypelniona
            if ( $w > 0 ) {
                if (empty($_POST['nazwa_'.$w])) {
                    $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                }
            }
            //       
            $pola = array(
                    array('products_id',$id_edytowanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('products_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('products_name_info',$filtr->process($_POST['nazwa_info_'.$w])),
                    array('products_description',$filtr->process($_POST['opis_'.$w])),
                    array('products_short_description',$filtr->process($_POST['opis_krotki_'.$w])),        
                    array('products_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),
                    array('products_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('products_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])),
                    array('products_seo_url',$filtr->process($_POST['url_meta_'.$w])));        
            $sql = $db->insert_query('products_description' , $pola);
            unset($pola);
        }
        
        // ---------------------------------- products_allegro_info
        
        if ( isset($_POST['kategoria_allegro']) ) {
        
            if ( !empty($_POST['opis_allegro']) || !empty($_POST['nazwa_allegro']) || (int)$_POST['kategoria_allegro'] > 0 || (float)$_POST['cena_brutto_allegro'] > 0 ) {
        
                // kasuje rekordy w tablicy
                $db->delete_query('products_allegro_info' , " products_id = '".$id_edytowanej_pozycji."'");           
                
                //       
                $pola = array(
                        array('products_id',$id_edytowanej_pozycji),
                        array('products_description_allegro',$filtr->process($_POST['opis_allegro'])),
                        array('products_name_allegro',$filtr->process($_POST['nazwa_allegro'])),
                        array('products_image_allegro',$filtr->process($_POST['zdjecie_allegro'])),
                        array('products_cat_id_allegro',(int)$_POST['kategoria_allegro']),
                        array('products_price_allegro',$filtr->process($_POST['cena_brutto_allegro'])));        
                $sql = $db->insert_query('products_allegro_info' , $pola);
                unset($pola);   
                
            }
            
        }

        // ---------------------------------- products to categories
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_to_categories' , " products_id = '".$id_edytowanej_pozycji."'");    
        
        if (!isset($_POST['id_kat'])) {
            $pola = array(
                    array('products_id',$id_edytowanej_pozycji),
                    array('categories_id','0'));        
            $sql = $db->insert_query('products_to_categories' , $pola);              
            //
          } else {
            $tablica_kat = $_POST['id_kat'];
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                $pola = array(
                        array('products_id',$id_edytowanej_pozycji),
                        array('categories_id',$tablica_kat[$q]));   
                //
                if ( isset($_POST['id_glowna']) && (int)$_POST['id_glowna'] > 0 ) {
                    //
                    if ( (int)$_POST['id_glowna'] == $tablica_kat[$q] ) {
                         $pola[] = array('categories_default', '1');
                    }
                    //
                }
                //
                $sql = $db->insert_query('products_to_categories' , $pola);        
            }
        }
        unset($tablica_kat, $pola); 
        
        // ---------------------------------- additional images
        
        // kasuje rekordy w tablicy
        $db->delete_query('additional_images' , " products_id = '".$id_edytowanej_pozycji."'");           
        
        for ($w = 1, $c = count($pola_zdjec); $w < $c; $w++) {
            $pola = array(
                    array('products_id',$id_edytowanej_pozycji),
                    array('images_description',$pola_zdjec[$w]['alt']),
                    array('popup_images',$pola_zdjec[$w]['zdjecie']),
                    array('sort_order',$pola_zdjec[$w]['sort']));        
            $sql = $db->insert_query('additional_images' , $pola);
            unset($pola);
        }

        // ---------------------------------- extra fields  

        // kasuje rekordy w tablicy
        $db->delete_query('products_to_products_extra_fields' , " products_id = '".$id_edytowanej_pozycji."'");         

        // pola tekstowe dla wszystkich jezykow
        $zapytanie_pola = "select * from products_extra_fields where languages_id = '0' and products_extra_fields_image = '0' order by products_extra_fields_order";
        $sqls = $db->open_query($zapytanie_pola);
        //
        if ($db->ile_rekordow($sqls) > 0) { 
            //
            while ($infs = $sqls->fetch_assoc()) { 
                if (!empty($_POST['pole_999_'.$infs['products_extra_fields_id']])) {
                    $pola = array(
                            array('products_id',$id_edytowanej_pozycji),
                            array('products_extra_fields_id',$infs['products_extra_fields_id']),
                            array('products_extra_fields_value',$filtr->process($_POST['pole_999_'.$infs['products_extra_fields_id']])),
                            array('products_extra_fields_link',$filtr->process($_POST['pole_url_999_'.$infs['products_extra_fields_id']])));        
                    $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                    unset($pola);                
                }                
            }
            //
        }
        $db->close_query($sqls);
        unset($zapytanie_pola); 
        //
        // pola graficzne dla wszystkich jezykow
        $zapytanie_pola = "select * from products_extra_fields where languages_id = '0' and products_extra_fields_image = '1' order by products_extra_fields_order";
        $sqls = $db->open_query($zapytanie_pola);
        //
        if ($db->ile_rekordow($sqls) > 0) { 
            //
            while ($infs = $sqls->fetch_assoc()) { 
                if (!empty($_POST['pole_999_zdjecie_'.$infs['products_extra_fields_id']])) {
                    $pola = array(
                            array('products_id',$id_edytowanej_pozycji),
                            array('products_extra_fields_id',$infs['products_extra_fields_id']),
                            array('products_extra_fields_value',$filtr->process($_POST['pole_999_zdjecie_'.$infs['products_extra_fields_id']])),
                            array('products_extra_fields_link',$filtr->process($_POST['pole_url_999_zdjecie_'.$infs['products_extra_fields_id']])));        
                    $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                    unset($pola);                
                }                
            }
            //
        }   
        $db->close_query($sqls);
        unset($zapytanie_pola); 
        //     
        // pola tekstowe i graficzne dla poszczegolnych jezykow
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {        
            //
            // pola tekstowe
            $zapytanie_pola = "select * from products_extra_fields where languages_id = '" . $ile_jezykow[$w]['id'] . "' and products_extra_fields_image = '0' order by products_extra_fields_order";
            $sqls = $db->open_query($zapytanie_pola);
            //
            if ($db->ile_rekordow($sqls) > 0) { 
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    if (isset($_POST['pole_'.$infs['products_extra_fields_id']]) && !empty($_POST['pole_'.$infs['products_extra_fields_id']])) {
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_extra_fields_id',$infs['products_extra_fields_id']),
                                array('products_extra_fields_value',$filtr->process($_POST['pole_'.$infs['products_extra_fields_id']])),
                                array('products_extra_fields_link',$filtr->process($_POST['pole_url_'.$infs['products_extra_fields_id']])));        
                        $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                        unset($pola);                
                    }                
                }
                //
            }
            $db->close_query($sqls);
            unset($zapytanie_pola); 
            //
            // pola graficzne
            $zapytanie_pola = "select * from products_extra_fields where languages_id = '" . $ile_jezykow[$w]['id'] . "' and products_extra_fields_image = '1' order by products_extra_fields_order";
            $sqls = $db->open_query($zapytanie_pola);
            //
            if ($db->ile_rekordow($sqls) > 0) { 
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    if (isset($_POST['pole_zdjecie_'.$infs['products_extra_fields_id']]) && !empty($_POST['pole_zdjecie_'.$infs['products_extra_fields_id']])) {
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_extra_fields_id',$infs['products_extra_fields_id']),
                                array('products_extra_fields_value',$filtr->process($_POST['pole_zdjecie_'.$infs['products_extra_fields_id']])),
                                array('products_extra_fields_link',$filtr->process($_POST['pole_url_zdjecie_'.$infs['products_extra_fields_id']])));        
                        $sql = $db->insert_query('products_to_products_extra_fields' , $pola);
                        unset($pola);                
                    }                
                }
                //
            }   
            $db->close_query($sqls);
            unset($zapytanie_pola);         
            //
        }
        
        // ---------------------------------- pola tekstowe 

        // kasuje rekordy w tablicy
        $db->delete_query('products_to_text_fields' , " products_id = '".$id_edytowanej_pozycji."'");         

        // pola tekstowe dla wszystkich jezykow
        $zapytanie_pola = "select products_text_fields_id from products_text_fields_info where languages_id = '".$_SESSION['domyslny_jezyk']['id']."'";
        $sqls = $db->open_query($zapytanie_pola);
        //
        if ($db->ile_rekordow($sqls) > 0) { 
            //
            while ($infs = $sqls->fetch_assoc()) { 
                if (isset($_POST['pole_txt_'.$infs['products_text_fields_id']])) {
                    $pola = array(
                            array('products_id',$id_edytowanej_pozycji),
                            array('products_text_fields_id',$infs['products_text_fields_id']));        
                    $sql = $db->insert_query('products_to_text_fields' , $pola);
                    unset($pola);                
                }                
            }
            //
        }
        $db->close_query($sqls);
        unset($zapytanie_pola); 
        //        
        
        // ---------------------------------- info (zakladki)
        
        // kasuje rekordy w tablicy
        $db->delete_query('products_info' , " products_id = '".$id_edytowanej_pozycji."'");         
        
        for ($q = 1; $q < 5; $q++) {
            //
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //           
                $pola = array(
                        array('products_id',$id_edytowanej_pozycji),
                        array('products_info_id',$q),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('products_info_name',$filtr->process($_POST['nazwa_zakladki_'.$q.'_'.$w])),
                        array('products_info_description',$filtr->process($_POST['dod_zakladka_'.$q.'_'.$w])));        
                $sql = $db->insert_query('products_info', $pola);
                unset($pola);
            } 
            //
        }
        
        // ---------------------------------- linki
        
        if ( isset($_POST['link_1_0']) ) {        
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_link' , " products_id = '".$id_edytowanej_pozycji."'");         
            
            for ($q = 1; $q < 5; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['link_'.$q.'_'.$w]) && !empty($_POST['link_url_'.$q])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_link_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_link_name',$filtr->process($_POST['link_'.$q.'_'.$w])),
                                array('products_link_description',$filtr->process($_POST['link_opis_'.$q.'_'.$w])),
                                array('products_link_url',$filtr->process($_POST['link_url_'.$q])));        
                        $sql = $db->insert_query('products_link', $pola);
                        unset($pola);
                    }
                } 
                //
            } 

        }
        
        // ---------------------------------- file
        
        if ( isset($_POST['plik_1']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_file' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($q = 1; $q < 6; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['plik_'.$q]) && !empty($_POST['plik_nazwa_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_file_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_file_name',$filtr->process($_POST['plik_nazwa_'.$q.'_'.$w])),
                                array('products_file',$filtr->process($_POST['plik_'.$q])),
                                array('products_file_description',$filtr->process($_POST['plik_opis_'.$q.'_'.$w])),
                                array('products_file_login',$filtr->process($_POST['plik_klient_'.$q]))
                                );        
                        $sql = $db->insert_query('products_file', $pola);
                        unset($pola);
                    }
                } 
                //
            } 

        }
        
        // ---------------------------------- pliki elektroniczne
        
        if ( isset($_POST['ile_plikow_0']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_file_shopping' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                for ($q = 1; $q <= (int)($_POST['ile_plikow_'.$w]); $q++) {
                    //
                    if (!empty($_POST['plik_elektroniczny_nazwa_'.$q.'_'.$w]) && !empty($_POST['plik_elektroniczny_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_file_shopping_name',$filtr->process($_POST['plik_elektroniczny_nazwa_'.$q.'_'.$w])),
                                array('products_file_shopping',$filtr->process($_POST['plik_elektroniczny_'.$q.'_'.$w]))
                                );        
                        $sql = $db->insert_query('products_file_shopping', $pola);
                        unset($pola);
                    }
                } 
                //
            } 
            
        }

        // ---------------------------------- youtube
        
        if ( isset($_POST['film_url_1']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_youtube' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($q = 1; $q < 5; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['film_url_'.$q]) && !empty($_POST['film_nazwa_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_film_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_film_name',$filtr->process($_POST['film_nazwa_'.$q.'_'.$w])),
                                array('products_film_url',$filtr->process($_POST['film_url_'.$q])),
                                array('products_film_description',$filtr->process($_POST['film_opis_'.$q.'_'.$w])),
                                array('products_film_width',$filtr->process($_POST['film_szerokosc_'.$q])),
                                array('products_film_height',$filtr->process($_POST['film_wysokosc_'.$q]))
                                );        
                        $sql = $db->insert_query('products_youtube', $pola);
                        unset($pola);
                    }
                } 
                //
            }

        }
        
        // ---------------------------------- filmy flv
        
        if ( isset($_POST['flv_plik_1']) ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_film' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($q = 1; $q < 5; $q++) {
                //
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    //
                    if (!empty($_POST['flv_plik_'.$q]) && !empty($_POST['flv_nazwa_'.$q.'_'.$w])) {
                        //               
                        $pola = array(
                                array('products_id',$id_edytowanej_pozycji),
                                array('products_film_id',$q),
                                array('language_id',$ile_jezykow[$w]['id']),
                                array('products_film_name',$filtr->process($_POST['flv_nazwa_'.$q.'_'.$w])),
                                array('products_film_full_size',$filtr->process($_POST['flv_ekran_'.$q])),
                                array('products_film_file',$filtr->process($_POST['flv_plik_'.$q])),
                                array('products_film_description',$filtr->process($_POST['flv_opis_'.$q.'_'.$w])),
                                array('products_film_width',$filtr->process($_POST['flv_szerokosc_'.$q])),
                                array('products_film_height',$filtr->process($_POST['flv_wysokosc_'.$q]))
                                );        
                        $sql = $db->insert_query('products_film', $pola);
                        unset($pola);
                    }
                } 
                //
            }    

        }
        
        // ---------------------------------- muzyka mp3
        
        // ustalanie ilosci zdjec
        $pola_mp3 = array();
        for ($r = 1; $r < 100; $r++) {
            if (isset($_POST['utwor_mp3_'.$r]) && !empty($_POST['utwor_mp3_'.$r])) {
                $pola_mp3[] = array('plik_mp3' => $filtr->process($_POST['utwor_mp3_'.$r]),
                                    'nazwa_mp3' => $filtr->process($_POST['nazwa_mp3_'.$r]));
            }
        }        

        if ( count($pola_mp3) > 0 ) {
        
            // kasuje rekordy w tablicy
            $db->delete_query('products_mp3' , " products_id = '".$id_edytowanej_pozycji."'");          
            
            for ($w = 0, $c = count($pola_mp3); $w < $c; $w++) {
                $pola = array(
                        array('products_id',$id_edytowanej_pozycji),
                        array('products_mp3_id',($w + 1)),
                        array('products_mp3_name',$pola_mp3[$w]['nazwa_mp3']),
                        array('products_mp3_file',$pola_mp3[$w]['plik_mp3']));          
                $sql = $db->insert_query('products_mp3' , $pola);
                unset($pola);
            } 

        }

        // ---------------------------------- obliczanie ilosci produktu na podstawie stanu magazynowego cech
        if (CECHY_MAGAZYN == 'tak') {
            //
            $ogolna_ilosc = 0;
            $zapytanie_pola = "select distinct * from products_stock where products_id = '" . $id_edytowanej_pozycji . "'";
            $sqls = $db->open_query($zapytanie_pola);     
            //
            if ((int)$db->ile_rekordow($sqls) > 0) {
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    $ogolna_ilosc = $ogolna_ilosc + $infs['products_stock_quantity'];
                }
                $db->close_query($sqls);
                //
                $pola = array(array('products_quantity',$ogolna_ilosc));
                $sql = $db->update_query('products', $pola, "products_id = '".$id_edytowanej_pozycji."'");
                //
            }
            //
            unset($zapytanie_pola, $ogolna_ilosc, $infs, $pola); 
            //
        }
        
        //
        // jezeli jest filtr kategoria
        $kat = '';
        if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
            //
            $tablica_kat = $_POST['id_kat'];
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                //
                if ((int)$tablica_kat[$q] == (int)$_GET['kategoria_id']) {
                    $_GET['kategoria_id'] = (int)$_GET['kategoria_id'];
                    break;
                }
                //
            }       
            //
        }
        unset($tablica_kat);
        //        
        Funkcje::PrzekierowanieURL('produkty.php?id_poz='.$id_edytowanej_pozycji);
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    // sprawdza czy sa wogole cechy w sklepie
    $SaCechy = false;
    $zapytanieSpr = "select products_options_id from products_options";
    $sqlSpr = $db->open_query($zapytanieSpr);     
    //
    if ( (int)$db->ile_rekordow($sqlSpr) > 0 ) {
        $SaCechy = true;
    }
    //
    $db->close_query($sqlSpr);
    unset($zapytanieSpr);
    
    // sprawdza czy sa wogole dodatkowe pola w sklepie
    $SaDodPola = false;
    $zapytanieSpr = "select products_extra_fields_id from products_extra_fields";
    $sqlSpr = $db->open_query($zapytanieSpr);     
    //
    if ( (int)$db->ile_rekordow($sqlSpr) > 0 ) {
        $SaDodPola = true;
    }
    //
    $db->close_query($sqlSpr);
    unset($zapytanieSpr); 

    // sprawdza czy sa wogole dodatkowe pola tekstowe w sklepie
    $SaDodPolaTekstowe = false;
    $zapytanieSpr = "select products_text_fields_id from products_text_fields";
    $sqlSpr = $db->open_query($zapytanieSpr);     
    //
    if ( (int)$db->ile_rekordow($sqlSpr) > 0 ) {
        $SaDodPolaTekstowe = true;
    }
    //
    $db->close_query($sqlSpr);
    unset($zapytanieSpr);       
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

        <?php if ( $SaCechy == true ) { ?>
        <script type="text/javascript" src="produkty/cechy.js"></script>   
        <?php } ?>
          
        <form action="produkty/produkty_edytuj.php" method="post" id="poForm" class="cmxform" onsubmit="return sprKat()">  

        <div class="poleForm">
            <div class="naglowek">Edycja produktu</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                ?>              
            
                <input type="hidden" name="akcja" value="zapisz_edycje" />
                
                <input type="hidden" name="id_produktu" value="<?php echo $filtr->process($_GET['id_poz']); ?>" />
                <?php 
                $zadanieDuplikacja = false;
                $id_produktu = $filtr->process((int)$_GET['id_poz']); 
                ?>

                <?php 
                $ile_jezykow = Funkcje::TablicaJezykow(); 
                $jezyk_szt = count($ile_jezykow);
                ?>

                <!-- Skrypt do walidacji formularza -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                $.validator.addMethod("greaterThan",
                function (value, element) {
                  if( $(element).val().length === 0 ) {
                      return true;
                  }
                  var $min = $("#"+$(element).data('linked'));
                  if (this.settings.onfocusout) {
                    $min.off(".validate-greaterThan").on("blur.validate-greaterThan", function () {
                      $(element).valid();
                    });
                  }
                  return parseFloat(value) > parseFloat($min.val());
                }, "Wartość musi byc większa niż cena produktu" );

                $.validator.addClassRules({
                        max: {
                            greaterThan: true
                        }
                });
                $("#poForm").validate({
                  focusCleanup: true,
                  focusInvalid: false,
                  ignoreTitle: true,
                  rules: {
                    nazwa_0: {
                      required: true
                    } 
                  },
                  messages: {
                    nazwa_0: {
                      required: "Pole jest wymagane"
                    }
                  }
                });

                $('input.datepicker').Zebra_DatePicker({
                   format: 'd-m-Y',
                   inside: false,
                   readonly_element: false
                });

                });
                
                function sprKat() {
                    var zaz = 0;
                    $('input:checkbox').each( function() {
                        nazwaKat = $(this).attr('name');
                        if ( nazwaKat == 'id_kat[]' && $(this).is(':checked') ) {
                             zaz++;
                        }
                    });
                    if ( zaz == 0 ) {
                         $.colorbox( { html:'<div id="PopUpInfo">Nie została wybrana kategoria do jakiej ma być przypisany produkt.</div>', initialWidth:50, initialHeight:50, maxWidth:'90%', maxHeight:'90%' } );
                         return false;
                    }
                    return true;
                }                                 
                
                function szukajTbl(tablica, szuk) {
                  for (var i = 0; i < tablica.length; i++) {
                      if (tablica[i] == szuk) return true;
                  }
                }   
                
                function pokaz_dane( poleId, nr, idTab ) {
                  //                  
                  var pole = $('#ajax_zakladki').val();
                  var sprawdz = pole.split(',');
                  //
                  if ( !szukajTbl(sprawdz, nr) ) {
                      //
                      $('#ekr_preloader').css('display','block');
                      //
                      var pamietaj_html = $("#" + poleId).html();
                      $.get('produkty/produkty_dodaj_zakl_' + poleId + '.php',
                            { tok: '<?php echo Sesje::Token(); ?>', id_produktu: '<?php echo $id_produktu; ?>', id_tab: idTab }, function(data) {
                            if (data != '') {
                                $("#" + poleId).html(data);
                              } else {
                                $("#" + poleId).html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            //
                            pokazChmurki();                            
                            usunPlikZdjecie();                            
                            //
                      });
                      $('#ajax_zakladki').val( $('#ajax_zakladki').val() + ',' + nr );
                      //                      
                  }
                };                    
                //]]>
                </script>           

                <input type="hidden" id="ajax_zakladki" value="" />

                <table style="width:100%"><tr>
                
                    <td id="lewe_zakladki" style="vertical-align:top">
                        <span onclick="gold_tabs_horiz('0','<?php echo $tab_0 = rand(0,999999); ?>')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</span> 
                        <span onclick="gold_tabs_horiz('14')" class="a_href_info_zakl" id="zakl_link_14">Przypisane kategorie</span>                        
                        <span onclick="gold_tabs_horiz('1','<?php echo $tab_1 = rand(0,999999); ?>','opis_')" class="a_href_info_zakl" id="zakl_link_1">Opis</span>   
                        <span onclick="gold_tabs_horiz('2','<?php echo $tab_2 = rand(0,999999); ?>','opis_krotki_')" class="a_href_info_zakl" id="zakl_link_2">Krótki opis</span>
                        <span onclick="gold_tabs_horiz('21','<?php echo $tab_21 = rand(0,999999); ?>');pokaz_dane('allegro','21','<?php echo $tab_21; ?>')" class="a_href_info_zakl" id="zakl_link_21">Dane <span></span></span>                          
                        <span onclick="gold_tabs_horiz('3')" class="a_href_info_zakl" id="zakl_link_3">Zdjęcia produktu</span>   
                        
                        <?php if ( $SaDodPola == true ) { ?>
                        <span onclick="gold_tabs_horiz('4','<?php echo $tab_4 = rand(0,999999); ?>')" class="a_href_info_zakl" id="zakl_link_4">Dodatkowe pola opisowe</span>  
                        <?php } ?>
                        
                        <?php if ( $SaDodPolaTekstowe == true ) { ?>
                        <span onclick="gold_tabs_horiz('19')" class="a_href_info_zakl" id="zakl_link_19">Dodatkowe pola tekstowe</span>  
                        <?php } ?>                        
                        
                        <?php if ( $SaCechy == true ) { ?>
                        <span onclick="gold_tabs_horiz('5')" class="a_href_info_zakl" id="zakl_link_5">Cechy produktu</span>    
                        <?php } ?>
                        
                        <span onclick="gold_tabs_horiz('6','<?php echo $tab_6 = rand(0,999999); ?>')" class="a_href_info_zakl" id="zakl_link_6">Pozycjonowanie</span>   
                        <span onclick="gold_tabs_horiz('7','<?php echo $tab_7 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_7">Dodatkowa zakładka #1</span>  
                        <span onclick="gold_tabs_horiz('8','<?php echo $tab_8 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_8">Dodatkowa zakładka #2</span> 
                        <span onclick="gold_tabs_horiz('9','<?php echo $tab_9 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_9">Dodatkowa zakładka #3</span>
                        <span onclick="gold_tabs_horiz('10','<?php echo $tab_10 = rand(0,999999); ?>','dod_zakladka_')" class="a_href_info_zakl" id="zakl_link_10">Dodatkowa zakładka #4</span>
                        <span onclick="gold_tabs_horiz('11','<?php echo $tab_11 = rand(0,999999); ?>');pokaz_dane('dod_linki','11','<?php echo $tab_11; ?>')" class="a_href_info_zakl" id="zakl_link_11">Linki</span>
                        <span onclick="gold_tabs_horiz('12','<?php echo $tab_12 = rand(0,999999); ?>');pokaz_dane('pliki','12','<?php echo $tab_12; ?>')" class="a_href_info_zakl" id="zakl_link_12">Pliki</span>
                        <span onclick="gold_tabs_horiz('20','<?php echo $tab_20 = rand(0,999999); ?>');pokaz_dane('pliki_elektroniczne','20','<?php echo $tab_20; ?>')" class="a_href_info_zakl" id="zakl_link_20">Sprzedaż elektroniczna</span>
                        <span onclick="gold_tabs_horiz('16','<?php echo $tab_16 = rand(0,999999); ?>');pokaz_dane('youtube','16','<?php echo $tab_16; ?>')" class="a_href_info_zakl" id="zakl_link_16">Filmy YouTube</span>
                        <span onclick="gold_tabs_horiz('17','<?php echo $tab_17 = rand(0,999999); ?>');pokaz_dane('filmy','17','<?php echo $tab_17; ?>')" class="a_href_info_zakl" id="zakl_link_17">Filmy FLV</span>
                        <span onclick="gold_tabs_horiz('18');pokaz_dane('mp3','18')" class="a_href_info_zakl" id="zakl_link_18">Pliki MP3</span>
                        <span onclick="gold_tabs_horiz('15','')" class="a_href_info_zakl" id="zakl_link_15">Dostępne wysyłki</span>                        
                    </td>
                    
                    <td id="prawa_strona" style="vertical-align:top">

                        <?php 
                        // Informacje ogolne
                        include('produkty_dodaj_zakl_infor_ogolne.php');
                        
                        // Kategorie
                        include('produkty_dodaj_zakl_kategorie.php');                        
                        
                        // Opis
                        include('produkty_dodaj_zakl_opis.php');
                        
                        // Opis krotki
                        include('produkty_dodaj_zakl_opis_krotki.php'); 
                        
                        // Opis allegro
                        ?>
                        <div id="zakl_id_21" style="display:none;">
                            <div id="allegro">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php                                               

                        // Zdjecia
                        include('produkty_dodaj_zakl_zdjecia.php');   

                        // Dodatkowe pola
                        if ( $SaDodPola == true ) {
                             include('produkty_dodaj_zakl_dodatkowe_pola.php');                          
                        }          

                        // Dodatkowe pola tekstowe
                        if ( $SaDodPolaTekstowe == true ) {
                             include('produkty_dodaj_zakl_dodatkowe_pola_tekstowe.php');                          
                        }                          

                        // Cechy produktu
                        if ( $SaCechy == true ) {
                             include('produkty_dodaj_zakl_cechy.php');
                        }
                        
                        // Meta tagi
                        include('produkty_dodaj_zakl_meta_tagi.php');                        
                      
                        // Dodatkowe zakladki
                        include('produkty_dodaj_zakl_dod_zakladki.php');  
                        
                        // Linki
                        ?>
                        <div id="zakl_id_11" style="display:none;">
                            <div id="dod_linki">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php
                        
                        // Pliki
                        ?>
                        <div id="zakl_id_12" style="display:none;">
                            <div id="pliki">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php 

                        // Pliki elektroniczne
                        ?>
                        <div id="zakl_id_20" style="display:none;">
                            <div id="pliki_elektroniczne">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php                         

                        // Youtube
                        ?>
                        <div id="zakl_id_16" style="display:none;">
                            <div id="youtube">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php    

                        // Filmy FLV
                        ?>
                        <div id="zakl_id_17" style="display:none;">
                            <div id="filmy">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>                        
                        <?php                    

                        // Pliki Mp3
                        ?>
                        <div id="zakl_id_18" style="display:none;">
                            <div id="mp3">
                                <span class="padAjax">Brak danych ...</span>
                            </div>
                        </div>
                        <?php

                        // Wysylki
                        include('produkty_dodaj_zakl_wysylki.php');
                      
                        ?>
                        
                        <script type="text/javascript">
                        //<![CDATA[
                        gold_tabs_horiz('0','<?php echo $tab_0; ?>');
                        //]]>
                        </script>                         
                    
                    </td>
                
                </tr></table>
            
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
                </div>            
            
            <?php 
            $db->close_query($sql);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>  
            
        </div>
        
        </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>