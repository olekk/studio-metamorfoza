var skocz = '0';

function osc_ajax(plik,zmienna,ile_przyciskow,parametry,ile_wynikow_na_stronie) {

  if (zmienna == '') {
      zmienna = ((parseInt($("#aktualna_pozycja").html()) - 1)*ile_wynikow_na_stronie)+","+ile_wynikow_na_stronie+"";
  }
  
  // zamienia znaki + 
  parametry = parametry.split('+').join('%2B');

  plik_ze_zmienna = plik+'?parametr='+zmienna+parametry;
  
  $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');

  $('#ekr_preloader').css('display','block');
  $.get(plik_ze_zmienna, function(data) {
  
      $('#wynik_zapytania').html(data); 
      // okreslanie ktory przycisk zostal wcisniety
      var pozycja_przecinka = zmienna.indexOf(",");
      if (pozycja_przecinka != "-1") {
          var pozycja_buttona = (parseInt(zmienna.substring(0, pozycja_przecinka))/ile_wynikow_na_stronie)+1;
          $("#aktualna_pozycja").html(pozycja_buttona); 
      }
      pokaz_pasek(ile_przyciskow,plik,parametry,ile_wynikow_na_stronie);
      // 
      $('#ekr_preloader').delay(100).fadeOut('fast');  
      //
      // podstawielanie pozycji menu
      $(".pozycja_off").hover(
      function(){      
          $(this).removeClass('pozycja_off'); $(this).addClass('pozycja_on') },
      function(){ 
          $(this).removeClass('pozycja_on'); $(this).addClass('pozycja_off') }   
      ); 
      $(".pozycja_on").hover(
      function(){      
          $(this).removeClass('pozycja_off'); $(this).addClass('pozycja_on') },
      function(){ 
          $(this).removeClass('pozycja_on'); $(this).addClass('pozycja_off') }   
      );          
      //    
      pokazChmurki();
      $.ZaladujObrazki();
      
      if ( $(document).height() > $(window).height() && $(window).width() > 1120 ) {
           $('#do_dolu').fadeIn();
      }        
      
      if (skocz > 0) {
          if ( $('#sk_' + skocz).length ) {
               var wu = $('#sk_' + skocz).offset().top - 30;
               $('body').scrollTo(wu,400);
          }
      }
      
      // otwieranie linkow w nowej karcie
      $('.blank').click(function() {
          $(this).target = "_blank";
          window.open($(this).prop('href'));
          return false;
      });   

      // zaznaczanie opcji wyszukiwania
      $('#wyszukaj').find('select').each( function() {
          if ($(this).val() != '' && $(this).val() != '0') {
              $(this).css({'border':'1px solid #ff0000','color':'#ff0000'});
          }
      });
      $('#wyszukaj').find('input').each( function() {
          if ($(this).val() != '') {
              $(this).css({'border':'1px solid #ff0000','color':'#ff0000'});
          }
      });
      
      // podglad okna w listingu z zamowieniem
      $('.zmzoom_zamowienie').hover(function(event) {
         PodgladIn($(this),event,'zamowienie');
      }, function() {
         PodgladOut($(this),'zamowienie');
      });  

      // podglad okna w listingu z kuponem
      $('.zmzoom_kupon').hover(function(event) {
         PodgladIn($(this),event,'kupon');
      }, function() {
          PodgladOut($(this),'kupon');
      }); 

      // podglad okna w listingu z aukcja allegro
      $('.zmzoom_aukcja').hover(function(event) {
         PodgladIn($(this),event,'aukcja');
      }, function() {
          PodgladOut($(this),'aukcja');
      }); 

      // podglad okna w listingu punktow do zatwierdzenia z zamowieniem
      $('.zmzoom_punkty_zamowienie').hover(function(event) {
         PodgladIn($(this),event,'punkty_zamowienie');
      }, function() {
          PodgladOut($(this),'punkty_zamowienie');
      });   

      // podglad okna w listingu punktow do zatwierdzenia z recenzja
      $('.zmzoom_punkty_recenzje').hover(function(event) {
         PodgladIn($(this),event,'punkty_recenzje');
      }, function() {
          PodgladOut($(this),'punkty_recenzje');
      });       
      
      // wyswietlania info o aukcji allegro w listingu produktow
      PodgladAllegro();

  });      
  
  // ustawienie sortowania
  var czy_jest_sortowanie = parametry.indexOf("sort_");
  sort = "sort_a1";
  if (czy_jest_sortowanie != "-1") {
     for (t=1;t<20;t++) {
         if (parametry.indexOf("sort_a"+t) != "-1") { sort = "sort_a"+t; }
     }
  }
  if (document.getElementById(sort)) {
      var link_sortowania = document.getElementById(sort);
      link_sortowania.className = 'sortowanie_zaznaczone';
  }      
  //
  return false;
}

