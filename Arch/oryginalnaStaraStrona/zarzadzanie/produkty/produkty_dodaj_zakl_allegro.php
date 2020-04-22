<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] >= 0 && Sesje::TokenSpr()) { 

    $id_produktu = (int)$_GET['id_produktu'];
 
    ?>

    <div class="info_content">
    
        <script src="javascript/jquery.KategorieAllegro.js" type="text/javascript"></script>
        
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready( function() {
        
          $('#drzewo_allegro').click( function() {
          
              $('#drzewo_allegro_cont').slideUp();
              $('#catsPL').slideDown();
          
              $('#treePL').fileTree({
                root: '0',
                script: 'ajax/drzewo_allegro.php',
                expandSpeed: 500,
                collapseSpeed: 500,
                multiFolder: false
              },
              function(file,name,lisc) {
                $('#kategoria_allegro').val(file);
                $('#kategoria_allegro_widoczna').val(file);
                //
                if ( parseInt(file) > 0 ) {
                    $.post('ajax/allegro_sciezka_kategorii.php?tok=<?php echo Sesje::Token(); ?>', { kategoria: file }, function(data) {
                        if ( data != '') {
                           $('#wybrana_kategoria span').html(data);
                           $('#wybrana_kategoria').slideDown();
                        }
                    });
                } else {
                    $('#wybrana_kategoria span').html('');
                    $('#wybrana_kategoria').slideUp();                
                }
                //
              });
   
          });
          
          $.post('ajax/allegro_sciezka_kategorii.php?tok=<?php echo Sesje::Token(); ?>', { kategoria: $('#kategoria_allegro').val() }, function(data) {
              if ( data != '') {
                 //
                 if ( data.indexOf('braki') > 0 ) {
                     $('#drzewo_allegro_cont').hide();
                 }
                 $('#wybrana_kategoria span').html(data);
                 $('#wybrana_kategoria').slideDown();
                 //
              }
          });
          
          ckedit('opis_allegro','790','700px');
          
          $(".calkowita").change(	
              function () {
                  if (isNaN($(this).val())) {
                      $(this).val('');
                     } else {
                      $(this).val( parseInt($(this).val()) );
                  }        
              }
          );   

          $(".kropkaPusta").change(		
            function () {
              var type = this.type;
              var tag = this.tagName.toLowerCase();
              if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                  //
                  zamien_krp($(this),'');
                  //
              }
            }
          );          
          
        });
        //]]>
        </script>    
    
        <?php
        if ($id_produktu > 0) {    
            $zapytanie_tmp = "select * from products_allegro_info where products_id = '".$id_produktu."'";
            $sqls = $db->open_query($zapytanie_tmp);
            $dane_allegro = $sqls->fetch_assoc();
            //
            $products_description_allegro = $dane_allegro['products_description_allegro'];
            $products_price_allegro = $dane_allegro['products_price_allegro'];
            $products_name_allegro = $dane_allegro['products_name_allegro'];
            $products_image_allegro = $dane_allegro['products_image_allegro'];
            $products_cat_id_allegro = (($dane_allegro['products_cat_id_allegro'] > 0) ? $dane_allegro['products_cat_id_allegro'] : '');
            //
          } else {
            //
            $products_description_allegro = '';
            $products_price_allegro = '';
            $products_name_allegro = '';
            $products_image_allegro = '';
            $products_cat_id_allegro = '';
            //
        }
        ?>     

        <span class="maleSukces">Indywidualne parametry produktu przy wystawianiu aukcji na Allegro.</span>
        
        <span class="maleInfo">Kategoria do jakiej będzie przypisany produkt w Allegro.</span>        
      
        <p>
          <label>Kategoria Allegro:</label>
          <input type="text" id="kategoria_allegro_widoczna" size="15" value="<?php echo $products_cat_id_allegro; ?>" disabled="disabled" />
          <input type="hidden" name="kategoria_allegro" id="kategoria_allegro" value="<?php echo $products_cat_id_allegro; ?>" />
        </p>  
        
        <p id="wybrana_kategoria">
          <label>&nbsp;</label>
          <span></span>
        </p>        

        <p id="drzewo_allegro_cont">
          <label>&nbsp;</label>
          <span id="drzewo_allegro">pokaż drzewo kategorii Allegro</span>
        </p>             

        <div id="catsPL">
          <div id="treePL" class="treeBox"></div>
        </div>          

        <span class="maleInfo">Nazwa produktu do Allegro. Jeżeli nazwa nie będzie podana przy wystawianiu aukcji będzie pobierana nazwa główna produktu.</span>        
      
        <p>
          <label>Nazwa produktu na aukcję:</label>
          <input type="text" name="nazwa_allegro" onkeyup="licznik_znakow(this,'iloscZnakowAllegro',50)" size="75" value="<?php echo $products_name_allegro; ?>" />
        </p> 
        
        <p>
          <label></label>
          <span style="display:inline-block; margin:0px 0px 8px 4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakowAllegro"><?php echo (50 - strlen(utf8_decode($products_name_allegro))); ?></span></span>
        </p>        

        <span class="maleInfo">Indywidualne zdjęcie produktu do Allegro. Jeżeli zdjęcie nie będzie wybrane przy wystawianiu aukcji będzie pobierane główne zdjęcie produktu.</span>        
      
        <p>
          <label>Ścieżka zdjęcia:</label>
          <input type="text" name="zdjecie_allegro" size="75" value="<?php echo $products_image_allegro; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','','<?php echo KATALOG_ZDJEC; ?>')" id="foto" />                 
          <span class="usun_zdjecie toolTipTopText" data="foto" title="Usuń przypisane zdjęcie"></span>
        </p>      

        <div id="divfoto" style="padding-left:10px;display:none">
            <label>Zdjęcie:</label>
            <span id="fofoto">
                <span class="zdjecie_tbl">
                    <img src="obrazki/_loader_small.gif" alt="" />
                </span>
            </span> 

            <?php if (!empty($products_image_allegro)) { ?>
            <script type="text/javascript">
            //<![CDATA[            
            pokaz_obrazek_ajax('foto', '<?php echo $products_image_allegro; ?>')
            //]]>
            </script> 
            <?php } ?>  
            
        </div>        
      
        <span class="maleInfo">Cena brutto produktu do Allegro. Jeżeli cena nie będzie podana przy wystawianiu aukcji będzie pobierana cena główna produktu.</span>        
      
        <p>
          <label>Cena brutto produktu na aukcję:</label>
          <input type="text" name="cena_brutto_allegro" class="kropkaPusta" size="5" value="<?php echo $products_price_allegro; ?>" /> w zł
        </p>        
      
        <span class="maleInfo">Opis produktu wykorzystywany do Allegro. Jeżeli opis nie będzie wypełniony przy wystawianiu aukcji będzie pobierany główny opis produktu.</span>

        <p>
          <textarea cols="110" style="width:780px" rows="20" id="opis_allegro" name="opis_allegro"><?php echo $products_description_allegro; ?></textarea>
        </p>
            
        <?php

        if ($id_produktu > 0) {    
            $db->close_query($sqls); 
            unset($zapytanie_tmp, $dane_allegro); 
        }

        unset($products_description_allegro, $products_price_allegro, $products_name_allegro);                      
        ?>   
        
    </div> 

<?php } ?>