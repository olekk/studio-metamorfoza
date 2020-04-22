<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $komunikat = '';
    $api = 'Siodemka';
    $apiKurier = new SiodemkaApi();
    $weight_total = 0;
    $parcel = array();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $wysylkaZamowienie = array();
      $nazwisko_i_imie   = explode(' ', $_POST['adresat_nazwisko_i_imie']);
      $adres_odbiorcy    = Funkcje::PrzeksztalcAdresDomu($_POST['adresat_dom']);

      $wysylkaZamowienie['przesylka']['rodzajPrzesylki'] = $_POST['siodemka_rodzaj_przesylki'];
      $wysylkaZamowienie['przesylka']['placi']           = $_POST['siodemka_platnik'];
      $wysylkaZamowienie['przesylka']['formaPlatnosci']  = $_POST['siodemka_forma_platnosci'];

      $wysylkaZamowienie['przesylka']['nadawca']['numer']       = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_KLIENT_ID'];
      $wysylkaZamowienie['przesylka']['nadawca']['telKontakt']  = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_NADAWCA_TELEFON'];
      $wysylkaZamowienie['przesylka']['nadawca']['emailKontakt']= $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_NADAWCA_EMAIL'];

      $wysylkaZamowienie['przesylka']['odbiorca']['czyFirma']   = $_POST['siodemka_odbiorca_firma'];
      $wysylkaZamowienie['przesylka']['odbiorca']['nazwa']      = $_POST['adresat_firma'];
      $wysylkaZamowienie['przesylka']['odbiorca']['nip']        = $_POST['adresat_nip'];
      $wysylkaZamowienie['przesylka']['odbiorca']['nazwisko']   = $nazwisko_i_imie['1'];
      $wysylkaZamowienie['przesylka']['odbiorca']['imie']       = $nazwisko_i_imie['0'];
      $wysylkaZamowienie['przesylka']['odbiorca']['kodKraju']   = $_POST['kod_kraju_iso'];
      $wysylkaZamowienie['przesylka']['odbiorca']['kod']        = $_POST['adresat_kod_pocztowy'];
      $wysylkaZamowienie['przesylka']['odbiorca']['miasto']     = $_POST['adresat_miasto'];
      $wysylkaZamowienie['przesylka']['odbiorca']['ulica']      = $_POST['adresat_ulica'];
      $wysylkaZamowienie['przesylka']['odbiorca']['nrDom']      = $adres_odbiorcy['dom'];
      $wysylkaZamowienie['przesylka']['odbiorca']['nrLokal']    = $adres_odbiorcy['mieszkanie'];
      $wysylkaZamowienie['przesylka']['odbiorca']['telKontakt'] = $_POST['adresat_telefon'];
      $wysylkaZamowienie['przesylka']['odbiorca']['emailKontakt']= $_POST['adresat_mail'];

      $wysylkaZamowienie['przesylka']['paczki']['paczka'] = array();
      for ( $i = 0, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
        $parcel['typ']       = $_POST['parcel']['typ'][$i];
        $parcel['gab1']       = ceil($_POST['parcel']['dlugosc'][$i]);
        $parcel['gab2']       = ceil($_POST['parcel']['szerokosc'][$i]);
        $parcel['gab3']       = ceil($_POST['parcel']['wysokosc'][$i]);
        $parcel['waga']       = ceil($_POST['parcel']['waga'][$i]);
        $parcel['ksztalt']    = ( isset($_POST['parcel']['niestandard'][$i]) ? $_POST['parcel']['niestandard'][$i] : '0' );
        $weight_total += $_POST['parcel']['waga'][$i];
        array_push($wysylkaZamowienie['przesylka']['paczki']['paczka'], $parcel);
      }

      $wysylkaZamowienie['przesylka']['uslugi']['zkld']             = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA'];
      $wysylkaZamowienie['przesylka']['uslugi']['zd']               = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE'];

      if ( $_POST['ubezpieczenie'] != '' && $_POST['ubezpieczenie'] > 0 ) {
        $wysylkaZamowienie['przesylka']['uslugi']['ubezpieczenie']['kwotaUbezpieczenia'] = $_POST['ubezpieczenie'];
        $wysylkaZamowienie['przesylka']['uslugi']['ubezpieczenie']['opisZawartosci']     = $_POST['ubezpieczenie_opis'];
      }

      if ( isset($_POST['pobranie']) && $_POST['siodemka_pobranie'] > 0 ) {
        $wysylkaZamowienie['przesylka']['uslugi']['pobranie']['kwotaPobrania'] = $_POST['siodemka_pobranie'];
        $wysylkaZamowienie['przesylka']['uslugi']['pobranie']['formaPobrania'] = $_POST['siodemka_zwrot_pobrania'];
        $wysylkaZamowienie['przesylka']['uslugi']['pobranie']['nrKonta']       = preg_replace('/\D/', '', $_POST['siodemka_numer_konta']);
      }

      $wysylkaZamowienie['przesylka']['potwierdzenieNadania']['dataNadania']   = date("Y-m-d H:i");
      $wysylkaZamowienie['przesylka']['potwierdzenieNadania']['numerKuriera']  = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_KURIER_ID'];
      $wysylkaZamowienie['przesylka']['potwierdzenieNadania']['podpisNadawcy'] = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_POTWIERDZENIE_PODPIS'];

      $wysylkaZamowienie['przesylka']['uslugi']['awizacjaTelefoniczna'] = '0';
      $wysylkaZamowienie['przesylka']['uslugi']['potwNadEmail']         = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL'];
      $wysylkaZamowienie['przesylka']['uslugi']['potwDostEmail']        = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL'];
      $wysylkaZamowienie['przesylka']['uslugi']['potwDostSMS']          = $_POST['siodemka_potwierdzenie_dostarczenia_sms'];
      $wysylkaZamowienie['przesylka']['uslugi']['skladowanie']          = '0';
      $wysylkaZamowienie['przesylka']['uslugi']['nadOdbPKP']            = '0';
      $wysylkaZamowienie['przesylka']['uslugi']['odbNadgodziny']        = ( isset($_POST['odbNadgodziny']) ? $_POST['odbNadgodziny'] : '0' );
      $wysylkaZamowienie['przesylka']['uslugi']['odbWlas']              = '0';
      $wysylkaZamowienie['przesylka']['uslugi']['palNextDay']           = '0';
      $wysylkaZamowienie['przesylka']['uslugi']['osobaFiz']             = $_POST['siodemka_doreczenie_firma'];
      $wysylkaZamowienie['przesylka']['uslugi']['market']               = '0';
      $wysylkaZamowienie['przesylka']['uslugi']['zastrzDorNaGodz']      = $_POST['zastrzDorNaGodz'];
      $wysylkaZamowienie['przesylka']['uslugi']['zastrzDorNaDzien']     = $_POST['zastrzDorNaDzien'];


      $wysylkaZamowienie['przesylka']['uwagi']           = $_POST['siodemka_uwagi'];

      $wysylkaZamowienie['klucz']                        = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_API_PIN'];

      $noweZamowienie = $apiKurier->listNadanie( $wysylkaZamowienie );

      if ( is_object($noweZamowienie) ) {
        $paczka = $noweZamowienie->result->nrPrzesylki;

        //$status = $apiKurier->statusyPrzesylki( array('numerListu' => $paczka, 'czyOstatni'=>'1', 'klucz' => $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_API_PIN']) );

        $pola = array(
                array('orders_id',$filtr->process($_POST["id"])),
                array('orders_shipping_type',$api),
                array('orders_shipping_number',$paczka),
                array('orders_shipping_weight',$weight_total),
                array('orders_parcels_quantity',count($_POST['parcel']['dlugosc'])),
                array('orders_shipping_status','1'),
                array('orders_shipping_date_created', 'now()'),
                array('orders_shipping_date_modified', 'now()'),
                array('orders_shipping_comments', ''),
        );

        $db->insert_query('orders_shipping' , $pola);
        unset($pola);

        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));

      } else {
        $komunikat = $noweZamowienie;
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

          <?php
          $tablica_wysylek = $apiKurier->siodemka_post_parcel_array(false);

          $tekst = '<select style="width:100px;" name="parcel[typ][]" class="valid">';
          foreach ( $tablica_wysylek as $produkt ) {
            $tekst .= '<option value="'.$produkt['id'].'">'.$produkt['text'].'</option>';
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
                $(".item-row:last").after('<tr class="item-row"><td style="text-align:center"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" title="Usuń wiersz">usuń</a></div></td><td class="paczka" style="padding-top:10px; padding-bottom:8px;"><?php echo $tekst; ?></td><td class="paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td><td class="paczka"><input type="text" value="" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td><td class="paczka"><input type="text" value="" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td><td class="paczka"><input type="text" value="" size="4" name="parcel[waga][]" class="kropkaPusta required" /></td><td class="paczka"><input type="checkbox" value="1" name="parcel[niestandard][]" id="niestandard" /></td></tr>');
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
                waga         : { digits: true }
              }
            });

            $('#pobranie').change(function() {
                $("#siodemka_pobranie").val(($(this).is(':checked')) ? $("#wartosc_zamowienia_val").val() : "");
                if ( $(this).is(':checked') ) {
                    $("#PobranieAkapit").show();
                } else {
                    $("#PobranieAkapit").hide();
                }
            });

          });
          //]]>
          </script>

            <?php
            $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
            $waga_produktow = $zamowienie->waga_produktow;
            $wymiary        = array();

            $wysylki        = $apiKurier->produkty;

            $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
            $wymiary['0'] = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_WYMIARY_DLUGOSC'];
            $wymiary['1'] = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_WYMIARY_SZEROKOSC'];
            $wymiary['2'] = $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_WYMIARY_WYSOKOSC'];

            ?>


            <form action="sprzedaz/zamowienia_wysylka_siodemka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform">

              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                  <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
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
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('K,Z,L', $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI'], 'siodemka_rodzaj_przesylki', '', 'krajowa,zagraniczna,lokalna', '2');
                                ?>
                              </p> 
                              <p>
                                <label class="required">Kod kraju:</label>
                                <input type="text" size="20" name="kod_kraju_iso" id="kod_kraju_iso" value="<?php echo ( isset($_POST['kod_kraju_iso']) ? $_POST['kod_kraju_iso'] : 'PL' ); ?>" class="required toolTipText" title="dwuliterowy kod kraju" />
                              </p> 
                              <p>
                                <label>Kto płaci za przesyłkę:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('1,2,3', ( isset($_POST['siodemka_platnik']) ? $_POST['siodemka_platnik'] : $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_PLATNIK'] ), 'siodemka_platnik', '', 'nadawca,odbiorca,trzeci płatnik', '2' );
                                ?>
                              </p> 
                              <p>
                                <label>Forma płatności:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('G,P', ( isset($_POST['siodemka_forma_platnosci']) ? $_POST['siodemka_forma_platnosci'] : $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI'] ), 'siodemka_forma_platnosci', '', 'gotówka,przelew', '2' );
                                ?>
                              </p> 
                              <p>
                                <label>Potw. dost. przesyłki SMS:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('1,0', ( isset($_POST['siodemka_potwierdzenie_dostarczenia_sms']) ? $_POST['siodemka_potwierdzenie_dostarczenia_sms'] : $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS'] ), 'siodemka_potwierdzenie_dostarczenia_sms', '', 'tak,nie', '2' );
                                ?>
                              </p> 
                              <p>
                                <label>Uwagi [max. 200 znaków]:</label>
                                <textarea cols="45" rows="2" name="siodemka_uwagi" onkeyup="licznik_znakow(this,'iloscZnakow',200)" ><?php echo ( isset($_POST['siodemka_uwagi']) ? $_POST['siodemka_uwagi'] : 'Zamówienie numer: ' . $_GET['id_poz'] ); ?></textarea>
                              </p> 
                              <p>
                                <label></label>
                                <span style="display:inline-block; margin:0px 0px 8px 4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakow">200</span></span>
                              </p>
                            </td>
                          </tr>
                        </table>
                    </div>

                    <br />

                    <div class="obramowanie_tabeli">
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Informacje o pobraniu</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:0px;font-weight:normal;">
                              <p>
                                <label>Pobranie:</label>
                                <input type="checkbox" value="1" name="pobranie" id="pobranie" />
                              </p> 
                            </td>
                          </tr>

                          <tr id="PobranieAkapit" <?php echo ( isset($_POST['pobranie']) ? '' : 'style="display:none"' ); ?>>
                            <td style="padding-top:0px; padding-bottom:8px;font-weight:normal;">
                              <p>
                                <label>Kwota pobrania [PLN]:</label>
                                <input type="text" size="20" name="siodemka_pobranie" id="siodemka_pobranie" value="<?php echo ( isset($_POST['siodemka_pobranie']) ? $_POST['siodemka_pobranie'] : '' ); ?>" />
                              </p> 
                              <p>
                                <label>Forma zwrotu pobrania:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('P,B,N', ( isset($_POST['siodemka_zwrot_pobrania']) ? $_POST['siodemka_zwrot_pobrania'] : $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA'] ), 'siodemka_zwrot_pobrania', '', 'przekaz pocztowy,przelew bankowy,pobranie NextDay', '2' );
                                ?>
                              </p> 
                              <p>
                                <label>Numer konta w formacie IBAN:</label>
                                <input type="text" size="46" name="siodemka_numer_konta" id="siodemka_numer_konta" value="<?php echo ( isset($_POST['siodemka_numer_konta']) ? $_POST['siodemka_numer_konta'] : $apiKurier->polaczenie['INTEGRACJA_SIODEMKA_NUMER_KONTA'] ); ?>" />
                              </p> 
                            </td>
                          </tr>
                        </table>
                    </div>
                    
                    <br />

                    <div class="obramowanie_tabeli">
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Informacje o ubezpieczeniu</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:8px;font-weight:normal;">
                              <p>
                                <label>Wartość ubezpieczenia [PLN]:</label>
                                <input type="text" size="20" name="ubezpieczenie" id="ubezpieczenie" value="<?php echo ( isset($_POST['ubezpieczenie']) ? $_POST['ubezpieczenie'] : INTEGRACJA_SIODEMKA_KWOTA_UBEZPIECZENIA ); ?>" />
                              </p> 
                              <p>
                                <label>Opis zawartości przesyłki:</label>
                                <textarea cols="45" rows="2" name="ubezpieczenie_opis" class="toolTipText" title="Pole wymagane, jeżeli wpisano wartość ubezpieczenia" ><?php echo ( isset($_POST['ubezpieczenie_opis']) ? $_POST['ubezpieczenie_opis'] : INTEGRACJA_SIODEMKA_ZAWARTOSC ); ?></textarea>
                              </p> 
                            </td>
                          </tr>
                        </table>
                    </div>
                    
                    <br />

                    <div class="obramowanie_tabeli">
                        <table class="listing_tbl">
                          <tr class="div_naglowek">
                            <td>Informacje pozostałe</td>
                          </tr>
                          <tr>
                            <td style="padding-top:8px; padding-bottom:8px;font-weight:normal;">
                              <p>
                                <label>Godziny niestandardowe:</label>
                                <input type="checkbox" value="1" name="OdbNadgodziny" class="toolTipText" title="podjęcie przesyłki w godzinach niestandardowych" />
                              </p> 
                              <p>
                                <label>Doręczenie w dzień wolny:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('B,N,S', ( isset($_POST['zastrzDorNaDzien']) ? $_POST['zastrzDorNaDzien'] : 'B' ), 'zastrzDorNaDzien', 'zastrzeżenie doręczenia przesyłki na dzień wolny', 'brak,niedziela,sobota', '2' );
                                ?>
                              </p> 
                              <p>
                                <label>Doręczenie na  godzinę:</label>
                                <?php
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('0,10,12', ( isset($_POST['zastrzDorNaGodz']) ? $_POST['zastrzDorNaGodz'] : '0' ), 'zastrzDorNaGodz', 'zastrzeżenie doręczenia na godzinę : brak usługi, Siódemka NextDay 10, Siódemka NextDay 12', 'brak,NextDay 10,NextDay 12', '2' );
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
                    <td colspan="10">Informacje o paczkach</td>
                  </tr>
                  <tr>
                    <td style="width:50px"></td>
                    <td class="paczka" style="padding-top:8px;">Rodzaj paczki</td>
                    <td class="paczka">Długość [cm]</td>
                    <td class="paczka">Szerokość [cm]</td>
                    <td class="paczka">Wysokość [cm]</td>
                    <td class="paczka">Waga [kg]</td>
                    <td class="paczka">Niestand.</td>
                  </tr>

                  <tr class="item-row">
                    <td style="text-align:right"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" ></a></div></td>
                    <td class="paczka" style="padding-top:10px; padding-bottom:8px;">
                      <?php
                      $tablica = $apiKurier->siodemka_post_parcel_array(false);
                      echo Funkcje::RozwijaneMenu('parcel[typ][]', $tablica, '', 'style="width:100px;"');
                      unset($tablica);
                      ?>
                    </td>
                    <td class="paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc']['0']) ? $_POST['parcel']['dlugosc']['0'] : $wymiary['0'] ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                    <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc']['0']) ? $_POST['parcel']['szerokosc']['0'] : $wymiary['1'] ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                    <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc']['0']) ? $_POST['parcel']['wysokosc']['0'] : $wymiary['2'] ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                    <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga']['0']) ? $_POST['parcel']['waga']['0'] : ceil($waga_produktow) ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
                    <td class="paczka"><input type="checkbox" value="1" name="parcel[niestandard][]" id="niestandard" /></td>
                  </tr>

                  <?php
                  if ( isset($_POST['parcel']) && count($_POST['parcel']['dlugosc']) > 1 ) {
                    for ( $i = 1, $c = count($_POST['parcel']['dlugosc']); $i < $c; $i++ ) {
                      ?>
                      <tr class="item-row">
                        <td style="text-align:right"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" title="Usuń wiersz">usuń</a></div></td>
                        <td class="paczka" style="padding-top:10px; padding-bottom:8px;">
                          <?php
                          $tablica = $apiKurier->siodemka_post_parcel_array(false);
                          echo Funkcje::RozwijaneMenu('parcel[typ][]', $tablica, ( isset($_POST['parcel']['typ'][$i]) ? $_POST['parcel']['typ'][$i] : '' ), 'style="width:100px;"');
                          unset($tablica);
                          ?>
                        </td>
                        <td class="paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="<?php echo ( isset($_POST['parcel']['dlugosc'][$i]) ? $_POST['parcel']['dlugosc'][$i] : '' ); ?>" size="8" name="parcel[dlugosc][]" class="kropkaPustaZero required" /></td>
                        <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['szerokosc'][$i]) ? $_POST['parcel']['szerokosc'][$i] : '' ); ?>" size="8" name="parcel[szerokosc][]" class="kropkaPustaZero required" /></td>
                        <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['wysokosc'][$i]) ? $_POST['parcel']['wysokosc'][$i] : '' ); ?>" size="8" name="parcel[wysokosc][]" class="kropkaPustaZero required" /></td>
                        <td class="paczka"><input type="text" value="<?php echo ( isset($_POST['parcel']['waga'][$i]) ? $_POST['parcel']['waga'][$i] : '' ); ?>" size="4" name="parcel[waga][]" class="kropkaPustaZero required" /></td>
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
                            <td style="padding-top:8px; padding-bottom:8px;font-weight:normal;">
                              <p>
                                <label>Czy odbiorcą jest firma:</label>
                                <?php
                                $zaznaczony = '0';
                                if ( $zamowienie->dostawa['firma'] != '' ) {
                                  $zaznaczony = '1';
                                }
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('1,0', ( isset($_POST['siodemka_odbiorca_firma']) ? $_POST['siodemka_odbiorca_firma'] : $zaznaczony ), 'siodemka_odbiorca_firma', '', 'tak,nie', '2' );
                                unset($zaznaczony);
                                ?>
                              </p> 
                              <p>
                                <label>Doręczenie przesyłki do osoby fizycznej:</label>
                                <?php
                                $zaznaczony = '1';
                                if ( $zamowienie->dostawa['firma'] != '' ) {
                                  $zaznaczony = '0';
                                }
                                echo Konfiguracja::Dopuszczalne_Wartosci_Auto('1,0', ( isset($_POST['siodemka_doreczenie_firma']) ? $_POST['siodemka_doreczenie_firma'] : $zaznaczony ), 'siodemka_doreczenie_firma', '', 'tak,nie', '2' );
                                ?>
                              </p> 
                              <p>
                                <label>Nazwa firmy:</label>
                                <input type="text" size="30" name="adresat_firma" id="adresat_firma" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['firma'] : '---'); ?>" class="klient" />
                              </p> 
                              <p>
                                <label>NIP:</label>
                                <input type="text" size="30" name="adresat_nip" id="adresat_nip" value="<?php echo ( $zamowienie->platnik['nip'] != '' ? $zamowienie->platnik['nip'] : '---'); ?>" class="klient" />
                              </p> 
                              <p>
                                <label>Nazwisko i imię:</label>
                                <input type="text" size="30" name="adresat_nazwisko_i_imie" id="adresat_nazwisko_i_imie" value="<?php echo preg_replace('!\s+!', ' ', $zamowienie->dostawa['nazwa']); ?>"  class="klient" />
                              </p> 
                              <p>
                                <label>Ulica:</label>
                                <input type="text" size="30" name="adresat_ulica" id="adresat_ulica" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
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
                                <input type="text" size="30" name="adresat_mail" id="adresat_mail" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
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
