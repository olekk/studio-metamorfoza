<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');
// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ( isset($_GET['filtr']) ) {
     //
     unset($_SESSION['filtry']['zamowienia.php']);
     //
     Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz=' . $_GET['id_poz']);
}

if ($prot->wyswietlStrone) {

    $JestZamowienie = false;
    
    if ( ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) || ( isset($_POST['id']) && (int)$_POST['id'] > 0 ) ) {

        $jezyk = $_SESSION['domyslny_jezyk']['kod'];

        if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {
          $zamowienie = new Zamowienie((int)$_GET['id_poz']);
        } elseif ( isset($_POST['id']) && $_POST['id'] != '' ) {
          $zamowienie = new Zamowienie((int)$_POST['id']);
        }

        if ( $zamowienie->info['id_zamowienia'] > 0 ) {
        
            $i18n = new Translator($db, $zamowienie->klient['jezyk']);

            unset($_SESSION['waluta_zamowienia'], $_SESSION['waluta_zamowienia_symbol']);
            $_SESSION['waluta_zamowienia'] = $zamowienie->info['waluta'];
            $_SESSION['waluta_zamowienia_symbol'] = $waluty->ZwrocSymbolWalutyKod($zamowienie->info['waluta']);
            
            $JestZamowienie = true;
            
        }
        
    }

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        //
        $pola = array(
                array('orders_status',$filtr->process($_POST['status'])),
                array('last_modified ','now()')
        );

        $db->update_query('orders' , $pola, " orders_id  = '".(int)$_POST["id"]."'");	
        unset($pola);

        $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_var_id = 'EMAIL_ZMIANA_STATUSU_ZAMOWIENIA'";
        $sql = $db->open_query($zapytanie_tresc);
        $tresc = $sql->fetch_assoc();

        define('NUMER_ZAMOWIENIA', (int)$_POST["id"]);
        define('LINK', Seo::link_SEO('zamowienia_szczegoly.php',(int)$_POST["id"],'zamowienie','',true));
        define('STATUS_ZAMOWIENIA', Sprzedaz::pokazNazweStatusuZamowienia( (int)$_POST['status'], (int)$_POST["jezyk"] ));
        define('DATA_ZAMOWIENIA', date('d-m-Y',strtotime($zamowienie->info['data_zamowienia'])) );
        if ( isset($_POST["dolacz_komentarz"]) ) {
          define('KOMENTARZ', $filtr->process($_POST['komentarz']));
        } else {
          define('KOMENTARZ', '');
        }

        $zapytanie_mail = "SELECT customers_email_address, customers_telephone FROM orders WHERE orders_id = '".(int)$_POST["id"]."'";
        $sql_mail = $db->open_query($zapytanie_mail);

        $info_mail = $sql_mail->fetch_assoc();

        if ( isset($_POST['info_mail']) ) {

          $email = new Mailing;

          $powiadomienie_mail = $_POST['info_mail'];

          if ( $tresc['email_file'] != '' ) {
              $tablicaZalacznikow = explode(';', $tresc['email_file']);
          } else {
              $tablicaZalacznikow = array();
          }
          $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
          $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
          $cc              = Funkcje::parsujZmienne($tresc['dw']);

          $adresat_email   = $info_mail['customers_email_address'];
          $adresat_nazwa   = $filtr->process($_POST['nazwa_klienta']);

          $temat           = Funkcje::parsujZmienne($tresc['email_title']);
          $tekst           = $tresc['description'];
          $zalaczniki_tpl  = $tablicaZalacznikow;
          $szablon         = $tresc['template_id'];
          $jezyk           = (int)$_POST["jezyk"];

          $zalaczniki_file = $_FILES;
          
          $zalaczniki_multi = array( 'szablon' => $zalaczniki_tpl, 'pliki' => $zalaczniki_file );

          $tekst = Funkcje::parsujZmienne($tekst);
          $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', $tekst);

          $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki_multi, false);

        } else {
        
          $powiadomienie_mail = '0';
          
        }

        if ( SMS_WLACZONE == 'tak' && SMS_ZMIANA_STATUSU_ZAMOWIENIA == 'tak' && isset($_POST['info_sms']) ) {

          $adresat   = $info_mail['customers_telephone'];
          $wiadomosc = strip_tags(Funkcje::parsujZmienne($tresc['description_sms']));

          SmsApi::wyslijSms($adresat, $wiadomosc);

          $powiadomienie_sms = $_POST['info_sms'];

        } else {
        
          $powiadomienie_sms = '0';
          
        }
           
        $db->close_query($sql_mail);

        unset($zapytanie_mail);
        
        // pliki do maila
        if ( count($_FILES) > 0 ) {
        
            $zalaczone_pliki = array();
            foreach ( array_keys($_FILES['file']['name']) as $plik ) {
                //
                if ( !empty($_FILES['file']['name'][$plik]) ) {
                     $zalaczone_pliki[] = $_FILES['file']['name'][$plik];
                }
                //
            }

            if ( implode(', ', $zalaczone_pliki) != '' ) {
                 $_POST['komentarz'] = $_POST['komentarz'] . ((trim($_POST['komentarz']) != '') ? '<br /><br />' : '') . 'Zostały dołączone pliki: ' . implode(', ', $zalaczone_pliki);
            }
        
            unset($zalaczone_pliki);
            
        }

        //
        $pola = array(
                array('orders_id',(int)$_POST["id"]),
                array('orders_status_id',$filtr->process($_POST['status'])),
                array('date_added','now()'),
                array('customer_notified ',$powiadomienie_mail),
                array('customer_notified_sms',$powiadomienie_sms),
                array('comments',$filtr->process($_POST['komentarz']))
        );

        $db->insert_query('orders_status_history' , $pola);
        unset($pola);
        
        // zatwierdzenie punktow z zakupy
        if ( SYSTEM_PUNKTOW_STATUS == 'tak' ) {
            //
            if ( isset($_POST['punkty']) && (int)$_POST['punkty'] == 1 ) {
                //
                Klienci::dodajPunktyKlienta( $zamowienie->klient['id'], (int)$_POST['status_punktow'], $zamowienie->info['id_zamowienia'], (int)$_POST['ilosc_punktow'], $_POST['tryb'], (int)$_POST['pkt_id'] );
                //
            }
            //
            // zatwierdzenie punktow z programu partnerskiego
            if ( PP_STATUS == 'tak' ) {
                //
                if ( isset($_POST['punkty_pp']) && (int)$_POST['punkty_pp'] == 1 ) {
                    //
                    Klienci::dodajPunktyKlienta( (int)$_POST['klient_pp'], (int)$_POST['status_punktow_pp'], $zamowienie->info['id_zamowienia'], (int)$_POST['ilosc_punktow_pp'], 1, (int)$_POST['pkt_id_pp']  );
                    //       
                }
                //
            }
            //
        }
        
        if ( isset($_POST['powrot']) && (int)$_POST['powrot'] == 1 ) {
            //
            Funkcje::PrzekierowanieURL('zamowienia.php?id_poz='.(int)$_POST["id"]);
            //
          } else {
            //
            Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
            //
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

    <?php if ( $JestZamowienie == true && isset($zamowienie->klient['id']) && !empty($zamowienie->klient['id']) ) { ?>
    
        <script type="text/javascript" src="javascript/jquery.jeditable.js"></script>
        <script type="text/javascript">
        //<![CDATA[
        <?php include('zamowienia_szczegoly.js.php'); ?>
        //]]>
        </script>
      
        <div class="cmxform"> 
        
          <div class="poleForm">
              <div class="naglowek">Informacje o zamówieniu - zamówienie nr <?php echo $_GET['id_poz']; ?></div>

              <table style="width:100%"><tr>
              
                  <td id="lewe_zakladki" style="vertical-align:top">
                      <a href="javascript:gold_tabs_horiz('0','')" class="a_href_info_zakl" id="zakl_link_0">Podstawowe dane</a>
                      <a href="javascript:gold_tabs_horiz('1','')" class="a_href_info_zakl" id="zakl_link_1">Wysyłki [<?php echo count($zamowienie->dostawy); ?>]</a>
                      <a href="javascript:gold_tabs_horiz('2','')" class="a_href_info_zakl" id="zakl_link_2">Produkty [<?php echo count($zamowienie->produkty); ?>]</a>
                      <a href="javascript:gold_tabs_horiz('3','')" class="a_href_info_zakl" id="zakl_link_3">Historia zamówienia [<?php echo count($zamowienie->statusy); ?>]</a>                        
                      <a href="javascript:gold_tabs_horiz('4','')" class="a_href_info_zakl" id="zakl_link_4">Uwagi <?php echo ($zamowienie->klient['uwagi'] != '' || $zamowienie->info['uwagi'] != '' ? '[!]' : ''); ?></a>                        
                      <?php if ( $zamowienie->info['ilosc_pobran_plikow'] > 0 ) { ?>
                      <a href="javascript:gold_tabs_horiz('5','')" class="a_href_info_zakl" id="zakl_link_5">Historia pobrań plików [<?php echo $zamowienie->info['ilosc_pobran_plikow']; ?>]</a>
                      <?php } ?>
                  </td>
                  
                  <?php $licznik_zakladek = 0; ?>

                  <td id="prawa_strona" style="vertical-align:top">
                  
                      <?php
                      $toks = 'zamowienie';
                      
                      // informacje ogolne
                      include('zamowienia_szczegoly_zakl_info.php');
                      
                      // wysylki do zamowienia
                      include('zamowienia_szczegoly_zakl_wysylki.php');
                      
                      // produkty zamowienia
                      include('zamowienia_szczegoly_zakl_produkty.php');
                      
                      // historia zamowienia
                      include('zamowienia_szczegoly_zakl_historia.php');

                      // uwagi zamowienia
                      include('zamowienia_szczegoly_zakl_uwagi.php');

                      // historia pobran elektronicznych
                      if ( $zamowienie->info['ilosc_pobran_plikow'] > 0 ) {       
                           include('zamowienia_szczegoly_zakl_online.php');
                      }
                      
                      unset($toks);

                      $zakladka = '0';
                      if (isset($_GET['zakladka'])) $zakladka = (int)$_GET['zakladka'];
                      ?>
                      <script type="text/javascript">
                      //<![CDATA[
                      gold_tabs_horiz(<?php echo $zakladka; ?>,'0');
                      //]]>
                      </script>                         
                  
                  </td>
              </tr></table>

          </div>
        </div>
        
        <div class="przyciski_dolne">
             <button type="button" class="przyciskNon" onclick="cofnij('zamowienia','<?php echo Funkcje::Zwroc_Get(array((( empty($zamowienie->klient['id']) ) ? 'id_poz' : 'c'),'typ','zakladka','x','y')); ?>', 'sprzedaz');">Powrót</button>    
        </div>            

        <?php

        $db->close_query($sql);

    } else {

        echo '<div class="poleForm">
                  <div class="naglowek">Edycja danych</div>
                  <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
              </div>';
          
    }
    
    unset($JestZamowienie);
    ?>

    </div>
    <script type="text/javascript">
    $('.download').click(function() {
      setTimeout(function() {
        window.location = 'sprzedaz/zamowienia_szczegoly.php?id_poz=<?php echo (int)$_GET['id_poz']; ?>&zakladka=1';
      }, 2000);
    });
    </script>
    
    <?php
    include('stopka.inc.php');    
  
} ?>
