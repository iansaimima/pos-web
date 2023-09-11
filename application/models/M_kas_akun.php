<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class M_kas_akun extends MY_Model{
    
    private $kas_engine;
    
    private $cabang_selected_uuid;
    private $actor_user_uuid;
    private $actor_user_name;

    private $allow;
    private $allow_create;
    private $allow_update;
    private $allow_delete;
    function __construct(){
        parent::__construct();

        $this->kas_engine = new Kas_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = isset($cabang_selected["uuid"]) ? $cabang_selected["uuid"]: "";
        $this->cabang_selected_kode = isset($cabang_selected["kode"]) ? $cabang_selected["kode"]: "";

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
        
        $this->allow        = isset($privilege_list["allow_kas_akun"]) ? $privilege_list["allow_kas_akun"] : 0;
        $this->allow_create = isset($privilege_list["allow_kas_akun_create"]) ? $privilege_list["allow_kas_akun_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_kas_akun_update"]) ? $privilege_list["allow_kas_akun_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_kas_akun_delete"]) ? $privilege_list["allow_kas_akun_delete"] : 0;
    }

    function kas_akun_get_list($filters = array()){        
        if(!$this->allow) return array();

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters, true, true);       
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;
        
        $final_res = array();
        foreach ($res as $r) {
            $no++;
            
            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["nama"] = $r["nama"];
            $row["keterangan"] = $r["keterangan"];
            $row["last_updated"] = $r["last_updated"];
            $row["last_updated_user_name"] = $r["last_updated_user_name"];
            
            $final_res[] = $row;
        }
        
        return $final_res;
    }

    function kas_akun_plain_get_list($filters = array()){

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);       
        return $res;
    }

    function kas_akun_get_filtered_total($filters = array()) {
        if(!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters, false, true);
        return count($res);
    }

    function kas_akun_get_total($filters = array()) {
        if(!$this->allow) return 0;

        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);
        return count($res);
    }

    function kas_akun_get($uuid = "") {        
        if(!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);
        if(count($res) == 0) return array();
        $res = $res[0];
        return $res;
    }

    function kas_akun_delete($uuid = "") {
        if(!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        // **
        // get kas akun id
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);
        if(count($res) == 0) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Akun kas tidak ditemukan");
        }
        $res = $res[0];
        $uuid = $res["uuid"];

        // **
        // check jika kas akun sedang digunakan pada kas
        $filters = array();
        $filters["kas_akun_uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_alur_get_list($filters);
        if(count($res) > 0) return set_http_response_error(HTTP_BAD_REQUEST, "Akun kas ini sedang digunakan pada alur kas");

        // **
        // mulai proses delete
        $this->db->trans_start();
        try {
            $res = $this->kas_engine->kas_akun_delete($uuid);
            if($res == false ) {
                throw new Exception("Gagal menghapus Akun kas");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Akun kas telah dihapus");
    }

    function kas_akun_save() { 
        $uuid = $this->input->post("uuid");
        $nama = $this->input->post("nama");
        $keterangan = $this->input->post("keterangan");

        if(empty($nama)) return set_http_response_error(HTTP_BAD_REQUEST, "Nama Akun kas tidak boleh kosong");

        
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $last_updated = date("Y-m-d H:i:s");
        $last_updated_user_uuid = $this->actor_user_uuid;
        $last_updated_user_name = $this->actor_user_name;

        // **
        // check uuid if not empty
        if(!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->kas_engine->kas_akun_get_list($filters);
            if(count($res) > 0) {
                $res = $res[0];
                $uuid = $res["uuid"];
                $created = trim($res["created"]);
                $creator_user_uuid = $res["creator_user_uuid"];
                $creator_user_name = trim($res["creator_user_name"]);
            }else{
                return set_http_response_error(HTTP_BAD_REQUEST, "Invalid Akun Kas");
            }
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
        $data["nama"] = $nama;
        $data["keterangan"] = $keterangan;
        $data["cabang_uuid"] = $this->cabang_selected_uuid;

        $this->db->trans_start();
        try {
            $res = $this->kas_engine->kas_akun_save($data);
            if($res == false ) {
                throw new Exception("Gagal menyimpan Akun kas");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Akun kas telah disimpan");
    }

    function kas_akun_get_list_for_combobox(){
        $filters = array();
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->kas_engine->kas_akun_get_list($filters);

        $final_res = array();
        foreach($res as $r) {
            $row = array(
                "uuid" => trim($r["uuid"]), 
                "nama" => $r["nama"]
            );

            $final_res[] = $row;
        }

        return $final_res;
    }
    
}