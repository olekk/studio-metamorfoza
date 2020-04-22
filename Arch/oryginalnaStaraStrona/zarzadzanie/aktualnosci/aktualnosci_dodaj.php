<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('newsdesk_status','1'),
                array('newsdesk_date_added',date('Y-m-d', strtotime($filtr->process($_POST['data_dodania'])))),
                array('newsdesk_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0)));
        
        $sql = $db->insert_query('newsdesk' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $pola = array(
                array('newsdesk_id',$id_dodanej_pozycji),
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
                    array('newsdesk_id',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('newsdesk_article_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('newsdesk_article_short_text',$filtr->process($_POST['opis_krotki_'.$w])),
                    array('newsdesk_article_description',$filtr->process($_POST['opis_'.$w])),
                    array('newsdesk_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                    array('newsdesk_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('newsdesk_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));           
            $sql = $db->insert_query('newsdesk_description' , $pola);
            unset($pola);
            
        }

        unset($ile_jezykow);

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('aktualnosci.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('aktualnosci.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="aktualnosci/aktualnosci_dodaj.php" method="post" id="poForm" class="cmxform"> 
          
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
                                echo Funkcje::RozwijaneMenu('kategoria', $tab_tmp); 
                                //
                                unset($tab_tmp);
                                ?>
                            </p> 
                            
                            <p>
                                <label>Data dodania:</label>
                                <input type="text" name="data_dodania" value="<?php echo date('d-m-Y',time()); ?>" size="20"  class="datepicker" />                                             
                            </p>  

                            <table style="margin:10px">
                                <tr>
                                    <td><label>Widoczny dla grupy klientów:</label></td>
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
                                    ?>
                                    
                                    <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                                    
                                        <p>
                                           <?php if ($w == '0') { ?>
                                            <label class="required">Tytuł artykułu:</label>
                                            <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                                           <?php } else { ?>
                                            <label>Tytuł artykułu:</label>   
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
              <button type="button" class="przyciskNon" onclick="cofnij('aktualnosci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
          </div>           

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>
