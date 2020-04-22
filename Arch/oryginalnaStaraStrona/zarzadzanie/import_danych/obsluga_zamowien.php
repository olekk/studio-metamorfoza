<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Eksport zamówień do plików CSV</div>
    <div id="cont">

        <!-- Skrypt do walidacji formularza -->
        <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
                $("#exportForm").validate({
                    rules: {
                        zamowienie_start: {
                             range: [0, 999999],
                             number: true
                        },
                        zamowienie_koniec: {
                             range: [0, 999999],
                             number: true
                        },
                    }
                });
            });
            //]]>
        </script>

          <div class="poleForm">
            <div class="naglowek">Obsługa eksportu zamówień</div>

                <div class="pozycja_edytowana">  
                
                    <form action="import_danych/obsluga_zamowien_export.php" method="post" class="cmxform" id="exportForm">

                        <input type="hidden" name="akcja" value="export" />    

                        <div class="info_content">
                                    
                            <p>
                                <label>Status zamówień:</label>
                                <?php
                                $default = '';
                                if ( isset($_POST['status']) ) $default = $_POST['status'];
                                $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- dowolny ---');
                                echo Funkcje::RozwijaneMenu('status', $tablica,'',' style="width: 350px;"'); ?>
                                </p>           
                            <p>
                                  <label>Początkowy numer zamówienia:</label>
                                  <input type="text" name="zamowienie_start" id="zamowienie_start" value="" size="15" />
                            </p>           
                            <p>
                                  <label>Końcowy numer zamówienia:</label>
                                  <input type="text" name="zamowienie_koniec" id="zamowienie_koniec" value="" size="15" />
                            </p>           

                            <p>
                                  <label>Informacje o produktach:</label>
                                  <input type="radio" style="border:0px" name="produkty" value="1" />tak
                                  <input type="radio" checked="checked" style="border:0px" name="produkty" value="0" />nie
                            </p>           

                       </div>

                            <div class="przyciski_dolne" style="padding-left:0px">
                                <input type="submit" class="przyciskNon" value="Eksportuj dane CSV" />
                            </div>
                    </form>

                </div>

          </div>

    </div>    
    
    <?php
    include('stopka.inc.php');

}