<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

      $wartosc_towarow_brutto = 0;

      // dodanie rekordu do tablicy
      $pola = array(
              array('orders_id',$filtr->process($_POST['zamowienie_id'])),
              array('receipts_nr',$filtr->process($_POST['paragon_numer'])),
              array('receipts_date_sell',date('Y-m-d', strtotime($filtr->process($_POST['data_sprzedazy'])))),
              array('receipts_date_generated',date('Y-m-d', strtotime($filtr->process($_POST['data_wystawienia'])))),
              array('receipts_date_modified','now()'),
              array('receipts_comments',$filtr->process($_POST['komentarz'])));
              
      $db->insert_query('receipts' , $pola);
      unset($pola);
      
      $id_dodanej_pozycji = $db->last_id_query();

      Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["zamowienie_id"].'&zakladka='.$filtr->process($_POST["zakladka"]).'');

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    $zamowienie = new Zamowienie((int)$_GET['id_poz']);
    $numer_paragonu = Sprzedaz::WygenerujNumerParagonu(); 
    
    ?>
    
    <div id="naglowek_cont">Paragon</div>

    <div id="cont">

      <?php
      if (count($zamowienie) > 0) {
        ?>
        
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function() {

          $("#paragonForm").validate({
            rules: {
              paragon_numer: {required: true, remote: "ajax/sprawdz_numer_paragonu.php"}
            },
            messages: {
              paragon_numer: {required: "Pole jest wymagane", remote: "Taki numer paragonu już istnieje"}
            }
          });

          $('input.datepicker').Zebra_DatePicker({
            format: 'd-m-Y',
            inside: false,
            readonly_element: true
          });

        });
        //]]>
        </script>
            
        <form action="sprzedaz/zamowienia_paragon_generuj.php" method="post" id="paragonForm" class="cmxform">          

          <div class="poleForm">
            <div class="naglowek">Paragon do zamówienia nr: <?php echo $_GET['id_poz']; ?></div>
                
            <div class="pozycja_edytowana">
            
              <div class="info_content">
                    
                <input type="hidden" name="akcja" value="zapisz" />
                <input type="hidden" name="zamowienie_id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                <input type="hidden" name="zakladka" value="<?php echo $filtr->process((int)$_GET['zakladka']); ?>" />

                <p>
                    <label class="required">Numer paragonu:</label>
                    <input type="text" name="paragon_numer" id="paragon_numer" size="10" value="<?php echo $numer_paragonu; ?>" /> <span class="RokFaktury">/<?php echo ROK_KSIEGOWY_FAKTUROWANIA; ?></span>
                    <label style="display:none" class="error" for="paragon_numer" generated="true"></label>
                </p> 

                <p>
                    <label>Data sprzedaży:</label>
                    <input type="text" name="data_sprzedazy" id="data_sprzedazy" size="20" value="<?php echo date('d-m-Y', strtotime($zamowienie->info['data_zamowienia'])); ?>" class="datepicker" />
                </p> 

                <p>
                    <label>Data wystawienia:</label>
                    <input type="text" name="data_wystawienia" id="data_wystawienia" size="20" value="<?php echo date("d-m-Y"); ?>" class="datepicker" />
                </p> 

              </div>

              <div class="info_content">

                <p>
                    <label>Komentarz:</label>
                    <textarea cols="70" style="width:98%" rows="5" name="komentarz" id="komentarz"><?php echo ( FAKTURA_KOMENTARZ_TEKST == 'tak' ? 'Zamówienie numer ' . $_GET['id_poz'] : ''); ?></textarea>
                </p> 

              </div>

            </div>

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Get(array('typ','x','y')); ?>','sprzedaz');">Powrót</button>           
            </div>
            
          </div>

        </form>

        <?php

      } else {

        echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

      }

      ?>

    </div>
    
    <?php
    include('stopka.inc.php');

}

?>