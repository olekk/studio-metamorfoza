function aktualizuj() {

  var total = 0;

  $('.wartosc').each(function(i){

	var row = $(this).parents('.item-row');
  wartosc = row.find('.wartosc').val();

	if ( row.find('.prefix').val() == '1') {
		if (!isNaN(wartosc)) total += Number(wartosc);
	} else if ( row.find('.prefix').val() == '0') {
		if (!isNaN(wartosc)) total -= Number(wartosc);
    } else if ( row.find('.prefix').val() == '9' ) {
		if (!isNaN(wartosc)) total = Number(total);
    }
  });

  total = roundLiczba(total,2);

  $('#wartosc_razem').val(total);
}

function bind() {
  $(".wartosc").change(aktualizuj);
}

$(document).ready(function() {

  $('input').click(function(){
    $(this).select();
  });

  $("#addrow").click(function(){
    var liczba = Math.floor(Math.random()*10);
    $(".item-row:last").after('<tr class="item-row"><td class="faktura_produkt"><div class="delete-wpr"><a class="delete" href="javascript:void(0)" title="Kasuj wiersz">X</a></div></td><td class="faktura_produkt"><input type="text" value="" size="100" name="ot_indywidualna'+liczba+'[tytul]" class="opis" /></td><td class="faktura_produkt" id="wyborvat_' + liczba + '">-</td><td class="faktura_produkt" align="right"><input type="text" value="" size="20" name="ot_indywidualna'+liczba+'[wartosc]" class="wartosc" style="text-align:right;" onchange="this.value=roundLiczba(this.value,2)" /><input type="hidden" class="prefix" name="ot_indywidualna'+liczba+'[prefix]" value="1" /><input type="hidden" name="ot_indywidualna'+liczba+'[klasa]" value="ot_indywidualna'+liczba+'" /><input type="hidden" name="ot_indywidualna'+liczba+'[sort]" value="" /></td><td class="faktura_produkt"></td></tr>');
    if ($(".delete").length > 0) $(".delete").show();
    // dodaje select z vat
    $('#wyborvat_' + liczba).html( $('#vat_ukryty').html() );
    $('#wyborvat_' + liczba).find('select').attr('name', 'ot_indywidualna' + liczba + '_vat');
    //
    bind();
  });
  
  bind();
  
  $('body').on('click', '.delete', function() {
    var row = $(this).parents('.item-row');
    var klasa = row.find('.klasa').val();
    $('#usuwanie').append('<input type="hidden" name="kasuj[]" value="'+klasa+'" />');
    $(this).parents('.item-row').remove();
    aktualizuj();
    //if ($(".delete").length < 2) $(".delete").hide();
  });
  
});