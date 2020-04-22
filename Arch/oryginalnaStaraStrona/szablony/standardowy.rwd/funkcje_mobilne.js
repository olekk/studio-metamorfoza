$(document).ready(function() {

    // funkcja do ustawiania szerokosci kolumn boxow i modulow
    RWD_SzerokoscKolumn();
    
    // po zmianie wielkosci ekranu wywola ponownie funkcje
    $(window).resize(function() {
        RWD_SzerokoscKolumn();
    });
    
    // funkcja zwijania boxow przy malych rozdzielczosciach
    RWD_ZwiniecieBoxu();
    
    // wywoluje funkcje skalowania menu - przy uruchomieniu strony
    RWD_RozwijaneMenu( '#GorneMenu', 'li', 'RozwinGorneMenu', 'IkonaSubMenu' );    
    
    // po zmianie wielkosci ekranu wywola ponownie funkcje
    $(window).resize(function() {
    
        // ukrywa podmenu ul w gornym menu
        $('#GorneMenu li ul').hide();
        
        // wywoluje funkcje skalowania menu
        RWD_RozwijaneMenu( '#GorneMenu', 'li', 'RozwinGorneMenu', 'IkonaSubMenu' );

    });  
    // 
    
    // wywoluje funkcje do zwijania kolumn stopki
    RWD_ZwiniecieStopki( '.KolumnaStopki', 'strong' );
    
    // po zmianie wielkosci ekranu wywola ponownie funkcje
    $(window).resize(function() {
        RWD_ZwiniecieStopki( '.KolumnaStopki', 'strong' );
    });

});

// funkcja do skalowania okien np produktow
$(window).load(function(){  

    RWD_Skalowanie( { kontener: '.OknaRwd', pozycje: '.OknoRwd', cssDol: 'LiniaDolnaBrak' } );
    
    $(window).resize(function() {
        RWD_Skalowanie( { kontener: '.OknaRwd', pozycje: '.OknoRwd', cssDol: 'LiniaDolnaBrak' } );
    });    

});

/* 
kontener - nazwa klasy lub id elementu kontenera
element - pozycje menu - standardowo menu w postaci ul/li - jako parametr podawane li
klasaCss - nazwa klasy css z ikona do rozwijania glownego menu
klasaCssSubmenu - nazwa klasy css z ikona do rozwijana podmenu w menu - uzywane dla glownego menu
*/

// funkcja rozwijania menu
function RWD_RozwijaneMenu( kontener, element, klasaCss, klasaCssSubmenu ) {

    // domyslnie usuwa klase uzywana przy zwinietym menu i dodaje css dla pelnej wersji menu
    $(kontener).removeClass('Zwiniete').addClass('Rozwiniete');

    // zmienna szerokosci sklepu
    var szerokoscSklepu = $('#Strona').outerWidth();
    
    // okresli szerokosc menu - do okreslenia musi wlaczyc widocznosc menu
    var szerokoscMenu = 0;
    if ( $(kontener).find('ul:first').css('display') == 'none' ) {
         $(kontener).find('ul:first').show();       
         $('.' + klasaCssSubmenu).remove(); 
    } 
    
    // zlicza szerokosci poszczegolnych elementow
    $(kontener).find('ul:first ' + element).each(function() {
       szerokoscMenu += $(this).outerWidth();
    });       

    // ukrywa widocznosc menu
    $(kontener).find('ul:first').hide();

    // jezeli szerokosc menu jest wieksza od szerokosci sklepu to zwinie menu i wyswietli ikone do rozwijania
    if ( szerokoscMenu + 50 > szerokoscSklepu ) {
    
        // jezeli ma byc zwiniete menu to zmienia klasy css menu
        $(kontener).removeClass('Rozwiniete').addClass('Zwiniete');
    
        // jezeli nie ma ikony to ja doda
        if ( $('.' + klasaCss).length == 0 ) {
             $(kontener).prepend('<div class="RozwinMenu ' + klasaCss + '"><div>MENU</div></div>');
             // ukryje menu ul
             $(kontener + ' ul').hide();
        }
        
        // jezeli bedzie klikniecie w ikone rozwiniecia menu - rozwinie menu
        $('.RozwinMenu').off('click').click(function() {

            if ( $(this).parent().find('ul:first').css('display') == 'none' ) {
                 $(this).parent().find('ul:first').stop().slideDown('fast');
            } else {
                 $(this).parent().find('ul:first').stop().slideUp('fast');
            }          

        }); 
        //   
        // jezeli jest podany css dla submenu - tylko dla menu 3 pozimowego - gornego menu
        if ( klasaCssSubmenu != '' ) {
            //
            // szuka elementow - domyslnie li
            $(kontener + ' ' + element).each(function() {

                // jezeli element li zawiera w sobie kolejny ul to doda ikone do rozwiniecia menu
                if ( $(this).find('ul').length > 0 ) {
                     //
                     // dodaje element z ikona rozwijanego menu
                     if ( $(this).find('.' + klasaCssSubmenu).length == 0 ) {
                          $(this).find('ul').before('<b class="' + klasaCssSubmenu + '"></b>');
                     }
                     //
                }
                
            });    
            //
            // usuwa akcje hover jezeli jest menu zwiniete
            $(kontener).find('ul:first ' + element).off('mouseenter').off('mouseleave');
            //
            $('.' + klasaCssSubmenu).off('click').click(function() {

                // ukrywa wszystkie ul w gornym menu
                $(kontener + ' ' + element + ' ul').stop().slideUp('fast');

                // jezeli menu nie jest rozwiniete to je rozwinie
                if ( $(this).parent().find('ul').css('display') == 'none' ) {
                     $(this).parent().find('ul').stop().slideDown('fast');
                } else {
                     $(this).parent().find('ul').stop().slideUp('fast');
                }          

            });
            //
        }
        
    } else {
    
        // jezeli ma byc pelna wersja menu to zmienia klasy css menu
        $(kontener).removeClass('Zwiniete').addClass('Rozwiniete');
    
        // pokaze menu ul
        $(kontener).find('ul:first').show();
        
        // usunie ikone rozwijania menu - zeby w wersji pelnej nie bylo ikon rozwijania
        if ( $('.' + klasaCss).length > 0 ) {
             $('.' + klasaCss).remove();  
             
             if ( klasaCssSubmenu != '' ) {
                  $('.' + klasaCssSubmenu).remove();  
             }
        }    
        
        // akcja hover do rozwijania gornego menu po najechaniu mysza na menu
        $(kontener).find('ul:first ' + element).hover( function() {
        
            if ( $(this).find('ul').length > 0 ) {
                 if ( $(this).find('ul').css('display') == 'none' ) {
                      $(this).find('ul').stop().slideDown('fast');
                 }
            }
            
        },function () {

            $(this).find('ul').stop().slideUp('fast');

        });

    }

}

