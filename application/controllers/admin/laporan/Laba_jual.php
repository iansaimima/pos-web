<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Laba_jual extends MY_Controller{
    
    
    function __construct(){
        parent::__construct();
        
        model("m_penjualan", "penjualan");
        model("m_pelanggan", "pelanggan");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Laba Jual" => "active"
        );
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list();

        view("pages/laporan/laba_jual/index", $data);
    }

    function view(){
        $data = array();
        $data["report_data"] = $this->penjualan->laporan_laba_jual_rekap();

        view("pages/laporan/laba_jual/view", $data);
    }
    
}