<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = new ElektronicznyNadawca();

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

    if ( isset($_GET['wynik']) ) {
        $wynik = urldecode($_GET['wynik']);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja parametrów systemów wysyłkowych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych - Poczta Polska - elektroniczny nadawca</div>

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

            <form action="integracje/konfiguracja_wysylki_en.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" name="form_en" id="form-en" class="cmxform">
            
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="en" />
                
                <table class="listing_tbl">
                
                  <tr><td colspan="2" class="sledzenie_opis">
                    <div>Elektroniczny Nadawca Poczty Polskiej usprawnia i automatyzuje proces nadawania przesyłek pocztowych, umożliwia rejestrację przesyłek, generowanie dokumentów nadawczych w formie elektronicznej oraz automatyczne przekazywanie pliku z przesyłkami bezpośrednio do wybranej placówki pocztowej.</div>
                    <img src="obrazki/logo/logo_en.png" alt="" />
                  </td></tr>                    

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz integracjęz serwisem EN:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_POCZTA_EN_WLACZONY']['1'], $parametr['INTEGRACJA_POCZTA_EN_WLACZONY']['0'], 'integracja_poczta_en_wlaczony', $parametr['INTEGRACJA_POCZTA_EN_WLACZONY']['2'], '', $parametr['INTEGRACJA_POCZTA_EN_WLACZONY']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Włącz tryb testowy:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_POCZTA_EN_SANDBOX']['1'], $parametr['INTEGRACJA_POCZTA_EN_SANDBOX']['0'], 'integracja_poczta_en_sandbox', $parametr['INTEGRACJA_POCZTA_EN_SANDBOX']['2'], '', $parametr['INTEGRACJA_POCZTA_EN_SANDBOX']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Login:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_poczta_en_api_login" value="'.$parametr['INTEGRACJA_POCZTA_EN_API_LOGIN']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_POCZTA_EN_API_LOGIN']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Hasło:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_poczta_en_api_haslo" value="'.$parametr['INTEGRACJA_POCZTA_EN_API_HASLO']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_POCZTA_EN_API_HASLO']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Urząd nadania:</label>
                    </td>
                    <td>
                      <?php
                      $tablica_tmp = $api->getUrzedyNadania();
                      if ( $tablica_tmp != 'error' ) {
                          if ( is_array($tablica_tmp->urzedyNadania) ) {
                              foreach ( $tablica_tmp->urzedyNadania as $rekord ) {
                                  $tablica[] = array('id' => $rekord->urzadNadania,
                                                   'text' => $rekord->nazwaWydruk . ' ['.$rekord->urzadNadania.']')
                                  ;
                              }
                          } else {
                            $tablica[] = array('id' => $tablica_tmp->urzedyNadania->urzadNadania,
                                               'text' => $tablica_tmp->urzedyNadania->opis)
                            ;
                          }
                          echo Funkcje::RozwijaneMenu('integracja_poczta_en_urzad_nadania', $tablica, $parametr['INTEGRACJA_POCZTA_EN_URZAD_NADANIA']['0'], 'class="toolTipText" title="'. $parametr['INTEGRACJA_POCZTA_EN_URZAD_NADANIA']['2'].'"');
                      } else {
                          echo 'uzupełnij login i haslo przed wybraniem urzędu';
                      }
                      unset($tablica_tmp,$tablica);
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Sposób zwrotu pobrania:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_POCZTA_EN_TYP_POBRANIA']['1'], $parametr['INTEGRACJA_POCZTA_EN_TYP_POBRANIA']['0'], 'integracja_poczta_en_typ_pobrania', $parametr['INTEGRACJA_POCZTA_EN_TYP_POBRANIA']['2'], 'rachunek bankowy,przekaz', $parametr['INTEGRACJA_POCZTA_EN_TYP_POBRANIA']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Numer rachunku bankowego:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_poczta_en_nadawca_konto" value="'.$parametr['INTEGRACJA_POCZTA_EN_NADAWCA_KONTO']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_POCZTA_EN_NADAWCA_KONTO']['2'].'" />';
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Tytuł przelewu:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_poczta_en_tytul_przelewu" value="'.$parametr['INTEGRACJA_POCZTA_EN_TYTUL_PRZELEWU']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_POCZTA_EN_TYTUL_PRZELEWU']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślny rodzaj przesyłki:</label>
                    </td>
                    <td>
                    <?php
                    $domyslna = $parametr['INTEGRACJA_POCZTA_EN_PRZESYLKA_DOMYSLNA']['0'];
                    ?>
                    <select name="integracja_poczta_en_przesylka_domyslna" class="toolTipText" title="<?php echo $parametr['INTEGRACJA_POCZTA_EN_PRZESYLKA_DOMYSLNA']['2']; ?>">
                        <option value="">- wybierz -</option>
                        <optgroup label="Przesyłki krajowe">
                            <option value="2" <?php echo ( $domyslna == '2' ? 'selected="selected"' : '' ); ?>>Paczka pocztowa</option>
                            <option value="3" <?php echo ( $domyslna == '3' ? 'selected="selected"' : '' ); ?>>Paczka pocztowa PLUS</option>
                            <option value="5" <?php echo ( $domyslna == '5' ? 'selected="selected"' : '' ); ?>>Przesyłka pobraniowa</option>
                            <option value="6" <?php echo ( $domyslna == '6' ? 'selected="selected"' : '' ); ?>>Przesyłka polecona krajowa</option>
                            <option value="7" <?php echo ( $domyslna == '7' ? 'selected="selected"' : '' ); ?>>Przesyłka listowa z zadeklarowana wartością</option>
                            <option value="8" <?php echo ( $domyslna == '8' ? 'selected="selected"' : '' ); ?>>Przesyłka na warunkach szczególnych</option>
                            <option value="10" <?php echo ( $domyslna == '10' ? 'selected="selected"' : '' ); ?>>Pocztex</option>
                            <!-- <option value="11" <?php echo ( $domyslna == '11' ? 'selected="selected"' : '' ); ?>>E-PRZESYŁKA</option> -->
                            <option value="12" <?php echo ( $domyslna == '12' ? 'selected="selected"' : '' ); ?>>Pocztex kurier 48 (przesyłka biznesowa)</option>
                            <!-- <option value="15" <?php echo ( $domyslna == '15' ? 'selected="selected"' : '' ); ?>>Przesyłka firmowa nierejestrowana</option> -->
                            <option value="13" <?php echo ( $domyslna == '13' ? 'selected="selected"' : '' ); ?>>Przesyłka firmowa polecona</option>
                            <option value="14" <?php echo ( $domyslna == '14' ? 'selected="selected"' : '' ); ?>>Uługa paczkowa</option>
                        </optgroup>
                        <optgroup label="Przesyłki zagraniczne">
                            <option value="20" <?php echo ( $domyslna == '20' ? 'selected="selected"' : '' ); ?>>Zagraniczna przesyłka polecona</option>
                            <option value="22" <?php echo ( $domyslna == '22' ? 'selected="selected"' : '' ); ?>>Zagraniczna paczka do Unii Europejskiej</option>
                        </optgroup>
                     </select>
                    </td>
                  </tr>
                  

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Typ przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_POCZTA_EN_TYP_PRZESYLKI']['1'], $parametr['INTEGRACJA_POCZTA_EN_TYP_PRZESYLKI']['0'], 'integracja_poczta_en_typ_przesylki', $parametr['INTEGRACJA_POCZTA_EN_TYP_PRZESYLKI']['2'], 'priorytetowa,ekonomiczna', $parametr['INTEGRACJA_POCZTA_EN_TYP_PRZESYLKI']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Gabaryt:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_POCZTA_EN_GABARYT_PRZESYLKI']['1'], $parametr['INTEGRACJA_POCZTA_EN_GABARYT_PRZESYLKI']['0'], 'integracja_poczta_en_gabaryt_przesylki', $parametr['INTEGRACJA_POCZTA_EN_GABARYT_PRZESYLKI']['2'], 'Gabaryt A, Gabaryt B', $parametr['INTEGRACJA_POCZTA_EN_GABARYT_PRZESYLKI']['3'] );
                      ?>
                    </td>
                  </tr>
                  
                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Gabaryt Kurier48:</label>
                    </td>
                    <td>
                      <?php
                      echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_POCZTA_EN_POCZTEX_DOMYSLNY']['1'], $parametr['INTEGRACJA_POCZTA_EN_POCZTEX_DOMYSLNY']['0'], 'integracja_poczta_en_pocztex_domyslny', $parametr['INTEGRACJA_POCZTA_EN_POCZTEX_DOMYSLNY']['2'], 'XS, S, M, L, XL, XXL', $parametr['INTEGRACJA_POCZTA_EN_POCZTEX_DOMYSLNY']['3'] );
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślna zawartość przesyłki:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_poczta_en_zawartosc" value="'.$parametr['INTEGRACJA_POCZTA_EN_ZAWARTOSC']['0'].'" size="73" class="toolTipText" title="'. $parametr['INTEGRACJA_POCZTA_EN_ZAWARTOSC']['2'].'" />';
                      ?>
                    </td>
                  </tr>

                  <tr class="pozycja_off">
                    <td style="width:225px;padding-left:25px">
                      <label>Domyślna kwota ubezpieczenia:</label>
                    </td>
                    <td>
                      <?php
                      echo '<input type="text" name="integracja_poczta_en_kwota_ubezpieczenia" value="'.$parametr['INTEGRACJA_POCZTA_EN_KWOTA_UBEZPIECZENIA']['0'].'" size="30" class="kropkaPusta" />';
                      ?>
                    </td>
                  </tr>

                  <tr>
                    <td colspan="2">
                      <div class="przyciski_dolne">
                        <input type="submit" class="przyciskNon" value="Zapisz dane" />
                        <?php echo ( $system == 'en' ? $wynik : '' ); ?>
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
