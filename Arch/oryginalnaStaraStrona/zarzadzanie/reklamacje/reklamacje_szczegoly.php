<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //
        $pola = array(
                array('complaints_status_id',$filtr->process($_POST['status'])),
                array('complaints_date_modified','now()'),
        );
        $db->update_query('complaints' , $pola, " complaints_id  = '".(int)$_POST["id"]."'");	
        unset($pola);

        $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_var_id = 'EMAIL_ZMIANA_STATUSU_REKLAMACJI'";
        $sql = $db->open_query($zapytanie_tresc);
        $tresc = $sql->fetch_assoc();

        define('NUMER_REKLAMACJI', $filtr->process($_POST["id_reklamacji"]));
        define('LINK', Seo::link_SEO('reklamacje_szczegoly.php',$filtr->process($_POST["id_reklamacji"]),'reklamacja','',true));
        define('STATUS_REKLAMACJI', Reklamacje::pokazNazweStatusuReklamacji( (int)$_POST['status'], (int)$_POST["jezyk"] ));
        define('DATA_REKLAMACJI', date('d-m-Y',strtotime($filtr->process($_POST['data_reklamacji'])) ));
        if ( isset($_POST["dolacz_komentarz"]) ) {
          define('KOMENTARZ', $filtr->process($_POST['komentarz']));
        } else {
          define('KOMENTARZ', '');
        }

        if ( isset($_POST['info_mail']) ) {

          $email = new Mailing;

          if ( $tresc['email_file'] != '' ) {
              $tablicaZalacznikow = explode(';', $tresc['email_file']);
          } else {
              $tablicaZalacznikow = array();
          }

          $powiadomienie_mail = $_POST['info_mail'];

          $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
          $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
          $cc              = Funkcje::parsujZmienne($tresc['dw']);

          $adresat_email   = $filtr->process($_POST['adres_email_klienta']);
          $adresat_nazwa   = $filtr->process($_POST['nazwa_klienta']);

          $temat           = Funkcje::parsujZmienne($tresc['email_title']);
          $tekst           = $tresc['description'];
          $zalaczniki      = $tablicaZalacznikow;
          $szablon         = $tresc['template_id'];
          $jezyk           = (int)$_POST["jezyk"];


          $tekst = Funkcje::parsujZmienne($tekst);
          $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', $tekst);

          $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, false);

        } else {
          $powiadomienie_mail = '0';
        }

        //
        $pola = array(
                array('complaints_id',(int)$_POST["id"]),
                array('complaints_status_id',$filtr->process($_POST['status'])),
                array('date_added','now()'),
                array('customer_notified ',$powiadomienie_mail),
                array('comments',$filtr->process($_POST['komentarz']))
        );

        $db->insert_query('complaints_status_history' , $pola);
        unset($pola);

        //
        Funkcje::PrzekierowanieURL('reklamacje_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]).'');

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Szczegóły reklamacji</div>
    <div id="cont">

    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    
    $zapytanie = "SELECT * FROM complaints cu
                    LEFT JOIN customers c ON cu.complaints_customers_id = c.customers_id
                    LEFT JOIN address_book a ON c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id where cu.complaints_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
                    
    $sql = $db->open_query($zapytanie);

    if ((int)$db->ile_rekordow($sql) > 0) {
    
        $info = $sql->fetch_assoc();
        ?>

          <div class="cmxform"> 
          
            <div class="poleForm">
                <div class="naglowek">Edycja danych - reklamacja nr <?php echo $info['complaints_rand_id']; ?></div>

                <table style="width:100%"><tr>
                
                    <td id="lewe_zakladki" style="vertical-align:top">
                        <a href="javascript:gold_tabs_horiz('0','0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>  
                        <?php
                        // oblicza ile bylo zmian statusow
                        $zapytanie_statusy = "SELECT complaints_status_history_id, complaints_status_id FROM complaints_status_history WHERE complaints_id = '" . $info['complaints_id'] . "'";
                        $sql_statusy = $db->open_query($zapytanie_statusy);
                        $ile_statusow = (int)$db->ile_rekordow($sql_statusy);
                        ?>
                        <a href="javascript:gold_tabs_horiz('1','1')" class="a_href_info_zakl" id="zakl_link_1">Historia reklamacji [<?php echo $ile_statusow; ?>]</a>
                        <?php
                        unset($ile_statusow, $zapytanie_statusy, $sql_statusy);
                        ?>
                    </td>
                    
                    <?php $licznik_zakladek = 0; ?>

                    <td id="prawa_strona" style="vertical-align:top">
                    
                        <?php // ********************************************* INFORMACJE OGOLNE *************************************************** ?>
                    
                        <div id="zakl_id_0" style="display:none;">
                        
                            <div class="obramowanie_tabeli">
                              <table class="listing_tbl">
                                <tr class="div_naglowek">
                                  <td colspan="2">
                                  <div class="lf">Dane podstawowe reklamacji</div>
                                  <div class="LinEdytuj"><a href="reklamacje/reklamacje_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=0">edytuj</a></div>
                                  </td>
                                </tr>                              
                                <tr class="pozycja_off">
                                  <td style="width:25%">Tytuł reklamacji:</td>
                                  <td class="pozycjaBold" style="width:65%"><?php echo $info['complaints_subject']; ?></td>
                                </tr>                              
                                <tr class="pozycja_off">
                                  <td>Data zgłoszenia:</td>
                                  <td class="pozycjaBold"><?php echo date('d-m-Y H:i:s', strtotime($info['complaints_date_created'])); ?></td>
                                </tr>
                                <tr class="pozycja_off">
                                  <td>Data ostatniej korespondencji:</td>
                                  <?php
                                  $zapytanieStatus = "select max(date_added) as data from complaints_status_history where complaints_id = '" . $info['complaints_id'] . "'";
                                  $sqlp = $db->open_query($zapytanieStatus);
                                  $infoData = $sqlp->fetch_assoc();                                  
                                  ?>
                                  <td class="pozycjaBold"><?php echo date('d-m-Y H:i:s', strtotime($infoData['data'])); ?></td>
                                  <?php 
                                  $db->close_query($sqlp);
                                  unset($zapytanieStatus, $infoData);                                  
                                  ?>
                                </tr>
                                <tr class="pozycja_off">
                                  <td>Klient:</td>
                                  <td class="pozycjaBold">
                                  <?php
                                  $wyswietlana_nazwa = '';
                                  // jezeli klient jest z bazy
                                  if ($info['complaints_customers_id'] > 0) {
                                      //
                                      if ($info['entry_company'] != '') {
                                        $wyswietlana_nazwa .= '<span class="firma">'.$info['entry_company'] . '</span>';
                                      }
                                      $wyswietlana_nazwa .= $info['entry_firstname'] . ' ' . $info['entry_lastname'] . '<br />';
                                      $wyswietlana_nazwa .= $info['entry_street_address']. '<br />';
                                      $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'] . '<br />';
                                      //
                                    } else {
                                      //
                                      $wyswietlana_nazwa = nl2br($info['complaints_customers_name'] . ' <br />' . $info['complaints_customers_address']);
                                      //
                                  }   
                                  echo $wyswietlana_nazwa;
                                  unset($wyswietlana_nazwa);
                                  ?>
                                  </td>
                                </tr>
                                <tr class="pozycja_off">
                                  <td>Adres e-mail:</td>
                                  <td class="pozycjaBold"><?php echo $info['complaints_customers_email']; ?></td>
                                </tr>
                                <tr class="pozycja_off">
                                  <td>Nr zamówienia:</td>
                                  <td class="pozycjaBold"><?php echo $info['complaints_customers_orders_id']; ?></td>
                                </tr>                                
                                <tr class="pozycja_off">
                                  <?php
                                  // pobieranie informacji od uzytkownikach
                                  if ($info['complaints_service'] > 0) {
                                      //
                                      $zapytanie_uzytkownicy = "select distinct * from admin where admin_id = '" . $info['complaints_service'] . "'";
                                      $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
                                      $uzytkownicy = $sql_uzytkownicy->fetch_assoc();
                                      $obsluga = $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname'];
                                      $db->close_query($sql_uzytkownicy); 
                                      unset($zapytanie_uzytkownicy, $uzytkownicy);
                                    } else {
                                      $obsluga = '-';
                                  }
                                  //                                   
                                  ?>
                                  <td>Opiekun reklamacji:</td>
                                  <td class="pozycjaBold"><?php echo $obsluga; ?></td>
                                </tr>                                
                                <tr class="pozycja_off">
                                  <td>Wydruk zgłoszenia:</td>
                                  <td class="pozycjaBold"><a href="reklamacje/reklamacje_reklamacja_pdf.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>"><img src="obrazki/pdf_2.png" alt="Wydruk reklamacji" title="Wydruk reklamacji" /></a></td>
                                </tr>                                
                              </table>
                            </div>
                        </div>
                        
                        
                        <?php // ********************************************* HISTORIA *************************************************** ?>
                        
                        <div id="zakl_id_1" style="display:none;">

                          <?php
                          $zapytanie_statusy = "SELECT complaints_status_id, date_added, customer_notified, comments FROM complaints_status_history WHERE complaints_id = '" . (int)$_GET['id_poz'] . "' ORDER BY date_added";
                          $sql_statusy = $db->open_query($zapytanie_statusy);

                          if ((int)$db->ile_rekordow($sql_statusy) > 0) {


                            ?>
                            <div class="obramowanie_tabeli">
                            
                              <table class="listing_tbl" id="StatRekl">
                                <tr class="div_naglowek">
                                  <td>Data dodania</td>
                                  <td>Mail do klienta</td>
                                  <td>Status</td>
                                  <td style="width:50%">Komentarze</td>
                                </tr>
                                <?php while ($info_statusy = $sql_statusy->fetch_assoc()) { ?>
                                <tr class="pozycja_off">
                                  <td><?php echo date('d-m-Y H:i', strtotime($info_statusy['date_added'])); ?></td>
                                  <td><img src="obrazki/<?php echo ( $info_statusy['customer_notified'] == '1' ? 'tak.png' : 'tak_off.png' ); ?>" alt="" /></td>
                                  <td><?php echo Reklamacje::pokazNazweStatusuReklamacji($info_statusy['complaints_status_id']); ?></td>
                                  <td style="text-align:left"><?php echo $info_statusy['comments']; ?></td>
                                </tr>
                                <?php } ?>
                              </table>
                              
                            </div>
                            <?php } ?>

                            <div class="pozycja_edytowana" style="padding-top:20px;">
                            
                                <div class="info_content">

                                  <form action="reklamacje/reklamacje_szczegoly.php" method="post" id="reklamacjeUwagiForm" class="cmxform">

                                    <div>
                                        <input type="hidden" name="akcja" value="zapisz" />
                                        <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                                        <input type="hidden" name="id_reklamacji" value="<?php echo $info['complaints_rand_id']; ?>" />
                                        <input type="hidden" name="adres_email_klienta" value="<?php echo $info['complaints_customers_email']; ?>" />
                                        <input type="hidden" name="nazwa_klienta" value="<?php echo $info['complaints_customers_name']; ?>" />
                                        <input type="hidden" name="data_reklamacji" value="<?php echo $info['complaints_date_created']; ?>" />
                                        <input type="hidden" name="zakladka" value="1" />
                                    </div>
                                    
                                    <p id="wersja">
                                      <label>W jakim języku wysłać email:</label>
                                      <?php
                                      echo Funkcje::RadioListaJezykow('onclick="UkryjZapisz(0)"');
                                      ?>
                                    </p>
                                    
                                    <script type="text/javascript">
                                    //<![CDATA[
                                    function UkryjZapiszKomentarz(id) {
                                        if (parseInt(id) > 0) {
                                            $('#przyciski').slideDown('fast');     
                                        } else {
                                            $('#przyciski').slideUp('fast');  
                                            $("#komentarz_tresc").val('');
                                        }   
                                        //
                                        $('#ladujKomentarz').fadeIn('fast');
                                        $.post('reklamacje/standardowe_komentarze.php', { jezyk: 1, id: id, nazwy: 'tak' }, function(data){
                                          $("#komentarz").html(data);
                                          $('#ladujKomentarz').fadeOut('fast');
                                          $("#komentarz_tresc").val('');
                                        });                   
                                    }   
                                    function ZmienKomentarz(id) {
                                        var jezyk = $("input[name='jezyk']:checked").val();
                                        $('#ladujKomentarz').fadeIn('fast');
                                        $.post('reklamacje/standardowe_komentarze.php', { jezyk: jezyk, id: id, nazwy: 'nie' }, function(data){
                                          $("#komentarz_tresc").val(data);
                                          $('#ladujKomentarz').fadeOut('fast');
                                        });                 
                                    }
                                    
                                    $(document).ready(function() {
                                    
                                        $("input[name=jezyk]").change(function(){
                                          $("#status option:first").prop("selected",true); 
                                          $('#komentarz').html('<option selected="selected" value="0">--- najpierw wybierz status zamówienia ---</option>');
                                          $("#komentarz_tresc").val('');
                                        });                
                                    
                                    });
                                    //]]>
                                    </script>                                  

                                    <p>
                                      <label>Nowy status reklamacji:</label>
                                      <?php
                                      $tablica = Reklamacje::ListaStatusowReklamacji(true, '--- wybierz z listy ---');
                                      echo Funkcje::RozwijaneMenu('status', $tablica,'','id="status" onchange="UkryjZapiszKomentarz(this.value)" style="width:350px;"'); ?>
                                    </p>
                                    <p>
                                      <label>Standardowy komentarz:</label>
                                      <?php
                                      $tablica = Array();
                                      $tablica[] = array('id' => '0', 'text' => '--- najpierw wybierz status reklamacji ---');
                                      echo Funkcje::RozwijaneMenu('status_komentarz', $tablica,'','id="komentarz" onchange="ZmienKomentarz(this.value)" style="width:350px;"'); ?> 
                                    </p>
                                    
                                    <div id="ladujKomentarz"><img src="obrazki/_loader_small.gif" alt="" /></div>

                                    <p>
                                      <label>Poinformuj klienta e-mail:</label>
                                      <input type="checkbox" checked="checked" value="1" name="info_mail" id="info_mail" class="toolTip" title="Informacja o zmianie statusu zostanie przesłana do klienta" />
                                    </p>

                                    <p>
                                      <label>Dołącz komentarz do maila:</label>
                                      <input type="checkbox" checked="checked" value="1" name="dolacz_komentarz" id="dolacz_komentarz" class="toolTip" title="Informacja komentarza zostanie dołączona do maila z powiadomieniem do klienta" />
                                    </p>

                                    <p>
                                      <label>Komentarz:</label>
                                      <textarea cols="100" rows="10" name="komentarz" class="wysiwyg" id="komentarz_tresc"></textarea>
                                    </p>

                                    <div class="przyciski_dolne" id="przyciski" style="display:none">
                                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                                    </div>

                                  </form>

                                </div>
                             
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

            </div>
          </div>
          
          <div class="przyciski_dolne">
                <button type="button" class="przyciskNon" onclick="cofnij('reklamacje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>', 'reklamacje');">Powrót</button>    
          </div>           

          <?php

          $db->close_query($sql);

      } else {

          echo '<div class="poleForm">
                    <div class="naglowek">Edycja danych</div>
                    <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                </div>';
            
      }
      ?>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
