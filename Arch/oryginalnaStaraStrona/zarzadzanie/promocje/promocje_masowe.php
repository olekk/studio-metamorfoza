<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['napraw']) ) {
    //
    $_POST['akcja'] = 'zmiana_cen';
    $_POST['cena_1'] = 'x*1';
    for ($x = 2; $x <= ILOSC_CEN; $x++) {
         $_POST['cena_' . $x] = 'x*1';
    }
    //
}

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_promocji']) && ($_POST['akcja_promocji'] == 'dodaj' || $_POST['akcja_promocji'] == 'usun')) {
    
        // pobieranie informacji o vat
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

        //
        $DodatkoweCeny = '';
        for ($x = 2; $x <= ILOSC_CEN; $x++) {
            //
            $DodatkoweCeny .= 'p.products_price_'.$x.', p.products_price_tax_'.$x.', p.products_tax_'.$x.', p.products_old_price_'.$x . ',';
            //
        }        
        //
        if (isset($_POST['id_kat']) && count($_POST['id_kat']) > 0) {
            //
            $zapytanie = "select distinct p.products_id, 
                                          p.products_price, 
                                          p.products_price_tax, 
                                          p.products_tax, 
                                          p.products_old_price, 
                                          p.specials_status,
                                          p.products_tax_class_id,
                                          " . $DodatkoweCeny . "
                                          pc.products_id,
                                          pc.categories_id,
                                          pd.products_name
                                     from products p
                                left join products_to_categories pc ON pc.products_id = p.products_id
                                left join products_description pd ON pd.products_id = p.products_id
                                      and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' ";

            $zapytanie .= " where pc.categories_id in (";
            //
            $tablica_kat = $_POST['id_kat'];
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                //
                $zapytanie .= $tablica_kat[$q] . ',';
                //       
            } 
            unset($tablica_kat);
            //
            $zapytanie = substr($zapytanie,0,-1) . ')';
            //
        } else {
            //
            $zapytanie = "select distinct p.products_id, 
                                          p.products_price, 
                                          p.products_price_tax, 
                                          p.products_tax, 
                                          p.products_old_price, 
                                          p.specials_status,
                                          p.products_tax_class_id,
                                          " . $DodatkoweCeny . "
                                          pd.products_name
                                     from products p, products_description pd 
                                    where p.products_id = pd.products_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'";       
            //
        }

        // jezeli jest wybrany producent
        if (isset($_POST['id_producent']) && count($_POST['id_producent']) > 0) {
            //
            $zapytanie .= " and p.manufacturers_id in (";
            //
            $tablica_producent = $_POST['id_producent'];
            for ($q = 0, $c = count($tablica_producent); $q < $c; $q++) {
                //
                $zapytanie .= $tablica_producent[$q] . ',';
                //       
            } 
            unset($tablica_producent);
            //
            $zapytanie = substr($zapytanie,0,-1) . ')';
            //
        }
        
        // jezeli jest promocja
        if (isset($_POST['pomin_promocje']) && (int)$_POST['pomin_promocje'] == 1) {     
            $zapytanie .= " and p.specials_status = '0'";
        }    

        // jezeli jest cena od
        if (isset($_POST['cena_od']) && (int)$_POST['cena_od'] > 0) {     
            $zapytanie .= " and p.products_price_tax >= " . (int)$_POST['cena_od'];
        }
        // jezeli jest cena do
        if (isset($_POST['cena_do']) && (int)$_POST['cena_do'] > 0) {     
            $zapytanie .= " and p.products_price_tax <= " . (int)$_POST['cena_do'];
        }        
        
        if (isset($_POST['nazwa']) && !empty($_POST['nazwa'])) {
            $szukana_wartosc = $filtr->process($_POST['nazwa']);
            $zapytanie .= " and pd.products_name like '%".$szukana_wartosc."%'";
            unset($szukana_wartosc);
        }
        
        // jezeli jest status
        if (isset($_POST['status']) && (int)$_POST['status'] == 1) {     
            $zapytanie .= " and p.products_status = '1'";
        }    
        // jezeli jest status
        if (isset($_POST['status_wylaczony']) && (int)$_POST['status_wylaczony'] == 1) {     
            $zapytanie .= " and p.products_status = '0'";
        } 

        // jezeli jest vat
        if (isset($_POST['vat']) && (int)$_POST['vat'] != 'x') {     
            $zapytanie .= " and p.products_tax_class_id = " . (int)$_POST['vat'];
        }        
        // jezeli jest waluta
        if (isset($_POST['waluta']) && (int)$_POST['waluta'] != 'x') {     
            $zapytanie .= " and p.products_currencies_id  = " . (int)$_POST['waluta'];
        }  
        
        // jezeli jest usuwanie promocji
        if (isset($_POST['akcja_promocji']) && $_POST['akcja_promocji'] == 'usun') {     
            $zapytanie .= " and p.specials_status = '1'";
        }      

        // grupowanie produktow
        $zapytanie .= ' group by p.products_id ';

        // wykonanie zapytania
        $sql = $db->open_query($zapytanie);
        $Przetworzono = 0;
        //
        $BylaAktualizacja = false;
        //

        while ($info = $sql->fetch_assoc()) {
            //
            $pola = array();
            //
            // jezeli dodawanie promocji
            if ( $_POST['akcja_promocji'] == 'dodaj' ) {
                //
                $iloscCen = 1;
                if (isset($_POST['ilosc_cen']) && (int)$_POST['ilosc_cen'] == 1) {   
                    $iloscCen = ILOSC_CEN;
                }
                //
                for ($x = 1; $x <= $iloscCen; $x++) {
                    //
                    $cenaBruttoBaza = $info['products_price_tax' . (($x == 1) ? '' : '_'.$x)];
                    //
                    //
                    // jezeli obnizona zostaje cena glowna i przeniesione do poprzedniej
                    if ( $_POST['cena_promocji'] == 'cena_glowna' ) {
                    
                        //
                        $wielkoscObnizki = (float)$_POST['rabat_ceny_glownej'];
                        $brutto = 0;
                        $netto = 0;
                        $podatek = 0;
                        //
                        if ( $wielkoscObnizki > 0 && $cenaBruttoBaza > 0 ) {
                             //
                             switch ($_POST['rabat_ceny_glownej_rodzaj']) {
                                case 'liczba':
                                    //
                                    $brutto = $cenaBruttoBaza - $wielkoscObnizki;
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $brutto = ceil($brutto);
                                    }                            
                                    //
                                    $netto = round( $brutto / (1 + ($tablicaVat[$info['products_tax_class_id']]/100)), 2);
                                    $podatek = $brutto - $netto;
                                    //
                                    break;
                                case 'procent':
                                    //
                                    $brutto = $cenaBruttoBaza - ( $cenaBruttoBaza * ($wielkoscObnizki / 100) );
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $brutto = ceil($brutto);
                                    }                            
                                    //
                                    $netto = round( $brutto / (1 + ($tablicaVat[$info['products_tax_class_id']]/100)), 2);
                                    $podatek = $brutto - $netto;
                                    //
                                    break;
                             }
                             //
                        }
                        //
                        if ( $brutto > 0 && $netto > 0 ) {
                            //
                            $pola[] = array('products_price_tax' . (($x == 1) ? '' : '_'.$x), $brutto);
                            $pola[] = array('products_price' . (($x == 1) ? '' : '_'.$x), $netto);
                            $pola[] = array('products_tax' . (($x == 1) ? '' : '_'.$x), $podatek);     
                            //
                            $pola[] = array('specials_status','1'); 
                            $pola[] = array('products_old_price' . (($x == 1) ? '' : '_'.$x), $cenaBruttoBaza);
                            //
                            if ( $x == 1 ) {
                                 $BylaAktualizacja = true;
                            }
                            //
                        }
                        //
                        unset($brutto, $netto, $podatek, $wielkoscObnizki);
                        //
                    }
                    
                    // jezeli obnizona zostaje cena glowna i przeniesione do poprzedniej
                    if ( $_POST['cena_promocji'] == 'cena_poprzednia' ) {
                    
                        //
                        $wielkoscPodwyzki = (float)$_POST['narzut_ceny_poprzedniej'];
                        //
                        if ( $wielkoscPodwyzki > 0 && $cenaBruttoBaza > 0 ) {
                             //
                             switch ($_POST['narzut_ceny_poprzedniej_rodzaj']) {
                                case 'liczba':
                                    //
                                    $bruttoPoprzednia = $cenaBruttoBaza + $wielkoscPodwyzki;
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $bruttoPoprzednia = ceil($bruttoPoprzednia);
                                    }                            
                                    //
                                    break;
                                case 'procent':
                                    //
                                    $bruttoPoprzednia = $cenaBruttoBaza + ( $cenaBruttoBaza * ($wielkoscPodwyzki / 100) );
                                    //
                                    // zaokraglanie do pelnych kwot
                                    if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                                        $bruttoPoprzednia = ceil($bruttoPoprzednia);
                                    }                            
                                    //
                                    break;
                             }
                             //
                        }
                        //
                        if ( $bruttoPoprzednia > $cenaBruttoBaza ) {    
                            //
                            $pola[] = array('specials_status', '1'); 
                            $pola[] = array('products_old_price' . (($x == 1) ? '' : '_'.$x), $bruttoPoprzednia);
                            //
                            if ( $x == 1 ) {
                                 $BylaAktualizacja = true;
                            }
                            //
                        }
                        //
                        unset($bruttoPoprzednia, $wielkoscPodwyzki);
                        //
                    }  
                    
                }
                //
                if ( count($pola) > 0 ) {
                    //
                    // daty rozpoczecia i zakonczenia
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
                }
                //
            }
            
            // jezeli usuwanie promocji
            if ( $_POST['akcja_promocji'] == 'usun' ) {
                //
                // jezeli bez zmian cen tylko usuniecie promocji
                if ( $_POST['usuwanie_tryb'] == '0' ) { 
                    //
                    $pola[] = array('specials_status','0'); 
                    $pola[] = array('products_old_price','');
                    $pola[] = array('specials_date','');
                    $pola[] = array('specials_date_end','');
                    //
                    for ($x = 2; $x <= ILOSC_CEN; $x++) {
                        //
                        $pola[] = array('products_old_price_'.$x,'');
                        //
                    }
                    //
                    // jezeli przywrocenie ceny z poprzedniej
                  } else {
                    //
                    $pola = array(array('specials_status','0'),
                                  array('products_old_price','0'),
                                  array('specials_date',''),
                                  array('specials_date_end',''));            
                    //                         
                    $wartosc = $info['products_old_price'];
                    $netto = round( $wartosc / (1 + ($tablicaVat[$info['products_tax_class_id']]/100)), 2);
                    $podatek = $wartosc - $netto;
                    //
                    $pola[] = array('products_price_tax',$wartosc);
                    $pola[] = array('products_price',$netto);
                    $pola[] = array('products_tax',$podatek);  
                    //
                    unset($wartosc, $netto, $podatek);
                    //
                    // ceny dla pozostalych poziomow cen
                    for ($x = 2; $x <= ILOSC_CEN; $x++) {
                        //
                        // cena poprzednia
                        if ( $info['products_old_price_'.$x] > 0 ) {
                            //
                            $wartosc = $info['products_old_price_'.$x];
                            $netto = round( $wartosc / (1 + ($tablicaVat[$info['products_tax_class_id']]/100)), 2);
                            $podatek = $wartosc - $netto;    
                            //
                            $pola[] = array('products_old_price_'.$x,'0');
                            $pola[] = array('products_price_tax_'.$x,$wartosc);
                            $pola[] = array('products_price_'.$x,$netto);
                            $pola[] = array('products_tax_'.$x,$podatek);
                            //    
                            unset($wartosc, $netto, $podatek); 
                            //                
                        }
                        //
                    }             
                    //                     
                }
                //
                $BylaAktualizacja = true;                
                //
            }
            //
            if (count($pola) > 0) {
                $db->update_query('products' , $pola, 'products_id = ' . $info['products_id']);
                $Przetworzono++;
            }
            unset($pola);
            //            
          
        }
        
        if ($BylaAktualizacja == true) {
            //
            Funkcje::PrzekierowanieURL('promocje_masowe.php?suma=' . $Przetworzono);
            //
          } else {
            //
            Funkcje::PrzekierowanieURL('promocje_masowe.php?suma=0');          
            //
        }
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php'); 
    ?>

    <div id="naglowek_cont">Masowa zarządzanie promocjami produktów</div>
    <div id="cont">
    
          <div class="poleForm">
            <div class="naglowek">Wybierz zakres tworzenia lub usuwania promocji</div>
            
                <?php
                if (isset($_GET['suma'])) {
                ?>
                
                <?php if ((int)$_GET['suma'] > 0) { ?>
                
                    <div id="sukcesAktualizacji">
                        Dane zostały przetworzone. <br />
                        Ilość zaktualizowanych produktów: <strong><?php echo (int)$_GET['suma']; ?></strong>
                    </div>
                    
                    <?php } else { ?>
                    
                    <div id="sukcesAktualizacji">
                        Brak danych przetworzenia ...
                    </div>

                <?php } ?>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('promocje','','promocje');">Powrót</button>    
                </div>                 
                
                <?php
                
                } else { 
                
                ?>
                
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                    $('input.datepicker').Zebra_DatePicker({
                       format: 'd-m-Y',
                       inside: false,
                       readonly_element: false
                    });   
                });
                
                function promocje(wartosc) {
                    if (wartosc == 'usun') {
                        $('#promocje_dodawanie').slideUp();
                        $('#pominiecie').slideUp();
                        $('#promocje_usuwanie').slideDown();
                    }
                    if (wartosc == 'dodaj') {
                        $('#promocje_dodawanie').slideDown();
                        $('#pominiecie').slideDown();
                        $('#promocje_usuwanie').slideUp();
                    }  
                    if (wartosc == 'glowna') {
                        $('#obnizenie_ceny_glownej').slideDown();
                        $('#obnizenie_ceny_poprzedniej').slideUp();
                    }
                    if (wartosc == 'poprzednia') {
                        $('#obnizenie_ceny_glownej').slideUp();
                        $('#obnizenie_ceny_poprzedniej').slideDown();
                    }                    
                }
                //]]>
                </script> 
                
                <form action="promocje/promocje_masowe.php" method="post" id="zmiana_ceny" class="cmxform">                 
            
                <div class="tblMasowePromocje">

                    <div>
                        <input type="radio" style="border:0px" onclick="promocje('dodaj')" value="dodaj" name="akcja_promocji" checked="checked" /> utwórz promocje <br />
                        <input type="radio" style="border:0px" onclick="promocje('usun')" value="usun" name="akcja_promocji" /> usuń promocje
                    </div>
                    
                    <div id="promocje_usuwanie">
                        <input type="radio" name="usuwanie_tryb" value="0" checked="checked" /> usuń promocje bez zmian cen produktów (zostanie usunięty atrybut promocji oraz cena poprzednia) <br />   
                        <input type="radio" name="usuwanie_tryb" value="1" /> usuń promocje i zmień cenę na poprzednią (zostanie usunięty atrybut promocji i w jako cena produktu przypisana cena poprzednia)                    
                    </div>
                    
                    <div id="promocje_dodawanie">
                    
                        <div>
                            <input type="radio" style="border:0px" onclick="promocje('glowna')" value="cena_glowna" name="cena_promocji" checked="checked" /> z obniżeniem ceny produktów (aktualna cena produktu zostanie przeniesienia do <b>ceny poprzedniej</b> i cena produktu zostanie obniżona o ustaloną wartość) <br />
                            <input type="radio" style="border:0px" onclick="promocje('poprzednia')" value="cena_poprzednia" name="cena_promocji" /> bez obniżania ceny produktów (zostanie tylko dodana <b>cena poprzednia</b> produktu o ustalonej wartości)
                        </div> 

                        <div>
                            <p id="obnizenie_ceny_glownej">
                                <span>obniż cenę produktów o &nbsp; <input type="text" value="" size="5" name="rabat_ceny_glownej" /></span>
                                <span><input type="radio" style="border:0px" value="liczba" name="rabat_ceny_glownej_rodzaj" checked="checked" /> stała liczba</span>
                                <span><input type="radio" style="border:0px" value="procent" name="rabat_ceny_glownej_rodzaj" /> wartość procentowa</span>                
                            </p>
                            
                            <p id="obnizenie_ceny_poprzedniej" style="display:none">
                                <span>dodaj do ceny poprzedniej &nbsp; <input type="text" value="" size="5" name="narzut_ceny_poprzedniej" /></span>
                                <span><input type="radio" style="border:0px" value="liczba" name="narzut_ceny_poprzedniej_rodzaj" checked="checked" /> stała liczba</span>
                                <span><input type="radio" style="border:0px" value="procent" name="narzut_ceny_poprzedniej_rodzaj" /> wartość procentowa</span>
                            </p>
                            
                            <input type="checkbox" name="pelne" value="1" /> zaokrąglij nowe ceny brutto do pełnych kwot <br />   

                            <input type="checkbox" name="ilosc_cen" value="1" /> jeżeli produkt ma kilka poziomów cen ustaw cenę promocyjną dla wszystkich poziomów cen (przy wyłączonej opcji przeliczona będzie tylko cena nr 1)
                        </div> 

                        <div>
                            data rozpoczęcia &nbsp; <input type="text" id="data_promocja_od" name="data_promocja_od" value="" size="20"  class="datepicker" />
                            &nbsp; &nbsp; 
                            godz: 
                            <select name="data_promocja_od_godzina">
                            <?php
                            for ($c = 0;$c < 24; $c++) { 
                                echo '<option value="'.$c.'">'.$c.'</option>'; 
                            } 
                            unset($godz);
                            ?>
                            </select>
                            min: 
                            <select name="data_promocja_od_minuty">
                            <?php
                            for ($c = 0;$c < 6; $c++) { 
                                echo '<option value="'.($c*10).'">'.($c*10).'</option>'; 
                            } 
                            unset($min);
                            ?>
                            </select>                                              
                        </div>    

                        <div>
                            data zakończenia &nbsp; <input type="text" id="data_promocja_do" name="data_promocja_do" value="" size="20" class="datepicker" />
                            &nbsp; &nbsp; 
                            godz: 
                            <select name="data_promocja_do_godzina">
                            <?php
                            for ($c = 0;$c < 24; $c++) { 
                                echo '<option value="'.$c.'">'.$c.'</option>'; 
                            } 
                            unset($godz);
                            ?>
                            </select>
                            min: 
                            <select name="data_promocja_do_minuty">
                            <?php
                            for ($c = 0;$c < 6; $c++) { 
                                echo '<option value="'.($c*10).'">'.($c*10).'</option>'; 
                            } 
                            unset($min);
                            ?>                        
                            </select>                                              
                        </div>

                    </div>

                </div>
                
                <div class="naglowek" style="margin:10px;">Dodatkowe parametry dla tworzony <b>promocji</b></div>
                
                <table id="dodWarunki">
                    <tr>
            
                        <td>
                        
                            <span class="maleInfo" style="margin-left:0px">Promocje tylko dla wybranych kategorii</span>

                            <div id="drzewo">
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
                                            <td class="lfp">
                                                <input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" /> '.$tablica_kat[$w]['text'].'
                                            </td>
                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                          </tr>
                                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                }
                                echo '</table>';
                                unset($tablica_kat,$podkategorie);                     
                                ?> 
                            </div>  
                            
                        </td>
                        
                        <td>
                            
                            <span class="maleInfo">Promocje tylko dla wybranych producentów</span>
                        
                            <div id="producent">
                        
                            <?php
                            $Prd = Funkcje::TablicaProducenci();
                            //
                            if (count($Prd) > 0) {
                                //
                                echo '<table class="pkc">';
                                //
                                for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                    echo '<tr>                                
                                            <td class="lfp">
                                                <input type="checkbox" value="'.$Prd[$b]['id'].'" name="id_producent[]" /> '.$Prd[$b]['text'].'
                                            </td>                                
                                          </tr>';
                                }
                                echo '</table>';
                                //
                            }
                            unset($Prd);
                            ?> 
                            
                            </div>
                            
                        </td>  

                        <td>
                            
                            <span class="maleInfo">Dodatkowe parametry</span>
                        
                            <div id="inne">

                                <input type="checkbox" style="border:0px" value="1" name="status" /> <b>tylko aktywne produkty</b> <br />
                                <input type="checkbox" style="border:0px" value="1" name="status_wylaczone" /> tylko nieaktywne produkty <br />
                                <div id="pominiecie">
                                    <input type="checkbox" style="border:0px" value="1" name="pomin_promocje" /> <span style="color:#ff0000">pomiń produkty które już są w promocji</span>
                                </div>

                                <br />
                                
                                Produkty z ceną brutto od: <input type="text" size="5" class="calkowita" name="cena_od" /> do: <input type="text" size="5" class="calkowita" name="cena_do" />
                                
                                <br /><br />
                                
                                <table id="ciagZnakow">
                                    <tr>
                                        <td>
                                            Produkt ma mieć w nazwie ciąg znaków (tylko w języku polskim): 
                                        </td>
                                        <td>
                                            <input type="text" size="25" name="nazwa" />               
                                        </td>
                                    </tr>
                                </table>
                                
                                <br />
                                
                                Produkty ze stawką VAT:
                                
                                <?php
                                // pobieranie informacji o vat
                                $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
                                $sqls = $db->open_query($zapytanie_vat);
                                //
                                $tablica = array();
                                $tablica[] = array('id' => 'x', 'text' => '-');
                                //
                                while ($infs = $sqls->fetch_assoc()) { 
                                    $tablica[] = array('id' => $infs['tax_rates_id'], 'text' => $infs['tax_description']);
                                }
                                $db->close_query($sqls);
                                unset($zapytanie_vat, $infs);  
                                //             
                                echo Funkcje::RozwijaneMenu('vat', $tablica, 'x'); 
                                unset($tablica);
                                ?>
                                
                                <br /><br />
                                
                                Produkty z cenami w walucie:
                                
                                <?php
                                $sqls = $db->open_query("select * from currencies");  
                                //
                                $tablica = array();
                                $tablica[] = array('id' => 'x', 'text' => '-');
                                //
                                while ($infs = $sqls->fetch_assoc()) { 
                                    $tablica[] = array('id' => $infs['currencies_id'], 'text' => $infs['title']);
                                }
                                $db->close_query($sqls);
                                unset($infs);  
                                //             
                                echo Funkcje::RozwijaneMenu('waluta', $tablica, 'x'); 
                                unset($tablica);
                                ?> 
                                
                            </div>
                            
                        </td>                         

                    </tr>
                </table>
                
                <div class="ostrzezenie" style="margin:5px 0px 5px 20px">
                    Zatwierdzenie aktualizacji danych spowoduje zmianę cen produktów. Operacji nie można cofnąć ! <br />
                    Zalecane jest wykonanie kopii bazy danych przed dokonaniem zmian.
                </div>
                    
                <div style="padding:10px">
                     <input type="submit" class="przyciskBut" value="Wykonaj operację" />
                     <button type="button" class="przyciskNon" onclick="cofnij('promocje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','promocje');">Powrót</button>   
                </div>                
                
                </form>
                
                <?php } ?>
            
          </div>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>