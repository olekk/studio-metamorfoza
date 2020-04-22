<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_edytowanej_pozycji = $filtr->process($_POST['id']);
        //
        $pola = array(array('customers_name',$filtr->process($_POST['wystawiajacy'])),
                      array('reviews_rating',$filtr->process($_POST['ocena'])),
                      array('date_added',date('Y-m-d', strtotime($filtr->process($_POST['data_dodania'])))));
        //	
        $sql = $db->update_query('reviews', $pola, 'reviews_id = ' . $id_edytowanej_pozycji);
        unset($pola);        
        
        $pola = array(
                array('reviews_id',$id_edytowanej_pozycji),
                array('languages_id',$filtr->process($_POST['jezyk'])),
                array('reviews_text',$filtr->process($_POST['tresc_recenzji'])));          
        $sql = $db->update_query('reviews_description' , $pola, 'reviews_id = ' . $id_edytowanej_pozycji);
        unset($pola);
        
        Funkcje::PrzekierowanieURL('recenzje.php?id_poz='.$id_edytowanej_pozycji);
    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

          <form action="recenzje/recenzje_edytuj.php" method="post" id="recenzjeForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct * from reviews r, reviews_description rd where r.reviews_id = rd.reviews_id and r.reviews_id = '".$filtr->process($_GET['id_poz'])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">    
                
                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" id="rodzaj_modulu" value="recenzje" />
                    
                    <input type="hidden" name="id" value="<?php echo $info['reviews_id']; ?>" />
                    
                    <div class="info_content">

                    <!-- Skrypt do walidacji formularza -->
                    <script type="text/javascript">
                    //<![CDATA[
                    $(document).ready(function() {
                    
                    $("#recenzjeForm").validate({
                      rules: {
                        wystawiajacy: {
                          required: true
                        },
                        tresc_recenzji: {
                          required: true
                        }                     
                      },
                      messages: {
                        wystawiajacy: {
                          required: "Pole jest wymagane"
                        },
                        tresc_recenzji: {
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
                    
                    <p>
                        <label class="required">Nazwa opiniującego:</label>
                        <input type="text" name="wystawiajacy" id="wystawiajacy" value="<?php echo $info['customers_name']; ?>" size="30" />                                        
                    </p>                
                
                    <p>
                        <label class="required">Data dodania:</label>
                        <input type="text" name="data_dodania" id="data_dodania" value="<?php echo ((Funkcje::czyNiePuste($info['date_added'])) ? date('d-m-Y',strtotime($info['date_added'])) : ''); ?>" size="20" class="datepicker" />                                        
                    </p>
                    
                    <p>
                        <label>Język recenzji:</label>
                        <?php
                        $tablica_jezykow = Funkcje::TablicaJezykow();                 
                        echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,$info['languages_id']);
                        ?>                                   
                    </p>                    
                    
                    <p>
                        <label class="required">Opinia:</label>
                        <textarea name="tresc_recenzji" id="tresc_recenzji" rows="10" cols="50" class="toolTip" title="Treść recenzji - bez tagów HTML"><?php echo $info['reviews_text']; ?></textarea>                                           
                    </p>
                    
                    <table>
                        <tr>
                            <td class="ocena_tbl"><label>Ocena:</label></td>
                            <td>
                              <img title="Ocena 1/5" alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /> <input type="radio" value="1" name="ocena" <?php echo (($info['reviews_rating'] == '1') ? 'checked="checked"' : ''); ?> /> <br />
                              <img title="Ocena 2/5" alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /> <input type="radio" value="2" name="ocena" <?php echo (($info['reviews_rating'] == '2') ? 'checked="checked"' : ''); ?> /> <br />
                              <img title="Ocena 3/5" alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /> <input type="radio" value="3" name="ocena" <?php echo (($info['reviews_rating'] == '3') ? 'checked="checked"' : ''); ?> /> <br />
                              <img title="Ocena 4/5" alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /> <input type="radio" value="4" name="ocena" <?php echo (($info['reviews_rating'] == '4') ? 'checked="checked"' : ''); ?> /> <br />
                              <img title="Ocena 5/5" alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /> <input type="radio" value="5" name="ocena" <?php echo (($info['reviews_rating'] == '5') ? 'checked="checked"' : ''); ?> />
                            </td>
                        </tr>
                    </table>                     

                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('recenzje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>');">Powrót</button>     
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