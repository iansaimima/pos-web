<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Gudang_engine extends Db_engine {
    //put your code here
    
    public function __construct() {
        parent::__construct();
    }

    function gudang_get_list($filters = array(), $pagination = false, $datatables = false){
        $column_search  = array(           
            null,   
            "gudang.kode",  
            "gudang.nama",  
            "gudang.alamat",  
            "gudang.no_telepon", 
            );
        $column_order   = $column_search;
        $order          = array(
            "gudang.kode" => "asc"
        );
    
        $this->db->select("
            gudang.*
        ");
        $this->db->from("gudang");        
        
        foreach ($filters as $key => $value) {
            switch ($key) {
                case "cabang_uuid":
                    $this->db->where("gudang.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "uuid":
                    $this->db->where("gudang.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "kode":
                    $this->db->where("LOWER(gudang.kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "nama":
                    $this->db->where("LOWER(gudang.nama)", strtolower($value));
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }
        
        if($datatables){
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }
        
        $res = $this->db->get()->result_array();
        return $res;
    }

    function gudang_delete($uuid = "") {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("gudang");

        return $uuid;
    }

    function gudang_save($save_data = array()) {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["kode"] = $save_data["kode"];
        $data["nama"] = $save_data["nama"];
        $data["alamat"] = $save_data["alamat"];
        $data["no_telepon"] = $save_data["no_telepon"];
        $data["keterangan"] = $save_data["keterangan"];
        $data["fungsi"] = $save_data["fungsi"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);
        if(empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("gudang");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("gudang");
        }
        
        return $uuid;
    }
}