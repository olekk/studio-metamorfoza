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
        <div class="naglowek">Edycja danych - Firma KurJerzy.pl</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#form-kurjerzy").validate({
                rules: {
                  integracja_kurjerzy_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_kurjerzy_wlaczony']:checked", "#form-kurjerzy").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_kurjerzy_api_pin: {required: function() {var wynik = true; if ( $("input[name='integracja_kurjerzy_wlaczony']:checked", "#form-kurjerzy").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_kurjerzy_wymiary_dlugosc: { digits: true },
                  integracja_kurjerzy_wymiary_szerokosc: { digits: true },
                  integracja_kurjerzy_wymiary_wysokosc: { digits: true }
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

            <form action="integracje/konfiguracja_wysylki_kurjerzy.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_kurjerzy" id="form-kurjerzy" class="cmxform">
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="kurjerzy" />
                
                <table class="listing_tbl">
                
                  <tr><td colspan="2" class="sledzenie_opis">
                    <div>Serwis KurJerzy.pl to pośrednik między Klientami a firmami kurierskimi. Oferujemy rozwiązania szybkie, bezpieczne oraz konkurencyjne cenowo. Współpracujemy z powszechnie uznanymi na rynku przesyłek kurierskich firmami: DHL, UPS oraz FedEx. KurJerzy.pl to tani kurier zapewniający: maksymalnie uproszczony proces składania zamówień, wiele możliwości bezpiecznej i szybkiej zapłaty za usługę, pomoc konsultanta oraz odpowiedzialność firmy w momencie wystąpienia jakichkolwiek problemów z przesyłką kurierską. Dodatkowo, Serwis oferuje takie mechanizmy, jak np.: Prepaid, zniżki dla sklepów, wyszukiwarka kodów pocztowych, możliwość śledzenia przesyłki kurierskiej oraz wiele innych.</div>
                    <img src="obrazki/logo/logo_kurjerzy.png" alt="" />
                  </td></tr>                    

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz integrację KurJerzy.pl:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KURJERZY_WLACZONY']['1'], $parametr['INTEGRACJA_KURJERZY_WLACZONY']['0'], 'integracja_kurjerzy_wlaczony', $parametr['INTEGRACJA_KURJERZY_WLACZONY']['2'], '', $parametr['INTEGRACJA_KURJERZY_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Klucz API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kurjerzy_api_key" value="'.$parametr['INTEGRACJA_KURJERZY_API_KEY']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_API_KEY']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">PIN API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kurjerzy_api_pin" value="'.$parametr['INTEGRACJA_KURJERZY_API_PIN']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_API_PIN']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_kurjerzy_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_KURJERZY_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' szerokość: <input type="text" name="integracja_kurjerzy_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_KURJERZY_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' wysokość: <input type="text" name="integracja_kurjerzy_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_KURJERZY_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Preferowana zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kurjerzy_zawartosc" value="'.$parametr['INTEGRACJA_KURJERZY_ZAWARTOSC']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_ZAWARTOSC']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Nadawca:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_nazwa" name="integracja_kurjerzy_nadawca_nazwa" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_NAZWA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_NAZWA']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Ulica:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_ulica" name="integracja_kurjerzy_nadawca_ulica" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_ULICA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_ULICA']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Numer domu / lokalu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_dom" name="integracja_kurjerzy_nadawca_dom" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_DOM']['0'].'" size="20" class="required toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_DOM']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Kod pocztowy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_kod_pocztowy" name="integracja_kurjerzy_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_KOD_POCZTOWY']['0'].'" size="20" class="required toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_KOD_POCZTOWY']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Miejscowość:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_miasto" name="integracja_kurjerzy_nadawca_miasto" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_MIASTO']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_MIASTO']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_telefon" name="integracja_kurjerzy_nadawca_telefon" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_TELEFON']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_TELEFON']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kurjerzy_nadawca_email" name="integracja_kurjerzy_nadawca_email" value="'.$parametr['INTEGRACJA_KURJERZY_NADAWCA_EMAIL']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_KURJERZY_NADAWCA_EMAIL']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'kurjerzy' ? $wynik : '' ); ?>
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
