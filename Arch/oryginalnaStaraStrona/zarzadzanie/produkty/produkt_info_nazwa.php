<?php
// dodatkowa zmienna do wylaczania mozliwosci zmiany statusu produktu jezeli kategoria
// do ktorej nalezy jest wylaczona
$wylacz_status = true;

// nazwa produktu i kategorie do jakich jest przypisany
$do_jakich_kategorii_przypisany = '<span class="male_kat">Kategoria: ';
$kategorie = $db->open_query("select distinct categories_id from products_to_categories where products_id = '".(int)$info['products_id']."'");
//
if ( (int)$db->ile_rekordow($kategorie) > 0 ) {
    while ($id_kategorii = $kategorie->fetch_assoc()) {
        // okreslenie nazwy kategorii
        if ((int)$id_kategorii['categories_id'] == '0') {
            $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
            $wylacz_status = false;
          } else {
            //
            if ( isset($TablicaKategorii[(int)$id_kategorii['categories_id']]) ) {
                //
                $do_jakich_kategorii_przypisany .= '<span style="color:#ff0000">'.$TablicaKategorii[(int)$id_kategorii['categories_id']]['text'].'</span>, ';
                //
                if ($TablicaKategorii[(int)$id_kategorii['categories_id']]['status'] == '1') {
                   $wylacz_status = false;
                }
                //
            }
            //
        }
    }
  } else {
    $do_jakich_kategorii_przypisany .= 'Bez kategorii, ';
    $wylacz_status = false;
}
$do_jakich_kategorii_przypisany = substr($do_jakich_kategorii_przypisany,0,-2);
$do_jakich_kategorii_przypisany .= '</span>';

$db->close_query($kategorie);
unset($kategorie);

$nr_kat = '';
if (trim($info['products_model']) != '') {
    $nr_kat = '<span class="male_nr_kat">Nr kat: <b>'.$info['products_model'].'</b></span>';
}

$kod_producenta = '';
if (trim($info['products_man_code']) != '') {
    $kod_producenta = '<span class="male_nr_kat">Kod prod: <b>'.$info['products_man_code'].'</b></span>';
}

// pobieranie danych o producencie
$prd = '';
if (trim($info['manufacturers_name']) != '') {                     
    //
    $prd = '<span class="male_producent">Producent: <b>'.$info['manufacturers_name'].'</b></span>';
    //
}                  

// informacja o aukcji
$allegro = '';
if (trim($info['auction_id']) != '') {                     
    //
    $allegro = '<div class="info_allegro" id="allegro_' . $info['products_id'] . '"><div></div><img src="obrazki/logo/logo_allegro_male.png" alt="Produkt na Allegro" /></div>';
    //
}  

$szybki_link = '<span class="edpr" onclick="edpr('.$info['products_id'].')"></span>';

$tgm = '<div class="edycja_prd" id="edpr_'.$info['products_id'].'">' . $szybki_link . '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $prd . $allegro . '</div>';

$tgm_ajax = $szybki_link . '<b>'.$info['products_name'].'</b>' . $do_jakich_kategorii_przypisany . $nr_kat . $kod_producenta . $prd . $allegro;
?>