// funkcja skalowania szerokosci kolumn z boxami i srodka
function RWD_SzerokoscKolumn() {

    // sprawdza czy jest kontener #strona
    if ( $('#Strona').length ) {
        //
        var szerokoscStrony = $('#Strona').outerWidth();

        if ( $('#LewaKolumna').length ) {
             szerokoscStrony -= $('#LewaKolumna').outerWidth();
        }
        
        if ( $('#PrawaKolumna').length ) {
             szerokoscStrony -= $('#PrawaKolumna').outerWidth();
        }        
        
        $('#SrodekKolumna').width( szerokoscStrony );
        //
    }

}

// funkcja do rozwijania stopki
function RWD_ZwiniecieStopki( kontener, element ) {

    if ( $(kontener + ' ' + element).css('cursor') == 'pointer' ) {
         $(kontener + ' ul').hide();
         // zmiania klasa css na do rozwiniecia
         $(kontener + ' ' + element).find('span').removeClass('StopkaZwin').addClass('StopkaRozwin');
       } else {
         $(kontener + ' ul').show();         
    }

    $(kontener + ' ' + element).off('click').click(function() {

        if ( $(kontener + ' ' + element).css('cursor') == 'pointer' ) {
        
            $(kontener + ' ul').stop().slideUp('fast');
            
            // zmiania klasa css na do rozwiniecia
            $(kontener + ' ' + element).find('span').removeClass('StopkaZwin').addClass('StopkaRozwin');            

            // jezeli menu nie jest rozwiniete to je rozwinie
            if ( $(this).parent().find('ul').css('display') == 'none' ) {
                 //
                 $(this).parent().find('ul').stop().slideDown('fast');
                 $(this).find('span').removeClass('StopkaRozwin').addClass('StopkaZwin');
                 //
            } else {
                 $(this).parent().find('ul').stop().slideUp('fast');
            }          

        }
        
    });

}

