<?php

class Miniaturki {

  public $Thumbsize;
  public $Thumbheight;
  public $Thumbwidth;
  public $Inflate;
  public $Quality;
  public $Backgroundcolor;
  public $Chmodlevel;
  public $Thumblocation;
  public $Thumbprefix;
  public $Thumbfilename;
  public $Copyright;
  public $Copyrighttext;
  public $Copyrightposition;
  public $Copyrightfonttype;
  public $Copyrightfontsize;
  public $Copyrighttextcolor;
  public $Square;
  public $Keeptransparency;
  public $Watermark;
  public $Watermarkfilename;
  public $Watermarktransparency;
  public $Watermarkposition;

  private $image;
  private $im;
  private $thumb;
  private $newimage;
  private $size;
  private $thumbx;
  private $thumby;
  private $copyrx;
  private $copyry;

  public function __construct() {
  
    $this->Thumbsize              = 150;
    $this->Thumbheight            = 0;
    $this->Thumbwidth             = 0;
    $this->Inflate                = false;
    $this->Quality                = '100';
    $this->Backgroundcolor        = (( MINIATURKA_KOLOR_TLA != '') ? '#'.MINIATURKA_KOLOR_TLA : '');
    $this->Chmodlevel             = '0644';
    $this->Thumblocation          = '';
    $this->Thumbprefix            = '';
    $this->Thumbfilename          = '';
    $this->Copyright              = ( TEKST_COPYRIGHT_POKAZ == 'tak' ? true : false );
    $this->Copyrighttext          = TEKST_COPYRIGHT_TRESC;
    $this->Copyrightposition      = TEKST_COPYRIGHT_POZYCJA;
    $this->Copyrightfonttype      = 'programy/font/'.TEKST_COPYRIGHT_FONT;
    $this->Copyrightfontsize      = TEKST_COPYRIGHT_FONT_ROZMIAR;
    $this->Copyrighttextcolor     = (( TEKST_COPYRIGHT_FONT_KOLOR != '') ? '#'.TEKST_COPYRIGHT_FONT_KOLOR : '');
    //$this->Square                 = ( MINIATURKA_WYROWNAJ_ROZMIAR == 'tak' ? true : false );
    $this->Square                 = true;
    $this->Keeptransparency       = true;
    $this->Watermark              = ( OBRAZ_COPYRIGHT_POKAZ == 'tak' ? true : false );
    $this->Watermarkfilename      = KATALOG_ZDJEC . '/watermark-maly.png';
    $this->Watermarkposition      = OBRAZ_COPYRIGHT_POZYCJA;
    $this->Watermarktransparency  = OBRAZ_COPYRIGHT_OPACITY;
   
  }

  public function __destruct() {
  
    if(is_resource($this->im)) imagedestroy($this->im);
    if(is_resource($this->thumb)) imagedestroy($this->thumb);
    if(is_resource($this->newimage)) imagedestroy($this->newimage);
  
  }

  //tworzenie miniaturki
  public function Createthumb($filename="unknown",$output="screen") {

    if ( pathinfo($filename, PATHINFO_EXTENSION) == 'swf' || pathinfo($filename, PATHINFO_EXTENSION) == 'SWF' ) {
      return;
    }
    if (is_array($filename) && $output=="file") {
      foreach ($filename as $name) {
        $this->image=$name;
        $this->thumbmaker();
        $this->savethumb();
      }
    } else {
      if ( !is_file($filename) ) {
          return;
      }
      $this->image=$filename;
      $this->thumbmaker();
      if ($output=="file") {
        $this->savethumb();
      } else {
        $this->displaythumb();
      }
    }

  }

  //Dodanie wszystkich efektow do tworzonej miniaturki
  private function thumbmaker() {

    if($this->loadimage()) {
      $this->createemptythumbnail();
      imagecopyresampled($this->thumb,$this->im,0,0,0,0,$this->thumbx,$this->thumby,imagesx($this->im),imagesy($this->im));
      if ($this->Square) {$this->square(); }
      if ($this->Copyright) { $this->addcopyright(); }
      if ($this->Watermark) { $this->addwatermark(); }
    }

  }

