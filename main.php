<?php

function square_app_utility($src_dir, $dest_dir, $bg_color = [255, 255, 255]) {
	$response = new stdClass();
	$response->status = "error";
	//$response->value = "";
	
	if(!is_dir($src_dir)) {
		$response->value = "Source Directory does not exists.";
		return json_encode($response);
	}
	
	if(!is_dir($dest_dir)) {
		$response->value = "Destination Directory does not exists.";
		return json_encode($response);
	}
	
	if(sizeof($bg_color) != 3) {
		$response->value = "Invalid RGB values";
		return json_encode($response);
	}
	
	$response->value = array();
	foreach(glob($src_dir.'*') as $filename) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png'){
			$in_filename = $src_dir.basename($filename);
			
			list($width, $height) = getimagesize($in_filename);
			$maxLength = 0;
			if($width > $height) {
				$maxLength = $width;
			} else {
				$maxLength = $height;
			}
			
			if($ext === 'jpg' || $ext === 'jpeg'){
				$image = imagecreatefromjpeg($in_filename);
			}else if($ext === 'png'){
				$image = imagecreatefrompng($in_filename);
				$white = imagecolorallocate($image, bg_color[0], bg_color[1], bg_color[2]);
			}
			
			$response->value[basename($filename)] = square_app_utility_single($image, $dest_dir.basename($filename), $maxLength, $bg_color);
		}
	}
	
	$response->status = "success";
	
	header('Content-Type: application/json');
	return json_encode($response, JSON_PRETTY_PRINT);
}

function square_app_utility_single($src_img, $dest_img, $square_dimensions, $bg_color) {
	$responseValue = "false";
	
	//Constant JPEG Quality
	$jpeg_quality=100;
	
    // Step one: Rezise with proportion the src_img *** I found this in many places.

    $old_x=imageSX($src_img);
    $old_y=imageSY($src_img);

    $ratio1=$old_x/$square_dimensions;
    $ratio2=$old_y/$square_dimensions;

    if($ratio1>$ratio2) {
        $thumb_w=$square_dimensions;
        $thumb_h=$old_y/$ratio1;
    } else {
        $thumb_h=$square_dimensions;
        $thumb_w=$old_x/$ratio2;
    }

    // we create a new image with the new dimmensions
    $smaller_image_with_proportions=ImageCreateTrueColor($thumb_w,$thumb_h);

    // resize the big image to the new created one
    imagecopyresampled($smaller_image_with_proportions,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 

    // *** End of Step one ***

    // Step Two (this is new): "Copy and Paste" the $smaller_image_with_proportions in the center of a white image of the desired square dimensions

    // Create image of $square_dimensions x $square_dimensions in white color (white background)
    $final_image = imagecreatetruecolor($square_dimensions, $square_dimensions);
    $bg = imagecolorallocate ( $final_image, $bg_color[0], $bg_color[1], $bg_color[2]);
    imagefilledrectangle($final_image,0,0,$square_dimensions,$square_dimensions,$bg);

    // need to center the small image in the squared new white image
    if($thumb_w>$thumb_h) {
        // more width than height we have to center height
        $dst_x=0;
        $dst_y=($square_dimensions-$thumb_h)/2;
    } elseif($thumb_h>$thumb_w) {
        // more height than width we have to center width
        $dst_x=($square_dimensions-$thumb_w)/2;
        $dst_y=0;
    } else {
        $dst_x=0;
        $dst_y=0;
    }

    $src_x=0; // we copy the src image complete
    $src_y=0; // we copy the src image complete

    $src_w=$thumb_w; // we copy the src image complete
    $src_h=$thumb_h; // we copy the src image complete

    $pct=100; // 100% over the white color ... here you can use transparency. 100 is no transparency.

    imagecopymerge($final_image,$smaller_image_with_proportions,$dst_x,$dst_y,$src_x,$src_y,$src_w,$src_h,$pct);

    imagejpeg($final_image,$dest_img,$jpeg_quality);

    // destroy aux images (free memory)
    imagedestroy($src_img); 
    imagedestroy($smaller_image_with_proportions);
    imagedestroy($final_image);
	
	$responseValue = "true";
	return $responseValue;
}

?>