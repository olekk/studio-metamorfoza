<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('coupons_description',$filtr->process($_POST["opis"])),
                array('coupons_discount_type',$filtr->process($_POST["rodzaj"])),                
                array('coupons_min_order',$filtr->process($_POST["wartosc"])),
                array('coupons_min_quantity',$filtr->process($_POST["ilosc"])),
                array('coupons_specials',$filtr->process($_POST["promocja"])),
                array('coupons_quantity',$filtr->process($_POST["ilosc_kuponow"])),
                array('coupons_customers_groups_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0))
        );
        
        if ($filtr->process($_POST["rodzaj"]) == 'fixed') {
            $pola[] = array('coupons_discount_value',$filtr->process($_POST["rabat_kwota"]));
        }
        
        if ($filtr->process($_POST["rodzaj"]) == 'percent') {
            $pola[] = array('coupons_discount_value',$filtr->process($_POST["rabat_procent"]));
        }        
        
        if (!empty($_POST['data_od'])) {
            $pola[] = array('coupons_date_start',date('Y-m-d', strtotime($filtr->process($_POST['data_od']))));
          } else {
            $pola[] = array('coupons_date_start','0000-00-00');            
        }  

        if (!empty($_POST['data_do'])) {
            $pola[] = array('coupons_date_end',date('Y-m-d', strtotime($filtr->process($_POST['data_do']))));
          } else {
            $pola[] = array('coupons_date_end','0000-00-00');            
        }  
        //			
        $db->update_query('coupons' , $pola, " coupons_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        
        if ( $filtr->process($_POST['rodzaj']) == 'fixed' ) {
             $_POST['rodzaj'] = 'kwota';
           } else {
             $_POST['rodzaj'] = 'procent';
        }
        if ( isset($_GET['rodzaj_opcja']) && ( $_GET['rodzaj_opcja'] != $filtr->process($_POST['rodzaj']) ) ) {
             unset($_GET);
        }    
        
        Funkcje::PrzekierowanieURL('kupony.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#kuponyForm").validate({
              rules: {
                rabat_kwota: {
                  range: [0.01, 100000],
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_kwota").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  }                
                },
                rabat_procent: {
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_procent").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  } 
                },
                ilosc: {
                  range: [0, 100000],
                  number: true
                },
                wartosc: {
                  range: [1, 100000],
                  number: true
                },
                ilosc_kuponow: {
                  range: [1, 100000],
                  number: true,
                  required: true
                }                
              },
              messages: {
                rabat_kwota: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 0.01"
                },
                rabat_procent: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 0.01"
                },              
                ilosc: {
                  range: "Wartość musi być wieksza lub równa 1"
                },
                wartosc: {
                  range: "Wartość musi być wieksza lub równa 1"
                },
                ilosc_kuponow: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 1"
                }                 
              }
            });
            
            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               direction: 1,
               inside: false,
               readonly_element: false
            });             
            
          });
          
          function rodzaj_rabat(elem) {
             $('#rodzaj_kwota').css('display','none');
             $('#rodzaj_procent').css('display','none');
             //
             if (elem != '') {
                $('#rodzaj_' + elem).slideDown();
             }
          }             
          //]]>
          </script>      

          <form action="kupony/kupony_edytuj.php" method="post" id="kuponyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from coupons where coupons_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <p>
                      <label>Kod kuponu:</label>
                      <input type="text" name="kod" id="kod" value="<?php echo $info['coupons_name']; ?>" size="35" disabled="disabled" />
                    </p>   

                    <p>
                      <label>Opis kuponu:</label>
                      <input class="toolTipText" type="text" name="opis" value="<?php echo $info['coupons_description']; ?>" size="50" title="Opis kuponu - widoczny tylko dla administratora sklepu" />
                    </p>
                    
                    <?php if ( !empty($info['coupons_email']) ) { ?>
                    
                    <p>
                      <label>Wysłany na mail:</label>
                      <strong class="adEmail"><?php echo $info['coupons_email']; ?></strong>
                    </p>                    
                    
                    <?php } ?>
                    
                    <p>
                      <label>Rodzaj rabatu:</label>
                      <input type="radio" value="fixed" name="rodzaj" onclick="rodzaj_rabat('kwota')" class="toolTipTop" title="Rabat jest stały kwotowy" <?php echo (($info['coupons_discount_type'] == 'fixed') ? 'checked="checked"' : ''); ?> /> kwotowy
                      <input type="radio" value="percent" name="rodzaj" onclick="rodzaj_rabat('procent')" class="toolTipTop" title="Rabat obliczany jest procentowo od wartości zamówienia" <?php echo (($info['coupons_discount_type'] == 'percent') ? 'checked="checked"' : ''); ?> /> procentowy
                    </p>
                    
                    <div id="rodzaj_kwota" <?php echo (($info['coupons_discount_type'] == 'fixed') ? 'style="display:block"' : 'style="display:none"'); ?>>
                      <p>
                          <label class="required">Wartość rabatu:</label>
                          <input type="text" name="rabat_kwota" id="rabat_kwota" value="<?php echo $info['coupons_discount_value']; ?>" size="10" class="toolTip" title="Wartość kwotowa powyżej 0.01" />
                      </p>
                    </div>
                    
                    <div id="rodzaj_procent" <?php echo (($info['coupons_discount_type'] == 'percent') ? 'style="display:block"' : 'style="display:none"'); ?>>
                      <p>
                          <label class="required">Wartość rabatu (w %):</label>
                          <input type="text" name="rabat_procent" id="rabat_procent" value="<?php echo $info['coupons_discount_value']; ?>" size="3" class="toolTip" title="Wartość procentowa od 0.01 do 100%" />
                      </p>
                    </div>
                    
                    <p>
                        <label>Data rozpoczęcia:</label>
                        <input type="text" name="data_od" value="<?php echo ((Funkcje::czyNiePuste($info['coupons_date_start'])) ? date('d-m-Y',strtotime($info['coupons_date_start'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>

                    <p>
                        <label>Data zakończenia:</label>
                        <input type="text" name="data_do" value="<?php echo ((Funkcje::czyNiePuste($info['coupons_date_end'])) ? date('d-m-Y',strtotime($info['coupons_date_end'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>
                    
                    <div class="ramkaWarunki">
                    
                        <b>Dodatkowe warunki użycia kuponu</b>    

                        <table style="margin-bottom:10px">
                            <tr>
                                <td><label>Dostępny dla grupy klientów:</label></td>
                                <td>
                                    <?php                        
                                    $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                    foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                        echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" ' . ((in_array($GrupaKlienta['id'], explode(',', $info['coupons_customers_groups_id']))) ? 'checked="checked" ' : '') . '/> ' . $GrupaKlienta['text'] . '<br />';
                                    }               
                                    unset($TablicaGrupKlientow);
                                    ?>
                                </td>
                            </tr>
                        </table> 
                        
                        <div class="ostrzezenie" style="margin:0px 15px 10px 0px">Jeżeli nie zostanie wybrana żadna grupa klientów to kupon będzie dostępny dla wszystkich klientów.</div>

                        <p>
                          <label>Minimalna ilość produktów:</label>
                          <input class="toolTip kropkaPusta" type="text" name="ilosc" id="ilosc" value="<?php echo (($info['coupons_min_quantity'] == 0) ? '' : $info['coupons_min_quantity']); ?>" size="3" title="Ilość produktów w koszyku od jakiej będzie można zrealizować kupon" />
                        </p> 

                        <p>
                          <label>Minimalna wartość zamówienia:</label>
                          <input class="toolTip kropkaPusta" type="text" name="wartosc" id="wartosc" value="<?php echo (($info['coupons_min_order'] == 0) ? '' : $info['coupons_min_order']); ?>" size="10" title="Wartość zamówienia od jakiej będzie można zrealizować kupon" />
                        </p>  
                        
                        <p>
                          <label>Produkty promocyjne:</label>
                          <input type="radio" value="1" name="promocja" class="toolTipTop" title="Czy kuponem mają być objęte produkty promocyjne ?" <?php echo (($info['coupons_specials'] == '1') ? 'checked="checked"' : ''); ?> /> tak
                          <input type="radio" value="0" name="promocja" class="toolTipTop" title="Czy kuponem mają być objęte produkty promocyjne ?" <?php echo (($info['coupons_specials'] == '0') ? 'checked="checked"' : ''); ?>/> nie
                        </p>   

                        <p>
                          <label>Dostępny tylko dla:</label>
                          <?php
                          $warunki = '<strong>brak</strong>';
                          if ( $info['coupons_exclusion'] == 'kategorie' && $info['coupons_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych kategorii</strong>';
                               //
                               $warunki .= '<span id="listaWarunkow">';
                               //
                               $kategoria_nazwa = $db->open_query("select distinct categories_id, categories_name from categories_description where categories_id in (".$info['coupons_exclusion_id'].") and language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
                               while ($nazwa = $kategoria_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['categories_name'] . '<br />';                               
                               }
                               $db->close_query($kategoria_nazwa);    
                               unset($kategoria_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //
                          }
                          if ( $info['coupons_exclusion'] == 'producenci' && $info['coupons_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych producentów</strong>';
                               //
                               $warunki .= '<span id="listaWarunkow">';
                               //
                               $producent_nazwa = $db->open_query("select distinct manufacturers_name from manufacturers where manufacturers_id in (".$info['coupons_exclusion_id'].")");
                               while ($nazwa = $producent_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['manufacturers_name'] . '<br />';                               
                               }
                               $db->close_query($producent_nazwa);    
                               unset($producent_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //                               
                          }  
                          if ( $info['coupons_exclusion'] == 'produkty' && $info['coupons_exclusion_id'] != '' ) {
                               $warunki = '<strong>wybranych produktów</strong>';
                               //
                               $warunki .= '<span id="listaWarunkow">';
                               //
                               $produkt_nazwa = $db->open_query("select distinct products_id, products_name from products_description where products_id in (".$info['coupons_exclusion_id'].") and language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
                               while ($nazwa = $produkt_nazwa->fetch_assoc()) {
                                      $warunki .= '&raquo ' . $nazwa['products_name'] . '<br />';                               
                               }
                               $db->close_query($produkt_nazwa);    
                               unset($produkt_nazwa, $nazwa);                               
                               //
                               $warunki .= '</span>';
                               //                               
                          }    
                          echo $warunki;
                          unset($warunki);
                          ?>
                        </p>                         

                    </div>
                    
                    <p>
                      <label class="required">Ilość dostępnych kuponów:</label>
                      <input class="toolTipText" type="text" name="ilosc_kuponow" id="ilosc_kuponow" value="<?php echo $info['coupons_quantity']; ?>" size="6" title="Wartość określa ile kuponów może zostać wykorzystanych w sklepie" />
                    </p>   

                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}