<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( isset($_POST['ajax']) ) {
    
        if ( isset($_POST['id_aukcji']) && (int)$_POST['id_aukcji'] > 0 ) {
        
            $allegro = new Allegro(true, true);

            $db->delete_query('allegro_auctions' , " auction_id = '".floatval($_POST['id_aukcji'])."'");  
            $db->delete_query('allegro_auctions_sold' , " auction_id = '".floatval($_POST['id_aukcji'])."'");  
            $db->delete_query('allegro_transactions' , " auction_id = '".floatval($_POST['id_aukcji'])."'");  
            
            $wynik = $allegro->doFinishItem( floatval($_POST['id_aukcji']), '', '', 'text' );
            //
            if ( $wynik == '1' ) {
            
              $pola = array(
                      array('auction_status','2'));
                      
              $db->update_query('allegro_auctions' , $pola, " auction_id = '".floatval($_POST['id_aukcji'])."'");	
              unset($pola);
              
              echo '<span>Aukcja o nr <b>' . floatval($_POST['id_aukcji']) . '</b> została usunięta</span> <br />';
 
            } else {

              echo '<span style="color:#ff0000">Aukcja o nr <b>' . floatval($_POST['id_aukcji']) . '</b> - wystąpił błąd: ' . $wynik . '</span> <br />';
            
            }

            unset($allegro);
        
        }
    
    } else {

        if ( isset($_POST['akcja_dolna']) && isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {

            // wczytanie naglowka HTML
            include('naglowek.inc.php');
            ?>
            
            <div id="naglowek_cont">Usuwanie aukcji allegro</div>
            
            <div id="cont">
                  
                <form action="allegro/allegro_aukcje_akcja_usun_zakoncz.php" method="post" class="cmxform">          

                <div class="poleForm">
                
                  <div class="naglowek">Usuwanie aukcji z bazy sklepu oraz Allegro</div>
                  
                  <div class="pozycja_edytowana">

                      <?php if ( $_POST['akcja_dolna'] != 'usun_zakoncz' ) { ?>
                  
                          <input type="hidden" name="akcja" value="usun_zakoncz" />  
                          <input type="hidden" name="akcja_dolna" value="usun_zakoncz" />
                      
                          <p>
                            Czy usunąć poniższe aukcje z bazy danych sklepu oraz <b style="color:#ff0000">zakończyć trwające aukcje na Allegro</b> ?
                          </p> 

                          <p class="listaAukcji">
                            <?php
                            $idAukcji = implode(',', $_POST['opcja']);

                            $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id IN (" . $idAukcji . ")";
                            $sql = $db->open_query($zapytanie);
                            
                            while ( $info = $sql->fetch_assoc() ) {
                            
                                echo '<input type="hidden" name="opcja[]" value="'.$info['auction_id'].'" />';
                                
                                $link = '';
                                if ( Allegro::SerwerAllegro() == 'nie' ) {
                                  $link = 'http://allegro.pl/item' .  $info['auction_id'] . '_webapi.html';
                                } else {
                                  $link = 'http://allegro.pl.webapisandbox.pl/show_item.php?item='.$info['auction_id'];
                                }                          
                                
                                echo '<a href="' . $link . '">' . $info['auction_id'] . '</a> - ' . $info['products_name'] . '<br />';
                                
                                unset($link);
                                
                            }
                            
                            $db->close_query($sql);
                            unset($zapytanie, $idAukcji);                          
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
                              
                              function allegro_dane(nr) {

                                $.post( "allegro/allegro_aukcje_akcja_usun_zakoncz.php?tok=<?php echo Sesje::Token(); ?>", 
                                  { 
                                    id_aukcji : tablicaId[nr],
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

                                    $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + nr + '</span>');    

                                    $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                                    if (nr < tablicaId.length) {
                                      allegro_dane(nr + 1);
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

                              allegro_dane(0);

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

                  <div class="przyciski_dolne" id="przyciski" <?php echo (( $_POST['akcja_dolna'] == 'usun_zakoncz' )  ? 'style="display:none"' : ''); ?>>
                    
                    <?php if ( $_POST['akcja_dolna'] != 'usun_zakoncz' ) { ?>
                        <input type="submit" class="przyciskNon" value="Usuń dane" />
                    <?php } ?>
                    
                    <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
                    
                  </div>

                </div>

                </form>

            </div>    
            
            <?php
            include('stopka.inc.php');

        } else {
        
            Funkcje::PrzekierowanieURL('allegro_aukcje.php');
            
        }
        
    }
}

?>