<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));

    if (isset($_GET['zakladka']) && $_GET['zakladka'] != '' ) {
      unset($_GET['zakladka']);
    }
    if (isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) {
      $_GET['id_poz'] = $_GET['klient_id'];
      unset($_GET['klient_id']); 
    }

    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and CONCAT(c.customers_firstname, ' ', c.customers_lastname, c.customers_email_address, a.entry_company, a.entry_nip) LIKE '%".$szukana_wartosc."%'";
    }

    if ( isset($_GET['szukaj_grupa']) && $_GET['szukaj_grupa'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_grupa']);
        $warunki_szukania .= " and c.customers_groups_id = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = ( $_GET['szukaj_status'] == '1' ? '1' : '0' );
        $warunki_szukania .= " and c.customers_status = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_typ']) && $_GET['szukaj_typ'] != '0' ) {
        $szukana_wartosc = ( $_GET['szukaj_typ'] == '2' ? '1' : '0' );
        $warunki_szukania .= " and c.customers_guest_account = '".$szukana_wartosc."'";
    }

    if ( isset($_GET['szukaj_punkty']) && $_GET['szukaj_punkty'] != '0' ) {
        $warunki_szukania .= " and c.customers_shopping_points > 0 ";
    }    

    if ( $warunki_szukania != '' ) {
      $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "SELECT c.customers_id, CONCAT(c.customers_firstname, c.customers_lastname), c.customers_shopping_points, c.customers_firstname, c.customers_lastname, c.customers_status, c.customers_email_address, c.customers_guest_account, c.customers_przetwarzanie, c.customers_telephone, a.entry_country_id, a.entry_city, a.entry_street_address, a.entry_postcode, DATE_FORMAT(ci.customers_info_date_account_created, '%d.%m.%Y') AS data_rejestracji, ci.customers_info_date_account_last_modified, a.entry_company, c.customers_groups_id, count(cb.customers_id) AS koszyk
    FROM customers c
    LEFT JOIN address_book a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id
    LEFT JOIN customers_info ci on ci.customers_info_id = c.customers_id 
    LEFT JOIN customers_basket cb ON cb.customers_id = c.customers_id
    " . $warunki_szukania;
    $zapytanie .= " GROUP BY c.customers_id";    

    if ( isset($_GET['szukaj_koszyk']) && $_GET['szukaj_koszyk'] != '0' ) {
        if ( $_GET['szukaj_koszyk'] == '1' ) {
            $zapytanie .= " HAVING koszyk > 0 ";
        } elseif ( $_GET['szukaj_koszyk'] == '2' ) {
            $zapytanie .= " HAVING koszyk = 0 ";
        }
    }
    $sql = $db->open_query($zapytanie);

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
         
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'ci.customers_info_date_account_created desc';
                break;
            case "sort_a2":
                $sortowanie = 'ci.customers_info_date_account_created asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'c.customers_lastname desc';
                break;
            case "sort_a4":
                $sortowanie = 'c.customers_lastname asc';
                break;                 
            case "sort_a5":
                $sortowanie = 'c.customers_email_address desc';
                break;
            case "sort_a6":
                $sortowanie = 'c.customers_email_address asc';
                break;
            case "sort_a7":
                $sortowanie = 'c.customers_shopping_points desc';
                break;
            case "sort_a8":
                $sortowanie = 'c.customers_shopping_points asc';
                break;                
        }            
    } else { $sortowanie = 'ci.customers_info_date_account_created desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
        
            $zapytanie .= " limit ".$_GET['parametr'];  

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('ID', 'center'),
                                      array('Klient', 'center'),
                                      array('Kontakt', 'center'),
                                      array('Grupa', 'center'),
                                      array('Data rejestracji', 'center'),
                                      array('Zamówień', 'center'),
                                      array('Koszyk', 'center'),
                                      array('Schowek', 'center'),
                                      array('Punkty', 'center'),
                                      array('Status', 'center'));
            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            while ($info = $sql->fetch_assoc()) {
            
                  $ilosc_zamowien = Klienci::pokazIloscZamowienKlienta($info['customers_id']);

                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['customers_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['customers_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['customers_id'].'">';
                  } 

                  // aktywany czy nieaktywny
                  if ($info['customers_status'] == '1') { $obraz = 'aktywny_on.png'; $alt = 'Konto jest aktywne'; } else { $obraz = 'aktywny_off.png'; $alt = 'Konto jest nieaktywne'; }               

                  $tablica = array();

                  $tablica[] = array($info['customers_id'],'center');
                  $wyswietlana_nazwa = '';
                  $kontakt = '';

                  if ( $info['entry_company'] != '' ) {
                    $wyswietlana_nazwa .= '<span class="firma">'.$info['entry_company'] . '</span><br />';
                  }
                  $wyswietlana_nazwa .= $info['customers_firstname']. ' ' . $info['customers_lastname'] . '<br />';
                  $wyswietlana_nazwa .= $info['entry_street_address']. '<br />';
                  $wyswietlana_nazwa .= $info['entry_postcode']. ' ' . $info['entry_city'] . '<br />';
                  
                  // jezeli staly klient
                  if ( $ilosc_zamowien > 1 ) {
                       $wyswietlana_nazwa = '<img style="float:right" src="obrazki/medal.png" alt="Stały klient" title="Stały klient - ilość zamówień: ' . $ilosc_zamowien . '" />' . $wyswietlana_nazwa;
                  }
                  unset($iloscZam);                  
                  
                  // zarejestrowany czy nie
                  if ( $info['customers_guest_account'] == '1' ) { $wyswietlana_nazwa = '<img style="float:right" src="obrazki/gosc.png" alt="Klient bez rejestracji" title="Klient bez rejestracji" />' . $wyswietlana_nazwa; };

                  $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');

                  if (!empty($info['customers_email_address'])) {
                     $kontakt .= '<span class="maly_mail">' . $info['customers_email_address'] . '</span>';
                  }
                  if (!empty($info['customers_telephone'])) {
                      $kontakt .= '<span class="maly_telefon">' . $info['customers_telephone'] . '</span>';
                  }
                  $tablica[] = array($kontakt,'','line-height:17px');

                  $tablica[] = array( (($info['customers_guest_account'] == '1') ? '-' : Klienci::pokazNazweGrupyKlientow($info['customers_groups_id'])),'center');
                  $tablica[] = array($info['data_rejestracji'],'center');
                  $tablica[] = array($ilosc_zamowien,'center');

                  $tablica[] = array(( $info['koszyk'] > 0 ? $info['koszyk'] : '-' ),'center');
                  $tablica[] = array(Klienci::pokazIloscProduktowSchowka($info['customers_id']),'center');
                  
                  /* punkty */
                  $tablica[] = array((((int)$info['customers_shopping_points'] == 0) ? '-' : $info['customers_shopping_points'] . ' pkt'),'center');

                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['customers_id']; 
                  
                  $zmienne_do_przekazania_zamowienia = '?klient_id='.(int)$info['customers_id']; 

                  $tablica[] = array('<a href="klienci/klienci_status.php'.$zmienne_do_przekazania.'"><img src="obrazki/'.$obraz.'" alt="'.$alt.'" title="'.$alt.'" /></a>','center');                    

                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $tekst .= '<a href="klienci/klienci_wyslij_email.php'.$zmienne_do_przekazania.'"><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail" title="Wyślij e-mail" /></a>';
                  if ( SMS_WLACZONE == 'tak' ) {
                    if ( Klienci::CzyNumerGSM($info['customers_telephone']) ) {
                      $tekst .= '<a href="klienci/klienci_wyslij_sms.php'.$zmienne_do_przekazania.'"><img src="obrazki/wyslij_sms.png" alt="Wyślij wiadomość SMS" title="Wyślij wiadomość SMS" /></a>';
                    } else {
                      $tekst .= '<img src="obrazki/wyslij_sms_off.png" alt="Brak numeru GSM - nie można wysłać wiadomości" title="Brak numeru GSM - nie można wysłać wiadomości" />';
                    }
                  }
                  
                  if ( $ilosc_zamowien > 0 ) {
                       $tekst .= '<a href="sprzedaz/zamowienia.php'.$zmienne_do_przekazania_zamowienia.'"><img src="obrazki/lista_wojewodztw.png" alt="Zamówienia klienta" title="Zamówienia klienta" /></a>';
                  }

                  if ( $info['koszyk'] > 0 ) {
                    $tekst .= '<img onclick="podgladKoszyka(\'' . (int)$info['customers_id'] . '\')" class="toolTipTop cur" style="cursor:pointer;" src="obrazki/koszyk.png" alt="" title="Pokaz zawartość koszyka" />';
                  }

                  $tekst .= '<br /><br />';
                  
                  if (  $info['customers_guest_account'] != '1' ) {
                    $tekst .= '<a href="klienci/klienci_zmien_haslo.php'.$zmienne_do_przekazania.'"><img src="obrazki/haslo.png" alt="Zmień hasło" title="Zmień hasło" /></a>';
                  }
                  
                  $tekst .= '<a href="klienci/klienci_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
                  $tekst .= '<a href="klienci/klienci_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_dodaj.php?klient='.(int)$info['customers_id'].'"><img src="obrazki/import.png" alt="Dodaj nowe zamówienie" title="Dodaj nowe zamówienie" /></a>';
                  
                  $tekst .= '</td></tr>';
                  
                  unset($ilosc_zamowien);
                  
            } 
            $tekst .= '</table>';
            //
            echo $tekst;
            //
            $db->close_query($sql);
            unset($listing_danych,$tekst,$tablica,$tablica_naglowek);        

        }
    }  
    
    // ******************************************************************************************************************************************************************
    // wyswietlanie listingu
    if (!isset($_GET['parametr'])) { 

        // wczytanie naglowka HTML
        include('naglowek.inc.php');
        ?>

        <!-- Skrypt do autouzupelniania -->             
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function() {
           $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_klienci.php', 50, 400 );
        });
        //]]>
        </script>     

        <script type="text/javascript">
        //<![CDATA[
        function podgladKoszyka(id_klienta) {
            $.colorbox( { href:"ajax/koszyk_klienta.php?uzytkownik_id=" + id_klienta, width:'1000', maxHeight:'90%', open:true, initialWidth:50, initialHeight:50 } ); 
        }
        //]]>
        </script>    


        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Klienci</div>

            <div id="wyszukaj">
                <form action="klienci/klienci.php" method="post" id="klienciForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span>Wyszukaj klienta:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="30" />
                </div>  
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Grupa:</span>
                    <?php
                    $tablica = Klienci::ListaGrupKlientow();
                    echo Funkcje::RozwijaneMenu('szukaj_grupa', $tablica, ((isset($_GET['szukaj_grupa'])) ? $filtr->process($_GET['szukaj_grupa']) : '')); ?>
                </div>  

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Status:</span>
                    <?php
                    $tablia_status= Array();
                    $tablia_status[] = array('id' => '0', 'text' => 'dowolny');
                    $tablia_status[] = array('id' => '1', 'text' => 'aktywny');
                    $tablia_status[] = array('id' => '2', 'text' => 'nieaktywny');
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : '')); ?>
                </div>  

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Typ:</span>
                    <?php
                    $tablia_typ = Array();
                    $tablia_typ[] = array('id' => '0', 'text' => 'dowolny');
                    $tablia_typ[] = array('id' => '1', 'text' => 'zarejestrowany');
                    $tablia_typ[] = array('id' => '2', 'text' => 'gość');
                    echo Funkcje::RozwijaneMenu('szukaj_typ', $tablia_typ, ((isset($_GET['szukaj_typ'])) ? $filtr->process($_GET['szukaj_typ']) : '')); ?>
                </div>  

               <div class="cl" style="height:9px"></div>

                <div class="wyszukaj_select">
                    <span style="width:95px">Koszyk:</span>
                    <?php
                    $tablia_koszyk = Array();
                    $tablia_koszyk[] = array('id' => '0', 'text' => 'dowolny');
                    $tablia_koszyk[] = array('id' => '1', 'text' => 'tak');
                    $tablia_koszyk[] = array('id' => '2', 'text' => 'nie');
                    echo Funkcje::RozwijaneMenu('szukaj_koszyk', $tablia_koszyk, ((isset($_GET['szukaj_koszyk'])) ? $filtr->process($_GET['szukaj_koszyk']) : '')); ?>
                </div> 

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Punkty:</span>
                    <?php
                    $tablia_typ = Array();
                    $tablia_typ[] = array('id' => '0', 'text' => 'wszyscy');
                    $tablia_typ[] = array('id' => '1', 'text' => 'tylko z punktami');
                    echo Funkcje::RozwijaneMenu('szukaj_punkty', $tablia_typ, ((isset($_GET['szukaj_punkty'])) ? $filtr->process($_GET['szukaj_punkty']) : '')); ?>
                </div>                

                <?php 
                // tworzy ukryte pola hidden do wyszukiwania - filtra  
                if (isset($_GET['sort'])) { 
                    echo '<div><input type="hidden" name="sort" value="'.$filtr->process($_GET['sort']).'" /></div>';
                }                
                ?>                

                <div class="wyszukaj_przycisk"><input type="image" alt="Szukaj" src="obrazki/ok.png" /></div>
                </form>
                
                <?php
                if ( Listing::wylaczFiltr(basename($_SERVER['SCRIPT_NAME'])) == true ) {
                  echo '<div id="wyszukaj_ikona"><a href="klienci/klienci.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?>                 
                
                <div style="clear:both"></div>
            </div>        
            
            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="klienci/klienci.php?sort=sort_a1">daty rejestracji malejąco</a>
            <a id="sort_a2" class="sortowanie" href="klienci/klienci.php?sort=sort_a2">daty rejestracji rosnąco</a>
            <a id="sort_a3" class="sortowanie" href="klienci/klienci.php?sort=sort_a3">nazwiska malejąco</a>
            <a id="sort_a4" class="sortowanie" href="klienci/klienci.php?sort=sort_a4">nazwiska rosnąco</a>
            <a id="sort_a5" class="sortowanie" href="klienci/klienci.php?sort=sort_a5">e-mail malejąco</a>
            <a id="sort_a6" class="sortowanie" href="klienci/klienci.php?sort=sort_a6">e-mail rosnąco</a>
            <a id="sort_a7" class="sortowanie" href="klienci/klienci.php?sort=sort_a7">ilość pkt malejąco</a>
            <div style="margin-left:77px">
                <a id="sort_a8" class="sortowanie" href="klienci/klienci.php?sort=sort_a8">ilość pkt rosnąco</a>            
            </div>
            </div>             

            <div id="pozycje_ikon">
                <div>
                    <a class="dodaj" href="klienci/klienci_dodaj.php">dodaj nowego klienta</a>
                </div> 
                <div id="legenda" style="float:right">
                    <span class="stalyklient"> stały klient</span>
                    <span class="bez_kont"> klient bez rejestracji</span>
                </div>                 
            </div>
            <div style="clear:both;"></div>               
        
            <div id="wynik_zapytania"></div>
            <div id="aktualna_pozycja">1</div>
            <div id="dolny_pasek_stron"></div>
            <div id="pokaz_ile_pozycji"></div>
            <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>
            
            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('klienci/klienci.php', $zapytanie, $ile_licznika, $ile_pozycji, 'customers_id'); ?>
            //]]>
            </script>              

        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
