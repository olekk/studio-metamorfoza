<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if ( !isset($_GET['zakladka']) ) {
       $_GET['zakladka'] = '0';
    }

    if ( ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) || ( isset($_POST['id_poz']) && (int)$_POST['id_poz'] > 0 ) ) {

        if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {
          $zamowienie = new Zamowienie((int)$_GET['id_poz']);
        } elseif ( isset($_POST['id']) && $_POST['id'] != '' ) {
          $zamowienie = new Zamowienie((int)$_POST['id']);
        }
        
        $i18n = new Translator($db, $zamowienie->klient['jezyk']);
        
    } else {
    
        $_GET['id_poz'] = 0;   
    
    }
        
    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $pozycje = Array();
      $zapytanie_pozycje = "SELECT class, sort_order
                            FROM orders_total
                            WHERE orders_id = '" . (int)$_POST["id"]. "' ORDER BY sort_order DESC";
                            
      $sql_pozycje = $db->open_query($zapytanie_pozycje);
      while ($pozycje_wiersz = $sql_pozycje->fetch_assoc()) {
        $pozycje[] = $pozycje_wiersz['class'];
        if ( $pozycje_wiersz['class'] == 'ot_total' ) {
          $sortowanie = $pozycje_wiersz['sort_order'];
        }
        if ( $pozycje_wiersz['class'] == 'ot_shipping' ) {
          $sortowanie = $pozycje_wiersz['sort_order'];
        }
      }
      $sortowanie--;

      if ( isset($_POST['kasuj']) ) {
        foreach ( $_POST['kasuj'] as $skasuj ) {
          $db->delete_query('orders_total' , " orders_id = '".(int)$_POST["id"]."' AND class = '".$skasuj."'");  
        }
      }

      foreach ( $_POST as $klucz => $wartosci ) {

        if ( is_array($wartosci) && ( !isset($_POST['kasuj']) || $wartosci != $_POST['kasuj'] ) ) {

          if ( in_array($wartosci['klasa'], $pozycje) ) {

              $pola = array(
                      array('title',$filtr->process($wartosci['tytul'])),
                      array('text',$waluty->FormatujCene($filtr->process($wartosci['wartosc']), false, $_POST['waluta'])),
                      array('value',$filtr->process($wartosci['wartosc'])));
                      
              if ( isset($_POST[$klucz . '_vat']) ) {
                  //
                  $stawka_vat = explode('|', $filtr->process($_POST[$klucz . '_vat']));
                  $pola[] = array('tax',$stawka_vat[0]);
                  $pola[] = array('tax_class_id',$stawka_vat[1]);   
                  unset($stawka_vat);
                  //
              }
              
              // jezeli jest platnosc to musi miec taki sam vat jak wysylka
              if ( $klucz == 'ot_payment' ) {
                   //
                   if ( isset($_POST['ot_shipping_vat']) ) {
                      //
                      $stawka_vat = explode('|', $filtr->process($_POST['ot_shipping_vat']));
                      $pola[] = array('tax',$stawka_vat[0]);
                      $pola[] = array('tax_class_id',$stawka_vat[1]);   
                      unset($stawka_vat);
                      //
                   }                   
                   //
              }
   
              $db->update_query('orders_total' , $pola, " orders_id = '".(int)$_POST["id"]."' AND class = '".$wartosci['klasa']."'");
              unset($pola);


          } else {

            if ( $wartosci['wartosc'] >= 0 ) {
              $prefix = '1';
            } else {
              $prefix = '0';
            }
            
            $pola = array(
                    array('orders_id',(int)$_POST["id"]),
                    array('title',$filtr->process($wartosci['tytul'])),
                    array('text',$waluty->FormatujCene(number_format(abs($filtr->process($wartosci['wartosc'])),2), false, $_POST['waluta'])),
                    array('value',number_format(abs($filtr->process($wartosci['wartosc'])),2)),
                    array('prefix',$prefix),
                    array('class',$filtr->process($wartosci['klasa'])),
                    array('sort_order',$sortowanie));
                    
            if ( isset($_POST[$klucz . '_vat']) ) {
                //
                $stawka_vat = explode('|', $filtr->process($_POST[$klucz . '_vat']));
                $pola[] = array('tax',$stawka_vat[0]);
                $pola[] = array('tax_class_id',$stawka_vat[1]);   
                unset($stawka_vat);
                //
            }                    
                    
            $sortowanie--;

            $db->insert_query('orders_total' , $pola);
            unset($pola);

          }
        }
      }

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]).'');
    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    
    <div id="naglowek_cont">Edycja pozycji</div>

    <script type="text/javascript" src="javascript/podsuma.js"></script>

    <div id="cont">
          
          <?php
          $zapytanie = "SELECT 
                        * 
                        FROM orders_total
                        WHERE orders_id = '" . (int)$_GET['id_poz']. "' 
                        ORDER BY sort_order";
          $sql = $db->open_query($zapytanie);
            
          if ((int)$db->ile_rekordow($sql) > 0) {
            ?>
            
            <form action="sprzedaz/zamowienia_podsumowanie_edytuj.php" method="post" id="zamowieniaForm" class="cmxform">          

              <div class="poleForm">
                <div class="naglowek">Edycja zamówienia numer : <?php echo $_GET['id_poz']; ?></div>
                
                    <div class="pozycja_edytowana">
                        
                        <div class="info_content">
                    
                        <input type="hidden" name="akcja" value="zapisz" />
                    
                        <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                        <input type="hidden" name="zakladka" value="<?php echo $filtr->process((int)$_GET['zakladka']); ?>" />
                        
                        <div id="usuwanie"></div>

                        <table cellpadding="0" cellspacing="0" style="width:100%" id="items">
                          <tr>
                              <td class="faktura_naglowek" style="width:10px"></td>
                              <td class="faktura_naglowek" style="width:650px">Opis</td>
                              <td class="faktura_naglowek" style="width:100px">Vat</td>
                              <td class="faktura_naglowek" style="text-align:right;">Wartość <?php echo $_SESSION['waluta_zamowienia_symbol']; ?></td>
                              <td class="faktura_naglowek"></td>
                          </tr>

                          <?php
                          // echo '<td>'.$info['currency'].'</td>';
                          
                          $vat = Produkty::TablicaStawekVat('', true, true);

                          while ( $info = $sql->fetch_assoc() ) {

                            if ( $info['class'] != 'ot_total' ) {
                            
                              echo '<tr class="item-row">';
                              
                              echo '<td class="faktura_produkt">'.($info['class'] != 'ot_subtotal' ? '<div class="delete-wpr"><a class="delete" href="javascript:void(0)" title="Usuń wiersz">X</a></div>' : '').'</td>';
                              echo '<td class="faktura_produkt"><input type="text" value="'.$info['title'].'" size="100" name="'.$info['class'].'[tytul]" class="opis" /></td>';
                              echo '<td class="faktura_produkt">';
                              
                              if ( $info['class'] == 'ot_shipping' || strpos($info['class'],'ot_indywidu') > -1 ) {
                              
                                  $domyslny_vat = $vat[1];
                                  //
                                  foreach ( $vat[0] as $poz_vat ) {
                                      //
                                      $tb_tmp = explode('|', $poz_vat['id']);
                                      if ( $tb_tmp[1] == $info['tax_class_id'] ) {
                                           $domyslny_vat = $poz_vat['id'];
                                      }
                                      //
                                  }
                                  //
                                  unset($poz_vat);                         
                        
                                  echo Funkcje::RozwijaneMenu($info['class'].'_vat', $vat[0], $domyslny_vat);
                                  
                              }
                              
                              echo '</td>';
                              echo '<td class="faktura_produkt" align="right"><input type="text" value="'.( $info['prefix'] == '0' ? number_format($info['value'],2) : $info['value']).'" size="20" name="'.$info['class'].'[wartosc]" class="wartosc" style="text-align:right;'.( $info['prefix'] == '0' ? 'color:red;' : '' ).'" onchange="this.value=roundLiczba(this.value,2)" /></td>';
                              echo '<td class="faktura_produkt"><input type="hidden" class="prefix" name="'.$info['class'].'[prefix]" value="'.$info['prefix'].'" /><input class="klasa" type="hidden" name="'.$info['class'].'[klasa]" value="'.$info['class'].'" />';
                              echo '<input type="hidden" name="'.$info['class'].'[sort]" value="'.$info['sort_order'].'" /></td>';
                              
                              echo '</tr>';
                              
                            } else {
                            
                              $podsumowanie = '<tr id="razem">';
                              
                              $podsumowanie .= '<td class="faktura_produkt"></td>';
                              $podsumowanie .= '<td class="faktura_produkt"><input type="text" value="'.$info['title'].'" size="100" name="'.$info['class'].'[tytul]" class="opis" /></td>';
                              $podsumowanie .= '<td class="faktura_produkt"></td>';
                              $podsumowanie .= '<td class="faktura_produkt" align="right"><input type="text" value="'.$info['value'].'" size="20" name="'.$info['class'].'[wartosc]" id="wartosc_razem" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td>';
                              $podsumowanie .= '<td class="faktura_produkt"><input type="hidden" name="'.$info['class'].'[klasa]" value="'.$info['class'].'" />';
                              $podsumowanie .= '<input type="hidden" name="'.$info['class'].'[sort]" value="'.$info['sort_order'].'" /></td>';
                              
                              $podsumowanie .= '</tr>';

                            }
                            
                          }                          
                          ?>
                          
                          <div id="vat_ukryty" style="display:none">
                              <?php echo Funkcje::RozwijaneMenu('', $vat[0], $vat[1]); ?>
                          </div>
                          
                          <tr id="hiderow">
                            <td colspan="4" style="padding-top:10px;padding-bottom:10px;"><a id="addrow" href="javascript:void(0)" title="Dodaj nową pozycję" class="dodaj">dodaj nową pozycję</a></td>
                          </tr>
                          
                          <?php
                          unset($vat);
                          
                          echo $podsumowanie;
                          unset($podsumowanie);
                          ?>
                          
                        </table>

                        </div>
                     
                    </div>


                    <div class="przyciski_dolne">
                      <input type="hidden" name="waluta" value="<?php echo $_SESSION['waluta_zamowienia']; ?>" />
                      <input type="submit" class="przyciskNon" value="Zapisz dane" />
                      <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Get(array('x','y','jezyk')); ?>','sprzedaz');">Powrót</button>           
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
          unset($info);            
          ?>

    </div>    
    
    <?php
    include('stopka.inc.php');

}

?>
