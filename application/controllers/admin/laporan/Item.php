<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class item extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_item");
        model("m_item_kategori");
        model("m_gudang");
    }
    
    function index() {
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Stock Item" => "active"
        );
        $data["item_kategori_list"] = $this->m_item_kategori->item_kategori_get_list();
        $data["gudang_list"] = $this->m_gudang->gudang_get_list_for_combobox();
        view("pages/laporan/item/index", $data);
    }

    function view() {
        $data = array();
        $data["report_data"] = $this->m_item->laporan_item();
        
        view("pages/laporan/item/view", $data);
    }
} 