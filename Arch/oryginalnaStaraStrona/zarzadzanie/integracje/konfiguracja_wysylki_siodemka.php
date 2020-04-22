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
        <div class="naglowek">Edycja danych - Firma kierierska Siódemka</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#form-siodemka").validate({
                rules: {
                  integracja_siodemka_api_pin: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_siodemka_klient_id: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_siodemka_kurier_id: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_siodemka_potwierdzenie_podpis: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_siodemka_nadawca_telefon: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_siodemka_nadawca_email: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_wlaczony']:checked", "#form-siodemka").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_siodemka_numer_konta: {required: function() {var wynik = true; if ( $("input[name='integracja_siodemka_zwrot_pobrania']:checked", "#form-siodemka").val() == "P" ) { wynik = false; } return wynik; }},
                  integracja_siodemka_wymiary_dlugosc: { digits: true },
                  integracja_siodemka_wymiary_szerokosc: { digits: true },
                  integracja_siodemka_wymiary_wysokosc: { digits: true }
                  }
              });

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);

            });
            //]]>
          </script>  

          <div class="sledzenie">

            <form action="integracje/konfiguracja_wysylki_siodemka.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_siodemka" id="form-siodemka" class="cmxform"> 
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="siodemka" />
                
                <table class="listing_tbl">
                
                  <tr><td colspan="2" class="sledzenie_opis">
                    <div>Integracja z firmą kurierską Siódemka umożliwiająca nadwanie paczek bezpośrednio z poziomu edycji zamówienia w sklepie.</div>
                    <img src="obrazki/logo/logo_siodemka.png" alt="" />
                  </td></tr>                   

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz integrację Siódemka:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_WLACZONY']['1'], $parametr['INTEGRACJA_SIODEMKA_WLACZONY']['0'], 'integracja_siodemka_wlaczony', $parametr['INTEGRACJA_SIODEMKA_WLACZONY']['2'], '', $parametr['INTEGRACJA_SIODEMKA_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Numer klienta w WebMobile7:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_klient_id" value="'.$parametr['INTEGRACJA_SIODEMKA_KLIENT_ID']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_KLIENT_ID']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Kod API:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_api_pin" value="'.$parametr['INTEGRACJA_SIODEMKA_API_PIN']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_API_PIN']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Nr kuriera:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_kurier_id" value="'.$parametr['INTEGRACJA_SIODEMKA_KURIER_ID']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_KURIER_ID']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Podpis nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_siodemka_potwierdzenie_podpis" value="'.$parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_PODPIS']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_PODPIS']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Numer telefonu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_nadawca_telefon" name="integracja_siodemka_nadawca_telefon" value="'.$parametr['INTEGRACJA_SIODEMKA_NADAWCA_TELEFON']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_NADAWCA_TELEFON']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label class="required">Adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_nadawca_email" name="integracja_siodemka_nadawca_email" value="'.$parametr['INTEGRACJA_SIODEMKA_NADAWCA_EMAIL']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_NADAWCA_EMAIL']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślny rodzaj przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['1'], $parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['0'], 'integracja_siodemka_rodzaj_przesylki', $parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['2'], 'krajowa,zagraniczna,lokalna', $parametr['INTEGRACJA_SIODEMKA_RODZAJ_PRZESYLKI']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Kto płaci za usługę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_PLATNIK']['1'], $parametr['INTEGRACJA_SIODEMKA_PLATNIK']['0'], 'integracja_siodemka_platnik', $parametr['INTEGRACJA_SIODEMKA_PLATNIK']['2'], 'nadawca,odbiorca,trzeci płatnik', $parametr['INTEGRACJA_SIODEMKA_PLATNIK']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Forma płatności za przesyłkę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['1'], $parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['0'], 'integracja_siodemka_forma_platnosci', $parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['2'], 'gotówka,przelew', $parametr['INTEGRACJA_SIODEMKA_FORMA_PLATNOSCI']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Potwierdzenie doręczenia:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['0'], 'integracja_siodemka_potwierdzenie_doreczenia', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['2'], 'brak,PD email,PD kurier,PD email i PD kurier', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DORECZENIA']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Forma zwrotu pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['1'], $parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['0'], 'integracja_siodemka_zwrot_pobrania', $parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['2'], 'przekaz pocztowy,przelew bankowy,pobranie NextDay', $parametr['INTEGRACJA_SIODEMKA_ZWROT_POBRANIA']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Numer konta bankowego:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_numer_konta" name="integracja_siodemka_numer_konta" value="'.$parametr['INTEGRACJA_SIODEMKA_NUMER_KONTA']['0'].'" size="73" class="required toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_NUMER_KONTA']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Dokumenty zwrotne:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['1'], $parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['0'], 'integracja_siodemka_dokumenty_zwrotne', $parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_DOKUMENTY_ZWROTNE']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Potwierdzenie nadania przesyłki na podany adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['0'], 'integracja_siodemka_potwierdzenie_nadania_email', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_NADANIA_EMAIL']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Potwierdzenie dostarczenia przesyłki na adres e-mail:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['0'], 'integracja_siodemka_potwierdzenie_dostarczenia_email', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_EMAIL']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Potwierdzenie dostarczenia przesyłki SMS:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['1'], $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['0'], 'integracja_siodemka_potwierdzenie_dostarczenia_sms', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['2'], 'nie,tak', $parametr['INTEGRACJA_SIODEMKA_POTWIERDZENIE_DOSTARCZENIA_SMS']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Preferowane wymiary przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo 'długość: <input type="text" name="integracja_siodemka_wymiary_dlugosc" value="'.$parametr['INTEGRACJA_SIODEMKA_WYMIARY_DLUGOSC']['0'].'" size="12" />';
                      echo ' szerokość: <input type="text" name="integracja_siodemka_wymiary_szerokosc" value="'.$parametr['INTEGRACJA_SIODEMKA_WYMIARY_SZEROKOSC']['0'].'" size="12" />';
                      echo ' wysokość: <input type="text" name="integracja_siodemka_wymiary_wysokosc" value="'.$parametr['INTEGRACJA_SIODEMKA_WYMIARY_WYSOKOSC']['0'].'" size="12" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_zawartosc" name="INTEGRACJA_SIODEMKA_ZAWARTOSC" value="'.$parametr['INTEGRACJA_SIODEMKA_ZAWARTOSC']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_ZAWARTOSC']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślna wartość ubezpieczenia:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" id="integracja_siodemka_kwota_ubezpieczenia" name="INTEGRACJA_SIODEMKA_KWOTA_UBEZPIECZENIA" value="'.$parametr['INTEGRACJA_SIODEMKA_KWOTA_UBEZPIECZENIA']['0'].'" size="30" class="kropkaPusta toolTipText" title="'. $parametr['INTEGRACJA_SIODEMKA_KWOTA_UBEZPIECZENIA']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'siodemka' ? $wynik : '' ); ?>
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
