<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $nazwa_plik = $_FILES['file']['tmp_name']; 
        $dane = file($nazwa_plik);
        
        //
        $separator = $filtr->process($_POST['sep']);
        //
        
        // linia pierwsza
        $linia = explode($separator,$dane[0]); 
        
        $jezyki = Funkcje::TablicaJezykow();
        
        $kolumny = array();
        
        for ( $i = 1, $c = count($linia); $i < $c; $i++) { 
              //
              foreach ( $jezyki as $wartosc ) {
                    //
                    $linia[$i] = mb_strtolower($linia[$i], 'UTF-8');
                    $linia[$i] = str_replace(' ', '', $linia[$i]);
                    $linia[$i] = str_replace('jezyk', '', $linia[$i]);

                    if ( $linia[$i] == mb_strtolower($wartosc['kod'], 'UTF-8') ) {
                        $kolumny[$i] = $wartosc['id'];
                    }
                    //
              }
              //
        }
        
        unset($jezyki, $linia);
       
        for ($i = 1, $c = count($dane); $i < $c; $i++) { 
        
            $linia = explode($separator,$dane[$i]);     
            //
            // trzeba sprawdzic czy kod jest w bazie
            $zapytanie = "select translate_constant_id, translate_constant from translate_constant where translate_constant = '" . strtoupper($filtr->process($linia[0])) . "'";
            $sql = $db->open_query($zapytanie);
                
            if ((int)$db->ile_rekordow($sql) > 0) {
                //
                $info = $sql->fetch_assoc();
                //
                // dla kazdego jezyka
                foreach ( $kolumny as $klucz => $wartosc ) {
                    //
                    if ( !empty($linia[$klucz]) && isset($linia[$klucz]) ) {
                        //
                        $pola = array();
                        //
                        $pola[] = array('translate_value', $filtr->process($linia[$klucz]));   
     
                        $db->update_query('translate_value', $pola, 'translate_constant_id = "' . $info['translate_constant_id'] . '" and language_id = "' . $wartosc . '"');
                        
                        unset($pola);
                        //
                    }
                    //
                }
                
                unset($info);
 
            }
            
            $db->close_query($sql);
                           
        }       

        unset($kolumny);

        Funkcje::PrzekierowanieURL('tlumaczenia.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import danych tłumaczeń</div>
    <div id="cont">
    
          <form action="slowniki/tlumaczenia_import.php" method="post" id="tlumaczeniaForm" class="cmxform" enctype="multipart/form-data">   

          <script type="text/javascript">
          //<![CDATA[
          $(function(){
             $('#upload').MultiFile({
              max: 1,
              accept:'txt|csv',
              STRING: {
               denied:'Nie można przesłać pliku w tym formacie $ext!',
               selected:'Wybrany plik: $file',
              }
             }); 
          });
          //]]>
          </script>          

          <div class="poleForm">
            <div class="naglowek">Import danych tłumaczeń</div>
            
            <div class="pozycja_edytowana">
                
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <div class="ostrzezenie" style="margin:8px">
                    Zalecane jest wykonanie kopii bazy danych przed dokonaniem importu tłumaczeń.
                </div>   

                <span class="maleInfo" style="margin-left:8px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>                 

                <p style="padding:12px;">
                    <label>Separator pól:</label>
                    <input type="radio" name="sep" value=";" checked="checked" /> ; (średnik) &nbsp;
                    <input type="radio" name="sep" value=":" /> : (dwukropek) &nbsp;
                    <input type="radio" name="sep" value="," /> , (przecinek) &nbsp;
                    <input type="radio" name="sep" value="#" /> # (płotek)
                </p>
                
                <p style="padding:12px;">
                  <label>Plik do importu:</label>
                  <input type="file" name="file" id="upload" size="53" />
                </p>

                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Importuj dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('tlumaczenia','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>           
            </div>                 


          </div>                      
          </form>

    </div>    

    <?php
    include('stopka.inc.php');

}
?>