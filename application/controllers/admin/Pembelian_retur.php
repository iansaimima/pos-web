<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Pembelian_retur extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_pembelian_retur", "pembelian_retur");
        model("m_pembelian", "pembelian");
        model("m_kas_akun", "kas_akun");
        model("m_settings", "settings");
        model("m_gudang", "gudang");
        model("m_item", "item");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Transaksi" => "", "Pembelian" => "", "Retur Pembelian" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/transaksi/pembelian_retur/index", $data);
    }

    function ajax_list($gudang_uuid = ""){
        $data           = $this->pembelian_retur->pembelian_retur_get_list($_GET, $gudang_uuid);      
        $rows_total     = $this->pembelian_retur->pembelian_retur_get_total(array(), $gudang_uuid);
        $filtered_total = $this->pembelian_retur->pembelian_retur_get_filtered_total($_GET, $gudang_uuid);

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
        $data["detail"] = $this->pembelian_retur->pembelian_retur_get($uuid);
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/transaksi/pembelian_retur/ajax_detail", $data);
    }

    function ajax_save(){
        $res = $this->pembelian_retur->pembelian_retur_save();

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_delete($uuid = ""){
        $res = $this->pembelian_retur->pembelian_retur_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function pembelian_get_detail_by_number_formatted() {
        $res = $this->pembelian_retur->pembelian_get_detail_by_number_formatted();
        
        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_get_detail_by_kode_and_pembelian_uuid() {
        $res = $this->pembelian_retur->item_get_detail_by_kode_and_pembelian_uuid();
        
        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function pembelian_popup_list(){
        view("pages/transaksi/pembelian_retur/pembelian_popup_list");
    }

    function ajax_pembelian_list(){
        $data           = $this->pembelian->pembelian_get_list($_GET);
        $rows_total     = $this->pembelian->pembelian_get_total($_GET);
        $filtered_total = $this->pembelian->pembelian_get_filtered_total($_GET);

        $table_data = array(
          "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1, 
          "recordsTotal"    => $rows_total,
          "recordsFiltered" => $filtered_total, 
          "data"            => $data
        );

        if(ob_get_contents()) ob_clean();
        echo json_encode($table_data);
    }

    function item_popup_list(){        
        view("pages/transaksi/pembelian_retur/item_popup_list");
    }

    function ajax_item_list($pembelian_uuid = ''){
        $data           = $this->item->item_get_list_for_pembelian_uuid($_GET, $pembelian_uuid);
        $rows_total     = $this->item->item_get_total_for_pembelian_uuid($_GET, $pembelian_uuid);
        $filtered_total = $this->item->item_get_filtered_total_for_pembelian_uuid($_GET, $pembelian_uuid);

        $table_data = array(
          "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1, 
          "recordsTotal"    => $rows_total,
          "recordsFiltered" => $filtered_total, 
          "data"            => $data
        );

        if(ob_get_contents()) ob_clean();
        echo json_encode($table_data);
    }
    
}