<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cabang extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_cabang", "cabang");
        model("m_gudang", "gudang");
        model("m_pemasok", "pemasok");
        model("m_pelanggan", "pelanggan");
        model("m_kas_akun", "kas_akun");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Pengaturan" => "", "Cabang" => "active"
        );
        view("pages/pengaturan/cabang/index", $data);
    }
    
    function ajax_list(){
        $data = array();        
        $data["list"] = $this->cabang->cabang_get_list();
        view("pages/pengaturan/cabang/ajax_list", $data);
    }

    function ajax_detail($uuid = ""){
        $data = array();
        $data["detail"] = $this->cabang->cabang_get($uuid);

        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["pemasok_list"] = $this->pemasok->pemasok_get_list_for_combobox();
        $data["pelanggan_list"] = $this->pelanggan->pelanggan_get_list_for_combobox();
        $data["kas_akun_list"] = $this->kas_akun->kas_akun_get_list_for_combobox();

        view("pages/pengaturan/cabang/ajax_detail", $data);
    }

    function ajax_save() {

        $res = $this->cabang->cabang_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}