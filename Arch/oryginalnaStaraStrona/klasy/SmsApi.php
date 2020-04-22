<?php

class SmsApi {

  /*
  **** klasa do obslugi wysylania SMS
  */

  public static function wyslijSMS($adresat, $tekst, $tryb = '') {

    $url   = 'https://ssl.smsapi.pl/sms.do';

    if ( $tryb == '' ) {
        if ( SMS_TYP_WIADOMOSCI == 'pro' ) {
            $tryb = '0';
        } else {
            $tryb = '1';
        }
    }

    $plZnaki = array("Ą" => "A", "Ć" => "C", "Ę" => "E", "Ł" => "L", "Ń" => "N", "Ó" => "O", "Ś" => "S", "Ż" => "Z", "Ź" => "Z", "ą" => "a", "ć" => "c", "ę" => "e", "ł" => "l", "ń" => "n", "ó" => "o", "ś" => "s", "ż" => "z", "ź" => "z");
    $tekst = strtr($tekst, $plZnaki);

    $parameters = array();
    $parameters['username']              = SMS_UZYTKOWNIK;
    $parameters['password']              = md5(SMS_HASLO);
    $parameters['to']                    = $adresat;
    $parameters['from']                  = (SMS_NADAWCA != '' ? SMS_NADAWCA : 'Eco');
    $parameters['eco']                   = $tryb;
    $parameters['message']               = $tekst;
    $parameters['normalize']             = '1';
    $parameters['flash']                 = SMS_FLASH;

    $post_str = ''; 

    foreach ( $parameters as $key=>$val ) {
        $post_str .= $key.'='.$val.'&'; 
    } 
    $post_str = substr($post_str, 0, -1); 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,20);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 180);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
    $content = curl_exec($ch);
    curl_close ($ch);

    return;

  }

}