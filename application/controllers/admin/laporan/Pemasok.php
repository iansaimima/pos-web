<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Pemasok extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_laporan");
    }
    
    function index() {
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Pemasok" => "active"
        );
        view("pages/laporan/pemasok/index", $data);
    }

    function view() {
        $data = array();
        $data["pemasok_list"] = $this->m_laporan->laporan_pemasok();
        
        view("pages/laporan/pemasok/view", $data);
    }
} 