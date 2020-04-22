<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $allegro = new Allegro(true);

  // wyczyszczenie tablicy zawierajacej definicje pol formularzy
  $db->truncate_query('allegro_categories');        

  // wczytanie naglowka HTML
  include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import drzewa kategorii z serwisu Allegro</div>
    <div id="cont">

      <div class="poleForm">
        <div class="naglowek">Konfiguracja Allegro</div>

          <?php if ( isset($_POST['akcja']) && $_POST['akcja'] == 'importuj' ) { ?>
            
            <div class="pozycja_edytowana">    

              <div id="import">
                    
                <div id="postep">Postęp importu ...</div>
                    
                <div id="suwak">
                  <div style="margin:1px;overflow:hidden">
                    <div id="suwak_aktywny"></div>
                  </div>
                </div>
                        
                <div id="procent"></div>  
              </div>   
                    
              <div id="zaimportowano" style="display:none">
                Dane zostały wczytane do sklepu ...
              </div>
              <div id="bladimportu" style="display:none">
                Wystąpił błąd w odpowiedzi serwisu API Allegro - dane nie zostały poprawnie zaimportowane ...
              </div>

              <?php
              $ilosc_rekordow = $allegro->doGetCatsDataCount();

              $wynik = strstr( $ilosc_rekordow, 'ERR' );
              if ( $wynik ) {
                echo Okienka::pokazOkno('Błąd', $ilosc_rekordow);
                echo Okienka::przekierujAdres(( $_SESSION['szyfrowanie'] ? ADRES_URL_SKLEPU_SSL : ADRES_URL_SKLEPU ) . '/allegro/konfiguracja_polaczenia.php');
              }
              $liczba_linii = $ilosc_rekordow / 500;
              ?>

              <script type="text/javascript">
                //<![CDATA[
                //
                var ilosc_rekordow = <?php echo $ilosc_rekordow; ?>;
                var ilosc_linii = <?php echo $liczba_linii; ?>;
                var licznik_rekordow = 0;
                //

                function import_danych(limit) {

                  $.post( "allegro/import_definicji_kategorii.php?tok=<?php echo Sesje::Token(); ?>", 
                    { 
                      limit: limit,
                    },
                    function(data) {

                      if (ilosc_linii <= 1) {
                        procent = 100;
                      } else {
                        procent = parseInt((limit / (ilosc_linii - 1)) * 100);
                        if (procent > 100) {
                          procent = 100;
                        }
                      }

                                 
                      $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + licznik_rekordow + '</span>');    

                      $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');

                      if (ilosc_linii - 1 > limit && data != 'BLAD' ) {
                        import_danych(limit + 1);
                      } else {
                        $('#postep').css('display','none');
                        $('#suwak').slideUp("fast");
                        if ( data != 'BLAD' ) {
                            $('#zaimportowano').slideDown("fast");
                        } else {
                            $('#bladimportu').slideDown("fast");
                        }
                        $('#przyciski').slideDown("fast");
                      }   
                      if (data != '') {
                        licznik_rekordow = licznik_rekordow + 500;
                        if (licznik_rekordow > ilosc_rekordow ) {
                          licznik_rekordow = ilosc_rekordow;
                        }
                        $('#licz_produkty').html(licznik_rekordow);
                      }
                    }
                  );

                }; 
                //
                import_danych(0);              
                //]]>
              </script>   
              <?php
              $pola = array(
                      array('value',$allegro->doGetSysStatus('3'))
              );
              $db->update_query('allegro_connect' , $pola, " params = 'CONF_CATEGORIES_WER'");	
              ?>
              <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                <button type="button" class="przyciskNon" onclick="cofnij('konfiguracja_polaczenia','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','allegro');">Powrót</button> 
              </div>                    

            </div>

          <?php } ?>
 
        </div>                      

     </div>    
    
    <?php
    include('stopka.inc.php');

}