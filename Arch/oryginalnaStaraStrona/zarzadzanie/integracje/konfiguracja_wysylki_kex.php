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
      $db->update_query('settings' , $pola, " code LIKE 'INTEGRACJA_KEX_%'");	
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
        <div class="naglowek">Edycja danych - Firma kierierska K-EX</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#form-kex").validate({
                rules: {
                  integracja_kex_api_key: {required: function() {var wynik = true; if ( $("input[name='integracja_kex_wlaczony']:checked", "#form-kex").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_kex_klient_ck: {required: function() {var wynik = true; if ( $("input[name='integracja_kex_wlaczony']:checked", "#form-kex").val() == "nie" ) { wynik = false; } return wynik; }},
                  }
              });

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);

            });
            //]]>
          </script>  

          <div class="sledzenie">

            <form action="integracje/konfiguracja_wysylki_kex.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_kex" id="form-kex" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="kex" />
                
                <table class="listing_tbl">
                
                  <tr><td colspan="2" class="sledzenie_opis">
                    <div>Integracja z firmą kurierską K-EX umożliwiająca nadawanie paczek bezpośrednio z poziomu edycji zamówienia w sklepie.</div>
                    <img src="obrazki/logo/logo_kex.png" alt="" />
                  </td></tr>                   

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz integrację K-EX:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_WLACZONY']['1'], $parametr['INTEGRACJA_KEX_WLACZONY']['0'], 'integracja_kex_wlaczony', $parametr['INTEGRACJA_KEX_WLACZONY']['2'], '', $parametr['INTEGRACJA_KEX_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_SANDBOX']['1'], $parametr['INTEGRACJA_KEX_SANDBOX']['0'], 'integracja_kex_sandbox', $parametr['INTEGRACJA_KEX_SANDBOX']['2'], '', $parametr['INTEGRACJA_KEX_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Numer CK klienta (zleceniodawcy):</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_klient_ck" value="'.$parametr['INTEGRACJA_KEX_KLIENT_CK']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KEX_KLIENT_CK']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Kod API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_api_key" value="'.$parametr['INTEGRACJA_KEX_API_KEY']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KEX_API_KEY']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Powiadamienie e-mail::</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['1'], $parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['0'], 'integracja_kex_powiadom_email', $parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['2'], '', $parametr['INTEGRACJA_KEX_POWIADOM_EMAIL']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Osoba nadająca:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_osoba_nadajaca" value="'.$parametr['INTEGRACJA_KEX_OSOBA_NADAJACA']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KEX_OSOBA_NADAJACA']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>E-mail nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_adres_email" value="'.$parametr['INTEGRACJA_KEX_ADRES_EMAIL']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KEX_ADRES_EMAIL']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Tel. stacjonarny nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_telefon_stacjonarny" value="'.$parametr['INTEGRACJA_KEX_TELEFON_STACJONARNY']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KEX_TELEFON_STACJONARNY']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Tel. komórkowy nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_kex_telefon_gsm" value="'.$parametr['INTEGRACJA_KEX_TELEFON_GSM']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_KEX_TELEFON_GSM']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślny rodzaj usługi:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['1'], $parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['0'], 'integracja_kex_rodzaj_uslugi', $parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['2'], 'Express,LTL', $parametr['INTEGRACJA_KEX_RODZAJ_USLUGI']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Kto płaci za usługę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_PLATNIK']['1'], $parametr['INTEGRACJA_KEX_PLATNIK']['0'], 'integracja_kex_platnik', $parametr['INTEGRACJA_KEX_PLATNIK']['2'], 'gotówką nadawca,gotówką odbiorca,zleceniodawca wg umowy,strona trzecia', $parametr['INTEGRACJA_KEX_PLATNIK']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Rachunek bankowy pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_kex_numer_konta" name="integracja_kex_numer_konta" value="'.$parametr['INTEGRACJA_KEX_NUMER_KONTA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_KEX_NUMER_KONTA']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Awizacja odbioru:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['1'], $parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['0'], 'integracja_kex_odbior_powiadomienie', $parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['2'], 'SMS,E-mail,Telefon', $parametr['INTEGRACJA_KEX_ODBIOR_POWIADOMIENIE']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Awizacja dostawy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['1'], $parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['0'], 'integracja_kex_dostawa_powiadomienie', $parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['2'], 'SMS,E-mail,Telefon', $parametr['INTEGRACJA_KEX_DOSTAWA_POWIADOMIENIE']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Potwierdzenie dostawy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['1'], $parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['0'], 'integracja_kex_dostawa_potwierdzenie', $parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['2'], 'SMS,E-mail', $parametr['INTEGRACJA_KEX_DOSTAWA_POTWIERDZENIE']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_KEX_ZAWARTOSC']['1'], $parametr['INTEGRACJA_KEX_ZAWARTOSC']['0'], 'integracja_kex_zawartosc', $parametr['INTEGRACJA_KEX_ZAWARTOSC']['2'], '', $parametr['INTEGRACJA_KEX_ZAWARTOSC']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'kex' ? $wynik : '' ); ?>
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
