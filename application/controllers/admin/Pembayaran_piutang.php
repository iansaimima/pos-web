<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pembayaran_piutang extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_pembayaran_piutang", "pembayaran_piutang");
        model("m_pelanggan", "pelanggan");
        model("m_kas_akun", "kas_akun");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Transaksi" => "", "Penjualan" => "", "Pembayaran Piutang" => "active"
        );
        view("pages/transaksi/pembayaran_piutang/index", $data);
    }
    
    function ajax_list(){
        $data           = $this->pembayaran_piutang->pembayaran_piutang_get_list($_GET);      
        $rows_total     = $this->pembayaran_piutang->pembayaran_piutang_get_total();
        $filtered_total = $this->pembayaran_piutang->pembayaran_piutang_get_filtered_total($_GET);

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
        $data["detail"] = $this->pembayaran_piutang->pembayaran_piutang_get($uuid);
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list_for_combobox();
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list_for_combobox();
        view("pages/transaksi/pembayaran_piutang/ajax_detail", $data);
    }

    function ajax_add_new($uuid = ""){
        $data = array();
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list_for_combobox();
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list_for_combobox();
        view("pages/transaksi/pembayaran_piutang/ajax_add_new", $data);
    }

    function ajax_konfirmasi(){        
        $pelanggan_uuid = get("pelanggan_uuid");
        $jumlah_bayar = to_number(get("jumlah_bayar"));
        $data["penjualan_belum_lunas_data"] = $this->pembayaran_piutang->generate_penjualan_belum_lunas_get_list_for_pelanggan_uuid_and_jumlah_bayar($pelanggan_uuid, $jumlah_bayar);
        
        view("pages/transaksi/pembayaran_piutang/ajax_konfirmasi", $data);
    }

    function ajax_save(){
        $res = $this->pembayaran_piutang->pembayaran_piutang_save();
    
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_delete($uuid = ''){
        $res = $this->pembayaran_piutang->pembayaran_piutang_delete($uuid);
    
        // if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}