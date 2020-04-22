<?php
class ZaufaneOpineo {

    function __construct() {
        $this->adres = 'http://www.wiarygodneopinie.pl/gate.php';
    }

    function opineo_zapisz_zaproszenie($email, $login, $haslo, $czas = 5, $order_no = false, $produkty = false) {

        if (defined('OPINEO_DEBUG')){
            return json_decode("{\"code\":".OPINEO_DEBUG.",\"message\":\"...\"}", true);
        }
        if ($czas != 5 && $czas != 1 && $czas != 10 && $czas != 20){
            $czas = 5;
        }
        
        $query = Array(
            'type'  =>  'php',
            'email' =>  $email,
            'login' =>  $login,
            'pass'  =>  $haslo,
            'queue' =>  $czas,
        );
        if (!empty($order_no)) $query['order_no'] = $order_no;
        
        $query = http_build_query($query);
        
        $o = curl_init();
        curl_setopt($o, CURLOPT_URL, $this->adres.'?'.$query);
        curl_setopt($o, CURLOPT_RETURNTRANSFER, 1);
        
        if(is_array($produkty)){
            curl_setopt($o, CURLOPT_POST, 1);
            curl_setopt($o, CURLOPT_POSTFIELDS, 'products='.json_encode($produkty));
        }
        
        $json = curl_exec($o);
        $http_status = curl_getinfo($o, CURLINFO_HTTP_CODE);        
        curl_close($o); 

        if ($http_status !== 200)
            return $json;
            
        if ($json === false)
            return false;           
            
        $res = json_decode($json, true);
        return $res;
    }
}
?>