<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    
    if ( isset($_GET['sort']) ) {
         $_SESSION['filtry'][ 'cechy.php' ]['sort'] = $filtr->process($_GET['sort']);   
    }
    
    ?>
    
    <div id="caly_listing">

        <div id="ajax"></div>

        <div id="naglowek_cont">Zarządzenie cechami produktów</div>
        
        <div id="sortowanie">
            <span>Sortowanie: </span>
            <a id="sort_a1" class="sortowanie" href="cechy/cechy.php?sort=sort_a1">nazwy rosnąco</a>
            <a id="sort_a2" class="sortowanie" href="cechy/cechy.php?sort=sort_a2">nazwy malejąco</a>     
            <a id="sort_a3" class="sortowanie" href="cechy/cechy.php?sort=sort_a3">kolejność rosnąco</a>
            <a id="sort_a4" class="sortowanie" href="cechy/cechy.php?sort=sort_a4">kolejność malejąco</a>                      
        </div>    
        
        <div id="id_wart"></div>
        <table style="width:100%">
            <tr>
                <td class="cech_dodaj">
                    <div>
                        <a class="dodaj" href="cechy/cechy_nazwy_dodaj.php">dodaj nową cechę</a>
                    </div>            
                </td>
                <td class="cech_dodaj">
                    <div id="cechy_wartosci_dodawanie" style="display:none">
                       <a class="dodaj" href="javascript:dodaj_wartosc()">dodaj nową wartość cechy</a> 
                    </div>
                </td>
            </tr>
            
            <tr>
                <td id="cechy_nazwy">
                    <script type="text/javascript">
                    //<![CDATA[       
                    $("#cechy_nazwy").html('<img src="obrazki/_loader.gif" />');
                    $.get('cechy/cechy_nazwy.php?tok=<?php echo Sesje::Token(); ?>', function(data) { 
                         $('#cechy_nazwy').html(data); <?php echo ((isset($_GET['id_cechy']) && (int)$_GET['id_cechy'] > 0) ? 'pokaz_wartosci_cechy("'.$filtr->process($_GET['id_cechy']).'");' : ''); ?>  
                         pokazChmurki();                            
                    });                       
                    //]]>
                    </script>          
                </td>
                <td  id="cechy_wartosci_td">
                    <div id="cechy_wartosci">
                        <div id="komnik">Nie wybrano cechy ...</div>                
                    </div>
                </td>
            </tr>
        </table>
        
    </div>        

    <?php include('stopka.inc.php'); ?>

<?php } ?>