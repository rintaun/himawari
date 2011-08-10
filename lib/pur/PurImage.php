<?php

/**
 * General static methods around image manipulation.
 */
class PurImage{
	
	/**
	 * Compare two images or resources and return true if they strictly match.
	 * 
	 * @return boolean True on match
	 * @param mixed $image1 Path or resource of first image
	 * @param mixed $image2 Path or resource of second image
	 */
	public static function compare($image1,$image2){
		if(is_string($image1)){
			if(!is_readable($image1)) throw new Exception('Expected image file not readable: '.$image1);
			$image1 = self::resource($image1);
			$destroy1 = true;
		}else if(!is_resource($image1)){
			throw new Exception('Source image must be a string path or an image resource');
		}
		if(is_string($image2)){
			if(!is_readable($image2)) throw new Exception('Expected image file not readable: '.$image2);
			$image2 = self::resource($image2);
			$destroy2 = true;
		}else if(!is_resource($image2)){
			throw new Exception('Expected image must be a string path or an image resource');
		}
		$x1 = imagesx($image1);
		$y1 = imagesy($image1);
		if($x1!=imagesx($image2)) return false;
		if($y1!=imagesy($image2)) return false;
		for($x=0;$x<$x1;$x++){
			for($y =0;$y<$y1;$y++){
				//echo $x.' '.$y."\n";
				$sourceRgb = imagecolorat($image1, $x, $y);
				$expectedRgb = imagecolorat($image2, $x, $y);
				/*
				if($sourceRgb!=$expectedRgb){
					echo "\n";
					$sourceColors = imagecolorsforindex($image1,$sourceRgb);
					print_r($sourceColors);
					echo "\n";
					$expectedColors = imagecolorsforindex($image2,$expectedRgb);
					print_r($expectedColors);
					echo "\n";
				}
				*/
				if($sourceRgb!=$expectedRgb) return false;
			}
		}
		if(isset($destroy1)) imagedestroy($image1);
		if(isset($destroy2)) imagedestroy($image2);
		return true;
	}
	
	/**
	 * Image layer-based composition, the method take an argument as an argument and
	 * return a resource or an image path.
	 * 
	 * Options may include:
	 * - *destination*: Image path where the image should be generated, if not provided, the method return an image resource
	 * - *source*: Image path or resource
	 * - *background*: Background color and alpha. Takes several forms like "#000000 50" for black with 50% alpha
	 * - *width*: Canvas width of the generated image or resource; "100" and "80+top" (with top defined as 20) for 100 pixels
	 * - *height*: Canvas height of the generated image or resource; "50" and "25+top" (with top defined as 20) for 75 pixels
	 * - *top*: Offset from the top of the source in pixel
	 * - *left*: Offset from the left of the source in pixel
	 * - *bottom*: Offset from the bottom of the source in pixel
	 * - *right*: Offset from the right of the source in pixel
	 * - *resize*: Redimension the source image;
	 *   May be an array or a string; 
	 *   "100x75", "array(100,75)" and "array(width=>100,height=>75)" for 100 width by 75 height pixels
	 *   "x75", "*x75", "array(null,75)" and "array(height=>75)" for proportianal resize with height of 75 pixels
	 * - *rotate*: Rotate the source in degree
	 * - *flip*: Flip the source horizontally or vertically
	 * - *mask*: Mask applied to the source after the resize, rotate and flip effects
	 * - *masks*: List of masks
	 * - *layer*: Single layer applied on top of the source image; May contains all the root properties
	 * - *layers*: List of layers
	 * 
	 * @return mixed Resource or image path
	 * @param array $options Configuration array
	 */
	public static function compose(array $options){
		$canvas = self::resource(empty($options['destination'])?null:$options['destination'],$options);
	}
	
	public static function hex2rgb($hex){
		if(strlen($hex)==6){
			list($r, $g, $b) = array($hex[0].$hex[1],$hex[2].$hex[3],$hex[4].$hex[5]);
		}elseif (strlen($hex) == 3){
			list($r, $g, $b) = array($hex[0].$hex[0], $hex[1].$hex[1], $hex[2].$hex[2]);
		}
		return array(
			'red'=>hexdec($r),
			'green'=>hexdec($g),
			'blue'=>hexdec($b),
		);
	}
	