  //Zaladowanie obrazka do pamieci
  private function loadimage() {

    if (is_resource($this->im)) {
      return true;
    } else if (file_exists($this->image)) {
      $this->size=GetImageSize($this->image);
      ini_set('memory_limit', -1);

      switch($this->size[2]) {
        case 1:
          if (imagetypes() & IMG_GIF) {$this->im=imagecreatefromgif($this->image);return true;} else {$this->invalidimage('Brak obslugi GIF');return false;}
          break;
        case 2:
          if (imagetypes() & IMG_JPG) {$this->im=imagecreatefromjpeg($this->image);$this->Keeptransparency=false;return true;} else {$this->invalidimage('Brak obslugi JPG');return false;}
          break;
        case 3:
          if (imagetypes() & IMG_PNG) {$this->im=imagecreatefrompng($this->image);return true;} else {$this->invalidimage('Brak obslugi PNG');return false;}
          break;
        default:
          $this->invalidimage('Nieznany typ pliku');
          return false;
      }
      ini_restore('memory_limit');
    } else {
      $this->invalidimage('Brak pliku');
      return false;
    }

  }

  //Obsluga bledow  - wyswietlonych w miejscu obrazka
  private function invalidimage($message) {

    $this->thumb=imagecreate(80,75);
    $black=imagecolorallocate($this->thumb,0,0,0);$yellow=imagecolorallocate($this->thumb,255,255,0);
    imagefilledrectangle($this->thumb,0,0,80,75,imagecolorallocate($this->thumb,255,0,0));
    imagerectangle($this->thumb,0,0,79,74,$black);imageline($this->thumb,0,20,80,20,$black);
    imagefilledrectangle($this->thumb,1,1,78,19,$yellow);imagefilledrectangle($this->thumb,27,35,52,60,$yellow);
    imagerectangle($this->thumb,26,34,53,61,$black);
    imageline($this->thumb,27,35,52,60,$black);imageline($this->thumb,52,35,27,60,$black);
    imagestring($this->thumb,1,5,5,$message,$black);

  }

