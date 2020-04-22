<?php
if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

    chdir('../');            

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    // zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
    $protKlient = new Dostep($db);

    if ($protKlient->wyswietlStrone) {    

        // grupy newslettera
        $grupyNewslettera = '';
        if ( isset($_POST['biuletyn']) ) {
             //
             if ( isset($_POST['newsletter_grupa']) ) {
                  $grupyNewslettera = ',' . implode(',', $filtr->process($_POST['newsletter_grupa'])) . ',';
             }
             //
        }   
        
        $pola = array(
                array('customers_id_private',$filtr->process($_POST['id_klienta_magazyn'])),
                array('customers_firstname',$filtr->process($_POST['imie'])),
                array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                array('customers_fax',( isset($_POST['fax']) ? $filtr->process($_POST['fax']) : '' )),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('customers_status',$_POST['aktywnosc']),
                array('customers_dod_info',$filtr->process($_POST['notatki'])),
                array('language_id',$_SESSION['domyslny_jezyk']['id'])            
        );
        
        // jezeli jest konto z rejestracja
        if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {
          //
          $zakodowane_haslo = Funkcje::zakodujHaslo($filtr->process($_POST["password"]));
          $pola[] = array('customers_password',$zakodowane_haslo);
          $pola[] = array('customers_discount',$filtr->process($_POST['rabat']));
          $pola[] = array('customers_groups_id',(int)$_POST['grupa']);
          $pola[] = array('customers_email_address',$filtr->process($_POST['email']));
          $pola[] = array('customers_nick',$filtr->process($_POST['nick']));
          $pola[] = array('customers_guest_account','0');
          //
        } else {
          //
          $pola[] = array('customers_email_address',$filtr->process($_POST['email_bez_rejestracji']));
          $pola[] = array('customers_guest_account','1');
          //
        }

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
        
        // dane do newslettera
        $pola = array(
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',$grupyNewslettera),
                array('date_added',( isset($_POST['biuletyn']) ? 'now()' : '0000-00-00')),
                array('customers_id',$id_dodanej_pozycji));
                
        // jezeli jest konto z rejestracja
        if (isset($_POST['rodzaj_konta']) && $_POST['rodzaj_konta'] == 0) {   
          //
          // najpierw usuwa dane jezeli juz kiedys byl dodany taki email
          $db->delete_query('subscribers' , " subscribers_email_address = '".$filtr->process($_POST['email'])."'"); 
          //      
          $pola[] = array('subscribers_email_address',$filtr->process($_POST['email']));
          //
        } else {
          // najpierw usuwa dane jezeli juz kiedys byl dodany taki email
          $db->delete_query('subscribers' , " subscribers_email_address = '".$filtr->process($_POST['email_bez_rejestracji'])."'"); 
          // 
          $pola[] = array('subscribers_email_address',$filtr->process($_POST['email_bez_rejestracji']));
          //
        }

        $sql = $db->insert_query('subscribers' , $pola);
        unset($pola);

        unset($grupyNewslettera);
        
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('zamowienia_dodaj.php?klient_id='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('zamowienia.php');
        }
        
    }
    
}

if (!class_exists('Dostep')) {
    exit;
}
  
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$protKlient = new Dostep($db);

