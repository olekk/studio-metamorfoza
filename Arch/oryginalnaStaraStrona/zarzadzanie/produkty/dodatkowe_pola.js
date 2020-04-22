$(document).ready(function(){

    $('.obrazek_pole').attr('autocomplete', 'off');

    $('.obrazek_pole').bind('blur',	
      function () {
        var idt = $(this).attr("id");
        var idt_tab = idt.split('_');
        var idp = idt_tab[ idt_tab.length - 1];
        usun_slownik(idp);
        pokaz_obrazek_ajax(idt, $(this).val());
      }
    ); 
    
    $('.usun_zdjecie_pola').click(function() {
        //
        var atr = $(this).attr('data');
        $('#' + atr).val('');
        //
        var idt_tab = atr.split('_');
        var idp = idt_tab[ idt_tab.length - 1];
        usun_slownik(idp);            
        //
        $('#div' + atr).slideUp('fast');            
        //
    });        

});

function dodaj_dodatkowe_pole(id) {
    //
    if ( $('#pole_nazwa_' + $('#id_dod_pola_' + id).val()).length == 0 ) {
        //
        $.get('ajax/lista_dodatkowych_pol.php?tok=' + $('#tok').val(), { id_jezyka: id, id: $('#id_dod_pola_' + id).val(), katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            //
            $('#nowe_pola_' + id).append(data);
            //
            pokazChmurki(); 
            $('.obrazek_pole').attr('autocomplete', 'off');
            //
            $('.obrazek_pole').bind('blur',	
              function () {
                var idt = $(this).attr("id");
                var idt_tab = idt.split('_');
                var idp = idt_tab[ idt_tab.length - 1];
                usun_slownik(idp);
                pokaz_obrazek_ajax(idt, $(this).val());
              }
            );
            //                
            $('.usun_zdjecie_pola').click(function() {
                //
                var atr = $(this).attr('data');
                $('#' + atr).val('');
                //
                var idt_tab = atr.split('_');
                var idp = idt_tab[ idt_tab.length - 1];
                usun_slownik(idp);            
                //
                $('#div' + atr).slideUp('fast');            
                //
            });  
            //
        });
        //
    }
    //
    if ( $('.pole_dodatkowe').length > 0 ) {
         $('#brak_pol_' + id).show();
       } else {
         $('#brak_pol_' + id).hide();
    }
    //
}
function usun_pole(id) {
    $('.tip-twitter').css({'visibility':'hidden'});
    $('#pole_nazwa_' + id).remove();
    //
    if ( $('.pole_dodatkowe_' + id).length > 0 ) {
         $('#brak_pol_' + id).hide();
       } else {
         $('#brak_pol_' + id).show();
    }        
    //
}    
function pokaz_slownik(id) {
    //
    if ( $('#slownik_' + id).html() == '' ) {
        $.get('ajax/slownik_dodatkowych_pol.php?tok=' + $('#tok').val(), { id: id }, function(data) {
            //
            $('#slownik_' + id).hide();
            $('#slownik_' + id).html(data);
            $('#slownik_' + id).slideDown('fast');
            //
            usun_slownik(id);
            //
        });
    } else {
        $('#slownik_' + id).slideUp('fast', function() {
            $('#slownik_' + id).html('')
        });
    }
    //
}
function usun_slownik(id) {
    //
    if ( $('#dodatkowe_pole_slownik_' + id).length ) {
         $('#dodatkowe_pole_slownik_' + id).find('option').removeAttr('selected');
    }
    //
}
function zmien_input(id) {
    //
    if ( $('#foto_pole_' + id).length ) {
         $('#foto_pole_' + id).val( $('#dodatkowe_pole_slownik_' + id + ' option:selected').text() );
    }
    //
    if ( $('#divfoto_pole_' + id).length ) {
        //
        pokaz_obrazek_ajax('foto_pole_' + id, $('#foto_pole_' + id).val());
        //
    }
    //
}    