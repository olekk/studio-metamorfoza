<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja_dolna']) && $_POST['akcja_dolna'] == '0') {
    
            if (isset($_POST['id']) && count($_POST['id']) > 0) {
            
                foreach ($_POST['id'] as $pole) {
                
                    // zmiana statusu ------------ ** -------------
                    if (isset($_POST['status_' . $pole])) {
                        $status = (int)$_POST['status_' . $pole];
                      } else {
                        $status = 0;
                    }
                    $status = (($status == 1) ? '1' : '0');
                    $pola = array(array('products_status',$status));
                    $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                    unset($pola, $status);
                
                }
            
            }
            
        } else {

            if (isset($_POST['opcja'])) {
                //
                if (count($_POST['opcja']) > 0) {
                
                    // jezeli usuwanie promocji i przywrocenie ceny to ustawi tablice vat
                    if ( (int)$_POST['akcja_dolna'] == 11 ) {
                        //
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
                    }
                    
                    foreach ($_POST['opcja'] as $pole) {
            
                        switch ((int)$_POST['akcja_dolna']) {
                            case 1:
                                // usuwa z produktu zaznaczenie ze jest promocja ------------ ** -------------
                                $pola = array(array('specials_status','0'),
                                              array('specials_date',''),
                                              array('specials_date_end',''),
                                              array('products_old_price','0'));
                                //
                                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                                    //
                                    $pola[] = array('products_old_price_'.$x,'0');
                                    //
                                }                                              
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;                          
                            case 2:
                                // zmiana statusu na nieaktywny ------------ ** -------------
                                $pola = array(array('products_status','0'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break; 
                            case 3:
                                // zmiana statusu na aktywny ------------ ** -------------
                                $pola = array(array('products_status','1'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;                               
                            case 4:
                                // usuniecie produktow ------------ ** -------------
                                Produkty::SkasujProdukt($pole);
                                break; 
                            case 5:
                                // wyzerowanie daty rozpoczecia ------------ ** -------------
                                $pola = array(array('specials_date','0000-00-00'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;    
                            case 6:
                                // wyzerowanie daty zakonczenia ------------ ** -------------
                                $pola = array(array('specials_date_end','0000-00-00'));
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;
                            case 7:
                                // dodaj/odejmij iloœæ dni do daty rozpoczêcia ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = (int)$filtr->process($_POST['wartosc']) * 86400; // 86400 - ilosc sekund na dzien
                                }
                                // pobiera wartosc daty dla danego produktu
                                $zapytanie = "select distinct products_id, specials_date from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['specials_date'])) {
                                    if (strtotime($info['specials_date']) > time() && (strtotime($info['specials_date']) + $wskaznikObliczenia) > time() ) {
                                         $pola = array(array('specials_date',date('Y-m-d H:i:s', strtotime($info['specials_date']) + $wskaznikObliczenia)));
                                      } else {
                                         $pola = array(array('specials_date','0000-00-00'));
                                    }
                                }
                                //
                                $db->close_query($sql);
                                unset($info);
                                //
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola, $wskaznikObliczenia);                                 
                                break; 
                            case 8:
                                // dodaj/odejmij iloœæ dni do daty zakonczenia ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = (int)$filtr->process($_POST['wartosc']) * 86400; // 86400 - ilosc sekund na dzien
                                }
                                // pobiera wartosc daty dla danego produktu
                                $zapytanie = "select distinct products_id, specials_date_end from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['specials_date_end'])) {
                                    if (strtotime($info['specials_date_end']) > time() && (strtotime($info['specials_date_end']) + $wskaznikObliczenia) > time() ) {
                                         $pola = array(array('specials_date_end',date('Y-m-d H:i:s', strtotime($info['specials_date_end']) + $wskaznikObliczenia)));
                                      } else {
                                         $pola = array(array('specials_date_end','0000-00-00'));
                                    }
                                }
                                //
                                $db->close_query($sql);
                                unset($info);
                                //
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola, $wskaznikObliczenia);                                  
                                break; 
                            case 9:
                                // zwiêksz/zmniejsz ceny przekreœlone o xx.xx z³ ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = $filtr->process($_POST['wartosc']);
                                }
                                // pobiera wartosc ceny dla danego produktu
                                $zapytanie = "select distinct products_id, products_old_price from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['products_old_price'])) {
                                    $pola = array(array('products_old_price',$info['products_old_price'] + $wskaznikObliczenia));
                                }
                                //
                                $db->close_query($sql);
                                unset($info);
                                //
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola, $wskaznikObliczenia);                               
                                break; 
                            case 10:
                                // zwiêksz/zmniejsz ceny przekreœlone o xx.xx % ------------ ** -------------
                                if (isset($_POST['wartosc']) && !empty($_POST['wartosc'])) {
                                    $wskaznikObliczenia = $filtr->process($_POST['wartosc']);
                                }
                                // pobiera wartosc ceny dla danego produktu
                                $zapytanie = "select distinct products_id, products_old_price from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie); 
                                $info = $sql->fetch_assoc(); 
                                //
                                if (!empty($info['products_old_price'])) {
                                    $pola = array(array('products_old_price',$info['products_old_price'] + ($info['products_old_price'] * ($wskaznikObliczenia / 100))));
                                }
                                //
                                $db->close_query($sql);
                                unset($info);
                                //
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola, $wskaznikObliczenia);                             
                                break;     
                            case 11:
                                // usuwa promocje i ustawia cene poprzednia jako glowna
                                //
                                $pola = array(array('specials_status','0'),
                                              array('products_old_price','0'),
                                              array('specials_date',''),
                                              array('specials_date_end',''));            
                                //
                                $zapytanie = "select distinct * from products where products_id = '".$pole."'";
                                $sql = $db->open_query($zapytanie);    
                                $info = $sql->fetch_assoc();  
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
                                $db->close_query($sql);
                                unset($info);
                                // 
                                $sql = $db->update_query('products' , $pola, " products_id = '".$pole."'");
                                unset($pola);                             
                                break;                                   
                        }          

                    }
                
                }
                //
            }
            
    }
    
    Funkcje::PrzekierowanieURL('promocje.php');
    
}
?>