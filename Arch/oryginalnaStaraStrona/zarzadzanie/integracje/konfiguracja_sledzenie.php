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

    $zapytanie = "SELECT * FROM settings WHERE type = 'sledzenie' ORDER BY sort ";
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

    <div id="naglowek_cont">Konfiguracja parametrów systemów śledzących</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana" style="overflow:hidden;">  

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#googleForm").validate({
                rules: {
                  integracja_google_id: {required: function() {var wynik = true; if ( $("input[name='integracja_google_wlaczony']:checked", "#googleForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });
              
              $("#googleWebForm").validate({
                rules: {
                  integracja_google_web_id: {required: function() {var wynik = true; if ( $("input[name='integracja_google_web_wlaczony']:checked", "#googleWebForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });              

              $("#ceneoForm").validate({
                rules: {
                  integracja_ceneo_opinie_id: {required: function() {
                    var wynik = true; 
                    if ( $("input[name='integracja_ceneo_opinie_wlaczony']:checked", "#ceneoForm").val() == "nie" && $("input[name='integracja_ceneo_widget_wlaczony']:checked", "#ceneoForm").val() == "nie" ) { wynik = false; }
                    return wynik; 
                  }},
                }
              });

              $("#opineoForm").validate({
                rules: {
                  integracja_opineo_opinie_login: {required: function() {var wynik = true; if ( $("input[name='integracja_opineo_opinie_wlaczony']:checked", "#opineoForm").val() == "nie" ) { wynik = false; } return wynik; }},
                  integracja_opineo_opinie_pass: {required: function() {var wynik = true; if ( $("input[name='integracja_opineo_opinie_wlaczony']:checked", "#opineoForm").val() == "nie" ) { wynik = false; } return wynik; }},
                }
              });

              $("#okazjeForm").validate({
                rules: {
                  integracja_okazje_id: {required: function() {var wynik = true; if ( $("input[name='integracja_okazje_wlaczony']:checked", "#okazjeForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);
            });
            //]]>
          </script>  

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="google" />
            </div>
            
            <div class="obramowanie_tabeliSpr">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td style="text-align:left" colspan="2">Google Analytics</td>
                </tr>
                
                <tr><td colspan="2" class="sledzenie_opis">
                  <div>Usługa Google Analytics nie tylko umożliwia pomiar wielkości sprzedaży i liczby konwersji, ale również zapewnia bieżący wgląd w to, jak użytkownicy korzystają z Twojej witryny, jak do niej dotarli i co możesz zrobić, by chętnie do niej wracali.</div>
                  <img src="obrazki/logo/logo_google_analytics.png" alt="" />
                </td></tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Włącz moduł Google Analytics:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_WLACZONY']['1'], $parametr['INTEGRACJA_GOOGLE_WLACZONY']['0'], 'integracja_google_wlaczony', $parametr['INTEGRACJA_GOOGLE_WLACZONY']['2'], '', $parametr['INTEGRACJA_GOOGLE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Używana wersja Google Analytics:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_RODZAJ']['1'], $parametr['INTEGRACJA_GOOGLE_RODZAJ']['0'], 'integracja_google_rodzaj', $parametr['INTEGRACJA_GOOGLE_RODZAJ']['2'], '', $parametr['INTEGRACJA_GOOGLE_RODZAJ']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="pozycja_off" id="Remarketing">
                  <td style="width:225px;padding-left:25px">
                    <label>Czy używasz list remarketingowych:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_GOOGLE_ADWORDS']['1'], $parametr['INTEGRACJA_GOOGLE_ADWORDS']['0'], 'integracja_google_adwords', $parametr['INTEGRACJA_GOOGLE_ADWORDS']['2'], '', $parametr['INTEGRACJA_GOOGLE_ADWORDS']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label class="required">Identyfikator Google:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_google_id" name="integracja_google_id" value="'.$parametr['INTEGRACJA_GOOGLE_ID']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_GOOGLE_ID']['2'].'" />';
                    ?>
                  </td>
                </tr>

                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'google' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>
          
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleWebForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="googleweb" />
            </div>
            
            <div class="obramowanie_tabeliSpr">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td style="text-align:left" colspan="2">Google Narzędzia dla Webmasterów</td>
                </tr>
                
                <tr><td colspan="2" class="sledzenie_opis">
                  <div>Dzięki Narzędziom Google dla webmasterów będziesz otrzymywać szczegółowe raporty o widoczności Twoich stron w Google. Narzędzia Google możliwiają weryfikowanie indeksowania stron sklepu, optymalizowanie go pod kątem wyszukiwarek, dostarczają szeregu innych danych interesujących z punktu widzenia pozycjonowania i analizy strony.</div>
                  <img src="obrazki/logo/logo_google_web.png" alt="" />
                </td></tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Kod weryfikacyjny:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_google_weryfikacja" value="'.$parametr['INTEGRACJA_GOOGLE_WERYFIKACJA']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_GOOGLE_WERYFIKACJA']['2'].'" />';
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'googleweb' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>          

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ceneoForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="ceneo" />
            </div>
            
            <div class="obramowanie_tabeliSpr">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td style="text-align:left" colspan="2">CENEO zaufane opinie</td>
                </tr>
                
                <tr><td colspan="2" class="sledzenie_opis">
                  <div>"Zaufane Opinie" to szczególny system zbierania opinii o transakcjach w sklepach internetowych. Komentarze z zielonym znaczkiem są publikowane na podstawie ankiet wypełnianych przez osoby, które złożyły zamówienie on-line w sklepie objętym programem „Zaufanych Opinii”. Czytając taką opinię masz pewność, że informacje o wybranym sklepie pochodzą od rzeczywistych Klientów.</div>
                  <img src="obrazki/logo/logo_opinie_ceneo.png" alt="" />
                </td></tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Włącz moduł zaufanych opinii CENEO:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['1'], $parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['0'], 'integracja_ceneo_opinie_wlaczony', $parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_CENEO_OPINIE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Model rozliczeń CPA:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CENEO_OPINIE_WARIANT']['1'], $parametr['INTEGRACJA_CENEO_OPINIE_WARIANT']['0'], 'integracja_ceneo_opinie_wariant', $parametr['INTEGRACJA_CENEO_OPINIE_WARIANT']['2'], '', $parametr['INTEGRACJA_CENEO_OPINIE_WARIANT']['3'] );
                    ?>
                  </td>
                </tr>

                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label class="required">Identyfikator CENEO:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_ceneo_opinie_id" name="integracja_ceneo_opinie_id" value="'.$parametr['INTEGRACJA_CENEO_OPINIE_ID']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_CENEO_OPINIE_ID']['2'].'" />';
                    ?>
                  </td>
                </tr>

                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Liczba dni do wysłania ankiety:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['1'], $parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['0'], 'integracja_ceneo_opinie_czas', $parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['2'], '', $parametr['INTEGRACJA_CENEO_OPINIE_CZAS']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'ceneo' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>

          
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="opineoForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="opineo" />
            </div>
            
            <div class="obramowanie_tabeliSpr">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td style="text-align:left" colspan="2">OPINEO</td>
                </tr>
                
                <tr><td colspan="2" class="sledzenie_opis">
                  <div>Opineo.pl jest serwisem propagującym zakupy w internecie. Gromadzi opinie użytkowników o dokonanych przez nich transakcjach po to, by e-zakupy były jak najmniej ryzykowne. Przez ponad 2 lata działalności zespół Opineo.pl stworzył jeden z największych w Polsce serwisów oceniających sklepy internetowe. Każdy sklep jest traktowany na równych zasadach, nie jesteśmy zależni od żadnego sklepu czy porównywarki cenowej, to przekłada się na naszą wiarygodność i dużą liczbę użytkowników.</div>
                  <img src="obrazki/logo/logo_opineo.png" alt="" />
                </td></tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Włącz moduł Opinie OPINEO:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['1'], $parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['0'], 'integracja_opineo_opinie_wlaczony', $parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_OPINEO_OPINIE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label class="required">Login:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" name="integracja_opineo_opinie_login" value="'.$parametr['INTEGRACJA_OPINEO_OPINIE_LOGIN']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_OPINEO_OPINIE_LOGIN']['2'].'" />';
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label class="required">Hasło:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="password" name="integracja_opineo_opinie_pass" value="'.$parametr['INTEGRACJA_OPINEO_OPINIE_PASS']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_OPINEO_OPINIE_PASS']['2'].'" />';
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Liczba dni do wysłania zaproszenia:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['1'], $parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['0'], 'integracja_opineo_opinie_czas', $parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['2'], '', $parametr['INTEGRACJA_OPINEO_OPINIE_CZAS']['3'] );
                    ?>
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'opineo' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>
          
          
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="okazjeForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="okazje" />
            </div>
            
            <div class="obramowanie_tabeliSpr">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td style="text-align:left" colspan="2">Wiarygodne Opinie okazje.info</td>
                </tr>
                
                <tr><td colspan="2" class="sledzenie_opis">
                  <div>Program Wiarygodne Opinie to system gromadzenia i kontrolowania wartościowych opinii i ocen, wystawianych przez Twoich klientów po dokonaniu zakupu. Udział w Programie pozwala na podniesienie wiarygodności Twojego sklepu wśród użytkowników, kupujących online. W Programie może uczestniczyć każdy sklep, który współpracuje z Okazje.info. Przystępując do Programu, Twój sklep otrzyma specjalne oznaczenie na listingach oraz na stronie sklepu, co pozwoli wyróżnić go na tle konkurentów.</div>
                  <img src="obrazki/logo/logo_okazje.png" alt="" />
                </td></tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Włącz moduł opinii okazje.info:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OKAZJE_WLACZONY']['1'], $parametr['INTEGRACJA_OKAZJE_WLACZONY']['0'], 'integracja_okazje_wlaczony', $parametr['INTEGRACJA_OKAZJE_WLACZONY']['2'], '', $parametr['INTEGRACJA_OKAZJE_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label class="required">Identyfikator w serwisie okazje.info:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_okazje_id" name="integracja_okazje_id" value="'.$parametr['INTEGRACJA_OKAZJE_ID']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_OKAZJE_ID']['2'].'" />';
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'okazje' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_sledzenie.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="salesmediaForm" class="cmxform"> 
            
            <div>
              <input type="hidden" name="akcja" value="zapisz" />
              <input type="hidden" name="system" value="salesmedia" />
            </div>
            
            <div class="obramowanie_tabeliSpr">
            
              <table class="listing_tbl">
              
                <tr class="div_naglowek">
                  <td style="text-align:left" colspan="2">Program partnerski Salesmedia.pl</td>
                </tr>
                
                <tr><td colspan="2" class="sledzenie_opis">
                  <div>Sieć afiliacyjna nastawiona na budowanie długotrwałych relacji z wydawcą i reklamodawcą. Naszym celem jest jak najefektywniejsze wykorzystywanie powierzchni reklamowych w postaci generowanych sprzedaży. Rozliczamy się tylko za wygenerowane sprzedaże przez Wydawców z naszej sieci, a przy tym świadczymy innowacyjną i transparentną technologie, która da Wydawcy jak i Reklamodawcy jeszcze większą kontrolę na prowadzonymi działaniami.</div>
                  <img src="obrazki/logo/logo_salesmedia.png" alt="" />
                </td></tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label>Włącz integrację Salesmedia:</label>
                  </td>
                  <td>
                    <?php
                    echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['1'], $parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['0'], 'integracja_salesmedia_wlaczony', $parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['2'], '', $parametr['INTEGRACJA_SALESMEDIA_WLACZONY']['3'] );
                    ?>
                  </td>
                </tr>
                
                <tr class="pozycja_off">
                  <td style="width:225px;padding-left:25px">
                    <label class="required">Identyfikator w serwisie Salesmedia:</label>
                  </td>
                  <td>
                    <?php
                    echo '<input type="text" id="integracja_salesmedia_id" name="integracja_salesmedia_id" value="'.$parametr['INTEGRACJA_SALESMEDIA_ID']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_SALESMEDIA_ID']['2'].'" />';
                    ?>
                  </td>
                </tr>
                
                <tr>
                  <td colspan="2">
                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'salesmedia' ? $wynik : '' ); ?>
                    </div>
                  </td>
                </tr>
                
              </table>
              
            </div>

            </form>
            
          </div>

        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
