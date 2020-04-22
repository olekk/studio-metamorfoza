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
    
    <div id="naglowek_cont">Wysłanie wiadomości e-mail do klienta</div>
    <div id="cont">

          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() { 

            // Skrypt do wysylania zalacznikow poprzez AJAX
            var options = { 
                target:     '#przetwarzanie', 
                url:        'ajax/wyslij_email.php',
                beforeSend:function(){
                    $("#przetwarzanie").show();
                },
                complete:function(){
                    $("#formularz").slideUp('fast', function() {
                        $("#kontynuuj").show();
                    });
                }
            }; 
            
            $('#emailForm').ajaxForm(options);
             
            // Skrypt do walidacji formularza
            $("#emailForm").validate({
              rules: {
                temat: {required: true},
              }
            });

            $('#upload').MultiFile({
              max: <?php echo EMAIL_ILOSC_ZALACZNIKOW; ?>,
              accept:'<?php echo EMAIL_DOZWOLONE_ZALACZNIKI; ?>',
              STRING: {
               denied:'Nie można przesłać pliku w tym formacie $ext!',
               duplicate:'Taki plik jest już dodany:\n$file!',
               selected:'Wybrany plik: $file'
              }
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
              <form action="klienci/klienci_wyslij_email.php" method="post" id="emailForm" class="cmxform" enctype="multipart/form-data">          

                <div class="poleForm">

                  <div class="naglowek">Adresat wiadomości : <?php echo $info['customers_firstname'].' '.$info['customers_lastname'].'; email: '. $info['customers_email_address']; ?></div>

                  <div id="przetwarzanie" style="padding-bottom:20px;display:none;"><img src="obrazki/_loader_small.gif" alt="przetwarzanie..." /></div>

                  <div id="formularz">

                    <div class="pozycja_edytowana">

                      <div class="info_content">

                        <input type="hidden" name="akcja" value="zapisz" />

                        <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                        <input type="hidden" id="adres_email" name="adres_email" value="<?php echo $info['customers_email_address']; ?>" />
                        <input type="hidden" id="adresat" name="adresat" value="<?php echo $info['customers_firstname'].' '.$info['customers_lastname']; ?>" />

                        <p>
                            <label>Szablon emaila:</label>
                            <?php
                            $tablica = Funkcje::ListaSzablonowEmail(false);
                            echo Funkcje::RozwijaneMenu('szablon', $tablica ); ?>
                        </p>

                        <p id="wersja">
                          <label>Wersja językowa szablonu:</label>
                          <?php
                          echo Funkcje::RadioListaJezykow();
                          ?>
                        </p>

                        <p>
                          <label class="required">Temat:</label>
                          <input type="text" name="temat" id="temat" size="83" value="" />
                        </p>

                        <p>
                          <label>Do wiadomości:</label>
                          <input type="text" name="cc" id="cc" size="83" value="" class="toolTipText" title="Rozdzielone przecinkami adresy e-mail na które ma zostać przesłana kopia wiadomości" />
                        </p>

                        <p>
                          <label>Treść wiadomości:</label>
                          <textarea id="wiadomosc" name="wiadomosc" class="wysiwyg" cols="150" rows="10" title="Wpisz tylko treść wiadomości - pozostałe elementy zostaną dołączone z domyślnego szablonu wiadomości email."></textarea>
                        </p>

                        <p style="padding-top:15px;padding-bottom:10px;">
                          <label>Załączniki:</label>
                          <input type="file" name="file[]" id="upload" size="53" />
                        </p>
                        
                        <div class="maleInfo" style="margin-left:180px">Dozwolne formaty plików: <?php echo implode(', ', explode('|', EMAIL_DOZWOLONE_ZALACZNIKI)); ?></div>
                        
                      </div>

                      <div class="przyciski_dolne">
                        <input id="form_submit" type="submit" class="przyciskNon" value="Wyślij wiadomość e-mail" />
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
