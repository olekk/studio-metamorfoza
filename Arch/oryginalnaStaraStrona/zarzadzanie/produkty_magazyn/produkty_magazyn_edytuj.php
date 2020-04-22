<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        if ($_POST["bez_cech"] == '0') {

            $pola = array(
                    array('products_quantity',$filtr->process($_POST["ilosc"])),
                    array('products_availability_id',$filtr->process($_POST["dostepnosc"])),
                    array('products_shipping_time_id',$filtr->process($_POST["wysylka"])),
                    );
            //			
            $db->update_query('products' , $pola, " products_id = '".(int)$_POST["id"]."'");	
            unset($pola);

        } else {
        
            $suma_ilosci = 0;
        
            for ($r = 1; $r < (int)$_POST["ilosc_magazynu"]; $r++) {
                //
                $Ilosc = 0;
                if (CECHY_MAGAZYN == 'tak') {
                    $Ilosc = $filtr->process($_POST["ilosc_" . $r]);
                }
                //
                $pola = array(
                        array('products_stock_quantity',$Ilosc),
                        array('products_stock_availability_id',$filtr->process($_POST["dostepnosc_" . $r])),
                        array('products_id',(int)$_POST["id"]),
                        array('products_stock_attributes',$filtr->process($_POST["id_cechy_" . $r])),
                        array('products_stock_model',$filtr->process($_POST["nr_kat_" . $r])),                   
                        );
                //	
                unset($Ilosc);
                
                // kasuje rekordy w tablicy
                $db->delete_query('products_stock' , " products_id = '".(int)$_POST["id"]."' and products_stock_attributes = '".$filtr->process($_POST["id_cechy_" . $r])."'");	            
                
                //
                if ($filtr->process($_POST["ilosc_" . $r]) != '' || $filtr->process($_POST["dostepnosc_" . $r]) > 0 || $filtr->process($_POST["nr_kat_" . $r]) != '') {
                    $db->insert_query('products_stock' , $pola);	
                }
                unset($pola);
                //      
                $suma_ilosci = $suma_ilosci + (float)$filtr->process($_POST["ilosc_" . $r]);
                //
            }
            
            //
            if (CECHY_MAGAZYN == 'nie') {
                $suma_ilosci = $filtr->process($_POST["ilosc"]);
            }
            //
            $pola = array(
                    array('products_quantity',$suma_ilosci),
                    array('products_availability_id',$filtr->process($_POST["dostepnosc"])),
                    array('products_shipping_time_id',$filtr->process($_POST["wysylka"]))
                    );
            //			
            $db->update_query('products' , $pola, " products_id = '".(int)$_POST["id"]."'");	
            unset($pola);
            //            
        
        }
        
        if ( isset($_GET['dostep']) && ( $_GET['dostep'] != $filtr->process($_POST["dostepnosc"]) ) ) {
             unset($_GET);
        }    
        if ( isset($_GET['wysylka']) && ( $_GET['wysylka'] != $filtr->process($_POST["wysylka"]) ) ) {
             unset($_GET);
        }         
        
        Funkcje::PrzekierowanieURL('produkty_magazyn.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <form action="produkty_magazyn/produkty_magazyn_edytuj.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from products where products_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                    
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <?php
                    $cechy = "select distinct * from products_attributes where products_id = '".$info['products_id']."'";
                    $sqlc = $db->open_query($cechy); 
                    //
                    if ($db->ile_rekordow($sqlc) > 0) { 
                        $zCechami = true;
                    } else {
                        $zCechami = false;
                    }
                    ?>
                    
                    <?php if ($zCechami == true) { ?>
                        
                        <input type="hidden" name="bez_cech" value="1" />

                        <?php
                        
                        // tworzenie tablic z wartosciami cech
                        $wartosciCech = array(); 
                        //
                        $cechy = "select * from products_options_values where language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                        $sqlc = $db->open_query($cechy);
                        while ($cecha = $sqlc->fetch_assoc()) {
                               $wartosciCech[ $cecha['products_options_values_id'] ] = $cecha['products_options_values_name'];
                        }
                        $db->close_query($sqlc);
                        unset($cecha, $cechy);        
                        //
                        
                        // tworzenie tablic z nazwami cech
                        $nazwyCech = array(); 
                        //
                        $cechy = "select * from products_options where language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
                        $sqlc = $db->open_query($cechy);
                        while ($cecha = $sqlc->fetch_assoc()) {
                               $nazwyCech[ $cecha['products_options_id'] ] = $cecha['products_options_name'];
                        }
                        $db->close_query($sqlc);
                        unset($cecha, $cechy);        
                        //        
                        
                        // tworzenie tablicy dostepnosci
                        $zapytanieDostepnosc = "select distinct * from products_availability p, products_availability_description pd where p.products_availability_id = pd.products_availability_id and p.mode = '0' and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' order by pd.products_availability_name";
                        $sqls = $db->open_query($zapytanieDostepnosc);
                        //
                        $tablicaDostepnosci = array( array('id' => 0, 'text' => '-- brak --'),
                                                     array('id' => 99999, 'text' => 'AUTOMATYCZNY') );
                        while ($infs = $sqls->fetch_assoc()) { 
                              $tablicaDostepnosci[] = array('id' => $infs['products_availability_id'], 'text' => $infs['products_availability_name']);
                        }
                        $db->close_query($sqls);    
                        unset($zapytanieDostepnosc, $infs);
        
        
                        // wyszukiwanie cech z tablicy
                        $cechy_zapytanie = "select distinct pa.options_id, po.products_options_name, po.products_options_type from products_attributes pa, products_options po where pa.products_id = '".$info['products_id']."' and pa.options_id = po.products_options_id and po.language_id = '".$_SESSION['domyslny_jezyk']['id']."' order by po.products_options_sort_order asc";
                        $sqlq = $db->open_query($cechy_zapytanie);                         
                        //
                        while ($cecha_pozycja = $sqlq->fetch_assoc()) { 
                            //
                            // wyszukiwanie wartosci cechy z tablicy
                            $cechy_wartosci_zapytanie = "select distinct * from products_attributes pa, products_options_values_to_products_options pv where pa.products_id = '".$info['products_id']."' and pa.options_id = '".$cecha_pozycja['options_id']."' and pa.options_values_id = pv.products_options_values_id order by pv.products_options_values_sort_order asc";
                            $sqlw = $db->open_query($cechy_wartosci_zapytanie); 
                            
                            $CiagDoWartosci = '';                            
                        
                            while ($cecha_wartosc = $sqlw->fetch_assoc()) { 
                                $CiagDoWartosci .= $cecha_wartosc['products_options_values_id'].',';
                            }
                            
                            $CiagDoWartosci = substr($CiagDoWartosci,0,-1);
                            $CiagDoTablic[$cecha_pozycja['options_id']] = explode(',', $CiagDoWartosci);
                            $pozCech = $cecha_pozycja['options_id']; 
                        }
                        
                        if (CECHY_MAGAZYN == 'tak') {
                            echo '<div class="cechy_naglowek">Stan magazynowy cech produktu / dostępność produktów / nr katalogowy</div>';
                          } else {   
                            echo '<div class="cechy_naglowek">Dostępność produktów / nr katalogowy</div>';
                        }
                        
                        echo '<div class="stany_cech">';
                        echo '<table class="tbl_cechy">';         

                        if (count($CiagDoTablic) > 1) {
                            $tab = Funkcje::Permutations($CiagDoTablic);

                            $tworzenie_naglowka = false;
                            $id_magazyn = 1;
                            //
                            foreach($tab as $tablica) {
                                $ciag = '';
                                ksort($tablica);
                                foreach($tablica as $klucz => $wartosc) {
                                    $ciag .= $klucz . '-' . $wartosc . ',';
                                }
                                $stan = substr($ciag,0,-1);
                                //
                                if ($tworzenie_naglowka == false) {
                                    echo '<tr>';
                                    //
                                    // tworzenie naglowka cech
                                    $ng_cech = explode(',',$stan);
                                    for ($r = 0, $c = count($ng_cech); $r < $c; $r++) {
                                        //
                                        // ustala nazwy cech do naglowka
                                        $do = explode('-',$ng_cech[$r]);
                                        echo '<td class="nagl">';
                                        
                                        if ( isset( $nazwyCech[$do[0]] ) ) {
                                             echo $nazwyCech[$do[0]];
                                        }
                                        
                                        echo '</td>'; 
                                        unset($do);                      
                                        //
                                    }
                                    //
                                    if (CECHY_MAGAZYN == 'tak') {
                                        echo '<td class="nagl">Ilość</td>';
                                    }
                                    echo '<td class="nagl">Dostępność</td>';
                                    echo '<td class="nagl">Nr katalogowy</td>';
                                    
                                    echo '</tr>';
                                    //
                                    $tworzenie_naglowka = true;
                                }
                                //
                                // generowanie poszczegolnych pozycji
                                echo '<tr>';
                                $ng_cech = explode(',',$stan);
                                for ($r = 0, $c = count($ng_cech); $r < $c; $r++) {
                                    // ustala wartosci cech
                                    $do = explode('-',$ng_cech[$r]);
                                    echo '<td>';
                                    
                                    if ( isset( $wartosciCech[$do[1]] ) ) {
                                         echo $wartosciCech[$do[1]];
                                    }
                                    
                                    echo '</td>';
                                    unset($do);                  
                                    //
                                }
                                //

                                // szuka w bazie ilosci magayznu
                                $cec = "select distinct * from products_stock where products_id = '".$info['products_id'] ."' and products_stock_attributes = '".$stan."'";
                                $sqlw = $db->open_query($cec);    
                                $ilosc_cechy = $sqlw->fetch_assoc(); 
                                $db->close_query($sqlw);                    
                                //
                                
                                if (CECHY_MAGAZYN == 'tak') {    
                                    echo '<td>
                                              <input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$stan.'" />
                                              <input type="text" class="kropka" name="ilosc_'.$id_magazyn.'" size="8" value="'.(($ilosc_cechy['products_stock_quantity'] == 0) ? '' : $ilosc_cechy['products_stock_quantity']).'" />
                                          </td>';
                                }
                                          
                                echo '<td>';
                                
                                      if (CECHY_MAGAZYN == 'nie') {    
                                          echo '<input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$stan.'" />';
                                      }                                
                                
                                      //                          
                                      echo Funkcje::RozwijaneMenu('dostepnosc_'.$id_magazyn, $tablicaDostepnosci, $ilosc_cechy['products_stock_availability_id']);         
                                      //
                                echo '</td>';
                                
                                echo '<td>
                                          <input type="text" name="nr_kat_'.$id_magazyn.'" size="40" value="'.$ilosc_cechy['products_stock_model'].'" />
                                      </td>';                                
                                
                                echo '</tr>';
                                
                                $id_magazyn++;
                                unset($cec, $ilosc_cechy);                     
                                //            
                            }

                          } else { 
                          
                            echo '<tr>';
                            // ustala nazwy cech do naglowka
                            echo '<td class="nagl">';
                            
                            if ( isset( $nazwyCech[$pozCech] ) ) {
                                 echo $nazwyCech[$pozCech];
                            }
                            
                            echo '</td>'; 
                            
                            //
                            if (CECHY_MAGAZYN == 'tak') {
                                echo '<td class="nagl">Ilość</td>';
                            }
                            echo '<td class="nagl">Dostępność</td>';
                            echo '<td class="nagl">Nr katalogowy</td>';

                            echo '</tr>';
                      
                            $WarCechPojedyncze = explode(',',$CiagDoWartosci);
                            $id_magazyn = 1;
                            
                            for ($a = 0, $ca = count($WarCechPojedyncze); $a < $ca; $a++) {
                                // ustala wartosci cech
                                echo '<tr><td>';
                                
                                if ( isset( $wartosciCech[$WarCechPojedyncze[$a]] ) ) {
                                     echo $wartosciCech[$WarCechPojedyncze[$a]];
                                }
                                
                                echo '</td>'; 
                                //
                                // szuka w bazie ilosci magayznu
                                $cec = "select distinct * from products_stock where products_id = '".$info['products_id'] ."' and products_stock_attributes = '".$pozCech.'-'.$WarCechPojedyncze[$a]."'";
                                $sqlw = $db->open_query($cec);    
                                $ilosc_cechy = $sqlw->fetch_assoc(); 
                                $db->close_query($sqlw);                    
                                //    

                                if (CECHY_MAGAZYN == 'tak') {                        
                                    echo '<td>
                                              <input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$pozCech.'-'.$WarCechPojedyncze[$a].'" />
                                              <input type="text" class="kropka" name="ilosc_'.$id_magazyn.'" size="8" value="'.(($ilosc_cechy['products_stock_quantity'] == 0) ? '' : $ilosc_cechy['products_stock_quantity']).'" />
                                          </td>'; 
                                }
                                
                                echo '<td>';
                                
                                      if (CECHY_MAGAZYN == 'nie') {    
                                          echo '<input type="hidden" name="id_cechy_'.$id_magazyn.'" value="'.$pozCech.'-'.$WarCechPojedyncze[$a].'" />';
                                      }                                     
                                
                                      //                        
                                      echo Funkcje::RozwijaneMenu('dostepnosc_'.$id_magazyn, $tablicaDostepnosci, $ilosc_cechy['products_stock_availability_id']);         
                                      //
                                echo '</td>';
                                
                                echo '<td>
                                          <input type="text" name="nr_kat_'.$id_magazyn.'" size="40" value="'.$ilosc_cechy['products_stock_model'].'" />
                                      </td>';                                    
                                
                                echo '</tr>';
                                
                                $id_magazyn++;          
                                unset($cec, $ilosc_cechy);                     
                                ///
                            }
                          
                        }

                        echo '</table>';   

                        echo '<input type="hidden" name="ilosc_magazynu" value="'.$id_magazyn.'" />';
                        
                        echo '</div>';
                        
                        unset($tablicaDostepnosci, $nazwyCech, $wartosciCech);
                        
                        ?>
                        
                    <?php } ?>                    
                    
                    <?php if ($zCechami == false || CECHY_MAGAZYN == 'nie') { ?>
                        
                        <?php if ($zCechami == false) { ?>
                        <input type="hidden" name="bez_cech" value="0" />
                        <?php } ?>
                        
                        <p>
                          <label>Ilość w magazynie:</label>
                          <input type="text" name="ilosc" size="5" class="kropka" value="<?php echo ((Funkcje::czyNiePuste($info['products_quantity'])) ? $info['products_quantity'] : ''); ?>" />
                        </p>
                        
                    <?php } ?>
                    
                    <p>
                      <label>Stan dostępności:</label>                                       
                      <?php echo Funkcje::RozwijaneMenu('dostepnosc', Produkty::TablicaDostepnosci('-- brak --'), $info['products_availability_id']); ?>
                    </p>     

                    <p>
                      <label>Wysyłka:</label>                                        
                      <?php echo Funkcje::RozwijaneMenu('wysylka', Produkty::TablicaCzasWysylki('-- brak --'), $info['products_shipping_time_id']); ?>
                    </p>
                    
                    </div>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('produkty_magazyn','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','produkty_magazyn');">Powrót</button>           
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

}