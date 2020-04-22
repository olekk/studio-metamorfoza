function zmien_ceche() {
    var id = $("#id_cecha").val();
    $("#cech_wartosc").html('<img src="obrazki/_loader_small.gif">');
    $.get('ajax/zmien_cechy.php',
         { id: id, tok: $('#tok').val() }, function(data) { $('#cech_wartosc').css('display','none'); $('#cech_wartosc').html(data); $('#cech_wartosc').fadeIn(); });
}
function typ_cechy(rodzaj) {
    $.get('ajax/typ_cechy.php',
     { id_unikalne: $("#id_unikalne").val(), rodzaj: rodzaj, tok: $('#tok').val() }, function(data) { 
        //
        if ( rodzaj == 'cechy' ) {
             lista_cech('wyswietl','nie');
        } else {
             lista_cech('wyswietl','tak')
        }
        //
     });
}
function lista_cech(akcja, kombinacje) {
    if (akcja == undefined) {
        akcja = 'dodaj';
    }
    if (kombinacje == undefined) {
        if ( $('#rodzajCechyCecha').prop('checked') ) {
             kombinacje = 'nie';
             $('#kombinacje').show();
           } else {
             kombinacje = 'tak';
             $('#kombinacje').hide();
        }
    }    
    //
    if ( $('#rodzajCechyCecha').prop('checked') ) {
         rodzaj_cechy = 'cechy';
       } else {
         rodzaj_cechy = 'ceny';
    }
    //
    //
    $('#ekr_preloader').css('display','block');
    $("#dodaj_ceche").css('display','none');
    //
    $.ajax({ type :"post", 
             data : { id_cechy: $("#id_cecha").val(), id_wartosc: $("#id_wartosc").val(), id_unikalne: $("#id_unikalne").val(), akcja: akcja, kombinacje: kombinacje, rodzaj_cechy: rodzaj_cechy },
             url : "ajax/lista_cech.php?tok=" + $('#tok').val()
          }).done(function( data ) { 
             $('#lista_cech').css('display','none'); $('#lista_cech').html(data); $('#lista_cech').fadeIn(); $("#dodaj_ceche").css('display','block'); $('#ekr_preloader').delay(100).fadeOut('fast'); pokazChmurki(); 
                //
                $('.rozwin_foto').click(function() {
                   idnr = $(this).attr('id');
                   //
                   if ( $('#tbl_' + idnr).css('display') == 'none' ) {
                        $('#tbl_' + idnr).slideDown();
                      } else {
                        $('#tbl_' + idnr).slideUp();
                   }
                   delete idnr;
                   //
                });
                //
             }           
    );     
}    
function zapisz_obraz_cechy(id_produktu, kombinacja_cech, id) {
    var wartosc = $("#zdjecie_cechy_" + id).val();
    $("#zapis_obrazka_" + id).html('<img src="obrazki/_loader_small.gif">');
    $.post("ajax/zapisz_obrazek_cechy.php?tok=" + $('#tok').val(),
         { id_produktu: id_produktu, kombinacja_cech:kombinacja_cech, wartosc: wartosc }, function(data) { $("#zapis_obrazka_" + id).html(''); $('#tbl_foto_cechy_' + id).slideUp(); $('#foto_cechy_' + id).html(data); });
}
function usun_obraz_cechy(id_produktu, kombinacja_cech, id) {
    $("#zapis_obrazka_" + id).html('<img src="obrazki/_loader_small.gif">');
    $.post("ajax/zapisz_obrazek_cechy.php?tok=" + $('#tok').val(),
         { id_produktu: id_produktu, kombinacja_cech:kombinacja_cech, wartosc: '' }, function(data) { $("#zapis_obrazka_" + id).html(''); $('#tbl_foto_cechy_' + id).slideUp(); $('#foto_cechy_' + id).html(data); });
}
function zamien_w_cechach(wartosc) {
    var wart = $(wartosc).val();
    regexp = eval("/,/g");
    wart = format_zl( wart.replace(regexp,".") );
    if (!isNaN(wart)) {
        if (wart == 0) {
            $(wartosc).val('');
          } else {
            $(wartosc).val(wart);
        }
      } else {
        $(wartosc).val('');
    }    
}
function ajax_cecha_kwota(elem,id_prod,typ,nr_ceny,id_cech,id) {
    if (typ == 'netto') {
        var wartosc_vat = $('#vat').val();
        $('#netto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $('#brutto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_ceny_cechy.php',
            { id_prod: id_prod, id_cech: id_cech, nr_ceny: nr_ceny, id: id, cena_netto: $(elem).val(), typ: typ, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#netto_' + nr_ceny + '_' + id).html(data.netto); $('#brutto_' + nr_ceny + '_' + id).html(data.brutto); }, "json");     
    }   
    if (typ == 'brutto') {
        var wartosc_vat = $('#vat').val();
        $('#netto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $('#brutto_' + nr_ceny + '_' + id).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_ceny_cechy.php',
            { id_prod: id_prod, id_cech: id_cech, nr_ceny: nr_ceny, id: id, cena_brutto: $(elem).val(), typ: typ, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#netto_' + nr_ceny + '_' + id).html(data.netto); $('#brutto_' + nr_ceny + '_' + id).html(data.brutto); }, "json");     
    }    
}
function ajax_cecha(typ,wartosc,id_cech,rodzaj) {
    if (rodzaj == undefined) {
        rodzaj = 'kwota';
    }
    if (typ == 'cena_netto') {
        var wartosc_vat = $('#vat').val();
        $('#td_cena_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { cena_netto: $(wartosc).val(), id: id_cech, rodzaj: rodzaj, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#td_cena_' + id_cech).html(data); });        
    }
    if (typ == 'cena_brutto') {
        var wartosc_vat = $('#vat').val();
        $('#td_cena_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { cena_brutto: $(wartosc).val(), id: id_cech, rodzaj: rodzaj, vat: wartosc_vat, tok: $('#tok').val() }, function(data) { $('#td_cena_' + id_cech).html(data); });        
    }    
    if (typ == 'waga') {
        $('#td_waga_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { waga: $(wartosc).val(), id: id_cech, tok: $('#tok').val() }, function(data) { $('#td_waga_' + id_cech).html(data); });        
    }        
    if (typ == 'prefix') {
        $('#td_prefix_' + id_cech).html('<img src="obrazki/_loader_small.gif">');
        $.get('ajax/zmien_input_cechy.php',
            { prefix: $(wartosc).val(), id: id_cech, tok: $('#tok').val() }, function(data) { $('#td_prefix_' + id_cech).html(data); });        
    }        
}
function ajax_cecha_magazyn(wartosc,id_prod,id_magazyn,id_wroc, typ) {
    $('#' + typ + '_' + id_wroc).html('<img src="obrazki/_loader_small.gif">');
    //
    $.post("ajax/zmien_magazyn_cechy.php?tok=" + $('#tok').val(), 
        { id_prod: id_prod, 
          ilosc: $(wartosc).val(),
          magazyn: id_magazyn,
          id_wroc: id_wroc,
          typ: typ
        },
        function(data) { $('#' + typ + '_' + id_wroc).html(data); }           
    );           
}    
function ajax_cecha_skasuj(typ,wartosc) {
    $('#td_skasuj_' + wartosc).html('<img src="obrazki/_loader_small.gif">');
    $.get('ajax/skasuj_cechy.php',
          { typ: typ, id: wartosc, tok: $('#tok').val() }, 
          function(data) { $('#td_skasuj_' + wartosc).html(data); 
            //
            if ( $('#rodzajCechyCecha').prop('checked') ) {
                 rodzaj_cechy = 'cechy';
                 kombinacje = 'nie';
                 $('#kombinacje').show();
               } else {
                 rodzaj_cechy = 'ceny';
                 kombinacje = 'tak';
                 $('#kombinacje').hide();
            }
            //
            $.ajax({ type :"post", 
                     data : { id_cechy: 0, id_wartosc: 0, id_unikalne: $("#id_unikalne").val(), akcja: '', kombinacje: kombinacje, rodzaj_cechy: rodzaj_cechy },
                     url : "ajax/lista_cech.php?tok=" + $('#tok').val()
                  }).done(function( data ) { 
                     $('#lista_cech').css('display','none'); $('#lista_cech').html(data); $('#lista_cech').fadeIn(); $("#dodaj_ceche").css('display','block'); }           
            ); 
            //
          });
}         