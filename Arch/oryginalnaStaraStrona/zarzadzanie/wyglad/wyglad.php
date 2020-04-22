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
    
    <script type="text/javascript" src="javascript/jquery-ui.js"></script>
    <script type="text/javascript" src="wyglad/wyglad.js"></script>
    <script type="text/javascript" src="programy/jscolor/jscolor.js"></script>

    <script type="text/javascript">
    //<![CDATA[    
    $(function() {     
        // kolejnosc dla lewej kolumny
        $("#wyglad_lewa").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_box.php?tok=<?php echo Sesje::Token(); ?>", order + '&kolumna=lewa');                    															 
            }								  
        });	
        $("#wyglad_lewa").disableSelection();
        //
        // kolejnosc dla prawej kolumny
        $("#wyglad_prawa").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_box.php?tok=<?php echo Sesje::Token(); ?>", order + '&kolumna=prawa'); 															 
            }								  
        });	
        $("#wyglad_prawa").disableSelection(); 
        //
        // kolejnosc dla srodkowej kolumny
        $("#wyglad_srodek_srodek").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_modul.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=srodek'); 															 
            }								  
        });	
        $("#wyglad_srodek_srodek").disableSelection();
        //
        // kolejnosc dla srodkowej kolumny - czesc gorna
        $("#wyglad_srodek_gora").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_modul.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=gora'); 															 
            }								  
        });	
        $("#wyglad_srodek_gora").disableSelection();        
        //
        // kolejnosc dla srodkowej kolumny - czesc dolna
        $("#wyglad_srodek_dol").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_modul.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=dol'); 															 
            }								  
        });	
        $("#wyglad_srodek_dol").disableSelection();        
        //        
        // kolejnosc dla gornego menu
        $("#wyglad_gorne_menu").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=gorne_menu&stala=GORNE_MENU');														 
            }								  
        });	
        $("#wyglad_gorne_menu").disableSelection();   
        //
        // kolejnosc dla dolnego menu
        $("#wyglad_dolne_menu").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=dolne_menu&stala=DOLNE_MENU');														 
            }								  
        });	
        $("#wyglad_dolne_menu").disableSelection(); 
        //
        // kolejnosc pierwszej kolmny stopki
        $("#wyglad_stopka_pierwsza").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_pierwsza&stala=STOPKA_PIERWSZA');														 
            }								  
        });	
        $("#wyglad_stopka_pierwsza").disableSelection();
        //
        // kolejnosc drugiej kolmny stopki
        $("#wyglad_stopka_druga").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_druga&stala=STOPKA_DRUGA');														 
            }								  
        });	
        $("#wyglad_stopka_druga").disableSelection();   
        //
        // kolejnosc trzeciej kolmny stopki
        $("#wyglad_stopka_trzecia").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_trzecia&stala=STOPKA_TRZECIA');														 
            }								  
        });	
        $("#wyglad_stopka_trzecia").disableSelection();  
        //
        // kolejnosc czwartej kolmny stopki
        $("#wyglad_stopka_czwarta").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_czwarta&stala=STOPKA_CZWARTA');														 
            }								  
        });	
        $("#wyglad_stopka_czwarta").disableSelection();  
        //
        // kolejnosc piatej kolmny stopki
        $("#wyglad_stopka_piata").sortable({ 
            opacity: 0.6, 
            cursor: 'move', 
            update: function() {
                var order = $(this).sortable("serialize"); 
                $.post("wyglad/wyglad_serialize_stala.php?tok=<?php echo Sesje::Token(); ?>", order + '&typ=stopka_piata&stala=STOPKA_PIATA');														 
            }								  
        });	
        $("#wyglad_stopka_piata").disableSelection(); 
    });     
    function infoSzablon(katalog, post) {
        var opis = $('#Tpl_' + katalog).html();
        $('#OpisSzablonow').hide();
        $('#OpisSzablonow').html(opis);
        $('#OpisSzablonow').fadeIn();
        //
        if ( post == 1 ) {
             $.post("wyglad/wyglad_szablon_konfig.php?tok=<?php echo Sesje::Token(); ?>", { dane: $('#Konfig_' + katalog).html() }, function(data) { document.location = '/zarzadzanie/wyglad/wyglad.php' } );		
        }
    }
    //]]>
    </script>     
    
    <div id="naglowek_cont">Definiowanie ustawień wyglądu sklepu</div>
    
    <div id="infoAjax">wszystkie zmiany są zapisywane w czasie rzeczywistym bezpośrednio do bazy sklepu</div>
    
    <div id="cont">

          <form action="wyglad/wyglad.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Ustawienia wyglądu sklepu</div>
            
                <table style="width:100%"><tr>
                
                    <td id="lewe_zakladki" style="vertical-align:top">
                        <a href="javascript:gold_tabs_horiz('0')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                        <a href="javascript:gold_tabs_horiz('1')" class="a_href_info_zakl" id="zakl_link_1">Tło sklepu</a>
                        <a href="javascript:gold_tabs_horiz('2')" class="a_href_info_zakl" id="zakl_link_2">Nagłówek sklepu</a>
                        <a href="javascript:gold_tabs_horiz('3')" class="a_href_info_zakl" id="zakl_link_3">Górne menu</a>
                        <a href="javascript:gold_tabs_horiz('4')" class="a_href_info_zakl" id="zakl_link_4">Boxy w kolumnach</a>
                        <a href="javascript:gold_tabs_horiz('5')" class="a_href_info_zakl" id="zakl_link_5">Moduły środkowe</a>
                        <a href="javascript:gold_tabs_horiz('6')" class="a_href_info_zakl" id="zakl_link_6">Dolne menu</a>
                        <a href="javascript:gold_tabs_horiz('7')" class="a_href_info_zakl" id="zakl_link_7">Stopka pierwsza kolumna</a>
                        <a href="javascript:gold_tabs_horiz('8')" class="a_href_info_zakl" id="zakl_link_8">Stopka druga kolumna</a>
                        <a href="javascript:gold_tabs_horiz('9')" class="a_href_info_zakl" id="zakl_link_9">Stopka trzecia kolumna</a>
                        <a href="javascript:gold_tabs_horiz('10')" class="a_href_info_zakl" id="zakl_link_10">Stopka czwarta kolumna</a>
                        <a href="javascript:gold_tabs_horiz('11')" class="a_href_info_zakl" id="zakl_link_11">Stopka piąta kolumna</a>
                        <a href="javascript:gold_tabs_horiz('12')" class="a_href_info_zakl" id="zakl_link_12">Szablon mobilny</a>
                    </td>

                    <td id="prawa_strona" style="vertical-align:top">
                    
                        <div id="zakl_id_0" style="display:none;">

                            <p>
                                <label>Szerokość sklepu:</label>
                                <input type="text" name="szerokosc" onchange="zmienGet(this.value,'SZEROKOSC_SKLEPU')" value="<?php echo SZEROKOSC_SKLEPU; ?>" size="5" /> &nbsp;px
                                <span class="maleInfo" style="display:inline-block">nie dotyczy szablonów RWD które mają zmienne szerokości</span>
                            </p> 
                            
                            <p>
                                <label>Szerokość lewej kolumny:</label>
                                <input type="text" name="szerokosc_lewa" value="<?php echo SZEROKOSC_LEWEJ_KOLUMNY; ?>" onchange="zmienGet(this.value,'SZEROKOSC_LEWEJ_KOLUMNY')" size="5" /> &nbsp;px
                            </p>

                            <p>
                                <label>Szerokość prawej kolumny:</label>
                                <input type="text" name="szerokosc_prawa" value="<?php echo SZEROKOSC_PRAWEJ_KOLUMNY; ?>" onchange="zmienGet(this.value,'SZEROKOSC_PRAWEJ_KOLUMNY')" size="5" /> &nbsp;px
                            </p> 
                            
                            <p>
                                <label>Włączona lewa kolumna:</label>
                                <input type="radio" value="tak" name="lewa_kol" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_LEWA_KOLUMNA == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="lewa_kol" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_LEWA_KOLUMNA == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>  
                            
                            <p>
                                <label>Czy lewa kolumna z boxami ma się wyświetlać tylko na podstronach (nie będzie widoczna na stronie głównej) ?</label>
                                <input type="radio" value="tak" name="lewa_kol_wszedzie" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_LEWA_WSZEDZIE == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="lewa_kol_wszedzie" onchange="zmienGet(this.value,'CZY_WLACZONA_LEWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_LEWA_WSZEDZIE == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>                            

                            <p>
                                <label>Włączona prawa kolumna:</label>
                                <input type="radio" value="tak" name="prawa_kol" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_PRAWA_KOLUMNA == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="prawa_kol" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_KOLUMNA')" <?php echo ((CZY_WLACZONA_PRAWA_KOLUMNA == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>

                            <p>
                                <label>Czy prawa kolumna z boxami ma się wyświetlać tylko na podstronach (nie będzie widoczna na stronie głównej) ?</label>
                                <input type="radio" value="tak" name="prawa_kol_wszedzie" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_PRAWA_WSZEDZIE == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="prawa_kol_wszedzie" onchange="zmienGet(this.value,'CZY_WLACZONA_PRAWA_WSZEDZIE')" <?php echo ((CZY_WLACZONA_PRAWA_WSZEDZIE == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>                            

                            <p>
                                <label>Opis do szablonu:</label>
                                <span id="OpisSzablonow"></span>
                            </p>     

                            <p>
                                <label>Dymyślny szablon sklepu:</label>
                            </p>                       
                            
                            <?php
                            $dir = opendir('../szablony/');
                            
                            echo '<table id="szablony"><tr>';
                            $licznikTd = 0;
                                               
                            while (false !== ($katalog = readdir($dir))) {
                                
                                if ($katalog != '.' && $katalog != '..' && is_dir('../szablony/' . $katalog . '/_podglad')) {
                                    
                                    $img = '../szablony/' . $katalog . '/_podglad/screen.jpg';
                                    if (file_exists($img)) {
                                        echo '<td><img src="' . $img . '" alt="' . $katalog . '" title="' . $katalog . '" />';
                                      } else {
                                        echo '<td>Brak podglądu ...';
                                    }
                                    unset($img);
                                    
                                    // plik opisu szablonu
                                    $opis = '../szablony/' . $katalog . '/_podglad/opis.tpo';
                                    echo '<div style="display:none" id="Tpl_' . str_replace('.', '_', $katalog) . '">';
                                    if (file_exists($opis)) {
                                        echo nl2br(file_get_contents($opis)); 
                                    }
                                    echo '</div>';
                                    unset($opis);
                                    
                                    // dodatkowa konfiguracja
                                    $konfg = '../szablony/' . $katalog . '/_podglad/konfiguracja.dat';
                                    echo '<div style="display:none" id="Konfig_' . str_replace('.', '_', $katalog) . '">';
                                    if (file_exists($konfg)) {
                                        echo strip_tags(file_get_contents($konfg)); 
                                    }
                                    echo '</div>';
                                    unset($konfg);                                    
                                    
                                    echo '<div><input type="radio" value="'.$katalog.'" name="domyslny_szablon" onclick="infoSzablon(\''. str_replace('.', '_', $katalog) .'\',1);zmienGet(this.value,\'DOMYSLNY_SZABLON\')" '.((DOMYSLNY_SZABLON == $katalog) ? 'checked="checked"' : '') ." /> " . $katalog . '</div></td>';
                                    
                                    $licznikTd++;
                                    
                                    if ($licznikTd == 2) {
                                        echo '</tr><tr>';
                                        $licznikTd = 0;
                                    }
                                    
                                }
                                
                            }
                            closedir($dir);
                            
                            echo '</tr></table>';
                            ?>
                            
                        </div>
                            
                            
                        <div id="zakl_id_1" style="display:none;">

                            <p>
                                <label>Tło zewnętrzne sklepu:</label>
                                <input type="radio" value="1" name="tlo_sklepu" onclick="zmien_tlo(1)" <?php echo ((TLO_SKLEPU_RODZAJ == 'kolor') ? 'checked="checked"' : ''); ?> /> jednolity kolor
                                <input type="radio" value="0" name="tlo_sklepu" onclick="zmien_tlo(2)" <?php echo ((TLO_SKLEPU_RODZAJ == 'obraz') ? 'checked="checked"' : ''); ?> /> tło obrazkowe
                            </p>
                            
                            <div id="tlo_1" <?php echo ((TLO_SKLEPU_RODZAJ == 'kolor') ? '' : 'style="display:none"'); ?>>
                                <p>
                                  <label>Kolor:</label>
                                  <input name="kolor" class="color {required:false}" id="color" style="-moz-box-shadow:none" value="<?php echo TLO_SKLEPU; ?>" onchange="zmienGet(this.value,'TLO_SKLEPU')" size="8" />                    
                                </p>
                            </div>
                            
                            <div id="tlo_2" <?php echo ((TLO_SKLEPU_RODZAJ == 'obraz') ? '' : 'style="display:none"'); ?>>
                                <p>
                                  <label>Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie" size="95" value="<?php echo TLO_SKLEPU; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto','TLO_SKLEPU','<?php echo KATALOG_ZDJEC; ?>')" id="foto" />                 
                                </p> 

                                <p>
                                    <label>Powtarzanie tła:</label>
                                    <input type="radio" value="no-repeat center top" name="tlo_sklepu_powtarzanie" onclick="zmienGet(this.value,'TLO_SKLEPU_POWTARZANIE')" <?php echo ((TLO_SKLEPU_POWTARZANIE == 'no-repeat center center') ? 'checked="checked"' : ''); ?> /> bez powtarzania wyśrodkowane
                                    <input type="radio" value="repeat-x" name="tlo_sklepu_powtarzanie" onclick="zmienGet(this.value,'TLO_SKLEPU_POWTARZANIE')" <?php echo ((TLO_SKLEPU_POWTARZANIE == 'repeat-x') ? 'checked="checked"' : ''); ?> /> w poziomie
                                    <input type="radio" value="repeat-y" name="tlo_sklepu_powtarzanie" onclick="zmienGet(this.value,'TLO_SKLEPU_POWTARZANIE')" <?php echo ((TLO_SKLEPU_POWTARZANIE == 'repeat-y') ? 'checked="checked"' : ''); ?> /> w pionie
                                    <input type="radio" value="repeat" name="tlo_sklepu_powtarzanie" onclick="zmienGet(this.value,'TLO_SKLEPU_POWTARZANIE')" <?php echo ((TLO_SKLEPU_POWTARZANIE == 'repeat') ? 'checked="checked"' : ''); ?> /> w poziomie i pionie
                                </p>                
                            </div>
                            
                        </div>   

                        
                        <div id="zakl_id_2" style="display:none;">

                            <p>
                                <label>Nagłówek sklepu:</label>
                                <input type="radio" value="1" name="naglowek_sklepu" onclick="zmien_naglowek(1)" <?php echo ((NAGLOWEK_RODZAJ == 'kod') ? 'checked="checked"' : ''); ?> /> jako kod
                                <input type="radio" value="0" name="naglowek_sklepu" onclick="zmien_naglowek(2)" <?php echo ((NAGLOWEK_RODZAJ == 'obraz') ? 'checked="checked"' : ''); ?> /> obrazek
                            </p>
                            
                            <div id="naglowek_1" <?php echo ((NAGLOWEK_RODZAJ == 'kod') ? '' : 'style="display:none"'); ?>>
                                <p>
                                  <label>Kod:</label>
                                  <textarea name="kod_naglowek" id="kod_naglowek" onchange="zmienGet(this.value,'NAGLOWEK')" rows="15" cols="90"><?php echo NAGLOWEK; ?></textarea>
                                </p>
                            </div>
                            
                            <div id="naglowek_2" <?php echo ((NAGLOWEK_RODZAJ == 'obraz') ? '' : 'style="display:none"'); ?>>
                                <p>
                                  <label>Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie_naglowek" size="95" value="<?php echo NAGLOWEK; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto_naglowek','NAGLOWEK','<?php echo KATALOG_ZDJEC; ?>')" id="foto_naglowek" />                 
                                </p>              
                            </div>
                            
                        </div>         

                        
                        <div id="zakl_id_3" style="display:none;">
                        
                            <div class="wyglad_tbl">
                            
                                <div class="naglowek">Linki w górnym menu</div>

                                <div id="wyglad_gorne_menu">
                                
                                    <?php
                                    if (GORNE_MENU != '') {
                                        //
                                        $pozycje_menu = explode(',',GORNE_MENU);
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nazwaDowyswietlania = '';
                                            $edycjaElementu = '';
                                        
                                            $strona = explode(';', $pozycje_menu[$x]);
                                            
                                            switch ($strona[0]) {
                                                case "strona":
                                                    $sqls = $db->open_query("select * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.pages_id = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="stronainfo">'.$infs['pages_title'].((!empty($infs['link'])) ? ' <span>( link zewnętrzny: '.$infs['link'].' )</span>' : '<span>( link do strony informacyjnej )</span>' ).'</span>';
                                                    $edycjaElementu = '<a href="strony_informacyjne/strony_informacyjne_edytuj.php?id_poz=' . $infs['pages_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'strona';
                                                    break;
                                                case "galeria":
                                                    $sqls = $db->open_query("select * from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.id_gallery = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="galeria">'.$infs['gallery_name'].'<span>( link do galerii )</span></span>';
                                                    $edycjaElementu = '<a href="galerie/galerie_edytuj.php?id_poz=' . $infs['id_gallery'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'galeria';
                                                    break; 
                                                case "formularz":
                                                    $sqls = $db->open_query("select * from form p, form_description pd where p.id_form = pd.id_form and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.id_form = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="formularz">'.$infs['form_name'].'<span>( link do formularza )</span></span>';
                                                    $edycjaElementu = '<a href="formularze/formularze_edytuj.php?id_poz=' . $infs['id_form'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'formularz';
                                                    break; 
                                                case "kategoria":
                                                    $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="artykul_kategoria">'.$infs['categories_name'].'<span>( link do kategorii aktualności )</span></span>';
                                                    $edycjaElementu = '<a href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'kategoria';
                                                    break; 
                                                case "artykul":
                                                    $sqls = $db->open_query("select * from newsdesk n, newsdesk_description nd where n.newsdesk_id = nd.newsdesk_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.newsdesk_id = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="artykul">'.$infs['newsdesk_article_name'].'<span>( link do aktualności )</span></span>';
                                                    $edycjaElementu = '<a href="aktualnosci/aktualnosci_edytuj.php?id_poz=' . $infs['newsdesk_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'artykul';
                                                    break; 
                                                case "kategproduktow":
                                                    $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="produkt_kategoria">'.$infs['categories_name'].'<span>( link do kategorii produktów )</span></span>';
                                                    $edycjaElementu = '<a href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=' . $nr_zakladki . '"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'kategproduktow';
                                                    break;                                                     
                                                case "grupainfo":
                                                    $sqls = $db->open_query("select pg.pages_group_id,
                                                                                    pg.pages_group_code,
                                                                                    pg.pages_group_title,
                                                                                    pgd.pages_group_name
                                                                               from pages_group pg left join pages_group_description pgd on pg.pages_group_id = pgd.pages_group_id and pgd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'
                                                                              where pg.pages_group_id  = '".(int)$strona[1]."'");                                                    
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="rozwijane">'.$infs['pages_group_name'].'<span>( okno rozwijane stron informacyjnych z grupy: ' . $infs['pages_group_code'] . ' )</span></span>';
                                                    $edycjaElementu = '<a href="strony_informacyjne/strony_informacyjne_grupy_edytuj.php?id_poz=' . $infs['pages_group_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'grupainfo';
                                                    break;                                                    
                                                case "artkategorie":
                                                    $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="rozwijane">'.$infs['categories_name'].'<span>( okno rozwijane z artykułami z kategorii aktualności: ' . $infs['categories_name'] . ' )</span></span>';
                                                    $edycjaElementu = '<a href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = $strona[1].'artkategorie';
                                                    break; 
                                                case "prodkategorie":
                                                    $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$strona[1]."'");
                                                    $infs = $sqls->fetch_assoc();
                                                    $nazwaDowyswietlania = '<span class="rozwijane">'.$infs['categories_name'].'<span>( okno rozwijane z podkategoriami z kategorii produktów: ' . $infs['categories_name'] . ' )</span></span>';
                                                    $edycjaElementu = '<a href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                                                    $idDoDiva = (int)$strona[1].'prodkategorie';
                                                    break;                                                             
                                            }

                                            $db->close_query($sqls); 
                                            unset($infs);                                                
                                            ?>
                                            
                                            <div class="stala" id="gorne_menu_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','gorne_menu')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <?php echo $edycjaElementu; ?>
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_gorne_menu">
                                    <span class="dodaj" onclick="dodaj_stala('gorne_menu')" style="cursor:pointer">dodaj nową pozycję menu</span>
                                </div>

                                <div class="legenda">
                                    <span class="rozwijane"> menu rozwijane</span>
                                    <span class="stronainfo"> strona informacyjna</span>
                                    <span class="galeria"> galeria</span>
                                    <span class="formularz"> formularz</span>
                                    <span class="artykul_kategoria"> kategoria artykułów</span>
                                    
                                    <br /><br />
                                    
                                    <span class="produkt_kategoria"> kategoria produktów</span>
                                    <span class="artykul"> artykuł</span>                                   
                                </div> 

                            </div>
                            
                        </div>                         
                        
                        
                        <div id="zakl_id_4" style="display:none;">
                        
                            <div class="wyglad_tbl">
                            
                                <div class="info_lewa">
                                
                                    <div class="naglowek">Boxy w lewej kolumnie</div>

                                    <div id="wyglad_lewa">

                                        <?php
                                        // pobieranie boxow do lewej kolumny
                                        $boxy = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.box_column = 'lewa' and p.box_status = '1' order by p.box_sort, pd.box_title");
                                        
                                        if ((int)$db->ile_rekordow($boxy) == 0) { 
                                        
                                            echo '<p style="padding:10px">Brak pozycji ...</p>';
                                        
                                        } else {
                                    
                                            while ($info = $boxy->fetch_assoc()) {
                                                ?>
                                                <div class="box" id="box_<?php echo $info['box_id']; ?>">
                                                    <?php
                                                    // plik php czy strona informacyjna
                                                    if ($info['box_type'] == 'plik') { 
                                                      echo '<span class="iplik">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>'; 
                                                    }
                                                    if ($info['box_type'] == 'java') { 
                                                      echo '<span class="ikodjava">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';               
                                                    }
                                                    if ($info['box_type'] == 'strona') { 
                                                      echo '<span class="istrona">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';   
                                                    }                                              
                                                    ?>
                                                    <img class="strzalka toolTipTop" onclick="ple(<?php echo $info['box_id']; ?>,'lewa')" src="obrazki/strzalka_prawa.png" alt="Przenieś do prawej kolumny" title="Przenieś do prawej kolumny" />
                                                    <img class="skasuj toolTipTop" onclick="psk(<?php echo $info['box_id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                    <a href="wyglad/boxy_edytuj.php?id_poz=<?php echo $info['box_id']; ?>&amp;zakladka=4"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację boxu" /></a>
                                                </div>                        
                                                <?php
                                            }
                                            
                                        }
                                        
                                        $db->close_query($boxy);
                                        unset($info);
                                        ?>
                                        
                                    </div>
                                    
                                    <div class="dodaj_box">
                                        <span class="dodaj" onclick="dodaj_box('lewa','dodaj_box_lewa')" style="cursor:pointer">dodaj nowy box</span><span id="dodaj_box_lewa"></span>
                                    </div>                         

                                </div>
                                
                                <div class="info_prawa">
                                
                                    <div class="naglowek">Boxy w prawej kolumnie</div>
                                
                                    <div id="wyglad_prawa">

                                        <?php
                                        // pobieranie boxow do lewej kolumny
                                        $boxy = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.box_column = 'prawa' and p.box_status = '1' order by p.box_sort, pd.box_title");
                                        
                                        if ((int)$db->ile_rekordow($boxy) == 0) { 
                                        
                                            echo '<p style="padding:10px">Brak pozycji ...</p>';
                                        
                                        } else {
                                                                            
                                            while ($info = $boxy->fetch_assoc()) {
                                                ?>
                                                <div class="box" id="box_<?php echo $info['box_id']; ?>" style="text-align:right">
                                                    <?php
                                                    // plik php czy strona informacyjna
                                                    if ($info['box_type'] == 'plik') { 
                                                      echo '<span class="rplik">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>'; 
                                                    }
                                                    if ($info['box_type'] == 'java') { 
                                                      echo '<span class="rkodjava">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';               
                                                    }
                                                    if ($info['box_type'] == 'strona') { 
                                                      echo '<span class="rstrona">'.$info['box_title'].'<br /><strong>' . $info['box_description'] . BoxyModuly::PolozenieBoxu($info['box_localization']) . '</strong></span>';   
                                                    }                                               
                                                    ?>
                                                    <img class="strzalka toolTipTop" style="float:left" onclick="ple(<?php echo $info['box_id']; ?>,'prawa')" src="obrazki/strzalka_lewa.png" alt="Przenieś do lewej kolumny" title="Przenieś do lewej kolumny" />
                                                    <img class="skasuj toolTipTop" style="float:left" onclick="psk(<?php echo $info['box_id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                    <a href="wyglad/boxy_edytuj.php?id_poz=<?php echo $info['box_id']; ?>&amp;zakladka=4"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację boxu" /></a>
                                                </div>                        
                                                <?php
                                            }
                                            
                                        }
                                        
                                        $db->close_query($boxy);
                                        unset($info);
                                        ?>                    

                                    </div>
                                    
                                    <div class="dodaj_box">
                                        <span class="dodaj" onclick="dodaj_box('prawa','dodaj_box_prawa')" style="cursor:pointer">dodaj nowy box</span><span id="dodaj_box_prawa"></span>
                                    </div>                         
                                    
                                </div>
                                
                                <div class="cl"></div>
                                
                                <div class="legenda">
                                    <span class="plik"> box jest plikiem php</span>
                                    <span class="strona"> box wyświetla zawartość strony informacyjnej</span>
                                    <span class="kodjava"> box wyświetla wynik działania skryptu</span>
                                </div>                                
                                
                            </div>
                            
                        </div>     

                        
                        <div id="zakl_id_5" style="display:none;">
                        
                            <div class="wyglad_tbl">
                            
                                <div class="naglowek">Moduły wyświetlane nad częścią główną sklepu</div>

                                <div id="wyglad_srodek_gora">

                                    <?php
                                    // pobieranie modulow
                                    $moduly = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.modul_status = '1' and p.modul_position = 'gora' order by p.modul_sort, pd.modul_title");
                                    
                                    if ((int)$db->ile_rekordow($moduly) == 0) { 
                                    
                                        echo '<p style="padding:10px">Brak pozycji ...</p>';
                                    
                                    } else {
                                    
                                        while ($info = $moduly->fetch_assoc()) {
                                            ?>
                                            <div class="box" id="modul_<?php echo $info['modul_id']; ?>">
                                                <?php
                                                // plik php czy strona informacyjna
                                                if ($info['modul_type'] == 'plik') { 
                                                  echo '<span class="iplik">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>'; 
                                                }
                                                if ($info['modul_type'] == 'java') { 
                                                  echo '<span class="ikodjava">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';               
                                                }
                                                if ($info['modul_type'] == 'strona') { 
                                                  echo '<span class="istrona">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                }                                               
                                                ?>
                                                <img class="skasuj toolTipTop" onclick="msk(<?php echo $info['modul_id']; ?>,'gora')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <a href="wyglad/srodek_edytuj.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację modułu" /></a>
                                            </div>                        
                                            <?php
                                        }
                                        
                                    }
                                    
                                    $db->close_query($moduly);
                                    unset($info);
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_modul">
                                    <span class="dodaj" onclick="dodaj_modul('gora','dodaj_modul_gora')" style="cursor:pointer">dodaj nowy moduł</span><span id="dodaj_modul_gora"></span>
                                </div>   

                                <br />
                            
                                <div class="naglowek">Moduły w części głównej sklepu</div>
                                
                                <div class="wyglad_srodek_boxy lf"></div>

                                <div id="wyglad_srodek_srodek">

                                    <?php
                                    // pobieranie modulow
                                    $moduly = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.modul_status = '1' and p.modul_position = 'srodek' order by p.modul_sort, pd.modul_title");
                                    
                                    if ((int)$db->ile_rekordow($moduly) == 0) { 
                                    
                                        echo '<p style="padding:10px">Brak pozycji ...</p>';
                                    
                                    } else {
                                    
                                        while ($info = $moduly->fetch_assoc()) {
                                            ?>
                                            <div class="box" id="modul_<?php echo $info['modul_id']; ?>">
                                                <?php
                                                // plik php czy strona informacyjna
                                                if ($info['modul_type'] == 'plik') { 
                                                  echo '<span class="iplik">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>'; 
                                                }
                                                if ($info['modul_type'] == 'java') { 
                                                  echo '<span class="ikodjava">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';               
                                                }
                                                if ($info['modul_type'] == 'strona') { 
                                                  echo '<span class="istrona">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                }                                               
                                                ?>
                                                <img class="skasuj toolTipTop" onclick="msk(<?php echo $info['modul_id']; ?>,'srodek')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <a href="wyglad/srodek_edytuj.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację modułu" /></a>
                                            </div>                        
                                            <?php
                                        }
                                        
                                    }
                                    
                                    $db->close_query($moduly);
                                    unset($info);
                                    ?>
                                    
                                </div>
                                
                                <div class="wyglad_srodek_boxy rg"></div>                                
                                
                                <div style="clear:both"></div>
                                
                                <div class="dodaj_modul" style="margin-left:85px">
                                    <span class="dodaj" onclick="dodaj_modul('srodek','dodaj_modul_srodek')" style="cursor:pointer">dodaj nowy moduł</span><span id="dodaj_modul_srodek"></span>
                                </div>
                                
                                <br />
                                
                                <div class="naglowek">Moduły wyświetlane pod częścią główną sklepu</div>

                                <div id="wyglad_srodek_dol">

                                    <?php
                                    // pobieranie modulow
                                    $moduly = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.modul_status = '1' and p.modul_position = 'dol' order by p.modul_sort, pd.modul_title");
                                    
                                    if ((int)$db->ile_rekordow($moduly) == 0) { 
                                    
                                        echo '<p style="padding:10px">Brak pozycji ...</p>';
                                    
                                    } else {
                                    
                                        while ($info = $moduly->fetch_assoc()) {
                                            ?>
                                            <div class="box" id="modul_<?php echo $info['modul_id']; ?>">
                                                <?php
                                                // plik php czy strona informacyjna
                                                if ($info['modul_type'] == 'plik') { 
                                                  echo '<span class="iplik">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>'; 
                                                }
                                                if ($info['modul_type'] == 'java') { 
                                                  echo '<span class="ikodjava">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';               
                                                }
                                                if ($info['modul_type'] == 'strona') { 
                                                  echo '<span class="istrona">'.$info['modul_title'].'<br /><strong>' . $info['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
                                                }                                               
                                                ?>
                                                <img class="skasuj toolTipTop" onclick="msk(<?php echo $info['modul_id']; ?>,'dol')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <a href="wyglad/srodek_edytuj.php?id_poz=<?php echo $info['modul_id']; ?>&amp;zakladka=5"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację modułu" /></a>
                                            </div>                        
                                            <?php
                                        }
                                        
                                    }
                                    
                                    $db->close_query($moduly);
                                    unset($info);
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_modul">
                                    <span class="dodaj" onclick="dodaj_modul('dol','dodaj_modul_dol')" style="cursor:pointer">dodaj nowy moduł</span><span id="dodaj_modul_dol"></span>
                                </div>                                   

                                <div class="legenda">
                                    <span class="plik"> moduł jest plikiem php</span>
                                    <span class="strona"> moduł wyświetla zawartość strony informacyjnej</span>
                                    <span class="kodjava"> moduł wyświetla wynik działania skryptu</span>
                                </div>                                 

                            </div>
                            
                        </div>        


                        <div id="zakl_id_6" style="display:none;">
                        
                            <div class="wyglad_tbl">
                            
                                <div class="naglowek">Linki w dolnym menu</div>

                                <div id="wyglad_dolne_menu">
                                
                                    <?php
                                    $pozycje_menu = explode(',',DOLNE_MENU);
                                    //
                                    if (DOLNE_MENU != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 6;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="stala" id="dolne_menu_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','dolne_menu')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <?php echo $edycjaElementu; ?>
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_dolne_menu">
                                    <span class="dodaj" onclick="dodaj_stala('dolne_menu')" style="cursor:pointer">dodaj nową pozycję menu</span>
                                </div> 

                                <div class="legenda">
                                    <span class="stronainfo"> strona informacyjna</span>
                                    <span class="galeria"> galeria</span>
                                    <span class="formularz"> formularz</span>
                                    <span class="artykul_kategoria"> kategoria artykułów</span>
                                    <span class="produkt_kategoria"> kategoria produktów</span>
                                    <span class="artykul"> artykuł</span>         
                                </div>                                 

                            </div>
                            
                        </div>
                        
                        
                        <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>
                        
                        <div id="zakl_id_7" style="display:none;">
                        
                            <div class="wyglad_tbl" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                    <?php
                                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    
                                        // pobieranie danych jezykowych
                                        $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id  where w.translate_constant = 'STOPKA_NAGLOWEK_PIERWSZA' and t.language_id = '" .$ile_jezykow[$w]['id']."'";
                                        $sqls = $db->open_query($zapytanie_jezyk);
                                        $nazwa = $sqls->fetch_assoc();   
                                        ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <p>
                                                <label>Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_PIERWSZA',<?php echo $ile_jezykow[$w]['id']; ?>)" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" />
                                            </p> 
                                                        
                                        </div>
                                        <?php  

                                        $db->close_query($sqls);
                                        unset($nazwa);
                                    }                    
                                    ?>                      
                                </div> 
                                
                                
                                <div class="naglowek" style="margin-top:20px">Linki w pierwszej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_pierwsza">
                                
                                    <?php
                                    $pozycje_menu = explode(',',STOPKA_PIERWSZA);
                                    //
                                    if (STOPKA_PIERWSZA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 7;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="stala" id="stopka_pierwsza_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_pierwsza')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <?php echo $edycjaElementu; ?>
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_stopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_pierwsza')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div> 

                                <div class="legenda">
                                    <span class="stronainfo"> strona informacyjna</span>
                                    <span class="galeria"> galeria</span>
                                    <span class="formularz"> formularz</span>
                                    <span class="artykul_kategoria"> kategoria artykułów</span>
                                    <span class="produkt_kategoria"> kategoria produktów</span>
                                    <span class="artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script type="text/javascript">
                            //<![CDATA[
                            gold_tabs('0');
                            //]]>
                            </script>                             
                            
                        </div>   


                        <div id="zakl_id_8" style="display:none;">
                        
                            <div class="wyglad_tbl" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 100, $cw = count($ile_jezykow); $w < $cw + 100; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-100]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                    <?php
                                    for ($w = 100, $cw = count($ile_jezykow); $w < $cw + 100; $w++) {
                                    
                                        // pobieranie danych jezykowych
                                        $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id  where w.translate_constant = 'STOPKA_NAGLOWEK_DRUGA' and t.language_id = '" .$ile_jezykow[$w-100]['id']."'";
                                        $sqls = $db->open_query($zapytanie_jezyk);
                                        $nazwa = $sqls->fetch_assoc();   
                                        ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <p>
                                                <label>Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_DRUGA',<?php echo $ile_jezykow[$w-100]['id']; ?>)" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" />
                                            </p> 
                                                        
                                        </div>
                                        <?php  

                                        $db->close_query($sqls);
                                        unset($nazwa);
                                    }                    
                                    ?>                      
                                </div> 
                                
                                
                                <div class="naglowek" style="margin-top:20px">Linki w drugiej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_druga">
                                
                                    <?php
                                    $pozycje_menu = explode(',',STOPKA_DRUGA);
                                    //
                                    if (STOPKA_DRUGA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 8;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="stala" id="stopka_druga_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_druga')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <?php echo $edycjaElementu; ?>
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_stopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_druga')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>   

                                <div class="legenda">
                                    <span class="stronainfo"> strona informacyjna</span>
                                    <span class="galeria"> galeria</span>
                                    <span class="formularz"> formularz</span>
                                    <span class="artykul_kategoria"> kategoria artykułów</span>
                                    <span class="produkt_kategoria"> kategoria produktów</span>
                                    <span class="artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script type="text/javascript">
                            //<![CDATA[
                            gold_tabs('100');
                            //]]>
                            </script>                             
                            
                        </div>    


                        <div id="zakl_id_9" style="display:none;">
                        
                            <div class="wyglad_tbl" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 200, $cw = count($ile_jezykow); $w < $cw + 200; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-200]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                    <?php
                                    for ($w = 200, $cw = count($ile_jezykow); $w < $cw + 200; $w++) {
                                    
                                        // pobieranie danych jezykowych
                                        $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_NAGLOWEK_TRZECIA' and t.language_id = '" .$ile_jezykow[$w-200]['id']."'";
                                        $sqls = $db->open_query($zapytanie_jezyk);
                                        $nazwa = $sqls->fetch_assoc();   
                                        ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <p>
                                                <label>Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_TRZECIA',<?php echo $ile_jezykow[$w-200]['id']; ?>)" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" />
                                            </p> 
                                                        
                                        </div>
                                        <?php  

                                        $db->close_query($sqls);
                                        unset($nazwa);
                                    }                    
                                    ?>                      
                                </div> 
                                
                                
                                <div class="naglowek" style="margin-top:20px">Linki w trzeciej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_trzecia">
                                
                                    <?php
                                    $pozycje_menu = explode(',',STOPKA_TRZECIA);
                                    //
                                    if (STOPKA_TRZECIA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 9;
                                            include('wyglad_menu_pozycje.php');                                              
                                            ?>
                                            
                                            <div class="stala" id="stopka_trzecia_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_trzecia')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <?php echo $edycjaElementu; ?>
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_stopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_trzecia')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>       

                                <div class="legenda">
                                    <span class="stronainfo"> strona informacyjna</span>
                                    <span class="galeria"> galeria</span>
                                    <span class="formularz"> formularz</span>
                                    <span class="artykul_kategoria"> kategoria artykułów</span>
                                    <span class="produkt_kategoria"> kategoria produktów</span>
                                    <span class="artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script type="text/javascript">
                            //<![CDATA[
                            gold_tabs('200');
                            //]]>
                            </script>                             
                            
                        </div>                         


                        <div id="zakl_id_10" style="display:none;">
                        
                            <div class="wyglad_tbl" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 300, $cw = count($ile_jezykow); $w < $cw + 300; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-300]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                    <?php
                                    for ($w = 300, $cw = count($ile_jezykow); $w < $cw + 300; $w++) {
                                    
                                        // pobieranie danych jezykowych
                                        $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_NAGLOWEK_CZWARTA' and t.language_id = '" .$ile_jezykow[$w-300]['id']."'";
                                        $sqls = $db->open_query($zapytanie_jezyk);
                                        $nazwa = $sqls->fetch_assoc();   
                                        ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <p>
                                                <label>Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_CZWARTA',<?php echo $ile_jezykow[$w-300]['id']; ?>)" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" />
                                            </p> 
                                                        
                                        </div>
                                        <?php  

                                        $db->close_query($sqls);
                                        unset($nazwa);
                                    }                    
                                    ?>                      
                                </div> 
                                
                                
                                <div class="naglowek" style="margin-top:20px">Linki w czwartej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_czwarta">
                                
                                    <?php
                                    $pozycje_menu = explode(',',STOPKA_CZWARTA);
                                    //
                                    if (STOPKA_CZWARTA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 10;
                                            include('wyglad_menu_pozycje.php');                                                
                                            ?>
                                            
                                            <div class="stala" id="stopka_czwarta_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_czwarta')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <?php echo $edycjaElementu; ?>
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_stopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_czwarta')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>

                                <div class="legenda">
                                    <span class="stronainfo"> strona informacyjna</span>
                                    <span class="galeria"> galeria</span>
                                    <span class="formularz"> formularz</span>
                                    <span class="artykul_kategoria"> kategoria artykułów</span>
                                    <span class="produkt_kategoria"> kategoria produktów</span>
                                    <span class="artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script type="text/javascript">
                            //<![CDATA[
                            gold_tabs('300');
                            //]]>
                            </script>                             
                            
                        </div> 


                        <div id="zakl_id_11" style="display:none;">
                        
                            <div class="wyglad_tbl" style="margin-top:0px">
                        
                                <div class="info_tab">
                                <?php
                                for ($w = 400, $cw = count($ile_jezykow); $w < $cw + 400; $w++) {
                                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\')">'.$ile_jezykow[$w-400]['text'].'</span>';
                                }                    
                                ?>                   
                                </div>
                                
                                <div style="clear:both"></div>
                                
                                <div class="info_tab_content" style="margin-left:0px; margin-right:0px">
                                    <?php
                                    for ($w = 400, $cw = count($ile_jezykow); $w < $cw + 400; $w++) {
                                    
                                        // pobieranie danych jezykowych
                                        $zapytanie_jezyk = "select distinct * from translate_constant w left join translate_value t on w.translate_constant_id = t.translate_constant_id where w.translate_constant = 'STOPKA_NAGLOWEK_PIATA' and t.language_id = '" .$ile_jezykow[$w-400]['id']."'";
                                        $sqls = $db->open_query($zapytanie_jezyk);
                                        $nazwa = $sqls->fetch_assoc();   
                                        ?> 

                                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                        
                                            <p>
                                                <label>Nazwa nagłówka stopki:</label>   
                                                <input type="text" onchange="zmienGetJezyk(this.value,'STOPKA_NAGLOWEK_PIATA',<?php echo $ile_jezykow[$w-400]['id']; ?>)" name="nazwa_<?php echo $w; ?>" size="45" value="<?php echo $nazwa['translate_value']; ?>" />
                                            </p> 
                                                        
                                        </div>
                                        <?php  

                                        $db->close_query($sqls);
                                        unset($nazwa);
                                    }                    
                                    ?>                      
                                </div> 
                                
                                
                                <div class="naglowek" style="margin-top:20px">Linki w piątej kolumnie stopki</div>
                                
                                <div id="wyglad_stopka_piata">
                                
                                    <?php
                                    $pozycje_menu = explode(',',STOPKA_PIATA);
                                    //
                                    if (STOPKA_PIATA != '') {
                                        //
                                        for ($x = 0, $c = count($pozycje_menu); $x < $c; $x++) {
                                        
                                            $nr_zakladki = 11;
                                            include('wyglad_menu_pozycje.php');
                                            ?>
                                            
                                            <div class="stala" id="stopka_piata_<?php echo $idDoDiva; ?>">
                                                <?php echo $nazwaDowyswietlania; ?>
                                                <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','stopka_piata')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                                                <?php echo $edycjaElementu; ?>
                                            </div>                        
                                            <?php
                                            
                                            unset($nazwaDowyswietlania, $idDoDiva, $edycjaElementu, $nr_zakladki); 
                                        }
                                        //
                                    } else {
                                        
                                      echo '<p style="padding:10px">Brak pozycji ...</p>';
                                      
                                    }
                                    ?>
                                    
                                </div>
                                
                                <div class="dodaj_stopka">
                                    <span class="dodaj" onclick="dodaj_stala('stopka_piata')" style="cursor:pointer">dodaj nową pozycję do kolumny</span>
                                </div>       

                                <div class="legenda">
                                    <span class="stronainfo"> strona informacyjna</span>
                                    <span class="galeria"> galeria</span>
                                    <span class="formularz"> formularz</span>
                                    <span class="artykul_kategoria"> kategoria artykułów</span>
                                    <span class="produkt_kategoria"> kategoria produktów</span>
                                    <span class="artykul"> artykuł</span>         
                                </div>                                 
                            
                            </div>
                            
                            <script type="text/javascript">
                            //<![CDATA[
                            gold_tabs('400');
                            //]]>
                            </script>                             
                            
                        </div>
                        
                        
                        <div id="zakl_id_12" class="SzablonMobilny" style="display:none;">
                        
                            <div class="maleInfo" style="margin:0px 0px 5px 25px">Ustawienia dotyczą wyglądu szablonów mobilnych (widocznych na urządzenia przenośnych).</div>
                        
                            <p>
                                <label>Czy ma być używany szablon mobilny ?</label>
                                <input type="radio" value="tak" name="szablon_mobilny" onchange="zmienGet(this.value,'SZABLON_MOBILNY')" <?php echo ((SZABLON_MOBILNY == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="szablon_mobilny" onchange="zmienGet(this.value,'SZABLON_MOBILNY')" <?php echo ((SZABLON_MOBILNY == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p> 

                            <p>
                                <label>Czy sklep ma rozpoznawać czy jest wyświetlany na urządzeniu mobilnym i przełączać szablon na mobilny ?</label>
                                <input type="radio" value="tak" name="mobilny_rozpoznawanie" onchange="zmienGet(this.value,'MOBILNY_ROZPOZNAWANIE')" <?php echo ((MOBILNY_ROZPOZNAWANIE == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_rozpoznawanie" onchange="zmienGet(this.value,'MOBILNY_ROZPOZNAWANIE')" <?php echo ((MOBILNY_ROZPOZNAWANIE == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>                            

                            <p>
                                <label>Nagłówek dla szablonu mobilnego (ścieżka obrazka):</label>           
                                <input type="text" name="zdjecie_naglowek_mobilny" size="55" value="<?php echo NAGLOWEK_MOBILNY; ?>" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto_naglowek_mobilny','NAGLOWEK_MOBILNY','<?php echo KATALOG_ZDJEC; ?>')" id="foto_naglowek_mobilny" />                 
                            </p> 

                            <p>
                                <label>Czy wyświetlać górne menu z linkami ?</label>
                                <input type="radio" value="tak" name="mobilny_gorne_menu" onchange="zmienGet(this.value,'MOBILNY_GORNE_MENU')" <?php echo ((MOBILNY_GORNE_MENU == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_gorne_menu" onchange="zmienGet(this.value,'MOBILNY_GORNE_MENU')" <?php echo ((MOBILNY_GORNE_MENU == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>  

                            <p>
                                <label>Czy wyświetlać box z producentami ?</label>
                                <input type="radio" value="tak" name="mobilny_box_produceci" onchange="zmienGet(this.value,'MOBILNY_BOX_PRODUCENCI')" <?php echo ((MOBILNY_BOX_PRODUCENCI == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_box_produceci" onchange="zmienGet(this.value,'MOBILNY_BOX_PRODUCENCI')" <?php echo ((MOBILNY_BOX_PRODUCENCI == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p> 

                            <p>
                                <label>Czy wyświetlać moduł ostatnio dodanych aktualności na stronie głównej ?</label>
                                <input type="radio" value="tak" name="mobilny_modul_aktualnosci" onchange="zmienGet(this.value,'MOBILNY_MODUL_AKTUALNOSCI')" <?php echo ((MOBILNY_MODUL_AKTUALNOSCI == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_modul_aktualnosci" onchange="zmienGet(this.value,'MOBILNY_MODUL_AKTUALNOSCI')" <?php echo ((MOBILNY_MODUL_AKTUALNOSCI == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p> 

                            <p>
                                <label>Czy wyświetlać moduł hitów na stronie głównej ?</label>
                                <input type="radio" value="tak" name="mobilny_modul_hity" onchange="zmienGet(this.value,'MOBILNY_MODUL_HITY')" <?php echo ((MOBILNY_MODUL_HITY == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_modul_hity" onchange="zmienGet(this.value,'MOBILNY_MODUL_HITY')" <?php echo ((MOBILNY_MODUL_HITY == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>   
                            
                            <p>
                                <label>Ile produktów wyświetlać w module hitów ?</label>
                                <?php
                                $tablica = array('1','2','4','6','8');                                        
                                echo Funkcje::RozwijaneMenu('ilosc_hitow', $tablica, MOBILNY_ILE_HITOW, 'onchange="zmienGet(this.value,\'MOBILNY_ILE_HITOW\')"'); 
                                unset($tablica);
                                ?>                                
                            </p>                            

                            <p>
                                <label>Czy wyświetlać moduł nowości na stronie głównej ?</label>
                                <input type="radio" value="tak" name="mobilny_modul_nowosci" onchange="zmienGet(this.value,'MOBILNY_MODUL_NOWOSCI')" <?php echo ((MOBILNY_MODUL_NOWOSCI == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_modul_nowosci" onchange="zmienGet(this.value,'MOBILNY_MODUL_NOWOSCI')" <?php echo ((MOBILNY_MODUL_NOWOSCI == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>
                            
                            <p>
                                <label>Ile produktów wyświetlać w module nowości ?</label>
                                <?php
                                $tablica = array('1','2','4','6','8');                                        
                                echo Funkcje::RozwijaneMenu('ilosc_nowosci', $tablica, MOBILNY_ILE_NOWOSCI, 'onchange="zmienGet(this.value,\'MOBILNY_ILE_NOWOSCI\')"'); 
                                unset($tablica);
                                ?>                                
                            </p>                                  
                            
                            <p>
                                <label>Czy wyświetlać moduł promocji na stronie głównej ?</label>
                                <input type="radio" value="tak" name="mobilny_modul_promocje" onchange="zmienGet(this.value,'MOBILNY_MODUL_PROMOCJE')" <?php echo ((MOBILNY_MODUL_PROMOCJE == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_modul_promocje" onchange="zmienGet(this.value,'MOBILNY_MODUL_PROMOCJE')" <?php echo ((MOBILNY_MODUL_PROMOCJE == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p> 
                            
                            <p>
                                <label>Ile produktów wyświetlać w module promocji ?</label>
                                <?php
                                $tablica = array('1','2','4','6','8');                                        
                                echo Funkcje::RozwijaneMenu('ilosc_promocji', $tablica, MOBILNY_ILE_PROMOCJI, 'onchange="zmienGet(this.value,\'MOBILNY_ILE_PROMOCJI\')"'); 
                                unset($tablica);
                                ?>                                
                            </p>                             

                            <p>
                                <label>Czy wyświetlać moduł produktów polecanych na stronie głównej ?</label>
                                <input type="radio" value="tak" name="mobilny_modul_polecane" onchange="zmienGet(this.value,'MOBILNY_MODUL_POLECANE')" <?php echo ((MOBILNY_MODUL_POLECANE == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_modul_polecane" onchange="zmienGet(this.value,'MOBILNY_MODUL_POLECANE')" <?php echo ((MOBILNY_MODUL_POLECANE == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p> 

                            <p>
                                <label>Ile produktów wyświetlać w module produktów polecanych ?</label>
                                <?php
                                $tablica = array('1','2','4','6','8');                                        
                                echo Funkcje::RozwijaneMenu('ilosc_polecane', $tablica, MOBILNY_ILE_POLECANE, 'onchange="zmienGet(this.value,\'MOBILNY_ILE_POLECANE\')"'); 
                                unset($tablica);
                                ?>                                
                            </p>                              

                            <p>
                                <label>Czy wyświetlać dolne menu z linkami ?</label>
                                <input type="radio" value="tak" name="mobilny_dolne_menu" onchange="zmienGet(this.value,'MOBILNY_DOLNE_MENU')" <?php echo ((MOBILNY_DOLNE_MENU == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_dolne_menu" onchange="zmienGet(this.value,'MOBILNY_DOLNE_MENU')" <?php echo ((MOBILNY_DOLNE_MENU == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>

                            <p>
                                <label>Czy formularz zapisania do newslettera ?</label>
                                <input type="radio" value="tak" name="mobilny_newsletter" onchange="zmienGet(this.value,'MOBILNY_NEWSLETTER')" <?php echo ((MOBILNY_NEWSLETTER == 'tak') ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="nie" name="mobilny_newsletter" onchange="zmienGet(this.value,'MOBILNY_NEWSLETTER')" <?php echo ((MOBILNY_NEWSLETTER == 'nie') ? 'checked="checked"' : ''); ?> /> nie
                            </p>                             
                            
                        </div>                         
                        
                    </td>
                
                </tr></table>
                
                <script type="text/javascript">
                //<![CDATA[
                infoSzablon('<?php echo str_replace('.', '_', DOMYSLNY_SZABLON); ?>',0);
                <?php
                $zakladka = '0';
                if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                ?>
                gold_tabs_horiz(<?php echo $zakladka; ?>);
                //]]>
                </script>            
            
          </div>

          </form>

    </div>    

    <form action="wyglad/wyglad.php" method="post" class="cmxform">
    
        <div id="ekr_edit">
            <div id="edit_tlo"></div>
            <div id="edytuj_stale">
                <div id="edytuj_okno">
                    <img class="zamknij_box" onclick="zamknij_edycje()" src="obrazki/zamknij.png" alt="Zamknij okno" title="Zamknij okno" />
                    <br />
                    <div id="glowne_okno_edycji"></div>
                </div>
            </div>
        </div>

    </form>
                
    <?php include('stopka.inc.php'); 

}
?>
