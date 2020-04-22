<?php

class Klient {

  public function Klient( $id_klienta ) {

    $this->id_klienta = $id_klienta;

    // informacje ogolne o kliencie
    $this->info = array();
    // informacje znizkach klienta
    $this->znizki = array();

    if ( !isset($_SESSION['znizkiKlienta']) ) {
      $_SESSION['znizkiKlienta'] = $this->ZnizkiKlienta($this->id_klienta);
    }

  }
  
  // funkcja sprawdzajaca poprawnosc hasla klienta podczas logowania
  public static function sprawdzHasloKlienta($hasloBazy, $hasloKlienta) {
    //
    if (Funkcje::czyNiePuste($hasloBazy) && Funkcje::czyNiePuste($hasloKlienta)) {
        //
        $spr = explode(':', $hasloKlienta);
        //
        if (sizeof($spr) != 2) return false;
        if (md5($spr[1] . $hasloBazy) == $spr[0]) { return true; }
    }
    //
    return false;
  }   

  // funkcja usuwajaca informacje po wylogowaniu klienta
  public static function WylogujKlienta() {

    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0) {
        //
        unset($_SESSION['customer_default_address_id'], $_SESSION['customer_firstname'], $_SESSION['customer_email'], $_SESSION['znizkiKlienta'], $_SESSION['adresDostawy'], $_SESSION['adresFaktury'], $_SESSION['poziom_cen'], $_SESSION['customers_groups_id'], $_SESSION['min_zamowienie']);
        unset($_SESSION['krajDostawy'], $_SESSION['rodzajDostawy']);
        $_SESSION['customer_id'] = 0;
        $_SESSION['gosc'] = 1;
        if ( isset($_SESSION['koszyk']) ) {
            unset($_SESSION['koszyk']);
        }
        if ( isset($_SESSION['podsumowanieZamowienia']) ) {
            unset($_SESSION['podsumowanieZamowienia']);
        }
        if ( isset($_SESSION['punktyKlienta']) ) {
            unset($_SESSION['punktyKlienta']);
        }
        if ( isset($_SESSION['kuponRabatowy']) ) {
            unset($_SESSION['kuponRabatowy']);
        }
        //
        // program partnerski
        if ( isset($_SESSION['pp_id']) ) {
             unset($_SESSION['pp_id']);
        }  
        if ( isset($_SESSION['pp_statystyka']) ) {
            unset($_SESSION['pp_statystyka']);
        }        
    }
    return;
  }

  // funkcja zwraca w formie tablicy znizki klienta
  public static function ZnizkiKlienta($idKlienta, $ZnizkaIndywidualna = null) {

    $TablicaWynik = array();

    // znizka indywidualna
    if (!empty($ZnizkaIndywidualna) && $ZnizkaIndywidualna != 0) {
        //
        $TablicaWynik[] = array('Indywidualna','Indywidualna',$ZnizkaIndywidualna);
        //
      } else { 
        //
        $zapytanie = "select customers_discount from customers where customers_id = '" . $idKlienta . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        $info = $sql->fetch_assoc();
        //
        if ($info['customers_discount'] != 0) {
            $TablicaWynik[] = array('Indywidualna','Indywidualna',$info['customers_discount']);
        }
        //
        $GLOBALS['db']->close_query($sql);
        unset($info, $zapytanie);        
    }

    // znizka grupowa
    $zapytanie = "select cg.customers_groups_discount, c.customers_groups_id from customers c, customers_groups cg where customers_id = '" . $idKlienta . "' and c.customers_groups_id = cg.customers_groups_id";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    $info = $sql->fetch_assoc();
    //
    $IdGrupyKlienta = $info['customers_groups_id'];
    //
    if ($info['customers_groups_discount'] != 0) {
        $TablicaWynik[] = array('Grupa klientów','Grupa klientów',$info['customers_groups_discount']);
    }
    //
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie);         
    //
    
    // znizki dla producentow
    $zapytanie = "select dm.discount_discount, dm.discount_manufacturers_id from discount_manufacturers dm where dm.discount_customers_id = '" . $idKlienta . "' or dm.discount_groups_id = '" . $IdGrupyKlienta . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    while ($info = $sql->fetch_assoc()) {
        //
        $znizkiProducentow = explode(',', $info['discount_manufacturers_id']);
        //
        foreach ( $znizkiProducentow as $producentId ) {
            //       
            // szuka czy juz nie ma takiego producenta
            $Jest = false;
            foreach ( $TablicaWynik as $Tmp ) {
               if ( $Tmp[0] == 'Producent' && $Tmp[1] == $producentId ) {
                    $Jest = true;
               }
            }
            //
            if ( $Jest == false ) {
                $TablicaWynik[] = array('Producent',$producentId,$info['discount_discount']);
            }
            //
            unset($Jest);
            //
        }
        //
        unset($znizkiProducentow);
        //
    }
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie);         
    //    
    
    // znizki dla kategorii
    $zapytanie = "select dp.discount_discount, dp.discount_categories_id from discount_categories dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id = '" . $IdGrupyKlienta . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    while ($info = $sql->fetch_assoc()) {
        //
        $znizkiKategorii = explode(',', $info['discount_categories_id']);
        //
        foreach ( $znizkiKategorii as $kategoriaId ) {
             //        
             // szuka czy juz nie ma takiej kategorii
             $Jest = false;
             foreach ( $TablicaWynik as $Tmp ) {
                 if ( $Tmp[0] == 'Kategoria' && $Tmp[1] == $kategoriaId ) {
                      $Jest = true;
                 }
             }
             //             
             if ( $Jest == false ) { 
                //
                $TablicaWynik[] = array('Kategoria',$kategoriaId,$info['discount_discount']);
                //
             }
             //
             unset($Jest);
             //
        }
        //
        unset($znizkiKategorii);
        //    
    }
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie);         
    //     
    
    // znizki dla produktow
    $zapytanie = "select dp.discount_discount, dp.discount_products_id from discount_products dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id = '" . $IdGrupyKlienta . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    while ($info = $sql->fetch_assoc()) {
        // szuka czy juz nie ma takiego produktu
        $Jest = false;
        foreach ( $TablicaWynik as $Tmp ) {
           if ( $Tmp[0] == 'Produkt' && $Tmp[1] == $info['discount_products_id'] ) {
                $Jest = true;
           }
        }
        //    
        if ( $Jest == false ) {
            //
            $TablicaWynik[] = array('Produkt',$info['discount_products_id'],$info['discount_discount']);
            //
        }
        //
        unset($Jest);
        //    
    }
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie);         
    //    
    
    return $TablicaWynik;
  }
  
  // funkcja zwraca w formie tablicy znizki klienta
  public static function ZnizkiKlientaInfo($idKlienta, $ZnizkaIndywidualna = null) {

    $TablicaWynik = array();

    // znizka indywidualna
    if (!empty($ZnizkaIndywidualna) && $ZnizkaIndywidualna != 0) {
        //
        $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_INDYWIDUALNA'],'Indywidualna',$ZnizkaIndywidualna);
        //
      } else { 
        //
        $zapytanie = "select customers_id, customers_discount from customers where customers_id = '" . $idKlienta . "'";
        $sql = $GLOBALS['db']->open_query($zapytanie);
        $info = $sql->fetch_assoc();
        //
        if ($info['customers_discount'] != 0) {
            $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_INDYWIDUALNA'],'Indywidualna',$info['customers_discount'],$info['customers_id']);
        }
        //
        $GLOBALS['db']->close_query($sql);
        unset($info, $zapytanie);        
    }

    // znizka grupowa
    $zapytanie = "select cg.customers_groups_discount, c.customers_groups_id from customers c, customers_groups cg where customers_id = '" . $idKlienta . "' and c.customers_groups_id = cg.customers_groups_id";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    $info = $sql->fetch_assoc();
    //
    $IdGrupyKlienta = $info['customers_groups_id'];
    //
    if ($info['customers_groups_discount'] != 0) {
        $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_GRUPA_KLIENTOW'],$GLOBALS['tlumacz']['ZNIZKI_GRUPA_KLIENTOW'],$info['customers_groups_discount'],$info['customers_groups_id']);
    }
    //
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie);         
    //
    
    // znizki dla producentow
    $zapytanie = "select * from discount_manufacturers dm where dm.discount_customers_id = '" . $idKlienta . "' or dm.discount_groups_id = '" . $IdGrupyKlienta . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    $NazwyProducentow = Producenci::TablicaProducenci();
    //
    while ($info = $sql->fetch_assoc()) {
        //
        $znizkiProducentow = explode(',', $info['discount_manufacturers_id']);
        //
        foreach ( $znizkiProducentow as $producentId ) {
            //
            if ( isset($NazwyProducentow[$producentId]) ) {
                 //       
                 // szuka czy juz nie ma takiego producenta
                 $Jest = false;
                 foreach ( $TablicaWynik as $Tmp ) {
                     if ( $Tmp[0] == $GLOBALS['tlumacz']['ZNIZKI_PRODUCENT'] && $Tmp[3] == $producentId ) {
                          $Jest = true;
                     }
                 }
                 //
                 if ( $Jest == false ) {
                      $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_PRODUCENT'],$NazwyProducentow[$producentId]['Nazwa'],$info['discount_discount'], $producentId);
                 }
                 //
                 unset($Jest);
                 //
            }
            //
        }
        //
        unset($znizkiProducentow);
        //
    }
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie, $NazwyProducentow);         
    //    
    
    // znizki dla kategorii
    $zapytanie = "select dp.discount_discount, dp.discount_categories_id from discount_categories dp where dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id = '" . $IdGrupyKlienta . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    while ($info = $sql->fetch_assoc()) {
        //
        $znizkiKategorii = explode(',', $info['discount_categories_id']);
        //
        foreach ( $znizkiKategorii as $kategoriaId ) {
            //
            if ( isset($GLOBALS['tablicaKategorii'][$kategoriaId]) ) {
                 //        
                 // szuka czy juz nie ma takiej kategorii
                 $Jest = false;
                 foreach ( $TablicaWynik as $Tmp ) {
                     if ( $Tmp[0] == $GLOBALS['tlumacz']['ZNIZKI_KATEGORIA'] && $Tmp[3] == $kategoriaId ) {
                          $Jest = true;
                     }
                 }
                 //
                 if ( $Jest == false ) {          
                     //    
                     $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_KATEGORIA'],$GLOBALS['tablicaKategorii'][$kategoriaId]['Nazwa'],$info['discount_discount'],$kategoriaId);
                     //
                 }
                 //
                 unset($Jest);
                 //                     
            }
            //
        }
        //
        unset($znizkiKategorii);
        //
    }
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie);         
    //     
    
    // znizki dla produktow
    $zapytanie = "select dp.discount_discount, pd.products_name, pd.products_id from discount_products dp, products_description pd where (dp.discount_customers_id = '" . $idKlienta . "' or discount_groups_id = '" . $IdGrupyKlienta . "')
                    and pd.products_id = dp.discount_products_id and pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    //
    while ($info = $sql->fetch_assoc()) {
        // szuka czy juz nie ma takiego produktu
        $Jest = false;
        foreach ( $TablicaWynik as $Tmp ) {
           if ( $Tmp[0] == $GLOBALS['tlumacz']['ZNIZKI_PRODUKT'] && $Tmp[3] == $info['products_id'] ) {
                $Jest = true;
           }
        }
        //    
        if ( $Jest == false ) {
            //
            $TablicaWynik[] = array($GLOBALS['tlumacz']['ZNIZKI_PRODUKT'],$info['products_name'],$info['discount_discount'],$info['products_id']);
            //
        }
        //
        unset($Jest);
        //    
    }
    $GLOBALS['db']->close_query($sql);
    unset($info, $zapytanie);         
    //    
    
    return $TablicaWynik;
  }
  
  // funkcja wyswietlajaca dane adresowe klienta
  public static function PokazAdresKlienta( $typ ) {
    global $zamowienie;

    $dane = Array();
    if ( $typ == 'klient' ) {
      $dane = $zamowienie->klient;
    } elseif ( $typ == 'dostawa' ) {
      $dane = $zamowienie->dostawa;
    } elseif ( $typ == 'platnik' ) {
      $dane = $zamowienie->platnik;
    }

    $tekst = '';
    $tekst .= $dane['nazwa'] . '<br />';
    $tekst .= ( $dane['firma'] != '' ? $dane['firma'] . '<br />' : '' );
    $tekst .= $dane['ulica'] . '<br />';
    $tekst .= $dane['kod_pocztowy'] . ' ' . $dane['miasto'] . '<br />';
    $tekst .= ( $dane['wojewodztwo'] != '' ? $dane['wojewodztwo'] . '<br />' : '' );
    $tekst .= ( $dane['kraj'] != '' ? $dane['kraj'] . '<br />' : '' );
    $tekst .= '<br />';
    $tekst .= ( $dane['nip'] != '' && $typ == 'platnik' ? 'NIP: ' . $dane['nip'] . '<br />' : '' );

    if ( $typ == 'klient' ) {
      $tekst .= 'Tel: ' . $dane['telefon'] . '<br />';
      $tekst .= $dane['adres_email'];
    }

    return $tekst;
  }  

  // funkcja wyswietlajaca komentarz do zamowienia klienta
  public static function pokazKomentarzZamowienia( $zamowienie_id ) {

    $zapytanie = "SELECT orders_status_id, customer_notified, comments FROM orders_status_history WHERE orders_id = '" . $zamowienie_id . "' ORDER BY date_added LIMIT 1";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($komentarz = $sql->fetch_assoc()) {
      $wynik = $komentarz['comments'];
    }
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  }  
  
  // funkcja zwaraca ilosc zamowien klienta
  public static function IloscZamowien( $id_email_klienta, $typ = 'id', $nr_zam = '' ) {

    if ( $typ == 'id' ) {
    
        $zapytanie = "SELECT orders_id FROM orders WHERE customers_id = '" . $id_email_klienta . "'";
        
      } else {
      
        $zapytanie = "SELECT orders_id FROM orders WHERE lower(customers_email_address) = '" . strtolower($id_email_klienta) . "' and orders_id != '" . $nr_zam . "'";
        
    }

    $sql = $GLOBALS['db']->open_query($zapytanie);

    $wynik = (int)$GLOBALS['db']->ile_rekordow($sql);
    
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  }   
  
  // funkcja zwracajaca wartosc minimalnego zamowienia
  public static function MinimalneZamowienie() {
  
    if ( isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
        //
        $MinimalneZamowienieGrupy = $_SESSION['min_zamowienie'];
        //
      } else {
        // jezeli klient nie jest zalogowany przyjmie min zamowienie domyslnej grupy
        $zapytanie = "SELECT customers_groups_min_amount FROM customers_groups WHERE customers_groups_id = '1'";
        $sql = $GLOBALS['db']->open_query($zapytanie);  
        $info = $sql->fetch_assoc(); 
        //
        $MinimalneZamowienieGrupy = $info['customers_groups_min_amount'];
        //
        $GLOBALS['db']->close_query($sql); 
        unset($zapytanie, $info);     
        //
    }  
    
    return $MinimalneZamowienieGrupy;
    
  } 
  
  // funkcja do wyswietlania nazwy panstwa
  public static function pokazNazwePanstwa($id) {

    $wynik = ''; 
    $zapytanie = "SELECT countries_name FROM countries_description WHERE countries_id = ".(int)$id." AND language_id = '".$_SESSION['domyslnyJezyk']['id']."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($nazwa_grupy = $sql->fetch_assoc()) {
      $wynik = $nazwa_grupy['countries_name'];
    }
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);

  	return $wynik;
  }    

  // funkcja do wyswietlania nazwy wojewodztwa
  public static function pokazNazweWojewodztwa($id) {

    $wynik = ''; 
    $zapytanie = "SELECT zone_name FROM zones WHERE zone_id = ".(int)$id." ";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($nazwa = $sql->fetch_assoc()) {
      $wynik = $nazwa['zone_name'];
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

  	return $wynik;
  }  

  // funkcja generujaca rozwijana liste panstw
  public static function ListaPanstw( $tryb = 'countries_id' ) {

    $tablicaPanstw = array();

    $panstwa = "
      SELECT c.".$tryb.", cd.countries_name 
        FROM countries c
        LEFT JOIN countries_description cd ON cd.countries_id = c.countries_id AND cd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'
        ORDER BY cd.countries_name
    ";

    $sql = $GLOBALS['db']->open_query($panstwa);

    while ($wartosciPanstw = $sql->fetch_assoc()) {
      $tablicaPanstw[] = array('id' => $wartosciPanstw[$tryb],
                               'text' => $wartosciPanstw['countries_name']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($wartosciPanstw, $panstwa);

    return $tablicaPanstw;
  }

  
  // funkcja generujaca rozwijana liste wojewodztw
  public static function ListaWojewodztw($filtr = '') {

    $tablicaWojewodztw = array();

    $wojewodztwa = "
      SELECT zone_id, zone_country_id, zone_name 
        FROM zones 
        WHERE zone_country_id = '".$filtr."' 
        ORDER BY zone_name
    ";

    $sql = $GLOBALS['db']->open_query($wojewodztwa);

    while ($wartosciWojewodztw = $sql->fetch_assoc()) {
      $tablicaWojewodztw[] = array('id' => $wartosciWojewodztw['zone_id'],
                                   'text' => $wartosciWojewodztw['zone_name']);
    }
    $GLOBALS['db']->close_query($sql);
    unset($wartosciWojewodztw, $wojewodztwa);

    return $tablicaWojewodztw;
  }  
  
  
  // funkcja generujaca tablice zawierajaca statusy punktow
  public static function ListaStatusowReklamacji( $dowolna = true, $tekst = 'dowolny', $CzyscHtml = false ) {

    $tablica = array();
    if ( $dowolna ) {
      $tablica[] = array('id' => '0', 'text' => $tekst);
    }
    $zapytanie = "SELECT s.points_status_id, sd.points_status_name FROM customers_points_status s LEFT JOIN customers_points_status_description sd ON sd.points_status_id = s.points_status_id AND sd.language_id = '".$_SESSION['domyslnyJezyk']['id']."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($nazwa_statusu = $sql->fetch_assoc()) {
      $tablica[] = array('id' => $nazwa_statusu['points_status_id'], 'text' => (($CzyscHtml == true) ? strip_tags($nazwa_statusu['points_status_name']) : $nazwa_statusu['points_status_name']));
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie); 

    return $tablica;
  }  
  
  
  // funkcja wyswietlajaca status punktow
  public static function pokazNazweStatusuPunktow( $status_id, $jezyk = '1') {
  
    /*
    typy punktow
    1 - oczekujace
    2 - zatwierdzone
    3 - anulowane
    4 - wykorzystane
    */     

    $wynik = '';
    $zapytanie = "SELECT s.points_status_id, s.points_status_color, sd.points_status_name FROM customers_points_status s LEFT JOIN customers_points_status_description sd ON sd.points_status_id = s.points_status_id WHERE s.points_status_id = '".$status_id."' AND sd.language_id = '".$jezyk."'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($nazwa_statusu = $sql->fetch_assoc()) {
      $wynik = '<span style="color: #'.$nazwa_statusu['points_status_color'].'">'.$nazwa_statusu['points_status_name'].'</span>';
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);    

    return $wynik;
  }    
  
  // funkcja generujaca dodatkowe pola dla klientow
  static public function pokazDodatkowePolaKlientow($klient_id,$languages_id = '1' ) {
    global $i18n;

    $ciag_dodatkowych_pol ='';

    $dodatkowe_pola_klientow = "
      SELECT ce.fields_id, ce.fields_input_type, ce.fields_required_status, cei.fields_input_value, cei.fields_name, ce.fields_status, ce.fields_input_type, ce.fields_type 
        FROM customers_extra_fields ce, customers_extra_fields_info cei 
        WHERE ce.fields_status = '1' 
        AND cei.fields_id = ce.fields_id 
        AND cei.languages_id = '".$languages_id."'
        ORDER BY ce.fields_order";

    $sql = $GLOBALS['db']->open_query($dodatkowe_pola_klientow);

    if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0  ) {

      while ( $dodatkowePola = $sql->fetch_assoc() ) {

        $wartosc = '';

        if( isset($klient_id) && (int)$klient_id > 0 ) {

          $wartosc_query = "SELECT value FROM customers_to_extra_fields WHERE customers_id = '" . $klient_id . "' AND fields_id= '" . $dodatkowePola['fields_id'] . "'";

          $wartosc_info = $GLOBALS['db']->open_query($wartosc_query);
          $dodatkowePolaInfo = $wartosc_info->fetch_assoc();

          $wartosc_list = explode("\n", $dodatkowePolaInfo['value']);

          for($i = 0, $n = count($wartosc_list); $i < $n; $i++) {
            $wartosc_list[$i] = trim($wartosc_list[$i]);
          }
          $wartosc = $wartosc_list[0];

          $GLOBALS['db']->close_query($wartosc_info);

        }

        $ciag_dodatkowych_pol .= '<p>';
        $ciag_dodatkowych_pol .= '<span>' . $dodatkowePola['fields_name'] . ': ' . (($dodatkowePola['fields_required_status' ]== 1) ? '<em class="required"></em>': '') . '</span>';

        $wartosci_pola_lista = explode("\n", $dodatkowePola['fields_input_value']);
        $wartosci_pola_tablica = array();
        
        foreach($wartosci_pola_lista as $wartosc_pola) {
          $wartosc_pola = trim($wartosc_pola);
          $wartosci_pola_tablica[] = array('id' => $wartosc_pola, 'text' => $wartosc_pola);
        }

        switch($dodatkowePola['fields_input_type']) {
          // Pole typu INPUT
          case 0:
            if ( $dodatkowePola['fields_type'] == 'kalendarz' ) {
                 $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="'.htmlentities($wartosc, ENT_QUOTES, "UTF-8").'" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required datefields"': 'class="datefields"').' size="30" />';
               } else {
                 $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="'.htmlentities($wartosc, ENT_QUOTES, "UTF-8").'" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" style="width:95%" />';
            }
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
            break;

          // Pole typu TEXTAREA
          case 1:
            $ciag_dodatkowych_pol .= '<textarea name="fields_' . $dodatkowePola['fields_id'].'" cols="40" style="width:95%" rows="4" id="fields_'.$dodatkowePola['fields_id'].'" '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').'>'.htmlentities($wartosc, ENT_QUOTES, "UTF-8").'</textarea>';
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_WYMAGANE_POLE'] . '</label>';
            break;

          // Pole typu RADIO
          case 2:
            $cnt = 0;
            foreach($wartosci_pola_lista as $wartosc_pola) {
              $wartosc_pola = trim($wartosc_pola);
              $zaznaczone = ( $wartosc == $wartosc_pola ? 'checked="checked"' : '' );
              $ciag_dodatkowych_pol .= '<input type="radio" value="'.htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8").'" name="fields_' . $dodatkowePola['fields_id'].'" '.$zaznaczone. ' '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' /> ' . htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8") . '';

              $cnt++;
              if ( $cnt < count($wartosci_pola_lista) ) {
                $ciag_dodatkowych_pol .= '<br />';
              }
            }
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_JEDNA_OPCJE'] . '</label>';
            break;

          // Pole typu CHECKBOX
          case 3:
            $cnt = 0;
            foreach($wartosci_pola_lista as $wartosc_pola) {
              $wartosc_pola = trim($wartosc_pola);

              if ( isset($wartosc_list) && count($wartosc_list) > 0 ) {
                   $zaznaczone = ( in_array($wartosc_pola, $wartosc_list) ? 'checked="checked"' : '' );
                 } else {
                   $zaznaczone = '';
              }
              
              $ciag_dodatkowych_pol .= '<input type="checkbox"  value="'.htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8").'" name="fields_' . $dodatkowePola['fields_id'].'[]" ' . $zaznaczone . ' '.(($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' /> ' . htmlentities($wartosc_pola, ENT_QUOTES, "UTF-8");

              $cnt++;
              if ( $cnt < count($wartosci_pola_lista) ) {
                $ciag_dodatkowych_pol .= '<br />';
              }
            }
            $ciag_dodatkowych_pol .= '<label class="error" for="fields_' . $dodatkowePola['fields_id'].'[]" style="display:none">' . $GLOBALS['tlumacz']['BLAD_ZAZNACZ_OPCJE'] . '</label>';
            break;

          // Pole typu SELECT
          case 4:
              $ciag_dodatkowych_pol .= Funkcje::RozwijaneMenu('fields_' . $dodatkowePola['fields_id'], $wartosci_pola_tablica, $wartosc, ' style="width:80%"');
            break;

          default:
            $ciag_dodatkowych_pol .= '<input type="text" name="fields_'.$dodatkowePola['fields_id'].'" value="'.htmlentities($wartosc, ENT_QUOTES, "UTF-8").'" id="fields_' . $dodatkowePola['fields_id'] . '" ' . (($dodatkowePola['fields_required_status']==1) ? 'class="required"': '').' size="40" style="width:95%" />';
            break;
        }

        $ciag_dodatkowych_pol .= '</p>';
      }
      
       
    }
    $GLOBALS['db']->close_query($sql);
    
    unset($dodatkowe_pola_klientow, $dodatkowe_pola);        
    
    return $ciag_dodatkowych_pol;
  }  

} 

?>