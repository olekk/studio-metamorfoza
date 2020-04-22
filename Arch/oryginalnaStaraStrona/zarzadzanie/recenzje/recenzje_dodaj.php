<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(array('products_id',$filtr->process($_POST['id_produkt'])),
                      array('customers_id','0'),
                      array('customers_name',$filtr->process($_POST['wystawiajacy'])),
                      array('reviews_rating',$filtr->process($_POST['ocena'])),
                      array('date_added',date('Y-m-d', strtotime($filtr->process($_POST['data_dodania'])))),
                      array('approved','1'));
        //	
        $sql = $db->insert_query('reviews', $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);        
        
        $pola = array(
                array('reviews_id',$id_dodanej_pozycji),
                array('languages_id',$filtr->process($_POST['jezyk'])),
                array('reviews_text',$filtr->process($_POST['tresc_recenzji'])));          
        $sql = $db->insert_query('reviews_description' , $pola);
        unset($pola);
        
        Funkcje::PrzekierowanieURL('recenzje.php?id_poz='.$id_dodanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="recenzje/recenzje_dodaj.php" method="post" id="recenzjeForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" id="rodzaj_modulu" value="recenzje" />
                
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

                <table>
                    <tr>
                        <td style="vertical-align:top">                   

                            <?php
                            $plik = 'recenzje.php';
                            if ( isset($_SESSION['filtry'][$plik]['kategoria_id']) ) {
                                 $_GET['kategoria_id'] = $_SESSION['filtry'][$plik]['kategoria_id'];
                            }
                            unset($plik);
                            ?>
                            
                            <?php if (!isset($_GET['kategoria_id'])) { ?>
                
                            <p style="font-weight:bold;height:30px;">
                            Wyszukaj produkt lub wybierz kategorię z której<br /> chcesz wybrać produkt do napisania recenzji
                            </p>
                            
                            <div style="margin-left:10px;margin-top:7px;" id="fraza">
                                <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /></div> <span title="Wyszukaj produkt" onclick="fraza_produkty()"></span>
                            </div>                              
                            
                            <div id="drzewo" style="margin-left:10px;margin-top:7px;width:300px;">
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
                                            <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                          </tr>
                                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                }
                                if ( count($tablica_kat) == 0 ) {
                                     echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                                }                                
                                echo '</table>';
                                unset($tablica_kat,$podkategorie);
                                ?> 
                            </div>

                            <?php } ?>                            
                            
                        </td><td style="vertical-align:top">             

                            <div id="wynik_produktow_recenzje" style="display:none"></div>     

                            <div class="info_content" style="padding-left:5px">                
                            
                                <div id="formi" style="display:none">
                                
                                    <span class="wynik_naglowek_dodanie">Wpisz dane nowej recenzji</span>
                                
                                    <p>
                                        <label class="required">Nazwa opiniującego:</label>
                                        <input type="text" name="wystawiajacy" id="wystawiajacy" value="" size="30" />                                        
                                    </p>                
                                
                                    <p>
                                        <label class="required">Data dodania:</label>
                                        <input type="text" name="data_dodania" id="data_dodania" value="" size="20" class="datepicker" />                                        
                                    </p>
                                    
                                    <p>
                                        <label>Język recenzji:</label>
                                        <?php
                                        $tablica_jezykow = Funkcje::TablicaJezykow();                 
                                        echo Funkcje::RozwijaneMenu('jezyk',$tablica_jezykow,0);
                                        ?>                                   
                                    </p>                    
                                    
                                    <p>
                                        <label class="required">Opinia:</label>
                                        <textarea name="tresc_recenzji" id="tresc_recenzji" rows="5" cols="60" class="toolTip" title="Treść recenzji - bez tagów HTML"> </textarea>                                           
                                    </p>
                                    
                                    <table>
                                        <tr>
                                            <td class="ocena_tbl"><label>Ocena:</label></td>
                                            <td>
                                              <img title="Ocena 1/5" alt="Ocena 1/5" src="obrazki/recenzje/star_1.png" /> <input type="radio" value="1" name="ocena" checked="checked" /> <br />
                                              <img title="Ocena 2/5" alt="Ocena 2/5" src="obrazki/recenzje/star_2.png" /> <input type="radio" value="2" name="ocena" /> <br />
                                              <img title="Ocena 3/5" alt="Ocena 3/5" src="obrazki/recenzje/star_3.png" /> <input type="radio" value="3" name="ocena" /> <br />
                                              <img title="Ocena 4/5" alt="Ocena 4/5" src="obrazki/recenzje/star_4.png" /> <input type="radio" value="4" name="ocena" /> <br />
                                              <img title="Ocena 5/5" alt="Ocena 5/5" src="obrazki/recenzje/star_5.png" /> <input type="radio" value="5" name="ocena" />
                                            </td>
                                        </tr>
                                    </table>                   

                                </div>

                            </div>
                            
                        </td>
                        
                    </tr>
                </table>                            
                            
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              <button type="button" class="przyciskNon" onclick="cofnij('recenzje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','recenzje');">Powrót</button>   
            </div>

            <?php if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) { ?>
            
            <script type="text/javascript">
            //<![CDATA[            
            podkat_produkty(<?php echo (int)$_GET['kategoria_id']; ?>);
            //]]>
            </script>       

            <?php } ?>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
