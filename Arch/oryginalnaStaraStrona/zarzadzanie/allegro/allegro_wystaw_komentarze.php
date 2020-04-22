<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {
  $allegro = new Allegro();

  ?>
    <div class="pozycja_edytowana" style="padding-top:20px;">

      <div class="info_content">

          <p>
            <label>Rodzaj komentarza:</label>
            <input type="radio" value="POS" name="fe-comment-type" checked="checked" /> Pozytywny
            <input type="radio" value="NEG" name="fe-comment-type" class="toolTipTop" /> Negatywny
            <input type="radio" value="NEU" name="fe-comment-type" class="toolTipTop" /> Neutralny
          </p>

          <p>
            <label>Komentarz:</label>
            <textarea cols="100" rows="4" name="fe-comment" onkeyup="licznik_znakow(this,'iloscZnakow',255)"><?php echo $allegro->polaczenie['CONF_STANDARD_COMMENTS']; ?></textarea>
          </p>
          
          <p>
            <label></label>
            <span style="display:inline-block; margin:0px 0px 8px 4px">Ilość znaków do wpisania: <span class="iloscZnakow" id="iloscZnakow"><?php echo (255-strlen(utf8_decode($allegro->polaczenie['CONF_STANDARD_COMMENTS']))); ?></span></span>
          </p>

      </div>

    </div>

  <?php
  }
?>