<?php

//
echo '<div class="BoxNewsletter">';

echo '<p class="NewsletterOpis">{__TLUMACZ:INFO_NEWSLETTER}</p>';

echo '<form action="/" onsubmit="return sprNewsletter(this)" method="post" class="cmxform" id="newsletter">';

echo '<p class="PoleAdresu">';

    echo '<input type="text" name="email" id="emailNewsletter" value="{__TLUMACZ:TWOJ_ADRES_EMAIL}" />';
    
echo '</p>';

echo '<div>';

    echo '<input type="submit" id="submitNewsletter" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_ZAPISZ}" /> &nbsp;';
    
    echo '<input type="button" id="submitUnsubscribeNewsletter" class="przyciskWylaczony" onclick="wypiszNewsletter()" value="{__TLUMACZ:PRZYCISK_WYPISZ}" />';
    
echo '</div>';

echo '</form>';
 
echo '</div>';

//
    
?>