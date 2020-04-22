<?php
chdir('../');             

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {

    if ($_GET['p'] == 'lista') {
    
        $sameLinki = false;
        $textLinki = '';
    
        // pobieranie pol jakie sa w stalej w bazie do sprawdzenia czy takie pole nie jest dodane
        $sqlp = $db->open_query("select distinct * from settings where code = '" . strtoupper($_GET['div']) . "'");
        $infc = $sqlp->fetch_assoc();
        $ciagStalej = ','.$infc['value'];
        $db->close_query($sqlp); 
        unset($infc);     

        $zmiennaDoSprawdzaniaCzyCosZostalo = 0;

        // pobieranie stron informacyjnych
        $sqls = $db->open_query("select p.pages_id, pd.pages_title from pages p, pages_description pd where p.pages_id = pd.pages_id and pages_modul = '0' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'");

        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz stronę informacyjną ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!strpos($ciagStalej, 'strona;'.$infs['pages_id'])) {
                $tablica[] = array('id' => $infs['pages_id'], 'text' => $infs['pages_title'] . ((!empty($infs['link'])) ? ' ( link zewnętrzny )' : '' ));
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:10px">';
            $textLinki .= Funkcje::RozwijaneMenu('strony', $tablica, '', ' onchange="wybierz_stala(this.value,\'strona\',\''.$_GET['div'].'\')" style="width:430px"');
            $textLinki .= '</div>';
            unset($tablica);
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }
        
        // pobieranie galerii
        $sqls = $db->open_query("select distinct id_gallery, gallery_name from gallery_description pd where pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz galerię ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!strpos($ciagStalej, 'galeria;'.$infs['id_gallery'])) {
                $tablica[] = array('id' => $infs['id_gallery'], 'text' => $infs['gallery_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:10px">';
            $textLinki .= Funkcje::RozwijaneMenu('galerie', $tablica, '', ' onchange="wybierz_stala(this.value,\'galeria\',\''.$_GET['div'].'\')" style="width:430px"');
            $textLinki .= '</div>';
            unset($tablica);
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }            

        // pobieranie formularza
        $sqls = $db->open_query("select distinct id_form, form_name from form_description pd where pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz formularz ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!strpos($ciagStalej, 'formularz;'.$infs['id_form'])) {
                $tablica[] = array('id' => $infs['id_form'], 'text' => $infs['form_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:10px">';
            $textLinki .= Funkcje::RozwijaneMenu('formularze', $tablica, '', ' onchange="wybierz_stala(this.value,\'formularz\',\''.$_GET['div'].'\')" style="width:430px"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }   
        
        // pobieranie nazw kategorii aktualnosci
        $sqls = $db->open_query("select distinct categories_id,	categories_name from newsdesk_categories_description pd where pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię aktualności ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!strpos($ciagStalej, 'kategoria;'.$infs['categories_id'])) {
                $tablica[] = array('id' => $infs['categories_id'], 'text' => $infs['categories_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:10px">';
            $textLinki .= Funkcje::RozwijaneMenu('kategoria', $tablica, '', ' onchange="wybierz_stala(this.value,\'kategoria\',\''.$_GET['div'].'\')" style="width:430px"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }           
        
        // pobieranie nazw aktualnosci
        $sqls = $db->open_query("select distinct newsdesk_id,	newsdesk_article_name from newsdesk_description pd where pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz aktualności ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!strpos($ciagStalej, 'artykul;'.$infs['newsdesk_id'])) {
                $tablica[] = array('id' => $infs['newsdesk_id'], 'text' => $infs['newsdesk_article_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:10px">';
            $textLinki .= Funkcje::RozwijaneMenu('artykul', $tablica, '', ' onchange="wybierz_stala(this.value,\'artykul\',\''.$_GET['div'].'\')" style="width:430px"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        } 
        
        // pobieranie nazw kategorii produktow
        $sqls = $db->open_query("select distinct c.categories_id, cd.categories_name from categories_description cd, categories c where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
        
        $tablica = array();
        $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię produktów ...');
        while ($infs = $sqls->fetch_assoc()) { 
            if (!strpos($ciagStalej, 'kategproduktow;'.$infs['categories_id'])) {
                $tablica[] = array('id' => $infs['categories_id'], 'text' => $infs['categories_name']);
            }
        }
        $db->close_query($sqls); 
        unset($zapytanie_tmp, $infs);    
        //      
        if (count($tablica) > 1) {
            $textLinki .= '<div style="padding:10px">';
            $textLinki .= Funkcje::RozwijaneMenu('kategproduktow', $tablica, '', ' onchange="wybierz_stala(this.value,\'kategproduktow\',\''.$_GET['div'].'\')" style="width:430px"');
            $textLinki .= '</div>';        
            //
            $zmiennaDoSprawdzaniaCzyCosZostalo++;
            $sameLinki = true;
        }              
                
        if ( $sameLinki == true ) {
            //
            $textLinki = '<strong>Pojedyncze linki menu</strong>' . $textLinki;
            //
        }
        
        unset($sameLinki);
        
        // tylko dla gornego menu
        
        $textOkna = '';
        
        if ( $_GET['div'] == 'gorne_menu' ) {
        
            $sameOkna = false;

            // pobieranie nazw grup stron informacyjnych
            $sqls = $db->open_query("select pg.pages_group_id,
                                            pg.pages_group_code,
                                            pg.pages_group_title,
                                            pgd.pages_group_name
                                       from pages_group pg left join pages_group_description pgd on pg.pages_group_id = pgd.pages_group_id and pgd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
            
            $tablica = array();
            $tablica[] = array('id' => 0, 'text' => '... wybierz grupę stron informacyjnych ...');
            while ($infs = $sqls->fetch_assoc()) { 
                if (!strpos($ciagStalej, 'grupainfo;'.$infs['pages_group_id'])) {
                    $tablica[] = array('id' => $infs['pages_group_id'], 'text' => $infs['pages_group_code'] . ' - ' . $infs['pages_group_name']);
                }
            }
            $db->close_query($sqls); 
            unset($zapytanie_tmp, $infs);    
            //      
            if (count($tablica) > 1) {
                $textOkna .= '<div style="padding:10px">';
                $textOkna .= Funkcje::RozwijaneMenu('grupainfo', $tablica, '', ' onchange="wybierz_stala(this.value,\'grupainfo\',\''.$_GET['div'].'\')" style="width:430px"');
                $textOkna .= '</div>';        
                //
                $zmiennaDoSprawdzaniaCzyCosZostalo++;
                $sameOkna = true;
            }   

            // pobieranie nazw kategorii aktualnosci
            $sqls = $db->open_query("select distinct categories_id,	categories_name from newsdesk_categories_description pd where pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
            
            $tablica = array();
            $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię aktualności ...');
            while ($infs = $sqls->fetch_assoc()) { 
                if (!strpos($ciagStalej, 'artkategorie;'.$infs['categories_id'])) {
                    $tablica[] = array('id' => $infs['categories_id'], 'text' => $infs['categories_name']);
                }
            }
            $db->close_query($sqls); 
            unset($zapytanie_tmp, $infs);    
            //      
            if (count($tablica) > 1) {
                $textOkna .= '<div style="padding:10px">';
                $textOkna .= Funkcje::RozwijaneMenu('artkategorie', $tablica, '', ' onchange="wybierz_stala(this.value,\'artkategorie\',\''.$_GET['div'].'\')" style="width:430px"');
                $textOkna .= '</div>';        
                //
                $zmiennaDoSprawdzaniaCzyCosZostalo++;
                $sameOkna = true;
            }    

            // pobieranie nazw kategorii produktow
            $sqls = $db->open_query("select distinct c.categories_id, cd.categories_name from categories_description cd, categories c where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'");
            
            $tablica = array();
            $tablica[] = array('id' => 0, 'text' => '... wybierz kategorię produktów ...');
            while ($infs = $sqls->fetch_assoc()) { 
                if (!strpos($ciagStalej, 'prodkategorie;'.$infs['categories_id'])) {
                    $tablica[] = array('id' => $infs['categories_id'], 'text' => $infs['categories_name']);
                }
            }
            $db->close_query($sqls); 
            unset($zapytanie_tmp, $infs);    
            //      
            if (count($tablica) > 1) {
                $textOkna .= '<div style="padding:10px">';
                $textOkna .= Funkcje::RozwijaneMenu('prodkategorie', $tablica, '', ' onchange="wybierz_stala(this.value,\'prodkategorie\',\''.$_GET['div'].'\')" style="width:430px"');
                $textOkna .= '</div>';        
                //
                $zmiennaDoSprawdzaniaCzyCosZostalo++;
                $sameOkna = true;
            }              
        
            if ( $sameOkna == true ) {
                //
                $textOkna = '<strong class="OknaRozwijane">W postaci rozwijanych okien</strong>' . $textOkna;
                //
            }
            
            unset($sameOkna);
                
        }
        
        if ($zmiennaDoSprawdzaniaCzyCosZostalo == 0) {
            $textLinki .= '<div style="padding:10px">Brak danych do dodania ...</div>';
        }  
        
        echo $textLinki . $textOkna;

        unset($textLinki, $textOkna, $tablica, $zmiennaDoSprawdzaniaCzyCosZostalo);        
    }
    
    if ($_GET['p'] == 'dodaj') {
    
        $nazwaDowyswietlania = '';
        $edycjaElementu = '';    
    
        switch ($_GET['rodzaj']) {
            case "strona":
                $sqls = $db->open_query("select * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.pages_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="stronainfo">'.$infs['pages_title'].((!empty($infs['link'])) ? ' <span>( link zewnętrzny: '.$infs['link'].' )</span>' : '<span>( link do strony informacyjnej )</span>' ).'</span>';
                $edycjaElementu = '<a href="strony_informacyjne/strony_informacyjne_edytuj.php?id_poz=' . $infs['pages_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'strona';
                break;
            case "galeria":
                $sqls = $db->open_query("select * from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.id_gallery = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="galeria">'.$infs['gallery_name'].'<span>( link do galerii )</span></span>';
                $edycjaElementu = '<a href="galerie/galerie_edytuj.php?id_poz=' . $infs['id_gallery'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'galeria';
                break; 
            case "formularz":
                $sqls = $db->open_query("select * from form p, form_description pd where p.id_form = pd.id_form and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.id_form = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="formularz">'.$infs['form_name'].'<span>( link do formularza )</span></span>';
                $edycjaElementu = '<a href="formularze/formularze_edytuj.php?id_poz=' . $infs['id_form'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'formularz';
                break;
            case "kategoria":
                $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="artykul_kategoria">'.$infs['categories_name'].'<span>( link do kategorii aktualności )</span></span>';
                $edycjaElementu = '<a href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'kategoria';
                break; 
            case "artykul":
                $sqls = $db->open_query("select * from newsdesk n, newsdesk_description nd where n.newsdesk_id = nd.newsdesk_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.newsdesk_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="artykul">'.$infs['newsdesk_article_name'].'<span>( link do aktualności )</span></span>';
                $edycjaElementu = '<a href="aktualnosci/aktualnosci_edytuj.php?id_poz=' . $infs['newsdesk_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'artykul';
                break; 
            case "kategproduktow":
                $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="produkt_kategoria">'.$infs['categories_name'].'<span>( link do kategorii produktów )</span></span>';
                $edycjaElementu = '<a href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'kategproduktow';
                break;                   
            case "grupainfo":
                $sqls = $db->open_query("select pg.pages_group_id,
                                                pg.pages_group_code,
                                                pg.pages_group_title,
                                                pgd.pages_group_name
                                           from pages_group pg left join pages_group_description pgd on pg.pages_group_id = pgd.pages_group_id and pgd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'
                                          where pg.pages_group_id  = '".(int)$_GET['id']."'");
                
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="rozwijane">'.$infs['pages_group_name'].'<span>( okno rozwijane stron informacyjnych z grupy: ' . $infs['pages_group_code'] . ' )</span></span>';
                $edycjaElementu = '<a href="strony_informacyjne/strony_informacyjne_grupy_edytuj.php?id_poz=' . $infs['pages_group_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'grupainfo';
                break;
            case "artkategorie":
                $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="rozwijane">'.$infs['categories_name'].'<span>( okno rozwijane z artykułami z kategorii aktualności: ' . $infs['categories_name'] . ' )</span></span>';
                $edycjaElementu = '<a href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'artkategorie';
                break; 
            case "prodkategorie":
                $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$_GET['id']."'");
                $infs = $sqls->fetch_assoc();
                $nazwaDowyswietlania = '<span class="rozwijane">'.$infs['categories_name'].'<span>( okno rozwijane z podkategoriami z kategorii produktów: ' . $infs['categories_name'] . ' )</span></span>';
                $edycjaElementu = '<a href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=3"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
                $idDoDiva = (int)$_GET['id'].'prodkategorie';
                break;                
        }

        $db->close_query($sqls); 
        unset($infs);                                                
        ?>
        
        <div class="stala" id="<?php echo $_GET['div']; ?>_<?php echo $idDoDiva; ?>">
            <?php echo $nazwaDowyswietlania; ?>
            <img class="skasuj toolTipTop" onclick="ssk('<?php echo $idDoDiva; ?>','<?php echo $_GET['div']; ?>')" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
            <?php echo $edycjaElementu; ?>
        </div>                        
        <?php
        
    }    
    
}
?>
