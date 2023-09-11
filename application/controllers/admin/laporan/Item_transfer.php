<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Item_transfer extends MY_Controller{
    
    
    function __construct(){
        parent::__construct();
        
        model("m_item_transfer", "item_transfer");
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Stock Opname" => "active"
        );
        
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/laporan/item_transfer/index", $data);
    }

    function view(){
        $data = array();
        $data["report_data"] = $this->item_transfer->laporan_item_transfer();

        view("pages/laporan/item_transfer/view", $data);
    }
    
}