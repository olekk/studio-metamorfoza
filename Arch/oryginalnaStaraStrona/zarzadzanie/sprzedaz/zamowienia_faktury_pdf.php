<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

require_once('../tcpdf/config/lang/pol.php');
require_once('../tcpdf/tcpdf.php');

// zainicjowanie klasy sprawdzajacej czy uzytkownik ma dostep do modulu
$prot = new Dostep($db);

if ($prot->wyswietlStrone) {

  $i18n = new Translator($db, '1');
  $tlumacz = $i18n->tlumacz( array('WYGLAD', 'KLIENCI', 'KLIENCI_PANEL', 'PRODUKT', 'FAKTURA') );

  class MYPDF extends TCPDF {
    public function Footer() {
      global $tlumacz;
      $this->SetY(-15);
      $this->SetFont('helvetica', 'I', 6);
      $this->Cell(0, 0, $tlumacz['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
    }
  }

  $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

  $pdf->SetCreator('shopGold');
  $pdf->SetAuthor('shopGold');
  $pdf->SetTitle('Zestawienie faktur');
  $pdf->SetSubject('Zestawienie faktur');
  $pdf->SetKeywords('Zestawienie faktur');

  if ( !empty($_POST['data_od']) && !empty($_POST['data_do']) ) {
       //
       $pdf->SetHeaderData('', '', 'Zestawienie faktur - ' . $filtr->process($_POST['data_od']) . '-' . $filtr->process($_POST['data_do']));
       //
    } else { 
       //
       $pdf->SetHeaderData('', '', 'Zestawienie faktur - ' . $_POST['data_wydruku_mc'] . '-' . $_POST['data_wydruku_rok']);
       //
  }

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

  $text = PDFFaktury::WydrukZestawieniaFakturPDF($filtr->process($_POST['data_wydruku_mc']).'-'.$filtr->process($_POST['data_wydruku_rok']), '2', '', $filtr->process($_POST['data_od']), $filtr->process($_POST['data_do']));
  $pdf->writeHTML($text, true, false, false, false, '');

  if ( !empty($_POST['data_od']) && !empty($_POST['data_do']) ) {
       //
       $pdf->Output('zestawienie_faktur_' . $filtr->process($_POST['data_od']) . '_' . $filtr->process($_POST['data_do']) . '.pdf', 'D');
       //
    } else { 
       //
       $pdf->Output('zestawienie_faktur_' . $filtr->process($_POST['data_wydruku_mc']) . '_' . $filtr->process($_POST['data_wydruku_rok']) . '.pdf', 'D');
       //
  }
  
}

?>