<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        //
        $pola = array(
                array('templates_id',$filtr->process($_POST['szablon'])),
                array('language_id',$filtr->process($_POST['jezyk'])),
                array('title',$filtr->process($_POST['temat'])),
                array('content',$filtr->process($_POST['wiadomosc'])),
                array('destination',$filtr->process($_POST['odbiorcy'])),
                array('date_added','now()')
        );
        
        if ((int)$_POST['odbiorcy'] == 1 || (int)$_POST['odbiorcy'] == 2 || (int)$_POST['odbiorcy'] == 6) {
            //
            if (!empty($_POST['data_od'])) {
                $pola[] = array('order_date_start',date('Y-m-d', strtotime($filtr->process($_POST['data_od']))));
            }
            if (!empty($_POST['data_do'])) {
                $pola[] = array('order_date_end',date('Y-m-d', strtotime($filtr->process($_POST['data_do']))));
            }
            $pola[] = array('order_status',$filtr->process($_POST['status']));
            $pola[] = array('order_min',$filtr->process($_POST['wartosc_od']));
            $pola[] = array('order_max',$filtr->process($_POST['wartosc_do']));
            //
        }

        if ((int)$_POST['odbiorcy'] == 2) {
        
            if ( isset($_POST['newsletter_grupa']) ) {
                 $grupyNewslettera = ',' . implode(',', $filtr->process($_POST['newsletter_grupa'])) . ',';
            }        
            $pola[] = array('customers_newsletter_group',$grupyNewslettera);
            unset($grupyNewslettera);
            
        }
        
        if ((int)$_POST['odbiorcy'] == 3) {
        
            if (!empty($_POST['data_aktywacji'])) {
                $pola[] = array('activation',date('Y-m-d', strtotime($filtr->process($_POST['data_aktywacji']))));
            }
            
        }          
        
        // jezeli wybrano odbiorcow dla okreslonej grupy klientow
        if ((int)$_POST['odbiorcy'] == 6) {
            //
            $pola[] = array('customers_group_id',$filtr->process($_POST['grupa_klientow']));
            //
        }        
        
        $sql = $db->insert_query('newsletters' , $pola);
        unset($pola);         
     
        $id_dodanej_pozycji = $db->last_id_query();
 
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('newsletter.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('newsletter.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Definiowanie nowego newslettera</div>
    <div id="cont">
          
      <!-- Skrypt do walidacji formularza -->
      <script type="text/javascript">
      //<![CDATA[
      $(document).ready(function() {
        $("#newsForm").validate({
          rules: {
            temat: {
              required: true
            }            
          },
          messages: {
            temat: {
              required: "Pole jest wymagane"
            }      
          }
        });

        ckedit('wiadomosc','99%','500px');

        $('input.datepicker').Zebra_DatePicker({
           format: 'd-m-Y',
           inside: false,
           readonly_element: false
        });        

      });         

      function zmien_divy(id) {
        if (parseInt(id) == 1 || parseInt(id) == 2) {
            $('#warunki').slideDown();
            $('#grupa_klientow').slideUp();
            $('#warunki_box').slideUp();
        }
        if (parseInt(id) == 6) {
            $('#warunki').slideDown();
            $('#grupa_klientow').slideDown();
            $('#warunki_box').slideUp();
        }
        if (parseInt(id) == 3) {
            $('#warunki').slideUp();
            $('#grupa_klientow').slideUp();
            $('#warunki_box').slideDown();
        } 
        if (parseInt(id) == 4) {
            $('#warunki').slideUp();
            $('#grupa_klientow').slideUp();
            $('#warunki_box').slideUp();
        }          
        if (parseInt(id) == 5) {
            $('#warunki').slideUp();
            $('#grupa_klientow').slideUp();
            $('#warunki_box').slideUp();
        }
        if (parseInt(id) == 2) {
            $('#grupy_newslettera').slideDown();
          } else {
            $('#grupy_newslettera').slideUp();
        }        
      }            
      
      //]]>
      </script>         

      <div class="poleForm">
        <div class="naglowek">Dodawanie danych</div>
        
        <form action="newsletter/newsletter_dodaj.php" method="post" id="newsForm" class="cmxform">   
        
        <div class="pozycja_edytowana">
        
            <div class="info_content">
        
            <input type="hidden" name="akcja" value="zapisz" />
            
            <p>
                <label>Szablon emaila:</label>
                <?php
                $tablica = Funkcje::ListaSzablonowEmail(false);
                echo Funkcje::RozwijaneMenu('szablon', $tablica ); ?>
            </p>

            <p id="wersja">
              <label>Wersja językowa szablonu:</label>
              <?php
              echo Funkcje::RadioListaJezykow();
              ?>
            </p>  
            
            <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

            <p>
                <label>Odbiorcy newslettera:</label>
                <?php
                $tab_tmp = array();
                $tab_tmp[] = array('id' => 1, 'text' => 'do wszystkich zarejestrowanych klientów sklepu');      
                $tab_tmp[] = array('id' => 2, 'text' => 'tylko zarejestrowani klienci którzy wyrazili zgodnę na newsletter');   
                $tab_tmp[] = array('id' => 3, 'text' => 'tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu');   
                $tab_tmp[] = array('id' => 4, 'text' => 'do wszystkich którzy zapisali się do newslettera');
                $tab_tmp[] = array('id' => 5, 'text' => 'mailing');   
                $tab_tmp[] = array('id' => 6, 'text' => 'tylko do określonej grupy klientów');   
                //
                echo Funkcje::RozwijaneMenu('odbiorcy', $tab_tmp, '', ' onclick="zmien_divy(this.value)"'); 
                //
                unset($tab_tmp);
                ?>
            </p>        

            <div id="warunki">
                <p>
                  <label>Data zamówienia od:</label>
                  <input type="text" name="data_od" value="" size="20" class="datepicker" />      
                  do: <input type="text" name="data_do" value="" size="20" class="datepicker" />      
                </p>
                
                <p>
                  <label>Status zamówienia:</label>
                  <?php
                  $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
                  echo Funkcje::RozwijaneMenu('status', $tablica,'','style="width: 350px;"'); ?>
                </p> 

                <p>
                  <label>Wartość zamówienia od:</label>
                  <input type="text" name="wartosc_od" class="kropka" value="" size="20" />      
                  do: <input type="text" name="wartosc_do" class="kropka" value="" size="20" />      
                </p>                    
            </div>
            
            <div id="warunki_box" style="display:none">
                <p>
                  <label>Data aktywacji od:</label>
                  <input type="text" name="data_aktywacji" value="" size="20" class="datepicker" />   
                </p>                  
            </div>            
            
            <div id="grupa_klientow" style="display:none">
                <p>
                  <label>Tylko do grupy klientów:</label>
                  <?php
                  $tablica = Klienci::ListaGrupKlientow(false);                                        
                  echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica); 
                  ?>
                </p>                    
            </div>
            
            <?php
            $TablicaGrup = Newsletter::GrupyNewslettera();
            if ( count($TablicaGrup) > 0 ) {
            ?>
            <div id="grupy_newslettera" style="display:none">
                <table>
                    <tr>
                        <td><label>Tylko klienci przypisaniu <br /> do grupy:</label></td>   
                        <td>
                        
                        <span class="maleInfo" style="margin-left:2px">Jeżeli nie będzie zaznaczona żadna grupa domyślnie zostaną wybrane wszystkie grupy</span>
                        
                        <?php
                        foreach ($TablicaGrup as $Grupa) {
                            //
                            echo '<input type="checkbox" value="' . $Grupa['id'] . '" name="newsletter_grupa[]" /> ' . $Grupa['text'] . '<br />';
                            //
                        }
                        ?>
                        </td>
                    </tr>
                </table>
            </div>
            <?php
            unset($TablicaGrup);
            }
            ?>            
            
            <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />
            
            <p>
              <label class="required">Tytuł newslettera:</label>
              <input type="text" name="temat" id="temat" size="83" value="" />
            </p>
            
            <p>
              <label>Treść newslettera:</label>
              <textarea id="wiadomosc" name="wiadomosc" cols="90" rows="10"></textarea>
            </p>       

            </div>
            
        </div>

        <div class="przyciski_dolne">
          <input type="submit" class="przyciskNon" value="Zapisz dane" />
          <button type="button" class="przyciskNon" onclick="cofnij('newsletter','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>   
        </div>

        </form>            

      </div> 

      <div class="objasnienia">
    
        <div class="objasnieniaTytul">Znaczniki, które możesz użyć w treści wiadomości:</div>
        <div class="objasnieniaTresc">

        <div style="padding-bottom:10px;font-weight:bold;">Treść wiadomości</div>
        
            <ul class="mcol">
              <li><b>{LINK} dowolny tekst {/LINK}</b> - Link umożliwiający wypisanie się z newslettera (tekst pomiędzy znacznikami zostanie przekształcony na link)</li>
              <li><b>{KUPON_RABATOWY}</b> - Kod kuponu rabatowego - używane jeżeli newsletter będzie używany do rozsyłania kuponów rabatowych - powiązane z menu Asortyment / Kupony rabatowe</li>
              <li><b>{ADRES_URL_SKLEPU}</b> - Adres internetowy sklepu</li>
            </ul>

            <div style="padding-bottom:10px;font-weight:bold;">Dane sklepu</div>
            <ul class="mcol">
              <?php
              $zapytanie = "SELECT * FROM settings WHERE type = 'firma' OR type = 'sklep' ORDER BY type, sort";

              $sql = $db->open_query($zapytanie);

              while ($info = $sql->fetch_assoc()) {
                echo '<li><b>{'.$info['code'].'}</b> - '.$info['description'].'</li>';
              }
              $db->close_query($sql);
              unset($zapytanie,$info);

              ?>
            </ul>
        
        </div>
      </div>   

    </div>    
    
    <?php
    include('stopka.inc.php');

}