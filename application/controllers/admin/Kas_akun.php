<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kas_akun extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_kas_akun", "kas_akun");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Kas" => "", "Akun Kas" => "active"
        );
        view("pages/kas/kas_akun/index", $data);
    }
    
    function ajax_list(){
        $data           = $this->kas_akun->kas_akun_get_list($_GET);      
        $rows_total     = $this->kas_akun->kas_akun_get_total();
        $filtered_total = $this->kas_akun->kas_akun_get_filtered_total($_GET);

        $table_data = array(
          "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1, 
          "recordsTotal"    => $rows_total,
          "recordsFiltered" => $filtered_total, 
          "data"            => $data
        );

        if(ob_get_contents()) ob_clean();
        echo json_encode($table_data);
    }

    function ajax_detail($uuid = ""){
        $data = array();
        $data["detail"] = $this->kas_akun->kas_akun_get($uuid);
        view("pages/kas/kas_akun/ajax_detail", $data);
    }

    function ajax_delete($uuid = "" ){

        $res = $this->kas_akun->kas_akun_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save() {

        $res = $this->kas_akun->kas_akun_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}