  //utworzenie pustej miniaturki
  private function createemptythumbnail() {
  
    $thumbsize=$this->Thumbsize;
    $thumbwidth=$this->Thumbwidth;
    $thumbheight=$this->Thumbheight;

    if ($thumbsize==0) { 
      $thumbsize=9999;
      $thumbwidth=0;
      $thumbheight=0; 
    }

    if (!$this->Inflate) {
      if ($thumbsize > $this->size[0] && $thumbsize > $this->size[1]) {
        $thumbsize=max($this->size[0],$this->size[1]);
      }
      if ($thumbheight > $this->size[1]) {
        $thumbheight=$this->size[1];
      }
      if ($thumbwidth > $this->size[0]) {
        $thumbwidth=$this->size[0];
      }
    }

    $ratio = $this->size[1] / $this->size[0];

    if ( $thumbheight > 0 && $thumbwidth > 0 ) {

      $rx = $this->size[0] / $thumbwidth;
      $ry = $this->size[1] / $thumbheight;

      if ($rx < $ry) {
        $thumbwidth = intval($thumbheight / $ratio);
      } else {
        $thumbheight = intval($thumbwidth * $ratio);
      }

      $this->thumb=imagecreatetruecolor($thumbwidth,$thumbheight);

    } else if ( $thumbheight > 0 ) {

      $ratio = $thumbheight / $this->size[1];
      $thumbwidth = intval($this->size[0] * $ratio);

      $this->thumb=imagecreatetruecolor($thumbwidth,$thumbheight);

    } else if ( $thumbwidth > 0 ) {

      $ratio = $thumbwidth / $this->size[0];
      $thumbheight = intval($this->size[1] * $ratio);
      $this->thumb=imagecreatetruecolor($thumbwidth,$thumbheight);

    } else {

      $x1=$thumbsize;
      $x2=ceil($this->size[0]/($this->size[1]/$thumbsize));
      $y1=ceil($this->size[1]/($this->size[0]/$thumbsize));
      $y2=$thumbsize;
      if ($this->size[0]>$this->size[1]) { $this->thumb=imagecreatetruecolor($x1,$y1);} else {$this->thumb=imagecreatetruecolor($x2,$y2);}

    }

    $this->thumbx=imagesx($this->thumb);$this->thumby=imagesy($this->thumb);

    if ($this->Keeptransparency) {
      $alpha=imagecolortransparent($this->im);
      $palletsize = imagecolorstotal($this->im);
      if ($alpha >= 0  && $alpha < $palletsize) {
        $color=imagecolorsforindex($this->im,$alpha);
        $color_index=imagecolorallocate($this->thumb,$color['red'],$color['green'],$color['blue']);
        imagefill($this->thumb,0,0,$color_index);
        imagecolortransparent($this->thumb,$color_index);
      } else {
        imagealphablending($this->thumb,false);
        $color_alpha=imagecolorallocatealpha($this->im,0,0,0,127);
        imagefill($this->thumb,0,0,$color_alpha);
        imagesavealpha($this->thumb,true);
        imagealphablending($this->thumb,true);
      }
    } else {
      if ( $this->Backgroundcolor != '' ) {
        imagefilledrectangle($this->thumb,0,0,$this->thumbx,$this->thumby,imagecolorallocate($this->thumb,hexdec(substr($this->Backgroundcolor,1,2)),hexdec(substr($this->Backgroundcolor,3,2)),hexdec(substr($this->Backgroundcolor,5,2))));
      } else {
        imagefilledrectangle($this->thumb,0,0,$this->thumbx,$this->thumby,imagecolorallocate($this->thumb,hexdec('FF'),hexdec('FF'),hexdec('FF')));
      }
    }

    if ( $this->Thumbsize != 0 && $this->Thumbwidth == 0 && $this->Thumbheight == 0 && $this->Square ) {
      $this->copyrx   = $this->Thumbsize;
      $this->copyry  = $this->Thumbsize;
    } elseif ( $this->Thumbsize != 0 && $this->Thumbwidth == 0 && $this->Thumbheight == 0 && !$this->Square ) {
      $this->copyrx   = $this->thumbx;
      $this->copyry  = $this->thumby;
    } elseif ( $this->Thumbwidth != 0 && $this->Thumbheight != 0 && $this->Square ) {
      $this->copyrx   = $this->Thumbwidth;
      $this->copyry  = $this->Thumbheight;
    } elseif ( $this->Thumbwidth != 0 && $this->Thumbheight != 0 && !$this->Square ) {
      $this->copyrx   = $this->thumbx;
      $this->copyry  = $this->thumby;
    }

  }

  //Zapisanie miniaturki
  private function savethumb() {
  
    if ($this->Thumbfilename!='') {
      $this->image=$this->Thumbfilename;
    }   
    switch($this->size[2]) {
      case 1:
        imagegif($this->thumb,$this->Thumblocation.$this->Thumbprefix.basename($this->image));
        break;
      case 2:
        imagejpeg($this->thumb,$this->Thumblocation.$this->Thumbprefix.basename($this->image),$this->Quality);
        break;
      case 3:
        imagepng($this->thumb,$this->Thumblocation.$this->Thumbprefix.basename($this->image));
        break;
    }   
    if ($this->Chmodlevel!='' && is_resource($this->im)) {chmod($this->Thumblocation.$this->Thumbprefix.basename($this->image),octdec($this->Chmodlevel));}
    if(is_resource($this->im)) imagedestroy($this->im);
    imagedestroy($this->thumb);

  }

  //Wyswietlenie miniaturki na ekranie
  private function displaythumb() {

    switch($this->size[2]) {
      case 1:
        header("Content-type: image/gif");imagegif($this->thumb);
        break;
      case 2:
        header("Content-type: image/jpeg");imagejpeg($this->thumb,NULL,$this->Quality);
        break;
      case 3:
        header("Content-type: image/png");imagepng($this->thumb);
        break;
    }
    imagedestroy($this->im);
    imagedestroy($this->thumb);
    exit;

  }

