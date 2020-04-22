<?php
chdir('../');            

if (isset($_POST['id']) && (int)$_POST['id'] > 0) {

    // wczytanie ustawien inicjujacych system
    require_once('ustawienia/init.php');
    
    if (Sesje::TokenSpr()) {
    
        //
        if ($_POST['akcja'] == 'wl') {
            //
            $_SESSION['produktyPorownania'][(int)$_POST['id']] = (int)$_POST['id'];
            //
        }
        //
        if ($_POST['akcja'] == 'wy') {
            //
            unset($_SESSION['produktyPorownania'][(int)$_POST['id']]);
            //
        }
        //
        // wyswietla produkty
        if (count($_SESSION['produktyPorownania']) > 0) {
            //
            $DoPorownaniaId = '';
            foreach ($_SESSION['produktyPorownania'] AS $Id) {
                $DoPorownaniaId .= $Id . ',';
            }
            $DoPorownaniaId = substr($DoPorownaniaId, 0, -1);
            //
            $zapNazwy = Produkty::SqlPorownanieProduktow($DoPorownaniaId); 
            //
            $sqlNazwy = $db->open_query($zapNazwy);
            //
            while ($infc = $sqlNazwy->fetch_assoc()) {
                //
                // ustala jaka ma byc tresc linku
                $linkSeo = ((!empty($infc['products_seo_url'])) ? $infc['products_seo_url'] : $infc['products_name']);
                //
                // jezeli jest uruchomione z boxu
                if (isset($_POST['box'])) {
                    //
                    echo '<span onclick="PorownajBox(' . $infc['products_id'] . ')"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a> <br />';
                    //    
                  } else {
                    //
                    echo '<span onclick="Porownaj(' . $infc['products_id'] . ',\'wy\')"></span><a href="' . Seo::link_SEO( $linkSeo, $infc['products_id'], 'produkt' ) . '">' . $infc['products_name'] . '</a> <br />';
                    //    
                }
                unset($linkSeo);
                //
            }
            $db->close_query($sqlNazwy); 
            unset($zapNazwy, $DoPorownaniaId, $infc);      
            //
        }
    
    }
    
}

?>