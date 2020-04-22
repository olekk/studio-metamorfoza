<?php
if ( isset($toks) ) {
?>

    <div id="zakl_id_3" style="display:none;" class="pozycja_edytowana">

        <div class="obramowanie_tabeli">
        
          <table class="listing_tbl" id="infoTblHistoria">
          
            <tr class="div_naglowek">
              <td>Data dodania</td>
              <td>Mail do klienta</td>
              <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?><td align="center">SMS do klienta</td><?php } ?>
              <td>Status</td>
              <td style="width:50%">Komentarze</td>
              <td>Akcja</td>
            </tr>
            
            <?php 
            if ( count($zamowienie->statusy) > 0 ) {
            
              foreach ( $zamowienie->statusy as $status ) {
                ?>
                <tr class="pozycja_off">
                  <td style="white-space:nowrap;"><?php echo date('d-m-Y H:i', strtotime($zamowienie->statusy[$status['zamowienie_status_id']]['data_dodania'])); ?></td>
                  <td><img src="obrazki/<?php echo ( $zamowienie->statusy[$status['zamowienie_status_id']]['powiadomienie_mail'] == '1' ? 'tak.png' : 'tak_off.png' ); ?>" alt="" /></td>
                  <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?><td style="text-align:center"><img src="obrazki/<?php echo ( $zamowienie->statusy[$status['zamowienie_status_id']]['powiadomienie_sms'] == '1' ? 'tak.png' : 'tak_off.png' ); ?>" alt="" /></td><?php } ?>
                  <td><?php echo Sprzedaz::pokazNazweStatusuZamowienia($zamowienie->statusy[$status['zamowienie_status_id']]['status_id']); ?></td>
                  <td style="text-align:left">
                      <div style="overflow:auto; max-width:400px">
                           <?php echo $zamowienie->statusy[$status['zamowienie_status_id']]['komentarz']; ?>
                      </div>
                  </td>
                  <td>
                  <?php
                  if ( count($zamowienie->statusy) > 1 ) {
                    echo '<a href="sprzedaz/zamowienia_historia_usun.php?id_poz='.$_GET['id_poz'].'&amp;status_id='.$status['zamowienie_status_id'].'&amp;zakladka=3"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  } else {
                    echo '<img src="obrazki/kasuj_off.png" alt="Opcja niedostępna" title="Opcja niedostępna" />';
                  }
                  ?>
                  </td>
                </tr>
                <?php
              }
            } 
            ?>
            
          </table>
          
        </div>

        <div class="pozycja_edytowana" style="margin-top:20px;">
        
            <div class="info_content">

              <form action="sprzedaz/zamowienia_szczegoly.php" method="post" id="zamowieniaUwagiForm" class="cmxform" enctype="multipart/form-data">

                <div>
                    <input type="hidden" name="akcja" value="zapisz" />
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    <input type="hidden" name="nazwa_klienta" value="<?php echo $zamowienie->klient['nazwa']; ?>" />
                    <input type="hidden" name="telefon_klienta" value="<?php echo $zamowienie->klient['telefon']; ?>" />
                    <input type="hidden" name="zakladka" value="3" />
                </div>
                
                <p id="wersja">
                  <label>W jakim języku wysłać email:</label>
                  <?php
                  echo Funkcje::RadioListaJezykow('onclick="UkryjZapiszKomentarz(0)"');
                  ?>
                </p>
                
                <script type="text/javascript">
                //<![CDATA[
                function UkryjZapiszKomentarz(id) {
                    if (parseInt(id) > 0) {
                        $('#przyciski').slideDown('fast');     
                    } else {
                        $('#przyciski').slideUp('fast');
                        $("#komentarz_tresc").val('');
                    }   
                    //
                    $('#ladujKomentarz').fadeIn('fast');
                    $.post('sprzedaz/standardowe_komentarze.php', { jezyk: 1, id: id, nazwy: 'tak', id_zamowienia: '<?php echo $zamowienie->info['id_zamowienia']; ?>' }, function(data){
                      $("#komentarz").html(data);
                      $('#ladujKomentarz').fadeOut('fast');
                      $("#komentarz_tresc").val('');
                    });                   
                }   
                function ZmienKomentarz(id) {
                    var jezyk = $("input[name='jezyk']:checked").val();
                    $('#ladujKomentarz').fadeIn('fast');
                    $.post('sprzedaz/standardowe_komentarze.php', { jezyk: jezyk, id: id, nazwy: 'nie', id_zamowienia: '<?php echo $zamowienie->info['id_zamowienia']; ?>' }, function(data){
                      $("#komentarz_tresc").val(data);
                      $('#ladujKomentarz').fadeOut('fast');
                    });                 
                }
                
                $(document).ready(function() {
                
                    $("input[name=jezyk]").change(function(){
                      $("#status option:first").prop("selected",true); 
                      $('#komentarz').html('<option selected="selected" value="0">--- najpierw wybierz status zamówienia ---</option>');
                      $("#komentarz_tresc").val('');
                    });

                    $('#upload').MultiFile({
                      max: <?php echo EMAIL_ILOSC_ZALACZNIKOW; ?>,
                      accept:'<?php echo EMAIL_DOZWOLONE_ZALACZNIKI; ?>',
                      STRING: {
                       denied:'Nie można przesłać pliku w tym formacie $ext!',
                       duplicate:'Taki plik jest już dodany:\n$file!',
                       selected:'Wybrany plik: $file'
                      }
                    });                    
                
                });
                //]]>
                </script>
                
                <p>
                  <label>Nowy status zamówienia:</label>
                  <?php
                  $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
                  echo Funkcje::RozwijaneMenu('status', $tablica,'','id="status" onchange="UkryjZapiszKomentarz(this.value)" style="width:350px;"'); ?>
                </p>
                <p>
                  <label>Standardowy komentarz:</label>
                  <?php
                  $tablica = array();
                  $tablica[] = array('id' => '0', 'text' => '--- najpierw wybierz status zamówienia ---');
                  echo Funkcje::RozwijaneMenu('status_komentarz', $tablica,'','id="komentarz" onchange="ZmienKomentarz(this.value)" style="width:350px;"'); ?>                  
                </p>
                
                <div id="ladujKomentarz"><img src="obrazki/_loader_small.gif" alt="" /></div>

                <p>
                  <label>Poinformuj klienta e-mail:</label>
                  <input type="checkbox" checked="checked" value="1" name="info_mail" id="info_mail" class="toolTip" title="Informacja o zmianie statusu zostanie przeslana do klienta" />
                </p>

                <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?>
                <p>
                  <label>Poinformuj klienta SMS:</label>
                  <?php if ( Klienci::CzyNumerGSM($zamowienie->klient['telefon']) ) { ?>
                    <input type="checkbox" value="1" name="info_sms" id="info_sms" class="toolTip" title="Wysłanie powiadomienia SMS do klienta o zmianie statusu" />
                  <?php } else { ?>
                    <input type="checkbox" value="1" name="info_sms" id="info_sms" disabled="disabled" />
                  <?php } ?>
                </p>
                <?php } ?>

                <p>
                  <label>Dołącz komentarz do maila:</label>
                  <input type="checkbox" checked="checked" value="1" name="dolacz_komentarz" id="dolacz_komentarz" class="toolTip" title="Informacja komentarza zostanie dołączona do maila z powiadomieniem do klienta" />
                </p>
                
                <div class="ramkaPunkty">

                    <?php
                    if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
                    
                        $zapytaniePkt = "select unique_id, points from customers_points where customers_id = '" . $zamowienie->klient['id'] . "' and orders_id = '" . $zamowienie->info['id_zamowienia'] . "' and points > 0 and points_status != '2' and points_status != '4' and points_type = 'SP'";
                        $sqlp = $db->open_query($zapytaniePkt);       
                        
                        if ((int)$db->ile_rekordow($sqlp) > 0) {
                        
                            $info = $sqlp->fetch_assoc();
                            ?>
                            
                            <div>

                            <p class="punkty">
                              <label>Zmień status punktów:</label>
                              <input type="checkbox" value="1" name="punkty" class="toolTip" title="Zostanie zmieniony statusów punktów które zostały naliczone klientowi przy złożeniu zamówienia" />
                            </p>     
                            
                            <p>
                                <label>Nowy status punktów:</label>
                                <?php        
                                echo Funkcje::RozwijaneMenu('status_punktow', Klienci::ListaStatusowPunktow(false), 2);
                                ?>                        
                            </p>      

                            <p>
                                <label>Zmiana punktów klienta:</label>
                                <input type="radio" value="1" name="tryb" class="toolTipTop" title="Ogólna ilość punktów klienta zostanie zmieniona" checked="checked" /> dodaj     
                                <input type="radio" value="2" name="tryb" class="toolTipTop" title="Ogólna ilość punktów klienta pozostanie bez zmian" /> nie dodawaj
                            </p>

                            <p>
                                <label>Ilość punktów do zatwierdzenia:</label>
                                <input type="text" name="ilosc_punktow" value="<?php echo $info['points']; ?>" size="5" />
                                <input type="hidden" name="pkt_id" value="<?php echo $info['unique_id']; ?>" />
                            </p>

                            </div>

                        <?php
                        }
                        
                        $db->close_query($sqlp);
                        unset($zapytaniePkt, $info);
                        
                    }

                    // program partnerski
                    if ( SYSTEM_PUNKTOW_STATUS == 'tak' && PP_STATUS == 'tak' ) {
                    
                        $zapytaniePkt = "select unique_id, customers_id, points from customers_points where orders_id = '" . $zamowienie->info['id_zamowienia'] . "' and points > 0 and points_status != '2' and points_status != '4' and points_type = 'PP'";
                        $sqlp = $db->open_query($zapytaniePkt);       
                        
                        if ((int)$db->ile_rekordow($sqlp) > 0) {
                        
                            $info = $sqlp->fetch_assoc();
                            ?>
                            
                            <div>

                            <p class="punkty">
                              <label>Dodaj i zmień status punktów z Programu Partnerskiego <br /><a class="blank" href="klienci/klienci_edytuj.php?id_poz=<?php echo $info['customers_id']; ?>">[szczegóły klienta]</a>:</label>
                              <input type="checkbox" value="1" name="punkty_pp" class="toolTip" title="Zostanie zmieniony statusów punktów które zostały naliczone z Programu Partnerskiego" />
                              <input type="hidden" name="klient_pp" value="<?php echo $info['customers_id']; ?>" />
                            </p>  

                            <p>
                                <label>Nowy status punktów:</label>
                                <?php        
                                echo Funkcje::RozwijaneMenu('status_punktow_pp', Klienci::ListaStatusowPunktow(false), 2);
                                ?>                        
                            </p>  

                            <p>
                                <label>Ilość punktów do zatwierdzenia:</label>
                                <input type="text" name="ilosc_punktow_pp" value="<?php echo $info['points']; ?>" size="5" />
                                <input type="hidden" name="pkt_id_pp" value="<?php echo $info['unique_id']; ?>" />
                            </p>
                            
                            </div>

                        <?php
                        }
                        
                        $db->close_query($sqlp);
                        unset($zapytaniePkt, $info);
                        
                    }
                    ?>  

                </div>

                <p style="padding-top:15px;padding-bottom:10px;">
                  <label>Załączniki:</label>
                  <input type="file" name="file[]" id="upload" size="53" />
                </p>
                
                <div class="maleInfo" style="margin-left:180px">Dozwolne formaty plików: <?php echo implode(', ', explode('|', EMAIL_DOZWOLONE_ZALACZNIKI)); ?></div> 
                
                <p>
                  <label>Komentarz:</label>
                  <textarea cols="100" rows="10" name="komentarz" class="wysiwyg" id="komentarz_tresc"></textarea>
                </p>

                <div class="przyciski_dolne" id="przyciski" style="display:none">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <input type="hidden" name="powrot" id="powrot" value="0" />
                  <input type="submit" class="przyciskNon" value="Zapisz dane i wróć do listy zamówień" onclick="$('#powrot').val(1)" />
                </div>

              </form>

            </div>
         
        </div>
        
    </div>
    
<?php
}
?>    