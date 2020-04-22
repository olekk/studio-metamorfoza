<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $glowne_id = $filtr->process($_POST['id_wybrany_produkt']);
        $saPodobne = false;
        //
        // kasuje rekordy w tablicy
        $db->delete_query('products_accesories' , " pacc_products_id_master = '".$glowne_id."'");           
        //
        if (isset($_POST['id_produktow'])) {
            if (count($_POST['id_produktow']) > 0) {
                //        
                foreach ($_POST['id_produktow'] as $pole) {
                    //
                    // sprawdza czy juz nie ma takiego rekordu
                    $zapytanie = "select distinct * from products_accesories where pacc_products_id_master = '".$glowne_id."' and pacc_products_id_slave = '".$pole."'";
                    $sqls = $db->open_query($zapytanie);  
                    //
                    if ((int)$db->ile_rekordow($sqls) == 0) {        
                        //
                        $pola = array(array('pacc_products_id_master',$glowne_id),
                                      array('pacc_products_id_slave',$pole));
                        //	
                        $sql = $db->insert_query('products_accesories', $pola);
                        $saPodobne = true;
                        //
                        unset($pola);  
                        //
                    }
                    //
                    $db->close_query($sqls);
                    unset($zapytanie);                   
                }
                //
            }
        }                
        
        if ($saPodobne == false) {
            Funkcje::PrzekierowanieURL('akcesoria_dodatkowe.php');
          } else {
            Funkcje::PrzekierowanieURL('akcesoria_dodatkowe.php?id_poz='.$glowne_id);
        }         
    } 

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="akcesoria_dodatkowe/akcesoria_dodatkowe_edytuj.php" method="post" id="akcesoria_dodatkoweForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from products_accesories where pacc_products_id_master = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
 
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" id="rodzaj_modulu" value="akcesoria_dodatkowe" />
                    
                    <input type="hidden" name="id" value="<?php echo $filtr->process($_GET['id_poz']); ?>" />
                    
                    <div id="drzewo_akcesoria_dodatkowe" style="display:none"></div>     
                    <div id="wynik_produktow_akcesoria_dodatkowe" style="display:none"></div>     

                    <div id="formi">
                    
                        <div id="wybrany_produkt"></div>
                        
                        <?php
                        $do_id = '';
                        while ($info = $sql->fetch_assoc()) {
                            $do_id .= ',' . $info['pacc_products_id_slave'];
                        }
                        $do_id = $do_id . ',';
                        ?>
                        <input type="hidden" value="<?php echo $do_id; ?>" id="jakie_id" />
                        
                        <div id="wybrane_produkty"></div>
                        
                        <div id="lista_do_wyboru"></div>
                        
                        <script type="text/javascript">
                        //<![CDATA[
                        lista_akcja('<?php echo $filtr->process($_GET['id_poz']); ?>','akcesoria_dodatkowe');
                        dodaj_do_listy('','0');
                        //]]>
                        </script>                          

                    </div>                    
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('akcesoria_dodatkowe','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
                </div>

            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>