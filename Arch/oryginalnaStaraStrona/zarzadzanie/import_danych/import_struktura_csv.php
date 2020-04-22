<?php
$linia = (int)$_POST['limit'];

// przejescie do wybranej linii
$file->seek( $linia );
$DaneCsv = $file->current(); 

// tworzenie tablicy poszczegolnych pol
$TabDaneCsv = explode($_POST['separator'], $DaneCsv);
$TablicaDane = array();

// przypisanie danych do tablicy
// tablica bedzie miala postac np
// $TablicaDane[Nr_katalogowy] = jakas wartosc
//

if (count($TabDaneCsv) > 0) {
    //
    for ($q = 0, $c = count($TablicaDef); $q < $c; $q++) {
        
        if (isset($TabDaneCsv[$q])) {
            //
            $TabDaneCsv[$q] = trim($TabDaneCsv[$q]);
            //
            // jezeli ciag zaczyna sie od " lub ' i konczy na " lub ' to trzeba to wyczyscic
            if ((substr($TabDaneCsv[$q], 0, 1) == "'") && (substr($TabDaneCsv[$q], (strlen($TabDaneCsv[$q]) - 1), 1) == "'")) {
                $TabDaneCsv[$q] = substr($TabDaneCsv[$q], 1, (strlen($TabDaneCsv[$q]) - 2));
            }
            if ((substr($TabDaneCsv[$q], 0, 1) == '"') && (substr($TabDaneCsv[$q], (strlen($TabDaneCsv[$q]) - 1), 1) == '"')) {
                $TabDaneCsv[$q] = substr($TabDaneCsv[$q], 1, (strlen($TabDaneCsv[$q]) - 2));
            }       
            //
            $TabDaneCsv[$q] = str_replace('""', '"', $TabDaneCsv[$q]);
            //
            $TablicaDane[$TablicaDef[$q]] = trim($TabDaneCsv[$q]);
            
            if ( trim($TablicaDef[$q]) == 'Nazwa_produktu' ) {
                 $TablicaDane['Nazwa_produktu_struktura'] = trim($TabDaneCsv[$q]); 
            }            
            
        }
        
    }
    //
}
?>