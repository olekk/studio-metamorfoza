<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // jezeli jest usuniecie filtra
    if ( isset($_GET['filtr']) && $_GET['filtr'] == 'nie' ) {
         //
         unset($_SESSION['filtry']['menadzer_produktow.php']);
         //
         Funkcje::PrzekierowanieURL('menadzer_produktow.php');
         //
    }

    $fraza = '';
    if ( isset($_SESSION['filtry']['menadzer_produktow.php']) && !empty($_SESSION['filtry']['menadzer_produktow.php']) ) {
        //
        $fraza = $_SESSION['filtry']['menadzer_produktow.php'];
        //
    }
    if (isset($_POST['szukaj_fraze']) && !empty($_POST['szukaj_fraze'])) {
        //
        $_SESSION['filtry']['menadzer_produktow.php'] = $filtr->process($_POST['szukaj_fraze']);
        $fraza = $filtr->process($_POST['szukaj_fraze']);
        //
        if (!get_magic_quotes_gpc()) {
            $fraza = str_replace("'", "\'",$fraza);
        }        
        //
    }

    if (isset($_POST['akcja'])) {
    
        if (isset($_POST['id_produkt']) && count($_POST['id_produkt']) > 0) {
        
            $Kategoria = (int)$_POST['aktualna_kategoria_id'];
        
            foreach ($_POST['id_produkt'] as $produkt) {
            
                switch ($_POST['akcja']) {
                
                    // przeniesienie produktu do innej kategorii
                    case 'przeniesienie':
                        //
                        if ( isset($_POST['kategoria_id']) && (int)$_POST['kategoria_id'] > 0 ) {
                            //
                            $db->delete_query('products_to_categories' , " products_id = '".$produkt."'");                              
                            //
                            $pola = array(array('products_id',$produkt),
                                          array('categories_id',(int)$_POST['kategoria_id']));
                                          
                            $sql = $db->insert_query('products_to_categories' , $pola);
                            unset($pola);
                            //
                            $Kategoria = (int)$_POST['kategoria_id'];
                            //
                        }
                        //
                        break; 
                        
                    // powiazanie produktu do inna kategoria
                    case 'powiazanie':
                        //
                        if ( isset($_POST['kategoria_id']) && (int)$_POST['kategoria_id'] > 0 ) {
                            //
                            $db->delete_query('products_to_categories' , " products_id = '".$produkt."' and categories_id = '".(int)$_POST['kategoria_id']."'");                              
                            //
                            $pola = array(array('products_id',$produkt),
                                          array('categories_id',(int)$_POST['kategoria_id']));
                                          
                            $sql = $db->insert_query('products_to_categories' , $pola);
                            unset($pola);
                            //
                            $Kategoria = (int)$_POST['kategoria_id'];
                            //                            
                        }
                        //
                        break; 

                    // usuniecie 
                    case 'usuniecie':
                        //
                        if ( isset($_POST['usuniecie_id']) ) {
                            //
                            // tylko z danej kategorii
                            if ( $_POST['usuniecie_id'] == 'kat_wybrana' && isset($_POST['aktualna_kategoria_id']) && (int)$_POST['aktualna_kategoria_id'] > 0 ) {
                                //
                                $db->delete_query('products_to_categories' , " products_id = '".$produkt."' and categories_id = '".(int)$_POST['aktualna_kategoria_id']."'");                              
                                //
                            }
                            // calkowite
                            if ( $_POST['usuniecie_id'] == 'kat_wszystkie' ) {
                                //
                                Produkty::SkasujProdukt($produkt);     
                                //
                            }
                        }
                        //
                        break; 
                        
                    // zmiana statusu
                    case 'status_zmiana':
                        //
                        if ( isset($_POST['status']) ) {
                            //
                            if ( $_POST['status'] == 'nieaktywny' ) {
                                //
                                $pola = array(array('products_status','0'));
                                $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                                unset($pola);                              
                                //
                            }
                            //
                            if ( $_POST['status'] == 'aktywny' ) {
                                //
                                $pola = array(array('products_status','1'));
                                $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                                unset($pola);                              
                                //
                            }
                            //                           
                        }
                        //
                        break;  

                    // zmiana opcji produktow - usuniecie
                    case 'atrybuty':
                        //
                        $pola = array();                 
                        //
                        if ( isset($_POST['atr_nowosc']) ) {
                            //
                            $pola[] = array('new_status','0');
                            //
                        }                        
                        //
                        if ( isset($_POST['atr_hit']) ) {
                            //
                            $pola[] = array('star_status','0');                    
                            $pola[] = array('star_date','0000-00-00');
                            $pola[] = array('star_date_end','0000-00-00');
                            //
                        }   
                        //
                        if ( isset($_POST['atr_polecany']) ) {
                            //
                            $pola[] = array('featured_status','0');                  
                            $pola[] = array('featured_date','0000-00-00');
                            $pola[] = array('featured_date_end','0000-00-00');
                            //
                        }                           
                        //
                        if ( isset($_POST['atr_negocjacja']) ) {
                            //
                            $pola[] = array('products_make_an_offer','0');                 
                            //
                        } 
                        //
                        if ( isset($_POST['atr_darmowa_wysylka']) ) {
                            //
                            $pola[] = array('free_shipping_status','0');                 
                            //
                        }  
                        //
                        if ( isset($_POST['atr_porownywarki']) ) {
                            //
                            $pola[] = array('export_status','0');                 
                            //
                        }                          
                        //
                        $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                        unset($pola);                         
                        //                  
                        break; 
                        
                    // zmiana opcji produktow - dodanie
                    case 'atrybuty_dodaj':
                        //
                        $pola = array();                 
                        //
                        if ( isset($_POST['atr_dodaj_nowosc']) ) {
                            //
                            $pola[] = array('new_status','1');
                            //
                        }                        
                        //
                        if ( isset($_POST['atr_dodaj_hit']) ) {
                            //
                            $pola[] = array('star_status','1');  
                            //
                        }   
                        //
                        if ( isset($_POST['atr_dodaj_polecany']) ) {
                            //
                            $pola[] = array('featured_status','1');                  
                            //
                        }                           
                        //
                        if ( isset($_POST['atr_dodaj_negocjacja']) ) {
                            //
                            $pola[] = array('products_make_an_offer','1');                 
                            //
                        } 
                        //
                        if ( isset($_POST['atr_dodaj_darmowa_wysylka']) ) {
                            //
                            $pola[] = array('free_shipping_status','1');                 
                            //
                        }  
                        //
                        if ( isset($_POST['atr_dodaj_porownywarki']) ) {
                            //
                            $pola[] = array('export_status','1');                 
                            //
                        }                          
                        //
                        $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                        unset($pola);                         
                        //                  
                        break;                         
                        
                    // staly koszt wysylki
                    case 'stala_wysylka':
                        //
                        if ( isset($_POST['koszt_wysylki']) ) {
                            //
                            $pola = array(array('shipping_cost',(float)$_POST['koszt_wysylki']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;   

                    // dostepne wysylki
                    case 'dostepne_wysylki':
                        //
                        if ( isset($_POST['metody_wysylki']) ) {
                            //
                            $pola = array(array('shipping_method',implode(';',$filtr->process($_POST['metody_wysylki']))));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                        
                        } else {
                            //
                            $pola = array(array('shipping_method',''));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            // 
                        }
                        //
                        break;    

                    // rodzaj produktu
                    case 'rodzaj_produktu':
                        //
                        if ( isset($_POST['rodzaj_produktu_lista']) ) {
                            //
                            $pola = array(array('products_type',$filtr->process($_POST['rodzaj_produktu_lista'])));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;      

                    // czas wysylki
                    case 'czas_wysylki':
                        //
                        if ( isset($_POST['czas_wysylki_lista']) ) {
                            //
                            $pola = array(array('products_shipping_time_id',(int)$_POST['czas_wysylki_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break; 

                    // dostepnosc
                    case 'dostepnosc':
                        //
                        if ( isset($_POST['dostepnosc_lista']) ) {
                            //
                            $pola = array(array('products_availability_id',(int)$_POST['dostepnosc_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break; 

                    // stan produktu
                    case 'stan_produktu':
                        //
                        if ( isset($_POST['stan_produktu_lista']) ) {
                            //
                            $pola = array(array('products_condition_products_id',(int)$_POST['stan_produktu_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;      

                    // gwarancja
                    case 'gwarancja':
                        //
                        if ( isset($_POST['gwarancja_lista']) ) {
                            //
                            $pola = array(array('products_warranty_products_id',(int)$_POST['gwarancja_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;  

                    // jednostka miary
                    case 'jednostka_miary':
                        //
                        if ( isset($_POST['jednostka_miary_lista']) ) {
                            //
                            $pola = array(array('products_jm_id',(int)$_POST['jednostka_miary_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;

                    // gabaryt
                    case 'gabaryt':
                        //
                        if ( isset($_POST['gabaryt_lista']) ) {
                            //
                            $pola = array(array('products_pack_type',(int)$_POST['gabaryt_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break; 

                    // kupowanie
                    case 'kupowanie':
                        //
                        if ( isset($_POST['kupowanie_lista']) ) {
                            //
                            $pola = array(array('products_buy',(int)$_POST['kupowanie_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;  

                    // kupowanie
                    case 'grupa_klientow':
                        //
                        if ( isset($_POST['grupa_klientow_lista']) ) {
                            //
                            $pola = array(array('customers_group_id',(int)$_POST['grupa_klientow_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;    

                    // producent
                    case 'producent':
                        //
                        if ( isset($_POST['producent_lista']) ) {
                            //
                            $pola = array(array('manufacturers_id',(int)$_POST['producent_lista']));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;

                    // data dostepnosci
                    case 'data_dostepnosci':
                        //
                        if ( isset($_POST['data_dostepnosci_lista']) ) {
                            //
                            $pola = array(array('products_date_available',((!empty($_POST['data_dostepnosci_lista'])) ? date('Y-m-d', strtotime($filtr->process($_POST['data_dostepnosci_lista']))) : '')));
                            $db->update_query('products' , $pola, " products_id = '".$produkt."'");
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;  

                    // dodatkowe pola w formie tekstu
                    case 'dod_pole_text':
                        //
                        if ( isset($_POST['dod_pole_text_lista']) ) {
                            //
                            $pola = array();
                            if ( !empty($_POST['dod_pole_text_wartosc']) ) {
                                 $pola[] = array('products_extra_fields_value',$filtr->process($_POST['dod_pole_text_wartosc']));
                            }
                            if ( !empty($_POST['dod_pole_text_link']) && !isset($_POST['dod_pole_text_link_usun']) ) {
                                 $pola[] = array('products_extra_fields_link',$filtr->process($_POST['dod_pole_text_link']));
                            } else if ( isset($_POST['dod_pole_text_link_usun']) ) {
                                 $pola[] = array('products_extra_fields_link','');
                            }
                            
                            if ( count($pola) > 0 ) {
                                 $db->update_query('products_to_products_extra_fields' , $pola, " products_id = '".$produkt."' and products_extra_fields_id = '" . (int)$_POST['dod_pole_text_lista'] . "'");
                            }
                            
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;

                    // dodatkowe pola w formie obrazkow
                    case 'dod_pole_obrazki':
                        //
                        if ( isset($_POST['dod_pole_obrazki_lista']) ) {
                            //
                            $pola = array();
                            if ( !empty($_POST['dod_pole_obrazki_wartosc']) ) {
                                 $pola[] = array('products_extra_fields_value',$filtr->process($_POST['dod_pole_obrazki_wartosc']));
                            }
                            if ( !empty($_POST['dod_pole_obrazki_link']) && !isset($_POST['dod_pole_obrazki_link_usun']) ) {
                                 $pola[] = array('products_extra_fields_link',$filtr->process($_POST['dod_pole_obrazki_link']));
                            } else if ( isset($_POST['dod_pole_obrazki_link_usun']) ) {
                                 $pola[] = array('products_extra_fields_link','');
                            }
                            
                            if ( count($pola) > 0 ) {
                                 $db->update_query('products_to_products_extra_fields' , $pola, " products_id = '".$produkt."' and products_extra_fields_id = '" . (int)$_POST['dod_pole_obrazki_lista'] . "'");
                            }
                            
                            unset($pola);                              
                            //                           
                        }
                        //
                        break;                        

                }

            }
            
            if ( $Kategoria > 0 ) {
                //
                Funkcje::PrzekierowanieURL('menadzer_produktow.php?id=' . $Kategoria);
                //
              } else {
                //
                Funkcje::PrzekierowanieURL('menadzer_produktow.php');
                //
            }
            
        }
    
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');     

    ?>

    <div id="naglowek_cont">Menadżer produktów </div>
    <div id="cont">
    
          <script type="text/javascript">
          //<![CDATA[    
          function produkty_menadzera(id) {
              //
              $('#listaPrd').html('<div style="padding:8px"><img src="obrazki/_loader_small.gif"></div>');
              $.get("ajax/lista_produktow_menadzer.php",
                  { id: id, tok: $('#tok').val(), fraza: '<?php echo $fraza; ?>' },
                  function(data) { 
                      $('#listaPrd').hide();
                      $('#listaPrd').html(data);             
                      $('#listaPrd').fadeIn('fast');
                      pokazChmurki();
                      //
                      $('input.datepicker').Zebra_DatePicker({
                        format: 'd-m-Y',
                        inside: false,
                        readonly_element: false
                      });                          
                      //
              }); 
          }   
          //
          // zaznacza checkboxy
          function zaznaczPrd(akcja) {
              if (akcja == 1) {
                  $(".tblProdukty").find("input[name='id_produkt[]']").prop("checked",false);
              }
              // zaznacza wszystkie checkboxy
              if (akcja == 0) {
                  $(".tblProdukty").find("input[name='id_produkt[]']").prop("checked",true);
              }        
          }    
          function zmien_akcje(id) {
              $('.akcja').find("input[name='akcja']").prop("checked",false);
              $('.akcja').hide(); 
              $('#ButZapis').hide();
              //
              if ( id != '0' ) {
                  //
                  var podzial = id.split('-');
                  if ( podzial.length > 1 ) {
                       $('#span_1').hide();
                       $('#span_2').hide();
                       $('#span_' + podzial[1]).show();
                       $('#span_' + podzial[1] + ' input:first-child').prop("checked",true);
                       $('#akcja_' + podzial[0]).fadeIn();
                       $('#ButZapis').fadeIn(); 
                     } else {
                       $('#akcja_' + id + " input[type=radio]:first-child").prop("checked",true);
                       $('#akcja_' + id).fadeIn();
                       $('#ButZapis').fadeIn(); 
                  }
                  //  
              }
          } 
          //]]>
          </script>          
    
          <div class="poleForm">
            <div class="naglowek">Przeglądarka kategorii i produktów</div>
            
            <div id="listaPrd"></div>

          </div>
          
          <script type="text/javascript">
          //<![CDATA[              
          produkty_menadzera(<?php echo ((isset($_GET['id']) && (int)$_GET['id'] > 0) ? (int)$_GET['id'] : 0); ?>);
          //]]>
          </script>
          
    </div>
    
    <?php
    unset($fraza);
    
    include('stopka.inc.php');    
    
} ?>