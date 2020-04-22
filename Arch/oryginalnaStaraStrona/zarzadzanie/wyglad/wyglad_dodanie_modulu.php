<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_GET['p'] == 'lista') {

        // pobieranie modulow wylaczonych
        $sqls = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.modul_status = '0' order by pd.modul_title");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz moduł ...');
        while ($infs = $sqls->fetch_assoc()) { 
            $tablica[] = array('id' => $infs['modul_id'], 'text' => $infs['modul_title'] . ' - ' . substr( $infs['modul_description'], 0, 110 ) . ((strlen($infs['modul_description']) > 100) ? ' ...' : ''));
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {        
            echo '<div style="padding:10px">';
            echo Funkcje::RozwijaneMenu('moduly', $tablica, '', ' onchange="wybierz_modul(this.value,\'' . $filtr->process($_GET['typ']) .'\')" style="width:430px"');
            echo '</div>';
            unset($tablica);
          } else { 
            echo '<div style="padding:10px">Brak danych do dodania ...</div>';
        }
        unset($tablica);        
        
    }
    
    if ($_GET['p'] == 'dodaj') {
    
        $sqls = $db->open_query("select * from theme_modules p, theme_modules_description pd where p.modul_id = pd.modul_id and p.modul_id = '".(int)$_GET['id']."' and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        $infs = $sqls->fetch_assoc();

        ?>
        <div class="box" id="modul_<?php echo (int)$_GET['id']; ?>">
            <?php
            if ($infs['modul_type'] == 'plik') { 
              echo '<span class="iplik">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>'; 
            }
            if ($infs['modul_type'] == 'java') { 
              echo '<span class="ikodjava">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';               
            }
            if ($infs['modul_type'] == 'strona') { 
              echo '<span class="istrona">'.$infs['modul_title'].'<br /><strong>' . $infs['modul_description'] . BoxyModuly::PolozenieModulu($info['modul_localization']) . '</strong></span>';   
            }    
            ?>
            <img class="skasuj toolTipTop" onclick="msk(<?php echo $infs['modul_id']; ?>,'<?php echo $filtr->process($_GET['typ']); ?>')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
            <a href="wyglad/srodek_edytuj.php?id_poz=<?php echo $infs['modul_id']; ?>&amp;zakladka=5"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj konfigurację modułu" /></a>
        </div>                        
        <?php  

        $db->close_query($sqls); 
        unset($infs);    
        //      
    }    
    
}
?>
