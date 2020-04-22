<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $tab_zakladki = array('pierwsza', 'druga', 'trzecia');

    $wynik  = '';
    $system = ( isset($_POST['system']) ? $_POST['system'] : '' );

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      if ( isset($_POST['system']) && $_POST['system'] != 'konfiguracja' ) {
      
          while (list($key, $value) = each($_POST)) {
            if ( $key != 'akcja' ) {
              //
              // usuwa http z adresu facebook
              if ( strtoupper($key) == 'ZAKLADKA_FACEBOOK_PROFIL' ) {
                   $value = str_replace( array('http://','https://','http:\\','https:\\'), '', $value);
              }
              //
              $pola = array(
                      array('value',stripslashes($value))
              );
              $db->update_query('settings' , $pola, " code = '".strtoupper($key)."'");	
              unset($pola);
            }
          }

      }
      
      if ( isset($_POST['system']) && $_POST['system'] == 'konfiguracja' ) {
      
        $pola = array(
                array('value',$_POST['konfiguracja'])
        );
        $db->update_query('settings' , $pola, " code = 'WYSUWANE_ZAKLADKI_WYSWIETLANIE'");	
        unset($pola);

      }
      
      $wynik = '<div id="'.$system.'" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $zapytanie = "SELECT * FROM settings WHERE type = 'zakladki' ORDER BY sort ";
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

    <div id="naglowek_cont">Konfiguracja parametrów wysuwanych zakładek</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana" style="overflow:hidden;">  

          <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              $("#allegroForm").validate({
                rules: {
                  zakladka_allegro_id: {required: function() {var wynik = true; if ( $("input[name='zakladka_allegro_wlaczona']:checked", "#allegroForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });            
              $("#facebookForm").validate({
                rules: {
                  zakladka_facebook_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_facebook_wlaczona']:checked", "#facebookForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });
              $("#ggForm").validate({
                rules: {
                  zakladka_gg_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_gg_wlaczona']:checked", "#ggForm").val() == "nie" ) { wynik = false; } return wynik; }},
                  zakladka_gg_numer: {required: function() {var wynik = true; if ( $("input[name='zakladka_gg_wlaczona']:checked", "#ggForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });    
              $("#nkForm").validate({
                rules: {
                  zakladka_nk_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_nk_wlaczona']:checked", "#nkForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              }); 
              $("#youtubeForm").validate({
                rules: {
                  zakladka_youtube_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_youtube_wlaczona']:checked", "#youtubeForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });  
              $("#googleForm").validate({
                rules: {
                  zakladka_google_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_google_wlaczona']:checked", "#googleForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });    
              $("#twitterForm").validate({
                rules: {
                  zakladka_twitter_profil: {required: function() {var wynik = true; if ( $("input[name='zakladka_twitter_wlaczona']:checked", "#twitterForm").val() == "nie" ) { wynik = false; } return wynik; }},
                  zakladka_twitter_widget: {required: function() {var wynik = true; if ( $("input[name='zakladka_twitter_wlaczona']:checked", "#twitterForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });              
              $("#ceneoForm").validate({
                rules: {
                  zakladka_ceneo_kod: {required: function() {var wynik = true; if ( $("input[name='zakladka_ceneo_wlaczona']:checked", "#ceneoForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });               
              $("#okazjeForm").validate({
                rules: {
                  zakladka_okazje_info_kod: {required: function() {var wynik = true; if ( $("input[name='zakladka_okazje_info_wlaczona']:checked", "#okazjeForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });               
              $("#opineoForm").validate({
                rules: {
                  zakladka_opineo_kod: {required: function() {var wynik = true; if ( $("input[name='zakladka_opineo_wlaczona']:checked", "#opineoForm").val() == "nie" ) { wynik = false; } return wynik; }}
                }
              });  
              
              <?php
              for ($r = 1; $r <= count($tab_zakladki); $r++ ) {
              ?>
              $("#<?php echo $tab_zakladki[$r - 1]; ?>Form").validate({
                rules: {
                  zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_ikona: {required: function() {var wynik = true; if ( $("input[name='zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_wlaczona']:checked", "#<?php echo $tab_zakladki[$r - 1]; ?>Form").val() == "nie" ) { wynik = false; } return wynik; }},
                  zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_szerokosc: {required: function() {var wynik = true; if ( $("input[name='zakladka_<?php echo $tab_zakladki[$r - 1]; ?>_wlaczona']:checked", "#<?php echo $tab_zakladki[$r - 1]; ?>Form").val() == "nie" ) { wynik = false; } return wynik; }, number: true }
                }
              });
              <?php
              }

              if ( isset($_GET['aktualizacja']) ) {
                   $system = 'pobranieAllegro';
              }
              ?>

              setTimeout(function() {
                $('#<?php echo $system; ?>').fadeOut();
              }, 3000);
            });
            //]]>
          </script> 
          
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="konfiguracjaForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="konfiguracja" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Konfiguracja wyświetlania wysuwanych zakładek</td>
                    </tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Czy wysuwane zakładki mają być widoczne cały czas niezależnie od szerokości sklepu ?</label>
                      </td>
                      <td>
                        <input type="radio" name="konfiguracja" value="tak" <?php echo (($parametr['WYSUWANE_ZAKLADKI_WYSWIETLANIE'][0] == 'tak') ? 'checked="checked"' : ''); ?> /> tak, mają być widoczne cały czas <br />
                        <input type="radio" name="konfiguracja" value="nie" <?php echo (($parametr['WYSUWANE_ZAKLADKI_WYSWIETLANIE'][0] == 'nie') ? 'checked="checked"' : ''); ?> /> mają być ukrywane jeżeli szerokość sklepu jest zbliżona do szerokości ekranu (zapobiega nachodzeniu zakładek na treść sklepu)
                      </td>
                    </tr>

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'konfiguracja' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>            

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(array('aktualizacja')); ?>" method="post" id="allegroForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="allegro" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Allegro</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>
                          Wyświetla wysuwaną zakładkę z losowymi <b>komentarzami</b> z Allegro. Wyświetlane są losowo komentarze z ostatnich 25 otrzymanych pozytywnych komentarzy. 
                          <span class="maleInfo">Do poprawnego działania zakładki muszą być najpierw zaimportowane komentarze z konta Allegro.</span>
                      </div>
                      <img src="obrazki/logo/logo_allegro.png" alt="" />
                    </td></tr> 

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Pobieranie danych:</label>
                      </td>
                      <td>
                        <a class="pobierzKomentarze" href="allegro/allegro_komentarze.php">pobierz <b>komentarze</b> z Allegro</a>
                        
                        <?php
                        if ( isset($_GET['aktualizacja']) ) {
                        ?>
                        <span id="pobranieAllegro" class="maleSukces">dane zostały zapamiętane</span>
                        <?php
                        }
                        ?>
                      </td>
                    </tr>                     

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Komentarze w sklepie:</label>
                      </td>
                      <td>
                        <?php
                        if (file_exists('../xml/komentarze.xml')) { 
                            //
                            echo '<span class="DataKomentarzy">dane komentarzy ostatnio pobierane: ' . date('d-m-Y H:i',filemtime('../xml/komentarze.xml')) . '</span>';
                            //
                        } else {
                            //
                            echo '<span class="DataKomentarzy">dane komentarzy nie były jeszcze pobierane !</span>';
                            //
                        }
                        ?>
                      </td>
                    </tr>                    
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę allegro:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_ALLEGRO_WLACZONA']['1'], $parametr['ZAKLADKA_ALLEGRO_WLACZONA']['0'], 'zakladka_allegro_wlaczona', $parametr['ZAKLADKA_ALLEGRO_WLACZONA']['2'], '', $parametr['ZAKLADKA_ALLEGRO_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Numer użytkownika w portalu Allegro:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_allegro_id" name="zakladka_allegro_id" value="'.$parametr['ZAKLADKA_ALLEGRO_ID']['0'].'" size="53" class="toolTipText" title="'. $parametr['ZAKLADKA_ALLEGRO_ID']['2'].'" />';
                        ?>
                        <span class="maleInfo">numer użytkownika Allegro w postaci liczbowej - jest to wartość liczbowa z linku np: http://allegro.pl/show_user.php?uid=123456 - gdzie wartość po uid= to numer użytkownika</span>
                      </td>
                    </tr>    
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Ilość wyświetlanych jednorazowo komentarzy:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_ALLEGRO_KOMENTARZE_ILOSC']['1'], $parametr['ZAKLADKA_ALLEGRO_KOMENTARZE_ILOSC']['0'], 'zakladka_allegro_komentarze_ilosc', $parametr['ZAKLADKA_ALLEGRO_KOMENTARZE_ILOSC']['2'], '', $parametr['ZAKLADKA_ALLEGRO_KOMENTARZE_ILOSC']['3'] );
                        ?>
                      </td>
                    </tr>                     

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_ALLEGRO_STRONA']['1'], $parametr['ZAKLADKA_ALLEGRO_STRONA']['0'], 'zakladka_allegro_strona', $parametr['ZAKLADKA_ALLEGRO_STRONA']['2'], '', $parametr['ZAKLADKA_ALLEGRO_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>   
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_ALLEGRO_SORT']['1'], $parametr['ZAKLADKA_ALLEGRO_SORT']['0'], 'zakladka_allegro_sort', $parametr['ZAKLADKA_ALLEGRO_SORT']['2'], '', $parametr['ZAKLADKA_ALLEGRO_SORT']['3'] );
                        ?>
                      </td>
                    </tr>  
                    
                    <?php
                    if (file_exists('../xml/komentarze.xml')) { 
                    ?>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'allegro' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                    
                    <?php
                    }
                    ?>
                    
                  </table>

              </div>
            </form>
            
          </div>                   

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="facebookForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="facebook" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Facebook - Like Box</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z treścią z Facebook w formie Like Box.</div>
                      <img src="obrazki/logo/logo_lubie_to.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_FACEBOOK_WLACZONA']['1'], $parametr['ZAKLADKA_FACEBOOK_WLACZONA']['0'], 'zakladka_facebook_wlaczona', $parametr['ZAKLADKA_FACEBOOK_WLACZONA']['2'], '', $parametr['ZAKLADKA_FACEBOOK_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Adres profilu strony na Facebook:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_facebook_profil" name="zakladka_facebook_profil" value="'.$parametr['ZAKLADKA_FACEBOOK_PROFIL']['0'].'" size="53" class="toolTipText" title="'. $parametr['ZAKLADKA_FACEBOOK_PROFIL']['2'].'" />';
                        ?>
                        <span class="maleInfo">adres profilu facebook w postaci adresu np www.facebook.com/platform - bez http:</span>
                      </td>
                    </tr>    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_FACEBOOK_STRONA']['1'], $parametr['ZAKLADKA_FACEBOOK_STRONA']['0'], 'zakladka_facebook_strona', $parametr['ZAKLADKA_FACEBOOK_STRONA']['2'], '', $parametr['ZAKLADKA_FACEBOOK_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>   
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_FACEBOOK_SORT']['1'], $parametr['ZAKLADKA_FACEBOOK_SORT']['0'], 'zakladka_facebook_sort', $parametr['ZAKLADKA_FACEBOOK_SORT']['2'], '', $parametr['ZAKLADKA_FACEBOOK_SORT']['3'] );
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
          
          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ggForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="gg" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka GG - okno komunikatora</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z oknem komunikatora GG.</div>
                      <img src="obrazki/logo/logo_gg.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę GG:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GG_WLACZONA']['1'], $parametr['ZAKLADKA_GG_WLACZONA']['0'], 'zakladka_gg_wlaczona', $parametr['ZAKLADKA_GG_WLACZONA']['2'], '', $parametr['ZAKLADKA_GG_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Unikalny kod widgetu GG:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_profil" name="zakladka_gg_profil" value="'.$parametr['ZAKLADKA_GG_PROFIL']['0'].'" size="73" class="toolTipText" title="'. $parametr['ZAKLADKA_GG_PROFIL']['2'].'" />';
                        ?>
                        <span class="maleInfo">
                            unikalny kod widgetu GG - fragment z wygenerowanego kodu widgetu GG zaznaczony na obrazku poniżej żółtym kolorem - kod widgetu generuje się na stronie: http://www.gg.pl/info/komunikator-na-twoja-strone/ 
                            (przy wyświetlaniu w sklepie widgetu nie są brane pod uwagę ustawienia wpisane podczas generowania widgetu na stronie GG, tj. kolor, nazwa na przycisku, wiadomość powitalna i pozostałe) <br /><br />
                            <img style="border:1px solid #ccc" src="obrazki/pomoc/gg_zakladka.jpg" alt="" />
                        </span>
                      </td>
                    </tr> 

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Numer komunikatora GG:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_numer" name="zakladka_gg_numer" value="'.$parametr['ZAKLADKA_GG_NUMER']['0'].'" size="20" />';
                        ?>
                      </td>
                    </tr>    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Wiadomość powitalna, gdy użytkownik jest <b style="color:#44a04c">Dostępny</b>:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_online" name="zakladka_gg_online" value="'.$parametr['ZAKLADKA_GG_ONLINE']['0'].'" size="60" />';
                        ?>
                      </td>
                    </tr>    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Wiadomość powitalna, gdy użytkownik jest <b style="color:#ff0000">Niedostępny</b>:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_gg_offline" name="zakladka_gg_offline" value="'.$parametr['ZAKLADKA_GG_OFFLINE']['0'].'" size="60" />';
                        ?>
                      </td>
                    </tr>                     

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GG_STRONA']['1'], $parametr['ZAKLADKA_GG_STRONA']['0'], 'zakladka_gg_strona', $parametr['ZAKLADKA_GG_STRONA']['2'], '', $parametr['ZAKLADKA_GG_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GG_SORT']['1'], $parametr['ZAKLADKA_GG_SORT']['0'], 'zakladka_gg_sort', $parametr['ZAKLADKA_GG_SORT']['2'], '', $parametr['ZAKLADKA_GG_SORT']['3'] );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'gg' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>           

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="nkForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="nk" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka NaszaKlasa</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z NaszejKlasy. Dodanie widżetu Naszej Klasy "Dołącz do Grupy" pozwola zaprezentować własną Grupę użytkownikom sklepu.</div>
                      <img src="obrazki/logo/logo_nk.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę NaszaKlasa:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_NK_WLACZONA']['1'], $parametr['ZAKLADKA_NK_WLACZONA']['0'], 'zakladka_nk_wlaczona', $parametr['ZAKLADKA_NK_WLACZONA']['2'], '', $parametr['ZAKLADKA_NK_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Numer NK:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_nk_profil" name="zakladka_nk_profil" value="'.$parametr['ZAKLADKA_NK_PROFIL']['0'].'" size="53" class="toolTipText" title="'. $parametr['ZAKLADKA_NK_PROFIL']['2'].'" />';
                        ?>
                        <span class="maleInfo">aby uzyskać numer NK należy po zalogowaniu się na swoje konto w NK wygenerować widget "Dołącz do Grupy" - strona do generowania widgetów http://nk.pl/widgets; po wygenerowaniu widgetu należy z wygenerowanego kodu skopiować wartość liczbową z ciągu znaków: data-nk-group-id="1234" (w podanym przykładzie jest to 1234); wartość ta to Numer NK</span>
                      </td>
                    </tr>    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_NK_STRONA']['1'], $parametr['ZAKLADKA_NK_STRONA']['0'], 'zakladka_nk_strona', $parametr['ZAKLADKA_NK_STRONA']['2'], '', $parametr['ZAKLADKA_NK_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_NK_SORT']['1'], $parametr['ZAKLADKA_NK_SORT']['0'], 'zakladka_nk_sort', $parametr['ZAKLADKA_NK_SORT']['2'], '', $parametr['ZAKLADKA_NK_SORT']['3'] );
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

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="youtubeForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="youtube" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Youtube</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z Youtube.</div>
                      <img src="obrazki/logo/logo_youtube.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę Youtube:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_WLACZONA']['1'], $parametr['ZAKLADKA_YOUTUBE_WLACZONA']['0'], 'zakladka_youtube_wlaczona', $parametr['ZAKLADKA_YOUTUBE_WLACZONA']['2'], '', $parametr['ZAKLADKA_YOUTUBE_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Nazwa użytkownika youtube.com:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_youtube_profil" name="zakladka_youtube_profil" value="'.$parametr['ZAKLADKA_YOUTUBE_PROFIL']['0'].'" size="53" class="toolTipText" title="'. $parametr['ZAKLADKA_YOUTUBE_PROFIL']['2'].'" />';
                        ?>
                        <span class="maleInfo">nazwa użytkownika youtube.com - jest to wartość wyświetlana w linku youtube za słowem user np: https://www.youtube.com/user/mojanazwa - należy wpisać samą nazwę - w tym przykładzie słowo: mojanazwa</span>
                      </td>
                    </tr>    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_STRONA']['1'], $parametr['ZAKLADKA_YOUTUBE_STRONA']['0'], 'zakladka_youtube_strona', $parametr['ZAKLADKA_YOUTUBE_STRONA']['2'], '', $parametr['ZAKLADKA_YOUTUBE_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_YOUTUBE_SORT']['1'], $parametr['ZAKLADKA_YOUTUBE_SORT']['0'], 'zakladka_youtube_sort', $parametr['ZAKLADKA_YOUTUBE_SORT']['2'], '', $parametr['ZAKLADKA_YOUTUBE_SORT']['3'] );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'youtube' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div> 

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="googleForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="google" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Google Plus</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z Google Plus.</div>
                      <img src="obrazki/logo/logo_google_plus.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę Google Plus:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GOOGLE_WLACZONA']['1'], $parametr['ZAKLADKA_GOOGLE_WLACZONA']['0'], 'zakladka_google_wlaczona', $parametr['ZAKLADKA_GOOGLE_WLACZONA']['2'], '', $parametr['ZAKLADKA_GOOGLE_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Nazwa użytkownika google.com:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_google_profil" name="zakladka_google_profil" value="'.$parametr['ZAKLADKA_GOOGLE_PROFIL']['0'].'" size="53" class="toolTipText" title="'. $parametr['ZAKLADKA_GOOGLE_PROFIL']['2'].'" />';
                        ?>
                        <span class="maleInfo">nazwa użytkownika google.com - jest to wartość liczbowa wyświetlana w linku google prowadzącym do profilu użytkownika; numer można uzyskać wchodząc na własną stronę profilu google plus; następnie należy najechać kursorem myszy na nazwę użytkownika na dowolnym swoim wpisie (poście) - w pasku przeglądarki będzie widoczny będzie link użytkownika z unikalnym numerem np: https://plus.google.com/10173625062436423423 - w pole nazwa użytkownika należy wpisać wartość liczbową z linku</span>
                      </td>
                    </tr>    

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GOOGLE_STRONA']['1'], $parametr['ZAKLADKA_GOOGLE_STRONA']['0'], 'zakladka_google_strona', $parametr['ZAKLADKA_GOOGLE_STRONA']['2'], '', $parametr['ZAKLADKA_GOOGLE_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_GOOGLE_SORT']['1'], $parametr['ZAKLADKA_GOOGLE_SORT']['0'], 'zakladka_google_sort', $parametr['ZAKLADKA_GOOGLE_SORT']['2'], '', $parametr['ZAKLADKA_GOOGLE_SORT']['3'] );
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
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="twitterForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="twitter" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Twitter</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z Twitter.</div>
                      <img src="obrazki/logo/logo_twitter.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę Twitter:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_TWITTER_WLACZONA']['1'], $parametr['ZAKLADKA_TWITTER_WLACZONA']['0'], 'zakladka_twitter_wlaczona', $parametr['ZAKLADKA_TWITTER_WLACZONA']['2'], '', $parametr['ZAKLADKA_TWITTER_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Nazwa użytkownika twitter.com:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_twitter_profil" name="zakladka_twitter_profil" value="'.$parametr['ZAKLADKA_TWITTER_PROFIL']['0'].'" size="53" class="toolTipText" title="'. $parametr['ZAKLADKA_TWITTER_PROFIL']['2'].'" />';
                        ?>
                        <span class="maleInfo">nazwa użytkownika Twitter - jest to wartość wyświetlana w linku twittera za adresem portalu np: https://twitter.com/adres24pl - należy wpisać samą nazwę - w tym przykładzie słowo: adres24pl</span>
                      </td>
                    </tr>    
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Identyfikator widgetu twitter.com:</label>
                      </td>
                      <td>
                        <?php
                        echo '<input type="text" id="zakladka_twitter_widget" name="zakladka_twitter_widget" value="'.$parametr['ZAKLADKA_TWITTER_WIDGET']['0'].'" size="53" class="toolTipText" title="'. $parametr['ZAKLADKA_TWITTER_WIDGET']['2'].'" />';
                        ?>
                      </td>
                    </tr>                     

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Strona po której ma się wyświetlać zakładka:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_TWITTER_STRONA']['1'], $parametr['ZAKLADKA_TWITTER_STRONA']['0'], 'zakladka_twitter_strona', $parametr['ZAKLADKA_TWITTER_STRONA']['2'], '', $parametr['ZAKLADKA_TWITTER_STRONA']['3'] );
                        ?>
                      </td>
                    </tr>  

                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Kolejność wyświetlania na stronie:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_TWITTER_SORT']['1'], $parametr['ZAKLADKA_TWITTER_SORT']['0'], 'zakladka_twitter_sort', $parametr['ZAKLADKA_TWITTER_SORT']['2'], '', $parametr['ZAKLADKA_TWITTER_SORT']['3'] );
                        ?>
                      </td>
                    </tr>                     

                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo ( $system == 'twitter' ? $wynik : '' ); ?>
                        </div>
                      </td>
                    </tr>
                  </table>

              </div>
            </form>
            
          </div>

          <?php
          // dodatkowe indywidualne zakladki
          for ($r = 1; $r <= count($tab_zakladki); $r++ ) {
          
              $nazwa = $tab_zakladki[$r - 1];
              $nr = $r;
              //
              include('konfiguracja_zakladki_indywidualna.php');
              //
              unset($nazwa, $nr);
              
          }
          unset($tab_zakladki);
          ?>                    

          <div class="sledzenie">
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ceneoForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="ceneo" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Ceneo Sprawdź nas</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z Ceneo "Sprawdź nas". Narzędzie, dzięki któremu sklepy mogą opublikować informacje gromadzone przez Ceneo (m.in. oceny klientów) na swojej stronie.<br /><span class="maleInfo">Uwaga: Zakładka działa niezależnie od zakładek systemów społacznościowch - jej zawartość konfiguruje się w panelu w serwisie Ceneo - skąd należy pobrać gotowy kod do wklejenia w poniższy formularz.</span></div>
                      <img src="obrazki/logo/logo_ceneo.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę Ceneo:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_CENEO_WLACZONA']['1'], $parametr['ZAKLADKA_CENEO_WLACZONA']['0'], 'zakladka_ceneo_wlaczona', $parametr['ZAKLADKA_CENEO_WLACZONA']['2'], '', $parametr['ZAKLADKA_CENEO_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Kod wyświetlający widget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea cols="110" rows="5" name="zakladka_ceneo_kod" class="toolTipText" title="'. $parametr['ZAKLADKA_CENEO_KOD']['2'].'">'.$parametr['ZAKLADKA_CENEO_KOD']['0'].'</textarea>';
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
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="okazjeForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="okazje" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka Okazje.info Wiarygodne opinie</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z Okazje.info "Wiarygodne opinie". Program Wiarygodne Opinie to darmowy system zarządzania opiniami i ocenami Twojego sklepu wystawianymi przez Klientów po dokonaniu zakupu. Najlepsze sklepy wyróżniane są certyfikatem "Polecany przez Klientów".<br /><span class="maleInfo">Uwaga: Zakładka działa niezależnie od zakładek systemów społacznościowch - jej zawartość konfiguruje się w panelu w serwisie Okazje.info - skąd należy pobrać gotowy kod do wklejenia w poniższy formularz.</span></div>
                      <img src="obrazki/logo/logo_opinie_okazje.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę Okazje.info:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['1'], $parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['0'], 'zakladka_okazje_info_wlaczona', $parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['2'], '', $parametr['ZAKLADKA_OKAZJE_INFO_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Kod wyświetlający widget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea cols="110" rows="5" name="zakladka_okazje_info_kod" class="toolTipText" title="'. $parametr['ZAKLADKA_OKAZJE_INFO_KOD']['2'].'">'.$parametr['ZAKLADKA_OKAZJE_INFO_KOD']['0'].'</textarea>';
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
          
            <form action="integracje/konfiguracja_zakladki.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="opineoForm" class="cmxform">
            
              <div>
                  <input type="hidden" name="akcja" value="zapisz" />
                  <input type="hidden" name="system" value="opineo" />
              </div>
              
              <div class="obramowanie_tabeliSpr">
              
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td style="text-align:left" colspan="2">Wysuwana zakładka OPINEO Zaufane opinie</td>
                    </tr>
                    
                    <tr><td colspan="2" class="sledzenie_opis">
                      <div>Wyświetla wysuwaną zakładkę z OPINEO "Zaufane opinie". Opineo.pl jest serwisem propagującym zakupy w internecie. Gromadzi opinie użytkowników o dokonanych przez nich transakcjach po to, by e-zakupy były jak najmniej ryzykowne.<br /><span class="maleInfo">Uwaga: Zakładka działa niezależnie od zakładek systemów społacznościowch - jej zawartość konfiguruje się w panelu w serwisie Okazje.info - skąd należy pobrać gotowy kod do wklejenia w poniższy formularz.</span></div>
                      <img src="obrazki/logo/logo_opineo.png" alt="" />
                    </td></tr>                  
                  
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label>Włącz zakładkę OPINEO:</label>
                      </td>
                      <td>
                        <?php
                        echo Konfiguracja::Dopuszczalne_Wartosci_Auto($parametr['ZAKLADKA_OPINEO_WLACZONA']['1'], $parametr['ZAKLADKA_OPINEO_WLACZONA']['0'], 'zakladka_opineo_wlaczona', $parametr['ZAKLADKA_OPINEO_WLACZONA']['2'], '', $parametr['ZAKLADKA_OPINEO_WLACZONA']['3'] );
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_off">
                      <td style="width:225px;padding-left:25px">
                        <label class="required">Kod wyświetlający widget:</label>
                      </td>
                      <td>
                        <?php
                        echo '<textarea cols="110" rows="5" name="zakladka_opineo_kod" class="toolTipText" title="'. $parametr['ZAKLADKA_OPINEO_KOD']['2'].'">'.$parametr['ZAKLADKA_OPINEO_KOD']['0'].'</textarea>';
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
        
        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
