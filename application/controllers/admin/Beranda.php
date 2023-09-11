<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Beranda extends MY_Controller{
    public function __construct(){
        parent::__construct();

        model("m_item");
        model("m_cabang");
        model("m_pelanggan");
        model("m_pemasok");
        model("m_penjualan");
        model("m_pembayaran_piutang");
        model("m_kas_alur", "kas_alur");
    }

    function index(){
        //-- Index

        $data = array();
        $data["total_item"] = $this->m_item->item_get_total();
        $data["total_item_aktif"] = $this->m_item->item_get_total_aktif();
        $data["total_pelanggan"] = $this->m_pelanggan->pelanggan_get_total();
        $data["total_pemasok"] = $this->m_pemasok->pemasok_get_total();
        
        $data["saldo_saat_ini"] = $this->kas_alur->kas_alur_get_saldo_saat_ini();

        $data["penjualan_total_data"] = $this->m_penjualan->penjualan_total_data();

        $start_date = date("Y-01-01");
        $end_date = date("Y-m-d");
        $data["total_piutang_aktif_tahun_ini"] = $this->m_pembayaran_piutang->dashboard_total_piutang_aktif($start_date, $end_date);

        $start_date = "";
        $end_date = date("Y-m-d");
        $data["total_piutang_aktif"] = $this->m_pembayaran_piutang->dashboard_total_piutang_aktif($start_date, $end_date);

        $start_date = "";
        $end_date = date("Y-m-d");
        $data["daftar_piutang_sudah_jatuh_tempo"] = $this->m_pembayaran_piutang->dashboard_daftar_piutang_jatuh_tempo($start_date, $end_date, 10);

        $start_date = date("Y-m-d");        
        $end_date = date("Y-m-d", strtotime($start_date. " +14 day"));
        $data["daftar_piutang_akan_jatuh_tempo_14_hari"] = $this->m_pembayaran_piutang->dashboard_daftar_piutang_jatuh_tempo($start_date, $end_date, 10);

        $data["grafik_laba_jual"] = $this->m_penjualan->dashboard_grafik_laba_jual_for_year(date("Y"));
        $data["grafik_penjualan"] = $this->m_penjualan->dashboard_grafik_penjualan_for_year(date("Y"));

        $data["item_stock_minimum_list"] = $this->m_item->item_stock_minimum();
        view("pages/beranda/index", $data);
    }

    function ajax_get_grafik_penjualan($last_day_number = 7){
        $res = $this->m_penjualan->dashboard_grafik_penjualan_for_last_day_number($last_day_number);

        if(ob_get_contents()) ob_get_clean();
        echo json_encode($res);
    }

    function ajax_get_grafik_laba_jual_tahunan($tahun = 0){
        $res = $this->m_penjualan->dashboard_grafik_laba_jual_for_year((int) $tahun);

        if(ob_get_contents()) ob_get_clean();
        echo json_encode($res);
    }

    function set_cabang($uuid = ""){
        $this->m_cabang->set_cabang_terpilih($uuid);

        $target_uri = $this->input->get("target");
        if(empty($target_uri)) $target_uri = $this->uri->segment(1);

        redirect_url($target_uri);
    }
}