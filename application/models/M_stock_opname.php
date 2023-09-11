<?php

defined('BASEPATH') or exit('No direct script access allowed');
class M_stock_opname extends MY_Model
{

    private $item_engine;
    private $pelanggan_engine;
    private $kas_engine;
    private $gudang_engine;
    private $settings_engine;

    private $cabang_selected_uuid;
    private $cabang_selected_kode;
    private $actor_user_uuid;
    private $actor_user_name;

    private $allow;
    private $allow_create;
    private $allow_update;
    private $allow_delete;
    private $allow_detail_create;
    private $allow_detail_update;
    private $allow_detail_delete;
    function __construct()
    {
        parent::__construct();

        $this->settings_engine = new Settings_engine();
        $this->pelanggan_engine = new Pelanggan_engine();
        $this->item_engine = new Item_engine();
        $this->gudang_engine = new Gudang_engine();
        $this->kas_engine = new Kas_engine();

        $cabang_selected = get_session("cabang_selected");
        $this->cabang_selected_uuid = $cabang_selected["uuid"];
        $this->cabang_selected_kode = $cabang_selected["kode"];

        $user = get_session("user");
        $role = strtolower(get_session("role"));
        $this->actor_user_uuid = isset($user["uuid"]) ? $user["uuid"] : "";
        $this->actor_user_name = isset($user["name"]) ? $user["name"] : "";

        $privilege_list = isset($user["privilege_list"]) ? $user["privilege_list"] : array();

        $this->allow        = isset($privilege_list["allow_stock_opname"]) ? $privilege_list["allow_stock_opname"] : 0;
        $this->allow_create = isset($privilege_list["allow_stock_opname_create"]) ? $privilege_list["allow_stock_opname_create"] : 0;
        $this->allow_update = isset($privilege_list["allow_stock_opname_update"]) ? $privilege_list["allow_stock_opname_update"] : 0;
        $this->allow_delete = isset($privilege_list["allow_stock_opname_delete"]) ? $privilege_list["allow_stock_opname_delete"] : 0;
        $this->allow_print = isset($privilege_list["allow_stock_opname_print"]) ? $privilege_list["allow_stock_opname_print"] : 0;
    }

    function stock_opname_get_list($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return array();

        $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_opname_get_list($filters, true, true);
        $no = isset($filters["start"]) ? (int) $filters["start"] : 0;

        $final_res = array();
        foreach ($res as $r) {
            $no++;

            $row = array();
            $row["no"] = $no;
            $row["uuid"] = trim($r["uuid"]);
            $row["number_formatted"] = $r["number_formatted"];
            $row["tanggal"] = date("d M Y", strtotime($r["tanggal"]));
            $row["keterangan"] = $r["keterangan"];

            $final_res[] = $row;
        }

        return $final_res;
    }

