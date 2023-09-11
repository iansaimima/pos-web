<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class M_laporan extends MY_Model{
    
    private $cabang_selected_uuid;

    private $item_engine;
    private $pelanggan_engine;
    private $pemasok_engine;
    private $settings_engine;

    private $allow_laporan_item;
    private $allow_laporan_pembelian;
    private $allow_laporan_penjualan;
    private $allow_laporan_pelanggan;
    private $allow_laporan_pemasok;
    private $allow_laporan_kas;
    function __construct(){
        parent::__construct();

        $this->settings_engine = new Settings_engine();
        $this->item_engine = new Item_engine();
        $this->pelanggan_engine = new Pelanggan_engine();
        $this->pemasok_engine = new Pemasok_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = $cabang_selected["uuid"];

        $user = get_session("user");
        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow_laporan_item           = isset($privilege_list["allow_laporan_item"]) ? $privilege_list["allow_laporan_item"] : 0;
        $this->allow_laporan_pembelian      = isset($privilege_list["allow_laporan_pembelian"]) ? $privilege_list["allow_laporan_pembelian"] : 0;
        $this->allow_laporan_penjualan      = isset($privilege_list["allow_laporan_penjualan"]) ? $privilege_list["allow_laporan_penjualan"] : 0;
        $this->allow_laporan_pelanggan      = isset($privilege_list["allow_laporan_pelanggan"]) ? $privilege_list["allow_laporan_pelanggan"] : 0;
        $this->allow_laporan_pemasok        = isset($privilege_list["allow_laporan_pemasok"]) ? $privilege_list["allow_laporan_pemasok"] : 0;
        $this->allow_laporan_kas            = isset($privilege_list["allow_laporan_kas"]) ? $privilege_list["allow_laporan_kas"] : 0;
    }

    function laporan_item(){
        $result = array(
            "list" => array(),
            "filter_by_kategori" => 0, 
            "item_kategori_nama" => "",
            "filter_by_arsip" => ""
        );

        if(!$this->allow_laporan_item) return $result;

        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach($list as $l){
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }
        

        $gudang_uuid = $this->input->get("gudang_uuid");
        $item_kategori_uuid = $this->input->get("item_kategori_uuid");
        $arsip = $this->input->get("arsip");

        $item_kategori_uuid = "";
        $item_kategori_nama = "Semua Kategori";

        $filter_gudang_uuid = "";
        $filter_gudang_nama = "Semua Gudang";

        if(!empty($item_kategori_uuid)) {
            // **
            // check item kategori uuid
            $filters = array();
            $filters["uuid"] = $item_kategori_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->item_kategori_get_list($filters);
            if(count($res) == 0) return $result;
            $res = $res[0];
            $item_kategori_uuid = $res["uuid"];
            $item_kategori_nama = trim($res["nama"]);
        }

        // **
        // check gudang uuid
        if (!empty($gudang_uuid)) {
            $filters = array();
            $filters["uuid"] = $gudang_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->gudang_engine->gudang_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];
                $filter_gudang_uuid = $res["uuid"];
                $filter_gudang_nama = $res["kode"] . " - " . $res["nama"];
            }
        }

        $filters = array();
        if(!empty($item_kategori_uuid)) {
            $filters["item_kategori_uuid"] = $item_kategori_uuid;
            $result["filter_by_kategori"] = 1;
            $result["item_kategori_nama"] = $item_kategori_nama;
        };
        if($arsip != "") {
            $filters["arsip"] = (int) $arsip;
            $result["filter_by_arsip"] = 1;
        };
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if(count($res) == 0) return $result;

        $item_list = array();

        foreach($res as $l) {
            $row = array();
            $row["kode"] = $l["kode"];
            $row["barcode"] = $l["barcode"];
            $row["nama"] = $l["nama"];
            $row["item_kategori_nama"] = $l["item_kategori_nama"];
            $row["stock"] = 0;
            $row["satuan"] = "";
            
            $struktur_satuan_harga_json = trim($l["struktur_satuan_harga_json"]);
            $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
            if(!is_array($struktur_satuan_harga_list) || count($struktur_satuan_harga_list) == 0) {
                $item_list[] = $row;

                continue;
            }

            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($l["uuid"]);
            foreach($struktur_satuan_harga_list as $key => $l2) {
                $satuan = $l2["satuan"];
                $stock = $stock / (int) $l2['konversi'];

                $row2 = $row;
                $row["satuan"] = $satuan;
                $row["stock"] = $stock;

                $item_list[] = $row;
            }
        }

        $result["item_list"] = $item_list;
        $result["header"] = array(
            "nama_toko" => $settings_list["TOKO_NAMA"]["_value"],
            "alamat_toko" => $settings_list["TOKO_ALAMAT"]["_value"],
            "no_telp_toko" => $settings_list["TOKO_NO_TELEPON"]["_value"],
        );
        return $result;
    }   

    function laporan_pelanggan(){
        if(!$this->allow_laporan_pelanggan) return array();

        $filters = array();
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->pelanggan_engine->pelanggan_get_list($filters);
        return $list;
    }

    function laporan_pemasok(){
        if(!$this->allow_laporan_pemasok) return array();

        $filters = array();
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $list = $this->pemasok_engine->pemasok_get_list($filters);
        return $list;
    }
}