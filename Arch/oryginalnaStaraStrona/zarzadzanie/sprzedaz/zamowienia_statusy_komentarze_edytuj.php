<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $id = $filtr->process($_POST['id']);
        //
        $pola = array(
                array('comments_name',$filtr->process($_POST["nazwa"])),
                array('sort_order',$filtr->process($_POST["sort"]))
        );
        //	
        $db->update_query('standard_order_comments' , $pola, " comments_id = '".(int)$_POST["id"]."'");	
        
        unset($pola);

        // kasuje rekordy w tablicy
        $db->delete_query('standard_order_comments_description' , " comments_id = '".$filtr->process($_POST["id"])."'");        
        
        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
            //
            $pola = array(
                    array('comments_id',$id),
                    array('comments_text',$filtr->process($_POST['edytor_'.$w])),
                    array('languages_id',$ile_jezykow[$w]['id']));        
            //
            $sql = $db->insert_query('standard_order_comments_description' , $pola);  
            unset($pola);
            //
        }

        unset($ile_jezykow);    

        
        //
        Funkcje::PrzekierowanieURL('zamowienia_statusy_komentarze.php?id_poz='.(int)$_POST["id"].'&status_id='.(int)$_POST["status_id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    if ( !isset($_GET['status_id']) ) {
         $_GET['status_id'] = 0;
    }     
    ?>
    
    <div id="naglowek_cont">Edycja komentarza zamówienia dla statusu - <?php echo Sprzedaz::pokazNazweStatusuZamowienia((int)$_GET['status_id']); ?></div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#slownikForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                sort: {
                  required: true,
                  range: [0, 999],
                  number: true
                }
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane"
                },               
                sort: {
                  required: "Pole jest wymagane",
                  range: "Pole musi być liczbą większą od 0",
                  number: "Pole musi być liczbą"
                }
              }
            });
          });
          //]]>
          </script>     

          <form action="sprzedaz/zamowienia_statusy_komentarze_edytuj.php" method="post" id="slownikForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }               
            
            $zapytanie = "select * from standard_order_comments where comments_id = '" . $filtr->process((int)$_GET['id_poz']) . "' and status_id = '" . $filtr->process((int)$_GET['status_id']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
                <div class="pozycja_edytowana">
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    <input type="hidden" name="status_id" value="<?php echo $_GET["status_id"]; ?>" />

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                    
                    <div class="info_tab">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\')">'.$ile_jezykow[$w]['text'].'</span>';
                    }                    
                    ?>                   
                    </div>
                    
                    <div style="clear:both"></div>
                    
                    <div class="info_tab_content">
                        <?php
                        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from standard_order_comments_description where comments_id = '".$filtr->process((int)$_GET['id_poz'])."' and languages_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $nazwa = $sqls->fetch_assoc();   
                            
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <div class="edytor">
                                  <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo $nazwa['comments_text']; ?></textarea>
                                </div>   
                                            
                                <?php
                                $db->close_query($sqls);
                                unset($nazwa); 
                                ?>

                            </div>
                            <?php                    
                        }                    
                        ?>                      
                    </div>
                    
                    <script type="text/javascript">
                    //<![CDATA[
                    gold_tabs('0','edytor_');
                    //]]>
                    </script> 

                    <p>
                          <label class="required">Nazwa:</label>
                          <input type="text" name="nazwa" size="60" value="<?php echo $info['comments_name']; ?>" id="nazwa" class="toolTipText" title="Nazwa wyświetlana na liście wyboru komentarzy" />
                    </p>

                    <p>
                          <label class="required">Kolejność wyświetlnia:</label>
                          <input type="text" name="sort" id="sort" size="8" value="<?php echo $info['sort_order']; ?>" class="toolTipText" title="Kolejność wyświetlania na liście wyboru komentarzy" />
                    </p>

                </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_statusy_komentarze','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button>   
            </div>            

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>
          </div>                      
          </form>

    </div>    
    
    <div class="objasnienia">
    
        <div class="objasnieniaTytul">Znaczniki, które możesz użyć w treści wiadomości:</div>
        
        <div class="objasnieniaTresc">

            <ul class="mcol">

                <li><b>{NR_PRZESYLKI}</b> - Numer dokumentu przewozowego firmy kurierskiej</li>
                <li><b>{WARTOSC_ZAMOWIENIA}</b> - Wartość zamówienia</li>
                <li><b>{ILOSC_PUNKTOW}</b> - Ilość punktów za zamówienie</li>
                <li><b>{DOKUMENT_SPRZEDAZY}</b> - Dokument sprzedaży do zamówienia: paragon lub faktura</li>
                <li><b>{FORMA_PLATNOSCI}</b> - Wybrana przez klienta forma płatności za zamówienie</li>
                <li><b>{FORMA_WYSYLKI}</b> - Wybrana przez klienta forma wysyłki zamówienia</li>
                <li><b>{LINK_PLIKOW_ELEKTRONICZNYCH}</b> - Link do pobrania plików elektronicznych (bez znacznika linku &lt;a href ... - używane tylko przy sprzedaży produktów online</li>
                
            </ul>

        </div> 
        
    </div>    
    
    <?php
    include('stopka.inc.php');

}

