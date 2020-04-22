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
                array('complaints_rand_id',$filtr->process($_POST["id_reklamacji"])),
                array('complaints_customers_orders_id',$filtr->process($_POST["nr_zamowienia"])),
                array('complaints_subject',$filtr->process($_POST["tytul"])),
                array('complaints_date_created','now()'),
                array('complaints_date_modified','now()'),
                array('complaints_service',$filtr->process($_POST["opiekun_id"])),
                array('complaints_status_id',$filtr->process($_POST["status_id"]))
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
        $db->insert_query('complaints' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        
        $pola = array(
                array('complaints_id',$id_dodanej_pozycji),
                array('complaints_status_id',$filtr->process($_POST["status_id"])),
                array('date_added','now()'),
                array('comments',$filtr->process($_POST["wiadomosc"]))
                );

        $db->insert_query('complaints_status_history' , $pola);	
        unset($pola);                
        
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('reklamacje.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('reklamacje.php');
        }
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
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
            
            ckedit('wiadomosc','99%','200px');     
          });
          
          function wybierz_klienta(id) {
            if (id == 1) {
                $('#klient_z_bazy').slideDown('fast');
                $('#klient_z_poza_bazy').slideUp('fast');
                $('#nr_zamowienia').val('');
            }
            if (id == 2) {
                $('#klient_z_poza_bazy').slideDown('fast'); 
                $('#klient_z_bazy').slideUp('fast');
                $('#nr_zamowienia').val('99999999');
            }                                      
          }   
          //]]>
          </script>     
          
          <form action="reklamacje/reklamacje_dodaj.php" method="post" id="reklamacjaForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <?php
                $Id_Reklamacji = Reklamacje::UtworzIdReklamacji(15);
                ?>
                
                <input type="hidden" name="id_reklamacji" value="<?php echo $Id_Reklamacji; ?>" />
                
                <p>
                  <label class="required">Nr zgłoszenia:</label>
                  <input type="text" name="id_tmp" size="25" value="<?php echo $Id_Reklamacji; ?>" disabled="disabled" />     
                </p>
                
                <p>
                  <label class="required">Tytuł reklamacji:</label>
                  <input type="text" name="tytul" id="tytul" size="75" value="" />     
                </p>

                <p>
                  <label>Rodzaj klienta:</label>
                  <input type="radio" value="1" name="rodzaj_klienta" onclick="wybierz_klienta(1)" checked="checked" /> z bazy sklepu
                  <input type="radio" value="2" name="rodzaj_klienta" onclick="wybierz_klienta(2)" /> spoza bazy sklepu        
                </p>                

                <div id="klient_z_bazy">
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
                                      echo '<tr class="pozycja_off">';
                                      echo '<td><input type="radio" name="klient_id" value="' . $klient['id'] . '" ' . (($zaznacz == false) ? 'checked="checked"' : '') . ' /></td>';
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
                                      //
                                  }
                                  unset($zaznacz);
                                  ?>
                                  
                                </table>
                                
                              </div>  
                              <?php
                              unset($tablica_klientow);
                              ?>
                          </td>
                      </tr>
                  </table>                    
                </div>
                
                <div id="klient_z_poza_bazy" style="display:none">
                  <p>
                    <label>Imię i nazwisko:</label>
                    <input type="text" name="dane_klienta_nazwa" size="55" value="" />     
                  </p>                
                  <p>
                    <label>Adres klienta:</label>
                    <textarea name="dane_klienta_adres" rows="5" cols="80"></textarea>
                  </p>
                  <p>
                    <label>Adres email:</label>
                    <input type="text" name="email_klienta" size="35" value="" />     
                  </p>                    
                </div>

                <p>
                  <label id="nrZamowienia" class="required">Nr zamówienia:</label>
                  <input type="text" name="nr_zamowienia" id="nr_zamowienia" class="calkowita" size="15" value="" /> 
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
                  echo Funkcje::RozwijaneMenu('opiekun_id', $lista_uzytkownikow, '', 'style="width:200px;"'); 
                  unset($lista_uzytkownikow);
                  ?>
                </p>                
                
                <p>
                  <label class="required">Status reklamacji:</label>
                  <?php echo Funkcje::RozwijaneMenu('status_id', Reklamacje::ListaStatusowReklamacji( false ), '', 'style="width:300px;"'); ?>
                </p>

                <p>
                  <label>Opis reklamacji:</label>
                  <textarea id="wiadomosc" name="wiadomosc" cols="90" rows="5"></textarea>
                </p>                 

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('reklamacje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','reklamacje');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
