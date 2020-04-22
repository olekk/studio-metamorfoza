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
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Wykres rejestracji klientów</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje rejestracje klientów w sklepie w określonych przedziałach czasowych</span>

                    <div class="NadWykres">
                        <div id="wykres"></div>                                              
                    </div>
                    
                    <script type="text/javascript" src="javascript/swfobject.js"></script>
                    <script type="text/javascript">
                    swfobject.embedSWF(
                    "programy/openChart/open-flash-chart.swf", "wykres",
                    "980", "230", "9.0.0", "expressInstall.swf",
                    {"data-file":"statystyki/rejestracje_klientow_wykres_dzienny.php"}, {"wmode" : "transparent"} );
                    </script>   
                    
                    <br /><br />

                    <div class="NadWykres">
                        <div id="wykresMiesiace"></div>                                              
                    </div>
                    
                    <script type="text/javascript" src="javascript/swfobject.js"></script>
                    <script type="text/javascript">
                    swfobject.embedSWF(
                    "programy/openChart/open-flash-chart.swf", "wykresMiesiace",
                    "980", "230", "9.0.0", "expressInstall.swf",
                    {"data-file":"statystyki/rejestracje_klientow_wykres_miesieczny.php"}, {"wmode" : "transparent"} );
                    </script>                    
                    
                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}