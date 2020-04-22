<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $JestZamowienie = false;

    if ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) {
      $zamowienie = new Zamowienie((int)$_GET['id_poz']);
      $JestZamowienie = true;
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $kombinacja_cech      = '';

        if ( isset($_POST['cecha']) && count($_POST['cecha']) > 0 ) {

          foreach ( $_POST['cecha'] as $key ) {
            $tablica_wartosc_cechy = explode( ';', $key );
            $prefix = $_POST['cecha_prefix'][$tablica_wartosc_cechy['1']];
            $cena_cechy_netto = $_POST['cecha_cena_netto'][$tablica_wartosc_cechy['1']];
            $cena_cechy_brutto = $_POST['cecha_cena_brutto'][$tablica_wartosc_cechy['1']];
            $kombinacja_cech[ $tablica_wartosc_cechy['1'] ] = $tablica_wartosc_cechy['1'].'-'.$tablica_wartosc_cechy['0'];

            $zapytanie_wartosc_cechy = "SELECT * FROM products_options_values
                                            WHERE products_options_values_id = '" . (int)$tablica_wartosc_cechy['0']. "' 
                                            AND language_id =  '1'";
                        
            $sql_wartosc_cechy = $db->open_query($zapytanie_wartosc_cechy);

            if ((int)$db->ile_rekordow($sql_wartosc_cechy) > 0) {
              $info_wartosc_cechy = $sql_wartosc_cechy->fetch_assoc();
              $nazwa_wartosci_cechy = $info_wartosc_cechy['products_options_values_name'];
            }

            $pola = array(
                    array('products_options_values',$nazwa_wartosci_cechy),
                    array('products_options_values_id',$tablica_wartosc_cechy['0']),
                    array('options_values_price',$cena_cechy_netto),
                    array('options_values_tax',($cena_cechy_brutto-$cena_cechy_netto)),
                    array('options_values_price_tax',$cena_cechy_brutto),
                    array('price_prefix',$prefix)
            );

            $db->update_query('orders_products_attributes' , $pola, " orders_id = '".(int)$_POST["id"]."' AND orders_products_id = '".(int)$_POST["id_produktu"]."' AND products_options_id = '".(int)$tablica_wartosc_cechy['1']."'");	
            unset($pola);

          }
        }
        
        ksort($kombinacja_cech);
        $kombinacja_cech = implode(',', $kombinacja_cech);
        
        // szuka czy dana kombinacja cech nie ma unikalnego nr katalogowego
        $nr_katalogowy_cechy = $filtr->process($_POST["model"]);
        $zapytanie_cechy = "SELECT products_stock_model FROM products_stock WHERE products_stock_attributes = '" . $kombinacja_cech . "' and products_id = '" . $filtr->process($_POST["id_produktu_org"]) . "'";
        $sql_nr_kat_cechy = $db->open_query($zapytanie_cechy);
        //
        if ((int)$db->ile_rekordow($sql_nr_kat_cechy) > 0) {
          $info_nr_kat_cechy = $sql_nr_kat_cechy->fetch_assoc();
          //
          if (!empty($info_nr_kat_cechy['products_stock_model'])) {
              $nr_katalogowy_cechy = $info_nr_kat_cechy['products_stock_model'];
          }
          //
          unset($info_nr_kat_cechy);
        }   
        //
        $db->close_query($sql_nr_kat_cechy);      
        //       

        // dodatkowe pola opisowe
        // sprawdzi czy wogole sa 
        $ciagTxt ='';
        //
        if ( isset($_POST['pole_txt_nazwa_1']) ) {
            //
            for ( $p = 1; $p < 50; $p++ ) {
                //
                $ciagTxt .= '{#{';
                //            
                if ( isset($_POST['pole_txt_nazwa_' . $p]) ) {
                    //
                    if ( trim($_POST['pole_txt_wartosc_' . $p]) != '' ) {
                         $ciagTxt .= $filtr->process($_POST['pole_txt_nazwa_' . $p]) . '|*|' . $filtr->process($_POST['pole_txt_wartosc_' . $p]);
                         //
                         if ( isset($_POST['plik_txt_' . $p]) ) {
                            $ciagTxt .= '|*|plik';
                          } else {
                            $ciagTxt .= '|*|txt';
                         }
                         //
                    }
                    //
                }
                //
                $ciagTxt .= '}#}';
                //
            }
            //
        }
        //

        $pola = array(
                array('products_name',$filtr->process($_POST["nazwa"])),
                array('products_model',$nr_katalogowy_cechy),
                array('products_pkwiu',$filtr->process($_POST["pkwiu"])),
                array('products_quantity',$filtr->process($_POST["ilosc"])),
                array('products_price',(($_POST['ma_cechy'] == 'tak') ? $filtr->process($_POST["cena_1_podstawa"]) : $filtr->process($_POST["cena_1"]))),
                array('products_price_tax',(($_POST['ma_cechy'] == 'tak') ? $filtr->process($_POST["brut_1_podstawa"]) : $filtr->process($_POST["brut_1"]))),
                array('final_price',$filtr->process($_POST["cena_1"])),
                array('final_price_tax',$filtr->process($_POST["brut_1"])),
                array('products_comments',$filtr->process($_POST["komentarz"])),
                array('products_text_fields',$ciagTxt),
                array('products_stock_attributes',$kombinacja_cech)
        );
        
        $stawka_vat = explode('|', $filtr->process($_POST['vat']));
        $pola[] = array('products_tax',$stawka_vat[0]);
        $pola[] = array('products_tax_class_id',$stawka_vat[1]);   
        unset($stawka_vat);        
        
        //			
        $db->update_query('orders_products' , $pola, " orders_products_id = '".(int)$_POST["id_produktu"]."'");	
        unset($pola);

        // aktualizacja ilosci sprzedanych produktow
        if ( $_POST["ilosc_org"] != $_POST["ilosc"] ) {

            $Ilosc = $_POST["ilosc_org"] - $_POST["ilosc"];

            $zapytanie_sprzedane = "SELECT products_ordered FROM products WHERE products_id = '".(int)$_POST['id_produktu_org']."'";
            $sql_sprzedane = $db->open_query($zapytanie_sprzedane);
            $sprzedane = $sql_sprzedane->fetch_assoc();

            if ( $Ilosc > 0 ) {
                $sprzedane_akt = $sprzedane['products_ordered'] - $Ilosc;
            } else {
                $Ilosc = abs($Ilosc);
                $sprzedane_akt = $sprzedane['products_ordered'] + $Ilosc;
            }

            $pola = array(
                    array('products_ordered',$sprzedane_akt));
                    
            $db->update_query('products' , $pola, "products_id = '" . (int)$_POST['id_produktu_org'] . "'");

            $db->close_query($sql_sprzedane);         
            unset($zapytanie_sprzedane, $sprzedane, $pola, $sprzedane_akt);

        }

        Sprzedaz::PodsumowanieZamowieniaAktualizuj($_POST["id"], $_POST["waluta"]);

        //
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]).'');
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
        $("#zamowieniaForm").validate({
          rules: {
            nazwa: {
              required: true
            },
            ilosc: {
              required: true,
              range: [0, 999999]
            },
            cena_1: {
              required: true,
              range: [0, 999999]
            }
          }
        });
      });
      //]]>
      </script>        

      <?php
      if ( !isset($_GET['produkt_id']) ) {
           $_GET['produkt_id'] = 0;
      }     
      if ( !isset($_GET['zakladka']) ) {
           $_GET['zakladka'] = '0';
      }        
      
      if ( $JestZamowienie == true && isset($zamowienie->produkty[(int)$_GET['produkt_id']]) && count($zamowienie->produkty[(int)$_GET['produkt_id']]) > 0 ) {
      ?>
        
        <form action="sprzedaz/zamowienia_produkt_edytuj.php" method="post" id="zamowieniaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja produktu</div>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    <input type="hidden" name="id_produktu" value="<?php echo $filtr->process($_GET['produkt_id']); ?>" />
                    <input type="hidden" name="zakladka" value="<?php echo $filtr->process((int)$_GET['zakladka']); ?>" />
                    <input type="hidden" name="id_produktu_org" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['id_produktu']; ?>" />
                    <input type="hidden" name="waluta" value="<?php echo $zamowienie->info['waluta']; ?>" />
                    <input type="hidden" name="ilosc_org" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['ilosc']; ?>" />

                    <p>
                      <label class="required">Nazwa produktu:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['nazwa']; ?>" size="53" />
                    </p>   

                    <p>
                      <label>Model:</label>
                      <input type="text" name="model" id="model" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['model']; ?>" size="53" />
                    </p>   

                    <p>
                      <label>Symbol PKWiU:</label>
                      <input type="text" name="pkwiu" id="pkwiu" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['pkwiu']; ?>" size="53" />
                    </p>   

                    <p>
                      <label class="required">Ilość:</label>
                      <input type="text" name="ilosc" id="ilosc" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['ilosc']; ?>" size="20" />
                    </p>   

                    <?php
                    $vat = Produkty::TablicaStawekVat('', true, true);
                    $domyslny_vat = $vat[1];

                    foreach ( $vat[0] as $poz_vat ) {
                        //
                        $tb_tmp = explode('|', $poz_vat['id']);
                        if ( $tb_tmp[1] == $zamowienie->produkty[$_GET['produkt_id']]['tax_id'] ) {
                             $domyslny_vat = $poz_vat['id'];
                        }
                        //
                    }
                    //
                    unset($poz_vat);                         
                    ?>
                    <p>
                      <label class="required">Stawka VAT:</label>
                      <?php echo Funkcje::RozwijaneMenu('vat', $vat[0], $domyslny_vat, ' id="vat"'); ?>
                    </p>   
                    
                    <?php
                    unset($vat, $domyslny_vat);                    

                    $ProduktMaCechy = false;
                    if ( isset($zamowienie->produkty[$_GET['produkt_id']]['attributes']) && count($zamowienie->produkty[$_GET['produkt_id']]['attributes']) > 0 ) {
                         $ProduktMaCechy = true;
                    }
                    ?>
                    
                    <div>
                      <input name="ma_cechy" value="<?php echo (($ProduktMaCechy == true) ? 'tak' : 'nie'); ?>" type="hidden" />
                    </div>
                    
                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                    <?php
                    if ( $ProduktMaCechy == true ) {
                    ?>
                    
                    <p>
                      <label class="required">Cena netto produktu bez cech:</label>
                      <input class="oblicz" type="text" name="cena_1_podstawa" id="cena_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_netto']; ?>" size="20" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p> 

                    <p>
                      <label class="required">Cena brutto produktu bez cech:</label>
                      <input class="oblicz_brutto" type="text" name="brut_1_podstawa" id="brut_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_brutto']; ?>" size="20" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p> 

                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                    <?php } else { ?>
                    
                    <div>
                      <input type="hidden" name="cena_1_podstawa" id="cena_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_netto']; ?>" />
                      <input type="hidden" name="brut_1_podstawa" id="brut_1_podstawa" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_brutto']; ?>" />
                    </div>
                    
                    <?php } ?>

                    <p>
                      <label class="required">Cena netto produktu <?php echo (($ProduktMaCechy == true) ? 'z cechami' : ''); ?>:</label>
                      <input class="oblicz" type="text" name="cena_1" id="cena_1" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_netto']; ?>" size="20" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p>   
                    <?php $podatek_vat  = $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_brutto'] - $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_netto']; ?>
                    <p>
                      <label class="required">Podatek VAT produktu:</label>
                      <input type="text" name="v_at_1" id="v_at_1" value="<?php echo $podatek_vat; ?>" size="20" />
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p>   

                    <p>
                      <label class="required">Cena brutto produktu <?php echo (($ProduktMaCechy == true) ? 'z cechami' : ''); ?>:</label>
                      <input type="text" class="oblicz_brutto" name="brut_1" id="brut_1" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['cena_koncowa_brutto']; ?>" size="20" />
                      
                      <?php echo $waluty->waluty[$zamowienie->info['waluta']]['symbol']; ?>
                    </p> 
                    
                    <?php
                    if (!empty($zamowienie->produkty[$_GET['produkt_id']]['pola_txt'])) {
                      //
                      $PoleTxt = Funkcje::serialCiag($zamowienie->produkty[$_GET['produkt_id']]['pola_txt']);
                      if ( count($PoleTxt) > 0 ) {
                          $nrPola = 1;
                          foreach ( $PoleTxt as $WartoscTxt ) {
                              //
                              ?>
                              <p>
                                <label><?php echo $WartoscTxt['nazwa']; ?>:
                                <?php
                                echo '<input type="hidden" value="' . $WartoscTxt['nazwa'] . '" name="pole_txt_nazwa_' . $nrPola . '" />';
                                if ($WartoscTxt['typ'] == 'plik') {
                                    echo '<input type="checkbox" name="plik_txt_' . $nrPola . '" value="1" checked="checked" /> plik';
                                }
                                ?>
                                </label>
                                <textarea name="pole_txt_wartosc_<?php echo $nrPola; ?>" cols="60" rows="2"><?php echo $WartoscTxt['tekst']; ?></textarea>
                              </p>                              
                              <?php
                              //    
                              $nrPola++;
                          }
                      }
                      unset($PoleTxt);
                      //
                    }                    
                    ?>

                    <p>
                      <label>Komentarz:</label>
                      <textarea name="komentarz" cols="60" rows="3"><?php echo $zamowienie->produkty[$_GET['produkt_id']]['komentarz']; ?></textarea>
                    </p>    

                    <?php

                    $wartosc_cech_produktu = 0;
                    $wartosc_cech_produktu_tax = 0;
                    $i = 0;

                    if ( $ProduktMaCechy == true ) {

                      echo '<hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />';

                      $cechy = '';

                      foreach ( $zamowienie->produkty[$_GET['produkt_id']]['attributes'] as $cecha ) {

                        $cecha_biezaca = $cecha['id_cechy'].'-'.$cecha['id_wartosci'];
                        $cechy .= $cecha_biezaca .',';
                        echo '<p>';
                        echo '<label>'.$cecha['cecha'].':</label>';
                        $tablica = Funkcje::lista_wartosci_cechy_produktu($zamowienie->produkty[$_GET['produkt_id']]['id_produktu'], $cecha['id_cechy'], $zamowienie->produkty[$_GET['produkt_id']]['id_waluty']);

                        echo Sprzedaz::RozwijaneMenuCechy('cecha['.$cecha['id_cechy'].']', $tablica, $cecha['id_wartosci'], 'style="width:250px;" id="cecha_'.$cecha['id_cechy'].'" onchange="wyswietlCechy('.$cecha['id_cechy'].');" ','','', $zamowienie->produkty[$_GET['produkt_id']]['id_waluty'], $zamowienie->info['waluta'] );

                        echo '&nbsp;<input type="text" name="cecha_prefix['.$cecha['id_cechy'].']" value="'.$cecha['prefix'].'" id="cecha_prefix_'.$cecha['id_cechy'].'" size="1" onchange="sumaCech()" />&nbsp;';
                        echo '<input class="oblicz" type="text" name="cecha_cena_netto['.$cecha['id_cechy'].']" value="'.$cecha['cena_netto'].'" id="cecha_cena_'.$cecha['id_cechy'].'" />&nbsp;';
                        echo '<input class="oblicz_brutto" type="text" name="cecha_cena_brutto['.$cecha['id_cechy'].']" value="'.$cecha['cena_brutto'].'" id="cecha_brut_'.$cecha['id_cechy'].'" />';
                        echo '</p>';

                        $i++;
                      }
                      $cechy = substr($cechy, 0 ,-1);
                    }
                    
                    unset($ProduktMaCechy);

                    ?>
                    <input type="hidden" name="ilosc_pierwotna" value="<?php echo $zamowienie->produkty[$_GET['produkt_id']]['ilosc']; ?>" />

                    </div>
                 
                </div>
                
                <script type="text/javascript">
                //<![CDATA[            
                sumaCech();
                //]]>
                </script>                 
                
                <div class="cechy_info" style="padding:20px;">
                  <div class="ostrzezenie"> Po zmianie ilości produktów pamiętaj o zaktualizowaniu stanów magazynowych.</div>
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
                </div>

          </div>                      
        </form>

        <?php

      } else {

        ?>
        
        <div class="poleForm"><div class="naglowek">Edycja produktu</div>
            <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
        </div>
        
        <?php
        
      }

      ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}