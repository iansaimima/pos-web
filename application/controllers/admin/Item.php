<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Item extends MY_Controller{
    

    function __construct(){
        parent::__construct();
    
        model("m_item", "item");
        model("m_item_kategori", "item_kategori");
        model("m_gudang", "gudang");
    }

    function index(){        
        $data = array();
        $data["breadcrumb"] = array(
            "Item" => "", "Daftar Item" => "active"
        );
        $data["gudang_list"] = $this->gudang->gudang_get_list_for_combobox();
        $data["item_kategori_list"] = $this->item_kategori->item_kategori_get_list();
        view("pages/item/item/index", $data);
    }
    
    function ajax_list($arsip = "-1", $gudang_uuid = "", $item_kategori_uuid = ""){
        $tipe = "";
        if($gudang_uuid == "-") $gudang_uuid = "";
        if($item_kategori_uuid == "-") $item_kategori_uuid = "";
        $data           = $this->item->item_get_list_with_stock($_GET, $arsip, $tipe, $gudang_uuid, $item_kategori_uuid);      
        $rows_total     = $this->item->item_get_total($_GET, $arsip, $tipe);
        $filtered_total = $this->item->item_get_filtered_total($_GET, $arsip, $tipe);

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
        $data["detail"] = $this->item->item_get($uuid);

        $data["item_kategori_list"] = $this->item_kategori->item_kategori_get_list_for_combobox();
        view("pages/item/item/ajax_detail", $data);
    }

    function ajax_item_get_nama_by_kode(){
        $res = $this->item->item_get_nama_by_kode();
        
        if(ob_get_contents()) ob_clean();
        echo $res;
    }

    function ajax_save() {
        $res = $this->item->item_save();

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_set_arsip($uuid = "") {
        $res = $this->item->item_set_arsip($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_set_aktif($uuid = "") {
        $res = $this->item->item_set_aktif($uuid);

        if(ob_get_contents()) ob_clean();
        echo json_encode($res);
    }

    function ajax_update_struktur_harga_json(){
        $res = $this->item->item_update_struktur_satuan_harga_json();

        if(ob_get_contents()) ob_clean();
        echo json_encode($res); 
    }

    function ajax_delete_struktur_harga_json_row(){
        $res = $this->item->item_delete_struktur_satuan_harga_json_row();

        if(ob_get_contents()) ob_clean();
        echo json_encode($res); 
    }
    
    function search_flow_stock_in(){
        $res = $this->item->item_search_flow_stock_in();

        header("content-type: application/json");
        if(ob_get_contents()) ob_clean();
        echo json_encode($res); 
    }
    
    function search_item_for_stock_opname(){
        $res = $this->item->item_get_detail_by_kode_and_tanggal();

        header("content-type: application/json");
        if(ob_get_contents()) ob_clean();
        echo json_encode($res); 
    }

    function popup_list_flow_stock_in(){
        view("pages/item/item/item_popup_list_flow_stock_in");
    }
    
    function search_flow_stock_out(){
        $res = $this->item->item_search_flow_stock_out();

        header("content-type: application/json");
        if(ob_get_contents()) ob_clean();
        echo json_encode($res); 
    }

    function popup_list_flow_stock_out(){
        view("pages/item/item/item_popup_list_flow_stock_out");
    }

    function ajax_popup_list($flow = "in", $tipe = "barang"){
        $data = array();
        if($flow == "in"){
            $data           = $this->item->item_get_list($_GET, 0, $tipe);
        }else{
            $data           = $this->item->item_get_list_with_stock($_GET, 0, $tipe);
        }
        
        $rows_total     = $this->item->item_get_total($_GET, 0, $tipe);
        $filtered_total = $this->item->item_get_filtered_total($_GET, 0, $tipe);

        $table_data = array(
          "draw"            => isset($_GET["draw"]) ? (int) $_GET["draw"] : 1, 
          "recordsTotal"    => $rows_total,
          "recordsFiltered" => $filtered_total, 
          "data"            => $data
        );

        if(ob_get_contents()) ob_clean();
        echo json_encode($table_data);
    }
}