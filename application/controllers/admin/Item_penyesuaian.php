<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_penyesuaian extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    }

    function index(){
        view("pages/item/item_penyesuaian/index");
    }
    
}