$(document).ready(function() {

  $('input').click(function(){
    $(this).select();
  });

  $("#addrow").click(function(){

  var wiersz = $("#licznik").val();
	var wiersz_nastepny = parseFloat(wiersz) + parseFloat(1);
	
  $(".item-row:last").after('<tr class="item-row"><td class="faktura_produkt" style="text-align:center;"><div class="delete-wpr"><a class="delete" href="javascript:void(0)" title="Usuń wiersz">X</a></div></td><td class="faktura_produkt" style="text-align:center;"></td><td class="faktura_produkt" style="text-align:center;"></td><td class="faktura_produkt"><textarea cols="35" rows="2" name="wiersz['+wiersz+'][adresat]"></textarea></td><td class="faktura_produkt"><textarea cols="35" rows="2" name="wiersz['+wiersz+'][adres_dostawy]"></textarea></td><td class="faktura_produkt"><input type="text" name="wiersz['+wiersz+'][wartosc]" size="10" value="" style="text-align:right;" /></td><td class="faktura_produkt"><input type="checkbox" style="border:0px" name="wiersz['+wiersz+'][rodzaj_wysylki]" value="0" /> EKON<br /><input type="checkbox" style="border:0px" name="wiersz['+wiersz+'][rodzaj_wysylki]" value="1" checked="checked" /> PRIOR</td><td class="faktura_produkt" style="text-align:center;"><input type="checkbox" style="border:0px" name="wiersz['+wiersz+'][pobranie]" value="1" /></td><td class="faktura_produkt" style="text-align:center;"><input type="checkbox" style="border:0px" name="wiersz['+wiersz+'][wartosciowa]" value="1" /></td></tr>');

	$('#licznik').val( wiersz_nastepny );
    if ($(".deleteText").length > 1) $(".deleteText").show();
  });
  
  $('body').on('click', '.delete', function() {
    var row = $(this).parents('.item-row');
    $(this).parents('.item-row').remove();
    if ($(".deleteText").length < 2) $(".deleteText").hide();
  });
  
});
