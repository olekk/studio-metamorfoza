<?php

class Seo {

    /*
    
    opis dla przykladowych linkow:
    produkt - Seo::link_SEO($info['products_name'],$info['products_id'],'produkt') -
    ** WAZNE ** 
    Dla produktow moze byc jeszcze $info['products_seo_url'] - indywidualny link SEO - nie z nazwy produktu
    ** K **
    szczegoly reklamacji - Seo::link_SEO('reklamacje_szczegoly.php',(int)$_POST["id"],'reklamacja')
    szczegoly zamowienia - Seo::link_SEO('zamowienia_szczegoly.php',(int)$_POST["id"],'zamowienie')
    kategoria - Seo::link_SEO($info['categories_name'],$info['categories_id']'kategoria')
    formularz - Seo::link_SEO($info['form_name'],$info['form_id'],'formularz')
    galeria - Seo::link_SEO($info['gallery_name'],$info['gallery_id'],'galeria')
    ankieta - Seo::link_SEO($info['poll_name'],$info['id_poll'],'ankieta')
    producent - Seo::link_SEO($info['manufacturers_name'],$info['manufacturers_id'],'producent')
    aktualnosc - Seo::link_SEO($info['newsdesk_article_name'],$info['newsdesk_id']'aktualnosci')
    kontakt - Seo::link_SEO($info['kontakt.php'],'','inna')
    
    stosowane prefixy
    zs - zamowienie
    zr - reklamacja
    p - produkt
    kartapdf - produkt_pdf
    c - kategoria
    pm - strona informacyjna
    a - ankieta
    g - galeria
    f - formularz
    m - producent
    r - recenzja
    rw - napisz recenzje
    n - aktualnosc
    nc - kategoria aktualnosci
    */

    static public function link_SEO($ciag_znakow, $id = '', $typ = '', $prefix = '') {  
        //
        $prefixy = array('zamowienie' => 'zs',
                         'reklamacja' => 'rs',
                         'produkt' => 'p',
                         'produkt_pdf' => 'kartapdf',
                         'kategoria' => 'c',
                         'strona_informacyjna' => 'pm',
                         'ankieta' => 'a',
                         'galeria' => 'g',
                         'formularz' => 'f',
                         'recenzja' => 'r',
                         'producent' => 'm',
                         'aktualnosc' => 'n',
                         'kategoria_aktualnosci' => 'nc',
                         'inna' => '');        
        //
        $polskie = array(',', ' - ',' ','ę', 'Ę', 'ó', 'Ó', 'Ą', 'ą', 'Ś', 'ś', 'ł', 'Ł', 'ż', 'Ż', 'Ź', 'ź', 'ć', 'Ć', 'ń', 'Ń','-','_',"'","/","?", '"', ":", 'ś', '!','.', '&', '&amp;', '#', ';', '[',']','php', '(', ')', '`', '%', '”', '„', '…');
        $miedzyn = array('-','-','-','e', 'e', 'o', 'o', 'a', 'a', 's', 's', 'l', 'l', 'z', 'z', 'z', 'z', 'c', 'c', 'n', 'n','-','-',"","","","","",'s','','', '', '', '', '', '', '', '', '', '', '', '', '');
        $ciag_znakow = str_replace($polskie, $miedzyn, $ciag_znakow);
        $ciag_znakow = strtolower($ciag_znakow);
        
        // usuwa tagi html z nazwy
        $ciag_znakow = strip_tags($ciag_znakow);        
        
        // usuń wszytko co jest niedozwolonym znakiem
        $ciag_znakow = preg_replace('/[^0-9a-z\-]+/', '', $ciag_znakow);
        
        // zredukuj liczbę myślników do jednego obok siebie
        $ciag_znakow = preg_replace('/[\-]+/', '-', $ciag_znakow);
        
        // usuwamy możliwe myślniki na początku i końcu
        $ciag_znakow = trim($ciag_znakow, '-');

        $ciag_znakow = stripslashes($ciag_znakow);
        
        // na wszelki wypadek
        $ciag_znakow = urlencode($ciag_znakow);
        
        $prefix = '';
        if ( !empty($typ) ) {
            //
            if ($prefix == '') {
                $prefix = $prefixy[$typ];
            }
            //
        }
        unset($prefixy, $polskie, $miedzyn);
        
        /*
        postac np produkt/11/nazwa-produktu.html
        return $typ . '/' . $id . '/' . $ciag_znakow . '.html';
        */
        
        /*
        postac np nazwa-produktu-p-11.html
        */
        // return ADRES_URL_SKLEPU . '/' . $ciag_znakow . '-' . $prefix . '-' . $id . '.html';
        
        if ( $typ == '' ) {
            return $ciag_znakow;
        }
        
        if ( $typ != 'inna' ) {
            return $ciag_znakow . '-' . $prefix . '-' . $id . '.html';
          } else {
            return $ciag_znakow . '.html';
        }
    }
    
    // porownanie aktualnego linku z przegladarka - jezeli jest inny to przekieruje na poprawny
    static public function link_Spr( $linkSeo ) {  
        //
        $podzielLink = explode('?', $_SERVER['REQUEST_URI'], 2);
        //
        if ( substr($podzielLink[0], 0, 1) == '/' ) {
             $podzielLink[0] = substr($podzielLink[0], 1);
        }
        //
        $podzielSlash = explode('/', $podzielLink[0]);
        //
        if ( $podzielSlash[0] != $linkSeo ) {
             Funkcje::PrzekierowanieURL($linkSeo); 
        }
        //
    }
    
}

?>