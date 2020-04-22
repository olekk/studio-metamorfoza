<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_GET['p'] == 'lista') {

        // pobieranie boxow wylaczonych
        $sqls = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.box_status = '0' order by pd.box_title");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz box ...');
        while ($infs = $sqls->fetch_assoc()) { 
            $tablica[] = array('id' => $infs['box_id'], 'text' => $infs['box_title'] . ' - ' . substr( $infs['box_description'], 0, 110 ) . ((strlen($infs['box_description']) > 100) ? ' ...' : ''));
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            echo '<div style="padding:10px">';
            echo Funkcje::RozwijaneMenu('boxy', $tablica, '', ' onchange="wybierz_box(this.value, \''.$filtr->process($_GET['kolumna']).'\')" style="width:430px"');
            echo '</div>';            
          } else { 
            echo '<div style="padding:10px">Brak danych do dodania ...</div>';
        }
        unset($tablica);
        
    }
    
    if ($_GET['p'] == 'dodaj') {
    
        $sqls = $db->open_query("select * from theme_box p, theme_box_description pd where p.box_id = pd.box_id and p.box_id = '".(int)$_GET['id']."' and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        $infs = $sqls->fetch_assoc();
        
        if ($_GET['kolumna'] == 'lewa') {
        
        // dla lewej kolumny
        ?>
        <div class="box" id="box_<?php echo (int)$_GET['id']; ?>">
            <?php
            // plik php czy strona informacyjna
            if ($infs['box_type'] == 'plik') { 
              echo '<span class="iplik">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . BoxyModuly::PolozenieBoxu($infs['box_localization']) . '</strong></span>'; 
            }
            if ($infs['box_type'] == 'java') { 
              echo '<span class="ikodjava">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . BoxyModuly::PolozenieBoxu($infs['box_localization']) . '</strong></span>';               
            }
            if ($infs['box_type'] == 'strona') { 
              echo '<span class="istrona">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . BoxyModuly::PolozenieBoxu($infs['box_localization']) . '</strong></span>';   
            }                                               
            ?>
            <img class="strzalka toolTipTop" onclick="ple(<?php echo (int)$_GET['id']; ?>)" src="obrazki/strzalka_prawa.png" alt="Przenieś do prawej kolumny" title="Przenieś do prawej kolumny" />
            <img class="skasuj toolTipTop" onclick="psk(<?php echo (int)$_GET['id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
            <a href="wyglad/boxy_edytuj.php?id_poz=<?php echo (int)$_GET['id']; ?>&amp;zakladka=4"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację boxu" /></a>
        </div>                        
        <?php  
        
        }
        
        if ($_GET['kolumna'] == 'prawa') {

        // dla prawej kolumny
        ?>
        <div class="box" id="box_<?php echo (int)$_GET['id']; ?>" style="text-align:right">
            <?php
            // plik php czy strona informacyjna
            if ($infs['box_type'] == 'plik') { 
              echo '<span class="rplik">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . '</strong></span>'; 
            }
            if ($infs['box_type'] == 'java') { 
              echo '<span class="rkodjava">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . '</strong></span>';               
            }
            if ($infs['box_type'] == 'strona') { 
              echo '<span class="rstrona">'.$infs['box_title'].'<br /><strong>' . $infs['box_description'] . '</strong></span>';   
            }                                               
            ?>
            <img class="strzalka toolTipTop" style="float:left" onclick="ple(<?php echo $infs['box_id']; ?>)" src="obrazki/strzalka_lewa.png" alt="Przenieś do lewej kolumny" title="Przenieś do lewej kolumny" />
            <img class="skasuj toolTipTop" style="float:left" onclick="psk(<?php echo $infs['box_id']; ?>)" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
            <a href="wyglad/boxy_edytuj.php?id_poz=<?php echo $infs['box_id']; ?>&amp;zakladka=4"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację boxu" /></a>
        </div>                        
        <?php   

        }

        $db->close_query($sqls); 
        unset($infs);    
        //      
        
    }    
    
}
?>
