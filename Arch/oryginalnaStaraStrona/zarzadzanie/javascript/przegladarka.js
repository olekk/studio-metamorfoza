// przegladarka plikow
function przegladarka( katalog, input, edytor, stala, produkt ) {
    //
    $('#przegladarka').css('display','none');
    $('#ekr_preloader').css('display','block');
    //
    $.post('../zarzadzanie/przegladarka.php?typ=' + edytor + '&tok=' + $('#tok').val(), { folder: katalog, pole: input, stala: stala, produkt: produkt }, function(data) {
        //
        $('#ekr_preloader').css('display','none');
        //
        $('#listPlik_' + edytor).html( data );
        $('#przegladarka').css('display','block');
        //
        masowoProdukt();
        //
    });
}     

function przegladarkaZamknij( edytor ) {
    //
    $('#przegladarka').css('display','none');
    $('#listPlik_' + edytor).html('');
    //
}

function przegladarkaFolder( katalog, input, edytor, stala, produkt ) {
    //
    $('#przegladarka').css('display','none');
    $('#ekr_preloader').css('display','block');
    //
    $.post('../zarzadzanie/przegladarka.php?typ=' + edytor + '&tok=' + $('#tok').val(), { folder: katalog, pole: input, stala: stala, akcja: 'f', nowy: $('#nowyfolder').val(), produkt: produkt }, function(data) {
        //
        $('#ekr_preloader').css('display','none');
        //
        $('#przegladarka').fadeIn('fast', function() { $('#listPlik_' + edytor).html( data ); masowoProdukt(); } );
        //
    });
}

function przegladarkaSzukaj( katalog, input, edytor, stala, produkt ) {
    //
    $('#przegladarka').css('display','none');
    $('#ekr_preloader').css('display','block');
    //
    $.post('../zarzadzanie/przegladarka.php?typ=' + edytor + '&tok=' + $('#tok').val(), { folder: katalog, pole: input, stala: stala, akcja: 's', szukaj: $('#szukanafraza').val(), produkt: produkt }, function(data) {
        //
        $('#ekr_preloader').css('display','none');
        //
        $('#przegladarka').fadeIn('fast', function() { $('#listPlik_' + edytor).html( data ); masowoProdukt(); } );
        //
    });
}

function getUrlParam(paramName) {
    var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
    var match = window.location.search.match(reParam) ;
    return (match && match.length > 1) ? match[1] : '' ;
}

function wstaw_obrazek( wartosc, akcja, stala, katalog ) {
    //
    if ( akcja == 'strona') {
        //
        var inp = $('#pole').val();
        $('#' + inp).val ( wartosc );
        $('#' + inp).focus();
        przegladarkaZamknij();
        pokaz_obrazek_ajax( inp, wartosc );
        //
    }
    if ( akcja == 'ckedit') {
        //
        var funcNum = getUrlParam('CKEditorFuncNum');
        var fileUrl = '/' + katalog + '/' + wartosc;
        window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);    
        window.close();
        //
    }
    //
    if ( stala != '' ) {
        $('#ekr_preloader').css('display','block');
        $.post("wyglad/wyglad_zapisz_stala.php?tok=" + $('#tok').val(), { wart: wartosc, stala: stala }, function(data) {  $('#ekr_preloader').fadeOut(); });
    }
}
 
// wyswietla obrazek ajaxem
function podgladObrazka(wartosc) {    
    //
    $("#podglad").html('<img src="obrazki/_loader_small.gif">');
    //
    $.get('ajax/obraz.php', { tok: $('#tok').val(), foto: wartosc, sz: '150', wy: '150', info: 'tak', sciezka: 'tak' }, function(data) {
        if (data != '') {
            $("#podglad").html(data);
        } else {
            $("#podglad").html('... brak podglądu ...');
        }
    });
}

function masowoProdukt() {
    //
    $('.zaznaczMasowo').click(function() {

       if ( $('input[name="zaznacz_masowo[]"]:checked').length > 0 ) {
          //
          $('#wybierzZdjecia').fadeIn();
          //
        } else {
          //
          $('#wybierzZdjecia').fadeOut();
          //
       }
       
    });   
    //
    $('#wybierzZdjecia').click(function() {
        //
        przegladarkaZamknij();
        //
        var pliki_do_wgrania = '';
        var suma_do_wgrania = 0;
        $('input[name="zaznacz_masowo[]"]:checked').each(function() {
            //
            pliki_do_wgrania += $(this).val() + ';';
            suma_do_wgrania++;
            //
        });
        //
        $.get('ajax/dodaj_zdjecia_wiele.php', { pliki: pliki_do_wgrania, id: parseInt($("#ile_pol").val()), katalog: $('#katalog_glowny').val() }, function(data) {
           $("#ile_pol").val( parseInt($("#ile_pol").val()) + suma_do_wgrania );
           $('#wyniki tr:last').after(data);
           //
           pokazChmurki();            
        });                                                  
        //
    });
    //
}   