<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $allegro = new Allegro();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        if ( $_POST['domyslny'] == '1' ) {
          $pola = array(array('value',$filtr->process($_POST["id"])));
          $db->update_query('allegro_connect' , $pola, " params = 'CONF_DEFAULT_TEMPLATE'");
        }


        $wynik = $allegro->przetworzSzablon( $_POST['id'], $_POST['szablon'], true, false );

        $katalog = KATALOG_SKLEPU.'allegro/';
        $nazwa_pliku = $katalog . $_POST['id'].'/szablon.txt';
        $fp = fopen($nazwa_pliku, "w+");
        flock($fp, 2);
        fwrite($fp, $wynik);
        flock($fp, 3);
        fclose($fp);

        Funkcje::PrzekierowanieURL('szablony.php?id_poz='.$_POST["id"]);
    }

    // wczytanie naglowka HTML

    include('naglowek.inc.php');

    ?>
    
    <div id="naglowek_cont">Edycja szablonu aukcji Allegro</div>
    <div id="cont">
          
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            ckedit('szablon','99%','800px');

          });         
          //]]>
          </script>         

          <form action="allegro/szablony_edytuj.php" method="post" id="allegroForm" class="cmxform">     

            <div class="poleForm">          

            <?php 
          
            if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = rand(1,31231213);
            }
          
            $dane = '';
            $nazwa_pliku = '../allegro/'.$_GET['id_poz'].'/szablon.txt';
            if (is_writable($nazwa_pliku)) {
                if ($plik = fopen($nazwa_pliku, "r")) {
                  $dane = fread($plik, filesize($nazwa_pliku));
                  fclose($plik);
                }
            }
          
            if ( $dane != '' ) {
            $dane = str_replace('[SERWER]', ADRES_URL_SKLEPU . '/allegro/'.$_GET['id_poz'].'/obrazki', $dane);
            ?>

              <div class="naglowek">Edycja danych</div>
            
              <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process($_GET['id_poz']); ?>" />
                    
                    <p>
                      <label>Czy jest to szablon domyślny:</label>
                      <input type="radio" name="domyslny" value="1" <?php echo ( $allegro->polaczenie['CONF_DEFAULT_TEMPLATE'] == $_GET['id_poz'] ? 'checked="checked"' : '' ); ?> /> tak
                      <input type="radio" name="domyslny" value="0" <?php echo ( $allegro->polaczenie['CONF_DEFAULT_TEMPLATE'] != $_GET['id_poz'] ? 'checked="checked"' : '' ); ?> /> nie
                    </p>                    

                    <p>
                      <textarea id="szablon" name="szablon" cols="90" rows="10"><?php echo $dane; ?></textarea>
                    </p>    

                    </div>

              </div>

              <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('szablony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>           
              </div>                 

              <?php
            
            } else {
            
              ?>
            
              <div class="naglowek">Edycja danych</div>
            
              <div class="pozycja_edytowana">            
                  <div class="pozycja_edytowana">Brak danych do wyświetlenia lub plik nie ma praw do zapisu</div>
              </div>

              <div class="przyciski_dolne">
               <button type="button" class="przyciskNon" onclick="cofnij('szablony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button>           
              </div>
              
            <?php
            }
            ?>

            </div>
            
        </form>

        <div class="objasnienia">
        
              <div class="objasnieniaTytul">Znaczniki, które możesz użyć w szablonie  <br /><span style="color:#ff0000">(użycie części znaczników wymaga dodatkowego kodu CSS - dokładny opis użycia znajduje się w instrukcji w dziale Allegro / Konfiguracja szablonów aukcji): </span></div>
              
              <br />
              
              <div class="objasnieniaTresc">

                <div style="padding-bottom:10px;font-weight:bold;">Treść szablonu aukcji</div>
                <ul class="mcol">
                  <li><b>[SERWER]</b> - adres sklepu w pełnej postaci z http://adres ....</li>
                  <li><b>[OPIS]</b> - opis produktu - dane są zwracane w formie tekstu</li>
                  <li><b>[NAZWA]</b> - nazwa produktu - dane są zwracane w formie tekstu</li>
                  <li><b>[DODATKOWA_NAZWA]</b> - dodatkowy tekst do nazwy produktu</li>
                  <li><b>[ZDJECIA]</b> - zdjęcia produktu wybrane do wyświetlania podczas wystawiania aukcji produktu</li>
                  <li><b>[ID_ALLEGRO]</b> - ID użytkownika w serwisie Allegro</li>
                  <li><b>[UZYTKOWNIK_ALLEGRO]</b> - login użytkownika w serwisie Allegro</li>
                  <li><b>[CECHY_PRODUKTU]</b> - cechy produktu wystawianego produktu (wybrane podczas wystawiania aukcji)</li>
                  <li><b>[CENA_WYWOLAWCZA]</b> - cena wywoławcza produktu na aukcji</li>
                  <li><b>[CENA_MINIMALNA]</b> - cena minimalna produktu na aukcji</li>
                  <li><b>[CENA_KUP_TERAZ]</b> - cena KUP TERAZ produktu na aukcji</li>
                  <li><b>[LICZBA_SZTUK]</b> - ilość produktu wystawionego na aukcji</li>
                  <li><b>[CZAS_WYSYLKI]</b> - czas wysyłki produktu wystawionego na aukcji</li>
                  <li><b>[STAN_PRODUKTU]</b> - stan produktu wystawionego na aukcji</li>
                  <li><b>[GWARANCJA]</b> - gwarancja produktu wystawionego na aukcji</li>
                  <li><b>[NUMER_KATALOGOWY]</b> - numer katalogowy produktu </li>
                  <li><b>[KOD_PRODUCENTA]</b> - kod producenta produktu</li>
                  <li><b>[KOD_EAN]</b> - kod EAN</li>
                  <li><b>[PKWIU]</b> - PKWiU</li>
                  <li><b>[PRODUCENT]</b> - nazwa producenta produktu</li>
                  <li><b>[LOGO_PRODUCENTA]</b> - logo producenta produktu</li>
                  <li><b>[WAGA]</b> - waga produktu w KG</li>
                  <li><b>[DODATKOWA_ZAKLADKA_x_NAZWA]</b> - tytuł dodatkowej zakładki o nr x - x to wartości od 1 do 4</li>
                  <li><b>[DODATKOWA_ZAKLADKA_x_TRESC]</b> - treść dodatkowej zakładki o nr x - x to wartości od 1 do 4</li>
                  <li><b>[DODATKOWE_POLA_OPISOWE]</b> - dodatkowe pola opisowe wystawianego produktu</li>
                  <li><b>[GALERIA]</b> - galeria zdjęć produktu</li>
                  <li><b>[INNE_AUKCJE]</b> - pozostałe aukcje jakie są oferowane przez sprzedającego</li>                 
                </ul>

              </div>
              
        </div>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
