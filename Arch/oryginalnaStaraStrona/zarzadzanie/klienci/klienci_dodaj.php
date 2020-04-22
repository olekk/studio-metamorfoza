<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        // grupy newslettera
        $grupyNewslettera = '';
        if ( isset($_POST['biuletyn']) ) {
             //
             if ( isset($_POST['newsletter_grupa']) ) {
                  $grupyNewslettera = ',' . implode(',', $filtr->process($_POST['newsletter_grupa'])) . ',';
             }
             //
        }   
    
        $zakodowane_haslo = Funkcje::zakodujHaslo($filtr->process($_POST["password"]));
        $pola = array(
                array('customers_id_private',$filtr->process($_POST['id_klienta_magazyn'])),
                array('customers_nick',$filtr->process($_POST['nick'])),
                array('customers_firstname',$filtr->process($_POST['imie'])),
                array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                array('customers_email_address',$filtr->process($_POST['email'])),
                array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                array('customers_fax',( isset($_POST['fax']) ? $filtr->process($_POST['fax']) : '' )),
                array('customers_password',$zakodowane_haslo),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('customers_discount',$filtr->process($_POST['rabat'])),
                array('customers_groups_id',(int)$_POST['grupa']),
                array('customers_status',$_POST['aktywnosc']),
                array('customers_dod_info',$filtr->process($_POST['notatki'])),
                array('language_id',$_SESSION['domyslny_jezyk']['id']));

        if (isset($_POST['data_urodzenia'])) {
          $pola[] = array('customers_dob', date('Y-m-d', strtotime($filtr->process($_POST['data_urodzenia']))));
        }

        if (isset($_POST['plec'])) {
          $pola[] = array('customers_gender',$filtr->process($_POST['plec']));
        }
        
        $sql = $db->insert_query('customers' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        unset($pola);

        $pola = array(
                array('customers_info_id',$id_dodanej_pozycji),
                array('customers_info_number_of_logons','0'),
                array('customers_info_date_account_created','now()'),
                array('customers_info_date_account_last_modified','now()'));
                
        $sql = $db->insert_query('customers_info' , $pola);
        unset($pola);

        $pola = array(
                array('customers_id',$id_dodanej_pozycji),
                array('entry_company',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nazwa_firmy']) : '')),
                array('entry_nip',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nip_firmy']) : '')),
                array('entry_pesel',(($_POST['osobowosc'] == '1') ? $filtr->process($_POST['pesel']) : '')),
                array('entry_firstname',$filtr->process($_POST['imie'])),
                array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                array('entry_street_address',$filtr->process($_POST['ulica'])),
                array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                array('entry_city',$filtr->process($_POST['miasto'])),
                array('entry_country_id',$filtr->process($_POST['panstwo'])),
                array('entry_zone_id',(isset($_POST['wojewodztwo']) ? $filtr->process($_POST['wojewodztwo']) : '')));

        $sql = $db->insert_query('address_book' , $pola);
        $id_dodanej_pozycji_adres = $db->last_id_query();
        unset($pola);

        $pola = array(
                array('customers_default_address_id',$id_dodanej_pozycji_adres));

        $db->update_query('customers' , $pola, " customers_id = '".(int)$id_dodanej_pozycji."'");	
        unset($pola);

        // dodatkowe pola klientow
        $db->delete_query('customers_to_extra_fields' , " customers_id = '".(int)$id_dodanej_pozycji."'");  

        $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type FROM customers_extra_fields ce WHERE ce.fields_status = '1'";

        $sql = $db->open_query($dodatkowe_pola_klientow);

        if ( (int)$db->ile_rekordow($sql) > 0  ) {

          while ( $dodatkowePola = $sql->fetch_assoc() ) {
          
            $wartosc = '';
            if ( $dodatkowePola['fields_input_type'] != '3' ) {
            
              $pola = array(
                      array('customers_id',(int)$id_dodanej_pozycji),
                      array('fields_id',(int)$dodatkowePola['fields_id']),
                      array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']])));
              
            } else {
            
              if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
              
                foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
                  $wartosc .= $value . "\n";
                }
                
                $pola = array(
                        array('customers_id',(int)$id_dodanej_pozycji),
                        array('fields_id',(int)$dodatkowePola['fields_id']),
                        array('value',$filtr->process($wartosc)));
              }

            }
            
            if ( count($pola) > 0 ) {
              $pola[] = array('language_id', '1');
              $db->insert_query('customers_to_extra_fields' , $pola);
              unset($pola);
            }
            
          }

        }
        //
        
        // dane do newslettera
        // najpierw usuwa dane jezeli juz kiedys byl dodany taki email
        $db->delete_query('subscribers' , " subscribers_email_address = '".$filtr->process($_POST['email'])."'"); 
        //
        $pola = array(
                array('customers_id',$id_dodanej_pozycji),
                array('subscribers_email_address',$filtr->process($_POST['email'])),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('date_added',( isset($_POST['biuletyn']) ? 'now()' : '0000-00-00')));

        $sql = $db->insert_query('subscribers' , $pola);
        unset($pola);
        
        unset($grupyNewslettera);

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('klienci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('klienci.php');
        }
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
            $("#klienciForm").validate({
              rules: {
                email: {required: true,email: true,remote: "ajax/sprawdz_czy_jest_mail_klient.php"},
                nick: {remote: "ajax/sprawdz_czy_jest_nick.php"},
                imie: {required: true},
                nazwisko: {required: true},
                ulica: {required: true},
                kod_pocztowy: {required: true},
                miasto: {required: true},
                nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }},
                nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik;}},
                rabat: {range: [-100, 0],number: true},
                password: {required: true}
              },
              messages: {
                email: {required: "Pole jest wymagane",email: "Wpisano niepoprawny adres e-mail",remote: "Taki adres jest już używany"},
                nick: {remote: "Taki login jest już używany"}
              }         
            });

            $('input.datepicker').Zebra_DatePicker({
                view: 'years',
                format: 'd-m-Y',
                inside: false,
                readonly_element: false
            });

            $("#selection").change( function() {
                $("#selectionresult").html('<img src="obrazki/_loader_small.gif">');
                $.ajax({
                      type: "POST",
                      data: "data=" + $(this).val(),
                      url: "ajax/wybor_wojewodztwa.php",
                      success: function(msg){
                        if (msg != '') { 
                          $("#selectionresult").html(msg).show(); 
                         } else { 
                          $("#selectionresult").html('<em>Brak</em>'); 
                        }
                      }
                });
            });
        });
        
        function pokazGrupyNewsletter() {
          //
          if ($('#biuletyn').prop('checked') == true) {
              $('#grupy_newslettera').slideDown();
            } else {
              $('#grupy_newslettera').slideUp();
          }
          //
        }          
        //]]>
        </script>

        <form action="klienci/klienci_dodaj.php" method="post" id="klienciForm" class="cmxform"> 
        
        <div class="poleForm">
          <div class="naglowek">Dodawanie nowego klienta</div>
          
              <input type="hidden" name="akcja" value="zapisz" />

              <table style="width:100%"><tr>
              
                  <td id="lewe_zakladki" style="vertical-align:top">
                      <a href="javascript:gold_tabs_horiz('0','0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                      <a href="javascript:gold_tabs_horiz('1','1')" class="a_href_info_zakl" id="zakl_link_1">Dane adresowe</a> 
                      <a href="javascript:gold_tabs_horiz('2','2')" class="a_href_info_zakl" id="zakl_link_2">Uwagi</a>
                  </td>
                  
                  <?php $licznik_zakladek = 0; ?>

                  <td id="prawa_strona" style="vertical-align:top">
                  
                      <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                  
                      <div id="zakl_id_0" style="display:none;">
                      
                        <p>
                          <label>Status konta:</label>
                          <input type="radio" value="1" name="aktywnosc" checked="checked" /> aktywne
                          <input type="radio" value="0" name="aktywnosc" /> nieaktywne
                        </p> 

                        <p>
                          <label class="required">Adres e-mail:</label>
                          <input type="text" name="email" id="email" size="53" value="" class="toolTipText" title="Adres wykorzystywany do logowania oraz do korespondencji" />
                        </p>                          
                                  
                        <p>
                          <label>Login:</label>
                          <input type="text" name="nick" id="nick" size="53" value="" class="toolTipText" title="Może być używany do logowania zamiennie z wprowadzonym adresem e-mail" />
                        </p>                          
                                  
                        <p>
                          <label class="required">Imię:</label>
                          <input type="text" name="imie" id="imie" size="53" value="" />
                        </p> 

                        <p>
                          <label class="required">Nazwisko:</label>
                          <input type="text" name="nazwisko" id="nazwisko" size="53" value="" />
                        </p>

                        <?php
                        if ( KLIENT_POKAZ_PLEC == 'tak' ) {
                          ?>
                          <p>
                            <label>Płeć:</label>
                            <input type="radio" value="f" name="plec" checked="checked" /> kobieta
                            <input type="radio" value="m" name="plec"  /> mężczyzna
                          </p> 
                          <?php
                        }
                        ?>

                        <?php
                        if ( KLIENT_POKAZ_DATE_URODZENIA == 'tak' ) {
                          ?>
                          <p>
                            <label>Data urodzenia:</label>
                            <input type="text" name="data_urodzenia" id="data_urodzenia" size="30" value="" class="datepicker" />
                          </p> 
                          <?php
                        }
                        ?>

                        <?php
                        if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                          ?>
                          <p>
                            <label>Numer telefonu:</label>
                            <input type="text" name="telefon" id="telefon" size="32" value="" />
                          </p>
                          <?php
                        }
                        ?>

                        <?php
                        if ( KLIENT_POKAZ_FAX == 'tak' ) {
                          ?>
                          <p>
                            <label>Numer faxu:</label>
                            <input type="text" name="fax" id="fax" size="32" value="" />
                          </p>
                          <?php
                        }
                        ?>

                        <p>
                          <label>Grupa klientów:</label>
                          <?php
                          $tablica = Klienci::ListaGrupKlientow(false);
                          echo Funkcje::RozwijaneMenu('grupa', $tablica); ?>
                        </p>

                        <p>
                          <label>Indywidualny rabat [%]:</label>
                          <input type="text" name="rabat" id="rabat" value="" size="5" class="toolTip" title="liczba z zakresu -100 do 0" />
                        </p>
                        
                        <p>
                          <label>Id klienta w programie magazynowym:</label>
                          <input type="text" name="id_klienta_magazyn" size="20" value="<?php echo $info['customers_id_private']; ?>" />
                        </p>                           
                        
                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                        <p>
                          <label>Subskrypcja biuletynu:</label>
                          <input type="checkbox" checked="checked" value="1" name="biuletyn" onclick="pokazGrupyNewsletter()" id="biuletyn" />
                        </p>
                        
                        <?php
                        $TablicaGrup = Newsletter::GrupyNewslettera();
                        if ( count($TablicaGrup) > 0 ) {
                        ?>
                        <div id="grupy_newslettera">
                          <table>
                              <tr>
                                  <td><label>Przypisany do grup newslettera:</label></td>   
                                  <td>
                                  
                                  <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie klient będzie przypisany do wszystkich grup</span>
                                  
                                  <?php
                                  foreach ($TablicaGrup as $Grupa) {
                                      //
                                      echo '<input type="checkbox" value="' . $Grupa['id'] . '" name="newsletter_grupa[]" /> ' . $Grupa['text'] . '<br />';
                                      //
                                  }
                                  ?>
                                  </td>
                              </tr>
                          </table>
                        </div>
                        <?php
                        unset($TablicaGrup);
                        }
                        ?>    

                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                        <p>
                          <label class="required">Hasło:</label>
                          <input type="password" name="password" id="password" value="" size="53" />
                        </p>

                        <p>
                          <label class="required">Powtórz hasło:</label>
                          <input type="password" name="nowe_haslo_powtorz" id="nowe_haslo_powtorz" value="" size="53" equalTo="#password" />
                        </p>
                        
                        <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;">

                        <div style="margin-top:10px;margin-left:10px;">
                        <?php echo Klienci::pokazDodatkowePolaKlientow('',$_SESSION['domyslny_jezyk']['id']); ?>
                        </div>

                      </div>
                      
                      <?php // ********************************************* KSIAZKA ADRESOWA *************************************************** ?>
                      
                      <div id="zakl_id_1" style="display:none;">

                        <p>
                          <label>Osobowość prawna:</label>
                          <input type="radio" value="1" name="osobowosc" onclick="$('#pesel').slideDown();$('#firma').slideUp();$('#nip').slideUp()" checked="checked" /> osoba fizyczna
                          <input type="radio" value="0" name="osobowosc" onclick="$('#pesel').slideUp();$('#firma').slideDown();$('#nip').slideDown()" /> firma
                        </p> 

                        <p id="pesel">
                          <label>Numer PESEL:</label>
                          <input type="text" name="pesel" value="" size="32" />
                        </p>

                        <p id="firma" style="display:none;">
                          <label class="required">Nazwa firmy:</label>
                          <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="" size="53" />
                        </p>

                        <p id="nip" style="display:none;" class="required">
                          <label class="required">Numer NIP:</label>
                          <input type="text" name="nip_firmy" id="nip_firmy" value="" size="32" />
                        </p>

                        <p>
                          <label class="required">Ulica i numer domu:</label>
                          <input type="text" name="ulica" id="ulica" size="53" value="" />
                        </p>                          
                                 
                        <p>
                          <label class="required">Kod pocztowy:</label>
                          <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="" />
                        </p> 

                        <p>
                          <label class="required">Miejscowość:</label>
                          <input type="text" name="miasto" id="miasto" size="53" value="" />
                        </p>

                        <p>
                          <label class="required">Kraj:</label>
                          <?php
                          $tablicaPanstw = Klienci::ListaPanstw();
                          echo Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, '170', 'id="selection"'); ?>
                        </p>

                        <?php
                        if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                          ?>
                          <p>
                            <label for="selectionresult">Województwo:</label>
                            <?php
                            $tablicaWojewodztw = Klienci::ListaWojewodztw('170');
                            echo '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw).'</span>';
                            ?>
                          </p>
                          <?php
                        }
                        ?>

                      </div>
                      
                      <?php // ********************************************* UWAGI *************************************************** ?>
                      
                      <div id="zakl_id_2" style="display:none;">
                         <p>
                           <label>Uwagi:</label>
                           <textarea name="notatki" cols="50" rows="10" class="toolTipText" title="Zawartość informacji widoczna tylko dla obsługi sklepu."></textarea>
                        </p>                                        
                      </div>    

                      <script type="text/javascript">
                      //<![CDATA[
                      gold_tabs_horiz('0','0');
                      //]]>
                      </script>                         
                  
                  </td>
              
              </tr></table>
              
          </div>
          
          <div class="przyciski_dolne">
            <input type="submit" class="przyciskNon" value="Zapisz dane" />
            <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
          </div>            

        </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>