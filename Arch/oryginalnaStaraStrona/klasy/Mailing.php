<?php

class Mailing {

  /*
  **** klasa do obslugi wysylania emaili
  */

  public function Mailing() {

    require_once('inne/simple_html_dom.php');
    $this->email = new PHPMailer(true);

    if ( EMAIL_SPOSOB_WYSLANIA == 'smtp' ) {
        $this->email->IsSMTP();                                                                            // czy jest mozliowsc SMTP
        $this->email->Host       = EMAIL_ADRES_SERWERA_SMTP;                                               // SMTP server
        $this->email->SMTPDebug  = 0;                                                                      // wlacz SMTP debug
                                                                                                           // 1 = bledy i wiadomosci
                                                                                                           // 2 = tylko wiadomosci
        if ( EMAIL_AUTENTYKACJA_SERWERA_SMTP == 'tak' ) {
            $this->email->SMTPAuth   = true;                                                               // wlacz SMTP authentication
            $this->email->Port       = EMAIL_PORT_SERWERA_SMTP;                                            // port SMTP na serwerze Gmaila
            $this->email->Username   = EMAIL_LOGIN_SERWERA_SMTP;                                           // SMTP uzytkownik
            $this->email->Password   = EMAIL_HASLO_SERWERA_SMTP;                                           // SMTP haslo
        }

        if ( EMAIL_SERWER_SMTP_SSL == 'tak' ) {
            $this->email->SMTPSecure = 'tls';
        }
    }
    $this->email->CharSet    = "UTF-8";
    $this->email->Encoding    = '8bit';

  }

  public function wyslijEmail($nadawca_email, $nadawca_nazwa, $adresat_email, $adresat_nazwa, $cc, $temat, $tekst, $szablon, $jezyk, $zalaczniki, $odpowiedz_email = '', $odpowiedz_nazwa = '', $zalacznikiCiag = array()) {

    try {

      $this->email->SetFrom($nadawca_email, $nadawca_nazwa);

      if ( $odpowiedz_email != '' &&  $odpowiedz_nazwa != '' ) {
        $this->email->AddReplyTo($odpowiedz_email, $odpowiedz_nazwa);
      } else {
        $this->email->AddReplyTo($nadawca_email, $nadawca_nazwa);
      }

      $this->email->AddAddress($adresat_email, $adresat_nazwa);

      if($cc != "") {
        $ccOdbiorcy = explode(",",$cc); 
        foreach($ccOdbiorcy as $ccOdbiorca) { 
          $this->email->AddBCC($ccOdbiorca);  
        } 
      }

      $this->email->Subject = $temat;

      if ( count($zalaczniki) > 0 ) {

        if ( isset($zalaczniki['file']) ) {
        
            foreach(array_keys($zalaczniki['file']['name']) as $key) {
            
               $source = $zalaczniki['file']['tmp_name'][$key];
               $filename = $zalaczniki['file']['name'][$key];
               $this->email->AddAttachment($source, $filename);
               
            }
            
        } else {
        
            foreach($zalaczniki as $zalacznik) {
            
               // jezeli pliki sklepu
               if ( !is_array($zalacznik) ) {
                   $filename = KATALOG_SKLEPU . '' . $zalacznik;
                   if (file_exists($filename)) {
                        $this->email->AddAttachment($filename);
                   }
               }
               
               // jezeli pliki formularz
               if ( is_array($zalacznik) ) {
                   if ( isset($zalacznik['tmp_name']) && $zalacznik['name'] ) {
                       $source = $zalacznik['tmp_name'];
                       $filename = $zalacznik['name'];
                       $this->email->AddAttachment($source, $filename);
                   }
               }
               
            }
            
        }
        
      }
      
      if ( count($zalacznikiCiag) > 0 ) {
        for ($v = 0; $v < count($zalacznikiCiag); $v++ ) {
           $this->email->AddStringAttachment($zalacznikiCiag[$v]['ciag'], $zalacznikiCiag[$v]['plik'], "base64", $zalacznikiCiag[$v]['typ']);
        }
      }

      if ( $tekst != '' ) {

        $tekst = $this->PodstawSzablon($tekst, $jezyk, $szablon);

        // zamiana adresu strony na klikalny link w tekscie
        $tekst = $this->UtworzLinkAdresu($tekst);

      } else {
        $tekst = '<br />';
      }

      $this->email->MsgHTML($tekst);

      $this->email->Send();

      $komunikat  = 'Wiadomość została wysłana !!!<br />';
      $komunikat .= 'Adresat wiadomości: <b>' . $adresat_nazwa . '</b><br />';
      $komunikat .= 'Adres email       : <b>' . $adresat_email . '</b><br />';

    }
    
    catch (phpmailerException $e) {
      $komunikat   = '<span class="czerwony">Wiadomość nie została wysłana !!!</span><br />';
      $komunikat  .= $e->errorMessage();
    } catch (Exception $e) {
      $komunikat   = '<span class="czerwony">Wiadomość nie została wysłana !!!</span><br />';
      $komunikat  .= $e->getMessage(); 
    }

    return $komunikat;
  }

