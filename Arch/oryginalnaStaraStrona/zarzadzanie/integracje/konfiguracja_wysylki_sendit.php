<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      while (list($key, $value) = each($_POST)) {
        if ( $key != 'akcja' ) {
          $pola = array(
                  array('value',$filtr->process($value))
          );
          $db->update_query('settings' , $pola, " code = '".strtoupper($key)."'");	
          unset($pola);
        }
      }

      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'wysylki' ORDER BY sort ";
    $sql = $db->open_query($zapytanie);

    $parametr = array();

    if ( $db->ile_rekordow($sql) > 0 ) {
      while ($info = $sql->fetch_assoc()) {
        $parametr[$info['code']] = array($info['value'], $info['limit_values'], $info['description'], $info['form_field_type']);
      }
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów systemów wysyłkowych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych - Firma SendIt</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#form-sendit").validate({
                rules: {
                  integracja_sendit_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_sendit_wlaczony']:checked", "#form-sendit").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_sendit_api_pin: {required: function() {var wynik = true; if ( $("input[name='integracja_sendit_wlaczony']:checked", "#form-sendit").val() == "nie" ) { wynik = false; } return wynik; }}
                  }
              });

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);

            });
            //]]>
          </script>  

          <?php
          $adres_firmy  = Funkcje::PrzeksztalcAdres(DANE_ADRES_LINIA_1);
          ?>

          <div class="sledzenie">

            <form action="integracje/konfiguracja_wysylki_sendit.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_sendit" id="form-sendit" class="cmxform">
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="sendit" />
                
                <table class="listing_tbl">
                
                  <tr><td colspan="2" class="sledzenie_opis">
                    <div>Sendit.pl to platforma wysyłkowa, dzięki której szybko i wygodnie zamówisz krajowe oraz międzynarodowe usługi kurierskie w atrakcyjnych cenach.Korzystając z usług Sendit.pl masz pewność, że zadbamy o Twoje zamówienie na każdym etapie jego realizacji. Jeżeli pojawi się taka potrzeba, pomożemy także w procesie reklamacji. Z nami wysyłka jest pewna i bezpieczna.</div>
                    <img src="obrazki/logo/logo_sendit.png" alt="" />
                  </td></tr>                    

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz integrację SendIt:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SENDIT_WLACZONY']['1'], $parametr['INTEGRACJA_SENDIT_WLACZONY']['0'], 'integracja_sendit_wlaczony', $parametr['INTEGRACJA_SENDIT_WLACZONY']['2'], '', $parametr['INTEGRACJA_SENDIT_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SENDIT_SANBOX']['1'], $parametr['INTEGRACJA_SENDIT_SANBOX']['0'], 'integracja_sendit_sanbox', $parametr['INTEGRACJA_SENDIT_SANBOX']['2'], '', $parametr['INTEGRACJA_SENDIT_SANBOX']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Klucz API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_sendit_api_key" value="'.$parametr['INTEGRACJA_SENDIT_API_KEY']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_API_KEY']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Login API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_sendit_api_login" value="'.$parametr['INTEGRACJA_SENDIT_API_LOGIN']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_API_LOGIN']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Hasło API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_sendit_api_haslo" value="'.$parametr['INTEGRACJA_SENDIT_API_HASLO']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_API_HASLO']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Nadawca:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_nazwa" name="INTEGRACJA_SENDIT_NADAWCA_NAZWA" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_NAZWA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_NAZWA']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Adres:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_ulica" name="integracja_sendit_nadawca_ulica" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_ULICA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_ULICA']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Kod pocztowy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_kod_pocztowy" name="integracja_sendit_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_KOD_POCZTOWY']['0'].'" size="20" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_KOD_POCZTOWY']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Miejscowość:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_miasto" name="integracja_sendit_nadawca_miasto" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_MIASTO']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_MIASTO']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Kraj:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_kraj" name="integracja_sendit_nadawca_kraj" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_KRAJ']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_KRAJ']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_telefon" name="integracja_sendit_nadawca_telefon" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_TELEFON']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_TELEFON']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_email" name="integracja_sendit_nadawca_email" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_EMAIL']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_EMAIL']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Osoba kontaktowa:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_sendit_nadawca_kontakt" name="integracja_sendit_nadawca_kontakt" value="'.$parametr['INTEGRACJA_SENDIT_NADAWCA_KONTAKT']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SENDIT_NADAWCA_KONTAKT']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'sendit' ? $wynik : '' ); ?>
                      </div>
                    </td>
                  </tr>
                  
                </table>
                
            </form>

          </div>

        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
