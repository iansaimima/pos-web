<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Pembelian extends MY_Controller{
    
    function __construct(){
        parent::__construct();    

        model("m_pembelian");
        model("m_pemasok", "pemasok");
        model("m_gudang", "gudang");
    }

    function harian(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Pembelian" => "", "Harian" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/laporan/pembelian/harian/index", $data);
    }

    function harian_view(){
        $data = array();
        $data["report_data"] = $this->m_pembelian->laporan_pembelian_harian();
        view("pages/laporan/pembelian/harian/view", $data);
    }

    function rekap(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Pembelian" => "", "Rekap" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["pemasok_list"] = $this->pemasok->pemasok_get_list();
        view("pages/laporan/pembelian/rekap/index", $data);
    }

    function rekap_view(){
        $data = array();
        $data["report_data"] = $this->m_pembelian->laporan_pembelian_rekap();
        view("pages/laporan/pembelian/rekap/view", $data);
    }

    function detail(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Pembelian" => "", "Detail" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["pemasok_list"] = $this->pemasok->pemasok_get_list();
        view("pages/laporan/pembelian/detail/index", $data);
    }

    function detail_view(){
        $data = array();
        $data["report_data"] = $this->m_pembelian->laporan_pembelian_detail();
        view("pages/laporan/pembelian/detail/view", $data);
    }
    
}