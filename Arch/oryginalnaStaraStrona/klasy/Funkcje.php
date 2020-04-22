<?php

class Funkcje {

  public static function czyNiePuste($wartosc) {
      if (is_array($wartosc)) {
        if (count($wartosc) > 0) {
          return true;
        } else {
          return false;
        }
      } else {
        if ( (is_string($wartosc) || is_int($wartosc)) && ($wartosc != '') && ($wartosc != 'NULL') && (strlen(trim($wartosc)) > 0) && ($wartosc!='0000-00-00 00:00:00') && ($wartosc!='0000-00-00') && ($wartosc!='0.00')  && ($wartosc!='0.000')) {
          return true;
        } else {
          return false;
        }
      }
  }

  // Zapisanie i wyswietlanie zapytan do bazy danych - tylko dla celow programistycznych
  public static function pokazZapytania() {
    $time_start = explode(' ', PAGE_PARSE_START_TIME);
    $time_end = explode(' ', microtime());
    $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
    $ciag = '<div style="text-align: center;" class="tekst10">Czas przetwarzania strony: <b>' . $parse_time . ' s</b><br />Ilość zapytań: <b>' . $GLOBALS['zapytaniaIlosc'] . ' </b></div>';
    if (WYSWIETL_ZAPYTANIA) {
      $ciag .= '<b>Wykonane zapytania do bazy:</b> ';
      Funkcje::drukujTablice($GLOBALS['zapytaniaTresc']);
      $ciag .= '</div>';
    }
    return $ciag;
  }

  
  // funkcja generujaca i wyswietlajaca miniaturki zdjec
  public static function pokazObrazek( $plik_zdjecia, $alt, $szerokosc, $wysokosc, $ikony = array(), $parametr = '', $wielkosc = 'maly', $skaluj = true, $ladowanie = false, $znakWodny = true ) {
    global $thumb;
    
    // jezeli jest wylaczona obsluga preloadera obrazkow
    if ( PRELOAD_OBRAZKOW == 'nie' ) {
         $ladowanie = false;
    }
    
    if ($skaluj == false) {
        $thumb->Square = false;
    } else {
        $thumb->Square = true;
    }

    $katalog_zdjec      = KATALOG_ZDJEC;
    $katalog_miniaturek = 'mini';
    $prefix_miniaturek  = 'px_';

    if ( $wielkosc == 'maly' ) {
      $wielkosc_obrazka = OBRAZ_COPYRIGHT_MALY;
      $pokaz_copyright  = TEKST_COPYRIGHT_POKAZ_MINI;
      $pokaz_watermark  = OBRAZ_COPYRIGHT_POKAZ_MINI;
    } elseif ( $wielkosc == 'sredni' ) {
      $wielkosc_obrazka = OBRAZ_COPYRIGHT_SREDNI;
      $pokaz_copyright  = TEKST_COPYRIGHT_POKAZ_MINI;
      $pokaz_watermark  = OBRAZ_COPYRIGHT_POKAZ_MINI;
    } elseif ( $wielkosc == 'zoom' ) {
      $wielkosc_obrazka = OBRAZ_COPYRIGHT_ZOOM;
      $pokaz_copyright  = TEKST_COPYRIGHT_POKAZ_MINI;
      $pokaz_watermark  = OBRAZ_COPYRIGHT_POKAZ_MINI;
    } elseif ( $wielkosc == 'duzy' ) {
      $wielkosc_obrazka = OBRAZ_COPYRIGHT_DUZY;
      $pokaz_copyright  = TEKST_COPYRIGHT_POKAZ;
      $pokaz_watermark  = OBRAZ_COPYRIGHT_POKAZ;
    }

    if ( $pokaz_copyright == 'tak' && $pokaz_watermark == 'nie' ) {
      $prefix_miniaturek  = 'cpx_';
    } elseif ( $pokaz_copyright == 'tak' && $pokaz_watermark == 'tak' ) {
      $prefix_miniaturek  = 'cwpx_';
    } elseif ( $pokaz_copyright == 'nie' && $pokaz_watermark == 'tak' ) {
      $prefix_miniaturek  = 'wpx_';
    } elseif ( $pokaz_copyright == 'nie' && $pokaz_watermark == 'nie' ) {
      $prefix_miniaturek  = 'px_';
    }

    // Sprawdza czy przekazana zmienna z plikiem nie jest pusta
    if ( ($plik_zdjecia == '') || ($plik_zdjecia == 'NULL') || (strlen(trim($plik_zdjecia)) == 0) || pathinfo($plik_zdjecia, PATHINFO_EXTENSION) == 'swf' ) {
      return '';
    }

    // Sprawdza czy przekazana zmienna z plikiem zawiera adres URL
    $czy_jest_url = strpos($plik_zdjecia, 'http');
    if ($czy_jest_url !== false) {
      $adres_zdjecia =  preg_replace("/((http|https|ftp):\/\/)?([^\/]+)(.*)/si", "$4", $plik_zdjecia);
      $plik_zdjecia = str_replace($katalog_zdjec,'',$adres_zdjecia);
    }

    $znaczki = array("%5B", "%5D", "%20");
    $nawiasy = array("[", "]", " ");
    $plik_zdjecia = str_replace($znaczki, $nawiasy, $plik_zdjecia);

    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;

    // Sprawdza czy istnieje na serwerze plik przekazany do funkcji
    if ( is_file($sciezka_bezwgledna_do_pliku) ) {
      $plik_zdjecia = $plik_zdjecia;
    } else {
      if ( POKAZ_DOMYSLNY_OBRAZEK == 'tak' ) {
        $plik_zdjecia = 'domyslny.gif';
        if ( !is_file(KATALOG_SKLEPU . $katalog_zdjec . '/' .$plik_zdjecia ) ) {

            //utworzenie obrazka domyslny.gif jezeli nie ma takiego na serwerze
            $tekst_na_zdjeciu = "brak foto";
            $font  = 3;
            $szerokosc_czcionki = ImageFontWidth($font);
            $wysokosc_czcionki = ImageFontHeight($font);

            $szerokosc_tekstu = $szerokosc_czcionki * strlen($tekst_na_zdjeciu);
            $pozycja_poziom = ceil(($szerokosc - $szerokosc_tekstu) / 2);
            $wysokosc_tekstu = $wysokosc_czcionki;
            $pozycja_pion = ceil(($wysokosc - $wysokosc_tekstu) / 2);

            $image = imagecreatetruecolor ($szerokosc,$wysokosc);
            $white = imagecolorallocate ($image,255,255,255);
            $black = imagecolorallocate ($image,0,0,0);
            imagefill($image,0,0,$white);
            imagestring ($image,$font,$pozycja_poziom,$pozycja_pion,$tekst_na_zdjeciu,$black);
            imagegif($image,KATALOG_SKLEPU . $katalog_zdjec . '/' .$plik_zdjecia);
            imagedestroy($image);
        }
      } else {
        return '';
      }
    }
    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;
    $sciezka_wgledna_do_pliku = dirname($katalog_zdjec . '/' . $plik_zdjecia);

    // Pobranie danych o skladowych elementach sciezki do pliku
    $info = pathinfo($sciezka_bezwgledna_do_pliku);

    $nazwa_pliku_miniaturki = $szerokosc.$prefix_miniaturek.$info["basename"];
    
    // Jezeli sa ikony na obrazku
    $Ikona = '';
    if (count($ikony) > 0 && IKONY_NA_ZDJECIACH == 'tak') {
        //
        if ($ikony['nowosc'] == '1' && IKONY_NA_ZDJECIACH_NOWOSCI == 'tak') {
            $Ikona = ((IKONY_NA_ZDJECIACH_ILOSC == 'jedna') ? '' : $Ikona) . '<em class="Nowosc_'.$_SESSION['domyslnyJezyk']['kod'].' Ikona"></em>';
        }
        if ($ikony['promocja'] == '1' && IKONY_NA_ZDJECIACH_PROMOCJE == 'tak') {
            $Ikona = ((IKONY_NA_ZDJECIACH_ILOSC == 'jedna') ? '' : $Ikona) . '<em class="Promocja_'.$_SESSION['domyslnyJezyk']['kod'].' Ikona"></em>';
        }
        if ($ikony['polecany'] == '1' && IKONY_NA_ZDJECIACH_POLECANE == 'tak') {
            $Ikona = ((IKONY_NA_ZDJECIACH_ILOSC == 'jedna') ? '' : $Ikona) . '<em class="Polecany_'.$_SESSION['domyslnyJezyk']['kod'].' Ikona"></em>';
        }        
        if ($ikony['hit'] == '1' && IKONY_NA_ZDJECIACH_NASZ_HIT == 'tak') {
            $Ikona = ((IKONY_NA_ZDJECIACH_ILOSC == 'jedna') ? '' : $Ikona) . '<em class="Hit_'.$_SESSION['domyslnyJezyk']['kod'].' Ikona"></em>';
        }
        if ($ikony['darmowa_dostawa'] == '1' && IKONY_NA_ZDJECIACH_DOSTAWA == 'tak') {
            $Ikona = ((IKONY_NA_ZDJECIACH_ILOSC == 'jedna') ? '' : $Ikona) . '<em class="Dostawa_'.$_SESSION['domyslnyJezyk']['kod'].' Ikona"></em>';
        }        
        //
        $Ikona = '<span class="IkonkiProduktu">' . $Ikona . '</span>';
        //        
    }
    //

    if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki) ) {

      $miniaturka =  $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;
      //  title="'.$alt.'"
      
      // preloader
      if ( $ladowanie == true && $_SESSION['mobile'] != 'tak' && WygladMobilny::UrzadzanieMobilne() == false ) {
      
           return $Ikona . '<img data-src-original="' . $miniaturka .'" width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' src="' . KATALOG_ZDJEC . '/loader.gif" '.$parametr.' alt="' . $alt . '" />';
           
         } else {
         
           return $Ikona . '<img src="' . $miniaturka . '" width="' . $szerokosc . '" ' . (($skaluj == true) ? 'height="' . $wysokosc . '"' : '') . ' '.$parametr.' alt="' . $alt . '" />';
           
      }

    } else { 

      // Tablica przedrostkow plikow zaleznych od ustawionych zabezpieczen
      $tablica_przedrostkow = array();
      $tablica_przedrostkow[]  = 'cpx_';
      $tablica_przedrostkow[]  = 'cwpx_';
      $tablica_przedrostkow[]  = 'wpx_';
      $tablica_przedrostkow[]  = 'px_';

      // Sprawdza czy istnieje katalog na miniaturki - jesli nie to go tworzy
      if (is_dir($info['dirname'] . '/' . $katalog_miniaturek) == false) {
        $old_mask = umask(0);
        mkdir($info['dirname'] . '/' . $katalog_miniaturek, 0777, true);
        umask($old_mask);
      }

      // Usuwa miniaturki, ktore nie spelniaja aktualnych warunkow zabezpieczenia
      for ( $i = 0, $c = count($tablica_przedrostkow); $i < $c; $i++ ) {
        if ( $tablica_przedrostkow[$i] != $prefix_miniaturek ) {
          if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $szerokosc.$tablica_przedrostkow[$i].$info["basename"]) ) {
            @unlink($info['dirname'] . '/' . $katalog_miniaturek . '/' . $szerokosc.$tablica_przedrostkow[$i].$info["basename"]);
          }
        }
      }

      // Generowanie miniaturki
      $file = $sciezka_bezwgledna_do_pliku;
      $thumb -> Thumbwidth        = $szerokosc;
      $thumb -> Thumbheight       = $wysokosc;
      $thumb -> Thumblocation     = $info['dirname'] . '/' . $katalog_miniaturek . '/';
      $thumb -> Thumbprefix       = $szerokosc.$prefix_miniaturek;
      $thumb -> Thumbfilename     = '';
      $thumb -> Copyright         = ( $pokaz_copyright == 'tak' && $znakWodny == true ? true : false );
      $thumb -> Watermark         = ( $pokaz_watermark == 'tak' && $znakWodny == true ? true : false );
      $thumb -> Watermarkfilename = KATALOG_ZDJEC . '/'.$wielkosc_obrazka;

      $thumb -> Createthumb($file,'file');

      $miniaturka =  '/' . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $nazwa_pliku_miniaturki;
      
      unset($thumb);
      // preloader
      if ( $ladowanie == true ) {
      
           return $Ikona . '<img data-src-original="' . $miniaturka .'" src="' . KATALOG_ZDJEC . '/loader.gif" '.$parametr.' alt="'.$alt.'" />';
           
         } else {
         
           return $Ikona . '<img src="' . $miniaturka .'" '.$parametr.' alt="'.$alt.'" />';
           
      }      

    }

  }  

  // funkcja przycinajaca tekst do okreslonej ilosci znakow
  public static function przytnijTekst($tekst, $dlugosc = 250, $zakonczenie = '&#8230;') {

    if ( mb_strlen($tekst) < $dlugosc ) {
      return $tekst;
    }

    $tekst = str_replace('&nbsp;', ' ', $tekst);
    $tekst = str_replace(array("\r\n", "\r", "\n"), ' ', $tekst);

    if (mb_strlen($tekst) <= $dlugosc) {
      return $tekst;
    }

    $wynik = "";
    foreach (explode(' ', trim($tekst)) as $val) {
      $wynik .= $val.' ';

      if (mb_strlen($wynik) >= $dlugosc) {
        $wynik = trim($wynik);
        return (mb_strlen($wynik) == mb_strlen($tekst)) ? $wynik : $wynik.$zakonczenie;
      }       
    }
  }

  // funkcja losujaca elementy z tablicy
  public static function wylosujElementyTablicyJakoTekst($tablicaWejsciowa, $LimitZapytania = 1) {

    $wynik = array();
    //$tablicaWejsciowa = array_unique($tablicaWejsciowa);

    if ( count($tablicaWejsciowa) < $LimitZapytania ) {
      $LimitZapytania = count($tablicaWejsciowa);
    }

    srand ((float) microtime() * 10000000);
    $LosowaTablica = array_rand($tablicaWejsciowa, $LimitZapytania);

    if ( count($LosowaTablica) > 1 ) {
      foreach ( $LosowaTablica as $val) {
        $wynik[] = $tablicaWejsciowa[$val];
      }
    } else {
      $wynik[] = $tablicaWejsciowa[$LosowaTablica];
    }

    return implode(',',$wynik);

  }

  // funkcja losujaca elementy z tablicy
  public static function wylosujElementyTablicyJakoTablica($tablicaWejsciowa, $LimitZapytania = 1) {

    $wynik = array();
    //$tablicaWejsciowa = array_unique($tablicaWejsciowa);

    if ( count($tablicaWejsciowa) < $LimitZapytania ) {
      $LimitZapytania = count($tablicaWejsciowa);
    }

    srand ((float) microtime() * 10000000);
    $LosowaTablica = array_rand($tablicaWejsciowa, $LimitZapytania);


    if ( count($LosowaTablica) > 1 ) {
      foreach ( $LosowaTablica as $val) {
        $wynik[] = $tablicaWejsciowa[$val];
      }
    } else {
      $wynik[] = $tablicaWejsciowa[$LosowaTablica];
    }

    return $wynik;

  }
  
  // funkcja generujaca rozwijane menu SELECT
  public static function RozwijaneMenu($nazwa, $wartosc, $default = '', $parametry = '') {
    $wynik = '<select name="' . $nazwa . '"';

    if (Funkcje::czyNiePuste($parametry)) $wynik .= ' ' . $parametry;

    $wynik .= '>';

    if (empty($default) && ( (isset($_GET[$nazwa]) && is_string($_GET[$nazwa])) || (isset($_POST[$nazwa]) && is_string($_POST[$nazwa])) ) ) {
      if (isset($_GET[$nazwa]) && is_string($_GET[$nazwa])) {
        $default = stripslashes($_GET[$nazwa]);
      } elseif (isset($_POST[$nazwa]) && is_string($_POST[$nazwa])) {
        $default = stripslashes($_POST[$nazwa]);
      }
    }

    for ($i = 0, $n = count($wartosc); $i < $n; $i++) {
      $ciag_tekstu = $wartosc[$i]['text'];
      
      $wynik .= '<option value="' . $wartosc[$i]['id'] . '"';
      if ($default == '') {
          if ($wartosc[$i]['id'] == '0') {
              $wynik .= ' selected="selected"';
          }
        } else {      
          if ($default == $wartosc[$i]['id']) {
            $wynik .= ' selected="selected"';
          }
      }

      $wynik .= '>' . $ciag_tekstu . '</option>';
    }
    $wynik .= '</select>';

    return $wynik;
  }  
  
  // funkcja podstawiajaca wartosci pod zmienne w szablonach maili
  public static function parsujZmienne($tekst){

    $szukanyCiag          = "/\{([a-zA-Z0-9_]+)\}/i";
    $funkcjaZamiany       = 'Funkcje::podstawZmienne';

    return preg_replace_callback($szukanyCiag, $funkcjaZamiany, $tekst);
  }

  public static function podstawZmienne($matches) {
      return constant($matches[1]);
  }

  // funkcja kodujaca haslo
  public static function zakodujHaslo($tekst) {
    $haslo = '';
    for ($i=0; $i<10; $i++) {
      $haslo .= Funkcje::losowaWartosc();
    }
    $salt = substr(md5($haslo), 0, 2);
    $haslo = md5($salt . $tekst) . ':' . $salt;
    return $haslo;
  }

  // funkcja zwaracjaca losowa wartosc liczbowa
  public static function losowaWartosc($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }
    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  // funkcja generujaca losowe haslo
  public static function generujHaslo() {

    $losoweMale = substr(str_shuffle("abcdefghijkmnopqrstuvwxyz"), 0, 4);
    $losoweDuze = substr(str_shuffle("ABCDEFGHIJKLMNPQRSTUVWXYZ"), 0, 4);
    $losoweCyfry = substr(str_shuffle("23456789"), 0, 1);
    $losoweZnaki = substr(str_shuffle("@#$%"), 0, 1);

    $tekst = str_shuffle($losoweMale.$losoweDuze.$losoweCyfry.$losoweZnaki);

    return $tekst;
  }

  // funkcja losowo mieszajaca tablice
  public static function wymieszajTablice($list) { 
    if (!is_array($list)) return $list; 

    $klucze = array_keys($list); 
    shuffle($klucze); 
    $random = array(); 
    foreach ($klucze as $klucz) { 
      $random[] = $list[$klucz]; 
    }
    return $random; 
  } 
  
  // ile razy wystapil element w tablicy
  public static function arrayIloscWystapien($zwrot, $tablica){
    $zwrot_array = array();
    //
    for ($i = 0, $x = count($tablica); $i < $x; $i++) {
        //
        if ($tablica[$i] == $zwrot) {
            $zwrot_array[] = $tablica[$i];
        }
        //
    }
    $iloscWystapien = count($zwrot_array);
    //
    return $iloscWystapien;
  }
 
  public static function drukujTablice($tablica, $exit = false) {
    if ( count($tablica) > 0 ) {
      echo "<pre>";
      print_r ($tablica);
      echo "</pre>";
    } else {
      echo 'tablica jest pusta';
    }
    if ($exit) exit();
  }

  // zapisanie i wyswietlanie czasu przetwarzania strony - tylko dla celow programistycznych
  public static function pokazSledzenie() {
    echo '<div style="text-align: left;" class="tekst10"><hr />';
    echo '<b>Tablica SESSION:</b> ';
    Funkcje::drukujTablice($_SESSION);
    echo '<hr />';
    echo '<b>Tablica COOKIE:</b> ';
    Funkcje::drukujTablice($_COOKIE);
    echo '<hr />';
    echo '<b>Tablica POST:</b> ';
    Funkcje::drukujTablice($_POST);
    echo '<hr />';
    echo '<b>Tablica GET:</b> ';
    Funkcje::drukujTablice($_GET);
    echo '<hr />';
  }

  // wygenerowanie header location dla polaczenia SSL
  public static function PrzekierowanieSSL( $adres ) {

    //zawsze kieruje po https jesli jest logowanie
    if ( $adres == 'logowanie.html' && WLACZENIE_SSL == 'tak' ) { 
        session_write_close();
        header("Location: ".ADRES_URL_SKLEPU_SSL."/".$adres);
        exit();
    }

    //jesli jest podsumowanie zamowienia
    if ( $adres == '/zamowienie-podsumowanie.html' && WLACZENIE_SSL == 'tak' ) { 
        session_write_close();
        header("Location: ".ADRES_URL_SKLEPU_SSL.$adres);
        exit();
    }

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') {
        session_write_close();
        header("Location: ".ADRES_URL_SKLEPU_SSL."/".$adres);
        exit();
    } else {
        session_write_close();
        header("Location: ".$adres);
        exit();
    }
  
    return;
  }
  
  // wygenerowanie header location
  public static function PrzekierowanieURL( $adres ) {
    //
    session_write_close();
    if ( $adres == '' || $adres == '/' ) {
         header("Location: ".ADRES_URL_SKLEPU);
       } else {
         header("Location: ".ADRES_URL_SKLEPU."/".$adres);
    }
    exit();    
    //
  }  
  
  // zastepuje funkcje in_array - umozliwia szukanie wartosci tablicy w innej tablicy
  public static function SzukajwTablicy($Szukana, $Przeszukiwana) {
    //
    if ( empty($Szukana) ) {
        return false;
    }
    //
    $Znalezionych = 0;
    //
    foreach ($Szukana as $WartoscSzukana) {
        //
        if (in_array($WartoscSzukana, $Przeszukiwana)) {
            $Znalezionych++;
        }
        //
    }
    //
    if (count($Szukana) == $Znalezionych) {
        return true;
      } else {
        return false;
    }
  }
  
  // czysci tablice wielowymiarowa z duplikatow
  public static function CzyscTabliceUnikalne($tablica) {
    //
    $wynik = array_map("unserialize", array_unique(array_map("serialize", $tablica)));
    //
    foreach ($wynik as $klucz => $wartosc) {
        if ( is_array($wartosc) ) {
            $wynik[$klucz] = Funkcje::CzyscTabliceUnikalne($wartosc);
        }
    }
    //
    return $wynik;
  }
  
  // funkcja zwraca aktualny link przegladarki
  public static function RequestURI() {
    if(!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
        if($_SERVER['QUERY_STRING']) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }
    }
    return $_SERVER['REQUEST_URI'];
  }  

  // zwraca get z linku
  public static function Zwroc_Get($tablica = '', $dodawanie = false, $separator = '') {
    //
    if ($separator == '') {
        $separator = '&';
        $znak = '?';
        if ($dodawanie == true) {
          $znak = '&';
        }
      } else {
        $znak = $separator;
    }
    
    if ($tablica == '') $tablica = array();
    //
    $wynik = '';
    reset($_GET);
    while (list($klucz, $wartosc) = each($_GET)) {
      //
      if ( $klucz == 'szukaj' ) {
           $wartosc = str_replace('/', '[back]', $wartosc);
      }
      //
      if (!in_array($klucz, $tablica)) {  $wynik .= $klucz . '=' . $wartosc . $separator; }
    }
    if (!empty($wynik)) {
      $wynik = $znak.$wynik;
      $wynik = substr($wynik,0,strlen($wynik)-1);
    }
    return $wynik;
  }    
  
  // funkcja wyswietlajaca status zamowienia klienta
  public static function pokazNazweStatusuZamowienia( $status_id, $jezyk = '1') {

    $wynik = '';
    $zapytanie = "SELECT s.orders_status_id, s.orders_status_color, sd.orders_status_name FROM orders_status s LEFT JOIN orders_status_description sd ON sd.orders_status_id = s.orders_status_id WHERE s.orders_status_id = '".$status_id."' AND sd.language_id = '".$jezyk."'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($nazwa_statusu = $sql->fetch_assoc()) {
      $wynik = '<span>'.$nazwa_statusu['orders_status_name'].'</span>';
    }
    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $wynik;
  } 

  // funkcja wyswietlajaca imie i nazwisko opiekuna zamowienia
  public static function PokazOpiekuna($id) {

    $zapytanie = "SELECT * FROM admin WHERE admin_id = '".$id."'";
    $sql = $GLOBALS['db']->open_query($zapytanie);
    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      while ($info = $sql->fetch_assoc()) { 
        $wynik = $info['admin_firstname'] . ' ' . $info['admin_lastname'];
      }
    } else {
      $wynik = $GLOBALS['tlumacz']['OPIEKUN_BRAK'];
    }
    $GLOBALS['db']->close_query($sql); 
    unset($zapytanie);    

    return $wynik;
  }  

  // funkcja generujaca numer faktury VAT
  public static function WygenerujNumerFaktury( $typ ) {

    $numer_faktury = '1';

    $zapytanie = "SELECT MAX(invoices_nr) AS numerek
                  FROM invoices
                  WHERE invoices_type = '".$typ."' AND YEAR(invoices_date_generated) = '".ROK_KSIEGOWY_FAKTUROWANIA."'";
                  
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      while ($info = $sql->fetch_assoc()) {
        $numer_faktury = $info['numerek'] + 1;
      }
    }

    $GLOBALS['db']->close_query($sql);  
    unset($zapytanie);
    
    return $numer_faktury;
  } 

  // funkcja zwraca domyslna jednostke miary produktow ustawiona w sklepie
  public static function domyslnaJednostkaMiary() {
    
    $wynik = '';
    
    if ( isset($GLOBALS['jednostkiMiary'][0]) ) {
         $wynik = $GLOBALS['jednostkiMiary'][0]['id'];
    }
    
    return $wynik;
  }

  // funkcja zwraca domyslny podatek VAT ustawiony w sklepie
  public static function domyslnyPodatekVat() {

    $wynik = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('StawkaVatDomyslna', CACHE_INNE);      

    if ( !$WynikCache && !is_array($WynikCache) ) { 
    
        $sql = $GLOBALS['db']->open_query("SELECT tax_rates_id, tax_rate FROM tax_rates WHERE tax_default = '1'");  
        $tax = $sql->fetch_assoc();
        
        $wynik = array('id' => $tax['tax_rates_id'],
                       'stawka' =>  $tax['tax_rate']);
        
        $GLOBALS['db']->close_query($sql);
        
        $GLOBALS['cache']->zapisz('StawkaVatDomyslna', $wynik, CACHE_INNE);
        
      } else {
     
       $wynik = $WynikCache;
    
    }    

    return $wynik;
  }
  
  // funkcja zwraca stawke podatku VAT na podstawie ID
  public static function StawkaPodatekVat($id = 1) {
  
    $stawkiVat = array();
    
    // cache zapytania
    $WynikCache = $GLOBALS['cache']->odczytaj('StawkiVat', CACHE_INNE);   
    
    if ( !$WynikCache && !is_array($WynikCache) ) {    

        $sql = $GLOBALS['db']->open_query("SELECT tax_rates_id, tax_rate FROM tax_rates");  
        while ($tax = $sql->fetch_assoc()) {
          $stawkiVat[$tax['tax_rates_id']] = $tax['tax_rate'];
        }
        $GLOBALS['db']->close_query($sql);
        
        $GLOBALS['cache']->zapisz('StawkiVat', $stawkiVat, CACHE_INNE);
        
      } else {
     
       $stawkiVat = $WynikCache;
    
    }        

    return $stawkiVat[$id];
  }  
  
  // sprawdza czy podana wartosc jest adresem email
  public static function CzyPoprawnyMail($email){
    return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $email);
  }  

  //funkcja wyswietla animacje flash
  public static function pokazFlash($nazwa, $animacja, $szerokosc = '' , $wysokosc = '' , $tlo = '' , $parametry = '') {

    if( $szerokosc != '' ) {
      $szerokosc_animacji = 'width="'.$szerokosc.'"' ;
    }

    if( $wysokosc != '' ) {
      $wysokosc_animacji = 'height="'.$wysokosc.'"' ;
    }

    if( $parametry != '' ) {
      $film = $animacja . '?' . $parametry ;
    } else {
      $film = $animacja ;
    }

    $flash  = '<object type="application/x-shockwave-flash" data="'.$film.'" '.$szerokosc_animacji . $wysokosc_animacji.'>'."\n";
    $flash .= '<param name="movie" value="'.$film.'" />' . "\n";
    if( $tlo != '' ) {
      $flash .= '<param name="bgcolor" value="#'.$tlo.'" />' . "\n" ;
    } else {
      $flash .= '<param name="wmode" value="transparent" />' . "\n" ;
    }
    $flash .= '</object>' . "\n" ;

    return $flash;

  }
  
  // funkcja zwraca id produktu z ciagu z cechami - w postaci 1x1-1x2-2
  public static function SamoIdProduktuBezCech( $id ) {

    // dzieli na tablice
    $TabCechy = explode('x', $id);
    //
    return (int)$TabCechy[0];

  }   
  
  // funkcja zwraca w postaci tablicy cechy produktu - w postaci 1x1-1x2-2
  public static function CechyProduktuPoId( $id, $tylko_ilosc = false ) {
    //
    // dzieli na tablice
    $TabCechy = explode('x', $id);
    $TablicaWynikow = array();

    if ( $tylko_ilosc == false ) {
    
        // zaczynam od 1 - pomijam pierwsza wartosc bo to id produktu
        for ($r = 1, $c = count($TabCechy); $r < $c; $r++) {
            //
            $CechyWart = explode('-', $TabCechy[$r]);
            //
            $TablicaWynikow[] = array('nazwa_cechy' => $GLOBALS['NazwyCech'][ $CechyWart[0] ]['nazwa'],
                                      'wartosc_cechy' => $GLOBALS['WartosciCech'][ $CechyWart[1] ]['nazwa']);
            //
        }   
        
      } else {
      
        // jezeli ma tylko policzyc ilosc cech
      
        // zaczynam od 1 - pomijam pierwsza wartosc bo to id produktu
        for ($r = 1, $c = count($TabCechy); $r < $c; $r++) {
            //
            $CechyWart = explode('-', $TabCechy[$r]);
            //
            $TablicaWynikow[] = array('cecha' => $CechyWart[0], 'wartosc' => $CechyWart[1]);
            //
        }  
      
    }

    return $TablicaWynikow;
    
  }  
  
  // funkcja tworzy tablice globalne z nazwami cech i wartosciami
  public static function TabliceCech() {
    //
    if (!isset($GLOBALS['NazwyCech'])) {
        //
        // nazwy cech
        $NazwyCech = array();
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('NazwyCech_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
        
        if ( !$WynikCache && !is_array($WynikCache) ) {
            //
            $zapytanie = "SELECT products_options_id, products_options_type, products_options_value, products_options_name, products_options_description, products_options_images_enabled FROM products_options WHERE language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
              $NazwyCech[$info['products_options_id']] = array('id'     => $info['products_options_id'],
                                                               'typ'    => $info['products_options_type'],
                                                               'rodzaj' => $info['products_options_value'],
                                                               'nazwa'  => $info['products_options_name'],
                                                               'opis'   => $info['products_options_description']);
              
              //
              if ( $info['products_options_images_enabled'] == 'true' ) {
                 //
                 $NazwyCech[$info['products_options_id']]['typ'] = 'foto';
                 //
              }
              //
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql);
            //
            $GLOBALS['cache']->zapisz('NazwyCech_' . $_SESSION['domyslnyJezyk']['kod'], $NazwyCech, CACHE_INNE);
            //
          } else { 
            //
            $NazwyCech = $WynikCache;
            //
        }
          
        $GLOBALS['NazwyCech'] = $NazwyCech;
        
        //
    }
    
    if (!isset($GLOBALS['WartosciCech'])) {
        //
        // wartosci cech
        $WartosciCech = array();
        
        $WynikCache = $GLOBALS['cache']->odczytaj('WartosciCech_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
        
        if ( !$WynikCache && !is_array($WynikCache) ) {      
            //
            $zapytanie = "SELECT products_options_values_id, products_options_values_name, products_options_values_thumbnail, products_options_values_status FROM products_options_values WHERE language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'";
            $sql = $GLOBALS['db']->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
              $WartosciCech[$info['products_options_values_id']] = array('id'    => $info['products_options_values_id'],
                                                                         'nazwa' => $info['products_options_values_name'],
                                                                         'foto'  => $info['products_options_values_thumbnail'],
                                                                         'status' => (($info['products_options_values_status'] == 1) ? 'tak' : 'nie'));      
            }
            //
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info, $sql); 
            //
            $GLOBALS['cache']->zapisz('WartosciCech_' . $_SESSION['domyslnyJezyk']['kod'], $WartosciCech, CACHE_INNE);
            //
          } else { 
            //
            $WartosciCech = $WynikCache;
            //
        }
            
        $GLOBALS['WartosciCech'] = $WartosciCech;
           
        //
    }
    //
  }
  
  // funkcja zwraca ilosc ogolna pol opisowych 
  public static function OgolnaIloscDodatkowychPol() {
    //
    $WynikCache = $GLOBALS['cache']->odczytaj('DodatkowePola_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $zapytanie = "SELECT DISTINCT products_extra_fields_id 
                                 FROM products_extra_fields 
                                WHERE products_extra_fields_status = '1' AND products_extra_fields_view = '1' AND (languages_id = '0' OR languages_id = '" . $_SESSION['domyslnyJezyk']['id'] . "')";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        // obejscie zeby 0 nie bylo tozsame z false
        $zapisz = (int)$GLOBALS['db']->ile_rekordow($sql);
        if ( $zapisz == 0 ) {
             $zapisz = 'xx';
        }
        //
        $GLOBALS['cache']->zapisz('DodatkowePola_' . $_SESSION['domyslnyJezyk']['kod'], $zapisz, CACHE_INNE);
        $IloscPol = (int)$GLOBALS['db']->ile_rekordow($sql);
        //
        $GLOBALS['db']->close_query($sql);
        //
        unset($zapisz, $zapytanie);
        //
    } else {
        $IloscPol = (int)$WynikCache;
    }  

    return $IloscPol;
    
  }  
  
  // funkcja zwraca ilosc ogolna pol tekstowych
  public static function OgolnaIloscDodatkowychPolTekstowych() {
    //
    $WynikCache = $GLOBALS['cache']->odczytaj('DodatkoweTekstowe_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_INNE);
    
    if ( !$WynikCache ) {
        //
        $zapytanie = "SELECT DISTINCT products_text_fields_id 
                                 FROM products_text_fields
                                WHERE products_text_fields_status = '1'";

        $sql = $GLOBALS['db']->open_query($zapytanie);
        //
        // obejscie zeby 0 nie bylo tozsame z false
        $zapisz = (int)$GLOBALS['db']->ile_rekordow($sql);
        if ( $zapisz == 0 ) {
             $zapisz = 'xx';
        }
        //
        $GLOBALS['cache']->zapisz('DodatkoweTekstowe_' . $_SESSION['domyslnyJezyk']['kod'], $zapisz, CACHE_INNE);
        $IloscPol = (int)$GLOBALS['db']->ile_rekordow($sql);
        //
        $GLOBALS['db']->close_query($sql);
        //
        unset($zapisz, $zapytanie);
        //
    } else {
        $IloscPol = (int)$WynikCache;
    }  

    return $IloscPol;
    
  }  
  
  // funkcja generujaca pola RADIO
  public static function ListaRadio($nazwa, $wartosc, $default = '', $parametry = '') {

    $wynik = '';

    $i = 1;
    foreach ( $wartosc as $rekord ) {

      if ( $rekord['id'] != '0' ) {
        $ciag_tekstu = $rekord['text'];
        
        $wynik .= '<input type="radio" id="'.$nazwa.'_' . $rekord['id'] . '" name="' . $nazwa . '" value="' . $rekord['id'] . '"';
        if ($default == '' && $i == '1') {
            $wynik .= ' checked="checked"';
        } else {      
            if ($default == $rekord['id']) {
              $wynik .= ' checked="checked"';
            }
        }

        if (Funkcje::czyNiePuste($parametry)) $wynik .= ' ' . $parametry . ' ';

        $wynik .= ' />'.$ciag_tekstu.'<br />';
      } else {
        $wynik .= '---';
      }
      $i++;
    }

    return $wynik;
  }  
  
  // funkcja obliczajaca wynik z dowolnego wzoru matematycznego
  public static function obliczWzor( $mathString ) {
    $mathString = trim($mathString);
    $mathString = preg_replace('/[^0-9\+\-\*\.\/\(\) ]/i', '', $mathString); 
    if ( is_numeric($mathString) ) {
        return $mathString;
    }
    $compute = create_function('', 'return (' . $mathString . ');' );
    return 0 + round($compute(), 2);
  }
  
  public static function Sg( $tekst ) {
    return base64_decode($tekst);
  }

  // funkcja sprawdzajaca czy podana wartosc jest w zadanym zakresie liczb
  public static function czyWartoscJestwZakresie($wartosc, $maximum, $minimum) {

    if ( is_numeric($minimum) && $minimum != '0' ) {
      if ( $wartosc <= $minimum ) return false;
    }
    if ( is_numeric($maximum) && $maximum != '0' ) {
      if ( $wartosc >= $maximum ) return false;
    }
    return true;
  }

  // funkcja generujaca pola RADIO w koszyku
  public static function ListaRadioKoszyk($nazwa, $wartosc, $default = '', $parametry = '') {

    $wynik = '';

    $i = 1;
    foreach ( $wartosc as $rekord ) {

      if ( $rekord['id'] != '0' ) {
      
        $wynik .= '<div class="ListaTbl">';

        $ciag_tekstu = $rekord['text'];
        
        $wynik .= '<div class="ListaRadio"><input type="radio" id="'.$nazwa.'_' . $rekord['id'] . '" name="' . $nazwa . '" value="' . $rekord['id'] . '"';
        if ($default == '' && $i == '1') {
            $wynik .= ' checked="checked"';
        } else {      
            if ($default == $rekord['id']) {
              $wynik .= ' checked="checked"';
            }
        }

        if (Funkcje::czyNiePuste($parametry)) $wynik .= ' ' . $parametry . ' ';

        $wynik .= ' /></div>';
        
        $wynik .= '<div class="ListaOpis"><label for="'.$nazwa.'_' . $rekord['id'].'" title="">'.$ciag_tekstu.'</label>';
        if ( $rekord['objasnienie'] != '' ) {
          $wynik .= '<div class="InfoTip"><img src="szablony/'.DOMYSLNY_SZABLON.'/obrazki/nawigacja/info_tip.png" alt="" /><span class="tip">'.$rekord['objasnienie'].'</span></div>';
        }
        $wynik .= '</div>';
        
        $wynik .= '<div class="ListaCena">';
        if ( $rekord['wartosc'] > 0 ) {
          $wynik .= $GLOBALS['waluty']->WyswietlFormatCeny($GLOBALS['waluty']->PokazCeneBezSymbolu($rekord['wartosc'],'',true), $_SESSION['domyslnaWaluta']['id'], true, false);
        }   
        $wynik .= '</div>';

        $wynik .= '</div>';

      } else {
      
        $wynik .= '---';
        
      }
      $i++;
    }

    return $wynik;
  }  
  
  // funkcja sprawdza czy jest wlaczona platnosc po podaniu nazwy klasy - uzywane do niestandardowych platnosci
  public static function  CzyJestWlaczonaPlatnosc($id, $array) {

    if ( isset($array) && !isset($array['0']) ) {
       foreach ($array as $key => $val) {
           if ($val['klasa'] === $id) {
               return $key;
           }
       }
    } else {
      return null;
    }

   return null;
  }
  
  // funkcja do pobierania pliku na karcie produktu
  public static function pobierzPlik($file) {
 
    if (!is_file($file)) { die("<b>404 Nie ma takiego pliku !</b>"); }
     
    $len = filesize($file);
    $filename = basename($file);
    $file_extension = strtolower(substr(strrchr($filename,"."),1));
      
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
     
    header("Content-Type: application/force-download");
     
    $header = "Content-Disposition: attachment; filename=" . $filename . ";";
    header( $header );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . $len);
    @readfile($file);
    exit;
 
  } 

  // zamienia kropke w liczbie na domyslny separator waluty - uzywane przy pdf
  public static function KropkaPrzecinek( $wartosc ) {
    global $waluty, $zamowienie;
    
    return str_replace( '.', $waluty->waluty[$zamowienie->info['waluta']]['separator'], $wartosc );
    
  }  

  // sprawdzi czy sa jakies hity, polecane z datami - produkty ktore trzeba aktualizowac raz na dzien
  public static function AktualizacjaProduktowJednodniowych( $sql ) {
    //
    $WynikAktualizacji = 0;

    $IloscHitow = 0;
    $IloscPolecanych = 0;
    $IloscOczekiwanych = 0;
    
    while ($info = $sql->fetch_assoc()) {
        if ( Funkcje::czyNiePuste($info['star_date']) || Funkcje::czyNiePuste($info['star_date_end']) ) {
            $IloscHitow++;
        }  
        if ( Funkcje::czyNiePuste($info['featured_date']) || Funkcje::czyNiePuste($info['featured_date']) ) {
            $IloscPolecanych++;
        }
        if ( Funkcje::czyNiePuste($info['products_date_available']) ) {
            $IloscOczekiwanych++;
        }                                 
    }
    
    $dataRok = date('Y-m-d');

    // Wylacza lub wlacza nasze hity
    if ( $IloscHitow > 0 ) {
        // wlacza
        $GLOBALS['db']->open_query("UPDATE products SET star_status = '1' WHERE star_status = '0' AND ((star_date < '" . $dataRok . "' AND star_date != '0000-00-00') OR (star_date_end > '" . $dataRok . "' AND star_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();
        // wylacza
        $GLOBALS['db']->open_query("UPDATE products SET star_status = '0' WHERE star_status = '1' AND ((star_date > '" . $dataRok . "' AND star_date != '0000-00-00') OR (star_date_end < '" . $dataRok . "' AND star_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();        
    }
    
    // Wylacza lub wlacza polecane
    if ( $IloscPolecanych > 0 ) {
        // wlacza
        $GLOBALS['db']->open_query("UPDATE products SET featured_status = '1' WHERE featured_status = '0' AND ((featured_date < '" . $dataRok . "' AND featured_date != '0000-00-00') OR (featured_date_end > '" . $dataRok . "' AND featured_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();
        // wylacza
        $GLOBALS['db']->open_query("UPDATE products SET featured_status = '0' WHERE featured_status = '1' AND ((featured_date > '" . $dataRok . "' AND featured_date != '0000-00-00') OR (featured_date_end < '" . $dataRok . "' AND featured_date_end != '0000-00-00'))");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();        
    }
    
    // Wylaczenie produktow z data dostepnosci od 
    if ( $IloscOczekiwanych > 0 ) {
        $GLOBALS['db']->open_query("UPDATE products SET products_date_available = '0000-00-00' WHERE products_status = '1' AND products_date_available < '" . $dataRok . "'");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();
    }
    
    // Ustawienia nowosci jezeli sa automatyczne wg dat
    if ( NOWOSCI_USTAWIENIA == 'automatycznie wg daty dodania' ) {
        //
        $GLOBALS['db']->open_query("UPDATE products SET new_status = '0' WHERE new_status = '1'");
        $GLOBALS['db']->open_query("UPDATE products SET new_status = '1' WHERE new_status = '0' AND DATE_SUB(CURDATE(),INTERVAL " . NOWOSCI_ILOSC_DNI . " DAY) <= products_date_added");
        $WynikAktualizacji += $GLOBALS['db']->last_query_effect();        
        //
    }
    
    // jezeli byly zaktualizowane jakies produkty to skasuje cache produktow
    if ( $WynikAktualizacji > 0 ) {
        $GLOBALS['cache']->UsunCacheProduktow();
    }
    
    unset( $dataRok, $info, $WynikAktualizacji, $IloscHitow, $IloscPolecanych, $IloscOczekiwanych);    
    //
  }
  
  public static function WlasnyCron( $iloscMiniut, $aktualnyCron ) {
    //
    $przelicznikSekund = $iloscMiniut * 60;
    $noweSekundy = 0;
    $aktualneSekundy = time();
    if ( ((int)($aktualnyCron / $przelicznikSekund) * $przelicznikSekund) < $aktualneSekundy ) {
          //
          // jezeli czas jest wiecej niz co godzine przelicznik musi byc na godziny
          if ( $przelicznikSekund > 3600 ) {
               //
               $noweSekundy = ((int)($aktualneSekundy / 3600) * 3600) + $przelicznikSekund;
               //
             } else {
               //
               $noweSekundy = ((int)($aktualneSekundy / $przelicznikSekund) * $przelicznikSekund) + $przelicznikSekund;
               //
          }
          //
    }   
    //
    return $noweSekundy;
    //
  }
  
  // funkcja zarzadzania cronami  
  public static function ZarzadzanieCronami() {

    $definicje = array();
    $definicje[1] = array( 'status' => CRON_1_STATUS, 'sekundy' => CRON_1_SEKUNDY, 'godziny' => CRON_1_ILOSC_GODZIN, 'skrypt' => CRON_1_SKRYPT );
    $definicje[2] = array( 'status' => CRON_2_STATUS, 'sekundy' => CRON_2_SEKUNDY, 'godziny' => CRON_2_ILOSC_GODZIN, 'skrypt' => CRON_2_SKRYPT );
    $definicje[3] = array( 'status' => CRON_3_STATUS, 'sekundy' => CRON_3_SEKUNDY, 'godziny' => CRON_3_ILOSC_GODZIN, 'skrypt' => CRON_3_SKRYPT );
    $definicje[4] = array( 'status' => CRON_4_STATUS, 'sekundy' => CRON_4_SEKUNDY, 'godziny' => CRON_4_ILOSC_GODZIN, 'skrypt' => CRON_4_SKRYPT );

    for ( $b = 1; $b < 5; $b++ ) {
    
        if ( $definicje[$b]['status'] == 'tak' ) {
            
            $Czas = Funkcje::WlasnyCron( $definicje[$b]['godziny'] * 60, $definicje[$b]['sekundy'] );
            if ( $Czas > 0 ) {
            
                $c = curl_init( ADRES_URL_SKLEPU . '/harmonogram/' . $definicje[$b]['skrypt'] );
                curl_setopt( $c, CURLOPT_FOLLOWLOCATION, false );
                curl_setopt( $c, CURLOPT_HEADER, false );
                curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
                curl_exec( $c );
                curl_close( $c );    

                $GLOBALS['db']->open_query("UPDATE settings SET value = '" . $Czas . "' WHERE code = 'CRON_" . $b . "_SEKUNDY'");  

                unset($c);
                
            }
            unset($Czas);

        }
    
    }
    
    unset($definicje);
  
  }

  // funkcja zwracajaca domyslny status zamowienia
  public static function PokazDomyslnyStatusZamowienia() {

    $wynik = '-';
    $zapytanie = "SELECT orders_status_id FROM orders_status WHERE orders_status_default = '1'";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    while($domyslny_status = $sql->fetch_assoc()) {
      $wynik = $domyslny_status['orders_status_id'];
    }
    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

    return $wynik;
  }  

  // funkcja zapisujaca XML do tablicy
  public static function Xml2Array($xml,$main_heading = '') {
  
    $deXml = simplexml_load_string($xml);
    $deJson = json_encode($deXml);
    $xml_array = json_decode($deJson,true);
    if (! empty($main_heading)) {
        $returned = $xml_array[$main_heading];
        return $returned;
    } else {
        return $xml_array;
    }
    
  }

  // funkcja zwracajaca kod ISO kraju o podanym id
  public static function kodISOKrajuDostawy( $kraj_id ) {

    $zapytanie = "SELECT countries_iso_code_2 FROM countries WHERE countries_id = '" . (int)$kraj_id . "'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
         $info = $sql->fetch_assoc();
         return $info['countries_iso_code_2'];
    } else {
         return $_SESSION['krajDostawy']['kod'];
    }

  }

  // funkcja zwracajaca tablice aktywnych systemow ratalnych
  public static function AktywneSystemyRatalne() {

    $SystemyRatalne = array();

    // cache zapytania    
    $WynikCache = $GLOBALS['cache']->odczytaj('SystemyRatalne', CACHE_INNE);    
    
    if ( !$WynikCache && !is_array($WynikCache) ) {
        $zapSystemyRatalne = "SELECT p.id, p.klasa, pp.kod, pp.wartosc FROM modules_payment p
                              LEFT JOIN modules_payment_params pp ON p.id = pp.modul_id WHERE p.status = '1' AND (p.klasa = 'platnosc_santander' OR p.klasa = 'platnosc_lukas' OR p.klasa = 'platnosc_mbank' OR p.klasa = 'platnosc_lukas' OR p.klasa = 'platnosc_payu')";
        $sql = $GLOBALS['db']->open_query($zapSystemyRatalne);
        //
        while ($info = $sql->fetch_assoc()) {
            $SystemyRatalne[$info['klasa']][$info['kod']] = $info['wartosc'];      
        }
        if ( isset($SystemyRatalne['platnosc_payu']) && $SystemyRatalne['platnosc_payu']['PLATNOSC_PAYU_RATY_WLACZONE'] == 'nie' ) {
            unset($SystemyRatalne['platnosc_payu']);
        }
        //
        $GLOBALS['db']->close_query($sql);
        //
        $GLOBALS['cache']->zapisz('SystemyRatalne', $SystemyRatalne, CACHE_INNE);  
        //        
        unset($zapSystemyRatalne, $info, $sql);    
    } else {
        $SystemyRatalne = $WynikCache;
    }
    
    unset($WynikCache, $zapSystemyRatalne);    

    return $SystemyRatalne; 

  }


  // funkcja zwracajaca kod wojewodztwa
  public static function kodWojewodztwa($panstwo_id, $wojewodztwo_id, $kod_domyslny) {

    $zapytanie = "SELECT zone_code FROM zones WHERE zone_id = '" . (int)$wojewodztwo_id . "'";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
         $info = $sql->fetch_assoc();
         return $info['zone_code'];
    } else {
         $kod_domyslny;
    }
  }

  // sprawdza czy katalog jest pusty
  public static function czyFolderJestPusty( $folderName ){
    $files = array ();
    if ( $handle = opendir ( $folderName ) ) {
        while ( false !== ( $file = readdir ( $handle ) ) ) {
            if ( $file != "." && $file != ".." && $file != "js" && $file != "index.php" ) {
                $files [] = $file;
            }
        }
        closedir ( $handle );
    }
    unset($folderName);
    return ( count ( $files ) > 0 ) ? true: false;
  }

  // funkcja generujaca i wyswietlajaca zdjecia duze ze znakiem wodnym
  public static function pokazObrazekWatermark( $plik_zdjecia) {
    global $thumb;
    
    $katalog_zdjec      = KATALOG_ZDJEC;
    $katalog_miniaturek = 'watermark';

    $wielkosc_obrazka = OBRAZ_COPYRIGHT_DUZY;
    $pokaz_copyright  = TEKST_COPYRIGHT_POKAZ;
    $pokaz_watermark  = OBRAZ_COPYRIGHT_POKAZ;

    if ( $pokaz_copyright == 'tak' && $pokaz_watermark == 'nie' ) {
      $prefix_miniaturek  = 'cpx_';
    } elseif ( $pokaz_copyright == 'tak' && $pokaz_watermark == 'tak' ) {
      $prefix_miniaturek  = 'cwpx_';
    } elseif ( $pokaz_copyright == 'nie' && $pokaz_watermark == 'tak' ) {
      $prefix_miniaturek  = 'wpx_';
    }

    // Sprawdza czy przekazana zmienna z plikiem nie jest pusta
    if ( ($plik_zdjecia == '') || ($plik_zdjecia == 'NULL') || (strlen(trim($plik_zdjecia)) == 0) || pathinfo($plik_zdjecia, PATHINFO_EXTENSION) == 'swf' ) {
      return '';
    }

    // Sprawdza czy przekazana zmienna z plikiem zawiera adres URL
    $czy_jest_url = strpos($plik_zdjecia, 'http');
    if ($czy_jest_url !== false) {
      $adres_zdjecia =  preg_replace("/((http|https|ftp):\/\/)?([^\/]+)(.*)/si", "$4", $plik_zdjecia);
      $plik_zdjecia = str_replace($katalog_zdjec,'',$adres_zdjecia);
    }

    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;

    // Sprawdza czy istnieje na serwerze plik przekazany do funkcji
    if ( is_file($sciezka_bezwgledna_do_pliku) ) {
      $plik_zdjecia = $plik_zdjecia;
    } else {
        return '/' . $katalog_zdjec . '/domyslny.gif';
    }
    $sciezka_bezwgledna_do_pliku = KATALOG_SKLEPU . $katalog_zdjec . '/' . $plik_zdjecia;
    $sciezka_wgledna_do_pliku = dirname($katalog_zdjec . '/' . $plik_zdjecia);

    // Pobranie danych o skladowych elementach sciezki do pliku
    $info = pathinfo($sciezka_bezwgledna_do_pliku);

    list($szerokosc, $wysokosc, $typ, $atrybuty) = getimagesize($sciezka_bezwgledna_do_pliku);

    $nazwa_pliku_miniaturki = md5($info["basename"]).'.'.$info["extension"];
    
    if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $prefix_miniaturek . $nazwa_pliku_miniaturki) ) {

      $miniaturka =  $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $prefix_miniaturek . $nazwa_pliku_miniaturki;
      
      return $miniaturka;
           
    } else {

      // Tablica przedrostkow plikow zaleznych od ustawionych zabezpieczen
      $tablica_przedrostkow = array();
      $tablica_przedrostkow[]  = 'cpx_';
      $tablica_przedrostkow[]  = 'cwpx_';
      $tablica_przedrostkow[]  = 'wpx_';
      $tablica_przedrostkow[]  = 'px_';

      // Sprawdza czy istnieje katalog na miniaturki - jesli nie to go tworzy
      if (is_dir($info['dirname'] . '/' . $katalog_miniaturek) == false) {
        $old_mask = umask(0);
        mkdir($info['dirname'] . '/' . $katalog_miniaturek, 0777, true);
        umask($old_mask);
      }
      // Usuwa miniaturki, ktore nie spelniaja aktualnych warunkow zabezpieczenia
      for ( $i = 0, $c = count($tablica_przedrostkow); $i < $c; $i++ ) {
        if ( $tablica_przedrostkow[$i] != $prefix_miniaturek ) {
          if ( is_file($info['dirname'] . '/' . $katalog_miniaturek . '/' . $tablica_przedrostkow[$i].$nazwa_pliku_miniaturki) ) {
            @unlink($info['dirname'] . '/' . $katalog_miniaturek . '/' . $tablica_przedrostkow[$i].$nazwa_pliku_miniaturki);
          }
        }
      }

      // Generowanie miniaturki
      $file = $sciezka_bezwgledna_do_pliku;
      $thumb -> Thumbwidth        = $szerokosc;
      $thumb -> Thumbheight       = $wysokosc;
      $thumb -> Thumblocation     = $info['dirname'] . '/' . $katalog_miniaturek . '/';
      $thumb -> Thumbprefix       = $prefix_miniaturek;
      $thumb -> Thumbfilename     = $nazwa_pliku_miniaturki;
      $thumb -> Copyright         = ( $pokaz_copyright == 'tak' ? true : false );
      $thumb -> Watermark         = ( $pokaz_watermark == 'tak' ? true : false );
      $thumb -> Watermarkfilename = KATALOG_ZDJEC . '/'.$wielkosc_obrazka;

      $thumb -> Createthumb($file,'file');

      $miniaturka =  '/' . $sciezka_wgledna_do_pliku . '/' . $katalog_miniaturek . '/' . $prefix_miniaturek . $nazwa_pliku_miniaturki;
      unset($thumb);

      return $miniaturka;
           
    }

  }  

  // dzieli ciag uzywany w polach tekstowych produktow
  public static function serialCiag( $ciag ) {
    //
    $ciag = str_replace('{#{', '', stripslashes($ciag));
    $PodzialGlowny = explode('}#}', $ciag);
    $TablicaWynikowa = array();
    //
    foreach ( $PodzialGlowny as $Pole ) {
        //
        $PodPodzial = explode('|*|', $Pole);
        if ( count($PodPodzial) == 3 ) {
            $TablicaWynikowa[] = array( 'nazwa' => $PodPodzial[0],
                                        'tekst' => $PodPodzial[1],
                                        'typ'   => $PodPodzial[2] );
        }
        unset($PodPodzial);
        //
    }
    //
    unset($ciag, $PodzialGlowny);
    //
    return $TablicaWynikowa;
  }

  //usuwa slashe po deserializacji tablicy jak jest wlaczone na serwerze magic_quotes
  public static function stripslashes_array($array) {
        return is_array($array) ? array_map('Funkcje::stripslashes_array', $array) : stripslashes(stripslashes($array));
  }

  // funkcja rozbijająca adres na elementy *************************************************************
  public static function PrzeksztalcAdres( $adres ){

    $adres_klienta = explode(' ', $adres );

    if (count ($adres_klienta) > 1 ) {
      $numer_domu = array_reverse($adres_klienta);
      unset($adres_klienta[count($adres_klienta) - 1]);//usuwam ostatni element
      $adres_klienta = implode(' ',$adres_klienta);
    } else {
      $adres_klienta = implode(' ', $adres_klienta);
      $numer_domu[0] = '';
    }

    return array('ulica'=> $adres_klienta,
                 'dom'=> $numer_domu[0]
                );
  }

  // funkcja rozbijająca adres na elementy *************************************************************
  public static function PrzeksztalcAdresDomu( $adres ){

    $adres_klienta = explode('/', $adres );

    if (count ($adres_klienta) > 1 ) {
      $numer_domu = array_reverse($adres_klienta);
      unset($adres_klienta[count($adres_klienta) - 1]);//usuwam ostatni element
      $adres_klienta = implode(' ',$adres_klienta);
    } else {
      $adres_klienta = implode(' ', $adres_klienta);
      $numer_domu[0] = '';
    }

    return array('dom'=> $adres_klienta,
                 'mieszkanie'=> $numer_domu[0]
                );
  }


}
?>