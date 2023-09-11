<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Penjualan_retur extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_penjualan_retur", "penjualan_retur");
        model("m_penjualan", "penjualan");
        model("m_kas_akun", "kas_akun");
        model("m_settings", "settings");
        model("m_item", "item");
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Transaksi" => "", "Penjualan" => "", "Penjualan Retur" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/transaksi/penjualan_retur/index", $data);
    }

    function ajax_list($gudang_uuid = ""){
        $data           = $this->penjualan_retur->penjualan_retur_get_list($_GET, $gudang_uuid);
        $rows_total     = $this->penjualan_retur->penjualan_retur_get_total(array(), $gudang_uuid);
        $filtered_total = $this->penjualan_retur->penjualan_retur_get_filtered_total($_GET, $gudang_uuid);

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
        $data["detail"] = $this->penjualan_retur->penjualan_retur_get($uuid);
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/transaksi/penjualan_retur/ajax_detail", $data);
    }

    function ajax_save(){
        $res = $this->penjualan_retur->penjualan_retur_save();

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_delete($uuid = ""){
        $res = $this->penjualan_retur->penjualan_retur_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function penjualan_get_detail_by_number_formatted() {
        $res = $this->penjualan_retur->penjualan_get_detail_by_number_formatted();
        
        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_get_detail_by_kode_and_penjualan_uuid() {
        $res = $this->penjualan_retur->item_get_detail_by_kode_and_penjualan_uuid();
        
        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function penjualan_popup_list(){
        view("pages/transaksi/penjualan_retur/penjualan_popup_list");
    }

    function ajax_penjualan_list(){
        $data           = $this->penjualan->penjualan_get_list($_GET);
        $rows_total     = $this->penjualan->penjualan_get_total($_GET);
        $filtered_total = $this->penjualan->penjualan_get_filtered_total($_GET);

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
        view("pages/transaksi/penjualan_retur/item_popup_list");
    }

    function ajax_item_list($penjualan_uuid = ''){
        $data           = $this->item->item_get_list_for_penjualan_uuid($_GET, $penjualan_uuid);
        $rows_total     = $this->item->item_get_total_for_penjualan_uuid($_GET, $penjualan_uuid);
        $filtered_total = $this->item->item_get_filtered_total_for_penjualan_uuid($_GET, $penjualan_uuid);

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