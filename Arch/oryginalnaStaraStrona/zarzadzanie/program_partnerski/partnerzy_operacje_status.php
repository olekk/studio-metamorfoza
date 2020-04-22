<?php
chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {
        //
        $id_edytowanej_pozycji = $filtr->process($_POST['id']);
        $id_klienta = $filtr->process($_POST['id_poz']);
        //
        $pola = array(array('points_status',$filtr->process((int)$_POST['status'])));
        
        // jezeli status anulowane lub zatwierdzone
        if ( (int)$_POST['status'] == 2 || (int)$_POST['status'] == 3 ) {
            $pola[] = array('date_confirm','now()');
          } else {
            $pola[] = array('date_confirm','');
        }
        //	
        $sql = $db->update_query('customers_points', $pola, 'unique_id = ' . $id_edytowanej_pozycji);
        unset($pola);        
        
        // czy ma zmniejszyc ogolna ilosc punktow klienta
        if ($_POST['tryb'] == '1') {
            //
            // ile klient ma punktow
            $zapytanie = "select distinct customers_shopping_points from customers where customers_id = '".$id_klienta."'";
            $sqlc = $db->open_query($zapytanie);       
            $info = $sqlc->fetch_assoc();
            $IleMaPkt = $info['customers_shopping_points'];
            $db->close_query($sqlc);
            unset($info, $zapytanie);            
            //
            $LiczbaPkt = (int)$IleMaPkt + $filtr->process((int)$_POST['punkty']);
            if ($LiczbaPkt < 0) {
                $LiczbaPkt = 0;
            }
            //
            $pola = array(array('customers_shopping_points', $LiczbaPkt));
            //	
            $sql = $db->update_query('customers', $pola, 'customers_id = ' . $id_klienta);
            unset($pola, $LiczbaPkt);            
            //
        }
        
        // jezeli ma wyslac do klienta maila
        if (isset($_POST['mail']) && $_POST['mail'] == 'tak') {  
        
            $zapytanie_tresc = "SELECT t.sender_name, t.email_var_id, t.sender_email, t.dw, t.template_id, t.email_file, tz.email_title, tz.description, tz.description_sms FROM email_text t LEFT JOIN email_text_description tz ON tz.email_text_id = t.email_text_id AND tz.language_id = '".(int)$_POST["jezyk"]."' WHERE t.email_var_id = 'EMAIL_ZMIANA_STATUSU_PUNKTOW'";
            $sql = $db->open_query($zapytanie_tresc);
            $tresc = $sql->fetch_assoc();        
        
            $zapytanie_klient = "SELECT * FROM customers WHERE customers_id = '".$id_klienta."'";
            $sql_klient = $db->open_query($zapytanie_klient);
            $info_klient = $sql_klient->fetch_assoc();       

            $zapytanie_punkty = "SELECT * FROM customers_points WHERE unique_id = '".$id_edytowanej_pozycji."'";
            $sql_punkty = $db->open_query($zapytanie_punkty);
            $info_punkty = $sql_punkty->fetch_assoc();             

            define('STATUS_PUNKTOW', Klienci::pokazNazweStatusuPunktow( (int)$_POST['status'], (int)$_POST["jezyk"] ));
            define('DATA_PUNKTOW', date('d-m-Y',strtotime($info_punkty['date_added'])) );
            define('ILOSC_PUNKTOW', $info_punkty['points'] );
            define('OGOLNA_ILOSC_PUNKTOW', $info_klient['customers_shopping_points'] );
            define('KOMENTARZ', $filtr->process($_POST['komentarz']));  

            $email = new Mailing;

            if ( $tresc['email_file'] != '' ) {
                $tablicaZalacznikow = explode(';', $tresc['email_file']);
            } else {
                $tablicaZalacznikow = array();
            }

            $nadawca_email   = Funkcje::parsujZmienne($tresc['sender_email']);
            $nadawca_nazwa   = Funkcje::parsujZmienne($tresc['sender_name']);
            $cc              = Funkcje::parsujZmienne($tresc['dw']);

            $adresat_email   = $info_klient['customers_email_address'];
            $adresat_nazwa   = $info_klient['customers_firstname'] . ' ' . $info_klient['customers_lastname'];

            $temat           = Funkcje::parsujZmienne($tresc['email_title']);
            $tekst           = $tresc['description'];
            $zalaczniki      = $tablicaZalacznikow;
            $szablon         = $tresc['template_id'];
            $jezyk           = (int)$_POST["jezyk"];


            $tekst = Funkcje::parsujZmienne($tekst);
            $tekst = preg_replace('#(<br */?>\s*)+#i', '<br /><br />', $tekst);

            $wiadomosc = $email->wyslijEmail($nadawca_email,$nadawca_nazwa,$adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, false);
            
            $db->close_query($sql_klient);
            $db->close_query($sql_punkty);
            $db->close_query($sql);
            unset($wiadomosc, $zapytanie_punkty, $info_punkty, $zapytanie_klient, $info_klient, $tresc, $zapytanie_tresc);             

        }
        
        Funkcje::PrzekierowanieURL('partnerzy_operacje.php?id_poz='.(int)$id_klienta);        

    }   

    // wczytanie naglowka HTML
    include('naglowek.inc.php');
    ?>

    <div id="naglowek_cont">Zmiana statusu</div>
    <div id="cont">

          <form action="program_partnerski/partnerzy_operacje_status.php" method="post" id="klienciForm" class="cmxform"> 
          
          <div class="poleForm">
            <div class="naglowek">Zmiana statusu punktów klienta</div>
            
            <?php
            if ( !isset($_GET['id_poz']) ) {
                 $_GET['id_poz'] = 0;
            }    
            if ( !isset($_GET['id']) ) {
                 $_GET['id'] = 0;
            }               
                       
            $zapytanie = "select distinct * from customers_points where unique_id = '".$filtr->process($_GET["id"])."' and customers_id = '".$filtr->process($_GET["id_poz"])."'";
            $sql = $db->open_query($zapytanie);
            
            if ((int)$db->ile_rekordow($sql) > 0) {

                $info = $sql->fetch_assoc();  
                ?>            
            
                <div class="pozycja_edytowana">                    
                
                    <input type="hidden" name="akcja" value="zapisz" />

                    <input type="hidden" name="id" value="<?php echo $info['unique_id']; ?>" />
                    <input type="hidden" name="id_poz" value="<?php echo $info['customers_id']; ?>" />

                    <div class="info_content">
                    
                    <p>
                        <label>Ilość punktów:</label>
                        <input type="text" name="punkty" value="<?php echo $info['points']; ?>" size="8" class="calkowita" />
                    </p>
                    
                    <p>
                        <label>Aktualny status:</label>
                        <span class="daty"><b><?php echo Klienci::pokazNazweStatusuPunktow($info['points_status']); ?></b></span>
                    </p>                    

                    <p>
                        <label>Nowy status punktów:</label>
                        <?php        
                        echo Funkcje::RozwijaneMenu('status', Klienci::ListaStatusowPunktow(false))
                        ?>                        
                    </p>       

                    <p>
                        <label>Zmiana punktów klienta:</label>
                        <input type="radio" value="1" name="tryb" class="toolTipTop" title="Ogólna ilość punktów klienta zostanie zmieniona" checked="checked" /> dodaj punkty klientowi           
                        <input type="radio" value="2" name="tryb" class="toolTipTop" title="Ogólna ilość punktów klienta pozostanie bez zmian" /> nie dodawaj punktów
                    </p>
                    
                    <hr style="color:#82b4cd;border-top: 1px dashed #c0d9e6;border-bottom:none;border-left:none;border-right:none;width:96%;" />
                    
                    <p>
                      <label>Poinformuj klienta e-mail:</label>
                      <input type="checkbox" checked="checked" value="tak" name="mail" class="toolTip" title="Informacja o zmianie statusu zostanie przesłana do klienta" />
                    </p>                    

                    <p>
                      <label>W jakim języku wysłać email:</label>
                      <?php
                      echo Funkcje::RadioListaJezykow();
                      ?>
                    </p>  

                    <p>
                      <label>Komentarz:</label>
                      <textarea cols="100" rows="10" name="komentarz" class="wysiwyg"></textarea>
                    </p>                    
                    
                    </div>
                    
                </div>
                
                <div class="przyciski_dolne">
                  <input type="submit" class="przyciskNon" value="Zapisz dane" />
                  <button type="button" class="przyciskNon" onclick="cofnij('partnerzy_operacje','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz')); ?>','program_partnerski');">Powrót</button>    
                </div>

            <?php 
            $db->close_query($sql);
            unset($info);

            } else {
            
                echo '<div class="pozycja_edytowana">Brak danych do wyświetlenia</div>';
            
            }
            ?>                    
            
          </div>

          </form>

    </div>
    
    <?php
    include('stopka.inc.php');    
    
} ?>