<?php

if ( isset($pobierzFunkcje) ) {

    // tylko dla zalogowanych
    $warunek = " and ( products_file_login = '1' ";
    if ( (int)$_SESSION['customer_id'] > 0 && $_SESSION['gosc'] == '0' ) {
        $warunek .= " or products_file_login = '0' ";
    }
    $warunek .= ' ) ';

    $zapytanie = "SELECT products_file_unique_id, products_file_name, products_file, products_file_description FROM products_file WHERE products_id = '" . $this->id_produktu . "' AND language_id = '" . $this->jezykDomyslnyId . "'" . $warunek . " ";

    $sql = $GLOBALS['db']->open_query($zapytanie);

    while ($info = $sql->fetch_assoc()) {
        //
        if ( !empty($info['products_file_name']) && !empty($info['products_file']) ) {
            //
            // generowanie id
            $UniqId = ($info['products_file_unique_id'] * $info['products_file_unique_id']);
            //
            $this->Pliki[] = array( 'nazwa' => $info['products_file_name'],
                                    'opis'  => $info['products_file_description'],
                                    'plik'  => 'pobierz-' . Sesje::Token() . '-' . $UniqId . '.html');
            unset($UniqId);
            //
        }            
    }
    $GLOBALS['db']->close_query($sql); 

    unset($zapytanie, $info);
  
}

?>        