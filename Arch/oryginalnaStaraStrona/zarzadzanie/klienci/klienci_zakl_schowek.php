<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {

    $zapytanie_schowek = "SELECT cb.products_id, cb.customers_id, cb.customers_wishlist_date_added, p.products_status, p.products_image, pd.products_name FROM customers_wishlist cb LEFT JOIN products p ON p.products_id = cb.products_id LEFT JOIN products_description pd ON cb.products_id = pd.products_id AND pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' WHERE cb.customers_id = '" . (int)$_GET['id_klienta'] . "'";
    $sql_schowek = $db->open_query($zapytanie_schowek);

    if ((int)$db->ile_rekordow($sql_schowek) > 0) {

      ?>
      <div class="obramowanie_tabeli">
      
        <table class="listing_tbl">
        
          <tr class="div_naglowek">
            <td>Id</td>
            <td>Foto</td>
            <td>Nazwa produktu</td>
            <td>Status</td>
            <td>Dodano</td>
            <td>&nbsp;</td>
          </tr>
          
          <?php while ($info_schowek = $sql_schowek->fetch_assoc()) {
            $tgm = Funkcje::pokazObrazek($info_schowek['products_image'], $info_schowek['products_name'], '40', '40');
            $zmienne_do_przekazania = '?id_poz='.$filtr->process((int)$_GET['id_klienta']).'&product_id='.$info_schowek['products_id'].'&zakladka=3'; 

            ?>
            <tr class="pozycja_off">
              <?php
              // czy produkt ma cechy
              $CechaPrd = Produkty::CechyProduktuPoId($info_schowek['products_id']);
              $JakieCechy = '';
              if (count($CechaPrd) > 0) {
                  //
                  for ($a = 0, $ca = count($CechaPrd); $a < $ca; $a++) {
                      $JakieCechy .= '<div class="wgl_cecha">' . $CechaPrd[$a]['nazwa_cechy'] . ': <b>' . $CechaPrd[$a]['wartosc_cechy'] . '</b></div>';
                  }
                  //
              }
              ?>                                 
              <td><?php echo Produkty::IdProduktuCech($info_schowek['products_id']); ?></td>
              <td><?php echo $tgm; ?></td>
              <td class="listPrd" style="text-align:left"><?php echo '<a href="produkty/produkty_edytuj.php?id_poz=' . Produkty::IdProduktuCech($info_schowek['products_id']) . '"><b>' . $info_schowek['products_name']  . '</b></a>' . $JakieCechy; ?></td>
              <td>
                  <?php
                  if ($info_schowek['products_status'] == '1') { $obraz = '<img class="chmurka" src="obrazki/aktywny_on.png" alt="Ten produkt jest aktywny" title="Ten produkt jest aktywny" />'; } else { $obraz = '<img class="chmurka" src="obrazki/aktywny_off.png" alt="Ten produkt jest nieaktywny" title="Ten produkt jest nieaktywny" />'; }
                  echo $obraz;
                  unset($obraz);
                  ?>
              </td>
              <td><?php echo ((!empty($info_schowek['customers_wishlist_date_added'])) ? date('d-m-Y', strtotime($info_schowek['customers_wishlist_date_added'])) : ''); ?></td>
              <td><a href="klienci/klienci_kasuj_schowek.php<?php echo $zmienne_do_przekazania; ?>"><img class="chmurka" src="obrazki/kasuj.png" alt="Usuń tę pozycję" title="Usuń tę pozycję" /></a></td>
            </tr>
          <?php } ?>
          
        </table>
        
      </div>
      <?php

   }
}
?>