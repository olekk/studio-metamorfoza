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
    
        $pola = array(
                array('customers_id_private',$filtr->process($_POST['id_klienta_magazyn'])),
                array('customers_nick',$filtr->process($_POST['nick'])),
                array('customers_firstname',$filtr->process($_POST['imie'])),
                array('customers_lastname',$filtr->process($_POST['nazwisko'])),
                array('customers_email_address',$filtr->process($_POST['email'])),
                array('customers_telephone',( isset($_POST['telefon']) ? $filtr->process($_POST['telefon']) : '' )),
                array('customers_fax',( isset($_POST['fax']) ? $filtr->process($_POST['fax']) : '' )),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',( isset($_POST['biuletyn']) ? $grupyNewslettera : '' )),
                array('customers_discount',$filtr->process($_POST['rabat'])),
                array('customers_groups_id',(int)$_POST['grupa']),
                array('customers_status',$_POST['aktywnosc']),
                array('customers_dod_info',$filtr->process($_POST['notatki']))
        );

        if (isset($_POST['data_urodzenia'])) {
          $pola[] = array('customers_dob', date('Y-m-d', strtotime($filtr->process($_POST['data_urodzenia']))));
        }

        if (isset($_POST['plec'])) {
          $pola[] = array('customers_gender',$filtr->process($_POST['plec']));
        }
        
        $db->update_query('customers' , $pola, " customers_id = '".(int)$_POST["id"]."'");	
        unset($pola);

        $pola = array(
                array('customers_info_date_account_last_modified','now()')
        );
        $db->update_query('customers_info' , $pola, " customers_info_id = '".(int)$_POST["id"]."'");	
        unset($pola);

        $pola = array(
                array('entry_company',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nazwa_firmy']) : '')),
                array('entry_nip',(($_POST['osobowosc'] == '0') ? $filtr->process($_POST['nip_firmy']) : '')),
                array('entry_pesel',(($_POST['osobowosc'] == '1') ? $filtr->process($_POST['pesel']) : '')),
                array('entry_firstname',$filtr->process($_POST['imie'])),
                array('entry_lastname',$filtr->process($_POST['nazwisko'])),
                array('entry_street_address',$filtr->process($_POST['ulica'])),
                array('entry_postcode',$filtr->process($_POST['kod_pocztowy'])),
                array('entry_city',$filtr->process($_POST['miasto'])),
                array('entry_country_id',$filtr->process($_POST['panstwo'])),
                array('entry_zone_id',(isset($_POST['wojewodztwo']) ? $filtr->process($_POST['wojewodztwo']) : ''))
        );

        $db->update_query('address_book' , $pola, " customers_id = '".(int)$_POST["id"]."'");	
        unset($pola);


        // dodatkowe pola klientow
        $db->delete_query('customers_to_extra_fields' , " customers_id = '".(int)$_POST["id"]."'");  

        $dodatkowe_pola_klientow = "SELECT ce.fields_id, ce.fields_input_type 
                                      FROM customers_extra_fields ce 
                                     WHERE ce.fields_status = '1'";

        $sql = $db->open_query($dodatkowe_pola_klientow);

        if ( (int)$db->ile_rekordow($sql) > 0  ) {

          while ( $dodatkowePola = $sql->fetch_assoc() ) {
            $wartosc = '';
            if ( $dodatkowePola['fields_input_type'] != '3' ) {
              $pola = array(
                      array('customers_id',(int)$_POST["id"]),
                      array('fields_id',(int)$dodatkowePola['fields_id']),
                      array('value',$filtr->process($_POST['fields_' . $dodatkowePola['fields_id']]))
              );
            } else {
              if ( isset($_POST['fields_' . $dodatkowePola['fields_id']]) ) {
                foreach ($_POST['fields_' . $dodatkowePola['fields_id']] as $key => $value) {
                  $wartosc .= $value . "\n";
                }
                $pola = array(
                        array('customers_id',(int)$_POST["id"]),
                        array('fields_id',(int)$dodatkowePola['fields_id']),
                        array('value',rtrim($filtr->process($wartosc)))
                );
              }

            }

            if ( count($pola) > 0 ) {
              $pola[] = array('language_id', $filtr->process($_POST['language_id']));
              $db->insert_query('customers_to_extra_fields' , $pola);
              unset($pola);
            }
          }

        }
        //
        
        // dane do newslettera
        $db->delete_query('subscribers' , " customers_id = '".(int)$_POST["id"]."'");         
        //
        $pola = array(
                array('customers_id',(int)$_POST["id"]),
                array('subscribers_email_address',$filtr->process($_POST['email'])),
                array('customers_newsletter',( isset($_POST['biuletyn']) ? '1' : '0')),
                array('customers_newsletter_group',( isset($_POST['biuletyn']) ? $grupyNewslettera : '' )),
                array('date_added',( isset($_POST['biuletyn']) ? 'now()' : '0000-00-00')),
        );          

        $sql = $db->insert_query('subscribers' , $pola);
        unset($pola); 
        
        unset($grupyNewslettera);

        Funkcje::PrzekierowanieURL('klienci.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          <?php

          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }    
                      
          $zapytanie = "select c.customers_id, 
                             c.customers_id_private, 
                             c.language_id, 
                             c.customers_status, 
                             c.customers_dod_info, 
                             c.customers_gender, 
                             c.customers_firstname, 
                             c.customers_lastname, 
                             c.customers_dob, 
                             c.customers_email_address,
                             c.customers_shopping_points,
                             c.customers_guest_account,
                             a.entry_company, 
                             a.entry_nip, 
                             a.entry_pesel, 
                             a.entry_street_address, 
                             a.entry_postcode, 
                             a.entry_city, 
                             a.entry_zone_id, 
                             a.entry_country_id, 
                             c.customers_telephone, 
                             c.customers_fax, 
                             c.customers_newsletter, 
                             c.customers_newsletter_group,
                             c.customers_groups_id, 
                             c.customers_discount, 
                             c.customers_default_address_id, 
                             c.customers_nick
                        from customers c left join address_book a on 
                             c.customers_default_address_id = a.address_book_id
                       where a.customers_id = c.customers_id and c.customers_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";

                             
          $sql = $db->open_query($zapytanie);

          $info = $sql->fetch_assoc();

          ?>
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {

              $("#klienciForm").validate({
                rules: {
                  email: {required: true,email: true,remote: "ajax/sprawdz_czy_jest_mail_klient.php?user_id=<?php echo $info['customers_id']; ?>&tok=<?php echo Sesje::Token(); ?>"},
                  nick: {remote: "ajax/sprawdz_czy_jest_nick.php?user_id=<?php echo $info['customers_id']; ?>&tok=<?php echo Sesje::Token(); ?>"},
                  imie: {required: true},
                  nazwisko: {required: true},
                  ulica: {required: true},
                  kod_pocztowy: {required: true},
                  miasto: {required: true},
                  nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik; }},
                  nip_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#klienciForm").val() == "1" ) { wynik = false; } return wynik;}},
                  rabat: {range: [-100, 00],number: true}
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
                    type: "post",
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
          //]]>
          </script>

          <form action="klienci/klienci_edytuj.php" method="post" id="klienciForm" class="cmxform"> 

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
              <?php

              if ((int)$db->ile_rekordow($sql) > 0) {
              
                // znizki klienta
                $TblZnizki = Klienci::ZnizkiKlienta($info['customers_id'], $info['customers_discount']);              
            
                ?>
                
                <script type="text/javascript">
                //<![CDATA[                
                    
                function szukajTbl(tablica, szuk) {
                  for (var i = 0; i < tablica.length; i++) {
                      if (tablica[i] == szuk) return true;
                  }
                }          

                function pokaz_dane( nr ) {
                  //
                  var pole = $('#ajax_zakladki').val();
                  var sprawdz = pole.split(',');
                  //
                  if ( !szukajTbl(sprawdz, nr) ) {
                      // koszyk klienta
                      if ( nr == '2' ) {
                        var pamietaj_html = $("#koszyk_klienta").html();
                        $('#ekr_preloader').css('display','block');
                        $("#koszyk_klienta").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_koszyk.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#koszyk_klienta").html(data);
                              } else {
                                $("#koszyk_klienta").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }  
                      // schowek klienta
                      if ( nr == '3' ) {
                        var pamietaj_html = $("#schowek_klienta").html();
                        $('#ekr_preloader').css('display','block');
                        $("#schowek_klienta").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_schowek.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#schowek_klienta").html(data);
                              } else {
                                $("#schowek_klienta").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }                     
                      // punkty klienta
                      if ( nr == '5' ) {
                        var pamietaj_html = $("#punkty").html();
                        $('#ekr_preloader').css('display','block');
                        $("#punkty").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_punkty.php?tok=<?php echo Sesje::Token(); ?>', { ogolem: <?php echo "'" . $info['customers_shopping_points'] . "'"; ?>, id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#punkty").html(data);
                              } else {
                                $("#punkty").html(pamietaj_html);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }
                      // recenzje
                      if ( nr == '6' ) {
                        var pamietaj_html = $("#recenzje").html();
                        $('#ekr_preloader').css('display','block');
                        $("#recenzje").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_recenzje.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#recenzje").html(data);
                              } else {
                                $("#recenzje").html(pamietaj_html);
                            } 
                            $('#ekr_preloader').delay(100).fadeOut('fast');   
                            pokazChmurki();
                        });
                      }
                      // statystyki
                      if ( nr == '7' ) {
                        var pamietaj_html = $("#statystyki").html();
                        $('#ekr_preloader').css('display','block');
                        $("#statystyki").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_statystyki.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#statystyki").html(data);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                        });
                      }
                      // lista zamowien
                      if ( nr == '9' ) {
                        var pamietaj_html = $("#zamowienia").html();
                        $('#ekr_preloader').css('display','block');
                        $("#zamowienia").html('Pobieranie danych ...');
                        $.get('klienci/klienci_zakl_zamowienia.php?tok=<?php echo Sesje::Token(); ?>', { id_klienta: <?php echo "'" . $info['customers_id'] . "'"; ?> }, function(data) {
                            if (data != '') {
                                $("#zamowienia").html(data);
                            }
                            $('#ekr_preloader').delay(100).fadeOut('fast');
                            pokazChmurki();
                        });
                      }                      
                      $('#ajax_zakladki').val( $('#ajax_zakladki').val() + ',' + nr );
                  }
                };     

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
                
                <input type="hidden" id="ajax_zakladki" value="" />
          
                <input type="hidden" name="akcja" value="zapisz" />

                <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                <input type="hidden" name="language_id" value="<?php echo $info['language_id']; ?>" />

                <table style="width:100%"><tr>
                
                    <td id="lewe_zakladki" style="vertical-align:top">
                        <a href="javascript:gold_tabs_horiz('0','')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                        <a href="javascript:gold_tabs_horiz('1','')" class="a_href_info_zakl" id="zakl_link_1">Dane adresowe</a> 
                        <a href="javascript:gold_tabs_horiz('9','');pokaz_dane('9')" class="a_href_info_zakl" id="zakl_link_9">Lista zamówień [<?php echo (int)Klienci::pokazIloscZamowienKlienta($info['customers_id']); ?>]</a> 
                        
                        <?php
                        // jezeli klient nie jest gosciem
                        if ( $info['customers_guest_account'] == '0' ) {
                        ?>
                        
                            <a href="javascript:gold_tabs_horiz('2','');pokaz_dane('2')" class="a_href_info_zakl" id="zakl_link_2">Zawartość koszyka [<?php echo Klienci::pokazIloscProduktowKoszyka($info['customers_id'], '0'); ?>]</a>
                            <a href="javascript:gold_tabs_horiz('3','');pokaz_dane('3')" class="a_href_info_zakl" id="zakl_link_3">Zawartość schowka [<?php echo Klienci::pokazIloscProduktowSchowka($info['customers_id'], '0'); ?>]</a>
                            <a href="javascript:gold_tabs_horiz('4','')" class="a_href_info_zakl" id="zakl_link_4">Zniżki klienta [<?php echo count($TblZnizki); ?>]</a>
                            <?php
                            // oblicza ile jest pozycji w tabeli punktow
                            $zapytanie_punkty = "SELECT customers_id FROM customers_points WHERE customers_id = '" . $info['customers_id'] . "'";
                            $sql_punkty = $db->open_query($zapytanie_punkty);
                            $ile_poz = (int)$db->ile_rekordow($sql_punkty);
                            ?>                        
                            <a href="javascript:gold_tabs_horiz('5','');pokaz_dane('5')" class="a_href_info_zakl" id="zakl_link_5">System punktów [<?php echo $ile_poz; ?>]</a>
                            <?php
                            $db->close_query($sql_punkty);
                            unset($ile_poz, $zapytanie_punkty, $sql_punkty);
                            //
                            // oblicza ile jest pozycji w tabeli punktow
                            $zapytanie_recenzje = "SELECT reviews_id FROM reviews WHERE customers_id = '" . $info['customers_id'] . "'";
                            $sql_recenzje = $db->open_query($zapytanie_recenzje);
                            $ile_poz = (int)$db->ile_rekordow($sql_recenzje);
                            ?>                        
                            <a href="javascript:gold_tabs_horiz('6','');pokaz_dane('6')" class="a_href_info_zakl" id="zakl_link_6">Recenzje [<?php echo $ile_poz; ?>]</a>
                            <?php
                            $db->close_query($sql_recenzje);
                            unset($ile_poz, $zapytanie_recenzje, $sql_recenzje);
                        
                        }
                        ?>      
                        
                        <a href="javascript:gold_tabs_horiz('7','');pokaz_dane('7')" class="a_href_info_zakl" id="zakl_link_7">Statystyka</a>
                        <a href="javascript:gold_tabs_horiz('8','')" class="a_href_info_zakl" id="zakl_link_8">Uwagi</a>
                    </td>
                    
                    <?php $licznik_zakladek = 0; ?>

                    <td id="prawa_strona" style="vertical-align:top">
                    
                        <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                    
                        <div id="zakl_id_0" style="display:none;">
                        
                          <?php if ( $info['customers_guest_account'] == '0' ) { ?>
                        
                          <p>
                            <label>Status konta:</label>
                            <input type="radio" value="1" name="aktywnosc" <?php echo ( $info['customers_status'] == '1' ? 'checked="checked"' : '' ); ?> /> aktywne
                            <input type="radio" value="0" name="aktywnosc" <?php echo ( $info['customers_status'] == '0' ? 'checked="checked"' : '' ); ?> /> nieaktywne
                          </p> 
                          
                          <?php } else { ?>
                          
                          <p>
                            <span class="klient_gosc">Ten klient <b>nie jest zarejestrowany</b> - konto tylko do realizacji zamówienia.</span>
                          </p>
                          
                          <?php } ?>

                          <p>
                            <label class="required">Adres e-mail:</label>
                            <input type="text" name="email" id="email" size="53" value="<?php echo $info['customers_email_address']; ?>" class="toolTipText" title="Adres wykorzystywany do logowania oraz do korespondencji" />
                          </p>      

                          <?php if ( $info['customers_guest_account'] == '0' ) { ?>
                                    
                          <p>
                            <label>Login:</label>
                            <input type="text" name="nick" id="nick" size="53" value="<?php echo $info['customers_nick']; ?>" class="toolTipText" title="Może być używany do logowania zamiennie z wprowadzonym adresem e-mail" />
                          </p>                             
                          
                          <?php } ?>

                          <p>
                            <label class="required">Imię:</label>
                            <input type="text" name="imie" id="imie" size="53" value="<?php echo Funkcje::formatujTekstInput($info['customers_firstname']); ?>" />
                          </p> 

                          <p>
                            <label class="required">Nazwisko:</label>
                            <input type="text" name="nazwisko" id="nazwisko" size="53" value="<?php echo Funkcje::formatujTekstInput($info['customers_lastname']); ?>" />
                          </p>

                          <?php
                          if ( KLIENT_POKAZ_PLEC == 'tak' ) {
                            ?>
                            <p>
                              <label>Płeć:</label>
                              <input type="radio" value="f" name="plec" <?php echo ( $info['customers_gender'] == 'f' ? 'checked="checked"' : '' ); ?> /> kobieta
                              <input type="radio" value="m" name="plec" <?php echo ( $info['customers_gender'] == 'm' ? 'checked="checked"' : '' ); ?> /> mężczyzna
                            </p> 
                            <?php
                          }
                          ?>

                          <?php
                          if ( KLIENT_POKAZ_DATE_URODZENIA == 'tak' ) {
                            ?>
                            <p>
                              <label>Data urodzenia:</label>
                              <input type="text" name="data_urodzenia" id="data_urodzenia" size="30" value="<?php echo ((Funkcje::czyNiePuste($info['customers_dob'])) ? date('d-m-Y', strtotime($info['customers_dob'])) : ''); ?>" class="datepicker" />
                            </p> 
                            <?php
                          }
                          ?>

                          <?php
                          if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                            ?>
                          <p>
                            <label>Numer telefonu:</label>
                            <input type="text" name="telefon" id="telefon" size="32" value="<?php echo $info['customers_telephone']; ?>" />
                          </p>
                            <?php
                          }
                          ?>

                          <?php
                          if ( KLIENT_POKAZ_FAX == 'tak' ) {
                            ?>
                            <p>
                              <label>Numer faxu:</label>
                              <input type="text" name="fax" id="fax" size="32" value="<?php echo $info['customers_fax']; ?>" />
                            </p>
                            <?php
                          }
                          ?>
                          
                          <?php if ( $info['customers_guest_account'] == '0' ) { ?>

                          <p>
                            <label>Grupa klientów:</label>
                            <?php
                            $tablica = Klienci::ListaGrupKlientow(false);
                            echo Funkcje::RozwijaneMenu('grupa', $tablica, $info['customers_groups_id'] ); ?>
                          </p>

                          <p>
                            <label>Indywidualny rabat [%]:</label>
                            <input type="text" name="rabat" id="rabat" value="<?php echo $info['customers_discount']; ?>" size="5" class="toolTip" title="liczba z zakresu -100 do 0" />
                          </p>
                          
                          <?php } else { ?>
                            <input type="hidden" name="grupa" id="grupa" value="1" size="5" />
                          <?php } ?>
                          
                          <p>
                            <label>Id klienta w programie magazynowym:</label>
                            <input type="text" name="id_klienta_magazyn" size="20" value="<?php echo $info['customers_id_private']; ?>" />
                          </p>                          
                          
                          <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />

                          <p>
                            <label>Subskrypcja biuletynu:</label>
                            <input type="checkbox" value="1" name="biuletyn" onclick="pokazGrupyNewsletter()" id="biuletyn" <?php echo ( $info['customers_newsletter'] == '1' ? 'checked="checked"' : '' ); ?> />
                          </p>    
                  
                          <?php
                          $TablicaGrup = Newsletter::GrupyNewslettera();
                          if ( count($TablicaGrup) > 0 ) {
                          ?>
                          <div id="grupy_newslettera" <?php echo ( $info['customers_newsletter'] == '0' ? 'style="display:none"' : '' ); ?> >
                            <table>
                                <tr>
                                    <td><label>Przypisany do grup newslettera:</label></td>   
                                    <td>
                                    
                                    <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie klient będzie przypisany do wszystkich grup</span>
                                    
                                    <?php
                                    foreach ($TablicaGrup as $Grupa) {
                                        //
                                        echo '<input type="checkbox" value="' . $Grupa['id'] . '" name="newsletter_grupa[]" ' . ((in_array($Grupa['id'], explode(',',$info['customers_newsletter_group']))) ? 'checked="checked"' : '') . ' /> ' . $Grupa['text'] . '<br />';
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

                          <div style="margin-top:10px;margin-left:10px;">
                          <?php echo Klienci::pokazDodatkowePolaKlientow($info['customers_id'],$info['language_id']); ?>
                          </div>

                        </div>
                        
                        <?php // ********************************************* KSIAZKA ADRESOWA *************************************************** ?>
                        
                        <div id="zakl_id_1" style="display:none;">

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
                            <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo Funkcje::formatujTekstInput($info['entry_company']); ?>" size="53" />
                          </p>

                          <p id="nip" <?php echo ( $info['entry_nip'] != '' ? '' : 'style="display:none;"' ); ?> class="required">
                            <label class="required">Numer NIP:</label>
                            <input type="text" name="nip_firmy" id="nip_firmy" value="<?php echo $info['entry_nip']; ?>" size="32" />
                          </p>

                          <p>
                            <label class="required">Ulica i numer domu:</label>
                            <input type="text" name="ulica" id="ulica" size="53" value="<?php echo Funkcje::formatujTekstInput($info['entry_street_address']); ?>" />
                          </p>                          
                                   
                          <p>
                            <label class="required">Kod pocztowy:</label>
                            <input type="text" name="kod_pocztowy" id="kod_pocztowy" size="12" value="<?php echo $info['entry_postcode']; ?>" />
                          </p> 

                          <p>
                            <label class="required">Miejscowość:</label>
                            <input type="text" name="miasto" id="miasto" size="53" value="<?php echo Funkcje::formatujTekstInput($info['entry_city']); ?>" />
                          </p>

                          <p>
                            <label class="required">Kraj:</label>
                            <?php
                            $tablicaPanstw = Klienci::ListaPanstw();
                            echo Funkcje::RozwijaneMenu('panstwo', $tablicaPanstw, $info['entry_country_id'], 'id="selection"'); ?>
                          </p>

                          <?php
                          if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                            ?>
                            <p>
                              <label for="selectionresult">Województwo:</label>
                              <?php
                              $tablicaWojewodztw = Klienci::ListaWojewodztw($info['entry_country_id']);
                              echo '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', $tablicaWojewodztw, $info['entry_zone_id']).'</span>';
                              ?>
                            </p>
                            <?php
                          }
                          ?>

                        </div>
                        
                        <?php
                        if ( $info['customers_guest_account'] == '0' ) {
                        ?>
                        
                        <?php // ********************************************* KOSZYK *************************************************** ?>
                        
                        <div id="zakl_id_2" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana" id="koszyk_klienta">
                                <span class="maleInfo">Klient nie ma nic w koszyku</span>
                            </div>

                        </div>    
                        
                        <?php // ********************************************* SCHOWEK *************************************************** ?>
                        
                        <div id="zakl_id_3" style="display:none;" class="ZaklCent">

                            <div class="pozycja_edytowana" id="schowek_klienta">
                                <span class="maleInfo">Klient nie ma nic w schowku</span>
                            </div>

                        </div>     

                        <?php // ********************************************* ZNIZKI KLIENTA *************************************************** ?>
                        
                        <div id="zakl_id_4" style="display:none;">
                        
                            <div class="pozycja_edytowana">

                            <?php
                            //
                            if (count($TblZnizki) > 0) {
                                //
                                ?>
                                <div class="obramowanie_tabeli">
                                    <table class="listing_tbl">
                                    <tr class="div_naglowek">
                                      <td style="text-align:center">Typ</td>
                                      <td style="text-align:center">Nazwa</td>
                                      <td style="text-align:right">Wartość</td>
                                    </tr>                                
                                    <?php
                                    //
                                    for ($j = 0, $cj = count($TblZnizki); $j < $cj; $j++) {                                   
                                        if ($TblZnizki[$j][0] == $TblZnizki[$j][1]) {
                                            //
                                            echo '<tr class="pozycja_off"><td class="typ">-</td><td><b>' . $TblZnizki[$j][0] . '</b></td><td class="znizki">' . $TblZnizki[$j][2] . ' %</td></tr>';
                                            //
                                          } else {
                                            //
                                            echo '<tr class="pozycja_off"><td class="typ">' . $TblZnizki[$j][0] . '</td><td><b>' . $TblZnizki[$j][1] . '</b></td><td class="znizki">' . $TblZnizki[$j][2] . ' %</td></tr>';
                                            //
                                        }
                                    }
                                    //
                                    ?>
                                    </table>
                                </div>
                                <?php
                            } else {
                                echo '<div class="pozycja_edytowana">Brak zniżek przypisanych do konta klienta</div>';
                            }
                            unset($TblZnizki);
                            ?>
                            
                            </div>
                            
                        </div>  

                        <?php // ********************************************* SYSTEM PUNKTOW *************************************************** ?>
                        
                        <div id="zakl_id_5" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana" id="punkty">
                                <span class="maleInfo">Brak punktów</span>
                            </div>

                        </div>     
                        
                        <?php // ********************************************* RECENZJE *************************************************** ?>
                        
                        <div id="zakl_id_6" style="display:none;" class="ZaklCent">
                        
                            <div class="pozycja_edytowana" id="recenzje">
                                <span class="maleInfo">Klient nie napisał żadnej recenzji</span>
                            </div>

                        </div>  

                        <?php } ?>

                        <?php // ********************************************* STATYSTYKA *************************************************** ?>
                        
                        <div id="zakl_id_7" style="display:none;">
                        
                            <div id="statystyki">
                                <span class="maleInfo">Brak statystyk dla klienta</span>
                            </div>

                        </div>                        

                        <?php // ********************************************* UWAGI *************************************************** ?>
                        
                        <div id="zakl_id_8" style="display:none;">
                           <p>
                             <label style="width:70px">Uwagi:</label>
                             <textarea name="notatki" cols="100" rows="10" class="toolTipText" title="Zawartość informacji widoczna tylko dla obsługi sklepu."><?php echo $info['customers_dod_info']; ?></textarea>
                          </p>                                        
                        </div>  

                        <?php // ********************************************* LISTA ZAMOWIEN *************************************************** ?>
                        
                        <div id="zakl_id_9" style="display:none;">
                        
                            <div class="pozycja_edytowana" id="zamowienia">
                                <span class="maleInfo">Brak zamówień dla klienta</span>
                            </div>

                        </div>                          

                        <?php
                        $zakladka = '0';
                        if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                        unset($_GET['zakladka']);
                        ?>
                        <script type="text/javascript">
                        //<![CDATA[
                        gold_tabs_horiz(<?php echo $zakladka; ?>,'0'); pokaz_dane(<?php echo $zakladka; ?>,'0');
                        //]]>
                        </script>                         
                    
                    </td>
                </tr></table>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
                </div>            
            
            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>
            
          </div>                

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
