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
                array('discount_name',$filtr->process($_POST["nazwa"])),
                array('discount_discount',$filtr->process($_POST["rabat"])));

        //	
        $db->update_query('discount_products' , $pola, " discount_id = '".(int)$_POST["id"]."'");	

        unset($pola);
        //
        Funkcje::PrzekierowanieURL('rabaty_produktow.php?id_poz='.(int)$_POST["id"]);
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
            $("#eForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                rabat: { required: true, range: [-100, 0], number: true }
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane"
                }
              }
            });
          });                          
          //]]>
          </script>        

          <form action="klienci/rabaty_produktow_edytuj.php" method="post" id="eForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Edycja danych</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select * from discount_products where discount_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {
            
                $info = $sql->fetch_assoc();
                ?>            
            
                <div class="pozycja_edytowana">
                
                    <div class="info_content">
                
                        <input type="hidden" name="akcja" value="zapisz" />
                        
                        <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

                        <p>
                          <label class="required">Nazwa:</label>
                          <input type="text" name="nazwa" id="nazwa" value="<?php echo $info['discount_name']; ?>" size="53" />
                        </p>   

                        <p>
                          <label class="required">Rabat [%]:</label>
                          <input class="toolTip" type="text" name="rabat" id="rabat" value="<?php echo $info['discount_discount']; ?>" size="5" title="liczba z zakresu -100 do 0" />
                        </p>
                        
                        <p>
                          <label>Produkt:</label>
                          <?php
                          //
                          $produkt_nazwa = $db->open_query("select distinct products_name from products_description where products_id = '".(int)$info['discount_products_id']."' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
                          $nazwa = $produkt_nazwa->fetch_assoc();
                          //
                          $db->close_query($produkt_nazwa);    
                          unset($produkt_nazwa);
                          //
                          ?>
                          <input type="text" name="nazwa" value="<?php echo $nazwa['products_name']; ?>" size="83" disabled="disabled" />
                        </p> 

                        <?php if ($info['discount_groups_id'] > 0) { ?>

                        <p>
                          <label>Grupa klientów:</label>
                          <?php
                          $tablica = Klienci::ListaGrupKlientow(false);                                        
                          echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica, $info['discount_groups_id'], 'disabled="disabled"');
                          unset($tablica);
                          ?>
                        </p>
                        
                        <?php } else { ?>
                        
                        <table>
                            <tr>
                                <td class="label_tbl"><label>Klient:</label></td>
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
                                        foreach ( $tablica_klientow as $klient) {
                                            //
                                            if ( $klient['id'] == $info['discount_customers_id'] ) {
                                                //
                                                echo '<tr class="pozycja_off">';
                                                echo '<td><input type="radio" name="klient" value="' . $klient['id'] . '" checked="checked" disabled="disabled" /></td>';
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
                                            }
                                            //
                                        }
                                        ?>
                                        
                                      </table>
                                      
                                    </div>  
                                    <?php
                                    unset($tablica_klientow);
                                    ?>
                                </td>
                            </tr>
                        </table>                          

                        <?php } ?>

                    </div>
                    
                </div>

                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('rabaty_produktow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
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