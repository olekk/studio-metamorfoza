<?php
// kategoria i podkategorie

$BylyKategorie = false;

$parent = 0;
for ($w = 1; $w < 11; $w++) {
    if (isset($TablicaDane['Kategoria_' . $w . '_nazwa']) && trim($TablicaDane['Kategoria_' . $w. '_nazwa']) != '') {

        $zapytanieKategorie = "select c.categories_id, cd.categories_name from categories c, categories_description cd where cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."' and c.categories_id = cd.categories_id and categories_name = '" . addslashes($filtr->process($TablicaDane['Kategoria_' . $w . '_nazwa'])) . "' and parent_id = '" . $parent . "'";
        $sql = $db->open_query($zapytanieKategorie);
        
        // jezeli jest tylko aktualizacja kategorii z pliku csv - sa kategorie a nie ma nr katalogowego
        if ($CzyDodawanie == false && !isset($TablicaDane['Nr_katalogowy'])) {
        
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();
            
                // aktualizacja zdjecia jezeli jest
                $pola = array();
                if (isset($TablicaDane['Kategoria_' . $w. '_zdjecie']) && trim($TablicaDane['Kategoria_' . $w. '_zdjecie']) != '') {
                    $pola[] = array('categories_image',$filtr->process($TablicaDane['Kategoria_' . $w. '_zdjecie']));
                }                     
                
                if (count($pola) > 0) {
                    $db->update_query('categories' , $pola, "categories_id = '" . $info['categories_id'] . "'");                
                }                
                unset($pola);            
            
                $pola = array();

                // jezeli jest opis
                if (isset($TablicaDane['Kategoria_' . $w. '_opis']) && trim($TablicaDane['Kategoria_' . $w. '_opis']) != '') {
                    $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis']));
                }             
                // jezeli jest meta tytul
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul']) && trim($TablicaDane['Kategoria_' . $w. '_meta_tytul']) != '') {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul']));
                  } else {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta opis
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis']) && trim($TablicaDane['Kategoria_' . $w. '_meta_opis']) != '') {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis']));
                  } else {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta slowa kluczowe
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa']) && trim($TablicaDane['Kategoria_' . $w. '_meta_slowa']) != '') {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa']));
                  } else {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                } 

                if (count($pola) > 0) {
                    $db->update_query('categories_description' , $pola, "categories_id = '" . $info['categories_id'] . "' and language_id = '".$_SESSION['domyslny_jezyk']['id']."'");                
                }
                
                // ---------------------------------------------------------------
                // dodawanie do innych jezykow jak sa inne jezyki
                for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                    //
                    $kod_jezyka = $ile_jezykow[$j]['kod'];
                    //
                    if (isset($TablicaDane['Kategoria_' . $w . '_nazwa_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]) != '') {
                    
                        $pola = array();
                        
                        // jezeli jest opis
                        if (isset($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]));
                        }             
                        // jezeli jest meta tytul
                        if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]));
                          } else {
                            $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]));
                        }
                        // jezeli jest meta opis
                        if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]));
                          } else {
                            $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]));
                        }
                        // jezeli jest meta slowa kluczowe
                        if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) != '') {
                            $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]));
                          } else {
                            $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]));
                        }                            
                                
                        if (count($pola) > 0) {
                            $db->update_query('categories_description' , $pola, "categories_id = '" . $info['categories_id'] . "' and language_id = '".$ile_jezykow[$j]['id']."'");                
                        }      

                        unset($pola);             
                    }    
                    //
                    unset($kod_jezyka);
                    //
                }                
                
                $parent = $info['categories_id'];
                unset($info);                
        
            }
        
        } else {

            if ((int)$db->ile_rekordow($sql) == 0) {
                //
                $pola = array(
                        array('parent_id',$parent),
                        array('sort_order','1'),
                        array('categories_status','1'));
                        
                // jezeli jest zdjecie
                if (isset($TablicaDane['Kategoria_' . $w. '_zdjecie']) && trim($TablicaDane['Kategoria_' . $w. '_zdjecie']) != '') {
                    $pola[] = array('categories_image',$filtr->process($TablicaDane['Kategoria_' . $w. '_zdjecie']));
                }                     
                
                $sql = $db->insert_query('categories' , $pola);
                $id_dodanej_pozycji = $db->last_id_query();
                unset($pola);
                //
                $pola = array(
                        array('categories_id',$id_dodanej_pozycji),
                        array('language_id',$_SESSION['domyslny_jezyk']['id']),
                        array('categories_name',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa'])));  

                // jezeli jest opis
                if (isset($TablicaDane['Kategoria_' . $w. '_opis']) && trim($TablicaDane['Kategoria_' . $w. '_opis']) != '') {
                    $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis']));
                }             
                // jezeli jest meta tytul
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul']) && trim($TablicaDane['Kategoria_' . $w. '_meta_tytul']) != '') {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul']));
                  } else {
                    $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta opis
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis']) && trim($TablicaDane['Kategoria_' . $w. '_meta_opis']) != '') {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis']));
                  } else {
                    $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }
                // jezeli jest meta slowa kluczowe
                if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa']) && trim($TablicaDane['Kategoria_' . $w. '_meta_slowa']) != '') {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa']));
                  } else {
                    $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']));
                }             
                        
                $sql = $db->insert_query('categories_description' , $pola);
                unset($pola);    
                //
                $parent = $id_dodanej_pozycji;
                
                // ---------------------------------------------------------------
                // dodawanie do innych jezykow jak sa inne jezyki
                for ($j = 0, $cnt = count($ile_jezykow); $j < $cnt; $j++) {
                    //
                    $kod_jezyka = $ile_jezykow[$j]['kod'];
                    //
                    $NazwaTmp = $filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa']);
                    if (isset($TablicaDane['Kategoria_' . $w . '_nazwa_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]) != '') {
                        $NazwaTmp = $filtr->process($TablicaDane['Kategoria_' . $w. '_nazwa_' . $kod_jezyka]);
                    }
                    //
                    $pola = array(
                            array('categories_id',$id_dodanej_pozycji),
                            array('language_id',$ile_jezykow[$j]['id']),
                            array('categories_name',$NazwaTmp));    

                    // jezeli jest opis
                    if (isset($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_description',$filtr->process($TablicaDane['Kategoria_' . $w. '_opis_' . $kod_jezyka]));
                    }             
                    // jezeli jest meta tytul
                    if (isset($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_meta_title_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_tytul_' . $kod_jezyka]));
                      } else {
                        $pola[] = array('categories_meta_title_tag',$NazwaTmp);
                    }
                    // jezeli jest meta opis
                    if (isset($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_meta_desc_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_opis_' . $kod_jezyka]));
                      } else {
                        $pola[] = array('categories_meta_desc_tag',$NazwaTmp);
                    }
                    // jezeli jest meta slowa kluczowe
                    if (isset($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) && trim($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]) != '') {
                        $pola[] = array('categories_meta_keywords_tag',$filtr->process($TablicaDane['Kategoria_' . $w. '_meta_slowa_' . $kod_jezyka]));
                      } else {
                        $pola[] = array('categories_meta_keywords_tag',$NazwaTmp);
                    }                            
                            
                    if ($ile_jezykow[$j]['id'] != $_SESSION['domyslny_jezyk']['id']) {
                        $sql = $db->insert_query('categories_description' , $pola);
                    }
                    unset($pola);              
                    //
                    unset($kod_jezyka, $NazwaTmp);
                    //
                }
                
            } else {
            
                // jezeli znaleziono taka kategorie
                $info = $sql->fetch_assoc();
                $parent = $info['categories_id'];
                $db->close_query($sql);
                unset($info);             
            
            }
            
        }
        
        $BylyKategorie = true;
        
    }

}
unset($id_dodanej_pozycji);
?>