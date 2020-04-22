<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        // sprawdz warunki
        //
        $warunki_szukania = '';
        //
        // jezeli jest prefix
        if (isset($_POST['prefix']) && !empty($_POST['prefix'])) {
            $Prefix = $filtr->process($_POST['prefix']);
            $dlugosc = strlen($Prefix);
            //
            $warunki_szukania = " and SUBSTR(coupons_name,1,".$dlugosc.") = '" .$Prefix. "'";
            unset($Prefix, $dlugosc);
        }
        
        // jezeli jest data od
        if (isset($_POST['data_od']) && !empty($_POST['data_od'])) {
            $DataOd = $filtr->process($_POST['data_od']);
            $warunki_szukania .= " and coupons_date_added >= '".date('Y-m-d', strtotime($DataOd))."'";            
            //
            unset($DataOd);
        }   

        // jezeli jest data do
        if (isset($_POST['data_od']) && !empty($_POST['data_do'])) {
            $DataDo = $filtr->process($_POST['data_do']);
            $warunki_szukania .= " and coupons_date_added <= '".date('Y-m-d', strtotime($DataDo))."'";            
            //
            unset($DataDo);            
        }           
        
        if ( $warunki_szukania != '' ) {
          $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
        }        
    
        $zapytanie = "select * from coupons" . $warunki_szukania;
        $sql = $db->open_query($zapytanie);
        
        //
        if ((int)$db->ile_rekordow($sql) > 0) {
        
            $ciag_do_zapisu = '';
            
            $tablica_pol = array();
            $tablica_pol[] = array('kod','Kod kuponu;','coupons_name');
            $tablica_pol[] = array('opis','Opis;','coupons_description');
            $tablica_pol[] = array('rodzaj','Rodzaj rabatu;','coupons_discount_type');
            $tablica_pol[] = array('znizka','Wartość rabatu;','coupons_discount_value');
            $tablica_pol[] = array('data_utworzenia','Data utworzenia kuponu;','coupons_date_added');
            $tablica_pol[] = array('waznosc_od','Data rozpoczęcia;','coupons_date_start');
            $tablica_pol[] = array('waznosc_do','Data zakończenia;','coupons_date_end');
            $tablica_pol[] = array('grupa_klientow','Grupa klientów;','coupons_customers_groups_id');
            $tablica_pol[] = array('ilosc_produktow','Minimalna ilość produktów;','coupons_min_quantity');
            $tablica_pol[] = array('wartosc_zamowienia','Minimalna wartość zamówienia;','coupons_min_order');
            $tablica_pol[] = array('ilosc_kuponow','Ilość dostępnych kuponów;','coupons_quantity');
            $tablica_pol[] = array('promocje','Promocje;','coupons_specials');
            $tablica_pol[] = array('warunki','Ograniczenia typ;','coupons_exclusion');
            $tablica_pol[] = array('warunki_id','Ograniczenia ID;','coupons_exclusion_id');

            for ($w = 0, $c = count($tablica_pol); $w < $c; $w++) {
                if (isset($_POST[$tablica_pol[$w][0]])) {
                    //
                    if ((int)$_POST[$tablica_pol[$w][0]] == 1) {
                        $ciag_do_zapisu .= $tablica_pol[$w][1];
                    }
                    //
                }
            }            

            $ciag_do_zapisu = substr($ciag_do_zapisu, 0, -1);
            $ciag_do_zapisu .= "\n";            
            
            while ($info = $sql->fetch_assoc()) {

                for ($w = 0, $c = count($tablica_pol); $w < $c; $w++) {
                    if (isset($_POST[$tablica_pol[$w][0]])) {
                        //
                        if ((int)$_POST[$tablica_pol[$w][0]] == 1) {
                            if (Funkcje::czyNiePuste($info[$tablica_pol[$w][2]])) {
                                //
                                $DoZapisu = $info[$tablica_pol[$w][2]];
                                //
                                // jezeli rodzaj rabatu
                                if ($tablica_pol[$w][0] == 'rodzaj') {
                                    switch ($info[$tablica_pol[$w][2]]) {
                                        case "fixed":
                                            $DoZapisu = 'kwota';
                                            break;
                                        case "percent":
                                            $DoZapisu = 'procent';
                                            break;                                             
                                    }                                     
                                }
                                //
                                // jezeli data
                                if ($tablica_pol[$w][0] == 'data_utworzenia' || $tablica_pol[$w][0] == 'waznosc_od' || $tablica_pol[$w][0] == 'waznosc_do') {
                                    $DoZapisu = date('d-m-Y',strtotime($info[$tablica_pol[$w][2]]));
                                }
                                
                                if ($tablica_pol[$w][0] == 'promocje') {
                                    if ($info[$tablica_pol[$w][2]] == '1') {
                                        $DoZapisu = 'tak';
                                      } else {
                                        $DoZapisu = 'nie';
                                    }
                                }
                                //
                                $ciag_do_zapisu .=  $DoZapisu . ';';
                              } else {
                                $ciag_do_zapisu .= '-;';
                            }
                        }
                        //
                    }
                }    
                
                $ciag_do_zapisu = substr($ciag_do_zapisu, 0, -1);
                $ciag_do_zapisu .= "\n";

            }
            
            //
            $db->close_query($sql);
            unset($info);      

            header("Content-Type: application/force-download\n");
            header("Cache-Control: cache, must-revalidate");   
            header("Pragma: public");
            header("Content-Disposition: attachment; filename=eksport_kuponow_rabatowych_" . date("d-m-Y") . ".csv");
            print $ciag_do_zapisu;
            exit;   
            
        } else {
        
            $czy_jest_blad = true;
        
        }
        
        $db->close_query($sql);        

        //Funkcje::PrzekierowanieURL('kupony.php');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Eksport danych</div>
    <div id="cont">
    
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {

            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               inside: false,
               readonly_element: false
            });             
            
          });       
          //]]>
          </script>       
          
          <form action="kupony/kupony_export.php" method="post" id="kuponyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Eksport danych</div>
            
            <div class="pozycja_edytowana">
                
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <div class="naglowek_export">Zakres danych do eksportu (pozostawienie pol pustych spowoduje eksport wszystkich kuponów)</div>
            
                <p>
                  <label>Kupony o prefixie:</label>
                  <input type="text" name="prefix" id="prefix" value="" size="10" />
                </p>   

                <p>
                    <label>Data dodania od:</label>
                    <input type="text" name="data_od" value="" size="20" class="datepicker" />                                        
                </p>

                <p>
                    <label>Data dodania do:</label>
                    <input type="text" name="data_do" value="" size="20" class="datepicker" />                                        
                </p>
                
                <div class="naglowek_export">Jakie dane eksportować ?</div>
                
                <table class="inputy">
                    <tr>
                        <td><input type="checkbox" name="kod" value="1" checked="checked" /> kod kuponu</td>
                        <td><input type="checkbox" name="opis" value="1" /> opis</td>
                        <td><input type="checkbox" name="rodzaj" value="1" checked="checked" /> rodzaj kuponu</td>
                        <td><input type="checkbox" name="znizka" value="1" checked="checked" /> zniżka</td>
                        <td><input type="checkbox" name="data_utworzenia" value="1" checked="checked" /> data utworzenia</td>
                    </tr><tr>
                        <td><input type="checkbox" name="waznosc_od" value="1" checked="checked" /> ważność od</td>
                        <td><input type="checkbox" name="waznosc_do" value="1" checked="checked" /> ważność do</td>
                        <td><input type="checkbox" name="grupa_klientow" value="1" /> id grupy klientów</td>
                        <td><input type="checkbox" name="ilosc_produktow" value="1" /> minimalna ilość produktów</td>
                        <td><input type="checkbox" name="wartosc_zamowienia" value="1" /> minimalna wartość zamówienia</td>
                    </tr>
                    </tr><tr>
                        <td><input type="checkbox" name="ilosc_kuponow" value="1" checked="checked" /> ilość dostępnych kuponów</td>
                        <td><input type="checkbox" name="promocje" value="1" checked="checked" /> wykluczenia promocji</td>
                        <td colspan="2"><input type="checkbox" name="warunki" value="1" checked="checked" /> ograniczenia kategorii / producentów / produktów</td>
                        <td colspan="2"><input type="checkbox" name="warunki_id" value="1" checked="checked" /> id ograniczen kategorii / producentów / produktów</td>
                    </tr>                    
                </table>
                
                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Wygeneruj dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>           
            </div>                 


          </div>                      
          </form>

    </div>    
    
    <?php
    if ($czy_jest_blad == true) {
        //
        echo Okienka::pokazOkno('Błąd generowania','Nie wygenerowano pliku - brak danych wynikowych');
        //
    }
    
    include('stopka.inc.php');

}