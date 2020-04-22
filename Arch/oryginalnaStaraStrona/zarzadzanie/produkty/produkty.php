<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

// filtry ze strony glownej
if ( isset($_GET['kategoria']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'kategoria';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['brutto']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'brutto';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['nazwa']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'nazwa';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['vat']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['blad'] = 'vat';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['wszystkie']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['aktywne']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['status'] = 'tak';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['nieaktywne']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     $_SESSION['filtry']['produkty.php']['status'] = 'nie';
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}
if ( isset($_GET['filtr']) && !empty($_GET['szukaj']) ) {
     //
     unset($_SESSION['filtry']['produkty.php']);
     //
     // zamienia ' na \' - jezeli nie jest wlaczony magic
     if (!get_magic_quotes_gpc()) {
        $_GET['szukaj'] = str_replace("'", "\'",$_GET['szukaj']);
     }         
     //     
     if ( isset($_GET['opcja_szukania']) && ($_GET['opcja_szukania'] == 'nr_katalogowy' || $_GET['opcja_szukania'] == 'nr_producenta') ) {
          $_SESSION['filtry']['produkty.php']['opcja_numer'] = 'nr_katalogowy';
          $_SESSION['filtry']['produkty.php']['nrkat'] = rawurlencode($_GET['szukaj']);
        } else {
          $_SESSION['filtry']['produkty.php']['opcja_numer'] = 'nazwa';
          $_SESSION['filtry']['produkty.php']['szukaj'] = rawurlencode($_GET['szukaj']);
     }
     //
     Funkcje::PrzekierowanieURL('produkty.php');
}

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    // pobieranie informacji o vat - do wyswietlania informacji jaki podatek ma produkt
    $zapytanie_vat = "select distinct * from tax_rates order by tax_rate desc";
    $sqls = $db->open_query($zapytanie_vat);
    //
    $tablicaVat = array();
    while ($infs = $sqls->fetch_assoc()) { 
        $tablicaVat[$infs['tax_rates_id']] = $infs['tax_description'];
    }
    $db->close_query($sqls);
    unset($zapytanie_vat, $infs);  
    //

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && !empty($_GET['szukaj'])) {
        //
        if ( isset($_SESSION['filtry']['produkty.php']['opcja_numer']) && $_SESSION['filtry']['produkty.php']['opcja_numer'] == 'nazwa' ) {
             $_GET['szukaj'] = rawurldecode($_GET['szukaj']);
             //
             $_SESSION['filtry']['produkty.php']['szukaj'] = Listing::podmienMagic($_GET['szukaj'], 'wlacz');             
             //
             unset($_SESSION['filtry']['produkty.php']['opcja_numer']);
        }
        //
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and pd.products_name like '%".$szukana_wartosc."%'";
        unset($szukana_wartosc);
    }   
    
    // jezeli jest nr kat lub id
    if (isset($_GET['nrkat']) && !empty($_GET['nrkat'])) {
        //
        if ( isset($_SESSION['filtry']['produkty.php']['opcja_numer']) && $_SESSION['filtry']['produkty.php']['opcja_numer'] == 'nr_katalogowy' ) {
             $_GET['nrkat'] = rawurldecode($_GET['nrkat']); 
             //
             $_SESSION['filtry']['produkty.php']['nrkat'] = Listing::podmienMagic($_GET['nrkat'], 'wlacz');            
             //
             unset($_SESSION['filtry']['produkty.php']['opcja_numer']);
        }
        //    
        $szukana_wartosc = $filtr->process($_GET['nrkat']);
        $warunki_szukania = " and (p.products_model like '%".$szukana_wartosc."%' or p.products_man_code like '%".$szukana_wartosc."%' or p.products_id = ".(int)$szukana_wartosc.")";
        unset($szukana_wartosc);
    }

    // jezeli jest wybrana grupa klienta
    if (isset($_GET['klienci']) && (int)$_GET['klienci'] > 0) {
        $id_klienta = (int)$_GET['klienci'];
        $warunki_szukania .= " and find_in_set(" . $id_klienta . ", p.customers_group_id) ";        
        unset($id_klienta);
    }    
    
    // jezeli jest zakres cen
    if (isset($_GET['cena_od']) && (float)$_GET['cena_od'] >= 0) {
        $cena = (float)$_GET['cena_od'];
        $warunki_szukania .= " and p.products_price_tax >= '".$cena."'";
        unset($cena);
    }
    if (isset($_GET['cena_do']) && (float)$_GET['cena_do'] >= 0) {
        $cena = (float)$_GET['cena_do'];
        $warunki_szukania .= " and p.products_price_tax <= '".$cena."'";
        unset($cena);
    }    

    // jezeli jest wybrana kategoria
    if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
        $id_kategorii = (int)$_GET['kategoria_id'];
        $warunki_szukania .= " and pc.categories_id = '".$id_kategorii."'";
        unset($id_kategorii);
    }
    
    // jezeli jest wybrany producent
    if (isset($_GET['producent']) && (int)$_GET['producent'] > 0) {
        $id_producenta = (int)$_GET['producent'];
        $warunki_szukania .= " and p.manufacturers_id = '".$id_producenta."'";
        unset($id_producenta);
    } 
    
    // jezeli jest wybrana waluta
    if (isset($_GET['waluta']) && (int)$_GET['waluta'] > 0) {
        $id_waluty = (int)$_GET['waluta'];
        $warunki_szukania .= " and p.products_currencies_id = '".$id_waluty."'";
        unset($id_waluty);
    }    

    // jezeli jest wybrany status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $warunki_szukania .= " and p.products_status = '".(($_GET['status'] == 'tak') ? '1' : '0')."'";
    }     
    
    // data dodania
    if ( isset($_GET['szukaj_data_dodania_od']) && $_GET['szukaj_data_dodania_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_dodania_od'] . ' 00:00:00')));
        $warunki_szukania .= " and p.products_date_added >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_dodania_do']) && $_GET['szukaj_data_dodania_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_dodania_do'] . ' 23:59:59')));
        $warunki_szukania .= " and p.products_date_added <= '".$szukana_wartosc."'";
    }    
    
    // data dostepnosci
    if ( isset($_GET['szukaj_data_dostepnosci_od']) && $_GET['szukaj_data_dostepnosci_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d', strtotime($filtr->process($_GET['szukaj_data_dostepnosci_od'])));
        $warunki_szukania .= " and p.products_date_available >= '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_data_dostepnosci_do']) && $_GET['szukaj_data_dostepnosci_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d', strtotime($filtr->process($_GET['szukaj_data_dostepnosci_do'])));
        $warunki_szukania .= " and p.products_date_available <= '".$szukana_wartosc."'";
    }   

    // dostepnosc produktu
    if (isset($_GET['dostep']) && (int)$_GET['dostep'] > 0) {
        $id_dostepnosci = (int)$_GET['dostep'];
        $warunki_szukania .= " and p.products_availability_id = '".$id_dostepnosci."'";
        unset($id_dostepnosci);
    }   

    // aukcje allegro
    if (isset($_GET['allegro'])) {
        if ((int)$_GET['allegro'] == 1) {
            $warunki_szukania .= " and a.auction_id != ''";
        } elseif ((int)$_GET['allegro'] == 2) {
            $warunki_szukania .= " and a.auction_id IS NULL";
        }
    }    
    
    // ilosc magazynu
    if (isset($_GET['ilosc_od'])) {
        $ilosc = $filtr->process((float)$_GET['ilosc_od']);
        $warunki_szukania .= " and p.products_quantity >= '".$ilosc."'";
        unset($ilosc);
    }
    if (isset($_GET['ilosc_do'])) {
        $ilosc = $filtr->process((float)$_GET['ilosc_do']);
        $warunki_szukania .= " and p.products_quantity <= '".$ilosc."'";
        unset($ilosc);
    }       

    // jezeli jest opcja
    if (isset($_GET['opcja']) && !empty($_GET['opcja'])) {
        switch ($filtr->process($_GET['opcja'])) {
            case "nowosc":
                $warunki_szukania .= " and p.new_status = '1'";
                break;
            case "promocja":
                $warunki_szukania .= " and p.specials_status = '1'";
                break;
            case "hit":
                $warunki_szukania .= " and p.star_status = '1'";
                break; 
            case "polecany":
                $warunki_szukania .= " and p.featured_status = '1'";
                break;   
            case "export":
                $warunki_szukania .= " and p.export_status = '1'";
                break; 
            case "negoc":
                $warunki_szukania .= " and p.products_make_an_offer = '1'";
                break;     
            case "wysylka_gratis":
                $warunki_szukania .= " and p.free_shipping_status = '1'";
                break;             
        }     
    } 
    
    // jezeli jest blad w produktach
    if (isset($_GET['blad']) && !empty($_GET['blad'])) { 
        switch ($filtr->process($_GET['blad'])) {
            case "brutto":
                $warunki_szukania .= " and (( p.products_price_tax = 0 and p.products_price > 0 )";
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    $warunki_szukania .= " or ( p.products_price_tax_" . $x . " = 0 and p.products_price_" . $x . " > 0 )";
                }
                $warunki_szukania .= ")";
                break; 
            case "vat":
                $warunki_szukania .= " and (( p.products_tax = 0 and p.products_price > 0.02 )";
                for ($x = 2; $x <= ILOSC_CEN; $x++) {
                    $warunki_szukania .= " or ( p.products_tax_" . $x . " = 0 and p.products_price_" . $x . " > 0.02 )";
                }
                $warunki_szukania .= ")";                
                break;
            case "kategoria":
                $warunki_szukania .= " and (pc.categories_id is null or pc.categories_id = '0')";
                break;
            case "nazwa":
                $warunki_szukania .= " and pd.products_name = ''";
                break;                
        }  
    }  
    
    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }
               
    $zapytanie = 'SELECT
                         p.products_id, 
                         p.products_price_tax, 
                         p.products_tax,
                         p.products_old_price,
                         p.products_quantity,
                         p.sort_order,
                         p.customers_group_id,
                         p.manufacturers_id,
                         p.products_image, 
                         p.products_model,
                         p.products_ean,
                         p.products_man_code,
                         p.products_date_added, 
                         p.products_status, 
                         p.products_buy,
                         p.products_make_an_offer, 
                         p.new_status,
                         p.star_status,
                         p.star_date,
                         p.star_date_end,                         
                         p.specials_status,
                         p.specials_date,
                         p.specials_date_end,
                         p.featured_status,
                         p.featured_date,
                         p.featured_date_end,                         
                         p.export_status,
                         p.free_shipping_status,
                         p.products_currencies_id,
                         p.products_tax_class_id,
                         pd.language_id, 
                         pd.products_name, 
                         pd.products_seo_url,
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && $_GET['blad'] == 'kategoria')) ? 'pc.categories_id,' : '').'
                         m.manufacturers_id,
                         m.manufacturers_name,
                         a.auction_id,
                         pj.products_jm_quantity_type
                  FROM products p
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && $_GET['blad'] == 'kategoria')) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id
                         LEFT JOIN products_jm pj ON p.products_jm_id = pj.products_jm_id
                         LEFT JOIN allegro_auctions a ON a.products_id = p.products_id AND a.auction_status = "1" ' . $warunki_szukania . ' GROUP BY p.products_id '; 

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = 'SELECT p.products_id
                         FROM products p
                         '.((isset($_GET['kategoria_id']) || (isset($_GET['blad']) && $_GET['blad'] == 'kategoria')) ? 'LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id' : '').'
                         LEFT JOIN products_description pd ON pd.products_id = p.products_id
                         AND pd.language_id = "' . $_SESSION['domyslny_jezyk']['id'] . '"
                         LEFT JOIN manufacturers m ON m.manufacturers_id = p.manufacturers_id';
                         if ( isset($_GET['allegro']) && ((int)$_GET['allegro'] == 1 || (int)$_GET['allegro'] == 2) ) {
                            $ZapytanieDlaPozycji .= ' LEFT JOIN allegro_auctions a ON a.products_id = p.products_id AND a.auction_status = "1"';
                         }
    $ZapytanieDlaPozycji .= $warunki_szukania . ' GROUP BY p.products_id ';

    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    $sortowanie = '';
    //
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a17":
                $sortowanie = 'pd.products_name asc, p.products_id';
                break;
            case "sort_a2":
                $sortowanie = 'pd.products_name desc, p.products_id';
                break;
            case "sort_a7":
                $sortowanie = 'p.products_model asc, p.products_id';
                break;
            case "sort_a8":
                $sortowanie = 'p.products_model desc, p.products_id';
                break;  
            case "sort_a9":
                $sortowanie = 'p.products_price_tax asc, p.products_id';
                break;
            case "sort_a10":
                $sortowanie = 'p.products_price_tax desc, p.products_id';
                break;  
            case "sort_a11":
                $sortowanie = 'p.products_quantity asc, p.products_id';
                break;
            case "sort_a12":
                $sortowanie = 'p.products_quantity desc, p.products_id';
                break;                            
            case "sort_a3":
                $sortowanie = 'p.products_status desc, pd.products_name, p.products_id';
                break;  
            case "sort_a4":
                $sortowanie = 'p.products_status asc, pd.products_name, p.products_id';
                break;
            case "sort_a5":
                $sortowanie = 'p.products_date_added asc, pd.products_name, p.products_id';
                break; 
            case "sort_a6":
                $sortowanie = 'p.products_date_added desc, pd.products_name, p.products_id';
                break; 
            case "sort_a13":
                $sortowanie = 'p.products_id desc';
                break;
            case "sort_a14":
                $sortowanie = 'p.products_id asc';
                break;    
            case "sort_a15":
                $sortowanie = 'p.sort_order desc, p.products_id';
                break;
            case "sort_a16":
                $sortowanie = 'p.sort_order asc, p.products_id';
                break;                        
        }            
    }  
    
    $zapytanie .= (($sortowanie != '') ? " order by ".$sortowanie : '');    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array();
            $tablica_naglowek[] = array('Akcja','center');
            $tablica_naglowek[] = array('ID','center');
            $tablica_naglowek[] = array('Zdjęcie','center');  
            $tablica_naglowek[] = array('Nazwa produktu', '', 'width:40%');
            $tablica_naglowek[] = array('Cena');
            $tablica_naglowek[] = array('Opcje');
            $tablica_naglowek[] = array('Ilość','center');
            $tablica_naglowek[] = array('Sort','center');
            $tablica_naglowek[] = array('Status','center');
            
            echo $listing_danych->naglowek($tablica_naglowek);

            $tekst = '';

            while ($info = $sql->fetch_assoc()) {
                  
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['products_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['products_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['products_id'].'">';
                  } 

                  $tablica = array();

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['products_id'].'" /><input type="hidden" name="id[]" value="'.$info['products_id'].'" />','center');
                  
                  $tablica[] = array($info['products_id'],'center'); 

                  // czyszczenie z &nbsp; i zbyt dlugiej nazwy
                  $info['products_name'] = Funkcje::PodzielNazwe($info['products_name']);
                  $info['products_model'] = Funkcje::PodzielNazwe($info['products_model']);

                  if ( !empty($info['products_image']) ) {
                       //
                       $tgm = '<div id="zoom'.rand(1,99999).'" class="imgzoom" onmouseover="ZoomIn(this,event)" onmouseout="ZoomOut(this)">';
                       $tgm .= '<div class="zoom">' . Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '250', '250') . '</div>';
                       $tgm .= Funkcje::pokazObrazek($info['products_image'], $info['products_name'], '40', '40', ' class="Reload"', true);
                       $tgm .= '</div>';
                       //
                     } else { 
                       //
                       $tgm = '-';
                       //
                  }
                  
                  $tablica[] = array($tgm,'center');    
                  
                  // ladowanie info o produkcie z zew pliku
                  include('produkty/produkt_info_nazwa.php');
                  $tablica[] = array($tgm);
                  unset($tgm, $tgm_ajax);
                  
                  unset($do_jakich_kategorii_przypisany, $nr_kat, $kod_producenta, $prd, $allegro);
                  
                  if ( ((strtotime($info['specials_date']) > time() && $info['specials_date'] != '0000-00-00 00:00:00') || (strtotime($info['specials_date_end']) < time() && $info['specials_date_end'] != '0000-00-00 00:00:00') ) ) {
                     $IkonaPromocja = '<img src="obrazki/promocja_wylaczona.png" alt="Promocja nieaktywna" title="Promocja nie jest wyświetlana ze względu na datę rozpoczęcia lub zakończenia promocji" />';
                   } else {
                     $IkonaPromocja = '<img src="obrazki/promocja.png" alt="Cena jest promocyjna" title="Cena jest promocyjna" />';
                  }
                  
                  $tablica[] = array('<div class="cena">Cena brutto: '.(($info['specials_status'] == '1' || Funkcje::czyNiePuste($info['specials_date']) || Funkcje::czyNiePuste($info['specials_date_end'])) ? $IkonaPromocja : '').'
                                      <input type="text" name="cena_'.$info['products_id'].'" value="'.$info['products_price_tax'].'" class="cen_prod" onchange="zamien_krp(this)" /> <br />                                    
                                      Cena poprzednia:
                                      <input type="text" name="cenaold_'.$info['products_id'].'" value="'.(((float)$info['products_old_price'] == 0) ? '' : $info['products_old_price']).'" class="cen_prod" onchange="zamien_krp(this)" />                                      
                                      </div>
                                      <div class="waluta">Waluta: <span>'.$waluty->ZwrocSymbolWaluty($info['products_currencies_id']).'</span></div>
                                      <div class="waluta">Podatek: <span>'.$tablicaVat[$info['products_tax_class_id']].'</span></div>');                  
                                      
                  unset($IkonaPromocja);
                  
                  // nowosci - automatyczne czy reczne
                  $InputNowosci = '<input type="checkbox" style="border:0px" name="nowosc_'.$info['products_id'].'" value="1" '.(($info['new_status'] == '1') ? 'checked="checked"' : '').' /> nowość <br />';
                  if ( NOWOSCI_USTAWIENIA == 'automatycznie wg daty dodania' ) {
                       $InputNowosci = '<span class="chmurka" title="Opcja nieaktywna - nowości określane na podstawie daty dodania"><input type="checkbox" style="border:0px" disabled="disabled" name="nowosc_'.$info['products_id'].'" value="1" '.(($info['new_status'] == '1') ? 'checked="checked"' : '').' /> <span class="wylaczony">nowość</span></span> <br />';
                  }
                  
                  $tablica[] = array('<div class="opcje">
                                      ' . $InputNowosci . '
                                      <input type="checkbox" style="border:0px" name="hit_'.$info['products_id'].'" value="1" '.(($info['star_status'] == '1' || Funkcje::czyNiePuste($info['star_date']) || Funkcje::czyNiePuste($info['star_date_end'])) ? 'checked="checked"' : '').' /> nasz hit <br />
                                      <input type="checkbox" style="border:0px" name="promocja_'.$info['products_id'].'" value="1" '.(($info['specials_status'] == '1' || Funkcje::czyNiePuste($info['specials_date']) || Funkcje::czyNiePuste($info['specials_date_end'])) ? 'checked="checked"' : '').' /> promocja <br />
                                      <input type="checkbox" style="border:0px" name="polecany_'.$info['products_id'].'" value="1" '.(($info['featured_status'] == '1' || Funkcje::czyNiePuste($info['featured_date']) || Funkcje::czyNiePuste($info['featured_date_end'])) ? 'checked="checked"' : '').' /> polecany <br />
                                      <input type="checkbox" style="border:0px" name="export_'.$info['products_id'].'" value="1" '.(($info['export_status'] == '1') ? 'checked="checked"' : '').' /> do porównywarek <br />
                                      <input type="checkbox" style="border:0px" name="negocjacja_'.$info['products_id'].'" value="1" '.(($info['products_make_an_offer'] == '1') ? 'checked="checked"' : '').' /> <span style="color:#ff0000">negocjacja ceny</span> <br />
                                      <input type="checkbox" style="border:0px" name="wysylka_'.$info['products_id'].'" value="1" '.(($info['free_shipping_status'] == '1') ? 'checked="checked"' : '').' /> <span>darmowa wysyłka</span>
                                      </div>');                                      
                  unset($InputNowosci);
                                      
                  // ilosc  
                  // jezeli jednostka miary calkowita
                  if ( $info['products_jm_quantity_type'] == 1 ) {
                       $info['products_quantity'] = (int)$info['products_quantity'];
                  }                     
                  $tablica[] = array((($info['products_quantity'] <= 0) ? '<span class="niskiStan">'.$info['products_quantity'].'</span>' : $info['products_quantity']),'center');                                       

                  // sort
                  $tablica[] = array('<input type="text" name="sort_'.$info['products_id'].'" value="'.$info['sort_order'].'" class="sort_prod" />','center');                    
                  
                  // aktywany czy nieaktywny
                  $bezKupowania = '';
                  if ($info['products_buy'] == '0') {
                      $bezKupowania = '<div class="bez_kupowania toolTipTopText" title="Produktu nie można kupować"></div>';
                  }               
                  
                  $tablica[] = array( $bezKupowania . (($wylacz_status == true) ? '<div class="wylKat" title="Kategoria do której należy produkt jest wyłączona">' : '') . '<input type="checkbox" style="border:0px" name="status_'.$info['products_id'].'" value="1" '.(($info['products_status'] == '1') ? 'checked="checked"' : '').' />' . (($wylacz_status == true) ? '</div>' : ''),'center');
                  unset($bezKupowania); 
                  
                  $tekst .= $listing_danych->pozycje($tablica);

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.$info['products_id'];   

                  // ustala jaka ma byc tresc linku
                  $linkSeo = ((!empty($info['products_seo_url'])) ? $info['products_seo_url'] : $info['products_name']);                  
                                      
                  $tekst .= '<td class="rg_right" style="width:10%">';                 
                  $tekst .= '<a href="produkty/produkty_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>'; 
                  $tekst .= '<a href="produkty/produkty_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>'; 
                  $tekst .= '<a href="allegro/allegro_wystaw_aukcje.php'.$zmienne_do_przekazania.'"><img src="obrazki/allegro_lapka.png" alt="Wystaw na Allegro" title="Wystaw na Allegro" /></a><br /><br />'; 
                  $tekst .= '<a href="produkty/produkty_duplikuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/duplikuj.png" alt="Duplikuj" title="Duplikuj" /></a>';                   
                  $tekst .= '<a class="blank" href="' . Seo::link_SEO( $linkSeo, $info['products_id'], 'produkt', '', false ) . '"><img src="obrazki/zobacz.png" alt="Zobacz w sklepie" title="Zobacz w sklepie" /></a>';
                  $tekst .= '</td></tr>';                  

                  unset($tablica, $linkSeo);
            } 
            $tekst .= '</table>';
            
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);   
             
        }
    }  

    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <!-- Skrypt do autouzupelniania -->
        <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_produkty.php', 50, 350 );
            
            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });     
            
            $('#pamietaj').click(function() {
               if ($(this).prop('checked') == true) {
                   createCookie("kategoria","tak");                 
                 } else {
                   createCookie("kategoria","",-1);
               }
            });            
          });
          
          function edpr(id) {
            $("#edpr_"+id).html('<img src="obrazki/_loader_small.gif" alt="" />');
            $('.edpr').hide();
            $.get('ajax/produkt_szybka_edycja.php', { tok: '<?php echo Sesje::Token(); ?>', id: id }, function(data) {
                $("#edpr_"+id).html(data);
            });          
          }
          //]]>
        </script>     

        <div id="caly_listing">
        
            <div id="ajax"></div>
        
            <div id="naglowek_cont">Produkty</div>
            
            <div id="wyszukaj">
                <form action="produkty/produkty.php" method="post" id="poForm" class="cmxform"> 
                
                <div id="wyszukaj_text">
                    <span style="width:110px">Wyszukaj produkt:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="35" />
                </div>  
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Producent:</span>                                        
                    <?php echo Funkcje::RozwijaneMenu('producent', Funkcje::TablicaProducenci('-- brak --'), ((isset($_GET['producent'])) ? $filtr->process($_GET['producent']) : ''), ' style="width:120px"'); ?>
                </div>
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Opcja:</span>
                    <?php
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- brak --');
                    $tablica[] = array('id' => 'nowosc', 'text' => 'Nowość');
                    $tablica[] = array('id' => 'hit', 'text' => 'Nasz hit');
                    $tablica[] = array('id' => 'promocja', 'text' => 'Promocja');
                    $tablica[] = array('id' => 'polecany', 'text' => 'Polecany');
                    $tablica[] = array('id' => 'export', 'text' => 'Do porównywarek');
                    $tablica[] = array('id' => 'negoc', 'text' => 'Negocjacja ceny');
                    $tablica[] = array('id' => 'wysylka_gratis', 'text' => 'Darmowa wysyłka');
                    ?>                                          
                    <?php echo Funkcje::RozwijaneMenu('opcja', $tablica, ((isset($_GET['opcja'])) ? $filtr->process($_GET['opcja']) : ''), ' style="width:120px"'); ?>
                </div>  
                
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Grupa klientów:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('klienci', Klienci::ListaGrupKlientow(true), ((isset($_GET['klienci'])) ? $filtr->process($_GET['klienci']) : ''), ' style="width:130px"'); 
                    unset($tablica);
                    ?>
                </div>                 

                <div class="cl" style="height:9px"></div>
                
                <div class="wyszukaj_select">
                    <span style="width:110px">ID lub nr kat:</span>
                    <input type="text" name="nrkat" value="<?php echo ((isset($_GET['nrkat'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['nrkat'])) : ''); ?>" size="20" />
                </div>                 
                
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Cena brutto:</span>
                    <input type="text" name="cena_od" value="<?php echo ((isset($_GET['cena_od'])) ? $filtr->process($_GET['cena_od']) : ''); ?>" size="6" /> do
                    <input type="text" name="cena_do" value="<?php echo ((isset($_GET['cena_do'])) ? $filtr->process($_GET['cena_do']) : ''); ?>" size="6" />
                </div>
                
                <?php
                $sqls = $db->open_query("select * from currencies");  
                //
                $tablica = array();
                $tablica[] = array('id' => '', 'text' => '-- dowolna --');
                //
                while ($infs = $sqls->fetch_assoc()) { 
                    $tablica[] = array('id' => $infs['currencies_id'], 'text' => $infs['title']);
                }
                $db->close_query($sqls);
                unset($infs);  
                //             
                ?>
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Waluta:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('waluta', $tablica, ((isset($_GET['waluta'])) ? $filtr->process($_GET['waluta']) : ''), ' style="width:100px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>
                
                <?php  
                //
                $tablica = array();
                $tablica[] = array('id' => '', 'text' => '-- dowolny --');
                $tablica[] = array('id' => 'tak', 'text' => 'aktywne');
                $tablica[] = array('id' => 'nie', 'text' => 'nieaktywne');
                //             
                ?>
                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Status:</span>
                    <?php                         
                    echo Funkcje::RozwijaneMenu('status', $tablica, ((isset($_GET['status'])) ? $filtr->process($_GET['status']) : ''), ' style="width:100px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                <?php
                unset($tablica);
                ?>     
                
                <div class="cl" style="height:9px"></div>

                <div class="wyszukaj_select">
                    <span style="width:110px">Data dodania:</span>
                    <input type="text" id="data_dodania_od" name="szukaj_data_dodania_od" value="<?php echo ((isset($_GET['szukaj_data_dodania_od'])) ? $filtr->process($_GET['szukaj_data_dodania_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dodania_do" name="szukaj_data_dodania_do" value="<?php echo ((isset($_GET['szukaj_data_dodania_do'])) ? $filtr->process($_GET['szukaj_data_dodania_do']) : ''); ?>" size="8" class="datepicker" />
                </div>   

                <div class="wyszukaj_select">
                    <span style="margin-left:10px">Data dostępności:</span>
                    <input type="text" id="data_dostepnosci_od" name="szukaj_data_dostepnosci_od" value="<?php echo ((isset($_GET['szukaj_data_dostepnosci_od'])) ? $filtr->process($_GET['szukaj_data_dostepnosci_od']) : ''); ?>" size="8" class="datepicker" /> do 
                    <input type="text" id="data_dostepnosci_do" name="szukaj_data_dostepnosci_do" value="<?php echo ((isset($_GET['szukaj_data_dostepnosci_do'])) ? $filtr->process($_GET['szukaj_data_dostepnosci_do']) : ''); ?>" size="8" class="datepicker" />
                </div> 

                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Ilość magazynu:</span>
                    <input type="text" name="ilosc_od" class="calkowita" value="<?php echo ((isset($_GET['ilosc_od'])) ? $filtr->process($_GET['ilosc_od']) : ''); ?>" size="4" /> do
                    <input type="text" name="ilosc_do" class="calkowita" value="<?php echo ((isset($_GET['ilosc_do'])) ? $filtr->process($_GET['ilosc_do']) : ''); ?>" size="4" />
                </div>                

                <div class="cl" style="height:9px"></div>
                
                <div class="wyszukaj_select">
                    <span style="width:110px">Stan dostępności:</span>                                         
                    <?php 
                    echo Funkcje::RozwijaneMenu('dostep', Produkty::TablicaDostepnosci('-- brak --'), ((isset($_GET['dostep'])) ? $filtr->process($_GET['dostep']) : ''), ' style="width:160px"'); 
                    ?>
                </div>  

                <div class="wyszukaj_select">
                    <span style="margin-left:10px;">Allegro:</span>                                         
                    <?php  
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- dowolne --');
                    $tablica[] = array('id' => '1', 'text' => 'produkty z aukcjami Allegro');
                    $tablica[] = array('id' => '2', 'text' => 'produkty bez aukcji Allegro');
                    //             
                    echo Funkcje::RozwijaneMenu('allegro', $tablica, ((isset($_GET['allegro'])) ? $filtr->process($_GET['allegro']) : ''), ' style="width:150px"'); 
                    unset($tablica);
                    ?>
                </div>                 
                
                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra 
                if (isset($_GET['kategoria_id'])) { 
                    echo '<div><input type="hidden" name="kategoria_id" value="'.(int)$_GET['kategoria_id'].'" /></div>';
                }   
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                

                // dodatkowy select do wyswietlenia produktow z bledami
                $ZapytanieBledy = "SELECT pd.products_name, p.products_price_tax, p.products_tax, pc.categories_id
                                     FROM products p
                                     LEFT JOIN products_to_categories pc ON pc.products_id = p.products_id
                                     LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'
                                    WHERE pd.products_name is null or p.products_price_tax = 0 or p.products_tax = 0 or ( pc.categories_id is null or pc.categories_id = 0 )";   

                $sqlBledy = $db->open_query($ZapytanieBledy);
                if ((int)$db->ile_rekordow($sqlBledy)) {                                   
                    //
                    $tablica = array();
                    $tablica[] = array('id' => '', 'text' => '-- wszystkie --');
                    $tablica[] = array('id' => 'brutto', 'text' => 'produkty bez uzupełnionej ceny brutto');
                    $tablica[] = array('id' => 'vat', 'text' => 'produkty bez uzupełnionej kwoty VAT');
                    $tablica[] = array('id' => 'kategoria', 'text' => 'produkty bez przypisanej kategorii');
                    $tablica[] = array('id' => 'nazwa', 'text' => 'produkty bez wpisanej nazwy');                
                    //             
                    ?>
                    <div class="wyszukaj_select" style="margin-left:10px;">
                        <span style="width:120px; color:#ff0000; padding:0px;">Błędy w produktach:</span>
                        <?php                         
                        echo Funkcje::RozwijaneMenu('blad', $tablica, ((isset($_GET['blad'])) ? $filtr->process($_GET['status']) : ''), ' style="color:#ff0000;width:180px"'); 
                        unset($tablica);
                        ?>
                    </div>                 
                    <?php
                    unset($tablica);
                } else {
                    unset($_SESSION['filtry']['produkty.php']['blad']);
                }
                $db->close_query($sqlBledy);
                unset($ZapytanieBledy);
                ?>
                
                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="produkty/produkty.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
                
            </div>        
            
            <form action="produkty/produkty_akcja.php" method="post" class="cmxform">
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="produkty/produkty.php?sort=sort_a1">brak</a>
            <a id="sort_a17" class="sortowanie" href="produkty/produkty.php?sort=sort_a17">nazwy rosnąco</a>
            <a id="sort_a2" class="sortowanie" href="produkty/produkty.php?sort=sort_a2">nazwy malejąco</a>
            <a id="sort_a7" class="sortowanie" href="produkty/produkty.php?sort=sort_a7">nr katalogowy rosnąco</a>
            <a id="sort_a8" class="sortowanie" href="produkty/produkty.php?sort=sort_a8">nr katalogowy malejąco</a> 
            <a id="sort_a9" class="sortowanie" href="produkty/produkty.php?sort=sort_a9">cena rosnąco</a>
            <a id="sort_a10" class="sortowanie" href="produkty/produkty.php?sort=sort_a10">cena malejąco</a>             
            <a id="sort_a3" class="sortowanie" href="produkty/produkty.php?sort=sort_a3">aktywne</a>
            <a id="sort_a4" class="sortowanie" href="produkty/produkty.php?sort=sort_a4">nieaktywne</a>
            <div style="margin-left:77px">
                <a id="sort_a5" class="sortowanie" href="produkty/produkty.php?sort=sort_a5">daty dodania rosnąco</a>
                <a id="sort_a6" class="sortowanie" href="produkty/produkty.php?sort=sort_a6">daty dodania malejąco</a> 
                <a id="sort_a11" class="sortowanie" href="produkty/produkty.php?sort=sort_a11">ilość rosnąco</a>
                <a id="sort_a12" class="sortowanie" href="produkty/produkty.php?sort=sort_a12">ilość malejąco</a> 
                <a id="sort_a13" class="sortowanie" href="produkty/produkty.php?sort=sort_a13">ID malejąco</a>
                <a id="sort_a14" class="sortowanie" href="produkty/produkty.php?sort=sort_a14">ID rosnąco</a>
                <a id="sort_a15" class="sortowanie" href="produkty/produkty.php?sort=sort_a15">sortowanie malejąco</a>
                <a id="sort_a16" class="sortowanie" href="produkty/produkty.php?sort=sort_a16">sortowanie rosnąco</a>                
            </div>
            </div>        
            
            <div style="clear:both;"></div>               
            
            <?php 
            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                $cSciezka = explode("_",$sciezka);
               } else {
                $cSciezka = array();
            }
            ?>

            <?php
            // przycisk dodania nowego produktu
            ?>
            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="produkty/produkty_dodaj.php">dodaj nowy produkt</a>                    
                </div>         
                <?php if (isset($_GET['kategoria_id'])) { ?>
                <div>
                    <input type="checkbox" id="pamietaj" value="<?php echo (int)$_GET['kategoria_id']; ?>" <?php echo ((isset($_COOKIE['kategoria'])) ? 'checked="checked"' : ''); ?>/><span class="pamietaj_kat"> zaznaczaj automatycznie wybraną kategorię przy dodawaniu nowego produktu</span>
                </div>
                <?php } ?>
            </div>
            
            <div style="clear:both;"></div>
            
            <table style="width:1020px">
                <tr>
                    <td style="width:250px;vertical-align:top">
                    
                        <div class="okno_kateg">
                            <div class="okno_naglowek" style="padding:5px; padding-bottom:8px;">Kategorie</div>
                            <?php
                            echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                            $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                            for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                $podkategorie = false;
                                if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                // sprawdza czy nie jest wybrana
                                $style = '';
                                if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                                    if ((int)$_GET['kategoria_id'] == $tablica_kat[$w]['id']) {
                                        $style = ' style="color:#ff0000"';
                                    }
                                }
                                //
                                echo '<tr>
                                        <td class="lfp"><a href="produkty/produkty.php?kategoria_id='.$tablica_kat[$w]['id'].'" '.$style.'>'.$tablica_kat[$w]['text'].'</a></td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'\',\'\',\'produkty\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            if ( count($tablica_kat) == 0 ) {
                                 echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                            }
                            echo '</table>';
                            unset($tablica_kat,$podkategorie,$style);
                            ?>        

                            <?php 
                            if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) {
                                $sciezka = Kategorie::SciezkaKategoriiId((int)$_GET['kategoria_id'], 'categories');
                                $cSciezka = explode("_",$sciezka);                    
                                if (count($cSciezka) > 1) {
                                    //
                                    $ostatnie = strRpos($sciezka,'_');
                                    $analiza_sciezki = str_replace("_",",",substr($sciezka,0,$ostatnie));
                                    ?>
                                    <script type="text/javascript">
                                    //<![CDATA[            
                                    podkat('<?php echo $analiza_sciezki; ?>', '<?php echo $cSciezka[count($cSciezka)-1]; ?>','','','produkty');
                                    //]]>
                                    </script>
                                <?php
                                unset($sciezka,$cSciezka);
                                }
                            } ?>
                        </div>
                        
                    </td>
                    <td style="width:760px;vertical-align:top;padding-left:10px">
                    
                        <div id="wynik_zapytania" style="width:760px"></div>
                        <div id="aktualna_pozycja">1</div>
                        
                        <div id="akcja">
                            <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                            <div class="lf" style="padding-right:20px">
                                <span onclick="akcja(1)">zaznacz wszystkie</span>
                                <span onclick="akcja(2)">odznacz wszystkie</span>
                            </div>
                            <div id="akc">
                                Wykonaj akcje: 
                                <select name="akcja_dolna">
                                    <option value="0"></option>
                                    <?php
                                    /*
                                    <option value="1">zmień status zaznaczonych na nieaktywne</option>
                                    <option value="2">zmień status zaznaczonych na aktywne</option>
                                    */
                                    ?>
                                    <option value="3">usuń zaznaczone produkty</option>
                                </select>
                            </div>
                            <div style="clear:both;"></div>
                        </div>                        
                        
                        <div id="dolny_pasek_stron"></div>
                        <div id="pokaz_ile_pozycji"></div>
                        <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
                        
                        <?php if ($ile_pozycji > 0) { ?>
                        <div id="zapis"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></div>
                        <?php } ?>                         
                        
                    </td>
                </tr>               
                
            </table>
            
            </form>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('produkty/produkty.php', $zapytanie, $ile_licznika, $ile_pozycji, 'products_id'); ?>
            //]]>
            </script>                     
                
        </div>     

        <?php include('stopka.inc.php'); ?>

    <?php 
    } 
    
}?>
