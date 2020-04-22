<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'KurJerzy';
    $apiKurier = new KurjerzyApi();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $wysylkaZamowienie = array();

      // dane uslugi
      $wysylkaZamowienie['order'][0]['service']['product_id'] = $_POST['typ_wysylki'];

      // dane przesylki
      $parcel = array();
      $weight_total = 0;

      for ( $i = 0, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
        $parcel[$i]['weight']    = ceil($_POST['parcel']['waga'][$i]);
        $parcel[$i]['height']    = ceil($_POST['parcel']['wysokosc'][$i]);
        $parcel[$i]['width']     = ceil($_POST['parcel']['szerokosc'][$i]);
        $parcel[$i]['length']    = ceil($_POST['parcel']['dlugosc'][$i]);
        $weight_total += $_POST['parcel']['waga'][$i];
      }

      $wysylkaZamowienie['order'][0]['parcel'] = $parcel;

      $wysylkaZamowienie['order'][0]['details']['content']     = $_POST['zawartosc'];
      $wysylkaZamowienie['order'][0]['details']['order_value'] = $_POST['wartosc'];
      $wysylkaZamowienie['order'][0]['details']['insurance']   = $_POST['ubezpieczenie'];
      $wysylkaZamowienie['order'][0]['details']['charging']    = $_POST['pobranie'];

      // szczegoly odbioru
      $wysylkaZamowienie['order'][0]['shipment']['date']       = $_POST['daty'];
      $wysylkaZamowienie['order'][0]['shipment']['time_from']  = $_POST['godzina_od'];
      $wysylkaZamowienie['order'][0]['shipment']['time_to']    = $_POST['godzina_do'];

      // dane nadawcy
      $wysylkaZamowienie['order'][0]['sender']['name']         = $_POST['nadawca'];
      $wysylkaZamowienie['order'][0]['sender']['street']       = $_POST['nadawca_ulica'];
      $wysylkaZamowienie['order'][0]['sender']['housenr']      = $_POST['nadawca_dom'];
      $wysylkaZamowienie['order'][0]['sender']['postcode']     = $_POST['nadawca_kod_pocztowy'];
      $wysylkaZamowienie['order'][0]['sender']['city']         = $_POST['nadawca_miasto'];
      $wysylkaZamowienie['order'][0]['sender']['phone']        = $_POST['nadawca_telefon'];
      $wysylkaZamowienie['order'][0]['sender']['email']        = $_POST['nadawca_mail'];

      // dane odbiorcy
      $wysylkaZamowienie['order'][0]['recipient']['name']      = $_POST['adresat'];
      $wysylkaZamowienie['order'][0]['recipient']['street']    = $_POST['adresat_ulica'];
      $wysylkaZamowienie['order'][0]['recipient']['housenr']   = $_POST['adresat_dom'];
      $wysylkaZamowienie['order'][0]['recipient']['postcode']  = $_POST['adresat_kod_pocztowy'];
      $wysylkaZamowienie['order'][0]['recipient']['city']      = $_POST['adresat_miasto'];
      $wysylkaZamowienie['order'][0]['recipient']['phone']     = $_POST['adresat_telefon'];
      $wysylkaZamowienie['order'][0]['recipient']['email']     = $_POST['adresat_mail'];

      $noweZamowienie = $apiKurier->makeOrder( $wysylkaZamowienie );

      if ( array_key_exists('error', $noweZamowienie) ) {
        $komunikat = implode('<br>', $noweZamowienie['error']);
      }

      if ( isset($noweZamowienie['order'][0]) ) {

        $pola = array(
                array('orders_id',$filtr->process($_POST["id"])),
                array('orders_shipping_type',$api),
                array('orders_shipping_number',$noweZamowienie['order'][0]),
                array('orders_shipping_weight',$weight_total),
                array('orders_parcels_quantity',count($_POST['parcel']['dlugosc'])),
                array('orders_shipping_status','1'),
                array('orders_shipping_date_created', 'now()'),
                array('orders_shipping_comments', ''),
        );

        $db->insert_query('orders_shipping' , $pola);
        unset($pola);

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
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

          <script type="text/javascript" src="javascript/jquery.chained.remote.js"></script>        
          <script type="text/javascript" src="javascript/paczka.js"></script>

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $.validator.addMethod("valueNotEquals", function (value, element, arg) {
              return arg != value;
            }, "Wybierz opcję");

            $("#apiForm").validate({
              rules: {
                szerokosc    : { required: true },
                dlugosc      : { required: true },
                wysokosc     : { required: true },
                zawartosc    : { required: true },
                ubezpieczenie: { digits: true },
                waga         : { digits: true },
                typ_wysylki  : { required: true, valueNotEquals: "0" },
                daty         : { required: true, valueNotEquals: "0" },
                godzina_od   : { required: true, valueNotEquals: "0" },
                godzina_do : { required: true, valueNotEquals: "0" }
              }
            });
          });
          //]]>
          </script>

          <script type="text/javascript" charset="utf-8">
          //<![CDATA[
          $(function() {
            $("#daty_wysylki").remoteChained("#typ_wysylki", "ajax/kurjerzy_data_json.php");
            $("#godzina_start").remoteChained("#typ_wysylki, #daty_wysylki", "ajax/kurjerzy_godzina_od_json.php");
            $("#godzina_koniec").remoteChained("#typ_wysylki, #daty_wysylki, #godzina_start", "ajax/kurjerzy_godzina_do_json.php");
          });
          //]]>
          </script>

            <?php
            if ( $apiKurier->success ) {
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;
            $wymiary        = array();

            $wysylki        = $apiKurier->produkty;

            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
            $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_KURJERZY_WYMIARY_DLUGOSC'];
            $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_KURJERZY_WYMIARY_SZEROKOSC'];
            $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_KURJERZY_WYMIARY_WYSOKOSC'];

            ?>

            <form action="sprzedaz/zamowienia_wysylka_kurjerzy.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />

                  <input type="hidden" name="nadawca" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KURJERZY_NADAWCA_NAZWA']; ?>" />
                  <input type="hidden" name="nadawca_ulica" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KURJERZY_NADAWCA_ULICA']; ?>" />
                  <input type="hidden" name="nadawca_dom" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KURJERZY_NADAWCA_DOM']; ?>" />
                  <input type="hidden" name="nadawca_kod_pocztowy" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KURJERZY_NADAWCA_KOD_POCZTOWY']; ?>" />
                  <input type="hidden" name="nadawca_miasto" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KURJERZY_NADAWCA_MIASTO']; ?>" />
                  <input type="hidden" name="nadawca_telefon" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KURJERZY_NADAWCA_TELEFON']; ?>" />
                  <input type="hidden" name="nadawca_mail" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_KURJERZY_NADAWCA_EMAIL']; ?>" />
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
                            <td style="padding-top:8px; padding-bottom:8px;">
                              <p>
                                <label class="required">Rodzaj wysyłki:</label>
                                <?php
                                  $domyslnie = '';
                                  if ( isset($_POST['typ_wysylki']) ) $domyslnie = $_POST['typ_wysylki'];
                                  $tablica = KurjerzyApi::ListaWysylek($wysylki, true);
                                  echo Funkcje::RozwijaneMenu('typ_wysylki', $tablica, $domyslnie, 'id="typ_wysylki" style="width:250px;"' ); 
                                  unset($tablica);
                                  ?>
                              </p> 
                              <p>
                                <label class="required">Data odbioru:</label>
                                <select id="daty_wysylki" name="daty" style="width:250px;">
                                  <option value="0">---</option>
                                </select>
                              </p> 
                              <p>
                                <label class="required">Odbiór od godziny:</label>
                                <select id="godzina_start" name="godzina_od" style="width:70px;">
                                  <option value="0">---</option>
                                </select>
                              </p>
                              <p>
                                <label class="required">Do godziny:</label>
                                <select id="godzina_koniec" name="godzina_do" style="width:70px;">
                                  <option value="0">---</option>
                                </select>
                              </p> 
                              <p>
                                <label class="required">Zawartość przesyłki:</label>
                                <input type="text" size="45" name="zawartosc" id="zawartosc" value="<?php echo ( isset($_POST['zawartosc']) ? $_POST['zawartosc'] : $apiKurier->polaczenie['INTEGRACJA_KURJERZY_ZAWARTOSC']); ?>" class="required toolTipText" title="np. telefon, komputer, itp." />
                              </p> 
                              <p>
                                <label class="required">Wartość przesyłki [PLN]:</label>
                                <input type="text" size="20" name="wartosc" id="wartosc" value="<?php echo ( isset($_POST['wartosc']) ? $_POST['wartosc'] : $zamowienie->info['wartosc_zamowienia_val'] ); ?>" onchange="zamien_krp(this)" class="required" />
                              </p> 
                              <p>
                                <label>Wartość ubezpieczenia [PLN]:</label>
                                <input type="text" size="20" name="ubezpieczenie" id="ubezpieczenie" value="<?php echo ( isset($_POST['ubezpieczenie']) ? $_POST['ubezpieczenie'] : '' ); ?>" class="toolTipText" title="wartość ubezpieczenia musi być liczbą całkowitą" />
                              </p> 
                              <p>
                                <label>Wartość pobrania [PLN]:</label>
                                <input type="text" size="20" name="pobranie" id="pobranie" value="<?php echo ( isset($_POST['pobranie']) ? $_POST['pobranie'] : '' ); ?>" onchange="zamien_krp(this)" />
                              </p> 
                            </td>
                          </tr>
                        </table>
                        
                    </div>

                    <br />

                      <div class="obramowanie_tabeli">
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td colspan="10">Informacje o paczkach</td>
                          </tr>
                          <tr>
                            <td style="width:50px"></td>
                            <td class="paczka" style="padding-top:8px;">Długość [cm]</td>
                            <td class="paczka">Szerokość [cm]</td>
                            <td class="paczka">Wysokość [cm]</td>
                            <td class="paczka">Waga [kg]</td>
                            <td class="paczka">Niestandardowa</td>
                          </tr>

                          <tr class="item-row">
                            <td style="text-align:right"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" ></a></div></td>
                            <td class="paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc']['0']) ? $_POST['parcel']['dlugosc']['0'] : $wymiary['0'] ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPusta required" /></td>
                            <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc']['0']) ? $_POST['parcel']['szerokosc']['0'] : $wymiary['1'] ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPusta required" /></td>
                            <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc']['0']) ? $_POST['parcel']['wysokosc']['0'] : $wymiary['2'] ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPusta required" /></td>
                            <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : ceil($waga_produktow) ); ?>" size="8" name="parcel[waga][]" class="kropkaPusta required" /></td>
                            <td class="paczka"><input type="checkbox" value="1" name="parcel[niestandard][]" id="niestandard" /></td>
                          </tr>

                          <?php
                          if ( isset($_POST['parcel']) && count($_POST['parcel']['dlugosc']) > 1 ) {
                            for ( $i = 1, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
                              ?>
                              <tr class="item-row">
                                <td style="text-align:right"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" title="Usuń wiersz">usuń</a></div></td>
                                <td class="paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc'][$i]) ? $_POST['parcel']['dlugosc'][$i] : '' ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPusta required" /></td>
                                <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc'][$i]) ? $_POST['parcel']['szerokosc'][$i] : '' ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPusta required" /></td>
                                <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc'][$i]) ? $_POST['parcel']['wysokosc'][$i] : '' ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPusta required" /></td>
                                <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga'][$i]) ? $_POST['parcel']['waga'][$i] : '' ); ?>" size="8" name="parcel[waga][]" class="kropkaPusta required" /></td>
                                <td class="paczka"><input type="checkbox" value="1" name="parcel[niestandard][]" id="niestandard" /></td>
                              </tr>
                              <?php
                            }
                          }
                          ?>

                          <tr id="hiderow">
                            <td colspan="10" style="padding-left:10px;padding-top:10px;padding-bottom:10px;"><a id="addrow" href="javascript:void(0)" title="Dodaj pozycję" class="dodaj">Dodaj paczkę</a></td>
                          </tr>

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
                                <input type="text" size="30" name="adresat" id="adresat" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['firma']: $zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Ulica:</label>
                                <input type="text" size="30" name="adresat_ulica" id="adresat_ulica" value="<?php echo $adres_klienta['ulica']; ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Numer domu / Lokalu:</label>
                                <input type="text" size="30" name="adresat_dom" id="adresat_dom" value="<?php echo $adres_klienta['dom']; ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Kod pocztowy:</label>
                                <input type="text" size="30" name="adresat_kod_pocztowy" id="adresat_kod_pocztowy" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Miejscowość:</label>
                                <input type="text" size="30" name="adresat_miasto" id="adresat_miasto" value="<?php echo $zamowienie->dostawa['miasto']; ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Numer telefonu:</label>
                                <input type="text" size="30" name="adresat_telefon" id="adresat_telefon" value="<?php echo $zamowienie->klient['telefon']; ?>"  class="klient" />
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
            <?php } else {
                echo 'Sprawdź konfigurację modułu';
            } ?>
        
        </div>
      </div>

    <?php } ?>
    
    </div>    
    
    <?php
    include('stopka.inc.php');    
    
} ?>
