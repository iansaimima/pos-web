<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penjualan extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_penjualan", "penjualan");
        model("m_pelanggan", "pelanggan");
        model("m_item", "item");
        model("m_kas_akun", "kas_akun");
        model("m_settings", "settings");
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Transaksi" => "", "Penjualan" => "", "Daftar Penjualan" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        view("pages/transaksi/penjualan/index", $data);
    }
    
    function ajax_list($gudang_uuid = ""){
        $data           = $this->penjualan->penjualan_get_list($_GET, $gudang_uuid);      
        $rows_total     = $this->penjualan->penjualan_get_total(array(), $gudang_uuid);
        $filtered_total = $this->penjualan->penjualan_get_filtered_total($_GET, $gudang_uuid);

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
        $data["detail"] = $this->penjualan->penjualan_get($uuid);
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list_for_combobox();
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list_for_combobox();
        $data["settings_list"] = $this->settings->settings_get_list();
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/transaksi/penjualan/ajax_detail", $data);
    }

    function pelanggan_get_detail_by_uuid($uuid = "") {
        $res = $this->penjualan->pelanggan_get_detail_by_uuid($uuid);
        
        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_get_detail_by_kode() {
        $res = $this->penjualan->item_get_detail_by_kode();

        if(ob_get_contents()) ob_clean();
        header("Content-type: application/json");
        echo json_encode($res);
    }

    function item_popup_list(){
        view("pages/transaksi/penjualan/item_popup_list");
    }

    function ajax_item_list(){
        $data           = $this->item->item_get_list($_GET, 0);      
        $rows_total     = $this->item->item_get_total($_GET, 0);
        $filtered_total = $this->item->item_get_filtered_total($_GET, 0);

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
        $res = $this->penjualan->penjualan_save();
    
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_delete($uuid = ''){
        $res = $this->penjualan->penjualan_delete($uuid);
    
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
    function cetak_nota($uuid = ""){
        $data = array();
        $data["print_data"] = $this->penjualan->penjualan_cetak($uuid);
        view("pages/transaksi/penjualan/cetak_nota", $data);
    }

    function cetak_riwayat_pembayaran_piutang($uuid = ""){
        $data = array();
        $data["print_data"] = $this->penjualan->penjualan_cetak_riwayat_pembayaran_piutang($uuid);
        view("pages/transaksi/penjualan/cetak_riwayat_pembayaran_piutang", $data);
    }
    
}