<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Model
 *
 * @author ian
 */
class MY_Model extends CI_Model {
    //put your code here
    
    var $is_admin;
    var $is_client;    
  
    public $level_allow_delete;
    public function __construct() {
        $this->level_allow_delete = array("Administrator", "Super Administrator");
    }
    
    function get_level(){
        return get_session("user_login_level_name");
    }
    
    function is_superadmin(){
        // ** 
        // check if user logged in is_gutsy_admin
        $level = get_session("level");
        return trim($level) == "Super Administrator";
    }
    
    function is_admin(){
        // ** 
        // check if user logged in is_gutsy_admin
        $level = get_session("level");
        return trim($level) == "Administrator";
    }
    
    function is_user(){
        // ** 
        // check if user logged in is editor
        $level = get_session("user_login_level_name");
        return trim($level) == "User";
    }
    
    function encrypt_password($password){        
        if(empty($password)) return "";
                
        $hash1 = md5(CHIPPER_TEXT);
        $hash2 = md5(sha1($hash1) . "&" . sha1($password));                
        $hash3 = sha1(md5(SESSION_PREFIX) . "*" . md5($hash2));
        $hash4 = md5($hash3);

        return $hash4;
    }
    
    function post($key_post = ""){
        if(isset($_POST[$key_post])){
            return $_POST[$key_post];
        }else{
            return null;
        }
    }
    
    function get($key_get = ""){
        if(isset($_GET[$key_get])){
            return $_GET[$key_get];
        }else{
            return null;
        }
    }
}