// funkcja do zwijania boxow przy malych rozdzielczosciach
function RWD_ZwiniecieBoxu() {

    $(window).resize(function() {
    
        if ( $('#Strona').length ) {
             var szerokoscStrony = $('#Strona').outerWidth();        
        }
    
        $('.BoxRwd').each(function() {
            //
            if ( $(this).find('.BoxZawartosc').length ) {
                 //
                 if ( szerokoscStrony < 760 ) {
                      $(this).find('.BoxZawartosc').css('display','none');
                    } else {
                      $(this).find('.BoxZawartosc').css('display','block');
                 }
                 //
                 if ( $(this).find('.BoxZawartosc').css('display') == 'none' ) {
                      $(this).find('.BoxRozwinZwin').addClass('BoxRozwin').removeClass('BoxZwin');
                 }
                 //
            }         
            //
        });
        
    });
    
    $('.BoxRwd').find('.BoxRozwinZwin').bind('click', function(event) {
        
        var elemBox = $(event.target).parent().parent().find('.BoxZawartosc');
        var elemNaglowek = $(event.target).parent().parent().find('.BoxRozwinZwin');
        
        if ( elemBox.length ) {
            //
            if ( elemBox.css('display') == 'block' ) {
                 //
                 elemBox.stop().slideUp();
                 if ( elemNaglowek.length ) {
                     elemNaglowek.addClass('BoxRozwin').removeClass('BoxZwin');
                 }                 
                 //
               } else {
                 //
                 elemBox.stop().slideDown();
                 if ( elemNaglowek.length ) {
                     elemNaglowek.removeClass('BoxRozwin').addClass('BoxZwin');
                 }                 
                 //
            }
            //
        }
              
    });
    
}

/* 
kontener - nazwa klasy lub id elementu kontenera (.xx lub #xx) - element ktory musi miec stala szerokosc w zaleznosci od rozdzielczosci
pozycje - nazwa klasy elementow produktow do skalowania (z kropka na poczatku) - szerokosc tych elementow bedzie zmienna w zaleznosci od rozdzielczosci
cssDol - klasa css dla ostatnich elementow w wierszu - do usuniecia dolnej ramki (bez kropek - sama nazwa klasy)
*/

function RWD_Skalowanie( opcje ) {

    $( opcje.kontener ).each(function() {

        // pozycje w kontenerze do skalowania
        $pozycje = $(this).find( opcje.pozycje );
        
        // ustawia wysokosc elementow na auto
        $pozycje.css( 'height', 'auto' );
        
        // jezeli jest klasa css dla bokow to usuwa ze wszystkich elementow ta klase - istotne przy skalowaniu zeby 
        // po przeskalowaniu nie zostaly gdzies niepotrzebne klasy i zeby nie brakowalo linii po przeskalowaniu np w srodku
        if ( opcje.cssDol != '' ) {
            $pozycje.removeClass(opcje.cssDol);
        }     

        // ile bedzie pozycji w wierszu
        var iloscPozycjiKolumna = Math.floor( $(this).width() / $pozycje.width() );

        // jezeli jest klasa css dla dolu a ilosc w wierszu jest = 1 to dla ostatniego elementu doda klase css dla dolu (usuniecie linii)
        if (( iloscPozycjiKolumna == null || iloscPozycjiKolumna < 2 ) && ( opcje.cssDol != '' )) {
            // ustala ostatni element
            ostatni = $pozycje[ $pozycje.length - 1 ];
            // dodaje klase css dolu dla ostatniego elementu
            $(ostatni).addClass(opcje.cssDol);
            delete ostatni;
        }
        
        // jezeli ilosc w wierszu jest = 1 to nie ma potrzeby skalowania i przerywa funkcje
        if ( iloscPozycjiKolumna == null || iloscPozycjiKolumna < 2 ) return true;   

        // licznik wierszy
        var wiersze = 1;
        // petla analizujaca po kolei pozycje
        for( var i = 0, j = $pozycje.length; i < j; i += iloscPozycjiKolumna ) {

            // maksymalna wysokosc wyjsciowa
            var maxWysokosc	= 0;
            // tworzy tablice z elementami w wierszu
            $row = $pozycje.slice( i, i + iloscPozycjiKolumna );
            
            // petla ktora ustala maksymalna wysokosc elementu w wierszu
            $row.each( function() {
              var wysokoscElementu = parseInt( $( this ).outerHeight() );
              if ( wysokoscElementu > maxWysokosc ) maxWysokosc = wysokoscElementu;
            });
            
            // ustala dla wszystkich elementow w wierszu najwieksza wysokosc
            $row.css( 'height', maxWysokosc );

        }
        
        // jezeli jest klasa css dla dolu to doda dla elementow w ostatnim wierszu klase css dla dolu (usuniecie linii)
        if ( opcje.cssDol != '' ) {
        
            // ustala ile jest elementow w ostatnim wierszu
            ile = parseInt( $pozycje.length - (parseInt($pozycje.length / iloscPozycjiKolumna) * iloscPozycjiKolumna) );
            
            // jezeli wychodzi ze jest 0 to oznacza ze trzeba wszystkim elementom w ostatnim wierszu dodac klase css
            if ( ile == 0 ) {
                 ile = iloscPozycjiKolumna;
            }
        
            // petla do dodania klasy css dla dolu
            for (var y = 1; y <= ile; y++ ) {
                //
                // pobiera element
                rowm = $pozycje[ $pozycje.length - y ];
                // dodaje klase css
                $(rowm).addClass( opcje.cssDol );
                delete rowm;          
                //
            }

            delete ile;
        
        }  

    });

}
    