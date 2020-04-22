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
                array('gift_status','1'),
                array('gift_value_of',$filtr->process($_POST['input_od'])),
                array('gift_value_for',$filtr->process($_POST['input_do'])),
                array('gift_products_id',$filtr->process($_POST['id_prod'])),
                array('gift_min_quantity',(int)$_POST['ilosc']),
                array('customers_group_id',((isset($_POST['grupa_klientow'])) ? implode(',', $_POST['grupa_klientow']) : 0)));
                
        // jezeli gratis bedzie z cena
        if ((int)$_POST['tryb_cena'] == 1) {
            //  
            $pola[] = array('gift_price',$filtr->process($_POST['cena']));
            //
        }    

        if ($_POST['warunek'] == 'kategoria' && isset($_POST['id_kat']) && count($_POST['id_kat']) > 0) {
            $pola[] = array('gift_exclusion','kategorie'); 
            //
            $tablica_kat = $_POST['id_kateg'];
            $lista = '';
            for ($q = 0, $c = count($tablica_kat); $q < $c; $q++) {
                //
                $lista .= $tablica_kat[$q] . ',';
                //
            } 
            $lista = substr($lista, 0, -1);
            //
            $pola[] = array('gift_exclusion_id',$lista); 
            unset($tablica_kat, $lista);
        }
        
        if ($_POST['warunek'] == 'producent' && isset($_POST['id_producent']) && count($_POST['id_producent']) > 0) {
            $pola[] = array('gift_exclusion','producenci'); 
            //
            $tablica_producent = $_POST['id_producent'];
            $lista = '';
            for ($q = 0, $c = count($tablica_producent); $q < $c; $q++) {
                //
                $lista .= $tablica_producent[$q] . ',';
                //
            } 
            $lista = substr($lista, 0, -1);
            //
            $pola[] = array('gift_exclusion_id',$lista); 
            unset($tablica_producent, $lista);
        }  

        if ($_POST['warunek'] == 'produkt' && isset($_POST['id_produkt']) && count($_POST['id_produkt']) > 0) {
            $pola[] = array('gift_exclusion','produkty'); 
            //
            $tablica_produkt = $_POST['id_produkt'];
            $lista = '';
            for ($q = 0, $c = count($tablica_produkt); $q < $c; $q++) {
                //
                $lista .= $tablica_produkt[$q] . ',';
                //
            } 
            $lista = substr($lista, 0, -1);
            //
            $pola[] = array('gift_exclusion_id',$lista); 
            unset($tablica_produkt, $lista);
        }           
                
        $sql = $db->insert_query('products_gift' , $pola);
        $id_dodanej_pozycji = $db->last_id_query();
        
        unset($pola);

        if (isset($id_dodanej_pozycji) && $id_dodanej_pozycji > 0) {
            Funkcje::PrzekierowanieURL('gratisy.php?id_poz='.$id_dodanej_pozycji);
        } else {
            Funkcje::PrzekierowanieURL('gratisy.php');
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
            $("#gratisyForm").validate({
              rules: {
                id_prod: {
                  required: function(element) {
                    if ($("#id_prod").val() == '') {
                        return true;
                      } else {
                        return false;
                    }
                  }
                },            
                cena: {
                  required: function(element) {
                    if ($("#kwota_gratis").css('display') == 'block') {
                        return true;
                      } else {
                        return false;
                    }
                  },
                  range: [0.01, 1000000],
                  number: true                  
                },   
                ilosc: {
                  range: [0, 100000],
                  number: true
                },                
                input_od: {
                  required: true,
                  range: [1, 1000000],
                  number: true
                },  
                input_do: {
                  required: true,
                  range: [1, 1000000],
                  number: true
                }                
              },
              messages: {
                id_prod: {
                  required: "Nie został wybrany produkt",
                },            
                cena: {
                  required: "Pole jest wymagane",
                },
                ilosc: {
                  range: "Wartość musi być wieksza od 0"
                },                  
                input_od: {
                  required: "Pole jest wymagane",
                },
                input_do: {
                  required: "Pole jest wymagane",
                }                
              }
            });
          });
          
          function anuluj_minus(elem) {
            if ($(elem).val() < 0) {
                $(elem).val( $(elem).val() * -1 );
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

          // uzywane do generowania drzewa kategorii
          function podkat_gratisy(id) {
              //
              $('#pp_'+id).html('<img src="obrazki/_loader_small.gif">');
              $.get("ajax/drzewo_podkategorie_gratisy.php",
                  { pole: id, tok: $('#tok').val() },
                  function(data) { 
                      $('#pp_'+id).css('display','none');
                      $('#pp_'+id).html(data);
                      $('#pp_'+id).css('padding-left','15px');
                      $('#pp_'+id).css('display','block');                                                           
                      //
                      $('#imgp_'+id).html('<img src="obrazki/zwin.png" onclick="podkat_gratisy_off('+ "'" + id + "'" + ')" alt="Zwiń" title="Zwiń" />'); 
                      //
                      pokazChmurki();
                      //
              });
          }
          function podkat_gratisy_off(id, typ) {
              //
              $('#pp_'+id).css('display','none');
              $('#pp_'+id).css('padding','0px');
              $('#imgp_'+id).html('<img src="obrazki/rozwin.png" onclick="podkat_gratisy('+ "'" + id + "'" + ')" alt="Rozwiń" title="Rozwiń" />'); 
          }          
          //]]>
          </script>     

          <form action="gratisy/gratisy_dodaj.php" method="post" id="gratisyForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <div class="info_content">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <p>
                  <label style="width:500px">Produkt który będzie gratisem:</label>
                </p>

                <table style="margin-top:-30px"><tr>
                    <td style="vertical-align:top">
                    
                        <div style="margin-left:175px;margin-top:1px" id="fraza">
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
                                        <td class="lfp"><input type="radio" onclick="podkat_produkty(this.value)" value="'.$tablica_kat[$w]['id'].'" name="id_kat" /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            if ( count($tablica_kat) == 0 ) {
                                 echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                            }                                
                            echo '</table>';
                            unset($podkategorie);   
                            ?>            
                        </div>        
                    </td>
                    <td style="vertical-align:top">
                        
                        <input type="hidden" id="rodzaj_modulu" value="gratisy" />
                        <div id="wynik_produktow_gratisy"></div>                     
                        
                    </td>
                </tr></table>    

                <p>
                    <input type="hidden" name="id_prod" id="id_prod" value="" />
                </p>
                
                <p>
                  <label>Czy gratis będzie dodawany za darmo czy będzie miał cenę ?</label>
                  <input type="radio" value="1" name="tryb_cena" onclick="$('#kwota_gratis').slideDown()" checked="checked" class="toolTipTop" title="Umożliwia przypisanie gratisowi ceny - np 1 zł" /> będzie miał cenę
                  <input type="radio" value="2" name="tryb_cena" onclick="$('#kwota_gratis').slideUp()" class="toolTipTop" title="Gratis będzie dodawany do zamówienia za darmo" /> będzie darmowy           
                </p>                 
                
                <div id="kwota_gratis">
                
                    <p>
                        <label class="required">Cena brutto:</label>           
                        <input type="text" name="cena" id="cena" class="toolTip" title="Wartość musi być większa od 0.01" size="15" value="1.00" />
                    </p>  
                    
                </div>
                
                <div class="ramkaWarunki">
                
                    <b>Dodatkowe warunki wyświetlania gratisu</b>                       

                    <table style="margin:10px 10px 10px 0px">
                        <tr>
                            <td><label>Dostępny dla grupy klientów:</label></td>
                            <td>
                                <?php                        
                                $TablicaGrupKlientow = Klienci::ListaGrupKlientow(false);
                                foreach ( $TablicaGrupKlientow as $GrupaKlienta ) {
                                    echo '<input type="checkbox" value="' . $GrupaKlienta['id'] . '" name="grupa_klientow[]" ' . ((in_array($GrupaKlienta['id'], explode(',', $info['customers_group_id']))) ? 'checked="checked" ' : '') . '/> ' . $GrupaKlienta['text'] . '<br />';
                                }               
                                unset($TablicaGrupKlientow);
                                ?>
                            </td>
                        </tr>
                    </table> 
                    
                    <div class="ostrzezenie" style="margin:0px 15px 15px 0px">Jeżeli nie zostanie wybrana żadna grupa klientów to gratis będzie dostępny dla wszystkich klientów.</div>
                
                    <p>
                        <label class="required">Dostępny od kwoty:</label>           
                        <input class="toolTip" onchange="anuluj_minus(this)" title="Poziom kwotowy od jakiego będzie przyznawany gratis" type="text" name="input_od" id="input_od" size="15" value="" />
                    </p>
                    
                    <p>
                        <label class="required">Dostępny do kwoty:</label>           
                        <input class="toolTip" onchange="anuluj_minus(this)" title="Poziom kwotowy do jakiego będzie przyznawany gratis" type="text" name="input_do" id="input_do" size="15" value="" />
                    </p>    

                    <p>
                      <label>Minimalna ilość produktów:</label>
                      <input class="toolTip kropkaPusta" type="text" name="ilosc" id="ilosc" value="" size="3" title="Ilość produktów w koszyku od jakiej będzie wyświetlany gratis" />
                    </p>                                                 
                    
                    <span class="maleInfo" style="margin-left:165px">Jeżeli zostaną wybrane dodatkowe warunki dostępności gratisu (kategoria, producent, produkt) to ilość produktów będzie obliczana dla n/w warunków.</span>
                                                
                    <p>
                      <label>Dostępny tylko dla:</label>
                      <input type="radio" value="kategoria" name="warunek" onclick="warun('kategorie')" class="toolTipTop" title="Gratis będzie dostępny tylko jeżeli w koszyku będą produkty z określnych kategorii" checked="checked" /> wybranych kategorii
                      <input type="radio" value="producent" name="warunek" onclick="warun('producenci')" class="toolTipTop" title="Gratis będzie dostępny tylko jeżeli w koszyku będą produkty z określnych producentów" /> wybranych producentów
                      <input type="radio" value="produkt" name="warunek" onclick="warun('produkty')" class="toolTipTop" title="Gratis będzie dostępny tylko jeżeli w koszyku będą określone produkty" /> wybranych produktów
                    </p>                        
                    
                    <div id="kategorie">
                        <div id="drzewo_kategorii" style="margin-left:165px;width:550px;margin-bottom:0px;">
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
                                        <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kateg[]" /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                                        <td class="rgp" '.(($podkategorie) ? 'id="imgp_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat_gratisy(\''.$tablica_kat[$w]['id'].'\')" />' : '').'</td>
                                      </tr>
                                      '.(($podkategorie) ? '<tr><td colspan="2"><div id="pp_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                            }
                            if ( count($tablica_kat) == 0 ) {
                                 echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
                            }                                                                
                            echo '</table>';
                            unset($podkategorie); 
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

                    <span class="ostrzezenie" style="margin:10px 0px 5px 165px">
                        Jeżeli nie zostanie wybrana żadna kategoria, producent czy produkt - gratis będzie dostępny tylko w oparciu o wartość kwotową koszyka.
                    </span>                 

                </div>
                
                </div>

            </div>

            <div class="przyciski_dolne">
              <?php
              if ( count($tablica_kat) > 0 ) {
              ?>
              <input type="submit" class="przyciskNon" value="Zapisz dane" />
              <?php
              }
              ?>
              <button type="button" class="przyciskNon" onclick="cofnij('gratisy','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','gratisy');">Powrót</button>   
            </div>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
