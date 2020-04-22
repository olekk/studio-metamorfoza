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

      if ( $_POST['integracja_inpost_nadawanie'] == 'tak' ) { $_POST['integracja_inpost_paczkomat'] = ''; } 
      if ( $_POST['integracja_inpost_nadawca_etykieta'] == 'nie' ) { 
        $_POST['integracja_inpost_nadawca_imie'] = ''; 
        $_POST['integracja_inpost_nadawca_nazwisko'] = ''; 
        $_POST['integracja_inpost_nadawca_ulica'] = ''; 
        $_POST['integracja_inpost_nadawca_dom'] = ''; 
        $_POST['integracja_inpost_nadawca_kod_pocztowy'] = ''; 
        $_POST['integracja_inpost_nadawca_miasto'] = ''; 
        $_POST['integracja_inpost_nadawca_telefon'] = ''; 
        $_POST['integracja_inpost_nadawca_email'] = ''; 
      } 

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

    <style type="text/css">
    .nadawca_etykieta { <?php echo ( $parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['0'] == 'tak' ? '' : 'display: none;'); ?> }
    </style>

    <div id="naglowek_cont">Konfiguracja parametrów systemów wysyłkowych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych - Firma Paczkomaty InPost</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#form-inpost").validate({
                rules: {
                  integracja_inpost_login_email: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_login_haslo: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_wlaczony']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_imie: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_nazwisko: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_ulica: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_dom: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_kod_pocztowy: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_miasto: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_email: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_inpost_nadawca_telefon: {required: function() {var wynik = true; if ( $("input[name='integracja_inpost_nadawca_etykieta']:checked", "#form-inpost").val() == "nie" ) { wynik = false; } return wynik; }}
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

          <!-- INPOST - START -->

          <?php
          $inpost = new InPostApi();
          ?>
          <div class="sledzenie">

                <form action="integracje/konfiguracja_wysylki_inpost.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_inpost" id="form-inpost" class="cmxform">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="system" value="inpost" />
                    
                    <table class="listing_tbl">
                    
                      <tr><td colspan="2" class="sledzenie_opis">
                        <div>Paczkomaty InPost to system skrytek pocztowych, służący do odbierania  paczek 24 godziny na dobę przez 7 dni w tygodniu. Osoba robiąca zakupy przez Internet – po zamówieniu przesyłki do Paczkomatu InPost - otrzyma SMS i e-mail z kodem odbioru. Aby odebrać przesyłkę wystarczy wpisać na panelu Paczkomatu InPost numer telefonu komórkowego oraz otrzymany kod odbioru, a skrytka z oczekiwaną przesyłką otworzy się. W ciągu 2 dni roboczych od momentu nadania paczki, przesyłka znajdzie się w paczkomacie. Odbiór paczki jest możliwy o dowolnej porze dnia czy nocy.</div>
                        <img src="obrazki/logo/logo_inpost.png" alt="" />
                      </td></tr>                    
                    
                      <tr class="pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Włącz integrację Paczkomaty InPost:</label>
                        </td>
                        <td>
                          <?php
                          echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_INPOST_WLACZONY']['1'], $parametr['INTEGRACJA_INPOST_WLACZONY']['0'], 'integracja_inpost_wlaczony', $parametr['INTEGRACJA_INPOST_WLACZONY']['2'], '', $parametr['INTEGRACJA_INPOST_WLACZONY']['3'] );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label class="required">Adres e-mail:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" name="integracja_inpost_login_email" value="'.$parametr['INTEGRACJA_INPOST_LOGIN_EMAIL']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_LOGIN_EMAIL']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label class="required">Hasło:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="password" name="integracja_inpost_login_haslo" value="'.$parametr['INTEGRACJA_INPOST_LOGIN_HASLO']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_LOGIN_HASLO']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Domyślny rozmiar paczki:</label>
                        </td>
                        <td>
                          <?php
                          $tablica = $inpost->inpost_post_parcel_array(false);
                          echo Funkcje::RozwijaneMenu('integracja_inpost_wymiary', $tablica, $parametr['INTEGRACJA_INPOST_WYMIARY']['0'], 'style="width:250px;" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_WYMIARY']['2'].'"');
                          unset($tablica);
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Nadawanie przesyłek w oddziale lub odbiór przez kuriera:</label>
                        </td>
                        <td>
                          <input type="radio" value="tak" name="integracja_inpost_nadawanie" onclick="$('#paczkomat_domyslny').slideUp();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWANIE']['0'] == 'tak' ? 'checked="checked"' : ''); ?> class="toolTipTop" title="<?php echo $parametr['INTEGRACJA_INPOST_NADAWANIE']['2']; ?>" /> tak
                          <input type="radio" value="nie" name="integracja_inpost_nadawanie" onclick="$('#paczkomat_domyslny').slideDown();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWANIE']['0'] == 'nie' ? 'checked="checked"' : ''); ?> class="toolTipTop" title="<?php echo $parametr['INTEGRACJA_INPOST_NADAWANIE']['2']; ?>" /> nie
                        </td>
                      </tr>
                      
                      <tr class="pozycja_off" id="paczkomat_domyslny" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWANIE']['0'] == 'tak' ? 'style="display:none;"' : '' ); ?>>
                        <td style="width:225px;padding-left:25px">
                          <label>Paczkomat, w którym będą nadawane przesyłki:</label>
                        </td>
                        <td>
                          <?php 
                          echo $inpost->inpost_machines_dropdown_all( array('selected' => $parametr['INTEGRACJA_INPOST_PACZKOMAT']['0'], 'name' => 'integracja_inpost_paczkomat') );
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Inne dane nadawcy na etykiecie:</label>
                        </td>
                        <td>
                          <input type="radio" value="tak" name="integracja_inpost_nadawca_etykieta" onclick="$('.nadawca_etykieta').slideDown();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['0'] == 'tak' ? 'checked="checked"' : ''); ?> class="toolTipTop" title="<?php echo $parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['2']; ?>" /> tak
                          <input type="radio" value="nie" name="integracja_inpost_nadawca_etykieta" onclick="$('.nadawca_etykieta').slideUp();" <?php echo ($parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['0'] == 'nie' ? 'checked="checked"' : ''); ?> class="toolTipTop" title="<?php echo $parametr['INTEGRACJA_INPOST_NADAWCA_ETYKIETA']['2']; ?>" /> nie
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off" >
                        <td style="width:225px;padding-left:25px">
                          <label>Imię nadawcy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_imie" name="integracja_inpost_nadawca_imie" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_IMIE']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_IMIE']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Nazwisko nadawcy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_nazwisko" name="integracja_inpost_nadawca_nazwisko" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_NAZWISKO']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_NAZWISKO']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Ulica:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_ulica" name="integracja_inpost_nadawca_ulica" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_ULICA']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_ULICA']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Numer domu / lokalu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_dom" name="integracja_inpost_nadawca_dom" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_DOM']['0'].'" size="20" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_DOM']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Kod pocztowy:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_kod_pocztowy" name="integracja_inpost_nadawca_kod_pocztowy" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_KOD_POCZTOWY']['0'].'" size="20" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_KOD_POCZTOWY']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Miejscowość:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_miasto" name="integracja_inpost_nadawca_miasto" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_MIASTO']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_MIASTO']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Numer telefonu:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_telefon" name="integracja_inpost_nadawca_telefon" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_TELEFON']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_TELEFON']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr class="nadawca_etykieta pozycja_off">
                        <td style="width:225px;padding-left:25px">
                          <label>Adres e-mail:</label>
                        </td>
                        <td>
                          <?php
                          echo '<input type="text" id="integracja_inpost_nadawca_email" name="integracja_inpost_nadawca_email" value="'.$parametr['INTEGRACJA_INPOST_NADAWCA_EMAIL']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_INPOST_NADAWCA_EMAIL']['2'].'" />';
                          ?>
                        </td>
                      </tr>
                      
                      <tr>
                        <td colspan="2">
                          <div class="przyciski_dolne">
                            <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'inpost' ? $wynik : '' ); ?>
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
