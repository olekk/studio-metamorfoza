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
            <div class="naglowek">Wykres sprzedaży w okresach miesięcznych</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje wartość sprzedaży w okresach miesięcznych</span>
                    
                    <div id="margin-top:8px">
                    
                        <div class="MalyWykres">
                            <div id="wykresPrognoza"></div>                                              
                        </div>
                        
                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykresPrognoza",
                        "450", "230", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/wykres_sprzedazy_wykres_prognoza.php"}, {"wmode" : "transparent"} );
                        </script>

                        <div class="MalyWykres">
                            <div id="wykresPrognozaMiesiace"></div>                                              
                        </div>
                        
                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykresPrognozaMiesiace",
                        "450", "230", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/wykres_sprzedazy_wykres_prognoza_miesiace.php"}, {"wmode" : "transparent"} );
                        </script>        

                        <div class="cl"></div>
                    
                    </div>
                    
                    <?php
                    $zapytanieWaluty = "select code, title from currencies";
                    $sqlWaluta = $db->open_query($zapytanieWaluty);
                    
                    while ($infr = $sqlWaluta->fetch_assoc()) {
                    
                        // ilosc miesiacy w dacie dzisiejszej
                        $miesiac = (date('Y',time()) * 12) + date('m',time());                    
                    
                        $ObliczRokDo = (int)($miesiac/12);
                        if ( $ObliczRokDo == ($miesiac/12) ) {
                             $ObliczRokDok = $ObliczRokDo - 1;
                        }
        
                        $ObliczMiesiacDo = $miesiac - ((int)($miesiac/12) * 12);  
                        
                        if ( $ObliczMiesiacDo == 0 ) {
                             $ObliczMiesiacDo = 12;
                        }
                        
                        $miesiac = $miesiac - 17;

                        $ObliczRokOd = (int)($miesiac/12);
                        $ObliczMiesiacOd = $miesiac - ((int)($miesiac/12) * 12);                              
                    
                        $zapytanie = "select o.orders_id,
                                             o.currency,
                                             o.date_purchased, 
                                             ot.orders_id,
                                             ot.value, 
                                             ot.class
                                        from orders o, orders_total ot
                                        where o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.currency = '".$infr['code']."'
                                             and o.date_purchased >= '".$ObliczRokOd.'.'.$ObliczMiesiacOd.".01 00:00'
                                             and o.date_purchased <= '".$ObliczRokDo.'.'.$ObliczMiesiacDo.".31 23:59'";

                        $sql = $db->open_query($zapytanie);

                        $IloscZamowien = 0;
                        $WartoscZamowien = 0;

                        while ($info = $sql->fetch_assoc()) {
                            //
                            $IloscZamowien++;
                            $WartoscZamowien = $WartoscZamowien + $info['value'];
                            //
                        }                                              
                    
                        $db->close_query($sql);
                        unset($zapytanie, $info);
                        if ($IloscZamowien > 0 && $WartoscZamowien > 0) {

                        ?>
                        
                        <div class="legenda">
                            <div class="legendaLinia">ilość zamówień</div>
                            <div class="legendaSlupek">wartość zamówień</div>
                        </div>
                            
                        <div class="DuzyWykres">
                            <div id="wykres_<?php echo $infr['code']; ?>"></div>                                              
                        </div>
                        
                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykres_<?php echo $infr['code']; ?>",
                        "980", "230", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/wykres_sprzedazy_wykres.php?waluta=<?php echo $infr['code']; ?>"}, {"wmode" : "transparent"} );
                        </script>                       
                    
                        <?php 
                        }
                        
                    }    
                    $db->close_query($sqlWaluta);
                    unset($infr, $zapytanieWaluty);                    
                    ?>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}