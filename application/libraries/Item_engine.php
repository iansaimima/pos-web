<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Item_engine extends Db_engine
{
    //put your code here

    public function __construct()
    {
        parent::__construct();
    }

    // ====================================================
    // ** OBJECT ITEM KATEGORI
    // ====================================================
    function item_kategori_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "item_kategori.nama",
            "item_kategori.keterangan",
        );
        $column_order   = $column_search;
        $order          = array(
            "item_kategori.nama" => "asc"
        );

        $this->db->select("
            item_kategori.*

        ");
        $this->db->from("item_kategori");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("item_kategori.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("item_kategori.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function item_kategori_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("item_kategori");

        return $uuid;
    }

    function item_kategori_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();

        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["nama"] = $save_data["nama"];
        $data["keterangan"] = $save_data["keterangan"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);

        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("item_kategori");
        } else {
            $this->db->where("uuid", $uuid);
            $this->db->update("item_kategori");
        }

        return $uuid;
    }
    // ====================================================
    // ** END - OBJECT ITEM KATEGORI
    // ====================================================

    // ====================================================
    // ** OBJECT ITEM
    // ====================================================
    function item_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            "item.kode",
            "item.barcode",
            "item.nama",
            null,
            null,
            "item_kategori.nama",
            null,
            null,
            "item.tipe",
            "item.arsip",
        );
        $column_order   = $column_search;
        $order          = array(
            "item.nama" => "asc"
        );

        $this->db->select("
            item.*,
            item_kategori.nama item_kategori_nama
        ");
        $this->db->from("item");
        $this->db->join("item_kategori", "item_kategori.uuid = item.item_kategori_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("item.uuid", $value);
                    unset($filters[$key]);
                    break;

                case "search_keyword":
                    $this->db->group_start();
                    $this->db->like("item.kode", $value);
                    $this->db->or_like("item.barcode", $value);
                    $this->db->or_like("item.nama", $value);
                    $this->db->or_like("item_kategori.nama", $value);
                    $this->db->group_end();
                    
                    unset($filters[$key]);
                    break;

                case "item_kategori_uuid":
                    $this->db->where("item.item_kategori_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("item.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "arsip":
                    $this->db->where("item.arsip", (int) $value);
                    unset($filters[$key]);
                    break;
                case "kode":
                    $this->db->where("LOWER(kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "tipe":
                    $this->db->where("LOWER(tipe)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "include_tipe_jasa":
                    if ((int) $value == 0) {
                        $this->db->where("LOWER(tipe) !=", 'jasa');
                    }
                    unset($filters[$key]);
                    break;
                case "minimum":
                    if ((int) $value == 1) {
                        $this->db->where("cache_stock <", 'minimum_stock', false);
                    }
                    unset($filters[$key]);
                    break;
                case "cek_stock_saat_penjualan":                    
                    $this->db->where("item.cek_stock_saat_penjualan", (int) $value);
                    unset($filters[$key]);
                    break;
                case "barcode":
                    $this->db->where("barcode", $value);
                    unset($filters[$key]);
                    break;
                case "nama_like":
                    $this->db->like("nama", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function item_get_list_api($filters = array())
    {
        // **
        // generate column serach list
        $column_search_list  = array(
            array("" => ""),
            array("kode" => "item.kode"),
            array("barcode" => "item.barcode"),
            array("nama" => "item.nama"),
        );

        // **
        // generate pendukung column serach untuk pagination dan datatables
        $column_search = array();
        $params_search = array();
        $column_search_map = array();
        foreach ($column_search_list as $search) {
            if (!is_array($search)) continue;
            foreach ($search as $key => $value) {
                if (empty($key)) continue;
                $params_search[] = $value;
                $column_search[] = $value;
                $column_search_map[$key] = $value;
                break;
            }
        }

        $column_order   = $column_search;
        $order          = array(
            "item.kode"
        );

        $this->db->select("
            item.*,
            item_kategori.nama item_kategori_nama
        ");
        $this->db->from("item");
        $this->db->join("item_kategori", "item_kategori.uuid = item.item_kategori_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("item.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "uuid_list":
                    $this->db->where_in("item.uuid", $value);
                    unset($filters[$key]);
                    break;

                case "search_keyword":
                    $this->db->group_start();
                    $this->db->like("item.kode", $value);
                    $this->db->or_like("item.barcode", $value);
                    $this->db->or_like("item.nama", $value);
                    $this->db->or_like("item_kategori.nama", $value);
                    $this->db->group_end();
                    
                    unset($filters[$key]);
                    break;

                case "item_kategori_uuid":
                    $this->db->where("item.item_kategori_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("item.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "arsip":
                    $this->db->where("item.arsip", (int) $value);
                    unset($filters[$key]);
                    break;
                case "kode":
                    $this->db->where("LOWER(kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "tipe":
                    $this->db->where("LOWER(tipe)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "include_tipe_jasa":
                    if ((int) $value == 0) {
                        $this->db->where("LOWER(tipe) !=", 'jasa');
                    }
                    unset($filters[$key]);
                    break;
                case "minimum":
                    if ((int) $value == 1) {
                        $this->db->where("cache_stock <", 'minimum_stock', false);
                    }
                    unset($filters[$key]);
                    break;
                case "barcode":
                    $this->db->where("barcode", $value);
                    unset($filters[$key]);
                    break;
                case "nama_like":
                    $this->db->like("nama", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $this->generate_datatables_input($filters, $column_search_map, $column_order, $order);

        $res = $this->db->get()->result_array();
        return $res;
    }

    function item_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("item");

        return $uuid;
    }

    function item_set_arsip($uuid = "", $arsip = 0, $arsip_date = null, $actor_user_uuid = "", $actor_user_name = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);

        $this->db->set("arsip", $arsip);
        $this->db->set("arsip_date", $arsip_date);
        $this->db->set("arsip_user_uuid", $actor_user_uuid);
        $this->db->set("arsip_user_name", $actor_user_name);

        // wajib
        $this->db->set("last_updated", date("Y-m-d H:i:s"));
        $this->db->set("last_updated_user_uuid", $actor_user_uuid);
        $this->db->set("last_updated_user_name", $actor_user_name);

        $this->db->update("item");

        return $uuid;
    }

    function item_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();

        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];

        // wajib
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["kode"] = $save_data["kode"];
        $data["barcode"] = $save_data["barcode"];
        $data["nama"] = $save_data["nama"];
        $data["keterangan"] = $save_data["keterangan"];
        $data["struktur_satuan_harga_json"] = $save_data["struktur_satuan_harga_json"];
        $data["item_kategori_uuid"] = $save_data["item_kategori_uuid"];
        $data["tipe"] = $save_data["tipe"];
        $data["cek_stock_saat_penjualan"] = (int) $save_data["cek_stock_saat_penjualan"];
        $data["minimum_stock"] = $save_data["minimum_stock"];

        $data["harga_jual_tipe_jasa"] = (float) $save_data["harga_jual_tipe_jasa"];
        $data["satuan_tipe_jasa"] = $save_data["satuan_tipe_jasa"];

        $data["margin_persen"] = (float) $save_data["margin_persen"];
        $data["margin_nilai"] = (float) $save_data["margin_nilai"];
        $data["jenis_perhitungan_harga_jual"] = strtoupper($save_data["jenis_perhitungan_harga_jual"]);

        $data["cache_harga_pokok"] = (float) $save_data["cache_harga_pokok"];
        $data["cache_stock"] = (int) $save_data["cache_stock"];

        $data["arsip"] = $save_data["arsip"];
        $data["arsip_date"] = $save_data["arsip_date"];
        $data["arsip_user_uuid"] = $save_data["arsip_user_uuid"];
        $data["arsip_user_name"] = $save_data["arsip_user_name"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);

        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("item");
        } else {
            $this->db->where("uuid", $uuid);
            $this->db->update("item");
        }

        return $uuid;
    }

    function item_update_struktur_satuan_harga_json($uuid = "", $struktur_satuan_harga_json = "")
    {
        if (empty($uuid)) return false;
        if (!isJson($struktur_satuan_harga_json)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("struktur_satuan_harga_json", $struktur_satuan_harga_json);
        $this->db->update("item");

        return $uuid;
    }

    function item_update_cache_harga_pokok($uuid = "", $cache_harga_pokok = 0)
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("cache_harga_pokok", $cache_harga_pokok);
        $this->db->update("item");

        return $uuid;
    }

    function item_update_cache_stock($uuid = "", $cache_stock = 0)
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("cache_stock", $cache_stock);
        $this->db->update("item");

        return $uuid;
    }

    function item_get_total_stock_for_item_uuid($item_uuid = "", $gudang_uuid = false)
    {
        if (empty($item_uuid)) return false;
        if (empty($gudang_uuid)) $gudang_uuid = false;
        $item_uuid_list = array($item_uuid);
        $total_stock_list = $this->get_total_stock_for_date_range_and_item_uuid_list($item_uuid_list, false, false, $gudang_uuid);
        return $total_stock_list[$item_uuid];
    }

    function item_get_rata_rata_harga_beli_satuan_terkecil_for_item_uuid($item_uuid = "")
    {
        if (empty($item_uuid)) return false;

        $harga_beli_satuan_terkecil_list = array();

        // **
        // get rata-rata from pembelian_detail
        $this->db->select('harga_beli_satuan_terkecil');
        $this->db->from("pembelian_detail");
        $this->db->where("item_uuid", $item_uuid);
        $this->db->where("LOWER(item_tipe)", "barang");
        $res = $this->db->get()->result_array();
        if (count($res) > 0) {
            foreach ($res as $r) {
                $harga_beli_satuan_terkecil_list[] = (float) $r["harga_beli_satuan_terkecil"];
            }
        }

        // **
        // get rata-rata from stock awal
        $this->db->select('harga_beli_satuan_terkecil');
        $this->db->from("stock_awal");
        $this->db->where("item_uuid", $item_uuid);
        $this->db->where("LOWER(item_tipe)", "barang");
        $res = $this->db->get()->result_array();
        if (count($res) > 0) {
            foreach ($res as $r) {
                $harga_beli_satuan_terkecil_list[] = (float) $r["harga_beli_satuan_terkecil"];
            }
        }

        // **
        // get rata-rata from item transfer ke_gudang_uuid
        // $this->db->select('harga_beli_satuan_terkecil');
        // $this->db->from("item_transfer_detail");
        // $this->db->where("item_uuid", $item_uuid);
        // $this->db->where("LOWER(item_tipe)", "barang");
        // $res = $this->db->get()->result_array();
        // if (count($res) > 0) {
        //     foreach ($res as $r) {
        //         $harga_beli_satuan_terkecil_list[] = (float) $r["harga_beli_satuan_terkecil"];
        //     }
        // }

        $rata_rata_harga_beli_satuan_terkecil = 0;
        if (count($harga_beli_satuan_terkecil_list) > 0) {
            $rata_rata_harga_beli_satuan_terkecil = array_sum($harga_beli_satuan_terkecil_list) / count($harga_beli_satuan_terkecil_list);
        }

        return $rata_rata_harga_beli_satuan_terkecil;
    }
    // ====================================================
    // ** END - OBJECT ITEM
    // ====================================================

    // ====================================================
    // ** BEGIN - OBJECT STOCK AWAL
    // ====================================================
    function stock_awal_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "stock_awal.item_kode",
            "stock_awal.item_nama",
            "stock_awal.item_kategori_nama",
            "stock_awal.jumlah",
            "stock_awal.satuan",
            "stock_awal.harga_beli_satuan",
            "stock_awal.total",
        );
        $column_order   = $column_search;
        $order          = array(
            "stock_awal.item_kode" => "asc"
        );

        $this->db->select("
            stock_awal.*
        ");
        $this->db->from("stock_awal");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("stock_awal.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("stock_awal.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "number_formatted":
                    $this->db->where("stock_awal.number_formatted", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("stock_awal.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("stock_awal.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("stock_awal.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("stock_awal.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_tipe":
                    $this->db->where("LOWER(stock_awal.item_tipe)", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("stock_awal.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("stock_awal.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("stock_awal.item_kategori_uuid", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function stock_awal_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("stock_awal");

        return $uuid;
    }

    function stock_awal_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();

        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["tanggal"] = $save_data["tanggal"];

        $data["item_uuid"] = $save_data["item_uuid"];
        $data["item_kode"] = $save_data["item_kode"];
        $data["item_barcode"] = $save_data["item_barcode"];
        $data["item_nama"] = $save_data["item_nama"];
        $data["item_struktur_satuan_harga_json"] = $save_data["item_struktur_satuan_harga_json"];
        $data["item_tipe"] = $save_data["item_tipe"];
        $data["item_kategori_uuid"] = $save_data["item_kategori_uuid"];
        $data["item_kategori_nama"] = $save_data["item_kategori_nama"];

        $data["jumlah"] = $save_data["jumlah"];
        $data["satuan"] = $save_data["satuan"];
        $data["harga_beli_satuan"] = $save_data["harga_beli_satuan"];
        $data["harga_beli_satuan_terkecil"] = $save_data["harga_beli_satuan_terkecil"];
        $data["jumlah_satuan_terkecil"] = $save_data["jumlah_satuan_terkecil"];

        $data["gudang_uuid"] = $save_data["gudang_uuid"];
        $data["gudang_kode"] = $save_data["gudang_kode"];
        $data["gudang_nama"] = $save_data["gudang_nama"];

        $data["total"] = $save_data["total"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];
        $cabang_kode = $save_data["cabang_kode"];

        $this->db->set($data);

        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("stock_awal");
        } else {
            $this->db->where("uuid", $uuid);
            $this->db->update("stock_awal");
        }

        return $uuid;
    }

    function stock_awal_get_next_number($tahun = "", $gudang_kode = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("stock_awal");
        $this->db->where("tahun", $tahun);
        $this->db->where("gudang_kode", $gudang_kode);
        $this->db->where("cabang_uuid", $cabang_uuid);
        $res = $this->db->get()->result_array();
        $res = $res[0];
        $max = (int)$res["max_number"];

        $next = 0;
        if ($max == 0) {
            $next = 1;
        } else {
            $next = $max + 1;
        }

        $year_code = date("y", strtotime("$tahun-01-01 00:00:00"));

        $number = $next;
        $number_formatted = PREFIX_STOCK_AWAL . "/$cabang_kode/$gudang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
    // ====================================================
    // ** END - OBJECT STOCK AWAL
    // ====================================================

    // ====================================================
    // ** BEGIN - OBJECT STOCK OPNAME
    // ====================================================
    function stock_opname_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "stock_opname.number_formatted",
            "stock_opname.tanggal",
            "stock_opname.keterangan",
        );
        $column_order   = $column_search;
        $order          = array(
            "stock_opname.number_formatted" => "asc"
        );

        $this->db->select("
            stock_opname.*
        ");
        $this->db->from("stock_opname");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("stock_opname.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("stock_opname.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("stock_opname.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "number_formatted":
                    $this->db->where("stock_opname.number_formatted", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("stock_opname.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("stock_opname.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function stock_opname_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("stock_opname");

        return $uuid;
    }

    function stock_opname_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();

        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];

        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["number"] = $save_data["number"];
        $data["number_formatted"] = $save_data["number_formatted"];

        $data["tanggal"] = $save_data["tanggal"];
        $data["tahun"] = $save_data["tahun"];

        $data["gudang_uuid"] = $save_data['gudang_uuid'];
        $data["gudang_kode"] = $save_data['gudang_kode'];
        $data["gudang_nama"] = $save_data['gudang_nama'];

        $data["keterangan"] = $save_data['keterangan'];

        $data["cabang_uuid"] = $save_data['cabang_uuid'];
        $cabang_kode = $save_data['cabang_kode'];

        $this->db->set($data);

        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("stock_opname");
        } else {

            $this->db->where("uuid", $uuid);
            $this->db->update("stock_opname");
        }
        return $uuid;
    }

    function stock_opname_get_next_number($tahun = "", $gudang_kode = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("stock_opname");
        $this->db->where("tahun", $tahun);
        $this->db->where("gudang_kode", $gudang_kode);
        $this->db->where("cabang_uuid", $cabang_uuid);
        $res = $this->db->get()->result_array();
        $res = $res[0];
        $max = (int)$res["max_number"];

        $next = 0;
        if ($max == 0) {
            $next = 1;
        } else {
            $next = $max + 1;
        }

        $year_code = date("y", strtotime("$tahun-01-01 00:00:00"));

        $number = $next;
        $number_formatted = PREFIX_STOCK_OPNAME . "$cabang_kode/$gudang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
    // ====================================================
    // ** END - OBJECT STOCK OPNAME
    // ====================================================

    // ====================================================
    // ** BEGIN - OBJECT STOCK OPNAME DETAIL
    // ====================================================
    function stock_opname_detail_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array();
        $column_order   = $column_search;
        $order          = array();

        $this->db->select("
            stock_opname_detail.*,
            stock_opname.tanggal,
            stock_opname.number_formatted,
            stock_opname.gudang_kode,
            stock_opname.gudang_nama,
        ");
        $this->db->from("stock_opname_detail");
        $this->db->join("stock_opname", "stock_opname.uuid = stock_opname_detail.stock_opname_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("stock_opname_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("stock_opname_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("stock_opname.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("stock_opname.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("stock_opname.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "stock_opname_uuid":
                    $this->db->where("stock_opname_detail.stock_opname_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("stock_opname.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "stock_opname_hash_uuid":
                    $this->db->where("md5(stock_opname_detail.stock_opname_uuid)", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("stock_opname_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(stock_opname_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_tipe":
                    $this->db->where("LOWER(stock_opname_detail.item_tipe)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan_terkecil":
                    $this->db->where("LOWER(stock_opname_detail.satuan_terkecil)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("stock_opname_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("stock_opname_detail.item_kategori_uuid", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function stock_opname_detail_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("stock_opname_detail");

        return $uuid;
    }

    function stock_opname_detail_delete_by_stock_opname_uuid($stock_opname_uuid = "")
    {
        if (empty($stock_opname_uuid)) return false;

        $this->db->where("stock_opname_uuid", $stock_opname_uuid);
        $this->db->delete("stock_opname_detail");

        return $stock_opname_uuid;
    }

    function stock_opname_detail_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();

        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["stock_opname_uuid"] = $save_data["stock_opname_uuid"];

        $data["item_uuid"] = $save_data["item_uuid"];
        $data["item_kode"] = $save_data["item_kode"];
        $data["item_barcode"] = $save_data["item_barcode"];
        $data["item_nama"] = $save_data["item_nama"];
        $data["item_struktur_satuan_harga_json"] = $save_data["item_struktur_satuan_harga_json"];
        $data["item_tipe"] = $save_data["item_tipe"];
        $data["item_kategori_uuid"] = $save_data["item_kategori_uuid"];
        $data["item_kategori_nama"] = $save_data["item_kategori_nama"];

        $data["satuan_terkecil"] = $save_data["satuan_terkecil"];
        $data["stock_system_satuan_terkecil"] = $save_data["stock_system_satuan_terkecil"];
        $data["stock_fisik_satuan_terkecil"] = $save_data["stock_fisik_satuan_terkecil"];
        $data["stock_selisih_satuan_terkecil"] = $save_data["stock_selisih_satuan_terkecil"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);
        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("stock_opname_detail");
        } else {
            $this->db->where("uuid", $uuid);
            $this->db->update("stock_opname_detail");
        }

        return $uuid;
    }

    function stock_opname_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_stock_opname_uuid($filters = array())
    {
        $this->db->select("
            stock_opname.number_formatted stock_opname_number_formatted,
            stock_opname_detail.*,
            stock_opname.tanggal,
            sum(stock_opname_detail.stock_selisih_satuan_terkecil) total_stock_selisih_satuan_terkecil,  
        ");
        $this->db->from("stock_opname_detail");
        $this->db->join("stock_opname", "stock_opname.uuid = stock_opname_detail.stock_opname_uuid", "left");
        $this->db->group_by("stock_opname_detail.item_uuid");
        $this->db->group_by("stock_opname_detail.stock_opname_uuid");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("stock_opname_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("stock_opname_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("stock_opname.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("stock_opname.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("stock_opname.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "stock_opname_uuid":
                    $this->db->where("stock_opname_detail.stock_opname_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("stock_opname_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(stock_opname_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan_terkecil":
                    $this->db->where("LOWER(stock_opname_detail.satuan_terkecil)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("stock_opname_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("stock_opname_detail.item_kategori_uuid", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }
    // ====================================================
    // ** END - OBJECT STOCK OPNAME DETAIL
    // ====================================================

    // =======================================================================================
    // ITEM TRANSFER
    // =======================================================================================
    function item_transfer_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "item_transfer.number_formatted",
            "item_transfer.tanggal",
            "item_transfer.dari_gudang_nama",
            "item_transfer.ke_gudang_nama",
        );
        $column_order   = $column_search;
        $order          = array(
            "item_transfer.number_formatted" => "asc"
        );

        $this->db->select("
            item_transfer.*
        ");
        $this->db->from("item_transfer");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("item_transfer.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("item_transfer.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("item_transfer.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("item_transfer.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "number_formatted":
                    $this->db->where("item_transfer.number_formatted", $value);
                    unset($filters[$key]);
                    break;
                case "dari_gudang_uuid":
                    $this->db->where("item_transfer.dari_gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "ke_gudang_uuid":
                    $this->db->where("item_transfer.ke_gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function item_transfer_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("item_transfer");

        return $uuid;
    }

    function item_transfer_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];

        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["number"] = $save_data["number"];
        $data["number_formatted"] = $save_data["number_formatted"];

        $data["tanggal"] = $save_data["tanggal"];
        $data["tahun"] = $save_data["tahun"];

        $data["dari_gudang_uuid"] = $save_data['dari_gudang_uuid'];
        $data["dari_gudang_kode"] = $save_data['dari_gudang_kode'];
        $data["dari_gudang_nama"] = $save_data['dari_gudang_nama'];

        $data["ke_gudang_uuid"] = $save_data['ke_gudang_uuid'];
        $data["ke_gudang_kode"] = $save_data['ke_gudang_kode'];
        $data["ke_gudang_nama"] = $save_data['ke_gudang_nama'];

        $data["keterangan"] = $save_data["keterangan"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $tahun = $save_data["tahun"];
        $cabang_uuid = $save_data["cabang_uuid"];
        $cabang_kode = $save_data["cabang_kode"];
        $dari_gudang_kode = $save_data["dari_gudang_kode"];

        $this->db->set($data);
        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert('item_transfer');
        } else {
            $this->db->where("uuid", $uuid);
            $this->db->update('item_transfer');
        }
        return $uuid;
    }

    function item_transfer_get_next_number($tahun = "", $dari_gudang_kode = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("item_transfer");
        $this->db->where("tahun", $tahun);
        $this->db->where("dari_gudang_kode", $dari_gudang_kode);
        $this->db->where("cabang_uuid", $cabang_uuid);
        $res = $this->db->get()->result_array();
        $res = $res[0];
        $max = (int)$res["max_number"];

        $next = 0;
        if ($max == 0) {
            $next = 1;
        } else {
            $next = $max + 1;
        }

        $year_code = date("y", strtotime("$tahun-01-01 00:00:00"));

        $number = $next;
        $number_formatted = PREFIX_ITEM_TRANSFER . "/$cabang_kode/$dari_gudang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
    // =======================================================================================
    // END - ITEM TRANSFER
    // =======================================================================================

    // =======================================================================================
    // ITEM TRANSFER DETAIL
    // =======================================================================================
    function item_transfer_detail_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array();
        $column_order   = $column_search;
        $order          = array();

        $this->db->select("
            item_transfer_detail.*,
            item_transfer.number_formatted item_transfer_number_formatted,
            item_transfer.tanggal
        ");
        $this->db->from("item_transfer_detail");
        $this->db->join("item_transfer", "item_transfer.uuid = item_transfer_detail.item_transfer_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("item_transfer_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("item_transfer_detail.cabang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "dari_gudang_uuid":
                    $this->db->where("item_transfer.dari_gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "ke_gudang_uuid":
                    $this->db->where("item_transfer.ke_gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("item_transfer.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("item_transfer.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "item_transfer_uuid":
                    $this->db->where("item_transfer_detail.item_transfer_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_transfer_uuid_list":
                    $this->db->where_in("item_transfer_detail.item_transfer_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("item_transfer_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(item_transfer_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_tipe":
                    $this->db->where("LOWER(item_transfer_detail.item_tipe)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan":
                    $this->db->where("LOWER(item_transfer_detail.satuan)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("item_transfer_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("item_transfer_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function item_transfer_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_item_transfer_uuid($filters = array())
    {

        $this->db->select("
            item_transfer.number_formatted item_transfer_number_formatted,
            item_transfer.tanggal,
            sum(item_transfer_detail.jumlah_satuan_terkecil) total_jumlah_satuan_terkecil,            
        ");
        $this->db->from("item_transfer_detail");
        $this->db->join("item_transfer", "item_transfer.uuid = item_transfer_detail.item_transfer_uuid", "left");
        $this->db->group_by("item_transfer_detail.item_uuid");
        $this->db->group_by("item_transfer_detail.item_transfer_uuid");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("item_transfer_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("item_transfer_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("item_transfer.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("item_transfer.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "item_transfer_uuid":
                    $this->db->where("item_transfer_detail.item_transfer_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "dari_gudang_uuid":
                    $this->db->where("item_transfer.dari_gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "ke_gudang_uuid":
                    $this->db->where("item_transfer.ke_gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_transfer_uuid_list":
                    $this->db->where_in("item_transfer_detail.item_transfer_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("item_transfer_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(item_transfer_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan":
                    $this->db->where("LOWER(item_transfer_detail.satuan)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("item_transfer_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("item_transfer_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function item_transfer_detail_delete($uuid = "")
    {
        if (empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("item_transfer_detail");

        return $uuid;
    }

    function item_transfer_detail_delete_by_item_transfer_uuid($item_transfer_uuid = "")
    {
        if (empty($item_transfer_uuid)) return false;

        $this->db->where("item_transfer_uuid", $item_transfer_uuid);
        $this->db->delete("item_transfer_detail");

        return $item_transfer_uuid;
    }

    function item_transfer_detail_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["item_transfer_uuid"] = $save_data["item_transfer_uuid"];
        $data["item_uuid"] = $save_data["item_uuid"];
        $data["item_kode"] = $save_data["item_kode"];
        $data["item_barcode"] = $save_data["item_barcode"];
        $data["item_nama"] = $save_data["item_nama"];
        $data["item_struktur_satuan_harga_json"] = $save_data["item_struktur_satuan_harga_json"];
        $data["item_tipe"] = $save_data["item_tipe"];
        $data["item_kategori_uuid"] = $save_data["item_kategori_uuid"];
        $data["item_kategori_nama"] = $save_data["item_kategori_nama"];

        $data["jumlah"] = $save_data["jumlah"];
        $data["satuan"] = $save_data["satuan"];

        $data["harga_beli_satuan"] = $save_data["harga_beli_satuan"];
        $data["harga_beli_satuan_terkecil"] = $save_data["harga_beli_satuan_terkecil"];

        $data["jumlah_satuan_terkecil"] = $save_data["jumlah_satuan_terkecil"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);

        if (empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("item_transfer_detail");
        } else {
            $this->db->where("uuid", $uuid);
            $this->db->update("item_transfer_detail");
        }
        return $uuid;
    }

    function item_transfer_detail_harga_beli_satuan_terkecil_list_by_item_uuid($item_uuid = "")
    {
        if (empty($item_uuid)) return array();

        $this->db->select("harga_beli_satuan_terkecil");
        $this->db->from("item_transfer_detail");
        $this->db->where("item_uuid", $item_uuid);
        $res = $this->db->get()->result_array();

        return $res;
    }
    // =======================================================================================
    // END -- ITEM TRANSFER DETAIL
    // =======================================================================================


    function get_total_stock_for_date_range_and_item_uuid_list($item_uuid_list = array(), $start_date = false, $end_date = false, $gudang_uuid = false)
    {
        if (count($item_uuid_list) == 0) return array();

        $transaksi_engine = new Transaksi_engine();

        $total_stock_masuk_list = array();
        $total_stock_keluar_list = array();
        $total_stock_list = array();

        foreach ($item_uuid_list as $index => $item_uuid) {
            $total_stock_masuk_list[$item_uuid] = 0;
            $total_stock_keluar_list[$item_uuid] = 0;
            $total_stock_list[$item_uuid] = 0;
        }

        //
        // -- ambil stock masuk dari stock awal
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["gudang_uuid"] = $gudang_uuid;
        $stock_awal_list = $this->stock_awal_get_list($filters);

        // -- ambil stock masuk dari pembelian
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["gudang_uuid"] = $gudang_uuid;
        $pembelian_list = $transaksi_engine->pembelian_detail_get_list($filters);

        // -- ambil stock masuk dari penjualan retur
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["gudang_uuid"] = $gudang_uuid;
        $penjualan_retur_list = $transaksi_engine->penjualan_retur_detail_get_list($filters);

        // -- ambil stock masuk dari item transfer ke_gudang_uuid
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["ke_gudang_uuid"] = $gudang_uuid;
        $item_transfer_masuk_list = $this->item_transfer_detail_get_list($filters);

        // **
        // hitung stock masuk
        foreach ($stock_awal_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l["jumlah_satuan_terkecil"];

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil;
        }
        foreach ($pembelian_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l['jumlah_satuan_terkecil'];

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil;
        }
        foreach ($penjualan_retur_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l["jumlah_satuan_terkecil"];

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil;
        }
        foreach ($item_transfer_masuk_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l["jumlah_satuan_terkecil"];

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil;
        }

        // -- ambil stock keluar dari pembelian retur
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["gudang_uuid"] = $gudang_uuid;
        $pembelian_retur_list = $transaksi_engine->pembelian_retur_detail_get_list($filters);

        // -- ambil stock keluar dari penjualan
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["gudang_uuid"] = $gudang_uuid;
        $penjualan_list = $transaksi_engine->penjualan_detail_get_list($filters);

        // -- ambil stock keluar dari stock opname
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["gudang_uuid"] = $gudang_uuid;
        $stock_opname_list = $this->stock_opname_detail_get_list($filters);

        // -- ambil stock keluar dari item transfer dari_gudang_uuid
        $filters = array();
        $filters["item_uuid_list"] = $item_uuid_list;
        if ($start_date) $filters["start_date"]  = $start_date;
        if ($end_date)   $filters["end_date"]    = $end_date;
        if ($gudang_uuid) $filters["dari_gudang_uuid"] = $gudang_uuid;
        $item_transfer_keluar_list = $this->item_transfer_detail_get_list($filters);

        // **
        // hitung stock keluar
        foreach ($penjualan_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l['jumlah_satuan_terkecil'];

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil * -1;
        }
        foreach ($pembelian_retur_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l['jumlah_satuan_terkecil'] * -1;

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil;
        }

        foreach ($stock_opname_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l['stock_selisih_satuan_terkecil'];

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil;
        }

        foreach ($item_transfer_keluar_list as $l) {
            $item_uuid = $l["item_uuid"];
            $jumlah_satuan_terkecil = (float) $l['jumlah_satuan_terkecil'] * -1;

            $total_stock_list[$item_uuid] += $jumlah_satuan_terkecil;
        }

        return $total_stock_list;
    }
}