	public static function mask($source,$mask,$destination=null){
		if(is_string($source)){
			$source = self::resource($source);
		}else if(!is_resource($source)){
			throw new Exception('Source must be a string path or an image resource');
		}
		if(is_string($mask)){
			$mask = self::resource($mask);
		}else if(!is_resource($mask)){
			throw new Exception('Source must be a string path or an image resource');
		}
		$sourceX = imagesx($source);
		$sourceY = imagesy($source);
		if(empty($destination)){
			$destination = self::resource(array('width'=>$sourceX,'height'=>$sourceY));
		}else if(is_string($destination)){
			$destination = self::resource($destination);
		}else if(!is_resource($destination)){
			throw new Exception('Destination must be a string path or an image resource or null');
		}
		$colors = array();
		for($x=0;$x<$sourceX;$x++){
			for($y=0;$y<$sourceY;$y++){
				$thres = abs(self::toAlpha($mask, $x, $y)-127);
				$rgb = imagecolorat($source, $x, $y);
				$rgbs = imagecolorsforindex($source,$rgb);
				$alpha = $rgbs['alpha']+$thres;
				if($alpha>127) $alpha = 127;
				$colors[$rgb] = imagecolorallocatealpha($source, $rgbs['red'], $rgbs['green'], $rgbs['blue'], $alpha);
				imagesetpixel($destination, $x, $y, $colors[$rgb]);
			}
		}
		return $destination;
	}
	
