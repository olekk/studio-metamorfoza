<?php
chdir('../'); 

// ustawienie strefy czasowej - na wypadek jezeli nie ma tego w konfiguracji PHP
date_default_timezone_set('Europe/Warsaw');

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'wyszukaj' ) {

      $warunek_wysylek = "";
      $warunek_platnosci = "";
      $warunek_daty = "";
      $warunek_status = "";

      //warunki wyboru wysylek
      if ( isset($_POST['wysylka']) && count($_POST['wysylka']) > 0 ) {
        $warunek_wysylek .= ' AND (';
        for ( $i =0, $c = count($_POST['wysylka']); $i < $c; $i++ ) {
          if ( $i == '0' ) {
            $warunek_wysylek .= " o.shipping_module IN ('".str_replace('|','\',\'',$_POST['wysylka'][$i])."') ";
          } else {
            $warunek_wysylek .= " OR o.shipping_module IN ('".str_replace('|','\',\'',$_POST['wysylka'][$i])."') ";
          }
        }
        $warunek_wysylek .= ')';
      }

      //warunki wyboru platnosci
      if ( isset($_POST['platnosc']) && count($_POST['platnosc']) > 0 ) {
        $warunek_platnosci .= ' AND (';
        for ( $i = 0, $c = count($_POST['platnosc']); $i < $c; $i++ ) {
          if ( $i == '0' ) {
            $warunek_platnosci .= " o.payment_method IN ('".str_replace('|','\',\'',$_POST['platnosc'][$i])."') ";
          } else {
            $warunek_platnosci .= " OR o.payment_method IN ('".str_replace('|','\',\'',$_POST['platnosc'][$i])."') ";
          }
        }
        $warunek_platnosci .= ')';
      }

      //warunki wyboru statusu zamowiena
      if ( isset($_POST['status']) && $_POST['status'] != '' && $_POST['status'] != '0' ) {
        $warunek_status = " AND o.orders_status = ". $_POST['status'];
      }

      //warunki wyboru dat
      if ( $_POST['data_statusu_od'] != '' && $_POST['data_statusu_do'] == '' ) {
        $startdate       = date("Y-m-d H:i:s",strtotime($_POST['data_statusu_od'] . ' 00:00:00'));
        $enddate         = date("Y-m-d H:i:s");
      } elseif ( $_POST['data_statusu_od'] == '' && $_POST['data_statusu_do'] != '' ) {
        $startdate       = date("Y-m-d H:i:s",strtotime('1970-01-01 00:00:00'));
        $enddate         = date("Y-m-d H:i:s",strtotime($_POST['data_statusu_do'] . ' 00:00:00'));
      } elseif ( $_POST['data_statusu_od'] != '' && $_POST['data_statusu_do'] != '' ) {
        $startdate       = date("Y-m-d H:i:s",strtotime($_POST['data_statusu_od'] . ' 00:00:00'));
        $enddate         = date("Y-m-d H:i:s",strtotime($_POST['data_statusu_do'] . ' 23:59:59'));
      } else {
        $startdate       = date("Y-m-d H:i:s",strtotime('1970-01-01 00:00:00'));
        $enddate         = date("Y-m-d H:i:s");
      }
      $warunek_daty    = " h.date_added between '" . $startdate . "' AND '" . $enddate . "' ";


      $zapytanie = "SELECT DISTINCT 
        o.orders_id, o.shipping_module, o.orders_status, o.delivery_name, o.delivery_company, o. delivery_street_address, o.delivery_postcode, o.delivery_city, o.delivery_state, o.delivery_country, o.payment_method, o.currency,
        h.comments, h.date_added, 
        ot.class, ot.title, ot.value 
        FROM orders o
        LEFT JOIN orders_status_history h ON h.orders_id = o.orders_id AND h.orders_status_id = o.orders_status
        LEFT JOIN orders_total ot ON ot.orders_id = o.orders_id AND ot.class = 'ot_total'
        WHERE " . $warunek_daty . " " .
        $warunek_status . " " . 
        $warunek_wysylek . " " .
        $warunek_platnosci . " 
        GROUP BY o.orders_id
      ";
      $sql = $db->open_query($zapytanie);
      $ile_pozycji = (int)$db->ile_rekordow($sql);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Generowanie etykiet adresowych</div>
    <div id="cont">
          
          <script type="text/javascript" src="javascript/jquery.multi-select.js"></script>
          <script type="text/javascript" src="javascript/jquery.application.js"></script>

          <script type="text/javascript" src="javascript/etykiety.js"></script>
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });                
          });
          //]]>
          </script>        

            <form action="sprzedaz/zamowienia_etykiety_adresowe.php" method="post" id="etykietyForm" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Wybór zamówień</div>
                
                    <div class="pozycja_edytowana">
                        
                        <div class="info_content">
                    
                          <input type="hidden" name="akcja" value="wyszukaj" />
                      
                          <table style="width:100%">
                            <tr>
                              <td align="left" style="padding-right:15px;">
                                <div class="obramowanie_tabeli">
                                  <table class="listing_tbl">
                                    <tr class="div_naglowek">
                                      <td>Wybierz wysyłki</td>
                                    </tr>

                                    <tr>
                                      <td style="width:100%">
                                        <?php
                                        if ( isset($_POST['wysylka']) && count($_POST['wysylka']) > 0 ) {
                                          $tablica_wysylek = $_POST['wysylka'];
                                        } else {
                                          $tablica_wysylek = array();
                                        }

                                        $wszystkie_wysylki_tmp = Array();
                                        $wszystkie_wysylki_tmp = Moduly::TablicaWysylekNazwy();

                                        echo '<select name="wysylka[]" multiple="multiple" id="multipleHeadersEmptyWysylka">';
                                        foreach ( $wszystkie_wysylki_tmp as $value ) {
                                          $wybrany = '';
                                          if ( in_array($value['id'], $tablica_wysylek ) ) {
                                            $wybrany = 'selected="selected"';
                                          }
                                          echo '<option value="'.$value['id'].'" '.$wybrany.'>'.$value['text'].'</option>';
                                        }
                                        echo '</select>';
                                        ?>
                                      </td>
                                    </tr>
                                  </table>
                                </div>
                              </td>

                              <td align="right">
                                <div class="obramowanie_tabeli">
                                  <table class="listing_tbl">
                                    <tr class="div_naglowek">
                                      <td>Wybierz płatności</td>
                                    </tr>

                                    <tr>
                                      <td style="width:100%">
                                        <?php
                                        if ( isset($_POST['platnosc']) && count($_POST['platnosc']) > 0 ) {
                                          $tablica_platnosci = $_POST['platnosc'];
                                        } else {
                                          $tablica_platnosci = array();
                                        }

                                        $wszystkie_platnosci_tmp = Array();
                                        $wszystkie_platnosci_tmp = Moduly::TablicaPlatnosciNazwy();

                                        echo '<select name="platnosc[]" multiple="multiple" id="multipleHeadersEmptyPlatnosc">';
                                        foreach ( $wszystkie_platnosci_tmp as $value ) {
                                          $wybrany = '';
                                          if ( in_array($value['id'], $tablica_platnosci ) ) {
                                            $wybrany = 'selected="selected"';
                                          }
                                          echo '<option value="'.$value['id'].'" '.$wybrany.'>'.$value['text'].'</option>';
                                        }
                                        echo '</select>';
                                        ?>
                                      
                                      </td>
                                    </tr>
                                  </table>
                                </div>
                              </td>
                            </tr>
                          </table>

                        </div>
                     
                        <div class="info_content" style="margin-top:15px;">
                          <p>
                            <label>Zamówienia ze statusem:</label>
                            <?php
                            $default = '';
                            if ( isset($_POST['status']) ) $default = $_POST['status'];
                            $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- dowolny ---');
                            echo Funkcje::RozwijaneMenu('status', $tablica,'',' style="width: 350px;"'); ?>
                          </p>

                          <p>
                            <label>Status zmieniony:</label>
                              od <input type="text" id="data_statusu_od" name="data_statusu_od" value="<?php echo ( isset($_POST['data_statusu_od']) ? $_POST['data_statusu_od'] : '' ); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                              <input type="text" id="data_statusu_do" name="data_statusu_do" value="<?php echo ( isset($_POST['data_statusu_do']) ? $_POST['data_statusu_do'] : '' ); ?>" size="10" class="datepicker" />
                          </p>

                        </div>

                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Wybierz zamówienia" />
                    </div>

              </div>                      
            </form>


    </div>

    <?php
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'wyszukaj' ) {
      ?>
      <div id="contWynik" style="padding-top:10px;">
        <?php
        if ( $ile_pozycji > 0 ) {
          ?>
          <div class="obramowanie_tabeli">
            <form action="sprzedaz/zamowienia_etykiety_adresowe_pdf.php" method="post" id="wynikForm" class="cmxform">          
              <div>
                <input type="hidden" name="akcja" value="wydrukuj" />

                <table class="listing_tbl">
                  <tr class="div_naglowek">
                    <td style="text-align:center;">Usuń</td>
                    <td style="text-align:center;">Zamówienie</td>
                    <td style="text-align:center;">Treść</td>
                  </tr>

                  <?php
                  $i = 1;
                  while ($info = $sql->fetch_assoc()) {

                    $adresat = '';
                    if ( $info['delivery_company'] != '' ) {
                      $adresat .=  $info['delivery_company'] . "\n";
                    } else {
                      $adresat .= $info['delivery_name'] . "\n";;
                    }
                    $adresat .= $info['delivery_street_address'];
                    $adresat .= "\n" . $info['delivery_postcode'] . ' ' . $info['delivery_city'];
                    $adresat .= "\n" . $info['delivery_country'];

                    echo '<tr class="item-row">';
                    echo '<td class="faktura_produkt" style="text-align:center;width:50px;"><div class="delete-wpr"><a class="delete" href="javascript:void(0)" title="Usuń wiersz">X</a></div></td>';
                    echo '<td class="faktura_produkt" style="text-align:center;width:100px;">'.$info['orders_id'].'</td>';
                    echo '<td class="faktura_produkt"><textarea cols="100" rows="4" name="wiersz['.$i.'][adresat]">'.$adresat.'</textarea></td>';
                    echo '</tr>';

                    $i++;
                  }
                  ?>
                  <tr id="hiderow">
                    <td colspan="11" style="padding:20px;"><a id="addrow" href="javascript:void(0)" title="Dodaj pozycję" class="dodaj">Dodaj pozycję</a></td>
                  </tr>
                </table>

                <p>
                  <label>Format etykiet:</label>
                  <?php
                  $tablica = array();
                  $zapytanie_etykiety = "SELECT * FROM print_labels ORDER BY brand, name";
                  $sqls = $db->open_query($zapytanie_etykiety);
                  while ($infos = $sqls->fetch_assoc()) {
                    $tablica[] = array('id' => $infos['id'], 'text' => $infos['name'] . ' ' . $infos['description']);
                    if ( $infos['label_default'] == '1' ) {
                      $domyslna = $infos['id'];
                      $ramka    = $infos['border'];
                    }
                  }
                  echo Funkcje::RozwijaneMenu('id', $tablica, $domyslna, 'style="width:350px;"');
                  unset($tablica);
                  ?>
                </p>
                <p>
                  <label>Rozpocznij od pozycji:</label>
                  <input type="text" name="offset" id="offset" value="0" class="toolTipText" title="Pozycja na arkuszu etykiet, od której rozpocząć drukowanie; 0 - od początku" />
                </p>

                <p>
                  <label>Czy drukować ramkę:</label>
                  <input type="radio" value="0" name="ramka" <?php echo ( $ramka == '1' ? 'checked="checked"' : '' ); ?> /> nie
                  <input type="radio" value="1" name="ramka" <?php echo ( $ramka == '0' ? 'checked="checked"' : '' ); ?> /> tak                       
                </p>

              </div>

              <div class="przyciski_dolne">
                 <input type="submit" class="przyciskNon" value="Wydrukuj etykiety adresowe" />
              </div>

              <div><input type="hidden" name="licznik" id="licznik" value="<?php echo $i; ?>" /></div>
            </form>
          </div>
          <?php
        } else {
          echo '<div class="poleForm" style="padding-top:10px;"><div class="pozycja_edytowana"><p>Brak wyników do wyświetlenia</p></div></div>';
        }
        ?>
      </div>
      <?php
    }
    ?>

    <?php
    include('stopka.inc.php');

}