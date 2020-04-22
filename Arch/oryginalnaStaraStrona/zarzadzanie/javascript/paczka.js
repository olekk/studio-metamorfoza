$(document).ready(function() {

  //$('input').click(function(){
  //  $(this).select();
  //});

  $("#addrow").click(function(){
    $(".item-row:last").after('<tr class="item-row"><td align="center"><div class="delete-wpr"><a class="deleteText" href="javascript:void(0)" title="Usuń wiersz">usuń</a></div></td><td class="paczka" style="padding-top:10px; padding-bottom:8px;"><input type="text" value="" size="8" name="parcel[dlugosc][]" class="kropkaPusta  required" /></td><td class="paczka"><input type="text" value="" size="8" name="parcel[szerokosc][]" class="kropkaPusta required" /></td><td class="paczka"><input type="text" value="" size="8" name="parcel[wysokosc][]" class="kropkaPusta required" /></td><td class="paczka"><input type="text" value="" size="8" name="parcel[waga][]" class="kropkaPusta required" /></td><td class="paczka"><input type="checkbox" value="1" name="parcel[niestandard][]" id="niestandard" /></td></tr>');
    if ($(".deleteText").length > 1) $(".deleteText").show();
  });
  
  $('body').on('click', '.deleteText', function() {
    var row = $(this).parents('.item-row');
    $(this).parents('.item-row').remove();
    if ($(".deleteText").length < 2) $(".deleteText").hide();
  });
  
});

