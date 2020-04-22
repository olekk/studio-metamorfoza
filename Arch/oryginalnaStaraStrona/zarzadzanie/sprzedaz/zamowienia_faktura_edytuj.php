<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      //aktualizacja rekordu w tablicy invoices
      $pola = array(
              array('invoices_nr',$filtr->process($_POST['faktura_numer'])),
              array('invoices_date_sell',date('Y-m-d', strtotime($filtr->process($_POST['data_sprzedazy'])))),
              array('invoices_date_generated',date('Y-m-d', strtotime($filtr->process($_POST['data_wystawienia'])))),
              array('invoices_date_modified','now()'),
              array('invoices_billing_name',$filtr->process($_POST['klient_nazwa'])),
              array('invoices_billing_company_name',$filtr->process($_POST['klient_firma'])),
              array('invoices_billing_nip',$filtr->process($_POST['klient_nip'])),
              array('invoices_billing_pesel',$filtr->process($_POST['klient_pesel'])),
              array('invoices_billing_street_address',$filtr->process($_POST['klient_ulica'])),
              array('invoices_billing_city',$filtr->process($_POST['klient_miasto'])),
              array('invoices_billing_postcode',$filtr->process($_POST['klient_kod_pocztowy'])),
              array('invoices_billing_country',$filtr->process($_POST['klient_panstwo'])),
              array('invoices_payment_type',$filtr->process($_POST['platnosc'])),
              array('invoices_payment_status', ( isset($_POST['rozliczona']) && $_POST['rozliczona'] == '1' ? $_POST['rozliczona'] : '0') ),
              array('invoices_date_payment',( isset($_POST['rozliczona']) && $_POST['rozliczona'] == '1' ? date('Y-m-d', strtotime($filtr->process($_POST['data_wystawienia']))) : date('Y-m-d', strtotime($filtr->process($_POST['data_platnosci']))) )),
              array('invoices_comments',$filtr->process($_POST['komentarz'])));

      $db->update_query('invoices' , $pola, " invoices_id = '".$filtr->process($_POST['id_faktury'])."'");
      unset($pola);

      /*
      //aktualizacja zapisow dotyczacych platnika w tablicy orders
      $pola = array(
              array('date_purchased',date('Y-m-d', strtotime($filtr->process($_POST['data_sprzedazy'])))),
              array('last_modified','now()'),
              array('billing_name',$filtr->process($_POST['klient_nazwa'])),
              array('billing_company',$filtr->process($_POST['klient_firma'])),
              array('billing_nip',$filtr->process($_POST['klient_nip'])),
              array('billing_pesel',$filtr->process($_POST['klient_pesel'])),
              array('billing_street_address',$filtr->process($_POST['klient_ulica'])),
              array('billing_city',$filtr->process($_POST['klient_miasto'])),
              array('billing_postcode',$filtr->process($_POST['klient_kod_pocztowy'])),
      );
      $db->update_query('orders' , $pola, " orders_id = '".$filtr->process($_POST['zamowienie_id'])."'");
      unset($pola);
      */

      foreach ( $_POST['produkt'] as $produkt ) {

        $pola = array(
                array('products_invoice_name',$filtr->process($produkt['nazwa'])),
                array('products_pkwiu',$filtr->process($produkt['pkwiu'])),
                array('products_quantity',$filtr->process($produkt['ilosc'])),
                array('products_price',$filtr->process($produkt['cena_netto']) - $filtr->process($produkt['cecha_cena_netto'])),
                array('products_price_tax',$filtr->process($produkt['cena_brutto']) - $filtr->process($produkt['cecha_cena_brutto'])),
                array('final_price',$filtr->process($produkt['cena_netto'])),
                array('final_price_tax',$filtr->process($produkt['cena_brutto'])));
                
        //
        $stawka_vat = explode('|', $produkt['vat']);
        $pola[] = array('products_tax',$stawka_vat[0]);
        $pola[] = array('products_tax_class_id',$stawka_vat[1]);   
        unset($stawka_vat);                
        //                 
                
        $db->update_query('orders_products' , $pola, " orders_products_id = '".$filtr->process($produkt['orders_products_id'])."' AND orders_id = '".$filtr->process($_POST['zamowienie_id'])."'");
        unset($pola);
        
        //wartosc produktow
        $wartosc_towarow_brutto = $wartosc_towarow_brutto + ( $produkt['cena_brutto'] * $produkt['ilosc']);
        
      }

      //aktualizacja wpisow w tablicy orders_total
      foreach ( $_POST['podsuma'] as $podsuma ) {
      
        $pola = array(
                array('title',$filtr->process($podsuma['nazwa'])),
                array('text',$waluty->FormatujCene($filtr->process($podsuma['wartosc_brutto']),false, $filtr->process($_POST['waluta_zamowienia']))),
                array('value',$filtr->process($podsuma['wartosc_brutto'])));
                
        if ( isset($podsuma['vat']) ) {
            //
            $stawka_vat = explode('|', $podsuma['vat']);
            $pola[] = array('tax',$stawka_vat[0]);
            $pola[] = array('tax_class_id',$stawka_vat[1]);   
            unset($stawka_vat);                
            //
        }                
                
        $db->update_query('orders_total' , $pola, " orders_total_id = '".$filtr->process($podsuma['orders_total_id'])."'");
        unset($pola);
        
      }

      $pola = array(
              array('text',$waluty->FormatujCene($_POST['total_brutto'],false, $filtr->process($_POST['waluta_zamowienia']))),
              array('value',$filtr->process($_POST['total_brutto'])));
              
      $db->update_query('orders_total' , $pola, " orders_id = '".(int)$_POST['zamowienie_id']."' AND class='ot_total'");
      unset($pola);

      $pola = array(
              array('text',$waluty->FormatujCene($wartosc_towarow_brutto,false, $filtr->process($_POST['waluta_zamowienia']))),
              array('value',$filtr->process($wartosc_towarow_brutto)));
              
      $db->update_query('orders_total' , $pola, " orders_id = '".(int)$_POST['zamowienie_id']."' AND class='ot_subtotal'");
      unset($pola);

      if ( isset($_POST['ot_loyalty_discount']) ) {
          $pola = array(
                  array('text',$waluty->FormatujCene($_POST['ot_loyalty_discount'],false, $filtr->process($_POST['waluta_zamowienia']))),
                  array('value',$filtr->process($_POST['ot_loyalty_discount'])));
                  
          $db->update_query('orders_total' , $pola, " orders_id = '".(int)$_POST['zamowienie_id']."' AND class='ot_loyalty_discount'");
          unset($pola);
      }

      if ( isset($_POST['ot_redemptions']) ) {
          $pola = array(
                  array('text',$waluty->FormatujCene($_POST['ot_redemptions'],false, $filtr->process($_POST['waluta_zamowienia']))),
                  array('value',$filtr->process($_POST['ot_redemptions'])));
                  
          $db->update_query('orders_total' , $pola, " orders_id = '".(int)$_POST['zamowienie_id']."' AND class='ot_redemptions'");
          unset($pola);
      }

      if ( isset($_POST['ot_discount_coupon']) ) {
          $pola = array(
                  array('text',$waluty->FormatujCene($_POST['ot_discount_coupon'],false, $filtr->process($_POST['waluta_zamowienia']))),
                  array('value',$filtr->process($_POST['ot_discount_coupon'])));
                  
          $db->update_query('orders_total' , $pola, " orders_id = '".(int)$_POST['zamowienie_id']."' AND class='ot_discount_coupon'");
          unset($pola);
      }

      if ( isset($_POST['total_rabat_brutto']) ) {
          $pola = array(
                  array('text',$waluty->FormatujCene($_POST['total_rabat_brutto'],false, $filtr->process($_POST['waluta_zamowienia']))),
                  array('value',$filtr->process($_POST['total_rabat_brutto'])));
                  
          $db->update_query('orders_total' , $pola, " orders_id = '".(int)$_POST['zamowienie_id']."' AND class='ot_total'");
          unset($pola);
      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["zamowienie_id"].'&zakladka='.$filtr->process($_POST["zakladka"]).'');

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    $tablica_jm          = array();
    $tablica_jm          = Produkty::TablicaJednostekMiaryProduktow();
    $tablica_vat         = array();
    $domyslny_vat        = '';

    $stawki_tablica       = Array();
    $podsumowanie_tablica = Array();

    $sql_tmp = $db->open_query("select * from tax_rates order by sort_order");
    while ($stawki_vat = $sql_tmp->fetch_assoc()) {
    
        $stawki_tablica[] = $stawki_vat['tax_rate'] . '|' . $stawki_vat['tax_rates_id'];
        $tablica_vat[] = array('id' => $stawki_vat['tax_rate'] . '|' . $stawki_vat['tax_rates_id'], 'text' => $stawki_vat['tax_short_description']);

        if ( $stawki_vat['tax_default'] == '1' ) {
          $domyslny_vat = $stawki_vat['tax_rate'].'|'.$stawki_vat['tax_short_description'];
        }
        
    }
    $db->close_query($sql_tmp);
    ?>
    
    <div id="naglowek_cont">Faktura</div>

    <?php
    $kopia_proformy = false;
    if ( isset($_GET['proforma_id']) && $_GET['proforma_id'] != '' ) {
      $_GET['id'] = $_GET['proforma_id'];
      $kopia_proformy = true;
      $numer_faktury = Sprzedaz::WygenerujNumerFaktury($_GET['typ']); 
    }
    ?>

    <script type="text/javascript" src="javascript/faktura.js"></script>
    <script type="text/javascript">
    //<![CDATA[
    <?php include('faktura.js.php'); ?>
    //]]>
    </script>
    
    <div id="cont">

      <?php
    
      if ( !isset($_GET['id']) ) {
         $_GET['id'] = 0;
      }    
            
      $zapytanie = "SELECT * FROM invoices WHERE invoices_id  = '" . $filtr->process($_GET['id']) . "'";
      $sql = $db->open_query($zapytanie);
            
      if ((int)$db->ile_rekordow($sql) > 0) {

        $zamowienie = new Zamowienie((int)$_GET['id_poz']);

        $info = $sql->fetch_assoc();
        ?>
        
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function() {

          $("#fakturaForm").validate({
            rules: {
              faktura_numer: {required: true, remote: "ajax/sprawdz_numer_faktury.php?typ=2&id=<?php echo $filtr->process((int)$_GET['id']); ?>"},
              <?php if ( trim($info['invoices_billing_company_name']) != '' ) { ?>
              klient_firma: {required: true},
              <?php } else { ?>
              klient_nazwa: {required: true},
              <?php } ?>
              klient_ulica: {required: true},
              klient_miasto: {required: true},
              klient_kod_pocztowy: {required: true}
            },
            messages: {
              faktura_numer: {required: "Pole jest wymagane", remote: "Taki numer faktury już istnieje"}
            }
          });

          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: true
          });
          
        });
        //]]>
        </script>            
            
        <form action="sprzedaz/zamowienia_faktura_edytuj.php" method="post" id="fakturaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Faktura do zamówienia nr: <?php echo $_GET['id_poz']; ?></div>
                
            <div class="pozycja_edytowana">
              <div class="info_content">
                    
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="zamowienie_id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                <input type="hidden" name="zakladka" value="<?php echo $filtr->process((int)$_GET['zakladka']); ?>" />
                <input type="hidden" name="rodzaj_faktury" value="<?php echo $filtr->process((int)$_GET['typ']); ?>" />
                <input type="hidden" name="id_faktury" value="<?php echo $filtr->process((int)$_GET['id']); ?>" />

                <p>
                    <label class="required">Numer faktury:</label>
                    <input type="text" name="faktura_numer" id="faktura_numer" size="10" value="<?php echo ( $kopia_proformy ? $numer_faktury : $info['invoices_nr'] ); ?>" /> <span class="RokFaktury">/<?php echo date('Y', strtotime($info['invoices_date_generated'])); ?></span>
                    <label style="display:none" class="error" for="faktura_numer" generated="true"></label>
                </p> 

                <p>
                    <label>Data sprzedaży:</label>
                    <input type="text" name="data_sprzedazy" id="data_sprzedazy" size="20" value="<?php echo date('d-m-Y', strtotime($info['invoices_date_sell'])); ?>" class="datepicker" />
                </p> 

                <p>
                    <label>Data wystawienia:</label>
                    <input type="text" name="data_wystawienia" id="data_wystawienia" size="20" value="<?php echo ( $kopia_proformy ? date("d-m-Y") : date('d-m-Y', strtotime($info['invoices_date_generated'])) ); ?>" class="datepicker" />
                </p> 
                
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                <p>
                    <label class="required">Nabywca:</label>
                    <?php if ( trim($info['invoices_billing_company_name']) != '' ) { ?>
                        <input type="text" name="klient_firma" id="klient_firma" size="120" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_company_name']); ?>" />
                        <input type="hidden" name="klient_nazwa" value="" />
                    <?php } else { ?>
                        <input type="text" name="klient_nazwa" id="klient_nazwa" size="120" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_name']); ?>" />
                        <input type="hidden" name="klient_firma" value="" />
                    <?php } ?>
                </p> 

                <p>
                    <label>NIP:</label>
                    <input type="text" name="klient_nip" id="klient_nip" size="50" value="<?php echo $info['invoices_billing_nip']; ?>" />
                    <label style="padding-left:20px;width:45px;">PESEL:</label>
                    <input type="text" name="klient_pesel" id="pesel" size="50" value="<?php echo $info['invoices_billing_pesel']; ?>" />
                </p> 

                <p>
                    <label class="required">Adres:</label>
                    <input type="text" name="klient_ulica" id="klient_ulica" size="120" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_street_address']); ?>" />
                </p> 

                <p>
                    <label class="required">Miejscowość:</label>
                    <input type="text" name="klient_miasto" id="klient_miasto" size="80" value="<?php echo Funkcje::formatujTekstInput($info['invoices_billing_city']); ?>" />
                </p> 

                <p>
                    <label class="required">Kod pocztowy:</label>
                    <input type="text" name="klient_kod_pocztowy" id="klient_kod_pocztowy" size="53" value="<?php echo $info['invoices_billing_postcode']; ?>" />
                </p> 
                
                <p>
                    <label>Kraj:</label>
                    <input type="text" name="klient_panstwo" id="klient_panstwo" size="53" value="<?php echo $info['invoices_billing_country']; ?>" />
                </p> 
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:98%;" />

                <?php
                $tablica = array();
                $tablica = Sprzedaz::TablicaTypowPlatnosci();
                ?>
                <p>
                    <label>Rodzaj płatności:</label>
                    <?php echo Funkcje::RozwijaneMenu('platnosc', $tablica, $info['invoices_payment_type']); ?>
                </p> 

                <p>
                    <label>Zapłacona:</label>
                    <input type="radio" value="1" name="rozliczona" <?php echo ( $info['invoices_payment_status'] == '1' ? 'checked="checked"' : '' ); ?> onclick="$('#data').slideUp()" /> tak
                    <input type="radio" value="0" name="rozliczona" <?php echo ( $info['invoices_payment_status'] == '0' ? 'checked="checked"' : '' ); ?> onclick="$('#data').slideDown()" /> nie
                </p> 

                <p id="data" <?php echo ($info['invoices_payment_status'] == '1' ? 'style="display:none;"' : '' ); ?>>
                    <label>Data płatności:</label>
                    <input type="text" name="data_platnosci" id="data_platnosci" size="20" value="<?php echo date('d-m-Y', ($info['invoices_date_payment'] == '0000-00-00 00:00:00' ? time() : strtotime($info['invoices_date_payment']) )); ?>" class="datepicker" />
                </p> 

              </div>

              <div style="padding:10px;">
                <table cellpadding="0" cellspacing="0" style="width:100%" id="items">
                  <tr>
                      <td class="faktura_naglowek">Nazwa</td>
                      <td class="faktura_naglowek">PKWIU</td>
                      <td class="faktura_naglowek">j.m.</td>
                      <td class="faktura_naglowek">Ilość</td>
                      <td class="faktura_naglowek">Cena jedn. brutto</td>
                      <td class="faktura_naglowek">Cena jedn. netto</td>
                      <td class="faktura_naglowek">Wartość netto</td>
                      <td class="faktura_naglowek">Stawka VAT</td>
                      <td class="faktura_naglowek">Wartość VAT</td>
                      <td class="faktura_naglowek">Wartość brutto</td>
                  </tr>
                  <?php

                  $podsumowanie_tablica['razem'] = array('razem_wartosc_brutto' => 0,
                                                         'razem_wartosc_netto' => 0,
                                                         'razem_wartosc_vat' => 0);

                  for ( $x = 0, $cnt = count($stawki_tablica); $x < $cnt; $x++ ) {
                  
                    $podsumowanie_tablica[$stawki_tablica[$x]] = array('razem_wartosc_brutto' => 0,
                                                                       'razem_wartosc_netto' => 0,
                                                                       'razem_wartosc_vat' => 0);
                                                                       
                  }

                  $i = 1;
                  $lp = 1;
                  
                  $domyslna_jm = Funkcje::domyslnaJednostkaMiary();

                  foreach ( $zamowienie->produkty as $produkt ) {

                    $ilosc                = $produkt['ilosc'];
                    $cena_brutto          = $produkt['cena_koncowa_brutto'];
                    $cena_netto           = $produkt['cena_koncowa_netto'];
                    $vat                  = $produkt['tax'];
                    $vat_id               = $produkt['tax_id'];
                    $vat_info             = $produkt['tax_info'];                        
                    $wartosc_brutto       = $waluty->FormatujCeneBezSymbolu($cena_brutto * $ilosc);
                    $wartosc_vat          = $waluty->FormatujCeneBezSymbolu($wartosc_brutto * ( $vat / ( 100 + $vat ) ));
                    $wartosc_netto        = $waluty->FormatujCeneBezSymbolu($wartosc_brutto - $wartosc_vat);

                    $podsumowanie_tablica['razem'] = array('razem_wartosc_brutto' => $podsumowanie_tablica['razem']['razem_wartosc_brutto']+$wartosc_brutto,
                                                           'razem_wartosc_netto' => $podsumowanie_tablica['razem']['razem_wartosc_netto']+$wartosc_netto,
                                                           'razem_wartosc_vat' => $podsumowanie_tablica['razem']['razem_wartosc_vat']+$wartosc_vat);

                    $szczegoly = $produkt['nazwa']."\n";

                    $wyswietl_cechy = '';
                    $wartosc_netto_cechy = 0;
                    $wartosc_brutto_cechy = 0;
                    $wartosc_vat_cechy = 0;
                    
                    if (isset($produkt['attributes']) && (count($produkt['attributes']) > 0)) {
                    
                      foreach ($produkt['attributes'] as $cecha ) {
                      
                        $wartosc_netto_cechy = $wartosc_netto_cechy + $cecha['cena_netto'];
                        $wartosc_brutto_cechy = $wartosc_brutto_cechy + $cecha['cena_brutto'];
                        $wartosc_vat_cechy = $wartosc_vat_cechy + $cecha['podatek'];
                        if ( FAKTURA_NAZWA_CECHY == 'tak' ) {
                          $wyswietl_cechy .= '- ' . $cecha['cecha'] . ':' . $cecha['wartosc'] . "\n";
                        }
                        
                      }
                      
                    }

                    if ( FAKTURA_NAZWA_NUMER_KATALOGOWY == 'tak' ) {
                      if (trim($produkt['model']) != '') {
                        $szczegoly .= '- ' . $produkt['model']."\n";
                      }
                    }

                    if ( FAKTURA_NAZWA_PRODUCENT == 'tak' ) {
                      if (trim($produkt['producent']) != '') {                     
                        $szczegoly .= '- ' . $produkt['producent']."\n";
                      }
                    }
                    // wyswietlenie cech produktu
                    if (!empty($wyswietl_cechy)) {                     
                      $szczegoly .= $wyswietl_cechy;
                    }
                    
                    // jezeli byla zapisana nazwa faktury
                    if ( trim($produkt['nazwa_faktura']) != '' ) {
                    
                        $szczegoly = $produkt['nazwa_faktura']."\n";

                    }

                    echo '<tr class="item-row">';
                    echo '<td class="faktura_produkt">
                          <input type="hidden" value="'.$lp.'" name="produkt['.$produkt['orders_products_id'].'][lp]" class="lp" />
                          <input type="hidden" value="'.$produkt['orders_products_id'].'" name="produkt['.$produkt['orders_products_id'].'][orders_products_id]" />
                          <input type="hidden" value="'.$wartosc_netto_cechy.'" name="produkt['.$produkt['orders_products_id'].'][cecha_cena_netto]" />
                          <input type="hidden" value="'.$wartosc_brutto_cechy.'" name="produkt['.$produkt['orders_products_id'].'][cecha_cena_brutto]" />
                          <input type="hidden" value="'.$wartosc_vat_cechy.'" name="produkt['.$produkt['orders_products_id'].'][cecha_podatek]" />';
                          
                    echo '<textarea cols="30" rows="3" name="produkt['.$produkt['orders_products_id'].'][nazwa]">'.trim($szczegoly).'</textarea></td>';
                    echo '<td class="faktura_produkt"><input type="text" value="'.$produkt['pkwiu'].'" size="6" name="produkt['.$produkt['orders_products_id'].'][pkwiu]" class="pkwiu" style="text-align:right;" /></td>';
                    echo '<td class="faktura_produkt">';
                    echo Funkcje::RozwijaneMenu('jm[]', $tablica_jm, ( $produkt['jm'] != '0' ? $produkt['jm'] : $domyslna_jm ) );
                    echo '</td>';
                    echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$ilosc.'" size="5" name="produkt['.$produkt['orders_products_id'].'][ilosc]" class="ilosc" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td>';
                    echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$cena_brutto.'" size="6" name="produkt['.$produkt['orders_products_id'].'][cena_brutto]" class="cena_brutto" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td>';
                    echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$cena_netto.'" size="6" name="produkt['.$produkt['orders_products_id'].'][cena_netto]" class="cena_netto" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td>';
                    echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$wartosc_netto.'" size="8" name="produkt['.$produkt['orders_products_id'].'][wartosc_netto]" class="wartosc_netto readonly" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" readonly="readonly" /></td>';
                    echo '<td class="faktura_produkt" style="text-align:right">';
                    
                    echo Funkcje::RozwijaneMenu('produkt['.$produkt['orders_products_id'].'][vat]', $tablica_vat, round($vat,0) . '|' . $vat_id, 'class="vat"');
                    
                    echo '</td>';
                    echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$wartosc_vat.'" size="10" name="produkt['.$produkt['orders_products_id'].'][wartosc_vat]" class="wartosc_vat readonly" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" readonly="readonly" /></td>';
                    echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$wartosc_brutto.'" size="10" name="produkt['.$produkt['orders_products_id'].'][wartosc_brutto]" class="wartosc_brutto readonly" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" readonly="readonly" /></td>';

                    echo '</tr>';

                    for ( $x = 0, $cnt = count($stawki_tablica); $x < $cnt; $x++ ) {
                    
                      if ( round($vat,0) . '|' . $vat_id == $stawki_tablica[$x] ) {
                      
                        $podsumowanie_tablica[$stawki_tablica[$x]] = array('razem_wartosc_brutto' => $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_brutto']+$wartosc_brutto,
                                                                           'razem_wartosc_netto' => $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_netto']+$wartosc_netto,
                                                                           'razem_wartosc_vat' => $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_vat']+$wartosc_vat);
                                                    
                        }
                        
                    }

                    $i++;
                    $lp++;
                  }

                  unset($domyslna_jm);

                  $dostawa_cena_brutto = 0;
                  $dostawa_cena_netto  = 0;
                  $dostawa_nazwa = 'Dostawa';

                  foreach ( $zamowienie->podsumowanie as $dodatki ) {

                    if ( $dodatki['klasa'] != 'ot_subtotal' && $dodatki['prefix'] != '9' && $dodatki['prefix'] != '0' ) {

                      $ilosc                = '1';
                      $cena_brutto          = $dodatki['wartosc'];
                      $vat                  = $dodatki['vat_stawka'];
                      $vat_id               = $dodatki['vat_id'];
                      $cena_netto           = $waluty->FormatujCeneBezSymbolu($dodatki['wartosc'] - ($dodatki['wartosc'] * ( $vat / ( 100 + $vat ) )));
                      $wartosc_brutto       = $waluty->FormatujCeneBezSymbolu($cena_brutto * $ilosc);
                      $wartosc_vat          = $waluty->FormatujCeneBezSymbolu($wartosc_brutto * ( $vat / ( 100 + $vat ) ));
                      $wartosc_netto        = $waluty->FormatujCeneBezSymbolu($wartosc_brutto - $wartosc_vat);

                      $podsumowanie_tablica['razem'] = array('razem_wartosc_brutto' => $podsumowanie_tablica['razem']['razem_wartosc_brutto']+$wartosc_brutto,
                                                             'razem_wartosc_netto' => $podsumowanie_tablica['razem']['razem_wartosc_netto']+$wartosc_netto,
                                                             'razem_wartosc_vat' => $podsumowanie_tablica['razem']['razem_wartosc_vat']+$wartosc_vat);
                      
                      for ( $x = 0, $cnt = count($stawki_tablica); $x < $cnt; $x++ ) {
                      
                        if ( $vat_id == substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1) ) {
                        
                            $podsumowanie_tablica[$stawki_tablica[$x]] = array('razem_wartosc_brutto' => $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_brutto']+$wartosc_brutto,
                                                                               'razem_wartosc_netto' => $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_netto']+$wartosc_netto,
                                                                               'razem_wartosc_vat' => $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_vat']+$wartosc_vat); 
                        }
                        
                      }

                      if ($dodatki['klasa'] != 'ot_shipping' && $dodatki['klasa'] != 'ot_payment' ) {

                          echo '<tr class="item-row">';
                          echo '<td class="faktura_produkt"><input type="hidden" value="'.$lp.'" name="podsuma['.$dodatki['orders_total_id'].'][lp]" class="lp" /><input type="hidden" value="'.$dodatki['orders_total_id'].'" name="podsuma['.$dodatki['orders_total_id'].'][orders_total_id]" />';
                          echo '<textarea cols="30" rows="3" name="podsuma['.$dodatki['orders_total_id'].'][nazwa]">'.$dodatki['tytul'].'</textarea></td>';
                          echo '<td class="faktura_produkt"><input type="text" value="" size="6" name="podsuma['.$dodatki['orders_total_id'].'][pkwiu]" class="pkwiu readonly" style="text-align:right;" disabled="disabled" /></td>';
                          echo '<td class="faktura_produkt">';
                          echo Funkcje::RozwijaneMenu('jm[]', $tablica_jm, '4', ' class="readonly" disabled="disabled"');
                          echo '</td>';
                          echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$ilosc.'" size="5" name="podsuma['.$dodatki['orders_total_id'].'][ilosc]" class="ilosc readonly" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" disabled="disabled" /></td>';
                          echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$cena_brutto.'" size="6" name="podsuma['.$dodatki['orders_total_id'].'][cena_brutto]" class="cena_brutto" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td>';
                          echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$cena_netto.'" size="6" name="podsuma['.$dodatki['orders_total_id'].'][cena_netto]" class="cena_netto" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td>';
                          echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$wartosc_netto.'" size="8" name="podsuma['.$dodatki['orders_total_id'].'][wartosc_netto]" class="wartosc_netto readonly" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" readonly="readonly" /></td>';
                          echo '<td class="faktura_produkt" style="text-align:right">';
                          
                          echo Funkcje::RozwijaneMenu('podsuma[' . $dodatki['orders_total_id'] . '][vat]', $tablica_vat, round($vat,0) . '|' . $vat_id, 'class="vat"');
                            
                          echo '</td>';
                          echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$wartosc_vat.'" size="10" name="podsuma['.$dodatki['orders_total_id'].'][wartosc_vat]" class="wartosc_vat readonly" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" readonly="readonly" /></td>';
                          echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$wartosc_brutto.'" size="10" name="podsuma['.$dodatki['orders_total_id'].'][wartosc_brutto]" class="wartosc_brutto readonly" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" readonly="readonly" /></td>';

                          echo '</tr>';
                          
                        } else {
                        
                          if ( $dodatki['klasa'] ==  'ot_shipping' ) {
                               $dostawa_id = $dodatki['orders_total_id'];
                               $dostawa_nazwa = $dodatki['tytul'];
                               $dostawa_vat_id = $dodatki['vat_id'];
                               $dostawa_vat_stawka = $dodatki['vat_stawka']; 
                               // sama wartosc dostawy - bez platnosci
                               $dostawa_tylko_cena_brutto = $dodatki['wartosc'];
                          }
                          $dostawa_cena_brutto += $dodatki['wartosc'];
                        }
                        
                        $lp++;
                        
                    }
                    
                  }
 
                  if ( $dostawa_cena_brutto > 0 ) {
                  
                      $dostawa_cena_netto           = $waluty->FormatujCeneBezSymbolu($dostawa_cena_brutto - ($dostawa_cena_brutto * ( $dostawa_vat_stawka / ( 100 + $dostawa_vat_stawka ) )));
                      $dostawa_wartosc_brutto       = $waluty->FormatujCeneBezSymbolu($dostawa_cena_brutto * 1);
                      $dostawa_wartosc_vat          = $waluty->FormatujCeneBezSymbolu($dostawa_wartosc_brutto * ( $dostawa_vat_stawka / ( 100 + $dostawa_vat_stawka ) ));
                      $dostawa_wartosc_netto        = $waluty->FormatujCeneBezSymbolu($dostawa_wartosc_brutto - $dostawa_wartosc_vat);

                      echo '<tr class="item-row">';
                      echo '<td class="faktura_produkt"><input type="hidden" value="'.$lp.'" name="podsuma['.$dostawa_id.'][lp]" class="lp" /><input type="hidden" value="'.$dostawa_id.'" name="podsuma['.$dostawa_id.'][orders_total_id]" />';
                      echo '<textarea cols="30" rows="3" name="podsuma['.$dostawa_id.'][nazwa]">'.$dostawa_nazwa.'</textarea>';
                      echo '<span class="maleInfo">Koszt wysyłki jest sumą łączną kosztu wysyłki i płatności<span></td>';
                      echo '<td class="faktura_produkt"><input type="text" value="" size="6" name="podsuma['.$dostawa_id.'][pkwiu]" class="pkwiu readonly" style="text-align:right;" disabled="disabled" /></td>';
                      echo '<td class="faktura_produkt">';
                      echo Funkcje::RozwijaneMenu('jm[]', $tablica_jm, '4', ' class="readonly" disabled="disabled"');
                      echo '</td>';
                      echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="1" size="5" name="podsuma['.$dostawa_id.'][ilosc]" class="ilosc readonly" style="text-align:right;" disabled="disabled" /></td>';
                      echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$waluty->FormatujCeneBezSymbolu($dostawa_cena_brutto).'" size="6" name="podsuma['.$dostawa_id.'][cena_brutto]" class="cena_brutto readonly" style="text-align:right;" disabled="disabled" /></td>';
                      echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$dostawa_cena_netto.'" size="6" name="podsuma['.$dostawa_id.'][cena_netto]" class="cena_netto readonly" style="text-align:right;" disabled="disabled" /></td>';
                      echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$dostawa_wartosc_netto.'" size="8" name="podsuma['.$dostawa_id.'][wartosc_netto]" class="wartosc_netto readonly" style="text-align:right;" disabled="disabled" /></td>';
                      echo '<td class="faktura_produkt" style="text-align:right">';
                              
                      echo Funkcje::RozwijaneMenu('podsuma['.$dostawa_id.'][vat]', $tablica_vat, round($dostawa_vat_stawka,0) . '|' . $dostawa_vat_id, ' class="vat readonly" disabled="disabled"');
                                
                      echo '</td>';
                      echo '<td class="faktura_produkt" style="text-align:right"><input type="text" value="'.$dostawa_wartosc_vat.'" size="10" name="podsuma['.$dostawa_id.'][wartosc_vat]" class="wartosc_vat readonly" style="text-align:right;" disabled="disabled" /></td>';
                      echo '<td class="faktura_produkt" style="text-align:right">
                                <input type="text" value="'.$dostawa_wartosc_brutto.'" size="10" name="podsuma['.$dostawa_id.'][wartosc_brutto_suma]" class="wartosc_brutto readonly" style="text-align:right;" disabled="disabled" />
                                <input type="hidden" value="'.$dostawa_tylko_cena_brutto.'" name="podsuma['.$dostawa_id.'][wartosc_brutto]" />
                            </td>';

                      echo '</tr>';
                      
                      unset($dostawa_vat_id, $dostawa_vat_stawka);
                      
                  }

                  ?>
                  <tr id="razem">
                      <td colspan="6" class="blank"> </td>
                      <td class="faktura_produkt" style="text-align:center">RAZEM</td>
                      <td class="faktura_produkt" style="text-align:center">X</td>
                      <td class="faktura_produkt" style="text-align:right"></td>
                      <td class="faktura_produkt" style="text-align:right"></td>
                  </tr>

                  <?php
                  for ( $x = 0, $cnt = count($stawki_tablica); $x < $cnt; $x++ ) {

                    $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_vat'] = round($podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_brutto'] * ( substr($stawki_tablica[$x],0,strpos($stawki_tablica[$x], '|')) / ( 100 + substr($stawki_tablica[$x],0,strpos($stawki_tablica[$x], '|')) ) ),2);
                    $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_netto'] = $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_brutto'] - $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_vat'];

                    ?>
                    <tr id="razem<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" <?php echo ( $podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_netto'] == 0 ? 'style="display:none;"' : '' ); ?>>
                        <td colspan="6" class="blank"> </td>
                        <td class="faktura_produkt" style="text-align:right"><input type="text" value="<?php echo $waluty->FormatujCeneBezSymbolu($podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_netto']); ?>" size="10" name="subtotal_netto_vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" id="subtotal_netto_vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" style="text-align:right;" /></td>
                        
                        <?php
                        $wyswietl_stawka = '';
                        foreach ( $tablica_vat as $stawka ) {
                            //
                            if ( $stawki_tablica[$x] == $stawka['id'] ) {
                                 $wyswietl_stawka = $stawka['text'];
                            }
                            //
                        }
                        ?>
                      
                        <td class="faktura_produkt" style="text-align:right"><input type="text" value="<?php echo $wyswietl_stawka; ?>" size="5" name="vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" id="vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" style="text-align:right;" /></td>
                        <td class="faktura_produkt" style="text-align:right"><input type="text" value="<?php echo $waluty->FormatujCeneBezSymbolu($podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_vat']); ?>" size="10" name="subtotal_vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" id="subtotal_vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" style="text-align:right;" /></td>
                        <td class="faktura_produkt" style="text-align:right"><input type="text" value="<?php echo $waluty->FormatujCeneBezSymbolu($podsumowanie_tablica[$stawki_tablica[$x]]['razem_wartosc_brutto']); ?>" size="10" name="subtotal_brutto_vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" id="subtotal_brutto_vat<?php echo substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1); ?>" style="text-align:right;" /></td>
                        
                        <?php
                        unset($wyswietl_stawka);
                        ?>                        
                    </tr>
                  <?php } ?>

                  <?php
                  $WartoscRabatu = 0;
                  
                  foreach ( $zamowienie->podsumowanie as $dodatki ) {

                      if ( $dodatki['prefix'] == '0' ) {

                          $vatDomyslny          = Funkcje::domyslnyPodatekVat();
                          $rabat_vat            = $vatDomyslny['stawka'];
                          unset($vatDomyslny);          
                          
                          $rabat_ilosc          = '1.00';
                          $rabat_cena_brutto    = $dodatki['wartosc'];
                          $rabat_cena_netto     = $waluty->FormatujCeneBezSymbolu($dodatki['wartosc'] - ($dodatki['wartosc'] * ( $rabat_vat / ( 100 + $rabat_vat ) )));
                          
                          $rabat_wartosc_brutto = $waluty->FormatujCeneBezSymbolu($rabat_cena_brutto * $rabat_ilosc);
                          $rabat_wartosc_vat    = $waluty->FormatujCeneBezSymbolu($rabat_wartosc_brutto * ( $rabat_vat / ( 100 + $rabat_vat ) ));
                          $rabat_wartosc_netto  = $waluty->FormatujCeneBezSymbolu($rabat_wartosc_brutto - $rabat_wartosc_vat);

                          $WartoscRabatu += $rabat_wartosc_brutto;

                          echo '<tr>
                            <td style="padding:4px;text-align:right" colspan="9">'.$dodatki['tytul'].'</td>
                            <td style="text-align:right;padding:4px;"><input type="text" name="'.$dodatki['klasa'].'" id="'.$dodatki['klasa'].'" value="'.$waluty->FormatujCeneBezSymbolu($rabat_wartosc_brutto).'" size="10" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2);aktualizuj_calosc()" /></td></tr>';
                            
                      }
                  }
                  
                  $kwotaDoZaplaty = $podsumowanie_tablica['razem']['razem_wartosc_brutto'] - $WartoscRabatu;

                  echo '<tr><td style="padding:4px;text-align:right" colspan="9">Do zapłaty</td><td style="padding:4px;text-align:right"><input type="text" value="'.$waluty->FormatujCeneBezSymbolu($kwotaDoZaplaty).'" size="10" style="text-align:right;" name="total_rabat_brutto" id="total_rabat_brutto" /></td></tr>';
                  ?>

                </table>
              </div>

              <div class="info_content">

                <p>
                    <label>Komentarz:</label>
                    <textarea cols="70" style="width:98%" rows="5" name="komentarz" id="komentarz"><?php echo $info['invoices_comments'] ?></textarea>
                </p> 

              </div>

            </div>

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>','sprzedaz');">Powrót</button>           
            </div>
            
          </div>

        </form>

        <?php

      } else {

        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

      }
      $db->close_query($sql);
      unset($zapytanie, $info);

      ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}

?>