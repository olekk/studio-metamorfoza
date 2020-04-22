<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      while (list($key, $value) = each($_POST)) {
        if ( $key != 'akcja' && $key != 'ilosc_znakow') {
          $pola = array(
                  array('value',$value)
          );
          $db->update_query('allegro_connect' , $pola, " params = '".strtoupper($key)."'");	
        }
      }

      $wynik = '<div class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }

    $allegro = new Allegro(true);

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <style type="text/css">
    .info_tab_content label { width:200px; padding-left:0px; }
    .info_tab_content label.error { display:block; margin-left: 170px; }

    .info_content label { width:200px; padding-left:0px; }
    .info_content label.error { display:block; margin-left: 0px; }
    </style>

    <div id="naglowek_cont">Konfiguracja parametrów obsługi aukcji Allegro</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana" style="overflow:hidden;">  

          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#allegroForm").validate();
            setTimeout(function() {
                $('.maleSukces').fadeOut();
            }, 3000);
          });
          //]]>
          </script>

          <div>
          
            <form action="allegro/konfiguracja_polaczenia.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="allegroForm" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="zapisz" />
              </div>
              
              <div class="info_content">
              
                <div class="obramowanie_tabeliSpr" style="margin-top:10px;">
                
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td align="left" colspan="2" style="padding-left:10px;">Parametry logowania</td>
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc">&nbsp;</div><label class="required">Domyślny serwer Allegro:</label>
                      </td>
                      <td>
                        <?php
                        echo $allegro->StworzPole('conf_country', 'Domyślny serwer Allegro', '4', '1', 'Serwer Allegro PL', '0', $allegro->polaczenie['CONF_COUNTRY'], 'style="width:168px;"');
                        ?>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Sandbox to zamknięte, odizolowane środowisko deweloperskie, pozwalające na bezpieczne testowanie aplikacji działających w oparciu o WebAPI platformy Allegro. Środowisko odwzorowuje wszystkie najistotniejsze produkcyjne funkcjonalności serwisów działających na platformie Allegro, jak również pozwala na przeprowadzenie symulowanych płatności w systemie PayU." /></div><label class="required">Tryb testowy SANDBOX:</label>
                      </td>
                      <td>
                        <input type="radio" <?php echo ( $allegro->polaczenie['CONF_SANDBOX'] == 'tak' ? 'checked="checked"' : '' ); ?> value="tak" name="conf_sandbox" style="border: 0px none;">tak
                        <input type="radio" <?php echo ( $allegro->polaczenie['CONF_SANDBOX'] == 'nie' ? 'checked="checked"' : '' ); ?> value="nie" name="conf_sandbox" style="border: 0px none;">nie
                      </td>
                    </tr>

                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Użyj trybu cURL w przypadku występowania na serwerze problemów z połączeniem z webAPI serwisu Allegro. W przeciwnym wypadku należy ustawić pozostawić tryb standardowy SOAP" /></div><label class="required">Łączenie z Allegro:</label>
                      </td>
                      <td>
                        <input type="radio" <?php echo ( $allegro->polaczenie['CONF_CURL'] == 'nie' ? 'checked="checked"' : '' ); ?> value="nie" name="conf_curl" style="border: 0px none;">SOAP (zalecane)
                        <input type="radio" <?php echo ( $allegro->polaczenie['CONF_CURL'] == 'tak' ? 'checked="checked"' : '' ); ?> value="tak" name="conf_curl" style="border: 0px none;">cURL (niezalecane)
                      </td>
                    </tr>

                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Jeżeli chcesz otrzymać klucz Allegro WebAPI, wygeneruj go własnoręcznie. Przejdź do zakładki Moje Allegro > Moje konto > WebAPI: Generowanie klucza i wypełnij krótki formularz." /></div><label class="required">Klucz WebAPI:</label>
                      </td>
                      <td>
                        <input type="text" name="conf_webapi_key" id="conf_webapi_key" size="53" value="<?php echo $allegro->polaczenie['CONF_WEBAPI_KEY']; ?>" class="required" />
                      </td>
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Login do serwisu Allegro" /></div><label class="required">Login:</label>
                      </td>
                      <td>
                        <input type="text" name="conf_login" id="conf_login" size="53" value="<?php echo $allegro->polaczenie['CONF_LOGIN']; ?>" class="required" />
                      </td>                          
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Identyfikator użytkownika w serwisie Allegro, wykorzystywany tylko w szablonach aukcji do generowania linków do stron użytkownika" /></div><label>ID użytkownika:</label>
                      </td>
                      <td>
                        <input type="text" name="conf_user_id" id="conf_user_id" size="53" value="<?php echo $allegro->polaczenie['CONF_USER_ID']; ?>" />
                      </td>                          
                    </tr>
                    
                  </table>
                  
                  <table class="listing_tbl" style="margin-top:2px;">
                  
                    <tr class="div_naglowek">
                      <td align="left" colspan="2">
                        <div style="float:left;padding-left:20px;">Parametry obsługi aukcji</div>
                      </td>
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Status jaki otrzyma zamówienie utworzone na podstawie aukcji Allegro" /></div><label>Status zamówienia:</label>
                      </td>
                      <?php
                      $tablica = Sprzedaz::ListaStatusowZamowien(false, '--- Wybierz z listy ---');
                      ?>
                      <td>
                        <?php echo Funkcje::RozwijaneMenu('conf_orders_status', $tablica, $allegro->polaczenie['CONF_ORDERS_STATUS'], ' style="width: 320px;"'); ?>
                      </td>                          
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td style="width:225px">
                        <div class="pomoc"><img src="obrazki/tip.png" alt="Pomoc" class="toolTipText" title="Domyślny komentarz do zakończonej transakcji - maks. 250 znaków" /></div><label>Domyślny komentarz:</label>
                      </td>
                      <td>
                        <textarea cols="60" rows="2" name="conf_standard_comments" onkeyup="licznik_znakow(this,'iloscZnakow',250)"><?php echo $allegro->polaczenie['CONF_STANDARD_COMMENTS']; ?></textarea><br />
                        <div style="display:inline-block; margin:4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakow"><?php echo (250-strlen(utf8_decode($allegro->polaczenie['CONF_STANDARD_COMMENTS']))); ?></span></div>
                      </td>                          
                    </tr>

                    <tr>
                      <td colspan="3">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo $wynik; ?>
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>
                
              </div>
              
            </form>
          </div>

          <?php if ($allegro->polaczenie['CONF_WEBAPI_KEY'] != '' ) { ?>
          <div style="width:49%;float:left;">
          
            <form action="allegro/konfiguracja_definicje_pol.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" class="cmxform"> 
            
              <div>
                <input type="hidden" name="akcja" value="importuj" />
              </div>
              
              <div class="info_content">
              
                <div class="obramowanie_tabeliSpr" style="margin-top:10px;">
                
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td align="left" colspan="2" style="padding-left:10px;">Definicje pól formularzy</td>
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td colspan="2" style="padding:20px">
                        <?php
                        $iloscPolAllegro = $allegro->doGetSellFormFieldsCount();
                        $zapytanie = "SELECT COUNT(*) AS ilosc FROM allegro_fields";
                        $sql = $db->open_query($zapytanie);
                        while ($info = $sql->fetch_assoc()) {
                            $iloscRekordowPol = $info['ilosc'];
                        }
                        $db->close_query($sql);
                        unset($zapytanie, $info);
                        ?>
                        Definicje pól formularzy zainstalowane w sklepie: <b><?php echo $allegro->polaczenie['CONF_FIELDS_WER']; ?></b><br /><br />
                        Definicje pól formularzy aktualne w Allegro: <b><?php echo $allegro->doGetSysStatus('4'); ?></b>
                        <?php
                        if ( $iloscPolAllegro != $iloscRekordowPol ) {
                            ?>
                            <br /><br /><span class="czerwony">Dane zapisane w tabeli definicji pól są niekompletne !!!</span>
                            <?php
                        }
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Aktualizuj" />
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>
                
              </div>
              
            </form>
          </div>
          
          <div style="width:49%;float:right;">
          
            <form action="allegro/konfiguracja_definicje_kategorii.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" class="cmxform">
            
              <div>
                <input type="hidden" name="akcja" value="importuj" />
              </div>
              
              <div class="info_content">
              
                <div class="obramowanie_tabeliSpr" style="margin-top:10px;">
                
                  <table class="listing_tbl">
                  
                    <tr class="div_naglowek">
                      <td align="left" colspan="2" style="padding-left:10px;">Definicje kategorii</td>
                    </tr>
                    
                    <tr class="pozycja_offAllegro">
                      <td colspan="2" style="padding:20px">
                        <?php
                        $iloscKategoriiAllegro = $allegro->doGetCatsDataCount();
                        $zapytanie = "SELECT COUNT(*) AS ilosc FROM allegro_categories";
                        $sql = $db->open_query($zapytanie);
                        while ($info = $sql->fetch_assoc()) {
                            $iloscRekordowKategorii = $info['ilosc'];
                        }
                        $db->close_query($sql);
                        unset($zapytanie, $info);
                        ?>
                        Definicje drzewa kategorii zainstalowane w sklepie: <b><?php echo $allegro->polaczenie['CONF_CATEGORIES_WER']; ?></b><br /><br />
                        Definicje drzewa kategorii aktualne w Allegro: <b><?php echo $allegro->doGetSysStatus('3'); ?></b>
                        <?php
                        if ( $iloscKategoriiAllegro != $iloscRekordowKategorii ) {
                            ?>
                            <br /><br /><span class="czerwony">Dane zapisane w tabeli definicji kategorii są niekompletne !!!</span>
                            <?php
                        }
                        ?>
                      </td>
                    </tr>
                    
                    <tr>
                      <td colspan="2">
                        <div class="przyciski_dolne">
                          <input type="submit" class="przyciskNon" value="Aktualizuj" />
                        </div>
                      </td>
                    </tr>
                    
                  </table>
                  
                </div>
                
              </div>
              
            </form>
            
          </div>
          <?php } ?>

        </div>
      </div>
    </div>

    
    <?php
    include('stopka.inc.php');    
    
} ?>