  // funkcja zamieniajaca link na podstac klikalna ************************************************************
  public function UtworzLinkAdresu($tekst) {
      $tekst = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $tekst);
      $tekst = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $tekst);
      $tekst = preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $tekst);
      return $tekst;
  }

  // funkcja podstawiajaca szablon emaila ********************************************************
  public function PodstawSzablon($tekst, $jezyk, $szablon = '1') {

      $zapytanie = "select s.template_id, sz.description from email_templates s LEFT JOIN email_templates_description sz ON sz.template_id = s.template_id AND sz.language_id = '".$jezyk."' WHERE s.template_id = '".$szablon."'";

      $sql = $GLOBALS['db']->open_query($zapytanie);

      if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
        $info = $sql->fetch_assoc();
        $html = $info['description'];
      }
      $GLOBALS['db']->close_query($sql);

      $html = str_replace('{CONTENT}',$tekst, $html);
      unset($info,$zapytanie);

      $html = str_replace('{ADRES_URL_SKLEPU}', ADRES_URL_SKLEPU, $html);

      $zmienne = "SELECT * FROM settings WHERE type = 'firma' OR type = 'kontakt' OR type = 'sklep' OR type = 'email'";
      $sql = $GLOBALS['db']->open_query($zmienne);
      while ($info = $sql->fetch_assoc()) {
        $ciag_zamieniany = '{'.$info['code'].'}';
        $ciag_wstawiany = $info['value'];
        $html = str_replace($ciag_zamieniany, $ciag_wstawiany, $html);
      }
      $GLOBALS['db']->close_query($sql);
      unset($info,$zmienne);

      // generowanie zalacznikow do maila
      if ( EMAIL_SPOSOB_WYLANIA_GRAFIK == 'nie' ) {
        $html = $this->UtworzSciezkeObrazow($html);
      } elseif ( EMAIL_SPOSOB_WYLANIA_GRAFIK == 'tak' ) {
        $html = $this->UtworzAdresObrazow($html);
      }

      return $html;
  }

  // funkcja zamieniajaca linki do obrazkow na sciezke ********************************************************
  public function UtworzSciezkeObrazow($tekst) {

      $adres_url = '(^\/' . KATALOG_ZDJEC . '|^'.str_replace('/','\/',ADRES_URL_SKLEPU).'\/' . KATALOG_ZDJEC . ')';
      $sciezka = KATALOG_SKLEPU . KATALOG_ZDJEC;

      $html = str_get_html($tekst);
      if ( empty($html) ) {
          return $html;
      } else {
          foreach($html->find('img') as $element) {
            $sciezka_obrazka = preg_replace('#'.$adres_url.'#i', $sciezka, $element->src);
            if ( is_file($sciezka_obrazka) ) {
              $element->src = $sciezka_obrazka;
            }
          }
      }

      return $html;
  }


  // funkcja zamieniajaca sciezki do obrazkow na linki ********************************************************
  public function UtworzAdresObrazow($tekst) {

      $sciezka = '(^\/' . KATALOG_ZDJEC . '|^'.str_replace('/','\/',KATALOG_SKLEPU). KATALOG_ZDJEC . ')';
      $adres_url = ADRES_URL_SKLEPU.'/' . KATALOG_ZDJEC;

      $html = str_get_html($tekst);
      if ( empty($html) ) {
          return $html;
      } else {
          foreach($html->find('img') as $element) {
            $sciezka_obrazka = preg_replace('#'.$sciezka.'#i', $adres_url, $element->src);
            $element->src = $sciezka_obrazka;
          }
      }

      return $html;
  }

}