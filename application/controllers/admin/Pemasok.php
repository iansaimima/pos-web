<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pemasok extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_pemasok", "pemasok");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Pengaturan" => "", "Pemasok" => "active"
        );
        view("pages/pengaturan/pemasok/index", $data);
    }
    
    function ajax_list(){
        $data           = $this->pemasok->pemasok_get_list($_GET);      
        $rows_total     = $this->pemasok->pemasok_get_total();
        $filtered_total = $this->pemasok->pemasok_get_filtered_total($_GET);

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
        $data["detail"] = $this->pemasok->pemasok_get($uuid);
        view("pages/pengaturan/pemasok/ajax_detail", $data);
    }

    function ajax_delete($uuid = "" ){

        $res = $this->pemasok->pemasok_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save() {

        $res = $this->pemasok->pemasok_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}