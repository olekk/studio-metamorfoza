<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $pola = array(
              array('orders_id',$filtr->process($_POST["id"])),
              array('products_id',$filtr->process($_POST["produkt_id"])),
              array('products_model',$filtr->process($_POST["model"])),
              array('products_name',$filtr->process($_POST["nazwa"])),
              array('products_pkwiu',$filtr->process($_POST["pkwiu"])),
              array('products_quantity',$filtr->process($_POST["ilosc"])),
              array('products_comments',$filtr->process($_POST["komentarz"]))              
      );
      
      $stawka_vat = explode('|', $filtr->process($_POST['vat']));
      $pola[] = array('products_tax',$stawka_vat[0]);
      $pola[] = array('products_tax_class_id',$stawka_vat[1]);   
      unset($stawka_vat);
      //			

      $sql = $db->insert_query('orders_products' , $pola);

      $id_dodanej_pozycji = $db->last_id_query();

      unset($pola);

      $wartosc_cech_netto = 0;
      $wartosc_cech_brutto = 0;
      $kombinacja_cech = array();

      if ( isset($_POST['cecha']) && count($_POST['cecha']) > 0 ) {

        foreach ( $_POST['cecha'] as $key ) {
        
          $tablica_wartosc_cechy = explode( ';', $key );
          $prefix  = $_POST['cecha_prefix'][$tablica_wartosc_cechy['1']];
          $cena_cechy_netto = $_POST['cecha_cena_netto'][$tablica_wartosc_cechy['1']];
          $cena_cechy_brutto = $_POST['cecha_cena_brutto'][$tablica_wartosc_cechy['1']];
          $kombinacja_cech[ $tablica_wartosc_cechy['1'] ] = $tablica_wartosc_cechy['1'].'-'.$tablica_wartosc_cechy['0'];

          $zapytanie_nazwa_cechy = "SELECT * FROM products_options
                                        WHERE products_options_id = '" . (int)$tablica_wartosc_cechy['1']. "' 
                                        AND language_id =  '1'";
                                        
          $sql_nazwa_cechy = $db->open_query($zapytanie_nazwa_cechy);
          unset($zapytanie_nazwa_cechy);

          if ((int)$db->ile_rekordow($sql_nazwa_cechy) > 0) {
            $info_nazwa_cechy = $sql_nazwa_cechy->fetch_assoc();
            $nazwa_cechy = $info_nazwa_cechy['products_options_name'];
          }

          $zapytanie_wartosc_cechy = "SELECT * FROM products_options_values
                                        WHERE products_options_values_id = '" . (int)$tablica_wartosc_cechy['0']. "' 
                                        AND language_id =  '1'";
                                        
          $sql_wartosc_cechy = $db->open_query($zapytanie_wartosc_cechy);
          unset($zapytanie_wartosc_cechy);

          if ((int)$db->ile_rekordow($sql_wartosc_cechy) > 0) {
            $info_wartosc_cechy = $sql_wartosc_cechy->fetch_assoc();
            $nazwa_wartosci_cechy = $info_wartosc_cechy['products_options_values_name'];
            unset($info_wartosc_cechy);
          }

          $pola = array(
                  array('orders_id',$filtr->process($_POST["id"])),
                  array('orders_products_id',$id_dodanej_pozycji),
                  array('products_options',$nazwa_cechy),
                  array('products_options_id',$tablica_wartosc_cechy['1']),
                  array('products_options_values',$nazwa_wartosci_cechy),
                  array('products_options_values_id',$tablica_wartosc_cechy['0']),
                  array('options_values_price',$cena_cechy_netto),
                  array('options_values_tax',($cena_cechy_brutto-$cena_cechy_netto)),
                  array('options_values_price_tax',$cena_cechy_brutto),
                  array('price_prefix',$prefix)
          );

          $sql = $db->insert_query('orders_products_attributes' , $pola);
          unset($pola, $tablica_wartosc_cechy, $prefix, $cena_cechy_netto, $cena_cechy_brutto, $cena_cechy_netto);

        }
      }
      
      ksort($kombinacja_cech);
      $kombinacja_cech = implode(',', $kombinacja_cech);

      // szuka czy dana kombinacja cech nie ma unikalnego nr katalogowego
      $nr_katalogowy_cechy = $filtr->process($_POST["model"]);
      $zapytanie_cechy = "SELECT products_stock_model FROM products_stock WHERE products_stock_attributes = '" . $kombinacja_cech . "' and products_id = '" . $filtr->process($_POST["produkt_id"]) . "'";
      $sql_nr_kat_cechy = $db->open_query($zapytanie_cechy);
      //
      if ((int)$db->ile_rekordow($sql_nr_kat_cechy) > 0) {
        $info_nr_kat_cechy = $sql_nr_kat_cechy->fetch_assoc();
        //
        if (!empty($info_nr_kat_cechy['products_stock_model'])) {
            $nr_katalogowy_cechy = $info_nr_kat_cechy['products_stock_model'];
        }
        //
        unset($info_nr_kat_cechy);
      }   
      //
      $db->close_query($sql_nr_kat_cechy);      
      //      

      $pola = array(
              array('products_price',(($_POST['ma_cechy'] == 'tak') ? $filtr->process($_POST["cena_1_podstawa"]) : $filtr->process($_POST["cena_1"]))),
              array('products_price_tax',(($_POST['ma_cechy'] == 'tak') ? $filtr->process($_POST["brut_1_podstawa"]) : $filtr->process($_POST["brut_1"]))),      
              array('final_price',$filtr->process($_POST["cena_1"])),
              array('final_price_tax',$filtr->process($_POST["brut_1"])),
              array('products_stock_attributes',$kombinacja_cech),
              array('products_model',$nr_katalogowy_cechy)
      );
      
      unset($nr_katalogowy_cechy, $zapytanie_cechy);

      //			

      $db->update_query('orders_products' , $pola, " orders_products_id = '".(int)$id_dodanej_pozycji."'");	
      unset($pola);

      // aktualizacja ilosci sprzedanych produktow
      $zapytanie_sprzedane = "SELECT products_ordered FROM products WHERE products_id = '".(int)$_POST['id_produktu_org']."'";
      $sql_sprzedane = $db->open_query($zapytanie_sprzedane);
      $sprzedane = $sql_sprzedane->fetch_assoc();

      $sprzedane_akt = $sprzedane['products_ordered'] + $_POST['ilosc'];

      $pola = array(
              array('products_ordered',$sprzedane_akt));

      $db->update_query('products' , $pola, "products_id = '" . (int)$_POST["produkt_id"] . "'");

      $db->close_query($sql_sprzedane);         
      unset($zapytanie_sprzedane, $sprzedane, $pola, $sprzedane_akt);

      Sprzedaz::PodsumowanieZamowieniaAktualizuj($_POST["id"], $_POST["waluta"]);

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]).'');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Dodawanie pozycji</div>
    <div id="cont">
    
    <?php
    if ( !isset($_GET['id_poz']) ) {
         $_GET['id_poz'] = 0;
    }     
    if ( !isset($_GET['zakladka']) ) {
         $_GET['zakladka'] = '0';
    }      
    
    if ( (int)$_GET['id_poz'] == 0 ) {
    ?>
       
      <div class="poleForm"><div class="naglowek">Dodawanie produktu</div>
        <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
      </div>      
      
    <?php
    } else {
    ?>      

      <div class="poleForm">
      
        <div class="naglowek">Dodawanie produktu</div>
        
        <form action="sprzedaz/zamowienia_szczegoly_produkt_dodaj.php" method="post" id="zamowieniaForm" class="cmxform">  
        
        <div class="pozycja_edytowana">
        
            <input type="hidden" name="akcja" value="zapisz" />
            
            <input type="hidden" id="rodzaj_modulu" value="zamowienie_produkt" />
            <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
            <input type="hidden" name="zakladka" value="<?php echo $filtr->process((int)$_GET['zakladka']); ?>" />

            <table>
                <tr>
                    <td style="vertical-align:top" id="drzewo_zamowienie_produkt">                   
            
                        <p style="font-weight:bold;height:30px;">
                        Wyszukaj produkt lub wybierz kategorię z której<br /> chcesz wybrać produkt do zamówienia
                        </p>
                        
                        <div style="margin-left:10px;margin-top:7px;" id="fraza">
                            <div>Wyszukaj produkt: <input type="text" size="15" value="" id="szukany" class="toolTipTopText" title="Wpisz nazwę produktu lub kod producenta" /></div> <span title="Wyszukaj produkt" onclick="fraza_produkty()"></span>
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
                            echo '</table>';
                            unset($tablica_kat,$podkategorie);
                            ?> 
                        </div> 
                        
                    </td>
                    
                    <td style="vertical-align:top">                               

                        <div id="wynik_produktow_zamowienie_produkt" style="display:none"></div>     

                        <div id="formi" style="display:none">
                        
                            <div id="wybrany_produkt"></div>
                            
                        </div>
                        
                    </td>
                    
                </tr>
            </table>                            
                        
        </div>

        <div class="przyciski_dolne" style="margin-left:2px">
          <input type="submit" id="ButZapis" class="przyciskNon" value="Zapisz dane" />
          <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>   
        </div>

        </form>  

        <span id="dodajProdukt" onclick="produkt_akcja(0,'zamowienie_produkt')">dodaj produkt z poza bazy sklepu</span>

      </div>     
      
    <?php } ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
