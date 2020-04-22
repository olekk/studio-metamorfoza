<?php

class Produkty {

  // zwraca tablice z jednostkami miary produktow
  public static function TablicaJednostekMiaryProduktow($brak = '') {

    $sql = $GLOBALS['db']->open_query("SELECT * FROM products_jm s, products_jm_description sd where s.products_jm_id = sd.products_jm_id and sd.language_id = '".$_SESSION['domyslnyJezyk']['id']."' ORDER BY sd.products_jm_name");  

    $tab = array();
    if ($brak != '') {
        $tab['0'] = array('id' => 0,
                       'text' => $brak);
    } 
    
    while ($jm = $sql->fetch_assoc()) {
        $tab[$jm['products_jm_id']] = array('id' => $jm['products_jm_id'],
                       'text' => $jm['products_jm_name']);
    }
    
    $GLOBALS['db']->close_query($sql);                   
    return $tab;
  } 

  // zwraca tablice stawkami VAT - na potrzeby formularza faktury
  public static function TablicaStawekVat($brak = '') {

    $sql = $GLOBALS['db']->open_query("SELECT * FROM tax_rates ORDER BY sort_order");  

    $tab = array();
    if ($brak != '') {
        $tab[] = array('id' => 0,
                       'text' => $brak);
    } 
    
    while ($vat = $sql->fetch_assoc()) {
        $tab[] = array('id' => $vat['tax_rate'].'|'.$vat['tax_short_description'],
                       'text' => $vat['tax_short_description']);
    }
    
    $GLOBALS['db']->close_query($sql);                   
    return $tab;
  }   
  
  // funkcja zwraca wartosc vat po id
  public static function PokazStawkeVAT( $vat_id, $pelna = false ) {

    $wynik = '0';
    $zapytanie = "SELECT * FROM tax_rates WHERE tax_rates_id = '".$vat_id."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    $stawka_vat = $sql->fetch_assoc();
    if ( $pelna == false ) {
         $wynik = $stawka_vat['tax_rate'];
      } else {
         $wynik = array('id' => $stawka_vat['tax_rates_id'],
                        'stawka' => $stawka_vat['tax_rate'],
                        'opis' => $stawka_vat['tax_description'],
                        'opis_krotki' => $stawka_vat['tax_short_description'],
                        'domyslny' => $stawka_vat['tax_default']);   
    }

    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  }    
  
  // zwraca jednostke miary produktow
  public static function PokazJednostkeMiary($id) {

    $sql = $GLOBALS['db']->open_query("SELECT * FROM products_jm_description WHERE products_jm_id = '".$id."' and language_id = '".$_SESSION['domyslnyJezyk']['id']."'");  

    while ($jm = $sql->fetch_assoc()) {
      $nazwa = $jm['products_jm_name'];
    }
    
    $GLOBALS['db']->close_query($sql);                   
    return $nazwa;
  }  
  
  // pasek stanu magazynowego produktu - zalezny od stanu magazynowego
  public static function PokazPasekMagazynu($ilosc) {
  
    $wynik = '';
  
    // jezeli liczba produktow = 0
    if ($ilosc <= 0) {
        $wynik = '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/0.gif" alt="" />';
    }
    
    // jezeli liczba produktow jest wieksza o 0 ale mniejsza o 1/2 liczby minimalnej
    if ($ilosc > 0 && $ilosc <= MAGAZYN_STAN_MINIMALNY / 2) {
        $wynik = '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/1.gif" alt="" />';
    }
    
    // jezeli liczba produktow jest wieksza od 1/2 liczby minimalnej ale mniejsza od liczby minimalnej
    if ($ilosc > MAGAZYN_STAN_MINIMALNY / 2 && $ilosc <= MAGAZYN_STAN_MINIMALNY) {
        $wynik = '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/2.gif" alt="" />';
    }
    
    // jezeli liczba produktow jest wieksza od liczby minimalnej ale mniejsza od 1,5 liczby minimalnej
    if ($ilosc > MAGAZYN_STAN_MINIMALNY && $ilosc <= MAGAZYN_STAN_MINIMALNY * 1.5) {
        $wynik = '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/3.gif" alt="" />';
    }
    
    // jezeli liczba produktow jest wieksza od 1,5 liczby minimanlej ale mniejsza od 2-krotnosci liczby mininalnej
    if ($ilosc > MAGAZYN_STAN_MINIMALNY * 1.5 && $ilosc <= MAGAZYN_STAN_MINIMALNY * 2) {
        $wynik = '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/4.gif" alt="" />';
    }
    
    // jezeli liczba produktow jest wieksza od 2-krotnosci liczby minimalnej
    if ($ilosc > MAGAZYN_STAN_MINIMALNY * 2) {
        $wynik = '<img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/magazyn/5.gif" alt="" />';
    }    

    return $wynik;
    
  }
  
