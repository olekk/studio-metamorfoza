<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('banners_title',$filtr->process($_POST['nazwa'])),
                      array('banners_group',$filtr->process($_POST['grupa'])),
                      array('languages_id',$filtr->process($_POST["jezyk"]))
         );
                      
        // jezeli banner to kod html
        if ($_POST['tryb'] == 'html') {
            $pola[] = array('banners_html_text',htmlspecialchars($_POST['text_html']));
            $pola[] = array('banners_url','');
            $pola[] = array('banners_image','');
            $pola[] = array('banners_image_text','');            
        }   

        // jezeli banner to obraz
        if ($_POST['tryb'] == 'obraz') {
            $pola[] = array('banners_url',$filtr->process($_POST['adres']));
            $pola[] = array('banners_image',$filtr->process($_POST['zdjecie']));
            $pola[] = array('banners_image_text',$filtr->process($_POST['text']));
            $pola[] = array('banners_html_text','');
        }         
        
        $db->update_query('banners' , $pola, " banners_id = '".(int)$_POST["id"]."'");	
        unset($pola);  

        if ( isset($_GET['grupa']) && ( $_GET['grupa'] != $filtr->process($_POST['grupa']) ) ) {
             $_GET['grupa'] = $filtr->process($_POST['grupa']);
        }

        Funkcje::PrzekierowanieURL('bannery_zarzadzanie.php?id_poz='.(int)$_POST["id"].Funkcje::Zwroc_Get(array('id_poz','x','y'),true));

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="wyglad/bannery_zarzadzanie_edytuj.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="ppForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from banners where banners_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

                    <!-- Skrypt do walidacji formularza -->
                    <script type="text/javascript">
                    //<![CDATA[
                    $(document).ready(function() {
                    $("#ppForm").validate({
                      rules: {
                        nazwa: {
                          required: true
                        },
                        zdjecie: {
                          required: function() {var wynik = true; if ( $("input[name='tryb']:checked", "#ppForm").val() == "html" ) { wynik = false; } return wynik; }
                        }
                      },
                      messages: {
                        nazwa: {
                          required: "Pole jest wymagane"
                        }         
                      }
                    });
                    });       

                    function zmien_tryb(id) {
                        if ($('#tryb_' + id).css('display') == 'none') {
                            $('#tryb_0').css('display','none'); 
                            $('#tryb_1').css('display','none');
                            //
                            $('#tryb_' + id).slideDown();
                        }
                    }                     
                    //]]>
                    </script>  

                    <p>
                      <label class="required">Nazwa banneru:</label>
                      <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['banners_title']; ?>" size="50" class="toolTipText" title="Nazwa banneru - tekst wyświetlany po najechaniu kursorem myszy na obrazek banneru" />
                    </p> 

                    <p>
                      <label>Grupa:</label>             
                      <?php
                      $zapytanie_tmp = "select distinct * from banners_group order by banners_group_code asc";
                      $sqls = $db->open_query($zapytanie_tmp);
                      //
                      $tablica = array();
                      while ($infs = $sqls->fetch_assoc()) { 
                        $tablica[] = array('id' => $infs['banners_group_code'], 'text' => $infs['banners_group_code'] . ' - ' . $infs['banners_group_title']);
                      }
                      $db->close_query($sqls); 
                      unset($zapytanie_tmp, $infs);                   
                      
                      echo Funkcje::RozwijaneMenu('grupa', $tablica, $info['banners_group'], 'style="width:400px"'); 
                      ?>
                    </p>
                    
                    <p>
                      <label>Dostępny dla wersji językowej:</label>
                      <?php
                      $tablica_jezykow = Funkcje::TablicaJezykow(true);                 
                      echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,$info['languages_id']);
                      ?>                  
                    </p>                    

                    <p>
                      <label>Banner będzie obrazkiem czy będzie to kod HTML ?</label>
                      <input type="radio" value="obraz" name="tryb" class="toolTipTop" onclick="zmien_tryb(0)" title="Banner będzie obrazkiem statycznym" <?php echo ((empty($info['banners_html_text'])) ? 'checked="checked"' : ''); ?> /> w formie obrazka
                      <input type="radio" value="html" name="tryb" class="toolTipTop" onclick="zmien_tryb(1)" title="Banner będzie generowany przez kod HTML" <?php echo ((empty($info['banners_html_text'])) ? '' : 'checked="checked"'); ?> /> jako kod HTML
                    </p>                  
        
                    <div id="tryb_0" <?php echo ((empty($info['banners_html_text'])) ? '' : 'style="display:none"'); ?>>
                        <p>
                          <label>Adres URL:</label>
                          <input type="text" name="adres" value="<?php echo $info['banners_url']; ?>" size="50" class="toolTipText" title="Adres strony do jakiej ma kierować banner" />
                        </p>

                        <p>
                          <label class="required">Ścieżka obrazka:</label>           
                          <input type="text" name="zdjecie" size="95" value="<?php echo $info['banners_image']; ?>" class="toolTipTop" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" autocomplete="off" />                 
                        </p>      

                        <p>
                            <label>Dodatkowy tekst:</label>
                            <textarea cols="62" rows="4" name="text" class="toolTipText" title="Tekst który może się wyświetlać na bannerze - opcja używana przy animowanym module bannerów"><?php echo $info['banners_image_text']; ?></textarea> 
                        </p> 
                    </div>
                    
                    <div id="tryb_1" <?php echo ((empty($info['banners_html_text'])) ? 'style="display:none"' : ''); ?>>
                        <p>
                            <label>Wstaw kod:</label>
                            <textarea cols="120" rows="15" name="text_html"><?php echo $info['banners_html_text']; ?></textarea>
                        </p>
                    </div>                

                    </div>             
                   
                </div>

                <div class="przyciski_dolne">
                  <?php 
                  if (count($tablica) > 0) { 
                  ?>
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <?php 
                  } 
                  unset($tablica);
                  ?>
                  <button type="button" class="przyciskNon" onclick="cofnij('bannery_zarzadzanie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','wyglad');">Powrót</button>   
                </div>            

                <?php

                unset($info);            
            
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
