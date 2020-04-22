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
    $zamowienie = new Zamowienie((int)$_GET['id_poz']);
  } else {
    exit;
  }
  
  if ( isset($zamowienie->klient['id']) && !empty($zamowienie->klient['id']) ) {
  
  $i18n = new Translator($db, '1');
  $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI', 'KLIENCI_PANEL', 'PRODUKT', 'FAKTURA') );

  class MYPDF extends TCPDF {
    // Stopka
    public function Footer() {
      global $tlumacz;
      // Position at 15 mm from bottom
      $this->SetY(-15);
      // Set font
      $this->SetFont('helvetica', 'I', 6);
      // Page number
      $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
    }
  }

  // create new PDF document
  $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

  // set document information
  $pdf->SetCreator('shopGold');
  $pdf->SetAuthor('shopGold');
  $pdf->SetTitle($tlumacz['FAKTURA']);
  $pdf->SetSubject($tlumacz['FAKTURA']);
  $pdf->SetKeywords($tlumacz['FAKTURA']);

  // set default header data
  if (file_exists(KATALOG_SKLEPU . KATALOG_ZDJEC . '/'.PDF_PLIK_NAGLOWKA)) {
    $plik_naglowka = PDF_PLIK_NAGLOWKA;
    $szerokosc_pliku_naglowka = PDF_PLIK_NAGLOWKA_SZEROKOSC;
  } else {
    $plik_naglowka = '';
    $szerokosc_pliku_naglowka = '';
  }

  $pdf->SetHeaderData($plik_naglowka, $szerokosc_pliku_naglowka, DANE_NAZWA_FIRMY_SKROCONA, ADRES_URL_SKLEPU ."\n".INFO_EMAIL_SKLEPU);

  $pdf->SetFont('dejavusans', '', 6);

  // set header and footer fonts
  $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', '6'));
  $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

  // set default monospaced font
  $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

  //set margins
  $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
  $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
  $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

  //set auto page breaks
  $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

  //set image scale factor
  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

  //set some language-dependent strings
  //$pdf->setLanguageArray($l);

  // ---------------------------------------------------------


  $pdf->AddPage();

  $pdf->SetFont('dejavusans', '', 8);

  $text = PDFFakturaProforma::WydrukFakturyPDF($filtr->process($_GET['id_poz']), '', '1', '');
  $pdf->writeHTML($text, true, false, false, false, '');

  //Close and output PDF document
  $pdf->Output('faktura_proforma_'.$filtr->process($_GET['id_poz']).'_'.time().'.pdf', 'D');
  
  }

}  

?>