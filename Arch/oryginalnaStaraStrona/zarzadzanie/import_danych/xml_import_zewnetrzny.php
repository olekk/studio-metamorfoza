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
    
    <div id="naglowek_cont">Import danych z zewnętrznych struktur XML</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików zewnętrznych struktur XML</div>

                <div class="pozycja_edytowana">  

                    <table class="csvTbl">
                        <tr>
                        
                            <td>
                            
                                <script type="text/javascript">
                                //<![CDATA[
                                function zazn_plik() {
                                    $('#plik_zew').prop('checked', true); 
                                }
                                
                                function doda(id) {
                                    if (id == 1) {
                                        $('#vat').slideDown('fast'); 
                                        $('#aktXML').hide();
                                        $('#dodXML').slideDown('fast');   
                                    }
                                    if (id == 0) {
                                        $('#vat').slideUp('fast');
                                        $('#dodXML').hide();
                                        $('#aktXML').slideDown('fast');                                           
                                    }                                        
                                }                                  
                                
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
                                            document.location = '/zarzadzanie/import_danych/xml_import_zewnetrzny.php';
                                        } else {
                                            document.location = '/zarzadzanie/import_danych/xml_import_zewnetrzny.php';
                                        }
                                    }
                                };

                                $('#plikForm').ajaxForm(options);
                                
                                $(function() {
                                    $('#wgraj').MultiFile({
                                        max: 1,
                                        accept: 'xml',
                                        STRING: {
                                            denied: 'Nie można przesłać pliku w tym formacie $ext!',
                                            duplicate: 'Taki plik jest już dodany:\n$file!',
                                            selected: 'Wybrany plik: $file',
                                        }
                                    }); 
                                });     

                                function wlaczInp() {
                                    $("#importXMLform :input").attr('disabled', false);
                                }
                                
                                function ZmienStrukture(wartosc) {
                                    if ( wartosc != '' ) {
                                        $.get("import_danych/plugin/" + wartosc + ".php",
                                            { tylko_rekordy: 'tak' },
                                            function(data) { 
                                                $("#opisImportu").html(data);
                                                $("#calaObsluga").slideDown();
                                                $("#rd").prop('checked', true); 
                                                $("#ra").prop('checked', true); 
                                        });                                        
                                      } else {
                                        $("#calaObsluga").slideUp();
                                    }
                                }
                                //]]>
                                </script>                                
                            
                                <form action="import_danych/xml_import_zewnetrzny_obsluga.php" method="post" class="cmxform" id="importXMLform">   
                            
                                <div>
                                    <input type="hidden" name="akcja" value="import" />
                                    <input type="hidden" name="typ" value="wszystkie" />
                                </div>
                                
                                <div class="poleForm">
                                    <div class="naglowek">Import i aktualizacja danych</div>
                            
                                    <div class="naglowek_csv">Wybierz plik do importu</div>
                                
                                    <div class="lista_plikow_xml">
                                        <ul>
                                        <?php
                                        $dir = '../import/';
                                        
                                        $ilosc_plikow = false;
                                        
                                        if (is_dir($dir)) {
                                            if ($dh = opendir($dir)) {
                                                while (($file = readdir($dh)) !== false) {
                                                    if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                                                        //
                                                        if (preg_match('@(.*)\.(xml)@i',$file)) {
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

                                        echo '<li><input type="radio" id="plik_zew" name="plik" value="url" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /><span>zewnętrzny adres pliku:</span><input onclick="zazn_plik()" type="text" size="40" class="toolTipText" title="Należy podać pełen adres pliku z http://" value="" name="adres_url" /></li>';
                                        unset($ilosc_plikow);             
                                        ?>
                                        </ul>
                                    </div>
                                    
                                    <div class="naglowek_csv">Wybierz strukturę pliku</div>
                                    
                                    <div class="poli sel">
                                    
                                        <select name="struktura" onchange="ZmienStrukture(this.value)">
                                            <option value="">-- wybierz strukturę --</option>
                                            <?php
                                            $dir = 'import_danych/plugin/';

                                            if (is_dir($dir)) {
                                                if ($dh = opendir($dir)) {
                                                    while (($file = readdir($dh)) !== false) {
                                                        if ($file != '.' && $file != '..' && !is_dir($dir . $file)) {
                                                            //
                                                            if (preg_match('@(.*)\.(php)@i',$file)) {
                                                                //
                                                                if ( !strpos($file, '_ilosc') ) {
                                                                    //
                                                                    $plikZawartosc = file_get_contents($dir . $file);
                                                                    if ( strpos($plikZawartosc, "_GET['tylko_rekordy']") > -1 ) {
                                                                        //
                                                                        $file = str_replace('.php', '', $file);
                                                                        $opis = strtoupper(substr($file,0,1)) . substr($file,1);
                                                                        //
                                                                        if (strpos($plikZawartosc,'{{') > -1) {
                                                                            $preg = preg_match('|{{([0-9A-Za-ząćęłńóśźż _,;:-?()]+?)}}|', $plikZawartosc, $matches);
                                                                            $opis = $matches[1];
                                                                        }
                                                                        //
                                                                        echo '<option value="' . $file . '">' . $opis . '</option>';
                                                                        //
                                                                        unset($file, $opis);
                                                                    }
                                                                    unset($plikZawartosc);
                                                                    //
                                                                }
                                                                //
                                                            }
                                                            //
                                                        }
                                                    }
                                                    closedir($dh);
                                                }
                                            }  

                                            unset($div);             
                                            ?>                                            
                                        </select>
                                        
                                    </div>
                                    
                                    <div id="calaObsluga" style="display:none">
                                    
                                        <div class="naglowek_csv">Rodzaj importu</div>
                                        
                                        <div class="poli">
                                        
                                            <input type="radio" onclick="doda(1)" checked="checked" id="rd" value="dodawanie" name="rodzaj_import" /> dodawanie danych
                                            <input type="radio" onclick="doda(0)" value="aktualizacja" id="ra" name="rodzaj_import" /> aktualizacja danych     

                                        </div>
                                        
                                        <div id="opisImportu"></div>
                                        
                                        <?php
                                        $zapytanie = "select * from tpl_xml order by tpl_xml_name";
                                        $sql = $db->open_query($zapytanie); 

                                        if ((int)$db->ile_rekordow($sql) > 0) {
                                        ?>
                                        
                                        <div class="naglowek_csv">Wybierz schemat importu pliku</div>
                                        
                                        <div class="poli">
                                        
                                            <?php 
                                            $tablica = array();
                                            $tablica[] = array('id' => 0, 'text' => '-- brak --');
                                            while ($info = $sql->fetch_assoc()) { 
                                                $tablica[] = array('id' => $info['tpl_xml_id'], 'text' => $info['tpl_xml_name']);
                                            }
                                            echo Funkcje::RozwijaneMenu('szablon', $tablica); 
                                            ?>

                                        </div>

                                        <?php } else { ?>
                                        
                                            <input type="hidden" name="szablon" value="0" />
                                        
                                        <?php }
                                        $db->close_query($sql);
                                        unset($zapytanie, $info);

                                        ?>
                                        
                                        <div id="vat">
                                        
                                            <div class="naglowek_csv">Wybierz podatek VAT produktów</div>
                                            
                                            <div class="poli">
                                            
                                                <?php
                                                // pobieranie informacji o vat
                                                $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
                                                $sqls = $db->open_query($zapytanie_vat);
                                                //
                                                $tablica = array();
                                                //
                                                while ($infs = $sqls->fetch_assoc()) { 
                                                    $tablica[] = array('id' => $infs['tax_rate'], 'text' => $infs['tax_description']);
                                                }
                                                $db->close_query($sqls);
                                                unset($zapytanie_vat, $infs);  
                                                //             
                                                echo Funkcje::RozwijaneMenu('vat', $tablica, 'x'); 
                                                unset($tablica);
                                                ?>

                                            </div>     

                                        </div>
                                        
                                        <div class="przyciski_dolne" style="padding-left:0px">
                                          <input type="submit" class="przyciskNon" onclick="wlaczInp()" value="Importuj dane XML" />
                                        </div>  
                                         
                                    </div>

                                </div>
                                
                                </form>
                                
                                <br />
                                
                                <form action="import_danych/xml_import_zewnetrzny.php" method="post" class="cmxform" id="plikForm" enctype="multipart/form-data"> 
                            
                                <div class="poleForm">
                                    <div class="naglowek">Wgrywanie plików xml do importu</div>
                                    
                                    <div class="lista_wgraj">
                                    
                                        <span class="ostrzezenie">
                                            Maksymalna ilość plików: 1, maksymalna wielkość pliku: <?php echo ((Funkcje::MaxUpload() < 15) ? Funkcje::MaxUpload() : '15' ); ?>MB
                                        </span>
                                        
                                        <input type="file" name="file[]" id="wgraj" size="45" />
                                        
                                        <div class="cl"></div>
                                        
                                        <input id="form_submit" style="margin-left:0px" type="submit" class="przyciskNon" value="Wgraj wybrany plik" />
                                        <input type="hidden" name="katalog" value="import/" />
                                        <input type="hidden" name="dozwolone" value="<?php echo PLIKI_IMPORT_XML; ?>" />
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