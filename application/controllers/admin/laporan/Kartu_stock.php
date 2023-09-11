<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Kartu_stock extends MY_Controller{
    
    
    function __construct(){
        parent::__construct();
        
        model("m_item", "item");
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Kartu Stock" => "active"
        );
        $data["item_list"] = $this->item->item_get_list_for_stock_opname();
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/laporan/kartu_stock/index", $data);
    }

    function view(){
        $data = array();
        $data["report_data"] = $this->item->laporan_kartu_stock();

        view("pages/laporan/kartu_stock/view", $data);
    }
    
}