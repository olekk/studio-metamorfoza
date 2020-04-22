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
        <div class="naglowek">Edycja danych - Firma DHL</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 
          <script type="text/javascript" src="javascript/jquery.populate.js"></script>

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);

            });
            //]]>
          </script>  

          <div class="sledzenie">

            <form action="integracje/konfiguracja_wysylki_dhl.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_dhl" id="form-dhl" class="cmxform">
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="dhl" />
                
                <table class="listing_tbl">
                
                  <tr><td colspan="2" class="sledzenie_opis">
                    <div>Posiadając podpisaną bezpośrednią umowę z kurierem DHL można z poziomu edycji zamówienia w prosty sposób generować pliki XML dla programu eCas.</div>
                    <img src="obrazki/logo/logo_dhl.png" alt="" />
                  </td></tr>                    

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz generowanie plików XML dla DHL:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_WLACZONY']['1'], $parametr['INTEGRACJA_DHL_WLACZONY']['0'], 'integracja_dhl_wlaczony', $parametr['INTEGRACJA_DHL_WLACZONY']['2'], '', $parametr['INTEGRACJA_DHL_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>ID kuriera:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_kurier_id" value="'.$parametr['INTEGRACJA_DHL_KURIER_ID']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_DHL_KURIER_ID']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Rodzaj usługi:</label>
                    </td>
                    <td>
                      <?php
                      $tablica = DhlApi::dhl_post_product_array();
                      echo Funkcje::RozwijaneMenu('integracja_dhl_usluga', $tablica, $parametr['INTEGRACJA_DHL_USLUGA']['0'], 'class="toolTipText" title="'. $parametr['INTEGRACJA_DHL_USLUGA']['2'].'"');
                      unset($tablica);
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Rodzaj przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      $tablica = DhlApi::dhl_post_category_array();
                      echo Funkcje::RozwijaneMenu('integracja_dhl_paczka', $tablica, $parametr['INTEGRACJA_DHL_PACZKA']['0'], 'class="toolTipText" title="'. $parametr['INTEGRACJA_DHL_PACZKA']['2'].'"');
                      unset($tablica);
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Płatność za usługę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_PLATNIK']['1'], $parametr['INTEGRACJA_DHL_PLATNIK']['0'], 'integracja_dhl_platnik', $parametr['INTEGRACJA_DHL_PLATNIK']['2'], '', $parametr['INTEGRACJA_DHL_PLATNIK']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Rodzaj płatności za usługę:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_TYP_PLATNOSCI']['1'], $parametr['INTEGRACJA_DHL_TYP_PLATNOSCI']['0'], 'integracja_dhl_typ_platnosci', $parametr['INTEGRACJA_DHL_TYP_PLATNOSCI']['2'], '', $parametr['INTEGRACJA_DHL_TYP_PLATNOSCI']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Ubezpieczenie:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_UBEZPIECZENIE']['1'], $parametr['INTEGRACJA_DHL_UBEZPIECZENIE']['0'], 'integracja_dhl_ubezpieczenie', $parametr['INTEGRACJA_DHL_UBEZPIECZENIE']['2'], 'tak,nie', $parametr['INTEGRACJA_DHL_UBEZPIECZENIE']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Znacznik BLP:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_DHL_BLP']['1'], $parametr['INTEGRACJA_DHL_BLP']['0'], 'integracja_dhl_blp', $parametr['INTEGRACJA_DHL_BLP']['2'], '1,0', $parametr['INTEGRACJA_DHL_BLP']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_zawartosc" value="'.$parametr['INTEGRACJA_DHL_ZAWARTOSC']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_DHL_ZAWARTOSC']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Numer telefonu nadawcy:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_pre_sen_tel" value="'.$parametr['INTEGRACJA_DHL_PRE_SEN_TEL']['0'].'" size="50" class="toolTipText" title="'. $parametr['INTEGRACJA_DHL_PRE_SEN_TEL']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Numer telefonu (gsm) nadawcy do preawiazacji:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_pre_sen_sms" value="'.$parametr['INTEGRACJA_DHL_PRE_SEN_SMS']['0'].'" size="50" class="toolTipText" title="'. $parametr['INTEGRACJA_DHL_PRE_SEN_SMS']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Adres e-mail nadawcy do preawiazacji:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_dhl_pre_sen_email" value="'.$parametr['INTEGRACJA_DHL_PRE_SEN_EMAIL']['0'].'" size="50" class="toolTipText" title="'. $parametr['INTEGRACJA_DHL_PRE_SEN_EMAIL']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                   <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'dhl' ? $wynik : '' ); ?>
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