  // -------------------------------------------------------
  
  // zapytanie o promocje 
  public static function SqlPromocjeProste( $warunek = '' ) {
    //
    $warunek_dat = " ";
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
               LEFT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.specials_status = '1' AND 
                         (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end) AND
                         p.products_status = '1' " . $GLOBALS['warunekProduktu'] . $warunek;
                   
    return $zapytanie;
    //
  }
  
  // zapytanie o promocje (strona z listingiem)
  public static function SqlPromocjeZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.specials_status = '1' AND                                  
                                  p.products_status = '1' AND
                                  (p.specials_date = '0000-00-00 00:00:00' OR now() > p.specials_date) AND (p.specials_date_end = '0000-00-00 00:00:00' OR now() < p.specials_date_end) " . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie; 
                  
    return $zapytanie;
    //
  }
  
  // zapytanie o nowosci
  public static function SqlNowosciProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id 
                    FROM products p
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.new_status = '1' AND 
                         p.products_status = '1' " . $GLOBALS['warunekProduktu'] . $warunek;
                  
    return $zapytanie;
    //
  } 
  
  // zapytanie o nowosci (strona z listingiem)
  public static function SqlNowosciZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.new_status = '1' AND                                  
                                  p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie;  
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o nowosci
  public static function SqlPolecaneProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id 
                    FROM products p
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.featured_status = '1' AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . $warunek;
                  
    return $zapytanie;
    //
  }   
  
  // zapytanie o polecane (strona z listingiem)
  public static function SqlPolecaneZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.featured_status = '1' AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie;    
                  
    return $zapytanie;
    //
  }   
  
  // zapytanie o nasz hit
  public static function SqlNaszHitProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.star_status = '1' AND 
                         p.products_status = '1' " . $GLOBALS['warunekProduktu'] . $warunek;
                  
    return $zapytanie;
    //
  }   
  
  // zapytanie o hity (strona z listingiem)
  public static function SqlNaszHitZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.star_status = '1' AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie;     
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o bestsellery
  public static function SqlBestselleryProste( $warunek = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.products_status = '1' " . $GLOBALS['warunekProduktu'] . " AND
                         p.products_ordered > 0 " . $warunek;
      
    return $zapytanie;
    //
  }    

  // zapytanie o bestsellery (w boxach)
  public static function SqlBestsellery( $ilosc = '' ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_ordered, 
                         pd.products_name, pd.products_seo_url
                    FROM products p
               LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.products_status = '1' AND p.products_ordered > 0 " . $GLOBALS['warunekProduktu'] . "
                ORDER BY p.products_ordered DESC
                   LIMIT " . $ilosc ;
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o bestsellery (strona z listingiem)
  public static function SqlBestselleryZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.products_ordered > 0 AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie;
                  
    return $zapytanie;
    //
  }    
  
  // zapytanie o produkty oczekiwane
  public static function SqlOczekiwaneProste( $warunek = '' ) {
    //
    $data = date('Y-m-d');
    
    $zapytanie = "SELECT DISTINCT p.products_id 
                    FROM products p
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.products_date_available > '" . $data . "' AND 
                         p.products_status = '1' " . $GLOBALS['warunekProduktu'] . $warunek;
                  
    unset($data);
    
    return $zapytanie;
    //
  }   
  
  // zapytanie o produkty oczekiwane (strona z listingiem)
  public static function SqlOczekiwaneZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $data = date('Y-m-d');
    
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.products_date_available > '" . $data . "' AND    
                                  c.categories_status = '1' AND
                                  p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie;    
    unset($data);
    
    return $zapytanie;
    //
  }   
  
  // zapytanie o produkty z recenzjami
  public static function SqlProduktyZawierajaceRecenzje() {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              INNER JOIN reviews r ON p.products_id = r.products_id AND r.approved = '1'
              INNER JOIN reviews_description rd ON r.reviews_id = rd.reviews_id AND rd.languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'];
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o recenzje
  public static function SqlRecenzje( $sortowanie ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, r.reviews_id
                    FROM reviews r
              INNER JOIN reviews_description rd ON rd.reviews_id = r.reviews_id AND rd.languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
              RIGHT JOIN products p ON p.products_id = r.products_id AND p.products_status = '1'" . $GLOBALS['warunekProduktu'] . "
               LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE r.approved = '1'
                ORDER BY " . $sortowanie;
                  
    return $zapytanie;
    //
  } 

  // zapytanie do recenzji
  public static function SqlRecenzja( $id ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM reviews r
              INNER JOIN reviews_description rd ON rd.reviews_id = r.reviews_id AND rd.languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
               LEFT JOIN products p ON p.products_id = r.products_id AND p.products_status = '1'" . $GLOBALS['warunekProduktu'] . "
               LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE r.approved = '1' AND 
                         r.reviews_id = '" . $id . "'";
                  
    return $zapytanie;
    //
  }   
  
  // zapytanie do napisz recenzje
  public static function SqlNapiszRecenzje( $id ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.products_id = '" . $id . "' AND p.products_status = '1'" . $GLOBALS['warunekProduktu'];
                  
    return $zapytanie;
    //
  }

  // zapytanie do szukania
  public static function SqlSzukajProdukty( $warunkiSzukania, $sortowanie, $dodatkowePola = '' ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id ";
    if ( $dodatkowePola != '' ) {
        $zapytanie .= "LEFT JOIN products_to_products_extra_fields p2pef ON p.products_id = p2pef.products_id AND products_extra_fields_id IN (".$dodatkowePola.") ";
    }
    $zapytanie .= "    RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                        LEFT JOIN products_stock ps ON ps.products_id = p.products_id
                            WHERE p.products_status = '1'"  . $GLOBALS['warunekProduktu'] . $warunkiSzukania . "
                         ORDER BY " . $sortowanie;

    return $zapytanie;
    //
  }  

  // zapytanie do porownania produktow
  public static function SqlPorownanieProduktow( $doPorownaniaId ) {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_status, pd.products_name, pd.products_seo_url
                             FROM products p
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                            WHERE p.products_id in (" . $doPorownaniaId . ")
                         ORDER BY pd.products_name";  
                  
    return $zapytanie;
    //
  }  

  // zapytanie od id produktow do listingu z kategorii  
  public static function SqlProduktyKategorii( $idPodkategorii, $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                        LEFT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE ptc.categories_id in (" . $idPodkategorii . ") AND                                  
                                  p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie;   
                  
    return $zapytanie;
    //
  } 
  
  // zapytanie od id produktow do listingu z producenta 
  public static function SqlProduktyProducenta( $idProducenta, $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.manufacturers_id = '" . $idProducenta . "' AND
                                  c.categories_status = '1' AND
                                  p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie;    

    return $zapytanie;
    //
  }  
  
  // zapytanie dla akcesorii dodatkowych
  public static function SqlProduktyAkcesoriaDodatkowe( $idProduktu, $ilosc = 9999 ) {
    //
    $zapytanie = "SELECT DISTINCT pa.pacc_products_id_slave as products_id
                             FROM products_accesories pa
                       RIGHT JOIN products p ON p.products_id = pa.pacc_products_id_slave AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . "
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                            WHERE pa.pacc_products_id_master = '" . $idProduktu . "'" . (($ilosc < 9999) ? ' limit ' . $ilosc : '');
                    

    return $zapytanie;
    //
  }
  
  // zapytanie dla produktow podobnych
  public static function SqlProduktyPodobne( $idProduktu, $ilosc = KARTA_PRODUKTU_PODOBNE_PRODUKTY_ILOSC ) {
    //
    $zapytanie = "SELECT DISTINCT pa.pop_products_id_slave as products_id
                             FROM products_options_products pa
                       RIGHT JOIN products p ON p.products_id = pa.pop_products_id_slave AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . "
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                            WHERE pa.pop_products_id_master = '" . $idProduktu . "' ORDER BY RAND() LIMIT " . $ilosc;
            
    return $zapytanie;
    //
  }  
  
  // zapytanie dla klienci zakupili takze
  public static function SqlProduktyKlienciKupiliTakze( $idProduktu, $naZamowien ) {
    //
    if ( count($naZamowien) > 0 ) {
        //
        $zapytanie = "SELECT DISTINCT p.products_id 
                        FROM orders_products opb, orders o, products p 
                  RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                   RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                       WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . " AND                   
                             opb.products_id != '" . $idProduktu . "' 
                             AND opb.products_id = p.products_id 
                             AND opb.orders_id = o.orders_id 
                             AND o.orders_id IN (" . implode(',', $naZamowien) . ") 
                             GROUP BY p.products_id 
                             ORDER BY RAND() LIMIT " . KARTA_PRODUKTU_KLIENCI_KUPILI_TAKZE_ILOSC;
        //
      } else {
        //
        $zapytanie = "SELECT p.products_id FROM products p WHERE p.products_status = '2'";
        //
    }
    
    return $zapytanie;
    //
  }  

  // zapytanie o nasz hit
  public static function SqlProduktyPozostaleKategorii( $idKategoriiProducenta, $typ, $idProduktu ) {
    //
    if ( $typ == 'kategoria' ) {
        //
        $zapytanie = "SELECT DISTINCT p.products_id
                        FROM products p
                  RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id AND ptc.categories_id = '" . $idKategoriiProducenta . "'
                  RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                       WHERE p.products_id != '" . $idProduktu . "' AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . " ORDER BY RAND() LIMIT " . KARTA_PRODUKTU_POZOSTALE_PRODUKTY_ILOSC;
        //
    }
    
    if ( $typ == 'producent' ) {
        //
        $zapytanie = "SELECT DISTINCT p.products_id
                        FROM products p
                  RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                  RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                       WHERE p.products_id != '" . $idProduktu . "' AND p.manufacturers_id = '" . $idKategoriiProducenta . "' AND p.products_status = '1' " . $GLOBALS['warunekProduktu'] . " ORDER BY RAND() LIMIT " . KARTA_PRODUKTU_POZOSTALE_PRODUKTY_ILOSC;
        //
    }    

    return $zapytanie;
    //
  }    
  
  // zapytanie o produkty nastepny poprzedni
  public static function ProduktyPoprzedniNastepny( $idKategoriiProducenta, $sortowanie, $idProduktu  ) {
    //
    //
    $tablica = array();

    $zapytanie = "SELECT DISTINCT p.products_id, p.sort_order, pd.products_name
                        FROM products p
                  RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id AND ptc.categories_id = '" . $idKategoriiProducenta . "'
                  RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                  LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                       WHERE p.products_status = '1' " . $GLOBALS['warunekProduktu'] . " ORDER BY ".$sortowanie."";
        //

    $sql = $GLOBALS['db']->open_query($zapytanie);
    $IloscProduktow = (int)$GLOBALS['db']->ile_rekordow($sql);

    if ( $GLOBALS['db']->ile_rekordow($sql) > 0 ) {

        $info = $sql->fetch_assoc();

        // Jezeli jest tylko jeden produkt
        $Poprzedni = $Nastepny = $info['products_id'];
        $PoprzedniNazwa = $NastepnyNazwa = $info['products_name'];

        // Jezeli wybrany produkt jest pierwszy
        if ($info['products_id'] == (int)$idProduktu ) {

            $info = $sql->fetch_assoc();
            $Poprzedni = $Nastepny = $info['products_id'];
            $PoprzedniNazwa = $NastepnyNazwa = $info['products_name'];
            while ( $info = $sql->fetch_assoc() ) {
                $Poprzedni = $info['products_id'];
                $PoprzedniNazwa = $info['products_name'];
            }
        // Jezeli nie jest to pierwszy produkt
        } else { 
            while ( $info = $sql->fetch_assoc() ) {
                if ( $info['products_id'] == (int)$idProduktu ) {
                    $info = $sql->fetch_assoc();
                    $Nastepny = $info['products_id'];
                    $NastepnyNazwa = $info['products_name'];
                    break;
                } else {
                    $Poprzedni = $info['products_id'];
                    $PoprzedniNazwa = $info['products_name'];
                }
            }
        }

        $GLOBALS['db']->znajdz_rekord($sql, 0);
        $info = $sql->fetch_assoc();
        $PierwszyProdukt = $info['products_id'];

        $GLOBALS['db']->znajdz_rekord($sql, $IloscProduktow-1);
        $info = $sql->fetch_assoc();
        $OstatniProdukt = $info['products_id'];

        if ( $PierwszyProdukt != $idProduktu ) {
            $tablica['prev'] = array('id' => $Poprzedni,
                                     'nazwa' => $PoprzedniNazwa
                            );
            }
        if ( $OstatniProdukt != $idProduktu ) {
            $tablica['next'] = array('id' => $Nastepny,
                                     'nazwa' => $NastepnyNazwa
                            );
        }
    }
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);    

    return $tablica;
    //
  }    

  // zapytanie do cennika - id produktow z danej kategorii
  public static function SqlProduktyCennik( $idKat ) {
  
    $IdPodkategorii = $idKat . ',';
    //    
    // musi znalezc podkategorie dla danej kategorii
    foreach(Kategorie::DrzewoKategorii($idKat) as $IdKategorii => $Tablica) {
        $IdPodkategorii .= Kategorie::TablicaPodkategorie($Tablica);
    }                 
    //
    $IdPodkategorii = substr($IdPodkategorii, 0, -1);        
    //       
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                    FROM products p
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id AND ptc.categories_id in (" . $IdPodkategorii . ")
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
               LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                   WHERE p.products_status = '1' " . $GLOBALS['warunekProduktu'] . "
                ORDER BY p.sort_order, pd.products_name";

    unset($IdPodkategorii);

    return $zapytanie;
    //
  }   
  
  // zapytanie o produkty dla autouzupelnienia
  public static function SqlAutoUzupelnienie() {
    //
    $zapytanie = "SELECT DISTINCT p.products_id, pd.products_name
                    FROM products p
               LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
              RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
              RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                   WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . "
                ORDER BY pd.products_name ASC";
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o wszystkie produkty - do katalogu produktow
  public static function SqlProduktyZlozone( $warunkiFiltrowania, $sortowanie ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . $warunkiFiltrowania . "
                         ORDER BY " . $sortowanie; 
                  
    return $zapytanie;
    //
  }  
  
  // zapytanie o wszystkie produkty
  public static function SqlProduktyProste( $sortowanie = '', $limit = '' ) {
    //
    if ( !isset($_SESSION['customer_id']) || $_SESSION['poziom_cen'] == 1 ) {
         $DodWarunekCen = '(p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100)';
       } else {
         $DodWarunekCen = '( (case when (p.products_price_tax_' . $_SESSION['poziom_cen'] . ' > 0) then (p.products_price_tax_' . $_SESSION['poziom_cen'] . '/cu.value)+(cu.value*cu.currencies_marza/100) else (p.products_price_tax/cu.value)+(cu.value*cu.currencies_marza/100) end) )';
    }   
    //
    $zapytanie = "SELECT DISTINCT p.products_id, p.products_date_added, p.products_price_tax, pd.products_name, cu.value, cu.currencies_marza, " . $DodWarunekCen . " AS cena
                             FROM products p
                        LEFT JOIN currencies cu ON cu.currencies_id = p.products_currencies_id
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                        LEFT JOIN products_description pd ON pd.products_id = p.products_id AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                            WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'] . 
                         (( $sortowanie != '' ) ? " ORDER BY " . $sortowanie : "" ) . 
                         (( $limit != '' ) ? " LIMIT " . $limit : "" );
                  
    return $zapytanie;
    //
  }     
  
  // zapytanie o wszystkie produkty do statystyki
  public static function SqlProduktyProsteStatystyka() {
    //
    $zapytanie = "SELECT DISTINCT p.products_id
                             FROM products p
                       RIGHT JOIN products_to_categories ptc ON ptc.products_id = p.products_id
                       RIGHT JOIN categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'
                            WHERE p.products_status = '1'" . $GLOBALS['warunekProduktu'];
                  
    return $zapytanie;
    //
  }   
    
  // funkcja zwracajaca nazwe kategorii do jakiej nalezy produkt
  public static function pokazKategorieProduktu( $produkt_id ) {

    $wynik = '';

    $zapytanie = "
               SELECT cd.categories_name FROM categories_description cd
               LEFT JOIN products_to_categories p2c ON p2c.categories_id = cd.categories_id
               WHERE p2c.products_id = '".$produkt_id."' AND cd.language_id = '".$_SESSION['domyslnyJezyk']['id']."' ";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    while ($info = $sql->fetch_assoc()) {
        $wynik = $info['categories_name'];      
    }
    //
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie, $info, $sql);

    return $wynik;

  }
  
  // funkcja zwraca tablice id produktow modulowych
  public static function ProduktyModulowe( $limit = 9999, $modul ) {
  
    switch ($modul) {
        case "polecane":
            $plik = 'PolecaneProste';
            $cache = CACHE_POLECANE;
            $sqlZap = Produkty::SqlPolecaneProste();
            break;  
        case "oczekiwane":
            $plik = 'OczekiwaneProste';
            $cache = CACHE_OCZEKIWANE;
            $sqlZap = Produkty::SqlOczekiwaneProste();
            break;
        case "nowosci":
            $plik = 'NowosciProste';
            $cache = CACHE_NOWOSCI;
            $sqlZap = Produkty::SqlNowosciProste();
            break; 
        case "hity":
            $plik = 'NaszHitProste';
            $cache = CACHE_HITY;
            $sqlZap = Produkty::SqlNaszHitProste();
            break; 
        case "promocje":
            $plik = 'PromocjeProste';
            $cache = CACHE_PROMOCJE;
            $sqlZap = Produkty::SqlPromocjeProste();
            break; 
        // wszystkie produkty
        case "produkty":
            $plik = 'Produkty';
            $cache = CACHE_PRODUKTY;
            $sqlZap = Produkty::SqlProduktyProste();
            break;             
    }      
  
    $Tablica = array();
    $WybraneProdukty = array();

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj($plik, $cache, true);

    if ( !$WynikCache && !is_array($WynikCache) ) {
         $sql_random = $GLOBALS['db']->open_query( $sqlZap );
         while ($info_random = $sql_random->fetch_assoc()) {
            $Tablica[] = $info_random['products_id'];
         }
         //
         $GLOBALS['cache']->zapisz($plik, $Tablica, $cache, true);
         //
         $GLOBALS['db']->close_query($sql_random); 
    } else {
         $Tablica = $WynikCache;
    }  
    
    //wybranie tylko unikalnych rekordow w tablicy;
    $Tablica = array_unique($Tablica);
    
    if (count($Tablica) > 0) {
        $WybraneProdukty = explode(',',Funkcje::wylosujElementyTablicyJakoTekst($Tablica, $limit));
    }
    
    unset($Tablica, $plik, $cache, $sqlZap);
    
    return $WybraneProdukty;
  
  }
  
  // funkcja zwraca tablice id produktow modulowych z recenzjami
  public static function ProduktyModuloweRecenzje( $limit = 9999 ) {

    $Tablica = array();
    $WybraneProdukty = array();

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('ProduktyZawierajaceRecenzje_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_RECENZJE, true);

    if ( !$WynikCache && !is_array($WynikCache) ) {
         $sql_random = $GLOBALS['db']->open_query( Produkty::SqlProduktyZawierajaceRecenzje() );
         while ($info_random = $sql_random->fetch_assoc()) {
            $Tablica[] = $info_random['products_id'];
         }
         //
         $GLOBALS['cache']->zapisz('ProduktyZawierajaceRecenzje_' . $_SESSION['domyslnyJezyk']['kod'], $Tablica, CACHE_RECENZJE, true);
         //
         $GLOBALS['db']->close_query($sql_random); 
    } else {
         $Tablica = $WynikCache;
    }  
    
    //wybranie tylko unikalnych rekordow w tablicy;
    $Tablica = array_unique($Tablica);
    
    if (count($Tablica) > 0) {
        $WybraneProdukty = explode(',',Funkcje::wylosujElementyTablicyJakoTekst($Tablica, $limit));
    }
    
    unset($Tablica, $plik, $cache, $sqlZap);
    
    return $WybraneProdukty;
  
  }  
  
  // funkcja zwraca tablice id produktow modulowych - bestsellery
  public static function ProduktyModuloweBestsellery( $limit = 9999 ) {

    $Tablica = array();
    $WybraneProdukty = array();

    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('Bestsellery', 30, true);

    if ( !$WynikCache && !is_array($WynikCache) ) {
         //
         $sql_random = $GLOBALS['db']->open_query( Produkty::SqlBestsellery( 200 ) );
         while ($info_random = $sql_random->fetch_assoc()) {
            $Tablica[] = $info_random['products_id'];
         }
         //
         $GLOBALS['cache']->zapisz('Bestsellery', $Tablica, 30, true);
         //
         $GLOBALS['db']->close_query($sql_random); 
    } else {
         $Tablica = $WynikCache;
    }  
    
    //wybranie tylko unikalnych rekordow w tablicy;
    $Tablica = array_unique($Tablica);
    
    $limt = 0;
    foreach ( $Tablica as $Poz ) {
        if ($limt < $limit) {
            $WybraneProdukty[] = $Poz;
        } else {
            break;
        }
        $limt++;
    }
    
    unset($Tablica, $limt);
    
    return $WybraneProdukty;
  
  }    

}

?>