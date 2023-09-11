<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class My_email_engine extends Db_engine {
  //put your code here
  
  private $config;
  public $email_username;
  public $email_password;
  public function __construct() {  
    $this->email_username = "mail.helper.gutsylab@gmail.com";
    $this->email_password = "23oct19 :P~";
  }
  
  function send_email($data = array()){
    $email_sender = trim($data["email_sender"]);
    $email_sender_alias = trim($data["email_sender_alias"]);
    $email_target = trim($data["email_receiver_target"]);
    $email_subject = trim($data["subject"]);
    $email_message = trim($data["message"]);
    
    if(empty($email_target))          return false;
    if(empty($email_subject))         return false;
    if(empty($email_message))         return false;    

    $config = array();
    $config['protocol'] = "smtp";
    $config['smtp_host'] = "ssl://smtp.googlemail.com";
    $config['smtp_port'] = "465";
    $config['smtp_user'] = $this->email_username;
    $config['smtp_pass'] = $this->email_password;
    $config['charset'] = "utf-8";
    $config['mailtype'] = "html";
    $config['newline'] = "\r\n";
        
    library("email");
    $ci =& get_instance();
    $ci->email->initialize($config);
    $ci->email->from($email_sender, $email_sender_alias);    
    $ci->email->to($email_target);
    $ci->email->subject($email_subject);
    $ci->email->message($email_message);
    
    $res = $ci->email->send();
    return $res;
  }
}
