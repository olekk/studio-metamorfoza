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
    
    <div id="naglowek_cont">Lista operacji programu partnerskiego klienta</div>
    <div id="cont">    
    
        <div class="poleForm">
            <div class="naglowek">Lista operacji</div>   
            
                <?php if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) { ?>

                <div id="pozycje_ikon" style="margin:10px">
                    <div>
                        <a class="dodaj" href="program_partnerski/partnerzy_operacje_dodaj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>">dodaj nową pozycję</a>
                    </div>         
                </div>

                <div class="cl"></div>

                <div style="margin:10px; margin-top:0px;">

                <?php
                $zapytanie_punkty = "SELECT * FROM customers_points WHERE customers_id = '" . (int)$_GET['id_poz'] . "' and (points_type = 'PP' OR points_type = 'PM') order by date_added desc";
                $sql_punkty = $db->open_query($zapytanie_punkty);

                if ((int)$db->ile_rekordow($sql_punkty) > 0) {
                    //
                    ?>
                    <div class="obramowanie_tabeli">
                        <table class="listing_tbl" id="pktlista">
                        <tr class="div_naglowek">
                          <td style="text-align:left">Tytuł punktów</td>
                          <td>Status</td>
                          <td>Data dodania</td>
                          <td>Data zatwierdzenia <br /> anulowania</td>
                          <td>Punkty</td>
                          <td>&nbsp;</td>
                        </tr>                                
                        <?php
                        //
                        /*
                        typy punktow
                        1 - oczekujace
                        2 - zatwierdzone
                        3 - anulowane
                        4 - wykorzystane
                        */
                        //
                        while ($pkt = $sql_punkty->fetch_assoc()) {
                            //
                            echo '<tr class="pozycja_off">';
                            echo '<td style="text-align:left" class="listPkt">';
                            
                            switch ($pkt['points_type']) {
                                case "RV":
                                    //
                                    $zapytanie_recenzja = "SELECT r.reviews_id, pd.products_name FROM reviews r, products_description pd WHERE r.products_id = pd.products_id and reviews_id = '" . $pkt['reviews_id'] . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                                    $sql_recenzja = $db->open_query($zapytanie_recenzja);
                                    $infr = $sql_recenzja->fetch_assoc();
                                    //
                                    if ((int)$db->ile_rekordow($sql_recenzja) > 0) {
                                        echo '<a href="recenzje/recenzje_edytuj.php?id_poz=' . $pkt['reviews_id'] . '">';
                                    }
                                    //
                                    echo 'Punkty za recenzję produktu ' . '<strong>' . $infr['products_name'] . '</strong>';
                                    //
                                    if ((int)$db->ile_rekordow($sql_recenzja) > 0) {
                                        echo '</a>';
                                    }
                                    //
                                    $db->close_query($sql_recenzja);
                                    unset($infr);
                                    //
                                    break;
                                case "SP":
                                    echo '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $pkt['orders_id'] . '">Punkty za zamówienie nr <strong>' . $pkt['orders_id'] . '</strong></a>';
                                    break; 
                                case "PP":
                                    echo '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $pkt['orders_id'] . '">Program partnerski - punkty za zamówienie nr <strong>' . $pkt['orders_id'] . '</strong></a>';
                                    break;                        
                                case "SC":
                                    echo '<a href="sprzedaz/zamowienia_szczegoly.php?id_poz=' . $pkt['orders_id'] . '">Punkty wykorzystane w zamówieniu nr <strong>' . $pkt['orders_id'] . '</strong></a>';
                                    break;                         
                                case "RJ":
                                    echo 'Punkty za rejestrację';
                                    break;                         
                                default:
                                    echo $pkt['points_comment'];
                                    break;                 
                            }                                          
                            
                            echo '</td>';
                            echo '<td>' . Klienci::pokazNazweStatusuPunktow($pkt['points_status']) . '</td>';
                            echo '<td>' . date('d-m-Y H:i',strtotime($pkt['date_added'])) . '</td>';
                            echo '<td>' . ((Funkcje::czyNiePuste($pkt['date_confirm'])) ? date('d-m-Y H:i',strtotime($pkt['date_confirm'])) : '-') . '</td>';
                            echo '<td>' . $pkt['points'] . '</td>';
                                                                    
                            $zmienne_do_przekazania = '?id_poz='.(int)$_GET['id_poz'] . '&id=' . $pkt['unique_id']; 
                            echo '<td class="rg_right"><a href="program_partnerski/partnerzy_operacje_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Usuń tę pozycję" title="Usuń tę pozycję" /></a>';
                            
                            if ( (int)$pkt['points_status'] != 4 ) {
                                //
                                echo '<a href="program_partnerski/partnerzy_operacje_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj ilość punktów" title="Edytuj ilość punktów" /></a>
                                      <a href="program_partnerski/partnerzy_operacje_status.php'.$zmienne_do_przekazania.'"><img src="obrazki/zatwierdz.png" alt="Zmień status" title="Zmień status" /></a>';
                                //
                            }
                            
                            echo '</td>';
                            echo '</tr>';
                            //
                        }
                        ?>
                        </table>

                    </div>
                    
                    <script type="text/javascript">
                    //<![CDATA[
                    $(document).ready(function(){
                        pokazChmurki();     
                    });
                    //]]>
                    </script>                    
                    <?php
                } 
                ?>
                
                </div>

                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('partnerzy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>    
                </div>
                
                <?php 
                } else { 
    
                 echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
                
                } ?>
                            
        </div>
    
    </div>
    
    <?php
    include('stopka.inc.php');   
}