<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Photo_engine {
    //put your code here
    
    var $max_size;
    var $allowed_type;
    public function __construct() {
        $this->allowed_type = array("jpeg", "jpg", "png");
        $this->max_size = 500000;
    }
    
    function get_max_size(){
        return parse_size(ini_get("upload_max_filesize"));
    }
    
    function get_allowed_image_type(){
        return array(
            "jpg",
            "jpeg", 
            "png"
        );
    }
    
    function create_thumbnail_old($files){                
        $my_file = $files['tmp_name'][0];      
        if(is_array($my_file)) $my_file = $my_file[0]; 
        
        $type = mime_content_type($my_file);
        $types = explode('/', $type);
        $type = $types[1];
        
        if(!in_array($type, $this->allowed_type)){
            return 0;
        }                
        
        if($type == "jpeg" || $type == "jpg"){
            $uploadedfile = $my_file;
            $src = imagecreatefromjpeg($uploadedfile);
        }else if($type=="png"){
            $uploadedfile = $my_file;
            $src = imagecreatefrompng($uploadedfile);
        }
                
        list($width,$height)=getimagesize($uploadedfile);

        $newwidth=128;
        $newheight=($height/$width)*$newwidth;
        $tmp=imagecreatetruecolor($newwidth,$newheight);        
        
        $newwidth1=128;
        $newheight1=($height/$width)*$newwidth1;
        $tmp1=imagecreatetruecolor($newwidth1,$newheight1);

        imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,
         $width,$height);

        imagecopyresampled($tmp1,$src,0,0,0,0,$newwidth1,$newheight1, 
        $width,$height);
                
        $filename1 = "images/thumbnail_" . md5(date('Y-m-d h:i:s')) . ".$type";                
                
        //echo "<img src='data:$type;base64," . base64_encode( stripslashes($file_contents) ) . "' />";
        
        imagejpeg($tmp1,$filename1,100);

        imagedestroy($src);        
        imagedestroy($tmp1);
        //unlink($filename1);
    }
    
    function create_thumbnail($image_string, $mime_type){                
        $res_image = $this->downsize_image($image_string, $mime_type, MAX_THUMBNAIL_IMAGE_SIZE);
        return $res_image;
    }
    function resize_regular_image($image_string, $mime_type){                
        $res_image = $this->downsize_image($image_string, $mime_type, MAX_REGULAR_IMAGE_SIZE);
        return $res_image;
    }
    
    function downsize_image($image_string, $mime_type, $max_size) {
        $exploded_extension = explode("/", $mime_type);
        
        $extension = "";
        if(count($exploded_extension) > 0){
            $extension = $exploded_extension[1];
        }
        
        if(!in_array($extension, $this->allowed_type)) return 0;
        
        $src = imagecreatefromstring($image_string);
        list($width,$height)=getimagesizefromstring($image_string);
        
        if ($width < $max_size && $height < $max_size) {
            return $image_string; // no need to resize
        }
        
        // else {
        //      ... resize
        // }
        $newwidth = $max_size;
        $newheight=($height/$width)*$newwidth;
        $tmp=imagecreatetruecolor($newwidth,$newheight);        
        
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        ob_start (); 
        if($extension == "jpeg" || $extension == "jpg"){
            imagejpeg($tmp);
        }else if($extension == "png"){
            imagepng($tmp);
        }       
        
        $image_data = ob_get_contents (); 
        ob_end_clean (); 
        imagedestroy($tmp);        
        return $image_data;
    }
    
    function get_file_contents($files){
        $my_file = $files["tmp_name"];
        if(is_array($my_file)) $my_file = $my_file[0];        
        
        $type = $files['type'];
        if(is_array($type)) $type = $type[0];
        
        $file = fopen($my_file, "r");
        $file_contents = fread($file, filesize($my_file));
        fclose($file);
        
        $file_contents = addslashes($file_contents);                
    }
    
    function photo_delete($image_path = ""){
        if(empty($image_path)) return 1;
        
        if(file_exists($image_path)){
            if(unlink($image_path)){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 1;
        }
    }
    
    function get_photo_dimension($photo_path = ""){
        if(empty($photo_path)) return array("height" => 0, "width" => 0);
        
        list($width, $height) = getimagesize($photo_path); 
        
        return array(
            "height"=> (int)$height,
            "width" => (int)$width
        );
    }
    
    function create_image_from_image_string($image_string,$mime_type, $file_path){        
        $im = imagecreatefromstring($image_string); // php function to create image from string
        // condition check if valid conversion
        if ($im !== false)  {
            // saves an image to specific location
            switch (strtolower($mime_type)) {
                case "image/png":                    
                    $resp = imagepng($im, $file_path);
                    imagedestroy($im);
                    
                    error_log($mime_type . ": " . $resp);
                    return $resp;                    
                case "image/jpeg":                    
                    $resp = imagejpeg($im, $file_path);
                    imagedestroy($im);
                    
                    error_log($mime_type . ": " . $resp);
                    return $resp;                
                case "image/jpg":                    
                    $resp = imagejpeg($im, $file_path);
                    imagedestroy($im);
                    
                    error_log($mime_type . ": " . $resp);
                    return $resp;
                default:
                    break;
            }
        } else  {
            return false;
        }
    }
}
