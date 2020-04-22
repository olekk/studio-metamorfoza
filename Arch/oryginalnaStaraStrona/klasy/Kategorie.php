<?php

class Kategorie {

    public static function TablicaKategorieGlobal() {
        
        $TablicaKategorii = array();
        
        // cache zapytania
        $WynikCache = $GLOBALS['cache']->odczytaj('TablicaKategorii_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_KATEGORIE); 

        if ( !$WynikCache ) {        
        
            // pobiera dane z bazy
            $sql = $GLOBALS['db']->open_query("SELECT c.categories_image as Foto, 
                                                      c.categories_icon as Ikona, 
                                                      cd.categories_name as Nazwa,
                                                      c.categories_id as IdKat, 
                                                      c.categories_view as Widocznosc,
                                                      c.parent_id as Parent, 
                                                      c.categories_color as Kolor, 
                                                      c.categories_color_status as KolorStatus, 
                                                      c.categories_background_color as KolorTla, 
                                                      c.categories_background_color_status as KolorTlaStatus,                                                       
                                                      0 as IloscProduktow, 
                                                      0 as WszystkichProduktow
                                                 FROM categories c
                                            LEFT JOIN categories_description cd ON cd.categories_id = c.categories_id AND cd.language_id = '" . $_SESSION['domyslnyJezyk']['id'] . "'
                                                WHERE c.categories_status = '1'
                                             ORDER BY c.sort_order, cd.categories_name");

            while ($info = $sql->fetch_assoc()) {
                $TablicaKategorii[$info['IdKat']] = $info;
            }
            
            $GLOBALS['cache']->zapisz('TablicaKategorii_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaKategorii, CACHE_KATEGORIE);
            
        } else {
        
            $TablicaKategorii = $WynikCache;
        
        }

        if ( !$WynikCache ) {  
            $GLOBALS['db']->close_query($sql);
            unset($zapytanie, $info);
        }
        
        unset($WynikCache);      
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
        
            // cache zapytania
            $WynikCache = $GLOBALS['cache']->odczytaj('TablicaKategorii_Ilosc_' . $_SESSION['domyslnyJezyk']['kod'], CACHE_KATEGORIE, true);  

            if ( !$WynikCache ) { 

                $sql = $GLOBALS['db']->open_query("SELECT count(*) as IloscProd,
                                                          p2c.categories_id as Kategoria
                                                     FROM products p, products_to_categories p2c, categories c
                                                    WHERE p.products_id = p2c.products_id AND c.categories_id = p2c.categories_id AND c.categories_status = '1' AND p.products_status = '1'" . $GLOBALS['warunekProduktu'] . " 
                                                 GROUP BY kategoria");
                    
                while ($info = $sql->fetch_assoc()) {
                    $TablicaKategorii[$info['Kategoria']]['IloscProduktow'] = $info['IloscProd'];
                }
                
                $GLOBALS['cache']->zapisz('TablicaKategorii_Ilosc_' . $_SESSION['domyslnyJezyk']['kod'], $TablicaKategorii, CACHE_KATEGORIE);
            
            } else {
        
                $TablicaKategorii = $WynikCache;
        
            }

            if ( !$WynikCache ) {  
                $GLOBALS['db']->close_query($sql);
                unset($zapytanie, $info);
            }
            
            unset($WynikCache);       

            foreach ($TablicaKategorii as $Pozycja) {
                $IdKat = $Pozycja['IdKat'];
                $Liczba = $Pozycja['IloscProduktow'];
                do {
                    if (!isset($TablicaKategorii[$IdKat])) {
                        $TablicaKategorii[$IdKat] = array();
                    }
                    
                    if (isset($TablicaKategorii[$IdKat]['WszystkichProduktow'])) {
                        $TablicaKategorii[$IdKat]['WszystkichProduktow'] += $Liczba;
                    }
                    
                    if (isset($TablicaKategorii[$IdKat]['Parent'])) {
                        $IdKat = $TablicaKategorii[$IdKat]['Parent'];
                      } else {
                        $IdKat = 0;
                    }
                } while ($IdKat > 0);
                
                unset($IdKat, $Liczba);
            }
        }
        
        return $TablicaKategorii;

    }
    
    // zwraca nazwe kategorii po id kategorii
    public static function NazwaKategoriiId ( $id ) {
        //
        if ( isset($GLOBALS['tablicaKategorii'][$id]) ) {
            return $GLOBALS['tablicaKategorii'][$id]['Nazwa'];
          } else {
            return '';
        }
        //
    }
    
    // zwraca tablice tylko z danymi o okreslonym parent - uzywane do wyszukiwania zaawansowanego
    public static function TablicaKategorieParent( $Parent = '0', $brak = '' ) {
        //
        $TablicaWynik = array();
        //
        if ($brak != '') {
            $TablicaWynik[] = array('id' => 0, 'text' => $brak);
        }        
        //
        foreach ($GLOBALS['tablicaKategorii'] as $IdKat => $TablicaWartosci) {
            //
            if ($TablicaWartosci['Parent'] == $Parent) {
                //
                // tylko widoczne kategorie
                if ( $TablicaWartosci['Widocznosc'] == '1' ) {
                     $TablicaWynik[] = array('id' => $IdKat, 'text' => $TablicaWartosci['Nazwa']);
                }
                //
            }
            //
        }
        //
        return $TablicaWynik;
        //
    }
    
    // tworzy tablice z drzewem kategorii - poszczegolne podkategorie sa jako podtablice
    public static function DrzewoKategorii( $Parent = '0', $Widocznosc = false ) {

        $ListaPodkategorii = array();
        $ListaKategorii = array();
        
        foreach($GLOBALS['tablicaKategorii'] as $Tmp) {
        
            if (isset($Tmp['IdKat'])) {
            
                $Pozycja = &$ListaPodkategorii[ $Tmp['IdKat'] ];
                $Pozycja['IdKat'] = $Tmp['IdKat'];
                $Pozycja['Parent'] = $Tmp['Parent'];
                $Pozycja['Widocznosc'] = $Tmp['Widocznosc'];
                $Pozycja['Nazwa'] = $Tmp['Nazwa'];
                $Pozycja['Foto'] = $Tmp['Foto'];
                $Pozycja['Ikona'] = $Tmp['Ikona'];
                $Pozycja['Kolor'] = $Tmp['Kolor'];
                $Pozycja['KolorStatus'] = (($Tmp['KolorStatus'] == 1) ? 'tak' : 'nie');
                $Pozycja['KolorTla'] = $Tmp['KolorTla'];
                $Pozycja['KolorTlaStatus'] = (($Tmp['KolorTlaStatus'] == 1) ? 'tak' : 'nie');                
                $Pozycja['IloscProduktow'] = $Tmp['IloscProduktow'];
                $Pozycja['WszystkichProduktow'] = $Tmp['WszystkichProduktow'];
                
                if ( ( $Tmp['Widocznosc'] == '1' && $Widocznosc == false ) || $Widocznosc == true ) {
                
                    if ($Tmp['Parent'] == $Parent) {
                        //
                        $ListaKategorii[ $Tmp['IdKat'] ] = &$Pozycja;
                        //
                    } else {
                        //
                        $ListaPodkategorii[ $Tmp['Parent'] ]['Podkategorie'][ $Tmp['IdKat'] ] = &$Pozycja;
                        //
                    }
                
                }
                
            }
            
        }
        
        return $ListaKategorii;

    }    
    
    // wyswietla kategorie w formie ul i li
    //
    // opis opcji //
    // TylkoRozwin = id kategorii dla jakiej ma byc rozwijane drzewo
    // GlebokoscDrzewa - ile podkategorii ma wyswietlac
    // Przyklej - alternatywny tekst na poczatku cpath - np jezeli ma wyswietlac tylko drzewo podkategorii
    // Separator - jaki ciag ma byc separatorem kategorii
    // KlasaCss - klasa dla aktywnej kategorii
    //
    // np wywolanie samych podkategorii dla okreslonej id kategorii
    // <ul>
    // foreach(Kategorie::DrzewoKategorii('14') as $IdKategorii => $Tablica) {
    //    echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica, '',10,'14_');
    // }    
    // </ul>
    //
    // rozwiniecie drzewa tylko dla podkategori id 2
    // foreach(Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica) {
    //    echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica, '2');
    // }
    // 
    // kompletne drzewo kategorii
    // foreach(Kategorie::DrzewoKategorii() as $IdKategorii => $Tablica) {
    //    echo Kategorie::WyswietlKategorie($IdKategorii, $Tablica);
    // }
    //
    public static function WyswietlKategorie($IdKat, $Tablica, $TylkoRozwin = array(), $GlebokoscDrzewa = 10, $Przyklej = '', $KlasaCss = 'Aktywna', $Separator = '_', $ParentGlowny = '', $CiagDoWyswietlania = '', $IdAktywne = array(), $PokazIkone = 'nie') {
    
        $cPath = $ParentGlowny . $IdKat;
        $PodzielCPath = explode($Separator, $cPath);    
        
        // klasa css aktywnej kategorii
        $css = '';
        $cssTla = '';
        if ( !empty($TylkoRozwin) || !empty($IdAktywne) ) {
            //
            if (in_array($IdKat, $TylkoRozwin) || in_array($IdKat, $IdAktywne)) {
                $css = ' class="'.$KlasaCss.'"';
            }
            //
        }
        
        // kolorowanie kategorii
        if ( $Tablica['KolorStatus'] == 'tak' && trim($Tablica['Kolor']) != '' && strlen($Tablica['Kolor']) == 6 ) {
             $css .= ' style="color:#' . $Tablica['Kolor'] . '"';
        }  

        // kolorowanie tla kategorii
        if ( $Tablica['KolorTlaStatus'] == 'tak' && trim($Tablica['KolorTla']) != '' && strlen($Tablica['KolorTla']) == 6 ) {
             $cssTla = ' style="background:#' . $Tablica['KolorTla'] . '"';
        }        
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em' . $css . '>('.$Tablica['WszystkichProduktow'] . ')</em>';
        }
        
        // ikona produktu
        $Ikona = '';
        $Nazwa = $Tablica['Nazwa'] . $SumaProduktow;
        
        if ( $PokazIkone == 'tak' ) {
             //
             if ( $Tablica['Ikona'] != '' ) {
                  //
                  $Ikona = '<span><img src="/' . KATALOG_ZDJEC . '/' . $Tablica['Ikona'] . '" alt="' . $Tablica['Nazwa'] . '" /></span>';
                  $Nazwa = '<span>' . $Nazwa . '</span>';
                  //
             }
             //
        }

        $CiagDoWyswietlania .= '<li><h2' . $cssTla . '><a' . $css . ' href="' . Seo::link_SEO($Tablica['Nazwa'], $Przyklej . $ParentGlowny . $IdKat, 'kategoria') . '">' . $Ikona . $Nazwa . '</a></h2>';
        
        unset($Ikona, $Nazwa);

        if (Funkcje::SzukajwTablicy($PodzielCPath, $TylkoRozwin) || empty($TylkoRozwin)) {
        
            if (count($PodzielCPath) <= $GlebokoscDrzewa) {

                if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
                    $CiagDoWyswietlania .= '<ul>';
                    foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                        $CiagDoWyswietlania .= Kategorie::WyswietlKategorie($PodkatId, $Podkat, $TylkoRozwin, $GlebokoscDrzewa, $Przyklej, $KlasaCss, $Separator, $cPath . $Separator, '', $IdAktywne, $PokazIkone);
                    }
                    $CiagDoWyswietlania .= '</ul>';
                }
            
            }
            
        }
        
        unset($cPath, $PodzielCPath, $css, $cssTla, $SumaProduktow);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }    
    
