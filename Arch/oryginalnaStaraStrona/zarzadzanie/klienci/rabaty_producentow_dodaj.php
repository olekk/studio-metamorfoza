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
                array('discount_discount',$filtr->process($_POST["rabat"])),
                array('discount_manufacturers_id',implode(',', $_POST['id_producent'])));
                
        if ((int)$_POST["tryb_rabat"] == 1) {
            $pola[] = array('discount_groups_id',$filtr->process($_POST["grupa_klientow"]));
          } else {
            $pola[] = array('discount_customers_id',$filtr->process($_POST["klient"]));
        }
        //			
        $db->insert_query('discount_manufacturers' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('rabaty_producentow.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('rabaty_producentow.php');
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
            $("#eForm").validate({
              rules: {
                nazwa: {
                  required: true
                },
                rabat: { required: true, range: [-100, 0], number: true },
                id_producenta: {
                  required: function(element) {
                    if ($("#id_producenta").val() == '') {
                        return true;
                      } else {
                        return false;
                    }
                  }
                }
              },
              messages: {
                nazwa: {
                  required: "Pole jest wymagane"
                },
                id_producenta: {
                  required: "Nie został wybrany producent"
                } 
              }
            });
            
            $('.pkc td').find('input').click( function() {
               $('#id_producenta').val( $(this).val() );
               //
               var checked = [];
               $("input[name='id_producent[]']:checked").each( function() {
                   checked.push(parseInt($(this).val()));
               });
               if ( checked.length == 0 ) {
                    $('#id_producenta').val('');
               }                     
            });            
          });
           
          function wybierz_zakres_rabatu(id) {
            if (id == 1) {
                $('#zakres_grupa').slideDown('fast');
                $('#zakres_klient').hide();        
            }
            if (id == 2) {
                $('#zakres_klient').slideDown('fast'); 
                $('#zakres_grupa').hide();    
            }                                      
          }                            
          //]]>
          </script>        

          <form action="klienci/rabaty_producentow_dodaj.php" method="post" id="eForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />

                    <p>
                      <label class="required">Nazwa:</label>
                      <input type="text" name="nazwa" id="nazwa" value="" size="53" />
                    </p>   

                    <p>
                      <label class="required">Rabat [%]:</label>
                      <input class="toolTip" type="text" name="rabat" id="rabat" value="" size="5" title="liczba z zakresu -100 do 0" />
                    </p>
                    
                    <p>
                      <label style="width:500px">Producenci do jakich będzie przypisany rabat:</label>
                    </p>
                    
                    <div id="drzewo" style="margin-left:175px;width:550px;">
                    
                      <?php
                      $Prd = Funkcje::TablicaProducenci();
                      //
                      if (count($Prd) > 0) {
                          //
                          echo '<table class="pkc">';
                          //
                          for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                              echo '<tr>                                
                                      <td class="lfp">
                                          <input type="checkbox" value="'.$Prd[$b]['id'].'" name="id_producent[]" /> '.$Prd[$b]['text'].'
                                      </td>                                
                                    </tr>';
                          }
                          echo '</table>';
                          //
                      }
                      unset($Prd);
                      ?>
                      
                    </div>  

                    <p>
                      <input type="hidden" name="id_producenta" id="id_producenta" value="" />
                    </p>                      
                    
                    <p>
                      <label>Rabat przypisany do:</label>
                      <input type="radio" value="1" name="tryb_rabat" onclick="wybierz_zakres_rabatu(1)" checked="checked" /> grupy klientów
                      <input type="radio" value="2" name="tryb_rabat" onclick="wybierz_zakres_rabatu(2)" /> indywidualnego klienta           
                    </p>

                    <p id="zakres_grupa">
                      <label>Grupa klientów:</label>
                      <?php
                      $tablica = Klienci::ListaGrupKlientow(false);                                        
                      echo Funkcje::RozwijaneMenu('grupa_klientow', $tablica, '');
                      unset($tablica);
                      ?>
                    </p>
                    
                    <div id="zakres_klient" style="display:none">
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
                                      $zaznacz = false;
                                      foreach ( $tablica_klientow as $klient) {
                                          //
                                          echo '<tr class="pozycja_off">';
                                          echo '<td><input type="radio" name="klient" value="' . $klient['id'] . '" ' . (($zaznacz == false) ? 'checked="checked"' : '') . ' /></td>';
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

                </div>
                
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <button type="button" class="przyciskNon" onclick="cofnij('rabaty_producentow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}