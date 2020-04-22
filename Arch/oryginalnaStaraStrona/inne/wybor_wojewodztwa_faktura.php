<?php
chdir('../');            

if (isset($_POST['data']) && !empty($_POST['data'])) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');

    if (Sesje::TokenSpr()) {

        $wojewodztwa = "
        SELECT zone_id, zone_country_id, zone_name 
          FROM zones 
          WHERE zone_country_id = '" . $_POST['data'] . "'
          ORDER BY zone_name
        ";

        $sql = $db->open_query($wojewodztwa);

        $tablicaWojewodztw[] = array( 'id' => '0',
                                    'text' => '--- wybierz z listy ---');

        while ($wojewodztwa_wartosci = $sql->fetch_assoc()) {
            $tablicaWojewodztw[] = array( 'id' => $wojewodztwa_wartosci['zone_id'],
                                          'text' => $wojewodztwa_wartosci['zone_name']);
        }

        $db->close_query($sql);
        unset($wojewodztwa, $wojewodztwa_wartosci);

        if (count($tablicaWojewodztw) > 1) {

            echo Funkcje::RozwijaneMenu('wojewodztwoFaktura', $tablicaWojewodztw, '', 'style="width:255px;"'); 
            } else {
            echo '<span style="padding:3px; line-height:18px;">Brak danych ...</span';
            
        }
        
    }
    
}

?>