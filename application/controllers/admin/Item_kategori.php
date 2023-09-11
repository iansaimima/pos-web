<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_kategori extends MY_Controller{
    
    function __construct(){
        parent::__construct();
    
        model("m_item_kategori", "item_kategori");
    }

    function index(){
        $data = array();
        $data["breadcrumb"] = array(
            "Item" => "", "Kategori Item" => "active"
        );
        view("pages/item/item_kategori/index", $data);
    }
    
    function ajax_list(){
        $data           = $this->item_kategori->item_kategori_get_list($_GET);      
        $rows_total     = $this->item_kategori->item_kategori_get_total();
        $filtered_total = $this->item_kategori->item_kategori_get_filtered_total($_GET);

        $table_data = array(
          "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1, 
          "recordsTotal"    => $rows_total,
          "recordsFiltered" => $filtered_total, 
          "data"            => $data
        );

        if(ob_get_contents()) ob_clean();
        echo json_encode($table_data);
    }

    function ajax_detail($uuid = ""){
        $data = array();
        $data["detail"] = $this->item_kategori->item_kategori_get($uuid);
        view("pages/item/item_kategori/ajax_detail", $data);
    }

    function ajax_delete($uuid = "" ){

        $res = $this->item_kategori->item_kategori_delete($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_save() {

        $res = $this->item_kategori->item_kategori_save();
        
        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }
    
}