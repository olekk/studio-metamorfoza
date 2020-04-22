<?php

class PlatnosciElektroniczne {

    // funkcja weryfikuje poprawnosc danych przekazanych z serwisu Przelewy24
    public static function p24_weryfikuj($p24_id_sprzedawcy, $p24_session_id, $p24_order_id, $p24_kwota, $tryb = '0') {
        $P = array(); 
        $RET = array();
        $url = "https://".( $tryb == '1' ? 'sandbox.przelewy24.pl/transakcja.php' : 'secure.przelewy24.pl/transakcja.php')."";
        $P[] = "p24_id_sprzedawcy=".$p24_id_sprzedawcy;
        $P[] = "p24_session_id=".$p24_session_id;
        $P[] = "p24_order_id=".$p24_order_id;
        $P[] = "p24_kwota=".$p24_kwota;
        $P[] = "p24_crc=".md5($p24_session_id."|". $p24_order_id."|". $p24_kwota."|".PLATNOSC_PRZELEWY24_CRC."");

        $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST,1);
        if(count($P)) curl_setopt($ch, CURLOPT_POSTFIELDS,join("&",$P));
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result=curl_exec ($ch);
        curl_close ($ch);

        $T = explode(chr(13).chr(10),$result);
        $res = false;
        foreach($T as $line) {
            $line = preg_replace("/[\n\r]/","",$line);
            if($line != "RESULT" and !$res) continue;
            if($res) $RET[] = $line;
            else $res = true;
        }
        return $RET;
    }

    //funkcja zwraca blad tranzakcji w serwisie Przelewy24
    public static function p24_tablicaBledow($kodBledu) {

        $tablica = array(
                   "err00" => "Nieprawidłowe wywołanie skryptu",
                   "err01" => "Nie uzyskano od sklepu potwierdzenia odebrania odpowiedzi autoryzacyjnej",
                   "err02" => "Nie uzyskano odpowiedzi autoryzacyjnej",
                   "err03" => "To zapytanie było już przetwarzane",
                   "err04" => "Zapytanie autoryzacyjne niekompletne lub niepoprawne",
                   "err05" => "Nie udało się odczytać konfiguracji sklepu internetowego",
                   "err06" => "Nieudany zapis zapytania autoryzacyjnego",
                   "err07" => "Inna osoba dokonuje płatności",
                   "err08" => "Nieustalony status połączenia ze sklepem",
                   "err09" => "Przekroczono dozwoloną liczbę poprawek danych",
                   "err10" => "Nieprawidłowa kwota transakcji!",
                   "err49" => "Zbyt wysoki wynik oceny ryzyka transakcji",
                   "err51" => "Nieprawidłowe wywołanie strony",
                   "err52" => "Błędna informacja zwrotna o sesji!",
                   "err53" => "Błąd transakcji !",
                   "err54" => "Niezgodność kwoty transakcji!",
                   "err55" => "Nieprawidłowy kod odpowiedzi!",
                   "err56" => "Nieprawidłowa karta",
                   "err57" => "Niezgodność flagi TEST!",
                   "err58" => "Nieprawidłowy numer sekwencji!",
                   "err59" => "Nieprawidłowa waluta transakcji!",
                   "err101" => "Błąd wywołania strony<br />W żądaniu transakcji brakuje któregoś z wymaganych parametrów lub pojawiła się niedopuszczalna wartość.",
                   "err102" => "Minął czas na dokonanie transakcji",
                   "err103" => "Nieprawidłowa kwota przelewu",
                   "err104" => "Transakcja oczekuje na potwierdzenie.",
                   "err105" => "Transakcja dokonana po dopuszczalnym czasie",
                   "err161" => "Żądanie transakcji przerwane przez użytkownika<br />Klient przerwał procedurę płatności wybierając przycisk Powrót na stronie wyboru formy płatności.",
                   "err162" => "Żądanie transakcji przerwane przez użytkownika<br />Klient przerwał procedurę płatności wybierając przycisk Rezygnuj na stronie z instrukcją płatności."
        );

        return $tablica[$kodBledu];

    }

    //funkcja zwraca blad tranzakcji w serwisie PayU
    public static function payu_tablicaBledow($kodBledu) {

        $tablica = array(
                  "100" => "brak parametru pos id",
                  "101" => "brak parametru session id",
                  "102" => "brak parametru ts",
                  "103" => "brak parametru sig",
                  "104" => "brak parametru desc",
                  "105" => "brak parametru client ip",
                  "106" => "brak parametru first name",
                  "107" => "brak parametru last name",
                  "108" => "brak parametru street",
                  "109" => "brak parametru city",
                  "110" => "brak parametru post code",
                  "111" => "brak parametru amount",
                  "112" => "błędny numer konta bankowego",
                  "113" => "brak parametru email",
                  "114" => "brak pnumeru telefonu",
                  "200" => "inny chwilowy błąd",
                  "201" => "inny chwilowy błąd bazy danych",
                  "202" => "POS o podanym identyfikatorze jest zablokowany",
                  "203" => "niedozwolona wartość pay_type dla danego pos_id",
                  "204" => "podana metoda płatności (wartość pay_type) jest chwilowo zablokowana dla danego pos_id, np. przerwa konserwacyjna bramki płatniczej",
                  "205" => "kwota transakcji mniejsza od wartości minimalnej",
                  "206" => "kwota transakcji większa od wartości maksymalnej",
                  "207" => "przekroczona wartość wszystkich transakcji dla jednego klienta w ostatnim przedziale czasowym",
                  "208" => "POS działa w wariancie ExpressPayment lecz nie nastapiła aktywacja tego wariantu współpracy (czekamy na zgode działu obsługi klienta)",
                  "209" => "błedny numer pos_id lub pos_auth_key",
                  "500" => "transakcja nie istnieje",
                  "501" => "brak autoryzacji dla danej transakcji",
                  "502" => "transakcja rozpoczęta wcześniej",
                  "503" => "autoryzacja do transakcji była juz przeprowadzana",
                  "504" => "transakcja anulowana wczesniej",
                  "505" => "transakcja przekazana do odbioru wcześniej",
                  "506" => "transakcja już odebrana",
                  "507" => "błąd podczas zwrotu środków do klienta",
                  "508" => "niewypełniony formularz",
                  "599" => "błędny stan transakcji, np. nie można uznać transakcji kilka razy lub inny, prosimy o kontakt",
                  "999" => "inny błąd krytyczny - prosimy o kontakt	"
        );

        return $tablica[$kodBledu];

    }

}

?>