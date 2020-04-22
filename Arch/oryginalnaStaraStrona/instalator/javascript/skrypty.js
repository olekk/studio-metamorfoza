$(document).ready(function() {

	// Ajax animation
	$("#loaderSpace").ajaxStart(function()
	{
		$(this).fadeIn('slow');
		$(this).children('div').fadeIn('slow');
	});

	$("#loaderSpace").ajaxComplete(function()
	{
		$(this).fadeOut('slow');
		$(this).children('div').fadeOut('slow');
	});

	
	if (!$('#licencja').prop('checked')) {
       $('#submitButton').removeClass('przycisk').addClass('przyciskWylaczony').attr('disabled', true);
    }

    $('#licencja').click(function() {
        if ($(this).prop('checked')) {
            $('#submitButton').addClass('przycisk').removeClass('przyciskWylaczony').attr('disabled', false);
        } else {
            $('#submitButton').removeClass('przycisk').addClass('przyciskWylaczony').attr('disabled', true);
        }
    });

    if ($('#licencja').prop('checked')) {
        $('#submitButton').addClass('przycisk').removeClass('przyciskWylaczony').attr('disabled', false);
    } else {
        $('#submitButton').removeClass('przycisk').addClass('przyciskWylaczony').attr('disabled', true);
    }

    if ( $('#konfiguracja').val() == '0' ) {
        $('#submitButton2').addClass('przycisk').removeClass('przyciskWylaczony').attr('disabled', false);
    } else {
        $('#submitButton2').removeClass('przycisk').addClass('przyciskWylaczony').attr('disabled', true);
    }

    $(function(){
            $('.toolTip, .toolTipText').poshytip({
              className: 'tip-twitter',
              hideTimeout: 500,
              showOn: 'focus',
              alignTo: 'target',
              alignX: 'right',
              alignY: 'center',
              offsetX: 15
            });
    }); 


    //$("form :input").change(function() {
    //    //alert('test');
    //    var $emptyFields = $('form :input').filter(function() {
    //        return $.trim(this.value) === "";
    //    });

    //    if (!$emptyFields.length) {
    //        $('#sprawdzPolaczenie').show();
    //    } else {
    //        $('#sprawdzPolaczenie').hide();
    //    }
    //});

});

function sprawdzPolaczenie() {
    $("#wynikTestu").html('').slideUp('fast');
    var host = $('#host').val();
    var port = $('#port').val();
    var dbuser = $('#dbuser').val();
    var dbname = $('#dbname').val();
    var dbpass = $('#dbpass').val();

    $.post("ajax/sprawdz_polaczenie.php",
        { host: host, port: port, dbuser: dbuser, dbname: dbname, dbpass: dbpass },
        function(data) {
            if ( data != 'Wykonano poprawne polaczenie z baza danych' ) {
                $("#wynikTestu").removeClass('ZgodnoscOK').addClass('ZgodnoscBrak'); 
                $('#submitButton3').removeClass('przycisk').addClass('przyciskWylaczony').attr('disabled', true);
            } else {
                $("#wynikTestu").removeClass('ZgodnoscBrak').addClass('ZgodnoscOK'); 
                $('#submitButton3').addClass('przycisk').removeClass('przyciskWylaczony').attr('disabled', false);
            }
        }           
	)    
	.done(function(data) {
		$("#wynikTestu").html(data).slideDown('fast');
	});




}

