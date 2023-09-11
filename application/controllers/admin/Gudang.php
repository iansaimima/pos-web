<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gudang extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Pengaturan" => "", "Gudang" => "active"
        );
        view("pages/pengaturan/gudang/index", $data);
    }
    
    function ajax_list(){
        $data           = $this->gudang->gudang_get_list($_GET);      
        $rows_total     = $this->gudang->gudang_get_total();
        $filtered_total = $this->gudang->gudang_get_filtered_total($_GET);

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
        $data["detail"] = $this->gudang->gudang_get($uuid);
        view("pages/pengaturan/gudang/ajax_detail", $data);
    }

    function ajax_delete($uuid = "" ){

        $res = $this->gudang->gudang_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save() {

        $res = $this->gudang->gudang_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}