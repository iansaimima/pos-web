<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class M_cabang extends MY_Model{
    
    private $cabang_engine;
    
    private $actor_user_uuid;
    private $actor_user_name;

    private $allow;
    private $allow_create;
    private $allow_update;
    private $allow_delete;
    
    function __construct(){
        parent::__construct();
    
        $this->cabang_engine = new Cabang_engine();

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();
        
        $this->allow        = isset($privilege_list["allow_cabang"]) ? $privilege_list["allow_cabang"] : 0;
        $this->allow_create = isset($privilege_list["allow_cabang_create"]) ? $privilege_list["allow_cabang_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_cabang_update"]) ? $privilege_list["allow_cabang_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_cabang_delete"]) ? $privilege_list["allow_cabang_delete"] : 0;
    }

    function cabang_get_list($filters = array()){        
        if(!$this->allow) return array();

        $filters["enabled"] = 1;
        $res = $this->cabang_engine->cabang_get_list($filters, true, true);       
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

    function cabang_get_filtered_total($filters = array()) {
        if(!$this->allow) return 0;

        $filters["enabled"] = 1;
        $res = $this->cabang_engine->cabang_get_list($filters, false, true);
        return count($res);
    }

    function cabang_get_total($filters = array()) {
        if(!$this->allow) return 0;

        $filters["enabled"] = 1;
        $res = $this->cabang_engine->cabang_get_list($filters);
        return count($res);
    }

    function cabang_get($uuid = "") {        
        if(!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["enabled"] = 1;
        $res = $this->cabang_engine->cabang_get_list($filters);
        if(count($res) == 0) return array();
        $res = $res[0];
        return $res;
    }

    function set_cabang_terpilih($uuid = ""){
        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["enabled"] = 1;
        $res = $this->cabang_engine->cabang_get_list($filters);
        if(count($res) == 0) return array();
        $res = $res[0];

        set_session("cabang_selected", $res);
    }

    function cabang_save(){
        $uuid = $this->input->post("uuid");
        $kode = $this->input->post("kode");
        $nama = $this->input->post("nama");
        $alamat = $this->input->post("alamat");
        $no_telepon = $this->input->post("no_telepon");

        $bulan_mulai_penggunaan_aplikasi = $this->input->post("bulan_mulai_penggunaan_aplikasi");
        $persediaan_stock_awal_default_gudang_uuid = $this->input->post("persediaan_stock_awal_default_gudang_uuid");
        $persediaan_stock_opname_default_gudang_uuid = $this->input->post("persediaan_stock_opname_default_gudang_uuid");
        $tahun_mulai_penggunaan_aplikasi = $this->input->post("tahun_mulai_penggunaan_aplikasi");
        $transaksi_pembelian_default_gudang_uuid = $this->input->post("transaksi_pembelian_default_gudang_uuid");
        $transaksi_pembelian_default_kas_akun_uuid = $this->input->post("transaksi_pembelian_default_kas_akun_uuid");
        $transaksi_penjualan_default_gudang_uuid = $this->input->post("transaksi_penjualan_default_gudang_uuid");
        $transaksi_penjualan_default_kas_akun_uuid = $this->input->post("transaksi_penjualan_default_kas_akun_uuid");
        
        $transaksi_pembelian_default_pemasok_uuid = $this->input->post("transaksi_pembelian_default_pemasok_uuid");
        $transaksi_penjualan_default_pelanggan_uuid = $this->input->post("transaksi_penjualan_default_pelanggan_uuid");
        
        $keterangan = $this->input->post("keterangan");

        if(empty($nama)) return set_http_response_error(HTTP_BAD_REQUEST, "Nama tidak boleh kosong");
        if(empty($alamat)) return set_http_response_error(HTTP_BAD_REQUEST, "Alamat tidak boleh kosong");
        if(empty($no_telepon)) return set_http_response_error(HTTP_BAD_REQUEST, "No. Telepon tidak boleh kosong");
        
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $last_updated = date("Y-m-d H:i:s");;
        $last_updated_user_uuid = $this->actor_user_uuid;
        $last_updated_user_name = $this->actor_user_name;

        // **
        // check uuid if not empty
        $is_addon = 0;
        $enabled = 0;
        if(!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $res = $this->cabang_engine->cabang_get_list($filters);
            if(count($res) > 0) {
                $res = $res[0];
                $uuid = $res["uuid"];
                $created = trim($res["created"]);
                $creator_user_uuid = $res["creator_user_uuid"];
                $creator_user_name = trim($res["creator_user_name"]);
                $number = (int) $res["number"];
                $number_formatted = trim($res["number_formatted"]);
                
                $is_addon = (int) $res["is_addon"];
                $enabled = (int) $res["enabled"];
            }else{
                return set_http_response_error(HTTP_BAD_REQUEST, "Invalid cabang");
            }
        }

        // **
        // cek duplikat kode
        $filters = array();
        $filters["kode"] = $kode;
        $res = $this->cabang_engine->cabang_get_list($filters);
        if(count($res) > 0) {
            $res = $res[0];
            $curr_uuid = $res["uuid"];

            if($curr_uuid != $uuid) return set_http_response_error(HTTP_BAD_REQUEST, "Cabang dengan kode $kode sudah terdaftar");
        }

        // ** 
        // cek duplikat nama
        $filters = array();
        $filters["nama"] = $nama;
        $res = $this->cabang_engine->cabang_get_list($filters);
        if(count($res) > 0) {
            $res = $res[0];
            $curr_uuid = $res["uuid"];

            if($curr_uuid != $uuid) return set_http_response_error(HTTP_BAD_REQUEST, "cabang dengan nama $nama sudah terdaftar");
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
        $data["kode"] = $kode;
        $data["nama"] = $nama;
        $data["alamat"] = $alamat;
        $data["no_telepon"] = $no_telepon;
        
        $data["bulan_mulai_penggunaan_aplikasi"] = $bulan_mulai_penggunaan_aplikasi;
        $data["tahun_mulai_penggunaan_aplikasi"] = $tahun_mulai_penggunaan_aplikasi;
        
        $data["persediaan_stock_awal_default_gudang_uuid"] = $persediaan_stock_awal_default_gudang_uuid;
        $data["persediaan_stock_opname_default_gudang_uuid"] = $persediaan_stock_opname_default_gudang_uuid;
        
        $data["transaksi_pembelian_default_gudang_uuid"] = $transaksi_pembelian_default_gudang_uuid;
        $data["transaksi_penjualan_default_gudang_uuid"] = $transaksi_penjualan_default_gudang_uuid;

        $data["transaksi_pembelian_default_kas_akun_uuid"] = $transaksi_pembelian_default_kas_akun_uuid;
        $data["transaksi_penjualan_default_kas_akun_uuid"] = $transaksi_penjualan_default_kas_akun_uuid;
        $data["transaksi_pembelian_default_pemasok_uuid"] = $transaksi_pembelian_default_pemasok_uuid;
        $data["transaksi_penjualan_default_pelanggan_uuid"] = $transaksi_penjualan_default_pelanggan_uuid;

        $data["keterangan"] = $keterangan;

        $data["is_addon"] = $is_addon;
        $data["enabled"] = $enabled;

        $this->db->trans_begin();
        try {
            $res = $this->cabang_engine->cabang_save($data);
            if($res == false ) {
                throw new Exception("Gagal menyimpan cabang");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("cabang telah disimpan");
    }

    function cabang_get_list_for_combobox(){
        $filters = array();
        $filters["enabled"] = 1;
        $res = $this->cabang_engine->cabang_get_list($filters);

        $final_res = array();
        foreach($res as $r) {
            $row = array(
                "uuid" => trim($r["uuid"]), 
                "kode" => $r["kode"],
                "nama" => $r["nama"],
                "alamat" => $r["alamat"]
            );

            $final_res[] = $row;
        }

        return $final_res;
    }
    
}