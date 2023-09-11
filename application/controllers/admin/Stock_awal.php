<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Stock_awal extends MY_Controller{
    
    function __construct(){
        parent::__construct();   
        
        model("m_stock_awal", "stock_awal");
        model("m_pembelian", "pembelian");
        model("m_gudang", "gudang");
        model("m_settings", "settings");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Persediaan" => "", "Stock Awal" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        $data['tanggal_mulai_penggunaan_aplikasi'] = $this->stock_awal->get_tanggal_mulai_penggunaan_aplikasi();
        view("pages/persediaan/stock_awal/index", $data);
    }
    
    function ajax_list($gudang_uuid = ""){
        $data           = $this->stock_awal->stock_awal_get_list($_GET, $gudang_uuid);      
        $rows_total     = $this->stock_awal->stock_awal_get_total(array(), $gudang_uuid);
        $filtered_total = $this->stock_awal->stock_awal_get_filtered_total($_GET, $gudang_uuid);

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
        $data["detail"] = $this->stock_awal->stock_awal_get($uuid);
        $data['tanggal_mulai_penggunaan_aplikasi'] = $this->stock_awal->get_tanggal_mulai_penggunaan_aplikasi();
        
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();

        view("pages/persediaan/stock_awal/ajax_detail", $data);
    }

    function ajax_delete($uuid = ""){
        $res = $this->stock_awal->stock_awal_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save(){
        $res = $this->stock_awal->stock_awal_save();

        if(ob_get_contents()) ob_clean();
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
}