<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $ByloPole = false;
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php'); 
    
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    
    $zapytanie = "SELECT * FROM comparisons WHERE comparisons_id = '".(int)$_GET['id_poz']."'";
    $sql = $db->open_query($zapytanie);
    
    if ( $db->ile_rekordow($sql) > 0 )  {
        
        $info = $sql->fetch_assoc();   

        $plugin = $info['comparisons_plugin'];    
        $id = $info['comparisons_id'];  
        
        $porownywarki = new Porownywarki($plugin, '0', '0', '/', true);

        $plikDoZapisu = KATALOG_SKLEPU . 'xml/' . $plugin.'.xml';
        
        $ilosc_rekordow = $porownywarki->IloscRekordow;
        
        unset($porownywarki);
        
        $liczba_linii = $ilosc_rekordow;
        if ( $ilosc_rekordow <= 100 ) {
            $limit = '5';
        } elseif ( $ilosc_rekordow > 100 && $ilosc_rekordow <= 1000) {
            $limit = '50';
        } elseif ( $ilosc_rekordow > 1000 && $ilosc_rekordow <= 10000) {
            $limit = '500';
        } elseif ( $ilosc_rekordow > 10000) {
            $limit = '1000';
        }
        
        if ( is_file($plikDoZapisu) ) {
             unlink($plikDoZapisu);
        }

        if ( !is_file($plikDoZapisu) ) {
        
            $plik = fopen($plikDoZapisu,'a+');
            
            if (!$plik) {
                echo Okienka::pokazOkno('Błąd', 'Nie można zapisać pliku /xml/' . $_GET['plugin'] . '.xml');
                exit;
            } else {
                fclose($plik);
            }
            
        }
        
        if ( is_writable($plikDoZapisu) ) {
        
            $fp = fopen($plikDoZapisu, "w+");
            fclose($fp);
            
        } else {
        
            echo Okienka::pokazOkno('Błąd', 'Brak praw do zapisu dla katalogu /xml', 'porownywarki/porownywarki.php');
            exit;
            
        }  

        $ByloPole = true;

    }
    ?>
    
    <div id="naglowek_cont">Eksport danych do porównywarki</div>
    <div id="cont">

        <div class="poleForm">
      
        <?php
        if ( $ByloPole == true ) {
        ?>
  
            <div class="naglowek">Generowanie pliku XML dla porównywarki <?php echo $info['comparisons_name']; ?></div>

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
                Dane zostały wyeksportowane do pliku <?php echo '<a href="'.ADRES_URL_SKLEPU.'/xml/'.$plugin.'.xml">'.ADRES_URL_SKLEPU.'/xml/'.$plugin.'.xml</a>'; ?>
              </div>

              <script type="text/javascript">
              //<![CDATA[
              //
              var ilosc_rekordow   = <?php echo $ilosc_rekordow; ?>;
              var ilosc_linii      = <?php echo $liczba_linii; ?>;
              var licznik_rekordow = 0;
              var limit            = <?php echo $limit; ?>;
              //

              function import_danych(offset) {

                  $.post( "porownywarki/plugin/<?php echo $plugin; ?>.php?tok=<?php echo Sesje::Token(); ?>", 
                    { 
                      offset         : offset,
                      limit          : limit,
                      limit_max      : ilosc_rekordow,
                      plugin         : '<?php echo $plugin; ?>',
                      ilosc_rekordow : ilosc_rekordow,
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
                        import_danych(offset + limit);
                      } else {
                        $('#postep').css('display','none');
                        $('#suwak').slideUp("fast");
                        $('#wgrany_produkt').slideUp("fast");
                        $('#zaimportowano').slideDown("fast");
                        $('#przyciski').slideDown("fast");
                      }   
                      if (data != '') {
                        licznik_rekordow = licznik_rekordow + limit;
                        if (licznik_rekordow > ilosc_rekordow ) {
                          licznik_rekordow = ilosc_rekordow;
                        }
                        $('#wgrany_produkt').html(data);
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
                      array('comparisons_last_export','now()'),
                      array('comparisons_products_exported',$ilosc_rekordow),
              );
              $db->update_query('comparisons' , $pola, " comparisons_id = '".$id."'");	
              ?>
              
              <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                <button type="button" class="przyciskNon" onclick="cofnij('porownywarki','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','porownywarki');">Powrót</button> 
              </div>

            </div>

            <?php 
            
            $db->close_query($sql);
            unset($zapytanie, $info, $plugin);
                
        } else {
        
            echo '<div class="naglowek">Generowanie pliku XML dla porównywarki</div><div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
        
        }
        ?>      
            
        </div>
                
    </div>    
    
    <?php
    
    unset($ByloPole);
    
    include('stopka.inc.php');

}