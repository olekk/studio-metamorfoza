// przenoszenie do lewej i prawej kolumny
function ple(id) {
    //
    var idDiv = $("#box_" + id).parent().attr('id');
    $("#box_" + id).css('display','none');
    $('.tip-twitter').css({'visibility':'hidden'});
    //
    if (idDiv == 'wyglad_lewa') {
        $("#box_" + id).appendTo("#wyglad_prawa");
        $("#box_" + id + " img").css('float','left');
        $("#box_" + id).css('text-align','right');
        $("#box_" + id + " .strzalka").attr('src','obrazki/strzalka_lewa.png');
        $("#box_" + id + " .strzalka").attr('alt','Przenieś do lewej kolumny');
        $("#box_" + id + " .strzalka").attr('title','Przenieś do lewej kolumny');
        
        $("#box_" + id + " span").css('backgroundPosition','right');
        $("#box_" + id + " span").css('paddingLeft','2px');
        $("#box_" + id + " span").css('paddingRight','25px');

        if ($("#wyglad_lewa").html().trim() == '') {
            $("#wyglad_lewa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }        
        if ($("#wyglad_prawa p").length > 0) {
            $("#wyglad_prawa p").remove();
        }
            
    }
    if (idDiv == 'wyglad_prawa') {
        $("#box_" + id).appendTo("#wyglad_lewa");
        $("#box_" + id + " img").css('float','right');
        $("#box_" + id).css('text-align','left');
        $("#box_" + id + " .strzalka").attr('src','obrazki/strzalka_prawa.png');
        $("#box_" + id + " .strzalka").attr('alt','Przenieś do prawej kolumny');
        $("#box_" + id + " .strzalka").attr('title','Przenieś do prawej kolumny');
        
        $("#box_" + id + " span").css('backgroundPosition','left');
        $("#box_" + id + " span").css('paddingLeft','25px');
        $("#box_" + id + " span").css('paddingRight','2px'); 

        if ($("#wyglad_prawa").html().trim() == '') {
            $("#wyglad_prawa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }        
        if ($("#wyglad_lewa p").length > 0) {
            $("#wyglad_lewa p").remove();
        }
        
    }            
    //
    $("#box_" + id).fadeIn('slow');
    //
    var order = $("#wyglad_lewa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=lewa');
    var order = $("#wyglad_prawa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=prawa'); 
    //
}

// kasowanie boxu
function psk(id) {
    $('.tip-twitter').css({'visibility':'hidden'});
    $("#box_" + id).remove();
    var order = $("#wyglad_lewa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=lewa&skasuj=1&idbox=' + id, function(data) {
        if ($("#wyglad_lewa").html().trim() == '') {
            $("#wyglad_lewa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }		
    });    
    var order = $("#wyglad_prawa").sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=prawa&skasuj=1&idbox=' + id, function(data) {
        if ($("#wyglad_prawa").html().trim() == '') {
            $("#wyglad_prawa").html('<p style="padding:10px">Brak pozycji ...</p>');
        }	
    }); 
}

// kasowanie modulu
function msk(id, typ) {
    $('.tip-twitter').css({'visibility':'hidden'});
    $("#modul_" + id).remove();
    var order = $("#wyglad_srodek_" + typ).sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_modul.php?tok=" + $('#tok').val(), order + '&skasuj=1&idmodul=' + id + '&typ=' + typ, function(data) {
        if ($("#wyglad_srodek_" + typ).html().trim() == '') {
            $("#wyglad_srodek_" + typ).html('<p style="padding:10px">Brak pozycji ...</p>');
        }	
    });    
}

// kasowanie stalej
function ssk(id, div) {
    $('.tip-twitter').css({'visibility':'hidden'});
    $("#" + div + "_" + id).remove();
    var order = $("#wyglad_" + div).sortable("serialize"); 
    $.post("wyglad/wyglad_serialize_stala.php?tok=" + $('#tok').val(), order + '&skasuj=1&idmodul=' + id + '&typ=' + div + '&stala=' + div.toUpperCase(),
        function(data) {
            if ($("#wyglad_" + div).html().trim() == '') {
                $("#wyglad_" + div).html('<p style="padding:10px">Brak pozycji ...</p>');
            }
        }
    );    
}

// zamkniecie okna edycji
function zamknij_edycje() {
    $('#ekr_edit').fadeOut( function(data) { $('#glowne_okno_edycji').html(''); } );
}

// dodawanie boxu do kolumny
function dodaj_box(kolumna, id_loadera) {
    $('#' + id_loadera).html('<img src="obrazki/_loader_small.gif">');
    $.get('wyglad/wyglad_dodanie_boxu.php', { tok: $('#tok').val(), p: 'lista', kolumna: kolumna }, function(data) {
        $('#' + id_loadera).html('');
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        $('#edytuj_stale').css({ 'top':margines/2 });
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //
        $('#ekr_edit').fadeIn();
    });
}  

// dodawanie modulu
function dodaj_modul(typ, id_loadera) {
    $('#' + id_loadera).html('<img src="obrazki/_loader_small.gif">');
    $.get('wyglad/wyglad_dodanie_modulu.php', { tok: $('#tok').val(), p: 'lista', typ: typ }, function(data) {
        $('#' + id_loadera).html('');
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        $('#edytuj_stale').css({ 'top':margines/2 });
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //        
        $('#ekr_edit').fadeIn();
    });
}

// dodawanie modulu
function dodaj_stala(div) {
    $.get('wyglad/wyglad_dodanie_stala.php', { tok: $('#tok').val(), p: 'lista', div: div }, function(data) {
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        $('#edytuj_stale').css({ 'top':margines/2 });
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //        
        $('#ekr_edit').fadeIn();
    });
}

function wybierz_box(id, kolumna) {
    //
    $.get('wyglad/wyglad_dodanie_boxu.php', { tok: $('#tok').val(), p: 'dodaj', id: id, kolumna: kolumna }, function(data) {
        //
        if ($("#wyglad_" + kolumna + " p").length > 0) {
            $("#wyglad_" + kolumna + " p").remove();
        }
        //    
        $("#wyglad_" + kolumna).prepend(data);
        $('#ekr_edit').fadeOut();
        //
        var order = $("#wyglad_" + kolumna).sortable("serialize"); 
        $.post("wyglad/wyglad_serialize_box.php?tok=" + $('#tok').val(), order + '&kolumna=' + kolumna);         
        //
        pokazChmurki();
    });    
}

function wybierz_modul(id, typ) {
    //
    $.get('wyglad/wyglad_dodanie_modulu.php', { tok: $('#tok').val(), p: 'dodaj', id: id, typ: typ }, function(data) {
        //
        if ($("#wyglad_srodek_" + typ + " p").length > 0) {
            $("#wyglad_srodek_" + typ + " p").remove();
        }
        //      
        $("#wyglad_srodek_" + typ).prepend(data);
        $('#ekr_edit').fadeOut();
        //
        var order = $("#wyglad_srodek_" + typ).sortable("serialize"); 
        $.post("wyglad/wyglad_serialize_modul.php?tok=" + $('#tok').val(), order + '&typ=' + typ);         
        //
        pokazChmurki();
    });    
}

function edytuj_modul(id, typ) {
    //
    $.get('wyglad/wyglad_edycja_modulu.php', { tok: $('#tok').val(), id: id, typ: typ }, function(data) {
        //
        $('#ekr_edit').css('display','none');
        $('#glowne_okno_edycji').html(data);
        //
        $('#ekr_edit').show();
        $('#ekr_edit').css({'visibility':'hidden'});
        var margines = $(window).height() - $('#edytuj_okno').height() - 50;
        $('#edytuj_stale').css({ 'top':margines/2 });
        $('#ekr_edit').css({'visibility':'visible'});
        $('#ekr_edit').hide();
        //        
        $('#ekr_edit').fadeIn();      
        //
        pokazChmurki();
    });    
} 

function wybierz_stala(id, rodzaj, div) {
    //
    $.get('wyglad/wyglad_dodanie_stala.php', { tok: $('#tok').val(), p: 'dodaj', id: id, rodzaj: rodzaj, div: div }, function(data) {
        //
        if ($("#wyglad_" + div + " p").length > 0) {
            $("#wyglad_" + div + " p").remove();
        }
        //
        $("#wyglad_" + div).prepend(data);
        $('#ekr_edit').fadeOut();
        //
        var order = $("#wyglad_" + div).sortable("serialize"); 
        $.post("wyglad/wyglad_serialize_stala.php?tok=" + $('#tok').val(), order + '&typ=' + div + '&stala=' + div.toUpperCase());	       
        //
        pokazChmurki();
    });    
} 

// chowa lub pokazuje opcje tla sklepu  
function zmien_tlo(id) {
    if (id == 1) {
        $('#tlo_2').slideUp();
        $('#tlo_1').slideDown();
        $('#foto').val('');
        $('#color').val('');
        zmienGet('kolor','TLO_SKLEPU_RODZAJ');
      } else {
        $('#tlo_1').slideUp();
        $('#tlo_2').slideDown(); 
        $('#color').val('');   
        $('#foto').val('');
        zmienGet('obraz','TLO_SKLEPU_RODZAJ');        
    }
    zmienGet('','TLO_SKLEPU');   
} 

// chowa lub pokazuje opcje naglowka
function zmien_naglowek(id) {
    if (id == 1) {
        $('#naglowek_2').slideUp();
        $('#naglowek_1').slideDown();
        $('#foto_naglowek').val('');
        $('#kod_naglowek').val('');
        zmienGet('kod','NAGLOWEK_RODZAJ');
      } else {
        $('#naglowek_1').slideUp();
        $('#naglowek_2').slideDown(); 
        $('#kod_naglowek').val('');   
        $('#foto_naglowek').val('');
        zmienGet('obraz','NAGLOWEK_RODZAJ');
    }
    zmienGet('','NAGLOWEK');   
}   

// zmiana wartosci post
function zmienGet(wart, stala) {
    $('#ekr_preloader').css('display','block');
    $.post("wyglad/wyglad_zapisz_stala.php?tok=" + $('#tok').val(), { wart: wart, stala: stala }, function(data) {  $('#ekr_preloader').fadeOut(); });
}

// zmiana wartosci post z id jezyka
function zmienGetJezyk(wart, stala, jezyk) {
    $('#ekr_preloader').css('display','block');
    $.post("wyglad/wyglad_zapisz_stala_jezykowa.php?tok=" + $('#tok').val(), { wart: wart, stala: stala, jezyk: jezyk }, function(data) { $('#ekr_preloader').fadeOut(); });
}

