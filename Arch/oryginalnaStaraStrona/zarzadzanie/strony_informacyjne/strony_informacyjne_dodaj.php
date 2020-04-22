<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        // jaki link zew
        $plik = '';
        if (!empty($_POST['link_tresc'])) {
            $plik = $filtr->process($_POST['link_tresc']);
        }
        if (!empty($_POST['plik'])) {
            $plik = $filtr->process($_POST['plik']);
        }    
    
        $pola = array(
                array('status','1'),
                array('pages_group',$filtr->process($_POST['grupa'])),
                array('link',(((int)$_POST['link'] == 0) ? '' : $plik)),
                array('nofollow',(int)$_POST['nofollow']),
                array('sort_order',( $_POST['sort'] == '' ? '1' : $filtr->process($_POST['sort']))));
                
        // grupy klientow zapisze tylko jak nie jest wyswietlanie w boxie czy module
        if ( (int)$_POST['box_modul'] == 0 ) {
            $pola[] = array('pages_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0));
        }
                
        $pola[] = array('pages_modul',(int)$_POST['box_modul']);
        
        $sql = $db->insert_query('pages' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola, $plik);
        
        if ((int)$_POST['link'] == 0) {
        
            $ile_jezykow = Funkcje::TablicaJezykow();
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                // jezeli nazwa w innym jezyku nie jest wypelniona
                if ( $w > 0 ) {
                    if (empty($_POST['nazwa_'.$w])) {
                        $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                    }
                }
                //            
                $pola = array(
                        array('pages_id',$id_dodanej_pozycji),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('pages_title',$filtr->process($_POST['nazwa_'.$w])),
                        array('pages_short_text',$filtr->process($_POST['opis_krotki_'.$w])),
                        array('pages_text',$filtr->process($_POST['opis_'.$w])),
                        array('meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                        array('meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                        array('meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));           
                $sql = $db->insert_query('pages_description' , $pola);
                unset($pola);
                
            }

            unset($ile_jezykow);

        } else {
        
            $ile_jezykow = Funkcje::TablicaJezykow();
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                // jezeli nazwa w innym jezyku nie jest wypelniona
                if ( $w > 0 ) {
                    if (empty($_POST['nazwa_'.$w])) {
                        $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                    }
                }
                //            
                $pola = array(
                        array('pages_id',$id_dodanej_pozycji),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('pages_title',$filtr->process($_POST['nazwa_'.$w])));           
                $sql = $db->insert_query('pages_description' , $pola);
                unset($pola);
                
            }

            unset($ile_jezykow);        
        
        }
        
        if ( isset($_GET['grupa']) && ( $_GET['grupa'] != $filtr->process($_POST['grupa']) ) ) {
             $_GET['grupa'] = $filtr->process($_POST['grupa']);
        }         

        // jezeli ma byc dodany do modulu
        if ( (int)$_POST['box_modul'] == 1 ) {
            //
            $pola = array(
                    array('modul_status','0'),
                    array('modul_type','strona'),
                    array('modul_pages_id',$id_dodanej_pozycji),
                    array('modul_header',$filtr->process($_POST['naglowek'])),
                    array('modul_description',$filtr->process($_POST['opis'])),
                    array('modul_display',$filtr->process($_POST['wyswietla'])),
                    array('modul_localization',(int)$_POST['polozenie']),
                    array('modul_rwd',(int)$_POST['rwd']),
                    array('modul_rwd_resolution',(int)$_POST['rwd_mala_rozdzielczosc']));                     
                              
            $sql = $db->insert_query('theme_modules' , $pola);
            $id_dodanej_pozycji_modul_box = $db->last_id_query();
            
            unset($pola);
            
            $ile_jezykow = Funkcje::TablicaJezykow();
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                // jezeli nazwa w innym jezyku nie jest wypelniona
                if ( $w > 0 ) {
                    if (empty($_POST['nazwa_'.$w])) {
                        $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                    }
                }
                //            
                $pola = array(
                        array('modul_id',$id_dodanej_pozycji_modul_box),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('modul_title',$filtr->process($_POST['nazwa_'.$w])));           
                $sql = $db->insert_query('theme_modules_description' , $pola);
                unset($pola);
            }            
            //
        }
        // jezeli ma byc dodany do boxu
        if ( (int)$_POST['box_modul'] == 2 ) {
            //
            $pola = array(
                    array('box_status','0'),
                    array('box_type','strona'),
                    array('box_pages_id',$id_dodanej_pozycji),
                    array('box_header',$filtr->process($_POST['naglowek'])),
                    array('box_description',$filtr->process($_POST['opis'])),
                    array('box_display',$filtr->process($_POST['wyswietla'])),
                    array('box_localization',$filtr->process((int)$_POST['polozenie'])),
                    array('box_rwd',(int)$_POST['rwd']),
                    array('box_rwd_resolution',(int)$_POST['rwd_mala_rozdzielczosc']));                      
                              
            $sql = $db->insert_query('theme_box' , $pola);
            $id_dodanej_pozycji_modul_box = $db->last_id_query();
            
            unset($pola);
            
            $ile_jezykow = Funkcje::TablicaJezykow();
            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                // jezeli nazwa w innym jezyku nie jest wypelniona
                if ( $w > 0 ) {
                    if (empty($_POST['nazwa_'.$w])) {
                        $_POST['nazwa_'.$w] = $_POST['nazwa_0'];
                    }
                }
                //            
                $pola = array(
                        array('box_id',$id_dodanej_pozycji_modul_box),
                        array('language_id',$ile_jezykow[$w]['id']),
                        array('box_title',$filtr->process($_POST['nazwa_'.$w])));           
                $sql = $db->insert_query('theme_box_description' , $pola);
                unset($pola);
            }            
            //
        }        
        //        

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('strony_informacyjne.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('strony_informacyjne.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="strony_informacyjne/strony_informacyjne_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
                <input type="hidden" name="akcja" value="zapisz" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <!-- Skrypt do walidacji -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                $("#poForm").validate({
                  rules: {
                    nazwa_0: {
                      required: true
                    }                    
                  },
                  messages: {
                    nazwa_0: {
                      required: "Pole jest wymagane"
                    }                   
                  }
                });
                });
                
                function zmien_link(id) {
                    if (id == 1) {
                        $('#zakl_link_1').css('display','none'); 
                        $('#zakl_link_2').css('display','none'); 
                        $('.edytor').slideUp(); 
                        $('#tresc_zew').slideUp(); 
                        $('#link_adres').slideDown();
                       } else {
                        $('#zakl_link_1').css('display','block'); 
                        $('#zakl_link_2').css('display','block');
                        $('#link_adres').slideUp();    
                        $('#tresc_zew').slideDown();                        
                        $('.edytor').slideDown();                       
                    }     
                }      

                function zmien_box_modul(opcja) {
                    if (opcja != 0) {                        
                        $('#zakl_link_2').css('display','none'); 
                        $('.tytul_zmiana').html('Tytuł strony / nazwa modułu / boxu:');
                        $('#link_zew').slideUp(); 
                        $('#link_adres').slideUp();   
                        $('#link_sort').slideUp();
                        $('#tresc_zew_opcje').slideDown();  
                       } else {
                        $('#zakl_link_2').css('display','block');
                        $('.tytul_zmiana').html('Tytuł strony:');
                        $('#link_adres').slideUp();    
                        $('#link_zew').slideDown();   
                        $('#link_sort').slideDown();
                        $('#tresc_zew_opcje').slideUp();                     
                    }                   
                }      

                function linkz(pole) {
                    if (pole == 1) {
                        $('#plik2').val(0);
                    }
                    if (pole == 2) {
                        $('#plik1').val('');
                    }
                }      
                function zmien_rwd(id) {
                    if (id == 0) {
                        $('#wyglad_rwd').slideUp();
                       } else {
                        $('#wyglad_rwd').slideDown();
                    }
                }                      
                //]]>
                </script> 
                
                <table style="width:100%"><tr>
                
                    <td id="lewe_zakladki" style="vertical-align:top">
                        <a href="javascript:gold_tabs_horiz('0','0','opis_')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>   
                        <a href="javascript:gold_tabs_horiz('1','100','opis_krotki_')" class="a_href_info_zakl" id="zakl_link_1">Tekst skrócony</a>
                        <a href="javascript:gold_tabs_horiz('2','200')" class="a_href_info_zakl" id="zakl_link_2">Pozycjonowanie</a>
                    </td>

                    <td id="prawa_strona" style="vertical-align:top">
                    
                        <div id="zakl_id_0" style="display:none;">

                            <p>
                              <label>Grupa:</label>             
                              <?php
                              $zapytanie_tmp = "SELECT * FROM pages_group ORDER BY pages_group_code ASC";
                              $sqls = $db->open_query($zapytanie_tmp);
                              //
                              $tablica = array();
                              $tablica[] = array('id' => '', 'text' => 'Strona nie przypisana do żadnej grupy');
                              while ($infs = $sqls->fetch_assoc()) { 
                                $tablica[] = array('id' => $infs['pages_group_code'], 'text' => $infs['pages_group_code'] . ' - ' . $infs['pages_group_title']);
                              }
                              $db->close_query($sqls); 
                              unset($zapytanie_tmp, $infs);                   
                              
                              echo Funkcje::RozwijaneMenu('grupa', $tablica, '', 'style="width:400px" class="toolTipTop" title="Wybierz grupę, jeżeli strona my być wyświetlana w niestandardowym miejscu. W innym przypadku można ją przypisać w definiowaniu wyglądu sklepu."'); 
                              ?>
                            </p>
                            
                            <div id="link_sort">

                                <p>
                                    <label>Kolejność wyswietlania:</label>
                                    <input type="text" name="sort" size="5" value="1" id="sort" />
                                </p>
                                
                                <table style="margin:10px">
                                    <tr>
                                        <td><label>Widoczna dla grupy klientów:</label></td>
                                        <td>
                                            <?php                        
                                            $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                            foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                                echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" /> ' . $GrupaKlienta['text'] . '<br />';
                                            }               
                                            unset($TablicaGrupKlientow);
                                            ?>
                                        </td>
                                    </tr>
                                </table> 
                                
                                <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to strona będzie widoczna dla wszystkich klientów.</div>
                                
                            </div>
                            
                            <div id="link_zew">

                                <p>
                                    <label>Czy strona będzie linkiem zewnętrznym:</label>
                                    <input type="radio" value="1" name="link" onclick="zmien_link(1)" /> tak
                                    <input type="radio" value="0" name="link" onclick="zmien_link(0)" checked="checked" /> nie
                                </p> 
                                
                            </div>

                            <div id="tresc_zew">
                            
                                <p>
                                    <label>Czy wyświetlać treść strony informacyjnej w boxie lub module środkowym ?</label>
                                    <input type="radio" value="0" name="box_modul" onclick="zmien_box_modul(0)" <?php echo (($info['pages_modul'] == 0) ? 'checked="checked"' : ''); ?> /> nie
                                    <input type="radio" value="2" name="box_modul" onclick="zmien_box_modul(1)" <?php echo (($info['pages_modul'] == 2) ? 'checked="checked"' : ''); ?> /> tak w boxie
                                    <input type="radio" value="1" name="box_modul" onclick="zmien_box_modul(2)" <?php echo (($info['pages_modul'] == 1) ? 'checked="checked"' : ''); ?> /> tak w module środkowym
                                    <input type="hidden" value="<?php echo $info['pages_modul']; ?>" name="poprzedni_box_modul" />
                                </p>

                            </div>
                            
                            <div id="tresc_zew_opcje" style="display:none">
                            
                                <p>
                                    <label>Nagłówek modułu / boxu:</label>
                                    <input type="radio" value="1" name="naglowek" class="toolTipTop" title="W module / boxie będzie wyświetał się nagłówek z nazwą modułu / boxu" checked="checked" /> tak
                                    <input type="radio" value="0" name="naglowek" class="toolTipTop" title="W module / boxie nie będzie się wyświetał nagłówek z nazwą modułu / boxu - tylko sama treść" /> nie
                                </p> 

                                <p>
                                    <label>Wyświetlanie modułu / boxu:</label>
                                    <input type="radio" value="1" name="polozenie" class="toolTipTop" title="Moduł / box będzie wyświetlany na wszystkich stronach" /> wszystkie strony
                                    <input type="radio" value="3" name="polozenie" class="toolTipTop" title="Moduł / box będzie wyświetlany tylko na podstronach (bez strony głównej)" /> tylko podstrony
                                    <input type="radio" value="2" name="polozenie" class="toolTipTop" title="Moduł / box będzie wyświetlany tylko na stronie głównej sklepu" checked="checked" /> tylko strona główna
                                </p>
                                
                                <p>
                                    <label>Opis modułu:</label>
                                    <textarea name="opis" rows="5" cols="70" class="toolTipTopText" title="Opis co będzie wyświetlał moduł / box - informacja tylko dla administratora sklepu"></textarea>
                                </p>

                                <p>
                                    <label>Co wyświetla ?</label>
                                    <input name="wyswietla" type="text" size="40" value="" class="toolTipTopText" title="Co będzie wyświetlał moduł / box - informacja tylko dla administratora sklepu" />
                                </p>    

                                <p>
                                    <label>Czy moduł / box ma się wyświetlać w wersji RWD ?</label>
                                    <input type="radio" value="0" name="rwd" class="toolTipTop" onclick="zmien_rwd(0)" title="Box jest przystosowany do wyświetlania w wersji RWD" /> nie
                                    <input type="radio" value="1" name="rwd" class="toolTipTop" onclick="zmien_rwd(1)" title="Box nie jest przystosowany do wyświetlania w wersji RWD" checked="checked" /> tak
                                </p>    

                                <div id="wyglad_rwd">
                                
                                    <p>
                                        <label>Czy zmieniać wygląd modułu / boxu przy małych rozdzielczościach ?</label>
                                        <input type="radio" value="0" name="rwd_mala_rozdzielczosc" class="toolTipTop" title="Box będzie widoczny przy małych rozdzielczościach ekranu" /> bez zmian
                                        <input type="radio" value="1" name="rwd_mala_rozdzielczosc" class="toolTipTop" title="Box nie będzie widoczny przy małych rozdzielczościach ekranu" checked="checked" /> ma być niewidoczny
                                    </p>

                                </div>                                 

                            </div>                            

                            <div id="link_adres" style="display:none">
                            
                                <p>
                                    <label>Link wewnętrzny sklepu:</label>
                                    <?php
                                    $tablica = array();
                                    $tablica[] = array('id' => 0, 'text' => '... wybierz plik ...');
                                    //
                                    $linia = file("strony_informacyjne/pliki_wewnetrzne.ddt");
                                    for ($i = 0; $i < count($linia); $i++) {        
                                        $wartosc = explode(';',$linia[$i]);
                                        //
                                        if (count($wartosc) > 1) {
                                            //
                                            $tablica[] = array('id' => trim($wartosc[1]), 'text' => trim($wartosc[0]));
                                            //
                                        }
                                    }   
                                    echo Funkcje::RozwijaneMenu('plik', $tablica, '', ' onchange="linkz(2)" id="plik2" style="width:330px"');
                                    ?>
                                </p>
                                
                                <p>
                                    <label>Link zewnętrzny z http:</label>
                                    <input type="text" name="link_tresc" onchange="linkz(1)" id="plik1" value="" size="90" />
                                </p>  
                                
                            </div>                            

                            <p>
                                <label>Stosować przy tej stronie atrybut <b>nofollow</b> ?</label>
                                <input type="radio" value="1" name="nofollow" class="toolTipTop" title="Strona NIE będzie indeksowana przez wyszukiwarki." /> tak
                                <input type="radio" value="0" name="nofollow" class="toolTipTop" title="Strona będzie indeksowana przez wyszukiwarki." checked="checked" /> nie
                            </p>                            
                            
                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'opis_\',760,400)">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                    
                                        <p>
                                           <?php if ($w == '0') { ?>
                                            <label class="required tytul_zmiana">Tytuł strony:</label>
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                                           <?php } else { ?>
                                            <label class="tytul_zmiana">Tytuł strony:</label>   
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" />
                                           <?php } ?>
                                        </p>                                      
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_<?php echo $w; ?>" name="opis_<?php echo $w; ?>"></textarea>
                                        </div>                            

                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>
                            
                        <div id="zakl_id_1" style="display:none;">

                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.($w+100).'" class="a_href_info_tab" onclick="gold_tabs(\''.($w+100).'\',\'opis_krotki_\',760,400)">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 100); ?>" style="display:none;">
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_krotki_<?php echo ($w + 100); ?>" name="opis_krotki_<?php echo $w; ?>"></textarea>
                                        </div>                            

                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>                        
                        
                        <div id="zakl_id_2" style="display:none;">
                        
                            <div class="info_tab">
                            <?php
                            for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                echo '<span id="link_'.($w+200).'" class="a_href_info_tab" onclick="gold_tabs(\''.($w+200).'\')">'.$ile_jezykow[$w]['text'].'</span>';
                            }                    
                            ?>                   
                            </div>
                            
                            <div style="clear:both"></div>
                            
                            <div class="info_tab_content">
                                <?php
                                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 200); ?>" style="display:none;">                        

                                        <p>
                                          <label>Meta Tagi - Tytuł:</label>
                                          <textarea name="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p> 
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                        </p>                                           
                                        
                                        <p>
                                          <label>Meta Tagi - Opis:</label>
                                          <textarea name="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p> 

                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                        </p>                                          
                                        
                                        <p>
                                          <label>Meta Tagi - Słowa kluczowe:</label>
                                          <textarea name="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"></textarea>
                                        </p>    
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                        </p>                                           
                            
                                    </div>
                                    <?php                    
                                }                    
                                ?>                      
                            </div>
                            
                        </div>                        
                        
                    </td>
                
                </tr></table>
                
                <script type="text/javascript">
                //<![CDATA[
                gold_tabs_horiz('0','0','opis_');
                //]]>
                </script>            
            
          </div>
             
          <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
          </div>           

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
