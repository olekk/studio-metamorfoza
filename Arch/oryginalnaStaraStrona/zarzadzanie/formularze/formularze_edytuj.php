<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
    
        $id_formularza = $filtr->process($_POST['id']);
        
        $pola = array(
                array('form_customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0)),
                array('form_captcha',$_POST['captcha'])
        );
        
        $sql = $db->update_query('form' , $pola, " id_form = '".$id_formularza."'");  
        
        unset($pola);
        
        // kasuje rekordy w tablicy
        $db->delete_query('form_description' , " id_form = '".$id_formularza."'");   
        $db->delete_query('form_field' , " id_form = '".$id_formularza."'");          
        
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
                    array('id_form',$id_formularza),
                    array('language_id',$ile_jezykow[$w]['id']),
                    array('form_name',$filtr->process($_POST['nazwa_'.$w])),
                    array('form_title_email',$filtr->process($_POST['tytul_'.$w])),
                    array('form_text_email',$filtr->process($_POST['opis_mail_'.$w])),
                    array('template_email_id',$filtr->process($_POST['szablon_'.$w])),
                    array('form_description',$filtr->process($_POST['edytor_'.$w])),
                    array('form_meta_title_tag',$filtr->process($_POST['tytul_meta_'.$w])),      
                    array('form_meta_desc_tag',$filtr->process($_POST['opis_meta_'.$w])),
                    array('form_meta_keywords_tag',$filtr->process($_POST['slowa_meta_'.$w])));                      

            // dodawanie emaili i opisow emaili
            $sc_licz = 1;
            for ($sd = 1; $sd < 6; $sd++) {
                //
                if (trim($filtr->process($_POST['mail_'.$w.'_'.$sd])) != '') {
                    $pola[] = array('form_email_' . $sc_licz,$filtr->process($_POST['mail_'.$w.'_'.$sd]));
                    $pola[] = array('form_email_name_' . $sc_licz,$filtr->process($_POST['mail_nazwa_'.$w.'_'.$sd]));
                    $sc_licz++;
                }
                //
            }                    
                    
            $sql = $db->insert_query('form_description' , $pola);
            unset($pola);
            
        }

        // dodawanie pol formularze
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
        
            for ($q = 1; $q <= (int)($_POST['ile_pol_'.$w]); $q++) {
                
                if (!empty($_POST['nazwa_pola_'.$q.'_'.$w])) {
                    $pola = array(
                            array('id_form',$id_formularza),
                            array('language_id',$ile_jezykow[$w]['id']),
                            array('form_field_sort',$filtr->process($_POST['sort_'.$q.'_'.$w])),
                            array('form_field_name',$filtr->process($_POST['nazwa_pola_'.$q.'_'.$w])),
                            array('form_field_typ',$filtr->process($_POST['typ_pola_'.$q.'_'.$w])));
                            
                    // jezeli pole nie jest file to jest wymagalnosc pola
                    if ((int)$_POST['typ_pola_'.$q.'_'.$w] != 5) {
                        $pola[] = array('form_field_required',(int)$_POST['wymagalnosc_'.$q.'_'.$w]);
                    }

                    // jezeli wybrany input lub textarea
                    if (((int)$_POST['typ_pola_'.$q.'_'.$w] == 0 && $_POST['dopuszczalne_'.$q.'_'.$w] != 'kalendarz') || (int)$_POST['typ_pola_'.$q.'_'.$w] == 1) {
                        $pola[] = array('form_field_length',$filtr->process($_POST['ilosc_znakow_'.$q.'_'.$w]));
                    }
                    
                    // jezeli wybrany input
                    if ((int)$_POST['typ_pola_'.$q.'_'.$w] == 0) {
                        $IloscZnakow = $filtr->process($_POST['dlugosc_pola_'.$q.'_'.$w]);
                        $pola[] = array('form_field_input_length', (((int)$IloscZnakow <= 0) ? 20 : (int)$IloscZnakow));
                        unset($IloscZnakow);
                        $pola[] = array('form_field_input_limit', $filtr->process($_POST['dopuszczalne_'.$q.'_'.$w]));
                    }  

                    // jezeli wybrany input i rodzaj email
                    if ((int)$_POST['typ_pola_'.$q.'_'.$w] == 0 && $_POST['dopuszczalne_'.$q.'_'.$w] == 'email') {
                        $pola[] = array('form_field_email', $filtr->process($_POST['wyslij_'.$q.'_'.$w]));
                        $pola[] = array('form_field_email_header', $filtr->process($_POST['nadawca_'.$q.'_'.$w]));
                    }                     
                    
                    // jezeli wybrany radio, checkbox lub select
                    if ((int)$_POST['typ_pola_'.$q.'_'.$w] == 2 || (int)$_POST['typ_pola_'.$q.'_'.$w] == 3 || (int)$_POST['typ_pola_'.$q.'_'.$w] == 4) {
                        $pola[] = array('form_field_value',$filtr->process($_POST['wartosc_pola_'.$q.'_'.$w]));
                    }    

                    // jezeli wybrane jest file
                    if ((int)$_POST['typ_pola_'.$q.'_'.$w] == 5) {
                        $pola[] = array('form_field_file_type',$filtr->process($_POST['format_pliku_'.$q.'_'.$w]));
                        $pola[] = array('form_field_file_size',(int)$_POST['wielkosc_pliku_'.$q.'_'.$w]);
                    }
                    
                    $sql = $db->insert_query('form_field' , $pola);
                    unset($pola);
                }
                
            }
        
        }        

        unset($ile_jezykow);   

        if ( isset($_POST['zakladka']) && (int)$_POST['zakladka'] > 0 ) {
          
            Funkcje::PrzekierowanieURL('/zarzadzanie/wyglad/wyglad.php?zakladka='.(int)$_POST['zakladka']);
          
          } else {
          
            Funkcje::PrzekierowanieURL('formularze.php?id_poz='.$id_formularza);
            
        }          
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="formularze/formularze_edytuj.php" method="post" id="poForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from form where id_form = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['id_form']; ?>" />
                    
                    <?php if (isset($_GET['zakladka']) && (int)$_GET['zakladka'] > 0 ) { ?>
                    <input type="hidden" name="zakladka" value="<?php echo (int)$_GET['zakladka']; ?>" />
                    <?php } ?>                      

                    <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                    <script type="text/javascript">
                    //<![CDATA[
                    $(document).ready(function() {
                    $("#poForm").validate({
                      rules: {
                        nazwa_0: {
                          required: true
                        },
                        tytul_0: {
                          required: true
                        }                       
                      },
                      messages: {
                        nazwa_0: {
                          required: "Pole jest wymagane"
                        },
                        tytul_0: {
                          required: "Pole jest wymagane"
                        }                         
                      }
                    });
                    });
                
                    function wart(id, id_jezyk, typ) {
                        $('#znaki_'+id+'_'+id_jezyk).css('display','none');                               
                        //
                        // jezeli plik
                        if ( typ == 5 ) {
                             $('#wymagane_'+id+'_'+id_jezyk).slideUp(); 
                             $('#wart_'+id+'_'+id_jezyk).slideUp(); 
                             $('#dlugosc_'+id+'_'+id_jezyk).slideUp();
                             $('#plik_'+id+'_'+id_jezyk).slideDown();
                           } else {
                             $('#wymagane_'+id+'_'+id_jezyk).slideDown();
                             $('#wart_'+id+'_'+id_jezyk).slideDown();   
                             $('#dlugosc_'+id+'_'+id_jezyk).slideUp();
                             $('#plik_'+id+'_'+id_jezyk).slideUp();                             
                        }
                    }   

                    function znaki(id, id_jezyk, typ) {
                        $('#wart_'+id+'_'+id_jezyk).css('display','none');                               
                        if (typ == '0') {
                            $('#dlugosc_'+id+'_'+id_jezyk).slideDown();
                            //
                            if ( $('[name="dopuszczalne_'+id+'_'+id_jezyk+'"]:checked').val() == 'kalendarz' ) {
                                 $('#znaki_'+id+'_'+id_jezyk).slideUp(); 
                              } else {
                                 $('#znaki_'+id+'_'+id_jezyk).slideDown(); 
                            }
                          } else {
                            $('#dlugosc_'+id+'_'+id_jezyk).slideUp(); 
                            $('#znaki_'+id+'_'+id_jezyk).slideDown(); 
                        }
                        $('#plik_'+id+'_'+id_jezyk).slideUp();
                    }

                    function email(id, id_jezyk, akcja) {
                        if (akcja == '0' || akcja == '2') {
                            $('#tylko_email_'+id+'_'+id_jezyk).slideUp(); 
                          } else {
                            $('#tylko_email_'+id+'_'+id_jezyk).slideDown(); 
                        }
                        if ( akcja == '2' ) {
                            $('#znaki_'+id+'_'+id_jezyk).slideUp(); 
                          } else {
                            $('#znaki_'+id+'_'+id_jezyk).slideDown();                           
                        }
                    }                     

                    function dodaj_formularz(id_jezyk) {
                        ile_pol = parseInt($("#ile_pol_"+id_jezyk).val()) + 1;
                        //
                        $('#wyniki_'+id_jezyk).append('<div id="wyniki_'+id_jezyk+'_'+ile_pol+'"></div>');
                        $('#wyniki_'+id_jezyk+'_'+ile_pol).css('display','none');
                        //
                        $.get('ajax/formularz.php?tok=<?php echo Sesje::Token(); ?>', { id: ile_pol, id_jezyk: id_jezyk }, function(data) {
                            $('#wyniki_'+id_jezyk+'_'+ile_pol).html(data);
                            $("#ile_pol_"+id_jezyk).val(ile_pol);
                            
                            $('#wyniki_'+id_jezyk+'_'+ile_pol).slideDown("fast");

                            $("form input:radio").css('border','0px');
                            $("form input:checkbox").css('border','0px');		
                        });
                    }

                    function dodaj_odbiorce(id_jezyk) {
                        if (parseInt($("#ile_odbiorcow_"+id_jezyk).val()) < 6) {               
                            ile_odbiorcow = parseInt($("#ile_odbiorcow_"+id_jezyk).val()) + 1;
                            if (ile_odbiorcow == 5) {
                                $("#ile_odb_"+id_jezyk).css('display','none');
                            }
                            $('#odbiorca_'+id_jezyk+'_'+ile_odbiorcow).slideDown("fast");
                            $("#ile_odbiorcow_"+id_jezyk).val(ile_odbiorcow);
                        }
                    }                    
                    //]]>
                    </script>  
                    
                    <p>
                      <label>Czy używać CAPTCHA:</label>
                      <input type="radio" value="1" name="captcha" <?php echo ($info['form_captcha'] == 1 ? 'checked="checked"' : ''); ?> /> tak
                      <input type="radio" value="0" name="captcha" <?php echo ($info['form_captcha'] == 0 ? 'checked="checked"' : ''); ?> /> nie
                    </p>   

                    <table style="margin:10px">
                        <tr>
                            <td><label>Widoczny dla grupy klientów:</label></td>
                            <td style="padding-left:5px">
                                <?php                        
                                $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                    echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" ' . ((in_array($GrupaKlienta['id'], explode(',', $info['form_customers_group_id']))) ? 'checked="checked" ' : '') . ' /> ' . $GrupaKlienta['text'] . '<br />';
                                }               
                                unset($TablicaGrupKlientow);
                                ?>
                            </td>
                        </tr>
                    </table> 
                    
                    <div class="ostrzezenie" style="margin:0px 15px 10px 25px">Jeżeli nie zostanie wybrana żadna grupa klientów to formularz będzie widoczny dla wszystkich klientów.</div>                                          
                    
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
                        
                            // pobieranie danych jezykowych
                            $zapytanie_jezyk = "select distinct * from form_description where id_form = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                            $sqls = $db->open_query($zapytanie_jezyk);
                            $formularz = $sqls->fetch_assoc();                           
                        
                            ?>
                            
                            <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                            
                                <?php
                                if ( (int)$_GET['id_poz'] != 1 ) {
                                ?>
                            
                                <div class="opis_znacznikow">
                                    <div class="objasnieniaTytul">Znaczniki, które możesz użyć w formularzu:</div>
                                    <div class="objasnieniaTresc">
                                        <ul class="mcol">
                                            <li><b>{PRODUKT}</b> - Nazwa produktu</li>
                                            <?php
                                            $zapytanie_stale = "SELECT * FROM settings WHERE code = 'INFO_NAZWA_SKLEPU'";
                                            //
                                            $sqlu = $db->open_query($zapytanie_stale);
                                            //
                                            while ($infu = $sqlu->fetch_assoc()) {
                                                echo '<li><b>{'.$infu['code'].'}</b> - '.$infu['description'].'</li>';
                                            }
                                            $db->close_query($sqlu);
                                            unset($zapytanie_stale,$infu);
                                          ?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <?php } ?>
                            
                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Nazwa formularza:</label>
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($formularz['form_name']); ?>" id="nazwa_1" />
                                   <?php } else { ?>
                                    <label>Nazwa formularza:</label>   
                                    <input type="text" name="nazwa_<?php echo $w; ?>" size="65" value="<?php echo Funkcje::formatujTekstInput($formularz['form_name']); ?>" />
                                   <?php } ?>
                                </p>

                                <p>
                                   <?php if ($w == '0') { ?>
                                    <label class="required">Tytuł emaila:</label>
                                    <input type="text" name="tytul_<?php echo $w; ?>" size="65" value="<?php echo $formularz['form_title_email']; ?>" id="tytul_1" />
                                   <?php } else { ?>
                                    <label>Tytuł emaila:</label>   
                                    <input type="text" name="tytul_<?php echo $w; ?>" size="65" value="<?php echo $formularz['form_title_email']; ?>" />
                                   <?php } ?>
                                </p>     

                                <p>
                                  <label>Dodatkowy tekst w mailu (bez tagów HTML):</label>
                                  <textarea name="opis_mail_<?php echo $w; ?>" rows="5" cols="50"><?php echo $formularz['form_text_email']; ?></textarea>
                                </p>                                 
                                
                                <p>
                                  <label>Szablon emaila:</label>
                                  <?php
                                  $tablica = Funkcje::ListaSzablonowEmail(false);
                                  echo Funkcje::RozwijaneMenu('szablon_' . $w, $tablica, $formularz['template_email_id'] ); ?>
                                </p>   

                                <br />

                                <p>
                                  <label>Meta Tagi - Tytuł:</label>
                                  <input type="text" name="tytul_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowNazwa_<?php echo $w; ?>')" value="<?php echo $formularz['form_meta_title_tag']; ?>" />
                                </p> 
                                
                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($formularz['form_meta_title_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowNazwa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_NAZWA; ?></span>
                                </p>                                
                                
                                <p>
                                  <label>Meta Tagi - Opis:</label>
                                  <input type="text" name="opis_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowOpis_<?php echo $w; ?>')" value="<?php echo $formularz['form_meta_desc_tag']; ?>" />
                                </p> 

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>"><?php echo strlen(utf8_decode($formularz['form_meta_desc_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowOpis_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_OPIS; ?></span>
                                </p>                                 
                                
                                <p>
                                  <label>Meta Tagi - Słowa kluczowe:</label>
                                  <input type="text" name="slowa_meta_<?php echo $w; ?>" size="120" onkeyup="licznik_znakow_meta(this,'iloscZnakowSlowa_<?php echo $w; ?>')" value="<?php echo $formularz['form_meta_keywords_tag']; ?>" />
                                </p> 

                                <p class="LicznikMeta">
                                  <label></label>
                                  Ilość wpisanych znaków: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>"><?php echo strlen(utf8_decode($formularz['form_meta_keywords_tag'])); ?></span>
                                  zalecana maksymalna ilość: <span class="iloscZnakow" id="iloscZnakowSlowa_<?php echo $w; ?>Max"><?php echo DLUGOSC_META_SLOWA; ?></span>
                                </p>                                    

                                <?php 
                                $IleWypelnionych = 0;
                                for ($sd = 1; $sd < 6; $sd++) { 
                                
                                if (!empty($formularz['form_email_'.$sd])) { $IleWypelnionych++; }
                                ?>

                                <div id="odbiorca_<?php echo $w; ?>_<?php echo $sd; ?>" <?php echo ((empty($formularz['form_email_'.$sd])) ? 'style="display:none"' : ''); ?>>
                                
                                    <div class="nagl_formi">Odbiorca formularza nr <span><?php echo $sd; ?></span></div>
                                    <p>
                                      <label>Adres email:</label>
                                      <input type="text" name="mail_<?php echo $w; ?>_<?php echo $sd; ?>" size="35" value="<?php echo $formularz['form_email_'.$sd]; ?>" />
                                    </p> 
                                    <p>
                                      <label>Nazwa odbiorcy:</label>
                                      <input type="text" name="mail_nazwa_<?php echo $w; ?>_<?php echo $sd; ?>" size="75" value="<?php echo $formularz['form_email_name_'.$sd]; ?>" />
                                    </p>  
                                    
                                </div>
                                <?php } ?>
                                
                                <div style="padding:10px;padding-top:20px;" id="ile_odb_<?php echo $w; ?>">
                                    <span class="dodaj" onclick="dodaj_odbiorce(<?php echo $w; ?>)" style="cursor:pointer">dodaj odbiorcę formularza</span>
                                </div>

                                <input value="<?php echo $IleWypelnionych; ?>" type="hidden" name="ile_odbiorcow_<?php echo $w; ?>" id="ile_odbiorcow_<?php echo $w; ?>" />                                 
                                
                                <div class="edytor" style="margin-bottom:10px">
                                  <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo $formularz['form_description']; ?></textarea>
                                </div>          

                                <div id="wyniki_<?php echo $w; ?>" class="polFor">
                                
                                    <?php
                                    $q = 1;
                                    // pobieranie poszczegolnych pol formularza
                                    $zapytanie_odpowiedz = "select distinct * from form_field where id_form = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."' order by form_field_sort";
                                    $sqlsp = $db->open_query($zapytanie_odpowiedz);
                                    //
                                    while ($pole = $sqlsp->fetch_assoc()) {
                                        ?>                                                            

                                        <div class="nagl_formi">Pole formularza nr <span><?php echo $q; ?></span></div>
                                    
                                        <p>
                                            <label>Typ pola:</label>  
                                            <input type="radio" value="0" name="typ_pola_<?php echo $q; ?>_<?php echo $w; ?>" onclick="znaki(<?php echo $q; ?>,<?php echo $w; ?>,0)" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika INPUT pozwala na wpisanie tylko jednego wiersza tekstu" <?php echo (($pole['form_field_typ'] == 0) ? 'checked="checked"' : ''); ?> /> Input
                                            <input type="radio" value="1" name="typ_pola_<?php echo $q; ?>_<?php echo $w; ?>" onclick="znaki(<?php echo $q; ?>,<?php echo $w; ?>,1)" class="toolTipTop" title="Pole tekstowe stworzone za pomocą znacznika TEXTAREA pozwala na wpisanie wielu wierszy tekstu" <?php echo (($pole['form_field_typ'] == 1) ? 'checked="checked"' : ''); ?> /> Textarea
                                            <input type="radio" value="2" name="typ_pola_<?php echo $q; ?>_<?php echo $w; ?>" onclick="wart(<?php echo $q; ?>,<?php echo $w; ?>,2)" class="toolTipTop" title="Pole jednokrotnego wyboru" <?php echo (($pole['form_field_typ'] == 2) ? 'checked="checked"' : ''); ?> /> Radio Button
                                            <input type="radio" value="3" name="typ_pola_<?php echo $q; ?>_<?php echo $w; ?>" onclick="wart(<?php echo $q; ?>,<?php echo $w; ?>,3)" class="toolTipTop" title="Pole wielokrotnego wyboru" <?php echo (($pole['form_field_typ'] == 3) ? 'checked="checked"' : ''); ?> /> Checkbox
                                            <input type="radio" value="4" name="typ_pola_<?php echo $q; ?>_<?php echo $w; ?>" onclick="wart(<?php echo $q; ?>,<?php echo $w; ?>,4)" class="toolTipTop" title="Pole listy rozwijanej" <?php echo (($pole['form_field_typ'] == 4) ? 'checked="checked"' : ''); ?> /> Drop down menu
                                            <input type="radio" value="5" name="typ_pola_<?php echo $q; ?>_<?php echo $w; ?>" onclick="wart(<?php echo $q; ?>,<?php echo $w; ?>,5)" class="toolTipTop" title="Pole wgrania pliku" <?php echo (($pole['form_field_typ'] == 5) ? 'checked="checked"' : ''); ?> /> Wgranie pliku
                                        </p>	
                                        
                                        <p>
                                            <label>Nazwa pola:</label>  
                                            <input type="text" value="<?php echo Funkcje::formatujTekstInput($pole['form_field_name']); ?>" name="nazwa_pola_<?php echo $q; ?>_<?php echo $w; ?>" size="40" />
                                        </p>
                                        
                                        <div id="wart_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_typ'] == 2 || $pole['form_field_typ'] == 3 || $pole['form_field_typ'] == 4) ? '' : 'style="display:none"'); ?>>
                                            <p>
                                                <label>Wartości pola (wprowadź każdą wartość w osobnej linii):</label>
                                                <textarea name="wartosc_pola_<?php echo $q; ?>_<?php echo $w; ?>" cols="50" rows="3"><?php echo $pole['form_field_value']; ?></textarea>
                                            </p>
                                        </div>
                                        
                                        <div id="znaki_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo ((($pole['form_field_typ'] == 0 || $pole['form_field_typ'] == 1) && $pole['form_field_input_limit'] != 'kalendarz') ? '' : 'style="display:none"'); ?>>
                                            <p>
                                                <label>Minimalna ilość znaków:</label>
                                                <input class="calkowita" type="text" value="<?php echo (($pole['form_field_length'] > 0) ? $pole['form_field_length'] : ''); ?>" name="ilosc_znakow_<?php echo $q; ?>_<?php echo $w; ?>" size="3" />
                                            </p>                                           
                                        </div>
                                        
                                        <div id="plik_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_typ'] == 5 ) ? '' : 'style="display:none"'); ?>>
                                            <p>
                                                <label>Dopuszczalne formaty plików:</label>
                                                <input type="text" value="<?php echo (($pole['form_field_file_type'] != '') ? $pole['form_field_file_type'] : ''); ?>" name="format_pliku_<?php echo $q; ?>_<?php echo $w; ?>" size="50" class="toolTipText" title="Będzie można wgrać tylko pliki w podanych formatach - każdy format musi być rozdzielony przecinkiem np: jpg,png,gif" />
                                            </p> 
                                            <p>
                                                <label>Maksymalny rozmiar pliku:</label>
                                                <input type="text" value="<?php echo (($pole['form_field_file_size'] > 0) ? $pole['form_field_file_size'] : ''); ?>" name="wielkosc_pliku_<?php echo $q; ?>_<?php echo $w; ?>" size="5" class="toolTip" title="Maksymalny rozmiar pliku jaki będzie można wgrać w MB" />
                                            </p>                                             
                                        </div>                                        
                                        
                                        <div id="dlugosc_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_typ'] == 0) ? '' : 'style="display:none"'); ?>>
                                            <p>
                                                <label>Długość pola:</label>
                                                <input class="calkowita" type="text" value="<?php echo $pole['form_field_input_length']; ?>" name="dlugosc_pola_<?php echo $q; ?>_<?php echo $w; ?>" size="3" />
                                            </p>     
                                            <p>
                                              <label>Dopuszczalne dane:</label>
                                              <input type="radio" value="email" onclick="email(<?php echo $q; ?>,<?php echo $w; ?>,1)" name="dopuszczalne_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_input_limit'] == 'email') ? 'checked="checked"' : ''); ?> /> adres email
                                              <input type="radio" value="liczby" onclick="email(<?php echo $q; ?>,<?php echo $w; ?>,0)" name="dopuszczalne_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_input_limit'] == 'liczby') ? 'checked="checked"' : ''); ?> /> tylko liczby
                                              <input type="radio" value="waluta" onclick="email(<?php echo $q; ?>,<?php echo $w; ?>,0)" name="dopuszczalne_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_input_limit'] == 'waluta') ? 'checked="checked"' : ''); ?> /> waluta (tylko liczby)
                                              <input type="radio" value="kalendarz" onclick="email(<?php echo $q; ?>,<?php echo $w; ?>,2)" name="dopuszczalne_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_input_limit'] == 'kalendarz') ? 'checked="checked"' : ''); ?> /> data (kalendarz)
                                              <input type="radio" value="tekst" onclick="email(<?php echo $q; ?>,<?php echo $w; ?>,0)" name="dopuszczalne_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_input_limit'] == 'tekst') ? 'checked="checked"' : ''); ?> /> dowolna wartość
                                            </p>    

                                            <?php
                                            // jezeli pole to email
                                            ?>
                                            <div id="tylko_email_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_input_limit'] != 'email') ? 'style="display:none"' : ''); ?>>
                                                <p>
                                                  <label>Wyślij wiadomość na tego maila (dane z formularza zostaną wysłane również na maila którego poda klient - używane przy poleć znajomemu):</label>
                                                  <input type="radio" value="1" name="wyslij_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_email'] == 1) ? 'checked="checked"' : ''); ?> /> tak
                                                  <input type="radio" value="0" name="wyslij_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_email'] == 0) ? 'checked="checked"' : ''); ?> /> nie
                                                </p>

                                                <p>
                                                  <label>Czy wpisany przez klienta mail ma być jako nadawca maila ?</label>
                                                  <input type="radio" value="1" name="nadawca_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_email_header'] == 1) ? 'checked="checked"' : ''); ?> /> tak
                                                  <input type="radio" value="0" name="nadawca_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_email_header'] == 0) ? 'checked="checked"' : ''); ?> /> nie                                                  
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div id="wymagane_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_typ'] != 5) ? '' : 'style="display:none"'); ?>>
                                        
                                            <p>
                                              <label>Wymagane:</label>
                                              <input type="radio" value="1" name="wymagalnosc_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_required'] == 1) ? 'checked="checked"' : ''); ?> /> tak
                                              <input type="radio" value="0" name="wymagalnosc_<?php echo $q; ?>_<?php echo $w; ?>" <?php echo (($pole['form_field_required'] == 0) ? 'checked="checked"' : ''); ?> /> nie
                                            </p>
                                            
                                        </div>
                                        
                                        <p>
                                            <label>Kolejność wyświetlania w formularzu:</label>  
                                            <input class="calkowita" type="text" value="<?php echo $pole['form_field_sort']; ?>" name="sort_<?php echo $q; ?>_<?php echo $w; ?>" size="4" />
                                        </p>                                                                                                      

                                        <?php 
                                        $q++;
                                    } 
                                    ?> 

                                    <?php
                                    $db->close_query($sqlsp);
                                    unset($pole);                                
                                    ?>                              

                                </div>                                     
                
                                <div style="padding:10px;padding-top:20px;">
                                    <span class="dodaj" onclick="dodaj_formularz(<?php echo $w; ?>)" style="cursor:pointer">dodaj nowe pole formularza</span>
                                </div>                                

                                <input value="<?php echo (($q > 1) ? ($q - 1) : 0); ?>" type="hidden" name="ile_pol_<?php echo $w; ?>" id="ile_pol_<?php echo $w; ?>" />
                                
                            </div>
                            <?php     

                            $db->close_query($sqls);
                            unset($formularz);                            
                            
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
                  
                  <?php 
                  // jezeli jest get zakladka wraca do ustawien wygladu
                  if (isset($_GET['zakladka']) ) { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('wyglad','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka')); ?>','wyglad');">Powrót</button> 
                  
                  <?php } else { ?>
                  
                  <button type="button" class="przyciskNon" onclick="cofnij('formularze','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button> 
                  
                  <?php } ?>
              
                </div>

            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>