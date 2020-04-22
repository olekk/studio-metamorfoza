<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <style type="text/css">
    .info_tab_content label { width:200px; padding-left:0px; }
    .info_tab_content label.error { display:block; margin-left: 170px; }

    .info_content label { width:200px; padding-left:0px; }
    .info_content label.error { display:block; margin-left: 0px; }
    </style>

    <div id="naglowek_cont">Szczegóły aukcji</div>
    <div id="cont">

      <?php
    
      if ( !isset($_GET['id_poz']) ) {
           $_GET['id_poz'] = 0;
      }    
    
      $zapytanie = "SELECT * FROM allegro_auctions WHERE allegro_id ='" .$filtr->process((int)$_GET['id_poz']). "'";

      $sql = $db->open_query($zapytanie);

      if ((int)$db->ile_rekordow($sql) > 0) {
        $info = $sql->fetch_assoc();

        ?>
        <div class="info_content">
        
          <div class="obramowanie_tabeliSpr" style="margin-top:10px;">
          
            <table class="listing_tbl">
            
              <tr class="div_naglowek">
                <td align="left" colspan="2" style="padding-left:10px;">Aukcja numer: <?php echo $info['auction_id']; ?></td>
              </tr>

              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Tytuł aukcji:</td>
                <td><?php echo $info['products_name']; ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Początkowa ilość produktów:</td>
                <td><?php echo $info['products_quantity']; ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Status aukcji:</td>
                <td>
                  <?php 
                  if ( $info['auction_status'] == '1' ) {
                    echo '<span class="zielony">TRWA</span>';
                  } elseif ( $info['auction_status'] == '2' ) {
                    echo '<span class="czerwony">ZAKOŃCZONA</span>';
                  } elseif ( $info['auction_status'] == '3' ) {
                    echo '<span class="czerwony">ZAKOŃCZONA PRZED CZASEM</span>';
                  } elseif ( $info['auction_status'] == '-1' ) {
                    echo '<span class="czerwony">OFERTA CZEKA NA WYSTAWIENIE</span>';
                  }
                  ?>
                </td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Data rozpoczęcia (sklep):</td>
                <td><?php echo date('d-m-Y H:i:s',strtotime($info['products_date_start'])); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Data zakończenia (sklep):</td>
                <td><?php echo date('d-m-Y H:i:s',strtotime($info['products_date_end'])); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Data rozpoczęcia (allegro):</td>
                <td><?php echo date('d-m-Y H:i:s',strtotime($info['auction_date_start'])); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Data zakończenia (allegro):</td>
                <td><?php echo ( strtotime($info['auction_date_end']) > 0 ? date('d-m-Y H:i:s',strtotime($info['auction_date_end'])) : 'do wyczerpania' ); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Wystawiający:</td>
                <td><?php echo $info['auction_seller']; ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Pozostała ilość przedmiotów:</td>
                <td><?php echo $info['auction_quantity']; ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Format aukcji:</td>
                <td>
                  <?php 
                  if ( $info['auction_buy_now'] == '1' ) {
                    echo 'Kup teraz';
                  } elseif ( $info['auction_buy_now'] == '0' ) {
                    echo 'licytacja';
                  }
                  ?>
                </td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Cena wywoławcza:</td>
                <td><?php echo $waluty->FormatujCene($info['products_start_price']); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Cena minimalna:</td>
                <td><?php echo $waluty->FormatujCene($info['products_min_price']); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Cena aktualna:</td>
                <td><?php echo $waluty->FormatujCene($info['products_now_price']); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Cena Kup teraz:</td>
                <td><?php echo $waluty->FormatujCene($info['products_buy_now_price']); ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Ilość ofert:</td>
                <td><?php echo $info['auction_bids']; ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Ilość wyświetleń:</td>
                <td><?php echo $info['auction_hits']; ?></td>
              </tr>
              
              <tr class="pozycja_offAllegro">
                <td style="width:225px;padding-left:25px">Ilość sprzedanych przedmiotów:</td>
                <td><?php echo $info['products_sold']; ?></td>
              </tr>
              
            </table>
          </div>
        </div>

        <?php
        $zapytaniea = "SELECT DISTINCT a.*, t.post_buy_form_pay_status, t.post_buy_form_it_quantity
                      FROM allegro_auctions_sold a
                      LEFT JOIN allegro_transactions t ON t.auction_id = a.auction_id AND a.buyer_id = t.buyer_id
                      WHERE a.auction_id='".$info['auction_id']."'";
        $sqla = $db->open_query($zapytaniea);

        if ( $db->ile_rekordow($sqla) > 0 ) {
          ?>
          <div class="info_content" id="aukcje_lista">
            <div class="obramowanie_tabeliSpr" style="margin-top:10px;">

              <table class="listing_tbl">
              
                <tr class="div_naglowek"> 
                  <td>Kupujący</td> 
                  <td>Konto</td> 
                  <td>Ilość produktów</td> 
                  <td>Data zakupu</td> 
                  <td>Cena</td> 
                  <td>Status<br />zakupu</td> 
                  <td>Numer<br />zamówienia</td> 
                  <td>Status<br />odwołania</td> 
                  <td>Data<br />odwołania</td> 
                  <td>Formularz<br />pozakupowy</td> 
                </tr>
                
                <?php
                while ( $infoa = $sqla->fetch_assoc() ) {
                  ?>
                  <tr class="pozycja_off">
                    <td><?php echo $infoa['buyer_name']; ?></td> 
                    <td><?php echo ( $infoa['buyer_status'] == '0' ? 'aktywne' : 'zablokowane'); ?></td> 
                    <td><?php echo round(( isset($infoa['post_buy_form_it_quantity']) ? $infoa['post_buy_form_it_quantity'] : $infoa['auction_quantity'] ),0); ?></td> 
                    <td><?php echo date('d-m-Y H:i:s',$infoa['auction_buy_date']); ?></td> 
                    <td><?php echo $waluty->FormatujCene($infoa['auction_price']); ?></td> 
                    <td>
                      <?php 
                      if ( $infoa['auction_status'] == '1' ) {
                        echo 'oferta zakończona sprzedażą';
                      } elseif ( $infoa['auction_status'] == '-1' ) {
                        echo 'oferta odwołana';
                      } elseif ( $infoa['auction_status'] == '0' ) {
                        echo 'oferta nie zakończona sprzedażą';
                      }
                      ?>
                    </td> 
                    <td><?php echo ( $infoa['orders_id'] != '0' ? $infoa['orders_id'] : '---' ); ?></td> 
                    <td>
                      <?php 
                      if ( $infoa['auction_lost_status'] == '1' ) {
                        echo 'oferta odwołana przez sprzedającego';
                      } elseif ( $infoa['auction_lost_status'] == '2' ) {
                        echo 'oferta odwołana przez administratora serwisu';
                      } elseif ( $infoa['auction_lost_status'] == '0' ) {
                        echo 'oferta nieodwołana';
                      }
                      ?>
                    </td> 
                    <td><?php echo ( $infoa['auction_lost_date'] != '' ? date('d-m-Y H:i:s',$infoa['auction_lost_date']) : '---'); ?></td> 
                    <td>
                      <?php
                      $stan_tranzakcji = '<img src="obrazki/aktywny_off.png" alt="Kupujący nie wypełnił formularza pozakupowego" title="Kupujący nie wypełnił formularza pozakupowego" />';
                      if ( $infoa['auction_postbuy_forms'] == '1' ) {
                        $stan_tranzakcji = '<img src="obrazki/aktywny_on.png" alt="Kupujący wypełnił formularz pozakupowy" title="Kupujący wypełnił formularz pozakupowy" />';
                        if ( $infoa['post_buy_form_pay_status'] == 'Anulowana' ) {
                          $stan_tranzakcji = '<img src="obrazki/uwaga.png" alt="Formularz pozakupowy został anulowany" title="Formularz pozakupowy został anulowany" />';
                        }
                      }

                      echo $stan_tranzakcji;
                      ?>
                    </td> 
                  </tr>
                  <?php
                }
                ?>
              </table>
              
            </div>
            
          </div>

          <?php
        }
        $db->close_query($sqla);
        unset($zapytaniea, $infoa);


      } else {

        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

      }
      $db->close_query($sql);
      unset($zapytanie, $info);

      ?>
      <div class="przyciski_dolne">
        <button type="button" class="przyciskNon" onclick="cofnij('allegro_aukcje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','allegro');">Powrót</button> 
      </div>            
    </div>
    <?php
    include('stopka.inc.php');    
    
} ?>