// funkcja wyswietlajaca pasek dolny
function pokaz_pasek(licznik,plik,parametry,ile_wynikow_na_stronie) {
    var aktualny_element = parseInt($("#aktualna_pozycja").html());
    var ile_przyciskow_przed_i_po = 5;
    //
    var tekst_do_wklejenia = '';
    var parms = parametry.replace("id_poz", "xxxs");
    for (f = 1; f <= licznik; f++) {
        if (f >= (aktualny_element - ile_przyciskow_przed_i_po) && f <= (aktualny_element + ile_przyciskow_przed_i_po)) {
            poczatek_szukania = (f-1) * ile_wynikow_na_stronie;
            if (f == parseInt($("#aktualna_pozycja").html())) {
                tekst_do_wklejenia = tekst_do_wklejenia + unescape('<div onclick="osc_ajax(%27'+plik+'%27,%27'+poczatek_szukania+','+ile_wynikow_na_stronie+'%27,'+licznik+',%27'+parms+'%27,%27'+ile_wynikow_na_stronie+'%27);" class="buts_on">'+f+'</div>');
              } else {
                tekst_do_wklejenia = tekst_do_wklejenia + unescape('<div onclick="osc_ajax(%27'+plik+'%27,%27'+poczatek_szukania+','+ile_wynikow_na_stronie+'%27,'+licznik+',%27'+parms+'%27,%27'+ile_wynikow_na_stronie+'%27);" onmouseover="this.className=%27buts_on%27" onmouseout="this.className=%27buts_off%27" class="buts_off">'+f+'</div>');
            }
            var pozycja = f;
        }
    }
    if (pozycja < licznik) {
        tekst_do_wklejenia = tekst_do_wklejenia + '<div class="kropki">...</div>';
    }
    if ((aktualny_element-1) > ile_przyciskow_przed_i_po) {
        tekst_do_wklejenia = '<div class="kropki">...</div>' + tekst_do_wklejenia;
    }    
    var pole_select_strona = '<select onchange="przejdz_do_strony(this.value,'+licznik+",'"+parms+"','"+ile_wynikow_na_stronie+"'"+",'"+plik+"')"+'">';
    for (r=1; r <= licznik; r++) {
        if (r == aktualny_element) {
            pole_select_strona += '<option value="'+r+'" selected>'+r+'</option>';
           } else {
            pole_select_strona += '<option value="'+r+'">'+r+'</option>';
        }
    }
    pole_select_strona += '</select>';
        
    tekst_do_wklejenia = tekst_do_wklejenia + '<div class="input_pole">przejdź do strony: '+pole_select_strona+'</div>';
    //
    var wyswietlanie_od = (((aktualny_element-1)*ile_wynikow_na_stronie)+1);
    var wyswietlanie_do = (parseInt(wyswietlanie_od)+parseInt(ile_wynikow_na_stronie))-1;
    var ile_jest_rekordow = $("#ile_rekordow").html();
    if (wyswietlanie_do > parseInt(ile_jest_rekordow)) {
        wyswietlanie_do = parseInt(ile_jest_rekordow);
    }
    if (parseInt(ile_jest_rekordow) == 0) {
        brak_wynikow();
       } else {
        $("#pokaz_ile_pozycji").html('Wyświetlanie: '+wyswietlanie_od+' do '+wyswietlanie_do+' z '+ile_jest_rekordow); 
        // jezeli jest mniej rekordow niz ilosc do pokazywania na stronie to ma nie pokazywac przyciskow
        if (parseInt(ile_jest_rekordow) > parseInt(ile_wynikow_na_stronie)) { 
            $("#dolny_pasek_stron").html(tekst_do_wklejenia);
           } else {
            $("#dolny_pasek_stron").html('');
        }
    }
}

// wystwietla komunikat i ukrywa div jezeli nei ma wynikow
function brak_wynikow() {
    $("#wynik_zapytania").html('<div style="padding:10px">Brak wyników do wyświetlania</div>');
    $("#pokaz_ile_pozycji").css('display','none');
    $("#dolny_pasek_stron").css('display','none');
    $("#sortowanie").css('display','none');
    //if (!$("#wyszukaj_ikona").length) { $("#wyszukaj").css('display','none'); }
    $("#akcja").css('display','none');
    $("#zapisz_zmiany").css('display','none');
}

// funkcja przechodzenia do okreslonej strony
function przejdz_do_strony(nr_strony,max_stron,parametry,ile_wynikow_na_stronie,plik) {   
    nr_poczatek = (nr_strony * ile_wynikow_na_stronie)-ile_wynikow_na_stronie;
    if (nr_poczatek < 0) { nr_poczatek = 0; }
    osc_ajax(plik,nr_poczatek+","+ile_wynikow_na_stronie,max_stron,parametry,ile_wynikow_na_stronie);
}
