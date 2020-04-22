<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $pola = array(
                array('gallery_status','1'),
                array('gallery_width_image',$filtr->process($_POST['szerokosc'])),
                array('gallery_height_image',$filtr->process($_POST['wysokosc'])),
                array('gallery_cols',$filtr->process($_POST['kolumny'])),
                array('gallery_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0)));
        
        $sql = $db->insert_query('gallery' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
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
                    array('id_gallery',$id_dodanej_pozycji),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('gallery_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('gallery_description',$filtr->process($_POST['edytor_'.$w])),  
                    array('gallery_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                    array('gallery_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('gallery_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));                        
            $sql = $db->insert_query('gallery_description' , $pola);
            unset($pola);
            
        }

        // dodawanie pol galerie
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
        
            for ($q = 1; $q <= (int)($_POST['ile_pol_'.$w]); $q++) {
                
                if (!empty($_POST['zdjecie_'.$q.'_'.$w])) {
                    $pola = array(
                            array('id_gallery',$id_dodanej_pozycji),
                            array('language_id',$ile_jezykow[$w]['id']),
                            array('gallery_image',$filtr->process($_POST['zdjecie_'.$q.'_'.$w])),
                            array('gallery_image_sort',$filtr->process($_POST['sort_'.$q.'_'.$w])),
                            array('gallery_image_description',$filtr->process($_POST['opis_zdjecia_'.$q.'_'.$w])),
                            array('gallery_image_alt',$filtr->process($_POST['alt_'.$q.'_'.$w])));

                    $sql = $db->insert_query('gallery_image' , $pola);
                    unset($pola);
                }
                
            }
        
        }
        
        unset($ile_jezykow);    

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('galerie.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('galerie.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="galerie/galerie_dodaj.php" method="post" id="pogallery" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">    
            
                <input type="hidden" name="akcja" value="zapisz" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <!-- Skrypt do walidacji formularza -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                $("#pogallery").validate({
                  rules: {
                    nazwa_0: {
                      required: true
                    },
                    szerokosc: {
                      range: [10, 1000],
                      number: true,
                      required: true
                    },
                    wysokosc: {
                      range: [10, 1000],
                      number: true,
                      required: true
                    },
                    kolumny: {
                      range: [1, 100],
                      number: true,
                      required: true
                    }                     
                  },
                  messages: {
                    nazwa_0: {
                      required: "Pole jest wymagane"
                    },
                    wysokosc: {
                      required: "Pole jest wymagane",
                      range: "Wartość musi być wieksza od 10"
                    },
                    szeroksc: {
                      required: "Pole jest wymagane",
                      range: "Wartość musi być wieksza od 10"
                    },     
                    kolumny: {
                      required: "Pole jest wymagane",
                      range: "Wartość musi być wieksza od 0 i mniejsza od 100"
                    }                    
                  }
                });
                });                    

                function dodaj_galerie(id_jezyk) {
                    ile_pol = parseInt($("#ile_pol_"+id_jezyk).val()) + 1;
                    //
                    $('#wyniki_'+id_jezyk).append('<div id="wyniki_'+id_jezyk+'_'+ile_pol+'"></div>');
                    $('#wyniki_'+id_jezyk+'_'+ile_pol).css('display','none');
                    //
                    $.get('ajax/galeria.php?tok=<?php echo Sesje::Token(); ?>', { id: ile_pol, id_jezyk: id_jezyk }, function(data) {
                        $('#wyniki_'+id_jezyk+'_'+ile_pol).html(data);
                        $("#ile_pol_"+id_jezyk).val(ile_pol);
                        
                        $('#wyniki_'+id_jezyk+'_'+ile_pol).slideDown("fast");

                        $("gallery input:radio").css('border','0px');
                        $("gallery input:checkbox").css('border','0px');

                        usunPlikZdjecie();
                    });
                }                
                //]]>
                </script>        

                <p>
                  <label class="required">Szerokość zdjęć w px:</label>
                  <input type="text" name="szerokosc" id="szerokosc" size="5" value="" class="calkowita" />
                </p>

                <p>
                  <label class="required">Wysokość zdjęć w px:</label>
                  <input type="text" name="wysokosc" id="wysokosc" size="5" value="" class="calkowita" />
                </p>                
                
                <p>
                  <label class="required">W ilu kolumnach mają wyświetlać się zdjęcia:</label>
                  <input type="text" name="kolumny" id="kolumny" size="5" value="" class="calkowita" />
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
                
                <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to galeria będzie widoczna dla wszystkich klientów.</div>                                          

                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\')">'.$ile_jezykow[$w]['text'].'</span>';
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
                                <label class="required">Nazwa galerii:</label>
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" id="nazwa_0" />
                               <?php } else { ?>
                                <label>Nazwa galerii:</label>   
                                <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="" />
                               <?php } ?>
                            </p>          

                            <p>
                              <label>Meta Tagi - Tytuł:</label>
                              <input type="text" name="tytul_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="" />
                            </p> 
                            
                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                            </p>                              
                            
                            <p>
                              <label>Meta Tagi - Opis:</label>
                              <input type="text" name="opis_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="" />
                            </p> 

                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                            </p>                             
                            
                            <p>
                              <label>Meta Tagi - Słowa kluczowe:</label>
                              <input type="text" name="slowa_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="" />
                            </p>      

                            <p class="LicznikMeta">
                              <label></label>
                              Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>">0</span>
                              zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                            </p>                            
                            
                            <div class="edytor" style="margin-bottom:10px">
                              <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"></textarea>
                            </div>                                 

                            <div id="wyniki_<?php echo $w; ?>" class="polFor">
                            
                                <div class="nagl_formi">Zdjęcie galerii nr <span>1</span></div>
                                
                                <p>
                                  <label>Ścieżka zdjęcia:</label>           
                                  <input type="text" name="zdjecie_1_<?php echo $w; ?>" size="95" value="" class="toolTipTopText obrazek" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki zdjęć" ondblclick="openFileBrowser('foto_1_<?php echo $w; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="foto_1_<?php echo $w; ?>" />                 
                                  <span class="usun_zdjecie toolTipTopText" data="foto_<?php echo $q; ?>_<?php echo $w; ?>" title="Usuń przypisane zdjęcie"></span>
                                </p>      

                                <div id="divfoto_1_<?php echo $w; ?>" style="padding-left:10px; display:none">
                                    <label>Zdjęcie:</label>
                                    <span id="fofoto_1_<?php echo $w; ?>">
                                        <span class="zdjecie_tbl">
                                            <img src="obrazki/_loader_small.gif" alt="" />
                                        </span>
                                    </span> 
                                </div>                                  
                            
                                <p>
                                    <label>Opis zdjęcia:</label>
                                    <textarea name="opis_zdjecia_1_<?php echo $w; ?>" rows="5" cols="50"></textarea>
                                </p>
                                
                                <p>
                                    <label>Opis znacznika ALT:</label>
                                    <input type="text" value="" name="alt_1_<?php echo $w; ?>" size="60" />
                                </p>
                                
                                <p>
                                    <label>Kolejność wyświetlania w galerii:</label>  
                                    <input class="calkowita" type="text" value="" name="sort_1_<?php echo $w; ?>" size="4" />
                                </p>                                 
                                
                            </div>      

                            <div style="padding:10px;padding-top:20px;">
                                <span class="dodaj" onclick="dodaj_galerie(<?php echo $w; ?>)" style="cursor:pointer">dodaj nowe zdjęcie</span>
                            </div> 

                            <input value="1" type="hidden" name="ile_pol_<?php echo $w; ?>" id="ile_pol_<?php echo $w; ?>" />                            
                            
                        </div>
                        <?php                    
                    }                    
                    ?>                      
                </div>
                
                <script type="text/javascript">
                //<![CDATA[
                gold_tabs('0','edytor_');
                //]]>
                </script> 
                
            </div>
            
            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('galerie','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>    
            </div>            
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>