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
                array('discount_products_id',$filtr->process($_POST["id_prod"])));
                   
        if ((int)$_POST["tryb_rabat"] == 1) {
            $pola[] = array('discount_groups_id',$filtr->process($_POST["grupa_klientow"]));
          } else {
            $pola[] = array('discount_customers_id',$filtr->process($_POST["klient"]));
        }
        //			

        $db->insert_query('discount_products' , $pola);	
        $id_dodanej_pozycji = $db->last_id_query();    

        unset($pola);
                
        //
        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('rabaty_produktow.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('rabaty_produktow.php');
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
                id_prod: {
                  required: function(element) {
                    if ($("#id_prod").val() == '') {
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
                id_prod: {
                  required: "Nie został wybrany produkt"
                }                
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

          <form action="klienci/rabaty_produktow_dodaj.php" method="post" id="eForm" class="cmxform">          

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
                      <label style="width:500px">Produkt do jakiego będzie przypisany rabat:</label>
                    </p>
                    
                    <table><tr>
                        <td style="vertical-align:top">
                        
                            <div style="margin-left:175px" id="fraza">
                                <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" /></div> <span title="Wyszukaj produkt" onclick="fraza_produkty()"></span>
                            </div>                        
                        
                            <div id="drzewo" style="margin-left:175px;width:250px;">
                                <?php
                                //
                                echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                                //
                                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                                    $podkategorie = false;
                                    if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                                    //
                                    echo '<tr>
                                            <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" /> '.$tablica_kat[$w]['text'].'</td>
                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                          </tr>
                                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                }
                                echo '</table>';
                                unset($tablica_kat,$podkategorie);   
                                ?>            
                            </div>        
                        </td>
                        <td style="vertical-align:top">
                            
                            <input type="hidden" id="rodzaj_modulu" value="rabat_produkty" />
                            <div id="wynik_produktow_rabat_produkty"></div>                     
                            
                        </td>
                    </tr></table>
                    
                    <p>
                      <input type="hidden" name="id_prod" id="id_prod" value="" />
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
              <button type="button" class="przyciskNon" onclick="cofnij('rabaty_produktow','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','klienci');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}