<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	/* Input */

define('MAP_API_KEY', 'AIzaSyBj5zfROXzT7myenK9AyZtTHkReQqo6gK0');

if (!function_exists('post')){
    function post($str = ''){
        $CI =& get_instance();
        return $CI->input->post($str);
    }
}

if (!function_exists('get')){
    function get($str = ''){
        $CI =& get_instance();
        return $CI->input->get($str);
    }
}

/* load  */
if (!function_exists('model')){
    function model($model = '', $alias = ''){
        $CI =& get_instance();

        if($alias){
            $CI->load->model($model, $alias);
            return $CI->$alias;
        }else{
            $CI->load->model($model);
            $modelName = explode('/', $model);
            $modelName = $modelName[count($modelName) - 1];
            return $CI->$modelName;
        }
    }
}

if (!function_exists('library')){
    function library($lib = ''){
        $CI =& get_instance();
        $CI->load->library($lib);
    }
}

if ( ! function_exists('helper'))
{
        function helper($helper = '')
        {
                $CI =& get_instance();
                $CI->load->helper($helper);
        }
}

/* View */

if ( ! function_exists('view'))
{
        function view($view = '', $data = '')
        {
                $CI =& get_instance();

                if(! isset($data)) {
                        return $CI->load->view($view);
                }
                return $CI->load->view($view, $data);
        }
}

if(!function_exists('generateCode')){
        function generateCode($lastID = '0', $format = '', $length = 0, $separator = '0')
        {
                $nextID = $lastID;
                $leftLength = ($length - strlen($nextID)) - strlen($format); 

                return $format . str_repeat($separator, $leftLength) . $nextID;
        }
}

if(!function_exists('errorMessage')){
        function errorMessage($message)
        {
                return '<div class = "error">' . $message . '</div>';
        }
}

if(!function_exists('successMessage')){
        function successMessage($message)
        {
                return '<div class = "success">' . $message . '</div>';
        }
}

if(!function_exists('email')){
        function email($data = array()){
                $CI =& get_instance();

                $headers = 'From: BestFitness<' . $CI->config->item('email') . '>' . "\n"; 
                $headers .= 'MIME-Version: 1.0' . "\n"; 
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 

                mail($data['to'], $data['subject'], $data['message'], $headers);
        }
}

if(!function_exists('printr')){            
        function printr($data){
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }
}

if(!function_exists('print_json')){            
        function print_json($data){
            header("content-type: application/json");
            if(ob_get_contents()) ob_clean();
            echo json_encode($data, JSON_PRETTY_PRINT);

        }
}

if(!function_exists('get_nama_bulan')){
    function get_nama_bulan($nomor_bulan = "0"){
        $bulan_array = array(
            "Tidak diketahui",
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        );
        return $bulan_array[$nomor_bulan];
    }
}

if(!function_exists("set_json_status")){
    function set_json_status($error, $status, $data = array()){
        $array = array(
            "error" => $error,
            "status" => $status,
            "data" => $data
        );
        ob_clean();                
        echo json_encode($array);                     
    }
}

function strip_image_tags($str){  
    $CI =& get_instance();
    return $CI->strip_image_tags($str);              
}

if(!function_exists('encrypt_password')){
    function encrypt_password($password){
        return md5(sha1($password) . md5($password));
    }
}

if(!function_exists('set_session_for_admin')){
    function set_session_for_admin($key, $value){
        $_SESSION[SESSION_PREFIX . "ADMIN_" . $key] = $value; 
    }
}

if(!function_exists('get_session_for_admin')){
    function get_session_for_admin($key){        
        if(isset($_SESSION[SESSION_PREFIX . "ADMIN_" . $key])){
            return $_SESSION[SESSION_PREFIX . "ADMIN_" . $key];
        }else{
            return "";
        }
    }
}

if(!function_exists('set_session')){
    function set_session($key, $value){
        $_SESSION[SESSION_PREFIX . $key] = $value; 
    }
}

if(!function_exists('get_session')){
    function get_session($key){        
        if(isset($_SESSION[SESSION_PREFIX . $key])){
            return $_SESSION[SESSION_PREFIX . $key];
        }else{
            return "";
        }
    }
}

if(!function_exists("create_alert")){
    function create_alert($response = "", $type = 0){
        set_session("response", $response);
        switch ((int) $type) {
            case 0:
                set_session("type", "ERROR");
                break;
            case 1:
                set_session("type", "SUCCESS");
                break;
            case 2:
                set_session("type", "WARNING");
                break;
            case 3:
                set_session("type", "INFO");
                break;
            default:
                set_session("type", "ERROR");
                break;
        }
    }
}

