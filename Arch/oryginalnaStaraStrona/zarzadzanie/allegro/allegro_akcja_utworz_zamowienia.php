<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['akcja_dolna']) && isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>
        
        <div id="naglowek_cont">Tworzenie zamówień na podstawie sprzedaży Allegro</div>
        
        <div id="cont">
              
            <form action="allegro/allegro_akcja_utworz_zamowienia.php" method="post" class="cmxform">          

            <div class="poleForm">
            
              <div class="naglowek">Tworzenie nowych zamówień na podstawie sprzedaży Allegro</div>
              
              <div class="pozycja_edytowana">

                  <?php if ( $_POST['akcja_dolna'] != 'zamowienia' ) { ?>
              
                      <input type="hidden" name="akcja" value="zamowienia" />  
                      <input type="hidden" name="akcja_dolna" value="zamowienia" />  
                  
                      <p>
                        Czy utworzyć nowe zamówienia dla niżej wymienionych aukcji ?
                      </p> 

                      <p class="listaAukcji">
                        <?php
                        foreach ($_POST['opcja'] as $akcjaSerial) {
                            //
                            $aukcja = unserialize($akcjaSerial);
                            //
                            $link = '';
                            if ( Allegro::SerwerAllegro() == 'nie' ) {
                              $link = 'http://allegro.pl/item' .  $aukcja['aukcja_id'] . '_webapi.html';
                            } else {
                              $link = 'http://allegro.pl.webapisandbox.pl/show_item.php?item='.$aukcja['aukcja_id'];
                            }   
                            //
                            // nazwa produktu
                            $zapytanie = "SELECT products_name FROM allegro_auctions WHERE auction_id = '" . $aukcja['aukcja_id'] . "'";
                            $sql = $db->open_query($zapytanie);
                            //
                            $info = $sql->fetch_assoc();
                            //
                            echo '<input type="hidden" name="opcja[]" value="' . $aukcja['id_poz'] . '_' . $aukcja['postform'] . '_' . $aukcja['transaction_id'] . '" />';
                            //
                            echo '<a href="' . $link . '">' . $aukcja['aukcja_id'] . '</a> - ' . $info['products_name'] . '<br />';
                            //
                            $db->close_query($sql);
                            unset($zapytanie, $info);                                 
                            //
                        }                     
                        ?>
                      </p> 
                      
                  <?php } else { ?>
                  
                      <?php                          
                      $komunikaty = '';
                      
                      if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {
                          ?>
                          
                          <div id="import">
                                
                            <div id="postep">Postęp procesu ...</div>
                                
                            <div id="suwak">
                              <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                              </div>
                            </div>
                                    
                            <div id="procent"></div>  
                            
                            <div id="wynik" class="listaAukcji" style="margin-top:10px;"></div>
                            
                          </div>   
                                
                          <div id="zaimportowano" style="display:none">
                            Dane zostały przetworzone
                          </div>                          
                          
                          <script type="text/javascript">
                          //<![CDATA[
                          
                          <?php
                          $tab_tmp = '';
                          foreach ( $_POST['opcja'] as $klucz => $id_aukcji_allegro ) {
                          
                              $tab_tmp .= "'" . $id_aukcji_allegro . "',";
                              
                          }     
                          $tab_tmp = substr($tab_tmp, 0, -1);
                          ?>   
                          
                          var tablicaId = new Array(<?php echo $tab_tmp; ?>);
                          
                          function allegro_zamowienie(nr) {

                            $.post( "allegro/allegro_utworz_zamowienie.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                tbl_danych : tablicaId[nr],
                                ajax : 'tak'
                              },
                              function(data) {

                                if (tablicaId.length - 1 <= 1) {
                                  procent = 100;
                                } else {
                                  procent = parseInt( ((nr + 1) / tablicaId.length) * 100 );
                                  if (procent > 100) {
                                    procent = 100;
                                  }
                                }
                                
                                if ( nr == (tablicaId.length - 1) ) {
                                    procent = 100;
                                }

                                $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + (nr + 1) + '</span>');    
                                
                                $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                                if (nr < tablicaId.length - 1) {
                                  allegro_zamowienie(nr + 1);
                                } else {
                                  $('#postep').css('display','none');
                                  $('#suwak').slideUp("fast");
                                  $('#wgrany_produkt').slideUp("fast");
                                  $('#zaimportowano').slideDown("fast");
                                  $('#przyciski').slideDown("fast");
                                }   
                                
                                if (data != '') {
                                  $('#wgrany_produkt').html(data);
                                  $('#licz_produkty').html(nr + 1);
                                  $('#wynik').html( $('#wynik').html() + data );
                                }
                                  
                              }
                            );
                            
                          };    

                          allegro_zamowienie(0);

                          //]]>
                          </script> 
                          
                          <?php
                          unset($tab_tmp);
                          
                      } else {
                      
                          $komunikaty = Okienka::pokazOkno('Błąd', 'Nie jesteś zalogowany w serwisie Allegro', 'allegro/allegro_logowanie.php');
                      
                      }
                      ?>

                      <p class="listaAukcji">
                        <?php echo $komunikaty; ?>
                      </p>
                      
                      <?php
                      unset($komunikaty);
                      ?>                      
                  
                  <?php } ?>
                  
              </div>

              <div class="przyciski_dolne" id="przyciski" <?php echo (( $_POST['akcja_dolna'] == 'zamowienia' )  ? 'style="display:none"' : ''); ?>>
                
                <?php if ( $_POST['akcja_dolna'] != 'zamowienia' ) { ?>
                    <input type="submit" class="przyciskNon" value="Utwórz zamówienia" />
                <?php } ?>
                
                <button type="button" class="przyciskNon" onclick="cofnij('allegro_sprzedaz','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                
              </div>

            </div>

            </form>

        </div>    
        
        <?php
        include('stopka.inc.php');
            
    }
    
}

?>