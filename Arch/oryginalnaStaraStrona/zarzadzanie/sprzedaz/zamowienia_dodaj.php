<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $i18n = new Translator($db, '1');
    $GLOBALS['tlumacz'] = $i18n->tlumacz( array('WYSYLKI','PODSUMOWANIE_ZAMOWIENIA','PLATNOSCI'), null, true );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $zapytanie = "select c.customers_id, c.language_id, c.customers_status, c.customers_dod_info, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_guest_account, c.customers_email_address, a.entry_company, a.entry_nip, a.entry_pesel, a.entry_street_address, a.entry_postcode, a.entry_city, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_groups_id, c.customers_discount, c.customers_default_address_id, c.customers_nick from customers c left join address_book a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . $filtr->process((int)$_POST['id']) . "'";

      $sql = $db->open_query($zapytanie);

      $info = $sql->fetch_assoc();

      $pola_info = array(
              array('invoice_dokument',$filtr->process($_POST['dokument'])),
              array('customers_id',$filtr->process($_POST['id'])),
              array('customers_name',$filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko'])),
              array('customers_company',$info['entry_company']),
              array('customers_nip',$info['entry_nip']),
              array('customers_pesel',$info['entry_pesel']),
              array('customers_street_address',$info['entry_street_address']),
              array('customers_city',$info['entry_city']),
              array('customers_postcode',$info['entry_postcode']),
              array('customers_state',( $info['entry_zone_id'] != '' ? Klienci::pokazNazweWojewodztwa($info['entry_zone_id']) : '' )),
              array('customers_country',Klienci::pokazNazwePanstwa($info['entry_country_id'])),
              array('customers_telephone',( isset($_POST['telefon']) && $_POST['telefon'] != '' ? $filtr->process($_POST['telefon']) : '' ) ),
              array('customers_email_address',$filtr->process($_POST['email'])),
              array('customers_dummy_account',$info['customers_guest_account']), 
              array('last_modified','now()'),
              array('date_purchased',date('Y-m-d h:i:s', strtotime($filtr->process($_POST['data_zamowienia'])))),
              array('orders_status',$filtr->process($_POST['status'])),
              array('orders_source','4'),
              array('currency',$_SESSION['domyslna_waluta']['kod']),
              array('currency_value',$_SESSION['domyslna_waluta']['przelicznik']),
              array('payment_method',$filtr->process($_POST['platnosc'])),
              array('shipping_module',$filtr->process($_POST['dostawa'])),
              array('shipping_info', ( isset($_POST['lokalizacja']) && $_POST['lokalizacja'] != '0' ? $filtr->process($_POST['lokalizacja']) : '' ) ));

      if ( $_POST['adres_dostawy'] == '1' ) {
        $pola_dostawa = array(
                  array('delivery_name',$filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko'])),
                  array('delivery_company',( isset($_POST['nazwa_firmy']) && $_POST['nazwa_firmy'] != '' ? $filtr->process($_POST['nazwa_firmy']) : '' ) ),
                  array('delivery_nip',( isset($_POST['nip_firmy']) && $_POST['nip_firmy'] != '' ? $filtr->process($_POST['nip_firmy']) : '' ) ),
                  array('delivery_pesel',( isset($_POST['pesel']) && $_POST['pesel'] != '' ? $filtr->process($_POST['pesel']) : '' ) ),
                  array('delivery_street_address',$filtr->process($_POST['ulica'])),
                  array('delivery_city',$filtr->process($_POST['miasto'])),
                  array('delivery_postcode',$filtr->process($_POST['kod_pocztowy'])),
                  array('delivery_state',$filtr->process($_POST['wojewodztwo'])),
                  array('delivery_country',$filtr->process($_POST['panstwo'])));
                  
      } else {
        $pola_dostawa = array(
                  array('delivery_name',$filtr->process($_POST['dostawa_imie']) . ' ' . $filtr->process($_POST['dostawa_nazwisko'])),
                  array('delivery_company',( isset($_POST['dostawa_nazwa_firmy']) && $_POST['dostawa_nazwa_firmy'] != '' ? $filtr->process($_POST['dostawa_nazwa_firmy']) : '' ) ),
                  array('delivery_nip',( isset($_POST['dostawa_nip_firmy']) && $_POST['dostawa_nip_firmy'] != '' ? $filtr->process($_POST['dostawa_nip_firmy']) : '' ) ),
                  array('delivery_pesel',( isset($_POST['dostawa_pesel']) && $_POST['dostawa_pesel'] != '' ? $filtr->process($_POST['dostawa_pesel']) : '' ) ),
                  array('delivery_street_address',$filtr->process($_POST['dostawa_ulica'])),
                  array('delivery_city',$filtr->process($_POST['dostawa_miasto'])),
                  array('delivery_postcode',$filtr->process($_POST['dostawa_kod_pocztowy'])),
                  array('delivery_state',$filtr->process($_POST['dostawa_wojewodztwo'])),
                  array('delivery_country',$filtr->process($_POST['dostawa_panstwo'])));
                  
      }
      $pola_platnik = array(
                array('billing_name',$filtr->process($_POST['imie']) . ' ' . $filtr->process($_POST['nazwisko'])),
                array('billing_company',( isset($_POST['nazwa_firmy']) && $_POST['nazwa_firmy'] != '' ? $filtr->process($_POST['nazwa_firmy']) : '' ) ),
                array('billing_nip',( isset($_POST['nip_firmy']) && $_POST['nip_firmy'] != '' ? $filtr->process($_POST['nip_firmy']) : '' ) ),
                array('billing_pesel',( isset($_POST['pesel']) && $_POST['pesel'] != '' ? $filtr->process($_POST['pesel']) : '' ) ),
                array('billing_street_address',$filtr->process($_POST['ulica'])),
                array('billing_city',$filtr->process($_POST['miasto'])),
                array('billing_postcode',$filtr->process($_POST['kod_pocztowy'])),
                array('billing_state',$filtr->process($_POST['wojewodztwo'])),
                array('billing_country',$filtr->process($_POST['panstwo'])));

      $pola = Array();
      $pola = array_merge( $pola_info, $pola_dostawa, $pola_platnik );

      $db->insert_query('orders' , $pola);
      $id_dodanej_pozycji = $db->last_id_query();
      unset($pola);

      //
      $pola = array(
              array('orders_id ',(int)$id_dodanej_pozycji),
              array('orders_status_id',$filtr->process($_POST['status'])),
              array('date_added','now()'),
              array('customer_notified ','0'),
              array('customer_notified_sms','0'),
              array('comments',$filtr->process($_POST['komentarz'])));

      $db->insert_query('orders_status_history' , $pola);
      unset($pola);

      $zamowienie = new Zamowienie($id_dodanej_pozycji);
      $suma = new SumaZamowienia();
      $tablica_modulow = $suma->przetwarzaj_moduly();
      
      foreach ( $tablica_modulow as $podsumowanie ) {

        $tekst = $waluty->FormatujCene($podsumowanie['wartosc']);

        $pola = array(
                  array('orders_id',(int)$id_dodanej_pozycji),
                  array('title', $podsumowanie['text'] ),
                  array('text', $tekst ),
                  array('value', $podsumowanie['wartosc'] ),
                  array('prefix', $podsumowanie['prefix'] ),
                  array('class', $podsumowanie['klasa'] ),
                  array('sort_order', $podsumowanie['sortowanie'] ));
                  
        if ( isset($podsumowanie['vat_id']) && isset($podsumowanie['vat_stawka']) ) {
            //
            $pola[] = array('tax',$podsumowanie['vat_stawka']);
            $pola[] = array('tax_class_id',$podsumowanie['vat_id']);
            //
        }                     

        $db->insert_query('orders_total' , $pola);
        
      }
      
      unset($_SESSION['koszyk']);

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.$id_dodanej_pozycji.'&klient_id='.(int)$_POST["id"].'&zakladka=2');
      
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {

            $("#zamowieniaForm").validate({
              rules: {
                imie: {
                  required: true
                },
                nazwisko: {
                  required: true
                },
                email: {
                  required: true
                },
                nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik; }},
                nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik;}},
                ulica: {
                  required: true
                },
                kod_pocztowy: {
                  required: true
                },
                miasto: {
                  required: true
                },
                panstwo: {
                  required: true
                },
                dostawa_ulica: {required: function() {var wynik = true; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik; }},
                dostawa_kod_pocztowy: {required: function() {var wynik = true; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik; }},
                dostawa_miasto: {required: function() {var wynik = true; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik; }},
                dostawa_panstwo: {required: function() {var wynik = true; if ( $("input[name='adres_dostawy']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik; }}
              }
            });

          });
          //]]>
          </script>

          <!-- Skrypt do autouzupelniania -->             
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
          $.AutoUzupelnienie( 'panstwo', 'Podpowiedzi', 'ajax/autouzupelnienie_kraje.php', 50, 400 );
          $.AutoUzupelnienie( 'wojewodztwo', 'Podpowiedzi', 'ajax/autouzupelnienie_wojewodztwa.php', 50, 400 );
          $.AutoUzupelnienie( 'dostawa_panstwo', 'Podpowiedzi', 'ajax/autouzupelnienie_kraje.php', 50, 400 );
          $.AutoUzupelnienie( 'dostawa_wojewodztwo', 'Podpowiedzi', 'ajax/autouzupelnienie_wojewodztwa.php', 50, 400 );
          });

          $(document).ready(function(){
              $("#selection").change( function() {
              $("#lokalizacje").show();
              $("#selectionresult").html('<img src="obrazki/_loader_small.gif">');
              $.ajax({
                  type: "POST",
                  data: "data=" + $(this).val(),
                  url: "ajax/wybor_lokalizacji_dostawy.php",
                  success: function(msg){
                    if (msg != '') { 
                        $("#lokalizacje").slideDown(); 
                        $("#selectionresult").html(msg).show(); 
                    } else { 
                        $("#selectionresult").html(''); 
                        $("#lokalizacje").slideUp(); 
                    }
                  }
              });
              });
              
              $('.inputKlienta').click( function() {
                  $('#dodajZamowienie').fadeIn('fast');
              });    

              $('#dodajKlienta').click(function() {
                 $('#dodawanieKlienta').slideDown();
                 $('#dodajKlienta').slideUp();
              })
              
              pokazChmurki();
          });
          
          function fraza_klienci() { 
              //
              $('#dodajZamowienie').hide();
              //
              $('#wybierz_klienta').html('<img src="obrazki/_loader_small.gif">');
              $.get("ajax/lista_klientow.php", 
                  { fraza: $('#szukany').val(), tok: $('#tok').val() },
                  function(data) { 
                      $('#wybierz_klienta').css('display','none');
                      $('#wybierz_klienta').html(data);
                      $('#wybierz_klienta').css('display','block'); 
                      //
                      pokazChmurki();
              });    
              // 
          }
          //]]>
          </script>
          
          <?php
          // jezeli jest dodawanie zamowienia z poziomu menu klienci
          if ( isset($_GET['klient']) && (int)$_GET['klient'] > 0 ) {
              //
              $zapytanie = "select customers_id from customers where customers_id = '" . (int)$_GET['klient'] . "'";
              $sql = $db->open_query($zapytanie);          
              //
              if ((int)$db->ile_rekordow($sql) > 0) {
                  $_GET['klient_id'] = (int)$_GET['klient'];
              }
              //
              $db->close_query($sql); 
              //
          }
          
          if ( !isset($_GET['klient_id']) || $_GET['klient_id'] == '' ) { ?>
          
            <div class="poleForm">
            
              <div class="naglowek">Wybierz klienta</div>
              
              <form action="sprzedaz/zamowienia_dodaj.php" method="get" id="zamowieniaForm" class="cmxform"> 
              
              <div class="pozycja_edytowana">

                <div class="info_content">

                  <?php
                  $tablica_klientow = Klienci::ListaKlientow( false );
                  ?>
                  
                  <div style="margin:5px;" id="fraza">
                      <div>Wyszukaj klienta: <input type="text" size="15" value="" id="szukany" class="toolTipTopText dlugiInput" title="Wpisz nazwisko imię, klienta, nazwę firmy, NIP lub adres email" /></div> <span title="Wyszukaj klienta" onclick="fraza_klienci()" ></span>
                  </div>                    
                  
                  <div class="obramowanie_tabeli" id="wybierz_klienta">
                  
                    <table class="listing_tbl">
                    
                      <tr class="div_naglowek">
                        <td>Wybierz</td>
                        <td>ID</td>
                        <td>Klient</td>
                        <td>Firma</td>
                        <td>Adres</td>
                        <td>Rabat indywidualny</td>
                        <td>Grupa</td>
                        <td>Kontakt</td>
                      </tr>           

                      <?php
                      foreach ( $tablica_klientow as $klient) {
                          //
                          echo '<tr class="pozycja_off">';
                          echo '<td><input class="inputKlienta" type="radio" name="klient_id" value="' . $klient['id'] . '" /></td>';
                          echo '<td>' . $klient['id'] . '</td>';
                          echo '<td>' . $klient['nazwa'] . (($klient['gosc'] == 1) ? '<img style="float:right" class="chmurka" src="obrazki/gosc.png" alt="Klient bez rejestracji" title="Klient bez rejestracji" />' : '') . '</td>';
                          
                          if ( !empty($klient['firma']) ) {
                               echo '<td><span class="firma">' . $klient['firma'] . '</span>' . ((!empty($klient['nip'])) ? 'NIP:&nbsp;' . $klient['nip'] : '') . '</td>';
                             } else{
                               echo '<td></td>';
                          }
                          
                          echo '<td>' . $klient['adres'] . '</td>';
                          echo '<td>' . (($klient['rabat'] != 0) ? $klient['rabat'] . '%' : ''). '</td>';
                          echo '<td>' . (($klient['gosc'] == 1) ? '-' : $klient['grupa']) . '</td>';
                          echo '<td><span class="maly_mail">' . $klient['email'] . '</span></td>';
                          echo '</tr>';
                          //
                      }
                      ?>
                      
                    </table>
                    
                  </div>
                
                </div>
              
              </div>

              <div class="przyciski_dolne">
                <?php
                if ( count($tablica_klientow) > 0 ) {
                ?>
                <input type="submit" class="przyciskNon" id="dodajZamowienie" value="Dodaj zamówienie" />
                <?php 
                }
                ?>
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button>   
              </div>   

              </form>
              
              <span id="dodajKlienta">dodaj nowego klienta</span>
              
              <div id="dodawanieKlienta">
              
                  <?php
                  include('zamowienia_klient_dodaj.php');
                  ?>
                  
              </div>

            </div>

          <?php } else {  ?>
          
            <form action="sprzedaz/zamowienia_dodaj.php" method="post" id="zamowieniaForm" class="cmxform">          

            <div class="poleForm">
              <div class="naglowek">Dodawanie nowego zamówienia</div>
              
              <input type="hidden" name="akcja" value="zapisz" />
              
              <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['klient_id']); ?>" />

              <?php
              $zapytanie = "select c.customers_id, c.language_id, c.customers_status, c.customers_dod_info, c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, a.entry_company, a.entry_nip, a.entry_pesel, a.entry_street_address, a.entry_postcode, a.entry_city, a.entry_zone_id, a.entry_country_id, c.customers_telephone, c.customers_fax, c.customers_newsletter, c.customers_groups_id, c.customers_discount, c.customers_default_address_id, c.customers_nick from customers c left join address_book a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . (int)$_GET['klient_id'] . "'";
              $sql = $db->open_query($zapytanie);

              $info = $sql->fetch_assoc();
              ?>
              
              <table style="width:100%"><tr>
              
                  <td id="lewe_zakladki" style="vertical-align:top">
                      <a href="javascript:gold_tabs_horiz('0','0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                      <a href="javascript:gold_tabs_horiz('1','1')" class="a_href_info_zakl" id="zakl_link_1">Dane adresowe</a>
                  </td>
                  
                  <?php $licznik_zakladek = 0; ?>

                  <td id="prawa_strona" style="vertical-align:top">
                  
                      <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                  
                      <div id="zakl_id_0" style="display:none;">

                            <p>
                              <label>Data zamówienia:</label>
                              <input type="text" name="data_zamowienia" id="data_zamowienia" size="53" value="<?php echo date('d-m-Y H:i:s'); ?>" readonly="readonly" />
                            </p>
                            <p>
                              <label>Dokument sprzedaży:</label>
                              <input type="radio" value="1" name="dokument" checked="checked" /> faktura
                              <input type="radio" value="0" name="dokument" /> paragon
                            </p>
                            <p>
                              <label class="required">Imię:</label>
                              <input type="text" name="imie" id="imie" size="53" value="<?php echo $info['customers_firstname']; ?>" />
                            </p>
                            <p>
                              <label class="required">Nazwisko:</label>
                              <input type="text" name="nazwisko" id="nazwisko" size="53" value="<?php echo $info['customers_lastname']; ?>" />
                            </p>

                            <p>
                              <label class="required">Adres e-mail:</label>
                              <input type="text" name="email" id="email" size="53" value="<?php echo $info['customers_email_address']; ?>" />
                            </p>

                            <?php
                            if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                              ?>
                              <p>
                                <label>Telefon:</label>
                                <input type="text" name="telefon" id="telefon" size="32" value="<?php echo $info['customers_telephone']; ?>" />
                              </p>
                              <?php
                            }
                            ?>

                            <?php
                            if ( KLIENT_POKAZ_FAX == 'tak' ) {
                              ?>
                              <p>
                                <label>Fax</label>
                                <input type="text" name="fax" id="fax" size="32" value="<?php echo $info['customers_fax']; ?>" />
                              </p>
                              <?php
                            }
                            ?>

                            <p>
                              <label>Status zamówienia:</label>
                              <?php
                              $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- Wybierz z listy ---');
                              echo Funkcje::RozwijaneMenu('status', $tablica,Sprzedaz::PokazDomyslnyStatusZamowienia(),'style="width: 344px;"'); ?>
                            </p>

                            <p>
                              <label>Komentarz:</label>
                              <textarea name="komentarz" cols="60" rows="10">Zamówienie ręczne</textarea>
                            </p>

                            <p>
                              <label>Forma płatności:</label>
                              <?php
                              $tablica_platnosci = Array();
                              $tablica_platnosci = Sprzedaz::ListaPlatnosciZamowien( false );
                              echo Funkcje::RozwijaneMenu('platnosc', $tablica_platnosci , '','style="width: 344px;"');
                              unset($tablica_platnosci);
                              ?>
                            </p>

                            <p>
                              <label>Dostawa:</label>
                              <?php
                              $tablica_wysylek = Array();
                              $tablica_wysylek = Sprzedaz::ListaWysylekZamowien( false );
                              echo Funkcje::RozwijaneMenu('dostawa', $tablica_wysylek , '','style="width: 344px;" id="selection"');
                              unset($tablica_wysylek);
                              ?>
                            </p>

                            <p id="lokalizacje" style="display:none;">
                              <label for="selectionresult">Lokalizacja:</label>
                              <?php
                              $tablicaLokalizacji[] = array('id' => '0',
                                                           'text' => '--- wybierz z listy ---');
                              echo '<span id="selectionresult">'.Funkcje::RozwijaneMenu('lokalizacja', $tablicaLokalizacji, '', 'style="width: 344px;"').'</span>';
                              ?>
                            </p>

                      </div>
                      
                      <?php // ********************************************* KSIAZKA ADRESOWA *************************************************** ?>
                      
                      <div id="zakl_id_1" style="display:none;">
                        <p style="padding-left:25px;padding-bottom:10px;font-weight:bold;">Dane płatnika</p>

                        <p>
                          <label>Osobowość prawna:</label>
                          <input type="radio" value="1" name="osobowosc" onclick="$('#pesel').slideDown();$('#firma').slideUp();$('#nip').slideUp()" <?php echo ( $info['entry_nip'] == '' ? 'checked="checked"' : '' ); ?> /> osoba fizyczna
                          <input type="radio" value="0" name="osobowosc" onclick="$('#pesel').slideUp();$('#firma').slideDown();$('#nip').slideDown()" <?php echo ( $info['entry_nip'] != '' ? 'checked="checked"' : '' ); ?> /> firma
                        </p> 

                        <p id="pesel" <?php echo ( $info['entry_nip'] == '' ? '' : 'style="display:none;"' ); ?> >
                          <label>Numer PESEL:</label>
                          <input type="text" name="pesel" value="<?php echo $info['entry_pesel']; ?>" size="32" />
                        </p>

                        <p id="firma" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?> >
                          <label class="required">Nazwa firmy:</label>
                          <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo $info['entry_company']; ?>" size="53" />
                        </p>

                        <p id="nip" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?> class="required">
                          <label class="required">Numer NIP:</label>
                          <input type="text" name="nip_firmy" id="nip_firmy" value="<?php echo $info['entry_nip']; ?>" size="32" />
                        </p>

                        <p>
                          <label class="required">Ulica i numer domu:</label>
                          <input type="text" name="ulica" id="ulica" size="53" value="<?php echo $info['entry_street_address']; ?>" />
                        </p>                          
                                 
                        <p>
                          <label class="required">Kod pocztowy:</label>
                          <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="<?php echo $info['entry_postcode']; ?>" />
                        </p> 

                        <p>
                          <label class="required">Miejscowość:</label>
                          <input type="text" name="miasto" id="miasto" size="53" value="<?php echo $info['entry_city']; ?>" />
                        </p>

                        <p>
                          <label class="required">Kraj:</label>
                          <input type="text" style="height:24px; padding-top:0px; padding-bottom:0px" name="panstwo" id="panstwo" size="53" value="<?php echo Klienci::pokazNazwePanstwa($info['entry_country_id']); ?>" />
                        </p>

                        <?php
                        if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                          ?>
                          <p>
                            <label>Województwo:</label>
                            <input type="text" style="height:24px; padding-top:0px; padding-bottom:0px" name="wojewodztwo" id="wojewodztwo" size="53" value="<?php echo ( $info['entry_zone_id'] != '' ? Klienci::pokazNazweWojewodztwa($info['entry_zone_id']) : '' ); ?>" />
                          </p>
                          <?php
                        }
                        ?>

                        <p>
                          <label>Adres dostawy:</label>
                            <input type="radio" value="1" name="adres_dostawy" checked="checked" onclick="$('#dostawa').slideUp();" /> taki sam jak adres klienta
                            <input type="radio" value="0" name="adres_dostawy" onclick="$('#dostawa').slideDown();" /> inny
                        </p>

                        <div id="dostawa" style="display:none;">
                          <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;">
                          <p style="padding-left:25px;font-weight:bold;">Adres dostawy</p>

                          <p>
                            <label>Nazwa firmy:</label>
                            <input type="text" name="dostawa_nazwa_firmy" id="dostawa_nazwa_firmy" value="" size="53" />
                          </p>

                          <p>
                            <label>Imię:</label>
                            <input type="text" name="dostawa_imie" id="dostawa_imie" size="53" value="" />
                          </p>
                          <p>
                            <label>Nazwisko:</label>
                            <input type="text" name="dostawa_nazwisko" id="dostawa_nazwisko" size="53" value="" />
                          </p>

                          <p>
                            <label class="required">Ulica i numer domu:</label>
                            <input type="text" name="dostawa_ulica" id="dostawa_ulica" size="53" value="" />
                          </p>                          
                                   
                          <p>
                            <label class="required">Kod pocztowy:</label>
                            <input type="text" name="dostawa_kod_pocztowy" id="dostawa_kod_pocztowy" size="12" value="" />
                          </p> 

                          <p>
                            <label class="required">Miejscowość:</label>
                            <input type="text" name="dostawa_miasto" id="dostawa_miasto" size="53" value="" />
                          </p>

                          <p>
                            <label class="required">Kraj:</label>
                          <input type="text" style="height:24px; padding-top:0px; padding-bottom:0px" name="dostawa_panstwo" id="dostawa_panstwo" size="53" value="" />
                          </p>

                          <?php
                          if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                            ?>
                            <p>
                              <label for="dostawa_selectionresult">Województwo:</label>
                              <input type="text" style="height:24px; padding-top:0px; padding-bottom:0px" name="dostawa_wojewodztwo" id="dostawa_wojewodztwo" size="53" value="" />
                            </p>
                            <?php
                          }
                          ?>
                        </div>

                      </div>

                      <?php
                      $zakladka = '0';
                      if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                      ?>
                      <script type="text/javascript">
                      //<![CDATA[
                      gold_tabs_horiz(<?php echo $zakladka; ?>,'0');
                      //]]>
                      </script>                         
                  
                  </td>
              </tr></table>    

              <?php
              $db->close_query($sql); 
              unset($zapytanie, $info);                
              ?>

            </div>         

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <?php
                // jezeli jest wywolanie z menu klientow
                if ( isset($_GET['klient']) ) {
                ?>
                <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Get(array('x','y','klient')); ?>','klienci');">Powrót</button>   
                <?php } else { ?>
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y','klient_id')); ?>','sprzedaz');">Powrót</button>   
                <?php } ?>
            </div>              
            
            </form>
            
          <?php } ?>

    </div>    
    <?php
    include('stopka.inc.php');

}