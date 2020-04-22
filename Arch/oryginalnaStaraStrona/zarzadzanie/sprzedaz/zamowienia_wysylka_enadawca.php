<?php
/*
2  - Paczka pocztowa                                    paczkaPocztowaType
3  - Paczka pocztowa PLUS                               paczkaPocztowaPLUSType
5  - Przesyłka pobraniowa                               przesylkaPobraniowaType
6  - Przesyłka polecona krajowa                         przesylkaPoleconaKrajowaType
7  - Przesyłka listowa z zadeklarowana wartością        przesylkaListowaZadeklarowanaWartoscType
8  - Przesyłka na warunkach szczególnych                przesylkaNaWarunkachSzczegolnychType
10 - POCZTEX                                            uslugaKurierskaType
11 - E-PRZESYŁKA                                        ePrzesylkaType
12 - Pocztex kurier 48 (przesyłka biznesowa)            przesylkaBiznesowaType
15 - Przesyłka firmowa nierejestrowana
13 - Przesyłka firmowa polecona                         przesylkaFirmowaPoleconaType
14 - Uługa paczkowa                                     uslugaPaczkowaType

20 - Przesyłka polecona zagraniczna                     przesylkaPoleconaZagranicznaType

*/

chdir('../');            

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

    $api = 'Elektroniczny Nadawca';
    $apiKurier = new ElektronicznyNadawca();

    if (isset($_POST['akcja']) && $_POST['akcja'] == 'zapisz') {


        //Sprawdzenie czy jest aktywny bufor - jak nie ma to utworzenie nowego

        try
        {
            $E = new getEnvelopeBuforList();
            $wynik = $apiKurier->getEnvelopeBuforList($E);
        }
        catch(SoapFault $soapFault)
        {
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y')));
            break;
        }

        if ( count($wynik) > 0 ) {
            if ( count($wynik->bufor) == 0 ) {

                $tmp = new createEnvelopeBufor();

                $B1 = new buforType();

                $B1->urzadNadania = $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_URZAD_NADANIA'];
                $B1->dataNadania  = date('d-m-Y');
                $B1->active       = true;
                $B1->opis         = 'Przesyłki ze sklepu';

                $tmp->bufor = $B1;

                $wynikB = $apiKurier->createEnvelopeBufor($tmp);
            }
        } else {
            echo Okienka::pokazOkno('Błąd', 'Sprawdź poprawność danych do logowania', 'index.php'.Funkcje::Zwroc_Get(array('przesylka','akcja','x','y'))); 
        }

        unset($E, $wynik, $wynikB);
        //

        $tmp = new addShipment();

        //dane adresowe - wspolne dla wszystkich wysylek
        $A = new adresType();

        $A->nazwa       = $_POST['wysylka']['nazwa'];
        $A->nazwa2      = $_POST['wysylka']['nazwa1'];
        $A->ulica       = $_POST['wysylka']['ulica'];
        $A->numerDomu   = $_POST['wysylka']['numerDomu'];
        $A->numerLokalu = $_POST['wysylka']['numerLokalu'];
        $A->miejscowosc = $_POST['wysylka']['miejscowosc'];
        $A->kodPocztowy = $_POST['wysylka']['kod'];
        $A->kraj        = ( isset($_POST['kraj']) ? $_POST['kraj'] : 'Polska');
        $A->telefon     = ( isset($_POST['wysylka']['telefon']) ? $_POST['wysylka']['telefon'] : '');
        $A->email       = ( isset($_POST['wysylka']['email']) ? $_POST['wysylka']['email'] : '');
        $A->mobile      = ( isset($_POST['wysylka']['mobile']) ? str_replace('-','',$_POST['wysylka']['mobile']) : '');


        if ( $_POST["typ_wysylki"] == '2' ) {
            $P = new paczkaPocztowaType();

            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? $_POST['wartosc']*100 : '' );
            $P->zwrotDoslanie               = ( isset($_POST['ZwrotDoslanie']) ? true : false );
            $P->egzemplarzBiblioteczny      = false;
            $P->dlaOciemnialych             = false;
        }

        if ( $_POST["typ_wysylki"] == '3' ) {
            $P = new paczkaPocztowaPLUSType();

            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? $_POST['wartosc']*100 : '' );
            $P->zwrotDoslanie               = ( isset($_POST['ZwrotDoslanie']) ? true : false );
        }

        if ( $_POST["typ_wysylki"] == '5' ) {
            $P = new przesylkaPobraniowaType();
            $Y = new pobranieType();

            $P->posteRestante               = '';
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->wartosc                     = ( isset($_POST['wartosc']) ? $_POST['wartosc']*100 : '' );
            $P->masa                        = $_POST['masa'];
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            $Y->sposobPobrania              = $_POST['sposobPobrania'];
            $Y->kwotaPobrania               = $_POST['kwotaPobrania'] * 100;
            $Y->nrb                         = $_POST['nrb'];
            $Y->tytulem                     = $_POST['tytulem'];

            $P->pobranie = $Y;
        }

        if ( $_POST["typ_wysylki"] == '6' ) {
            $P = new przesylkaPoleconaKrajowaType();

            $P->epo                         = '';
            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->masa                        = $_POST['masa'];
            $P->egzemplarzBiblioteczny      = false;
            $P->dlaOciemnialych             = false;
        }

        if ( $_POST["typ_wysylki"] == '7' ) {
            $P = new przesylkaListowaZadeklarowanaWartoscType();

            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['wartosc']) ? $_POST['wartosc']*100 : '' );
            $P->zwrotDoslanie               = ( isset($_POST['ZwrotDoslanie']) ? true : false );
        }

        if ( $_POST["typ_wysylki"] == '8' ) {
            $P = new przesylkaNaWarunkachSzczegolnychType();

            $P->posteRestante               = '';
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->kategoria                   = $_POST['kategoria'];
            $P->wartosc                     = ( isset($_POST['wartosc']) ? $_POST['wartosc']*100 : '' );
            $P->masa                        = $_POST['masa'];
        }

        if ( $_POST["typ_wysylki"] == '10' ) {
            $P = new uslugaKurierskaType();
            $Y = new pobranieType();
            //$D = new doreczenieUslugaKurierskaType();
            //$O = new odbiorPrzesylkiOdNadawcyType();
            //$ZD = new zwrotDokumentowKurierskaType();
            $PO = new potwierdzenieOdbioruKurierskaType();
            $PD = new potwierdzenieDoreczeniaType();
            $U = new ubezpieczenieType();
            $OP = new opakowanieKurierskaType();


            $P->posteRestante               = '';
            $P->termin                      = $_POST['terminRodzaj'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? $_POST['wartosc']*100 : '' );
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->zawartosc                   = $_POST['zawartosc'];
            $P->ponadgabaryt                = ( isset($_POST['ponadgabaryt']) ? true : false );
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );
            $P->uiszczaOplate               = $_POST['uiszczaOplate'];

            if ( isset($_POST['kopertaFirmowa']) ) {
                $P->opakowanie = 'FIRMOWA_DO_1KG';
            }

            if ( isset($_POST['pobranie']) && $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = $_POST['kwotaPobrania']*100;
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;

            //odbiorPrzesylkiOdNadawcyType Object
            //$O->wSobote                    = '';
            //$O->wNiedzieleLubSwieto        = '';
            //$O->wGodzinachOd20Do7          = '';

            //$P->odbiorPrzesylkiOdNadawcy = $O;

            //doreczenieUslugaKurierskaType Object
            //$D->oczekiwanyTerminDoreczenia = '';
            //$D->oczekiwanaGodzinaDoreczenia= '';
            //$D->wSobote                    = '';
            //$D->w90Minut                   = '';
            //$D->wNiedzieleLubSwieto        = '';
            //$D->doRakWlasnych              = '';
            //$D->wGodzinachOd20Do7          = '';
            //$D->po17                       = '';

            //$P->doreczenie = $D;

            //zwrotDokumentowKurierskaType Object
            //$ZD->rodzajPocztex             = '';
            //$ZD->rodzajPaczka              = '';
            //$ZD->rodzajList                = '';

            //$P->zwrotDokumentow = $ZD;

            //potwierdzenieOdbioruKurierskaType Object
            if ( isset($_POST['PotwierdzenieOdbioru']) ) {
                $PO->ilosc                     = $_POST['iloscPotwierdzenOdbioru'];
                $PO->sposob                    = $_POST['RodzajPotwierdzenOdbioru'];
            } else {
                $PO->ilosc                     = '';
                $PO->sposob                    = '';
            }

            $P->potwierdzenieOdbioru = $PO;

            //potwierdzenieDoreczeniaType Object
            if ( isset($_POST['PotwierdzenieDoreczenia']) ) {
                $PD->sposob                    = $_POST['RodzajPotwierdzenDoreczenia'];
                $PD->kontakt                   = $_POST['danePotwierdzenDoreczenia'];
            } else {
                $PD->sposob                    = '';
                $PD->kontakt                   = '';
            }

            $P->potwierdzenieDoreczenia = $PD;

            //ubezpieczenieType Object
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = $_POST['ubezpieczenie_wart']*100;

                $P->ubezpieczenie = $U;
            }
        }

        if ( $_POST["typ_wysylki"] == '11' ) {
            $P = new ePrzesylkaType();
            $Y = new pobranieType();

            //$P->urzadWydaniaEPrzesylki        = $_POST['urzadWydaniaEPrzesylki'];
            $P->masa                          = $_POST['masa'];
            $P->eSposobPowiadomieniaAdresata  = $_POST['eSposobPowiadomieniaAdresata'];
            $P->eSposobPowiadomieniaNadawcy   = $_POST['eSposobPowiadomieniaNadawcy'];
            $P->eKontaktAdresata              = $_POST['eKontaktAdresata'];
            $P->eKontaktNadawcy               = $_POST['eKontaktNadawcy'];
            $P->ostroznie                     = ( isset($_POST['ostroznie']) ? true : false );
            $P->wartosc                       = ( isset($_POST['wartosc']) ? $_POST['wartosc']*100 : '' );
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            if ( $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = $_POST['kwotaPobrania']*100;
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;
        }

        if ( $_POST["typ_wysylki"] == '12' ) {
            $P = new przesylkaBiznesowaType();
            $Y = new pobranieType();
            $U = new ubezpieczenieType();

            $P->masa                        = ( isset($_POST['CzyWartosciowa']) ? $_POST['waga'] : '' );
            $P->gabaryt                     = $_POST['gabaryt'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? $_POST['wartosc']*100 : '' );
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            if ( isset($_POST['pobranie']) && $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = $_POST['kwotaPobrania']*100;
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;

            //ubezpieczenieType Object
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = $_POST['ubezpieczenie_wart']*100;

                $P->ubezpieczenie = $U;
            }
        }

        if ( $_POST["typ_wysylki"] == '13' ) {
            $P = new przesylkaFirmowaPoleconaType();

            $P->posteRestante               = false;
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '' );
            $P->miejscowa                   = $_POST['miejscowa'];
            $P->kategoria                   = $_POST['kategoria'];
            $P->masa                        = $_POST['masa'];
            $P->egzemplarzBiblioteczny      = false;
            $P->dlaOciemnialych             = false;
        }

        if ( $_POST["typ_wysylki"] == '14' ) {
            $P = new uslugaPaczkowaType();
            $Y = new pobranieType();
            //$D = new doreczenieUslugaPocztowaType();
            //$ZD = new zwrotDokumentowPaczkowaType();
            $PO = new potwierdzenieOdbioruPaczkowaType();
            $PD = new potwierdzenieDoreczeniaType();
            $U = new ubezpieczenieType();

            $P->termin                      = $_POST['terminRodzaj'];
            $P->masa                        = $_POST['masa'];
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? $_POST['wartosc']*100 : '' );
            $P->ostroznie                   = ( isset($_POST['ostroznie']) ? true : false );
            $P->zawartosc                   = $_POST['zawartosc'];
            $P->ponadgabaryt                = ( isset($_POST['ponadgabaryt']) ? true : false );
            $P->uiszczaOplate               = $_POST['uiszczaOplate'];
            $P->sprawdzenieZawartosciPrzesylkiPrzezOdbiorce = ( isset($_POST['sprawdzenieZawartosciPrzesylkiPrzezOdbiorce']) ? true : false );

            if ( isset($_POST['pobranie']) && $_POST['kwotaPobrania'] > 0 ) {
                $Y->sposobPobrania              = $_POST['sposobPobrania'];
                $Y->kwotaPobrania               = $_POST['kwotaPobrania']*100;
                $Y->nrb                         = $_POST['nrb'];
                $Y->tytulem                     = $_POST['tytulem'];
            }

            $P->pobranie = $Y;

            //doreczenieUslugaPocztowaType Object
            //$D->oczekiwanyTerminDoreczenia = '';
            //$D->oczekiwanaGodzinaDoreczenia= '';
            //$D->wSobote                    = '';
            //$D->doRakWlasnych              = '';

            //$P->doreczenie = $D;

            //zwrotDokumentowPaczkowaType Object
            //$P->zwrotDokumentow            = '';

            //potwierdzenieOdbioruPaczkowaType Object
            if ( isset($_POST['PotwierdzenieOdbioru']) ) {
                $PO->ilosc                     = $_POST['iloscPotwierdzenOdbioru'];
                $PO->sposob                    = $_POST['RodzajPotwierdzenOdbioru'];
            } else {
                $PO->ilosc                     = '';
                $PO->sposob                    = '';
            }

            $P->potwierdzenieOdbioru = $PO;

            //potwierdzenieDoreczeniaType Object
            if ( isset($_POST['PotwierdzenieDoreczenia']) ) {
                $PD->sposob                    = $_POST['RodzajPotwierdzenDoreczenia'];
                $PD->kontakt                   = $_POST['danePotwierdzenDoreczenia'];
            } else {
                $PD->sposob                    = '';
                $PD->kontakt                   = '';
            }

            $P->potwierdzenieDoreczenia = $PD;

            //ubezpieczenieType Object
            if ( isset($_POST['CzyUbezpieczenie']) ) {
                $U->rodzaj                     = 'STANDARD';
                $U->kwota                      = $_POST['ubezpieczenie_wart']*100;

                $P->ubezpieczenie = $U;
            }
        }

        if ( $_POST["typ_wysylki"] == '20' ) {
            $P = new przesylkaPoleconaZagranicznaType();

            $P->posteRestante               = false;
            $P->masa                        = $_POST['masa'];
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '0');
        }

        if ( $_POST["typ_wysylki"] == '22' ) {
            $P = new paczkaZagranicznaType();
            $Z = new zwrotType();

            $P->posteRestante               = '';
            $P->kategoria                   = $_POST['kategoria'];
            $P->masa                        = $_POST['masa'];
            $P->iloscPotwierdzenOdbioru     = ( isset($_POST['PotwierdzenieOdbioru']) ? $_POST['iloscPotwierdzenOdbioru'] : '0');
            $P->ekspres                     = '';
            $P->wartosc                     = ( isset($_POST['CzyWartosciowa']) ? $_POST['wartosc']*100 : '' );


            if ( isset($_POST['zwrot_natychmiast']) || isset($_POST['zwrot_po_liczbie_dni']) ) {
                $Z->zwrotPoLiczbieDni           = '15';
            }
            if ( isset($_POST['porzucona']) ) {
                $Z->traktowacJakPorzucona       = true;
            } else {
                $Z->traktowacJakPorzucona       = false;
            }
            if ( !isset($_POST['porzucona']) ) {
                $Z->sposobZwrotu                = $_POST['sposob_zwr_zagr'];
            }

            $P->zwrot = $Z;

        }

        $P->guid = Funkcje::Guid();// wygenerowany guid

        $P->adres = $A;

        $tmp->przesylki[] = $P;

