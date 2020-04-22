<?php
if ($prot->wyswietlStrone && $_SESSION['grupaID'] == '1') {

    $IloscProduktow = Produkty::ProduktyModulowe(1000000, 'produkty');
    $IloscProduktowAktywne = Produkty::ProduktyModulowe(1000000, 'produktyAktywne');
    $IloscProduktowNieaktywne = Produkty::ProduktyModulowe(1000000, 'produktyNieaktywne');
    $IloscPromocji  = Produkty::ProduktyModulowe(1000000, 'promocje');

    $zapytanie = "SELECT DISTINCT count(c.categories_id) ilosc
                  FROM categories c
                  LEFT JOIN categories_description cd ON cd.categories_id = c.categories_id
                  WHERE cd.language_id = '".$_SESSION['domyslny_jezyk']['id']."'";
    $sql = $db->open_query($zapytanie);
    $info = $sql->fetch_assoc();

    echo '<div class="ModulyUwaga">Ilość kategorii w sklepie: <strong>' . $info['ilosc'] .'</strong> - <a class="edit toolTipTop" title="Przejdź do działu kategorii" href="./kategorie/kategorie.php">szczegóły</a></div>';
    $db->close_query($sql);
    unset($zapytanie, $info);

    echo '<div class="ModulyUwaga">Ilość produktów w sklepie: <strong>' . $IloscProduktow .'</strong> - <a class="edit toolTipTop" title="Przejdź do działu produktów" href="./produkty/produkty.php?wszystkie">szczegóły</a></div>';
    
    echo '<div class="ModulyPrzerwa">';
    
    echo 'w tym <b>nieaktywnych</b>: <strong>' . $IloscProduktowNieaktywne .'</strong> - <a class="edit toolTipTop" title="Przejdź do działu produktów nieaktywnych" href="./produkty/produkty.php?nieaktywne">szczegóły</a> <br />'; 
    
    echo 'w tym <b>aktywnych</b>: <strong>' . $IloscProduktowAktywne .'</strong> - <a class="edit toolTipTop" title="Przejdź do działu produktów aktywnych" href="./produkty/produkty.php?aktywne">szczegóły</a>'; 
    
    echo '</div>';

    echo '<div class="ModulyUwaga">Ilość produktów promocyjnych w sklepie: <strong>' . $IloscPromocji .'</strong> - <a class="edit toolTipTop" title="Przejdź do działu promocji" href="./promocje/promocje.php">szczegóły</a></div>';

    $zapytanie = "SELECT count(c.customers_id) ilosc
                  FROM customers c
                  LEFT JOIN customers_info ci ON ci.customers_info_id = c.customers_id
                  WHERE c.customers_guest_account = '0'";
    $sql = $db->open_query($zapytanie);
    $info = $sql->fetch_assoc();

    echo '<div class="ModulyUwaga">Ilość klientów zarejestrowanych: <strong>' . $info['ilosc'] .'</strong> - <a class="edit toolTipTop" title="Przejdź do działu klientów" href="./klienci/klienci.php">szczegóły</a></div>';
    $db->close_query($sql);
    unset($zapytanie, $info);
    
    if ( WLACZENIE_CACHE == 'tak' ) {
         echo '<a class="UsunCache toolTipTop" title="Jeżeli w sklepie był wykonywany import danych przy użyciu zewnętrznego programu - konieczne jest odświeżenie pamięci podręcznej sklepu" href="./?resetCache">odśwież pamięć podręczną sklepu (cache)</a>';
    }
    
    unset($IloscProduktow, $IloscProduktowAktywne, $IloscProduktowNieaktywne, $IloscPromocji);

} else {

    echo '<div class="ModulyOstrzezenie">Nie posiadasz uprawień do przeglądania tego elementu.</div>';

}
?>
