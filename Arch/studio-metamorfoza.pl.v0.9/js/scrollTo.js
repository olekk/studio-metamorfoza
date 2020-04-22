jQuery(function ($) {
    //zresetuj scrolla
        
    $.scrollTo(0);

    $('#link0').click(function () {
        $.scrollTo($('#fb-root'), 500);
    });
    $('#link1').click(function () {
        $.scrollTo($('#cennik'), 500);
    });
    $('#link2').click(function () {
        $.scrollTo($('#godziny'), 500);
    });
    $('#link3').click(function () {
        $.scrollTo($('#promocje'), 500);
    });
    $('#link4').click(function () {
        $.scrollTo($('#pracownicy'), 500);
    });
    $('#link5').click(function () {
        $.scrollTo($('#partnerzy'), 500);
    });
    $('#link6').click(function () {
        $.scrollTo($('#mapa'), 500);
    });
    $('#link7').click(function () {
        $.scrollTo($('#kontakt'), 500);
    });
    
    
});
