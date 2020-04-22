<?php
if ( isset($toks) ) {
?>

    <div id="zakl_id_0" style="display:none;" class="pozycja_edytowana">
    
        <div class="obramowanie_tabeli" style="margin-top:10px;">
        
          <table class="listing_tbl list_poj" id="infoTblPodsumowanie">
          
            <tr class="div_naglowek">
              <td colspan="2">
              <div class="lf">Podsumowanie</div>
              <div class="LinEdytuj"><a href="sprzedaz/zamowienia_podsumowanie_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=0">edytuj</a></div>
              </td>
            </tr>
            
            <?php
            for ($i = 0, $n = count($zamowienie->podsumowanie); $i < $n; $i++) {
              ?>
              <tr>
                <td class="InfoSpan"><?php echo $zamowienie->podsumowanie[$i]['tytul']; ?></td>
                <td style="text-align:right; width:20%">
                    <?php 
                    if ( $zamowienie->podsumowanie[$i]['prefix'] == '0' ) {
                        echo '<span style="color:red">';
                    }
                    echo $waluty->FormatujCene($zamowienie->podsumowanie[$i]['wartosc'], false, $zamowienie->info['waluta']);
                    if ( $zamowienie->podsumowanie[$i]['prefix'] == '0' ) {
                        echo '</span>';
                    } 
                    ?>
                </td>
              </tr>
            <?php } ?>
            
          </table>
          
        </div>

        <br />

        <div class="obramowanie_tabeli">
        
          <table class="listing_tbl" id="infoTbl">
            <tr>
              <td style="width:30%">Data zamówienia:</td><td><?php echo date('d-m-Y H:i:s', strtotime($zamowienie->info['data_zamowienia'])); ?></td>
            </tr>
            <tr>
              <td>Data ostatniej modyfikacji:</td><td><?php echo date('d-m-Y H:i:s', strtotime($zamowienie->info['data_modyfikacji'])); ?></td>
            </tr>
            <tr>
              <?php
              //utworzenie tablicy parametrow
              $lista_dok = Array();
              $lista_dok['0'] = 'Paragon';
              $lista_dok['1'] = 'Faktura';
              ?>
              <td>Dokument sprzedaży:</td>
              <td class="InfoNormal"><span class="editSelDokument" id="invoice_dokument"><strong><?php echo ( $zamowienie->info['dokument_zakupu'] == '1' ? 'Faktura' : 'Paragon' ); ?></strong></span>
              <?php echo "<span class=\"edit_dokument\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" class=\"toolTipTop\" onclick=\"edytuj_dokument('Dokument','".str_replace('"','%22',json_encode($lista_dok))."')\" /></span>"; ?>
              </td>
            </tr>
            <tr>
              <td>Klient:</td><td class="InfoNormal"><span class="InfoBold" id="customers_name"><?php echo $zamowienie->klient['nazwa']; ?></span>
              <?php echo "<span class=\"edit_pole\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" class=\"toolTipTop\" onclick=\"edytuj_pole('customers_name','text')\" /></span>"; ?>
              </td>
            </tr>
            <tr>
              <td>Adres e-mail:</td><td class="InfoNormal"><span class="InfoBold" id="customers_email_address"><?php echo $zamowienie->klient['adres_email']; ?></span>
              <?php echo "<span class=\"edit_pole\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" class=\"toolTipTop\" onclick=\"edytuj_pole('customers_email_address','text')\" /></span>"; ?>
              </td>
            </tr>
            <tr>
              <td>Telefon:</td><td class="InfoNormal"><span class="InfoBold" id="customers_telephone"><?php echo $zamowienie->klient['telefon']; ?></span>
              <?php echo "<span class=\"edit_pole\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" class=\"toolTipTop\" onclick=\"edytuj_pole('customers_telephone','text')\" /></span>"; ?>
              </td>
            </tr>
            <tr>
              <td>Adres IP klienta:</td><td class="InfoNormal"><?php echo wordwrap($zamowienie->info['adres_ip'], 110, "<br />", 1); ?></td>
            </tr>
            <tr>
              <td>Skąd trafił klient:</td><td class="InfoNormal"><?php echo wordwrap($zamowienie->info['referer'], 110, "<br />", 1); ?></td>
            </tr>
            <tr>
              <td>Uwagi klienta przesłane w zamówieniu:</td><td class="InfoNormal"><span id="comments"><?php echo Sprzedaz::pokazKomentarzZamowienia($_GET['id_poz']); ?></span>
              <?php echo "<span class=\"edit_pole\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" class=\"toolTipTop\" onclick=\"edytuj_pole('comments','textarea')\" /></span>"; ?>
              </td>
            </tr>
            <tr>
              <?php
              // pobieranie informacji od uzytkownikach
              $lista_uzytkownikow = array();
              $zapytanie_uzytkownicy = "SELECT * FROM admin WHERE admin_groups_id = '2' ORDER BY admin_lastname";
              $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
              //
              $lista_uzytkownikow['0'] = 'Nie przypisane ...';
              while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) { 
                $lista_uzytkownikow[$uzytkownicy['admin_id']] = $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname'];
              }
              $db->close_query($sql_uzytkownicy); 
              unset($zapytanie_uzytkownicy, $uzytkownicy);    
              //                                   
              ?>
              <td>Opiekun zamówienia:</td>
              <td class="InfoNormal"><span class="editSelOpiekun" id="service"><?php echo ( $zamowienie->info['opiekun'] == '0' ? 'nie przypisany' : System::PokazAdmina($zamowienie->info['opiekun']) ); ?></span>
              <?php echo "<span class=\"edit_opiekun\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" class=\"toolTipTop\" onclick=\"edytuj_opiekuna('Opiekun','".str_replace('"','%22',json_encode($lista_uzytkownikow))."')\" /></span>"; ?>
              </td>
            </tr>
            <tr>
              <td>Wydruki:</td>
              <td>
                <a href="sprzedaz/zamowienia_zamowienie_pdf.php?id_poz=<?php echo $_GET['id_poz']; ?>"><img src="obrazki/zamowienie_pdf.png" alt="Wygeneruj zamówienie PDF" class="toolTipTop" title="Wygeneruj zamówienie PDF" /></a>
                <a href="sprzedaz/zamowienia_faktura_proforma.php?id_poz=<?php echo $_GET['id_poz']; ?>&amp;zakladka=0"><img src="obrazki/faktura_pdf.png" alt="Wygeneruj fakturę proforma" class="toolTipTop" title="Wygeneruj fakturę proforma" /></a>
                <?php
                if ( $zamowienie->info['data_vat_proforma'] > 0 ) {
                    echo '<span class="vatProforma">Proforma pobrana przez klienta: ' .  date('d-m-Y H:i', $zamowienie->info['data_vat_proforma']) . '</span>';;
                }
                ?>
              </td>
            </tr>
            <tr>
              <td>Faktura VAT:</td>
              <td class="InfoNormal">
              
                <?php
                $tresc_vat = '';
                $sql_vat = $db->open_query("SELECT * FROM invoices WHERE orders_id = '".$_GET['id_poz']."' AND invoices_type = '2'");
                
                if ((int)$db->ile_rekordow($sql_vat) > 0) {
                
                  $info_vat = $sql_vat->fetch_assoc();
                  $tresc_vat .= '<div style="float:left;">';
                  $tresc_vat .= '<b>' . NUMER_FAKTURY_PREFIX . str_pad($info_vat['invoices_nr'], FAKTURA_NUMER_ZERA_WIODACE, 0, STR_PAD_LEFT) . strftime(NUMER_FAKTURY_SUFFIX, strtotime($info_vat['invoices_date_generated'])) . '</b><br />';
                  $tresc_vat .= 'Data utworzenia: ' . date('d-m-Y', strtotime($info_vat['invoices_date_generated'])) . '<br />';
                  $tresc_vat .= 'Data płatności: ' . date('d-m-Y', strtotime($info_vat['invoices_date_payment']));
                  $tresc_vat .= '</div><div class="IkoDiv">';
                  
                  if ( $info_vat['invoices_payment_status'] == '0' && strtotime($info_vat['invoices_date_payment']) > time() ) {
                    $tresc_vat .= '<img src="obrazki/uwaga.png" alt="Fatura nieopłacona" class="toolTipTop" title="Fatura nieopłacona" />';
                  } elseif ( $info_vat['invoices_payment_status'] == '0' && strtotime($info_vat['invoices_date_payment']) <= time() ) {
                    $tresc_vat .= '<img src="obrazki/blad.png" alt="Fatura przeterminowana" class="toolTipTop" title="Fatura przeterminowana" />';
                  } else {
                    $tresc_vat .= '<img src="obrazki/tak.png" alt="Fatura opłacona" class="toolTipTop" title="Fatura opłacona" />';
                  }
                  
                  $tresc_vat .= '<a href="sprzedaz/zamowienia_faktura_pdf.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_vat['invoices_id'].'&amp;zakladka=0"><img src="obrazki/pdf.png" alt="Wydrukuj fakturę VAT" class="toolTipTop" title="Wydrukuj fakturę VAT" /></a>';
                  $tresc_vat .= '<a href="sprzedaz/zamowienia_faktura_edytuj.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_vat['invoices_id'].'&amp;zakladka=0"><img src="obrazki/edytuj.png" alt="Edytuj fakturę VAT" class="toolTipTop" title="Edytuj fakturę VAT" /></a>';

                  $tresc_vat .= '</div>';
                  
                } else {
                
                  $tresc_vat = '<a href="sprzedaz/zamowienia_faktura_generuj.php?id_poz='.$_GET['id_poz'].'&amp;zakladka=0"><img src="obrazki/faktura.png" alt="Wygeneruj fakturę VAT" class="toolTipTop" title="Wygeneruj fakturę VAT" /></a>';
                }
                
                $db->close_query($sql_vat);
                echo $tresc_vat;
                ?>
                
              </td>
            </tr>
            <tr>
              <td>Paragon:</td>
              <td class="InfoNormal">
              
                <?php
                $tresc_paragon = '';
                $sql_vat = $db->open_query("SELECT * FROM receipts WHERE orders_id = '".$_GET['id_poz']."'");
                
                if ((int)$db->ile_rekordow($sql_vat) > 0) {
                
                  $info_paragon = $sql_vat->fetch_assoc();
                  $tresc_paragon .= '<div style="float:left;">';
                  $tresc_paragon .= '<b>' . NUMER_PARAGONU_PREFIX . str_pad($info_paragon['receipts_nr'], FAKTURA_NUMER_ZERA_WIODACE, 0, STR_PAD_LEFT) . strftime(NUMER_PARAGONU_SUFFIX, strtotime($info_paragon['receipts_date_generated'])) . '</b><br />';
                  $tresc_paragon .= 'Data utworzenia: ' . date('d-m-Y', strtotime($info_paragon['receipts_date_generated'])) . '<br />';
                  $tresc_paragon .= '</div><div class="IkoDiv">';
                  
                  $tresc_paragon .= '<a href="sprzedaz/zamowienia_paragon_pdf.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_paragon['receipts_id'].'&amp;zakladka=0"><img src="obrazki/pdf.png" alt="Wydrukuj paragon" class="toolTipTop" title="Wydrukuj paragon" /></a>';
                  $tresc_paragon .= '<a href="sprzedaz/zamowienia_paragon_edytuj.php?id_poz='.$_GET['id_poz'].'&amp;id='.$info_paragon['receipts_id'].'&amp;zakladka=0"><img src="obrazki/edytuj.png" alt="Edytuj paragon" class="toolTipTop" title="Edytuj paragon" /></a>';

                  $tresc_paragon .= '</div>';
                  
                } else {
                
                  $tresc_paragon = '<a href="sprzedaz/zamowienia_paragon_generuj.php?id_poz='.$_GET['id_poz'].'&amp;zakladka=0"><img src="obrazki/faktura.png" alt="Wygeneruj paragon" class="toolTipTop" title="Wygeneruj paragon" /></a>';
                }
                
                $db->close_query($sql_vat);
                echo $tresc_paragon;
                ?>
                
              </td>
            </tr>  
            <tr>
              <td>Waga produktów:</td>
              <td>
                <?php
                echo number_format($zamowienie->waga_produktow, 3, ',', '') . ' kg';
                ?>
              </td>
            </tr>
          </table>
          
        </div>
        
        <br />
        
        <table style="width:100%">
            <tr>
                <td style="width:50%; vertical-align:top">

                    <div class="obramowanie_tabeli">
                    
                      <table class="listing_tbl" id="infoTblWysylka">
                      
                        <tr class="div_naglowek">
                          <td colspan="2">
                          <div class="lf">Dane do wysyłki</div>
                          <div class="LinEdytuj"><a href="sprzedaz/zamowienia_adres_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;typ=dostawa&amp;zakladka=0">edytuj</a></div>
                          </td>
                        </tr>
                        
                        <?php if ( $zamowienie->dostawa['firma'] != '' ) { ?>
                        <tr >
                          <td>Nazwa firmy:</td><td><?php echo $zamowienie->dostawa['firma']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <?php if ( $zamowienie->dostawa['nip'] != '' ) { ?>
                        <tr>
                          <td>Numer NIP:</td><td><?php echo $zamowienie->dostawa['nip']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <tr>
                          <td>Imię i nazwisko:</td><td><?php echo $zamowienie->dostawa['nazwa']; ?></td>
                        </tr>
                        
                        <?php if ( $zamowienie->dostawa['pesel'] != '' ) { ?>
                        <tr>
                          <td>Numer PESEL:</td>
                          <td><?php echo $zamowienie->dostawa['pesel']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <tr>
                          <td>Adres:</td><td><?php echo $zamowienie->dostawa['ulica']; ?></td>
                        </tr>
                        <tr>
                          <td>Kod pocztowy:</td><td><?php echo $zamowienie->dostawa['kod_pocztowy']; ?></td>
                        </tr>
                        <tr>
                          <td>Miejscowość:</td><td><?php echo $zamowienie->dostawa['miasto']; ?></td>
                        </tr>
                        
                        <?php if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) { ?>
                        <tr>
                          <td>Województwo:</td><td><?php echo $zamowienie->dostawa['wojewodztwo']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <tr>
                          <td>Kraj:</td><td><?php echo $zamowienie->dostawa['kraj']; ?></td>
                        </tr>

                      </table>
                      
                    </div>
                    
                </td>
                
                <td style="width:50%; padding-left:10px; vertical-align:top">

                    <div class="obramowanie_tabeli">
                    
                      <table class="listing_tbl" id="infoTblPlatnik">
                      
                        <tr class="div_naglowek">
                          <td colspan="2">
                          <div class="lf">Dane płatnika</div>
                          <div class="LinEdytuj"><a href="sprzedaz/zamowienia_adres_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;typ=platnik&amp;zakladka=0">edytuj</a></div>
                          </td>
                        </tr>
                        
                        <?php if ( $zamowienie->platnik['firma'] != '' ) { ?>
                        <tr>
                          <td>Nazwa firmy:</td><td><?php echo $zamowienie->platnik['firma']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <?php if ( $zamowienie->platnik['nip'] != '' ) { ?>
                        <tr>
                          <td>Numer NIP:</td><td><?php echo $zamowienie->platnik['nip']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <?php if ( trim($zamowienie->platnik['nazwa']) != '' ) { ?>
                        <tr>
                          <td>Imię i nazwisko:</td><td><?php echo $zamowienie->platnik['nazwa']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <?php if ( $zamowienie->platnik['pesel'] != '' ) { ?>
                        <tr>
                          <td>Numer PESEL:</td><td><?php echo $zamowienie->platnik['pesel']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <tr>
                          <td>Adres:</td>
                          <td><?php echo $zamowienie->platnik['ulica']; ?></td>
                        </tr>
                        <tr>
                          <td>Kod pocztowy:</td><td><?php echo $zamowienie->platnik['kod_pocztowy']; ?></td>
                        </tr>
                        <tr>
                          <td>Miejscowość:</td>
                          <td><?php echo $zamowienie->platnik['miasto']; ?></td>
                        </tr>
                        
                        <?php if ( KLIENT_POKAZ_WOJEWODZTWO == 'tak' ) { ?>
                        <tr>
                          <td>Województwo:</td><td><?php echo $zamowienie->platnik['wojewodztwo']; ?></td>
                        </tr>
                        <?php } ?>
                        
                        <tr>
                          <td>Kraj:</td><td><?php echo $zamowienie->platnik['kraj']; ?></td>
                        </tr>

                      </table>
                    </div>
                    
                </td>
            </tr>
        </table>

        <div style="clear:both;"></div>

        <div class="obramowanie_tabeli" style="margin-top:10px;">
        
          <table class="listing_tbl list_poj">
          
            <tr class="div_naglowek">
              <td colspan="2">Płatność</td>
            </tr>
            
            <tr>
              <?php
              //utworzenie tablicy parametrow
              $lista = array();
              $sql_platnosci = $db->open_query("SELECT id, nazwa, klasa FROM modules_payment WHERE status = '1' order by sortowanie");
              $tlumacz = $i18n->tlumacz('PLATNOSCI');
              //
              while ($platnosci = $sql_platnosci->fetch_assoc()) {
                  //
                  $lista[$tlumacz['PLATNOSC_'.$platnosci['id'].'_TYTUL'].'|'.$platnosci['nazwa']] = $platnosci['nazwa'];
                  //
              }
              unset($tlumacz);
              //
              $db->close_query($sql_platnosci);
              $platnosciLista = json_encode($lista);
              $platnosciLista = str_replace('\r\n',' ',$platnosciLista);
              $platnosciLista = str_replace('"','%22',$platnosciLista);
              ?>
              <td>Forma płatności:</td>
              <td class="InfoSpan"><span class="editSelPlatnosc" id="payment_method"><?php echo $zamowienie->info['metoda_platnosci']; ?></span>
              <?php echo "<span class=\"edit_trigger\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" onclick=\"edytuj_platnosc('Platnosc','".$platnosciLista."')\" /></span>"; 
              unset($platnosciLista,$lista);
              ?>
              </td>
            </tr>
            
          </table>
          
        </div>

        <div class="obramowanie_tabeli" style="margin-top:10px;">
        
          <table class="listing_tbl list_poj">
          
            <tr class="div_naglowek">
              <td colspan="2">Dostawa</td>
            </tr>
            
            <tr>
              <?php
              $lista = array();
              $sql_wysylki = $db->open_query("SELECT id, nazwa, klasa FROM modules_shipping WHERE status = '1' order by sortowanie");
              //
              $tlumacz = $i18n->tlumacz('WYSYLKI');
              //
              while ($wysylki = $sql_wysylki->fetch_assoc()) {
                  if ( $wysylki['klasa'] == 'wysylka_odbior_osobisty' ) {

                    //utworzenie tablicy parametrow
                    $zapytanie_parametry = "SELECT modul_id, kod, wartosc FROM modules_shipping_params WHERE modul_id = '".$wysylki['id']."'";

                    $sql_parametry = $GLOBALS['db']->open_query($zapytanie_parametry);
                    while ($info_parametry = $sql_parametry->fetch_assoc()) {
                      $wysylki_parametry[$info_parametry['modul_id']][$info_parametry['kod']] = $info_parametry['wartosc'];
                    }
                    $GLOBALS['db']->close_query($sql_parametry);
                    unset($zapytanie_parametry, $info_parametry);
                    
                    if ( $wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1'] != '' ) {
                        $lista[$tlumacz['WYSYLKA_'.$wysylki['id'].'_TYTUL'].'|'.$wysylki['nazwa'].'|'.$wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1']] = $wysylki['nazwa'] .': '. $wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_1'];
                    }
                    if ( $wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2'] != '' ) {
                        $lista[$tlumacz['WYSYLKA_'.$wysylki['id'].'_TYTUL'].'|'.$wysylki['nazwa'].'|'.$wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2']] = $wysylki['nazwa'] .': '. $wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_2'];
                    }
                    if ( $wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3'] != '' ) {
                        $lista[$tlumacz['WYSYLKA_'.$wysylki['id'].'_TYTUL'].'|'.$wysylki['nazwa'].'|'.$wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3']] = $wysylki['nazwa'] .': '. $wysylki_parametry[$wysylki['id']]['WYSYLKA_ODBIOR_OSOBISTY_PUNKT_3'];
                    }
                    
                  } elseif ( $wysylki['klasa'] == 'wysylka_inpost' ) {
                  
                    $apiKurier = new InPostApi();
                    $paczkomaty_lista = array();

                    if ( $apiKurier->success ) {
                        $paczkomaty_lista = $apiKurier->inpost_find_nearest_machines( $zamowienie->dostawa['kod_pocztowy'] );
                        //
                        foreach ( $paczkomaty_lista as $paczkomat ) {
                            $lista[$tlumacz['WYSYLKA_'.$wysylki['id'].'_TYTUL'].'|'.$wysylki['nazwa'].'|Paczkomat ' . $paczkomat['name'] . ', ' . $paczkomat['street'] . ' ' . $paczkomat['buildingnumber'] . ', ' . $paczkomat['town']] = 'Paczkomat ' . $paczkomat['name'] . ', ' . $paczkomat['street'] . ' ' . $paczkomat['buildingnumber'] . ', ' . $paczkomat['town'].'';
                        }
                    }
                  } else {
                    
                    $lista[$tlumacz['WYSYLKA_'.$wysylki['id'].'_TYTUL'].'|'.$wysylki['nazwa']] = $wysylki['nazwa'];
                    
                  }

              }
              //
              unset($tlumacz);
              //
              $db->close_query($sql_wysylki);
              $wysylkiLista = json_encode($lista);
              $wysylkiLista = str_replace('\r\n',' ',$wysylkiLista);
              $wysylkiLista = str_replace('"','%22',$wysylkiLista);
              ?>
              <td>Forma dostawy:</td>
              <td class="InfoSpan"><span class="editSelWysylka" id="shipping_module"><?php echo $zamowienie->info['wysylka_modul'] . ( $zamowienie->info['wysylka_info'] != '' ? ': ' . $zamowienie->info['wysylka_info'] : '' ); ?></span>
              <?php echo "<span class=\"edit_wysylka\"><img src=\"obrazki/edytuj.png\" alt=\"Edytuj dane\" title=\"Edytuj dane\" onclick=\"edytuj_wysylke('Wysylka','".$wysylkiLista."')\" /></span>"; 
              unset($wysylkiLista,$lista);
              ?>
              </td>
            </tr>
          </table>
          
        </div>
        
        <?php
        
        // dodatkowe pola zamowien
        $dodatkowe_pola_zamowienia = "SELECT oe.fields_id, oe.fields_input_type, oe.fields_required_status, oei.fields_input_value, oei.fields_name, oe.fields_status, oe.fields_input_type 
                                        FROM orders_extra_fields oe, orders_extra_fields_info oei 
                                       WHERE oe.fields_status = '1' AND oei.fields_id = oe.fields_id AND oei.languages_id = '" . $_SESSION['domyslny_jezyk']['id'] . "' ORDER BY oe.fields_order";

        $sql_pola = $db->open_query($dodatkowe_pola_zamowienia);

        include('zamowienia_szczegoly_dodatkowe_pola.php');
        ?>

    </div>
    
<?php
}
?>        