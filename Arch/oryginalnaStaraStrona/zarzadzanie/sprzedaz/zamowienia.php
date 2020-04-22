<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if ( isset($_GET['zakladka']) ) unset($_GET['zakladka']);
if ( isset($_SESSION['waluta_zamowienia']) ) unset($_SESSION['waluta_zamowienia']);
if ( isset($_SESSION['waluta_zamowienia_symbol']) ) unset($_SESSION['waluta_zamowienia_symbol']);

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    Listing::postGet(basename($_SERVER['SCRIPT_NAME']));
        
    // id zamowien
    $ZamowieniaId = array();
    $ZamowieniaId[] = 0;
    
    // jezeli jest wyszukiwanie zamowien ze zmiana statusu
    if ( ( isset($_GET['szukaj_data_statusu_od']) && $_GET['szukaj_data_statusu_od'] != '' ) || ( isset($_GET['szukaj_data_statusu_do']) && $_GET['szukaj_data_statusu_do'] != '' ) ) {
    
         $warunki_szukania = '';

         if ( isset($_GET['szukaj_data_statusu_od']) && $_GET['szukaj_data_statusu_od'] != '' ) {
            $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_statusu_od'] . ' 00:00:00')));
            $warunki_szukania .= " and date_added >= '".$szukana_wartosc."'";
         }

         if ( isset($_GET['szukaj_data_statusu_do']) && $_GET['szukaj_data_statusu_do'] != '' ) {
            $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_statusu_do'] . ' 23:59:59')));
            $warunki_szukania .= " and date_added <= '".$szukana_wartosc."'";
         }      

         if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
            $warunki_szukania .= " and orders_status_id = '".(int)$_GET['szukaj_status']."'";
         }

         $warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
         
         $zapytanie = "SELECT orders_id FROM orders_status_history" . $warunki_szukania;
         $sql = $db->open_query($zapytanie);

         while ($info = $sql->fetch_assoc()) {
            $ZamowieniaId[] = $info['orders_id'];
         }
         
         $db->close_query($sql);
         unset($szukana_wartosc, $warunki_szukania, $zapytanie, $sql);
         
    }

    
    $warunki_szukania = '';
    // jezeli jest szukanie
    if (isset($_GET['szukaj']) && $_GET['szukaj'] != '' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj']);
        $warunki_szukania = " and (o.customers_email_address LIKE '%".$szukana_wartosc."%' OR o.customers_name LIKE '%".$szukana_wartosc."%' OR o.customers_company LIKE '%".$szukana_wartosc."%' OR o.customers_nip LIKE '%".$szukana_wartosc."%' OR o.delivery_name LIKE '%".$szukana_wartosc."%' OR o.delivery_company LIKE '%".$szukana_wartosc."%' OR o.billing_name LIKE '%".$szukana_wartosc."%' OR o.billing_company LIKE '%".$szukana_wartosc."%')";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_data_zamowienia_od']) && $_GET['szukaj_data_zamowienia_od'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_zamowienia_od'] . ' 00:00:00')));
        $warunki_szukania .= " and o.date_purchased >= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_data_zamowienia_do']) && $_GET['szukaj_data_zamowienia_do'] != '' ) {
        $szukana_wartosc = date('Y-m-d H:i:s', strtotime($filtr->process($_GET['szukaj_data_zamowienia_do'] . ' 23:59:59')));
        $warunki_szukania .= " and o.date_purchased <= '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_status']) && $_GET['szukaj_status'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_status']);
        $warunki_szukania .= " and o.orders_status = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_wysylka']) && $_GET['szukaj_wysylka'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_wysylka']);
        $warunki_szukania .= " and o.shipping_module = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['szukaj_platnosc']) && $_GET['szukaj_platnosc'] != '0' ) {
        $szukana_wartosc = $filtr->process($_GET['szukaj_platnosc']);
        $warunki_szukania .= " and o.payment_method = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['klient_id']) && (int)$_GET['klient_id'] != '' ) {
        $szukana_wartosc = (int)$_GET['klient_id'];
        $warunki_szukania .= " and o.customers_id = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( isset($_GET['szukaj_wartosc_zamowienia_od']) && (float)$_GET['szukaj_wartosc_zamowienia_od'] > 0 ) {
        $szukana_wartosc = (float)$_GET['szukaj_wartosc_zamowienia_od'];
        $warunki_szukania .= " and (ot.class = 'ot_total' and ot.value >= '".$szukana_wartosc."')";
        unset($szukana_wartosc);
    }  

    if ( isset($_GET['szukaj_wartosc_zamowienia_do']) && (float)$_GET['szukaj_wartosc_zamowienia_do'] > 0 ) {
        $szukana_wartosc = (float)$_GET['szukaj_wartosc_zamowienia_do'];
        $warunki_szukania .= " and (ot.class = 'ot_total' and ot.value <= '".$szukana_wartosc."')";
        unset($szukana_wartosc);
    }

    if ( isset($_GET['opiekun']) && (int)$_GET['opiekun'] > 0 ) {
        $szukana_wartosc = (int)$_GET['opiekun'];
        $warunki_szukania .= " and o.service = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }   

    if ( isset($_GET['typ_zam']) && (int)$_GET['typ_zam'] > 0 ) {
        $szukana_wartosc = (int)$_GET['typ_zam'];
        $warunki_szukania .= " and o.orders_source = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }    

    if ( isset($_GET['szukaj_numer']) && (int)$_GET['szukaj_numer'] > 0 ) {
        $szukana_wartosc = (int)$_GET['szukaj_numer'];
        $warunki_szukania .= " and o.orders_id = '".$szukana_wartosc."'";
        unset($szukana_wartosc);
    }
    
    if ( count($ZamowieniaId) > 1 ) {
        $warunki_szukania .= " and o.orders_id in (" . implode(',', $ZamowieniaId) . ")";
    } else {
        if ( ( isset($_GET['szukaj_data_statusu_od']) && $_GET['szukaj_data_statusu_od'] != '' ) || ( isset($_GET['szukaj_data_statusu_do']) && $_GET['szukaj_data_statusu_do'] != '' ) ) {
            $warunki_szukania .= " and o.orders_id in (0)";
        }
    }

    if ( $warunki_szukania != '' ) {
      //$warunki_szukania = preg_replace('/and/i', 'WHERE', $warunki_szukania, 1);
    }

    $zapytanie = "SELECT o.orders_id, o.invoice_proforma_date, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, o.customers_dummy_account, o.customers_company, o.customers_street_address, o.customers_postcode, o.customers_city, o.orders_status, o.orders_source, o.service, o.shipping_module, o.orders_adminnotes, ot.value, ot.class, ot.text as order_total, c.customers_dod_info 
                  FROM orders_total ot
                  RIGHT JOIN orders o ON o.orders_id = ot.orders_id 
                  LEFT JOIN customers c ON c.customers_id = o.customers_id
                  WHERE ot.class = 'ot_total' " . $warunki_szukania;

    // tworzenie paska do nastepnych okien - obliczanie ile bedzie podstron
    $ZapytanieDlaPozycji = "SELECT o.orders_id
                            FROM orders o 
                            LEFT JOIN orders_total ot ON o.orders_id = ot.orders_id AND ot.class = 'ot_total' 
                            " . $warunki_szukania;
    
    $sql = $db->open_query($ZapytanieDlaPozycji);
    $ile_pozycji = (int)$db->ile_rekordow($sql);

    $sql = $db->open_query($zapytanie);

    $ile_pozycji = (int)$db->ile_rekordow($sql); // ile jest wszystkich produktow
    $ile_licznika = ($ile_pozycji / ILOSC_WYNIKOW_NA_STRONIE);
    if ($ile_licznika == (int)$ile_licznika) { $ile_licznika = (int)$ile_licznika; } else { $ile_licznika = (int)$ile_licznika+1; }

    $db->close_query($sql);
    
    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'o.orders_id desc';
                break;
            case "sort_a2":
                $sortowanie = 'o.orders_id asc';
                break;                 
            case "sort_a3":
                $sortowanie = 'o.date_purchased desc';
                break;
            case "sort_a4":
                $sortowanie = 'o.date_purchased asc';
                break;                 
        }            
    } else { $sortowanie = 'orders_id desc'; }    
    
    $zapytanie .= " ORDER BY ".$sortowanie;    

    // ******************************************************************************************************************************************************************
    // obsluga listingu AJAX
    if (isset($_GET['parametr'])) {

        if ($ile_pozycji > 0) {
            
            $zapytanie .= " limit ".$_GET['parametr'];    

            $sql = $db->open_query($zapytanie);

            $listing_danych = new Listing();
            
            $tablica_naglowek = array(array('Info', 'center'),
                                      array('Akcja', 'center'),
                                      array('ID', 'center'),
                                      array('Klient', 'width:25%'),
                                      array('Data zamówienia', 'center'),
                                      array('Wartość', 'center'),
                                      array('Płatność', 'center', 'width:12%'),
                                      array('Dostawa', 'center', 'width:12%'),
                                      array('Status', 'center', 'width:12%'),
                                      array('Typ', 'center', 'white-space:nowrap;'));

            echo $listing_danych->naglowek($tablica_naglowek);
            
            $tekst = '';
            
            while ($info = $sql->fetch_assoc()) {
            
                  if (isset($_GET['id_poz']) && (int)$_GET['id_poz'] == $info['orders_id']) {
                     $tekst .= '<tr class="pozycja_on" id="sk_'.$info['orders_id'].'">';
                   } else {
                     $tekst .= '<tr class="pozycja_off" id="sk_'.$info['orders_id'].'">';
                  }         

                  $tablica = array();
                  
                  // informacje o uwagach na koncie klienta
                  $uwagi = '';
                  if ( $info['customers_dod_info'] != '' || $info['orders_adminnotes'] != '' ) {
                      $uwagi = '<span class="malaUwaga chmurka" title="Dodatkowa informacja obsługi sklepu"></span>';
                  }

                  $tablica[] = array('<div id="zamowienie_'.$info['orders_id'].'" class="zmzoom_zamowienie"><div class="podglad_zoom"></div><img src="obrazki/info_duze.png" alt="Szczegóły" /></div>' . $uwagi,'','width:30px');

                  $tablica[] = array('<input type="checkbox" style="border:0px" name="opcja[]" value="'.$info['orders_id'].'" /><input type="hidden" name="id[]" value="'.$info['orders_id'].'" />','center');

                  // pobranie faktury proforma
                  $proforma = '';
                  if ( $info['invoice_proforma_date'] > 0 ) {
                      $proforma = '<span class="malaProforma chmurka" title="Proforma pobrana przez klienta: ' . date('d-m-Y H:i', $info['invoice_proforma_date']) . '"></span>';
                  }

                  $tablica[] = array($info['orders_id'] . $proforma,'center');
                  unset($uwagi);
                  
                  $wyswietlana_nazwa = '';
                  if ( $info['customers_id'] > 0 ) {
                       $wyswietlana_nazwa = '<a class="zamKlient" href="klienci/klienci_edytuj.php?id_poz=' . $info['customers_id'] . '">';
                  }
                  
                  if ( $info['customers_company'] != '' ) {
                       $wyswietlana_nazwa .= '<span class="firma">'.$info['customers_company'] . '</span><br />';
                  }
                  
                  $wyswietlana_nazwa .= $info['customers_name'] . '<br />';
                  $wyswietlana_nazwa .= $info['customers_street_address']. '<br />';
                  $wyswietlana_nazwa .= $info['customers_postcode']. ' ' . $info['customers_city'] . '<br />';

                  if ( $info['customers_id'] > 0 ) {
                       $wyswietlana_nazwa .= '</a>';
                  }    
                  
                  // jezeli staly klient
                  $iloscZam = (int)Klienci::pokazIloscZamowienKlienta($info['customers_id']);
                  if ( $iloscZam > 1 ) {
                       $wyswietlana_nazwa = '<img style="float:right" src="obrazki/medal.png" alt="Stały klient" title="Stały klient - ilość zamówień: ' . $iloscZam . '" />' . $wyswietlana_nazwa;
                  }
                  unset($iloscZam);

                  // jezeli jest gosc wyswietli ikonke
                  if ( $info['customers_dummy_account'] == '1' ) { 
                       $wyswietlana_nazwa = '<img style="float:right" src="obrazki/gosc.png" alt="Klient bez rejestracji" title="Klient bez rejestracji" />' . $wyswietlana_nazwa;
                  }
                  
                  $tablica[] = array($wyswietlana_nazwa,'','line-height:17px');        
                  unset($wyswietlana_nazwa);

                  $tablica[] = array(date('d-m-Y H:i',strtotime($info['date_purchased'])),'center');
                  $tablica[] = array('<span class="infocena">'.$info['order_total'].'</span>','right', 'white-space:nowrap;');
                  $tablica[] = array($info['payment_method'],'center');

                  $wysylka   = $info['shipping_module'];
                  $zapytanie_dostawy = "SELECT * FROM orders_shipping WHERE orders_id = '" . (int)$info['orders_id'] . "'";
                  $sql_dostawy = $db->open_query($zapytanie_dostawy);
                  if ((int)$db->ile_rekordow($sql_dostawy) > 0) {
                    $wysylka = $wysylka . '<img class="utworzonaWysylka" src="obrazki/tak.png" alt="Utworzono wysyłki" title="Utworzono wysyłki" />';
                  }
                  $db->close_query($sql_dostawy);
                  unset($sql_dostawy, $zapytanie_dostawy);           

                  $tablica[] = array($wysylka,'center');
                  
                  // opiekun zamowienia
                  $zapytanie_tmp = "select distinct * from admin where admin_id = '".(int)$info['service']."'";
                  $sqls = $db->open_query($zapytanie_tmp);
                  if ((int)$db->ile_rekordow($sqls) > 0) {
                      $infs = $sqls->fetch_assoc();
                      $Opiekun = '<span class="opiekun">Opiekun:<span>'.$infs['admin_firstname'] . ' ' . $infs['admin_lastname'] . '</span></span>';
                      $db->close_query($sqls);
                     } else {
                      $Opiekun = '';
                  }
                  unset($zapytanie_tmp, $infs);    
                  //
                                  
                  $tablica[] = array(Sprzedaz::pokazNazweStatusuZamowienia($info['orders_status'], $_SESSION['domyslny_jezyk']['id']) . $Opiekun,'center');
                  
                  // 1 - zamowienie ze sklepu z rejestracja
                  // 2 - zamowienie ze sklepu bez rejestracji
                  // 3 - zamowienie z Allegro
                  // 4 - zamowienie dodane przez admina
                  
                  $TypZamowienia = '';
                  switch ($info['orders_source']) {
                    case "3":
                        $TypZamowienia = '<img src="obrazki/allegro_lapka.png" alt="Zamówienie z Allegro" title="Zamówienie z Allegro" />';
                        break;                 
                    case "4":
                        $TypZamowienia = '<img src="obrazki/raczka.png" alt="Zamówienie ręczne" title="Zamówienie ręczne" />';
                        break;             
                  }                     

                  $tablica[] = array($TypZamowienia,'center');
                  
                  // zmienne do przekazania
                  $zmienne_do_przekazania = '?id_poz='.(int)$info['orders_id']; 
                  
                  
                  $tekst .= $listing_danych->pozycje($tablica);
                  
                  $tekst .= '<td class="rg_right">';
                  
                  $tekst .= '<div class="zakSzczegolowe">';
                  $tekst .= '<a href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'"><img src="obrazki/zobacz.png" alt="Szczegóły zamówienia" title="Szczegóły zamówienia" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'&zakladka=1"><img src="obrazki/wysylki.png" alt="Generowanie wysyłek" title="Generowanie wysyłek" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'&zakladka=2"><img src="obrazki/produkty.png" alt="Zakupione produkty" title="Zakupione produkty" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_szczegoly.php'.$zmienne_do_przekazania.'&zakladka=3"><img src="obrazki/historia.png" alt="Historia zamówień" title="Historia zamówień" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_wyslij_email.php'.$zmienne_do_przekazania.'"><img src="obrazki/wyslij_mail.png" alt="Wyślij e-mail z zamówieniem" title="Wyślij e-mail z zamówieniem" /></a>';
                  $tekst .= '</div>';
                  
                  $tekst .= '<a href="sprzedaz/zamowienia_zamowienie_pdf.php'.$zmienne_do_przekazania.'"><img src="obrazki/zamowienie_pdf.png" alt="Wydruk zamówienia" title="Wydruk zamówienia" /></a>';
                  $tekst .= '<a href="sprzedaz/zamowienia_faktura_proforma.php'.$zmienne_do_przekazania.'"><img src="obrazki/proforma_pdf.png" alt="Wydruk faktury proforma" title="Wydruk faktury proforma" /></a>'; 
                  $tekst .= '<a href="sprzedaz/zamowienia_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';                  
                  $tekst .= '<a href="sprzedaz/zamowienia_pobierz.php'.$zmienne_do_przekazania.'"><img src="obrazki/export.png" alt="Pobierz" title="Pobierz" /></a>'; 
                  
                  $tekst .= '</td></tr>';
                  
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
          
        <script type="text/javascript">
        //<![CDATA[
        $(document).ready(function() {
            $.AutoUzupelnienie( 'szukaj', 'Podpowiedzi', 'ajax/autouzupelnienie_zamowienia.php', 50, 400 );
            
            $('input.datepicker').Zebra_DatePicker({
              format: 'd-m-Y',
              inside: false,
              readonly_element: false
            });             

           $('#akcja_dolna').change(function() {
             if ( this.value == '0' || this.value == '2' ) {
               $("#page").load('sprzedaz/blank.php');
             }
             if ( this.value == '1' ) {
               $("#page").load('sprzedaz/zamowienia_zmien_status_multi.php');
             }
           });
        });
        //]]>
        </script>

        <div id="caly_listing">
        
            <div id="ajax"></div>
            
            <div id="naglowek_cont">Zamówienia</div>

            <div id="wyszukaj">
                <form action="sprzedaz/zamowienia.php" method="post" id="zamowieniaForm" class="cmxform">

                <div id="wyszukaj_text">
                    <span style="width:90px">Wyszukaj:</span>
                    <input type="text" name="szukaj" id="szukaj" value="<?php echo ((isset($_GET['szukaj'])) ? Funkcje::formatujTekstInput($filtr->process($_GET['szukaj'])) : ''); ?>" size="30" />
                </div>  
                
                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span style="width:112px">Numer zamówienia:</span>
                    <input type="text" id="numer" name="szukaj_numer" value="<?php echo ((isset($_GET['szukaj_numer'])) ? $filtr->process($_GET['szukaj_numer']) : ''); ?>" size="10" />
                </div>  

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Data złożenia:</span>
                    <input type="text" id="data_zamowienia_od" name="szukaj_data_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_od'])) ? $filtr->process($_GET['szukaj_data_zamowienia_od']) : ''); ?>" size="10" class="datepicker" />&nbsp;do&nbsp;
                    <input type="text" id="data_zamowienia_do" name="szukaj_data_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_data_zamowienia_do'])) ? $filtr->process($_GET['szukaj_data_zamowienia_do']) : ''); ?>" size="10" class="datepicker" />
                </div>  

                <div class="cl" style="height:9px"></div>
                
                <div class="wyszukaj_select">
                    <span style="width:90px">Status:</span>
                    <?php
                    $tablia_status= Array();
                    $tablia_status = Sprzedaz::ListaStatusowZamowien(true);
                    echo Funkcje::RozwijaneMenu('szukaj_status', $tablia_status, ((isset($_GET['szukaj_status'])) ? $filtr->process($_GET['szukaj_status']) : ''), ' style="width:200px"'); ?>
                </div>                 

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Rodzaj wysyłki:</span>
                    <?php
                    $tablia_typ = Array();
                    $tablia_typ = Sprzedaz::ListaWysylekZamowien( true );
                    echo Funkcje::RozwijaneMenu('szukaj_wysylka', $tablia_typ, ((isset($_GET['szukaj_wysylka'])) ? $filtr->process($_GET['szukaj_wysylka']) : ''), ' style="width:230px"'); ?>
                </div>  

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Rodzaj płatności:</span>
                    <?php
                    $tablia_typ = Array();
                    $tablia_typ = Sprzedaz::ListaPlatnosciZamowien( true );
                    echo Funkcje::RozwijaneMenu('szukaj_platnosc', $tablia_typ, ((isset($_GET['szukaj_platnosc'])) ? $filtr->process($_GET['szukaj_platnosc']) : ''), ' style="width:200px"'); ?>
                </div>  
                
                <div class="cl" style="height:9px"></div>

                <div class="wyszukaj_select">
                    <span style="width:90px">Opiekun:</span>
                    <?php
                    // pobieranie informacji od uzytkownikach
                    $zapytanie_tmp = "select * from admin where admin_groups_id = '2' order by admin_lastname";
                    $sqls = $db->open_query($zapytanie_tmp);
                    //
                    $tablica_user = array();
                    $tablica_user[] = array('id' => 0, 'text' => 'dowolny');
                    while ($infs = $sqls->fetch_assoc()) { 
                    $tablica_user[] = array('id' => $infs['admin_id'], 'text' => $infs['admin_firstname'] . ' ' . $infs['admin_lastname']);
                    }
                    $db->close_query($sqls); 
                    unset($zapytanie_tmp, $infs);    
                    //
                    echo Funkcje::RozwijaneMenu('opiekun', $tablica_user, ((isset($_GET['opiekun'])) ? $filtr->process($_GET['opiekun']) : ''), ' style="width:150px"'); ?>
                </div>

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Typ zamówienia:</span>
                    <?php
                    $tablia_typ = Array();
                    $tablia_typ = Sprzedaz::TypyZamowien( true );
                    echo Funkcje::RozwijaneMenu('typ_zam', $tablia_typ, ((isset($_GET['typ_zam'])) ? $filtr->process($_GET['typ_zam']) : '99'), ' style="width:220px"'); ?>
                </div>     

                <div class="wyszukaj_select" style="margin-left:10px;">
                    <span>Wartość zamówienia:</span>
                    <input type="text" name="szukaj_wartosc_zamowienia_od" value="<?php echo ((isset($_GET['szukaj_wartosc_zamowienia_od'])) ? $filtr->process($_GET['szukaj_wartosc_zamowienia_od']) : ''); ?>" size="6" /> do
                    <input type="text" name="szukaj_wartosc_zamowienia_do" value="<?php echo ((isset($_GET['szukaj_wartosc_zamowienia_do'])) ? $filtr->process($_GET['szukaj_wartosc_zamowienia_do']) : ''); ?>" size="6" />
                </div> 

                <div class="cl" style="height:9px"></div>

                <div class="wyszukaj_select">
                    <span style="width:90px">Zmiana statusu:</span>
                    <input type="text" id="data_statusu_od" name="szukaj_data_statusu_od" value="<?php echo ((isset($_GET['szukaj_data_statusu_od'])) ? $filtr->process($_GET['szukaj_data_statusu_od']) : ''); ?>" size="10" class="datepicker" /> do 
                    <input type="text" id="data_dodania_do" name="szukaj_data_statusu_do" value="<?php echo ((isset($_GET['szukaj_data_statusu_do'])) ? $filtr->process($_GET['szukaj_data_statusu_do']) : ''); ?>" size="10" class="datepicker" />
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
                  echo '<div id="wyszukaj_ikona"><a href="sprzedaz/zamowienia.php?filtr=nie"><img src="obrazki/reset_szukaj.png" alt="Anuluj wyszukiwanie" title="Anuluj wyszukiwanie" /></a></div>';
                }
                ?> 

                <div style="clear:both"></div>
            </div>        
            
            <form action="sprzedaz/zamowienia_akcja.php" method="post" class="cmxform">

            <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a1">numeru malejąco</a>
            <a id="sort_a2" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a2">numeru rosnąco</a>
            <a id="sort_a3" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a3">daty malejąco</a>
            <a id="sort_a4" class="sortowanie" href="sprzedaz/zamowienia.php?sort=sort_a4">daty rosnąco</a>
            </div>             

            <div id="pozycje_ikon">
                <div>
                  <a class="dodaj" href="sprzedaz/zamowienia_dodaj.php<?php echo Funkcje::Zwroc_Wybrane_Get(array('klient_id')); ?>">dodaj nowe zamówienie</a>
                </div>    
                <div id="legenda" style="float:right">
                  <span class="pobranaproforma"> klient pobrał proformę</span>
                  <span class="stalyklient"> stały klient</span>
                  <span class="gosc"> klient bez rejestracji</span>
                  <span class="algro"> zamówienie z Allegro</span>
                  <span class="recz"> zamówienie ręczne</span>
                </div>                  
            </div>

            <div style="clear:both;"></div>               
        
            <table style="width:1020px">
                <tr>
                    <td style="width:100%;vertical-align:top;" colspan="2">

                      <div id="wynik_zapytania"></div>
                      <div id="aktualna_pozycja">1</div>

                      <div id="akcja">
                        <div class="lf"><img src="obrazki/strzalka.png" alt="" /></div>
                        <div class="lf" style="padding-right:20px">
                          <span onclick="akcja(1)">zaznacz wszystkie</span>
                          <span onclick="akcja(2)">odznacz wszystkie</span>
                        </div>
                        <div id="akc">
                          Wykonaj akcje: 
                          <select name="akcja_dolna" id="akcja_dolna">
                            <option value="0"></option>
                            <option value="1">zmień status zaznaczonych</option>
                            <option value="2">wydruk zamówienia PDF</option>
                            <option value="3">połącz wybrane zamowienia</option>
                            <option value="4">pobierz zamówienia w formacie CSV</option>
                          </select>
                        </div>
                        <div style="clear:both;"></div>
                      </div>
                    </td>
                 </tr>

                <tr><td><div id="page"></div></td></tr>

                 <tr>
                    <td>

                      <div id="dolny_pasek_stron"></div>
                      <div id="pokaz_ile_pozycji"></div>
                      <div id="ile_rekordow"><?php echo $ile_pozycji; ?></div>

                    </td>
                </tr>

                <?php if ($ile_pozycji > 0) { ?>
                <tr>
                  <?php if (isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) { ?>
                    <td align="left"><button type="button" class="przyciskNon" onclick="cofnij('klienci','?id_poz=<?php echo (int)$_GET['klient_id']; ?><?php echo Funkcje::Zwroc_Get(array('x','y','id_poz','klient_id')); ?>', 'klienci');">Powrót</button>    
                    </td>            
                  <?php } ?>                
                  <td align="right" id="zapisz_zmiany"><input type="submit" class="przyciskBut" value="Zapisz zmiany" /></td>
                 </tr>
                <?php } else { ?>                
                <tr>
                  <?php if (isset($_GET['klient_id']) && $_GET['klient_id'] != '' ) { ?>
                    <td align="left"><button type="button" class="przyciskNon" onclick="cofnij('klienci','?id_poz=<?php echo (int)$_GET['klient_id']; ?><?php echo Funkcje::Zwroc_Get(array('x','y','id_poz','klient_id')); ?>', 'klienci');">Powrót</button>    
                    </td>            
                  <?php } ?>                
                 </tr>
                <?php } ?>                
            </table>

            </form>

            <script type="text/javascript">
            //<![CDATA[
            $("#wynik_zapytania").html('<div style="padding:10px">Trwa ładowanie danych ...</div>');
            var skocz = '<?php echo ((isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0) ? (int)$_GET['id_poz'] : ''); ?>';
            <?php Listing::pokazAjax('sprzedaz/zamowienia.php', $zapytanie, $ile_licznika, $ile_pozycji, 'orders_id'); ?>
            //]]>
            </script>              
            
        </div>
                    
        <?php include('stopka.inc.php'); ?>

    <?php }

}
?>