  //Dodanie tekstu znaku wodnego
  private function addcopyright() {

    if ($this->Copyrightfonttype=='') {
      $widthx=imagefontwidth($this->Copyrightfontsize)*strlen($this->Copyrighttext);
      $heighty=imagefontheight($this->Copyrightfontsize);
      $fontwidth=imagefontwidth($this->Copyrightfontsize);
    } else {    
      $dimensions=imagettfbbox($this->Copyrightfontsize,0,$this->Copyrightfonttype,$this->Copyrighttext);
      $widthx=$dimensions[2];$heighty=$dimensions[5];
      $dimensions=imagettfbbox($this->Copyrightfontsize,0,$this->Copyrightfonttype,'W');
      $fontwidth=$dimensions[2];
    }

    $cpos=explode(' ',str_replace('%','',$this->Copyrightposition));

    if ( count($cpos) > 1 ) {
      $cposx=floor(min(max($this->copyrx*($cpos[0]/100)-0.5*$widthx,$fontwidth),$this->copyrx-$widthx-0.5*$fontwidth));
      $cposy=floor(min(max($this->copyry*($cpos[1]/100)-0.5*$heighty,$heighty),$this->copyry-$heighty*1.5));
    } else {
      $cposx = $fontwidth;
      $cposy = $this->copyry;
    }     
    if ($cposy <= 15 ) { $cposy = 15; }
    if ($cposy >= $this->copyry ) { $cposy = $this->copyry - 10; }

    if ($this->Copyrighttextcolor=='') {
      $colors=array();
      for ($i=$cposx;$i<($cposx+$widthx);$i++) {
        $indexis=ImageColorAt($this->thumb,$i,$cposy+0.5*$heighty);
        $rgbarray=ImageColorsForIndex($this->thumb,$indexis);
        array_push($colors,$rgbarray['red'],$rgbarray['green'],$rgbarray['blue']);
      }
      if (array_sum($colors)/count($colors)>180) {
        if ($this->Copyrightfonttype=='')
          imagestring($this->thumb,$this->Copyrightfontsize,$cposx,$cposy,$this->Copyrighttext,imagecolorallocate($this->thumb,0,0,0));
        else
          imagettftext($this->thumb,$this->Copyrightfontsize,0,$cposx,$cposy,imagecolorallocate($this->thumb,0,0,0),$this->Copyrightfonttype,$this->Copyrighttext);
      } else {
        if ($this->Copyrightfonttype=='')
          imagestring($this->thumb,$this->Copyrightfontsize,$cposx,$cposy,$this->Copyrighttext,imagecolorallocate($this->thumb,255,255,255));
        else
          imagettftext($this->thumb,$this->Copyrightfontsize,0,$cposx,$cposy,imagecolorallocate($this->thumb,255,255,255),$this->Copyrightfonttype,$this->Copyrighttext);       
      }
    } else {
      if ($this->Copyrightfonttype=='')
        imagestring($this->thumb,$this->Copyrightfontsize,$cposx,$cposy,$this->Copyrighttext,imagecolorallocate($this->thumb,hexdec(substr($this->Copyrighttextcolor,1,2)),hexdec(substr($this->Copyrighttextcolor,3,2)),hexdec(substr($this->Copyrighttextcolor,5,2))));
      else
        imagettftext($this->thumb,$this->Copyrightfontsize,0,$cposx,$cposy,imagecolorallocate($this->thumb,hexdec(substr($this->Copyrighttextcolor,1,2)),hexdec(substr($this->Copyrighttextcolor,3,2)),hexdec(substr($this->Copyrighttextcolor,5,2))),$this->Copyrightfonttype,$this->Copyrighttext);       
    }
    
  }

