<?php
function los($dlugosc = 1) {
    $tabelka = '1234567890QWERTYUIOPASDFGHJKKLZXCVBNM';
    $id = '';
    for ($i=0; $i<=$dlugosc; $i++)
    {
        $id .= $tabelka[rand()%(strlen($tabelka))];
    } 
    return $id;
}

chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
          
          <!-- Skrypt do walidacji formularza -->
          <script type="text/javascript">
          //<![CDATA[
          $(document).ready(function() {
            $("#kuponyForm").validate({
              rules: {
                pref: {
                  required: true,
                },             
                rabat_kwota: {
                  range: [0.01, 100000],
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_kwota").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  },                  
                },
                rabat_procent: {
                  range: [0.01, 100],
                  number: true,
                  required: function(element) {
                    if ($("#rodzaj_procent").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  }, 
                },
                ilosc: {
                  range: [1, 100000],
                  number: true,
                },
                wartosc: {
                  range: [1, 100000],
                  number: true,
                },
                ilosc_kuponow: {
                  range: [1, 100000],
                  number: true,
                  required: true,
                }                
              },
              messages: {
                pref: {
                  required: "Pole jest wymagane",
                },
                ilosc_generowana: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 1",
                },      
                liczba_znakow: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 1",
                },                
                rabat_kwota: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 0.01",
                },
                rabat_procent: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 0.01",
                },              
                ilosc: {
                  range: "Wartość musi być wieksza lub równa 1",
                },
                wartosc: {
                  range: "Wartość musi być wieksza lub równa 1",
                },
                ilosc_kuponow: {
                  required: "Pole jest wymagane",
                  range: "Wartość musi być wieksza lub równa 1",
                }                 
              }
            });
            
            $('input.datepicker').Zebra_DatePicker({
               format: 'd-m-Y',
               inside: false,
               readonly_element: false
            });             
            
          });
          
          function rodzaj_rabat(elem) {
             $('#rodzaj_kwota').css('display','none');
             $('#rodzaj_procent').css('display','none');
             //
             if (elem != '') {
                $('#rodzaj_' + elem).slideDown();
             }
          }             
          
          function warun(elem) {
             $('#kategorie').css('display','none');
             $('#producenci').css('display','none');
             $('#produkty').css('display','none');
             //
             if ( elem != 'produkty' ) {
                  $('#' + elem).slideDown();
                } else {
                  lista_produktow();
             }
          }      

          // uzywane przy podobnych i akcesoriach - do chowania listy produktow przy dodawaniu
          function lista_produktow() {
            //
            $('#produkty').show(); 
            $('#produkty').html('<img src="obrazki/_loader_small.gif">');
            $.get("ajax/lista_produktow_kupony.php",
                { tok: '<?php echo Sesje::Token(); ?>' },
                function(data) { 
                    $('#produkty').hide();
                    $('#produkty').html(data);     
                    $('#produkty').slideDown();                    
            }); 
          }                 
          //]]>
          </script>        

          <form action="kupony/kupony_dodaj_seria_losowa_mail_wyslij.php" method="post" id="kuponyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                    <input type="hidden" name="akcja" value="zapisz" />
                
                    <strong class="nagl">Dane dotyczące generowania kuponu</strong>

                    <p>
                      <label class="required">Prefix do kodu kuponu:</label>
                      <input type="text" name="pref" id="pref" value="" size="10" class="toolTipText" title="Dowolny ciąg znaków ktory będzie na początku nazwy kuponów" />
                    </p> 

                    <p>
                      <label>Opis kuponu:</label>
                      <input class="toolTipText" type="text" name="opis" value="" size="50" title="Opis kuponu - widoczny tylko dla administratora sklepu" />
                    </p>
                    
                    <p>
                      <label>Rodzaj rabatu:</label>
                      <input type="radio" value="fixed" name="rodzaj" onclick="rodzaj_rabat('kwota')" class="toolTipTop" title="Rabat jest stały kwotowy" checked="checked" /> kwotowy
                      <input type="radio" value="percent" name="rodzaj" onclick="rodzaj_rabat('procent')" class="toolTipTop" title="Rabat obliczany jest procentowo od wartości zamówienia" /> procentowy
                    </p>
                    
                    <div id="rodzaj_kwota">
                      <p>
                          <label class="required">Wartość rabatu:</label>
                          <input type="text" name="rabat_kwota" id="rabat_kwota" value="" size="10" class="toolTip" title="Wartość kwotowa powyżej 0.01" />
                      </p>
                    </div>
                    
                    <div id="rodzaj_procent" style="display:none">
                      <p>
                          <label class="required">Wartość rabatu (w %):</label>
                          <input type="text" name="rabat_procent" id="rabat_procent" value="" size="3" class="toolTip" title="Wartość procentowa od 0.01 do 100%" />
                      </p>
                    </div>
                    
                    <p>
                        <label>Data rozpoczęcia:</label>
                        <input type="text" name="data_od" value="" size="20" class="datepicker" />                                        
                    </p>

                    <p>
                        <label>Data zakończenia:</label>
                        <input type="text" name="data_do" value="" size="20" class="datepicker" />                                        
                    </p>

                    <div class="ramkaWarunki">
                    
                        <b>Dodatkowe warunki użycia kuponu</b>
                        
                        <table style="margin-bottom:10px">
                            <tr>
                                <td><label>Dostępny dla grupy klientów:</label></td>
                                <td>
                                    <?php                        
                                    $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                    foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                        echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" /> ' . $GrupaKlienta['text'] . '<br />';
                                    }               
                                    unset($TablicaGrupKlientow);
                                    ?>
                                </td>
                            </tr>
                        </table> 
                        
                        <div class="ostrzezenie" style="margin:0px 15px 10px 0px">Jeżeli nie zostanie wybrana żadna grupa klientów to kupon będzie dostępny dla wszystkich klientów.</div>                                                   

                        <p>
                          <label>Minimalna ilość produktów:</label>
                          <input class="toolTip kropkaPusta" type="text" name="ilosc" id="ilosc" value="" size="3" title="Ilość produktów w koszyku od jakiej będzie można zrealizować kupon" />
                        </p> 

                        <p>
                          <label>Minimalna wartość zamówienia:</label>
                          <input class="toolTip kropkaPusta" type="text" name="wartosc" id="wartosc" value="" size="10" title="Wartość zamówienia od jakiej będzie można zrealizować kupon" />
                        </p>  
                    
                        <p>
                          <label>Produkty promocyjne:</label>
                          <input type="radio" value="1" name="promocja" class="toolTipTop" title="Czy kuponem mają być objęte produkty promocyjne ?" checked="checked" /> tak
                          <input type="radio" value="0" name="promocja" class="toolTipTop" title="Czy kuponem mają być objęte produkty promocyjne ?" /> nie
                        </p>                         
                        
                        <p>
                          <label>Dostępny tylko dla:</label>
                          <input type="radio" value="kategoria" name="warunek" onclick="warun('kategorie')" class="toolTipTop" title="Kupon będzie można wykorzystać tylko w określnych kategoriach" checked="checked" /> wybranych kategorii
                          <input type="radio" value="producent" name="warunek" onclick="warun('producenci')" class="toolTipTop" title="Kupon będzie można wykorzystać tylko dla określonych producentów" /> wybranych producentów
                          <input type="radio" value="produkt" name="warunek" onclick="warun('produkty')" class="toolTipTop" title="Kupon będzie można wykorzystać tylko dla określonych produktów" /> wybranych produktów
                        </p>                        
                        
                        <div id="kategorie">
                            <div id="drzewo" style="margin-left:165px;width:550px;">
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
                                            <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                                            <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                                          </tr>
                                          '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                                }
                                if ( count($tablica_kat) == 0 ) {
                                     echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                                }                                     
                                echo '</table>';
                                unset($tablica_kat,$podkategorie); 
                                ?>            
                            </div> 
                        </div>
                        
                        <div id="producenci" style="display:none">
                            <?php
                            $Prd = Funkcje::TablicaProducenci();

                            for ($b = 0, $c = count($Prd); $b < $c; $b++) {
                                //
                                echo '<input type="checkbox" value="'.$Prd[$b]['id'].'" name="id_producent[]" /> '.$Prd[$b]['text'] . '<br />';
                            }
                            
                            if ( count($Prd) == 0 ) {
                                 echo '<div style="padding:10px">Brak wyników do wyświetlania</div>';
                            }                               

                            unset($Prd);
                            ?>
                        </div>
                        
                        <div id="produkty" style="display:none"></div>

                        <span class="ostrzezenie" style="margin:0px 0px 5px 165px">
                            Jeżeli nie zostanie wybrana żadna kategoria, producent czy produkt - kupon będzie aktywny dla wszystkich kategorii, producentów i produktów.
                        </span>                         

                    </div>
                    
                    <strong class="nagl">Dane maila</strong>
                
                    <p>
                      <label>Newsletter który ma być użyty do wysłania maili:</label>
                      <?php
                      $zapytanie = 'SELECT DISTINCT newsletters_id, title FROM newsletters order by title'; 
                      $sql = $db->open_query($zapytanie);
    
                      $tablica = array();
                      while ($info = $sql->fetch_assoc()) {
                             $tablica[] = array('id' => $info['newsletters_id'],
                                                'text' => $info['title']);
                      }

                      echo Funkcje::RozwijaneMenu('newsletter', $tablica, '', 'style="width:300px"'); 
       
                      $db->close_query($sql);
                      unset($tablica,$zapytanie, $info);                      
                      ?>
                    </p>                         

                </div>
             
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Przejdź dalej" />
              <button type="button" class="przyciskNon" onclick="cofnij('kupony','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','kupony');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}