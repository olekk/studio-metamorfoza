<?php
if ( isset($toks) ) {
?>

    <div id="zakl_id_2" style="display:none;" class="pozycja_edytowana">

      <?php
      if ( count($zamowienie->produkty) > 0) {
      ?>
      
      <div class="obramowanie_tabeli">
      
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function(){
        
            $('.zmzoom_produkt').hover(function(event) {
               PodgladIn($(this),event,'produkt');
            }, function() {
               PodgladOut($(this),'produkt');
            });

        });
        //]]>
        </script>         
      
        <table class="listing_tbl" id="infoTblProdukty">
          <tr class="div_naglowek">
            <td>Info</td>
            <td>ID</td>
            <td>Foto</td>
            <td>Nazwa</td>
            <td>Cena netto</td>
            <td>Podatek</td>
            <td>Cena brutto</td>
            <td>Ilość</td>
            <td>Wartość brutto</td>
            <td></td>
          </tr>
          <?php 

          //for ($i=0, $n=count($zamowienie->produkty); $i<$n; $i++) {
          foreach ( $zamowienie->produkty as $produkt ) {

            $wyswietl_cechy = '';

            if (isset($produkt['attributes']) && (count($produkt['attributes']) > 0)) {

              foreach ($produkt['attributes'] as $cecha ) {
                $wyswietl_cechy .= '<span class="male_nr_kat">'.$cecha['cecha'] . ': <b>' . $cecha['wartosc'] . '</b></span>';
              }
            }
            
            // czyszczenie z &nbsp; i zbyt dlugiej nazwy
            $produkt['nazwa'] = Funkcje::PodzielNazwe($produkt['nazwa']);
            $produkt['model'] = Funkcje::PodzielNazwe($produkt['model']);

            ?>
            <tr class="pozycja_off">
              <td style="width:30px;">
                  <?php if ( $produkt['id_produktu'] > 0 ) { ?>
                  <div id="produkt<?php echo rand(1,999); ?>_<?php echo $produkt['id_produktu']; ?>" class="zmzoom_produkt"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>
                  <?php } ?>
              </td>
              <td><?php echo (($produkt['id_produktu'] > 0) ? $produkt['id_produktu'] : '-'); ?></td>
              <td><?php echo Funkcje::pokazObrazek($produkt['zdjecie'], $produkt['nazwa'], '40', '40'); ?></td>
              <td style="text-align:left">
              <?php 
              if ( $produkt['id_produktu'] > 0 ) {
                   echo '<a class="LinkProduktu blank" href="' . Seo::link_SEO( $produkt['nazwa'], $produkt['id_produktu'], 'produkt', '', false ) . '">'.$produkt['nazwa'].'</a>';
                 } else {
                   echo '<span class="LinkProduktu">'.$produkt['nazwa'].'</span>';
              }
              if (trim($produkt['model']) != '') {
                echo '<span class="male_nr_kat">Nr kat: <b>'.$produkt['model'].'</b></span>';
              }
              // pobieranie danych o producencie
              if (trim($produkt['producent']) != '') {                      
                  //
                  echo '<span class="male_producent">Producent: <b>'.$produkt['producent'].'</b></span>';
                  //
              }                  
              // wyswietlenie cech produktu
              if (!empty($wyswietl_cechy)) {                     
                  //
                  echo $wyswietl_cechy;
                  //
              }
              // komentarz do produktu
              if (!empty($produkt['komentarz'])) {
                echo '<span class="male_nr_kat">Komentarz: <b>'.$produkt['komentarz'].'</b></span>';
              }       
              // dodatkowe pola opisowe
              if (!empty($produkt['pola_txt'])) {
                //
                $poleTxt = Funkcje::serialCiag($produkt['pola_txt']);
                if ( count($poleTxt) > 0 ) {
                    foreach ( $poleTxt as $wartoscTxt ) {
                        // jezeli pole to plik
                        if ( $wartoscTxt['typ'] == 'plik' ) {
                            echo '<span class="male_nr_kat">' . $wartoscTxt['nazwa'] . ': <a class="blank" href="' . ADRES_URL_SKLEPU . '/wgrywanie/' . $wartoscTxt['tekst'] . '"><b>załączony plik</b></a></span>';
                          } else {
                            echo '<span class="male_nr_kat">' . $wartoscTxt['nazwa'] . ': <b>' . $wartoscTxt['tekst'] . '</b></span>';
                        }                                          
                    }
                }
                unset($poleTxt);
                //
              }                                           

              ?>
              </td>
              <td style="white-space: nowrap"><?php echo $waluty->FormatujCene($produkt['cena_koncowa_netto'], false, $zamowienie->info['waluta']); ?></td>
              <td><?php echo (($produkt['tax_info'] != $produkt['tax']) ? $produkt['tax_info'] . ' - ' . $produkt['tax'] . '%' : $produkt['tax'] . '%'); ?> </td>
              <td style="white-space: nowrap"><?php echo $waluty->FormatujCene($produkt['cena_koncowa_brutto'], false, $zamowienie->info['waluta']); ?></td>
              <td><?php echo $produkt['ilosc']; ?></td>
              <td style="white-space: nowrap"><?php echo $waluty->FormatujCene($produkt['cena_koncowa_brutto'] * $produkt['ilosc'], false, $zamowienie->info['waluta']); ?></td>

              <td class="rg_right">
                <a href="sprzedaz/zamowienia_produkt_edytuj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;produkt_id=<?php echo (int)$produkt['orders_products_id'];?>&amp;zakladka=2"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>
                <a href="sprzedaz/zamowienia_produkt_usun.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;produkt_id=<?php echo (int)$produkt['orders_products_id'];?>&amp;zakladka=2"><img class="toolTipTop" src="obrazki/kasuj.png" alt="Usuń produkt" title="Usuń produkt" /></a>
              </td>
            </tr>
          <?php } ?>
        </table>
        
      </div>
      
      <?php } ?>

      <div id="dodaj_pozycje" style="padding:10px">
          <div>
              <a class="dodaj" href="sprzedaz/zamowienia_szczegoly_produkt_dodaj.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&amp;zakladka=2">dodaj nową pozycję</a>
          </div>
      </div>      
      
    </div>
    
<?php
}
?>