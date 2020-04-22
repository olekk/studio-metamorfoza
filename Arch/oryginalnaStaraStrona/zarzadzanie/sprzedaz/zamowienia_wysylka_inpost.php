<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'Paczkomaty InPost';
    $apiKurier = new InPostApi();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $wysylkaZamowienie = array();

      $adres_nadawcy  = Funkcje::PrzeksztalcAdresDomu($apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_DOM']);

      $sposobNadania = '1';
      if ( $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWANIE'] == 'tak' ) {
        $sposobNadania = '0';
      }

      for ( $i = 0, $c = count($_POST['parcel']['typ']); $i < $c; $i++ ) {
        $numer_tmp = $_POST['id'].$i;
        $wysylkaZamowienie[$numer_tmp] = array(
                                              'adreseeEmail'              => $_POST['adresat_mail'],
                                              'senderEmail'               => $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_EMAIL'],
                                              'phoneNum'                  => $_POST['adresat_telefon_gsm'],
                                              'boxMachineName'            => $_POST['paczkomat_preferowany'],
                                              'alternativeBoxMachineName' => $_POST['paczkomat_alternatywny'],
                                              'senderBoxMachineName'      => ( $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWANIE'] == 'tak' ? '' : $apiKurier->polaczenie['INTEGRACJA_INPOST_PACZKOMAT'] ),
                                              'packType'                  => $_POST['parcel']['typ'][$i],
                                              'insuranceAmount'           => $_POST['parcel']['ubezpieczenie'][$i],
                                              'onDeliveryAmount'          => $_POST['parcel']['pobranie'][$i],
                                              'customerRef'               => $_POST['komentarz'],
                                         );
        if ( $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_ETYKIETA'] == 'tak' ) {
          $wysylkaZamowienie[$numer_tmp]['senderAddress'] = array(
                                                                  'name'          => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_IMIE'],
                                                                  'surName'       => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_NAZWISKO'],
                                                                  'email'         => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_EMAIL'],
                                                                  'phoneNum'      => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_TELEFON'],
                                                                  'street'        => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_ULICA'],
                                                                  'buildingNo'    => $adres_nadawcy['dom'],
                                                                  'flatNo'        => $adres_nadawcy['mieszkanie'],
                                                                  'town'          => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_MIASTO'],
                                                                  'zipCode'       => $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_KOD_POCZTOWY'],
                                                                  'province'      => '',
                                               );
        }
      }

      $noweZamowienie = $apiKurier->inpost_send_packs( $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_EMAIL'], $apiKurier->polaczenie['INTEGRACJA_INPOST_LOGIN_HASLO'], $wysylkaZamowienie, '0', $sposobNadania );

      if ( is_array( $noweZamowienie ) ) {
        for ( $i = 0, $c = count($_POST['parcel']['typ']); $i < $c; $i++ ) {
          $numer_tmp = $_POST['id'].$i;
          if ( isset($noweZamowienie[$numer_tmp]['error_message']) ) {
            $komunikat = $noweZamowienie[$numer_tmp]['error_message'];
          } else {
            $pola = array(
                    array('orders_id',$filtr->process($_POST["id"])),
                    array('orders_shipping_type','InPost'),
                    array('orders_shipping_number',$noweZamowienie[$numer_tmp]['packcode']),
                    array('orders_shipping_weight',''),
                    array('orders_parcels_quantity','1'),
                    array('orders_shipping_status','Created'),
                    array('orders_shipping_date_created', 'now()'),
                    array('orders_shipping_date_modified', 'now()'),
                    array('orders_shipping_comments', ( isset($noweZamowienie[$numer_tmp]['customerdeliveringcode']) ? $noweZamowienie[$numer_tmp]['customerdeliveringcode'] : '' ) ),
            );

            $db->insert_query('orders_shipping' , $pola);
            unset($pola);
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

          }
        }
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

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $.validator.addMethod("valueNotEquals", function (value, element, arg) {
              return arg != value;
            }, "Wybierz opcję");

            $("#apiForm").validate({
              rules: {
                adresat_telefon_gsm: { required: true, remote: "ajax/sprawdz_poprawnosc_gsm.php" }, 
                ubezpieczenie: { digits: true, },
                typ_wysylki  : { required: true, valueNotEquals: "0" }
              },
              messages: {
                adresat_telefon_gsm: {
                  remote: "Niepoprawny numer telefonu gsm"
                }
              }
            });
          });
          //]]>
          </script>

          <?php
          $tablica_wysylek = $apiKurier->inpost_post_parcel_array(false);

          $tekst = '<select style="width:250px;" name="parcel[typ][]" class="valid">';
          foreach ( $tablica_wysylek as $produkt ) {
            $tekst .= '<option value="'.$produkt['id'].'" '.( $produkt['id'] == $apiKurier->polaczenie['INTEGRACJA_INPOST_WYMIARY'] ? 'selected="selected"' : '' ) .'>'.$produkt['text'].'</option>';
          }
          $tekst .= '</select>';
          ?>

          <script type="text/javascript">
          //<![CDATA[
            $(document).ready(function() {

              //$('input').click(function(){
              //  $(this).select();
              //});

              $("#addrow").click(function() {
                $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" title="Usuń wiersz">usuń</a></div></td><td class="paczka" style="padding-top:10px; padding-bottom:8px;"><?php echo $tekst; ?></td><td class="paczka"><input type="text" value="" size="10" name="parcel[pobranie][]" class="kropkaPusta" /></td><td class="paczka"><input type="text" value="" size="10" name="parcel[ubezpieczenie][]" class="kropkaPusta" /></td></tr>');
                if ($(".deleteText").length > 1) $(".deleteText").show();
              });
  
              $('body').on('click', '.deleteText', function() {
                var row = $(this).parents('.item-row');
                $(this).parents('.item-row').remove();
                if ($(".deleteText").length < 2) $(".deleteText").hide();
              });
              
            });
          //]]>
          </script>

            <?php
            if ( isset($_POST['id']) ) $_GET['id_poz'] = $_POST['id'];
            $zamowienie = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;

            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);

            ?>

            <form action="sprzedaz/zamowienia_wysylka_inpost.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" name="inne_dane" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']; ?>" />
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
                                <label class="required">Adres e-mail odbiorcy:</label>
                                <input type="text" size="40" name="adresat_mail" id="adresat_mail" value="<?php echo ( isset($_POST['adresat_mail']) ? $_POST['adresat_mail'] : $zamowienie->klient['adres_email'] ); ?>" class="required" />
                              </p> 
                              <p>
                                <label class="required">Numer telefonu komórkowego:</label>
                                <input type="text" size="40" name="adresat_telefon_gsm" id="adresat_telefon_gsm" value="<?php echo ( isset($_POST['adresat_telefon_gsm']) ? $_POST['adresat_telefon_gsm'] : ( Klienci::CzyNumerGSM($zamowienie->klient['telefon']) ? $zamowienie->klient['telefon'] : '' ) ); ?>" />
                              </p> 
                              <?php 

                              $nazwa_pola_preferowany  = array('name' => 'paczkomat_preferowany');
                              $nazwa_pola_alternatywny = array('name' => 'paczkomat_alternatywny');

                              if ( isset($_POST['paczkomat_preferowany']) ) {

                                  $zaznaczony_domyslny     = array('selected' => $_POST['paczkomat_preferowany']);
                                  $zaznaczony_alternatywny = array('selected' => $_POST['paczkomat_alternatywny']);

                              } else {
                                if ( $zamowienie->info['wysylka_info'] == '' ) {
                                    $tablica = $apiKurier->inpost_find_customer($zamowienie->klient['adres_email']);

                                    if (isset($tablica['error'])) {
                                        $zaznaczony_domyslny     = array('postcode' => $zamowienie->dostawa['kod_pocztowy']);
                                        $zaznaczony_alternatywny = array();
                                    } else {
                                        $zaznaczony_domyslny     = array('selected' => $tablica['preferedBoxMachineName']);
                                        $zaznaczony_alternatywny = array('selected' => $tablica['alternativeBoxMachineName']);
                                    }
                                } else {

                                    $danePaczkomatu = explode(' ', $zamowienie->info['wysylka_info']);
                                    $kodPaczkomatu = str_replace(',', '', $danePaczkomatu['1']);

                                    $kod_pocztowy_paczkomatu = '';
                                    $machines_all = $apiKurier->inpost_get_machine_list();
                                    foreach ( $machines_all as $paczkomat ) {
                                        if ( $paczkomat['name'] == $kodPaczkomatu ) {
                                            $kod_pocztowy_paczkomatu = $paczkomat['postcode'];
                                            $nazwa_pola_preferowany['selected'] = $paczkomat['name'];
                                        }
                                    }
                                    if ( $kod_pocztowy_paczkomatu != '' ) {
                                        $zaznaczony_domyslny     = array('postcode' => $kod_pocztowy_paczkomatu);
                                        $zaznaczony_alternatywny = array('postcode' => $zamowienie->dostawa['kod_pocztowy']);
                                    } else {
                                        $zaznaczony_domyslny     = array('postcode' => $zamowienie->dostawa['kod_pocztowy']);
                                        $zaznaczony_alternatywny = array();
                                    }
                                }

                              }
                              $domyslny     = array_merge($zaznaczony_domyslny, $nazwa_pola_preferowany);
                              $alternatywny = array_merge($zaznaczony_alternatywny, $nazwa_pola_alternatywny);

                              $paczkomat_domyslny = $apiKurier->inpost_machines_dropdown_all( $domyslny );
                              $paczkomat_alternatywny = $apiKurier->inpost_machines_dropdown_all( $alternatywny );
                              unset($tablica);
                              ?>

                              <p>
                                <label>Preferowany paczkomat:</label>
                              <?php 
                              echo $paczkomat_domyslny;
                              ?>
                              </p> 
                              <p>
                                <label>Alternatywny paczkomat:</label>
                              <?php 
                              echo $paczkomat_alternatywny;
                              ?>
                              </p>
                              <p>
                                <label>Dodatkowe info na etykiecie:</label>
                                <textarea cols="40" rows="2" name="komentarz"><?php echo ( isset($_POST['komentarz']) ? $_POST['komentarz'] : 'Zamówienie numer: ' . $_GET['id_poz'] ); ?></textarea>
                              </p>
                            </td>
                          </tr>
                        </table>
                        
                      <div class="ostrzezenie" style="margin-left:10px;">Dopuszczalna maksymalna waga paczki wynosi 25 kilogramów. <br />Waga zamówionych produktów wyznaczona na podstawie wagi produktów w zamówieniu (zaokrąglenie do góry do najbliższej liczby naturalnej): <b><?php echo ceil($waga_produktow); ?> kg</b></div>
                    </div>

                    <br />

                      <div class="obramowanie_tabeli">
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td colspan="10">Informacje o paczkach</td>
                          </tr>
                          <tr>
                            <td style="width:50px"></td>
                            <td class="paczka" style="padding-top:8px;">Typ paczki</td>
                            <td class="paczka">Kwota pobrania</td>
                            <td class="paczka">Kwota ubezpieczenia</td>
                          </tr>

                          <tr class="item-row">
                            <td style="text-align:right"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" ></a></div></td>
                            <td class="paczka" style="padding-top:10px; padding-bottom:8px;">
                              <?php
                              $tablica = $apiKurier->inpost_post_parcel_array(false);
                              echo Funkcje::RozwijaneMenu('parcel[typ][]', $tablica, $apiKurier->polaczenie['INTEGRACJA_INPOST_WYMIARY'], 'style="width:250px;"');
                              unset($tablica);
                              ?>
                            </td>
                            <td class="paczka"><input type="text" value="" size="10" name="parcel[pobranie][]" class="kropkaPusta" /></td>
                            <td class="paczka"><input type="text" value="" size="10" name="parcel[ubezpieczenie][]" class="kropkaPusta" /></td>
                          </tr>

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
                                <input type="text" size="30" name="adresat" id="adresat" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['firma']: $zamowienie->dostawa['nazwa']); ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Ulica:</label>
                                <input type="text" size="30" name="adresat_ulica" id="adresat_ulica" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Numer domu / Lokalu:</label>
                                <input type="text" size="30" name="adresat_dom" id="adresat_dom" value="<?php echo $adres_klienta['dom']; ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Kod pocztowy:</label>
                                <input type="text" size="30" name="adresat_kod_pocztowy" id="adresat_kod_pocztowy" value="<?php echo $zamowienie->dostawa['kod_pocztowy']; ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Miejscowość:</label>
                                <input type="text" size="30" name="adresat_miasto" id="adresat_miasto" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Numer telefonu:</label>
                                <input type="text" size="30" name="adresat_telefon" id="adresat_telefon" value="<?php echo $zamowienie->klient['telefon']; ?>" class="klient" />
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
      </div>

    <?php } ?>
    
    </div> 
    
    <?php
    include('stopka.inc.php');    
    
} ?>
