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
    
    <div id="naglowek_cont">Import danych z plików XML</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików XML</div>

                <?php if (isset($_POST['akcja']) && $_POST['akcja'] == 'import') { ?>
            
                <div class="pozycja_edytowana">    

                    <div id="import">
                    
                        <div id="postep">Postęp importu ...</div>
                    
                        <div id="suwak">
                            <div style="margin:1px;overflow:hidden">
                                <div id="suwak_aktywny"></div>
                            </div>
                        </div>
                        
                        <div id="procent"></div>  
                        
                        <div class="liczniki" <?php echo (($_POST['rodzaj_import'] == 'dodawanie' && $_POST['typ'] == 'wszystkie') ? '' : 'style="display:none"'); ?>>
                            Dodano: <span id="licz_dodane">0</span>
                        </div>
                        <div class="liczniki" <?php echo ((($_POST['rodzaj_import'] == 'aktualizacja' && $_POST['typ'] == 'wszystkie') || $_POST['typ'] == 'cechy') ? '' : 'style="display:none"'); ?>>
                            Zaktualizowano: <span id="licz_aktualizowane">0</span>
                        </div>                        

                    </div>   
                    
                    <div id="zaimportowano" style="display:none">
                        Dane z pliku <b><?php echo $filtr->process($_POST['plik']); ?></b> zostały wczytane do sklepu ...
                    </div>          

                    <div id="zobacz_raport" onclick="$('#raport').show()">zobacz jakie produkty zaimportowano</div>
                    <div style="margin:17px">
                        <ol id="raport"></ol>
                    </div>                    

                    <script type="text/javascript">
                    //<![CDATA[
                    //
                    <?php
                    $JestPlik = false;
                    if ($_POST['plik'] == 'url' && strpos($_POST['adres_url'], '.xml') > -1 && Funkcje::CzyJestPlik($_POST['adres_url'])) {
                        // 
                        $plik_z_produktami = $_POST['adres_url'];
                        $JestPlik = true;
                        //
                      } else if ($_POST['plik'] != 'url') {
                        //
                        $plik_z_produktami = "../import/" . $_POST['plik'];
                        $JestPlik = true;
                        //
                    }        
                    
                    if ($JestPlik == true) {
                        $dane_produktow = simplexml_load_file($plik_z_produktami);
                        if ($_POST['typ'] != 'kategorie') {
                            $liczba_linii = count($dane_produktow->Produkt);
                          } else {
                            $liczba_linii = count($dane_produktow->Kategoria);
                        }
                       } else {
                        $liczba_linii = 0;
                    }
                    ?>
                    var ilosc_linii = <?php echo $liczba_linii; ?>;
                    //
                    function import_xml(limit) {

                        $.post( "import_danych/import<?php echo ((isset($_POST['typ']) && $_POST['typ'] == 'cechy') ? '_aktualizacja_cech' : ''); ?>.php?tok=<?php echo Sesje::Token(); ?>", 
                              { 
                                format_importu: 'xml',
                                struktura: 'xml',
                                plik: '<?php echo $filtr->process($_POST['plik']); ?>',
                                rodzaj_import: '<?php echo ((isset($_POST['typ']) && $_POST['typ'] == 'cechy') ? 'aktualizacja' : $filtr->process($_POST['rodzaj_import'])); ?>',
                                adres_url: '<?php echo $filtr->process($_POST['adres_url']); ?>',
                                marza: '<?php echo $filtr->process($_POST['marza']); ?>',
                                typ: '<?php echo $filtr->process($_POST['typ']); ?>',
                                ilosc_linii: ilosc_linii,
                                limit: limit
                              },
                              function(data) {

                                 if (ilosc_linii > 0) {
                                     procent = parseInt((parseInt(data.suma) / ilosc_linii) * 100);
                                     if (procent > 100) {
                                         procent = 100;
                                     }
                                    } else {
                                     procent = 0;
                                 }
                                 
                                 <?php if ($_POST['typ'] == 'kategorie') { ?>
                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><span id="licz_produkty" style="display:none"></span>');                              
                                 <?php } else { ?>
                                 $('#procent').html('Stopień realizacji: <span>' + procent + '%</span><br />Przetworzono: <span id="licz_produkty">' + data.suma + '</span>');         
                                 <?php } ?>
                                 
                                 $('#suwak_aktywny').css('width' , (procent * 5) + 5 + 'px');
                                 
                                 // aktualizacja licznika dodawania i aktualizacji
                                 var dodane = parseInt($('#licz_dodane').html()) + parseInt(data.dodane);
                                 var zaktualizowane = parseInt($('#licz_aktualizowane').html()) + parseInt(data.aktualizacja);  

                                 $('#licz_dodane').html(dodane);                                  
                                 $('#licz_aktualizowane').html(zaktualizowane);                                  
                                 $('#raport').html( $('#raport').html() + data.nazwy );                                   
                                                                            
                                 if (ilosc_linii-1 > limit) {
                                    import_xml(parseInt(data.suma));
                                   } else {
                                    $('#postep').css('display','none');
                                    $('#suwak').slideUp("fast");
                                    $('#zaimportowano').slideDown("fast");
                                    $('#przyciski').slideDown("fast");
                                    
                                    if ( parseInt($('#licz_dodane').html()) > 0 || parseInt($('#licz_aktualizowane').html()) > 0 ) {
                                         $('#zobacz_raport').slideDown("fast");
                                    }                                    
                                    
                                 } 
                                 
                                 if (parseInt(data.suma) > ilosc_linii) {
                                    data.suma = ilosc_linii;
                                 }
                                 $('#licz_produkty').html(data.suma);   

                              },
                              "json"                          
                        );
                        
                    }; 
                    //
                    //]]>
                    </script>   
                    
                    <div class="przyciski_dolne" id="przyciski" style="padding-left:0px; display:none">
                      <button type="button" class="przyciskNon" onclick="cofnij('obsluga_xml','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','import_danych');">Powrót</button> 
                    </div> 

                    <script type="text/javascript">
                    //<![CDATA[         
                    if (ilosc_linii > 0) {
                        import_xml(0);              
                      } else {
                        $('#postep').css('display','none');
                        $('#suwak').css('display','none');
                        $('#procent').html('Dane nie zostały wczytane - nie można odczytać pliku, plik ma zły format danych lub plik jest pusty ...');
                        $('#procent').slideDown("fast");
                        $('#przyciski').slideDown("fast"); 
                        $('.liczniki').hide(); 
                    }
                    //]]>
                    </script>                      

                </div>
                
                <?php } ?>
 
          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}