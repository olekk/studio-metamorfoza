$(document).ready(function() {

  $('input').click(function(){
    $(this).select();
  });

  $("#addrow").click(function(){

  var wiersz = $("#licznik").val();
	var wiersz_nastepny = parseFloat(wiersz) + parseFloat(1);
	
  $(".item-row:last").after('<tr class="item-row"><td class="faktura_produkt" style="text-align:center;width:50px;"><div class="delete-wpr"><a class="delete" href="javascript:void(0)" title="Usuń wiersz">X</a></div></td><td class="faktura_produkt" style="text-align:center;width:100px;"></td><td class="faktura_produkt"><textarea cols="100" rows="4" name="wiersz['+wiersz+'][adresat]"></textarea></td></tr>');

	$('#licznik').val( wiersz_nastepny );
    if ($(".deleteText").length > 1) $(".deleteText").show();
  });
  
  $('body').on('click', '.delete', function() {
    var row = $(this).parents('.item-row');
    $(this).parents('.item-row').remove();
    if ($(".deleteText").length < 2) $(".deleteText").hide();
  });
  
});
