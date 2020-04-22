<?php
// sprawdza czy nie sa wyswietlane wyniki ankiety
if (!isset($_GET['ida'])) {
    //
    // dodatkowy warunek dla tylko zalogowanych
    $TylkoZalogowani = " and p.poll_login = '0'";
    if (isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0') {
        $TylkoZalogowani = "";
    }
    // sortowanie 
    $TrybSortowania = 'p.poll_date_added desc';
    if (ANKIETA_TRYB_WYSWIETLANIA == 'losowo') {
        $TrybSortowania = 'rand()';
    }

    $ankieta = "SELECT p.id_poll,
                       p.poll_date_added, 
                      pd.poll_name
                    FROM poll p
              INNER JOIN poll_description pd ON p.id_poll = pd.id_poll AND pd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                   WHERE p.poll_status = '1' " . $TylkoZalogowani . "
                ORDER BY " . $TrybSortowania . " LIMIT 1";

    $sqlAnkieta = $GLOBALS['db']->open_query($ankieta);

    unset($TylkoZalogowani, $TrybSortowania);

    if ((int)$GLOBALS['db']->ile_rekordow($sqlAnkieta) > 0) { 

        $infoAnkieta = $sqlAnkieta->fetch_assoc();
        $idAnkiety = $infoAnkieta['id_poll'];
        $NazwaAnkiety = $infoAnkieta['poll_name'];
        //
        $GLOBALS['db']->close_query($sqlAnkieta); 
        unset($infoAnkieta, $ankieta);
        
        // szukanie pozycji ankiety
        $ankietaPozycje = "SELECT id_poll_unique, poll_field FROM poll_field WHERE id_poll = '" . $idAnkiety . "' AND language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "' ORDER BY poll_field_sort";    
        $sqlPoz = $GLOBALS['db']->open_query($ankietaPozycje);
                
        // jezeli jest wiecej niz 1 pytanie
        if ((int)$GLOBALS['db']->ile_rekordow($sqlPoz) > 1) { 
        
            echo '<div class="Ankieta">';
            
            echo '<form action="' . Seo::link_SEO($NazwaAnkiety, $idAnkiety, 'ankieta') . '" method="post" class="cmxform" id="ankietaBox">';
            
            echo '<h4>' . $NazwaAnkiety . '</h4>';
            
            echo '<ul class="Pytania">';

            while ($pozycje = $sqlPoz->fetch_assoc()) {
                //
                echo '<li><input type="radio" name="ankieta" value="' . $pozycje['id_poll_unique'] . '" /><b>' . $pozycje['poll_field'] . '</b></li>';
                //
            }
            
            $GLOBALS['db']->close_query($sqlPoz); 
            
            echo '</ul>';
            
            echo '<br /><div id="BladAnkiety" class="error" style="display:none">{__TLUMACZ:BLAD_ZAZNACZ_JEDNA_OPCJE}</div>';    
            
            echo '<div>';
            echo '<input type="hidden" value="'.$idAnkiety.'" name="id" />';
            echo '<input type="submit" id="submitAnkieta" class="przyciskWylaczony" value="{__TLUMACZ:PRZYCISK_ZAGLOSUJ}" />';
            echo '</div>';
            
            echo '</form>';

            echo '</div>';
            echo '<div class="WszystkieKreska"><a href="' . Seo::link_SEO($NazwaAnkiety, $idAnkiety, 'ankieta') . '">{__TLUMACZ:ZOBACZ_WYNIKI_ANKIETY}</a></div>';
        
        }
        
        unset($ankietaPozycje, $idAnkiety, $NazwaAnkiety); 
            
    }

    unset($random, $tablica, $LimitZnakow);
    //
}
?>