if(!function_exists("clear_alert")){
    function clear_alert(){
        set_session("response", "");
        set_session("type", "");
    }
}

if(!function_exists("set_response")){
    function set_response($response = "", $type = "", $id = 0){
        $array = array(
            "response" => $response,
            "type" => "",
            "id" => $id
        );
        $type = (int) $type;
        switch ($type) {
            case 0:
                $array['type'] = "ERROR";
                break;
            case 1:
                $array['type'] = "SUCCESS";
                break;
            case 2:
                $array['type'] = "WARNING";
                break;
            case 3:
                $array['type'] = "INFO";
                break;
            default:
                $array['type'] = "ERROR";
                break;
        }
        
        return $array;
    }
}

if(!function_exists("parse_size")){
    function parse_size($size = "") {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
}

if(!function_exists("formatSizeUnits")){
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}

if(!function_exists("redirect_url")){
    function redirect_url($url){
        echo "<META HTTP-EQUIV=\"Refresh\" Content=\"0; URL=". base_url($url) ."\">";    
        exit;
    }
}

if(!function_exists('app_setting_get_app_client_name')){
    function  app_setting_get_app_client_name(){
    }
}

if(!function_exists("get_all_method_for_each_controllers")){
    function get_all_method_for_each_controllers($input_dir = ""){        
        $input_dir = rtrim($input_dir,"/");
        $input_dir = ltrim($input_dir,"/");
        
        $comment_sign = "//--";
            
        $controller_dir = "application/modules/admin/controllers/$input_dir";        
        $file_list = scandir($controller_dir);        
        
        $controller_list = array();
        foreach ($file_list as $index => $file_name) {
            if($file_name == "." || $file_name == "..") continue;
            $exploded_file_name = explode(".", $file_name);
            if($exploded_file_name[count($exploded_file_name) - 1] != "php") continue;
            
            $controller_list[] = $file_name;
        }        
        
        $final_controller_list = array();
        foreach ($controller_list as $index => $controller_file_name) {
            $controller = str_replace(".php", "", $controller_file_name);
            $file_name = $controller_dir . "/$controller_file_name";
            $file = file($file_name);                        
            if($file){                                
                $class_name = "";
                foreach ($file as $line_index => $line) {
                    if(empty($line)) continue;
                    
                    if(strtolower(substr($line, 0,5)) == "class"){
                        $class = $line;
                        $exploded_class = explode(" ", $class);
                        $class_name = strtolower($exploded_class[1]);
                    }
                    if(empty($class_name)) continue;                    
                    
                    if(substr(trim($line), 0,4) == $comment_sign){                        
                        $function = $file[$line_index - 1];
                        $function = str_replace("function", "", $function);
                        
                        $exploded_function = explode("(", $function);
                        $function_name = trim($exploded_function[0]);
                        $label = str_replace($comment_sign, "", trim($line)); 
                                                
                        $final_controller_list[$class_name][$function_name]['label'] = $label;
                        $final_controller_list[$class_name][$function_name]['allow'] = 0;
                    }
                }
            }
        } 
        
        return $final_controller_list;
    }
}

if(!function_exists("set_http_response_error")){
    function set_http_response_error($http_status_code = 0, $message = ""){

        $ci =& get_instance();

        $array = array(
            "is_success" => 0,
            "http_status_code" => $http_status_code,
            "message" => $message,
            "data" => array()
        );

        if($ci->uri->segment(1) != "api") {            
            $array["csrf_token_name"]   = $ci->security->get_csrf_token_name();
            $array["csrf_hash"]         = $ci->security->get_csrf_hash();
        }
        http_response_code($http_status_code);
        return $array;
    }
}

if(!function_exists("set_http_response_success")){
    function set_http_response_success($message = "", $data = array(), $id = ""){

        $ci =& get_instance();

        $array = array(
            "is_success" => 1,
            "http_status_code" => HTTP_OK,
            "message" => $message,
            "data" => $data,
        );
        

        if($ci->uri->segment(1) != "api") {    
            $array["id"]                = $id;
            $array["csrf_token_name"]   = $ci->security->get_csrf_token_name();
            $array["csrf_hash"]         = $ci->security->get_csrf_hash();
        }
        http_response_code(HTTP_OK);

        return $array;
    }
}

function penyebut($nilai) {
		$nilai = abs($nilai);
		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = " ". $huruf[$nilai];
		} else if ($nilai <20) {
			$temp = penyebut($nilai - 10). " belas";
		} else if ($nilai < 100) {
			$temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus" . penyebut($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = penyebut($nilai/100) . " ratus" . penyebut($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu" . penyebut($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = penyebut($nilai/1000) . " ribu" . penyebut($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = penyebut($nilai/1000000) . " juta" . penyebut($nilai % 1000000);
		} else if ($nilai < 1000000000000) {
			$temp = penyebut($nilai/1000000000) . " milyar" . penyebut(fmod($nilai,1000000000));
		} else if ($nilai < 1000000000000000) {
			$temp = penyebut($nilai/1000000000000) . " trilyun" . penyebut(fmod($nilai,1000000000000));
		}     
		return $temp;
	}
 
	function terbilang($nilai) {
		if($nilai<0) {
			$hasil = "minus ". trim(penyebut($nilai));
		} else {
			$hasil = trim(penyebut($nilai));
		}     		
		return $hasil;
	}
  
  

if(!function_exists("getProtectedValue")){
  function getProtectedValue($obj,$name) {
    $array = (array)$obj;
    $prefix = chr(0).'*'.chr(0);
    return $array[$prefix.$name];
  }
}

if(!function_exists("get_jenjang_pendidikan_list")){
  function get_jenjang_pendidikan_list() {
      $list = array();
      $list[] = "D3";
      $list[] = "D4";
      $list[] = "S1";
      
      return $list;
  }
}

if(!function_exists("get_gelar_list")){
  function get_gelar_list() {
      $list = array();
      $list[] = "A.Md";
      $list[] = "A.Md.T";
      $list[] = "A.Md.Kom";
      $list[] = "A.Md.Si";
      
      return $list;
  }
}


if(!function_exists("error_log_")){
    function error_log_($string = ""){
        error_reporting(1);
        error_log($string);
    }
}


if(!function_exists("get_mime_type_list")){
  function get_mime_type_list($mime_type = ""){
    
    $mimet = array( 
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',        
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',


        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );
    
    if(!empty($mime_type)){
      if(isset($mime_type)) return $mimet[$mime_type];
    }
    
    return $mimet;
  }
}

if(!function_exists('get_allowed_photo')){
    function get_allowed_photo(){
        return array(
            "jpg",
            "jpeg",
            "png",
            "bmp",
        );
    }
}

if(!function_exists("_mime_content_type")){  
  function _mime_content_type($filename) {
      

    $idx = explode( '.', $filename );
    $count_explode = count($idx);
    $idx = strtolower($idx[$count_explode-1]);

    $mimet = get_mime_type_list();

    if (isset( $mimet[$idx] )) {
     return $mimet[$idx];
    } else {
     return 'application/octet-stream';
    }
  }
}

if(!function_exists("isJson")) {
    function isJson($json_string) {
        json_decode($json_string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}

if(!function_exists("allowed_jenis_perhitungan_harga_jual")) {
    function allowed_jenis_perhitungan_harga_jual(){
        return array(
            "MARGIN MENGIKUTI HARGA JUAL", 
            "HARGA JUAL MENGIKUTI MARGIN"
        );
    }
}

if(!function_exists("to_number")){
    function to_number($string = ""){
        if(empty($string)) return 0;

        return preg_replace('/\D/', '', $string);
    }
}

if(!function_exists('generate_date_range_list')){    
    function generate_date_range_list($start_date, $end_date, $date_as_key = false) {
        // Specify the start date. This date can be any English textual format  
        $start_date = strtotime($start_date); // Convert date to a UNIX timestamp  
        
        // Specify the end date. This date can be any English textual format  
        $end_date = strtotime($end_date); // Convert date to a UNIX timestamp  
        
        // Loop from the start date to end date and output all dates inbetween  
        $list = array();
        for ($i=$start_date; $i<=$end_date; $i+=86400) {  
            $date = date("Y-m-d", $i);
            if($date_as_key) {
                $list[$date] = array();
            }else{
                $list[] = $date;
            }
        }  

        return $list;
    }
}

function get_user_ip(){

    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    return $ip;
}

function get_user_info($ip = ""){
    $geoplugin = unserialize( file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip) );
    return $geoplugin;
}

function microtime_(){
    $microtime = explode(".", microtime(true));
    return implode("", $microtime);
}



if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
           $headers = [];
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}