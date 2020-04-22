<?php
class Szablony {

    function Szablony($nazwa, $__pA = null, $__pB = null, $__pC = null) {
        global $nawigacja;
        
        $this->nazwa = $nazwa;
        $this->pA = $__pA;
        $this->pB = $__pB;
        $this->pC = $__pC;
        
        $this->parametr = array();
     
        $this->dane = Array();

    }

    function dodaj($nazwa, $wartosc = '') {
        if (is_array($nazwa)) {
            $this->dane = array_merge($this->dane, $nazwa);
        } else {
            $this->dane[$nazwa] = $wartosc; 
        }
    }
    
    function parametr($nazwa, $wartosc) {
        $this->parametr[$nazwa] = $wartosc;
    }

    function uruchom($glowny = false) {
        global $i18n, $nawigacja;
        
        $__pA = $this->pA;
        $__pB = $this->pB;
        $__pC = $this->pC;
        $__Parametr = $this->parametr;
    
        ob_start();
        
        // dodatkowe sprawdzanie czy jest plik na serwerze
        if ( !file_exists($this->nazwa) ) {
             exit('<span style="font-family:Arial;font-size:100%;color:#000000">Blad odczytu pliku <b>' . $this->nazwa . '</b> z szablonu graficznego sklepu ...'); 
        }
        
        require($this->nazwa);
        
        $this->tmpl = ob_get_contents();
        ob_end_clean();       

        $Szablon = $this->tmpl;
        foreach ( $this->dane as $Klucz => $Zamiana ) {
            //
            $Szablon = str_replace('{' . $Klucz . '}', $Zamiana, $Szablon);
            //
        }
        unset($Klucz, $Zamiana);
        
        // zamienia stale jezykowe
        $preg = preg_match_all('|{__TLUMACZ:([0-9A-Z_]+?)}|', $Szablon, $matches);
        foreach ($matches[1] as $WartoscJezykowa) {
            //
            if ( isset($GLOBALS['tlumacz'][$WartoscJezykowa]) ) {
                 $Szablon = str_replace('{__TLUMACZ:' . $WartoscJezykowa . '}', nl2br($GLOBALS['tlumacz'][$WartoscJezykowa]), $Szablon);
            }
            //
        }
        
        // zamienia linki SSL
        $preg = preg_match_all('|{__SSL:([0-9a-zA-Z-._]+?)}|', $Szablon, $matches);
        foreach ($matches[1] as $Link) {
            //
            if ( WLACZENIE_SSL == 'tak' ) {
                $Szablon = str_replace('{__SSL:' . $Link . '}', ADRES_URL_SKLEPU_SSL . '/' . $Link, $Szablon);
              } else {
                $Szablon = str_replace('{__SSL:' . $Link . '}', $Link, $Szablon);
            }
        }        
        
        // zmienia tylko adres aktualnej strony
        $Szablon = str_replace('{__AKTUALNY_LINK}', $_SERVER['REQUEST_URI'], $Szablon);
 
        // czysci komentarze html, kompresuje etc - tylko dla glownego szablonu
        if ( $glowny == true ) {

            // zabezpiecza komentarze js
            $Szablon = str_replace('"><!--', '"><!js--', $Szablon);
            $Szablon = str_replace('//-->', '/js/-->', $Szablon);
            //
            $Szablon = preg_replace('/<!--(.*)-->/Uis', '', $Szablon);
            //
            
            if ( KOMPRESJA_HTML == 'tak' ) {
                 $Szablon = $this->htmlCompress($Szablon);
            }
            
            // dodaje domene jezeli jest wlaczony ssl
            if ( WLACZENIE_SSL == 'tak' ) {
                 $Szablon = preg_replace("#(<\s*a\s+[^>]*href\s*=\s*[\"'])(?!http|mailto|gg|callto)([^\"'>]+)([\"'>]+)#", '$1' . ADRES_URL_SKLEPU . '/$2$3', $Szablon);
                 $Szablon = preg_replace('/([^(:\')])(\/{2,})/', '$1/', $Szablon);
            }
            
            // odwraca zabezpieczenie js
            $Szablon = str_replace('<!js--', '<!--', $Szablon);
            $Szablon = str_replace('/js/-->', '//-->', $Szablon);  

        }
        
        return $Szablon;

    }
    
    function htmlCompress($html) {
        //
        preg_match_all('!(<(?:code|pre|script).*>[^<]+</(?:code|pre|script)>)!',$html,$pre);
        $html = preg_replace('!<(?:code|pre).*>[^<]+</(?:code|pre)>!', '#pre#', $html);
        $html = preg_replace('#<!–[^\[].+–>#', '', $html);
        $html = preg_replace('/[\r\n\t]+/', ' ', $html);
        $html = preg_replace('/>[\s]+</', '><', $html);
        $html = preg_replace('/[\s]+/', ' ', $html);
        if (!empty($pre[0])) {
            foreach ($pre[0] as $tag) {
                $html = preg_replace('!#pre#!', $tag, $html,1);
            }
        }
        
        $html = str_replace('<script type="text/javascript">', '<script type="text/javascript">' . "\r\n", $html);
        $html = str_replace('//<![CDATA[', '//<![CDATA[' . "\r\n", $html);
        $html = str_replace('//]]>', '//]]>' . "\r\n", $html);
        $html = str_replace('</script>', '</script>' . "\r\n", $html);
        return $html;
       //
    }    

}
?>