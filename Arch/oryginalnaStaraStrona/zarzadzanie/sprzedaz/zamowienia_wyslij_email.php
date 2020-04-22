<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        if ( isset($_POST['email_1']) && $_POST['email_1'] != '') {
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".$_SESSION['domyslny_jezyk']['id']."' WHERE t.email_var_id = 'EMAIL_ZAMOWIENIE'";
            $sql = $db->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();    
        
            $email = new Mailing;
            
            if ( $tresc['email_file'] != '' ) {
              $tablicaZalacznikow = explode(';', $tresc['email_file']);
            } else {
              $tablicaZalacznikow = array();
            }

            $nadawca_email = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa = Funkcje::parsujZmienne($tresc['sender_name']); 

            $adresat_email = $filtr->process($_POST['email_1']);
            $adresat_nazwa = $filtr->process($_POST['adresat_nazwa']);
            
            $kopia_maila = array();
            for ( $t = 2; $t < 6; $t++ ) {
                //
                if ( isset($_POST['email_' . $t]) && $_POST['email_' . $t] != '') {
                     $kopia_maila[] = $filtr->process($_POST['email_' . $t]);
                }
                //
            }

            $temat           = $filtr->process($_POST['temat']);
            $tekst           = $filtr->process($_POST['wiadomosc']);
            $zalaczniki      = $tablicaZalacznikow;
            $szablon         = $tresc['template_id'];
            $jezyk           = $_SESSION['domyslny_jezyk']['id'];  

            $email->wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, implode(',', $kopia_maila), $temat, $tekst, $szablon, $jezyk, $zalaczniki);

            $db->close_query($sql);
            unset($tresc, $zapytanie_tresc, $nadawca_email, $nadawca_nazwa, $adresat_email, $kopia_maila, $adresat_nazwa, $temat, $tekst, $szablon, $jezyk);           

        }

        Funkcje::PrzekierowanieURL('zamowienia_wyslij_email.php?wyslano');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Wysłanie wiadomości e-mail zamówienia do klienta</div>
    <div id="cont">
    
        <?php
        if ( isset($_GET['wyslano']) ) {
        ?>
          
            <div class="poleForm">
        
                <div class="naglowek">Wysyłanie wiadomości ze szczegółami zamówienia</div>

                <div class="pozycja_edytowana">

                  <div class="mailWyslano">
                      Mail został wysłany ...
                  </div>    
                  
                  <div class="przyciski_dolne">
                    <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y','wyslano')); ?>','sprzedaz');">Powrót</button> 
                  </div>

                </div>     

            </div>
            
        <?php
        
        } else {

            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
              
            $zapytanie = "select * from orders where orders_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);

            if ((int)$db->ile_rekordow($sql) > 0) {
            ?>
              
              <form action="sprzedaz/zamowienia_wyslij_email.php" method="post" id="emailForm" class="cmxform">    

                <script type="text/javascript">
                //<![CDATA[            
                $(document).ready(function(){
                    ckedit('wiadomosc','970','1000');
                    
                    // Skrypt do walidacji formularza
                    $("#emailForm").validate({
                      rules: {
                        temat: { required: true},
                        email_1: { required: true, email: true},
                        email_2: { email: true},
                        email_3: { email: true},
                        email_4: { email: true},
                        email_5: { email: true},
                      }
                    });                    
                });
                //]]>
                </script>               

                <div class="poleForm">

                  <div class="naglowek">Wysyłanie wiadomości ze szczegółami zamówienia</div>

                  <div class="pozycja_edytowana">

                    <div class="info_content">

                      <input type="hidden" name="akcja" value="zapisz" />

                      <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

                      <?php
                      $zamowienie = new Zamowienie((int)$_GET['id_poz']);
                      
                      $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".$_SESSION['domyslny_jezyk']['id']."' WHERE t.email_var_id = 'EMAIL_ZAMOWIENIE'";
                      $sql_tresc = $GLOBALS['db']->open_query($zapytanie_tresc);
                      $tresc = $sql_tresc->fetch_assoc();                      
                      ?>            

                      <p>
                        <label class="required">Temat:</label>
                        <input type="text" name="temat" id="temat" size="83" value="<?php echo str_replace('{NUMER_ZAMOWIENIA}', $zamowienie->info['id_zamowienia'], $tresc['email_title']); ?>" />
                        <input type="hidden" name="adresat_nazwa" value="<?php echo $zamowienie->klient['nazwa']; ?>" />
                      </p>       

                      <br />
                      
                      <table class="wyslijMail">
                          <tr>
                              <td><label>Wyślij na maile:</label></td>
                              <td>
                                <input type="text" size="35" name="email_1" id="email_1" value="<?php echo INFO_EMAIL_SKLEPU; ?>" /> <br />
                                <input type="text" size="35" name="email_2" id="email_2" value="<?php echo $zamowienie->klient['adres_email']; ?>" /> <br />
                                <input type="text" size="35" name="email_3" id="email_3" value="" /> <br />
                                <input type="text" size="35" name="email_4" id="email_4" value="" /> <br />
                                <input type="text" size="35" name="email_5" id="email_5" value="" />
                              </td>
                          </tr>
                      </table>  
                      
                      <?php
                      //
                      $tekst = $tresc['description'];
                      //
                      $db->close_query($sql_tresc);
                      unset($zapytanie_tresc);  
                      //
                      $i18n = new Translator($db, '1');
                      $GLOBALS['tlumacz'] = $i18n->tlumacz( array('ZAMOWIENIE_REALIZACJA','PRODUKT'), null, true );
                      //
                      // podmiana danych
                      define('NUMER_ZAMOWIENIA', $zamowienie->info['id_zamowienia']);
                      
                      if ( $zamowienie->klient['gosc'] == '0' ) {
                          define('LINK', Seo::link_SEO('zamowienia_szczegoly.php',$zamowienie->info['id_zamowienia'],'zamowienie','',true)); 
                      } else {
                          define('LINK', $GLOBALS['tlumacz']['BRAK_DOSTEPU_DO_HISTORII']); 
                      }
                                          
                      define('IMIE_NAZWISKO_KUPUJACEGO', $zamowienie->klient['nazwa']);
                      define('DATA_ZAMOWIENIA', $zamowienie->info['data_zamowienia']);
                      define('DOKUMENT_SPRZEDAZY', $zamowienie->info['dokument_zakupu_nazwa']);
                      define('FORMA_PLATNOSCI', $zamowienie->info['metoda_platnosci']);
                      
                      if ( !empty($zamowienie->info['platnosci_info']) ) {
                            define('OPIS_FORMY_PLATNOSCI', '<br />' . $zamowienie->info['platnosci_info']);
                          } else {
                            define('OPIS_FORMY_PLATNOSCI', '');
                      }
                      
                      define('FORMA_WYSYLKI', $zamowienie->info['wysylka_modul']);    

                      if ( !empty($zamowienie->info['wysylka_info']) ) {
                            define('OPIS_FORMY_WYSYLKI', '<br />' . $zamowienie->info['wysylka_info']);
                          } else {
                            define('OPIS_FORMY_WYSYLKI', '');
                      }                      
                      //
                      // generowanie listy produktow
                      $lista_produktow = '<table style="width:100%;border-collapse: collapse; border-spacing:0;">';
                      $id_produktow_zamowienia = array();
                      
                      foreach ($zamowienie->produkty AS $produkt) {
                          //
                          $id_produktow_zamowienia[] = $produkt['id_produktu'];
                          //
                          $jakie_cechy = '';
                          if ( isset($produkt['attributes']) ) {
                              //
                              foreach ( $produkt['attributes'] As $cecha_produktu ) {
                                  //
                                  $jakie_cechy .= '<br /><span style="font-size:80%">' . $cecha_produktu['cecha'] . ': ' . $cecha_produktu['wartosc'] . '</span>';
                                  //
                              }
                              //
                          }                        
                          //
                          // czy produkt ma komentarz
                          $komentarz_produktu = '';
                          if ( $produkt['komentarz'] != '' ) {
                              //
                              $komentarz_produktu = '<br /><span style="font-size:80%">' . $GLOBALS['tlumacz']['KOMENTARZ_PRODUKTU'] . ' ' . $produkt['komentarz'] . '</span>';
                              //
                          }
                          // czy sa pola tekstowe
                          $pola_tekstowe = '';
                          if ( $produkt['pola_txt'] != '' ) {
                              //
                              $tbl_pol_txt = Funkcje::serialCiag($produkt['pola_txt']);
                              foreach ( $tbl_pol_txt as $wartosc_txt ) {
                                  //
                                  // jezeli pole to plik
                                  if ( $wartosc_txt['typ'] == 'plik' ) {
                                      $pola_tekstowe .= '<br /><span style="font-size:80%">' . $wartosc_txt['nazwa'] . ':</span> <a style="font-size:80%" href="' . ADRES_URL_SKLEPU . '/inne/wgranie.php?src=' . base64_encode(str_replace('.',';',$wartosc_txt['tekst'])) . '">' . $GLOBALS['tlumacz']['WGRYWANIE_PLIKU_PLIK'] . '</a>';
                                    } else {
                                      $pola_tekstowe .= '<br /><span style="font-size:80%">' . $wartosc_txt['nazwa'] . ': ' . $wartosc_txt['tekst'] . '</span>';
                                  }            
                              }
                              unset($tbl_pol_txt);
                              //
                          }    
                          //   
                          //
                          $lista_produktow .= '<tr>';
                          $lista_produktow .= '<td style="width:50%;padding:5px">' . $produkt['nazwa'] . $jakie_cechy . $pola_tekstowe . $komentarz_produktu . '</td>';
                          $lista_produktow .= '<td style="width:15%;padding:5px;text-align:center">' . $produkt['model'] . '</td>';
                          $lista_produktow .= '<td style="width:15%;padding:5px;text-align:center">' . $waluty->FormatujCene($produkt['cena_koncowa_brutto'], false, $_SESSION['domyslna_waluta']['id']) . '</td>';
                          $lista_produktow .= '<td style="width:5%;padding:5px;text-align:center">' . $produkt['ilosc'] . '</td>';
                          $lista_produktow .= '<td style="width:15%;padding:5px;text-align:center">' . $waluty->FormatujCene($produkt['cena_koncowa_brutto'] * $produkt['ilosc'], false, $_SESSION['domyslna_waluta']['id']) . '</td>';
                          $lista_produktow .= '</tr>';
                          //
                          unset($jakie_cechy, $komentarz_produktu, $pola_tekstowe);
                          //    
                      } 

                      $lista_produktow .= '</table>';

                      define('LISTA_PRODUKTOW', $lista_produktow); 
                      unset($lista_produktow);    

                      // podsumowanie zamowienia
                      $podsumowanie_tekst = '';
                      $koncowa_wartosc_zamowienia = 0;
                      foreach ( $zamowienie->podsumowanie as $podsuma ) {
                          //
                          if ( $podsuma['klasa'] != 'ot_total' ) {
                               $podsumowanie_tekst .= $podsuma['tytul'] . ': ' . $waluty->FormatujCene($podsuma['wartosc'], false, $_SESSION['domyslna_waluta']['id']) . '<br />';
                             } else {
                               $podsumowanie_tekst .= '<span style="font-size:120%;font-weight:bold">' . $podsuma['tytul'] . ': <span style="font-size:140%">' . $waluty->FormatujCene($podsuma['wartosc'], false, $_SESSION['domyslna_waluta']['id']) . '</span></span><br />';
                               $koncowa_wartosc_zamowienia = $podsuma['wartosc'];
                          }
                          //
                      }
                      define('MODULY_PODSUMOWANIA', $podsumowanie_tekst); 
                      unset($podsumowanie_tekst);
                      
                      // komentarz do zamowienia
                      if (count($zamowienie->statusy) > 0) {
                           //
                           $koment = '';
                           foreach ($zamowienie->statusy as $komentarz) {
                              $koment = $komentarz['komentarz'];
                              break;
                           }
                           //
                           if ( !empty($koment) != '' ) {
                               define('KOMENTARZ_DO_ZAMOWIENIA', $GLOBALS['tlumacz']['KOMENTARZ_DO_ZAMOWIENIA'] . '<br />' . $koment); 
                           }
                           //
                           unset($koment);
                           //
                         } else {
                           define('KOMENTARZ_DO_ZAMOWIENIA', '');
                      }                    
                      
                      // adres zamawiajacego
                      $dane_do_faktury = '';
                      $dane_do_faktury .= $zamowienie->platnik['nazwa'];
                      if ( trim($dane_do_faktury) != '' ) {
                         $dane_do_faktury .= '<br />';
                      }                    
                      if ( $zamowienie->platnik['firma'] != '' ) {
                          //
                          $dane_do_faktury .= $zamowienie->platnik['firma'] . '<br />';
                          $dane_do_faktury .= $zamowienie->platnik['nip'] . '<br />';
                          //
                      }
                      $dane_do_faktury .= $zamowienie->platnik['ulica'] . '<br />';
                      $dane_do_faktury .= $zamowienie->platnik['kod_pocztowy'] . ' ' . $zamowienie->platnik['miasto'] . '<br />';
                      if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                          //
                          $dane_do_faktury .= $zamowienie->platnik['wojewodztwo'] . '<br />';
                          //
                      }
                      $dane_do_faktury .= $zamowienie->platnik['kraj']; 
                      define('ADRES_ZAMAWIAJACEGO', $dane_do_faktury); 
                      unset($dane_do_faktury);
                      
                      // adres dostawy
                      $dane_do_wysylki = '';
                      $dane_do_wysylki .= $zamowienie->dostawa['nazwa'];
                      if ( trim($dane_do_wysylki) != '' ) {
                         $dane_do_wysylki .= '<br />';
                      }                    
                      if ( $zamowienie->dostawa['firma'] != '' ) {
                          //
                          $dane_do_wysylki .= $zamowienie->dostawa['firma'] . '<br />';
                          //
                      }
                      $dane_do_wysylki .= $zamowienie->dostawa['ulica'] . '<br />';
                      $dane_do_wysylki .= $zamowienie->dostawa['kod_pocztowy'] . ' ' . $zamowienie->dostawa['miasto'] . '<br />';
                      if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) {
                          //
                          $dane_do_wysylki .= $zamowienie->dostawa['wojewodztwo'] . '<br />';
                          //
                      }
                      $dane_do_wysylki .= $zamowienie->dostawa['kraj'] . '<br />';
                      if ( KLIENT_POKAZ_TELEFON == 'tak' ) {
                          //
                          $dane_do_wysylki .= $GLOBALS['tlumacz']['TELEFON_SKROCONY'] . $zamowienie->klient['telefon'] . '<br />';
                          //
                      }
                      define('ADRES_DOSTAWY', $dane_do_wysylki); 
                      unset($dane_do_wysylki);       

                      // sprzedaz elektroniczna - generowanie linku do pobrania - sprawdza czy sa w zamowieniu pliki ktore maja sprzedaz elektroniczna
                      $zapytanie_online = "SELECT products_file_shopping FROM products_file_shopping WHERE products_id in (" . implode(',', $id_produktow_zamowienia) . ") and language_id = '" . $_SESSION['domyslny_jezyk']['id'] . "'";
                      $sql_online = $GLOBALS['db']->open_query($zapytanie_online); 
                      //
                      if ((int)$GLOBALS['db']->ile_rekordow($sql_online) > 0) {
                          //
                          define('LINK_PLIKOW_ELEKTRONICZNYCH', '<br /><b>' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA'] . ' <a style="text-decoration:underline" href="' . ADRES_URL_SKLEPU . '/' . $zamowienie->sprzedaz_online_link . '">' . $GLOBALS['tlumacz']['POBRANIE_PLIKOW_ZAMOWIENIA_LINK'] . '</a></b><br />'); 
                          //
                        } else {
                          //
                          define('LINK_PLIKOW_ELEKTRONICZNYCH', ''); 
                          //
                      }
                      //
                      $GLOBALS['db']->close_query($sql_online);
                      unset($id_produktow_zamowienia);                      
                      
                      //
                      $tekst = Funkcje::parsujZmienne($tekst);
                      $tekst = preg_replace("{(<br[\\s]*(>|\/>)\s*){2,}}i", "<br /><br />", $tekst);                    
                      //
                      ?>

                      <p>
                        <label>Treść wiadomości:</label>
                        <textarea id="wiadomosc" name="wiadomosc" cols="150" rows="10"><?php echo $tekst; ?></textarea>
                      </p>

                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Wyślij wiadomość e-mail" />
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button> 
                    </div>

                  </div>

                </div>

              </form>
              
              <?php
              
            } else {
            
                echo '<div class="poleForm">
                        <div class="naglowek">Wysyłanie wiadomości</div>
                        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
                      </div>';

            }
            
            $db->close_query($sql);
            unset($zapytanie);
        
        }
        ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}
