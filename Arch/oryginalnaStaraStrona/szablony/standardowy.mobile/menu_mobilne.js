$(document).ready(function() {

  $('#WszystkieProducent').click( function() {
     
      if ( $('.Ukryj').css('display') == 'none' ) {
          $('.BoxProducenci .Ukryj').slideDown();
          $(this).hide();
      }
  
  });  
  
  // gorne menu - trzy poziomowe
  // szuka elementow li
  $('#GorneMenu li').each(function() {

      // jezeli element li zawiera w sobie kolejny ul to doda ikone do rozwiniecia menu
      if ( $(this).find('ul').length > 0 ) {
           //
           // dodaje element z ikona rozwijanego menu
           $(this).prepend('<b class="IkonaSubMenu"></b>');
           //
      }
      
  });
  
  $('body').delegate('.IkonaSubMenu', 'click', function() {

      // ukrywa wszystkie ul w gornym menu
      $('#GorneMenu li ul').slideUp('fast');

      // jezeli menu nie jest rozwiniete to je rozwinie
      if ( $(this).parent().find('ul').css('display') == 'none' ) {
           $(this).parent().find('ul').slideDown('fast');
      } else {
           $(this).parent().find('ul').slideUp('fast');
      }          

  });   

  // po skalowaniu ekranu
  $(window).resize(function() {
  
    // ukrywa podmenu ul w gornym menu
    $('#GorneMenu li ul').hide();

    // wywoluje funkcje skalowania menu
    RozwijaneMenuRwd( '#GorneMenu', 'li', 'RozwinGorneMenu' );
    RozwijaneMenuRwd( '#DolneMenu', 'li', 'RozwinDolneMenu' );

  });  
  // 
  
  // wywoluje funkcje skalowania menu - przy uruchomieniu strony
  RozwijaneMenuRwd( '#GorneMenu', 'li', 'RozwinGorneMenu' );
  RozwijaneMenuRwd( '#DolneMenu', 'li', 'RozwinDolneMenu' );
    
  // jezeli bedzie klikniecie w ikone rozwiniecia menu - rozwinie menu
  $('body').delegate('.RozwinMenu', 'click', function() {

      if ( $(this).parent().find('ul:first').css('display') == 'none' ) {
           $(this).parent().find('ul:first').slideDown('fast');
      } else {
           $(this).parent().find('ul:first').slideUp('fast');
      }          

  });        

});

// funkcja rozwijania menu
function RozwijaneMenuRwd( kontener, element, klasaCss ) {

    // zmienna szerokosci sklepu
    var szerokoscSklepu = $('#Strona').outerWidth();
    
    // okresli szerokosc menu - do okreslenia musi wlaczyc widocznosc menu
    var szerokoscMenu = 0;
    if ( $(kontener).find('ul:first').css('display') == 'none' ) {
         $(kontener).find('ul:first').show();                      
    }
    
    // zlicza szerokosci poszczegolnych elementow
    $(kontener).find('ul:first ' + element).each(function() {
       szerokoscMenu += $(this).outerWidth();
    });       
    
    // ukrywa widocznosc menu
    $(kontener).find('ul:first').hide();

    // jezeli szerokosc menu jest wieksza od szerokosci sklepu to zwinie menu i wyswietli ikone do rozwijania
    if ( szerokoscMenu > szerokoscSklepu ) {
    
        // jezeli nie ma ikony to ja doda
        if ( $('.' + klasaCss).length == 0 ) {
             $(kontener).prepend('<div class="RozwinMenu ' + klasaCss + '"><div>MENU</div></div>');
             // ukryje menu ul
             $(kontener + ' ul').hide();
        }
    
    } else {
    
        // pokaze menu ul
        $(kontener).find('ul:first').show();
        
        // usunie ikone rozwijania menu
        if ( $('.' + klasaCss).length > 0 ) {
             $('.' + klasaCss).remove();               
        }    

    }

}


