<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_artykulu = $filtr->process($_POST['id']);
        
        $pola = array(
                array('newsdesk_date_added',date('Y-m-d', strtotime($filtr->process($_POST['data_dodania'])))),
                array('newsdesk_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0)));
        
        $sql = $db->update_query('newsdesk' , $pola, "newsdesk_id = '".$id_artykulu."'");   
        
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('newsdesk_description' , " newsdesk_id = '".$id_artykulu."'");    
        $db->delete_query('newsdesk_to_categories' , " newsdesk_id = '".$id_artykulu."'");         
        
        $pola = array(
                array('newsdesk_id',$id_artykulu),
                array('categories_id',$filtr->process($_POST['kategoria'])));
        
        $sql = $db->insert_query('newsdesk_to_categories' , $pola);
        
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
                    array('newsdesk_id',$id_artykulu),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('newsdesk_article_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('newsdesk_article_short_text',$filtr->process($_POST['opis_krotki_'.$w])),
                    array('newsdesk_article_viewed',$filtr->process($_POST['licznik_odwiedzin_'.$w])),
                    array('newsdesk_article_description',$filtr->process($_POST['opis_'.$w])),
                    array('newsdesk_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                    array('newsdesk_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('newsdesk_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));           
            $sql = $db->insert_query('newsdesk_description' , $pola);
            unset($pola);
            
        }

        if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
          
            Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
          
          } else {
          
            Funkcje::PrzekierowanieURL('aktualnosci.php?id_poz='.$id_artykulu);
            
        }   
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="aktualnosci/aktualnosci_edytuj.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from newsdesk where newsdesk_id = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>             
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id" value="<?php echo $info['newsdesk_id']; ?>" />
                
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
                
                $('input.datepicker').Zebra_DatePicker({
                   format: 'd-m-Y',
                   inside: false,
                   readonly_element: false
                });    
                
                });            
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
                                <label>Przypisany do kategorii:</label>
                                <?php
                                // do jakiej kategorii nalezy
                                $zapytanie_kategoria = "select distinct * from newsdesk_to_categories where newsdesk_id = '".$filtr->process((int)$_GET['id_poz'])."'";
                                $sqls = $db->open_query($zapytanie_kategoria);
                                $kategoriaId = $sqls->fetch_assoc(); 
                                $db->close_query($sqls);
                                    
                                $sqls = $db->open_query('select distinct * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '" order by n.sort_order, nd.categories_name ');  
                                //
                                $tab_tmp = array();
                                $tab_tmp[] = array('id' => 0, 'text' => 'bez kategorii ...');      
                                //
                                while ($kategorie = $sqls->fetch_assoc()) {
                                    $tab_tmp[] = array('id' => $kategorie['categories_id'],
                                                       'text' => $kategorie['categories_name']);                                
                                
                                }
                                $db->close_query($sqls);
                                //
                                echo Funkcje::RozwijaneMenu('kategoria', $tab_tmp, $kategoriaId['categories_id']); 
                                //
                                unset($tab_tmp, $kategoriaId);
                                ?>
                            </p> 
                            
                            <p>
                                <label>Data dodania:</label>
                                <input type="text" name="data_dodania" value="<?php echo date('d-m-Y',strtotime($info['newsdesk_date_added'])); ?>" size="20"  class="datepicker" />                                             
                            </p>

                            <table style="margin:10px">
                                <tr>
                                    <td><label>Widoczny dla grupy klientów:</label></td>
                                    <td>
                                        <?php                        
                                        $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                        foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                            echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" ' . ((in_array($GrupaKlienta['id'], explode(',', $info['newsdesk_customers_group_id']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                                        }               
                                        unset($TablicaGrupKlientow);
                                        ?>
                                    </td>
                                </tr>
                            </table> 
                            
                            <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to artykuł będzie widoczny dla wszystkich klientów.</div>                           

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
                                    $zapytanie_jezyk = "select distinct * from newsdesk_description where newsdesk_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                    
                                        <p>
                                           <?php if ($w == '0') { ?>
                                            <label class="required">Tytuł artykułu:</label>
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($nazwa['newsdesk_article_name']); ?>" id="nazwa_0" />
                                           <?php } else { ?>
                                            <label>Tytuł artykułuy:</label>   
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($nazwa['newsdesk_article_name']); ?>" />
                                           <?php } ?>
                                        </p>                                      
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_<?php echo $w; ?>" name="opis_<?php echo $w; ?>"><?php echo $nazwa['newsdesk_article_description']; ?></textarea>
                                          <input type="hidden" name="licznik_odwiedzin_<?php echo $w; ?>" value="<?php echo $nazwa['newsdesk_article_viewed']; ?>" />
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
                                    $zapytanie_jezyk = "select distinct * from newsdesk_description where newsdesk_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 100); ?>" style="display:none;">
                                    
                                        <div class="edytor">
                                          <textarea cols="50" rows="30" id="opis_krotki_<?php echo ($w + 100); ?>" name="opis_krotki_<?php echo $w; ?>"><?php echo $nazwa['newsdesk_article_short_text']; ?></textarea>
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
                                    $zapytanie_jezyk = "select distinct * from newsdesk_description where newsdesk_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                                    $sqls = $db->open_query($zapytanie_jezyk);
                                    $nazwa = $sqls->fetch_assoc();   
                                    
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo ($w + 200); ?>" style="display:none;">                        

                                        <p>
                                          <label>Meta Tagi - Tytuł:</label>
                                          <textarea name="tytul_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $nazwa['newsdesk_meta_title_tag']; ?></textarea>
                                        </p> 
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['newsdesk_meta_title_tag'])); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                        </p>                                        
                                        
                                        <p>
                                          <label>Meta Tagi - Opis:</label>
                                          <textarea name="opis_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $nazwa['newsdesk_meta_desc_tag']; ?></textarea>
                                        </p>  

                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['newsdesk_meta_desc_tag'])); ?></span>
                                          zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                        </p>                                          
                                        
                                        <p>
                                          <label>Meta Tagi - Słowa kluczowe:</label>
                                          <textarea name="slowa_meta_<?php echo $w; ?>" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" rows="4" cols="70"><?php echo $nazwa['newsdesk_meta_keywords_tag']; ?></textarea>
                                        </p>    
                                        
                                        <p class="LicznikMeta">
                                          <label></label>
                                          Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($nazwa['newsdesk_meta_keywords_tag'])); ?></span>
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
              
              <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>  
              
              <?php } ?>

          </div>           

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
