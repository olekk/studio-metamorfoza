<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //        
        // kasuje rekordy w tablicy
        $db->delete_query('products_extra_fields_book' , " products_extra_fields_id = '".(int)$_POST['id']."'");    
        //
        foreach ( $_POST['nazwa'] as $pole ) {
            //
            if ( $filtr->process($pole) != '' ) {
                //
                $pola = array(
                        array('products_extra_fields_id',(int)$_POST['id']),
                        array('products_extra_fields_book_text',$filtr->process($pole))
                        );
                //
                $db->insert_query('products_extra_fields_book' , $pola);
                //
                unset($pola);
                //
            }
            //
        }
        //	
        Funkcje::PrzekierowanieURL('dodatkowe_pola.php?id_poz='.(int)$_POST['id']);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <script type="text/javascript">
    //<![CDATA[                        
    function dodaj_pole(zdjecie) {
        var ile_pol = parseInt($("#ile_pol").val()) + 1;
        //
        $.get('ajax/dodaj_pole_slownik.php', { id: ile_pol, zdjecie: zdjecie, katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            $('#pola_slowniki').append(data);
            $("#ile_pol").val(ile_pol);
            //
            if ( zdjecie == 1 ) {
                //
                $('.obrazek').bind('focus',
                  function () {
                    var id = $(this).attr("id");
                        pokaz_obrazek_ajax(id, $(this).val());
                  }
                );
                //
            }
            //
            pokazChmurki();            
        });
        //
    } 
    function usun_pole(id) {
        $('.tip-twitter').css({'visibility':'hidden'});
        $('#pole_' + id).remove();
        //
        if ( $('#divfoto_' + id).length ) {
             $('#divfoto_' + id).remove();
        }
        //
    }
    //]]>
    </script>
    
    <div id="naglowek_cont">Słowniki dodatkowych pól</div>
    
    <div id="cont">
          
        <form action="slowniki/dodatkowe_pola_slowniki.php" method="post" id="slownikForm" class="cmxform">          

        <div class="poleForm">
          <div class="naglowek">Dodawanie / edycja danych</div>
          
          <?php
          if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
          }      

          $licznik_pol = 1;
          ?>
          
          <div class="pozycja_edytowana">
          
              <div class="info_content">
          
                  <input type="hidden" name="akcja" value="zapisz" />
                  
                  <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
              
                  <div id="pola_slowniki">
                  
                      <?php
                      $zapytanie = "select pb.products_extra_fields_book_id, 
                                           pb.products_extra_fields_book_text,
                                           pe.products_extra_fields_image
                                      from products_extra_fields_book pb, products_extra_fields pe 
                                     where pb.products_extra_fields_id = '" . $filtr->process((int)$_GET['id_poz']) . "' and 
                                           pb.products_extra_fields_id = pe.products_extra_fields_id";
                                           
                      $sql = $db->open_query($zapytanie);
                      
                      $pola_zdjecie = 0;
                      
                      if ((int)$db->ile_rekordow($sql) > 0) {
                      
                          while ($info = $sql->fetch_assoc()) {
                          
                          // jezeli nie jest obrazkowy
                          if ( $info['products_extra_fields_image'] == 0 ) {
                              ?>              
                      
                              <p id="pole_<?php echo $licznik_pol; ?>">
                                <label>Wartość pola:</label>
                                <input type="text" name="nazwa[]" value="<?php echo $info['products_extra_fields_book_text']; ?>" size="105" />
                                <span class="usun_pole toolTipTopText" onclick="usun_pole(<?php echo $licznik_pol; ?>)" title="Usuń pole słownika"></span>
                              </p>
                              
                              <?php
                              
                            } else { 
                              
                              $pola_zdjecie = 1;
                            
                              ?>              
                      
                              <p id="pole_<?php echo $licznik_pol; ?>">
                                <label>Zdjęcie pola:</label>
                                <input type="text" name="nazwa[]" id="foto_<?php echo $licznik_pol; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto_<?php echo $licznik_pol; ?>','','<?php echo KATALOG_ZDJEC; ?>')" value="<?php echo $info['products_extra_fields_book_text']; ?>" size="105" />
                                <span class="usun_pole toolTipTopText" onclick="usun_pole(<?php echo $licznik_pol; ?>)" title="Usuń pole słownika"></span>
                              </p>
                              
                              <div id="divfoto_<?php echo $licznik_pol; ?>" style="padding-left:10px; display:none">
                                <label>&nbsp;</label>
                                <span id="fofoto_<?php echo $licznik_pol; ?>">
                                    <span class="zdjecie_tbl">
                                        <img src="obrazki/_loader_small.gif" alt="" />
                                    </span>
                                </span> 

                                <?php if (!empty($info['products_extra_fields_book_text'])) { ?>
                                <script type="text/javascript">
                                //<![CDATA[            
                                pokaz_obrazek_ajax('foto_<?php echo $licznik_pol; ?>', '<?php echo $info['products_extra_fields_book_text']; ?>')
                                //]]>
                                </script>  
                                
                              </div>  
                              
                              <?php }                      
                            
                          }
                              
                          $licznik_pol++;
                          
                          }

                      }
                      $db->close_query($sql);
                      ?>

                  </div>
                  
                  <div style="padding:10px;padding-top:20px;">
                      <span class="dodaj" onclick="dodaj_pole(<?php echo $pola_zdjecie; ?>)" style="cursor:pointer">dodaj kolejną pozycję</span>
                  </div> 

              </div>
              
          </div>
          
          <input value="<?php echo $licznik_pol; ?>" type="hidden" name="ile_pol" id="ile_pol" />
          
          <script type="text/javascript">
          //<![CDATA[            
          dodaj_pole(<?php echo $pola_zdjecie; ?>);
          //]]>
          </script> 

          <?php
          unset($info, $pola_zdjecie, $pola_zdjecie);
          ?>                                

          <div class="przyciski_dolne">
            <input type="submit" class="przyciskNon" value="Zapisz dane" />
            <button type="button" class="przyciskNon" onclick="cofnij('dodatkowe_pola','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>   
          </div>            

        </div>                      
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}