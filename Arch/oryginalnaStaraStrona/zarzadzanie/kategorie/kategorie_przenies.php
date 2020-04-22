<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        $id_kategoria_przenoszona = $filtr->process($_POST['id']);
        $id_kategoria = $filtr->process($_POST['id_kat']);
        $tablica_id = explode('_',Kategorie::SciezkaKategoriiId($id_kategoria,'categories'));
        
        $id_docelowa = $tablica_id[count($tablica_id) - 1];
        
        if ($id_kategoria_przenoszona != $id_docelowa) {
            //
            if ($id_docelowa != '') {
                //
                // blad jezeli bedzie sie chcialo przeniesc kategorie do jej podkategorii
                if (in_array($id_kategoria_przenoszona, $tablica_id)) {
                    $blad = '&blad=b2';
                    //
                    Funkcje::PrzekierowanieURL('kategorie_przenies.php?id_poz='.$id_kategoria_przenoszona . $blad);
                    //
                } else {
                    // jezeli jest wszystko ok zapisuje w bazie
                    $pola = array(array('parent_id',$id_docelowa));
                    $sql = $db->update_query('categories' , $pola, " categories_id = '".$id_kategoria_przenoszona."'");
                    unset($pola);
                    //
                    // jezeli zostanie przeniesiona jako podkategoria musi byc usunieta z gorne menu
                    if ( $tablica_id[0] != 0 ) {
                         Funkcje::UsuwanieWygladu('prodkategorie',$id_kategoria_przenoszona);                      
                    }
                    //
                    Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.$id_kategoria_przenoszona);
                    //                    
                }
            } else {
                //
                Funkcje::PrzekierowanieURL('kategorie.php?id_poz='.$id_kategoria_przenoszona);
                //            
            }
        } else {
            // blad jezeli chce sie przeniesc do tej samej kategorii
            $blad = '&blad=b1';
            //
            Funkcje::PrzekierowanieURL('kategorie_przenies.php?id_poz='.$id_kategoria_przenoszona . $blad);
            //
        }
        
    }
              
    // wczytanie naglowka HTML
    include('naglowek.inc.php');     
    ?>

    <div id="naglowek_cont">Przenoszenie kategorii</div>
    <div id="cont">        
    
        <form action="kategorie/kategorie_przenies.php" method="post" id="poForm" class="cmxform"> 

        <div class="poleForm">
            <div class="naglowek">Przenoszenie kategorii</div>
            
            <?php
            
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            
            $zapytanie = "select distinct categories_id, categories_name, language_id from categories_description where categories_id = '".$filtr->process($_GET['id_poz'])."' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
            $sql = $db->open_query($zapytanie);

            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();
                
                if (isset($_GET['blad'])) {
                    if ($_GET['blad'] == 'b1') {
                        $tekst = 'Nie można przenieść do tej samej kategorii !';
                    }
                    if ($_GET['blad'] == 'b2') {
                        $tekst = 'Kategoria nie może być przeniesiona do własnej podkategorii !';
                    }   
                    $tytul = 'Błąd przenoszenia kategorii';
                    echo Okienka::pokazOkno($tytul,$tekst);                 
                }              
                ?>            

                <div class="pozycja_edytowana">    

                    <input type="hidden" name="akcja" value="zapisz" />
                    
                    <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />
                       
                    <p>
                    Wybierz kategorię do której chcesz przenieść kategorię o nazwie: <span style="font-weight:bold"><?php echo $info['categories_name']; ?></span>
                    </p>
                    
                    <div id="drzewo" style="margin-left:10px;margin-top:10px">
                        <?php
                        //
                        echo '<table class="pkc" cellpadding="0" cellspacing="0">
                              <tr>
                                <td class="lfp" colspan="2"><input type="radio" value="0" name="id_kat" checked="checked" />-- brak kategorii nadrzędnej --</td>
                              </tr>';
                        //
                        $tablica_kat = Kategorie::DrzewoKategorii('0', '', '', '', false, true);
                        for ($w = 0, $c = count($tablica_kat); $w < $c; $w++) {
                            $podkategorie = false;
                            if ($tablica_kat[$w]['podkategorie'] == 'true') { $podkategorie = true; }
                            //
                            echo '<tr>
                                    <td class="lfp"><input type="radio" value="'.$tablica_kat[$w]['id'].'" name="id_kat" /> '.$tablica_kat[$w]['text'].'</td>
                                    <td class="rgp" '.(($podkategorie) ? 'id="img_'.$tablica_kat[$w]['id'].'"' : '').'>'.(($podkategorie) ? '<img src="obrazki/rozwin.png" alt="Rozwiń" title="Rozwiń" onclick="podkat(\''.$tablica_kat[$w]['id'].'\',\'\',\'radio\')" />' : '').'</td>
                                  </tr>
                                  '.(($podkategorie) ? '<tr><td colspan="2"><div id="p_'.$tablica_kat[$w]['id'].'"></div></td></tr>' : '').'';
                        }
                        echo '</table>';
                        unset($tablica_kat,$podkategorie);
                        ?> 
                    </div>                    

                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('kategorie','<?php echo Funkcje::Zwroc_Get(array('x','y')); ?>');">Powrót</button>        
                </div>                    
                
                <?php 
                $db->close_query($sql);
                unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>

        </div>      
        
        </form>

    </div>    
    
    <?php
    include('stopka.inc.php');

}
