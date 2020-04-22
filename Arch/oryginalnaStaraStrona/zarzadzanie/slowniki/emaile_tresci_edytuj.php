<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {

        foreach($_POST["zalacznik"] as $key => $val){
            if(empty($val)){
                unset($_POST["zalacznik"][$key]);
            }
        }
        $zalaczniki = implode(';', $_POST["zalacznik"]);
        //
        $pola = array(
                    array('sender_name',$filtr->process($_POST["nadawca_nazwa"])),
                    array('sender_email',$filtr->process($_POST["nadawca_email"])),
                    array('dw',$filtr->process($_POST["cc_email"])),
                    array('email_group',$filtr->process($_POST["grupa"])),
                    array('template_id',$filtr->process($_POST["szablon"])),
                    array('email_file',$zalaczniki)
                    );
        //
        $db->update_query('email_text' , $pola, " email_text_id = '".(int)$_POST["id"]."'");	
        unset($pola);             

        //
        $db->delete_query('email_text_description', "email_text_id = '".(int)$_POST["id"]."'");

        $ile_jezykow = Funkcje::TablicaJezykow();
        for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                //
                if (!empty($_POST['edytor_'.$w])) {
                        $pola = array(
                                    array('email_text_id',(int)$_POST["id"]),
                                    array('email_title',$filtr->process($_POST['tytul_'.$w])),
                                    array('description',$filtr->process($_POST['edytor_'.$w])),
                                    array('description_sms',$filtr->process($_POST['sms_'.$w])),
                                    array('language_id',$ile_jezykow[$w]['id'])
                         );
                } else {
                        $pola = array(
                                    array('email_text_id',(int)$_POST["id"]),
                                    array('email_title',$filtr->process($_POST['tytul_0'])),
                                    array('description',$filtr->process($_POST['edytor_0'])),
                                    array('description_sms',$filtr->process($_POST['sms_0'])),
                                    array('language_id',$ile_jezykow[$w]['id'])
                         );
                }
                $sql = $db->insert_query('email_text_description' , $pola);
                unset($pola);
        }                
        //
        Funkcje::PrzekierowanieURL('emaile_tresci.php?id_poz='.(int)$_POST["id"]);

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>
        
    <div id="naglowek_cont">Edycja pozycji</div>
    <div id="cont">

    <!-- Skrypt do walidacji formularza -->
    <script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {
        $("#slownikForm").validate({
            rules: {
                tytul_0: {
                    required: true
                },                            
                nadawca_nazwa: {
                    required: true
                },                            
                nadawca_email: {
                    required: true
                }                           
            }
        });
    });

    function dodaj_zalacznik() {
        var ile_pol = parseInt($("#ile_pol").val()) + 1;
        //
        $('#wyniki').append('<div id="wyniki'+ile_pol+'"></div>');
        //
        $.get('ajax/dodaj_zalacznik.php', { id: ile_pol, katalog: 'pobieranie' }, function(data) {
            $('#wyniki'+ile_pol).html(data);
            $("#ile_pol").val(ile_pol);
            //
            pokazChmurki();  
        });
    } 
    function usun_zalacznik(id) {
        $('.tip-twitter').css({'visibility':'hidden'});
        $('#wyniki' + id).remove();
    }

    //]]>
    </script>     

    <div class="poleForm">
        <div class="naglowek">Edycja danych</div>    

        <?php
            
        if ( !isset($_GET['id_poz']) ) {
             $_GET['id_poz'] = 0;
        }        
                            
        $zapytanie = "SELECT t.email_var_id, t.email_text_id, t.text_name, t.sender_name, t.sender_email, t.template_id, t.dw, t.email_group, t.email_file, tz.email_title, tz.description FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".$_SESSION['domyslny_jezyk']['id']."' WHERE t.email_text_id = '" . $filtr->process((int)$_GET['id_poz']) . "'";

        $sql = $db->open_query($zapytanie);
        
        $pokazObjasnienia = false;

        if ((int)$db->ile_rekordow($sql) > 0) {
            
            $info = $sql->fetch_assoc();
            
            $pokazObjasnienia = true;
            ?>

            <form action="slowniki/emaile_tresci_edytuj.php" method="post" id="slownikForm" class="cmxform"> 
            
            <div class="pozycja_edytowana">
            
                <input type="hidden" name="akcja" value="zapisz" />
                
                <input type="hidden" name="id" value="<?php echo $filtr->process((int)$_GET['id_poz']); ?>" />

                <?php $ile_jezykow = Funkcje::TablicaJezykow(); ?>

                <div class="info_tab">
                <?php
                for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                        echo '<span id="link_'.$w.'" class="a_href_info_tab" onclick="gold_tabs(\''.$w.'\',\'edytor_\', \'\',\'300\')">'.$ile_jezykow[$w]['text'].'</span>';
                }                                        
                ?>                                     
                </div>
                
                <div style="clear:both"></div>
                
                <div class="info_tab_content">
                    <?php
                    for ($w = 0, $c = count($ile_jezykow); $w < $c; $w++) {
                    
                        // pobieranie danych jezykowych
                        $zapytanie_jezyk = "select distinct * from email_text_description where email_text_id = '".$filtr->process((int)$_GET['id_poz'])."' and language_id = '" .$ile_jezykow[$w]['id']."'";
                        $sqls = $db->open_query($zapytanie_jezyk);
                        $nazwa = $sqls->fetch_assoc();     
                        
                        ?>
                        
                        <div id="info_tab_id_<?php echo $w; ?>" style="display:none;">
                        
                            <p>
                                <label <?php echo ( $w == '0' ? 'class="required"' : '' ); ?> >Tytuł emaila:</label>
                                <input type="text" name="tytul_<?php echo $w; ?>" size="90" value="<?php echo $nazwa['email_title']; ?>" id="tytul_<?php echo $w; ?>" />
                            </p>

                            <div class="edytor">
                                <textarea cols="50" rows="30" id="edytor_<?php echo $w; ?>" name="edytor_<?php echo $w; ?>"><?php echo $nazwa['description']; ?></textarea>
                            </div>     
                                
                            <?php 
                            $Ukryj = '';
                            if ( $info['email_var_id'] != 'EMAIL_ZMIANA_STATUSU_ZAMOWIENIA' && $info['email_var_id'] != 'EMAIL_ZAMOWIENIE' ) {
                                $Ukryj = 'style="display:none"';
                            }
                            ?>
                            <p <?php echo $Ukryj; ?>>
                                <label>Treść wiadomości SMS:</label>
                                <input type="text" name="sms_<?php echo $w; ?>" size="140" value="<?php echo $nazwa['description_sms']; ?>" id="sms_<?php echo $w; ?>" />
                            </p>

                            <?php
                            $db->close_query($sqls);
                            unset($nazwa); 
                            ?>

                        </div>
                        <?php                                        
                    }                                        
                    ?>                                            
                </div>
                
                <script type="text/javascript">
                //<![CDATA[
                gold_tabs('0','edytor_', '', '300');
                //]]>
                </script> 

                <p>
                    <label class="required">Nadawca nazwa:</label>
                    <input type="text" name="nadawca_nazwa" size="60" value="<?php echo $info['sender_name']; ?>" id="nadawca_nazwa" />
                </p>

                <p>
                    <label class="required">Nadawca email:</label>
                    <input type="text" name="nadawca_email" size="60" value="<?php echo $info['sender_email']; ?>" id="nadawca_email" />
                </p>

                <p>
                    <label>Prześlij do wiadomości:</label>
                    <input type="text" name="cc_email" size="60" value="<?php echo $info['dw']; ?>" id="cc_email" />
                </p>

                <p>
                    <label>Grupa:</label>
                    <?php
                    $tablica[] = array('id' => 'E-maile do klientów sklepu', 'text' => 'E-maile do klientów sklepu');
                    $tablica[] = array('id' => 'E-maile administratora', 'text' => 'E-maile administratora');
                    echo Funkcje::RozwijaneMenu('grupa', $tablica, $info['email_group'] ); 
                    unset($tablica);
                    ?>
                </p>

                <p>
                    <label>Szablon emaila:</label>
                    <?php
                    $tablica = Funkcje::ListaSzablonowEmail(false);
                    echo Funkcje::RozwijaneMenu('szablon', $tablica, $info['template_id'] ); ?>
                </p>

                <!-- Zalaczniki do maila -->
                <div id="wyniki">
                <?php
                    $tablicaZalacznikow = explode(';',$info['email_file']);
                    foreach($tablicaZalacznikow as $key => $val){
                        if( empty($val) ){
                            unset($tablicaZalacznikow[$key]);
                        }
                    }

                    if ( count($tablicaZalacznikow) > 0 ) {
                        $l = 1;
                        $ile_zalacznikow = count($tablicaZalacznikow);
                        foreach ( $tablicaZalacznikow as $zalacznik ) {
                            ?>
                            <div id="wyniki<?php echo $l; ?>">
                                <p>
                                <label>Plik załącznika:</label>
                                <input type="text" name="zalacznik[]" size="60" class="toolTipTopText" title="Kliknij dwukrotnie w to pole żeby otworzyć okno przeglądarki plików" ondblclick="openFileBrowser('zalacznik_<?php echo $l; ?>','','pobieranie')" id="zalacznik_<?php echo $l; ?>" value="<?php echo $zalacznik; ?>" />
                                <span class="usun_zalacznik toolTipTopText" onclick="usun_zalacznik('<?php echo $l; ?>')" title="Skasuj" />
                                </p>
                            </div>
                            <?php
                            $l++;
                        }
                    }
                ?>
                </div>

            </div>

            <input value="<?php echo ($ile_zalacznikow > 0 ? $ile_zalacznikow : '0'); ?>" type="hidden" name="ile_pol" id="ile_pol" />

            <div style="padding:10px;padding-top:20px;padding-left:30px;">
                <span class="dodaj" onclick="dodaj_zalacznik()" style="cursor:pointer">dodaj plik do dołączenia do maila</span>
            </div>   

            <div class="przyciski_dolne">
                <input type="submit" class="przyciskNon" value="Zapisz dane" />
                <button type="button" class="przyciskNon" onclick="cofnij('emaile_tresci','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','slowniki');">Powrót</button>     
            </div>

            </form>                

        <?php

        $db->close_query($sql);
                    
        } else {

            echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';

        }
    ?>
    
    </div>   

    <?php
    if ( $pokazObjasnienia == true ) {
    ?>    

    <div class="objasnienia">
        <div class="objasnieniaTytul">Znaczniki, które możesz użyć w tym e-mailu:</div>
        <div class="objasnieniaTresc">

        <div style="padding-bottom:10px;font-weight:bold;">Treść wiadomości</div>
            <ul class="mcol">

                <?php if ( $info['email_var_id'] == 'EMAIL_REJESTRACJA_KLIENTA_KONTO_AKTYWNE' ) { ?>
                    <li><b>{LINK}</b> - Link do strony logowania w sklepie</li>
                    <li><b>{LOGIN}</b> - Login zarejestrowanego klienta</li>
                    <li><b>{HASLO}</b> - Hasło zarejestrowanego klienta</li>
                    <li><b>{BIEZACA_DATA}</b> - Data wygenerowania wiadomości</li>
                    <li><b>{KLIENT_IP}</b> - IP komputera z którego rejestrował się klient</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_PRZYPOMNIENIE_HASLA' ) { ?>
                    <li><b>{LINK}</b> - Link do potwierdzenia zmiany hasła</li>
                    <li><b>{BIEZACA_DATA}</b> - Aktualna data</li>
                    <li><b>{KLIENT_IP}</b> - Adres IP komputerza klienta</li>
                <?php } ?>
                <?php if ( $info['email_var_id'] == 'EMAIL_PRZYPOMNIENIE_HASLA_KLIENTA' ) { ?>
                    <li><b>{HASLO}</b> - Wygenerowane hasło do logowania do sklepu</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_STATUSU_ZAMOWIENIA' ) { ?>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                    <li><b>{STATUS_ZAMOWIENIA}</b> - Status zamówienia</li>
                    <li><b>{DATA_ZAMOWIENIA}</b> - Data złożenia zamówienia</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                    <li><b>{LINK}</b> - Adres do historii zamówień klienta</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_STATUSU_REKLAMACJI' ) { ?>
                    <li><b>{NUMER_REKLAMACJI}</b> - Numer reklamacji</li>
                    <li><b>{DATA_REKLAMACJI}</b> - Data zgłoszenia reklamacji</li>
                    <li><b>{STATUS_REKLAMACJI}</b> - Status reklamacji</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                    <li><b>{LINK}</b> - Adres do historii reklamacji klienta</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_ZMIANA_STATUSU_PUNKTOW' ) { ?>
                    <li><b>{STATUS_PUNKTOW}</b> - Status punktów</li>
                    <li><b>{DATA_PUNKTOW}</b> - Data dopisania punktów do tabeli punktów klienta</li>
                    <li><b>{ILOSC_PUNKTOW}</b> - Ilość punktów których dotyczy zmiana statusu</li>
                    <li><b>{OGOLNA_ILOSC_PUNKTOW}</b> - Ogólna ilość punktów klienta</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_DODANIE_RECZNE_PUNKTOW' ) { ?>
                    <li><b>{STATUS_PUNKTOW}</b> - Status punktów</li>
                    <li><b>{ILOSC_PUNKTOW}</b> - Ilość punktów których dotyczy zmiana statusu</li>
                    <li><b>{OGOLNA_ILOSC_PUNKTOW}</b> - Ogólna ilość punktów klienta</li>
                    <li><b>{KOMENTARZ}</b> - Komentarz dołączany przez admina</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_POTWIERDZENIE_EMAIL_NEWSLETTERA' ) { ?>
                    <li><b>{LINK} tutaj tekst {/LINK}</b> - Link do potwierdzenia subskrypcji newslettera</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_REKLAMACJA_ZGLOSZENIE' ) { ?>
                    <li><b>{LINK}</b> - Link do strony ze szczegółami zgłoszenia</li>
                    <li><b>{KLIENT}</b> - Imię i nazwisko klienta</li>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                    <li><b>{NUMER_REKLAMACJI}</b> - Numer zgłoszenia reklamacji</li>
                    <li><b>{TYTUL_REKLAMACJI}</b> - Tytuł zgłoszenia reklamacji</li>
                    <li><b>{OPIS_REKLAMACJI}</b> - Opis zgłoszenia reklamacji</li>
                    <li><b>{BIEZACA_DATA}</b> - Data wysłania wiadomości</li>
                    <li><b>{KLIENT_IP}</b> - IP komputera z którego było wysłane zgłoszenie</li>
                <?php } ?>

                <?php if ( $info['email_var_id'] == 'EMAIL_ZAMOWIENIE' ) { ?>
                    <li><b>{IMIE_NAZWISKO_KUPUJACEGO}</b> - Imię i nazwisko osoby kupującej</li>
                    <li><b>{LINK}</b> - Link do strony ze szczegółami zgłoszenia</li>
                    <li><b>{NUMER_ZAMOWIENIA}</b> - Numer zamówienia</li>
                    <li><b>{DATA_ZAMOWIENIA}</b> - Data złożenia zamówienia</li>
                    <li><b>{FORMA_PLATNOSCI}</b> - Wybrana przez klienta forma płatności za zamówienie</li>
                    <li><b>{OPIS_FORMY_PLATNOSCI}</b> - Informacja do wybranej przez klienta formy płatności - np numer konta bankowego</li>
                    <li><b>{FORMA_WYSYLKI}</b> - Wybrana przez klienta forma wysyłki zamówienia</li>
                    <li><b>{OPIS_FORMY_WYSYLKI}</b> - Informacja do wybranej przez klienta formie wysyłki - np miejsce odbioru osobistego</li>                                        
                    <li><b>{DOKUMENT_SPRZEDAZY}</b> - Dokument sprzedaży - faktura lub paragon</li>
                    <li><b>{LINK_PLIKOW_ELEKTRONICZNYCH}</b> - Link wraz z informacją do pobrania plików elektronicznych - używane tylko przy sprzedaży produktów online</li>
                    <li><b>{LISTA_PRODUKTOW}</b> - Lista zamówionych przez klienta produktów</li>
                    <li><b>{MODULY_PODSUMOWANIA}</b> - Poszczególne pozycje zamówienia: wartość produktów, koszty wysyłki, zniżki, rabaty, ogólna wartość zamówienia etc</li>
                    <li><b>{KOMENTARZ_DO_ZAMOWIENIA}</b> - Komentarz dodany przez klienta do zamówienia</li>
                    <li><b>{ADRES_ZAMAWIAJACEGO}</b> - Dane adresowe klienta</li>
                    <li><b>{ADRES_DOSTAWY}</b> - Adres dostawy produktów</li>
                    <li><b>{ADRES_EMAIL_ZAMAWIAJACEGO}</b> - Adres email klienta</li>
                <?php } ?>                                    

                <li><b>{ADRES_URL_SKLEPU}</b> - Adres internetowy sklepu</li>
            </ul>

            <div style="padding-bottom:10px;font-weight:bold;">Dane sklepu</div>
            <ul class="mcol">
                <?php
                $zapytanie = "SELECT * FROM settings WHERE type = 'firma' OR type = 'sklep' ORDER BY type, sort";

                $sql = $db->open_query($zapytanie);

                while ($info = $sql->fetch_assoc()) {
                    echo '<li><b>{'.$info['code'].'}</b> - '.$info['description'].'</li>';
                }
                $db->close_query($sql);
                unset($zapytanie,$info);

                ?>
            </ul>
        
        </div> 
    </div>

    <?php
    }
    unset($pokazObjasnienia);
    ?>    

    </div> 
    
    <?php
    unset($info);                        
    include('stopka.inc.php');

}
