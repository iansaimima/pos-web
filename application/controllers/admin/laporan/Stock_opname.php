<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Stock_opname extends MY_Controller{
    
    
    function __construct(){
        parent::__construct();
        
        model("m_stock_opname", "stock_opname");
        model("m_gudang", "gudang");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Stock Opname" => "active"
        );
        
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        view("pages/laporan/stock_opname/index", $data);
    }

    function view(){
        $data = array();
        $data["report_data"] = $this->stock_opname->laporan_stock_opname();

        view("pages/laporan/stock_opname/view", $data);
    }
    
}