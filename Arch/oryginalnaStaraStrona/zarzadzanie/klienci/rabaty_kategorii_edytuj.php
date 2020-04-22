<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('discount_name',$filtr->process($_POST["nazwa"])),
                array('discount_discount',$filtr->process($_POST["rabat"])),
                array('discount_categories_id',implode(',', $_POST['id_kat'])));

        //	
        $db->update_query('discount_categories' , $pola, " discount_id = '".(int)$_POST["id"]."'");	

        unset($pola);
        //
        Funkcje::PrzekierowanieURL('rabaty_kategorii.php?id_poz='.(int)$_POST["id"]);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#eForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                rabat: { required: true, range: [-100, 0], number: true },
                id_kategorii: {
                  required: function(element) {
                    if ($("#id_kategorii").val() == '') {
                        return true;
                      } else {
                        return false;
                    }
                  }
                }                 
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane"
                },
                id_kategorii: {
                  required: "Nie została wybrana kategoria"
                }  
              }
            });
            
            $('.pkc td').find('input').click( function() {
                if ( $('#id_kategorii').length ) {
                     $('#id_kategorii').val( $(this).val() );
                     //
                     var checked = [];
                     $("input[name='id_kat[]']:checked").each( function() {
                         checked.push(parseInt($(this).val()));
                     });
                     if ( checked.length == 0 ) {
                          $('#id_kategorii').val('');
                     }                     
                }
            });
          });                          
          //]]>
          </script>        

          <form action="klienci/rabaty_kategorii_edytuj.php" method="post" id="eForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from discount_categories where discount_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                        <input type="hidden" name="akcja" value="zapisz" />
                        
                        <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

                        <p>
                          <label class="required">Nazwa:</label>
                          <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['discount_name']; ?>" size="53" />
                        </p>   

                        <p>
                          <label class="required">Rabat [%]:</label>
                          <input class="toolTip" type="text" name="rabat" id="rabat" value="<?php echo $info['discount_discount']; ?>" size="5" title="liczba z zakresu -100 do 0" />
                        </p>
                        
                        <p>
                          <label style="width:500px">Kategorie do jakich będzie przypisany rabat:</label>
                        </p>
                        
                        <div id="drzewo" style="margin-left:175px;width:550px;">    

                        <?php
                        $KategorieRabaty = explode(',', $info['discount_categories_id']);
                        
                        if ( count($KategorieRabaty) > 10 ) {
                            //
                            echo '<ul id="drzewoKategorii">';
                            foreach(Kategorie::DrzewoKategoriiZarzadzanie() as $IdKategorii => $Tablica) {
                                //
                                echo Kategorie::WyswietlDrzewoKategoriiCheckbox($IdKategorii, $Tablica, $KategorieRabaty);
                                //
                            }    
                            echo '</ul>';
                            //
                        } else {
                            //
                            echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                            //
                            $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                            for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                //
                                $check = '';
                                if ( in_array($tablica_kat[$w]['id'], $KategorieRabaty) ) {
                                    $check = 'checked="checked"';
                                }
                                //                                
                                echo '<tr>
                                        <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" '.$check.' /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            echo '</table>';
                            unset($tablica_kat,$podkategorie);                                
                            //
                            foreach ( $KategorieRabaty as $kategoria ) {
                            
                                $sciezka = Kategorie::SciezkaKategoriiId($kategoria);
                                $cSciezka = explode("_",$sciezka);                    
                                if (count($cSciezka) > 1) {
                                    //
                                    $ostatnie = strRpos($sciezka,'_');
                                    $analiza_sciezki = str_replace("_",",",substr($sciezka,0,$ostatnie));
                                    ?>
                                    <script type="text/javascript">
                                    //<![CDATA[            
                                    podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','checkbox','<?php echo implode(',', $KategorieRabaty); ?>');
                                    //]]>
                                    </script>
                                <?php
                                unset($sciezka,$cSciezka);
                                }
                          
                            }                             
                        }
                        unset($KategorieRabaty);
                        ?>                        

                        </div>
                        
                        <p>
                          <input type="hidden" name="id_kategorii" id="id_kategorii" value="<?php echo $info['discount_categories_id']; ?>" />
                        </p>                          

                        <?php if ($info['discount_groups_id'] > 0) { ?>

                        <p>
                          <label>Grupa klientów:</label>
                          <?php
                          $tablica = Klienci::ListaGrupKlientow(false);                                        
                          echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica, $info['discount_groups_id'], 'disabled="disabled"');
                          unset($tablica);
                          ?>
                        </p>
                        
                        <?php } else { ?>
                        
                        <table>
                            <tr>
                                <td class="label_tbl"><label>Klient:</label></td>
                                <td>
                                    <?php
                                    $tablica_klientow = Klienci::ListaKlientow( false );
                                    ?>
                                    <div class="obramowanie_tabeli lista_klientow">
                                    
                                      <table class="listing_tbl">
                                      
                                        <tr class="div_naglowek">
                                          <td>Wybierz</td>
                                          <td>ID</td>
                                          <td>Dane klienta</td>
                                          <td>Firma</td>
                                          <td>Kontakt</td>
                                        </tr>           

                                        <?php
                                        foreach ( $tablica_klientow as $klient) {
                                            //
                                            if ( $klient['id'] == $info['discount_customers_id'] ) {
                                                //
                                                echo '<tr class="pozycja_off">';
                                                echo '<td><input type="radio" name="klient" value="' . $klient['id'] . '" checked="checked" disabled="disabled" /></td>';
                                                echo '<td>' . $klient['id'] . '</td>';
                                                echo '<td>' . $klient['nazwa'] . '<br />' . $klient['adres'] . '</td>';
                                                
                                                if ( !empty($klient['firma']) ) {
                                                     echo '<td><span class="firma">' . $klient['firma'] . '</span>' . ((!empty($klient['nip'])) ? 'NIP:&nbsp;' . $klient['nip'] : '') . '</td>';
                                                   } else{
                                                     echo '<td></td>';
                                                }
                                                
                                                echo '<td><span class="maly_mail">' . $klient['email'] . '</span></td>';
                                                echo '</tr>';
                                                //
                                            }
                                            //
                                        }
                                        ?>
                                        
                                      </table>
                                      
                                    </div>  
                                    <?php
                                    unset($tablica_klientow);
                                    ?>
                                </td>
                            </tr>
                        </table>           

                        <?php } ?>

                    </div>
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('rabaty_kategorii','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
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

}