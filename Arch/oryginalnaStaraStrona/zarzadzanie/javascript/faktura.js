function aktualizuj_wartosci_wg_netto() {
  var row            = $(this).parents('.item-row');
  var pozycja        = row.find('.vat').val().indexOf('|');
  var opis_vat       = row.find('.vat').val().substring(pozycja+1);
  var stawka_vat     = row.find('.vat').val().substring(0,pozycja);

  var cena_brutto    = row.find('.cena_netto').val() * ( ( parseFloat(100) + parseFloat(stawka_vat) ) / 100 ) ;
  cena_brutto        = roundLiczba(cena_brutto,2);

  var wartosc_brutto = cena_brutto * row.find('.ilosc').val() ;
  wartosc_brutto     = roundLiczba(wartosc_brutto,2);

  var wartosc_vat    = wartosc_brutto * ( stawka_vat / ( parseFloat(100) + parseFloat(stawka_vat) ) ) ;
  wartosc_vat        = roundLiczba(wartosc_vat,2);

  var wartosc_netto  = wartosc_brutto - wartosc_vat ;
  wartosc_netto      = roundLiczba(wartosc_netto,2);

  isNaN(cena_brutto) ? row.find('.cena_brutto').html("N/A") : row.find('.cena_brutto').val(cena_brutto);
  isNaN(wartosc_netto) ? row.find('.wartosc_netto').html("N/A") : row.find('.wartosc_netto').val(wartosc_netto);
  isNaN(wartosc_brutto) ? row.find('.wartosc_brutto').html("N/A") : row.find('.wartosc_brutto').val(wartosc_brutto);
  isNaN(wartosc_vat) ? row.find('.wartosc_vat').html("N/A") : row.find('.wartosc_vat').val(wartosc_vat);

  aktualizuj_calosc();
}

function aktualizuj_wartosci_wg_brutto() {
  var row            = $(this).parents('.item-row');
  var pozycja        = row.find('.vat').val().indexOf('|');
  var opis_vat       = row.find('.vat').val().substring(pozycja+1);
  var stawka_vat     = row.find('.vat').val().substring(0,pozycja);

  var cena_netto     = ( row.find('.cena_brutto').val() * 100 ) / ( parseFloat(100) + parseFloat(stawka_vat) );
  cena_netto         = roundLiczba(cena_netto,2);

  var wartosc_brutto = row.find('.cena_brutto').val() * row.find('.ilosc').val() ;
  wartosc_brutto     = roundLiczba(wartosc_brutto,2);

  var wartosc_vat    = wartosc_brutto * ( stawka_vat / ( parseFloat(100) + parseFloat(stawka_vat) ) ) ;
  wartosc_vat        = roundLiczba(wartosc_vat,2);

  var wartosc_netto  = wartosc_brutto - wartosc_vat ;
  wartosc_netto      = roundLiczba(wartosc_netto,2);

  isNaN(cena_netto) ? row.find('.cena_netto').html("N/A") : row.find('.cena_netto').val(cena_netto);
  isNaN(wartosc_netto) ? row.find('.wartosc_netto').html("N/A") : row.find('.wartosc_netto').val(wartosc_netto);
  isNaN(wartosc_brutto) ? row.find('.wartosc_brutto').html("N/A") : row.find('.wartosc_brutto').val(wartosc_brutto);
  isNaN(wartosc_vat) ? row.find('.wartosc_vat').html("N/A") : row.find('.wartosc_vat').val(wartosc_vat);

  aktualizuj_calosc();
}

function bind() {
  $(".ilosc").change(aktualizuj_wartosci_wg_netto);
  $(".cena_netto").change(aktualizuj_wartosci_wg_netto);
  $(".vat").change(aktualizuj_wartosci_wg_netto);
  $(".cena_brutto").change(aktualizuj_wartosci_wg_brutto);
}