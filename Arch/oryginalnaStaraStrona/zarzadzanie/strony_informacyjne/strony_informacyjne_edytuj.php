<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_strony = $filtr->process($_POST['id']);
        
        // jaki link zew
        $plik = '';
        if (!empty($_POST['link_tresc'])) {
            $plik = $filtr->process($_POST['link_tresc']);
        }
        if (!empty($_POST['plik'])) {
            $plik = $filtr->process($_POST['plik']);
        }
    
        $pola = array(
                array('link',(((int)$_POST['link'] == 0) ? '' : $plik)),
                array('pages_group',$filtr->process($_POST['grupa'])),
                array('nofollow',(int)$_POST['nofollow']),
                array('sort_order',( $_POST['sort'] == '' ? '1' : $filtr->process($_POST['sort'])))
        );
        
        // grupy klientow zapisze tylko jak nie jest wyswietlanie w boxie czy module
        if ( (int)$_POST['box_modul'] == 0 ) {
            $pola[] = array('pages_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0));
        }        
        
        $sql = $db->update_query('pages' , $pola, " pages_id = '".$id_strony."'");        
        unset($pola, $plik);
        
        // kasuje rekordy w tablicy
        $db->delete_query('pages_description' , " pages_id = '".$id_strony."'");         
        
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
                        array('pages_id',$id_strony),
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
                        array('pages_id',$id_strony),
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

        if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
          
            Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
          
          } else {
          
            Funkcje::PrzekierowanieURL('strony_informacyjne.php?id_poz='.$id_strony);
            
        }        

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="strony_informacyjne/strony_informacyjne_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from pages where pages_id = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>             
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id" value="<?php echo $info['pages_id']; ?>" />
                
                <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                <?php } ?>                

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
                        $('.edytor').slideUp('fast'); 
                        $('#link_adres').slideDown();
                       } else { 
                        $('#zakl_link_1').css('display','block');
                        $('#zakl_link_2').css('display','block');
                        $('#link_adres').slideUp();                          
                        $('.edytor').slideDown();                       
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
                //]]>
                </script> 
                
                <table style="width:100%"><tr>
                
                    <td id="lewe_zakladki" style="vertical-align:top">
                        <a href="javascript:gold_tabs_horiz('0','0','opis_')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>                           
                        <a href="javascript:gold_tabs_horiz('1','100','opis_krotki_')" class="a_href_info_zakl" id="zakl_link_1">Tekst skrócony</a>
                        <?php if ($info['pages_modul'] == 0) { ?>
                        <a href="javascript:gold_tabs_horiz('2','200')" class="a_href_info_zakl" id="zakl_link_2">Pozycjonowanie</a>
                        <?php } ?>
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
                              
                              echo Funkcje::RozwijaneMenu('grupa', $tablica, $info['pages_group'], 'style="width:400px" class="toolTipTop" title="Wybierz grupę, jeżeli strona my być wyświetlana w niestandardowym miejscu. W innym przypadku można ją przypisać w definiowaniu wyglądu sklepu."'); 
                              ?>
                            </p>
                            
                            <?php if ($info['pages_modul'] == 1) { ?>
                            
                                <?php
                                $zapytanie_id = "SELECT modul_id FROM theme_modules WHERE modul_pages_id = '" . $info['pages_id'] . "'";
                                $sqls = $db->open_query($zapytanie_id);
                                $infs = $sqls->fetch_assoc();                           
                                ?>
                                
                                <div class="wyswietlanie_modul">
                                    Treść tej strony jest wyświetlana w module środkowym <br />
                                    <a href="/zarzadzanie/wyglad/srodek_edytuj.php?id_modul=<?php echo $infs['modul_id']; ?>&amp;id_poz=<?php echo $info['pages_id']; ?>">przejdź do edycji modułu</a>
                                </div>
                                
                                <?php
                                $db->close_query($sqls); 
                                unset($zapytanie_id, $infs); 
                                ?>
                            
                            <?php } ?>                            
                            
                            <?php if ($info['pages_modul'] == 2) { ?>
                            
                                <?php
                                $zapytanie_id = "SELECT box_id FROM theme_box WHERE box_pages_id = '" . $info['pages_id'] . "'";
                                $sqls = $db->open_query($zapytanie_id);
                                $infs = $sqls->fetch_assoc();                           
                                ?>
                                
                                <div class="wyswietlanie_box">
                                    Treść tej strony jest wyświetlana w boxie <br />
                                    <a href="/zarzadzanie/wyglad/boxy_edytuj.php?id_box=<?php echo $infs['box_id']; ?>&amp;id_poz=<?php echo $info['pages_id']; ?>">przejdź do edycji boxu</a>
                                </div>
                                
                                <?php
                                $db->close_query($sqls); 
                                unset($zapytanie_id, $infs); 
                                ?>
                            
                            <?php } ?>

                            <p <?php echo (($info['pages_modul'] != 0) ? 'style="display:none"' : ''); ?>>
                                <label>Kolejność wyswietlania:</label>
                                <input type="text" name="sort" size="5" value="<?php echo $info['sort_order']; ?>" id="sort" />
                            </p>
                            
                            <div <?php echo (($info['pages_modul'] != 0) ? 'style="display:none"' : ''); ?>>
                            
                                <table style="margin:10px">
                                    <tr>
                                        <td><label>Widoczna dla grupy klientów:</label></td>
                                        <td>
                                            <?php                        
                                            $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                            foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                                echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" ' . ((in_array($GrupaKlienta['id'], explode(',', $info['pages_customers_group_id']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                                            }               
                                            unset($TablicaGrupKlientow);
                                            ?>
                                        </td>
                                    </tr>
                                </table> 
                            
                                <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to strona będzie widoczna dla wszystkich klientów.</div>                         
                                
                            </div>

                            <p <?php echo (($info['pages_modul'] != 0) ? 'style="display:none"' : ''); ?>>
                                <label>Czy strona będzie linkiem zewnętrznym:</label>
                                <input type="radio" value="1" name="link" onclick="zmien_link(1)" <?php echo ((!empty($info['link'])) ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="0" name="link" onclick="zmien_link(0)" <?php echo ((empty($info['link'])) ? 'checked="checked"' : ''); ?> /> nie
                            </p>   

                            <div id="link_adres" style="display:none">
                            
                                <p>
                                    <label>Link wewnętrzny sklepu:</label>
                                    <?php
                                    $tablica = array();
                                    $tablica[] = array('id' => 0, 'text' => '... wybierz plik ...');
                                    $Bylo = false;
                                    //
                                    $linia = file("strony_informacyjne/pliki_wewnetrzne.ddt");
                                    for ($i = 0; $i < count($linia); $i++) {        
                                        $wartosc = explode(';',$linia[$i]);
                                        //
                                        if (count($wartosc) > 1) {
                                            //
                                            $tablica[] = array('id' => trim($wartosc[1]), 'text' => trim($wartosc[0]));
                                            //
                                            if (trim($wartosc[1]) == $info['link']) {
                                                $Bylo = true;
                                            }
                                        }
                                    }   
                                    echo Funkcje::RozwijaneMenu('plik', $tablica, $info['link'], ' onchange="linkz(2)" id="plik2" style="width:330px"');
                                    ?>
                                </p>
                                
                                <p>
                                    <label>Link zewnętrzny z http:</label>
                                    <input type="text" name="link_tresc" onchange="linkz(1)" id="plik1" value="<?php echo (($Bylo == false) ? $info['link'] : ''); ?>" size="90" />
                                </p>  
                                
                            </div>
                            
                            <p>
                                <label>Stosować przy tej stronie atrybut <b>nofollow</b> ?</label>
                                <input type="radio" value="1" name="nofollow" class="toolTipTop" title="Strona NIE będzie indeksowana przez wyszukiwarki." <?php echo ((!empty($info['nofollow'])) ? 'checked="checked"' : ''); ?> /> tak
                                <input type="radio" value="0" name="nofollow" class="toolTipTop" title="Strona będzie indeksowana przez wyszukiwarki." <?php echo ((empty($info['nofollow'])) ? 'checked="checked"' : ''); ?> /> nie
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
                                
                                    // pobieranie danych jezykowych
                                    $zapytanie_jezyk = "select distinct * from pages_description where pages_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                    
                                        <p>
                                           <?php if ($w == '0') { ?>
                                            <label class="required">Tytuł strony:</label>
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($nazwa['pages_title']); ?>" id="nazwa_0" />
                                           <?php } else { ?>
                                            <label>Tytuł strony:</label>   
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($nazwa['pages_title']); ?>" />
                                           <?php } ?>
                                        </p>                                      
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_<?php echo $w; ?>" name="opis_<?php echo $w; ?>"><?php echo $nazwa['pages_text']; ?></textarea>
                                        </div>                            

                                    </div>
                                    <?php 

                                    $db->close_query($sqls);      
                                    unset($nazwa, $zapytanie_jezyk);
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
                                
                                    // pobieranie danych jezykowych
                                    $zapytanie_jezyk = "select distinct * from pages_description where pages_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 100); ?>" style="display:none;">
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_krotki_<?php echo ($w + 100); ?>" name="opis_krotki_<?php echo $w; ?>"><?php echo $nazwa['pages_short_text']; ?></textarea>
                                        </div>                            

                                    </div>
                                    <?php    

                                    $db->close_query($sqls);      
                                    unset($nazwa, $zapytanie_jezyk);                                    
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
                                
                                    // pobieranie danych jezykowych
                                    $zapytanie_jezyk = "select distinct * from pages_description where pages_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 200); ?>" style="display:none;">                        

                                        <p>
                                          <label>Meta Tagi - Tytuł:</label>
                                          <textarea name="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $nazwa['meta_title_tag']; ?></textarea>
                                        </p> 
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['meta_title_tag'])); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                        </p>                                        
                                        
                                        <p>
                                          <label>Meta Tagi - Opis:</label>
                                          <textarea name="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $nazwa['meta_desc_tag']; ?></textarea>
                                        </p>   
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['meta_desc_tag'])); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                        </p>                                          
                                        
                                        <p>
                                          <label>Meta Tagi - Słowa kluczowe:</label>
                                          <textarea name="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $nazwa['meta_keywords_tag']; ?></textarea>
                                        </p>   

                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['meta_keywords_tag'])); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                        </p>                                          
                            
                                    </div>
                                    <?php                    

                                    $db->close_query($sqls);      
                                    unset($nazwa, $zapytanie_jezyk);                                    
                                }            
                                ?>                      
                            </div>
                            
                        </div>                        
                        
                    </td>
                
                </tr></table>
                
                <script type="text/javascript">
                //<![CDATA[
                gold_tabs_horiz('0','0','opis_');
                
                <?php 
                if (!empty($info['link'])) {
                    echo 'zmien_link(1);';
                } else {
                    echo 'zmien_link(0);';
                } ?>
                //]]>
                </script>            
            
            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>
          
          <div class="przyciski_dolne">
          
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
          
              <?php 
              // jezeli jest get zakladka wraca do ustawien wygladu
              if (isset($_GET['zakladka']) ) { ?>
              
              <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
              
              <?php } else { ?>
              
              <button type="button" class="przyciskNon" onclick="cofnij('strony_informacyjne','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','strony_informacyjne');">Powrót</button> 
              
              <?php } ?>
  
          </div>           

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
