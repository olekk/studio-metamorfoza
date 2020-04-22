<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_GET['akcja']) && $_GET['akcja'] == 'usun') {
        //			
        if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
            //
            $id = $filtr->process((int)$_GET['id']);
            $db->delete_query('customers_searches' , " search_id = '".$id."'");
            unset($pola);  
            //
        }    
        //
        Funkcje::PrzekierowanieURL('wyszukiwane_frazy.php' . Funkcje::Zwroc_Get(array('akcja','zakres','id','x','y')));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Raporty</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Wyszukiwane frazy</div>

                <div class="pozycja_edytowana">  

                    <span class="maleInfo">Raport prezentuje 100 najczęściej wyszukiwanych fraz w sklepie</span>
                    
                    <div id="wyborJezyka">
                        <table>
                            <tr>
                                <td><span>Pokaż statystyki dla języka:</span></td>
                                <?php
                                $jezyki = "SELECT * FROM languages WHERE status = '1' ORDER BY sort_order";                        
                                $sql = $db->open_query($jezyki);
                                while ($Lang = $sql->fetch_assoc()) {
                                    //
                                    $klasaCSS = 'class="nieaktywny"';
                                    if ((isset($_GET['jezyk']) && (int)$_GET['jezyk'] == $Lang['languages_id']) || (!isset($_GET['jezyk']) && $Lang['languages_id'] == '1')) {
                                        $klasaCSS = 'class="aktywny"';
                                    }
                                    //
                                    echo '<td><a '.$klasaCSS.' href="statystyki/wyszukiwane_frazy.php?jezyk='.$Lang['languages_id'].'"><img src="../' . KATALOG_ZDJEC . '/'.$Lang['image'].'" alt="'.$Lang['name'].'" title="'.$Lang['name'].'" /></a></td>';
                                }
                                $db->close_query($sql);
                                unset($jezyki, $sql);                        
                                ?>
                            </tr>
                        </table>
                    </div>
                    
                    <?php
                    // get jezyka
                    $IdJezyka = 1; // domyslnie jezyk id 1
                    if (isset($_GET['jezyk']) && (int)$_GET['jezyk']) {
                        $IdJezyka = (int)$_GET['jezyk'];
                    }
                    //
                    $zapytanie = "select * from customers_searches where language_id = '".$IdJezyka."' order by freq desc limit 100";
                    $sql = $db->open_query($zapytanie);
                    
                    if ((int)$db->ile_rekordow($sql) > 0) {
                        ?>
                        
                        <div class="NadWykres">
                            <div id="wykres"></div>                                              
                        </div>
                        
                        <br />
                        
                        <table class="tblStatystyki">

                        <tr class="TyNaglowek">
                            <td>Lp</td>
                            <td>Fraza</td>
                            <td>Ilość wyszukań</td>
                            <td>&nbsp;</td>
                        </tr>                  
                        
                        <?php
                        $poKolei = 1;
                        while ($info = $sql->fetch_assoc()) {
                        
                            echo '<tr>';
                            
                            echo '<td class="poKolei">' . $poKolei . '</td>'; 
                            
                            echo '<td class="linkProd">' . $info['search_key'] . '</td>';
                            
                            if ($info['freq'] == 1) {
                                $TrescZam = ' <span>raz</span>';
                            }
                            if ($info['freq'] > 1) {
                                $TrescZam = ' <span>razy</span>';
                            }                            
                            echo '<td class="wynikStat">' . $info['freq'] . $TrescZam . '</td>';
                            echo '<td class="wyczyscStat"><a href="statystyki/wyszukiwane_frazy.php?akcja=usun&id='.$info['search_id'].'"><img class="toolTipTopText" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a></td>';
                            echo '</tr>';
                            
                            $poKolei++;
                        
                        }            
                        $db->close_query($sql);
                        unset($poKolei);
                        ?>
                        
                        </table>
                        
                        <script type="text/javascript" src="javascript/swfobject.js"></script>
                        <script type="text/javascript">
                        swfobject.embedSWF(
                        "programy/openChart/open-flash-chart.swf", "wykres",
                        "970", "350", "9.0.0", "expressInstall.swf",
                        {"data-file":"statystyki/wyszukiwane_frazy_wykres.php?jezyk=<?php echo $IdJezyka; ?>"}, {"wmode" : "transparent"} );
                        </script>                          

                        <?php
                    } else {
                        //
                        echo '<div style="margin:10px">Brak statystyk ...</div>';
                        //
                    }
                    ?>
                    
                     

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}