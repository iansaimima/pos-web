<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Stock_opname extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_stock_opname", "stock_opname");
        model("m_settings", "settings");
        model("m_item", "item");
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Persediaan" => "", "Stock Opname" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/persediaan/stock_opname/index", $data);
    }

    function ajax_list($gudang_uuid = ""){
        $data           = $this->stock_opname->stock_opname_get_list($_GET, $gudang_uuid);      
        $rows_total     = $this->stock_opname->stock_opname_get_total(array(), $gudang_uuid);
        $filtered_total = $this->stock_opname->stock_opname_get_filtered_total($_GET, $gudang_uuid);

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
        $data["detail"] = $this->stock_opname->stock_opname_get($uuid);
        $data["settings_list"] = $this->settings->settings_get_list();
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/persediaan/stock_opname/ajax_detail", $data);
    }

    function ajax_save(){
        $res = $this->stock_opname->stock_opname_save();

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_delete($uuid = ""){
        $res = $this->stock_opname->stock_opname_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function item_get_detail_by_kode_and_tanggal() {
        $res = $this->stock_opname->item_get_detail_by_kode_and_tanggal();
        
        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_popup_list(){        
        view("pages/persediaan/stock_opname/item_popup_list");
    }

    function ajax_item_list(){
        $data           = $this->item->item_get_list_for_stock_opname($_GET);
        $rows_total     = $this->item->item_get_total_for_stock_opname($_GET);
        $filtered_total = $this->item->item_get_filtered_total_for_stock_opname($_GET);

        $table_data = array(
          "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1, 
          "recordsTotal"    => $rows_total,
          "recordsFiltered" => $filtered_total, 
          "data"            => $data
        );

        if(ob_get_contents()) ob_clean();
        echo json_encode($table_data);
    }
    
    function cetak($uuid = ""){
        $data = array();
        $data["print_data"] = $this->stock_opname->stock_opname_cetak($uuid);
        view("pages/persediaan/stock_opname/cetak", $data);
    }
}