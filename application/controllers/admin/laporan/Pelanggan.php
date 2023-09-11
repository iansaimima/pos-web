<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Pelanggan extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_laporan");
    }
    
    function index() {
        $data = array();
        $data["breadcrumb"] = array(
            "Laporan" => "", "Pelanggan" => "active"
        );
        view("pages/laporan/pelanggan/index", $data);
    }

    function view() {
        $data = array();
        $data["pelanggan_list"] = $this->m_laporan->laporan_pelanggan();
        
        view("pages/laporan/pelanggan/view", $data);
    }
} 