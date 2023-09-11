<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Piutang extends MY_Controller{
    
    function __construct(){
        parent::__construct();    

        model("m_pembayaran_piutang");
        model("m_pelanggan", "pelanggan");
    }

    function aktif(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Piutang" => "active"
        );
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list();
        view("pages/laporan/piutang/aktif/index", $data);
    }

    function aktif_view(){
        $data = array();
        $data["judul"] = "Laporan Piutang Aktif";
        $data["report_data"] = $this->m_pembayaran_piutang->laporan_pembayaran_piutang_aktif();
        view("pages/laporan/piutang/aktif/view", $data);
    }

    function aktif_sudah_jatuh_tempo(){
        $data = array();
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list();
        view("pages/laporan/piutang/aktif_sudah_jatuh_tempo/index", $data);
    }

    function aktif_sudah_jatuh_tempo_view(){
        $data = array();
        $data["judul"] = "Laporan Piutang Aktif Sudah JT";
        $data["sudah_jatuh_tempo"] = 1;
        $data["report_data"] = $this->m_pembayaran_piutang->laporan_pembayaran_piutang_aktif(true);
        view("pages/laporan/piutang/aktif_sudah_jatuh_tempo/view", $data);
    }
    
}