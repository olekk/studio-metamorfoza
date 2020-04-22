function aktualizuj_calosc() {

  var total_netto    = 0;
  var total_brutto   = 0;
  var total_vat      = 0;
  var ot_loyalty_discount   = 0;
  var ot_redemptions   = 0;
  var ot_discount_coupon = 0;
  var total_rabat_brutto =0;
  var rabat_calowity = 0;

  <?php
  
  for( $x = 0, $cnt = count($stawki_tablica); $x < $cnt; $x++ ) {
    echo "var total_netto".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)." = 0;\n";
    echo "var total_brutto".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)." = 0;\n";
    echo "var total_vat".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)." = 0;\n";
  }
  
  ?>

  // obliczanie wartosci brutto w pionie
  $('.wartosc_brutto').each(function(i) {
    var row = $(this).parents('.item-row');
    wartosc_brutto = $(this).val();
    if (!isNaN(wartosc_brutto)) total_brutto += Number(wartosc_brutto);

    <?php
    for( $x = 0, $cnt = count($stawki_tablica); $x < $cnt; $x++ ) {
      echo "if ( row.find('.vat').val() == '".$stawki_tablica[$x]."' ) {\n";
      echo "  if (!isNaN(wartosc_brutto)) total_brutto".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)." += Number(wartosc_brutto);\n";
      echo "}\n";
    }
    ?>

  });

  total_brutto   = roundLiczba(total_brutto,2);
  $('#total_brutto').val(total_brutto);

  ot_loyalty_discount = $('#ot_loyalty_discount').val();
  ot_redemptions      = $('#ot_redemptions').val();
  ot_discount_coupon  = $('#ot_discount_coupon').val();

  if (!isNaN(ot_loyalty_discount)) rabat_calowity += Number(ot_loyalty_discount);
  if (!isNaN(ot_redemptions)) rabat_calowity += Number(ot_redemptions);
  if (!isNaN(ot_discount_coupon)) rabat_calowity += Number(ot_discount_coupon);

  total_rabat_brutto = total_brutto - rabat_calowity;
  total_rabat_brutto = roundLiczba(total_rabat_brutto,2);
  $('#total_rabat_brutto').val(total_rabat_brutto);

  <?php
  for( $x = 0, $cnt = count($stawki_tablica); $x < $cnt; $x++ ) {
    echo " var subWartoscVat    = 0;\n";
    echo " var subWartoscNetto  = 0;\n";
    echo " var subTotalVat      = roundLiczba(total_brutto".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1).",2);\n";
    echo " var przelicznikVat   = ".substr($stawki_tablica[$x],0,strpos($stawki_tablica[$x], '|')) / ( 100 + substr($stawki_tablica[$x],0,strpos($stawki_tablica[$x], '|')) ).";\n";

    echo "subWartoscVat = roundLiczba((parseFloat(subTotalVat) * parseFloat(przelicznikVat)),2);\n";
    echo "subWartoscNetto = roundLiczba((subTotalVat - subWartoscVat),2);\n";

    echo "  $('#subtotal_netto_vat".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)."').val(subWartoscNetto);\n";
    echo "  $('#subtotal_vat".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)."').val(subWartoscVat);\n";
    echo "  $('#subtotal_brutto_vat".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)."').val(subTotalVat);\n";

    if ( $stawki_tablica[$x] != '23' ) {
        echo "if ( Number(subTotalVat) > 0 ) {\n";
        echo "  $('#razem".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)."').show();\n";
        echo "} else {\n";
        echo "  $('#razem".substr($stawki_tablica[$x],strpos($stawki_tablica[$x], '|')+1)."').hide();\n";
        echo "}\n";
    }

  }
  ?>

}

function aktualizuj_kolejnosc() {
  var licznik = 1;

  $('.lp').each(function(i){
    var row = $(this).parents('.item-row');
    row.find('.lp').val(licznik);
    licznik++;
  });

}

<?php 
$select_jm  = Funkcje::RozwijaneMenu('jm[]', $tablica_jm, '4'); 
$select_vat = Funkcje::RozwijaneMenu('vat[]', $tablica_vat, $domyslny_vat, 'class="vat"'); 
?>

$(document).ready(function() {

  //$('input').click(function(){
    //$(this).select();
  //});

  $("#addrow").click(function(){
    $(".item-row:last").after('<tr class="item-row"><td class="faktura_produkt"><div class="delete-wpr"><a class="delete" href="javascript:void(0)" title="Kasuj wiersz">X</a></div><input type="hidden" value="" name="lp[]" class="lp" /></td><td class="faktura_produkt"><textarea cols="30" rows="3" name="nazwa[]"></textarea></td><td class="faktura_produkt"><input type="text" value="" size="8" name="pkwiu[]" class="pkwiu" style="text-align:right;" /></td><td class="faktura_produkt"><?php echo $select_jm; ?></td><td class="faktura_produkt" align="right"><input type="text" value="1.00" size="5" name="ilosc[]" class="ilosc" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td><td class="faktura_produkt" align="right"><input type="text" value="0.00" size="8" name="cena_brutto[]" class="cena_brutto" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td><td class="faktura_produkt" align="right"><input type="text" value="0.00" size="8" name="cena_netto[]" class="cena_netto" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /></td><td class="faktura_produkt" align="right"><input type="text" value="0.00" size="10" name="wartosc_netto[]" class="wartosc_netto readonly" style="text-align:right;" readonly="readonly"></td><td class="faktura_produkt" align="right"><?php echo $select_vat; ?></td><td class="faktura_produkt" align="right"><input type="text" value="0.00" size="10" name="wartosc_vat[]" class="wartosc_vat readonly" style="text-align:right;" readonly="readonly"></td><td class="faktura_produkt" align="right"><input type="text" value="0.00" size="10" name="wartosc_brutto[]" class="wartosc_brutto readonly" style="text-align:right;" readonly="readonly"></td></tr>');
    aktualizuj_kolejnosc();
    if ($(".delete").length > 0) $(".delete").show();
    bind();
  });
  
  bind();
  
  $('body').on('click', '.delete', function() {
    $(this).parents('.item-row').remove();
    aktualizuj_calosc();
    aktualizuj_kolejnosc();
    if ($(".delete").length < 2) $(".delete").hide();
  });
  
});