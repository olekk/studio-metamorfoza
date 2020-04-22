<?php

class Kupony {

    public function Kupony( $kupon_kod ) {

        $this->id = $kupon_kod;
        $this->kupon = array();

        // ustalenie ilosci produktow i wartosci zamowienia
        $this->wartosc_zamowienia = 0;
        $this->ilosc_produktow = 0;
        
        $ZawartoscKoszyka = $GLOBALS['koszykKlienta']->ZawartoscKoszyka();
        
        $this->wartosc_zamowienia = $ZawartoscKoszyka['brutto_baza'];
        $this->ilosc_produktow = $ZawartoscKoszyka['ilosc_baza'];
        
        unset($ZawartoscKoszyka);        

        $this->DostepneKupony();

    }

    // funkcja zwraca w formie tablicy dane kuponu
    public function DostepneKupony() {

        $status = true;

        $data = date('Y-m-d');

        $zapytanie = "SELECT * FROM coupons
                               WHERE coupons_name = '" . $this->id . "' AND coupons_status = '1' AND
                               coupons_quantity > 0 AND 
                               ((('" . $data . "' >= coupons_date_start AND coupons_date_start != '0000-00-00') OR coupons_date_start = '0000-00-00') AND
                               ((coupons_date_end >= '" . $data . "' AND coupons_date_end != '0000-00-00') OR coupons_date_end = '0000-00-00'))";
      
        unset($data);

        $sql = $GLOBALS['db']->open_query($zapytanie);

        if ( (int)$GLOBALS['db']->ile_rekordow($sql) > 0 ) {

            $info = $sql->fetch_assoc();
            
            // ograniczenie tylko dla wybranej grupy klientow
            $grupaKlientowKuponu = false;
            
            if ( count(explode(',', $info['coupons_customers_groups_id'])) > 0 && $info['coupons_customers_groups_id'] != 0 ) {
                //
                if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' && isset($_SESSION['customers_groups_id']) && in_array($_SESSION['customers_groups_id'], explode(',', $info['coupons_customers_groups_id'])) ) {
                     $status = true;
                   } else {
                     $status = false;
                     $grupaKlientowKuponu = true;
                }
                //
            }

            // jezeli jest za mala ilosc produktow w koszyku
            if ( $info['coupons_min_quantity'] != '' && $info['coupons_min_quantity'] != '0' && $this->ilosc_produktow < $info['coupons_min_quantity'] ) {
                $status = false;
            }

            // jezeli jest za mala wartosc zamowienia w koszyku
            if ( $info['coupons_min_order'] != '' && $info['coupons_min_order'] != '0' && $this->wartosc_zamowienia < $GLOBALS['waluty']->PokazCeneBezSymbolu($info['coupons_min_order'],'',true) ) {
                $status = false;
            }       
            
            $warunekPromocji = false;            
            $warunekPomniejszeniaZamowienia = false;
            
            // dodatkowa tablica gdzie sa dodawane id produktow z promocji
            // zeby nie dublowac wykluczen jezeli produkt jest w promocji i np z niedozwolonej kategorii
            $idProduktowPromocji = array();
            
            // jezeli kupon ma wykluczenia promocji
            if ( $info['coupons_specials'] == '0' ) {
                 //
                 foreach ( $_SESSION['koszyk'] as $rekord ) {
                    //
                    $Produkt = new Produkt( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                    //      
                    // jezeli jest produkt promocyjny
                    if ( $Produkt->ikonki['promocja'] == '1' ) {
                         $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                         //
                         $warunekPromocji = true;
                         $warunekPomniejszeniaZamowienia = true;
                         //
                         $idProduktowPromocji[] = Funkcje::SamoIdProduktuBezCech( $rekord['id'] );
                         //
                    }  
                    //
                    unset($Produkt);
                    //
                 }
                 //
            }
            
            // ograniczenia tylko dla konkretnych kategorii, producentow i produktow
            if ( !empty($info['coupons_exclusion']) && !empty($info['coupons_exclusion_id']) ) {
                 //
                 foreach ( $_SESSION['koszyk'] as $rekord ) { 
                 
                    // jezeli jest tylko dla kategorii
                    if ( $info['coupons_exclusion'] == 'kategorie' ) {
                         //
                         // do jakich kategorii nalezy produkt
                         $tablica = Kategorie::ProduktKategorie( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                         //
                         $nalezyDoKategorii = false;
                         foreach ( $tablica as $id ) {
                            // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                            if ( in_array($id, explode(',', $info['coupons_exclusion_id']) ) ) {
                                 $nalezyDoKategorii = true;
                            }
                         }
                         //
                         // jezeli zadna z id katagorii nie nalezy do tablicy dozwolnych kategorii
                         // to obnizy wartosc zamowienia o wartosc produktu
                         if ( $nalezyDoKategorii == false && !in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $idProduktowPromocji) ) {
                              $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                              $warunekPomniejszeniaZamowienia = true;
                         }
                         //
                         unset($nalezyDoKategorii);
                    }
                    
                    // jezeli jest tylko dla producenta
                    if ( $info['coupons_exclusion'] == 'producenci' ) {
                         //
                         // do jakich producentow nalezy produkt
                         $id = Producenci::ProduktProducent( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ) );
                         //
                         $nalezyDoProducenta = false;
                         // sprawdza czy dane id nalezy do tablicy dozwolnych kategorii
                         if ( in_array($id, explode(',', $info['coupons_exclusion_id']) ) ) {
                             $nalezyDoProducenta = true;
                         }
                         //
                         // jezeli id producenta nie nalezy do tablicy dozwolnych producentow
                         // to obnizy wartosc zamowienia o wartosc produktu
                         if ( $nalezyDoProducenta == false && !in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $idProduktowPromocji) ) {
                              $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                              $warunekPomniejszeniaZamowienia = true;
                         }
                         //
                         unset($id, $nalezyDoProducenta);
                    }  

                    // jezeli jest tylko dla produktow
                    if ( $info['coupons_exclusion'] == 'produkty' ) {
                         //
                         $nalezyDoProduktow = false;
                         // sprawdza czy dane id nalezy do tablicy dozwolnych produktow
                         if ( in_array( Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), explode(',', $info['coupons_exclusion_id']) ) ) {
                             $nalezyDoProduktow = true;
                         }
                         //
                         // jezeli id produktu nie nalezy do tablicy dozwolnych produktow
                         // to obnizy wartosc zamowienia o wartosc produktu
                         if ( $nalezyDoProduktow == false && !in_array(Funkcje::SamoIdProduktuBezCech( $rekord['id'] ), $idProduktowPromocji) ) {
                              $this->wartosc_zamowienia -= $rekord['cena_brutto'] * $rekord['ilosc'];
                              $warunekPomniejszeniaZamowienia = true;
                         }
                         //
                         unset($nalezyDoProduktow);
                    }                      
                    
                 }
                 //                          
            }
            
            // po warunkach sprawdzi czy cos zostalo z kuponu
            if ( $this->wartosc_zamowienia <= 0 ) {
                 $status = false;
            }
            
            // obliczanie wartosci kuponu
            switch ($info['coupons_discount_type']) {
            
              case "fixed":
              
                  $wartoscKuponu = $GLOBALS['waluty']->PokazCeneBezSymbolu($info['coupons_discount_value'],'',true);
                  if ( $wartoscKuponu >= $this->wartosc_zamowienia ) {
                       $wartoscKuponu = $this->wartosc_zamowienia;
                  }
                  break;
                  
              case "percent":
              
                  $wartoscKuponu = round($this->wartosc_zamowienia * ( $info['coupons_discount_value'] / 100 ),2);
                  break;
                  
            }            

            $this->kupon = array('kupon_id' => $info['coupons_id'],
                                 'kupon_kod' => $this->id,
                                 'kupon_wartosc' => $wartoscKuponu,
                                 'kupon_status' => $status,
                                 'warunek_promocja' => $warunekPromocji,
                                 'mniejsza_wartosc' => $warunekPomniejszeniaZamowienia,
                                 'grupa_klientow' => $grupaKlientowKuponu
            );
            
            unset($wartoscKuponu, $warunekPromocji, $warunekPomniejszeniaZamowienia, $idProduktowPromocj, $grupaKlientowKuponu);

        }

        $GLOBALS['db']->close_query($sql);
        unset($zapytanie, $info);

    }

} 

?>