if ($protKlient->wyswietlStrone) {    
?>

    <!-- Skrypt do walidacji formularza -->
    <script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {
        $("#klienciForm").validate({
          rules: {
            email: {required: function() {var wynik = true; if ( $("input[name='rodzaj_konta']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; },email: true,remote: "ajax/sprawdz_czy_jest_mail_klient.php"},
            email_bez_rejestracji: {required: function() {var wynik = true; if ( $("input[name='rodzaj_konta']:checked", "#klienciForm").val() == "0" ) { wynik = false; } return wynik; },email: true},
            nick: {remote: "ajax/sprawdz_czy_jest_nick.php"},
            imie: {required: true},
            nazwisko: {required: true},
            ulica: {required: true},
            kod_pocztowy: {required: true},
            miasto: {required: true},
            nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }},
            nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik;}},
            rabat: {range: [-100, 0],number: true},
            password: {required: function() {var wynik = true; if ( $("input[name='rodzaj_konta']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }}
          },
          messages: {
            email: {required: "Pole jest wymagane",email: "Wpisano niepoprawny adres e-mail",remote: "Taki adres jest już używany"},
            email_bez_rejestracji: {required: "Pole jest wymagane",email: "Wpisano niepoprawny adres e-mail"},
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

    function rejestracjaKlienta(nr) {
      //
      if ( nr == 0 ) {
           $('.bezRejestracji').slideUp();
           $('.mailRejestracja').hide();
           $('.mailRejestracja input').val('');
           $('.mailBezRejestracja').show();
        } else {
           $('.bezRejestracji').slideDown();
           $('.mailRejestracja').show();
           $('.mailBezRejestracja').hide();
           $('.mailBezRejestracja input').val('');
      }
      //
    }
    //]]>
    </script>    

    <form action="sprzedaz/zamowienia_klient_dodaj.php" method="post" id="klienciForm" class="cmxform"> 

        <table class="dodawanieKlient">
        
            <tr>
            
                <td style="width:50%;vertical-align:top">
                
                    <div class="poleForm">
                    
                        <div class="naglowek">Dane podstawowe</div>
                        
                        <div class="pozycja_edytowana">   

                        <input type="hidden" name="akcja" value="zapisz" />

                        <p>
                          <label>Status konta:</label>
                          <input type="radio" value="1" name="aktywnosc" checked="checked" /> aktywne
                          <input type="radio" value="0" name="aktywnosc" /> nieaktywne
                        </p> 
                        
                        <p>
                          <label>Rodzaj konta:</label>
                          <input type="radio" value="1" onclick="rejestracjaKlienta(0)" name="rodzaj_konta" checked="checked" /> bez rejestracji
                          <input type="radio" value="0" onclick="rejestracjaKlienta(1)" name="rodzaj_konta" /> z rejestracją
                        </p>

                        <div class="bezRejestracji">

                            <p>
                              <label class="required">Hasło:</label>
                              <input type="password" name="password" id="password" value="" size="35" />
                            </p>

                            <p>
                              <label class="required">Powtórz hasło:</label>
                              <input type="password" name="nowe_haslo_powtorz" id="nowe_haslo_powtorz" value="" size="35" equalTo="#password" />
                            </p>
                        
                        </div>
                        
                        <div class="mailRejestracja" style="display:none">

                            <p>
                              <label class="required">Adres e-mail:</label>
                              <input type="text" name="email" id="email" size="35" value="" class="toolTipText" title="Adres wykorzystywany do logowania oraz do korespondencji" />
                            </p>

                        </div>
                        
                        <div class="mailBezRejestracja">
                        
                            <p>
                              <label class="required">Adres e-mail:</label>
                              <input type="text" name="email_bez_rejestracji" id="email_bez_rejestracji" size="35" value="" class="toolTipText" title="Adres wykorzystywany do korespondencji" />
                            </p> 
                            
                        </div>

                        <div class="bezRejestracji">
                            
                            <p>
                              <label>Login:</label>
                              <input type="text" name="nick" id="nick" size="35" value="" class="toolTipText" title="Może być używany do logowania zamiennie z wprowadzonym adresem e-mail" />
                            </p>

                        </div>

                        <p>
                          <label class="required">Imię:</label>
                          <input type="text" name="imie" id="imie" size="35" value="" />
                        </p> 

                        <p>
                          <label class="required">Nazwisko:</label>
                          <input type="text" name="nazwisko" id="nazwisko" size="35" value="" />
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
                        
                        <div class="bezRejestracji">

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
                            
                        </div>
                        
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

                        </div>
                    
                    </div>

                </td>
                
                <td style="width:50%;vertical-align:top">
                
                    <div class="poleForm" style="margin-left:10px">
                    
                        <div class="naglowek">Dane adresowe</div>
                        
                        <div class="pozycja_edytowana">                 
                
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
                              <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="" size="35" />
                            </p>

                            <p id="nip" style="display:none;" class="required">
                              <label class="required">Numer NIP:</label>
                              <input type="text" name="nip_firmy" id="nip_firmy" value="" size="32" />
                            </p>

                            <p>
                              <label class="required">Ulica i numer domu:</label>
                              <input type="text" name="ulica" id="ulica" size="35" value="" />
                            </p>                          
                                     
                            <p>
                              <label class="required">Kod pocztowy:</label>
                              <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="" />
                            </p> 

                            <p>
                              <label class="required">Miejscowość:</label>
                              <input type="text" name="miasto" id="miasto" size="35" value="" />
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
                    
                    </div>      

                    <div class="poleForm" style="margin-left:10px; margin-top:10px;">
                    
                        <div class="naglowek">Uwagi</div>
                        
                        <div class="pozycja_edytowana">       

                            <textarea name="notatki" cols="50" rows="5" class="toolTipText uwagiKlienta" title="Zawartość informacji widoczna tylko dla obsługi sklepu."></textarea>
                            
                        </div>
                        
                    </div>
                
                </td>
              
            </tr>
          
        </table>
        
        <div class="przyciski_dolne">
          <input type="submit" class="przyciskNon" value="Zapisz dane i przejdź dalej" />   
        </div>         

    </form>    
    
<?php 
} 

unset($protKlient);
?>    