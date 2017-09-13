<?php
/**
 *
 * This file is part of Roducks.
 *
 *    Roducks is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    Roducks is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with Roducks.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
*
*	$image = new Image();
*	$image->load($pic_url);
*	$image->resizeToWidth(700);
*	$image->save($save_pic);
*	
*/

namespace rdks\core\libs\Files;

class Image {
  
	var $image;
	var $image_type;

    private function ext($str){

        $ext = substr($str, -4);

        switch($ext){
        	case '.png':
        		$type = 'PNG';
        	break;
        	case '.jpg':
        	case 'jpeg':
        		$type = 'JPEG';
        	break;
        }

        return $type;
    }

    private function ImageCreateFrom($img){
    	$ext = $this->ext($img);
    	$method = "ImageCreateFrom" . $ext;

    	return $method($img);

    }

    private function ImageSave($img, $b, $c){
    	$ext = $this->ext($img);
    	$method = "Image" . $ext;

    	$method($img, $b, $c);

    }    

    private function ImageSave2($src, $img, $b, $c){
    	$ext = $this->ext($src);
    	$method = "Image" . $ext;

    	$method($img, $b, $c);

    }      

	public function load($filename) {
		
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		
		if( $this->image_type == IMAGETYPE_JPEG ) {
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->image_type == IMAGETYPE_GIF ) {
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->image_type == IMAGETYPE_PNG ) {
			$this->image = imagecreatefrompng($filename);
		}
	}

