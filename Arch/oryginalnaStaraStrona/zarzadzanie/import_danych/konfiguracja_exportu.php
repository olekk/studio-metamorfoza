<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $wynik = '';

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array( array('status',0) );
        $db->update_query('export_configuration', $pola, "code != ''");
        unset($pola);
        
        foreach ($_POST as $klucz => $wartosc) {
            //
            $pola = array( array('status',1) );
            $db->update_query('export_configuration', $pola, "code = '" . $klucz . "'");
            unset($pola);             
            //
        }
        
        $wynik = '<div id="zapisano" class="maleSukces" style="margin-left:20px;margin-top:10px;">dane zostały zmienione</div>';

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Konfiguracja eksportu produktów w formacie CSV oraz XML</div>
    <div id="cont">

      <div class="poleForm">
      
        <div class="naglowek">Edycja danych</div>

        <div class="pozycja_edytowana" style="overflow:hidden;"> 

            <script type="text/javascript">
            //<![CDATA[
            $(document).ready(function() {
              setTimeout(function() {
                $('#zapisano').fadeOut();
              }, 3000);
            });
            //]]>
            </script>         
        
            <div class="maleInfo">Zaznacz pozycje które mają być eksportowane dla plików CSV oraz XML</div>
            
            <div class="export">
            
                <form action="import_danych/konfiguracja_exportu.php" method="post" class="cmxform">
                
                <input type="hidden" value="zapisz" name="akcja" />
                
                <div class="lf">
                
                <?php
                $zapytanie = "select distinct * from export_configuration";
                $sql = $db->open_query($zapytanie);
                
                $suma_pozycji = (int)$db->ile_rekordow($sql) / 2;
                $licznik = 0;
                
                while ( $info = $sql->fetch_assoc() ) {
                
                    //
                    echo '<input type="checkbox" name="' . $info['code'] . '" value="1" ' . (($info['status'] == 1) ? 'checked="checked"' : '') . '/>' . $info['description'] . '<br />';
                    //

                    if ( $licznik == (int)$suma_pozycji ) {
                         echo '</div><div class="lf">';
                    }
                    
                    $licznik ++;                
                
                }
                
                $db->close_query($sql);
                unset($info);            
                ?>
                
                </div>
                
                <div class="cl"></div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" /><?php echo $wynik; ?>
                </div>          

                </form>
           
            </div> 
        
        </div>
        
      </div>
      
    </div>
                    
    <?php include('stopka.inc.php'); ?>

<?php } ?>
