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

    $zapytanie = "SELECT * FROM settings WHERE type = 'afiliacja' ORDER BY sort ";
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

    <div id="naglowek_cont">Konfiguracja parametrów systemów afiliacyjnych</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana" style="overflow:hidden;">  

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#webeForm").validate({
                rules: {
                  integracja_webepartners_mid: {required: function() {var wynik = true; if ( $("input[name='integracja_webepartners_zamowienia_wlaczony']:checked", "#webeForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);
            });
            //]]>
          </script> 

          <!-- Portale spolecznosciowe na karcie produktu -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="podzielForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="podziel" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Podziel się na karcie produktu - ikonki z odnośnikami do portali społecznościowych</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Dzięki wtyczce umieścisz na swojej stronie internetowej odnośniki, prosto z której użytkownicy będą mogli podzielić się linkiem do produktu.</div>
                      <img src="obrazki/logo/logo_podziel_sie.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz moduł "Podziel się":</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['1'], $parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['0'], 'integracja_podziel_sie_wlaczony', $parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_PODZIEL_SIE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'podziel' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          


          <!-- Przycisk FB "Lubie to" na karcie produktu -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="facebookForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="facebook" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Facebook - Lubię to na karcie produktu</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Dzięki wtyczce Like box umieścisz na swojej stronie internetowej ramkę, prosto z której użytkownicy będą mogli zostać Twoimi fanami. W ramce mogą być ponadto wyświetlone najnowsze aktualności z Twojej strony fanowskiej, a także zdjęcia fanów. Like box jest bezpłatną, skuteczną metodą na zwiększenie liczby fanów!.</div>
                      <img src="obrazki/logo/logo_lubie_to.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz przycisk "Lubię to":</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_LUBIETO_WLACZONY']['1'], $parametr['INTEGRACJA_FB_LUBIETO_WLACZONY']['0'], 'integracja_fb_lubieto_wlaczony', $parametr['INTEGRACJA_FB_LUBIETO_WLACZONY']['2'], '', $parametr['INTEGRACJA_FB_LUBIETO_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Format przycisku:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_LUBIETO_STYL']['1'], $parametr['INTEGRACJA_FB_LUBIETO_STYL']['0'], 'integracja_fb_lubieto_styl', $parametr['INTEGRACJA_FB_LUBIETO_STYL']['2'], '', $parametr['INTEGRACJA_FB_LUBIETO_STYL']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Schemat kolorów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_LUBIETO_KOLOR']['1'], $parametr['INTEGRACJA_FB_LUBIETO_KOLOR']['0'], 'integracja_fb_lubieto_kolor', $parametr['INTEGRACJA_FB_LUBIETO_KOLOR']['2'], '', $parametr['INTEGRACJA_FB_LUBIETO_KOLOR']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'facebook' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>
          
          
          <!-- recenzje FB na karcie produktu -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="facebookOpinieForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="facebookOpinie" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Facebook - komentarze (opinie) na karcie produktu</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wtyczka Komentarze Facebook pozwala opiniować klientom za pomocą swojego profilu na Facebooku produkty oferowane w sklepie. <br /><br />
                      <span class="ostrzezenie">Wtyczka jest powiązana z recenzjami sklepu. Wtyczka jest wyświetlana w zakładce Recenzje na karcie produktu.
                      Aby wtyczka była aktywna muszą być w sklepie włączone recenzje produktu (menu Konfiguracja / Konfiguracja sklepu / Ustawienia produktów).</span>
                      </div>
                      <img src="obrazki/logo/logo_komentarze.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz Komentarze Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['1'], $parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['0'], 'integracja_fb_opinie_wlaczony', $parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['2'], '', $parametr['INTEGRACJA_FB_OPINIE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Szerokość pola komentarzy:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_OPINIE_SZEROKOSC']['1'], $parametr['INTEGRACJA_FB_OPINIE_SZEROKOSC']['0'], 'integracja_fb_opinie_szerokosc', $parametr['INTEGRACJA_FB_OPINIE_SZEROKOSC']['2'], '', $parametr['INTEGRACJA_FB_OPINIE_SZEROKOSC']['3'], 5 );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Ilość wyświetlanych komentarzy:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['1'], $parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['0'], 'integracja_fb_opinie_ilosc_postow', $parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['2'], '', $parametr['INTEGRACJA_FB_OPINIE_ILOSC_POSTOW']['3'] );
                        ?>
                      </td>
                    </tr>                    
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Schemat kolorów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_FB_OPINIE_KOLOR']['1'], $parametr['INTEGRACJA_FB_OPINIE_KOLOR']['0'], 'integracja_fb_opinie_kolor', $parametr['INTEGRACJA_FB_OPINIE_KOLOR']['2'], '', $parametr['INTEGRACJA_FB_OPINIE_KOLOR']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'facebookOpinie' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          
          
          
          <!-- Przycisk Nasza klasa na karcie produktu -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="nkForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="nk" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Nasza klasa - Fajne na karcie produktu</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Dodanie na Twoją stronę widżetu "Fajne!" pozwala użytkownikom NK przyznawać gwiazdki materiałom, które zamieszczasz na tej stronie. Jeśli ktoś wyróżni gwiazdką daną informację, powiadomienie o tym pojawi się na NK, co sprawi, że kolejne osoby będą mogły zainteresować się Twoją publikacją.</div>
                      <img src="obrazki/logo/logo_nk.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz przycisk "Fajne!":</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_NK_FAJNE_WLACZONY']['1'], $parametr['INTEGRACJA_NK_FAJNE_WLACZONY']['0'], 'integracja_nk_fajne_wlaczony', $parametr['INTEGRACJA_NK_FAJNE_WLACZONY']['2'], '', $parametr['INTEGRACJA_NK_FAJNE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Format przycisku:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_NK_FAJNE_STYL']['1'], $parametr['INTEGRACJA_NK_FAJNE_STYL']['0'], 'integracja_nk_fajne_styl', $parametr['INTEGRACJA_NK_FAJNE_STYL']['2'], '', $parametr['INTEGRACJA_NK_FAJNE_STYL']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Schemat kolorów:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_NK_FAJNE_KOLOR']['1'], $parametr['INTEGRACJA_NK_FAJNE_KOLOR']['0'], 'integracja_nk_fajne_kolor', $parametr['INTEGRACJA_NK_FAJNE_KOLOR']['2'], '', $parametr['INTEGRACJA_NK_FAJNE_KOLOR']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'nk' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>          


          <!-- Przycisk Google +1 na karcie produktu -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="plusoneForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="plusone" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">GOOGLE - +1 na karcie produktu</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Przycisk +1 ułatwia użytkownikom znalezienie odpowiednich treści – witryn, wyników wyszukiwania Google albo reklam – dzięki opiniom osób, które znają i którym ufają. Przycisk +1 jest widoczny w wyszukiwarce Google, w witrynach oraz reklamach. Może się on na przykład znaleźć obok wyniku wyszukiwania w witrynie Google, reklamy Google lub artykułu w Twojej ulubionej witrynie informacyjnej.</div>
                      <img src="obrazki/logo/logo_plusone.png" alt="" />
                    </td></tr> 

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz przycisk PlusOne:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PLUSONE_WLACZONY']['1'], $parametr['INTEGRACJA_PLUSONE_WLACZONY']['0'], 'integracja_plusone_wlaczony', $parametr['INTEGRACJA_PLUSONE_WLACZONY']['2'], '', $parametr['INTEGRACJA_PLUSONE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Rozmiar przycisku:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PLUSONE_ROZMIAR']['1'], $parametr['INTEGRACJA_PLUSONE_ROZMIAR']['0'], 'integracja_plusone_rozmiar', $parametr['INTEGRACJA_PLUSONE_ROZMIAR']['2'], '', $parametr['INTEGRACJA_PLUSONE_ROZMIAR']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Wyświetlanie opisu:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_PLUSONE_ADNOTACJA']['1'], $parametr['INTEGRACJA_PLUSONE_ADNOTACJA']['0'], 'integracja_plusone_adnotacja', $parametr['INTEGRACJA_PLUSONE_ADNOTACJA']['2'], '', $parametr['INTEGRACJA_PLUSONE_ADNOTACJA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Szerokość wraz z opisem:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_plusone_szerokosc" value="'.$parametr['INTEGRACJA_PLUSONE_SZEROKOSC']['0'].'" size="20" class="toolTipText" title="'. $parametr['INTEGRACJA_PLUSONE_SZEROKOSC']['2'].'" />';
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'plusone' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>
                  
              </div>

            </form>
            
          </div>

          <!-- System afiliacyjny WebePartners -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="webeForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="webepartners" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Program afiliacyjny WebePartners</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Sieć WebePartners specjalizuje się w profesjonalnej obsłudze programów partnerskich sklepów internetowych. Rozlicza kampanie marketingowe w efektywnościowym modelu współpracy Cost Per Sale. Poprzez sieć wydawców zwiększa sprzedaż w sklepach internetowych w zamian za prowizję od sprzedaży.</div>
                      <img src="obrazki/logo/logo_webepartners.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Śledzenie zamówień:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['1'], $parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['0'], 'integracja_webepartners_zamowienia_wlaczony', $parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['2'], '', $parametr['INTEGRACJA_WEBEPARTNERS_ZAMOWIENIA_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Identyfikator sprzedawcy (MID):</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" name="integracja_webepartners_mid" value="'.$parametr['INTEGRACJA_WEBEPARTNERS_MID']['0'].'" size="53" class="toolTipText" title="'. $parametr['INTEGRACJA_WEBEPARTNERS_MID']['2'].'" />';
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'webepartners' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          <!-- System afiliacyjny cash4free Openrate -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="openrateForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="openrate" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Program afiliacyjny cas4free Openrate</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Program prowadzony jest przez OpenRate poprzez serwis internetowy pod adresem www.cash4free.pl, w którym OpenRate zamieści aktywne linki do stron sklepów internetowych Partnerów wraz ze wskazaniem wartości Cashback określonej kwotowo lub procentowo.</div>
                      <img src="obrazki/logo/logo_openrate.png" alt="" />
                    </td></tr>                   
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz integrację:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_OPENRATE_WLACZONY']['1'], $parametr['INTEGRACJA_OPENRATE_WLACZONY']['0'], 'integracja_openrate_wlaczony', $parametr['INTEGRACJA_OPENRATE_WLACZONY']['2'], '', $parametr['INTEGRACJA_OPENRATE_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'openrate' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>

              </div>
            </form>
          </div>
          
          <!-- chceto -->
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_afiliacja.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="chcetoForm" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="system" value="chceto" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Chce.to - odnośnik, który umożliwia jednym kliknięciem dodawać produkty na swoje chcelisty.</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Chce.to - serwis internetowy umożliwiający tworzenie chcelist, czyli list rzeczy, jakie chcemy mieć. Serwis ma na celu ułatwienie użytkownikom prostą z pozoru czynność dawania i dostawania prezentów.</div>
                      <img src="obrazki/logo/logo_chceto.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz przycisk +chce.to:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['INTEGRACJA_CHCE_TO_WLACZONY']['1'], $parametr['INTEGRACJA_CHCE_TO_WLACZONY']['0'], 'integracja_chce_to_wlaczony', $parametr['INTEGRACJA_CHCE_TO_WLACZONY']['2'], '', $parametr['INTEGRACJA_CHCE_TO_WLACZONY']['3'] );
                        ?>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'chceto' ? $wynik : '' ); ?>
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