	public function save($filename, $compression=100, $image_type=IMAGETYPE_JPEG, $permissions=null) {
		
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);         
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		}   

		if( $permissions != null) {
			chmod($filename,$permissions);
		}
	}

	public function output($image_type=IMAGETYPE_JPEG) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image);         
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image);
		}   
	}

	public function getWidth() {
		return imagesx($this->image);
	}

	public function getHeight() {
		return imagesy($this->image);
	}

	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}

	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height);
	}

	public function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100; 
		$this->resize($width,$height);
	}

	public function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;   
	}      

	/*
	*
	*	Add water mark over the photo
	*	blend('img/flowers.jpg', 'img/paper.png', 'tmp_example.jpg', ? , ?);
	*
	*/
	public function blend($img_original, $img_watermark, $img_new, $top = 0, $left = 0, $quality = 100) {
	
		// Get Original image size
		$info_original = getimagesize($img_original);
		$w_original = $info_original[0];
		$h_original = $info_original[1];
		
		// Get WaterMark image size
		$info_watermark = getimagesize($img_watermark);
		$w_watermark = $info_watermark[0];
		$h_watermark = $info_watermark[1];
		
		// Calculate watermark's position 
		if($top > 0 && $left > 0):
			$x = $top;
			$y = $left;
		else:
			$x = ($w_original - $w_watermark)/2;
			$y = ($h_original - $h_watermark)/2;
		endif;
		
		// Create Image from original
		$original = $this->ImageCreateFrom($img_original);
		
		// Apply a filter
		ImageAlphaBlending($original, true);
		
		// Create Image from watermark
		$watermark = ImageCreateFromPNG($img_watermark);
		
		// Copy watermark into the image
		ImageCopy($original, $watermark, $x, $y, 0, 0, $w_watermark, $h_watermark);
		
		// Save new Image
		$this->ImageSave2($img_original, $original, $img_new, $quality);

		// Close Images
		ImageDestroy($original);
		ImageDestroy($watermark);
	
	}

	/*
	*	Example
	*	-----------------------------------------------------------------
	*	$colors = array('color' => array(19,55,131), // rgb colors
	*				'shadow' => array(255,255,255)  // rgb colors
	*				);
	*
	*	$bg = array(19,55,131);	//rgb colors			
	*	
	*	$image->text('../img/flowers.jpg', 'tmp_test.jpg', "FLOWERS", "../fonts/METRO-DF.ttf",30,"white",false,$bg,false,30,10);
	*
	*/

	public function text($img_original,$img_final,$text, $font , $font_size, $color="black", $shadow = true, $bg = "white", $set_bg = false, $padding_top = 0, $padding_left = 0, $angle = 0, $quality = 100){
		
		// Get Original Image size
		$image_size = getimagesize($img_original);

		$w = $image_size[0]; // width
		$h = $image_size[1]; // height
		
		if($padding_top < $font_size):
			$padding_top = $font_size;
		else:
			$padding_top = $font_size + $padding_top;
		endif;
		
		// Create Image from Original
		$original = $this->ImageCreateFrom($img_original);
		
		// Crete Empty Image
		$img_empty = ImageCreateTrueColor($w, $h);
		
		// RGB Colors
		$rgb_color = array();
		$rgb_color['black'] = ImageColorAllocate($img_empty, 0, 0, 0);
		$rgb_color['white'] = ImageColorAllocate($img_empty, 255, 255, 255);

		if(is_array($color)):
			
			$text_color = ImageColorAllocate($img_empty, $color['color'][0], $color['color'][1], $color['color'][2]);
			$text_shadow = ImageColorAllocate($img_empty, $color['shadow'][0], $color['shadow'][1], $color['shadow'][2]);
		
		else:
			// Create Some Colors
			switch($color):
					
				case 'black':
					$text_color = $rgb_color['black'];
					$text_shadow = $rgb_color['white'];
				break;
					
				case 'white':
					$text_color = $rgb_color['white'];
					$text_shadow = $rgb_color['black'];
				break;
					
			endswitch;	
			
		endif;
		
		// Choose a Background
		if(is_array($bg)):
			$background = ImageColorAllocate($img_empty, $bg[0], $bg[1], $bg[2]);
		else:
			$background = $rgb_color[$bg];
		endif;
		
		// Copy Original Image into Empty Image
		ImageCopy($img_empty, $original, 0, 0, 0, 0, $w, $h);	
		
		// Fill solid color to text in the background
		if($set_bg) imagefilledrectangle($img_empty, 0, $padding_top, $w, $font_size, $background);
		
		// Add some shadow to the text
		if($shadow) imagettftext($img_empty, $font_size, $angle, $padding_left+1, $padding_top+1, $text_shadow, $font, $text);
		
		// Add text
		imagettftext($img_empty, $font_size, $angle , $padding_left, $padding_top, $text_color, $font, $text);
		
		// Save the new Image
		$this->ImageSave2($img_original, $img_empty,$img_final,$quality);
		
		// Close Empty Image
		ImageDestroy($img_empty);

	}  


	/*
	*	Example
	*	------------------------------------------------------
	*	crop('img/penguins.jpg','tmp_penguins.jpg',$_POST['w'],$_POST['h'],$_POST['x'],$_POST['y']);
	*
	*/
	public function crop($src, $new, $w, $h, $x, $y, $quality = 100){
		
		if($this->ext($src) == 'JPEG'):

			// Create a tempory image from original
			$original = ImageCreateFromJPEG($src);
			
			// Create an empty image with the correct sizes
			$final = ImageCreateTrueColor( $w, $h );

			// Copy original image into empty image
			ImageCopy($final, $original, 0, 0, $x, $y, $w, $h);

			// Save the new image
			$this->ImageSave2($src, $final, $new, $quality);
			
			// Destroy temp original image
			ImageDestroy($original);

		else:

			// Load the original image.
			$img = imagecreatefrompng($src);
			imagealphablending($img, true);

			// Create a blank canvas for the cropped image.
			$img_cropped = imagecreatetruecolor($w, $h);
			imagesavealpha($img_cropped, true);
			imagealphablending($img_cropped, false);
			$transparent = imagecolorallocatealpha($img_cropped, 0, 0, 0, 127);
			imagefill($img_cropped, 0, 0, $transparent);

			// Crop the image and store the data on the blank canvas.
			imagecopyresampled($img_cropped, $img, 0, 0, $x, $y, $w, $h, $w, $h); // or imagecopy()

			// Save the image.
			imagepng($img_cropped, $new, 2);

			// Free memory.
			imagedestroy($img);
			imagedestroy($img_cropped);	

		endif;	

	}
	
	
	/*
	*	Example
	*	-------------------------------------------
	*	extend('flowers.png', 'source/merry_xmas.png', 'tmp_xmas.jpg')
	*
	*/

	public function extend($img_original, $img_watermark, $img_new, $quality = 100) {
	
		// Get Watermark Image size
		$image_size = getimagesize($img_watermark);
		
		$w = $image_size[0];
		$h = $image_size[1];
		
		// Create Image from Original
		$original = $this->ImageCreateFrom($img_original);
		
		// Create Empty Image
		$final = ImageCreateTrueColor( $w, $h );
		
		// Blend Original image && Empty Image
		ImageCopy($final, $original, 0, 0, 0, 0, $w, $h);
		
		// Apply Filter
		ImageAlphaBlending($final, true);
		
		// Create Image from WaterMark
		$watermark = $this->ImageCreateFrom($img_watermark);
		
		// Copy WaterMark into the Original Image
		ImageCopy($final, $watermark, 0, 0, 0, 0, $w, $h);
		
		// Save the new Image
		$this->ImageSave($final, $img_new, $quality);
		
		// Close Images
		ImageDestroy($original);
		ImageDestroy($watermark);
	
	}  

	static function getSize($img){

		$img = preg_replace('/^\/(.+)/', '$1', $img);

		if(!empty($img) && file_exists($img)){

			// get image size
			list($w, $h) = getimagesize($img);

			return [$w, $h];

		}

		return ["auto","auto"];

	}
	
	static function getResize($img, $rz = 0){

			$output = ["auto","auto"];

			if($rz == 0){
				return $output;
			}

			$img = preg_replace('/^\/(.+)/', '$1', $img);

			if(!empty($img) && file_exists($img)){

				// get image size
				list($w, $h) = getimagesize($img);

				// square	
				if($w == $h && $rz < $w && $rz < $h){
					$output = [$rz, $rz];
				// horizontal	
				} else if($w > $h && $rz < $w){
					$ratio = $rz * $h;
					$hr = ceil($ratio / $w);  
					$output = [$rz, $hr];
				// vertical
				} else if($h > $w && $rz < $h){
					$ratio = $rz * $w;
					$wr = ceil($ratio / $h);  
					$output = [$wr, $rz];
				} else {
					$output = [$w, $h];
				}

			}

		return $output;	
	}

}	


?>