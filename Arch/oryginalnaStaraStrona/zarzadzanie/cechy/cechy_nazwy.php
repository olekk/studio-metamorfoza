<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && Sesje::TokenSpr()) {
    
    if ( isset($_SESSION['filtry']['cechy.php']['sort']) ) {
         //
         $_GET['sort'] = $_SESSION['filtry']['cechy.php']['sort'];
         //
    }    

    ?>
    <script type="text/javascript">
    //<![CDATA[   
    
    // ustawienie sortowania
    var parametry = '<?php echo $filtr->process($_GET['sort']); ?>';
    var czy_jest_sortowanie = parametry.indexOf("sort_");
    sort = "sort_a1";
    if (czy_jest_sortowanie != "-1") {
      for (t=1;t<20;t++) {
         if (parametry.indexOf("sort_a"+t) != "-1") { sort = "sort_a"+t; }
      }
    }
    if (document.getElementById(sort)) {
      var link_sortowania = document.getElementById(sort);
      link_sortowania.className = 'sortowanie_zaznaczone';
    }
    
    function pokaz_wartosci_cechy(id) {
        $("#cechy_wartosci").html('<img src="obrazki/_loader.gif">');
        $.get('cechy/cechy_wartosci.php' + '?id_cechy=' + id +'&tok=<?php echo Sesje::Token(); ?>', function(data) { $('#cechy_wartosci').css('display','none'); $('#cechy_wartosci').html(data); $('#cechy_wartosci').slideDown("fast"); });
        // 
        id_stare = $("#id_wart").html();
        if (id_stare != '') {
            $("#opcja_"+id_stare+" td").css("background","");
        }
        //
        var src = 'obrazki/tlo_lista_on.png';
        $("#opcja_"+id+" td").css('backgroundImage','url(' + src +')'); 
        $("#id_wart").html(id);
    }


    function dodaj_wartosc() { 
        document.location = '/zarzadzanie/cechy/cechy_wartosci_dodaj.php?id_cechy='+$("#id_wart").html(); 
    }
    //    
    //]]>
    </script>    

    <div class="ramka">

    <?php

    // informacje o produktach - zakres
    $listing_danych = new Listing();

    $tablica_naglowek = array(array('ID', 'center'),
                              array('Nazwa'),
                              array('Typ', 'center'),
                              array('Wartość', 'center'),
                              array('W formie<br />obrazków', 'center'),
                              array('Filtry', 'center'),
                              array('Sort', 'center'));
    echo $listing_danych->naglowek($tablica_naglowek);

    $zapytanie = "select distinct * from products_options where language_id = '".$_SESSION['domyslny_jezyk']['id']."'";

    // jezeli jest sortowanie
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case "sort_a1":
                $sortowanie = 'products_options_name asc';
                break;
            case "sort_a2":
                $sortowanie = 'products_options_name desc';
                break;
            case "sort_a3":
                $sortowanie = 'products_options_sort_order asc';
                break;  
            case "sort_a4":
                $sortowanie = 'products_options_sort_order desc';
                break;                      
        }            
    } else { $sortowanie = 'products_options_name asc'; }    

    $zapytanie .= " order by ".$sortowanie;    

    $sql = $db->open_query($zapytanie);    

    $tekst = '';
    while ($info = $sql->fetch_assoc()) {

          $tekst .= '<tr id="opcja_'.$info['products_options_id'].'" class="pozycja_off chmurka" style="cursor:pointer" title="Kliknij żeby zobaczyć wartości cech" onmouseover="this.className=\'pozycja_on\'" onmouseout="this.className=\'pozycja_off\'">';

          $tablica = array();
          
          $tablica[] = array($info['products_options_id'], 'center','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
          $tablica[] = array($info['products_options_name'], '','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
          
          $typ = '-';
          if ($info['products_options_images_enabled'] != 'true') {
              //
              if ($info['products_options_type'] == 'lista') {
                $typ = 'Select';
              }
              if ($info['products_options_type'] == 'radio') {
                $typ = 'Radio';
              } 
              //
          }
          $tablica[] = array($typ,'center', '',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');

          $typ = '-';
          // rodzaj wartosci - procent czy cena
          if ($info['products_options_value'] == 'kwota') {
            $typ = 'Kwota';
          }
          if ($info['products_options_value'] == 'procent') {
            $typ = 'Procent';
          }       
          $tablica[] = array($typ, 'center','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
          
          if ($info['products_options_images_enabled'] == 'true') {
            $tablica[] = array('<img src="obrazki/image_cechy.png" alt="Cecha wyświetlana w formie obrazków" title="Cecha wyświetlana w formie obrazków" onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\') />"', 'center','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
           } else {
            $tablica[] = array('-','center','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
          }     

          if ($info['products_options_filter'] == '1') {
            $tablica[] = array('<img src="obrazki/aktywny_on.png" alt="Cecha jest wyświetlana w filtrach w listingu produktów" title="Cecha jest wyświetlana w filtrach w listingu produktów" onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\') />"', 'center','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
           } else {
            $tablica[] = array('<img src="obrazki/aktywny_off.png" alt="Cecha nie jest wyświetlana w filtrach w listingu produktów" title="Cecha nie jest wyświetlana w filtrach w listingu produktów" onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\') />"', 'center','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
          }          
          
          $tablica[] = array($info['products_options_sort_order'], 'center','',' onclick="pokaz_wartosci_cechy(\''.$info['products_options_id'].'\')"');
          
          $tekst .= $listing_danych->pozycje($tablica);
          
          $tekst .= '<td class="rg_right">';
          
          // zmienne do przekazania
          $zmienne_do_przekazania = '?id_cechy='.(int)$info['products_options_id']; 
          
          $tekst .= '<a href="cechy/cechy_nazwy_edytuj.php'.$zmienne_do_przekazania.'"><img src="obrazki/edytuj.png" alt="Edytuj" title="Edytuj" /></a>';
          $tekst .= '<a href="cechy/cechy_nazwy_usun.php'.$zmienne_do_przekazania.'"><img src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" /></a>';
          
          $tekst .= '</td></tr>';
          
    } 
    
    if ( $tekst == '' ) {
         echo '<tr><td colspan="9" style="padding:10px">Brak wyników do wyświetlania</td></tr>';
    }    
    
    $tekst .= '</table>';
    //
    echo $tekst;
    //
    $db->close_query($sql);
    unset($listing_danych,$tekst,$tablica,$tablica_naglowek);      

    ?>
    
    </div>
    
<?php
}
?>    
