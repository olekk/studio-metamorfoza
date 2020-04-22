<?php
chdir('../'); 

// wczytanie ustawien inicjujacych system
require_once('ustawienia/init.php');

$blad = false;

if ( isset($_GET['id_poz']) && $_GET['id_poz'] != '' ) {

    $zapytanie = "SELECT customers_id FROM orders WHERE orders_id = '".(int)$_GET['id_poz']."' LIMIT 1";
    $sql = $GLOBALS['db']->open_query($zapytanie);

    if ((int)$GLOBALS['db']->ile_rekordow($sql) > 0) {
      $info = $sql->fetch_assoc();
      if ( (int)$info['customers_id'] == (int)$_SESSION['customer_id'] || (int)$info['customers_id'] == (int)$_SESSION['gosc_id'] ) {
        $zamowienie = new Zamowienie((int)$_GET['id_poz'], PDF_ZAMOWIENIE_SORTOWANIE_PRODUKTOW);
      } else {
        $blad = true;
      }
      unset($info);
    } else {
      $blad = true;
    }

    $GLOBALS['db']->close_query($sql);
    unset($zapytanie);

} else {
    $blad = true;
}

if ( $blad ) {
    Funkcje::PrzekierowanieURL('brak-strony.html'); 
}

unset($blad);

require_once('tcpdf/config/lang/pol.php');
require_once('tcpdf/tcpdf.php');  

$GLOBALS['tlumacz'] = array_merge( $i18n->tlumacz( array('KLIENCI', 'KLIENCI_PANEL', 'ZAMOWIENIE_REALIZACJA', 'KOSZYK') ), $GLOBALS['tlumacz'] );

class MYPDF extends TCPDF {

  public function Footer() {
      $this->SetY(-15);
      $this->SetFont('helvetica', 'I', 8);
      $this->Cell(0, 0, $GLOBALS['tlumacz']['WYGENEROWANO_W_PROGRAMIE'], 'T', false, 'L', 0, '', 0, false, 'T', 'M');
      $this->Cell(0, 0, $GLOBALS['tlumacz']['LISTING_STRONA'].' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
  }
  
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('shopGold');
$pdf->SetAuthor('shopGold');
$pdf->SetTitle($GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE']);
$pdf->SetSubject($GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE']);
$pdf->SetKeywords($GLOBALS['tlumacz']['DRUKUJ_ZAMOWIENIE']);

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

$text = PDFZamowienie::WydrukZmowieniaPDF($filtr->process($_GET['id_poz']));
$pdf->writeHTML($text, true, false, false, false, '');

$pdf->Output('zamowienie_'.$_GET['id_poz'].'_'.time().'.pdf', 'D');

?>