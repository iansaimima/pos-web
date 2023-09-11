<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cabang_engine extends Db_engine {
    //put your code here
    
    public function __construct() {
        parent::__construct();
    }

    function cabang_get_list($filters = array(), $pagination = false, $datatables = false){
        $column_search  = array(           
            null,    
            "cabang.kode",  
            "cabang.nama",  
            "cabang.alamat",  
            "cabang.no_telepon", 
            );
        $column_order   = $column_search;
        $order          = array(
            "cabang.kode" => "asc"
        );
    
        $this->db->select("
            cabang.*
        ");
        $this->db->from("cabang");        
        
        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("cabang.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "nama":
                    $this->db->where("LOWER(cabang.nama)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "kode":
                    $this->db->where("LOWER(cabang.kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }
        
        if($datatables){
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }else{
            $this->db->order_by("cabang.kode");
        }
        
        $res = $this->db->get()->result_array();
        return $res;
    }

    function cabang_delete($uuid = "") {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("cabang");

        return $uuid;
    }

    function cabang_save($save_data = array()) {
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

        $data["bulan_mulai_penggunaan_aplikasi"] = $save_data["bulan_mulai_penggunaan_aplikasi"];
        $data["tahun_mulai_penggunaan_aplikasi"] = $save_data["tahun_mulai_penggunaan_aplikasi"];

        $data["persediaan_stock_awal_default_gudang_uuid"] = $save_data["persediaan_stock_awal_default_gudang_uuid"];
        $data["persediaan_stock_opname_default_gudang_uuid"] = $save_data["persediaan_stock_opname_default_gudang_uuid"];
        
        $data["transaksi_pembelian_default_gudang_uuid"] = $save_data["transaksi_pembelian_default_gudang_uuid"];
        $data["transaksi_penjualan_default_gudang_uuid"] = $save_data["transaksi_penjualan_default_gudang_uuid"];
        
        $data["transaksi_pembelian_default_kas_akun_uuid"] = $save_data["transaksi_pembelian_default_kas_akun_uuid"];
        $data["transaksi_penjualan_default_kas_akun_uuid"] = $save_data["transaksi_penjualan_default_kas_akun_uuid"];
        
        $data["transaksi_pembelian_default_pemasok_uuid"] = $save_data["transaksi_pembelian_default_pemasok_uuid"];
        $data["transaksi_penjualan_default_pelanggan_uuid"] = $save_data["transaksi_penjualan_default_pelanggan_uuid"];


        $this->db->set($data);
        if(empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("cabang");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("cabang");
        }
        
        return $uuid;
    }
}