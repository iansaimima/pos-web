<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembelian extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_pembelian", "pembelian");
        model("m_pemasok", "pemasok");
        model("m_gudang", "gudang");
        model("m_item", "item");
        model("m_kas_akun", "kas_akun");
        model("m_settings", "settings");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Transaksi" => "", "Pembelian" => "", "Daftar Pembelian" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/transaksi/pembelian/index", $data);
    }
    
    function ajax_list($gudang_uuid = ""){
        $data           = $this->pembelian->pembelian_get_list($_GET, $gudang_uuid);      
        $rows_total     = $this->pembelian->pembelian_get_total(array(), $gudang_uuid);
        $filtered_total = $this->pembelian->pembelian_get_filtered_total($_GET, $gudang_uuid);

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
        $data["detail"] = $this->pembelian->pembelian_get($uuid);
        $data["pemasok_list"] = $this->pemasok->pemasok_get_list_for_combobox();
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/transaksi/pembelian/ajax_detail", $data);
    }

    function pemasok_get_detail_by_uuid($uuid = "") {
        $res = $this->pembelian->pemasok_get_detail_by_uuid($uuid);
        
        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_get_detail_by_kode() {
        $res = $this->pembelian->item_get_detail_by_kode();

        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_popup_list(){
        view("pages/transaksi/pembelian/item_popup_list");
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

    function ajax_save(){
        $res = $this->pembelian->pembelian_save();
    
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_delete($uuid = ''){
        $res = $this->pembelian->pembelian_delete($uuid);
    
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}