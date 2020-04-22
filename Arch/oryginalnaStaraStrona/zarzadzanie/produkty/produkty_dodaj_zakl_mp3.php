<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone && isset($_GET['id_produktu']) && (int)$_GET['id_produktu'] >= 0 && Sesje::TokenSpr()) { 

    $id_produktu = (int)$_GET['id_produktu'];
 
    ?>

    <script type="text/javascript">
    //<![CDATA[          
    $(document).ready(function(){
        pokazChmurki();    
    });    
    
    function dodaj_mp3() {
        ile_pol_mp3 = parseInt($("#ile_pol_mp3").val()) + 1;
        //
        $('#mp3t').append('<tr id="mp3_'+ile_pol_mp3+'"></tr>');
        //
        $.get('ajax/dodaj_mp3.php', { id: ile_pol_mp3, katalog: '<?php echo KATALOG_ZDJEC; ?>' }, function(data) {
            $('#mp3_'+ile_pol_mp3).html(data);
            $("#ile_pol_mp3").val(ile_pol_mp3);
            $("#brak_mp3").remove();
        });
    } 
    
    function usun_mp3(id) {
        $('#mp3_' + id).remove();
    }
    //]]>
    </script>

    <div class="info_content">
        
        <div class="ramka_foto">
        
            <table class="tbl_mp3" id="mp3t">
                <tr class="tbl_mp3_naglowek">
                    <td style="width:45%"><span>Nazwa pliku</span></td>
                    <td style="width:53%"><span>Tytuł utworu</span></td>
                    <td style="width:2%"><span>Usuń</span></td>
                </tr>

                <?php
                //
                // pobieranie danych o plikach mp3
                $zapytanie_mp3 = "select distinct * from products_mp3 where products_id = '".$id_produktu."'";
                $sqls = $db->open_query($zapytanie_mp3);
                
                $ile_mp3 = (int)$db->ile_rekordow($sqls);
                
                $nr_utworu = 1;
                
                if ( $ile_mp3 > 0 ) {
                    //
                    while ($utwor = $sqls->fetch_assoc()) {
                        ?>
                        
                        <tr id="mp3_<?php echo $nr_utworu; ?>">    
                            <td>                              
                                <input type="text" name="utwor_mp3_<?php echo $nr_utworu; ?>" value="<?php echo $utwor['products_mp3_file']; ?>" class="toolTipTopText" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików" ondblclick="openFileBrowser('plik_mp3_<?php echo $nr_utworu; ?>','','<?php echo KATALOG_ZDJEC; ?>')" id="plik_mp3_<?php echo $nr_utworu; ?>" />                 
                            </td> 
                            <td>                              
                                <input type="text" name="nazwa_mp3_<?php echo $nr_utworu; ?>" value="<?php echo $utwor['products_mp3_name']; ?>" />                 
                            </td> 
                            <td>
                                <img onclick="usun_mp3('<?php echo $nr_utworu; ?>')" style="cursor:pointer" src="obrazki/kasuj.png" alt="Skasuj" title="Skasuj" />
                            </td>
                        </tr>

                        <?php
                        
                        $nr_utworu++;

                    }
                    //
                    unset($nr_utworu);    
                }
                
                //
                $db->close_query($sqls); 
                unset($zapytanie_mp3);                
                
                if ( $ile_mp3 == 0 ) {
                ?>
                <tr id="brak_mp3">
                    <td colspan="3">Brak przypisanych utworów mp3 do produktu ...</td>
                </tr>                    
                <?php
                }
                ?>

            </table>
            
        </div>
        
        <input value="<?php echo $ile_mp3; ?>" type="hidden" name="ile_pol_mp3" id="ile_pol_mp3" />
        
        <div style="padding:10px;padding-top:20px;">
            <span class="dodaj" onclick="dodaj_mp3()" style="cursor:pointer">dodaj kolejny utwór</span>
        </div>  

        <?php
        unset($ile_mp3);
        ?>
        
    </div>
        
<?php } ?>
