<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Penjualan extends MY_Controller{
    
    function __construct(){
        parent::__construct();    

        model("m_penjualan");
        model("m_pelanggan", "pelanggan");
        model("m_gudang", "gudang");
    }

    function harian(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Penjualan" => "", "Harian" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/laporan/penjualan/harian/index", $data);
    }

    function harian_view(){
        $data = array();
        $data["report_data"] = $this->m_penjualan->laporan_penjualan_harian();
        view("pages/laporan/penjualan/harian/view", $data);
    }

    function rekap(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Penjualan" => "", "Rekap" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list();
        view("pages/laporan/penjualan/rekap/index", $data);
    }

    function rekap_view(){
        $data = array();
        $data["report_data"] = $this->m_penjualan->laporan_penjualan_rekap();
        view("pages/laporan/penjualan/rekap/view", $data);
    }

    function detail(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Penjualan" => "", "Detail" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list();
        view("pages/laporan/penjualan/detail/index", $data);
    }

    function detail_view(){
        $data = array();
        $data["report_data"] = $this->m_penjualan->laporan_penjualan_detail();
        view("pages/laporan/penjualan/detail/view", $data);
    }
    
}