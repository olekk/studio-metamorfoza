<?php
// /* dodatkowe ustawienia konfiguracyjne */
//
// {{BOX_BANNERY_PLATNOSCI_PAYU;Czy wyświetlać w boxie logo PayU;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_DOTPAY;Czy wyświetlać w boxie logo Dotpay;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_PAYPAL;Czy wyświetlać w boxie logo PayPal;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_PAYBYNET;Czy wyświetlać w boxie logo PayByNet;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_TRANSFERUJ;Czy wyświetlać w boxie logo Transferuj;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_PRZELEWY24;Czy wyświetlać w boxie logo Przelewy24;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_CASHBILL;Czy wyświetlać w boxie logo CashBill;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_PAYEEZY;Czy wyświetlać w boxie logo Payeezy;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_SANTANDER;Czy wyświetlać w boxie logo Santander;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_LUKAS;Czy wyświetlać w boxie logo Lukas Raty;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_MBANK;Czy wyświetlać w boxie logo MBANK Raty;tak;tak,nie}}
// {{BOX_BANNERY_PLATNOSCI_PAYU_RATY;Czy wyświetlać w boxie logo PayU Raty;tak;tak,nie}}
//

if ( defined('BOX_BANNERY_PLATNOSCI_PAYU') ) {
   $PayU = BOX_BANNERY_PLATNOSCI_PAYU;
 } else {
   $PayU = 'tak';
}

if ( defined('BOX_BANNERY_PLATNOSCI_DOTPAY') ) {
   $Dotpay = BOX_BANNERY_PLATNOSCI_DOTPAY;
 } else {
   $Dotpay = 'tak';
}

if ( defined('BOX_BANNERY_PLATNOSCI_PAYPAL') ) {
   $PayPal = BOX_BANNERY_PLATNOSCI_PAYPAL;
 } else {
   $PayPal = 'tak';
}

if ( defined('BOX_BANNERY_PLATNOSCI_PAYBYNET') ) {
   $PayByNet = BOX_BANNERY_PLATNOSCI_PAYBYNET;
 } else {
   $PayByNet = 'tak';
}

if ( defined('BOX_BANNERY_PLATNOSCI_TRANSFERUJ') ) {
   $Transferuj = BOX_BANNERY_PLATNOSCI_TRANSFERUJ;
 } else {
   $Transferuj = 'tak';
}
    
if ( defined('BOX_BANNERY_PLATNOSCI_PRZELEWY24') ) {
   $Przelewy24 = BOX_BANNERY_PLATNOSCI_PRZELEWY24;
 } else {
   $Przelewy24 = 'tak';
}
if ( defined('BOX_BANNERY_PLATNOSCI_CASHBILL') ) {
   $CashBill = BOX_BANNERY_PLATNOSCI_CASHBILL;
 } else {
   $CashBill = 'tak';
}
if ( defined('BOX_BANNERY_PLATNOSCI_PAYEEZY') ) {
   $Payeezy = BOX_BANNERY_PLATNOSCI_PAYEEZY;
 } else {
   $Payeezy = 'tak';
}
if ( defined('BOX_BANNERY_PLATNOSCI_SANTANDER') ) {
   $Santander = BOX_BANNERY_PLATNOSCI_SANTANDER;
 } else {
   $Santander = 'tak';
}
if ( defined('BOX_BANNERY_PLATNOSCI_MBANK') ) {
   $MbankRaty = BOX_BANNERY_PLATNOSCI_MBANK;
 } else {
   $MbankRaty = 'tak';
}
if ( defined('BOX_BANNERY_PLATNOSCI_LUKAS') ) {
   $LukasRaty = BOX_BANNERY_PLATNOSCI_LUKAS;
 } else {
   $LukasRaty = 'tak';
}
if ( defined('BOX_BANNERY_PLATNOSCI_PAYU_RATY') ) {
   $PayURaty = BOX_BANNERY_PLATNOSCI_PAYU_RATY;
 } else {
   $PayURaty = 'tak';
}
if ($PayU == 'tak' || $Dotpay == 'tak' || $PayPal == 'tak' || $PayByNet == 'tak' || $Transferuj == 'tak' || $Przelewy24 == 'tak' || $CashBill == 'tak' || $Payeezy == 'tak' || $Santander == 'tak' || $LukasRaty == 'tak' || $MbankRaty == 'tak' || $PayURaty == 'tak') { 
    //
    echo '<ul class="Grafiki">';
    //
    if ($PayU == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/payu.png" alt="PayU" /></li>';
    }
    if ($Dotpay == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/dotpay.png" alt="Dotpay" /></li>';
    } 
    if ($PayPal == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/paypal.png" alt="PayPal" /></li>';
    }    
    if ($PayByNet == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/paybynet.png" alt="PayByNet" /></li>';
    }     
    if ($Transferuj == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/transferuj.png" alt="Transferuj" /></li>';
    }     
    if ($Przelewy24 == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/przelewy24.png" alt="Przelewy24" /></li>';
    }     
    if ($CashBill == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/cashbill.png" alt="CashBill" /></li>';
    }     
    if ($Payeezy == 'tak') { 
        echo '<li><img src="' . KATALOG_ZDJEC . '/platnosci/payeezy.png" alt="Payeezy" /></li>';
    }     
    if ($LukasRaty == 'tak' || $MbankRaty == 'tak' || $PayURaty == 'tak' || $Santander == 'tak') {
        $SystemyRatalne = Funkcje::AktywneSystemyRatalne();
    }
    if ($Santander == 'tak' && isset($SystemyRatalne['platnosc_santander'])) { 
        echo '<li><span onclick="SantanderRegulamin()"><img src="' . KATALOG_ZDJEC . '/platnosci/santander.png" alt="Sandander Consumer" /></span></li>';
    }     
    
    if ($LukasRaty == 'tak' && isset($SystemyRatalne['platnosc_lukas'])) { 
        echo '<li><span onclick="LukasProcedura(\''.$SystemyRatalne['platnosc_lukas']['PLATNOSC_LUKAS_NUMER_SKLEPU'].'\')"><img src="' . KATALOG_ZDJEC . '/platnosci/raty_140x51_duckblue.png" alt="Credit Agricole Raty" /></span></li>';
    }     
    if ($MbankRaty == 'tak' && isset($SystemyRatalne['platnosc_mbank'])) { 
        echo '<li><span onclick="MbankProcedura(\''.$SystemyRatalne['platnosc_mbank']['PLATNOSC_MBANK_NUMER_SKLEPU'].'\')"><img src="' . KATALOG_ZDJEC . '/platnosci/mbank.png" alt="MBANK Raty" /></span></li>';
    }     
    if ($PayURaty == 'tak' && isset($SystemyRatalne['platnosc_payu'])) { 
        echo '<li><span onclick="PayURatyProcedura()"><img src="' . KATALOG_ZDJEC . '/platnosci/tu_kupisz_na_raty_payu_blue.png" alt="PayU Raty" /></span></li>';
    }     
    //
    echo '</ul>';
    //
}

unset($PayU, $Dotpay, $PayPal, $PayByNet, $CashBill, $Payeezy, $Transferuj, $Przelewy24, $Santander, $LukasRaty);

?>