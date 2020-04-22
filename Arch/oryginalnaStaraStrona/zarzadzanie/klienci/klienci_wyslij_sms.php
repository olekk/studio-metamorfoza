<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        Funkcje::PrzekierowanieURL('klienci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysłanie wiadomości SMS</div>
    <div id="cont">
          
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {

            // wysylka wiadomosci
            $('#form_submit').click(function(){

              var frm = $("#smsForm");
              var response_text = $('#przetwarzanie');
              var response_form = $('#formularz');
              var response_dalej = $('#kontynuuj');
              var dane = frm.serialize();
              var daneTbl = frm.serializeArray();
              var proceed = true;

              $.each(daneTbl, function(index, value) {
                var elem = frm.find('[name='+daneTbl[index].name+']');
                var elem_type = elem.prop('nodeName');
                if (elem.hasClass('required') && elem.val() === '') {
                  $('#error_' + daneTbl[index].name).show();
                  proceed = false;
                }
              });

              response_text.hide();
 
              if (proceed == true) {
                response_text.html('<img src="obrazki/_loader_small.gif">').show();

                $.post('ajax/wyslij_sms.php?tok=<?php echo Sesje::Token(); ?>', dane, function(data){
                  response_form.slideUp();
                  response_text.html(data);
                  response_dalej.slideDown();
                });
              }
              
              return false;
            });
          });
          //]]>
          </script>        

          <?php
            
          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }    
            
          $zapytanie = "select * from customers where customers_id = '" . (int)$_GET['id_poz'] . "'";
          $sql = $db->open_query($zapytanie);

          if ((int)$db->ile_rekordow($sql) > 0) {

            $info = $sql->fetch_assoc();
            ?>
            <form action="ajax/wyslij_sms.php" method="post" id="smsForm" class="cmxform" >

              <div class="poleForm">

                <div class="naglowek">Adresat wiadomości : <?php echo $info['customers_firstname'].' '.$info['customers_lastname'].' tel: '. $info['customers_telephone']; ?></div>

                <div id="przetwarzanie" style="padding-bottom:20px;display:none;"></div>

                <div id="formularz">

                  <div class="pozycja_edytowana">

                    <div class="info_content">

                      <input type="hidden" name="akcja" value="zapisz" />
                    
                      <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                      <input type="hidden" id="telefon" name="telefon" value="<?php echo $info['customers_telephone']; ?>" />

                      <p>
                        <label class="required">Wiadomość:</label>
                        <textarea class="required toolTipText" cols="50" rows="7" id="wiadomosc" name="wiadomosc" onkeyup="licznik_znakow(this,'iloscZnakow',160)" onclick="$('#error_wiadomosc').hide()" title="Pojedyncza wiadomość SMS może mieć do 160 znaków (bez znaków specjalnych, w tym polskich). Wiadomość, która posiada znaki specjalne, może liczyć do 70 znaków." ></textarea>
                      </p>
                      
                      <label for="wiadomosc" class="error" id="error_wiadomosc" style="display:none; margin-left:175px">To pole jest wymagane.</label>
                      
                      <p>
                        <label></label>
                        <span style="display:inline-block; margin:0px 0px 8px 4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakow">160</span></span>
                      </p>
                                            
                      <?php
                      if ( SMS_NADAWCA != '' ) {
                        ?>
                        <p>
                          <label>Tryb wysyłania wiadomości:</label>
                          <input type="radio" value="0" name="tryb" <?php echo ( SMS_TYP_WIADOMOSCI == 'pro' ? 'checked="checked"' : '' ); ?> class="toolTipTop" title="proSMS - wymaga posiadania zweryfikowanego nadawcy w serwisie smsAPI.pl" /> proSMS
                          <input type="radio" value="1" name="tryb" <?php echo ( SMS_TYP_WIADOMOSCI == 'eco' ? 'checked="checked"' : '' ); ?> class="toolTipTop" title="ecoSMS - wiadomość wysyłana z losowego numeru dziewięciocyfrowego" /> ecoSMS
                        </p>
                        <?php
                      } else {
                        echo '<input type="hidden" id="tryb" name="tryb" value="1" />';
                      }
                      ?>
                    </div>

                    <div class="przyciski_dolne">
                      <input id="form_submit" type="submit" class="przyciskNon" value="Wyślij wiadomość SMS" />
                      <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','klienci');">Powrót</button> 
                    </div>

                  </div>

                </div>

                <div class="przyciski_dolne" id="kontynuuj" style="display:none;">
                  <button type="button" class="przyciskNon" onclick="cofnij('klienci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Kontynuuj</button> 
                </div>

              </div>

              </form>
            
              <?php
              
            } else {
            
                echo '<div class="poleForm">
                        <div class="naglowek">Wysyłanie wiadomości</div>
                        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                      </div>';

            }
          $db->close_query($sql);
          unset($zapytanie, $info);
          ?>
    </div>
    <?php
    include('stopka.inc.php');

}