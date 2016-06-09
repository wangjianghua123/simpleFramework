<?php

/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: seccode.class.php 1059 2011-03-01 07:25:09Z monkey $
*/

class seccode {
/**
	 * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
	 */
	const REFRESH_GET_VAR='refresh';
	/**
	 * Prefix to the session variable name used by the action.
	 */
	
	/**
	 * @var integer how many times should the same CAPTCHA be displayed. Defaults to 3.
	 * A value less than or equal to 0 means the test is unlimited (available since version 1.1.2).
	 */
	public $testLimit = 3;
	/**
	 * @var integer the width of the generated CAPTCHA image. Defaults to 120.
	 */
	public $width = 72;
	/**
	 * @var integer the height of the generated CAPTCHA image. Defaults to 50.
	 */
	public $height = 30;
	/**
	 * @var integer padding around the text. Defaults to 2.
	 */
	public $padding = 2;
	/**
	 * @var integer the background color. For example, 0x55FF00.
	 * Defaults to 0xFFFFFF, meaning white color.
	 */
	public $backColor = 0xFFFFFF;
	/**
	 * @var integer the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
	 */
	public $foreColor = 0x2040A0;
	/**
	 * @var boolean whether to use transparent background. Defaults to false.
	 */
	public $transparent = false;
	/**
	 * @var integer the minimum length for randomly generated word. Defaults to 6.
	 */
	public $minLength = 6;
	/**
	 * @var integer the maximum length for randomly generated word. Defaults to 7.
	 */
	public $maxLength = 7;
	/**
	 * @var integer the offset between characters. Defaults to -2. You can adjust this property
	 * in order to decrease or increase the readability of the captcha.
	 * @since 1.1.7
	 **/
	public $offset = -2;
	/**
	 * @var string the TrueType font file. Defaults to Duality.ttf which is provided
	 * with the Yii release.
	 */
	public $fontFile;
	/**
	 * @var string the fixed verification code. When this is property is set,
	 * {@link getVerifyCode} will always return this value.
	 * This is mainly used in automated tests where we want to be able to reproduce
	 * the same verification code each time we run the tests.
	 * Defaults to null, meaning the verification code will be randomly generated.
	 * @since 1.1.4
	 */
	public $fixedVerifyCode;
	/**
	 * @var string the graphic extension that will be used to draw CAPTCHA image. Possible values
	 * are 'gd', 'imagick' and null. Null value means that fallback mode will be used: ImageMagick
	 * is preferred over GD. Default value is null.
	 * @since 1.1.13
	 */
	public $backend;

        protected function renderImageImagick($code)
	{
		$backColor=new ImagickPixel('#'.dechex($this->backColor));
		$foreColor=new ImagickPixel('#'.dechex($this->foreColor));

		$image=new Imagick();
		$image->newImage($this->width,$this->height,$backColor);

		if($this->fontFile===null)
			$this->fontFile=dirname(__FILE__).'/Duality.ttf';

		$draw=new ImagickDraw();
		$draw->setFont($this->fontFile);
		$draw->setFontSize(30);
		$fontMetrics=$image->queryFontMetrics($draw,$code);

		$length=strlen($code);
		$w=(int)($fontMetrics['textWidth'])-8+$this->offset*($length-1);
		$h=(int)($fontMetrics['textHeight'])-8;
		$scale=min(($this->width-$this->padding*2)/$w,($this->height-$this->padding*2)/$h);
		$x=10;
		$y=round($this->height*27/40);
		for($i=0; $i<$length; ++$i)
		{
			$draw=new ImagickDraw();
			$draw->setFont($this->fontFile);
			$draw->setFontSize((int)(rand(26,32)*$scale*0.8));
			$draw->setFillColor($foreColor);
			$image->annotateImage($draw,$x,$y,rand(-10,10),$code[$i]);
			$fontMetrics=$image->queryFontMetrics($draw,$code[$i]);
			$x+=(int)($fontMetrics['textWidth'])+$this->offset;
		}

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		$image->setImageFormat('png');
		echo $image;
	}
        
        protected function renderImageGD($code)
	{
		$image = imagecreatetruecolor($this->width,$this->height);
		$backColor = imagecolorallocate($image,
				(int)($this->backColor % 0x1000000 / 0x10000),
				(int)($this->backColor % 0x10000 / 0x100),
				$this->backColor % 0x100);
		imagefilledrectangle($image,0,0,$this->width,$this->height,$backColor);
		imagecolordeallocate($image,$backColor);

		if($this->transparent)
			imagecolortransparent($image,$backColor);

		$foreColor = imagecolorallocate($image,
				(int)($this->foreColor % 0x1000000 / 0x10000),
				(int)($this->foreColor % 0x10000 / 0x100),
				$this->foreColor % 0x100);
		if($this->fontFile === null)
			$this->fontFile = dirname(__FILE__) . '/Duality.ttf';

		$length = strlen($code);
		$box = imagettfbbox(30,0,$this->fontFile,$code);
		$w = $box[4] - $box[0] + $this->offset * ($length - 1);
		$h = $box[1] - $box[5];
		$scale = min(($this->width - $this->padding * 2) / $w,($this->height - $this->padding * 2) / $h);
		$x = 10;
		$y = round($this->height * 27 / 40);
		for($i = 0; $i < $length; ++$i)
		{
			$fontSize = (int)(rand(26,32) * $scale * 0.8);
			$angle = rand(-10,10);
			$letter = $code[$i];
			$box = imagettftext($image,$fontSize,$angle,$x,$y,$foreColor,$this->fontFile,$letter);
			$x = $box[2] + $this->offset;
		}
		imagecolordeallocate($image,$foreColor);
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Transfer-Encoding: binary');
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
	}

	function display($c) {
            $this->renderImageGD($c);
	}
}
?>