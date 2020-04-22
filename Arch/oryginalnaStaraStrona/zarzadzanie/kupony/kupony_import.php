<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

$czy_jest_blad = false;

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $nazwa_plik = $_FILES['file']['tmp_name']; 
        $dane = file($nazwa_plik);
        
        //
        $separator = $filtr->process($_POST['sep']);
        //
        // kolejnosc w pliku
        $sort_kod_kuponu = $filtr->process($_POST['kod_sort']);
        $sort_opis = $filtr->process($_POST['opis_sort']);
        $sort_rodzaj = $filtr->process($_POST['rodzaj_sort']);
        $sort_znizka = $filtr->process($_POST['znizka_sort']);
        $sort_waznosc_od = $filtr->process($_POST['waznosc_od_sort']);
        $sort_waznosc_do = $filtr->process($_POST['waznosc_do_sort']);
        $sort_grupa_klientow = $filtr->process($_POST['grupa_klientow_sort']);
        $sort_ilosc_produktow = $filtr->process($_POST['ilosc_produktow_sort']);
        $sort_wartosc_zamowienia = $filtr->process($_POST['wartosc_zamowienia_sort']);
        $sort_ilosc_kuponow = $filtr->process($_POST['ilosc_kuponow_sort']);  
        $sort_promocje = $filtr->process($_POST['promocje_sort']);        
        $sort_warunki = $filtr->process($_POST['warunki_sort']);
        $sort_warunki_id = $filtr->process($_POST['warunki_id_sort']);
        //
        for($i = 0, $c = count($dane); $i < $c; $i++) {                
            $linia = explode($separator,$dane[$i]);     
            //
            // jezeli jest kod kuponu
            if (isset($_POST['kod']) && $filtr->process($_POST['kod']) == '1' && (int)$sort_kod_kuponu > 0 && !empty($linia[(int)$sort_kod_kuponu-1])) {
                //
                // trzeba sprawdzic czy takiego kodu juz nie ma w bazie
                $zapytanie = "select coupons_name from coupons where coupons_name = '" . $filtr->process($linia[(int)$sort_kod_kuponu-1]) . "'";
                $sql = $db->open_query($zapytanie);
                
                if ((int)$db->ile_rekordow($sql) == 0) {
                    //
                    $pola = array();
                    //
                    $pola[] = array('coupons_name',$filtr->process($linia[(int)$sort_kod_kuponu-1]));    
                    
                    // jezeli jest opis
                    if ( isset($linia[(int)$sort_opis-1]) ) {
                        if (isset($_POST['opis']) && $filtr->process($_POST['opis']) == '1' && (int)$sort_opis > 0) {
                            $pola[] = array('coupons_description',$filtr->process($linia[(int)$sort_opis-1]));     
                        }
                    }
                    
                    // jezeli jest typ
                    if ( isset($linia[(int)$sort_rodzaj-1]) ) {
                        if (isset($_POST['rodzaj']) && $filtr->process($_POST['rodzaj']) == '1' && (int)$sort_rodzaj > 0 && ($linia[(int)$sort_rodzaj-1] == 'procent' || $linia[(int)$sort_rodzaj-1] == 'kwota')) {
                            switch ($linia[(int)$sort_rodzaj-1]) {
                                case "procent":
                                    $typ = 'percent';
                                    break;
                                case "kwota":
                                    $typ = 'fixed';
                                    break;                 
                                default:
                                    $typ = 'fixed';                         
                            }                        
                            $pola[] = array('coupons_discount_type',$typ); 
                            unset($typ);
                          } else {
                            $pola[] = array('coupons_discount_type','fixed');
                        }
                    }
                    
                    // jezeli jest wartosc znizki
                    if ( isset($linia[(int)$sort_znizka-1]) ) {
                        if (isset($_POST['znizka']) && $filtr->process($_POST['znizka']) == '1' && (int)$sort_znizka > 0 && (float)$linia[(int)$sort_znizka-1] > 0) {
                            $pola[] = array('coupons_discount_value',(float)$linia[(int)$sort_znizka-1]); 
                        }
                    }
                    
                    // jezeli jest waznosc od
                    if ( isset($linia[(int)$sort_waznosc_od-1]) ) {
                        if (isset($_POST['waznosc_od']) && $filtr->process($_POST['waznosc_od']) == '1' && (int)$sort_waznosc_od > 0 && strtotime($filtr->process($linia[(int)$sort_waznosc_od-1])) > 0) {
                            $pola[] = array('coupons_date_start',date('Y-m-d', strtotime($filtr->process($linia[(int)$sort_waznosc_od-1]))));  
                        }
                    }
                    
                    // jezeli jest waznosc do
                    if ( isset($linia[(int)$sort_waznosc_do-1]) ) {
                        if (isset($_POST['waznosc_do']) && $filtr->process($_POST['waznosc_do']) == '1' && (int)$sort_waznosc_do > 0 && strtotime($filtr->process($linia[(int)$sort_waznosc_do-1])) > 0) {
                            $pola[] = array('coupons_date_end',date('Y-m-d', strtotime($filtr->process($linia[(int)$sort_waznosc_do-1]))));  
                        }                    
                    }
                    
                    // jezeli jest minimalne zamowienie
                    if ( isset($linia[(int)$sort_wartosc_zamowienia-1]) ) {
                        if (isset($_POST['wartosc_zamowienia']) && $filtr->process($_POST['wartosc_zamowienia']) == '1' && (int)$sort_wartosc_zamowienia > 0 && (float)$linia[(int)$sort_wartosc_zamowienia-1] > 0) {
                            $pola[] = array('coupons_min_order',$filtr->process($linia[(int)$sort_wartosc_zamowienia-1])); 
                        }
                    }
                    
                    // jezeli jest grupa klientow
                    if ( isset($linia[(int)$sort_grupa_klientow-1]) ) {
                        if (isset($_POST['grupa_klientow']) && $filtr->process($_POST['grupa_klientow']) == '1' && (int)$sort_grupa_klientow > 0 && (int)$linia[(int)$sort_grupa_klientow-1] > 0) {
                            $pola[] = array('coupons_customers_groups_id',(int)$linia[(int)$sort_grupa_klientow-1]); 
                        }  
                    }                    
                    
                    // jezeli jest minialna ilosc produktow
                    if ( isset($linia[(int)$sort_ilosc_produktow-1]) ) {
                        if (isset($_POST['ilosc_produktow']) && $filtr->process($_POST['ilosc_produktow']) == '1' && (int)$sort_ilosc_produktow > 0 && (int)$linia[(int)$sort_ilosc_produktow-1] > 0) {
                            $pola[] = array('coupons_min_quantity',(int)$linia[(int)$sort_ilosc_produktow-1]); 
                        }  
                    }
                    
                    // jezeli jest ilosc kuponow
                    if ( isset($linia[(int)$sort_ilosc_kuponow-1]) ) {
                        if (isset($_POST['ilosc_kuponow']) && $filtr->process($_POST['ilosc_kuponow']) == '1' && (int)$sort_ilosc_kuponow > 0 && (int)$linia[(int)$sort_ilosc_kuponow-1] > 0) {
                            $pola[] = array('coupons_quantity',(int)$linia[(int)$sort_ilosc_kuponow-1]); 
                          } else {
                            $pola[] = array('coupons_quantity','1'); 
                        }   
                    }
                    
                    // jezeli jest info o promocji
                    if ( isset($linia[(int)$sort_promocje-1]) ) {
                        if (isset($_POST['promocje']) && $filtr->process($_POST['promocje']) == '1' && $linia[(int)$sort_warunki-1] == 'nie' && (int)$sort_promocje > 0) {
                            $pola[] = array('coupons_specials','0'); 
                          } else {
                            $pola[] = array('coupons_specials','1'); 
                        }      
                    }
                    
                    /* ograniczenia */
                    
                    $byloOgraniczenie = false;
                    
                    $rodzajOgraniczenia = '';
                    $IdOgraniczenia = '';

                    // jezeli jest ograniczenie
                    if ( isset($linia[(int)$sort_warunki-1]) ) {
                        if (isset($_POST['warunki']) && $filtr->process($_POST['warunki']) == '1' && (int)$sort_warunki > 0 && ($linia[(int)$sort_warunki-1] == 'kategorie' || $linia[(int)$sort_warunki-1] == 'producenci' || $linia[(int)$sort_warunki-1] == 'produkty')) {                      
                            $rodzajOgraniczenia = $linia[(int)$sort_warunki-1]; 
                        }    
                    }
                    
                    // jezeli jest ograniczenie
                    if ( isset($linia[(int)$sort_warunki_id-1]) ) {
                        if (isset($_POST['warunki_id']) && $filtr->process($_POST['warunki_id']) == '1' && (int)$sort_warunki_id > 0 && !empty($linia[(int)$sort_warunki_id-1])) {   
                            //
                            $tablicaSpr = array();
                            $podzial = explode(',', $linia[(int)$sort_warunki_id-1]);
                            foreach ($podzial as $id) {
                                if ( (int)$id > 0 ) {
                                     $tablicaSpr[] = $id;
                                }
                            }
                            //
                            if ( count($tablicaSpr) > 0 ) {
                                 $IdOgraniczenia = implode(',', $tablicaSpr);
                            }
                        }  
                    }
                    
                    if ( $rodzajOgraniczenia != '' && $IdOgraniczenia != '' ) {
                         //
                         $pola[] = array('coupons_exclusion',$rodzajOgraniczenia);
                         $pola[] = array('coupons_exclusion_id',$IdOgraniczenia);
                         //
                    }
                    
                    unset($byloOgraniczenie, $tablicaSpr, $rodzajOgraniczenia, $IdOgraniczenia);
                      
                    $pola[] = array('coupons_date_added','now()');
                    $pola[] = array('coupons_status','1');
                    //
                    $db->insert_query('coupons' , $pola); 
                    //
                    unset($pola);                    
                }
                
                $db->close_query($sql);

            }

        }        

        Funkcje::PrzekierowanieURL('kupony.php');
        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Import danych</div>
    <div id="cont">
    
          <form action="kupony/kupony_import.php" method="post" id="kuponyForm" class="cmxform" enctype="multipart/form-data">   

          <script type="text/javascript">
          //<![CDATA[
          $(function(){
             $('#upload').MultiFile({
              max: 1,
              accept:'txt|csv',
              STRING: {
               denied:'Nie można przesłać pliku w tym formacie $ext!',
               selected:'Wybrany plik: $file',
              }
             }); 
          });
          //]]>
          </script>          

          <div class="poleForm">
            <div class="naglowek">Import danych</div>
            
            <div class="pozycja_edytowana">
                
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                <div class="naglowek_export">Jakie dane będą importowane ?</div>
                
                <div style="padding:4px">
                    <table class="tbl_poz">
                    
                        <tr class="pozi_nagl">
                            <td style="text-align:left"><span>Pole do importu</span></td>
                            <td><span>Kolejność pola w pliku</span></td>
                        </tr>                 
                    
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="kod" value="1" checked="checked" /> kod kuponu</td>
                            <td><input type="text" size="2" name="kod_sort" value="1" /></td>
                        </tr>
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="opis" value="1" class="toolTipTop" title="Opis kuponu - widoczny tylko dla administratora sklepu" /> opis</td>
                            <td><input type="text" size="2" name="opis_sort" value="2" /></td>
                        </tr>
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="rodzaj" value="1" class="toolTipTop" title="Dopuszczalne wartości - słowo: kwota lub procent - jeżeli pole będzie zawierało inny zapis kupon nie zostanie dodany" /> rodzaj kuponu (kwota/procent)</td>
                            <td><input type="text" size="2" name="rodzaj_sort" value="3" /></td>
                        </tr>                    
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="znizka" value="1" class="toolTipTop" title="Zapis cyfrowy powyżej 0.01" /> zniżka</td>
                            <td><input type="text" size="2" name="znizka_sort" value="4" /></td>
                        </tr>
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="waznosc_od" value="1" class="toolTipTop" title="Data w formacie dzien-miesiąc-rok np 15-04-2012" /> ważność od (format dd-mm-rrrr)</td>
                            <td><input type="text" size="2" name="waznosc_od_sort" value="5" /></td>
                        </tr>
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="waznosc_do" value="1" class="toolTipTop" title="Data w formacie dzien-miesiąc-rok np 15-04-2012" /> ważność do (format dd-mm-rrrr)</td>
                            <td><input type="text" size="2" name="waznosc_do_sort" value="6" /></td>
                        </tr> 
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="grupa_klientow" value="1" class="toolTipTop" title="Zapis cyfrowy powyżej 1 (jeżeli jest dodawane kilka grup poszczególne id muszą być rozdzielone przecinkami)" /> id grupy klientów</td>
                            <td><input type="text" size="2" name="grupa_klientow_sort" value="7" /></td>
                        </tr>                         
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="ilosc_produktow" value="1" class="toolTipTop" title="Zapis cyfrowy powyżej 1" /> minimalna ilość produktów</td>
                            <td><input type="text" size="2" name="ilosc_produktow_sort" value="8" /></td>
                        </tr> 
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="wartosc_zamowienia" value="1" class="toolTipTop" title="Zapis cyfrowy powyżej 0.01" /> minimalna wartość zamówienia</td>
                            <td><input type="text" size="2" name="wartosc_zamowienia_sort" value="9" /></td>
                        </tr>
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="ilosc_kuponow" value="1" class="toolTipTop" title="Zapis cyfrowy powyżej 1" /> ilość dostępnych kuponów</td>
                            <td><input type="text" size="2" name="ilosc_kuponow_sort" value="10" /></td>
                        </tr>
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="promocje" value="1" class="toolTipTop" title="Dopuszczalne wartości - słowo: tak lub nie" /> produkty promocyjne</td>
                            <td><input type="text" size="2" name="promocje_sort" value="11" /></td>
                        </tr>   
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="warunki" value="1" class="toolTipTop" title="Dopuszczalne wartości - słowo: kategorie, producenci lub produkty" /> ograniczenia typ (kategorie/producenci/produkty)</td>
                            <td><input type="text" size="2" name="warunki_sort" value="12" /></td>
                        </tr>
                        <tr class="inputy">
                            <td style="text-align:left"><input type="checkbox" name="warunki_id" value="1" class="toolTipTop" title="Id kategorii, producentów lub produktów - rozdzielone przecinkami" /> ograniczenia id (id muszą być oddzielone przecinkiem)</td>
                            <td><input type="text" size="2" name="warunki_id_sort" value="13" /></td>
                        </tr>                        
                    </table>
                </div>
                
                <p style="padding:12px;">
                    <label>Separator pól:</label>
                    <input type="radio" name="sep" value=";" checked="checked" /> ; (średnik) &nbsp;
                    <input type="radio" name="sep" value=":" /> : (dwukropek) &nbsp;
                    <input type="radio" name="sep" value="#" /> # (płotek)
                </p>
                
                <p style="padding:12px;">
                  <label>Plik do importu:</label>
                  <input type="file" name="file" id="upload" size="53" />
                </p>

                <div class="legnedaKuponow">
                
                    <span class="maleInfo" style="margin-left:0px">Maksymalna wielkość pliku do wczytania: <?php echo Funkcje::MaxUpload(); ?> Mb</span>
                
                    <div class="ostrzezenie">Jeżeli w bazie będzie istniał kupon o importowanym numerze import danego kuponu nie zostanie wykonany.</div> <br />
                    <div class="ostrzezenie">Jeżeli importowany kupon nie będzie miał numeru nie zostanie dodany.</div> <br />
                    <div class="ostrzezenie">Jeżeli nie będzie podany rodzaj kuponu sklep przyjmie domyślnie wartość kwotową.</div> <br />
                    <div class="ostrzezenie">Jeżeli nie będzie podana ilość dostępnych kuponów system wstawi domyślne 1.</div> <br />
                    <div class="ostrzezenie">Jako data dodania zostanie wstawiona dzisiejsza data.</div>
                    <div class="ostrzezenie">Jeżeli jest dodawany typ ograniczenia użycia kuponu (kategorie, producenci lub produkty) muszą być podane również id (kategorii, producentów lub produktów) dla ograniczenia.</div>
                </div>
                
                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Importuj dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>           
            </div>                 


          </div>                      
          </form>

    </div>    

    <?php
    include('stopka.inc.php');

}
?>