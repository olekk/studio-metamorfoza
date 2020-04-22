<?php
if ($prot->wyswietlStrone && empty($GLOBALS['uprawnieniaZakladki']['zakladkaKlienci'])) {
?>

    <div class="NadWykres">
        <div id="wykres"></div>
    </div>
                    
    <script type="text/javascript" src="javascript/swfobject.js"></script>
    <script type="text/javascript">
        swfobject.embedSWF(
            "programy/openChart/open-flash-chart.swf", "wykres",
            "390", "230", "9.0.0", "expressInstall.swf",
            {"data-file":"statystyki/index_rejestracje_klientow_wykres_dzienny.php"}, {"wmode" : "transparent"} );
    </script>   

<?php

} else {

    echo '<div class="ModulyOstrzezenie">Nie posiadasz uprawień do przeglądania tego elementu.</div>';

}

?>