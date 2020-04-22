<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

require_once('../tcpdf/config/lang/pol.php');
require_once('../tcpdf/tcpdf.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  if ( isset($_GET['id_poz']) && (int)$_GET['id_poz'] > 0 ) {

      $i18n = new Translator($db, '1');
      $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI', 'KLIENCI_PANEL', 'REKLAMACJE') );

      class MYPDF extends TCPDF {

          public function Footer() {
            global $tlumacz;
              $this->SetY(-15);
              $this->SetFont('helvetica', 'I', 8);
              $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
              $this->Cell(0, 0, $tlumacz['LISTING_STRONA'].' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
          }
      }

      $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

      $pdf->SetCreator('shopGold');
      $pdf->SetAuthor('shopGold');
      $pdf->SetTitle($tlumacz['NAGLOWEK_ZGLOSZENIE_REKLAMACJI']);
      $pdf->SetSubject($tlumacz['NAGLOWEK_ZGLOSZENIE_REKLAMACJI']);
      $pdf->SetKeywords($tlumacz['NAGLOWEK_ZGLOSZENIE_REKLAMACJI']);

      if (file_exists(KATALOG_SKLEPU . KATALOG_ZDJEC . '/'.PDF_PLIK_NAGLOWKA)) {
        $plik_naglowka = PDF_PLIK_NAGLOWKA;
        $szerokosc_pliku_naglowka = PDF_PLIK_NAGLOWKA_SZEROKOSC;
      } else {
        $plik_naglowka = '';
        $szerokosc_pliku_naglowka = '';
      }
      $pdf->SetHeaderData($plik_naglowka, $szerokosc_pliku_naglowka, DANE_NAZWA_FIRMY_SKROCONA, ADRES_URL_SKLEPU ."\n".INFO_EMAIL_SKLEPU);

      $pdf->SetFont('dejavusans', '', 6);

      $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
      $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

      $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

      $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
      $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
      $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

      $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

      $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

      // ---------------------------------------------------------

      $pdf->AddPage();

      $pdf->SetFont('dejavusans', '', 8);

      $text = PDFReklamacja::WydrukReklamacjiPDF($_GET['id_poz']);
      $pdf->writeHTML($text, true, false, false, false, '');

      $pdf->Output('reklamacja_'.$_GET['id_poz'].'_'.time().'.pdf', 'D');
      

  } else {
    exit;
  }

}

?>