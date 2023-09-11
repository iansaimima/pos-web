<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Transaksi_engine extends Db_engine
{

    public function __construct()
    {
        parent::__construct();
    }

    // =======================================================================================
    // PEMBELIAN
    // =======================================================================================
    function pembelian_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "pembelian.number_formatted",
            "pembelian.tanggal",
            "pembelian.pemasok_number_formatted",
            "pembelian.pemasok_nama",
            "pembelian.total_akhir",
            "pembelian.sisa",
            "pembelian.lunas",
        );
        $column_order   = $column_search;
        $order          = array(
            "pembelian.number_formatted" => "asc"
        );

        $this->db->select("
            pembelian.*,
            kas_akun.nama kas_akun_nama
        ");
        $this->db->join('kas_akun', 'pembelian.kas_akun_uuid = kas_akun.uuid', 'left');
        $this->db->from("pembelian");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembelian.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pembelian.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("pembelian.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("pembelian.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "number_formatted":
                    $this->db->where("pembelian.number_formatted", $value);
                    unset($filters[$key]);
                    break;
                case "pemasok_uuid":
                    $this->db->where("pembelian.pemasok_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("pembelian.gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "kas_akun_uuid":
                    $this->db->where("pembelian.kas_akun_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "lunas":
                    $this->db->where("pembelian.lunas", (int) $value);
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

    function pembelian_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("pembelian");

        return $uuid;
    }

    function pembelian_update_kas_alur_uuid($uuid = "", $kas_alur_uuid = "")
    {
        if(empty($uuid)) return false;
        if(empty($kas_alur_uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("kas_alur_uuid", $kas_alur_uuid);
        $this->db->update("pembelian");

        return $uuid;
    }

    function pembelian_save($save_data = array())
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
        $data["no_nota_vendor"] = $save_data["no_nota_vendor"];

        $data["tanggal"] = $save_data["tanggal"];
        $data["tahun"] = $save_data["tahun"];

        $data["pemasok_uuid"] = $save_data["pemasok_uuid"];
        $data["pemasok_number_formatted"] = $save_data["pemasok_number_formatted"];
        $data["pemasok_nama"] = $save_data["pemasok_nama"];
        $data["pemasok_alamat"] = $save_data["pemasok_alamat"];
        $data["pemasok_no_telepon"] = $save_data["pemasok_no_telepon"];

        $data["gudang_uuid"] = $save_data['gudang_uuid'];
        $data["gudang_kode"] = $save_data['gudang_kode'];
        $data["gudang_nama"] = $save_data['gudang_nama'];

        $data["kas_akun_uuid"] = $save_data['kas_akun_uuid'];
        $data["kas_alur_uuid"] = $save_data['kas_alur_uuid'];

        $data["sub_total"] = $save_data["sub_total"];
        $data["potongan"] = $save_data["potongan"];
        $data["total_akhir"] = $save_data["total_akhir"];
        $data["bayar"] = $save_data["bayar"];
        $data["sisa"] = $save_data["sisa"];
        $data["lunas"] = $save_data["lunas"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];
        $cabang_kode = $save_data["cabang_kode"];

        $this->db->set($data);
        if(empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert('pembelian');
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update('pembelian');
        }
        return $uuid;
    }

    function pembelian_get_next_number($tahun = "", $gudang_kode = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("pembelian");
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
        $number_formatted = PREFIX_PEMBELIAN . "/$cabang_kode/$gudang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
    // =======================================================================================
    // END - PEMBELIAN
    // =======================================================================================

    // =======================================================================================
    // PEMBELIAN DETAIL
    // =======================================================================================
    function pembelian_detail_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "pembelian_detail.item_kode", 
            "pembelian_detail.item_barcode",
            "pembelian_detail.item_nama",
        );
        $column_order   = $column_search;
        $order          = array();

        $this->db->select("
            pembelian_detail.*,
            pembelian.number_formatted pembelian_number_formatted,
            pembelian.tanggal
        ");
        $this->db->from("pembelian_detail");
        $this->db->join("pembelian", "pembelian.uuid = pembelian_detail.pembelian_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembelian_detail.uuid", $value);
                    unset($filters[$key]);
                    break;                
                case "cabang_uuid":
                    $this->db->where("pembelian.cabang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("pembelian.gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("pembelian.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("pembelian.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_uuid":
                    $this->db->where("pembelian_detail.pembelian_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_uuid_list":
                    $this->db->where_in("pembelian_detail.pembelian_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("pembelian_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(pembelian_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_tipe":
                    $this->db->where("LOWER(pembelian_detail.item_tipe)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan":
                    $this->db->where("LOWER(pembelian_detail.satuan)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("pembelian_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("pembelian_detail.item_kategori_uuid",  $value);
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

    function pembelian_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_pembelian_uuid($filters = array())
    {

        $this->db->select("
            pembelian.number_formatted pembelian_number_formatted,
            pembelian.tanggal,
            sum(pembelian_detail.jumlah_satuan_terkecil) total_jumlah_satuan_terkecil,            
        ");
        $this->db->from("pembelian_detail");
        $this->db->join("pembelian", "pembelian.uuid = pembelian_detail.pembelian_uuid", "left");
        $this->db->group_by("pembelian_detail.item_uuid");
        $this->db->group_by("pembelian_detail.pembelian_uuid");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembelian_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pembelian_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("pembelian.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("pembelian.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_uuid":
                    $this->db->where("pembelian_detail.pembelian_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("pembelian.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_uuid_list":
                    $this->db->where_in("pembelian_detail.pembelian_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("pembelian_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(pembelian_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan":
                    $this->db->where("LOWER(pembelian_detail.satuan)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("pembelian_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("pembelian_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function pembelian_detail_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("pembelian_detail");

        return $uuid;
    }

    function pembelian_detail_delete_by_pembelian_uuid($pembelian_uuid = "")
    {
        if (empty($pembelian_uuid)) return false;

        $this->db->where("pembelian_uuid", $pembelian_uuid);
        $this->db->delete("pembelian_detail");

        return $pembelian_uuid;
    }

    function pembelian_detail_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["pembelian_uuid"] = $save_data["pembelian_uuid"];
        $data["item_uuid"] = $save_data["item_uuid"];
        $data["item_kode"] = $save_data["item_kode"];
        $data["item_barcode"] = $save_data["item_barcode"];
        $data["item_nama"] = $save_data["item_nama"];
        $data["item_struktur_satuan_harga_json"] = $save_data["item_struktur_satuan_harga_json"];
        $data["item_tipe"] = $save_data["item_tipe"];
        $data["item_kategori_uuid"] = $save_data["item_kategori_uuid"];
        $data["item_kategori_nama"] = $save_data["item_kategori_nama"];

        $data["harga_beli_satuan_terkecil"] = $save_data["harga_beli_satuan_terkecil"];
        $data["jumlah_satuan_terkecil"] = $save_data["jumlah_satuan_terkecil"];

        $data["jumlah"] = $save_data["jumlah"];
        $data["satuan"] = $save_data["satuan"];
        $data["harga_beli_satuan"] = $save_data["harga_beli_satuan"];
        $data["potongan_persen"] = $save_data["potongan_persen"];
        $data["potongan_harga"] = $save_data["potongan_harga"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);

        if(empty($uuid)){            
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("pembelian_detail");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("pembelian_detail");
        }
        return $uuid;
    }

    function pembelian_detail_harga_beli_satuan_terkecil_list_by_item_uuid($item_uuid = "")
    {
        if (empty($item_uuid)) return array();

        $this->db->select("harga_beli_satuan_terkecil");
        $this->db->from("pembelian_detail");
        $this->db->where("item_uuid", $item_uuid);
        $res = $this->db->get()->result_array();

        return $res;
    }
    // =======================================================================================
    // END -- PEMBELIAN DETAIL
    // =======================================================================================

    // =======================================================================================
    // PEMBELIAN RETUR
    // =======================================================================================
    function pembelian_retur_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "pembelian_retur.number_formatted",
            "pembelian_retur.pembelian_number_formatted",
            "pembelian_retur.tanggal",
            "pembelian.pemasok_number_formatted",
            "pembelian.pemasok_nama",
            "pembelian_retur.total_akhir",
            "pembelian_retur.sisa",
            "pembelian_retur.lunas",
        );
        $column_order   = $column_search;
        $order          = array(
            "pembelian_retur.number_formatted" => "asc"
        );

        $this->db->select("
            pembelian_retur.*,
            pembelian.no_nota_vendor,
            pembelian.pemasok_number_formatted, 
            pembelian.pemasok_nama, 
            pembelian.pemasok_alamat, 
            pembelian.pemasok_no_telepon,
            kas_akun.nama kas_akun_nama            
        ");
        $this->db->join('kas_akun', 'pembelian_retur.kas_akun_uuid = kas_akun.uuid', 'left');
        $this->db->join('pembelian', 'pembelian_retur.pembelian_uuid = pembelian.uuid', 'left');
        $this->db->from("pembelian_retur");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembelian_retur.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pembelian_retur.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "number_formatted":
                    $this->db->where("pembelian_retur.number_formatted", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_uuid":
                    $this->db->where("pembelian_retur.pembelian_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pemasok_uuid":
                    $this->db->where("pembelian_retur.pemasok_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("pembelian_retur.gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "kas_akun_uuid":
                    $this->db->where("pembelian_retur.kas_akun_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "lunas":
                    $this->db->where("pembelian_retur.lunas", (int) $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("pembelian_retur.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("pembelian_retur.tanggal <=", $value);
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

    function pembelian_retur_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("pembelian_retur");

        return $uuid;
    }

    function pembelian_retur_update_kas_alur_uuid($uuid = "", $kas_alur_uuid = "")
    {
        if(empty($uuid)) return false;
        if(empty($kas_alur_uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("kas_alur_uuid", $kas_alur_uuid);
        $this->db->update("pembelian_retur");

        return $uuid;
    }

    function pembelian_retur_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];

        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["pembelian_uuid"] = $save_data["pembelian_uuid"];
        $data["pembelian_number_formatted"] = $save_data["pembelian_number_formatted"];

        $data["number"] = $save_data["number"];
        $data["number_formatted"] = $save_data["number_formatted"];

        $data["tanggal"] = $save_data["tanggal"];
        $data["tahun"] = $save_data["tahun"];

        $data["pemasok_uuid"] = $save_data["pemasok_uuid"];

        $data["gudang_uuid"] = $save_data['gudang_uuid'];
        $data["gudang_kode"] = $save_data['gudang_kode'];
        $data["gudang_nama"] = $save_data['gudang_nama'];

        $data["kas_akun_uuid"] = $save_data['kas_akun_uuid'];
        $data["kas_alur_uuid"] = $save_data['kas_alur_uuid'];

        $data["sub_total"] = $save_data["sub_total"];
        $data["potongan"] = $save_data["potongan"];
        $data["total_akhir"] = $save_data["total_akhir"];
        $data["bayar"] = $save_data["bayar"];
        $data["sisa"] = $save_data["sisa"];
        $data["lunas"] = $save_data["lunas"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];
        $cabang_kode = $save_data["cabang_kode"];

        $this->db->set($data);

        if(empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("pembelian_retur");
        }else{            
            $this->db->set("uuid", $uuid);
            $this->db->update("pembelian_retur");
        }
        return $uuid;
    }

    function pembelian_retur_get_next_number($tahun = "", $gudang_kode = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("pembelian_retur");
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
        $number_formatted = PREFIX_PEMBELIAN_RETUR . "/$cabang_kode/$gudang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }


    function pembelian_retur_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_pembelian_uuid($filters = array())
    {

        $this->db->select("
            pembelian_retur.number_formatted pembelian_retur_number_formatted,
            pembelian_retur.tanggal,
            sum(pembelian_retur_detail.jumlah_satuan_terkecil) total_jumlah_satuan_terkecil,            
        ");
        $this->db->from("pembelian_retur_detail");
        $this->db->join("pembelian_retur", "pembelian_retur.uuid = pembelian_retur_detail.pembelian_retur_uuid", "left");
        $this->db->group_by("pembelian_retur_detail.item_uuid");
        $this->db->group_by("pembelian_retur_detail.pembelian_retur_uuid");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembelian_retur_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pembelian_retur_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("pembelian_retur.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("pembelian_retur.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("pembelian_retur.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_retur_uuid":
                    $this->db->where("pembelian_retur_detail.pembelian_retur_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_retur_uuid_list":
                    $this->db->where_in("pembelian_retur_detail.pembelian_retur_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("pembelian_retur_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(pembelian_retur_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan":
                    $this->db->where("LOWER(pembelian_retur_detail.satuan)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("pembelian_retur_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("pembelian_retur_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }
    // =======================================================================================
    // END - PEMBELIAN RETUR
    // =======================================================================================

    // =======================================================================================
    // PEMBELIAN RETUR DETAIL
    // =======================================================================================
    function pembelian_retur_detail_get_list($filters = array())
    {
        $this->db->select("
            pembelian_retur_detail.*,
            pembelian_retur.tanggal
        ");
        $this->db->from("pembelian_retur_detail");
        $this->db->join("pembelian_retur", "pembelian_retur.uuid = pembelian_retur_detail.pembelian_retur_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembelian_retur_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pembelian_retur_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("pembelian_retur.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("pembelian_retur.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("pembelian_retur.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_retur_uuid":
                    $this->db->where("pembelian_retur_detail.pembelian_retur_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembelian_retur_uuid_list":
                    $this->db->where_in("pembelian_retur_detail.pembelian_retur_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("pembelian_retur_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_tipe":
                    $this->db->where("LOWER(pembelian_retur_detail.item_tipe)",  $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("pembelian_retur_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("pembelian_retur_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function pembelian_retur_detail_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("pembelian_retur_detail");

        return $uuid;
    }

    function pembelian_retur_detail_delete_by_pembelian_retur_uuid($pembelian_retur_uuid = "")
    {
        if (empty($pembelian_retur_uuid)) return false;

        $this->db->where("pembelian_retur_uuid", $pembelian_retur_uuid);
        $this->db->delete("pembelian_retur_detail");

        return $pembelian_retur_uuid;
    }

    function pembelian_retur_detail_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["pembelian_retur_uuid"] = $save_data["pembelian_retur_uuid"];
        $data["item_uuid"] = $save_data["item_uuid"];
        $data["item_kode"] = $save_data["item_kode"];
        $data["item_barcode"] = $save_data["item_barcode"];
        $data["item_nama"] = $save_data["item_nama"];
        $data["item_struktur_satuan_harga_json"] = $save_data["item_struktur_satuan_harga_json"];
        $data["item_tipe"] = $save_data["item_tipe"];
        $data["item_kategori_uuid"] = $save_data["item_kategori_uuid"];
        $data["item_kategori_nama"] = $save_data["item_kategori_nama"];

        $data["harga_beli_satuan_terkecil"] = $save_data["harga_beli_satuan_terkecil"];
        $data["jumlah_satuan_terkecil"] = $save_data["jumlah_satuan_terkecil"];

        $data["jumlah"] = $save_data["jumlah"];
        $data["satuan"] = $save_data["satuan"];
        $data["harga_beli_satuan"] = $save_data["harga_beli_satuan"];
        $data["potongan_persen"] = $save_data["potongan_persen"];
        $data["potongan_harga"] = $save_data["potongan_harga"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);
        if(empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("pembelian_retur_detail");
        }else{            
            $this->db->where("uuid", $uuid);
            $this->db->update("pembelian_retur_detail");
        }
        return $uuid;
    }

    function pembelian_retur_detail_harga_beli_satuan_terkecil_list_by_item_uuid($item_uuid = "")
    {
        if (empty($item_uuid)) return array();

        $this->db->select("harga_beli_satuan_terkecil");
        $this->db->from("pembelian_retur_detail");
        $this->db->where("item_uuid", $item_uuid);
        $res = $this->db->get()->result_array();

        return $res;
    }
    // =======================================================================================
    // END -- PEMBELIAN RETUR DETAIL
    // =======================================================================================

    // =======================================================================================
    // PENJUALAN
    // =======================================================================================
    function penjualan_get_list($filters = array(), $pagination = false, $datatables = false, $get_total = false, $limit = false)
    {
        $column_search  = array(
            null,
            "penjualan.number_formatted",
            "penjualan.tanggal",
            "penjualan.pelanggan_number_formatted",
            "penjualan.pelanggan_nama",
            "penjualan.total_akhir",
            "penjualan.metode_pembayaran",
            "penjualan.cache_status",
        );
        $column_order   = $column_search;
        $order          = array(
            "penjualan.number_formatted" => "asc"
        );

        if (!$get_total) {
            $this->db->select("
                penjualan.*,
                kas_akun.nama kas_akun_nama
            ");
            $this->db->join('kas_akun', 'penjualan.kas_akun_uuid = kas_akun.uuid', 'left');
        } else {
            $this->db->select('sum(total_akhir) total_akhir');
        }
        $this->db->from("penjualan");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("penjualan.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("penjualan.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "number_formatted":
                    $this->db->where("penjualan.number_formatted", $value);
                    unset($filters[$key]);
                    break;
                case "creator_user_uuid":
                    $this->db->where("penjualan.creator_user_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "pelanggan_uuid":
                    $this->db->where("penjualan.pelanggan_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("penjualan.gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "kas_akun_uuid":
                    $this->db->where("penjualan.kas_akun_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "lunas":
                    $this->db->where("penjualan.lunas", (int) $value);
                    unset($filters[$key]);
                    break;
                case "metode_pembayaran":
                    $this->db->where("LOWER(penjualan.metode_pembayaran)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("penjualan.tanggal >= ", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("penjualan.tanggal <= ", $value);
                    unset($filters[$key]);
                    break;
                case "start_date_jatuh_tempo":
                    $this->db->where("penjualan.jatuh_tempo >= ", $value);
                    unset($filters[$key]);
                    break;
                case "end_date_jatuh_tempo":
                    $this->db->where("penjualan.jatuh_tempo <= ", $value);
                    unset($filters[$key]);
                    break;

                case "search_keyword":
                    $this->db->group_start();
                    $this->db->like("penjualan.number_formatted", $value);
                    $this->db->or_like("penjualan.pelanggan_nama", $value);
                    $this->db->or_like("penjualan.pelanggan_number_formatted", $value);
                    $this->db->group_end();
                    
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        if ($datatables) {
            $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
        } else {

            if (isset($filters["order_by"])) {
                $this->db->order_by($filters["order_by"]);
            }else{
                $this->db->order_by("number_formatted");
            }
            if ($limit) $this->db->limit((int) $limit);
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function penjualan_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("penjualan");

        return $uuid;
    }

    function penjualan_update_kas_alur_uuid($uuid = "", $kas_alur_uuid = "")
    {
        if(empty($uuid)) return false;
        if(empty($kas_alur_uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("kas_alur_uuid", $kas_alur_uuid);
        $this->db->update("penjualan");

        return $uuid;
    }

    function penjualan_save($save_data = array())
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

        $data["pelanggan_uuid"] = $save_data["pelanggan_uuid"];
        $data["pelanggan_number_formatted"] = $save_data["pelanggan_number_formatted"];
        $data["pelanggan_nama"] = $save_data["pelanggan_nama"];
        $data["pelanggan_alamat"] = $save_data["pelanggan_alamat"];
        $data["pelanggan_no_telepon"] = $save_data["pelanggan_no_telepon"];
        $data["pelanggan_potongan_persen"] = $save_data["pelanggan_potongan_persen"];

        $data["gudang_uuid"] = $save_data['gudang_uuid'];
        $data["gudang_kode"] = $save_data['gudang_kode'];
        $data["gudang_nama"] = $save_data['gudang_nama'];

        $data["kas_akun_uuid"] = $save_data['kas_akun_uuid'];
        $data["kas_alur_uuid"] = $save_data['kas_alur_uuid'];

        $data["sub_total"] = $save_data["sub_total"];
        $data["potongan"] = $save_data["potongan"];
        $data["total_akhir"] = $save_data["total_akhir"];
        $data["metode_pembayaran"] = $save_data["metode_pembayaran"];
        $data["jatuh_tempo"] = $save_data["jatuh_tempo"];
        $data["bayar"] = $save_data["bayar"];
        $data["kembali"] = $save_data["kembali"];
        $data["sisa"] = $save_data["sisa"];
        $data["lunas"] = $save_data["lunas"];
        $data["cache_status"] = $save_data["cache_status"];
        $data["cache_sisa_piutang"] = $save_data["cache_sisa_piutang"];

        $data["keterangan"] = $save_data["keterangan"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];
        $cabang_kode = $save_data["cabang_kode"];

        $this->db->set($data);

        if(empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("penjualan");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("penjualan"); 
        }

        return $uuid;
    }

    function penjualan_update_status_lunas($uuid = "", $lunas = 0, $cache_status = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("lunas", $lunas);
        $this->db->set("cache_status", $cache_status);
        $this->db->update("penjualan");

        return $uuid;
    }

    function penjualan_update_sisa_piutang($uuid = "", $sisa_piutang = 0)
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("cache_sisa_piutang", $sisa_piutang);
        $this->db->update("penjualan");

        return $uuid;
    }

    function penjualan_get_next_number($tahun = "", $gudang_kode = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("penjualan");
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
        $number_formatted = PREFIX_PENJUALAN . "/$cabang_kode/$gudang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
    // =======================================================================================
    // END - PENJUALAN
    // =======================================================================================

    // =======================================================================================
    // PENJUALAN DETAIL
    // =======================================================================================
    function penjualan_detail_get_item_sering_terjual($limit = 10){
        $res = $this->db->query("
            SELECT * FROM ( 
                SELECT 
                    item_uuid as uuid, 
                    item_kode as kode, 
                    item_nama as nama, 
                    sum(jumlah) as total_qty 
                FROM 
                    penjualan_detail 
                GROUP BY item_uuid 
            ) item 
            ORDER BY total_qty DESC 
            LIMIT $limit
        ")->result_array();

        return $res;
    }

    function penjualan_detail_get_list($filters = array())
    {
        $this->db->select("
            penjualan_detail.*,
            penjualan.tanggal
        ");
        $this->db->from("penjualan_detail");
        $this->db->join("penjualan", "penjualan.uuid = penjualan_detail.penjualan_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("penjualan_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("penjualan_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("penjualan.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("penjualan.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("penjualan.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_uuid":
                    $this->db->where("penjualan_detail.penjualan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_uuid_list":
                    $this->db->where_in("penjualan_detail.penjualan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("penjualan_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(penjualan_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_tipe":
                    $this->db->where("LOWER(penjualan_detail.item_tipe)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan":
                    $this->db->where("LOWER(penjualan_detail.satuan)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("penjualan_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("penjualan_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function penjualan_detail_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("penjualan_detail");

        return $uuid;
    }

    function penjualan_detail_delete_by_penjualan_uuid($penjualan_uuid = "")
    {
        if (empty($penjualan_uuid)) return false;

        $this->db->where("penjualan_uuid", $penjualan_uuid);
        $this->db->delete("penjualan_detail");

        return $penjualan_uuid;
    }

    function penjualan_detail_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["penjualan_uuid"] = $save_data["penjualan_uuid"];
        $data["item_uuid"] = $save_data["item_uuid"];
        $data["item_kode"] = $save_data["item_kode"];
        $data["item_barcode"] = $save_data["item_barcode"];
        $data["item_nama"] = $save_data["item_nama"];
        $data["item_struktur_satuan_harga_json"] = $save_data["item_struktur_satuan_harga_json"];
        $data["item_tipe"] = $save_data["item_tipe"];
        $data["item_cek_stock_saat_penjualan"] = $save_data["item_cek_stock_saat_penjualan"];
        $data["item_kategori_uuid"] = $save_data["item_kategori_uuid"];
        $data["item_kategori_nama"] = $save_data["item_kategori_nama"];

        $data["jumlah"] = $save_data["jumlah"];
        $data["satuan"] = $save_data["satuan"];
        $data["harga_jual_satuan_terkecil"] = $save_data["harga_jual_satuan_terkecil"];
        $data["margin_jual_satuan_terkecil"] = $save_data["margin_jual_satuan_terkecil"];
        $data["jumlah_satuan_terkecil"] = $save_data["jumlah_satuan_terkecil"];
        $data["harga_jual_satuan"] = $save_data["harga_jual_satuan"];
        $data["margin_jual_satuan"] = $save_data["margin_jual_satuan"];
        $data["potongan_persen"] = $save_data["potongan_persen"];
        $data["potongan_harga"] = $save_data["potongan_harga"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);

        if(empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("penjualan_detail");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("penjualan_detail");            
        }
        return $uuid;
    }

    function penjualan_detail_harga_beli_satuan_terkecil_list_by_item_uuid($item_uuid = "")
    {
        if ((int) $item_uuid == 0) return array();

        $this->db->select("harga_beli_satuan_terkecil");
        $this->db->from("penjualan_detail");
        $this->db->where("item_uuid", $item_uuid);
        $res = $this->db->get()->result_array();

        return $res;
    }


    function penjualan_detail_get_total_jumlah_satuan_terkecil_list_group_by_item_uuid_and_penjualan_uuid($filters = array())
    {

        $this->db->select("
            penjualan.number_formatted penjualan_number_formatted,
            penjualan.tanggal,
            sum(penjualan_detail.jumlah_satuan_terkecil) total_jumlah_satuan_terkecil,            
        ");
        $this->db->from("penjualan_detail");
        $this->db->join("penjualan", "penjualan.uuid = penjualan_detail.penjualan_uuid", "left");
        $this->db->group_by("penjualan_detail.item_uuid");
        $this->db->group_by("penjualan_detail.penjualan_uuid");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("penjualan_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("penjualan_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("penjualan.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("penjualan.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("penjualan.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_uuid":
                    $this->db->where("penjualan_detail.penjualan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_uuid_list":
                    $this->db->where_in("penjualan_detail.penjualan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("penjualan_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_kode":
                    $this->db->where("LOWER(penjualan_detail.item_kode)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "satuan":
                    $this->db->where("LOWER(penjualan_detail.satuan)", strtolower($value));
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("penjualan_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("penjualan_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }
    // =======================================================================================
    // END -- PENJUALAN DETAIL
    // =======================================================================================

    // =======================================================================================
    // PEMBAYARAN PIUTANG
    // =======================================================================================
    function pembayaran_piutang_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "pembayaran_piutang.number_formatted",
            "pembayaran_piutang.tanggal",
            "pembayaran_piutang.cara_bayar",
            "pelanggan.number_formatted",
            "pelanggan.nama",
            "pembayaran_piutang.jumlah",
            "pembayaran_piutang.keterangan"
        );
        $column_order   = $column_search;
        $order          = array(
            "pembayaran_piutang.number_formatted" => "asc"
        );

        $this->db->select("
            pembayaran_piutang.*,
            pelanggan.number_formatted pelanggan_number_formatted, 
            pelanggan.nama pelanggan_nama,
            pelanggan.alamat pelanggan_alamat,
            pelanggan.no_telepon pelanggan_no_telepon,
        ");
        $this->db->from("pembayaran_piutang");
        $this->db->join("pelanggan", "pelanggan.uuid = pembayaran_piutang.pelanggan_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembayaran_piutang.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pembayaran_piutang.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pelanggan_uuid":
                    $this->db->where("pembayaran_piutang.pelanggan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("pembayaran_piutang.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("pembayaran_piutang.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "kas_akun_uuid":
                    $this->db->where("pembayaran_piutang.kas_akun_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cara_bayar":
                    $this->db->where("LOWER(pembayaran_piutang.cara_bayar)", strtolower($value));
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function pembayaran_piutang_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("pembayaran_piutang");

        return $uuid;
    }

    function pembayaran_piutang_update_kas_alur_uuid($uuid = "", $kas_alur_uuid = "")
    {
        if(empty($uuid)) return false;
        if(empty($kas_alur_uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("kas_alur_uuid", $kas_alur_uuid);
        $this->db->update("pembayaran_piutang");

        return $uuid;
    }

    function pembayaran_piutang_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["pelanggan_uuid"] = $save_data["pelanggan_uuid"];
        $data["number"] = $save_data["number"];
        $data["number_formatted"] = $save_data["number_formatted"];
        $data["tanggal"] = $save_data["tanggal"];
        $data["tahun"] = $save_data["tahun"];
        $data["jumlah"] = $save_data["jumlah"];
        $data["sisa_jumlah_bayar"] = $save_data["sisa_jumlah_bayar"];
        $data["cara_bayar"] = $save_data["cara_bayar"];
        $data["kas_akun_uuid"] = $save_data["kas_akun_uuid"];
        $data["kas_alur_uuid"] = $save_data["kas_alur_uuid"];
        $data["keterangan"] = $save_data["keterangan"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];
        $cabang_kode = $save_data["cabang_kode"];

        $this->db->set($data);
        
        if(empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("pembayaran_piutang");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("pembayaran_piutang");            
        }
        return $uuid;
    }

    function pembayaran_piutang_get_next_number($tahun = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("pembayaran_piutang");
        $this->db->where("tahun", $tahun);
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
        $number_formatted = PREFIX_PEMBAYARAN_PIUTANG . "/$cabang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
    // =======================================================================================
    // END -- PEMBAYARAN PIUTANG
    // =======================================================================================

    // =======================================================================================
    // PEMBAYARAN PIUTANG DETAIL
    // =======================================================================================
    function pembayaran_piutang_detail_get_list($filters = array())
    {
        $this->db->select("
            pembayaran_piutang_detail.*,
            pembayaran_piutang.number_formatted pembayaran_piutang_number_formatted, 
            pembayaran_piutang.tanggal pembayaran_piutang_tanggal,
            pembayaran_piutang.keterangan pembayaran_piutang_keterangan,
            pembayaran_piutang.cara_bayar pembayaran_piutang_cara_bayar,
            penjualan.uuid, 
            penjualan.number_formatted, 
            penjualan.tanggal,
            penjualan.jatuh_tempo,
        ");
        $this->db->from("pembayaran_piutang_detail");
        $this->db->join("pembayaran_piutang", "pembayaran_piutang.uuid = pembayaran_piutang_detail.pembayaran_piutang_uuid", "left");
        $this->db->join("penjualan", "penjualan.uuid = pembayaran_piutang_detail.penjualan_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("pembayaran_piutang_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("pembayaran_piutang_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_uuid":
                    $this->db->where("pembayaran_piutang_detail.penjualan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_uuid_list":
                    $this->db->where_in("pembayaran_piutang_detail.penjualan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembayaran_piutang_uuid":
                    $this->db->where("pembayaran_piutang_detail.pembayaran_piutang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "pembayaran_piutang_uuid_list":
                    $this->db->where_in("pembayaran_piutang_detail.pembayaran_piutang_uuid", $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function pembayaran_piutang_detail_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("pembayaran_piutang_detail");

        return $uuid;
    }

    function pembayaran_piutang_detail_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["pembayaran_piutang_uuid"] = $save_data["pembayaran_piutang_uuid"];
        $data["penjualan_uuid"] = $save_data["penjualan_uuid"];
        $data["sisa_piutang"] = $save_data["sisa_piutang"];
        $data["potongan"] = $save_data["potongan"];
        $data["jumlah"] = $save_data["jumlah"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);
        
        if(empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("pembayaran_piutang_detail");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("pembayaran_piutang_detail");
        }

        return $uuid;
    }

    function pembayaran_piutang_detail_delete_for_pembayaran_piutang_uuid($pembayaran_piutang_uuid = "")
    {
        if (empty($pembayaran_piutang_uuid)) return false;

        $this->db->where("pembayaran_piutang_uuid", $pembayaran_piutang_uuid);
        $this->db->delete("pembayaran_piutang_detail");

        return $pembayaran_piutang_uuid;
    }

    function pembayaran_piutang_detail_get_total_pembayaran_for_penjualan_uuid_list($penjualan_uuid_list = array())
    {
        if (!is_array($penjualan_uuid_list))   return array();
        if (count($penjualan_uuid_list) == 0)  return array();

        $pembayaran_piutang_total_list = array();
        foreach ($penjualan_uuid_list as $index => $penjualan_uuid) {
            $pembayaran_piutang_total_list[$penjualan_uuid] = 0;
        }

        $this->db->where_in("penjualan_uuid", $penjualan_uuid_list);
        $this->db->select("penjualan_uuid, sisa_piutang, potongan, jumlah");
        $this->db->from("pembayaran_piutang_detail");
        $res = $this->db->get()->result_array();

        if (count($res) == 0) return $pembayaran_piutang_total_list;

        foreach ($res as $r) {
            $penjualan_uuid = $r["penjualan_uuid"];
            $sisa_piutang = (float) $r["sisa_piutang"];
            $potongan = (float) $r["potongan"];

            $jumlah = (float) $r["jumlah"];

            $pembayaran_piutang_total_list[$penjualan_uuid] += $jumlah;
        }

        return $pembayaran_piutang_total_list;
    }
    // =======================================================================================
    // END -- PEMBAYARAN PIUTANG DETAIL
    // =======================================================================================





    // =======================================================================================
    // PENJUALAN RETUR
    // =======================================================================================
    function penjualan_retur_get_list($filters = array(), $pagination = false, $datatables = false)
    {
        $column_search  = array(
            null,
            "penjualan_retur.number_formatted",
            "penjualan_retur.tanggal",
            "penjualan_retur.pelanggan_number_formatted",
            "penjualan_retur.pelanggan_nama",
            "penjualan_retur.total_akhir",
            "penjualan_retur.sisa",
            "penjualan_retur.lunas",
        );
        $column_order   = $column_search;
        $order          = array(
            "penjualan_retur.number_formatted" => "asc"
        );

        $this->db->select("
            penjualan_retur.*,
            penjualan.pelanggan_number_formatted, 
            penjualan.pelanggan_nama, 
            penjualan.pelanggan_alamat, 
            penjualan.pelanggan_no_telepon
        ");
        $this->db->join('kas_akun', 'penjualan_retur.kas_akun_uuid = kas_akun.uuid', 'left');
        $this->db->join('penjualan', 'penjualan_retur.penjualan_uuid = penjualan.uuid', 'left');
        $this->db->from("penjualan_retur");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("penjualan_retur.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("penjualan_retur.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_uuid":
                    $this->db->where("penjualan_retur.penjualan_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "number_formatted":
                    $this->db->where("penjualan_retur.number_formatted", $value);
                    unset($filters[$key]);
                    break;
                case "pelanggan_uuid":
                    $this->db->where("penjualan_retur.pelanggan_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("penjualan_retur.gudang_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "kas_akun_uuid":
                    $this->db->where("penjualan_retur.kas_akun_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "lunas":
                    $this->db->where("penjualan_retur.lunas", (int) $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("penjualan_retur.tanggal >= ", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("penjualan_retur.tanggal <= ", $value);
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

    function penjualan_retur_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("penjualan_retur");

        return $uuid;
    }

    function penjualan_retur_update_kas_alur_uuid($uuid = "", $kas_alur_uuid = "")
    {
        if(empty($uuid)) return false;
        if(empty($kas_alur_uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->set("kas_alur_uuid", $kas_alur_uuid);
        $this->db->update("penjualan_retur");

        return $uuid;
    }

    function penjualan_retur_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];

        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["penjualan_uuid"] = $save_data["penjualan_uuid"];
        $data["penjualan_number_formatted"] = $save_data["penjualan_number_formatted"];

        $data["number"] = $save_data["number"];
        $data["number_formatted"] = $save_data["number_formatted"];

        $data["tanggal"] = $save_data["tanggal"];
        $data["tahun"] = $save_data["tahun"];

        $data["pelanggan_uuid"] = $save_data["pelanggan_uuid"];
        $data["gudang_kode"] = $save_data['gudang_kode'];
        $data["gudang_nama"] = $save_data['gudang_nama'];

        $data["gudang_uuid"] = $save_data['gudang_uuid'];

        $data["kas_akun_uuid"] = $save_data['kas_akun_uuid'];
        $data["kas_alur_uuid"] = $save_data['kas_alur_uuid'];

        $data["sub_total"] = $save_data["sub_total"];
        $data["potongan"] = $save_data["potongan"];
        $data["total_akhir"] = $save_data["total_akhir"];
        $data["bayar"] = $save_data["bayar"];
        $data["sisa"] = $save_data["sisa"];
        $data["lunas"] = $save_data["lunas"];

        $data["keterangan"] = $save_data["keterangan"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];
        $cabang_kode = $save_data["cabang_kode"];
        

        $this->db->set($data);
        if(empty($uuid)) {
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("penjualan_retur");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("penjualan_retur");
        }
        
        return $uuid;
    }

    function penjualan_retur_get_next_number($tahun = "", $gudang_kode = "", $cabang_uuid = "", $cabang_kode = "")
    {
        if (empty($tahun)) $tahun = date("Y");
        if (strlen($tahun) != 4) $tahun = date("Y");

        $this->db->select_max("number", "max_number");
        $this->db->from("penjualan_retur");
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
        $number_formatted = PREFIX_PENJUALAN_RETUR . "/$cabang_kode/$gudang_kode/$year_code/" . str_pad($number, 5, "0", STR_PAD_LEFT);

        $result = array(
            "number" => $number,
            "number_formatted" => $number_formatted
        );

        return $result;
    }
    // =======================================================================================
    // END - PENJUALAN RETUR
    // =======================================================================================

    // =======================================================================================
    // PENJUALAN DETAIL RETUR
    // =======================================================================================
    function penjualan_retur_detail_get_list($filters = array())
    {
        $this->db->select("
            penjualan_retur_detail.*,
            penjualan_retur.tanggal
        ");
        $this->db->from("penjualan_retur_detail");
        $this->db->join("penjualan_retur", "penjualan_retur.uuid = penjualan_retur_detail.penjualan_retur_uuid", "left");

        foreach ($filters as $key => $value) {
            switch ($key) {
                case "uuid":
                    $this->db->where("penjualan_retur_detail.uuid", $value);
                    unset($filters[$key]);
                    break;
                case "cabang_uuid":
                    $this->db->where("penjualan_retur_detail.cabang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "start_date":
                    $this->db->where("penjualan_retur.tanggal >=", $value);
                    unset($filters[$key]);
                    break;
                case "end_date":
                    $this->db->where("penjualan_retur.tanggal <=", $value);
                    unset($filters[$key]);
                    break;
                case "gudang_uuid":
                    $this->db->where("penjualan_retur.gudang_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_retur_uuid":
                    $this->db->where("penjualan_retur_detail.penjualan_retur_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "penjualan_retur_uuid_list":
                    $this->db->where_in("penjualan_retur_detail.penjualan_retur_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid":
                    $this->db->where("penjualan_retur_detail.item_uuid",  $value);
                    unset($filters[$key]);
                    break;
                case "item_tipe":
                    $this->db->where("LOWER(penjualan_retur_detail.item_tipe)",  $value);
                    unset($filters[$key]);
                    break;
                case "item_uuid_list":
                    $this->db->where_in("penjualan_retur_detail.item_uuid", $value);
                    unset($filters[$key]);
                    break;
                case "item_kategori_uuid":
                    $this->db->where("penjualan_retur_detail.item_kategori_uuid",  $value);
                    unset($filters[$key]);
                    break;
                default:
                    break;
            }
        }

        $res = $this->db->get()->result_array();
        return $res;
    }

    function penjualan_retur_detail_delete($uuid = "")
    {
        if(empty($uuid)) return false;

        $this->db->where("uuid", $uuid);
        $this->db->delete("penjualan_retur_detail");

        return $uuid;
    }

    function penjualan_retur_detail_delete_by_penjualan_retur_uuid($penjualan_retur_uuid = "")
    {
        if (empty($penjualan_retur_uuid)) return false;

        $this->db->where("penjualan_retur_uuid", $penjualan_retur_uuid);
        $this->db->delete("penjualan_retur_detail");

        return $penjualan_retur_uuid;
    }

    function penjualan_retur_detail_save($save_data = array())
    {
        $uuid = $save_data["uuid"];

        $data = array();
        $data["created"] = $save_data["created"];
        $data["creator_user_uuid"] = $save_data["creator_user_uuid"];
        $data["creator_user_name"] = $save_data["creator_user_name"];
        $data["last_updated"] = date("Y-m-d H:i:s");
        $data["last_updated_user_uuid"] = $save_data["last_updated_user_uuid"];
        $data["last_updated_user_name"] = $save_data["last_updated_user_name"];

        $data["penjualan_retur_uuid"] = $save_data["penjualan_retur_uuid"];
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
        $data["harga_jual_satuan_terkecil"] = $save_data["harga_jual_satuan_terkecil"];
        $data["margin_jual_satuan_terkecil"] = $save_data["margin_jual_satuan_terkecil"];
        $data["jumlah_satuan_terkecil"] = $save_data["jumlah_satuan_terkecil"];
        $data["harga_jual_satuan"] = $save_data["harga_jual_satuan"];
        $data["margin_jual_satuan"] = $save_data["margin_jual_satuan"];
        $data["potongan_persen"] = $save_data["potongan_persen"];
        $data["potongan_harga"] = $save_data["potongan_harga"];

        $data["cabang_uuid"] = $save_data["cabang_uuid"];

        $this->db->set($data);
        if(Empty($uuid)){
            $uuid = $this->uuid_v4();
            $this->db->set("uuid", $uuid);
            $this->db->insert("penjualan_retur_detail");
        }else{
            $this->db->where("uuid", $uuid);
            $this->db->update("penjualan_retur_detail");
        }
        

        if ((int) $uuid == 0) {
            $uuid = $this->db->insert_uuid();
        }
        return $uuid;
    }

    function penjualan_retur_detail_harga_beli_satuan_terkecil_list_by_item_uuid($item_uuid = "")
    {
        if (empty($item_uuid)) return array();

        $this->db->select("harga_beli_satuan_terkecil");
        $this->db->from("penjualan_retur_detail");
        $this->db->where("item_uuid", $item_uuid);
        $res = $this->db->get()->result_array();

        return $res;
    }
    // =======================================================================================
    // END -- PENJUALAN RETUR DETAIL
    // =======================================================================================
}
