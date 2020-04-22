<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_klienta']) && (int)$_GET['id_klienta'] > 0 && Sesje::TokenSpr()) {   

    $zapytanie_zamowienia = "SELECT o.orders_id, o.customers_id, o.payment_method, o.date_purchased, o.orders_status, o.orders_source, o.service, o.shipping_module, ot.value, ot.class, ot.text as order_total 
                              FROM orders_total ot
                              RIGHT JOIN orders o ON o.orders_id = ot.orders_id 
                              WHERE ot.class = 'ot_total' and customers_id = '" . (int)$_GET['id_klienta'] . "'
                           ORDER BY o.date_purchased desc"; 
    $sql_zamowienia = $db->open_query($zapytanie_zamowienia);

    if ((int)$db->ile_rekordow($sql_zamowienia) > 0) {

      ?>
      <div class="obramowanie_tabeli">
      
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function(){
        
            $('.zmzoom_zamowienie_klient').hover(function(event) {
               PodgladIn($(this),event,'zamowienie');
            }, function() {
               PodgladOut($(this),'zamowienie_klient');
            }); 
            
        });
        //]]>
        </script>           
      
        <table class="listing_tbl" id="lista_zamowien">
        
          <tr class="div_naglowek">
            <td>Info</td>
            <td>ID</td>
            <td>Data zamówienia</td>
            <td>Wartość</td>
            <td>Płatność</td>
            <td>Dostawa</td>
            <td>Status</td>
            <td>Typ</td>
            <td>&nbsp;</td>
          </tr>
          
          <?php while ($info_zamowienie = $sql_zamowienia->fetch_assoc()) { ?>
          
            <tr class="pozycja_off">
              <td><div id="zamowienie_<?php echo $info_zamowienie['orders_id']; ?>" class="zmzoom_zamowienie_klient"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div></td>
              <td><?php echo $info_zamowienie['orders_id']; ?></td>
              <td><?php echo date('d-m-Y H:i',strtotime($info_zamowienie['date_purchased'])); ?></td>
              <td><?php echo '<span class="infocena">' . $info_zamowienie['order_total' ]. '</span>'; ?></td>
              <td><?php echo $info_zamowienie['payment_method']; ?></td>
              <td><?php echo $info_zamowienie['shipping_module']; ?></td>
              <td><?php echo Sprzedaz::pokazNazweStatusuZamowienia($info_zamowienie['orders_status'], $_SESSION['domyslny_jezyk']['id']); ?></a></td>
              <td>
              <?php
              switch ($info_zamowienie['orders_source']) {
                case "3":
                    echo '<img src="obrazki/allegro_lapka.png" alt="Zamówienie z Allegro" title="Zamówienie z Allegro" />';
                    break;                 
                case "4":
                    echo '<img src="obrazki/raczka.png" alt="Zamówienie ręczne" title="Zamówienie ręczne" />';
                    break;             
              }               
              ?>
              </td>
              <td>
                <a href="sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo $info_zamowienie['orders_id']; ?>"><img src="obrazki/zobacz.png" alt="Szczegóły zamówienia" title="Szczegóły zamówienia" /></a> <br />
                <a href="sprzedaz/zamowienia_zamowienie_pdf.php?id_poz=<?php echo $info_zamowienie['orders_id']; ?>"><img src="obrazki/zamowienie_pdf.png" alt="Wydruk zamówienia" title="Wydruk zamówienia" /></a>
                <a href="sprzedaz/zamowienia_faktura_proforma.php?id_poz=<?php echo $info_zamowienie['orders_id']; ?>"><img src="obrazki/proforma_pdf.png" alt="Wydruk faktury proforma" title="Wydruk faktury proforma" /></a>
              </td>
            </tr>
          <?php } ?>
          
        </table>
        
      </div>
      <?php
      
    } else {
   
      ?>
      
      <span class="maleInfo">Brak zamówień dla klienta</span>
      
      <?php
      
    }
}
?>