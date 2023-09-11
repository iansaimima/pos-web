<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Settings extends MY_Controller{
    
    function __construct(){
        parent::__construct();
        
        model("m_settings", "settings");
        model("m_kas_akun", "kas_akun");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Pengaturan" => "", "Lain-lain" => "active"
        );
        $data["settings_list"] = $this->settings->settings_get_list();
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_plain_get_list();

        view("pages/pengaturan/lain_lain/index", $data);
    }

    function ajax_delete($key = "" ){

        $res = $this->settings->settings_delete($key);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save() {

        $res = $this->settings->settings_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_bulk_save() {

        $res = $this->settings->bulk_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
    function change_logo(){
        $res = $this->settings->change_logo($_FILES['logo']);
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
}