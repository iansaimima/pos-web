<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class M_gudang extends MY_Model{
    
    private $gudang_engine;
    
    private $actor_user_uuid;
    private $actor_user_name;
    private $cabang_selected_uuid;

    private $allow;
    private $allow_create;
    private $allow_update;
    private $allow_delete;
    
    function __construct(){
        parent::__construct();
    
        $this->gudang_engine = new Gudang_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = isset($cabang_selected["uuid"]) ? $cabang_selected["uuid"]: "";

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
        
        $this->allow        = isset($privilege_list["allow_gudang"]) ? $privilege_list["allow_gudang"] : 0;
        $this->allow_create = isset($privilege_list["allow_gudang_create"]) ? $privilege_list["allow_gudang_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_gudang_update"]) ? $privilege_list["allow_gudang_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_gudang_delete"]) ? $privilege_list["allow_gudang_delete"] : 0;
    }

    function gudang_get_list($filters = array()){        
        if(!$this->allow) return array();

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters, true, true);       
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;
        
        $final_res = array();
        foreach ($res as $r) {
            $no++;
            
            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["kode"] = $r["kode"];
            $row["nama"] = $r["nama"];
            $row["alamat"] = $r["alamat"];
            $row["no_telepon"] = $r["no_telepon"];
            $row["last_updated"] = $r["last_updated"];
            $row["last_updated_user_name"] = $r["last_updated_user_name"];
            
            $final_res[] = $row;
        }
        
        return $final_res;
    }

    function gudang_get_filtered_total($filters = array()) {
        if(!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters, false, true);
        return count($res);
    }

    function gudang_get_total($filters = array()) {
        if(!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        return count($res);
    }

    function gudang_get($uuid = "") {        
        if(!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if(count($res) == 0) return array();
        $res = $res[0];
        return $res;
    }

    function gudang_delete($uuid = ""){
        if(!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        // **
        // get gudang id
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if(count($res) == 0) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Gudang tidak ditemukan");
        }
        $res = $res[0];
        $uuid = $res["uuid"];

        // **
        // mulai proses delete
        $this->db->trans_start();
        try {
            $res = $this->gudang_engine->gudang_delete($uuid);
            if($res == false ) {
                throw new Exception("Gagal menghapus gudang");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Gudang telah dihapus");
    }

    function gudang_save(){
        $uuid = $this->input->post("uuid");
        $kode = $this->input->post("kode");
        $nama = $this->input->post("nama");
        $alamat = $this->input->post("alamat");
        $no_telepon = $this->input->post("no_telepon");
        $keterangan = $this->input->post("keterangan");
        $fungsi = $this->input->post("fungsi");

        if(empty($kode)) return set_http_response_error(HTTP_BAD_REQUEST, "Kode tidak boleh kosong");
        if(empty($nama)) return set_http_response_error(HTTP_BAD_REQUEST, "Nama tidak boleh kosong");
        if(empty($alamat)) return set_http_response_error(HTTP_BAD_REQUEST, "Alamat tidak boleh kosong");
        if(empty($no_telepon)) return set_http_response_error(HTTP_BAD_REQUEST, "No. Telepon tidak boleh kosong");

        $kode = substr($kode,0,3);

        
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $last_updated = date("Y-m-d H:i:s");;
        $last_updated_user_uuid = $this->actor_user_uuid;
        $last_updated_user_name = $this->actor_user_name;

        // **
        // check uuid if not empty
        if(!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if(count($res) > 0) {
                $res = $res[0];
                $uuid = $res["uuid"];
                $created = trim($res["created"]);
                $creator_user_uuid = $res["creator_user_uuid"];
                $creator_user_name = trim($res["creator_user_name"]);
                $number = (int) $res["nummber"];
                $number_formatted = trim($res["number_formatted"]);
            }else{
                return set_http_response_error(HTTP_BAD_REQUEST, "Invalid gudang");
            }
        }

        // ** 
        // cek duplikat kode
        $filters = array();
        $filters["kode"] = $kode;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if(count($res) > 0) {
            $res = $res[0];
            $curr_uuid = $res["uuid"];

            if($curr_uuid != $uuid) return set_http_response_error(HTTP_BAD_REQUEST, "Gudang dengan kode $kode sudah terdaftar");
        }

        // ** 
        // cek duplikat nama
        $filters = array();
        $filters["nama"] = $nama;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if(count($res) > 0) {
            $res = $res[0];
            $curr_uuid = $res["uuid"];

            if($curr_uuid != $uuid) return set_http_response_error(HTTP_BAD_REQUEST, "Gudang dengan nama $nama sudah terdaftar");
        }

        if(empty($uuid)) {
            if(!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }else{
            if(!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }

        $data = array();
        $data["uuid"] = $uuid;
        $data["created"] = $created;
        $data["creator_user_uuid"] = $creator_user_uuid;
        $data["creator_user_name"] = $creator_user_name;
        $data["last_updated"] = $last_updated;
        $data["last_updated_user_uuid"] = $last_updated_user_uuid;
        $data["last_updated_user_name"] = $last_updated_user_name;        
        $data["kode"] = strtoupper($kode);
        $data["nama"] = $nama;
        $data["alamat"] = $alamat;
        $data["no_telepon"] = $no_telepon;
        $data["keterangan"] = $keterangan;
        $data["fungsi"] = $fungsi;
        $data["cabang_uuid"] = $this->cabang_selected_uuid;

        $this->db->trans_start();
        try {
            $res = $this->gudang_engine->gudang_save($data);
            if($res == false ) {
                throw new Exception("Gagal menyimpan gudang");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Gudang telah disimpan");
    }

    function gudang_get_list_for_combobox(){
        $filters = array();
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);

        $final_res = array();
        foreach($res as $r) {
            $row = array(
                "uuid" => trim($r["uuid"]), 
                "kode" => $r["kode"],
                "nama" => $r["nama"]
            );

            $final_res[] = $row;
        }

        return $final_res;
    }
    
}