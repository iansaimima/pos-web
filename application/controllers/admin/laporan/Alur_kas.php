<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Alur_kas extends MY_Controller{
    
    
    function __construct(){
        parent::__construct();
        
        model("m_kas_alur", "kas");
        model("m_pelanggan", "pelanggan");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Alur Kas" => "active"
        );
        view("pages/laporan/alur_kas/index", $data);
    }

    function view(){
        $data = array();
        $data["report_data"] = $this->kas->laporan_alur_kas();

        view("pages/laporan/alur_kas/view", $data);
    }
    
}