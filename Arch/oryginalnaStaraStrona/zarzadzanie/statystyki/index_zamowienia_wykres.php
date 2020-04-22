<?php
if ($prot->wyswietlStrone && empty($GLOBALS['uprawnieniaZakladki']['zakladkaZamowienia'])) {
?>

    <div class="legenda">
        <div class="legendaLinia">ilość zamówień</div>
            <div class="legendaSlupek">wartość zamówień</div>
    </div>

    <div class="DuzyWykres">
        <div id="wykres_PLN"></div>
    </div>

    <script type="text/javascript" src="javascript/swfobject.js"></script>
    <script type="text/javascript">
        swfobject.embedSWF(
        "programy/openChart/open-flash-chart.swf", "wykres_PLN",
        "390", "230", "9.0.0", "expressInstall.swf",
        {"data-file":"statystyki/index_zamowienia_wykres_dzienny.php?waluta=PLN"}, {"wmode" : "transparent"} );
    </script> 

<?php

} else {

    echo '<div class="ModulyOstrzezenie">Nie posiadasz uprawień do przeglądania tego elementu.</div>';

}

?>