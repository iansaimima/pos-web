<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Item_transfer extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_item_transfer", "item_transfer");
        model("m_settings", "settings");
        model("m_item", "item");
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Persediaan" => "", "Item Transfer" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/persediaan/item_transfer/index", $data);
    }

    function ajax_list($dari_gudang_uuid = ""){
        $data           = $this->item_transfer->item_transfer_get_list($_GET, $dari_gudang_uuid);      
        $rows_total     = $this->item_transfer->item_transfer_get_total(array(), $dari_gudang_uuid);
        $filtered_total = $this->item_transfer->item_transfer_get_filtered_total($_GET, $dari_gudang_uuid);

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
        $data["detail"] = $this->item_transfer->item_transfer_get($uuid);
        $data["settings_list"] = $this->settings->settings_get_list();
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/persediaan/item_transfer/ajax_detail", $data);
    }

    function ajax_save(){
        $res = $this->item_transfer->item_transfer_save();

        // if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_delete($uuid = ""){
        $res = $this->item_transfer->item_transfer_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function item_get_detail_by_kode() {
        $res = $this->item_transfer->item_get_detail_by_kode();

        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_popup_list(){        
        view("pages/persediaan/item_transfer/item_popup_list");
    }

    function ajax_item_list(){
        $data           = $this->item->item_get_list($_GET, 0, "Barang");
        $rows_total     = $this->item->item_get_total($_GET, 0, "Barang");
        $filtered_total = $this->item->item_get_filtered_total($_GET, 0, "Barang");

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
        $data["print_data"] = $this->item_transfer->item_transfer_cetak($uuid);
        view("pages/persediaan/item_transfer/cetak", $data);
    }
}