<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  ?>
    <!-- Skrypt do tworzenia CKEditora -->
    <script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {
      for ( var i in CKEDITOR.instances ){
         CKEDITOR.remove(CKEDITOR.instances[i])
         break;
      }
      var config = {
        filebrowserBrowseUrl : 'przegladarka.php?typ=ckedit&tok=<?php echo Sesje::Token(); ?>',
        filebrowserImageBrowseUrl : 'przegladarka.php?typ=ckedit&tok=<?php echo Sesje::Token(); ?>',
        filebrowserFlashBrowseUrl : 'przegladarka.php?typ=ckedit&tok=<?php echo Sesje::Token(); ?>',
        filebrowserWindowWidth : '990',
        filebrowserWindowHeight : '580',
        filebrowserWindowFeatures : 'menubar=no,toolbar=no,minimizable=no,resizable=no,scrollbars=no' 
      };
      $('textarea.wysiwyg').ckeditor(config);

    });
    //]]>
    </script>        

    <div class="pozycja_edytowana" style="padding-top:20px;">

      <div class="info_content">

        <p id="wersja">
          <label>W jakim języku wysłać email:</label>
          <?php
          echo Funkcje::RadioListaJezykow();
          ?>
        </p>

        <p>
          <label>Nowy status zamówienia:</label>
          <?php
          $tablica = Sprzedaz::ListaStatusowZamowien(true, '--- wybierz z listy ---');
          echo Funkcje::RozwijaneMenu('status', $tablica,'','id="status" onchange="UkryjZapiszKomentarz(this.value)" style="width:350px;"'); ?>
        </p>

        <p>
          <label>Standardowy komentarz:</label>
          <?php
          $tablica = array();
          $tablica[] = array('id' => '0', 'text' => '--- najpierw wybierz status zamówienia ---');
          echo Funkcje::RozwijaneMenu('status_komentarz', $tablica,'','id="komentarz" onchange="ZmienKomentarz(this.value)" style="width:350px;"'); ?>                  
        </p>
        
        <div id="ladujKomentarz"><img src="obrazki/_loader_small.gif" alt="" /></div>        

        <p>
          <label>Poinformuj klienta e-mail:</label>
          <input type="checkbox" checked="checked" value="1" name="info_mail" id="info_mail" class="toolTip" title="Informacja o zmianie statusu zostanie przesłana do klienta" />
        </p>

        <?php if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' ) { ?>
          <p>
            <label>Poinformuj klienta SMS:</label>
            <input type="checkbox" value="1" name="info_sms" id="info_sms" class="toolTip" title="Wysłanie powiadomienia SMS do klienta o zmianie statusu - tylko jeżeli jest podany poprawny numer GSM" />
          </p>
        <?php } ?>

        <p>
          <label>Dołącz komentarz do maila:</label>
          <input type="checkbox" checked="checked" value="1" name="dolacz_komentarz" id="dolacz_komentarz" class="toolTip" title="Informacja komentarza zostanie dołączona do maila z powiadomieniem do klienta" />
        </p>
        
        <?php if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) { ?>
        
        <p class="punkty">
          <label>Zatwierdź punkty:</label>
          <input type="checkbox" value="1" name="zatwierdz_punkty" id="zatwierdz_punkty" class="toolTip" title="Zostaną zatwierdzone i dodane do konta klienta punkty (bez punktów Programu Partnerskiego)" />
        </p>        
        
        <?php } ?>

        <p>
          <label>Komentarz:</label>
          <textarea cols="100" rows="10" name="komentarz" class="wysiwyg" id="komentarz_tresc"></textarea>
        </p>
        
        <span class="maleInfo">
            Jeżeli komentarz zawiera znaczniki w postaci {...} zostaną pod nie podstawione odpowiednie wartości podczas wysyłania wiadomości i zapisu zmiany statusu dla zamówienia.
        </span>

        <script type="text/javascript">
        //<![CDATA[
        function UkryjZapiszKomentarz(id) {
            if (parseInt(id)== 0) {
                $("#komentarz_tresc").val('');
            }   
            //
            $('#ladujKomentarz').fadeIn('fast');
            $.post('sprzedaz/standardowe_komentarze.php', { jezyk: 1, id: id, nazwy: 'tak', tryb: 'multi' }, function(data){
              $("#komentarz").html(data);
              $('#ladujKomentarz').fadeOut('fast');
              $("#komentarz_tresc").val('');
            });                   
        }   
        function ZmienKomentarz(id) {
            var jezyk = $("input[name='jezyk']:checked").val();
            $('#ladujKomentarz').fadeIn('fast');
            $.post('sprzedaz/standardowe_komentarze.php', { jezyk: jezyk, id: id, nazwy: 'nie', tryb: 'multi' }, function(data){
              $("#komentarz_tresc").val(data);
              $('#ladujKomentarz').fadeOut('fast');
            });                 
        }
        
        $(document).ready(function() {
        
            $("input[name=jezyk]").change(function(){
              $("#status option:first").prop("selected",true); 
              $('#komentarz').html('<option selected="selected" value="0">--- najpierw wybierz status zamówienia ---</option>');
              $("#komentarz_tresc").val('');
            });                
        
        });
        //]]>
        </script>

      </div>

    </div>

  <?php
  }
?>