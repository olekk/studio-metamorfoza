<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //			
        $db->delete_query('manufacturers' , " manufacturers_id = '".$filtr->process($_POST["id"])."'");  
        $db->delete_query('manufacturers_info' , " manufacturers_id = '".$filtr->process($_POST["id"])."'");
        //
        
        // szuka produktow przypisanych do danego producenta
        if ( isset($_POST['produkty']) && (int)$_POST['produkty'] == 1 ) {
            //
            $zapytanie = "select products_id from products where manufacturers_id = '" . $filtr->process($_POST["id"]) . "'";
            $sql = $db->open_query($zapytanie);                
            //
            while ($info = $sql->fetch_assoc()) {
                Produkty::SkasujProdukt($info['products_id']);  
            }
            //
            $db->close_query($sql);
            unset($zapytanie); 
            //
        } else {        
            // czyszczenie id w produktach
            $pola = array(
                    array('manufacturers_id',''));
            
            $sql = $db->update_query('products' , $pola, " manufacturers_id = '".$filtr->process($_POST["id"])."'");
            //
        }
        
        // czyszczenie w rabatach dla producentow
        // szuka czy kategoria nie jest uzywana w znizkach
        $zapytanie = "select discount_id, discount_manufacturers_id from discount_manufacturers";
        $sqld = $db->open_query($zapytanie);  
        //
        while ($info = $sqld->fetch_assoc()) {
            //
            if ( in_array( (int)$_POST["id"], explode(',', $info['discount_manufacturers_id']) ) ) {
                 //
                 $nowaTablica = explode(',', $info['discount_manufacturers_id']);
                 foreach ( $nowaTablica as $id => $wartosc ) {
                    //
                    if ( $wartosc == (int)$_POST["id"] || $wartosc == '' ) {
                         unset( $nowaTablica[$id] );
                    }
                    //
                 }                     
                 //
                 $pola = array( array('discount_manufacturers_id', implode(',', $nowaTablica)) );
                 $db->update_query('discount_manufacturers' , $pola, " discount_id = '".$info['discount_id']."'");
                 unset($pola);
                 //
            }
            //
        }
        //
        $db->close_query($sqld);
        unset($zapytanie); 
        //      

        Funkcje::PrzekierowanieURL('producenci.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Kasowanie pozycji</div>
    <div id="cont">
          
          <form action="producenci/producenci_usun.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Usuwanie danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from manufacturers where manufacturers_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    
                    <p>
                      Czy skasować producenta ?                    
                    </p>   
                    
                    <?php
                    $zapytanie = "select products_id from products where manufacturers_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
                    $sql = $db->open_query($zapytanie);
                    $ile_ma_produktow = (int)$db->ile_rekordow($sql);
                    //
                    $db->close_query($sql);
                    unset($zapytanie);                       
                    //
                    ?>
                    
                    <?php
                    if ( $ile_ma_produktow > 0 ) {
                    ?>
                    
                    <p>
                        <br /><span class="ostrzezenie">Producent zawiera <b><?php echo $ile_ma_produktow; ?></b> produktów wciąż powiązanych z tym producentem - <span style="color:#ff0000">po usunięciu producenta dane tego producenta zostaną usunięte z produktów</span> !</span>
                    </p>
                    
                    <p>
                        <br /><input type="checkbox" name="produkty" value="1" /> usuń całkowicie z bazy produkty przypisane do danego producenta
                    </p>   
                    
                    <?php
                    }
                    ?>
                 
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Usuń dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('producenci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
                </div>

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