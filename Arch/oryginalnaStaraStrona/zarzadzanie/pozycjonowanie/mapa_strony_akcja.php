<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

if (isset($_POST['plik']) && !empty($_POST['plik']) && isset($_POST['limit']) && (int)$_POST['limit'] > -1 && Sesje::TokenSpr()) {

    $WczytanyPlik = '';
    if ((int)$_POST['limit'] != 0) {
         $WczytanyPlik = file_get_contents($filtr->process($_POST['plik']), FILE_USE_INCLUDE_PATH);
    }
    
    // uchwyt pliku, otwarcie do dopisania
    if ((int)$_POST['limit'] == 0) {
        $fp = fopen($filtr->process($_POST['plik']), "w");
      } else {
        $fp = fopen($filtr->process($_POST['plik']), "a");
    }
    // blokada pliku do zapisu
    flock($fp, 2);
    
    $CoDoZapisania = '';
    
    if ((int)$_POST['limit'] == 0) {
        //
        $CoDoZapisania = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n" .
                         ' <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\r\n";    
        $CoDoZapisania .= '  <url>'."\r\n";
        $CoDoZapisania .= '    <loc>'.ADRES_URL_SKLEPU.'</loc>'."\r\n";
        $CoDoZapisania .= '    <priority>1.0</priority>'."\r\n";
        $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
        $CoDoZapisania .= '  </url>'."\r\n";
        //
        
        // INNE STRONY
        
        // bestsellery
        $Inne = array('bestsellery.html',
                      'hity.html',
                      'nowosci.html',
                      'produkty.html',
                      'polecane.html',
                      'promocje.html',
                      'oczekiwane.html',
                      'producenci.html',
                      'recenzje.html');

        foreach ( $Inne as $Str ) {
            //
            $CoDoZapisania .= '  <url>'."\r\n";
            $CoDoZapisania .= '    <loc>'.ADRES_URL_SKLEPU . '/' . $Str.'</loc>'."\r\n";      
            $CoDoZapisania .= '    <priority>0.5</priority>'."\r\n";
            $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
            $CoDoZapisania .= '  </url>'."\r\n";        
            //
        }

        // KATEGORIE
        if (isset($_POST['kategorie']) && (int)$_POST['kategorie'] == 1) {
            //
            $priorytet = $_POST['priorytet_kategorie'];
            $zapytanie = "select distinct c.categories_id,
                                          cd.categories_name
                                     from categories c, 
                                          categories_description cd
                                    where c.categories_status = '1' and
                                          c.categories_id = cd.categories_id and
                                          cd.language_id = '" . (int)$_POST['jezyk'] . "' 
                                 order by c.parent_id, c.sort_order, c.categories_id";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["categories_name"], Kategorie::SciezkaKategoriiId($info["categories_id"]),'kategoria','',false).'</loc>'."\r\n";      
                $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);            
        }        
        
        // STRONY INFORMACYJNE
        if (isset($_POST['strony_info']) && (int)$_POST['strony_info'] == 1) {
            //
            $priorytet = $_POST['priorytet_strony_info'];
            $zapytanie = "select distinct p.pages_id,
                                          pd.pages_title,
                                          pd.pages_text
                                     from pages p, 
                                          pages_description pd
                                    where p.status = '1' and
                                          p.pages_id = pd.pages_id and
                                          pd.language_id = '" . (int)$_POST['jezyk'] . "' and
                                          p.link = '' and
                                          p.pages_customers_group_id = '0' and
                                          ( p.pages_modul = '0' or length(pd.pages_text) > 10 )
                                 order by p.pages_id";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["pages_title"], $info["pages_id"],'strona_informacyjna','',false).'</loc>'."\r\n";      
                $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);            
        }

        // ANKIETY
        if (isset($_POST['ankiety']) && (int)$_POST['ankiety'] == 1) {
            //
            $priorytet = $_POST['priorytet_ankiety'];
            $zapytanie = "select distinct p.id_poll,
                                          pd.poll_name,
                                          p.poll_date_added
                                     from poll p, 
                                          poll_description pd
                                    where p.poll_status = '1' and
                                          p.id_poll = pd.id_poll and
                                          pd.language_id = '" . (int)$_POST['jezyk'] . "' 
                                 order by p.id_poll";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //
                $data_modyfikacji = '';
                if (Funkcje::czyNiePuste($info['poll_date_added'])) {
                    $data_modyfikacji = date('Y-m-d',strtotime($info['poll_date_added']));
                }          
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["poll_name"], $info["id_poll"],'ankieta','',false).'</loc>'."\r\n";     
                if (!empty($data_modyfikacji)) {
                    $CoDoZapisania .= '    <lastmod>'.$data_modyfikacji.'</lastmod>'."\r\n";
                }                
                $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);            
        } 

        // GALERIE
        if (isset($_POST['galerie']) && (int)$_POST['galerie'] == 1) {
            //
            $priorytet = $_POST['priorytet_galerie'];
            $zapytanie = "select distinct g.id_gallery,
                                          gd.gallery_name
                                     from gallery g, 
                                          gallery_description gd
                                    where g.gallery_status = '1' and
                                          g.gallery_customers_group_id = '0' and
                                          g.id_gallery = gd.id_gallery and
                                          gd.language_id = '" . (int)$_POST['jezyk'] . "' 
                                 order by g.id_gallery";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["gallery_name"], $info["id_gallery"],'galeria','',false).'</loc>'."\r\n";      
                $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);            
        }  

        // FORMULARZE
        if (isset($_POST['formularze']) && (int)$_POST['formularze'] == 1) {
            //
            $priorytet = $_POST['priorytet_formularze'];
            // wyklucza z mapy formularz zapytania o produkt, negocjacje i polec znajomemu
            $zapytanie = "select distinct f.id_form,
                                          fd.form_name
                                     from form f, 
                                          form_description fd
                                    where f.id_form != '2' and f.id_form != '3' and f.id_form != '4' and
                                          f.form_customers_group_id = '0' and
                                          f.form_status = '1' and
                                          f.id_form = fd.id_form and
                                          fd.language_id = '" . (int)$_POST['jezyk'] . "' 
                                 order by f.id_form";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["form_name"], $info["id_form"],'formularz','',false).'</loc>'."\r\n";      
                $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);            
        }
        
        // PRODUCENCI
        if (isset($_POST['producenci']) && (int)$_POST['producenci'] == 1) {
            //
            $priorytet = $_POST['priorytet_producenci'];
            $zapytanie = "select distinct manufacturers_id,
                                          manufacturers_name
                                     from manufacturers  
                                 order by manufacturers_id";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["manufacturers_name"], $info["manufacturers_id"],'producent','',false).'</loc>'."\r\n";      
                $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);
        }      

        // AKTUALNOSCI
        if (isset($_POST['aktualnosci']) && (int)$_POST['aktualnosci'] == 1) {
            //
            // artykuly
            $priorytet = $_POST['priorytet_aktualnosci'];
            $zapytanie = "select distinct n.newsdesk_id,
                                          n.newsdesk_date_added,
                                          nd.newsdesk_article_name
                                     from newsdesk n, 
                                          newsdesk_description nd
                                    where n.newsdesk_status = '1' and
                                          n.newsdesk_customers_group_id = '0' and
                                          n.newsdesk_id = nd.newsdesk_id and
                                          nd.language_id = '" . (int)$_POST['jezyk'] . "' 
                                 order by n.newsdesk_id";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //   
                $data_modyfikacji = '';
                if (Funkcje::czyNiePuste($info['newsdesk_date_added'])) {
                    $data_modyfikacji = date('Y-m-d',strtotime($info['newsdesk_date_added']));
                }          
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["newsdesk_article_name"], $info["newsdesk_id"],'aktualnosc','',false).'</loc>'."\r\n";     
                if (!empty($data_modyfikacji)) {
                    $CoDoZapisania .= '    <lastmod>'.$data_modyfikacji.'</lastmod>'."\r\n";
                }   
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($zapytanie, $info);    

            // kategorie artykulow
            $zapytanie = "select distinct n.categories_id,
                                          nd.categories_name
                                     from newsdesk_categories n, 
                                          newsdesk_categories_description nd
                                    where n.categories_id = nd.categories_id and
                                          nd.language_id = '" . (int)$_POST['jezyk'] . "' 
                                 order by n.categories_id";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //     
                $CoDoZapisania .= '  <url>'."\r\n";
                $CoDoZapisania .= '    <loc>'.Seo::link_SEO($info["categories_name"], $info["categories_id"],'kategoria_aktualnosci','',false).'</loc>'."\r\n";     
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);             
        }        
        
        // RECENZJE
        if (isset($_POST['recenzje']) && (int)$_POST['recenzje'] == 1) {
            //
            $priorytet = $_POST['priorytet_recenzje'];
            $zapytanie = "select distinct r.reviews_id,
                                          pd.products_name,
                                          pd.products_seo_url,
                                          r.date_added
                                     from reviews r, 
                                          products_description pd
                                    where r.approved = '1' and
                                          r.products_id = pd.products_id and
                                          pd.language_id = '" . (int)$_POST['jezyk'] . "' 
                                 order by r.reviews_id";

            $sql = $db->open_query($zapytanie);
            //
            while ($info = $sql->fetch_assoc()) {
                //
                $data_modyfikacji = '';
                if (Funkcje::czyNiePuste($info['date_added'])) {
                    $data_modyfikacji = date('Y-m-d',strtotime($info['date_added']));
                }          
                //   
                $CoDoZapisania .= '  <url>'."\r\n";
                
                if (!empty($info["products_seo_url"])) {
                    $link = Seo::link_SEO($info["products_seo_url"], $info["reviews_id"],'recenzja','',false);
                  } else {
                    $link = Seo::link_SEO($info["products_name"], $info["reviews_id"],'recenzja','',false);
                }

                $CoDoZapisania .= '    <loc>'.$link.'</loc>'."\r\n";
                
                unset($link);

                if (!empty($data_modyfikacji)) {
                    $CoDoZapisania .= '    <lastmod>'.$data_modyfikacji.'</lastmod>'."\r\n";
                }                
                $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
                $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
                $CoDoZapisania .= '  </url>'."\r\n";
            }
            $db->close_query($sql);
            unset($priorytet, $zapytanie, $info);            
        }      
        
    }
    
    $zapytanie = "select distinct p.products_id, 
                                  p.products_ordered, 
                                  p.products_date_added, 
                                  p.products_image, 
                                  pd.products_name,
                                  pd.products_seo_url
                             from products p
                        left join products_to_categories ptc on ptc.products_id = p.products_id
                       right join products_description pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$_POST['jezyk'] . "'
                       right join categories c ON c.categories_id = ptc.categories_id AND c.categories_status = '1'                             
                            where p.products_status = '1' and (p.customers_group_id = '0' or p.customers_group_id = '')
                            order by p.products_id limit ".(int)$_POST['limit'].",100";
                            
    $sql = $db->open_query($zapytanie);
    
    $top = 0;
    while ($info = $sql->fetch_assoc()) {
        //
        if (isset($_POST['automat']) && (int)$_POST['automat'] == 1) {
            //
            $top = max($top, $info['products_ordered']);
            $ratio = $top > 0 ? $info['products_ordered']/$top : 0;
            $priorytet = $ratio < .1 ? .1 : number_format($ratio, 1, '.', '');
            //
           } else {
            //
            $priorytet = $_POST['priorytet_produkty'];
            //
        }
        //
        $data_modyfikacji = '';
        if (Funkcje::czyNiePuste($info['products_date_added'])) {
            $data_modyfikacji = date('Y-m-d',strtotime($info['products_date_added']));
        }
        
        $CoDoZapisania .= '  <url>'."\r\n";
        
        if (!empty($info["products_seo_url"])) {
            $link = Seo::link_SEO($info["products_seo_url"], $info["products_id"],'produkt','',false);
          } else {
            $link = Seo::link_SEO($info["products_name"], $info["products_id"],'produkt','',false);
        }
        
        $CoDoZapisania .= '    <loc>'.$link.'</loc>'."\r\n";
        
        unset($link);
        
        // obrazki do produktow
        if (isset($_POST['obrazki']) && (int)$_POST['obrazki'] == 1 && !empty($info["products_image"])) {
            $CoDoZapisania .= '    <image:image>'."\r\n";
            $CoDoZapisania .= '       <image:loc>'.ADRES_URL_SKLEPU.'/' . KATALOG_ZDJEC . str_replace('//','/', '/'.$info["products_image"]).'</image:loc>'."\r\n";
            $CoDoZapisania .= '    </image:image>'."\r\n";    
        }
        
        if (!empty($data_modyfikacji)) {
            $CoDoZapisania .= '    <lastmod>'.$data_modyfikacji.'</lastmod>'."\r\n";
        }
        $CoDoZapisania .= '    <priority>'.$priorytet.'</priority>'."\r\n";
        $CoDoZapisania .= '    <changefreq>'.$_POST['index'].'</changefreq>'."\r\n";
        $CoDoZapisania .= '  </url>'."\r\n";
        //
        $_POST['limit'] = (int)$_POST['limit'] + 1;
        //
    }
    
    $db->close_query($sql);
    unset($info, $zapytanie);  

    if (isset($_POST['limit_max']) && (int)$_POST['limit_max'] < ((int)$_POST['limit'])) {
        
        if ( strpos($WczytanyPlik, '</urlset>') === false ) {
             $CoDoZapisania .= '</urlset>' . "\r\n";
        }
        
    }    

    fwrite($fp, $CoDoZapisania);
    
    // zapisanie danych do pliku
    flock($fp, 3);
    // zamkniecie pliku
    fclose($fp);  

    unset($CoDoZapisania, $WczytanyPlik);    

}

?>