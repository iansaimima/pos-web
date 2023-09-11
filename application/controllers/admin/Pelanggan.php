<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pelanggan extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_pelanggan", "pelanggan");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Pengaturan" => "", "Pelanggan" => "active"
        );
        view("pages/pengaturan/pelanggan/index", $data);
    }
    
    function ajax_list(){
        $data           = $this->pelanggan->pelanggan_get_list($_GET);      
        $rows_total     = $this->pelanggan->pelanggan_get_total();
        $filtered_total = $this->pelanggan->pelanggan_get_filtered_total($_GET);

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
        $data["detail"] = $this->pelanggan->pelanggan_get($uuid);
        view("pages/pengaturan/pelanggan/ajax_detail", $data);
    }

    function ajax_delete($uuid = "" ){

        $res = $this->pelanggan->pelanggan_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save() {

        $res = $this->pelanggan->pelanggan_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}