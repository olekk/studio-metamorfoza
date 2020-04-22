<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $pola = array(
                array('complaints_customers_orders_id',$filtr->process($_POST["nr_zamowienia"])),
                array('complaints_subject',$filtr->process($_POST["tytul"])),
                array('complaints_date_modified','now()'),
                array('complaints_service',$filtr->process($_POST["opiekun_id"]))
                );
                
        if ((int)$_POST["rodzaj_klienta"] == 1) {
            // jezeli jest klient z bazy
            $zapytanieKlient = "select customers_default_address_id, customers_firstname, customers_lastname, customers_email_address from customers where customers_id = '" . $filtr->process($_POST["klient_id"]) . "'";
            $sql = $db->open_query($zapytanieKlient);     
            $klient = $sql->fetch_assoc();
            //
            $pola[] = array('complaints_customers_id',$filtr->process($_POST["klient_id"]));
            $pola[] = array('complaints_customers_name',$klient['customers_firstname'] . ' ' . $klient['customers_lastname']);
            $pola[] = array('complaints_customers_address','');
            $pola[] = array('complaints_customers_email',$klient['customers_email_address']);
            //
            $db->close_query($sql);
            unset($zapytanieKlient, $klient);
            //
          } else {
            // jezeli klient nie jest z bazy
            $pola[] = array('complaints_customers_id','0');
            $pola[] = array('complaints_customers_name',$filtr->process($_POST["dane_klienta_nazwa"]));
            $pola[] = array('complaints_customers_address',$filtr->process($_POST["dane_klienta_adres"]));
            $pola[] = array('complaints_customers_email',$filtr->process($_POST["email_klienta"]));
            //
        }
        //			
        $db->update_query('complaints' , $pola, " complaints_id = '".(int)$_POST["id"]."'");		
        unset($pola);
        
        //
        Funkcje::PrzekierowanieURL('reklamacje_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#reklamacjaForm").validate({
              rules: {
                tytul: {
                  required: true
                }, 
                nr_zamowienia: {
                  required: true,
                  range: [1, 1000000],
                  number: true
                }            
              },
              messages: {
                tytul: {
                  required: "Pole jest wymagane"
                },
                nr_zamowienia: {
                  required: "Pole jest wymagane"
                }                
              }
            });
            
            ckedit('wiadomosc','100%','200px');     
          });
          
          function wybierz_klienta(id) {
            if (id == 1) {
                $('#klient_z_bazy').slideDown('fast');
                $('#klient_z_poza_bazy').slideUp('fast');      
            }
            if (id == 2) {
                $('#klient_z_poza_bazy').slideDown('fast'); 
                $('#klient_z_bazy').slideUp('fast');   
            }                                      
          }   
          //]]>
          </script>     

          <form action="reklamacje/reklamacje_edytuj.php" method="post" id="reklamacjaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            } 
            if ( !isset($_GET['zakladka']) ) {
                 $_GET['zakladka'] = '0';
            }            
            
            $zapytanie = "select * from complaints where complaints_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">

                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                    <input type="hidden" name="zakladka" value="<?php echo $filtr->process((int)$_GET['zakladka']); ?>" />
                    
                    <p>
                      <label class="required">Nr zgłoszenia:</label>
                      <input type="text" name="id_tmp" size="25" value="<?php echo $info['complaints_rand_id']; ?>" disabled="disabled" />     
                    </p>
                    
                    <p>
                      <label class="required">Tytuł reklamacji:</label>
                      <input type="text" name="tytul" id="tytul" size="75" value="<?php echo $info['complaints_subject']; ?>" />     
                    </p>

                    <p>
                      <label>Rodzaj klienta:</label>
                      <input type="radio" value="1" name="rodzaj_klienta" onclick="wybierz_klienta(1)" <?php echo (($info['complaints_customers_id'] > 0) ? 'checked="checked"' : ''); ?> /> z bazy sklepu
                      <input type="radio" value="2" name="rodzaj_klienta" onclick="wybierz_klienta(2)" <?php echo (($info['complaints_customers_id'] == 0) ? 'checked="checked"' : ''); ?> /> z poza bazy sklepu        
                    </p>                

                    <div id="klient_z_bazy" <?php echo (($info['complaints_customers_id'] > 0) ? '' : 'style="display:none"'); ?>>
                      <table>
                          <tr>
                              <td class="label_tbl"><label>Wybierz klienta:</label></td>
                              <td>
                                  <?php
                                  $tablica_klientow = Klienci::ListaKlientow( false );
                                  ?>
                                  <div class="obramowanie_tabeli lista_klientow">
                                  
                                    <table class="listing_tbl">
                                    
                                      <tr class="div_naglowek">
                                        <td>Wybierz</td>
                                        <td>ID</td>
                                        <td>Dane klienta</td>
                                        <td>Firma</td>
                                        <td>Kontakt</td>
                                      </tr>           

                                      <?php
                                      $zaznacz = false;
                                      foreach ( $tablica_klientow as $klient) {
                                          //
                                          $zaznacz_input = '';
                                          if ( $klient['id'] == $info['complaints_customers_id'] || ( $info['complaints_customers_id'] == 0 && $zaznacz == false ) ) {
                                               $zaznacz_input = 'checked="checked"';
                                          }
                                          //
                                          echo '<tr class="pozycja_off"' . (($klient['id'] == $info['complaints_customers_id']) ? ' id="wybrany"' : '') . '>';
                                          echo '<td><input type="radio" name="klient_id" value="' . $klient['id'] . '" ' . $zaznacz_input . ' /></td>';
                                          echo '<td>' . $klient['id'] . '</td>';
                                          echo '<td>' . $klient['nazwa'] . '<br />' . $klient['adres'] . '</td>';
                                          
                                          if ( !empty($klient['firma']) ) {
                                               echo '<td><span class="firma">' . $klient['firma'] . '</span>' . ((!empty($klient['nip'])) ? 'NIP:&nbsp;' . $klient['nip'] : '') . '</td>';
                                             } else{
                                               echo '<td></td>';
                                          }
                                          
                                          echo '<td><span class="maly_mail">' . $klient['email'] . '</span></td>';
                                          echo '</tr>';
                                          //
                                          $zaznacz = true;
                                          unset($zaznacz_input);
                                          //
                                      }
                                      unset($zaznacz);
                                      ?>
                                      
                                    </table>
                                    
                                    <?php if ($info['complaints_customers_id'] > 0) { ?>
 
                                    <script type="text/javascript">
                                    //<![CDATA[   
                                    $('.lista_klientow').scrollTop($('#wybrany').position().top);
                                    //]]>
                                    </script>    
                                    
                                    <?php } ?>

                                  </div>  
                                  <?php
                                  unset($tablica_klientow);
                                  ?>
                              </td>
                          </tr>
                      </table>   
                    </div>
                    
                    <div id="klient_z_poza_bazy" <?php echo (($info['complaints_customers_id'] == 0) ? '' : 'style="display:none"'); ?>>
                      <p>
                        <label>Imię i nazwisko:</label>
                        <input type="text" name="dane_klienta_nazwa" size="55" value="<?php echo $info['complaints_customers_name']; ?>" />     
                      </p>                
                      <p>
                        <label>Adres klienta:</label>
                        <textarea name="dane_klienta_adres" rows="5" cols="80"><?php echo $info['complaints_customers_address']; ?></textarea>
                      </p>
                      <p>
                        <label>Adres email:</label>
                        <input type="text" name="email_klienta" size="35" value="<?php echo $info['complaints_customers_email']; ?>" />     
                      </p>                         
                    </div>

                    <p>
                      <label class="required">Nr zamówienia:</label>
                      <input type="text" name="nr_zamowienia" id="nr_zamowienia" class="calkowita" size="15" value="<?php echo $info['complaints_customers_orders_id']; ?>" /> 
                    </p>
                    
                    <p>
                      <label>Opiekun reklamacji:</label>
                      <?php
                      // pobieranie informacji od uzytkownikach
                      $lista_uzytkownikow = Array();
                      $zapytanie_uzytkownicy = "select distinct * from admin order by admin_lastname, admin_firstname";
                      $sql_uzytkownicy = $db->open_query($zapytanie_uzytkownicy);
                      //
                      $lista_uzytkownikow[] = array('id' => 0, 'text' => 'Nie przypisany ...');
                      //
                      while ($uzytkownicy = $sql_uzytkownicy->fetch_assoc()) {
                        $lista_uzytkownikow[] = array('id' => $uzytkownicy['admin_id'], 'text' => $uzytkownicy['admin_firstname'] . ' ' . $uzytkownicy['admin_lastname']);
                      }
                      $db->close_query($sql_uzytkownicy); 
                      unset($zapytanie_uzytkownicy, $uzytkownicy);    
                      //                                   
                      echo Funkcje::RozwijaneMenu('opiekun_id', $lista_uzytkownikow, $info['complaints_service'], 'style="width:200px;"'); 
                      unset($lista_uzytkownikow);
                      ?>
                    </p>  

                    </div>

                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('reklamacje_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','reklamacje');">Powrót</button>           
                </div>                 

            <?php
            
            $db->close_query($sql);
            unset($info);            
            
            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