    function stock_opname_get_filtered_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_opname_get_list($filters, false, true);
        return count($res);
    }

    function stock_opname_get_total($filters = array(), $gudang_uuid = "")
    {
        if (!$this->allow) return 0;

        $filters["gudang_uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_opname_get_list($filters);
        return count($res);
    }

    function stock_opname_get($uuid = "")
    {
        if (!$this->allow) return array();

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_opname_get_list($filters);
        if (count($res) == 0) return array();
        $stock_opname = $res[0];
        $stock_opname_uuid = $stock_opname["uuid"];

        // **
        // get stock_opname detail list for stock_opname id
        $filters = array();
        $filters["stock_opname_uuid"] = $stock_opname_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->item_engine->stock_opname_detail_get_list($filters);
        $stock_opname["detail"] = $detail_list;

        return $stock_opname;
    }

    function stock_opname_delete($uuid = "")
    {
        if (!$this->allow_delete) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_opname_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Stock opname tidak ditemukan");
        $res = $res[0];
        $uuid = $res["uuid"];
        $stock_opname_uuid = $uuid;

        // **
        // get item id list from penjualan detail by penjualan id
        $filters = array();
        $filters["stock_opname_uuid"] = $stock_opname_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_opname_detail_get_list($filters);

        $item_uuid_list = array();
        foreach ($res as $r) {
            $item_uuid = $r['item_uuid'];
            $item_uuid_list[] = $item_uuid;
        }

        $this->db->trans_start();
        try {
            // **
            // hapus Stock opname
            $res = $this->item_engine->stock_opname_delete($stock_opname_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus Stock opname #001");

            // **
            // hapus Stock opname detail
            $res = $this->item_engine->stock_opname_detail_delete_by_stock_opname_uuid($stock_opname_uuid);
            if ($res == false ) throw new Exception("Gagal menghapus Stock opname #002");

            foreach ($item_uuid_list as $index => $item_uuid) {
                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menghapus Stock opname #003");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Stock opname telah dihapus");
    }

    function stock_opname_save()
    {
        $uuid = $this->input->post("uuid");
        $gudang_uuid = $this->input->post("gudang_uuid");
        $tanggal = $this->input->post("tanggal");
        $item_detail_json = $this->input->post("item_detail");
        $keterangan = $this->input->post("keterangan");

        // **
        // validasi
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal tidak valid");
        $item_detail_list = json_decode($item_detail_json, true);
        if (!is_array($item_detail_list)) $item_detail_list = array();
        if (count($item_detail_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Belum ada item yang dipilih pada stock opname");
                
        $tahun = date("Y", strtotime($tanggal . " 00:00:00"));
        $jam = date("H:i:s");

        $today_datetime = date("Y-m-d 23:59:59", strtotime($tanggal . " 00:00:00"));

        
        if(strtotime($tanggal) > time()) {
            return set_http_response_error(HTTP_BAD_REQUEST, "Tanggal Opname tidak boleh lebih dari tanggal hari ini");
        }

        // **
        // set gudang id
        // $settings = $this->settings_engine->get_settings('PERSEDIAAN_STOCK_OPNAME_DEFAULT_GUDANG_ID');
        // $gudang_uuid = $settings["_value"];

        
        // **
        // check gudang uuid
        $filters = array();
        $filters["uuid"] = $gudang_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->gudang_engine->gudang_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Gudang tidak ditemukan");
        $res = $res[0];
        $gudang_uuid = $res["uuid"];
        $gudang_kode = $res["kode"];
        $gudang_nama = $res["nama"];
        
        $created = date("Y-m-d H:i:s");
        $creator_user_uuid = $this->actor_user_uuid;
        $creator_user_name = $this->actor_user_name;
        $number = 0;
        $number_formatted = PREFIX_STOCK_OPNAME . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();

        $current_item_uuid_list = array();
        $old_tahun = $tahun;
        $old_gudang_kode = $gudang_kode;
        if (!empty($uuid)) {
            $filters = array();
            $filters["uuid"] = $uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->stock_opname_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Stock opname tidak ditemukan");
            $res = $res[0];
            $uuid = $res["uuid"];
            $stock_opname_uuid = $uuid;
            $created = $res["created"];
            $creator_user_uuid = $res["creator_user_uuid"];
            $creator_user_name = $res["creator_user_name"];
            $number = (int) $res["number"];
            $number_formatted = trim($res["number_formatted"]);
            $number_formatted = PREFIX_STOCK_OPNAME . "/" . $this->cabang_selected_kode . "/" . $gudang_kode . "/" . microtime_();

            $old_gudang_kode = $res["gudang_kode"];
            $old_tahun = $res["tahun"];

            // **
            // get current Stock opname detail list
            $filters = array();
            $filters["stock_opname_uuid"] = $stock_opname_uuid;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $list = $this->item_engine->stock_opname_detail_get_list($filters);
            foreach ($list as $l) {
                $current_item_uuid = $l['item_uuid'];
                $current_item_uuid_list[] = $current_item_uuid;
            }

            $jam = date("H:i:s", strtotime($res["tanggal"]));
        }

        if (empty($uuid)) {
            if (!$this->allow_create) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        } else {
            if (!$this->allow_update) return set_http_response_error(HTTP_FORBIDDEN, "Akses ditolak");
        }


        $final_item_list = array();
        $sub_total = 0;
        $item_uuid_list = array();
        foreach ($item_detail_list as $i) {
            $kode = $i["item_code"];
            $stock_fisik_satuan_terkecil = (int) $i["stock_fisik_satuan_terkecil"];

            if ((int) $stock_fisik_satuan_terkecil == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Stock fisik untuk Item dengan kode $kode harus diisi");

            $filters = array();
            $filters["kode"] = $kode;
            $filters["cabang_uuid"] = $this->cabang_selected_uuid;
            $res = $this->item_engine->item_get_list($filters);
            if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak ditemukan");
            $res = $res[0];
            $item_uuid = $res["uuid"];
            $item_kode = $kode;
            $item_barcode = $res["barcode"];
            $item_nama = $res["nama"];
            $item_struktur_satuan_harga_json = $res["struktur_satuan_harga_json"];
            $item_tipe = $res["tipe"];
            $item_margin_persen = $res["margin_persen"];
            $item_kategori_uuid = $res["item_kategori_uuid"];
            $item_kategori_nama = trim($res["item_kategori_nama"]);
            $struktur_satuan_harga_list = json_decode($item_struktur_satuan_harga_json, true);
            if (!is_array($struktur_satuan_harga_list) || count($struktur_satuan_harga_list) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode belum memiliki struktur satuan dan harga");

            $item_uuid_list[] = $item_uuid;

            $satuan_terkecil = "";
            foreach ($struktur_satuan_harga_list as $satuan => $s) {
                if (empty($satuan_terkecil)) $satuan_terkecil = $satuan;
            }
            if (empty($satuan_terkecil)) return set_http_response_error(HTTP_BAD_REQUEST, "Item dengan kode $kode tidak memiliki satuan terkecil");

            // **
            // ambil data stock system dari table jika edit
            $stock_opname_detail_uuid = "";                
            if ($stock_opname_uuid > 0) {
                $filters = array();
                $filters["item_uuid"] = $item_uuid;
                $filters["stock_opname_uuid"] = $stock_opname_uuid;
                $filters["cabang_uuid"] = $this->cabang_selected_uuid;
                $res = $this->item_engine->stock_opname_detail_get_list($filters);
                if (count($res) > 0) {
                    $res = $res[0];
                    $stock_opname_detail_uuid = $res["uuid"];
                    $stock_system_satuan_terkecil = (int) $res["stock_system_satuan_terkecil"];
                }
            }else{
                // **
                // get current stock system satuan terkecil untuk item id
                $total_stock_list = $this->item_engine->get_total_stock_for_date_range_and_item_uuid_list(array($item_uuid), false, $today_datetime, $gudang_uuid);
                $stock_system_satuan_terkecil = 0;
                if (isset($total_stock_list[$item_uuid])) {
                    $stock_system_satuan_terkecil = (int) $total_stock_list[$item_uuid];
                }
            }
                        
            $stock_selisih_satuan_terkecil = $stock_fisik_satuan_terkecil - $stock_system_satuan_terkecil;

            $stock_opname_detail_data_list = array(
                "uuid"     => "",
                "created" => date("Y-m-d H:i:s"),
                "creator_user_uuid" => $this->actor_user_uuid,
                "creator_user_name" => $this->actor_user_name,
                "last_updated" => date("Y-m-d H:i:s"),
                "last_updated_user_uuid" => $this->actor_user_uuid,
                "last_updated_user_name" => $this->actor_user_name,
                "stock_opname_uuid" => $uuid,
                "item_uuid" => $item_uuid,
                "item_kode" => $item_kode,
                "item_barcode" => $item_barcode,
                "item_nama" => $item_nama,
                "item_struktur_satuan_harga_json" => $item_struktur_satuan_harga_json,
                "item_tipe" => $item_tipe,
                "item_margin_persen" => $item_margin_persen,
                "item_kategori_uuid" => $item_kategori_uuid,
                "item_kategori_nama" => $item_kategori_nama,

                "satuan_terkecil" => $satuan_terkecil,
                "stock_system_satuan_terkecil" => $stock_system_satuan_terkecil,
                "stock_fisik_satuan_terkecil" => $stock_fisik_satuan_terkecil,
                "stock_selisih_satuan_terkecil" => $stock_selisih_satuan_terkecil,

                "cabang_uuid" => $this->cabang_selected_uuid,
            );

            $final_item_list[] = $stock_opname_detail_data_list;
        }

        // **
        // ambil deleted item id list
        $_temp = array_diff($current_item_uuid_list, $item_uuid_list);
        $deleted_item_data_list = array();
        foreach ($_temp as $index => $_item_uuid) {
            // **
            // get current total stock satuan terkecil
            $stock = $this->item_engine->item_get_total_stock_for_item_uuid($_item_uuid);
            $deleted_item_data_list[$_item_uuid]["stock"] = $stock;
        }

        $stock_opname_data = array(
            "uuid" => $uuid,
            "created" => $created,
            "creator_user_uuid" => $creator_user_uuid,
            "creator_user_name" => $creator_user_name,
            "last_updated" => date("Y-m-d H:i:s"),
            "last_updated_user_uuid" => $this->actor_user_uuid,
            "last_updated_user_name" => $this->actor_user_name,
            "number" => $number,
            "number_formatted" => $number_formatted,
            "tanggal" => $tanggal . " $jam",
            "tahun" => $tahun,
            
            "gudang_uuid" => $gudang_uuid,
            "gudang_kode" => $gudang_kode,
            "gudang_nama" => $gudang_nama,

            "keterangan" => $keterangan,

            "old_tahun" => $old_tahun, 
            "old_gudang_kode" => $old_gudang_kode,

            "cabang_uuid" => $this->cabang_selected_uuid,
            "cabang_kode" => $this->cabang_selected_kode,
        );

        $this->db->trans_start();
        try {
            // **
            // hapus semua stock_opname detail untuk stock_opname id jika stock_opname id != 0
            if (!empty($uuid)) {
                $res = $this->item_engine->stock_opname_detail_delete_by_stock_opname_uuid($uuid);
                if ($res == false ) throw new Exception("Gagal menyimpan Stock opname #001");
            }

            // **
            // simpan stock_opname
            $res = $this->item_engine->stock_opname_save($stock_opname_data);
            if ($res == false ) throw new Exception("Gagal menyimpan Stock opname #002");
            if (empty($uuid)) {
                $uuid = $res;
                $stock_opname_uuid = $uuid;
            }

            // **
            // simpan Stock opname detail
            foreach ($final_item_list as $stock_opname_detail) {
                $stock_opname_detail["stock_opname_uuid"] = $stock_opname_uuid;
                $item_uuid = $stock_opname_detail["item_uuid"];

                // **
                // simpan Stock opname detail
                $res = $this->item_engine->stock_opname_detail_save($stock_opname_detail);
                if ($res == false ) throw new Exception("Gagal menyimpan Stock opname #003");

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan Stock opname #004");
            }


            // **
            // update stock  untuk item yang dihapus
            foreach ($deleted_item_data_list as $_item_uuid => $data) {

                // **
                // get current total stock satuan terkecil
                $stock = $this->item_engine->item_get_total_stock_for_item_uuid($item_uuid);

                // update cache stock
                $res = $this->item_engine->item_update_cache_stock($_item_uuid, $stock);
                if ($res == false ) throw new Exception("Gagal menyimpan Stock opname #005");
            }
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return set_http_response_error(501, "Error : " . $e->getMessage());
        }

        $this->db->trans_commit();
        return set_http_response_success("Stock opname telah disimpan", array(), trim($uuid));
    }

    function item_get_detail_by_kode_and_tanggal()
    {
        $kode = get("item_kode");
        $tanggal = get("tanggal");
        $stock_opname_uuid = get("stock_opname_uuid");

        if (empty($kode)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_KODE");
        if (empty($tanggal)) return set_http_response_error(HTTP_BAD_REQUEST, "EMPTY_TANGGAL");

        $today_datetime = date("Y-m-d 23:59:59", strtotime($tanggal . " 00:00:00"));

        $filters = array();
        $filters["kode"] = $kode;
        $filters["arsip"] = 0;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->item_get_list($filters);
        if (count($res) == 0) return set_http_response_error(HTTP_BAD_REQUEST, "NO_DATA");
        $res = $res[0];
        $uuid = $res["uuid"];
        $item_uuid = $uuid;
        $current_stock = $this->item_engine->item_get_total_stock_for_item_uuid($uuid);
        $cek_stock_saat_penjualan = (int) $res["cek_stock_saat_penjualan"];
        $struktur_satuan_harga_json = $res["struktur_satuan_harga_json"];
        $struktur_satuan_harga_list = json_decode($struktur_satuan_harga_json, true);
        if (json_last_error_msg() != JSON_ERROR_NONE || !is_array($struktur_satuan_harga_list)) return set_http_response_error(HTTP_BAD_REQUEST, "Struktur satuan bermasalah");


        // **
        // get current stock system satuan terkecil untuk item id
        $total_stock_list = $this->item_engine->get_total_stock_for_date_range_and_item_uuid_list(array($item_uuid), false, $today_datetime);

        $stock_system_satuan_terkecil = 0;
        if (isset($total_stock_list[$item_uuid])) {
            $stock_system_satuan_terkecil = (int) $total_stock_list[$item_uuid];
        }
        $stock_fisik_satuan_terkecil = $stock_system_satuan_terkecil;
        $stock = $current_stock;
        $satuan_terkecil = "";
        foreach ($struktur_satuan_harga_list as $satuan => $l) {
            if (empty($satuan_terkecil)) $satuan_terkecil = $satuan;
        }

        $data = array();
        $data["kode"] = $res["kode"];
        $data["nama"] = $res["nama"];
        $data["nama_kategori"] = $res["item_kategori_nama"];
        $data["satuan"] = $satuan_terkecil;
        $data["stock_system"] = $stock_system_satuan_terkecil;
        $data["stock_fisik"] = $stock_fisik_satuan_terkecil;
        $data["stock_selisih"] = $stock_fisik_satuan_terkecil - $stock_system_satuan_terkecil;

        if (!empty($stock_opname_uuid)) {
            $filters = array();
            $filters["stock_opname_uuid"] = $stock_opname_uuid;
            $filters["item_uuid"] = $item_uuid;
            $res = $this->item_engine->stock_opname_detail_get_list($filters);
            if (count($res) > 0) {
                $res = $res[0];

                $data["stock_system"] = (int) $res["stock_system_satuan_terkecil"];
                $data["stock_fisik"] = (int) $res["stock_fisik_satuan_terkecil"];
                $data["stock_selisih"] = (int) $res["stock_selisih_satuan_terkecil"];
            }
        }

        return set_http_response_success("OK", $data);
    }

    function stock_opname_cetak($uuid = ""){
        
        $settings = get_session("settings");
        $header = array(
            "nama_toko" => $settings["TOKO_NAMA"]["_value"],
            "alamat_toko" => $settings["TOKO_ALAMAT"]["_value"],
            "no_telepon_toko" => $settings["TOKO_NO_TELEPON"]["_value"],
        );
        $body = array(
            "judul" => "Stock Opname",
            "no" => "",
            "tanggal" => "",
            "keterangan" => "",
            "content" => array()
        );
        $footer = array();
        $result = array(
            "header" => $header, 
            "body" => $body, 
            "footer" => $footer
        );

        // **
        // get detail
        if (!$this->allow) return $result;

        $filters = array();
        $filters["uuid"] = $uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $res = $this->item_engine->stock_opname_get_list($filters);
        if (count($res) == 0) return $result;
        $stock_opname = $res[0];
        $stock_opname_uuid = $stock_opname["uuid"];
        $number_formatted = $stock_opname["number_formatted"];
        $tanggal = date("d/m/Y", strtotime($stock_opname["tanggal"]));
        $keterangan = $stock_opname["keterangan"];
        $created = date("d/m/Y H:i:s", strtotime($stock_opname["created"]));
        $creator_user_name = $stock_opname["creator_user_name"];
        $last_updated = date("d/m/Y H:i:s", strtotime($stock_opname["last_updated"]));
        $last_updated_user_name = $stock_opname["last_updated_user_name"];
        $printed = date("d/m/Y H:i:s");
        $printed_user_name = $this->actor_user_name;

        $footer = array(
            "created" => $created, 
            "creator_user_name" => $creator_user_name, 
            "last_updated" => $last_updated, 
            "last_updated_user_name" => $last_updated_user_name, 
            "printed" => $printed, 
            "printed_user_name" => $printed_user_name
        );

        // **
        // get stock_opname detail list for stock_opname id
        $filters = array();
        $filters["stock_opname_uuid"] = $stock_opname_uuid;
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $detail_list = $this->item_engine->stock_opname_detail_get_list($filters);
        $stock_opname["detail"] = $detail_list;
        
        $body["judul"] = "Stock Opname";
        $body["tanggal"] = $tanggal;
        $body["no"] = $number_formatted;
        $body["keterangan"] = $keterangan;

        $contents = array();
        $no = 1;
        foreach($detail_list as $dl){
            $row = array(
                "no" => $no, 
                "kode" => $dl["item_kode"], 
                "nama" => $dl["item_nama"], 
                "kategori" => $dl["item_kategori_nama"], 
                "stock_system" => $dl["stock_system_satuan_terkecil"], 
                "stock_fisik" => $dl["stock_fisik_satuan_terkecil"], 
                "stock_selisih" => $dl["stock_selisih_satuan_terkecil"], 
                "satuan_terkecil" => $dl["satuan_terkecil"], 
            );

            $contents[] = $row;
            $no++;
        }

        $body["content"] = $contents;
        $result = array(
            "header" => $header, 
            "body" => $body, 
            "footer" => $footer
        );

        return $result;
    }

    function laporan_stock_opname(){
        $start_date = $this->input->get("start_date");
        $end_date = $this->input->get("end_date");
        $gudang_uuid = $this->input->get("gudang_uuid");

        
        $list = $this->settings_engine->get_all_settings();
        $settings_list = array();
        foreach($list as $l){
            $_key = $l['_key'];
            $settings_list[$_key] = $l;
        }

        $filter_gudang_uuid = "";
        $filter_gudang_nama = "Semua Gudang";

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
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        $filters["start_date"] = $start_date . " 00:00:00";
        $filters["end_date"] = $end_date . " 23:59:59";
        $filters["cabang_uuid"] = $this->cabang_selected_uuid;
        if(!empty($filter_gudang_uuid)) $filters["gudang_uuid"] = $filter_gudang_uuid;
        $detail_list = $this->item_engine->stock_opname_detail_get_list($filters);

        $contents = array();
        $no = 1;

        $total_stock_system = 0;
        $total_stock_fisik = 0;
        $total_stock_selisih = 0;

        foreach($detail_list as $dl){
            $stock_system = (double) $dl["stock_system_satuan_terkecil"];
            $stock_fisik = (double) $dl["stock_fisik_satuan_terkecil"];
            $stock_selisih = (double) $dl["stock_selisih_satuan_terkecil"];

            $row = array(
                "no" => $no, 
                "number_formatted" => $dl["number_formatted"], 
                "kode" => $dl["item_kode"], 
                "nama" => $dl["item_nama"], 
                "gudang_kode" => $dl["gudang_kode"], 
                "gudang_nama" => $dl["gudang_nama"], 
                "tanggal" => date("d-m-Y", strtotime($dl["tanggal"])), 
                "stock_system" => number_format($stock_system), 
                "stock_fisik" => number_format($stock_fisik), 
                "stock_selisih" => number_format($stock_selisih), 
                "satuan_terkecil" => $dl["satuan_terkecil"], 
            );

            $total_stock_system += $stock_system;
            $total_stock_fisik += $stock_fisik;
            $total_stock_selisih += $stock_selisih;

            $contents[] = $row;
            $no++;
        }
        
        $final_data = array(
            "header" => array(
                "nama_toko" => $settings_list["TOKO_NAMA"]["_value"],
                "alamat_toko" => $settings_list["TOKO_ALAMAT"]["_value"],
                "no_telepon_toko" => $settings_list["TOKO_NO_TELEPON"]["_value"],
            ),

            "filters" => array(
                "start_date" => date("d-m-Y", strtotime($start_date)),
                "end_date" => date("d-m-Y", strtotime($end_date)),
                "gudang_nama" => $filter_gudang_nama,
            ),

            "body" => $contents,

            "footer" => array(
                "total_stock_system" => $total_stock_system,
                "total_stock_fisik" => $total_stock_fisik,
                "total_stock_selisih" => $total_stock_selisih,
            ),
        );

        return $final_data;
    }
}
