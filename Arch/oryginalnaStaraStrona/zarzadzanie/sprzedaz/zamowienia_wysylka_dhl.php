<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'DHL';
    $apiKurier = new DhlApi();
    $komunikat = '';
    $blad = true;

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $DoZapisaniaXML = '';
      $plik           = '../xml/DHL/zam_'.$_POST['id'].'_'.time().'.xml';
      $ilosc_paczek   = 0;

      $DoZapisaniaXML  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
      $DoZapisaniaXML .= "<LIST>\n";

      $DoZapisaniaXML .= "<RECEIVER_ID>".$_POST['klient_id']."</RECEIVER_ID>\n";
      $DoZapisaniaXML .= "<RECEIVER_NAME>".$_POST['adresat']."</RECEIVER_NAME>\n";
      $DoZapisaniaXML .= "<RECEIVER_POSTCODE>".$_POST['adresat_kod_pocztowy']."</RECEIVER_POSTCODE>\n";
      $DoZapisaniaXML .= "<RECEIVER_CITY>".$_POST['adresat_miasto']."</RECEIVER_CITY>\n";
      $DoZapisaniaXML .= "<RECEIVER_STREET>".$_POST['adresat_ulica']."</RECEIVER_STREET>\n";
      $DoZapisaniaXML .= "<RECEIVER_HOUSENUMBER>".$_POST['adresat_dom']."</RECEIVER_HOUSENUMBER>\n";
      $DoZapisaniaXML .= "<RECEIVER_TEL>".$_POST['adresat_telefon']."</RECEIVER_TEL>\n";
      $DoZapisaniaXML .= "<PRE_REC_EMAIL>".$_POST['adresat_mail']."</PRE_REC_EMAIL>\n";

      if ( Klienci::CzyNumerGSM($_POST['adresat_telefon']) ) {
        $DoZapisaniaXML .= "<PRE_REC_SMS>".$_POST['adresat_telefon']."</PRE_REC_SMS>\n";
      }

      if ( $apiKurier->polaczenie['INTEGRACJA_DHL_PRE_SEN_TEL'] != '' ) {
        $DoZapisaniaXML .= "<SENDER_TEL>".$apiKurier->polaczenie['INTEGRACJA_DHL_PRE_SEN_TEL']."</SENDER_TEL>\n";
      }
      if ( $apiKurier->polaczenie['INTEGRACJA_DHL_PRE_SEN_SMS'] != '' ) {
        $DoZapisaniaXML .= "<PRE_SEN_SMS>".$apiKurier->polaczenie['INTEGRACJA_DHL_PRE_SEN_SMS']."</PRE_SEN_SMS>\n";
      }
      if ( $apiKurier->polaczenie['INTEGRACJA_DHL_PRE_SEN_EMAIL'] != '' ) {
        $DoZapisaniaXML .= "<PRE_SEN_EMAIL>".$apiKurier->polaczenie['INTEGRACJA_DHL_PRE_SEN_EMAIL']."</PRE_SEN_EMAIL>\n";
      }

      $DoZapisaniaXML .= "<PRODUCT>".$_POST['product']."</PRODUCT>\n";
      $DoZapisaniaXML .= "<INVOICE_TO>".$_POST['invoice_to']."</INVOICE_TO>\n";
      $DoZapisaniaXML .= "<PAYMENT_TYPE>".($_POST['invoice_to'] == 'N' ? 'P' : 'G' )."</PAYMENT_TYPE>\n";

      if ( $_POST['DOCUMENT'] > 0 ) {
        $DoZapisaniaXML .= "<DOCUMENT>".$_POST['DOCUMENT']."</DOCUMENT>\n";
        $ilosc_paczek    = $ilosc_paczek + $_POST['DOCUMENT'];
        $blad            = false;
      }
      if ( $_POST['CATEGORY1'] > 0 ) {
        $DoZapisaniaXML .= "<CATEGORY1>".$_POST['CATEGORY1']."</CATEGORY1>\n";
        $ilosc_paczek    = $ilosc_paczek + $_POST['CATEGORY1'];
        $blad            = false;
      }
      if ( $_POST['CATEGORY2'] > 0 ) {
        $DoZapisaniaXML .= "<CATEGORY2>".$_POST['CATEGORY2']."</CATEGORY2>\n";
        $ilosc_paczek    = $ilosc_paczek + $_POST['CATEGORY2'];
        $blad            = false;
      }
      if ( $_POST['CATEGORY3'] > 0 ) {
        $DoZapisaniaXML .= "<CATEGORY3>".$_POST['CATEGORY3']."</CATEGORY3>\n";
        $ilosc_paczek    = $ilosc_paczek + $_POST['CATEGORY3'];
        $blad            = false;
      }
      if ( $_POST['CATEGORY4'] > 0 ) {
        $DoZapisaniaXML .= "<CATEGORY4>".$_POST['CATEGORY4']."</CATEGORY4>\n";
        $ilosc_paczek    = $ilosc_paczek + $_POST['CATEGORY4'];
        $blad            = false;
      }
      if ( $_POST['NON_STANDARD_TO_31KG'] > 0 ) {
        $DoZapisaniaXML .= "<NON_STANDARD_TO_31KG>".$_POST['NON_STANDARD_TO_31KG']."</NON_STANDARD_TO_31KG>\n";
        $ilosc_paczek    = $ilosc_paczek + $_POST['NON_STANDARD_TO_31KG'];
        $blad            = false;
      }
      if ( $_POST['TOTAL_OVER_31KG'] > 0 ) {
        $DoZapisaniaXML .= "<TOTAL_OVER_31KG>".$_POST['TOTAL_OVER_31KG']."</TOTAL_OVER_31KG>\n";
        $ilosc_paczek    = $ilosc_paczek + $_POST['TOTAL_OVER_31KG'];
        $blad            = false;
      }

      $DoZapisaniaXML .= "<RETURN_ON_DELIVERY>".$_POST['return_on_delivery']."</RETURN_ON_DELIVERY>\n";

      if ( isset($_POST['pobranie']) && $_POST['cash_on_delivery'] > 0 ) {
        $DoZapisaniaXML .= "<CASH_ON_DELIVERY>".str_replace('.', ',', $_POST['cash_on_delivery'])."</CASH_ON_DELIVERY>\n";
      }

      if ( $_POST['goods_value'] != '' && $_POST['goods_value'] > 0 ) {
        $DoZapisaniaXML .= "<GOODS_VALUE>".round($_POST['goods_value'],0)."</GOODS_VALUE>\n";
      }

      if ( $_POST['comment'] != '' ) {
        $DoZapisaniaXML .= "<COMMENT>".$_POST['comment']."</COMMENT>\n";
      }
      if ( $_POST['content'] != '' ) {
        $DoZapisaniaXML .= "<CONTENT>".$_POST['content']."</CONTENT>\n";
      }

      $DoZapisaniaXML .= "<BLP>".$_POST['blp']."</BLP>\n";
      $DoZapisaniaXML .= "<Tour_ID>".$_POST['kurier_id']."</Tour_ID>\n";

      $DoZapisaniaXML .= "</LIST>\n";

      if ( $blad == false ) {
        // uchwyt pliku, otwarcie do dopisania
        $fp = fopen($plik, "w+");
        // blokada pliku do zapisu
        flock($fp, 2);
        fwrite($fp, $DoZapisaniaXML);
        // zapisanie danych do pliku
        flock($fp, 3);
        // zamkniecie pliku
        fclose($fp);

        $pola = array(
                array('orders_id',$filtr->process($_POST["id"])),
                array('orders_shipping_type',$api),
                array('orders_shipping_number',str_replace('../xml/DHL/', '', $plik)),
                array('orders_shipping_weight',''),
                array('orders_parcels_quantity',$ilosc_paczek),
                array('orders_shipping_status','Utworzony XML'),
                array('orders_shipping_date_created', 'now()'),
                array('orders_shipping_date_modified', 'now()'),
                array('orders_shipping_comments', ''),
        );

        $db->insert_query('orders_shipping' , $pola);
        unset($pola);

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

      } else {
        $komunikat = 'Zaznacz przynajmniej jeden rodzaj paczki do wysyłki';
      }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    if ( isset($komunikat) && $komunikat != '' ) {
      echo Okienka::pokazOkno('Błąd', $komunikat);
    }
    ?>

    <div id="naglowek_cont">Tworzenie wysyłki</div>
    <div id="cont">
    
    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    if ( !isset($_GET['zakladka']) ) {
         $_GET['zakladka'] = '0';
    }      
    
    if ( (int)$_GET['id_poz'] == 0 ) {
    ?>
       
      <div class="poleForm"><div class="naglowek">Wysyłka</div>
        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
      </div>      
      
    <?php
    } else {
    ?>    

      <div class="poleForm">
        <div class="naglowek">Wysyłka za pośrednictwem firmy <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

        <div class="pozycja_edytowana" style="overflow:hidden;">  

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {

            $('#pobranie').change(function() {
                    $("#cash_on_delivery").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                    $("#goods_value").val(($(this).is(':checked')) ? Math.round($("#wartosc_zamowienia_val").val()) : "");
                    if ( $(this).is(':checked') ) {
                        $("#pobranie_akapit").slideDown();
                    } else {
                        $("#pobranie_akapit").slideUp();
                    }
            });

            $("#apiForm").validate({
              rules: {
                //cash_on_delivery: { digits: true },
                goods_value: { digits: true, required: function(element) { return $("#cash_on_delivery").val() > 0;} }
              },
              messages: {
                goods_value: { digits: 'Wartość musi być liczbą całkowitą' }
              }
            });
          });
          //]]>
          </script>

          <?php
          $tablica_wysylek = $apiKurier->dhl_post_product_array(false);

          $tekst = '<select style="width:250px;" name="parcel[typ][]" class="valid">';
          foreach ( $tablica_wysylek as $produkt ) {
            $tekst .= '<option value="'.$produkt['id'].'" '.( $produkt['id'] == $apiKurier->polaczenie['INTEGRACJA_DHL_USLUGA'] ? 'selected="selected"' : '' ) .'>'.$produkt['text'].'</option>';
          }
          $tekst .= '</select>';
          ?>

          <script type="text/javascript">
          //<![CDATA[
            $(document).ready(function() {

              $('.plus').click(function() {
                  var sp = parseFloat($(this).prev('input').val());
                  $(this).prev('input').val(sp + 1);
              });

              $('.minus').click(function() {
                  var sp = parseFloat($(this).next('input').val());
                  if ( sp > 0 ) {
                   $(this).next('input').val(sp - 1);
                  }
              });

            });
          //]]>
          </script>

            <?php
            if ( isset($_POST['id']) ) $_GET['id_poz'] = $_POST['id'];
            $zamowienie = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;

            $adres_klienta      = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);

            ?>


            <form action="sprzedaz/zamowienia_wysylka_dhl.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" name="klient_id" value="<?php echo $zamowienie->klient['id']; ?>" />
                  <input type="hidden" name="wartosc_zamowienia_val" id="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
              </div>
              
              <table style="width:100%">
                <tr>
                  <td style="width:55%; vertical-align:top">

                    <div class="obramowanie_tabeli">
                    
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Informacje o przesyłce</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:8px;font-weight:normal;">
                              <p>
                                <label>ID kuriera:</label>
                                <input type="text" size="30" name="kurier_id" value="<?php echo ( isset($_POST['kurier_id']) ? $_POST['kurier_id'] : $apiKurier->polaczenie['INTEGRACJA_DHL_KURIER_ID'] ); ?>" class="required toolTipText" title="ID kuriera odbierającego przesyłkę" />
                              </p>

                              <p>
                                <label>Rodzaj usługi:</label>
                                <?php
                                $tablica = $apiKurier->dhl_post_product_array(false);
                                echo Funkcje::RozwijaneMenu('product', $tablica, $apiKurier->polaczenie['INTEGRACJA_DHL_USLUGA'], 'style="width:326px;"');
                                unset($tablica);
                                ?>
                              </p> 

                              <p>
                                <label>Płatnik:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('N,O', ( isset($_POST['invoice_to']) ? $_POST['invoice_to'] : $apiKurier->polaczenie['INTEGRACJA_DHL_PLATNIK'] ), 'invoice_to', '', 'nadawca,odbiorca', '2' );
                                ?>
                              </p> 

                              <p>
                                <label>Pobranie</label>
                                <input id="pobranie" value="1" type="checkbox" name="pobranie" style="margin-right:20px;" <?php echo ( isset($_POST['pobranie']) ? 'checked="checked"' : '' ); ?>>
                              </p> 

                              <div id="pobranie_akapit" <?php echo ( isset($_POST['pobranie']) ? '' : 'style="display:none;"' ); ?>>

                              <p>
                                <label>Kwota pobrania:</label>
                                <input class="kropkaPusta" type="text" size="30" id="cash_on_delivery" name="cash_on_delivery" value="<?php echo ( isset($_POST['cash_on_delivery']) ? $_POST['cash_on_delivery'] : '' ); ?>" />
                              </p>

                              </div>

                              <p>
                                <label>Kwota ubezpieczenia:</label>
                                <input type="text" size="30" name="goods_value" id="goods_value" value="<?php echo ( isset($_POST['goods_value']) ? $_POST['goods_value'] : '' ); ?>" class="toolTipText" title="Wartość musi być wpisana jeżeli jest przesyłka pobraniowa" />
                              </p>

                              <p>
                                <label>Zwrot dokumentów:</label>
                                <input type="radio" value="Y" name="return_on_delivery" class="toolTipTop" title="Usługa zwrotu potwierdzonego dokumentu" /> tak
                                <input type="radio" value="N" name="return_on_delivery" checked="checked"  class="toolTipTop" title="Usługa zwrotu potwierdzonego dokumentu" /> nie
                              </p> 

                              <p>
                                <label>Zawartość przesyłki:</label>
                                <textarea cols="46" rows="2" name="content"><?php echo ( isset($_POST['content']) ? $_POST['content'] : $apiKurier->polaczenie['INTEGRACJA_DHL_ZAWARTOSC'] ); ?></textarea>
                              </p>

                              <p>
                                <label>Pole komentarza do przesyłki:</label>
                                <textarea cols="46" rows="2" name="comment"><?php echo ( isset($_POST['comment']) ? $_POST['comment'] : '' ); ?></textarea>
                              </p>

                              <p>
                                <label>Znacznik BLP:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('0,1', ( isset($_POST['blp']) ? $_POST['blp'] : $apiKurier->polaczenie['INTEGRACJA_DHL_BLP'] ), 'blp', '', 'nie,tak', '2' );
                                ?>
                              </p> 

                            </td>
                          </tr>
                        </table>
                        
                    </div>

                    <br />

              <div class="obramowanie_tabeli">
                <table class="listing_tbl">
                  <tr class="div_naglowek">
                    <td>Rodzaj przesyłki</td>
                    <td>Ilość</td>
                  </tr>

                  <?php
                  $tablica = $apiKurier->dhl_post_category_array(false);
                  for ( $i = 0, $c = count($tablica); $i < $c; $i++ ) {
                    echo '<tr>';
                    echo '<td class="paczka" style="padding-top:10px; padding-bottom:8px; padding-left:25px;width:300px;">';
                    echo $tablica[$i]['text'];
                    echo '</td>';
                    echo '<td class="paczka">
                    <span class="minus">-</span>
                    <input type="text" size="5" name="'.$tablica[$i]['id'].'" value="'.( isset($_POST[$tablica[$i]['id']]) ? $_POST[$tablica[$i]['id']] : '0').'" style="text-align:center;" />
                    <span class="plus">+</span></td>';
                    echo '</tr>';
                  }
                  ?>

                </table>
              </div>

                  </td>
                  <td style="width:45%; vertical-align:top; padding-left:10px">

                    <div class="obramowanie_tabeli">
                    
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Informacje</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:8px;">
                              <p>
                                <label class="readonly" style="width:200px;">Forma dostawy w zamówieniu:</label>
                                <input type="text" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                              </p> 
                              <p>
                                <label class="readonly" style="width:200px;">Forma płatności w zamówieniu:</label>
                                <input type="text" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
                              </p> 
                              <p>
                                <label class="readonly" style="width:200px;">Wartość zamówienia:</label>
                                <input type="text" name="wartosc_zamowienia" value="<?php echo $waluty->FormatujCene($zamowienie->info['wartosc_zamowienia_val'], false, $zamowienie->info['waluta']); ?>" readonly="readonly" class="readonly" />
                              </p> 
                              <p>
                                <label class="readonly" style="width:200px;">Waga produktów:</label>
                                <input type="text" name="waga_zamowienia" value="<?php echo $waga_produktow; ?>" readonly="readonly" class="readonly" />
                              </p> 
                            </td>
                          </tr>
                        </table>
                    </div>

                    <br />

                    <div class="obramowanie_tabeli">
                    
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Informacje odbiorcy</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:8px;">
                              <p>
                                <label>Adresat:</label>
                                <input type="text" size="30" name="adresat" id="adresat" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['firma']: $zamowienie->dostawa['nazwa']); ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Ulica:</label>
                                <input type="text" size="30" name="adresat_ulica" id="adresat_ulica" value="<?php echo $adres_klienta['ulica']; ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Numer lokalu:</label>
                                <input type="text" size="30" name="adresat_dom" id="adresat_dom" value="<?php echo $adres_klienta['dom']; ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Kod pocztowy:</label>
                                <input type="text" size="30" name="adresat_kod_pocztowy" id="adresat_kod_pocztowy" value="<?php echo Funkcje::KodPocztowyBezKreski($zamowienie->dostawa['kod_pocztowy']); ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Miejscowość:</label>
                                <input type="text" size="30" name="adresat_miasto" id="adresat_miasto" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Numer telefonu:</label>
                                <input type="text" size="30" name="adresat_telefon" id="adresat_telefon" value="<?php echo preg_replace("/[^+0-9]/", "",$zamowienie->klient['telefon']); ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Adres e-mail:</label>
                                <input type="text" size="30" name="adresat_mail" id="adresat_mail" value="<?php echo $zamowienie->klient['adres_email']; ?>" class="klient" />
                              </p> 
                            </td>
                          </tr>
                        </table>
                        
                    </div>
                    
                  </td>
                </tr>
              </table>

              <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Utwórz przesyłkę" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
              </div>
            </form>
 
        </div>
      </div>

    <?php } ?>
    
    </div> 
    
    <?php
    include('stopka.inc.php');    
    
} ?>
