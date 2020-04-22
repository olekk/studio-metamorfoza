<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $glowne_id = $filtr->process($_POST['id_wybrany_produkt']);
        $saPodobne = false;
        //
        if (isset($_POST['id_produktow'])) {
            if (count($_POST['id_produktow']) > 0) {
                //                
                foreach ($_POST['id_produktow'] as $pole) {
                    //
                    $pola = array(array('pop_products_id_master',$glowne_id),
                                  array('pop_products_id_slave',$pole));
                    //	
                    $sql = $db->insert_query('products_options_products', $pola);
                    $saPodobne = true;
                    //
                    unset($pola);  
                }
                //
            }
        }                
        
        if (isset($_POST['id_produktow'])) {
            if (count($_POST['id_produktow']) > 0) {
                //
                foreach ($_POST['krzyzowo'] as $pole) {
                    //
                    // sprawdza czy juz nie ma takiego rekordu
                    $zapytanie = "select distinct * from products_options_products where pop_products_id_master = '".$pole."' and pop_products_id_slave = '".$glowne_id."'";
                    $sqls = $db->open_query($zapytanie);  
                    //
                    if ((int)$db->ile_rekordow($sqls) == 0) {
                        //
                        $pola = array(array('pop_products_id_master',$pole),
                                      array('pop_products_id_slave',$glowne_id));
                        //	
                        $sql = $db->insert_query('products_options_products', $pola);
                        //
                        unset($pola);
                        //
                    }
                    //
                    $db->close_query($sqls);
                    unset($zapytanie);            
                }        
                //
            }
        } 
        
        if ($saPodobne == false) {
            Funkcje::PrzekierowanieURL('podobne.php');
          } else {
            Funkcje::PrzekierowanieURL('podobne.php?id_poz='.$glowne_id);
        }        
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">

          <form action="podobne/podobne_dodaj.php" method="post" id="podobneForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Dodawanie danych</div>
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" id="rodzaj_modulu" value="podobne" />
                
                <table>
                    <tr>
                        <td style="vertical-align:top" id="drzewo_podobne">

                            <?php
                            $plik = 'podobne.php';
                            if ( isset($_SESSION['filtry'][$plik]['kategoria_id']) ) {
                                 $_GET['kategoria_id'] = $_SESSION['filtry'][$plik]['kategoria_id'];
                            }
                            unset($plik);
                            ?>                        

                            <?php if (!isset($_GET['kategoria_id'])) { ?>
                
                            <p style="font-weight:bold;height:30px;">
                            Wyszukaj produkt lub wybierz kategorię z której <br />chcesz wybrać produkt do produktów podobnych
                            </p>
                            
                            <div style="margin-left:10px;margin-top:7px;" id="fraza">
                                <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" class="toolTipTopText" title="Wpisz nazwę produktu lub kod producenta" /></div> <span title="Wyszukaj produkt" onclick="fraza_produkty()" ></span>
                            </div>                                 
                            
                            <div id="drzewo" style="margin-left:10px;margin-top:7px;width:300px;">
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
                                unset($tablica_kat,$podkategorie);
                                ?> 
                            </div>

                            <?php } ?>                            
                            
                        </td><td style="vertical-align:top">                               

                            <div id="wynik_produktow_podobne" style="display:none"></div>     

                            <div id="formi" style="display:none">
                            
                                <div id="wybrany_produkt"></div>
                                
                                <input type="hidden" value="," id="jakie_id" />
                                
                                <div id="wybrane_produkty"></div>
                                
                                <div id="lista_do_wyboru"></div>

                            </div>
                            
                        </td>
                        
                    </tr>
                </table>                            
                            
            </div>

            <div class="przyciski_dolne">
              <input type="submit" class="przyciskNon" value="Zapisz dane" id="ButZapis" style="display:none" />
              <button type="button" class="przyciskNon" onclick="cofnij('podobne','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','podobne');">Powrót</button>   
            </div>

            <?php if (isset($_GET['kategoria_id']) && (int)$_GET['kategoria_id'] > 0) { ?>
            
            <script type="text/javascript">
            //<![CDATA[            
            podkat_produkty(<?php echo (int)$_GET['kategoria_id']; ?>);
            //]]>
            </script>       

            <?php } ?>            

          </div>                      
          </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