//echo '<pre>';
//echo print_r($tmp);
//echo '</pre>';


        $przesylka = $apiKurier->addShipment($tmp); // wysłanie zapytania


        if (count(array($przesylka)) > 0 ) {

            $komunikat = '';
            if ( isset($przesylka->retval->error) && is_array($przesylka->retval->error) ) {
                foreach ( $przesylka->retval->error as $error ) {
                    $komunikat .= $error->errorNumber . ': ' . str_replace('"','',$error->errorDesc) . '<br />';
                }
            } elseif ( isset($przesylka->retval->error) && !is_array($przesylka->retval->error) ) {
                $komunikat .= $przesylka->retval->error->errorNumber . ': ' . str_replace('"','',$przesylka->retval->error->errorDesc) . '<br />';
            } else {
                $pola = array(
                        array('orders_id',$filtr->process($_POST["id"])),
                        array('orders_shipping_type',$api),
                        array('orders_shipping_number',$przesylka->retval->numerNadania),
                        array('orders_shipping_weight',$_POST['masa']/1000),
                        array('orders_parcels_quantity','1'),
                        array('orders_shipping_status','0'),
                        array('orders_shipping_date_created', 'now()'),
                        array('orders_shipping_date_modified', 'now()'),
                        array('orders_shipping_comments', $przesylka->retval->guid),
                );

                $db->insert_query('orders_shipping' , $pola);
                unset($pola);
                Funkcje::PrzekierowanieURL('zamowienia_szczegoly.php?id_poz='.(int)$_POST["id"].'&zakladka='.$filtr->process($_POST["zakladka"]));
            }
        }

    }

    // wczytanie naglowka HTML
    include('naglowek.inc.php');

    $zapytanie = "SELECT * FROM orders_shipping WHERE orders_shipping_type = 'Elektroniczny Nadawca' AND orders_shipping_status = '0' AND DATE(orders_shipping_date_created) < CURRENT_DATE";
    $sql = $db->open_query($zapytanie);

    if ( $db->ile_rekordow($sql) > 0 ) {
        echo Okienka::pokazOkno('Błąd', 'W buforze są niewysłane paczki z wcześniejszych dni<br />najpierw należy opróżnić bufor', 'index.php'); 
    }
    $db->close_query($sql);
    unset($zapytanie, $info);

    if ( isset($komunikat) && $komunikat != '' ) {
        echo Okienka::pokazOkno('Błąd', $komunikat);
    }

    $haslo = new getPasswordExpiredDate();
    $DataHasla = $apiKurier->getPasswordExpiredDate($haslo);

    $dataBiezaca = time();
    $ostrzezenie = '';
    if ( $dataBiezaca > strtotime($DataHasla->dataWygasniecia) ) {
        $ostrzezenie = ' - <span style="color:red;">hasło wygasło</span>';
    }
    ?>

    <div id="naglowek_cont">Tworzenie wysyłki - <?php echo 'data ważności hasła w serwisie e-nadawca : ' . $DataHasla->dataWygasniecia . $ostrzezenie; ?></div>
    <div id="cont">
    
        <?php
        if ( !isset($_GET['id_poz']) ) {
             $_GET['id_poz'] = 0;
        }     
        if ( !isset($_GET['zakladka']) ) {
             $_GET['zakladka'] = '0';
        }      
        
        if ( (int)$_GET['id_poz'] == 0 ) {
        ?>

            <div class="poleForm"><div class="naglowek">Wysyłka</div>
                <div class="pozycja_edytowana">Brak danych do wyświetlenia</div>
            </div>    
      
            <?php
        } else {
        ?>

        <div class="poleForm">
            <div class="naglowek">Wysyłka za pośrednictwem <?php echo $api; ?> - zamówienie numer : <?php echo $_GET['id_poz']; ?></div>

            <div class="pozycja_edytowana" style="overflow:hidden;">  

                <?php
                //if ( $apiKurier->success ) {
                    $zamowienie     = new Zamowienie((int)$_GET['id_poz']);
                    $waga_produktow = $zamowienie->waga_produktow * 1000;
                    $wymiary        = array();

                    $adres_klienta  = Funkcje::PrzeksztalcAdres($zamowienie->dostawa['ulica']);
                    $adres_dom_lokal = Funkcje::PrzeksztalcAdresDomu($adres_klienta['dom']);
                    ?>

                    <script type="text/javascript" charset="utf-8">
                      //<![CDATA[
                        $(function() {
                          var a = <?php echo ( isset($_POST['typ_wysylki']) ? $_POST['typ_wysylki'] : $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_PRZESYLKA_DOMYSLNA']); ?>;
                          var b = <?php echo ( isset($_POST['wysylka']['masa']) ? $_POST['wysylka']['masa'] : $waga_produktow ); ?>;
                          var c = '<?php echo ( isset($_POST['wysylka']['panstwo']) ? $_POST['wysylka']['panstwo'] : $zamowienie->dostawa['kraj'] ); ?>';

                          $("#formularz").load("ajax/enadawca_formularz.php", {valueType: a, wagaProduktow: b, krajDostawy: c, html: encodeURIComponent($("#addhtml").html())});

                          $('#typ_wysylki').bind('change', function(ev) {
                             var value = $(this).val();
                             var waga  = <?php echo $waga_produktow; ?>;
                             var panstwo  = '<?php echo $zamowienie->dostawa['kraj']; ?>';
                             $("#formularz").empty();
                             $("#formularz").html('<div style="margin:10px;margin-top:20px;text-align:center;"><img src="obrazki/_loader.gif"></div>');
                             $.ajax({
                                type: "POST",
                                url:  "ajax/enadawca_formularz.php",
                                data: {valueType: value, wagaProduktow: waga, krajDostawy: panstwo, html: encodeURIComponent($("#addhtml").html())},
                                success: function(msg){
                                        $("#formularz").html(msg).show(); 
                                        $(".kropka, .toolTip, .toolTipTop").change(		
                                          function () {
                                            var type = this.type;
                                            var tag = this.tagName.toLowerCase();
                                            if (type == 'text' && tag != 'textarea' && tag != 'radio' && tag != 'checkbox') {
                                                //
                                                zamien_krp($(this),'0.00');
                                                //
                                            }
                                          }
                                        ); 
                                },
                             });
                          });
                        });




                      //]]>
                    </script>

                    <form action="sprzedaz/zamowienia_wysylka_enadawca.php<?php echo Funkcje::Zwroc_Get(); ?>" method="post" id="apiForm" class="cmxform"> 
            
                        <div>
                            <input type="hidden" name="akcja" value="zapisz" />
                            <input type="hidden" name="id" value="<?php echo $_GET['id_poz']; ?>" />
                            <input type="hidden" name="zakladka" value="<?php echo $_GET['zakladka']; ?>" />
                            <input type="hidden" id="wartosc_zamowienia_val" name="wartosc_zamowienia_val" value="<?php echo $zamowienie->info['wartosc_zamowienia_val']; ?>" />
                            <input type="hidden" id="wartosc_ubezpieczenia_val" name="wartosc_ubezpieczenia_val" value="<?php echo $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_KWOTA_UBEZPIECZENIA']; ?>" />
                        </div>

                        <table style="width:100%">
                            <tr>
                                <td style="width:55%; vertical-align:top">

                                    <div class="obramowanie_tabeli">

                                        <table class="listing_tbl">
                                            <tr class="div_naglowek">
                                                <td>Informacje o przesyłce</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:8px; padding-bottom:8px;">
                                                    <p>
                                                        <label class="required">Rodzaj wysyłki:</label>
                                                        <?php
                                                        $domyslnie = $apiKurier->polaczenie['INTEGRACJA_POCZTA_EN_PRZESYLKA_DOMYSLNA'];
                                                        if ( isset($_POST['typ_wysylki']) ) {
                                                            $domyslnie = $_POST['typ_wysylki'];
                                                        }
                                                        $tablica = array(
                                                               array('id' => '2', 'text' => 'Paczka pocztowa'),
                                                               array('id' => '3', 'text' => 'Paczka pocztowa PLUS'),
                                                               array('id' => '5', 'text' => 'Przesyłka pobraniowa'),
                                                               array('id' => '6', 'text' => 'Przesyłka polecona krajowa'),
                                                               array('id' => '7', 'text' => 'Przesyłka listowa z zadeklarowana wartością'),
                                                               array('id' => '8', 'text' => 'Przesyłka na warunkach szczególnych'),
                                                               array('id' => '10', 'text' => 'Pocztex'),
                                                               //array('id' => '11', 'text' => 'E-PRZESYŁKA'),
                                                               array('id' => '12', 'text' => 'Pocztex kurier 48 (przesyłka biznesowa)'),
                                                               //array('id' => '15', 'text' => 'Przesyłka firmowa nierejestrowana'),
                                                               array('id' => '13', 'text' => 'Przesyłka firmowa polecona'),
                                                               array('id' => '14', 'text' => 'Usługa paczkowa'),
                                                               array('id' => '20', 'text' => 'Zagraniczna przesyłka polecona'),
                                                               array('id' => '22', 'text' => 'Zagraniczna paczka do Unii Europejskiej')
                                                        );
                                                        echo Funkcje::RozwijaneMenu('typ_wysylki', $tablica, $domyslnie, 'id="typ_wysylki" style="width:250px;"' ); 
                                                        unset($tablica);
                                                        ?>
                                                    </p> 
                                                    <div id="formularz" style="font-weight:normal;"></div>
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                    
                                </td>
                                <td style="width:45%; vertical-align:top; padding-left:10px">

                                    <div class="obramowanie_tabeli">
                    
                                        <table class="listing_tbl">
                                            <tr class="div_naglowek">
                                                <td>Informacje</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:8px; padding-bottom:8px;">
                                                    <p>
                                                        <label class="readonly" style="width:200px;">Forma dostawy w zamówieniu:</label>
                                                        <input type="text" name="sposob_dostawy" value="<?php echo $zamowienie->info['wysylka_modul']; ?>" readonly="readonly" class="readonly" />
                                                    </p> 
                                                    <p>
                                                        <label class="readonly" style="width:200px;">Forma płatności w zamówieniu:</label>
                                                        <input type="text" name="sposob_zaplaty" value="<?php echo $zamowienie->info['metoda_platnosci']; ?>" readonly="readonly" class="readonly" />
                                                    </p> 
                                                    <p>
                                                        <label class="readonly" style="width:200px;">Wartość zamówienia:</label>
                                                        <input type="text" name="wartosc_zamowienia" value="<?php echo $waluty->FormatujCene($zamowienie->info['wartosc_zamowienia_val'], false, $zamowienie->info['waluta']); ?>" readonly="readonly" class="readonly" />
                                                    </p> 
                                                    <p>
                                                        <label class="readonly" style="width:200px;">Waga produktów:</label>
                                                        <input type="text" name="waga_zamowienia" value="<?php echo $waga_produktow; ?>" readonly="readonly" class="readonly" />
                                                    </p> 
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <br />

                                    <div class="obramowanie_tabeli">
                    
                                        <table class="listing_tbl">
                                            <tr class="div_naglowek">
                                                <td>Informacje odbiorcy</td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:8px; padding-bottom:8px;">
                                                    <p>
                                                        <label>Adresat:</label>
                                                        <input type="text" size="30" name="wysylka[nazwa]" id="nazwa" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['firma'] : $zamowienie->dostawa['nazwa'] ); ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Adresat 1:</label>
                                                        <input type="text" size="30" name="wysylka[nazwa1]" id="nazwa1" value="<?php echo ( $zamowienie->dostawa['firma'] != '' ? $zamowienie->dostawa['nazwa'] : '' ); ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Ulica:</label>
                                                        <input type="text" size="30" name="wysylka[ulica]" id="ulica" value="<?php echo $adres_klienta['ulica']; ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Numer domu:</label>
                                                        <input type="text" size="30" name="wysylka[numerDomu]" id="numerDomu" value="<?php echo $adres_dom_lokal['dom']; ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Numer lokalu:</label>
                                                        <input type="text" size="30" name="wysylka[numerLokalu]" id="numerLokalu" value="<?php echo $adres_dom_lokal['mieszkanie']; ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Kod pocztowy:</label>
                                                        <input type="text" size="30" name="wysylka[kod]" id="kod" value="<?php echo str_replace('-','',$zamowienie->dostawa['kod_pocztowy']); ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Miejscowość:</label>
                                                        <input type="text" size="30" name="wysylka[miejscowosc]" id="miejscowosc" value="<?php echo $zamowienie->dostawa['miasto']; ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Państwo:</label>
                                                        <input type="text" size="30" name="wysylka[panstwo]" id="panstwo" value="<?php echo $zamowienie->dostawa['kraj']; ?>" class="klient" />
                                                    </p> 
                                                    <p>
                                                        <label>Numer telefonu:</label>
                                                        <?php if ( Klienci::CzyNumerGSM($zamowienie->klient['telefon']) ) { ?>
                                                            <input type="text" size="30" name="wysylka[mobile]" id="mobile" value="<?php echo $zamowienie->klient['telefon']; ?>" class="klient" />
                                                        <?php } else { ?>
                                                            <input type="text" size="30" name="wysylka[telefon]" id="telefon" value="<?php echo $zamowienie->klient['telefon']; ?>" class="klient" />
                                                        <?php } ?>
                                                    </p> 
                                                    <p>
                                                        <label>Adres e-mail:</label>
                                                        <input type="text" size="30" name="wysylka[email]" id="email" value="<?php echo $zamowienie->klient['adres_email']; ?>"  class="klient" />
                                                    </p> 
                                                </td>
                                            </tr>
                                        </table>
                        
                                    </div>
                    
                                </td>
                            </tr>
                        </table>

                        <div class="przyciski_dolne">
                            <input type="submit" class="przyciskNon" value="Utwórz przesyłkę" />
                            <button type="button" class="przyciskNon" onclick="cofnij('zamowienia_szczegoly','<?php echo Funkcje::Zwroc_Wybrane_Get(array('id_poz','zakladka')); ?>','sprzedaz');">Powrót</button>           
                        </div>
                    </form>

                    <?php 
                //} else {
                //    echo 'Sprawdź konfigurację modułu';
                //}
                ?>
        
            </div>
        </div>

        <?php 
        } 
        ?>
    
    </div>    
    
    <?php
    include('stopka.inc.php');    
    
} 


?>
