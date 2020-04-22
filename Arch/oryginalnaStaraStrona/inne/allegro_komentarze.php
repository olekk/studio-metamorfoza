<?php
chdir('../');             

if ( file_exists('xml/komentarze.xml') && isset($_POST['limit']) && (int)$_POST['limit'] > 0 ) {

    echo '<strong><a href="http://allegro.pl/show_user.php?uid=' . (int)$_POST['id'] . '" target="_blank">NASZE KOMENTARZE NA ALLEGRO</a></strong>';

    $xml = @simplexml_load_file("xml/komentarze.xml");   
    
    if ( $xml ) {
    
        if ( count($xml->komentarze->komentarz) > 0 ) {
        
            $dopuszczalneIlosci = array();
            for ($y = 0; $y < count($xml->komentarze->komentarz); $y++) {
                $dopuszczalneIlosci[] = $y;
            }
            
            if ( (int)$_POST['limit'] > count($dopuszczalneIlosci) ) {
                 $_POST['limit'] = count($dopuszczalneIlosci);
            }
            
            if ( count($dopuszczalneIlosci) > 1 ) {
                $losoweKlucze = array_rand($dopuszczalneIlosci, (int)$_POST['limit']);
              } else {
                $losoweKlucze = $dopuszczalneIlosci;
            }
            
            unset($dopuszczalneIlosci);

            for ($f = 0; $f < count($xml->komentarze->komentarz); $f++) {
            
                if ( in_array($f, $losoweKlucze) ) {

                    echo '<div class="komentarz">';

                        echo '<div class="Lf">';

                            echo '<a href="http://allegro.pl/show_user.php?uid=' . $xml->komentarze->komentarz[$f]->nick_id . '">' . $xml->komentarze->komentarz[$f]->nick . '</a>';
                            
                            echo '<span>(' . $xml->komentarze->komentarz[$f]->nick_komentarze . ')</span>';
                            
                            if ( $xml->komentarze->komentarz[$f]->typ == '1' ) {
                                echo '<span>(Kupujący)</span>';
                              } else {
                                echo '<span>(Sprzedający)</span>';
                            }
                            
                        echo '</div>';
                        
                        echo '<div class="Rg">';
                        
                            echo '<span class="pozytywny">Pozytywny</span>';
                            
                            echo $xml->komentarze->komentarz[$f]->data;
                            
                        echo '</div>';
                        
                        echo '<div class="cl"></div>';
                        
                        echo '<div class="opisKomentarza">';

                            echo $xml->komentarze->komentarz[$f]->opis;
                            
                        echo '</div>';
                        
                    echo '</div>';
                    
                }
                
            } 

            unset($xml, $losoweKlucze);
            
        }
        
    }
    
}    

?>