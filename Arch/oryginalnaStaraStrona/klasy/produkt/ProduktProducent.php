<?php

if ( isset($pobierzFunkcje) ) {

    $this->producent = array('id'        => $this->info['id_producenta'],
                             'nazwa'     => $this->info['nazwa_producenta'],
                             'link'      => '<a href="' . Seo::link_SEO( $this->info['nazwa_producenta'], $this->info['id_producenta'], 'producent' ) . '">' . $this->info['nazwa_producenta'] . '</a>',
                             'foto'      => Funkcje::pokazObrazek($this->info['foto_producenta'], $this->info['nazwa_producenta'], $szerokoscImg, $wysokoscImg, array(), '', 'maly', true, false, false),
                             'foto_link' => '<a href="' . Seo::link_SEO( $this->info['nazwa_producenta'], $this->info['id_producenta'], 'producent' ) . '">' . Funkcje::pokazObrazek($this->info['foto_producenta'], $this->info['nazwa_producenta'], $szerokoscImg, $wysokoscImg, array(), '', 'maly', true, false, false) . '</a>');

}
       
?>