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

      $pola = array(
              array('value','')
      );
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_FURGONETKA_%'");	
      unset($pola);

      while (list($key, $value) = each($_POST)) {
        if ( $key != 'akcja' ) {
          if ( is_array($value) ) {
              $wartosc = implode(';',$value);
          } else {
              $wartosc = $value;
          }
          $pola = array(
                  array('value',$wartosc)
          );
          $db->update_query('settings' , $pola, " code = '".strtoupper($key)."'");	
          unset($pola,$wartosc);
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
        <div class="naglowek">Edycja danych - Firma FURGONETKA</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#form-furgonetka").validate({
                rules: {
                  integracja_furgonetka_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_furgonetka_wlaczony']:checked", "#form-furgonetka").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_furgonetka_klient_ck: {required: function() {var wynik = true; if ( $("input[name='integracja_furgonetka_wlaczony']:checked", "#form-furgonetka").val() == "nie" ) { wynik = false; } return wynik; }},
                  }
              });

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);

            });
            //]]>
          </script>  

          <div class="sledzenie">

            <form action="integracje/konfiguracja_wysylki_furgonetka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="form-furgonetka" class="cmxform"> 
            
                <div>
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="system" value="furgonetka" />
                </div>

                <table class="listing_tbl">
                
                  <tr><td colspan="2" class="sledzenie_opis">
                    <div>Serwis Furgonetka.pl umożliwia korzystanie z szerokiego wachlarza usług kurierskich w bardzo atrakcyjnych cenach bez ograniczeń i potrzeby podpisywania umów. Oferujemy wygodne narzędzia, które pozwalają zarówno firmom, jak i osobom prywatnym, szybko i wygodnie zamówić kuriera.</div>
                    <img src="obrazki/logo/logo_furgonetka.png" alt="" />
                  </td></tr>                   

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz integrację FURGONETKA:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FURGONETKA_WLACZONY']['1'], $parametr['INTEGRACJA_FURGONETKA_WLACZONY']['0'], 'integracja_furgonetka_wlaczony', $parametr['INTEGRACJA_FURGONETKA_WLACZONY']['2'], '', $parametr['INTEGRACJA_FURGONETKA_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FURGONETKA_SANDBOX']['1'], $parametr['INTEGRACJA_FURGONETKA_SANDBOX']['0'], 'integracja_furgonetka_sandbox', $parametr['INTEGRACJA_FURGONETKA_SANDBOX']['2'], '', $parametr['INTEGRACJA_FURGONETKA_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Adres do logowania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_email" value="'.$parametr['INTEGRACJA_FURGONETKA_EMAIL']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_EMAIL']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Hasło do logowania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_password" value="'.$parametr['INTEGRACJA_FURGONETKA_PASSWORD']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_PASSWORD']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślny dostawca:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_FURGONETKA_KURIER']['0'];
                    ?>
                    <select name="integracja_furgonetka_kurier" class="toolTipText" title="<?php echo $parametr['INTEGRACJA_FURGONETKA_KURIER']['2']; ?>">
                        <option value="dpd" <?php echo ( $domyslna == 'dpd' ? 'selected="selected"' : '' ); ?>>DPD</option>
                        <option value="fedex" <?php echo ( $domyslna == 'fedex' ? 'selected="selected"' : '' ); ?>>Fedex</option>
                        <option value="ups" <?php echo ( $domyslna == 'ups' ? 'selected="selected"' : '' ); ?>>UPS</option>
                        <option value="inpost" <?php echo ( $domyslna == 'inpost' ? 'selected="selected"' : '' ); ?>>inPost</option>
                        <option value="kex" <?php echo ( $domyslna == 'kex' ? 'selected="selected"' : '' ); ?>>K-EX</option>
                        <!-- <option value="ruch" <?php echo ( $domyslna == 'ruch' ? 'selected="selected"' : '' ); ?>>Paczka w RUCHU</option> -->
                        <!-- <option value="poczta" <?php echo ( $domyslna == 'poczta' ? 'selected="selected"' : '' ); ?>>POCZTA</option> -->
                        <option value="xpress" <?php echo ( $domyslna == 'xpress' ? 'selected="selected"' : '' ); ?>>X-PRESS</option>
                        <option value="gls" <?php echo ( $domyslna == 'gls' ? 'selected="selected"' : '' ); ?>>GLS</option>
                     </select>
                    </td>
                  </tr>
                  <?php unset($domyslna); ?>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślny rodzaj przesyłki:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_FURGONETKA_RODZAJ_USLUGI']['0'];
                    ?>
                    <select name="integracja_furgonetka_rodzaj_uslugi" class="toolTipText" title="<?php echo $parametr['INTEGRACJA_FURGONETKA_RODZAJ_USLUGI']['2']; ?>">
                        <option value="package" <?php echo ( $domyslna == 'package' ? 'selected="selected"' : '' ); ?>>Paczka</option>
                        <option value="dox" <?php echo ( $domyslna == 'dox' ? 'selected="selected"' : '' ); ?>>Koperta</option>
                        <option value="pallette" <?php echo ( $domyslna == 'pallette' ? 'selected="selected"' : '' ); ?>>Paleta</option>
                     </select>
                    </td>
                  </tr>
                  <?php unset($domyslna); ?>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Rodzaj opakowania:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_FURGONETKA_RODZAJ_OPAKOWANIA']['0'];
                    ?>
                    <select name="integracja_furgonetka_rodzaj_opakowania" class="toolTipText" title="<?php echo $parametr['INTEGRACJA_FURGONETKA_RODZAJ_OPAKOWANIA']['2']; ?>">
                        <option value="0" <?php echo ( $domyslna == '0' ? 'selected="selected"' : '' ); ?>>karton</option>
                        <option value="8" <?php echo ( $domyslna == '7' ? 'selected="selected"' : '' ); ?>>opakowanie firmowe przewoźnika</option>
                        <option value="1" <?php echo ( $domyslna == '1' ? 'selected="selected"' : '' ); ?>>kontener metalowy</option>
                        <option value="2" <?php echo ( $domyslna == '2' ? 'selected="selected"' : '' ); ?>>kontener drewniany</option>
                        <option value="3" <?php echo ( $domyslna == '3' ? 'selected="selected"' : '' ); ?>>folia</option>
                        <option value="4" <?php echo ( $domyslna == '4' ? 'selected="selected"' : '' ); ?>>guma</option>
                        <option value="5" <?php echo ( $domyslna == '5' ? 'selected="selected"' : '' ); ?>>stretch</option>
                        <option value="6" <?php echo ( $domyslna == '6' ? 'selected="selected"' : '' ); ?>>tekura falista</option>
                        <option value="7" <?php echo ( $domyslna == '7' ? 'selected="selected"' : '' ); ?>>inne</option>
                     </select>
                    </td>
                  </tr>
                  <?php unset($domyslna); ?>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Kształt opakowania:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_FURGONETKA_KSZTALT_OPAKOWANIA']['0'];
                    ?>
                    <select name="integracja_furgonetka_ksztalt_opakowania" class="toolTipText" title="<?php echo $parametr['INTEGRACJA_FURGONETKA_KSZTALT_OPAKOWANIA']['2']; ?>">
                        <option value="0" <?php echo ( $domyslna == '0' ? 'selected="selected"' : '' ); ?>>karton o regularnym kształcie</option>
                        <option value="1" <?php echo ( $domyslna == '1' ? 'selected="selected"' : '' ); ?>>karton o nieregularnym kształcie</option>
                     </select>
                    </td>
                  </tr>
                  <?php unset($domyslna); ?>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_furgonetka_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_FURGONETKA_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' szerokość: <input type="text" name="integracja_furgonetka_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_FURGONETKA_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' wysokość: <input type="text" name="integracja_furgonetka_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_FURGONETKA_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['1'], $parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['0'], 'integracja_furgonetka_zawartosc', $parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_FURGONETKA_ZAWARTOSC']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Rachunek bankowy pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_furgonetka_numer_konta" name="integracja_furgonetka_numer_konta" value="'.$parametr['INTEGRACJA_FURGONETKA_NUMER_KONTA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NUMER_KONTA']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Nazwa firmy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_furgonetka_nadawca_firma" name="integracja_furgonetka_nadawca_firma" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_FIRMA']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_FIRMA']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Imię i nazwisko:</label>
                    </td>
                    <td>
                      <?php
                      echo 'imię: <input type="text" name="integracja_furgonetka_nadawca_imie" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_IMIE']['0'].'" size="25" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_IMIE']['2'].'" />';
                      echo ' nazwisko: <input type="text" name="integracja_furgonetka_nadawca_nazwisko" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_NAZWISKO']['0'].'" size="30" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_NAZWISKO']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Ulica i numer domu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_ulica" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_ULICA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_ULICA']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Kod pocztowy i miejscowość:</label>
                    </td>
                    <td>
                      <?php
                      echo 'kod: <input type="text" name="integracja_furgonetka_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY']['0'].'" size="17" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_KOD_POCZTOWY']['2'].'" />';
                      echo ' miasto: <input type="text" name="integracja_furgonetka_nadawca_miasto" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_MIASTO']['0'].'" size="40" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_MIASTO']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Kraj::</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_FURGONETKA_NADAWCA_KRAJ']['0'];
                    ?>
                    <select name="integracja_furgonetka_nadawca_kraj" class="toolTipText" title="">
                        <option value="PL">Polska</option>
                     </select>
                    </td>
                  </tr>
                  <?php unset($domyslna); ?>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_email" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_EMAIL']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_EMAIL']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_furgonetka_nadawca_telefon" value="'.$parametr['INTEGRACJA_FURGONETKA_NADAWCA_TELEFON']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_FURGONETKA_NADAWCA_TELEFON']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Paczkomat nadania:</label>
                    </td>
                    <td>
                      <?php
                      $domyslna = $parametr['INTEGRACJA_FURGONETKA_PACZKOMAT']['0'];
                      $url = 'http://furgonetka.pl/api/getPaczkomaty.xml';
                      $xml = simplexml_load_file($url);
                      $tablicaPaczkomatow;
                      $tablicaPaczkomatow[] = array('id' => '', 'text' => '--- wybierz z listy ---');
                      foreach ( $xml->all->node as $paczkomat ) {
                          $tablicaPaczkomatow[] = array('id' => $paczkomat->id, 'text' => $paczkomat->description);
                      }
                      echo Funkcje::RozwijaneMenu('integracja_furgonetka_paczkomat', $tablicaPaczkomatow, $domyslna);
                      unset($domyslna);
                      ?>
                    </td>
                  </tr>
                   
                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'furgonetka' ? $wynik : '' ); ?>
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
