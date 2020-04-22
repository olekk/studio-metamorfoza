<?php

if ( isset($pobierzFunkcje) ) {

    $zapRecenzja = "SELECT r.products_id, 
                      r.reviews_id,
                      r.customers_id,
                      r.customers_name,
                      r.reviews_rating,
                      r.date_added,
                      rd.reviews_text,
                      p.products_image, 
                      p.products_image_description,
                      p.products_model, 
                      pd.products_name, 
                      pd.products_seo_url
                    FROM reviews r
                    INNER JOIN reviews_description rd ON rd.reviews_id = r.reviews_id AND r.approved = '1' AND rd.languages_id = '" . $this->jezykDomyslnyId . "'
                    LEFT JOIN products p ON r.products_id = p.products_id
                    LEFT JOIN products_description pd ON pd.products_id = r.products_id AND pd.language_id = '" . $this->jezykDomyslnyId . "'
                    WHERE r.products_id = '" . $this->id_produktu . "' ORDER BY r.date_added DESC";

    $sqlRecenzje = $GLOBALS['db']->open_query($zapRecenzja);
    
    $SumaGlosow = 0;
    $IloscGlosow = 0;
    
    while ($infoRecenzja = $sqlRecenzje->fetch_assoc()) {    

        // ustala jaka ma byc tresc linku
        $linkSeo = ((trim($infoRecenzja['products_seo_url']) != '') ? $infoRecenzja['products_seo_url'] : strip_tags($infoRecenzja['products_name']));
        // ustala jaka ma alt zdjecia
        $altFoto = htmlspecialchars(((!empty($infoRecenzja['products_image_description'])) ? $infoRecenzja['products_image_description'] : strip_tags($infoRecenzja['products_name'])));
        
        // czy jest wypelnione pola obrazka glownego
        if (empty($infoRecenzja['products_image']) && POKAZ_DOMYSLNY_OBRAZEK == 'tak') {            
           $infoRecenzja['products_image'] = 'domyslny.gif';
        }
        //
        $linkIdFoto = 'id="fot_' . $this->idUnikat . $this->id_produktu . '" ';
        //
        $this->recenzje[$infoRecenzja['reviews_id']] = array(
              'recenzja_id'                 => $infoRecenzja['reviews_id'],
              'recenzja_link'               => '<a href="' . Seo::link_SEO( $linkSeo, $infoRecenzja['reviews_id'], 'recenzja' ) . '">' . $infoRecenzja['products_name'] . '</a>',
              'recenzja_zdjecie_link'       => ((!empty($infoRecenzja['products_image'])) ? '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $infoRecenzja['reviews_id'], 'recenzja' ) . '">' . Funkcje::pokazObrazek($infoRecenzja['products_image'], $altFoto, $this->szerImg, $this->wysImg, array(), $linkIdFoto . 'class="Zdjecie"', 'maly') . '</a>' : ''),
              'recenzja_zdjecie_link_ikony' => ((!empty($infoRecenzja['products_image'])) ? '<a class="Zoom" href="' . Seo::link_SEO( $linkSeo, $infoRecenzja['reviews_id'], 'recenzja' ) . '">' . Funkcje::pokazObrazek($infoRecenzja['products_image'], $altFoto, $this->szerImg, $this->wysImg, $this->ikonki, $linkIdFoto . 'class="Zdjecie"', 'maly') . '</a>' : ''),
              'recenzja_tekst'              => strip_tags($infoRecenzja['reviews_text']),
              'recenzja_tekst_krotki'       => Funkcje::przytnijTekst(strip_tags($infoRecenzja['reviews_text']), 200),
              'recenzja_data_dodania'       =>  date('d-m-Y',strtotime($infoRecenzja['date_added'])),
              'recenzja_ocena'              => $infoRecenzja['reviews_rating'],
              'recenzja_ocena_obrazek'      => '<img src="szablony/'.DOMYSLNY_SZABLON.'/obrazki/recenzje/ocena_' . $infoRecenzja['reviews_rating'] . '.png" alt="' . $GLOBALS['tlumacz']['OCENA_PRODUKTU'] . ' ' . $infoRecenzja['reviews_rating'] . '/5" />',
              'recenzja_oceniajacy'         => $infoRecenzja['customers_name']);
                                  
        unset($linkSeo, $altFoto, $linkIdFoto); 

        $SumaGlosow = $SumaGlosow + $infoRecenzja['reviews_rating'];
        $IloscGlosow++;

    }
    
    $SredniaOcena = 0;
    if ($SumaGlosow > 0) {
        $SredniaOcena = round($SumaGlosow / $IloscGlosow,2);
    }
    
    $this->recenzjeSrednia = array('srednia_ocena'         => $SredniaOcena,
                                   'ilosc_glosow'          => $IloscGlosow,
                                   'srednia_ocena_obrazek' => '<img src="szablony/'.DOMYSLNY_SZABLON.'/obrazki/recenzje/ocena_' . round($SredniaOcena) . '.png" alt="' . $GLOBALS['tlumacz']['SREDNIA_OCENA_PRODUKTU'] . ' ' . $SredniaOcena . '/5" />'); 
    
    $GLOBALS['db']->close_query($sqlRecenzje); 
    unset($sredniaOcena, $SumaGlosow, $IloscGlosow, $zapRecenzja, $infoRecenzja);
        
}
       
?>