	/**
	 * Create an image resource base on the provided file path.
	 * 
	 * Options may include:
	 * - *width*: Canvas width of the generated image or resource; "100" and "80+top" (with top defined as 20) for 100 pixels
	 * - *height*: Canvas height of the generated image or resource; "50" and "25+top" (with top defined as 20) for 75 pixels
	 * - *top*: Offset from the top of the source in pixel
	 * - *left*: Offset from the left of the source in pixel
	 * - *bottom*: Offset from the bottom of the source in pixel
	 * - *right*: Offset from the right of the source in pixel
	 * - *temp_dir*: Path to temporary working directory
	 * - *gm_convert*: Path to the gm convert utility (used for psd images, usually "gm convert" or "convert")
	 * 
	 * Create a new transparent resource of 100x75 pixels:
	 * * PurImage::resource(array('width'=>100,'height'=>75));
	 * 
	 * Create a resource from a psd file (with "gm" available in your path as "gm convert...")
	 * * PurImage::resource('path/to/image.psd');
	 * 
	 * Create a resource from a psd file (with "gm" not available but installed)
	 * * PurImage::resource('path/to/image.psd',array('gm_convert'=>'/usr/bin/gm convert'));
	 * 
	 * @return resource Resource associated to the file path
	 * @param mixed $source File path referencing an image or null to create a transparent resource
	 * @param array $options[optional] Configuration array
	 */
	public static function resource($source=null,array $options=array()){
		if(is_array($source)){
			$options = $source;
			$source = null;
		}
		if(empty($source)){
			if(empty($options['width'])||empty($options['height'])){
				throw new InvalidArgumentException('Missing Options: width and height required when creating a new resource');
			}
			$resource = imagecreatetruecolor($options['width'],$options['height']);
			imagesavealpha($resource, true);
			imagefill($resource,0,0,imagecolorallocatealpha($resource,255,255,255,127));
			return $resource;
		}else if(is_string($source)){
			if(!is_readable($source)) throw new InvalidArgumentException('Invalid Source Path: '.PurLang::toString($source).'');
			$type = exif_imagetype($source);
			switch($type){
				case IMAGETYPE_GIF:
					$source = imagecreatefromgif($source);
					break;
				case IMAGETYPE_JPEG:
					$source = imagecreatefromjpeg($source);
					break;
				case IMAGETYPE_PNG:
					$source = imagecreatefrompng($source);
					break;
				case IMAGETYPE_SWF:
					break;
				case IMAGETYPE_PSD:
					if(!isset($options['gm_convert'])){
						throw new Exception('Missing Option For PSD Image Type: gm_convert');
					}
					$temp = (isset($options['temp_dir'])?$options['temp_dir']:sys_get_temp_dir()).'/'.uniqid();
					$cmd = $options['gm_convert'].' -flatten '.escapeshellarg($source).' '.escapeshellarg('png:'.$temp);
					exec($cmd);
					$source = imagecreatefrompng($temp);
					PurFile::delete($temp);
					break;
				case IMAGETYPE_BMP:
					break;
				case IMAGETYPE_TIFF_II:
					break;
				case IMAGETYPE_TIFF_MM:
					break;
				case IMAGETYPE_JPC:
					break;
				case IMAGETYPE_JP2:
					break;
				case IMAGETYPE_JPX:
					break;
				case IMAGETYPE_JB2:
					break;
				case IMAGETYPE_SWC:
					break;
				case IMAGETYPE_IFF:
					break;
				case IMAGETYPE_WBMP:
					$source = imagecreatefromwbmp($source);
					break;
				case IMAGETYPE_XBM:
					$source = imagecreatefromxbm($source);
					break;
			}
			$created = true;
		}else if(is_resource($source)){
			$created = false;
		}else{
			throw new InvalidArgumentException('Invalid Source: empty, string or resource accepted');
		}
//		width
//		height
//		top
//		left
//		bottom
//		right
//		imagecopyresampled($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
		$sourceWidth = imagesx($source);
		$sourceHeight = imagesy($source);
		// Sanitize width and height
		if(!empty($options['width'])&&!empty($options['height'])){
			if($options['width']!=$sourceWidth&&$options['height']!=$sourceHeight){
				$resized = true;
			}
		}else if(!empty($options['width'])&&$options['width']!=$sourceWidth){
			$resized = true;
			$options['height'] = $sourceHeight*$options['width']/$sourceWidth;
		}else if(!empty($options['height'])&&$options['height']!=$sourceHeight){
			$resized = true;
			$options['width'] = $sourceWidth*$options['height']/$sourceHeight;
		}
		// Sanitize resize as an array with width and height keys
		if(!empty($options['resize'])){
			$resized = true;
			switch(gettype($options['resize'])){
				case 'string':
					$options['resize'] = explode('x',$options['resize']);
				case 'array':
					if(empty($options['resize'])||count($options['resize'])>2){
						throw new Exception('Invalid "resize": '.PurLang::toString($options['resize']));
					}
					// Deal with map
					if(!empty($options['resize']['width'])){
						$width = $options['resize']['width'];
					}
					if(!empty($options['resize']['height'])){
						$height = $options['resize']['height'];
					}
					// Deal with index
					if(!isset($width)){
						$width = array_shift($options['resize']);
					}
					if(!isset($height)){
						$height = array_shift($options['resize']);
					}
					// Deal with *
					if($width=='*') $width = null;
					if($height=='*') $height = null;
					// Rebuild resize
					$options['resize']['width'] = $width;
					unset($width);
					$options['resize']['height'] = $height;
					unset($height);
					if(empty($options['resize']['width'])){
						$options['resize']['width'] = $sourceWidth*$options['resize']['height']/$sourceHeight;
					}else if(empty($options['resize']['height'])){
						$options['resize']['height'] = $sourceHeight*$options['resize']['width']/$sourceWidth;
					}
					break;
				default:
					throw new Exception('Invalid "resize": '.PurLang::toString($options['resize']));
			}
		}else{
			$options['resize'] = array('width'=>$sourceWidth,'height'=>$sourceHeight);
		}
		if(isset($resized)){
			$destination = self::resource(null,$options);
			imagecopyresampled($destination,$source,0,0,0,0,
				$options['resize']['width'],$options['resize']['height'],
				$sourceWidth,$sourceHeight);
			if($created){
				imagedestroy($source);
			}
			$source = $destination;
		}
		return $source;
	}
	
	/**
	 * Retrieve the alpha overlay from a pixel color value.
	 * 
	 * Value range goes from 0 to 127 where 0 is full opacity and 127 is transparent.
	 * 
	 * @return int Alpha value
	 * @param mixed Image resource or path to image
	 * @param int $x X coordinate
	 * @param int $y Y coordinate
	 */
	public static function toAlpha($image,$x,$y){
		if(!is_resource($image)) throw new InvalidArgumentException('First argument not a resource');
		$rgb = imagecolorat($image, $x, $y);
		$c = imagecolorsforindex($image, $rgb);
		$ret = round(($c['red']+$c['green']+$c['blue'])/6);
		/*
		$r   = ($rgb >> 16) & 0xFF;
        $g   = ($rgb >> 8) & 0xFF;
        $b   = $rgb & 0xFF;
        $ret = round(($r + $g + $b) / 6);
		 */
		return ($ret > 1) ? ($ret - 1) : 0;
	}
	
}
