<?php
chdir('../');     

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zmiana_cen') {
    
        $warunek = '';
        //
        if (isset($_POST['id_kat']) && count($_POST['id_kat']) > 0) {
            //
            $zapytanie = "select distinct p.products_id, 
                                          p.products_quantity, 
                                          p.products_price_tax, 
                                          pc.products_id,
                                          pc.categories_id,
                                          pd.products_name
                                     from products p
                                left join products_to_categories pc ON pc.products_id = p.products_id
                                left join products_description pd ON pd.products_id = p.products_id
                                      and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' ";
            //
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
                                          p.products_quantity, 
                                          p.products_price_tax, 
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

        if (isset($_POST['nazwa']) && !empty($_POST['nazwa'])) {
            $szukana_wartosc = $filtr->process($_POST['nazwa']);
            $zapytanie .= " and pd.products_name like '%".$szukana_wartosc."%'";
            unset($szukana_wartosc);
        }

        // jezeli jest vat
        if (isset($_POST['vat']) && (int)$_POST['vat'] != 'x') {     
            $zapytanie .= " and p.products_tax_class_id = " . (int)$_POST['vat'];
        }        
        // jezeli jest waluta
        if (isset($_POST['waluta']) && (int)$_POST['waluta'] != 'x') {     
            $zapytanie .= " and p.products_currencies_id  = " . (int)$_POST['waluta'];
        }  

        $warunek = '';
        //
        $pola = array();  
        switch ($_POST['wariant']) {    
            case "1":
                $pola[] = array('products_status','0');
                $warunek = ' and products_status = 1';
                break;
            case "2":
                $pola[] = array('products_status','1');
                $warunek = ' and products_status = 0';
                break;            
            case "3":
                $pola[] = array('products_status','0');
                $warunek = ' and products_quantity <= 0 and products_status = 1';
                break;
            case "4":
                $pola[] = array('products_status','1');
                $warunek = ' and products_quantity > 0 and products_status = 0';
                break;   
            case "5":
                $pola[] = array('products_status','1');
                $warunek = ' and products_price_tax > 0 and products_status = 0';
                break; 
            case "6":
                $pola[] = array('products_status','0');
                $warunek = ' and products_price_tax <= 0 and products_status = 1';
                break;
            case "8":
                $pola[] = array('products_buy','0');
                $warunek = ' and products_buy = 1';
                break;
            case "9":
                $pola[] = array('products_buy','1');
                $warunek = ' and products_buy = 0';
                break;
            case "10":
                $pola[] = array('products_buy','0');
                $warunek = ' and products_quantity <= 0 and products_buy = 1';
                break;
            case "11":
                $pola[] = array('products_buy','1');
                $warunek = ' and products_quantity > 0 and products_buy = 0';
                break;                
        }
        //    
        
        // jezeli tylko zmiana statusu
        if ($_POST['wariant'] != '7') {
            //
            // wykonanie zapytania
            $sql = $db->open_query($zapytanie . str_replace('products_','p.products_',$warunek));
            $ile_pozycji = (int)$db->ile_rekordow($sql);
            //
            $BylaAktualizacja = false;
            //

            while ($info = $sql->fetch_assoc()) {
                //
                $db->update_query('products' , $pola, 'products_id = ' . $info['products_id'] . $warunek);
                $BylaAktualizacja = true;
                //
            }
            
            unset($pola);
            //
        }
        
        // jezeli kasowanie produktow
        if ($_POST['wariant'] == '7') {
            //
            // wykonanie zapytania
            $sql = $db->open_query($zapytanie . ' and p.products_status = 0');
            $ile_pozycji = (int)$db->ile_rekordow($sql);
            //
            $BylaAktualizacja = false;
            //

            if ($ile_pozycji > 0) {
                //
                while ($info = $sql->fetch_assoc()) {
                    //
                    Produkty::SkasujProdukt($info['products_id']);    
                    //
                }
                unset($info);
                
                $BylaAktualizacja = true;
                //
            }
            
            $db->close_query($sql);
            //
        }        
        
        if ($BylaAktualizacja == true && $ile_pozycji > 0) {
            //
            Funkcje::PrzekierowanieURL('masowa_zmiana_statusu.php?suma=' . $ile_pozycji);
            //
          } else {
            //
            Funkcje::PrzekierowanieURL('masowa_zmiana_statusu.php?suma=0');          
            //
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');     

    ?>

    <div id="naglowek_cont">Masowa zmiana statusu produktów</div>
    <div id="cont">
    
          <div class="poleForm">
            <div class="naglowek">Zmiana statusu produktów</div>
            
                <?php
                if (isset($_GET['suma'])) {
                ?>
                
                <?php if ((int)$_GET['suma'] > 0) { ?>
                
                    <div id="sukcesAktualizacji">
                        Dane zostały zaktualizowane. <br />
                        Ilość zaktualizowanych produktów: <strong><?php echo (int)$_GET['suma']; ?></strong>
                    </div>
                    
                    <?php } else { ?>
                    
                    <div id="sukcesAktualizacji">
                        Brak danych do aktualizacji ...
                    </div>

                <?php } ?>
                
                <div class="przyciski_dolne">
                  <button type="button" class="przyciskNon" onclick="cofnij('masowa_zmiana_statusu','','narzedzia');">Powrót</button>    
                </div>                 
                
                <?php
                
                } else { 
                
                ?>

                <form action="narzedzia/masowa_zmiana_statusu.php" method="post" id="zmiana_ceny" class="cmxform">                 
            
                <table class="tblEdycja">
                    <tr>
                    
                        <td class="lewaStatus">

                            <input type="hidden" name="akcja" value="zmiana_cen" />
                        
                            <input type="radio" style="border:0px" value="1" name="wariant" checked="checked" /> zmiana statusu produktów na nieaktywny <br />
                            <input type="radio" style="border:0px" value="2" name="wariant" /> zmiana statusu produktów na aktywny <br />
                            <input type="radio" style="border:0px" value="3" name="wariant" /> zmiana statusu na nieaktywny jeżeli stan magazynowy produktu jest równy lub mniejszy od zera <br />
                            <input type="radio" style="border:0px" value="4" name="wariant" /> zmiana statusu produktów na aktywny jeżeli stan magazynowy produktu jest większy od zera <br />
                            <input type="radio" style="border:0px" value="5" name="wariant" /> zmiana statusu produktów na aktywny jeżeli cena towaru jest większa od zera <br />
                            <input type="radio" style="border:0px" value="6" name="wariant" /> zmiana statusu produktów na nieaktywny jeżeli cena towaru jest mniejsza lub równa zero <br />
                            <input type="radio" style="border:0px" value="7" name="wariant" /> kasowanie produktów nieaktywnych z bazy danych <br /><br />
                            
                            <input type="radio" style="border:0px" value="8" name="wariant" /> wyłączenie możliwości kupowania produktów <br />
                            <input type="radio" style="border:0px" value="9" name="wariant" /> włączenie możliwości kupowania produktów <br />
                            <input type="radio" style="border:0px" value="10" name="wariant" /> wyłączenie kupowania jeżeli stan magazynowy produktu jest równy lub mniejszy od zera <br />
                            <input type="radio" style="border:0px" value="11" name="wariant" /> włączenie kupowania jeżeli stan magazynowy produktu jest większy od zera <br />
                            
                        </td>
                        
                        <td class="prawaStatus">
                        
                            <table style="width:100%">
                        
                                <tr>
                                    <td colspan="2">
                                        <div class="ostrzezenie">
                                            Zatwierdzenie aktualizacji danych spowoduje zmianę statusu produktów lub usunięcie danych.
                                            Operacji nie można cofnąć ! Zalecane jest wykonanie kopii bazy danych przed dokonaniem zmian.
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td colspan="2" style="padding-top:10px; padding-left:20px;">
                                        <input type="submit" class="przyciskBut" value="Aktualizuj dane" />
                                    </td>
                                </tr>

                            </table>
                        
                        </td>

                    </tr>
                </table>
                
                <div class="naglowek" style="margin:10px;">Dodatkowe parametry dla zmiany statusów</div>
                
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