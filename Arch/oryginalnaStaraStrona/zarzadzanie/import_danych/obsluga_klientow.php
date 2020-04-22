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
    
    <div id="naglowek_cont">Import / eksport danych klientów z plików CSV</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa importu i eksportu klientów</div>

                <div class="pozycja_edytowana">  
                
                    <script type="text/javascript">
                    //<![CDATA[                
                    var options = { 
                        target: '#ladowanie', 
                        url: 'ajax/ajax_plik_wgraj.php?tok=<?php echo Sesje::Token(); ?>',
                        beforeSend:function() {
                            $("#ladowanie").show();
                        },
                        complete:function() {
                            $("#ladowanie").hide();
                            if ( $("#ladowanie").html() != '' ) {
                                alert( $("#ladowanie").html() );
                                document.location = '/zarzadzanie/import_danych/obsluga_klientow.php';
                            } else {
                                document.location = '/zarzadzanie/import_danych/obsluga_klientow.php';
                            }
                        }
                    };

                    $('#plikForm').ajaxForm(options);
                    
                    $(function() {
                        $('#wgraj').MultiFile({
                            max: 1,
                            accept: 'csv',
                            STRING: {
                                denied: 'Nie można przesłać pliku w tym formacie $ext!',
                                duplicate: 'Taki plik jest już dodany:\n$file!',
                                selected: 'Wybrany plik: $file',
                            }
                        }); 
                    });
                    //]]>
                    </script>                  

                    <table class="csvTbl">
                        <tr>
                        
                            <td style="width:50%">
                            
                                <form action="import_danych/obsluga_klientow_import.php" method="post" class="cmxform">   

                                <div class="poleForm">
                                
                                    <input type="hidden" name="akcja" value="import" />
                                
                                    <div class="naglowek">Import danych</div>
                            
                                    <div class="naglowek_csv">Wybierz plik do importu</div>
                                
                                    <div class="lista_plikow">
                                        <ul>
                                        <?php
                                        $dir = '../import/';
                                        
                                        $ilosc_plikow = false;
                                        
                                        if (is_dir($dir)) {
                                            if ($dh = opendir($dir)) {
                                                while (($file = readdir($dh)) !== false) {
                                                    if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                                                        //
                                                        if (preg_match('@(.*)\.(csv)@i',$file)) {
                                                            //
                                                            echo '<li><input type="radio" name="plik" value="' . $file . '" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /><span>' . $file . '</span></li>';
                                                            $ilosc_plikow = true;
                                                            //
                                                        }
                                                        //
                                                    }
                                                }
                                                closedir($dh);
                                            }
                                        }                        
                                                     
                                        ?>
                                        </ul>
                                    </div>
                                    
                                    <div class="naglowek_csv">Separator pól</div>
                                    
                                    <div class="poli">
                                    
                                        <input type="radio" checked="checked" value=";" name="sep" class="toolTipTop" title="Pola w importowanym pliku są rozdzielone średnikiem" />; (średnik)    
                                        <input type="radio" value=":" name="sep" class="toolTipTop" title="Pola w importowanym pliku są rozdzielone dwukropkiem" />: (dwukropek)    
                                        <input type="radio" value="," name="sep" class="toolTipTop" title="Pola w importowanym pliku są rozdzielone przecinkiem" />, (przecinek)    
                                        <input type="radio" value="#" name="sep" class="toolTipTop" title="Pola w importowanym pliku są rozdzielone płotkiem" /># (płotek)                     
                                    
                                    </div>
                                    
                                    <div class="przyciski_dolne" style="padding-left:0px">
                                      <input type="submit" class="przyciskNon" value="Importuj dane CSV" />
                                    </div>                                    

                                </div>
                                
                                </form>
                                
                            </td>
                            
                            <td style="width:50%">
                            
                                <form action="import_danych/obsluga_klientow_export.php" method="post" class="cmxform">

                                <div class="poleForm">
                                
                                    <input type="hidden" name="akcja" value="export" />    
                                
                                    <div class="naglowek">Eksport danych</div>
                                    
                                    <div class="lista_export">
                                    
                                        <table class="input_export">
                                            <tr><td><input type="radio" checked="checked" value="wszystkie" name="zakres" /></td><td><span>pobierz <b>wszystkie dane</b> klientów</span></td></tr>
                                        </table>

                                        <div class="przyciski_dolne" style="padding-left:0px">
                                          <input type="submit" class="przyciskNon" value="Eksportuj dane CSV" />
                                        </div>                                         
                                        
                                    </div>
                                </div>
                                
                                </form>
                                
                                <br />
                                
                                <form action="import_danych/obsluga_klientow.php" method="post" class="cmxform" id="plikForm" enctype="multipart/form-data"> 
                            
                                <div class="poleForm">
                                    <div class="naglowek">Wgrywanie plików csv do importu</div>
                                    
                                    <div class="lista_wgraj">
                                    
                                        <span class="ostrzezenie">
                                            Maksymalna ilość plików: 1, maksymalna wielkość pliku: <?php echo ((Funkcje::MaxUpload() < 15) ? Funkcje::MaxUpload() : '15' ); ?>MB
                                        </span>
                                        
                                        <input type="file" name="file[]" id="wgraj" size="45" />
                                        
                                        <div class="cl"></div>
                                        
                                        <input id="form_submit" style="margin-left:0px" type="submit" class="przyciskNon" value="Wgraj wybrany plik" />
                                        <input type="hidden" name="katalog" value="import/" />
                                        <input type="hidden" name="dozwolone" value="<?php echo PLIKI_IMPORT_CSV; ?>" />
                                        <div id="ladowanie" style="display:none;"><img src="obrazki/_loader.gif" alt="przetwarzanie..." /></div>
                                    
                                    </div>                          
                                </div>                           
                                
                                </form>                                  
                                
                            </td>
                            
                        </tr>
                    </table>

                </div>

          </div>                      

    </div>    
    
    <?php
    include('stopka.inc.php');

}