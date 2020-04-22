<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'FURGONETKA';
    $apiKurier = new FurgonetkaApi();
    $komunikat = '';
    $blad = true;

    if ( !isset($_SESSION['furgonetkahash']) ) {
        $apiKurier->doLogin();
    } else {
        $hash = explode(':', $_SESSION['furgonetkahash']);
        if ( time() - $hash[1] > 600 ) {
            unset($_SESSION['furgonetkahash']);
            $apiKurier->doLogin();
        }
        unset($hash);
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $numerPrzesylki = '';
      $params = array();

      $params['sender_name']        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_IMIE'];
      $params['sender_surname']     = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_NAZWISKO'];
      $params['sender_company']     = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_FIRMA'];
      $params['sender_street']      = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_ULICA'];
      $params['sender_city']        = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_MIASTO'];
      $params['sender_postcode']    = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY'];
      $params['sender_phone']       = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_TELEFON'];
      $params['sender_email']       = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NADAWCA_EMAIL'];
      $params['iban']               = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_NUMER_KONTA'];

      $ImieNazwisko = explode(' ', $_POST['adresat_nazwisko_i_imie']);
      $params['receiver_name']         = $ImieNazwisko['0'];
      $params['receiver_surname']      = $ImieNazwisko['1'];

      if ($_POST['odbiorca_firma'] == '1') {
        $params['receiver_company']    = $_POST['adresat_firma'];
      }
      $params['receiver_street']       = $_POST['adresat_ulica'];
      $params['receiver_city']         = $_POST['adresat_miasto'];
      $params['receiver_postcode']     = $_POST['adresat_kod_pocztowy'];
      $params['receiver_country_code'] = $_POST['receiver_country_code'];
      $params['receiver_phone']        = $_POST['adresat_telefon'];
      $params['receiver_email']        = $_POST['adresat_email'];

      if ( isset($_POST['WysylkaInpost']) ) {
        $params['receiver_paczkomat'] = $_POST['integracja_furgonetka_paczkomat'];
        $params['sender_paczkomat'] = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_PACZKOMAT'];
      }

      $params['type']     = $_POST['parcel_type'];
      $params['wrapping'] = $_POST['wrapping'];
      $params['shape']    = $_POST['shape'];
      $params['weight']   = $_POST['weight'];
      $params['height']   = $_POST['height'];
      $params['width']    = $_POST['width'];
      $params['depth']    = $_POST['depth'];

      $params['description'] = $_POST['description'];
      $params['user_reference_number'] = $_POST['user_reference_number'];


      if (isset($_POST['dpd0930'])) {
        $params['guarantee'] = '0930';
      }
      if (isset($_POST['dpd1200'])) {
        $params['guarantee'] = '1200';
      }

      if (isset($_POST['odbSat'])) {
        $params['saturdayPickup'] = true;
      }
      if (isset($_POST['dpdSat'])) {
        $params['saturdayDelivery'] = true;
      }

      if (isset($_POST['rod'])) {
        $params['rod'] = true;
      }
      if (isset($_POST['cod'])) {
        $params['cod'] = $_POST['cod_kwota'];
        if ( isset($_POST['quick_transfer']) && $_POST['quick_transfer'] == '1' ) {
            $params['quick_transfer'] = true;
        }
      }

      $params['worth'] = $_POST['value'];

      if (isset($_POST['avizo_pickup_sms'])) {
        $params['avizo_pickup_sms'] = true;
      }
      if (isset($_POST['avizo_pickup_tel'])) {
        $params['avizo_pickup_tel'] = true;
      }
      if (isset($_POST['avizo_delivery_sms'])) {
        $params['avizo_delivery_sms'] = true;
      }
      if (isset($_POST['avizo_delivery_tel'])) {
        $params['avizo_delivery_tel'] = true;
      }

      if (isset($_POST['ups_saver'])) {
        $params['ups_saver'] = true;
      }

      if (isset($_POST['self_pickup'])) {
        $params['self_pickup'] = true;
      }

      if ( isset($_POST['kurier']) ) {
        $params['service'] = $_POST['kurier'];
      }

      $response = $apiKurier->doPackageAdd($params);

      if ( isset($response->package_id) ) {
        $numerPrzesylki = $response->package_id;
      }

      if ( $numerPrzesylki != '' ) {

          $pola = array(
                  array('orders_id',$filtr->process($_POST["id"])),
                  array('orders_shipping_type',$api . ' ('.$_POST['kurier'].')'),
                  array('orders_shipping_number',$numerPrzesylki),
                  array('orders_shipping_weight',$_POST['weight']),
                  array('orders_parcels_quantity',$_POST['number_of_packages']),
                  array('orders_shipping_status','0'),
                  array('orders_shipping_date_created', 'now()'),
                  array('orders_shipping_date_modified', 'now()'),
                  array('orders_shipping_comments', $_POST['kurier']),
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

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {

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

          <script type="text/javascript">
          //<![CDATA[
            $(document).ready(function() {

                $('#cod').click(function() {
                    $("#cod_kwota").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                    if ( $(this).is(':checked') ) {
                        $("#pobranie_kwota").slideDown();
                        $("#pobranie_typ").slideDown();
                    } else {
                        $("#pobranie_kwota").slideUp();
                        $("#pobranie_typ").slideUp();
                    }
                });

                $('#parcel_type').change(function () {
                    if ($(this).val() == 'package') {
                        $('#RodzajOpakowania').slideDown();
                        $('#KsztaltOpakowania').slideDown();
                    } else {
                        $('#RodzajOpakowania').slideUp();
                        $('#KsztaltOpakowania').slideUp();
                    }
                });

                $('#dpd0930').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd1200').attr("disabled", true);
                        $('#dpdSat').attr("disabled", true);
                        $('#odbSat').attr("disabled", true);
                        $('#avizo_pickup_sms').attr("disabled", true);
                        $('#avizo_pickup_tel').attr("disabled", true);
                        $('#avizo_delivery_sms').attr("disabled", true);
                        $('#avizo_delivery_tel').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                    } else {
                        $('#dpd1200').removeAttr("disabled");
                        $('#dpdSat').removeAttr("disabled");
                        $('#odbSat').removeAttr("disabled");
                        $('#avizo_pickup_sms').removeAttr("disabled");
                        $('#avizo_pickup_tel').removeAttr("disabled");
                        $('#avizo_delivery_sms').removeAttr("disabled");
                        $('#avizo_delivery_tel').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                    }
                });

                $('#dpd1200').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpdSat').attr("disabled", true);
                        $('#odbSat').attr("disabled", true);
                        $('#avizo_pickup_sms').attr("disabled", true);
                        $('#avizo_pickup_tel').attr("disabled", true);
                        $('#avizo_delivery_sms').attr("disabled", true);
                        $('#avizo_delivery_tel').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpdSat').removeAttr("disabled");
                        $('#odbSat').removeAttr("disabled");
                        $('#avizo_pickup_sms').removeAttr("disabled");
                        $('#avizo_pickup_tel').removeAttr("disabled");
                        $('#avizo_delivery_sms').removeAttr("disabled");
                        $('#avizo_delivery_tel').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                    }
                });

                $('#dpdSat').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                    }
                });

                $('#odbSat').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#dpdSat').attr("disabled", true);
                        $('#rod').attr("disabled", true);
                        $('#cod').attr("disabled", true);
                        $('#dpdSat').removeAttr("checked");
                        $('#ups_saver').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#dpdSat').removeAttr("disabled");
                        $('#rod').removeAttr("disabled");
                        $('#cod').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                        $('#self_pickup').removeAttr("disabled");
                    }
                });

                $('#rod').click(function() {
                    if ($(this).is(':checked')) {
                        $('#odbSat').attr("disabled", true);
                        $('#avizo_pickup_sms').attr("disabled", true);
                        $('#avizo_pickup_tel').attr("disabled", true);
                        $('#avizo_delivery_sms').attr("disabled", true);
                        $('#avizo_delivery_tel').attr("disabled", true);
                    } else {
                        $('#odbSat').removeAttr("disabled");
                        $('#avizo_pickup_sms').removeAttr("disabled");
                        $('#avizo_pickup_tel').removeAttr("disabled");
                        $('#avizo_delivery_sms').removeAttr("disabled");
                        $('#avizo_delivery_tel').removeAttr("disabled");
                    }
                });

                $('#avizo_pickup_sms').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#rod').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#rod').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                        $('#self_pickup').removeAttr("disabled");
                    }
                });

                $('#avizo_pickup_tel').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#rod').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#rod').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                        $('#self_pickup').removeAttr("disabled");
                    }
                });

                $('#avizo_delivery_sms').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#rod').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#rod').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                        $('#self_pickup').removeAttr("disabled");
                    }
                });

                $('#avizo_delivery_tel').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#rod').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#rod').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                        $('#self_pickup').removeAttr("disabled");
                    }
                });

                $('#ups_saver').click(function() {
                    if ($(this).is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#dpdSat').attr("disabled", true);
                        $('#odbSat').attr("disabled", true);
                        $('#avizo_pickup_sms').attr("disabled", true);
                        $('#avizo_pickup_tel').attr("disabled", true);
                        $('#avizo_delivery_sms').attr("disabled", true);
                        $('#avizo_delivery_tel').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                    } else {
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#dpdSat').removeAttr("disabled");
                        $('#odbSat').removeAttr("disabled");
                        $('#avizo_pickup_sms').removeAttr("disabled");
                        $('#avizo_pickup_tel').removeAttr("disabled");
                        $('#avizo_delivery_sms').removeAttr("disabled");
                        $('#avizo_delivery_tel').removeAttr("disabled");
                        $('#self_pickup').removeAttr("disabled");
                    }
                });

                $('#self_pickup').click(function() {
                    if ($(this).is(':checked')) {
                        $('#odbSat').attr("disabled", true);
                        $('#avizo_pickup_sms').attr("disabled", true);
                        $('#avizo_pickup_tel').attr("disabled", true);
                        $('#avizo_delivery_sms').attr("disabled", true);
                        $('#avizo_delivery_tel').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                    } else {
                        $('#odbSat').removeAttr("disabled");
                        $('#avizo_pickup_sms').removeAttr("disabled");
                        $('#avizo_pickup_tel').removeAttr("disabled");
                        $('#avizo_delivery_sms').removeAttr("disabled");
                        $('#avizo_delivery_tel').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                    }
                });

                $('#WysylkaInpost').click(function () {
                    if ($(this).is(':checked')) {
                        $('#PaczkomatyLista').slideDown();
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#odbSat').attr("disabled", true);
                        $('#dpdSat').attr("disabled", true);
                        $('#rod').attr("disabled", true);
                        $('#avizo_pickup_sms').attr("disabled", true);
                        $('#avizo_pickup_tel').attr("disabled", true);
                        $('#avizo_delivery_sms').attr("disabled", true);
                        $('#avizo_delivery_tel').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                    } else {
                        $('#PaczkomatyLista').slideUp();
                        $('#dpd0930').removeAttr("disabled");
                        $('#dpd1200').removeAttr("disabled");
                        $('#odbSat').removeAttr("disabled");
                        $('#dpdSat').removeAttr("disabled");
                        $('#rod').removeAttr("disabled");
                        $('#avizo_pickup_sms').removeAttr("disabled");
                        $('#avizo_pickup_tel').removeAttr("disabled");
                        $('#avizo_delivery_sms').removeAttr("disabled");
                        $('#avizo_delivery_tel').removeAttr("disabled");
                        $('#ups_saver').removeAttr("disabled");
                        $('#self_pickup').removeAttr("disabled");
                    }
                });

                if ($('#WysylkaInpost').is(':checked')) {
                        $('#dpd0930').attr("disabled", true);
                        $('#dpd1200').attr("disabled", true);
                        $('#odbSat').attr("disabled", true);
                        $('#dpdSat').attr("disabled", true);
                        $('#rod').attr("disabled", true);
                        $('#avizo_pickup_sms').attr("disabled", true);
                        $('#avizo_pickup_tel').attr("disabled", true);
                        $('#avizo_delivery_sms').attr("disabled", true);
                        $('#avizo_delivery_tel').attr("disabled", true);
                        $('#ups_saver').attr("disabled", true);
                        $('#self_pickup').attr("disabled", true);
                }

                // wycena paczki
                $('#form_wycen').click(function(){

                  var frm = $("#apiForm");
                  var response_text = $('#wystawianie');
                  var response_form = $('#wynik');
                  var dane = frm.serialize();
                  var daneTbl = frm.serializeArray();
                  var proceed = true;

                  response_text.hide();
     
                  if (proceed == true) {
                  
                    response_text.html('<img src="obrazki/_loader.gif">').show();

                    $.post('ajax/furgonetka_wycen_przesylke.php?tok=<?php echo Sesje::Token(); ?>', dane, function(data){
                      response_form.slideUp();
                      response_text.html(data);
                      $('#UtworzPrzesylke').show();
                    });
                  }

                  return false;
                });


            
            }); 
          //]]>
          </script>

            <?php
            if ( isset($_POST['id']) ) $_GET['id_poz'] = $_POST['id'];
            $zamowienie = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;

            ?>

            <form action="sprzedaz/zamowienia_wysylka_furgonetka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                  <input type="hidden" name="klient_id" value="<?php echo $zamowienie->klient['id']; ?>" />
                  <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
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
                                    <label>Rodzaj przesyłki:</label>
                                    <?php
                                    if ( isset($_POST['parcel_type']) ) {
                                        $domyslnie = $_POST['parcel_type'];
                                    } else {
                                        $domyslnie = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_RODZAJ_USLUGI'];
                                    }
                                    $tablica = array(
                                               array('id' => 'package', 'text' => 'Paczka'),
                                               array('id' => 'dox', 'text' => 'Koperta'),
                                               array('id' => 'pallette', 'text' => 'Paleta')
                                    );
                                    echo Funkcje::RozwijaneMenu('parcel_type', $tablica, $domyslnie, 'id="parcel_type" style="width:250px;"' ); 
                                    unset($tablica);
                                    ?>
                                </p>
                                
                                <p>
                                    <label class="required">Waga (kg):</label>
                                    <input type="text" size="20" name="weight" id="weight" value="<?php echo ( isset($_POST['weight']) ? $_POST['weight'] : $waga_produktow ); ?>" class="required" />
                                </p> 

                                <p>
                                    <label class="required">Wymiary (dł. x wys. x szer.):</label>
                                    <input type="text" size="10" name="width" id="width" value="<?php echo ( isset($_POST['width']) ? $_POST['width'] : $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYMIARY_DLUGOSC'] ); ?>" class="required" /> x
                                    <input type="text" size="10" name="height" id="height" value="<?php echo ( isset($_POST['height']) ? $_POST['height'] : $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYMIARY_WYSOKOSC'] ); ?>" class="required" /> x
                                    <input type="text" size="10" name="depth" id="depth" value="<?php echo ( isset($_POST['depth']) ? $_POST['depth'] : $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_WYMIARY_SZEROKOSC'] ); ?>" class="required" />
                                </p> 

                                <p>
                                    <label>Wartość:</label>
                                    <input type="text" size="20" name="value" id="value" value="<?php echo ( isset($_POST['value']) ? $_POST['value'] : '' ); ?>" class="toolTip" title="Podaj wartość przesyłki, która podlega ubezpieczeniu. " />
                                </p> 

                                <p>
                                    <label>Zawartość przesyłki:</label>
                                    <textarea cols="45" rows="2" name="description" ><?php echo ( isset($_POST['description']) ? $_POST['description'] : $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_ZAWARTOSC'] ); ?></textarea>
                                </p> 

                                <p id="RodzajOpakowania" <?php echo ( $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_RODZAJ_USLUGI'] == 'package' ? '' : 'style="display:none;"'); ?>>
                                    <label>Rodzaj opakowania:</label>
                                    <?php
                                    if ( isset($_POST['wrapping']) ) {
                                        $domyslnie = $_POST['wrapping'];
                                    } else {
                                        $domyslnie = $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_RODZAJ_OPAKOWANIA'];
                                    }
                                    $tablica = array(
                                               array('id' => '0', 'text' => 'Karton'),
                                               array('id' => '8', 'text' => 'Opakowanie firmowe przewoźnika'),
                                               array('id' => '1', 'text' => 'Kontener metalowy'),
                                               array('id' => '2', 'text' => 'Kontener drewniany'),
                                               array('id' => '3', 'text' => 'Folia'),
                                               array('id' => '4', 'text' => 'Guma'),
                                               array('id' => '5', 'text' => 'Stretch'),
                                               array('id' => '6', 'text' => 'Tektura falista'),
                                               array('id' => '7', 'text' => 'Inne')
                                    );
                                    echo Funkcje::RozwijaneMenu('wrapping', $tablica, $domyslnie, 'id="wrapping" style="width:250px;"' ); 
                                    unset($tablica);
                                    ?>
                                </p>

                                <p id="KsztaltOpakowania" <?php echo ( $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_RODZAJ_USLUGI'] == 'package' ? '' : 'style="display:none;"'); ?>>
                                    <label>Kształt:</label>
                                    <?php
                                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto('0,1', ( isset($_POST['shape']) ? $_POST['shape'] : $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_KSZTALT_OPAKOWANIA'] ), 'shape', '', 'standardowy,niestandardowy', '2' );
                                    ?>
                                </p> 

                                <p>
                                    <label>Liczba przesyłek:</label>
                                    <?php
                                    if ( isset($_POST['number_of_packages']) ) {
                                        $domyslnie = $_POST['number_of_packages'];
                                    } else {
                                        $domyslnie = '1';
                                    }
                                    $tablica = array(
                                               array('id' => '1', 'text' => '1'),
                                               array('id' => '2', 'text' => '2'),
                                               array('id' => '3', 'text' => '3'),
                                               array('id' => '4', 'text' => '4'),
                                               array('id' => '5', 'text' => '5'),
                                               array('id' => '6', 'text' => '6'),
                                               array('id' => '7', 'text' => '7'),
                                               array('id' => '8', 'text' => '8'),
                                               array('id' => '9', 'text' => '9'),
                                               array('id' => '10', 'text' => '10'),
                                    );
                                    echo Funkcje::RozwijaneMenu('number_of_packages', $tablica, $domyslnie, 'id="number_of_packages" style="width:50px;" class="toolTip" title="Wybierz liczbę jednakowych przesyłek do tego samego odbiorcy. Pamiętaj, że podana kwota pobrania określona jest dla 1 paczki."' ); 
                                    unset($tablica);
                                    ?>
                                </p>

                                <p>
                                    <label>Nr dok. sprzedaży:</label>
                                    <input type="text" size="30" name="user_reference_number" id="user_reference_number" value="<?php echo ( isset($_POST['user_reference_number']) ? $_POST['user_reference_number'] : 'Zamówienie numer: ' . $_GET['id_poz'] ); ?>" class="toolTip" title="Podaj numer dokumentu sprzedaży, który znajdzie się na etykiecie (opcjonalne)." />
                                </p> 

                                <p>
                                    <label>Wysyłka inPost:</label>
                                    <input id="WysylkaInpost" type="checkbox" name="WysylkaInpost" <?php echo ( isset($_POST['WysylkaInpost']) || ( !isset($_POST['WysylkaInpost']) && $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_KURIER'] == 'inpost') ? 'checked="checked"' : '' ); ?> />
                                <p>

                                <p id="PaczkomatyLista" <?php echo ( isset($_POST['WysylkaInpost']) || ( !isset($_POST['WysylkaInpost']) && $apiKurier->polaczenie['INTEGRACJA_FURGONETKA_KURIER'] == 'inpost') ? '' : 'style="display:none;"'); ?>>
                                    <label>Paczkomat podstawowy:</label>
                                    <?php 
                                    $domyslna = '';
                                    if ( $zamowienie->info['wysylka_info'] != '' ) {
                                        $danePaczkomatu = explode(' ', $zamowienie->info['wysylka_info']);
                                        $kodPaczkomatu = str_replace(',', '', $danePaczkomatu['1']);
                                        $domyslna = $kodPaczkomatu;
                                    }

                                    $ListaPaczkomatow = $apiKurier->doGetPaczkomaty();

                                    $tablicaPaczkomatow[] = array('id' => '0', 'text' => '--- wybierz z listy ---');
                                    foreach ( $ListaPaczkomatow->all->node as $paczkomat ) {
                                        $tablicaPaczkomatow[] = array('id' => $paczkomat->id, 'text' => $paczkomat->description);
                                    }
                                    echo Funkcje::RozwijaneMenu('integracja_furgonetka_paczkomat', $tablicaPaczkomatow, $domyslna, 'id="Paczkomaty" style="width:300px;"');
                                    unset($domyslna);
                                    ?>
                                </p>


                            </td>
                          </tr>
                        </table>

                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Usługi dodatkowe</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:8px;font-weight:normal;">

                              <p>
                                <label>Doręczenie do godziny 10:30:</label>
                                <input id="dpd0930" type="checkbox" name="dpd0930" <?php echo ( isset($_POST['dpd0930']) ? 'checked="checked"' : '' ); ?> />
                                <span><img src="obrazki/furgonetka/dpd_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Doręczenie do godziny 12:00:</label>
                                <input id="dpd1200" type="checkbox" name="dpd1200" <?php echo ( isset($_POST['dpd1200']) ? 'checked="checked"' : '' ); ?> />
                                <span><img src="obrazki/furgonetka/dpd_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Doręczenie przesyłki w sobotę:</label>
                                <input id="dpdSat" type="checkbox" name="dpdSat" <?php echo ( isset($_POST['dpdSat']) ? 'checked="checked"' : '' ); ?> <?php echo ( $waga_produktow > 50 ? 'disabled="disabled"' : '' ); ?> />
                                <span><img src="obrazki/furgonetka/dpd_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/kex_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Odbiór przesyłki w sobotę:</label>
                                <input id="odbSat" type="checkbox" name="odbSat" <?php echo ( isset($_POST['odbSat']) ? 'checked="checked"' : '' ); ?> />
                                <span><img src="obrazki/furgonetka/kex_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Dokumenty zwrotne:</label>
                                <input id="rod" type="checkbox" name="rod" <?php echo ( isset($_POST['rod']) ? 'checked="checked"' : '' ); ?> class="toolTip" title="Zaznacz, jeśli chcesz zamówić zwrot dokumentów dołączonych do przesyłki pierwotnej." />
                                <span><img src="obrazki/furgonetka/dpd_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/ups_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/fedex_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Pobranie przy doręczeniu:</label>
                                <input id="cod" type="checkbox" name="cod" <?php echo ( isset($_POST['cod']) ? 'checked="checked"' : '' ); ?> />
                                <span><img src="obrazki/furgonetka/dpd_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/ups_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/kex_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/fedex_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/xpress_mini.png" alt="" /></span>
                                <span><img src="obrazki/furgonetka/inpost_mini.png" alt="" /></span>
                              </p> 

                              <p id="pobranie_kwota" <?php echo ( isset($_POST['cod']) ? '' : 'style="display:none;"' ); ?>>
                                <label>Kwota pobrania:</label>
                                <input type="text" size="10" name="cod_kwota" id="cod_kwota" value="<?php echo ( isset($_POST['cod_kwota']) ? $_POST['cod_kwota'] : '' ); ?>" />
                              </p> 

                              <p id="pobranie_typ" <?php echo ( isset($_POST['cod']) ? '' : 'style="display:none;"' ); ?>>
                                <label>Usługa pobraniowa:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('0,1', ( isset($_POST['quick_transfer']) ? $_POST['quick_transfer'] : '0' ), 'quick_transfer', '', 'Standard,Express', '2' );
                                ?>
                              </p> 

                              <p>
                                <label>Awizacja SMS-owa odbioru:</label>
                                <input id="avizo_pickup_sms" type="checkbox" name="avizo_pickup_sms" <?php echo ( isset($_POST['avizo_pickup_sms']) ? 'checked="checked"' : '' ); ?> class="toolTip" title="Zaznacz, jeśli chcesz, aby kurier powiadomił nadawcę SMS-em o godzinie przyjazdu po przesyłkę." />
                                <span><img src="obrazki/furgonetka/kex_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Awizacja telefoniczna odbioru:</label>
                                <input id="avizo_pickup_tel" type="checkbox" name="avizo_pickup_tel" <?php echo ( isset($_POST['avizo_pickup_tel']) ? 'checked="checked"' : '' ); ?> class="toolTip" title="Zaznacz, jeśli chcesz, aby kurier zadzwonił do nadawcy z informacją o godzinie przyjazdu po przesyłkę." />
                                <span><img src="obrazki/furgonetka/kex_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Awizacja SMS-owa dostawy:</label>
                                <input id="avizo_delivery_sms" type="checkbox" name="avizo_delivery_sms" <?php echo ( isset($_POST['avizo_delivery_sms']) ? 'checked="checked"' : '' ); ?> class="toolTip" title="Zaznacz, jeśli chcesz, aby kurier powiadomił odbiorcę SMS-em o godzinie przyjazdu." />
                                <span><img src="obrazki/furgonetka/kex_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Awizacja telefoniczna dostawy:</label>
                                <input id="avizo_delivery_tel" type="checkbox" name="avizo_delivery_tel" <?php echo ( isset($_POST['avizo_delivery_tel']) ? 'checked="checked"' : '' ); ?> class="toolTip" title="Zaznacz, jeśli chcesz, aby kurier powiadomił odbiorcę SMS-em o godzinie przyjazdu." />
                                <span><img src="obrazki/furgonetka/kex_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>UPS Express Saver:</label>
                                <input id="ups_saver" type="checkbox" name="ups_saver" <?php echo ( isset($_POST['ups_saver']) ? 'checked="checked"' : '' ); ?> class="toolTip" title="Usługa UPS Express Saver gwarantuje dostarczenie przesyłki w następny dzień roboczy od jej nadania w godzinach 8.00 - 18.00." />
                                <span><img src="obrazki/furgonetka/ups_mini.png" alt="" /></span>
                              </p> 

                              <p>
                                <label>Nadanie w oddziale:</label>
                                <input id="self_pickup" type="checkbox" name="self_pickup" <?php echo ( isset($_POST['self_pickup']) ? 'checked="checked"' : '' ); ?> class="toolTip" title="Zaznacz, jeśli sam dostarczysz przesyłkę wraz z dokumentami do oddziału DPD. Paczka z tą usługą nie może być anulowana po zamówieniu." />
                                <span><img src="obrazki/furgonetka/dpd_mini.png" alt="" /></span>
                              </p> 
                            
                            </td>
                          </tr>
                        </table>

                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Wycena przesyłki</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:8px;font-weight:normal;">
                                
                              <div class="infoWycena">
                                  <div id="wystawianie" style="display:none;"></div>
                                  <div id="wynik" style="padding-bottom:20px;display:none;"></div>
                              </div>

                              <div class="przyciski_dolne">
                                <div id="przycisk_wycen" style="float:left"><input id="form_wycen" type="submit" class="przyciskNon" value="Wyceń przesyłkę" /></div>
                              </div>

                            </td>
                          </tr>
                        </table>
                    
                    </div>

                    <br />

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
                                <label>Czy odbiorcą jest firma:</label>
                                <?php
                                $zaznaczony = '0';
                                if ( $zamowienie->dostawa['firma'] != '' ) {
                                  $zaznaczony = '1';
                                }
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('1,0', ( isset($_POST['odbiorca_firma']) ? $_POST['odbiorca_firma'] : $zaznaczony ), 'odbiorca_firma', '', 'tak,nie', '2' );
                                unset($zaznaczony);
                                ?>
                              </p> 
                              <p>
                                <label>Nazwa firmy:</label>
                                <input type="text" size="30" name="adresat_firma" id="adresat_firma" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['firma']: $zamowienie->dostawa['nazwa']); ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Nazwisko i imię:</label>
                                <input type="text" size="30" name="adresat_nazwisko_i_imie" id="adresat_nazwisko_i_imie" value="<?php echo preg_replace('!\s+!', ' ', $zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Ulica  i numer domu:</label>
                                <input type="text" size="30" name="adresat_ulica" id="adresat_ulica" value="<?php echo $zamowienie->dostawa['ulica']; ?>"  class="klient" />
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
                                <label>Kraj:</label>
                                <?php 
                                $domyslnie = $apiKurier->getIsoCountry($zamowienie->dostawa['kraj']); 
                                $tablicaPanstw = $apiKurier->getCountrySelect($zamowienie->dostawa['kraj']); 
                                echo Funkcje::RozwijaneMenu('receiver_country_code', $tablicaPanstw, $domyslnie, 'id="receiver_country_code" class="klient" style="width:210px;"' ); 

                                unset($tablicaPanstw);
                                ?>
                              </p> 
                              <p>
                                <label>Numer telefonu:</label>
                                <?php
                                $numer_telefonu = preg_replace('/\W/','',$zamowienie->klient['telefon']);
                                if ( strlen($numer_telefonu) > 9 ) {
                                    $numer_telefonu = str_replace('48', '', $numer_telefonu);
                                }
                                ?>
                                <input type="text" size="30" name="adresat_telefon" id="adresat_telefon" value="<?php echo $numer_telefonu; ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Adres email:</label>
                                <input type="text" size="30" name="adresat_email" id="adresat_email" value="<?php echo $zamowienie->klient['adres_email']; ?>" class="klient" />
                              </p> 
                            </td>
                          </tr>
                        </table>
                        
                    </div>
                    
                  </td>
                </tr>
              </table>

              <div class="przyciski_dolne">
                <input type="submit" id="UtworzPrzesylke" class="przyciskNon" value="Utwórz przesyłkę" style="display:none;" />
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
