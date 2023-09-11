<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kas_engine extends Db_engine
{
  //put your code here

  public function __construct()
  {
    parent::__construct();
  }

  // ===========================================================================
  // KAS AKUN
  // ===========================================================================
  function kas_akun_get_list($filters = array(), $pagination = false, $datatables = false)
  {
    $column_search  = array(
      null,
      "kas_akun.nama",
      "kas_akun.keterangan",
    );
    $column_order   = $column_search;
    $order          = array(
      "kas_akun.nama" => "asc"
    );

    $this->db->select("*");
    $this->db->from("kas_akun");

    foreach ($filters as $key => $value) {
      switch ($key) {
        case "uuid":
          $this->db->where("uuid", $value);
          unset($filters[$key]);
          break;
        case "cabang_uuid":
          $this->db->where("cabang_uuid", $value);
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

  function kas_akun_delete($uuid = "")
  {
    if (empty($uuid)) return false;

    $this->db->where("uuid", $uuid);
    $this->db->delete("kas_akun");

    return $uuid;
  }

  function kas_akun_save($save_data = array())
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
    
    if(empty($uuid)) {
      $uuid = $this->uuid_v4();
      $this->db->set("uuid", $uuid);
      $this->db->insert("kas_akun");
    }else{
      $this->db->where("uuid", $uuid);
      $this->db->update("kas_akun");
    }
    return $uuid;
  }
  // ===========================================================================
  // END -- KAS AKUN
  // ===========================================================================

  // ===========================================================================
  // KAS KATEGORI
  // ===========================================================================
  function kas_kategori_get_list($filters = array(), $pagination = false, $datatables = false)
  {
    $column_search  = array(
      null,
      "kas_kategori.nama",
      "kas_kategori.alur_kas",
      "kas_kategori.keterangan",
    );
    $column_order   = $column_search;
    $order          = array(
      "kas_kategori.nama" => "asc"
    );

    $this->db->select("*");
    $this->db->from("kas_kategori");

    foreach ($filters as $key => $value) {
      switch ($key) {
        case "uuid":
          $this->db->where("uuid", $value);
          unset($filters[$key]);
          break;
        case "cabang_uuid":
          $this->db->where("cabang_uuid", $value);
          unset($filters[$key]);
          break;
        case 'alur_kas':
          $this->db->where("LOWER(id)", strtolower($value));
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

  function kas_kategori_delete($uuid = "")
  {
    if (empty($uuid)) return false;

    $this->db->where("uuid", $uuid);
    $this->db->delete("kas_kategori");

    return $uuid;
  }

  function kas_kategori_save($save_data = array())
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
    $data["alur_kas"] = $save_data["alur_kas"];
    
    $data["cabang_uuid"] = $save_data["cabang_uuid"];

    $this->db->set($data);
    
    if(empty($uuid)) {
      $uuid = $this->uuid_v4();
      $this->db->set("uuid", $uuid);
      $this->db->insert("kas_kategori");
    }else{
      $this->db->where("uuid", $uuid);
      $this->db->update("kas_kategori");
    }
    
    return $uuid;
  }
  // ===========================================================================
  // END -- KAS AKUN
  // ===========================================================================

  // ===========================================================================
  // KAS
  // ===========================================================================
  function kas_alur_get_list($filters = array(), $pagination = false, $datatables = false)
  {

    // jumlah index sesuai jumlah kolom di front-end
    $column_search  = array(
      null,
      "kas_alur.number_formatted",
      "kas_alur.tanggal",
      "kas_alur.kas_akun_nama",
      "kas_alur.kas_kategori_nama",
      "kas_alur.keterangan",
      "kas_alur.jumlah_masuk",
      "kas_alur.jumlah_keluar",
    );
    $column_order   = $column_search;
    $order          = array(
      "kas_alur.tanggal" => "desc",
    );

    foreach ($filters as $key => $value) {
      switch ($key) {
        case "uuid":
          $this->db->where("kas_alur.uuid", $value);
          unset($filters[$key]);
          break;
        case "cabang_uuid":
          $this->db->where("kas_alur.cabang_uuid", $value);
          unset($filters[$key]);
          break;
        case 'number_formatted':
          $this->db->like("number_formatted", trim($value));
          unset($filters[$key]);
          break;
        case 'start_date':
          $this->db->where("kas_alur.tanggal >=", date("Y-m-d", strtotime(trim($value))));
          unset($filters[$key]);
          break;
        case 'tanggal':
          $this->db->where("kas_alur.tanggal", date("Y-m-d", strtotime(trim($value))));
          unset($filters[$key]);
          break;
        case 'end_date':
          $this->db->where("kas_alur.tanggal <=", date("Y-m-d", strtotime(trim($value))));
          unset($filters[$key]);
          break;
        case 'alur_kas':
          $this->db->where("LOWER(kas_alur.alur_kas)", strtolower($value));
          unset($filters[$key]);
          break;
        case 'kas_akun_uuid':
          $this->db->where("kas_alur.kas_akun_uuid", $value);
          unset($filters[$key]);
          break;
        case 'kas_kategori_uuid':
          $this->db->where("kas_alur.kas_kategori_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_pembelian_uuid':
          $this->db->where("kas_alur.transaksi_pembelian_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_pembelian_retur_uuid':
          $this->db->where("kas_alur.transaksi_pembelian_retur_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_penjualan_uuid':
          $this->db->where("kas_alur.transaksi_penjualan_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_pembayaran_piutang_uuid':
          $this->db->where("kas_alur.transaksi_pembayaran_piutang_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_penjualan_retur_uuid':
          $this->db->where("kas_alur.transaksi_penjualan_retur_uuid", $value);
          unset($filters[$key]);
          break;

        default:
          break;
      }
    }

    $this->db->select("kas_alur.*");
    $this->db->from("kas_alur");

    if ($datatables) {
      $this->generate_datatables_input($filters, $column_search, $column_order, $order, $pagination);
    }

    $res = $this->db->get()->result_array();

    return $res;
  } 

  
  function kas_alur_get_list_for_print($filters = array()){
    

    $this->db->select("kas_alur.*");
    $this->db->from("kas_alur");
    
    foreach ($filters as $key => $value) {
      switch ($key) {
        case "uuid":
          $this->db->where("kas_alur.uuid", $value);
          unset($filters[$key]);
          break;
        case "cabang_uuid":
          $this->db->where("kas_alur.cabang_uuid", $value);
          unset($filters[$key]);
          break;
        case 'number_formatted':
          $this->db->like("number_formatted", trim($value));
          unset($filters[$key]);
          break;
        case 'start_date':
          $this->db->where("kas_alur.tanggal >=", date("Y-m-d", strtotime(trim($value))));
          unset($filters[$key]);
          break;
        case 'tanggal':
          $this->db->where("kas_alur.tanggal", date("Y-m-d", strtotime(trim($value))));
          unset($filters[$key]);
          break;
        case 'end_date':
          $this->db->where("kas_alur.tanggal <=", date("Y-m-d", strtotime(trim($value))));
          unset($filters[$key]);
          break;
        case 'alur_kas':
          $this->db->where("LOWER(kas_alur.alur_kas)", strtolower($value));
          unset($filters[$key]);
          break;
        case 'kas_akun_uuid':
          $this->db->where("kas_alur.kas_akun_uuid", $value);
          unset($filters[$key]);
          break;
        case 'kas_kategori_uuid':
          $this->db->where("kas_alur.kas_kategori_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_pembelian_uuid':
          $this->db->where("kas_alur.transaksi_pembelian_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_pembelian_retur_uuid':
          $this->db->where("kas_alur.transaksi_pembelian_retur_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_penjualan_uuid':
          $this->db->where("kas_alur.transaksi_penjualan_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_pembayaran_piutang_uuid':
          $this->db->where("kas_alur.transaksi_pembayaran_piutang_uuid", $value);
          unset($filters[$key]);
          break;
        case 'transaksi_penjualan_retur_uuid':
          $this->db->where("kas_alur.transaksi_penjualan_retur_uuid", $value);
          unset($filters[$key]);
          break;

        default:
          break;
      }
    }
    $this->db->order_by("kas_alur.tanggal asc, kas_alur.number_formatted asc, kas_alur.kas_transfer_uuid");
    $res = $this->db->get()->result_array();
    return $res;
  }

  function kas_alur_delete($uuid = "")
  {
    if (empty($uuid)) return false;

    $this->db->where("uuid", $uuid);
    $this->db->delete("kas_alur");

    return $uuid;
  }

  function kas_alur_delete_for_transaksi_pembelian_uuid($transaksi_pembelian_uuid = "")
  {
    if (empty($transaksi_pembelian_uuid)) return false;

    $this->db->where("transaksi_pembelian_uuid", $transaksi_pembelian_uuid);
    $this->db->delete("kas_alur");

    return $transaksi_pembelian_uuid;
  }

  function kas_alur_delete_for_transaksi_pembelian_retur_uuid($transaksi_pembelian_retur_uuid = "")
  {
    if (empty($transaksi_pembelian_retur_uuid)) return false;

    $this->db->where("transaksi_pembelian_retur_uuid", $transaksi_pembelian_retur_uuid);
    $this->db->delete("kas_alur");

    return $transaksi_pembelian_retur_uuid;
  }

  function kas_alur_delete_for_transaksi_penjualan_uuid($transaksi_penjualan_uuid = "")
  {
    if (empty($transaksi_penjualan_uuid)) return false;

    $this->db->where("transaksi_penjualan_uuid", $transaksi_penjualan_uuid);
    $this->db->delete("kas_alur");

    return $transaksi_penjualan_uuid;
  }

  function kas_alur_delete_for_transaksi_penjualan_retur_uuid($transaksi_penjualan_retur_uuid = "")
  {
    if (empty($transaksi_penjualan_retur_uuid)) return false;

    $this->db->where("transaksi_penjualan_retur_uuid", $transaksi_penjualan_retur_uuid);
    $this->db->delete("kas_alur");

    return $transaksi_penjualan_retur_uuid;
  }

  function kas_alur_delete_for_transaksi_pembayaran_piutang_uuid($transaksi_pembayaran_piutang_uuid = "")
  {
    if (empty($transaksi_pembayaran_piutang_uuid)) return false;

    $this->db->where("transaksi_pembayaran_piutang_uuid", $transaksi_pembayaran_piutang_uuid);
    $this->db->delete("kas_alur");

    return $transaksi_pembayaran_piutang_uuid;
  }

  function kas_alur_save($save_data = array())
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

    $data["kas_akun_uuid"] = $save_data["kas_akun_uuid"];
    $data["kas_akun_nama"] = $save_data["kas_akun_nama"];

    $data["kas_kategori_uuid"] = $save_data["kas_kategori_uuid"];
    $data["kas_kategori_nama"] = $save_data["kas_kategori_nama"];

    $data["alur_kas"] = strtolower($save_data["alur_kas"]);

    $data["jumlah_masuk"] = $save_data["jumlah_masuk"];
    $data["jumlah_keluar"] = $save_data["jumlah_keluar"];

    $data["kas_transfer_uuid"] = $save_data["kas_transfer_uuid"];
    $data["kas_transfer_number_formatted"] = $save_data["kas_transfer_number_formatted"];

    $data["transaksi_pembelian_uuid"] = $save_data["transaksi_pembelian_uuid"];
    $data["transaksi_pembelian_number_formatted"] = $save_data["transaksi_pembelian_number_formatted"];
    $data["transaksi_pembelian_retur_uuid"] = $save_data["transaksi_pembelian_retur_uuid"];
    $data["transaksi_pembelian_retur_number_formatted"] = $save_data["transaksi_pembelian_retur_number_formatted"];

    $data["transaksi_penjualan_uuid"] = $save_data["transaksi_penjualan_uuid"];
    $data["transaksi_penjualan_number_formatted"] = $save_data["transaksi_penjualan_number_formatted"];
    $data["transaksi_penjualan_retur_uuid"] = $save_data["transaksi_penjualan_retur_uuid"];
    $data["transaksi_penjualan_retur_number_formatted"] = $save_data["transaksi_penjualan_retur_number_formatted"];
    $data["transaksi_pembayaran_piutang_uuid"] = $save_data["transaksi_pembayaran_piutang_uuid"];
    $data["transaksi_pembayaran_piutang_number_formatted"] = $save_data["transaksi_pembayaran_piutang_number_formatted"];

    $data["keterangan"] = $save_data["keterangan"];
    $data["cabang_uuid"] = $save_data["cabang_uuid"];

    $this->db->set($data);
    
    if(empty($uuid)) {
      $uuid = $this->uuid_v4();
      $this->db->set("uuid", $uuid);
      $this->db->insert("kas_alur");
    }else{
      $this->db->where("uuid", $uuid);
      $this->db->update("kas_alur");
    }

    return $uuid;
  }

  function kas_alur_get_saldo_for_kas_akun_uuid($kas_akun_uuid = "")
  {
    if (empty($kas_akun_uuid)) return false;

    $this->db->select("(sum(jumlah_masuk) - sum(jumlah_keluar)) saldo");
    $this->db->from("kas_alur");
    $this->db->where("kas_akun_uuid", $kas_akun_uuid);
    $res = $this->db->get()->result_array();

    $res = $res[0];
    $saldo = (float) $res["saldo"];

    return $saldo;
  }

  function kas_alur_get_saldo_saat_ini($cabang_uuid = "")
  {
    $this->db->select("(sum(jumlah_masuk) - sum(jumlah_keluar)) saldo");
    $this->db->from("kas_alur");
    $this->db->where("cabang_uuid", $cabang_uuid);
    $res = $this->db->get()->result_array();

    $res = $res[0];
    $saldo = (float) $res["saldo"];

    return $saldo;
  }

  function kas_alur_get_saldo_sebelumnya_for_date($date = "", $cabang_uuid = "")
  {
    if (empty($date)) return 0;

    $this->db->select("(sum(jumlah_masuk) - sum(jumlah_keluar)) saldo");
    $this->db->from("kas_alur");
    $this->db->where("tanggal <=", $date);
    $this->db->where("cabang_uuid", $cabang_uuid);
    $res = $this->db->get()->result_array();

    $res = $res[0];
    $saldo = (float) $res["saldo"];

    return $saldo;
  }

  function kas_alur_set_kas_number($uuid = "", $number = 0, $number_formatted = "")
  {
    if (empty($uuid))      return false;
    if ((int) $number == 0)      return false;
    if (empty($number_formatted))      return false;

    $this->db->set("number", $number);
    $this->db->set("number_formatted", $number_formatted);
    $this->db->where("uuid", $uuid);
    $this->db->update("kas_alur");

    return $uuid;
  }

  function kas_alur_set_tanggal($uuid = "", $tanggal = "")
  {
    if (empty($uuid))      return false;

    $this->db->set("tanggal", $tanggal);
    $this->db->where("uuid", $uuid);
    $this->db->update("kas_alur");

    return $uuid;
  }

  function kas_alur_get_next_number($alur_kas = "", $tanggal = "", $cabang_uuid = "")
  {
    $alur_kas = strtolower(trim($alur_kas));
    $tanggal = date("ymd", strtotime($tanggal));

    $this->db->select_max("number");
    $this->db->from("kas_alur");
    $this->db->where("LOWER(alur_kas) = ", $alur_kas);
    $this->db->where("DATE_FORMAT(tanggal, '%y%m%d') = ", $tanggal);
    $this->db->where("cabang_uuid", $cabang_uuid);
    $res = $this->db->get()->result_array();

    $res = $res[0];
    $number = (int)$res["number"];

    return $number + 1;
  }

  function kas_alur_generate_number_formatted($prefix = "", $tanggal = "", $number = 1, $pad_length = 5, $cabang_kode = "")
  {
    if (!isset($prefix)) $prefix = "";
    if (!isset($tanggal)) $tanggal = date("ymd");
    if ((int) $number == 0) $number = 1;
    if ((int) $pad_length == 0) $pad_length = 5;

    $number_formatted = $prefix . $tanggal . str_pad($number, $pad_length, "0", STR_PAD_LEFT);

    return $number_formatted;
  }
  // ===========================================================================
  // END -- KAS
  // ===========================================================================
}
