<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_dodawanej_pozycji = $filtr->process($_POST['id_produkt']);
        //
        $pola = array();
        $pola[] = array('products_old_price',$filtr->process($_POST['cena_poprzednia']));
        
        // nowa cena produktu
        
        // pobieranie informacji o vat - tworzy tablice ze stawkami
        $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
        $sqls = $db->open_query($zapytanie_vat);
        //
        $tablicaVat = array();
        while ($infs = $sqls->fetch_assoc()) { 
            $tablicaVat[$infs['tax_rates_id']] = $infs['tax_rate'];
        }
        $db->close_query($sqls);
        unset($zapytanie_vat, $infs);  
        //                             
        $wartosc = (float)$_POST['cena_brutto'];
        $netto = round( $wartosc / (1 + ($tablicaVat[(int)$_POST['stawka_vat']]/100)), 2);
        $podatek = $wartosc - $netto;
        //
        $pola[] = array('products_price_tax',$wartosc);
        $pola[] = array('products_price',$netto);
        $pola[] = array('products_tax',$podatek);
        //
        unset($wartosc, $netto, $podatek);
        
        // ceny dla pozostalych poziomow cen
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
            // cena poprzednia
            if ( (isset($_POST['cena_poprzednia_'.$x]) && (float)$_POST['cena_poprzednia_'.$x] > 0) && (isset($_POST['cena_brutto_'.$x]) && (float)$_POST['cena_brutto_'.$x] > 0) ) {
                //
                $pola[] = array('products_old_price_'.$x,$filtr->process($_POST['cena_poprzednia_'.$x]));
                //
                $wartosc = (float)$_POST['cena_brutto_'.$x];
                $netto = round( $wartosc / (1 + ($tablicaVat[(int)$_POST['stawka_vat']]/100)), 2);
                $podatek = $wartosc - $netto;    
                //
                $pola[] = array('products_price_tax_'.$x,$wartosc);
                $pola[] = array('products_price_'.$x,$netto);
                $pola[] = array('products_tax_'.$x,$podatek);
                //    
                unset($wartosc, $netto, $podatek); 
                //                
                //
              } else {
                //
                $pola[] = array('products_old_price_'.$x,'0');
                //
            }
            //
        }         
                        
        $pola[] = array('specials_status','1');
        
        if (!empty($_POST['data_promocja_od'])) {
            $pola[] = array('specials_date',date('Y-m-d H:i:s', strtotime($filtr->process($_POST['data_promocja_od'])) + (int)$_POST['data_promocja_od_godzina'] * 3600 + (int)$_POST['data_promocja_od_minuty'] * 60 ));
          } else {
            $pola[] = array('specials_date','0000-00-00');            
        }
        if (!empty($_POST['data_promocja_do'])) {
            $pola[] = array('specials_date_end',date('Y-m-d H:i:s', strtotime($filtr->process($_POST['data_promocja_do'])) + (int)$_POST['data_promocja_do_godzina'] * 3600 + (int)$_POST['data_promocja_do_minuty'] * 60 ));
          } else {
            $pola[] = array('specials_date_end','0000-00-00');            
        }
        //	
        $sql = $db->update_query('products', $pola, 'products_id = ' . $id_dodawanej_pozycji);
        
        unset($pola, $tablicaVat);
        
        Funkcje::PrzekierowanieURL('promocje.php?id_poz='.$id_dodawanej_pozycji);
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="promocje/promocje_dodaj.php" method="post" id="poForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" id="rodzaj_modulu" value="promocje" />
                
                <!-- Skrypt do walidacji formularza -->
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                  $("#poForm").validate({
                    rules: {
                      cena_poprzednia: {
                        required: true
                      },
                      cena_brutto: {
                        required: true
                      }                       
                    },
                    messages: {
                      cena_poprzednia: {
                        required: "Pole jest wymagane"
                      },
                      cena_brutto: {
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
                
                function promocja(id) {
                  $('#formi').slideDown('fast');
                  $('#ButZapis').css('display','inline-block');
                  //
                  $('#danePromocji').html('<img style="margin-left:10px" src="obrazki/_loader_small.gif">');
                  //
                  $.get("ajax/promocja.php", 
                      { id: id, tok: $('#tok').val() },
                      function(data) { 
                          $('#danePromocji').hide();
                          $('#danePromocji').html(data);                                                           
                          $('#danePromocji').slideDown();
                  });                   
                }                
                //]]>
                </script>

                <table>
                    <tr>
                        <td style="vertical-align:top">
                        
                            <?php
                            $plik = 'promocje.php';
                            if ( isset($_SESSION['filtry'][$plik]['kategoria_id']) ) {
                                 $_GET['kategoria_id'] = $_SESSION['filtry'][$plik]['kategoria_id'];
                            }
                            unset($plik);
                            ?>                        
                
                            <?php if (!isset($_GET['kategoria_id'])) { ?>

                            <p style="font-weight:bold;height:30px;">
                            Wyszukaj produkt lub wybierz kategorię z której<br /> chcesz wybrać produkt do utworzenia promocji
                            </p>
                            
                            <div style="margin-left:10px;margin-top:7px;" id="fraza">
                                <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" class="toolTipTopText" title="Wpisz nazwę produktu lub kod producenta" /></div> <span title="Wyszukaj produkt" onclick="fraza_produkty()"></span>
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

                            <div id="wynik_produktow_promocje" style="display:none"></div> 
                            
                            <div class="info_content" style="padding-left:5px">                                 
                            
                                <div id="formi" style="display:none">
                                
                                    <span class="wynik_naglowek_dodanie">Ustaw parametry dodawanej promocji</span>
                                    
                                    <div id="danePromocji"></div>
                                
                                    <p>
                                        <label>Data rozpoczęcia:</label>
                                        <input type="text" name="data_promocja_od" value="" size="20"  class="datepicker" /> &nbsp; 
                                        godz: &nbsp;
                                        <select name="data_promocja_od_godzina">
                                        <?php
                                        for ($c = 0;$c < 24; $c++) { 
                                            echo '<option value="'.$c.'">'.$c.'</option>'; 
                                        } 
                                        ?>
                                        </select> &nbsp;
                                        min: &nbsp;
                                        <select name="data_promocja_od_minuty">
                                        <?php
                                        for ($c = 0;$c < 6; $c++) { 
                                            echo '<option value="'.($c*10).'">'.($c*10).'</option>'; 
                                        } 
                                        ?>
                                        </select>                                              
                                    </p>
                                    
                                    <p>
                                        <label>Data zakończenia:</label>
                                        <input type="text" name="data_promocja_do" value="" size="20" class="datepicker" /> &nbsp;

                                        godz: &nbsp;
                                        <select name="data_promocja_do_godzina">
                                        <?php
                                        for ($c = 0;$c < 24; $c++) { 
                                            echo '<option value="'.$c.'">'.$c.'</option>'; 
                                        } 
                                        ?>
                                        </select> &nbsp;
                                        min: &nbsp;
                                        <select name="data_promocja_do_minuty">
                                        <?php
                                        for ($c = 0;$c < 6; $c++) { 
                                            echo '<option value="'.($c*10).'">'.($c*10).'</option>'; 
                                        } 
                                        ?>                        
                                        </select>                                              
                                    </p>

                                </div>

                            </div>                            

                        </td>
                        
                    </tr>
                </table>

            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              <button type="button" class="przyciskNon" onclick="cofnij('promocje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','promocje');">Powrót</button>   
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
