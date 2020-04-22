<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        if ( $filtr->process($_POST["typ"]) == 'dostawa' ) {
          $pola = array(
                  array('delivery_name',$filtr->process($_POST["nazwa"])),
                  array('delivery_company',$filtr->process($_POST["nazwa_firmy"])),
                  array('delivery_street_address',$filtr->process($_POST["ulica"])),
                  array('delivery_city',$filtr->process($_POST["miasto"])),
                  array('delivery_postcode',$filtr->process($_POST["kod_pocztowy"])),
                  array('delivery_state',$filtr->process($_POST["wojewodztwo"])),
                  array('delivery_country',$filtr->process($_POST["kraj"]))
        );
        } else {
          $pola = array(
                  array('billing_name',( $_POST['osobowosc'] == '1' ? $filtr->process($_POST["nazwa"]) : '' )),
                  array('billing_company',( $_POST['osobowosc'] == '0' ? $filtr->process($_POST["nazwa_firmy"]) : '' )),
                  array('billing_nip',( $_POST['osobowosc'] == '0' ? $filtr->process($_POST["nip"]) : '' )),
                  array('billing_pesel',( $_POST['osobowosc'] == '1' ? $filtr->process($_POST["pesel"]) : '' )),
                  array('billing_street_address',$filtr->process($_POST["ulica"])),
                  array('billing_city',$filtr->process($_POST["miasto"])),
                  array('billing_postcode',$filtr->process($_POST["kod_pocztowy"])),
                  array('billing_state',$filtr->process($_POST["wojewodztwo"])),
                  array('billing_country',$filtr->process($_POST["kraj"]))
        );
        }
        //			
        $db->update_query('orders' , $pola, " orders_id = '".(int)$_POST["id"]."'");	
        unset($pola);
        //
        Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
    
          <?php
          if ( !isset($_GET['typ']) || ( $_GET['typ'] != 'dostawa' && $_GET['typ'] != 'platnik' ) ) {
               $_GET['typ'] = 'dostawa';
          }
          ?>          
          
          <!-- Skrypt do autouzupelniania --> 
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#zamowieniaForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                ulica: {
                  required: true
                },
                kod_pocztowy: {
                  required: true
                },
                miasto: {
                  required: true
                },
                kraj: {
                  required: true
                },
                <?php
                if ( $_GET['typ'] == 'platnik' ) {
                ?>
                nazwa_firmy: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik; }},
                nip: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#zamowieniaForm").val() == "1" ) { wynik = false; } return wynik;}},
                <?php
                }
                ?>
                nazwa: {required: function() {var wynik = true; if ( $("input[name='osobowosc']:checked", "#zamowieniaForm").val() == "0" ) { wynik = false; } return wynik;}}
              }
            });

            $.AutoUzupelnienie( 'kraj', 'PodpowiedziMale', 'ajax/autouzupelnienie_kraje.php', 50, 250 );
            $.AutoUzupelnienie( 'wojewodztwo', 'PodpowiedziMale', 'ajax/autouzupelnienie_wojewodztwa.php', 50, 250 );

          });
          //]]>
          </script>        

          <?php
          
          if ( !isset($_GET['id_poz']) ) {
               $_GET['id_poz'] = 0;
          }             
          if ( !isset($_GET['zakladka']) ) {
               $_GET['zakladka'] = '0';
          }           
          
          $zapytanie = "select * from orders where orders_id  = '" . $filtr->process((int)$_GET['id_poz']) . "'";
          $sql = $db->open_query($zapytanie);
            
          if ((int)$db->ile_rekordow($sql) > 0) {

            $info = $sql->fetch_assoc();
            
            ?>
            
            <form action="sprzedaz/zamowienia_adres_edytuj.php" method="post" id="zamowieniaForm" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Edycja danych adresowych - adres <?php echo ( $_GET['typ'] == 'dostawa' ? 'dostawy' : 'płatnika' )?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>
                
                    <div class="pozycja_edytowana">
                        
                        <div class="info_content">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                        <input type="hidden" name="typ" value="<?php echo $filtr->process($_GET['typ']); ?>" />
                        <input type="hidden" name="zakladka" value="<?php echo $filtr->process((int)$_GET['zakladka']); ?>" />
                        
                        <?php if ( $_GET['typ'] == 'dostawa' ) { ?>
                            <p>
                              <label class="required">Imię i nazwisko:</label>
                              <input type="text" name="nazwa" id="nazwa" value="<?php echo Funkcje::formatujTekstInput($info['delivery_name']); ?>" size="53" />
                            </p>   

                            <p>
                              <label>Nazwa firmy:</label>
                              <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo ( $_GET['typ'] == 'dostawa' ? Funkcje::formatujTekstInput($info['delivery_company']) : Funkcje::formatujTekstInput($info['billing_company']) ); ?>" size="53" />
                            </p>   

                        <?php } ?>

                        <?php if ( $_GET['typ'] == 'platnik' ) { ?>

                            <p>
                              <label>Osobowość prawna:</label>
                              <input type="radio" value="1" name="osobowosc" onclick="$('#fizyczna').slideDown();$('#pesel').slideDown();$('#firma').slideUp();$('#nip').slideUp()" <?php echo ( $info['billing_nip'] == '' ? 'checked="checked"' : '' ); ?> /> osoba fizyczna
                              <input type="radio" value="0" name="osobowosc" onclick="$('#fizyczna').slideUp();$('#pesel').slideUp();$('#firma').slideDown();$('#nip').slideDown()" <?php echo ( $info['billing_nip'] != '' ? 'checked="checked"' : '' ); ?> /> firma
                            </p> 

                            <p id="firma" <?php echo ( $info['billing_nip'] != '' ? '' : 'style="display:none;"' ); ?> >
                              <label class="required">Nazwa firmy:</label>
                              <input type="text" name="nazwa_firmy" id="nazwa_firmy" value="<?php echo ( $_GET['typ'] == 'dostawa' ? Funkcje::formatujTekstInput($info['delivery_company']) : Funkcje::formatujTekstInput($info['billing_company']) ); ?>" size="53" />
                            </p>   

                            <p id="fizyczna" <?php echo ( $info['billing_nip'] == '' ? '' : 'style="display:none;"' ); ?> >
                              <label class="required">Imię i nazwisko:</label>
                              <input type="text" name="nazwa" id="nazwa" value="<?php echo Funkcje::formatujTekstInput($info['billing_name']); ?>" size="53" />
                            </p>   

                            <p id="nip" <?php echo ( $info['billing_nip'] != '' ? '' : 'style="display:none;"' ); ?> class="required">
                              <label class="required">Numer NIP:</label>
                              <input type="text" name="nip" id="nip" value="<?php echo ( $_GET['typ'] == 'dostawa' ? $info['delivery_nip'] : $info['billing_nip'] ); ?>" size="53" />
                            </p>   

                             <p id="pesel" <?php echo ( $info['billing_nip'] == '' ? '' : 'style="display:none;"' ); ?> >
                              <label>Numer PESEL:</label>
                              <input type="text" name="pesel" id="pesel" value="<?php echo ( $_GET['typ'] == 'dostawa' ? $info['delivery_pesel'] : $info['billing_pesel'] ); ?>" size="53" />
                            </p>   

                        <?php } ?>

                        <p>
                          <label class="required">Adres:</label>
                          <input type="text" name="ulica" id="ulica" value="<?php echo ( $_GET['typ'] == 'dostawa' ? Funkcje::formatujTekstInput($info['delivery_street_address']) : Funkcje::formatujTekstInput($info['billing_street_address']) ); ?>" size="53" />
                        </p>   

                        <p>
                          <label class="required">Kod pocztowy:</label>
                          <input type="text" name="kod_pocztowy" id="kod_pocztowy" value="<?php echo ( $_GET['typ'] == 'dostawa' ? $info['delivery_postcode'] : $info['billing_postcode'] ); ?>" size="25" />
                        </p>   

                        <p>
                          <label class="required">Miejscowość:</label>
                          <input type="text" name="miasto" id="miasto" value="<?php echo ( $_GET['typ'] == 'dostawa' ? Funkcje::formatujTekstInput($info['delivery_city']) : Funkcje::formatujTekstInput($info['billing_city']) ); ?>" size="53" />
                        </p>   

                        <p>
                          <label>Województwo:</label>
                          <input type="text" style="height:24px; padding-top:0px; padding-bottom:0px" name="wojewodztwo" id="wojewodztwo" value="<?php echo ( $_GET['typ'] == 'dostawa' ? Funkcje::formatujTekstInput($info['delivery_state']) : Funkcje::formatujTekstInput($info['billing_state']) ); ?>" size="53" />
                        </p>   

                        <p>
                          <label class="required">Kraj:</label>
                          <input type="text" style="height:24px; padding-top:0px; padding-bottom:0px" name="kraj" id="kraj" value="<?php echo ( $_GET['typ'] == 'dostawa' ? Funkcje::formatujTekstInput($info['delivery_country']) : Funkcje::formatujTekstInput($info['billing_country']) ); ?>" size="53" />
                        </p>   

                        </div>
                     
                    </div>

                    <div class="przyciski_dolne">
                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('zakladka','id_poz')); ?>','sprzedaz');">Powrót</button>           
                    </div>

              </div>                      
            </form>

            <?php

          } else {
          
            ?>
            
            <div class="poleForm"><div class="naglowek">Edycja danych adresowych</div>
                <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
            </div>
            
            <?php

          }

          $db->close_query($sql);
          unset($zapytanie, $info);            
          ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}