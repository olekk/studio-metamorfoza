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

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zmiana_cen') {
    
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
            $DodatkoweCeny .= 'p.products_price_'.$x.', p.products_price_tax_'.$x.', p.products_tax_'.$x.', p.products_old_price_'.$x . ', p.products_retail_price_' . $x . ', ';
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
                                          p.products_retail_price,
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
                                          p.products_retail_price,
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
        
        // jezeli jest nowosc
        if (isset($_POST['nowosci']) && (int)$_POST['nowosci'] == 1) {     
            $zapytanie .= " and p.new_status = '1'";
        }
        // jezeli jest hit
        if (isset($_POST['hit']) && (int)$_POST['hit'] == 1) {     
            $zapytanie .= " and p.star_status = '1'";
        }
        // jezeli jest promocja
        if (isset($_POST['promocje']) && (int)$_POST['promocje'] == 1) {     
            $zapytanie .= " and p.specials_status = '1'";
        }  
        // jezeli jest polecany
        if (isset($_POST['polecane']) && (int)$_POST['polecane'] == 1) {     
            $zapytanie .= " and p.featured_status = '1'";
        } 
        // jezeli jest negocjacja
        if (isset($_POST['negocjacja']) && (int)$_POST['negocjacja'] == 1) {     
            $zapytanie .= " and p.products_make_an_offer = '1'";
        }      
        
        // jezeli jest negocjacja
        if (isset($_POST['cena_katalogowa']) && (int)$_POST['cena_katalogowa'] == 1) {     
            $zapytanie .= " and p.products_retail_price > 0";
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

            for ($x = 1; $x <= ILOSC_CEN; $x++) {
                //
                if (!empty($_POST['cena_'.$x])) {
                    //
                    $wzor = $filtr->process($_POST['cena_'.$x]);
                    $cenaBruttoBaza = $info['products_price_tax'.(($x == 1) ? '' : '_'.$x)];
                    $cenaNettoBaza = $info['products_price'.(($x == 1) ? '' : '_'.$x)];
                    //
                    // zamiana
                    for ($p = 1; $p <= ILOSC_CEN; $p++) {
                        //
                        if ( strpos($_POST['cena_'.$x], 'x' . $p) > -1 ) {
                             $cenaBruttoBaza = $info['products_price_tax'.(($p == 1) ? '' : '_'.$p)];
                             $cenaNettoBaza = $info['products_price'.(($p == 1) ? '' : '_'.$p)];                         
                             $wzor = str_replace('x' . $p, 'x', $wzor);
                        }
                        //
                    }                
                    //
                    if ($cenaBruttoBaza > 0) {
                        //
                        if (strpos($wzor, 'x') > -1) {
                            eval( '$brutto = ' . str_replace('x', $cenaBruttoBaza, $filtr->process($_POST['cena_'.$x])) .';' );
                            $brutto = round($brutto, 2);
                          } else {
                            $brutto = round($wzor, 2);
                        }
                        // zaokraglanie do pelnych kwot
                        if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                            $brutto = ceil($brutto);
                        }
                        //
                        $netto = round( $brutto / (1 + ($tablicaVat[$info['products_tax_class_id']]/100)), 2);
                        $podatek = $brutto - $netto;
                        //                       
                    } else if ($cenaNettoBaza > 0) {
                        //
                        if (strpos($wzor, 'x') > -1) {
                            eval( '$netto = ' . str_replace('x', $cenaNettoBaza, $filtr->process($_POST['cena_'.$x])) .';' );
                            $netto = round($netto, 2);
                          } else {
                            $netto = round($wzor, 2);
                        }
                        // zaokraglanie do pelnych kwot
                        if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                            $netto = ceil($netto);
                        }
                        //
                        $brutto = round( $netto * (1 + ($tablicaVat[$info['products_tax_class_id']]/100)), 2);
                        $podatek = $brutto - $netto;
                        //                                        
                    }
                    
                    if ( $cenaBruttoBaza > 0 || $cenaNettoBaza > 0 ) {
                        //
                        $pola[] = array('products_price_tax' . (($x == 1) ? '' : '_'.$x),$brutto);
                        $pola[] = array('products_price' . (($x == 1) ? '' : '_'.$x),$netto);
                        $pola[] = array('products_tax' . (($x == 1) ? '' : '_'.$x),$podatek);
                        //
                        unset($brutto, $netto, $podatek, $cenaBruttoBaza);                
                        //
                        $BylaAktualizacja = true;
                        //                     
                    }
                    //
                    unset($wzor, $cenaBruttoBaza, $cenaNettoBaza);
                    //
                }            
                //
                // jezeli jest cena promocyjna
                if (!empty($_POST['cena_poprzednia_'.$x]) && $info['specials_status'] ==  '1') {
                    //
                    $wzor = $filtr->process($_POST['cena_poprzednia_'.$x]);
                    $cenaBruttoBaza = $info['products_old_price' . (($x == 1) ? '' : '_'.$x)];
                    //
                    // zamiana
                    for ($p = 1; $p <= ILOSC_CEN; $p++) {
                        //
                        if ( strpos($_POST['cena_poprzednia_'.$x], 'x' . $p) > -1 ) {
                             $cenaBruttoBaza = $info['products_price_tax' . (($p == 1) ? '' : '_'.$p)];
                             $wzor = str_replace('x' . $p, 'x', $wzor);
                        }
                        //
                    }
                    //
                    if ($cenaBruttoBaza > 0) {
                        //        
                        if (strpos($wzor, 'x') > -1) {
                            eval( '$brutto = ' . str_replace('x', $cenaBruttoBaza, $filtr->process($_POST['cena_poprzednia_'.$x])) .';' );
                            $brutto = round($brutto, 2);  
                          } else {
                            $brutto = round($wzor, 2);  
                        }
                        // zaokraglanie do pelnych kwot
                        if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                            $brutto = ceil($brutto);
                        }
                        //
                        $pola[] = array('products_old_price' . (($x == 1) ? '' : '_'.$x),$brutto);
                        //
                        unset($brutto, $cenaBruttoBaza);                      
                        //
                        $BylaAktualizacja = true;
                        //
                    }
                    //        
                    unset($wzor, $cenaBruttoBaza);
                    //
                } 
                //
                // jezeli jest cena katalogowa
                if (!empty($_POST['cena_katalogowa_'.$x])) {
                    //
                    $wzor = $filtr->process($_POST['cena_katalogowa_'.$x]);
                    $cenaBruttoBaza = $info['products_retail_price' . (($x == 1) ? '' : '_'.$x)];
                    //
                    // zamiana
                    for ($p = 1; $p <= ILOSC_CEN; $p++) {
                        //
                        if ( strpos($_POST['cena_katalogowa_'.$x], 'x' . $p) > -1 ) {
                             $cenaBruttoBaza = $info['products_price_tax' . (($p == 1) ? '' : '_'.$p)];
                             $wzor = str_replace('x' . $p, 'x', $wzor);
                        }
                        //
                    }
                    //
                    if ($cenaBruttoBaza > 0) {
                        //        
                        if (strpos($wzor, 'x') > -1) {
                            eval( '$brutto = ' . str_replace('x', $cenaBruttoBaza, $filtr->process($_POST['cena_katalogowa_'.$x])) .';' );
                            $brutto = round($brutto, 2);  
                          } else {
                            $brutto = round($wzor, 2);  
                        }
                        // zaokraglanie do pelnych kwot
                        if (isset($_POST['pelne']) && (int)$_POST['pelne'] == 1) {   
                            $brutto = ceil($brutto);
                        }
                        //
                        $pola[] = array('products_retail_price' . (($x == 1) ? '' : '_'.$x),$brutto);
                        //
                        unset($brutto, $cenaBruttoBaza);                      
                        //
                        $BylaAktualizacja = true;
                        //
                    }
                    //        
                    unset($wzor, $cenaBruttoBaza);
                    //          
                } 
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
            if ( isset($_GET['napraw']) ) {
                 Funkcje::PrzekierowanieURL('masowa_zmiana_cen.php?naprawa');
               } else {
                 Funkcje::PrzekierowanieURL('masowa_zmiana_cen.php?suma=' . $Przetworzono);
            }
            //
          } else {
            //
            Funkcje::PrzekierowanieURL('masowa_zmiana_cen.php?suma=0');          
            //
        }
    }
    
    // wczytanie naglowka HTML
    include('naglowek.inc.php'); 
    ?>
    
    <script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {

        $.validator.addMethod("wzor", 
                             function(value, element) {
                                 var wzor = /^x?([0-9])?(\*([0-9]+\.?[0-9]*))?([+\-]?[0-9]+\.?[0-9]*)?$/i                                    
                                 return wzor.test( value );
                             }, 
                             "Nieprawidłowy wzór na zmianę cen"
        );          
      
        $("#zmiana_ceny").validate({
          rules: {
            <?php
            for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
            cena_<?php echo $x; ?>: {
              wzor: true
            },
            <?php } ?> 
            <?php
            for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
            cena_poprzednia_<?php echo $x; ?>: {
              wzor: true
            },     
            <?php } ?>
          },
          messages: {
            <?php
            for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
            cena_<?php echo $x; ?>: {
              wzor: "Nieprawidłowy wzór na zmianę cen"
            },
            <?php } ?> 
            <?php
            for ($x = 1; $x <= ILOSC_CEN; $x++) { ?>
            cena_poprzednia_<?php echo $x; ?>: {
              wzor: "Nieprawidłowy wzór na zmianę cen"
            },
            <?php } ?>
          }
        }); 
    });                  
    //]]>
    </script>      

    <div id="naglowek_cont">Masowa zmiana cen produktów</div>
    <div id="cont">
    
          <div class="poleForm">
            <div class="naglowek">Zmiana cen podstawowych wg wzoru</div>
            
                <?php
                if (isset($_GET['suma']) || isset($_GET['naprawa'])) {
                ?>
                
                <?php if ((int)$_GET['suma'] > 0) { ?>
                
                    <div id="sukcesAktualizacji">
                        Dane zostały zaktualizowane. <br />
                        Ilość zaktualizowanych produktów: <strong><?php echo (int)$_GET['suma']; ?></strong>
                    </div>
                    
                    <?php } else if ( isset($_GET['naprawa']) ) { ?>
                    
                    <div id="sukcesAktualizacji">
                        Dane zostały pomyślnie przeliczone i naprawione ...
                    </div>                    
                    
                    <?php } else { ?>
                    
                    <div id="sukcesAktualizacji">
                        Brak danych do aktualizacji ...
                    </div>

                <?php } ?>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('masowa_zmiana_cen','','narzedzia');">Powrót</button>    
                </div>                 
                
                <?php
                
                } else { 
                
                ?>

                <form action="narzedzia/masowa_zmiana_cen.php" method="post" id="zmiana_ceny" class="cmxform">                 
            
                <table class="tblEdycja">
                    <tr>
                    
                        <td class="lewa">

                            <input type="hidden" name="akcja" value="zmiana_cen" />
                        
                            <table style="width:100%">
                            
                                <tr>
                                    <td><b>Cena podstawowa:</b></td>
                                    <td><input type="text" name="cena_1" id="cena_1" size="15" style="width:150px" /></td>
                                </tr>
                                
                                <tr>
                                    <td><b>Cena poprzednia:</b></td>
                                    <td><input type="text" name="cena_poprzednia_1" id="cena_poprzednia_1" size="15" style="width:150px" /></td>
                                </tr>  

                                <tr>
                                    <td><b>Cena katalogowa:</b></td>
                                    <td><input type="text" name="cena_katalogowa_1" id="cena_katalogowa_1" size="15" style="width:150px" /></td>
                                </tr>                                 
                            
                                <?php
                                for ($x = 2; $x <= ILOSC_CEN; $x++) { ?>
                                
                                <tr>
                                    <td colspan="2" style="padding-right:75px"><hr style="border:0px;border-top:1px dotted #cccccc;"></td>
                                </tr>
                                
                                <tr>
                                    <td><b>Cena podstawowa nr <?php echo $x; ?>:</b></td>
                                    <td><input type="text" name="cena_<?php echo $x; ?>" size="15" style="width:150px" /></td>
                                </tr>
                                
                                <tr>
                                    <td><b>Cena poprzednia nr <?php echo $x; ?></b></td>
                                    <td><input type="text" name="cena_poprzednia_<?php echo $x; ?>" size="15" style="width:150px" /></td>
                                </tr> 

                                <tr>
                                    <td><b>Cena katalogowa nr <?php echo $x; ?>:</b></td>
                                    <td><input type="text" name="cena_katalogowa_<?php echo $x; ?>" size="15" style="width:150px" /></td>
                                </tr>                                  
                                <?php } ?>
                                
                                <tr>
                                    <td colspan="2" style="padding-top:15px">
                                        <input type="checkbox" name="pelne" value="1" /> zaokrąglij nowe ceny brutto do pełnych kwot
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td colspan="2" style="padding-top:15px">
                                        <div class="maleInfo" style="margin:0px">
                                            Narzędzie masowej zmiany cen operuje TYLKO na cenach głównych produktów. Nie są zmieniane ceny 
                                            kombinacji cech produktów jeżeli produkty mają wybraną taką opcję. Aktualizację takich cen można przeprowadzić 
                                            poprzez import danych w formacie CSV lub XML.
                                        </div>
                                    </td>
                                </tr>                                
                                
                                <tr>
                                    <td colspan="2" style="padding-top:15px">
                                        <div class="ostrzezenie">
                                            Zatwierdzenie aktualizacji danych spowoduje przeliczenie cen produktów wg zadanych parametrów.
                                            Operacji nie można cofnąć ! Zalecane jest wykonanie kopii bazy danych przed dokonaniem zmian.
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td colspan="2" style="padding-top:10px">
                                        <input type="submit" class="przyciskBut" value="Aktualizuj dane" style="margin-left:0px" />
                                    </td>
                                </tr>
                                
                            </table>

                        </td>
                        
                        <td class="prawa">
                        
                            W polach zmiany ceny jest możliwe zastosowanie wzoru do obliczenia cen. W miejsce znaku <b>x</b> zostanie wstawiona wartość ceny. <br /><br />
                            
                            <div class="przyklad">Przykładowe wzory modyfikacji:</div>
                            
                            <table class="tbl_opis">
                                <tr class="tbl_opis_naglowek">
                                    <td><span>Wzór pola</span></td>
                                    <td><span>Opis wzoru</span></td>
                                    <td><span>Opis</span></td>
                                </tr>                            
                                <tr>
                                    <td></td>
                                    <td>puste pole</td>
                                    <td>ceny pozostaną bez zmian</td>
                                </tr>
                                <tr>
                                    <td>123</td>
                                    <td>liczba</td>
                                    <td>zmiana cen produktów na wpisaną liczbę 123</td>
                                </tr>
                                <tr>
                                    <td>x-10</td>
                                    <td>x, znak plus lub znak minus, liczba</td>
                                    <td>cena zostanie wyliczona wg wzoru: <br />cena - 10</td>
                                </tr> 
                                <tr>
                                    <td>x*1.15</td>
                                    <td>x, znak mnożenia, liczba</td>
                                    <td>cena zostanie wyliczona wg wzoru: <br />cena * 1,15</td>
                                </tr> 
                                <tr>
                                    <td>x*1.15+5.50</td>
                                    <td>x, znak mnożenia, liczba, znak plus lub znak minus, liczb</td>
                                    <td>cena zostanie wyliczona wg wzoru: <br />cena * 1,15 + 5,50</td>
                                </tr>  
                                <tr>
                                    <td>x1*1.1</td>
                                    <td>x1, znak mnożenia, liczba, znak plus lub znak minus, liczb</td>
                                    <td>cena zostanie wyliczona wg wzoru: <br />cena podstawowa * 1,1 - taki wzór można zastosować np do wstawienia cen poprzednich do produktów</td>
                                </tr>  
                                <tr>
                                    <td style="border:0px">x1*0.9</td>
                                    <td style="border:0px">x1, znak mnożenia, liczba, znak plus lub znak minus, liczb</td>
                                    <td style="border:0px">cena zostanie wyliczona wg wzoru: <br />cena podstawowa * 0,9 - taki wzór można zastosować np do przygotowania cen nr 2, które bedą o 10% niższe od poziomu cen nr 1</td>
                                </tr>
                                
                                <tr class="tbl_opis_naglowek">
                                    <td colspan="3"><span>Możliwe zmienne do użycia we wzorach</span></td>
                                </tr>         
                                <tr>
                                    <td><b>x</b></td>
                                    <td colspan="2">wartość ceny w edytowanym polu</td>
                                </tr>
                                <tr>
                                    <td><b>x1</b></td>
                                    <td colspan="2">wartość ceny podstawowej nr 1 - wstawienie takiej zmiennej umożliwia obliczenie innej ceny na podstawie ceny nr 1</td>
                                </tr>  
                                <?php
                                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                                ?>
                                <tr>
                                    <td><b>x<?php echo $x; ?></b></td>
                                    <td colspan="2">wartość ceny podstawowej nr <?php echo $x; ?> - wstawienie takiej zmiennej umożliwia obliczenie innej ceny na podstawie ceny nr <?php echo $x; ?></td>
                                </tr>                                  
                                <?php
                                }
                                ?>
                            </table>
                            
                        </td>
                        
                    </tr>
                </table>
                
                <div class="naglowek" style="margin:10px;">Dodatkowe parametry dla zmiany cen</div>
                
                <table id="dodWarunki">
                    <tr>
            
                        <td>
                        
                            <span class="maleInfo" style="margin-left:0px">Zmiany tylko dla wybranych kategorii</span>

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
                            
                            <span class="maleInfo">Zmiany tylko dla wybranych producentów</span>
                        
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
                            
                                <input type="checkbox" style="border:0px" value="1" name="nowosci" /> tylko nowości <br />
                                <input type="checkbox" style="border:0px" value="1" name="hit" /> tylko nasz hit <br />
                                <input type="checkbox" style="border:0px" value="1" name="promocje" /> tylko promocje <br />
                                <input type="checkbox" style="border:0px" value="1" name="polecane" /> tylko polecane <br />
                                <input type="checkbox" style="border:0px" value="1" name="negocjacja" /> tylko z opcją negocjacji ceny <br /><br />
                                
                                <input type="checkbox" style="border:0px" value="1" name="cena_katalogowa" /> tylko produkty które mają cenę katalogową <br /><br />
                                
                                <input type="checkbox" style="border:0px" value="1" name="status" /> <b>tylko aktywne produkty</b> <br />
                                <input type="checkbox" style="border:0px" value="1" name="status_wylaczone" /> tylko nieaktywne produkty

                                <br /><br />
                                
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
                
                </form>
                
                <?php } ?>
            
          </div>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>