<?php
$nazwaDowyswietlania = '';
$edycjaElementu = '';

$strona = explode(';', $pozycje_menu[$x]);
                                            
switch ($strona[0]) {
    case "strona":
        $sqls = $db->open_query("select * from pages p, pages_description pd where p.pages_id = pd.pages_id and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.pages_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="stronainfo">'.$infs['pages_title'].((!empty($infs['link'])) ? ' <span>( link zewnętrzny: '.$infs['link'].' )</span>' : '<span>( link do strony informacyjnej )</span>' ).'</span>';
        $edycjaElementu = '<a href="strony_informacyjne/strony_informacyjne_edytuj.php?id_poz=' . $infs['pages_id'] . '&amp;zakladka=' . $nr_zakladki . '"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
        $idDoDiva = $strona[1].'strona';
        break;
    case "galeria":
        $sqls = $db->open_query("select * from gallery p, gallery_description pd where p.id_gallery = pd.id_gallery and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.id_gallery = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="galeria">'.$infs['gallery_name'].'<span>( link do galerii )</span></span>';
        $edycjaElementu = '<a href="galerie/galerie_edytuj.php?id_poz=' . $infs['id_gallery'] . '&amp;zakladka=' . $nr_zakladki . '"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
        $idDoDiva = $strona[1].'galeria';
        break; 
    case "formularz":
        $sqls = $db->open_query("select * from form p, form_description pd where p.id_form = pd.id_form and pd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and p.id_form = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="formularz">'.$infs['form_name'].'<span>( link do formularza )</span></span>';
        $edycjaElementu = '<a href="formularze/formularze_edytuj.php?id_poz=' . $infs['id_form'] . '&amp;zakladka=' . $nr_zakladki . '"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
        $idDoDiva = $strona[1].'formularz';
        break; 
    case "kategoria":
        $sqls = $db->open_query("select * from newsdesk_categories n, newsdesk_categories_description nd where n.categories_id = nd.categories_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.categories_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="artykul_kategoria">'.$infs['categories_name'].'<span>( link do kategorii aktualności )</span></span>';
        $edycjaElementu = '<a href="aktualnosci/aktualnosci_kategorie_edytuj.php?kat_id=' . $infs['categories_id'] . '&amp;zakladka=' . $nr_zakladki . '"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
        $idDoDiva = $strona[1].'kategoria';
        break; 
    case "artykul":
        $sqls = $db->open_query("select * from newsdesk n, newsdesk_description nd where n.newsdesk_id = nd.newsdesk_id and nd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and n.newsdesk_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="artykul">'.$infs['newsdesk_article_name'].'<span>( link do aktualności )</span></span>';
        $edycjaElementu = '<a href="aktualnosci/aktualnosci_edytuj.php?id_poz=' . $infs['newsdesk_id'] . '&amp;zakladka=' . $nr_zakladki . '"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
        $idDoDiva = $strona[1].'artykul';
        break; 
    case "kategproduktow":
        $sqls = $db->open_query("select * from categories c, categories_description cd where c.categories_id = cd.categories_id and c.parent_id = '0' and cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = '".(int)$strona[1]."'");
        $infs = $sqls->fetch_assoc();
        $nazwaDowyswietlania = '<span class="produkt_kategoria">'.$infs['categories_name'].'<span>( link do kategorii produktów )</span></span>';
        $edycjaElementu = '<a href="kategorie/kategorie_edytuj.php?id_poz=' . $infs['categories_id'] . '&amp;zakladka=' . $nr_zakladki . '"><img class="toolTipTop" src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj pozycję" /></a>';
        $idDoDiva = $strona[1].'kategproduktow';
        break;            
}

$db->close_query($sqls); 
unset($infs); 
?>