  //Utworzenie kwadratowej miniaturki
  private function square() {
  
    if ( $this->Thumbsize != 0 && $this->Thumbwidth == 0 && $this->Thumbheight == 0 ) {
      $squaresizex = $this->Thumbsize;
      $squaresizey = $this->Thumbsize;
    } elseif ( $this->Thumbwidth != 0 && $this->Thumbheight != 0 ) {
      $squaresizex = $this->Thumbwidth;
      $squaresizey = $this->Thumbheight;
    }

    $this->newimage=imagecreatetruecolor($squaresizex,$squaresizey);

    if ( $this->Backgroundcolor != '' ) {
      imagefilledrectangle($this->newimage,0,0,$squaresizex,$squaresizey,imagecolorallocate($this->newimage,hexdec(substr($this->Backgroundcolor,1,2)),hexdec(substr($this->Backgroundcolor,3,2)),hexdec(substr($this->Backgroundcolor,5,2))));
    } else {
      $indexis  = ImageColorAt($this->thumb,$this->thumbx-1,$this->thumby-1);
      $rgbarray = ImageColorsForIndex($this->thumb,$indexis);
      imagefilledrectangle($this->newimage,0,0,$squaresizex,$squaresizey,imagecolorallocate($this->newimage,$rgbarray['red'],$rgbarray['green'],$rgbarray['blue']));
    }

    $centerx=floor(($squaresizex-$this->thumbx)/2);
    $centery=floor(($squaresizey-$this->thumby)/2);
    imagecopy($this->newimage,$this->thumb,$centerx,$centery,0,0,$this->thumbx,$this->thumby);
    imagedestroy($this->thumb);
    $this->thumb=imagecreatetruecolor($squaresizex,$squaresizey);
    imagecopy($this->thumb,$this->newimage,0,0,0,0,$squaresizex,$squaresizey);
    imagedestroy($this->newimage);

  }

  //Utworzenie znaku wodnego
  private function addwatermark() {

    if( $this->size[2] == 2 || $this->size[2] == 3 ) {

      $waterMarkInfo   = getimagesize($this->Watermarkfilename);
      $waterMarkWidth  = $waterMarkInfo[0];
      $waterMarkHeight = $waterMarkInfo[1];

      $differenceX = $this->copyrx - $waterMarkWidth;
      $differenceY = $this->copyry - $waterMarkHeight;

      $wpos=explode(' ',str_replace('%','',$this->Watermarkposition));

      if ( count($wpos) > 1 ) {
        $wposx = floor(( $this->copyrx -$waterMarkWidth ) * ($wpos[0]/100));
        $wposy = floor(( $this->copyry -$waterMarkHeight ) * ($wpos[1]/100));
      } else {
        $wposx = 0;
        $wposy = 0;
      }     

      $finalWaterMarkImage = imagecreatefrompng($this->Watermarkfilename);
      $finalWaterMarkWidth = imagesx($finalWaterMarkImage);
      $finalWaterMarkHeight = imagesy($finalWaterMarkImage);

      $this->imagecopymerge_alpha(
        $this->thumb,
        $finalWaterMarkImage,
        $wposx,
        $wposy,
        0,
        0,
        $finalWaterMarkWidth,
        $finalWaterMarkHeight,
        intval($this->Watermarktransparency)
      );

      imagedestroy($finalWaterMarkImage);

    }
  }

  private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    if(!isset($pct)){
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx( $src_im );
    $h = imagesy( $src_im );
    // Turn alpha blending off
    imagealphablending( $src_im, false );
    // Find the most opaque pixel in the image (the one with the smallest alpha value)
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
    for( $y = 0; $y < $h; $y++ ){
        $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
        if( $alpha < $minalpha ){
            $minalpha = $alpha;
        }
    }
    //loop through image pixels and modify alpha for each
    for( $x = 0; $x < $w; $x++ ){
        for( $y = 0; $y < $h; $y++ ){
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat( $src_im, $x, $y );
            $alpha = ( $colorxy >> 24 ) & 0xFF;
            //calculate new alpha
            if( $minalpha !== 127 ){
                $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
            } else {
                $alpha += 127 * $pct;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
            //set pixel with the new color + opacity
            if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
                return false;
            }
        }
    }
    // The image copy
    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
} 

}
?>