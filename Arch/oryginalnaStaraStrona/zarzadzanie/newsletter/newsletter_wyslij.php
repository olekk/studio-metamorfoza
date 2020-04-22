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
    
    <div id="naglowek_cont">Wysyłanie newslettera</div>
    <div id="cont">
          
          <form action="newsletter/newsletter_wyslij.php" method="post" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Wysyłanie newslettera</div>
            
            <?php
            $zapytanie = "select * from newsletters where newsletters_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                if ( !isset($_GET['test']) ) {
                    //
                    $pola = array(array('date_sent','now()'));
                    $db->update_query('newsletters' , $pola, " newsletters_id = '" . $filtr->process((int)$_GET['id_poz']) . "'");
                    unset($pola);            
                    //
                }
                
                $info = $sql->fetch_assoc();
                ?>            
                
                <script type="text/javascript">
                //<![CDATA[
                var ogolny_limit = <?php echo ((isset($_GET['test'])) ? 1 : count(Newsletter::AdresyEmailNewslettera($info['newsletters_id']))); ?>;
                //
                function wyslij_newsletter(id, limit) {

                    if ($('#import').css('display') == 'none') {
                        $('#import').slideDown("fast");
                        $('#przyciski').slideUp("fast");
                    }
                    
                    $.post( "ajax/wyslij_newsletter.php?tok=<?php echo Sesje::Token(); ?>", 
                          { 
                            id: <?php echo $info['newsletters_id']; ?>,
                            <?php echo ((isset($_GET['test'])) ? 'test: 0,' : ''); ?>
                            limit: limit
                          },
                          function(data) {
                          
                             if ( data != '' ) {
                                  $('#blad').html(data);
                             }
                          
                             procent = parseInt(((limit + 1) / ogolny_limit) * 100);
                             $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Wysłano maili: <span>' + (limit + 1) + '</span>');
                             
                             $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                             
                             if (ogolny_limit-1 > limit) {
                                wyslij_newsletter(<?php echo $info['newsletters_id']; ?>, limit + 1);
                               } else {

                                if ( $('#blad').html() != '' ) {
                                     
                                     $('#p_wyslij').css('display','none');
                                     $('#postep').css('display','none');
                                     $('#suwak').slideUp("fast");
                                     $('#procent').slideUp("fast");
                                     $('#blad').slideDown("fast");
                                     $('#przyciski').slideDown("fast");
                                     
                                   } else { 
                                   
                                    $('#p_wyslij').css('display','none');
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#wynik_dzialania').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                    
                                }
                             }                                

                          }                          
                    );
                    
                }; 
                //]]>
                </script>                
            
                <div class="pozycja_edytowana">
                
                    <div id="dane_newslettera">
                    
                        <div>
                            Tytuł newslettera: <span><?php echo $info['title']; ?></span>
                        </div>
                        
                        <?php
                        if ( !isset($_GET['test']) ) {
                        ?>
                        
                        <div>
                            Odbiorcy newslettera: 
                            <?php
                            switch ($info['destination']) {
                                case "1":
                                    $doKogo = 'Do wszystkich zarejestrowanych klientów sklepu';
                                    break; 
                                case "2":
                                    $doKogo = 'Tylko zarejestrowani klienci którzy wyrazili zgodnę na newsletter';
                                    break;                          
                                case "3":
                                    $doKogo = 'Tylko klienci którzy zapisali się do newslettera, a nie są klientami sklepu';
                                    break;
                                case "4":
                                    $doKogo = 'Do wszystkich którzy zapisali się do newslettera';
                                    break;                        
                                case "5":
                                    $doKogo = 'Mailing';
                                    break;     
                                case "6":
                                    $doKogo = 'Tylko do określonej grupy klientów';
                                    break;                        
                            }                         
                            
                            ?>
                            <span><?php echo $doKogo; ?></span>
                        </div>
                        
                        <div>
                            Ilość maili do wysłania: <span><?php echo count(Newsletter::AdresyEmailNewslettera($info['newsletters_id'])); ?></span>
                        </div>
                        
                        <?php } else { ?>
                        
                        <div>
                            Odbiorcy newslettera: <span>TRYB TESTOWY: Wiadomość zostanie wysłana na adres właściciela sklepu ...</span>
                        </div>                        
                        
                        <?php } ?>
                        
                    </div>
                
                    <div id="import" style="display:none">
                    
                        <div id="postep">Postęp wysyłania ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent">Stopień realizacji: <span>0%</span><br />Wysłano maili: <span>0</span></div> 

                        <div id="blad" style="display:none"></div>

                        <div class="cl"></div>
                    
                    </div>   
                    
                    <div id="wynik_dzialania" style="display:none">
                        Newsletter został wysłany do klientów ...
                    </div>
                    
                </div>
                
                <div class="przyciski_dolne" id="przyciski">
                  <button type="button" id="p_wyslij" class="przyciskNon" onclick="wyslij_newsletter(<?php echo $info['newsletters_id']; ?>,0)">Wyślij newsletter</button> 
                  <button type="button" class="przyciskNon" onclick="cofnij('newsletter','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button> 
                </div>

            <?php
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            
            $db->close_query($sql);
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}