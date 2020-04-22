<?php
if ( isset($toks) ) {
?>

                        <div id="zakl_id_1" style="display:none;" class="pozycja_edytowana">
                            <?php
                            $zapytanie = "SELECT * FROM settings WHERE type = 'wysylki' ";
                            $sql = $db->open_query($zapytanie);

                            $parametr_kurierzy = array();

                            if ( $db->ile_rekordow($sql) > 0 ) {
                              while ($info = $sql->fetch_assoc()) {
                                $parametr_kurierzy[$info['code']] = array($info['value'], $info['limit_values'], $info['description']);
                              }
                            }
                            $db->close_query($sql);
                            unset($zapytanie);
                            ?>
                            <div class="obramowanie_tabeli">
                            
                              <table class="listing_tbl">
                              
                                <tr class="div_naglowek srodekTbl">
                                  <td>Firma</td>
                                  <td>Numer<br />dokumentu</td>
                                  <td>Data<br />utworzenia</td>
                                  <td>Ilość<br />paczek</td>
                                  <td>Status</td>
                                  <td>Data<br />aktualizacji</td>
                                  <td></td>
                                </tr>
                                      
                                <?php 
                                if ( count($zamowienie->dostawy) > 0) {

                                  foreach ( $zamowienie->dostawy as $dostawa ) {

                                    $status = $dostawa['status_przesylki'];
                                    $kodTrackingowy = '';

                                    if ( strpos($dostawa['rodzaj_przesylki'], 'SendIt') !== false ) {
                                        $status = Funkcje::PokazStatusSendit($dostawa['status_przesylki']);
                                        if ( $dostawa['komentarz'] != '' ) {
                                            $kodTrackingowyTMP = unserialize($dostawa['komentarz']);
                                            $kodTrackingowy = $kodTrackingowyTMP[0];
                                        }
                                    }
                                    if ( strtolower($dostawa['rodzaj_przesylki']) == strtolower('Elektroniczny Nadawca') ) {
                                        if ( $status == '0' ) {
                                            $status = 'W buforze';
                                        }
                                    }

                                    if ( stripos($dostawa['rodzaj_przesylki'], 'FURGONETKA') !== false ) {
                                        if ( $status == '0' ) {
                                            $status = 'Oczekująca';
                                        } elseif ( $status == '1' ) {
                                            $status = 'Zamówiona';
                                        }
                                    }
                                    ?>
                                    <tr class="pozycja_off srodekTbl">
                                      <td><?php echo $dostawa['rodzaj_przesylki']; ?></td>
                                      <td><?php echo $dostawa['numer_przesylki']; ?><?php echo ( $kodTrackingowy != '' ? '<br />'.$kodTrackingowy : '' ); ?></td>
                                      <td><?php echo date('d-m-Y H:i', strtotime($dostawa['data_utworzenia'])); ?></td>
                                      <td><?php echo $dostawa['ilosc_paczek']; ?></td>
                                      <td><?php echo $status; ?></td>
                                      <!-- <td align="left"><?php echo $dostawa['komentarz']; ?></td> -->
                                      <td><?php echo date('d-m-Y H:i', strtotime($dostawa['data_aktualizacji'])); ?></td>
                                      <td style="width:50px" class="rg_right">
                                      <?php
                                        if ( strpos($dostawa['rodzaj_przesylki'], 'SendIt') !== false ) {
                                          echo '<a href="sprzedaz/zamowienia_wysylka_sendit_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'"><img src="obrazki/przesylka_tracking.png" alt="Pobierz status przesyłki" title="Pobierz status przesyłki" /></a>';
                                          if ( $dostawa['status_przesylki'] > 2 && $dostawa['status_przesylki'] <= 10 ) {
                                            echo '<a href="sprzedaz/zamowienia_wysylka_sendit_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" title="Pobierz etykietę" /></a>';
                                          }

                                          if ( $dostawa['status_przesylki'] > 10 ) {
                                            echo '<a href="sprzedaz/zamowienia_wysylka_sendit_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=protokol&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/zamowienie_pdf.png" alt="Pobierz protokół" title="Pobierz protokół" /></a>';
                                          }
                                        }

                                        if ( strtolower($dostawa['rodzaj_przesylki']) == strtolower('KurJerzy') ) {
                                          echo '<a href="sprzedaz/zamowienia_wysylka_kurjerzy_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" title="Pobierz etykietę" /></a>';
                                          echo '<a href="sprzedaz/zamowienia_wysylka_kurjerzy_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'"><img src="obrazki/przesylka_tracking.png" alt="Pobierz informacje trackingowe" title="Pobierz informacje trackingowe" /></a>';
                                        }

                                        if ( strtolower($dostawa['rodzaj_przesylki']) == strtolower('InPost') ) {
                                          $refresh = false;

                                          if ( $dostawa['status_przesylki'] == 'Created' ) {
                                            echo '<a href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/kasuj.png" alt="Usuń paczkę" title="Usuń paczkę" /></a>';
                                          }

                                          echo '<a href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'"><img src="obrazki/etykieta_pdf.png" alt="Wygeneruj etykietę" title="Wygeneruj etykietę" /></a>';

                                          //if ( $dostawa['status_przesylki'] != 'Created' ) {
                                            echo '<a href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/przesylka_tracking.png" alt="Pobierz informacje trackingowe" title="Pobierz informacje trackingowe" /></a>';
                                          //}
                                          if ( $dostawa['status_przesylki'] != 'Created' && $dostawa['komentarz'] == '' ) {
                                            $refresh = true;
                                            echo '<a href="sprzedaz/zamowienia_wysylka_inpost_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=potwierdzenie&amp;przesylka='.$dostawa['numer_przesylki'].'" ' . ( $refresh == true ? 'class="download"' : '' ) . '><img src="obrazki/faktura.png" alt="Potwierdzenie nadania paczki" title="Potwierdzenie nadania paczki" /></a>';
                                          }
                                        }

                                        if ( strtolower($dostawa['rodzaj_przesylki']) == strtolower('Siodemka') ) {
                                          echo '<a href="sprzedaz/zamowienia_wysylka_siodemka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" title="Pobierz etykietę" /></a>';
                                          echo '<a href="sprzedaz/zamowienia_wysylka_siodemka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'"><img src="obrazki/przesylka_tracking.png" alt="Pobierz informacje trackingowe" title="Pobierz informacje trackingowe" /></a>';
                                          echo '<a href="sprzedaz/zamowienia_wysylka_siodemka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=list&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/faktura.png" alt="Potwierdzenie nadania paczki" title="Potwierdzenie nadania paczki" /></a>';
                                        }

                                        if ( strtolower($dostawa['rodzaj_przesylki']) == strtolower('DHL') ) {
                                          echo '<a href="sprzedaz/zamowienia_wysylka_dhl_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/delete.png" alt="Usuń plik XML" title="Usuń plik XML" /></a>';
                                          echo '<a href="sprzedaz/zamowienia_wysylka_dhl_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=pobierz&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/wczytaj.png" alt="Pobierz plik XML" title="Pobierz plik XML" /></a>';
                                        }

                                        if ( strtolower($dostawa['rodzaj_przesylki']) == strtolower('Elektroniczny Nadawca') ) {
                                          $danePrzesylki = explode(':',$dostawa['komentarz']);

                                          if ( $dostawa['status_przesylki'] == '0' ) {
                                            echo '<a href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$danePrzesylki[0].'" ><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" title="Pobierz etykietę" /></a>';

                                            echo '<a href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=pobranie&amp;przesylka='.$danePrzesylki[0].'" ><img src="obrazki/proforma_pdf.png" alt="Pobierz blankiet pobrania" title="Pobierz blankiet pobrania" /></a>';

                                            echo '<a href="sprzedaz/zamowienia_wysylka_enadawca_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=clearbuforGuid&amp;przesylka='.$danePrzesylki[0].'&amp;przesylkaId='.$dostawa['id_przesylki'].'" ><img src="obrazki/kasuj.png" alt="Usuń z bufora" title="Usuń z bufora" /></a>';
                                          }

                                        }

                                        if ( strtolower($dostawa['rodzaj_przesylki']) == strtolower('Kex') ) {
                                          if ( $status == 'OAK' || $status == 'WPR' ) {
                                            echo '<a href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=anuluj&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/kasuj.png" alt="Anuluj przesyłkę" title="Anuluj przesyłkę" /></a>';
                                          }
                                          echo '<a href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=status&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/przesylka_tracking.png" alt="Pobierz status przesyłki" title="Pobierz status przesyłki" /></a><br />';
                                          if ( $status == 'OAK' || $status == 'WPR' ) {
                                              echo '<a href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" title="Pobierz etykietę" /></a>';
                                          }
                                          if ( $status != 'OAK' ) {
                                            echo '<a href="sprzedaz/zamowienia_wysylka_kex_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=list&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/zamowienie_pdf.png" alt="Pobierz list przewozowy" title="Pobierz list przewozowy" /></a>';
                                          }
                                        }

                                        if ( stripos($dostawa['rodzaj_przesylki'], 'FURGONETKA') !== false ) {
                                          if ( $dostawa['status_przesylki'] == '0' ) {
                                              echo '<a href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=usun&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/delete.png" alt="Usuń z oczekujących" title="Usuń z oczekujących" /></a>';
                                              echo '<a href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=zamow&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/przesylka_dodaj.png" alt="Zamów paczkę" title="Zamów paczkę" /></a>';
                                          } elseif ( $dostawa['status_przesylki'] == '1' ) {
                                              echo '<a href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" title="Pobierz etykietę" /></a>';
                                              echo '<a href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=tracking&amp;przesylka='.$dostawa['numer_przesylki'].'&amp;serwis='.$dostawa['komentarz'].'" ><img src="obrazki/przesylka_tracking.png" alt="Pokaż szczegóły" title="Pokaż szczegóły" /></a>';
                                          } else {
                                              echo '<a href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=etykieta&amp;przesylka='.$dostawa['numer_przesylki'].'" ><img src="obrazki/etykieta_pdf.png" alt="Pobierz etykietę" title="Pobierz etykietę" /></a>';
                                              echo '<a href="sprzedaz/zamowienia_wysylka_furgonetka_akcja.php?id_poz='.(int)$_GET['id_poz'].'&amp;zakladka=1&amp;akcja=tracking&amp;przesylka='.$dostawa['numer_przesylki'].'&amp;serwis='.$dostawa['komentarz'].'" ><img src="obrazki/przesylka_tracking.png" alt="Pokaż szczegóły" title="Pokaż szczegóły" /></a>';
                                          }
                                        }
                                      ?>

                                      </td>
                                    </tr>
                                    <?php
                                  }

                                } else {
                                  ?>
                                  <tr class="pozycja_brak_danych">
                                    <td style="text-align:left" colspan="5">Brak pozycji do wyświetlenia</td>
                                  </tr>
                                  <?php
                                } ?>
                              </table>
                              
                            </div>

                            <div style="margin-top:20px;">
                            
                                <div class="obramowanie_tabeli" style="display:inline-block">
                                  <table class="listing_tbl">
                                  
                                    <tr class="div_naglowek">
                                      <td colspan="10">Utwórz wysyłkę</td>
                                    </tr>
                                    <?php
                                    $integracje = false;
                                    if ($parametr_kurierzy['INTEGRACJA_SENDIT_WLACZONY']['0'] == 'tak') $integracje = true;
                                    if ($parametr_kurierzy['INTEGRACJA_POCZTA_EN_WLACZONY']['0'] == 'tak') $integracje = true;
                                    if ($parametr_kurierzy['INTEGRACJA_KURJERZY_WLACZONY']['0'] == 'tak') $integracje = true;
                                    if ($parametr_kurierzy['INTEGRACJA_SIODEMKA_WLACZONY']['0'] == 'tak') $integracje = true;
                                    if ($parametr_kurierzy['INTEGRACJA_INPOST_WLACZONY']['0'] == 'tak') $integracje = true;
                                    if ($parametr_kurierzy['INTEGRACJA_DHL_WLACZONY']['0'] == 'tak') $integracje = true;
                                    if ($parametr_kurierzy['INTEGRACJA_KEX_WLACZONY']['0'] == 'tak') $integracje = true;
                                    if ($parametr_kurierzy['INTEGRACJA_FURGONETKA_WLACZONY']['0'] == 'tak') $integracje = true;

                                    if ( !$integracje ) {?>
                                    <tr class="pozycja_off">
                                        <td colspan="10">Brak włączonych modułów integracji z firmami kurierskimi</td>
                                    </tr>
                                    <?php } ?>

                                    <tr class="pozycja_off">
                                      
                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_SENDIT_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_SENDIT_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_sendit.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_sendit_min.png" class="toolTipTop" alt="Utwórz przesyłkę SendIt" title="Utwórz przesyłkę SendIt" /></a>
                                        </td>
                                        <?php } ?>

                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_POCZTA_EN_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_POCZTA_EN_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_enadawca.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_en_min.png" class="toolTipTop" alt="Utwórz przesyłkę do Elektronicznego nadawcy" title="Utwórz przesyłkę do Elektronicznego nadawcy" /></a>
                                        </td>
                                        <?php } ?>

                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_FURGONETKA_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_FURGONETKA_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_furgonetka.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_furgonetka_min.png" class="toolTipTop" alt="Utwórz przesyłkę do serwisu FURGONETKA" title="Utwórz przesyłkę do serwisu FURGONETKA" /></a>
                                        </td>
                                        <?php } ?>

                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_KURJERZY_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_KURJERZY_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_kurjerzy.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_kurjerzy_min.png" class="toolTipTop" alt="Utwórz przesyłkę KurJerzy.pl" title="Utwórz przesyłkę KurJerzy.pl" /></a>
                                        </td>
                                        <?php } ?>

                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_SIODEMKA_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_SIODEMKA_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_siodemka.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_siodemka_min.png" class="toolTipTop" alt="Utwórz przesyłkę Siódemka" title="Utwórz przesyłkę Siódemka" /></a>
                                        </td>
                                        <?php } ?>

                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_INPOST_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_INPOST_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_inpost.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_inpost_min.png" class="toolTipTop" alt="Utwórz przesyłkę Paczkomaty InPost" title="Utwórz przesyłkę Paczkomaty InPost" /></a>
                                        </td>
                                        <?php } ?>

                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_KEX_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_KEX_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_kex.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_kex_min.png" class="toolTipTop" alt="Utwórz przesyłkę K-EX" title="Utwórz przesyłkę K-EX" /></a>
                                        </td>
                                        <?php } ?>

                                        <?php if ( isset($parametr_kurierzy['INTEGRACJA_DHL_WLACZONY']['0']) && $parametr_kurierzy['INTEGRACJA_DHL_WLACZONY']['0'] == 'tak' ) { ?>
                                        <td style="text-align:center; width:110px">
                                            <a href="sprzedaz/zamowienia_wysylka_dhl.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=1"><img src="obrazki/logo/logo_dhl_min.png" class="toolTipTop" alt="Utwórz plik XML dla programu eCas" title="Utwórz plik XML dla programu eCas" /></a>
                                        </td>
                                        <?php } ?>

                                    </tr>
                                  </table>
                                  
                                </div>
                            </div>

                        </div>
    
<?php
}
?>    