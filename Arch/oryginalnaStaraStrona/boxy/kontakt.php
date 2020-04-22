<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_KONTAKT_DANE_FIRMY;Czy wyświetlać dane firmy, dane definiowane w menu Dane firmy;tak;tak,nie}}
// {{BOX_KONTAKT_DANE_FIRMY_NIP;Czy wyświetlać NIP firmy, dane definiowane w menu Dane firmy;tak;tak,nie}}
//

$PokazNazweSklepu = 'tak';
$PokazDaneFirmy = 'tak';
$PokazNip = 'tak';

if ( defined('BOX_KONTAKT_NAZWA_SKLEPU') ) {
   $PokazNazweSklepu = BOX_KONTAKT_NAZWA_SKLEPU;
}
if ( defined('BOX_KONTAKT_DANE_FIRMY') ) {
   $PokazDaneFirmy = BOX_KONTAKT_DANE_FIRMY;
}
if ( defined('BOX_KONTAKT_DANE_FIRMY_NIP') ) {
   $PokazNip = BOX_KONTAKT_DANE_FIRMY_NIP;
}

//
echo '<ul class="BoxKontakt">';
//
    // dane sklepu
    echo '<li class="Firma">';
    
    if ( $PokazDaneFirmy == 'tak' ) {
         //
         if ( DANE_FIRMY_BOX_KONTAKT != '' ) { echo nl2br(DANE_FIRMY_BOX_KONTAKT)  . '<br />'; }
         //
    }
    
    if ( DANE_NIP != '' && $PokazDaneFirmy == 'tak' && $PokazNip == 'tak' ) { echo 'NIP: ' . DANE_NIP . '<br />'; }
    
    echo '</li>';

    // email sklepu
    if ( INFO_EMAIL_SKLEPU != '' ) {
         echo '<li class="Iko Mail"><b>{__TLUMACZ:EMAIL}</b>';
         if ( isset($this->Formularze[1]) ) {
            echo '<a href="' . Seo::link_SEO( $this->Formularze[1], 1, 'formularz' ) . '">';
         }
         echo INFO_EMAIL_SKLEPU;
         if ( isset($this->Formularze[1]) ) {
            echo '</a>';
         }
         echo '</li>';
    } 

    // telefon
    if ( DANE_TELEFON_1 != '' || DANE_TELEFON_2 != '' || DANE_TELEFON_3 != '' ) {
         echo '<li class="Iko Tel"><b>{__TLUMACZ:TELEFON}</b>';
         //
         if ( DANE_TELEFON_1 != '' ) { echo DANE_TELEFON_1 . '<br />'; }
         if ( DANE_TELEFON_2 != '' ) { echo DANE_TELEFON_2 . '<br />'; }
         if ( DANE_TELEFON_3 != '' ) { echo DANE_TELEFON_3 . '<br />'; }
         //
         echo '</li>';
    }

    // fax
    if ( DANE_FAX_1 != '' ) {
         echo '<li class="Iko Fax"><b>Fax</b>' . DANE_FAX_1 . '</li>';
    }    

    // nr gg
    if ( DANE_GG_1 != '' || DANE_GG_2 != '' || DANE_GG_3 != '' ) {
         echo '<li class="Iko Gg"><b>Gadu Gadu</b>';
         //
         if ( DANE_GG_1 != '' ) { echo '<a rel="nofollow" href="gg:' . DANE_GG_1 . '">' . DANE_GG_1 . '</a><br />'; }
         if ( DANE_GG_2 != '' ) { echo '<a rel="nofollow" href="gg:' . DANE_GG_2 . '">' . DANE_GG_2 . '</a><br />'; }
         if ( DANE_GG_3 != '' ) { echo '<a rel="nofollow" href="gg:' . DANE_GG_3 . '">' . DANE_GG_3 . '</a><br />'; }
         //
         echo '</li>';
    }    
    
    // nr skype
    if ( DANE_SKYPE_1 != '' || DANE_SKYPE_2 != '' || DANE_SKYPE_3 != '' ) {
         echo '<li class="Iko Skype"><b>Skype</b>';
         //
         if ( DANE_SKYPE_1 != '' ) { echo '<a rel="nofollow" href="callto://' . DANE_SKYPE_1 . '">' . DANE_SKYPE_1 . '</a><br />'; }
         if ( DANE_SKYPE_2 != '' ) { echo '<a rel="nofollow" href="callto://' . DANE_SKYPE_2 . '">' . DANE_SKYPE_2 . '</a><br />'; }
         if ( DANE_SKYPE_3 != '' ) { echo '<a rel="nofollow" href="callto://' . DANE_SKYPE_3 . '">' . DANE_SKYPE_3 . '</a><br />'; }
         //
         echo '</li>';
    }

    // godziny dzialania
    if ( GODZINY_DZIALANIA != '' ) {
         echo '<li class="Iko Godziny"><b>{__TLUMACZ:GODZINY_OTWARCIA}</b>' . GODZINY_DZIALANIA . '</li>';
    }    
    
//
echo '</ul>';
//

unset($PokazNazweSklepu, $PokazDaneFirmy, $PokazNip);
?>