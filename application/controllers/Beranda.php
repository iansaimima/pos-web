<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Beranda extends CI_Controller{
    public function __construct(){
        parent::__construct();

        model("m_settings", "settings");
    }

    function index(){
        //-- Index
        redirect_url('admin');
    }
}