    // funkcja j.w. - uproszczona do cennikow produktow
    public static function WyswietlKategorieCennik($Tablica, $typ = 'pdf', $CiagDoWyswietlania = '') {
    
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em>('.$Tablica['WszystkichProduktow'] . ')</em>';
        }

        $CiagDoWyswietlania .= '<li>' . $Tablica['Nazwa'] . $SumaProduktow . '<a href="pobierz-cennik.html/typ=' . $typ . '/id=' . $Tablica['IdKat'] . '"><img src="szablony/' . DOMYSLNY_SZABLON . '/obrazki/cennik/pobierz.png" alt="' . $GLOBALS['tlumacz']['POBIERZ_CENNIK'] . '" title="' . $GLOBALS['tlumacz']['POBIERZ_CENNIK'] . '" /></a>';

        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $CiagDoWyswietlania .= '<ul>';
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                $CiagDoWyswietlania .= Kategorie::WyswietlKategorieCennik($Podkat, $typ);
            }
            $CiagDoWyswietlania .= '</ul>';
        }

        unset($SumaProduktow);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }        
    
    // funkcja j.w. do wyswietlania kategorii rozwijanych
    public static function WyswietlKategorieAnimacja($IdKat, $Tablica, $TylkoRozwin = '', $KlasaCss = 'Aktywna', $Separator = '_', $ParentGlowny = '', $CiagDoWyswietlania = '', $PokazIkone = 'nie') {
    
        $cPath = $ParentGlowny . $IdKat;
        $PodzielCPath = explode($Separator, $cPath);    
        
        // klasa css aktywnej kategorii
        $css = '';
        $cssTla = '';
        if (in_array($IdKat, $TylkoRozwin)) {
            $css = ' class="'.$KlasaCss.'"';
        }
        
        // kolorowanie kategorii
        if ( $Tablica['KolorStatus'] == 'tak' && trim($Tablica['Kolor']) != '' && strlen($Tablica['Kolor']) == 6 ) {
             $css .= ' style="color:#' . $Tablica['Kolor'] . '"';
        }      

        // kolorowanie tla kategorii
        if ( $Tablica['KolorTlaStatus'] == 'tak' && trim($Tablica['KolorTla']) != '' && strlen($Tablica['KolorTla']) == 6 ) {
             $cssTla = ' style="background:#' . $Tablica['KolorTla'] . '"';
        }          
        
        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em' . $css . '>('.$Tablica['WszystkichProduktow'] . ')</em>';
        }
        
        $Rozwin = '';
        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $Rozwin = '<span id="s' . $cPath . '" class="Rozwin Plus"></span>';
        }
        
        // ikona produktu
        $Ikona = '';
        $Nazwa = $Tablica['Nazwa'] . $SumaProduktow;
        
        if ( $PokazIkone == 'tak' ) {
             //
             if ( $Tablica['Ikona'] != '' ) {
                  //
                  $Ikona = '<span><img src="/' . KATALOG_ZDJEC . '/' . $Tablica['Ikona'] . '" alt="' . $Tablica['Nazwa'] . '" /></span>';
                  $Nazwa = '<span>' . $Nazwa . '</span>';
                  //
             }
             //
        }        

        $CiagDoWyswietlania .= '<li><h2' . $cssTla . '>'.$Rozwin.'<a'.$css.' href="' . Seo::link_SEO($Tablica['Nazwa'], $ParentGlowny . $IdKat, 'kategoria') . '">' . $Ikona . $Nazwa . '</a></h2>';
        
        unset($Ikona, $Nazwa);

        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $CiagDoWyswietlania .= '<ul id="rs' . $cPath . '">';
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                $CiagDoWyswietlania .= Kategorie::WyswietlKategorieAnimacja($PodkatId, $Podkat, $TylkoRozwin, $KlasaCss, $Separator, $cPath . $Separator, '', $PokazIkone);
            }
            $CiagDoWyswietlania .= '</ul>';
        }

        unset($cPath, $css, $cssTla, $SumaProduktow, $Rozwin);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }      
    
    // funkcja j.w. do wyswietlania kategorii wysuwanych
    public static function WyswietlKategorieWysuwane($IdKat, $Tablica, $TylkoRozwin = '', $KlasaCss = 'Aktywna', $Separator = '_', $ParentGlowny = '', $CiagDoWyswietlania = '', $PokazIkone = 'nie') {
    
        $cPath = $ParentGlowny . $IdKat;
        $PodzielCPath = explode($Separator, $cPath);    
        
        $Pokaz = '';
        $cssA = '';
        $cssTla = '';
        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $Pokaz = ' id="w' . $cPath . '" class="Pokaz"';
            $cssA = 'class="Rozwin"';
        }
        
        // kolorowanie kategorii
        if ( $Tablica['KolorStatus'] == 'tak' && trim($Tablica['Kolor']) != '' && strlen($Tablica['Kolor']) == 6 ) {
             $cssA .= ' style="color:#' . $Tablica['Kolor'] . '"';
        }        
        
        // kolorowanie tla kategorii
        if ( $Tablica['KolorTlaStatus'] == 'tak' && trim($Tablica['KolorTla']) != '' && strlen($Tablica['KolorTla']) == 6 ) {
             $cssTla = ' style="background:#' . $Tablica['KolorTla'] . '"';
        }  

        // jezeli jest wlaczona opcja pokazywania ilosci produktow z kategorii
        $SumaProduktow = '';
        if (LISTING_ILOSC_PRODUKTOW == 'tak') {
            $SumaProduktow = '<em ' . $cssA . '>('.$Tablica['WszystkichProduktow'] . ')</em>';
        }        
        
        // ikona produktu
        $Ikona = '';
        $Nazwa = $Tablica['Nazwa'] . $SumaProduktow;
        
        if ( $PokazIkone == 'tak' ) {
             //
             if ( $Tablica['Ikona'] != '' ) {
                  //
                  $Ikona = '<span><img src="/' . KATALOG_ZDJEC . '/' . $Tablica['Ikona'] . '" alt="' . $Tablica['Nazwa'] . '" /></span>';
                  $Nazwa = '<span>' . $Nazwa . '</span>';
                  //
             }
             //
        }          

        $CiagDoWyswietlania .= '<li' . $Pokaz . '><h2' . $cssTla . '><a ' . $cssA . ' href="' . Seo::link_SEO($Tablica['Nazwa'], $ParentGlowny . $IdKat, 'kategoria') . '">' . $Ikona . $Nazwa . '</a></h2>';
        
        unset($Ikona, $Nazwa);

        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            $CiagDoWyswietlania .= '<ul id="rw' . $cPath . '">';
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                $CiagDoWyswietlania .= Kategorie::WyswietlKategorieWysuwane($PodkatId, $Podkat, $TylkoRozwin, $KlasaCss, $Separator, $cPath . $Separator, '', $PokazIkone);
            }
            $CiagDoWyswietlania .= '</ul>';
        }

        unset($cPath, $cssTla, $SumaProduktow, $Pokaz);
        
        $CiagDoWyswietlania .= "</li>\r\n";
        
        return $CiagDoWyswietlania;
    }      
    
    
    // czysci GET cPath
    public static function WyczyscPath($cPath, $coReturn = 'tablica') {
        //
        $Ciag = explode('_', $cPath);
        $Tablica = array();
        foreach ($Ciag as $Wynik) {
            $Tablica[] = (int)$Wynik;
        }    
        //
        $Tmp = array();
        for ($i=0, $n = sizeof($Tablica); $i < $n; $i++) {
          if (!in_array($Tablica[$i], $Tmp)) {
            $Tmp[] = $Tablica[$i];
          }
        }
        if ($coReturn == 'tablica') {
            return $Tmp;
          } else {
            return implode('_', $Tmp);
        }
    } 
    
    // funkcja zwraca w formie tablicy id wszystkich podkategorii z danej kategorii
    public static function TablicaPodkategorie($Tablica, $IdKat = '') {
    
        $IdKat .= $Tablica['IdKat'] . ',';
    
        if(isset($Tablica['Podkategorie']) && is_array($Tablica['Podkategorie'])) {
            //
            foreach($Tablica['Podkategorie'] as $PodkatId => $Podkat) {
                //
                $IdKat .= Kategorie::TablicaPodkategorie($Podkat, '');
                //
            }
        }

        return $IdKat;
    }      
      
    // funkcja generujaca pelna sciezke cPath dla id kategorii
    public static function SciezkaKategoriiId($kat_id, $wynik = 'id', $separator = '_') {
        //
        $kategorie = array();
        Kategorie::NadrzednaKategoria($kategorie, $kat_id);
        //
        $ciag = '';
        //
        // jezeli ma zwrocic ciag tekstowy
        if ($wynik == 'nazwy') {
            for ($v = count($kategorie) - 1; $v > -1; $v--) {
                $ciag .= $kategorie[$v]['Nazwa'];
                if ($v > 0) {
                    $ciag .= $separator;
                }
            }
        }
        //
        if ($wynik == 'id') {
            for ($v = count($kategorie) - 2; $v > -1; $v--) {
                $ciag .= $kategorie[$v]['Parent'];
                if ($v > 0) {
                    $ciag .= $separator;
                }
            }
            $ciag .= (($ciag != '') ? $separator : '') . $kat_id;
        }
        //
        return $ciag;
    }     

    // zwraca id nadrzednej kategorii - uzywane do funkcji powyzej
    static function NadrzednaKategoria(&$kategorie, $kategorie_id) {
        //
        $Tmp = array();
        foreach ($GLOBALS['tablicaKategorii'] AS $klucz => $Tablica) {
            if ($klucz == $kategorie_id) {
                $Tmp[] = array( 'Parent' => $Tablica['Parent'],
                                'Nazwa' => $Tablica['Nazwa'] );
                break;
            }
        }
        //
        if (count($Tmp) > 0) {
            $kategorie[count($kategorie)] = array( 'Parent' => $Tmp[0]['Parent'], 
                                                   'Nazwa' => $Tmp[0]['Nazwa'] );
            if ($Tmp[0] != $kategorie_id) {
                Kategorie::NadrzednaKategoria($kategorie, $Tmp[0]['Parent']);
            }
        }
        //
        unset($Tmp);
    }    
    
    
    // zwraca tablice id do jakich nalezy produkt
    static function ProduktKategorie($id = '0') {
        //
        $WynikCache = $GLOBALS['cache']->odczytaj('Produkt_Id_' . $id . '_kategorie', CACHE_PRODUKTY);  

        if ( !$WynikCache && !is_array($WynikCache) ) {        
            //
            $zapytanie = "select ptc.categories_id from products_to_categories ptc, categories c where ptc.categories_id = c.categories_id and c.categories_status = '1' and ptc.products_id = '" . $id . "'";

            $sql = $GLOBALS['db']->open_query($zapytanie);
            
            $kategorie = array();
            while ($info = $sql->fetch_assoc()) {
                //
                $kategorie[] = $info['categories_id'];
                //            
            }
            $GLOBALS['db']->close_query($sql); 

            unset($zapytanie, $info);
            
            $GLOBALS['cache']->zapisz('Produkt_Id_' . $id . '_kategorie', $kategorie, CACHE_PRODUKTY);  
            
          } else {
            
            $kategorie = $WynikCache;
            
        }
        
        return $kategorie;
        //
    }     
    
}

?>