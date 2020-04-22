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
    
    <div id="naglowek_cont">Import / eksport danych z plików XML</div>
    <div id="cont">

          <div class="poleForm">
            <div class="naglowek">Obsługa plików XML</div>

                <div class="pozycja_edytowana">  

                    <script type="text/javascript">
                    //<![CDATA[                
                    function wybierz_eksport(id) {
                        for (x = 2; x < 4; x++) {
                            $('#tryb_'+x).slideUp('fast');                             
                        }
                        if (id != 1) {
                            $('#tryb_'+id).slideDown('fast');         
                        }
                    }   
                    function wybierz_zakres(id) {
                        if (id == 1) {
                            $('#rodzaj_import_wszystkie').slideDown('fast');
                            $('#marza').slideDown('fast');
                            $('#rodzaj_import_aktualizacja').slideUp('fast');      
                        }
                        if (id == 2) {
                            $('#rodzaj_import_wszystkie').slideDown('fast');
                            $('#marza').slideUp('fast');  
                            $('#rodzaj_import_aktualizacja').slideUp('fast');      
                        }
                        if (id == 3) {
                            $('#rodzaj_import_wszystkie').slideUp('fast');      
                            $('#marza').slideUp('fast');      
                            $('#rodzaj_import_aktualizacja').slideDown('fast');
                        }                                        
                    }           
                    function zazn_plik() {
                        $('#plik_zew').prop('checked', true); 
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
                                document.location = '/zarzadzanie/import_danych/obsluga_xml.php';
                            } else {
                                document.location = '/zarzadzanie/import_danych/obsluga_xml.php';
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
                    //]]>
                    </script>  

                    <span class="maleInfo">Obsługa plików XML dotyczy formatu XML (struktury pliku) sklepu shopGold. <b>Nie można</b> przy pomocy tego modułu zaimportować dowolnego pliku XML - plik musi posiadać
                    odpowiednią strukturę opisaną w instrukcji do sklepu. Moduł obsługi XML służy do wymiany danych pomiędzy sklepami shopGold lub dostawcami oferującymi XML zgodny ze strukturą sklepu.</span>

                    <table class="csvTbl">
                        <tr>
                        
                            <td style="width:50%">
                            
                                <form action="import_danych/obsluga_xml_import.php" method="post" class="cmxform">   
                            
                                <div>
                                    <input type="hidden" name="akcja" value="import" />
                                    <input type="hidden" name="typ" value="wszystkie" />
                                </div>
                                
                                <div class="poleForm">
                                    <div class="naglowek">Import i aktualizacja danych</div>
                            
                                    <div class="naglowek_csv">Wybierz plik do importu</div>
                                
                                    <div class="lista_plikow_xml">
                                        <table class="plikiTbl">
                                        
                                        <tr class="Naglowek">
                                            <td>Plik</td>
                                            <td>Rozmiar</td>
                                            <td>Data</td>
                                        </tr>
                                        
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
                                                            echo '<tr>
                                                                      <td><input type="radio" name="plik" value="' . $file . '" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /><span>' . Funkcje::PodzielNazwe($file) . '</span></td>
                                                                      <td>';
                                                                      
                                                                      // wielkosc pliku
                                                                      $wielkosc_pliku = filesize($dir . $file);
                                                                      if ($wielkosc_pliku > 1048576) {
                                                                          echo number_format(round($wielkosc_pliku/1048576, 1), 1) . ' MB';
                                                                      } elseif ($wielkosc_pliku > 1024) {
                                                                          echo number_format(round($wielkosc_pliku/1024)) . ' kB';
                                                                      } else  {
                                                                          echo number_format($wielkosc_pliku) . ' B';
                                                                      }    
                                                                      unset($wielkosc_pliku);
                                                                      
                                                                      echo '</td>
                                                                      
                                                                      <td>' . date('d-m-Y H:i',filemtime($dir . $file)) . '</td>
                                                                  </tr>';
                                                            $ilosc_plikow = true;
                                                            //
                                                        }
                                                        //
                                                    }
                                                }
                                                closedir($dh);
                                            }
                                        }  

                                        echo '<tr class="plikZew">
                                                  <td colspan="4"><input type="radio" id="plik_zew" name="plik" value="url" '.(($ilosc_plikow == false) ? 'checked="checked"' : '').' /><span>zewnętrzny adres pliku:</span><input type="text" size="37" onclick="zazn_plik()" class="toolTipText" title="Należy podać pełen adres pliku z http://" value="" name="adres_url" /></td>
                                              </tr>';
                                        unset($ilosc_plikow);
                                        ?>
                                        </table>
                                    </div>
                                    
                                    <div class="naglowek_csv" id="marza">Marża dla cen &nbsp; <input type="text" size="5" class="toolTip" title="Zmiana cen będzie dotyczyć wszystkich cen (również hurtowych) oraz ceny poprzedniej dla promocji" value="" name="marza" /> % </div>
                                    
                                    <div class="naglowek_csv">Zakres importu</div>
                                    
                                    <div class="poli">
                                    
                                        <input type="radio" onclick="wybierz_zakres(1)" checked="checked" value="wszystkie" name="typ" class="toolTipText" title="Importowane będą kategorie oraz produkty" /> produkty i kategorie lub same produkty <br />
                                        <input type="radio" onclick="wybierz_zakres(2)" name="typ" value="kategorie" class="toolTipText" title="Importowane będą wyłącznie kategorie" /> tylko kategorie <br />
                                        <input type="radio" onclick="wybierz_zakres(3)" name="typ" value="cechy" class="toolTipText" title="Importowane będą wyłącznie dane stanów magazynowych, dostępności cech i cen końcowych produktów" /> tylko aktualizacja <b>cech produktów</b>
                                                                        
                                    </div>
                                    
                                    <div class="naglowek_csv">Rodzaj importu</div>
                                    
                                    <div class="poli">
                                    
                                        <div id="rodzaj_import_wszystkie">
                                            <input type="radio" checked="checked" value="dodawanie" class="toolTipTop" title="Dane będą tylko dodawane, nie będą aktualizowane istniejące dane" name="rodzaj_import" /> dodawanie danych
                                            <input type="radio" value="aktualizacja" class="toolTipTop" title="Dane będą tylko aktualizowane, nie będą dodawane nowe dane" name="rodzaj_import" /> aktualizacja danych     
                                        </div>
                                        <div id="rodzaj_import_aktualizacja" style="display:none">
                                            <input type="radio" checked="checked" value="aktualizacja" class="toolTipTop" title="Dane będą tylko aktualizowane, nie będą dodawane nowe dane" name="rodzaj_import_tylko" /> aktualizacja danych     
                                        </div>                                        
                                        
                                    </div>
                                    
                                    <div class="przyciski_dolne" style="padding-left:0px">
                                      <input type="submit" class="przyciskNon" value="Importuj dane XML" />
                                    </div>                                    

                                </div>
                                
                                </form>

                            </td>
                            
                            <td style="width:50%">
                            
                                <form action="import_danych/obsluga_csv_xml_export.php" method="post" class="cmxform">

                                <div>
                                    <input type="hidden" name="akcja" value="export" />   
                                    <input type="hidden" name="format" value="xml" />                                
                                </div>
                                
                                <div class="poleForm">
                                    <div class="naglowek">Eksport danych - produkty i kategorie</div>
                                    
                                    <div class="lista_export_xml">
                                    
                                        <table class="input_export">
                                            <tr><td><input type="radio" value="pl" name="zakres" checked="checked" /></td><td><span>pobierz <b>wszystkie dane</b> tylko w <b>języku polskim</b> tylko z nazwami kategorii (bez opisów i szczegółów kategorii) *</span></td></tr>
                                            <tr><td><input type="radio" value="cechy" name="zakres" /></td><td><span>pobierz tylko <b>cechy produktów</b> - stany magazynowe, dostępności, zdjęcia i ceny produktu wg kombinacji cech w języku polskim</span></td></tr>
                                            <tr><td><input type="radio" value="cena_ilosc" name="zakres" /></td><td><span>pobierz tylko <b>ceny, dostępność i ilość produktów</b> w języku polskim</span></td></tr>
                                        </table>       

                                        <div class="maleInfo">* wszystkie dane z zakresu jaki został zaznaczony w menu Narzędzia / Import i eksport danych / Konfiguracja eksportu CSV i XML</div>
                                    
                                        <div class="naglowek_csv">Dane do eksportu</div>
                                        
                                        <div class="poli">
                                        
                                            <input type="radio" onclick="wybierz_eksport(1)" checked="checked" value="wszystkie" name="export_dane" /> wszystkie produkty   
                                            <input type="radio" onclick="wybierz_eksport(2)" value="producent" name="export_dane" />tylko producenta   
                                            <input type="radio" onclick="wybierz_eksport(3)" value="kategoria" name="export_dane" />tylko z kategorii                         
                                        
                                        </div>  

                                        <div id="tryb_2" style="display:none">
                                            <div id="producent">
                                                Producent <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci()); ?>
                                            </div>
                                        </div>     

                                        <div id="tryb_3" style="display:none">
                                            <div style="padding-top:10px;">
                                                <div id="drzewo" style="margin-left:15px;">
                                                    <?php
                                                    //
                                                    echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                                                    //
                                                    $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                                    for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                                        $podkategorie = false;
                                                        if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                                        //
                                                        echo '<tr>
                                                                <td class="lfp"><input type="radio" value="'.$tablica_kat[$w]['id'].'" name="id_kat" /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<b class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></b>' : '').'</td>
                                                                <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                                              </tr>
                                                              '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                                    }
                                                    echo '</table>';
                                                    unset($tablica_kat,$podkategorie);
                                                    ?> 
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="przyciski_dolne" style="padding-left:0px">
                                          <input type="submit" class="przyciskNon" value="Eksportuj dane XML" />
                                        </div>                                         
                                        
                                    </div>
                                </div>
                                
                                </form>
                                
                                <br />
                                
                                <form action="import_danych/obsluga_csv_xml_export.php" method="post" class="cmxform">

                                <div>
                                    <input type="hidden" name="akcja" value="export_kategorie" />
                                    <input type="hidden" name="format" value="xml" />                                
                                </div>
                                
                                <div class="poleForm">
                                    <div class="naglowek">Eksport kategorii</div>
                                    
                                    <div class="lista_export_xml">
                                    
                                        <input type="radio" value="pl" name="zakres" checked="checked" /> <span>pobierz dane kategorii tylko w języku polskim</span>
                                        
                                        <div class="przyciski_dolne" style="padding-left:0px">
                                          <input type="submit" class="przyciskNon" value="Eksportuj dane XML kategorii" />
                                        </div>                                         
                                        
                                    </div>
                                </div>
                                
                                </form> 
                                
                                <br />
                                
                                <form action="import_danych/obsluga_xml.php" method="post" class="cmxform" id="plikForm" enctype="multipart/form-data"> 
                            
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