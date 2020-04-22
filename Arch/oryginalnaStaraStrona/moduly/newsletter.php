<?php

//
echo '<div class="ModulNewsletter">';

echo '<strong>{__TLUMACZ:NAGLOWEK_NEWSLETTER}</strong>';

echo '<form action="/" onsubmit="return sprNewsletter(this)" method="post" class="cmxform" id="newsletterModul">';

echo '<p>{__TLUMACZ:INFO_NEWSLETTER}</p>';

echo '<p>';

    echo '<input type="text" name="email" id="emailNewsletterModul" value="{__TLUMACZ:TWOJ_ADRES_EMAIL}" />';
    
echo '</p>';

echo '<p>';

    echo '<input type="submit" id="submitNewsletterModul" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_ZAPISZ}" /> &nbsp;';
    
    echo '<input type="button" id="submitUnsubscribeNewsletterModul" class="przyciskWylaczony" onclick="wypiszNewsletter(\'newsletterModul\')" value="{__TLUMACZ:PRZYCISK_WYPISZ}" />';
    
echo '</p>';

echo '</form>';
 
echo '</div>';

?>