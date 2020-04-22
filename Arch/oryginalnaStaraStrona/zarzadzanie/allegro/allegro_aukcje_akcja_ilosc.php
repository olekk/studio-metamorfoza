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

            $zapytanie = "SELECT * FROM allegro_auctions WHERE auction_id = '".floatval($_POST['id_aukcji'])."' and auction_status = '1'";
            $sql = $db->open_query($zapytanie);

            if ( $db->ile_rekordow($sql) > 0 ) {
            
              $info = $sql->fetch_assoc();

              // okresli stan magazynowy produktu
              $zapytanie_produkt = "SELECT products_quantity FROM products WHERE products_id = '".$info['products_id']."'";
              $sql_produkt = $db->open_query($zapytanie_produkt);
              
              if ( $db->ile_rekordow($sql_produkt) > 0 ) {
              
                  $info_produkt = $sql_produkt->fetch_assoc();
                  
                  $ilosc_magazyn = $info_produkt['products_quantity'];
                  
                  // jezeli jest powiazanie cech z magazynem
                  if ( CECHY_MAGAZYN == 'tak' ) {

                      $zapytanie_ilosc_cechy = "SELECT * 
                                                  FROM products_stock
                                                 WHERE products_id = '" . (int)$info['products_id']. "' 
                                                   AND products_stock_attributes = '".str_replace(';', ',' , $info['products_stock_attributes'])."'";
                                                   
                      $sql_ilosc_cechy = $db->open_query($zapytanie_ilosc_cechy);

                      if ((int)$db->ile_rekordow($sql_ilosc_cechy) > 0) {
                      
                          $info_ilosc_cechy = $sql_ilosc_cechy->fetch_assoc();
                          $ilosc_magazyn = $info_ilosc_cechy['products_stock_quantity'];
                          
                      }
                      
                      $db->close_query($sql_ilosc_cechy);
                      unset($zapytanie_ilosc_cechy, $info_ilosc_cechy, $cechy_produktu);

                  }

                  $ilosc_do_wznowienia = $info['products_quantity'];
                  if ( $info['products_quantity'] > $ilosc_magazyn ) {
                    $ilosc_do_wznowienia = $ilosc_magazyn;
                  }                 
                  
                  if ( $ilosc_do_wznowienia > 0 ) {
                  
                      $id_aukcji = floatval($info['auction_id']);

                      $pola = array(
                              array('auction_quantity',floor($ilosc_do_wznowienia)));
                      
                      $rezultat = $allegro->doChangeQuantityItem( $id_aukcji, floor($ilosc_do_wznowienia) );

                      if ( is_array($rezultat) && count($rezultat['item-quantity-left']) > 0) {
                      
                        $db->update_query('allegro_auctions' , $pola, " auction_id = '".floatval($_POST['id_aukcji'])."'");
                        
                        unset($pola);
                        echo '<span>Aukcja o nr <b>' . $_POST['id_aukcji'] . '</b> - ilość produktów na aukcji została zaktualizowana ' . ((isset($info['products_name'])) ? '- produkt: ' . $info['products_name'] : '') . '</span> <br />';
                        
                      } else {
                      
                        if ( count($rezultat['items-sell-not-found']) > 0 ) {
                        
                          foreach ( $rezultat['items-sell-not-found'] as $val ) {
                            echo '<span style="color:#ff0000">Aukcja o nr <b>' . $_POST['id_aukcji'] . '</b> nie została odnaleziona: ' . $val . '</span> <br />';
                          }
                          
                        }
                        
                      }
                      
                    } else {
                    
                      echo '<span style="color:#ff0000">Aukcja o nr <b>' . $_POST['id_aukcji'] . '</b> - ilość produktów na Allegro nie może być równa 0 ' . ((isset($info['products_name'])) ? '- produkt: ' . $info['products_name'] : '') . '</span> <br />';
                    
                  }
                  
                  unset($info_produkt, $ilosc_do_wznowienia);
                  
              }
              
              $db->close_query($sql_produkt);
              unset($zapytanie_produkt);                 
              
              unset($info);
              
          }
          
          $db->close_query($sql);
          unset($zapytanie);      
          
        }
    
    } else {

        if ( isset($_POST['akcja_dolna']) && isset($_POST['opcja']) && count($_POST['opcja']) > 0 ) {

            // wczytanie naglowka HTML
            include('naglowek.inc.php');
            ?>
            
            <div id="naglowek_cont">Aktualizacja ilości produktów na aukcjach</div>
            
            <div id="cont">
                  
                <form action="allegro/allegro_aukcje_akcja_ilosc.php" method="post" class="cmxform">          

                <div class="poleForm">
                
                  <div class="naglowek">Synchronizacja ilości produktów na aukcjach ze stanem magazynowym sklepu</div>
                  
                  <div class="pozycja_edytowana">

                      <?php if ( $_POST['akcja_dolna'] != 'ilosc' ) { ?>
                  
                          <input type="hidden" name="akcja" value="ilosc" />  
                          <input type="hidden" name="akcja_dolna" value="ilosc" />
                      
                          <p>
                            Czy zaktualizować ilość produktów na aukcjach dla poniższych produktów ?
                            
                            <span class="maleInfo" style="margin-left:0px">sprawdzane i aktualizowane będą tylko aukcje trwające, pomijane będą aukcje zakończone</span>
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

                                $.post( "allegro/allegro_aukcje_akcja_ilosc.php?tok=<?php echo Sesje::Token(); ?>", 
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

                                    $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + (nr + 1) + '</span>');    

                                    $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                                    if (nr < tablicaId.length - 1) {
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

                  <div class="przyciski_dolne" id="przyciski" <?php echo (( $_POST['akcja_dolna'] == 'ilosc' )  ? 'style="display:none"' : ''); ?>>
                    
                    <?php if ( $_POST['akcja_dolna'] != 'ilosc' ) { ?>
                        <input type="submit" class="przyciskNon" value="Aktualizuj ilość produktów" />
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