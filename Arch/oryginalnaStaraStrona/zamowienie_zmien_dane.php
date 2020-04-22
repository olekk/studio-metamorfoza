<?php

$GLOBALS['kolumny'] = 'srodkowa';

// plik
$WywolanyPlik = 'zamowienie_zmien_dane';

include('start.php');

if ( $GLOBALS['koszykKlienta']->KoszykIloscProduktow() == 0 || (!isset($_SESSION['customer_id']) || (int)$_SESSION['customer_id'] == 0) ) {

    Funkcje::PrzekierowanieURL('koszyk.html'); 

}

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KOSZYK','ZAMOWIENIE_REALIZACJA','REJESTRACJA','KLIENCI') ), $GLOBALS['tlumacz'] );

// meta tagi
$Meta = MetaTagi::ZwrocMetaTagi( basename(__FILE__) );
$tpl->dodaj('__META_TYTUL', $Meta['tytul']);
$tpl->dodaj('__META_SLOWA_KLUCZOWE', $Meta['slowa']);
$tpl->dodaj('__META_OPIS', $Meta['opis']);
unset($Meta);

// breadcrumb
$nawigacja->dodaj($GLOBALS['tlumacz']['NAGLOWEK_ZAMOWIENIE_POTWIERDZENIE']);
$tpl->dodaj('__BREADCRUMB', $nawigacja->sciezka(' ' . $GLOBALS['tlumacz']['NAWIGACJA_SEPARATOR'] . ' '));

// wyglad srodkowy
$srodek = new Szablony($Wyglad->TrescLokalna($WywolanyPlik));

// select z panstwami
$srodek->dodaj('__WYBOR_PANSTWA', Funkcje::RozwijaneMenu('panstwo',Klient::ListaPanstw(), $_SESSION['adresDostawy']['panstwo'], 'id="selection" style="width:80%"'));

// select z panstwami do platnosci
$srodek->dodaj('__WYBOR_PANSTWA_FIRMA', Funkcje::RozwijaneMenu('panstwoFaktura',Klient::ListaPanstw(), $_SESSION['adresFaktury']['panstwo'], 'id="selectionFirma" style="width:80%"'));

// select z wojwodztwami
$srodek->dodaj('__WYBOR_WOJEWODZTWA', '<span id="selectionresult">'.Funkcje::RozwijaneMenu('wojewodztwo', Klient::ListaWojewodztw($_SESSION['adresDostawy']['panstwo']), $_SESSION['adresDostawy']['wojewodztwo'], ' style="width:80%"').'</span>');

// select z wojwodztwami do platnosci
$srodek->dodaj('__WYBOR_WOJEWODZTWA_FIRMA', '<span id="selectionresultFirma">'.Funkcje::RozwijaneMenu('wojewodztwoFaktura', Klient::ListaWojewodztw($_SESSION['adresFaktury']['panstwo']), $_SESSION['adresFaktury']['wojewodztwo'], ' style="width:80%"').'</span>');

// wybor osoba fizyczna / firma lub tylko firma - ukrywanie pola firma
if ( KLIENT_TYLKO_FIRMA == 'dowolny' ) {
     //
     $srodek->dodaj('__POLE_WYMAGANE', '');
     $srodek->dodaj('__TYLKO_FIRMA', '0');
     //
     if ( trim($_SESSION['adresFaktury']['firma']) != '' ) {
         $srodek->dodaj('__CSS_FIRMA', '');
         $srodek->dodaj('__CSS_FIZYCZNA', 'style="display:none"');
         $srodek->dodaj('__ZAZNACZ_FIZYCZNA', '');
         $srodek->dodaj('__ZAZNACZ_FIRMA', 'checked="checked"');
       } else {
         $srodek->dodaj('__CSS_FIRMA', 'style="display:none"');
         $srodek->dodaj('__CSS_FIZYCZNA', '');
         $srodek->dodaj('__ZAZNACZ_FIZYCZNA', 'checked="checked"');
         $srodek->dodaj('__ZAZNACZ_FIRMA', '');         
     }
     //
   } else {
     //
     $srodek->dodaj('__POLE_WYMAGANE', '<em class="required"></em>');
     $srodek->dodaj('__CSS_FIRMA', '');
     $srodek->dodaj('__CSS_FIZYCZNA', 'style="display:none"');
     $srodek->dodaj('__TYLKO_FIRMA', '1');     
     //
}

$tpl->dodaj('__SRODKOWA_KOLUMNA', $srodek->uruchom());

unset($srodek, $WywolanyPlik);

include('koniec.php');

?>