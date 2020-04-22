<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    ?>

    <div id="naglowek_cont">Automatyczne wypełnianie pól META</div>
    <div id="cont">

      <form action="pozycjonowanie/meta_tagi_automat_obsluga.php" method="post" id="pozycjonowanieForm" class="cmxform">
        <div class="poleForm">
            <div class="naglowek">Parametry wypełniania</div>

            <div class="pozycja_edytowana">
            
              <div class="info_content">

              <div class="ostrzezenie" style="margin:10px;">UWAGA: opcja wykonuje działania na bazie danych. Upewnij się, że posiadasz aktualną kopię na wypadek wystąpienia problemów.</div>

              <input type="hidden" name="akcja" value="aktualizuj" />

              <p id="wersja">
                <label>W jakim języku aktualizować dane:</label>
                <?php echo Funkcje::RadioListaJezykow(); ?>
              </p>

              <p>
                <label>Zakres modyfikacji:</label>
                <input type="radio" value="0" name="zakres" onclick="$('#drzewo').slideUp()" checked="checked" class="toolTipTop" title="Aktualizacja pól META dla wszystkich produktów w sklepie" /> wszystkie rekordy
                <input type="radio" value="1" name="zakres" onclick="$('#drzewo').slideDown()" class="toolTipTop" title="Aktualizacja pól META tylko dla produktów z zaznaczonych kategorii" /> tylko zaznaczone kategorie
              </p> 

              <div id="drzewo" style="display:none;margin:10px;width:950px;" >
                <p>Zaznacz wybrane kategorie</p>                           

                <?php
                //
                echo '<table class="pkc" cellpadding="0" cellspacing="0">';
                //
                $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                  $podkategorie = false;
                  if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                  $check = '';
                  //
                  echo '<tr>
                          <td class="lfp"><input type="checkbox" value="'.$tablica_kat[$w]['id'].'" name="id_kat[]" '.$check.' /> '.$tablica_kat[$w]['text'].(($tablica_kat[$w]['status'] == 0) ? '<span class="wylKat toolTipTopText" title="Kategoria jest nieaktywna" /></span>' : '').'</td>
                          <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'checkbox\')" />' : '').'</td>
                        </tr>
                        '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                }
                echo '</table>';
                unset($tablica_kat,$podkategorie);
                ?>
              </div>

              <p>
                <label>Metoda wypełniania danych:</label>
                <input type="radio" value="0" name="sposob" onclick="$('#danewlasne').slideUp();$('#objasnienia').slideUp()" checked="checked" class="toolTipTop" title="Sekcja META zostanie wypełniona nazwą oraz danymi zawartymi w opisie kategorii, producenta lub produktu" /> nazwa i opis
                <input type="radio" value="1" name="sposob" onclick="$('#danewlasne').slideDown();$('#objasnienia').slideDown()" class="toolTipTop" title="Sekcja meta zostanie wypełniona zdefiniowanymi poniżej danymi" /> zdefiniowane wartości
              </p> 

              <div id="danewlasne" style="display:none;" >
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                <p><b>KATEGORIE</b></p>

                <p>
                  <label>Tytuł kategorii:</label>
                  <input type="text" name="tytul_kat" id="tytul_kat" value="{NAZWA_KATEGORII}" size="103" />
                </p>
                <p>
                  <label>Opis kategorii:</label>
                  <textarea cols="100" rows="3" name="opis_kat">{DUZE_NAZWA_KATEGORII} {OPIS_KATEGORII}.</textarea>
                </p>
                <p>
                  <label>Słowa kluczowe dla kategorii:</label>
                  <textarea cols="100" rows="3" name="slowa_kat">{DUZE_NAZWA_KATEGORII}, {NAZWA_KATEGORII}, {MALE_NAZWA_KATEGORII}</textarea>
                </p>

                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                <p><b>PRODUCENCI</b></p>

                <p>
                  <label>Tytuł producenta:</label>
                  <input type="text" name="tytul_producent" id="tytul_producent" value="{NAZWA_PRODUCENTA}" size="103" />
                </p>
                <p>
                  <label>Opis producenta:</label>
                  <textarea cols="100" rows="3" name="opis_producent">{DUZE_NAZWA_PRODUCENTA} {OPIS_PRODUCENTA}</textarea>
                </p>
                <p>
                  <label>Słowa kluczowe dla producenta:</label>
                  <textarea cols="100" rows="3" name="slowa_producent">{DUZE_NAZWA_PRODUCENTA}, {NAZWA_PRODUCENTA}, {MALE_NAZWA_PRODUCENTA}</textarea>
                </p>

                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                <p><b>PRODUKTY</b></p>

                <p>
                  <label>Tytuł produktu:</label>
                  <input type="text" name="tytul_produkt" id="tytul_produkt" value="{NAZWA_PRODUKTU}" size="103" />
                </p>
                <p>
                  <label>Opis produktu:</label>
                  <textarea cols="100" rows="3" name="opis_produkt">{DUZE_NAZWA_PRODUKTU} najlepszy produkt na świecie. {OPIS_PRODUKTU}</textarea>
                </p>
                <p>
                  <label>Słowa kluczowe dla produktu:</label>
                  <textarea cols="100" rows="3" name="slowa_produkt">{NAZWA_PRODUKTU}, {NAZWA_KATEGORII}, {NAZWA_PRODUCENTA}, {DUZE_NAZWA_PRODUKTU}, {MALE_NAZWA_PRODUKTU}</textarea>
                </p>
                <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
              </div>

              <p>
                <label>Warunki wypełniania - kategorie:</label>
                <input type="radio" value="0" name="warunek_kat" class="toolTipTop" title="Aktualizacja wszystkich pól META" /> wszystkie
                <input type="radio" value="1" name="warunek_kat" class="toolTipTop" title="Aktualizacja tylko pustych pól META" /> tylko puste
                <input type="radio" value="2" name="warunek_kat" class="toolTipTop" title="Usunięcie wpisów z pól META" /> wyczyść
                <input type="radio" value="3" name="warunek_kat" checked="checked" class="toolTipTop" title="Żadne pola nie zostaną zmodyfikowane" /> bez zmian
              </p> 

              <p>
                <label>Warunki wypełniania - producenci:</label>
                <input type="radio" value="0" name="warunek_producent" class="toolTipTop" title="Aktualizacja wszystkich pól META" /> wszystkie
                <input type="radio" value="1" name="warunek_producent" class="toolTipTop" title="Aktualizacja tylko pustych pól META" /> tylko puste
                <input type="radio" value="2" name="warunek_producent" class="toolTipTop" title="Usunięcie wpisów z pól META" /> wyczyść
                <input type="radio" value="3" name="warunek_producent" checked="checked" class="toolTipTop" title="Żadne pola nie zostaną zmodyfikowane" /> bez zmian
              </p> 

              <p>
                <label>Warunki wypełniania - produkty:</label>
                <input type="radio" value="0" name="warunek_produkt" class="toolTipTop" title="Aktualizacja wszystkich pól META" /> wszystkie
                <input type="radio" value="1" name="warunek_produkt" class="toolTipTop" title="Aktualizacja tylko pustych pól META" /> tylko puste
                <input type="radio" value="2" name="warunek_produkt" class="toolTipTop" title="Usunięcie wpisów z pól META" /> wyczyść
                <input type="radio" value="3" name="warunek_produkt" checked="checked" class="toolTipTop" title="Żadne pola nie zostaną zmodyfikowane" /> bez zmian
              </p> 

              </div>

            </div>
            
            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Aktualizuj dane" />
            </div>            
        </div>
      </form>

    </div>


    <div class="objasnienia" id="objasnienia" style="display:none;">
      <div class="objasnieniaTytul">Znaczniki, które możesz użyć:</div>
      <div class="objasnieniaTresc">

        <ul class="mcol">
          <li><b>{NAZWA_KATEGORII}</b> - Nazwa kategorii</li>
          <li><b>{DUZE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_KATEGORII}</b> - Nazwa kategorii pisana z dużej litery</li>
          <li><b>{OPIS_KATEGORII}</b> - Opis kategorii</li>
          <li><b>{NAZWA_PRODUCENTA}</b> - Nazwa producenta</li>
          <li><b>{DUZE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUCENTA}</b> - Nazwa producenta pisana z dużej litery</li>
          <li><b>{OPIS_PRODUCENTA}</b> - Opis producenta</li>
        </ul>
        <ul class="mcol">
          <li><b>{NAZWA_PRODUKTU}</b> - Nazwa produktu</li>
          <li><b>{DUZE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana dużymi literami</li>
          <li><b>{MALE_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana małymi literami</li>
          <li><b>{Z_DUZEJ_NAZWA_PRODUKTU}</b> - Nazwa produktu pisana z dużej litery</li>
          <li><b>{OPIS_PRODUKTU}</b> - Opis produktu</li>
        </ul>
      </div>
    </div>

    <?php
    include('stopka.inc.php');    
    
} ?>
