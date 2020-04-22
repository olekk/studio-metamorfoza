<?php
chdir('../'); 

if (isset($_GET['ilosc']) && (int)$_GET['ilosc'] > 0 && isset($_GET['powrot'])) { 

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    $zapytanie = "SELECT search_key, freq FROM customers_searches WHERE language_id = '".$_SESSION['domyslnyJezyk']['id']."'";

    $sql = $db->open_query($zapytanie);

    $ile_rekordow = $db->ile_rekordow($sql);

    if ((int)$ile_rekordow > 0) { 

        while ($info = $sql->fetch_assoc()) {
            $tablica_tmp[] = array('search' => $info["search_key"], 'freq' => $info["freq"]);
        }

        $tablica = Funkcje::wylosujElementyTablicyJakoTablica($tablica_tmp, (int)$_GET['ilosc']);
        $tablica = Funkcje::wymieszajTablice($tablica);

        $x = 0;
        //utworzenie obiektu json
        $json = "({ tags:[";

        foreach ( $tablica as $wynik ) {

          $x++;
          $json .= "{tag:'" . $wynik["search"] . "',freq:'" . $wynik["freq"] . "'}";

          //dodanie przecinka lub zakonczenie obiektu json
          if ($x < count($tablica) ) {
            $json .= ",";
          } else {
            $json .= "]})";
          }

        }
        
    }

    $response = $_GET["powrot"] . $json;
    echo $response;

    $db->close_query($sql); 
    unset($response, $zapytanie, $info, $tablica_tmp);
    
}

?>