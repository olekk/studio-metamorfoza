<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $wynik = '';

  //wczytanie klasy do obslugi Allegro
  $allegro = new Allegro(true);

  if ( isset($_SESSION['hash']) && isset($_SESSION['allegro_user_login']) ) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    <div id="naglowek_cont">Synchronizacja Allegro</div>
    <div id="cont">

      <div class="poleForm">

          <div class="naglowek">Pobieranie informacji z serwisu Allegro</div>

            <?php //if ( isset($_POST['akcja']) && $_POST['akcja'] == 'synchronizuj' ) { ?>
              
              <div class="pozycja_edytowana">    

                <div id="import">
                      
                  <div id="postep">Postęp importu ...</div>
                      
                  <div id="suwak">
                    <div style="margin:1px;overflow:hidden">
                      <div id="suwak_aktywny"></div>
                    </div>
                  </div>
                          
                  <div id="procent"></div>  

                  <div id="wynik" class="listaAukcji" style="margin-top:10px;"></div>
                  
                </div>   
                      
                <div id="zaimportowano" style="display:none">
                  Dane w sklepie zostały zaktualizowane ...
                </div>

                <?php
                $zapytanie = "SELECT auction_id FROM allegro_auctions WHERE DATE_SUB(CURDATE(),INTERVAL 180 DAY) <= products_date_end ";
                $sql = $db->open_query($zapytanie);

                $ilosc_rekordow = $db->ile_rekordow($sql);

                // obsluga bledow webAPI
                $wynik = strstr( $ilosc_rekordow, 'ERR' );
                if ( $wynik ) {
                  echo Okienka::pokazOkno('Błąd', $ilosc_rekordow, 'allegro/allegro_synchronizuj.php');
                }
                
                $liczba_linii = $ilosc_rekordow;
                ?>

                <script type="text/javascript">
                  //<![CDATA[
                  //
                  var ilosc_rekordow = <?php echo $ilosc_rekordow; ?>;
                  var ilosc_linii = <?php echo $liczba_linii; ?>;
                  var licznik_rekordow = 0;
                  //

                  function import_danych(offset, limit) {

                    $.post( "allegro/allegro_import_aukcji.php?tok=<?php echo Sesje::Token(); ?>", 
                      { 
                        offset       : offset,
                        limit        : 25,
                        serwer       : '<?php echo Allegro::SerwerAllegro(); ?>'
                      },
                      function(data) {

                        if (ilosc_linii <= 1) {
                          procent = 100;
                        } else {
                          procent = parseInt((offset / (ilosc_linii - 1)) * 100);
                          if (procent > 100) {
                            procent = 100;
                          }
                        }

                        $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + licznik_rekordow + '</span>');    

                        $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                        if (ilosc_linii - 1 > offset) {
                          import_danych(offset + 25 , limit);
                        } else {
                          $('#postep').css('display','none');
                          $('#suwak').slideUp("fast");
                          $('#wgrany_produkt').slideUp("fast");
                          $('#zaimportowano').slideDown("fast");
                          $('#przyciski').slideDown("fast");
                        }   
                        if (data != '') {
                          licznik_rekordow = licznik_rekordow + 25;
                          if (licznik_rekordow > ilosc_rekordow ) {
                            licznik_rekordow = ilosc_rekordow;
                          }
                          $('#wgrany_produkt').html(data);
                          $('#licz_produkty').html(licznik_rekordow);
                          $('#wynik').html( $('#wynik').html() + data );
                        }
                        
                      }
                    );
                  }; 
                  //
                  import_danych(0, 0);              
                  //]]>
                </script> 
                
                <?php
                $pola = array(
                        array('value',time()));
                        
                $db->update_query('allegro_connect' , $pola, " params = 'CONF_LAST_SYNCHRONIZATION'");
                
                if ( isset($_POST['powrot']) && $_POST['powrot'] != '' ) {
                
                  $powrot = $_POST['powrot'];
                  
                } else {
                
                  $powrot = 'allegro_aukcje';
                  
                }
                ?>
                
                <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                  <button type="button" class="przyciskNon" onclick="cofnij('<?php echo $powrot; ?>','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
                </div>                    

              </div>

            <?php //} ?>
   
          </div>                      
      
      </div>
    </div>
    
    <?php
    include('stopka.inc.php');
    
  } else {
  
    Funkcje::PrzekierowanieURL('allegro_logowanie.php?strona='.$_POST['strona']);
    
  }


}

?>