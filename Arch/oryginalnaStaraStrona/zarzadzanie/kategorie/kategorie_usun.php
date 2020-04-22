<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('categories' , " categories_id = '".$filtr->process($_POST["id"])."'");  
        $db->delete_query('categories_description' , " categories_id = '".$filtr->process($_POST["id"])."'");
        
        // szuka produktow przypisanych do danej kategorii
        if ( isset($_POST['produkty']) && (int)$_POST['produkty'] == 1 ) {
            //
            $zapytanie = "select products_id from products_to_categories where categories_id = '" . $filtr->process($_POST["id"]) . "'";
            $sql = $db->open_query($zapytanie);                
            //
            while ($info = $sql->fetch_assoc()) {
                Produkty::SkasujProdukt($info['products_id']);  
            }
            //
            $db->close_query($sql);
            unset($zapytanie); 
            //
        }        
        
        // kasowanie podkategorii
        $drzewo = Kategorie::DrzewoKategorii($filtr->process($_POST["id"]), '', '', '', true, false);
        for ($i=0, $c = count($drzewo); $i < $c; $i++) {
            $db->delete_query('categories' , " categories_id = '".$drzewo[$i]['id']."'");
            $db->delete_query('categories_description' , " categories_id = '".$drzewo[$i]['id']."'");
            
            // szuka produktow przypisanych do danej kategorii
            if ( isset($_POST['produkty']) && (int)$_POST['produkty'] == 1 ) {
                //
                $zapytanie = "select products_id from products_to_categories where categories_id = '" . $drzewo[$i]['id'] . "'";
                $sqlc = $db->open_query($zapytanie);                
                //
                while ($info = $sqlc->fetch_assoc()) {
                    Produkty::SkasujProdukt($info['products_id']);  
                }
                //
                $db->close_query($sqlc);
                unset($zapytanie); 
                //
            }
            
            $db->delete_query('products_to_categories' , " categories_id = '".$drzewo[$i]['id']."'");
            
            // szuka czy kategoria nie jest uzywana w znizkach
            $zapytanie = "select discount_id, discount_categories_id from discount_categories";
            $sqld = $db->open_query($zapytanie);  
            //
            while ($info = $sqld->fetch_assoc()) {
                //
                if ( in_array( $drzewo[$i]['id'], explode(',', $info['discount_categories_id']) ) ) {
                     //
                     $nowaTablica = explode(',', $info['discount_categories_id']);
                     foreach ( $nowaTablica as $id => $wartosc ) {
                        //
                        if ( $wartosc == $drzewo[$i]['id'] || $wartosc == '' ) {
                             unset( $nowaTablica[$id] );
                        }
                        //
                     }                     
                     //
                     $pola = array( array('discount_categories_id', implode(',', $nowaTablica)) );
                     $db->update_query('discount_categories' , $pola, " discount_id = '".$info['discount_id']."'");
                     unset($pola);
                     //
                }
                //
            }
            //
            $db->close_query($sqld);
            unset($zapytanie); 
            //            
             
            unset($pola);
        }
        unset($drzewo); 
        
        // czyszczenie w rabatach dla kategorii
        // szuka czy kategoria nie jest uzywana w znizkach
        $zapytanie = "select discount_id, discount_categories_id from discount_categories";
        $sqlp = $db->open_query($zapytanie);  
        //
        while ($info = $sqlp->fetch_assoc()) {
            //
            if ( in_array( (int)$_POST["id"], explode(',', $info['discount_categories_id']) ) ) {
                 //
                 $nowaTablica = explode(',', $info['discount_categories_id']);
                 foreach ( $nowaTablica as $id => $wartosc ) {
                    //
                    if ( $wartosc == (int)$_POST["id"] || $wartosc == '' ) {
                         unset( $nowaTablica[$id] );
                    }
                    //
                 }
                 //
                 $pola = array( array('discount_categories_id', implode(',', $nowaTablica)) );
                 $db->update_query('discount_categories' , $pola, " discount_id = '".$info['discount_id']."'");
                 unset($pola);
                 //
            }
            //
        }
        //
        $db->close_query($sqlp);
        unset($zapytanie); 
        //         

        // funkcja usuwa rowniez wpis w gornym i dolnym menu i stopkach      
        Funkcje::UsuwanieWygladu('prodkategorie',$filtr->process($_POST["id"]));         
        
        $db->delete_query('products_to_categories' , " categories_id = '".$filtr->process($_POST["id"])."'");   

        Funkcje::PrzekierowanieURL('kategorie.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="kategorie/kategorie_usun.php" method="post" id="kategorieForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from categories where categories_id= '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <p>
                        Czy skasować kategorię ?
                        <?php
                        $drzewo = Kategorie::DrzewoKategorii((int)$_GET['id_poz'], '', '', '', true, false);
                        $ile_ma_podkategorii = count($drzewo);

                        if ($ile_ma_podkategorii > 0) { ?>
                        <br /><br /><span class="ostrzezenie">Kategoria zawiera <b><?php echo $ile_ma_podkategorii; ?></b> podkategorii wciąż powiązanych z tą kategorią - <span style="color:#ff0000">zostaną one również skasowane</span> !</span>
                        <?php } 
                        unset($ile_ma_podkategorii);
                        ?>

                        <?php
                        $ile_ma_produktow = 0;
                        //
                        $zapytanie = "select * from products_to_categories where categories_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
                        $sql = $db->open_query($zapytanie);
                        $ile_ma_produktow = (int)$db->ile_rekordow($sql);
                        //
                        $db->close_query($sql);
                        unset($zapytanie);                       
                        //
                        for ($i=0, $c = count($drzewo); $i < $c; $i++) {
                          //
                          $zapytanie = "select * from products_to_categories where categories_id = '" . $drzewo[$i]['id'] . "'";
                          $sql = $db->open_query($zapytanie);
                          $ile_ma_produktow += (int)$db->ile_rekordow($sql);                            
                          //
                          $db->close_query($sql);
                          unset($zapytanie);
                          //
                        }

                        if ($ile_ma_produktow > 0) { ?>
                        <br /><br /><span class="ostrzezenie">Kategoria wraz z podkategoriami zawiera <b><?php echo $ile_ma_produktow; ?></b> produktów wciąż powiązanych z tą kategorią i podkategoriami - <span style="color:#ff0000">po usunięciu kategorii dane tej kategorii zostaną usunięte z produktów</span> !</span>
                        <?php } ?>                      
                    </p>   
                    
                    <?php if ($ile_ma_produktow > 0) { ?>
                    <p>
                        <br /><input type="checkbox" name="produkty" value="1" /> usuń całkowicie z bazy produkty przypisane do danej kategorii
                    </p>
                    <?php } ?>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>
                </div>
                
                <?php
                $db->close_query($sql);
                unset($ile_ma_produktow); 
                ?>                

            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            $db->close_query($sql);
            unset($zapytanie);            
            ?>

          </div>  
          
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}